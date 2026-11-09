<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\DuplicateMediaException;
use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use App\Services\ImageOptimizationService;
use App\Services\MediaManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AdminMediaController extends Controller
{
    protected MediaManagerService $mediaService;
    protected ImageOptimizationService $optService;

    public function __construct(MediaManagerService $mediaService, ImageOptimizationService $optService)
    {
        $this->mediaService = $mediaService;
        $this->optService = $optService;
    }

    /**
     * Display the Media Library index.
     */
    public function index(Request $request): View
    {
        // Auto-deduplicate format variants (e.g. webp + jpg) into single canonical cards
        try {
            $this->mediaService->deduplicateFormatVariants();
        } catch (\Throwable $e) {
            // Silently continue if DB lock
        }

        $query = MediaAsset::query();

        // 1. Filter by Search
        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        // 2. Filter by Media Type (All, Image, Video)
        if ($request->filled('type') && $request->input('type') !== 'all') {
            $query->where('media_type', $request->input('type'));
        }

        // 3. Filter by Category
        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category', $request->input('category'));
        }

        // 4. Filter by Usage
        if ($request->input('usage') === 'in_use') {
            $query->inUse();
        } elseif ($request->input('usage') === 'unused') {
            $query->unused();
        }

        // 5. Sorting
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'size_desc':
                $query->orderBy('file_size_bytes', 'desc');
                break;
            case 'size_asc':
                $query->orderBy('file_size_bytes', 'asc');
                break;
            case 'used_desc':
                $query->orderBy('usage_count', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('filename', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $perPage = (int) $request->input('per_page', 36);
        $perPage = in_array($perPage, [18, 36, 72, 120]) ? $perPage : 36;
        $assets = $query->paginate($perPage)->withQueryString();

        // High-level KPI aggregations
        $kpis = [
            'total_assets' => MediaAsset::count(),
            'total_bytes' => (int) MediaAsset::sum('file_size_bytes'),
            'images_count' => MediaAsset::images()->count(),
            'images_bytes' => (int) MediaAsset::images()->sum('file_size_bytes'),
            'videos_count' => MediaAsset::videos()->count(),
            'videos_bytes' => (int) MediaAsset::videos()->sum('file_size_bytes'),
            'in_use_count' => MediaAsset::inUse()->count(),
            'unused_count' => MediaAsset::unused()->count(),
        ];

        // Format total storage
        $kpis['formatted_total_size'] = $this->formatBytes($kpis['total_bytes']);
        $kpis['formatted_images_size'] = $this->formatBytes($kpis['images_bytes']);
        $kpis['formatted_videos_size'] = $this->formatBytes($kpis['videos_bytes']);
        $kpis['in_use_pct'] = $kpis['total_assets'] > 0 ? round(($kpis['in_use_count'] / $kpis['total_assets']) * 100) : 0;

        $categories = [
            'all' => 'All Categories',
            'tours' => 'Tours & Experiences',
            'blogs' => 'Blog & Articles',
            'reviews' => 'Customer Reviews & Reels',
            'banners' => 'Hero & Promo Banners',
            'site' => 'Site Assets (Logo, OG)',
            'general' => 'General Uploads',
        ];

        $viewMode = $request->input('view', 'grid');

        return view('admin.media.index', compact('assets', 'kpis', 'categories', 'viewMode'));
    }

    /**
     * Get paginated media assets for the Universal Media Picker modal (JSON).
     */
    public function pickerItems(Request $request): JsonResponse
    {
        $query = MediaAsset::query();

        // 1. Filter by Search
        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        // 2. Filter by Media Type (all, image, video)
        if ($request->filled('type') && $request->input('type') !== 'all') {
            $query->where('media_type', $request->input('type'));
        }

        // 3. Filter by Category
        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category', $request->input('category'));
        }

        // 4. Default sort by newest
        $query->orderBy('created_at', 'desc');

        $perPage = min(60, max(12, (int) $request->input('per_page', 36)));
        $paginator = $query->paginate($perPage);

        $items = $paginator->getCollection()->map(function (MediaAsset $asset) {
            return [
                'id' => $asset->id,
                'uuid' => $asset->uuid,
                'filename' => $asset->filename,
                'original_name' => $asset->original_name ?: $asset->filename,
                'file_path' => $asset->file_path,
                'public_url' => $asset->clean_public_url,
                'full_url' => $asset->url,
                'poster_url' => $asset->poster_url,
                'media_type' => $asset->media_type,
                'is_image' => $asset->is_image,
                'is_video' => $asset->is_video,
                'extension' => strtoupper($asset->extension ?: ''),
                'formatted_size' => $asset->formatted_file_size,
                'file_size_bytes' => $asset->file_size_bytes,
                'dimensions' => $asset->dimensions,
                'width' => $asset->width,
                'height' => $asset->height,
                'title' => $asset->title,
                'alt_text' => $asset->alt_text,
                'category' => $asset->category,
                'usage_count' => (int) $asset->usage_count,
                'format_variants' => $asset->format_variants,
                'created_at_human' => $asset->created_at ? $asset->created_at->format('M d, Y') : '',
            ];
        });

        return response()->json([
            'success' => true,
            'assets' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ]);
    }

    /**
     * Upload single or multiple files via dropzone AJAX with duplicate detection.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:jpeg,png,jpg,webp,avif,svg,gif,bmp,mp4,webm,mov,ogg,m4v|max:51200', // max 50MB
            'file' => 'nullable|file|mimes:jpeg,png,jpg,webp,avif,svg,gif,bmp,mp4,webm,mov,ogg,m4v|max:51200',
            'category' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
            'force_upload' => 'nullable',
            'convert_format' => 'nullable|string|in:webp,avif,original',
            'convert_quality' => 'nullable|string|max:20',
        ]);

        $forceUpload = $request->boolean('force_upload', false) || $request->input('force_upload') === '1';
        $convertFormat = strtolower((string) $request->input('convert_format', ''));
        $convertQuality = $request->input('convert_quality', 'lossless');

        $uploadedFiles = [];
        if ($request->hasFile('files')) {
            $uploadedFiles = $request->file('files');
        } elseif ($request->hasFile('file')) {
            $uploadedFiles = [$request->file('file')];
        }

        if (empty($uploadedFiles)) {
            return response()->json([
                'success' => false,
                'message' => 'No files were provided for upload.',
            ], 422);
        }

        // 1. High-Quality Lossless / Near-Lossless Optimization & Conversion (WebP or AVIF)
        $conversionStats = [];
        if (in_array($convertFormat, ['webp', 'avif'], true)) {
            $processedFiles = [];
            foreach ($uploadedFiles as $file) {
                if ($this->optService->needsConversion($file)) {
                    try {
                        $converted = $this->optService->convert($file, $convertFormat, ['quality' => $convertQuality]);
                        $processedFiles[] = $converted['file'];
                        $conversionStats[] = [
                            'original_name' => $converted['original_filename'],
                            'converted_name' => $converted['converted_filename'],
                            'original_format' => $converted['original_format'],
                            'original_size' => $converted['original_size'],
                            'converted_size' => $converted['converted_size'],
                            'savings_bytes' => $converted['savings_bytes'],
                            'savings_percent' => $converted['savings_percent'],
                            'target_format' => $converted['target_format'],
                        ];
                    } catch (\Throwable $e) {
                        Log::warning('Image optimization failed for ' . $file->getClientOriginalName() . ': ' . $e->getMessage());
                        $processedFiles[] = $file;
                    }
                } else {
                    $processedFiles[] = $file;
                }
            }
            $uploadedFiles = $processedFiles;
        }

        // Duplicate Pre-Check if not force uploading
        if (! $forceUpload) {
            $detectedDuplicates = [];
            foreach ($uploadedFiles as $file) {
                $dup = $this->mediaService->findDuplicate($file);
                if ($dup) {
                    $detectedDuplicates[] = [
                        'uploaded_name' => $file->getClientOriginalName(),
                        'existing' => [
                            'id' => $dup->id,
                            'uuid' => $dup->uuid,
                            'filename' => $dup->filename,
                            'original_name' => $dup->original_name,
                            'public_url' => $dup->public_url,
                            'url' => $dup->url,
                            'thumbnail_url' => $dup->url,
                            'media_type' => $dup->media_type,
                            'formatted_size' => $dup->formatted_file_size,
                            'dimensions' => $dup->dimensions,
                            'category' => $dup->category,
                            'usage_count' => (int) $dup->usage_count,
                            'created_at_human' => $dup->created_at ? $dup->created_at->format('M d, Y') : '',
                        ],
                    ];
                }
            }

            if (! empty($detectedDuplicates)) {
                $firstDup = $detectedDuplicates[0]['existing'];
                return response()->json([
                    'success' => false,
                    'is_duplicate' => true,
                    'has_duplicates' => count($detectedDuplicates) > 1,
                    'duplicate' => $firstDup,
                    'duplicates' => $detectedDuplicates,
                    'message' => count($detectedDuplicates) === 1
                        ? "Duplicate detected: '{$detectedDuplicates[0]['uploaded_name']}' already exists as '{$firstDup['filename']}' in the Media Library."
                        : count($detectedDuplicates) . " uploaded file(s) already exist in the Media Library.",
                ], 409);
            }
        }

        $userId = auth()->id();
        $createdAssets = [];
        $errors = [];

        foreach ($uploadedFiles as $file) {
            try {
                $asset = $this->mediaService->upload($file, [
                    'category' => $request->input('category', 'general'),
                    'title' => $request->input('title'),
                    'alt_text' => $request->input('alt_text'),
                ], $userId, $forceUpload);

                $createdAssets[] = [
                    'id' => $asset->id,
                    'uuid' => $asset->uuid,
                    'filename' => $asset->filename,
                    'public_url' => $asset->public_url,
                    'url' => $asset->url,
                    'media_type' => $asset->media_type,
                    'formatted_size' => $asset->formatted_file_size,
                    'dimensions' => $asset->dimensions,
                ];
            } catch (DuplicateMediaException $e) {
                $dup = $e->getDuplicateAsset();
                return response()->json([
                    'success' => false,
                    'is_duplicate' => true,
                    'duplicate' => [
                        'id' => $dup->id,
                        'uuid' => $dup->uuid,
                        'filename' => $dup->filename,
                        'original_name' => $dup->original_name,
                        'public_url' => $dup->public_url,
                        'url' => $dup->url,
                        'thumbnail_url' => $dup->url,
                        'media_type' => $dup->media_type,
                        'formatted_size' => $dup->formatted_file_size,
                        'dimensions' => $dup->dimensions,
                        'category' => $dup->category,
                        'usage_count' => (int) $dup->usage_count,
                        'created_at_human' => $dup->created_at ? $dup->created_at->format('M d, Y') : '',
                    ],
                    'message' => $e->getMessage(),
                ], 409);
            } catch (\Throwable $e) {
                Log::error('Media Upload Error: ' . $e->getMessage());
                $errors[] = $file->getClientOriginalName() . ': ' . $e->getMessage();
            }
        }

        $message = count($createdAssets) . ' file(s) successfully uploaded.';
        if (! empty($conversionStats)) {
            $message .= ' (' . count($conversionStats) . ' image(s) optimized to ' . strtoupper($convertFormat) . ')';
        }

        return response()->json([
            'success' => count($createdAssets) > 0,
            'uploaded' => $createdAssets,
            'errors' => $errors,
            'conversions' => $conversionStats,
            'message' => $message,
        ]);
    }

    /**
     * Inspect file(s) for modern image optimization and format conversion opportunities.
     */
    public function inspectOptimization(Request $request): JsonResponse
    {
        $files = [];
        if ($request->hasFile('files')) {
            $files = $request->file('files');
        } elseif ($request->hasFile('file')) {
            $files = [$request->file('file')];
        }

        $supported = $this->optService->getSupportedFormats();
        $inspected = [];

        foreach ($files as $file) {
            $inspected[] = [
                'name' => $file->getClientOriginalName(),
                'size' => (int) $file->getSize(),
                'is_optimized' => $this->optService->isOptimized($file),
                'needs_conversion' => $this->optService->needsConversion($file),
                'is_convertible' => $this->optService->isConvertibleImage($file),
            ];
        }

        return response()->json([
            'success' => true,
            'supported_formats' => $supported,
            'files' => $inspected,
            'has_convertible' => collect($inspected)->contains('needs_conversion', true),
        ]);
    }

    /**
     * Check a file for duplicate before uploading.
     */
    public function checkDuplicate(Request $request): JsonResponse
    {
        if (! $request->hasFile('file')) {
            return response()->json(['success' => false, 'message' => 'No file provided.'], 422);
        }

        $duplicate = $this->mediaService->findDuplicate($request->file('file'));
        if ($duplicate) {
            return response()->json([
                'success' => true,
                'is_duplicate' => true,
                'duplicate' => [
                    'id' => $duplicate->id,
                    'uuid' => $duplicate->uuid,
                    'filename' => $duplicate->filename,
                    'original_name' => $duplicate->original_name,
                    'public_url' => $duplicate->public_url,
                    'url' => $duplicate->url,
                    'thumbnail_url' => $duplicate->url,
                    'media_type' => $duplicate->media_type,
                    'formatted_size' => $duplicate->formatted_file_size,
                    'dimensions' => $duplicate->dimensions,
                    'category' => $duplicate->category,
                    'usage_count' => (int) $duplicate->usage_count,
                    'created_at_human' => $duplicate->created_at ? $duplicate->created_at->format('M d, Y') : '',
                ],
                'message' => "An identical file already exists in the library as '{$duplicate->filename}'.",
            ]);
        }

        return response()->json([
            'success' => true,
            'is_duplicate' => false,
        ]);
    }

    /**
     * Get asset details & fresh usage references as JSON.
     */
    public function details(int $id): JsonResponse
    {
        $asset = MediaAsset::with('uploader:id,name')->findOrFail($id);

        // Refresh usage locations dynamically
        $usages = $this->mediaService->resolveAssetUsages($asset);

        return response()->json([
            'success' => true,
            'asset' => [
                'id' => $asset->id,
                'uuid' => $asset->uuid,
                'filename' => $asset->filename,
                'original_name' => $asset->original_name,
                'file_path' => $asset->file_path,
                'public_url' => $asset->clean_public_url,
                'poster_url' => $asset->poster_url,
                'full_url' => $asset->url,
                'media_type' => $asset->media_type,
                'is_video' => $asset->is_video,
                'is_image' => $asset->is_image,
                'mime_type' => $asset->mime_type,
                'extension' => strtoupper($asset->extension),
                'file_size_bytes' => $asset->file_size_bytes,
                'formatted_size' => $asset->formatted_file_size,
                'width' => $asset->width,
                'height' => $asset->height,
                'dimensions' => $asset->dimensions,
                'title' => $asset->title,
                'alt_text' => $asset->alt_text,
                'caption' => $asset->caption,
                'category' => $asset->category,
                'folder' => $asset->folder,
                'usage_count' => count($usages),
                'usage_locations' => $usages,
                'format_variants' => $asset->format_variants,
                'uploaded_by' => $asset->uploader ? $asset->uploader->name : 'System / Auto-Sync',
                'created_at' => $asset->created_at ? $asset->created_at->format('M d, Y h:i A') : 'N/A',
                'updated_at' => $asset->updated_at ? $asset->updated_at->format('M d, Y h:i A') : 'N/A',
            ],
        ]);
    }

    /**
     * Update asset metadata (Title, Alt Text, Caption, Category).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $asset = MediaAsset::findOrFail($id);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:50',
        ]);

        $asset->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Media metadata updated successfully.',
            'asset' => $asset,
        ]);
    }

    /**
     * Replace an existing media file preserving URL.
     */
    public function replace(Request $request, int $id): JsonResponse
    {
        $asset = MediaAsset::findOrFail($id);

        $request->validate([
            'file' => 'required|file|max:51200',
        ]);

        try {
            $updated = $this->mediaService->replace($asset, $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'File successfully replaced on disk. URL preserved.',
                'asset' => [
                    'id' => $updated->id,
                    'formatted_size' => $updated->formatted_file_size,
                    'dimensions' => $updated->dimensions,
                    'mime_type' => $updated->mime_type,
                    'url' => $updated->url,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error("Media replacement failed for asset {$id}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to replace file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a media asset with safe usage checks.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $asset = MediaAsset::findOrFail($id);
        $force = $request->boolean('force');

        $result = $this->mediaService->deleteAsset($asset, $force);

        if (! $result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    /**
     * Trigger manual scan and sync of server files.
     */
    public function syncExisting(): JsonResponse
    {
        try {
            $stats = $this->mediaService->scanAndSyncExistingMedia();

            return response()->json([
                'success' => true,
                'message' => "Sync complete: {$stats['scanned']} scanned, {$stats['indexed']} newly indexed, {$stats['updated']} updated, {$stats['in_use']} active in-use.",
                'stats' => $stats,
            ]);
        } catch (\Throwable $e) {
            Log::error('Media auto-sync failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk actions handler.
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|string|in:delete,set_category,refresh_usage',
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:media_assets,id',
            'category' => 'nullable|string|max:50',
            'force' => 'nullable|boolean',
        ]);

        $action = $request->input('action');
        $ids = $request->input('ids');
        $force = $request->boolean('force');

        if ($action === 'set_category') {
            $cat = $request->input('category', 'general');
            MediaAsset::whereIn('id', $ids)->update(['category' => $cat]);

            return response()->json(['success' => true, 'message' => count($ids) . ' asset(s) moved to ' . $cat . '.']);
        }

        if ($action === 'refresh_usage') {
            $assets = MediaAsset::whereIn('id', $ids)->get();
            foreach ($assets as $asset) {
                $this->mediaService->resolveAssetUsages($asset);
            }

            return response()->json(['success' => true, 'message' => 'Refreshed usages for ' . count($ids) . ' asset(s).']);
        }

        if ($action === 'delete') {
            $deleted = 0;
            $inUseBlocked = [];
            $assets = MediaAsset::whereIn('id', $ids)->get();

            foreach ($assets as $asset) {
                $res = $this->mediaService->deleteAsset($asset, $force);
                if ($res['success']) {
                    $deleted++;
                } else {
                    $inUseBlocked[] = $asset->filename;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Deleted {$deleted} asset(s)." . (count($inUseBlocked) > 0 ? " Blocked " . count($inUseBlocked) . " in-use asset(s)." : ""),
                'blocked' => $inUseBlocked,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action.'], 400);
    }

    /**
     * Format bytes to human string.
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));
        $i = min($i, count($units) - 1);
        $val = round($bytes / pow(1024, $i), 1);

        return $val . ' ' . $units[$i];
    }
}
