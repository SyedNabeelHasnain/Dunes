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
     * Preference ranking for image formats.
     * WebP is highest priority (modern standard, optimal compression), followed by AVIF, PNG, JPG.
     */
    public function formatScore(string $ext): int
    {
        return match (strtolower(trim($ext))) {
            'webp' => 100,
            'avif' => 90,
            'png' => 50,
            'jpg', 'jpeg' => 40,
            'gif' => 30,
            'svg' => 20,
            default => 10,
        };
    }

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

        $imageGroups = [];
        $videoItems = [];

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
                $filename = $file->getFilename();

                if (in_array($ext, $this->videoExtensions)) {
                    $videoItems[] = [
                        'file' => $file,
                        'fullPath' => $fullPath,
                        'relativePath' => $relativePath,
                        'filename' => $filename,
                        'ext' => $ext,
                    ];
                } else {
                    $folder = str_replace('\\', '/', trim(dirname($relativePath), '/. '));
                    $cleanName = strtolower(pathinfo($filename, PATHINFO_FILENAME));
                    $groupKey = $folder . '::' . $cleanName;
                    $imageGroups[$groupKey][] = [
                        'file' => $file,
                        'fullPath' => $fullPath,
                        'relativePath' => $relativePath,
                        'filename' => $filename,
                        'ext' => $ext,
                        'score' => $this->formatScore($ext),
                    ];
                }
            }
        }

        // Process images with format-variant grouping (e.g. collapse .webp and .jpg siblings into 1 canonical card)
        foreach ($imageGroups as $groupKey => $filesInGroup) {
            // Sort by format score descending (webp > avif > png > jpg)
            usort($filesInGroup, fn ($a, $b) => $b['score'] <=> $a['score']);
            $canonicalFile = $filesInGroup[0];
            $variantFiles = array_slice($filesInGroup, 1);

            $fullPath = $canonicalFile['fullPath'];
            $relativePath = $canonicalFile['relativePath'];
            $filename = $canonicalFile['filename'];
            $publicUrl = '/' . $relativePath;
            $ext = $canonicalFile['ext'];
            $sizeBytes = $canonicalFile['file']->getSize();
            $mimeType = File::mimeType($fullPath) ?: ('image/' . $ext);

            $width = null;
            $height = null;
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'avif', 'gif'])) {
                $imageInfo = @getimagesize($fullPath);
                if ($imageInfo) {
                    $width = $imageInfo[0];
                    $height = $imageInfo[1];
                }
            }

            $fileHash = @hash_file('sha256', $fullPath) ?: null;
            $category = $this->determineCategory($relativePath, $filename);
            $cleanName = pathinfo($filename, PATHINFO_FILENAME);
            $humanTitle = Str::headline(str_replace(['-', '_'], ' ', $cleanName));
            $folder = str_replace('\\', '/', trim(dirname($relativePath), '/. '));
            $baseNameLower = strtolower($cleanName);

            // Sibling format variants metadata
            $variantsMeta = [];
            foreach ($variantFiles as $vf) {
                $vExt = $vf['ext'];
                $variantsMeta[$vExt] = [
                    'public_url' => '/' . $vf['relativePath'],
                    'file_path' => $vf['relativePath'],
                    'file_size_bytes' => $vf['file']->getSize(),
                ];
            }

            // Find existing record by content hash first, then folder + base name, relative path, or filename
            $existing = null;
            if ($fileHash) {
                $existing = MediaAsset::where('file_hash', $fileHash)->first();
            }
            if (! $existing) {
                $existing = MediaAsset::where('folder', $folder)
                    ->where('media_type', 'image')
                    ->whereRaw('LOWER(filename) LIKE ?', [$baseNameLower . '.%'])
                    ->first();
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
                    'media_type' => 'image',
                    'mime_type' => $mimeType,
                    'extension' => $ext,
                    'file_hash' => $fileHash,
                    'file_size_bytes' => $sizeBytes,
                    'width' => $width,
                    'height' => $height,
                    'title' => $humanTitle,
                    'alt_text' => $humanTitle . ' - Dunes Discovery Dubai',
                    'category' => $category,
                    'folder' => $folder,
                    'extra_metadata' => ! empty($variantsMeta) ? ['format_variants' => $variantsMeta] : null,
                ]);
                $stats['indexed']++;
            } else {
                $asset = $existing;
                $currentMeta = is_array($existing->extra_metadata) ? $existing->extra_metadata : [];
                $currentVariants = $currentMeta['format_variants'] ?? [];
                $mergedVariants = array_merge($currentVariants, $variantsMeta);
                if (! empty($mergedVariants)) {
                    $currentMeta['format_variants'] = $mergedVariants;
                }

                $asset->update([
                    'filename' => $filename,
                    'file_path' => $relativePath,
                    'public_url' => $publicUrl,
                    'extension' => $ext,
                    'file_size_bytes' => $sizeBytes,
                    'mime_type' => $mimeType,
                    'file_hash' => $fileHash ?: $existing->file_hash,
                    'width' => $width ?: $existing->width,
                    'height' => $height ?: $existing->height,
                    'folder' => $folder,
                    'extra_metadata' => ! empty($currentMeta) ? $currentMeta : null,
                ]);
                $stats['updated']++;
            }

            // Merge usages and delete any legacy duplicate rows with same folder & base name
            $otherDuplicates = MediaAsset::where('folder', $folder)
                ->where('media_type', 'image')
                ->where('id', '!=', $asset->id)
                ->whereRaw('LOWER(filename) LIKE ?', [$baseNameLower . '.%'])
                ->get();

            if ($otherDuplicates->isNotEmpty()) {
                $allUsages = is_array($asset->usage_locations) ? $asset->usage_locations : [];
                foreach ($otherDuplicates as $otherDup) {
                    $otherUsages = is_array($otherDup->usage_locations) ? $otherDup->usage_locations : [];
                    $allUsages = array_merge($allUsages, $otherUsages);
                    $otherDup->forceDelete();
                }
                $uniqueU = [];
                foreach ($allUsages as $u) {
                    $uk = ($u['type'] ?? '') . '-' . ($u['id'] ?? '') . '-' . ($u['field'] ?? '');
                    $uniqueU[$uk] = $u;
                }
                $asset->usage_locations = array_values($uniqueU);
                $asset->usage_count = count($uniqueU);
                $asset->saveQuietly();
            }

            // Resolve cross-referenced usages across tours, blogs, reviews, settings
            $usages = $this->resolveAssetUsages($asset);
            if (count($usages) > 0) {
                $stats['in_use']++;
            }
        }

        // Process videos
        foreach ($videoItems as $item) {
            $fullPath = $item['fullPath'];
            $relativePath = $item['relativePath'];
            $filename = $item['filename'];
            $publicUrl = '/' . $relativePath;
            $ext = $item['ext'];
            $sizeBytes = $item['file']->getSize();
            $mimeType = File::mimeType($fullPath) ?: ('video/' . $ext);
            $fileHash = @hash_file('sha256', $fullPath) ?: null;
            $category = $this->determineCategory($relativePath, $filename);
            $cleanName = pathinfo($filename, PATHINFO_FILENAME);
            $humanTitle = Str::headline(str_replace(['-', '_'], ' ', $cleanName));
            $folder = str_replace('\\', '/', trim(dirname($relativePath), '/. '));

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
                    'media_type' => 'video',
                    'mime_type' => $mimeType,
                    'extension' => $ext,
                    'file_hash' => $fileHash,
                    'file_size_bytes' => $sizeBytes,
                    'title' => $humanTitle,
                    'alt_text' => $humanTitle . ' - Dunes Discovery Dubai',
                    'category' => $category,
                    'folder' => $folder,
                ]);
                $stats['indexed']++;
            } else {
                $asset = $existing;
                $asset->update([
                    'file_size_bytes' => $sizeBytes,
                    'mime_type' => $mimeType,
                    'file_hash' => $fileHash ?: $existing->file_hash,
                    'folder' => $folder,
                ]);
                $stats['updated']++;
            }

            $usages = $this->resolveAssetUsages($asset);
            if (count($usages) > 0) {
                $stats['in_use']++;
            }
        }

        // Run format variants deduplication pass
        $this->deduplicateFormatVariants();

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
            $settingLabels = [
                'site_logo' => 'Primary Brand Logo (Navbar/Schema)',
                'site_logo_white' => 'White Brand Logo (Footer/Email)',
                'site_favicon' => 'Browser Favicon',
                'site_hero_video' => 'Homepage Hero Video Background',
                'site_hero_poster' => 'Homepage Hero Video Poster',
                'seo_default_og_image' => 'Global Social Share (OG) Card',
                'about_story_image' => 'About Us Page Story Image',
            ];
            foreach ($settings as $setting) {
                if (! empty($setting->setting_value) && ($this->stringMatchesAsset($setting->setting_value, $filename, $path, $url) || str_contains($setting->setting_value, $filename) || str_contains($setting->setting_value, $path))) {
                    $key = $setting->setting_key;
                    $fieldLabel = $settingLabels[$key] ?? ('Setting: ' . $key);
                    $usages[] = [
                        'type' => 'Site Setting',
                        'id' => $setting->id,
                        'name' => $fieldLabel,
                        'field' => 'General Settings & Branding',
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

            // 6. Curated Customer Video Reviews (Reels & Tour Highlights)
            $videoReviewsPath = database_path('seeders/data/customer_video_reviews.json');
            if (file_exists($videoReviewsPath)) {
                $videoData = json_decode((string) file_get_contents($videoReviewsPath), true);
                if (is_array($videoData)) {
                    foreach ($videoData as $vItem) {
                        $vFile = $vItem['filename'] ?? '';
                        $vPoster = $vItem['poster_filename'] ?? '';
                        if ($this->stringMatchesAsset($vFile, $filename, $path, $url)) {
                            $usages[] = [
                                'type' => 'Customer Video Reel',
                                'id' => $vItem['id'] ?? 'reel',
                                'name' => $vItem['title'] ?? 'Customer Video Review',
                                'field' => 'Video Review Stream (Home & Tours)',
                                'edit_url' => route('admin.reviews.index'),
                            ];
                        }
                        if ($this->stringMatchesAsset($vPoster, $filename, $path, $url)) {
                            $usages[] = [
                                'type' => 'Customer Video Reel',
                                'id' => $vItem['id'] ?? 'poster',
                                'name' => $vItem['title'] ?? 'Customer Video Review',
                                'field' => 'Video Poster Thumbnail',
                                'edit_url' => route('admin.reviews.index'),
                            ];
                        }
                    }
                }
            }

            // 7. DDT Moments Gallery
            $ddtMomentsPath = database_path('seeders/data/ddt_moments.json');
            if (file_exists($ddtMomentsPath)) {
                $momentsData = json_decode((string) file_get_contents($ddtMomentsPath), true);
                if (is_array($momentsData)) {
                    foreach ($momentsData as $mItem) {
                        $mFile = $mItem['filename'] ?? '';
                        if ($this->stringMatchesAsset($mFile, $filename, $path, $url)) {
                            $usages[] = [
                                'type' => 'Moments Gallery',
                                'id' => $mItem['id'] ?? 'moment',
                                'name' => $mItem['title'] ?? 'DDT Desert Moment',
                                'field' => 'Curated Guest Experience Photo',
                                'edit_url' => route('admin.reviews.index'),
                            ];
                        }
                    }
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

        if ($cleanField === $cleanPath
            || $cleanField === $cleanUrl
            || $cleanField === $filename
            || str_ends_with($cleanField, '/' . $filename)
            || str_contains($cleanField, $path)) {
            return true;
        }

        // Match sibling format variants (e.g. tour has .jpg and canonical asset is .webp, or vice versa)
        $fieldBase = pathinfo($cleanField, PATHINFO_FILENAME);
        $assetBase = pathinfo($filename, PATHINFO_FILENAME);
        if (! empty($fieldBase) && ! empty($assetBase) && strcasecmp($fieldBase, $assetBase) === 0) {
            $fieldDir = str_replace('\\', '/', trim(dirname($cleanField), '/. '));
            $assetDir = str_replace('\\', '/', trim(dirname($cleanPath), '/. '));
            if ($fieldDir === '.' || $assetDir === '.' || $fieldDir === $assetDir || str_contains($cleanField, $assetBase)) {
                return true;
            }
        }

        return false;
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

        // Remove from disk if exists (check standard public_path and Hostinger parent public_html)
        $candidatePaths = [
            public_path($asset->file_path),
            public_path(ltrim($asset->clean_public_url, '/')),
            base_path('../public_html/' . $asset->file_path),
            base_path('../public_html/' . ltrim($asset->clean_public_url, '/')),
        ];
        foreach ($candidatePaths as $cp) {
            if (File::exists($cp)) {
                @unlink($cp);
            }
        }

        // Also clean up any recorded sibling format variants
        $variants = is_array($asset->extra_metadata) ? ($asset->extra_metadata['format_variants'] ?? []) : [];
        if (is_array($variants)) {
            foreach ($variants as $v) {
                $vPath = is_array($v) ? ($v['file_path'] ?? null) : $v;
                if ($vPath) {
                    if (File::exists(public_path($vPath))) {
                        @unlink(public_path($vPath));
                    }
                    if (File::exists(base_path('../public_html/' . $vPath))) {
                        @unlink(base_path('../public_html/' . $vPath));
                    }
                }
            }
        }

        // Also clean up any matching base_name file in the same directory
        $cleanDir = dirname(public_path($asset->file_path));
        $baseName = pathinfo($asset->filename, PATHINFO_FILENAME);
        if (File::isDirectory($cleanDir)) {
            foreach ($this->imageExtensions as $sibExt) {
                $sibFile = $cleanDir . DIRECTORY_SEPARATOR . $baseName . '.' . $sibExt;
                if (File::exists($sibFile)) {
                    @unlink($sibFile);
                }
            }
        }

        // Permanently remove database record
        $asset->forceDelete();
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

        // 4. Deduplicate format variants (e.g. .webp alongside .jpg for the same visual image)
        $variantReport = $this->deduplicateFormatVariants();
        $report['deleted'] += $variantReport['deleted'];
        $report['merged'] += $variantReport['merged'];

        return $report;
    }

    /**
     * Deduplicate format variants of the same image (e.g. foo.webp alongside foo.jpg).
     * The modern format (WebP > AVIF > PNG > JPG) is kept as the canonical MediaAsset,
     * sibling format paths are preserved in extra_metadata['format_variants'],
     * and all usages are consolidated into the canonical record.
     */
    public function deduplicateFormatVariants(): array
    {
        $report = ['merged' => 0, 'deleted' => 0];

        $assets = MediaAsset::where('media_type', 'image')->get();

        // Group by folder and lowercase canonical base name
        $grouped = [];
        foreach ($assets as $asset) {
            $folder = $asset->folder ?: dirname($asset->file_path);
            $folder = str_replace('\\', '/', trim($folder, '/. '));
            $baseName = strtolower(pathinfo($asset->filename, PATHINFO_FILENAME));
            $key = $folder . '::' . $baseName;
            $grouped[$key][] = $asset;
        }

        foreach ($grouped as $key => $records) {
            if (count($records) <= 1) {
                continue;
            }

            // Sort to select canonical:
            // 1. Format priority (webp > avif > png > jpg)
            // 2. If same format, higher usage_count
            usort($records, function ($a, $b) {
                $scoreA = $this->formatScore($a->extension ?: '');
                $scoreB = $this->formatScore($b->extension ?: '');
                if ($scoreA !== $scoreB) {
                    return $scoreB <=> $scoreA;
                }
                if ($a->usage_count !== $b->usage_count) {
                    return $b->usage_count <=> $a->usage_count;
                }
                return $a->id <=> $b->id;
            });

            /** @var MediaAsset $canonical */
            $canonical = $records[0];
            $duplicates = array_slice($records, 1);

            $meta = is_array($canonical->extra_metadata) ? $canonical->extra_metadata : [];
            $variants = $meta['format_variants'] ?? [];
            $cUsages = is_array($canonical->usage_locations) ? $canonical->usage_locations : [];

            foreach ($duplicates as $duplicate) {
                $dUsages = is_array($duplicate->usage_locations) ? $duplicate->usage_locations : [];
                $cUsages = array_merge($cUsages, $dUsages);

                $dupExt = strtolower($duplicate->extension ?: pathinfo($duplicate->filename, PATHINFO_EXTENSION));
                if (! empty($dupExt) && $dupExt !== strtolower($canonical->extension)) {
                    $variants[$dupExt] = [
                        'public_url' => $duplicate->clean_public_url,
                        'file_path' => $duplicate->file_path,
                        'file_size_bytes' => $duplicate->file_size_bytes,
                    ];
                }

                $duplicate->forceDelete();
                $report['deleted']++;
                $report['merged']++;
            }

            // Consolidate unique usages
            $uniqueUsages = [];
            foreach ($cUsages as $u) {
                $uk = ($u['type'] ?? '') . '-' . ($u['id'] ?? '') . '-' . ($u['field'] ?? '');
                $uniqueUsages[$uk] = $u;
            }

            $meta['format_variants'] = $variants;
            $canonical->extra_metadata = $meta;
            $canonical->usage_locations = array_values($uniqueUsages);
            $canonical->usage_count = count($uniqueUsages);
            $canonical->saveQuietly();
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
