<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Driver;
use App\Models\MediaAsset;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Tour;
use App\Models\Vehicle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MediaManagerService
{
    /**
     * Allowed extensions for scanning & uploading.
     */
    protected array $imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'avif', 'svg', 'gif', 'bmp'];
    protected array $videoExtensions = ['mp4', 'webm', 'mov', 'ogg', 'm4v'];

    /**
     * Normalize absolute or local path to canonical web-relative path (e.g. images/... or videos/...).
     */
    public function normalizeRelativePath(string $fullPath): string
    {
        $normalized = str_replace('\\', '/', $fullPath);

        // Extract canonical path starting with standard web folders
        foreach (['images/', 'videos/', 'uploads/'] as $prefix) {
            $pos = strpos($normalized, '/' . $prefix);
            if ($pos !== false) {
                return substr($normalized, $pos + 1);
            }
            if (str_starts_with($normalized, $prefix)) {
                return $normalized;
            }
        }

        // Fallback: strip public_path if matched
        $normalizedPublic = rtrim(str_replace('\\', '/', public_path()), '/') . '/';
        if (str_starts_with($normalized, $normalizedPublic)) {
            return substr($normalized, strlen($normalizedPublic));
        }

        return ltrim($normalized, '/');
    }

    /**
     * Scan all existing media in public directories and synchronize with database.
     */
    public function scanAndSyncExistingMedia(): array
    {
        $stats = [
            'scanned' => 0,
            'indexed' => 0,
            'updated' => 0,
            'skipped' => 0,
            'in_use' => 0,
        ];

        // Target scan directories (both standard public_path and parent public_html for Hostinger)
        $targetDirs = array_filter([
            public_path('images'),
            public_path('videos'),
            public_path('uploads'),
            base_path('../public_html/images'),
            base_path('../public_html/videos'),
            base_path('../public_html/uploads'),
        ], fn ($p) => File::exists($p));

        // Sanitize any existing records with raw server absolute path prefixes
        try {
            $corrupted = MediaAsset::where('public_url', 'like', '%home/%')
                ->orWhere('public_url', 'like', '%public_html%')
                ->orWhere('public_url', 'like', '%dunes-laravel%')
                ->get();
            foreach ($corrupted as $row) {
                $row->update([
                    'public_url' => $row->clean_public_url,
                    'file_path' => ltrim($row->clean_public_url, '/'),
                ]);
            }
        } catch (\Throwable $ex) {
            Log::warning('MediaManagerService sanitize notice: ' . $ex->getMessage());
        }

        // Deduplicate directory paths
        $realDirs = [];
        foreach ($targetDirs as $d) {
            $r = realpath($d);
            if ($r && ! in_array($r, $realDirs)) {
                $realDirs[] = $r;
            }
        }

        foreach ($realDirs as $dir) {
            $files = File::allFiles($dir);

            foreach ($files as $file) {
                $ext = strtolower($file->getExtension());
                if (! in_array($ext, array_merge($this->imageExtensions, $this->videoExtensions))) {
                    $stats['skipped']++;
                    continue;
                }

                $stats['scanned']++;

                $fullPath = $file->getRealPath();
                $relativePath = $this->normalizeRelativePath($fullPath);
                $publicUrl = '/' . $relativePath;
                $filename = $file->getFilename();
                $sizeBytes = $file->getSize();

                // Determine media type
                $mediaType = in_array($ext, $this->videoExtensions) ? 'video' : 'image';
                $mimeType = File::mimeType($fullPath) ?: ($mediaType === 'video' ? 'video/' . $ext : 'image/' . $ext);

                // Dimensions for raster images
                $width = null;
                $height = null;
                if ($mediaType === 'image' && in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'avif', 'gif'])) {
                    $imageInfo = @getimagesize($fullPath);
                    if ($imageInfo) {
                        $width = $imageInfo[0];
                        $height = $imageInfo[1];
                    }
                }

                // Compute SHA256 content hash for robust deduplication
                $fileHash = @hash_file('sha256', $fullPath) ?: null;

                // Categorization heuristic
                $category = $this->determineCategory($relativePath, $filename);

                // Auto-generate title & alt text from filename
                $cleanName = pathinfo($filename, PATHINFO_FILENAME);
                $humanTitle = Str::headline(str_replace(['-', '_'], ' ', $cleanName));

                // Find existing record by content hash first, then relative path, clean public url, or filename
                $existing = null;
                if ($fileHash) {
                    $existing = MediaAsset::where('file_hash', $fileHash)->first();
                }
                if (! $existing) {
                    $existing = MediaAsset::where('file_path', $relativePath)
                        ->orWhere('public_url', $publicUrl)
                        ->orWhere('filename', $filename)
                        ->first();
                }

                if (! $existing) {
                    $asset = MediaAsset::create([
                        'uuid' => (string) Str::uuid(),
                        'filename' => $filename,
                        'original_name' => $filename,
                        'disk' => 'direct_public',
                        'file_path' => $relativePath,
                        'public_url' => $publicUrl,
                        'media_type' => $mediaType,
                        'mime_type' => $mimeType,
                        'extension' => $ext,
                        'file_hash' => $fileHash,
                        'file_size_bytes' => $sizeBytes,
                        'width' => $width,
                        'height' => $height,
                        'title' => $humanTitle,
                        'alt_text' => $humanTitle . ' - Dunes Discovery Dubai',
                        'category' => $category,
                        'folder' => dirname($relativePath),
                    ]);
                    $stats['indexed']++;
                } else {
                    $asset = $existing;
                    $updateData = [
                        'file_size_bytes' => $sizeBytes,
                        'mime_type' => $mimeType,
                        'file_hash' => $fileHash ?: $existing->file_hash,
                        'width' => $width ?: $existing->width,
                        'height' => $height ?: $existing->height,
                    ];
                    // Keep the cleanest, most canonical path (e.g. root images/ over images/blog/)
                    if (empty($existing->file_path) || (str_contains($existing->file_path, '/blog/') && ! str_contains($relativePath, '/blog/'))) {
                        $updateData['filename'] = $filename;
                        $updateData['file_path'] = $relativePath;
                        $updateData['public_url'] = $publicUrl;
                    }
                    $asset->update($updateData);
                    $stats['updated']++;
                }

                // Resolve cross-referenced usages across tours, blogs, reviews, settings
                $usages = $this->resolveAssetUsages($asset);
                if (count($usages) > 0) {
                    $stats['in_use']++;
                }
            }
        }

        return $stats;
    }

    /**
     * Determine category based on file path and filename.
     */
    protected function determineCategory(string $relativePath, string $filename): string
    {
        $pathLower = strtolower($relativePath);
        $fileLower = strtolower($filename);

        if (str_contains($pathLower, 'videos/reviews') || str_contains($pathLower, 'video-posters') || str_contains($pathLower, 'reviews')) {
            return 'reviews';
        }
        if (str_contains($pathLower, 'blogs') || str_starts_with($fileLower, 'blog-')) {
            return 'blogs';
        }
        if (str_contains($pathLower, 'tours') || str_contains($fileLower, 'safari') || str_contains($fileLower, 'tour') || str_contains($fileLower, 'buggy') || str_contains($fileLower, 'quad')) {
            return 'tours';
        }
        if (str_contains($fileLower, 'banner') || str_contains($fileLower, 'hero') || str_contains($fileLower, 'promo')) {
            return 'banners';
        }
        if (str_contains($fileLower, 'logo') || str_contains($fileLower, 'favicon') || str_contains($fileLower, 'og_image')) {
            return 'site';
        }

        return 'general';
    }

    /**
     * Resolve all places where a media asset is actively used.
     */
    public function resolveAssetUsages(MediaAsset $asset): array
    {
        $usages = [];
        $filename = $asset->filename;
        $path = $asset->file_path;
        $url = $asset->clean_public_url;

        try {
            // 1. Tours
            $tours = Tour::select('id', 'name', 'slug', 'hero_image', 'thumb_image', 'og_image', 'gallery_images')->get();
            foreach ($tours as $tour) {
                $tourName = is_array($tour->name) ? ($tour->name['en'] ?? reset($tour->name)) : $tour->name;
                $editUrl = route('admin.tours.edit', $tour->id);

                if ($this->stringMatchesAsset($tour->hero_image, $filename, $path, $url)) {
                    $usages[] = [
                        'type' => 'Tour',
                        'id' => $tour->id,
                        'name' => $tourName,
                        'field' => 'Hero Image',
                        'edit_url' => $editUrl,
                    ];
                }
                if ($this->stringMatchesAsset($tour->thumb_image, $filename, $path, $url)) {
                    $usages[] = [
                        'type' => 'Tour',
                        'id' => $tour->id,
                        'name' => $tourName,
                        'field' => 'Thumbnail',
                        'edit_url' => $editUrl,
                    ];
                }
                if ($this->stringMatchesAsset($tour->og_image, $filename, $path, $url)) {
                    $usages[] = [
                        'type' => 'Tour',
                        'id' => $tour->id,
                        'name' => $tourName,
                        'field' => 'OG Social Image',
                        'edit_url' => $editUrl,
                    ];
                }
                if (! empty($tour->gallery_images)) {
                    $gallery = is_array($tour->gallery_images) ? $tour->gallery_images : json_decode($tour->gallery_images, true);
                    if (is_array($gallery)) {
                        foreach ($gallery as $gItem) {
                            if ($this->stringMatchesAsset($gItem, $filename, $path, $url)) {
                                $usages[] = [
                                    'type' => 'Tour',
                                    'id' => $tour->id,
                                    'name' => $tourName,
                                    'field' => 'Gallery Image',
                                    'edit_url' => $editUrl,
                                ];
                                break;
                            }
                        }
                    }
                }
            }

            // 2. Blog Posts
            $posts = BlogPost::select('id', 'title', 'slug', 'featured_image', 'og_image', 'content')->get();
            foreach ($posts as $post) {
                $postTitle = is_array($post->title) ? ($post->title['en'] ?? reset($post->title)) : $post->title;
                $editUrl = route('admin.blogs.edit', $post->id);

                if ($this->stringMatchesAsset($post->featured_image, $filename, $path, $url)) {
                    $usages[] = [
                        'type' => 'Blog Post',
                        'id' => $post->id,
                        'name' => $postTitle,
                        'field' => 'Featured Image',
                        'edit_url' => $editUrl,
                    ];
                }
                if ($this->stringMatchesAsset($post->og_image, $filename, $path, $url)) {
                    $usages[] = [
                        'type' => 'Blog Post',
                        'id' => $post->id,
                        'name' => $postTitle,
                        'field' => 'OG Social Image',
                        'edit_url' => $editUrl,
                    ];
                }
                $contentStr = is_array($post->content) ? json_encode($post->content) : (string) $post->content;
                if (str_contains($contentStr, $filename) || str_contains($contentStr, $path)) {
                    $usages[] = [
                        'type' => 'Blog Post',
                        'id' => $post->id,
                        'name' => $postTitle,
                        'field' => 'Article Content Body',
                        'edit_url' => $editUrl,
                    ];
                }
            }

            // 3. Reviews
            $reviews = Review::select('id', 'guest_name', 'media_url', 'thumbnail_url', 'photos')->get();
            foreach ($reviews as $rev) {
                if ($this->stringMatchesAsset($rev->media_url, $filename, $path, $url) || $this->stringMatchesAsset($rev->thumbnail_url, $filename, $path, $url)) {
                    $usages[] = [
                        'type' => 'Review',
                        'id' => $rev->id,
                        'name' => 'Review from ' . ($rev->guest_name ?? 'Guest'),
                        'field' => 'Review Media',
                        'edit_url' => route('admin.reviews.index'),
                    ];
                }
                if (! empty($rev->photos)) {
                    $photos = is_array($rev->photos) ? $rev->photos : json_decode($rev->photos, true);
                    if (is_array($photos)) {
                        foreach ($photos as $photo) {
                            if ($this->stringMatchesAsset($photo, $filename, $path, $url)) {
                                $usages[] = [
                                    'type' => 'Review',
                                    'id' => $rev->id,
                                    'name' => 'Review from ' . ($rev->guest_name ?? 'Guest'),
                                    'field' => 'Review Photo',
                                    'edit_url' => route('admin.reviews.index'),
                                ];
                                break;
                            }
                        }
                    }
                }
            }

            // 4. Settings
            $settings = Setting::select('id', 'setting_key', 'setting_value')->get();
            foreach ($settings as $setting) {
                if (! empty($setting->setting_value) && (str_contains($setting->setting_value, $filename) || str_contains($setting->setting_value, $path))) {
                    $usages[] = [
                        'type' => 'Site Setting',
                        'id' => $setting->id,
                        'name' => $setting->setting_key,
                        'field' => 'Configuration Value',
                        'edit_url' => route('admin.settings.index'),
                    ];
                }
            }

            // 5. Vehicles & Drivers
            $vehicles = Vehicle::select('id', 'name', 'image')->get();
            foreach ($vehicles as $v) {
                if ($this->stringMatchesAsset($v->image, $filename, $path, $url)) {
                    $usages[] = [
                        'type' => 'Fleet Vehicle',
                        'id' => $v->id,
                        'name' => $v->name,
                        'field' => 'Vehicle Image',
                        'edit_url' => route('admin.vehicles.edit', $v->id),
                    ];
                }
            }

            $drivers = Driver::select('id', 'name', 'avatar')->get();
            foreach ($drivers as $d) {
                if ($this->stringMatchesAsset($d->avatar, $filename, $path, $url)) {
                    $usages[] = [
                        'type' => 'Safari Driver',
                        'id' => $d->id,
                        'name' => $d->name,
                        'field' => 'Driver Avatar',
                        'edit_url' => route('admin.drivers.edit', $d->id),
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning("MediaManagerService: usage resolution partial error for {$asset->id}: " . $e->getMessage());
        }

        // Save updated usage
        $asset->usage_count = count($usages);
        $asset->usage_locations = $usages;
        $asset->save();

        return $usages;
    }

    /**
     * Check if a field value matches an asset's filename, path, or URL.
     */
    protected function stringMatchesAsset(?string $fieldValue, string $filename, string $path, string $url): bool
    {
        if (empty($fieldValue)) {
            return false;
        }

        $cleanField = ltrim(trim($fieldValue), '/');
        $cleanPath = ltrim(trim($path), '/');
        $cleanUrl = ltrim(trim($url), '/');

        return $cleanField === $cleanPath
            || $cleanField === $cleanUrl
            || $cleanField === $filename
            || str_ends_with($cleanField, '/' . $filename)
            || str_contains($cleanField, $path);
    }

    /**
     * Upload one or multiple files and register in media library.
     */
    public function upload(UploadedFile $file, array $attributes = [], ?int $userId = null): MediaAsset
    {
        $origName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension());
        $cleanBase = Str::slug(pathinfo($origName, PATHINFO_FILENAME));
        if (empty($cleanBase)) {
            $cleanBase = 'media-asset';
        }

        $mediaType = in_array($ext, $this->videoExtensions) ? 'video' : 'image';
        $subfolder = $mediaType === 'video' ? 'videos/uploads' : 'images/uploads';
        $targetDir = public_path($subfolder);

        if (! File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        // Unique filename
        $uniqueFilename = $cleanBase . '-' . Str::random(6) . '.' . $ext;
        $counter = 1;
        while (File::exists($targetDir . DIRECTORY_SEPARATOR . $uniqueFilename)) {
            $uniqueFilename = $cleanBase . '-' . Str::random(6) . '-' . $counter . '.' . $ext;
            $counter++;
        }

        // Move to public destination
        $file->move($targetDir, $uniqueFilename);
        $fullPath = $targetDir . DIRECTORY_SEPARATOR . $uniqueFilename;
        $relativePath = $subfolder . '/' . $uniqueFilename;
        $publicUrl = '/' . $relativePath;
        $sizeBytes = filesize($fullPath);
        $fileHash = @hash_file('sha256', $fullPath) ?: null;
        $mimeType = File::mimeType($fullPath) ?: ($mediaType === 'video' ? 'video/' . $ext : 'image/' . $ext);

        // Dimensions for images
        $width = null;
        $height = null;
        if ($mediaType === 'image' && in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'avif', 'gif'])) {
            $imageInfo = @getimagesize($fullPath);
            if ($imageInfo) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
            }
        }

        $humanTitle = $attributes['title'] ?? Str::headline(str_replace(['-', '_'], ' ', $cleanBase));
        $altText = $attributes['alt_text'] ?? ($humanTitle . ' - Dunes Discovery Dubai');
        $category = $attributes['category'] ?? $this->determineCategory($relativePath, $uniqueFilename);

        $asset = MediaAsset::create([
            'uuid' => (string) Str::uuid(),
            'filename' => $uniqueFilename,
            'original_name' => $origName,
            'disk' => 'direct_public',
            'file_path' => $relativePath,
            'public_url' => $publicUrl,
            'media_type' => $mediaType,
            'mime_type' => $mimeType,
            'extension' => $ext,
            'file_hash' => $fileHash,
            'file_size_bytes' => $sizeBytes,
            'width' => $width,
            'height' => $height,
            'title' => $humanTitle,
            'alt_text' => $altText,
            'caption' => $attributes['caption'] ?? null,
            'category' => $category,
            'folder' => $subfolder,
            'uploaded_by' => $userId,
        ]);

        return $asset;
    }

    /**
     * Replace an existing media asset's file on disk while keeping the exact same URL.
     */
    public function replace(MediaAsset $asset, UploadedFile $newFile): MediaAsset
    {
        $targetFullPath = public_path($asset->file_path);
        $targetDir = dirname($targetFullPath);

        if (! File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        // Backup existing file temporarily
        $backupPath = $targetFullPath . '.bak';
        if (File::exists($targetFullPath)) {
            File::copy($targetFullPath, $backupPath);
        }

        try {
            // Overwrite target file
            $newFile->move($targetDir, basename($targetFullPath));

            // Update asset metadata
            $sizeBytes = filesize($targetFullPath);
            $mimeType = File::mimeType($targetFullPath) ?: $asset->mime_type;

            $width = null;
            $height = null;
            if ($asset->media_type === 'image') {
                $imageInfo = @getimagesize($targetFullPath);
                if ($imageInfo) {
                    $width = $imageInfo[0];
                    $height = $imageInfo[1];
                }
            }

            $asset->update([
                'file_size_bytes' => $sizeBytes,
                'mime_type' => $mimeType,
                'width' => $width ?: $asset->width,
                'height' => $height ?: $asset->height,
            ]);

            // Remove backup on success
            if (File::exists($backupPath)) {
                File::delete($backupPath);
            }

            // Flush relevant sitemap & image caches
            Cache::forget('sitemap_images_xml');

            return $asset;
        } catch (\Throwable $e) {
            // Restore backup on failure
            if (File::exists($backupPath)) {
                File::move($backupPath, $targetFullPath);
            }
            throw $e;
        }
    }

    /**
     * Safely delete an asset with in-use check.
     */
    public function deleteAsset(MediaAsset $asset, bool $force = false): array
    {
        // Re-check usages to be sure
        $usages = $this->resolveAssetUsages($asset);

        if (count($usages) > 0 && ! $force) {
            return [
                'success' => false,
                'in_use' => true,
                'message' => 'Asset cannot be deleted because it is actively in use.',
                'usages' => $usages,
            ];
        }

        // Remove from disk if exists
        $targetFullPath = public_path($asset->file_path);
        if (File::exists($targetFullPath)) {
            @unlink($targetFullPath);
        }

        // Delete database record
        $asset->delete();
        Cache::forget('sitemap_images_xml');

        return [
            'success' => true,
            'message' => 'Media asset deleted successfully.',
        ];
    }

    /**
     * Refresh usage counts across all media assets.
     */
    public function refreshAllUsages(): int
    {
        $assets = MediaAsset::all();
        $count = 0;
        foreach ($assets as $asset) {
            $this->resolveAssetUsages($asset);
            $count++;
        }

        return $count;
    }

    /**
     * Deduplicate existing media asset rows by content hash and filename.
     * Merges usage locations into the canonical record and safely removes redundant entries.
     */
    public function deduplicateExistingAssets(): array
    {
        $report = ['merged' => 0, 'deleted' => 0];

        // 1. Calculate file_hash for existing records if missing
        $allAssets = MediaAsset::all();
        foreach ($allAssets as $asset) {
            if (empty($asset->file_hash)) {
                $candidates = [
                    public_path($asset->file_path),
                    public_path(ltrim($asset->public_url, '/')),
                    base_path('../public_html/' . $asset->file_path),
                    base_path('../public_html/' . ltrim($asset->public_url, '/')),
                ];
                $found = null;
                foreach ($candidates as $cand) {
                    if (File::exists($cand)) {
                        $found = $cand;
                        break;
                    }
                }
                if ($found) {
                    $asset->file_hash = @hash_file('sha256', $found) ?: null;
                    $asset->saveQuietly();
                }
            }
        }

        // 2. Group by file_hash (where present)
        $groupedByHash = MediaAsset::whereNotNull('file_hash')
            ->get()
            ->groupBy('file_hash');

        foreach ($groupedByHash as $hash => $records) {
            if ($records->count() <= 1) {
                continue;
            }

            // Select canonical: prioritize higher usage count, then root images/ folder over blog/
            $canonical = $records->sortByDesc(function ($item) {
                $score = ($item->usage_count * 100);
                if (! str_contains($item->file_path, '/blog/')) {
                    $score += 10;
                }
                return $score;
            })->first();

            foreach ($records as $duplicate) {
                if ($duplicate->id === $canonical->id) {
                    continue;
                }

                // Merge usage locations
                $cUsages = is_array($canonical->usage_locations) ? $canonical->usage_locations : [];
                $dUsages = is_array($duplicate->usage_locations) ? $duplicate->usage_locations : [];
                $merged = array_merge($cUsages, $dUsages);
                $unique = [];
                foreach ($merged as $u) {
                    $k = ($u['type'] ?? '') . '-' . ($u['id'] ?? '') . '-' . ($u['field'] ?? '');
                    $unique[$k] = $u;
                }
                $canonical->usage_locations = array_values($unique);
                $canonical->usage_count = count($unique);
                $canonical->saveQuietly();

                $duplicate->forceDelete();
                $report['deleted']++;
                $report['merged']++;
            }
        }

        // 3. Deduplicate by exact filename for any rows without hash
        $groupedByName = MediaAsset::all()->groupBy('filename');
        foreach ($groupedByName as $filename => $records) {
            if ($records->count() <= 1) {
                continue;
            }

            $canonical = $records->sortByDesc(function ($item) {
                $score = ($item->usage_count * 100);
                if (! str_contains($item->file_path, '/blog/')) {
                    $score += 10;
                }
                return $score;
            })->first();

            foreach ($records as $duplicate) {
                if ($duplicate->id === $canonical->id) {
                    continue;
                }

                $cUsages = is_array($canonical->usage_locations) ? $canonical->usage_locations : [];
                $dUsages = is_array($duplicate->usage_locations) ? $duplicate->usage_locations : [];
                $merged = array_merge($cUsages, $dUsages);
                $unique = [];
                foreach ($merged as $u) {
                    $k = ($u['type'] ?? '') . '-' . ($u['id'] ?? '') . '-' . ($u['field'] ?? '');
                    $unique[$k] = $u;
                }
                $canonical->usage_locations = array_values($unique);
                $canonical->usage_count = count($unique);
                $canonical->saveQuietly();

                $duplicate->forceDelete();
                $report['deleted']++;
                $report['merged']++;
            }
        }

        return $report;
    }

    /**
     * Index a single existing or newly saved file into MediaAsset library.
     */
    public function scanAndSyncFile(string $fullPath, string $category = 'general'): ?MediaAsset
    {
        if (! File::exists($fullPath)) {
            return null;
        }

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        if (! in_array($ext, array_merge($this->imageExtensions, $this->videoExtensions))) {
            return null;
        }

        $relativePath = $this->normalizeRelativePath($fullPath);
        $publicUrl = '/' . $relativePath;
        $filename = basename($fullPath);
        $sizeBytes = filesize($fullPath);
        $fileHash = @hash_file('sha256', $fullPath) ?: null;

        $mediaType = in_array($ext, $this->videoExtensions) ? 'video' : 'image';
        $mimeType = File::mimeType($fullPath) ?: ($mediaType === 'video' ? 'video/' . $ext : 'image/' . $ext);

        $width = null;
        $height = null;
        if ($mediaType === 'image' && in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'avif', 'gif'])) {
            $imageInfo = @getimagesize($fullPath);
            if ($imageInfo) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
            }
        }

        $existing = null;
        if ($fileHash) {
            $existing = MediaAsset::where('file_hash', $fileHash)->first();
        }
        if (! $existing) {
            $existing = MediaAsset::where('filename', $filename)
                ->orWhere('file_path', $relativePath)
                ->orWhere('public_url', $publicUrl)
                ->first();
        }

        $cleanName = pathinfo($filename, PATHINFO_FILENAME);
        $humanTitle = Str::headline(str_replace(['-', '_'], ' ', $cleanName));

        if (! $existing) {
            $asset = MediaAsset::create([
                'uuid' => (string) Str::uuid(),
                'filename' => $filename,
                'original_name' => $filename,
                'disk' => 'direct_public',
                'file_path' => $relativePath,
                'public_url' => $publicUrl,
                'media_type' => $mediaType,
                'mime_type' => $mimeType,
                'extension' => $ext,
                'file_hash' => $fileHash,
                'file_size_bytes' => $sizeBytes,
                'width' => $width,
                'height' => $height,
                'title' => $humanTitle,
                'alt_text' => $humanTitle . ' - Dunes Discovery Dubai',
                'category' => $category,
                'folder' => dirname($relativePath),
            ]);
        } else {
            $asset = $existing;
            $asset->update([
                'file_size_bytes' => $sizeBytes,
                'mime_type' => $mimeType,
                'file_hash' => $fileHash ?: $existing->file_hash,
                'width' => $width ?: $existing->width,
                'height' => $height ?: $existing->height,
            ]);
        }

        $this->resolveAssetUsages($asset);

        return $asset;
    }
}
