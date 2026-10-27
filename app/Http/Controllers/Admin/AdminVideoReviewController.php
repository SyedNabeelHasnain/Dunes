<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Services\ReviewMediaGalleryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminVideoReviewController extends Controller
{
    protected ReviewMediaGalleryService $galleryService;

    public function __construct(ReviewMediaGalleryService $galleryService)
    {
        $this->galleryService = $galleryService;
    }

    /**
     * Path to the customer video reviews data file.
     */
    protected function getJsonPath(): string
    {
        return database_path('seeders/data/customer_video_reviews.json');
    }

    /**
     * Load raw video review records from JSON.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function loadRecords(): array
    {
        $path = $this->getJsonPath();
        if (! File::exists($path)) {
            return [];
        }

        $content = File::get($path);
        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    /**
     * Save video review records to JSON.
     *
     * @param  array<int, array<string, mixed>>  $records
     */
    protected function saveRecords(array $records): void
    {
        $path = $this->getJsonPath();
        File::put($path, json_encode(array_values($records), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->galleryService->clearCache();
    }

    /**
     * Display a listing of customer video reviews with placement toggles and SEO stats.
     */
    public function index(Request $request): View
    {
        $records = $this->loadRecords();
        $tours = Tour::where('status', 'active')->select('id', 'name', 'slug')->get();
        $categories = ReviewMediaGalleryService::CATEGORIES;

        // Filtering
        $search = trim((string) $request->input('search', ''));
        $categoryFilter = $request->input('category', 'all');
        $placementFilter = $request->input('placement', 'all');

        $filtered = collect($records)->filter(function ($item) use ($search, $categoryFilter, $placementFilter) {
            if ($search !== '') {
                $searchLower = strtolower($search);
                $titleMatch = str_contains(strtolower($item['title'] ?? ''), $searchLower);
                $nameMatch = str_contains(strtolower($item['reviewer_name'] ?? ''), $searchLower);
                $countryMatch = str_contains(strtolower($item['reviewer_country'] ?? ''), $searchLower);
                $transcriptMatch = str_contains(strtolower($item['transcript'] ?? ''), $searchLower);
                if (! ($titleMatch || $nameMatch || $countryMatch || $transcriptMatch)) {
                    return false;
                }
            }

            if ($categoryFilter !== 'all' && ($item['category'] ?? '') !== $categoryFilter) {
                return false;
            }

            if ($placementFilter !== 'all') {
                $placements = $item['placements'] ?? [];
                if (empty($placements[$placementFilter])) {
                    return false;
                }
            }

            return true;
        })->values()->all();

        // Stats calculation
        $totalVideos = count($records);
        $activeVideos = collect($records)->where('is_active', true)->count();
        $homeVideos = collect($records)->filter(fn ($i) => ! empty($i['placements']['home']))->count();
        $tourVideos = collect($records)->filter(fn ($i) => ! empty($i['placements']['tour']))->count();
        $totalDurationSec = collect($records)->sum(fn ($i) => (int) ($i['duration'] ?? 0));
        $avgRating = $totalVideos > 0 ? round(collect($records)->avg(fn ($i) => (float) ($i['rating'] ?? 5.0)), 1) : 5.0;

        $stats = [
            'total' => $totalVideos,
            'active' => $activeVideos,
            'home' => $homeVideos,
            'tour' => $tourVideos,
            'total_duration' => sprintf('%02d:%02d min', floor($totalDurationSec / 60), $totalDurationSec % 60),
            'avg_rating' => $avgRating,
        ];

        return view('admin.video-reviews.index', compact(
            'filtered',
            'records',
            'tours',
            'categories',
            'stats',
            'search',
            'categoryFilter',
            'placementFilter'
        ));
    }

    /**
     * Store a newly created or uploaded video review.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'reviewer_name' => 'required|string|max:255',
            'reviewer_country' => 'nullable|string|max:100',
            'reviewer_country_code' => 'nullable|string|max:10',
            'tour_slug' => 'nullable|string|max:100',
            'category' => 'required|string',
            'rating' => 'required|numeric|min:1|max:5',
            'duration' => 'required|integer|min:1|max:3600',
            'caption' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'transcript' => 'nullable|string',
            'alt' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'keywords' => 'nullable|string',
            'video_file' => 'nullable|mimes:mp4,mov,webm,m4v|max:65536',
            'poster_file' => 'nullable|image|mimes:jpeg,png,webp,jpg|max:10240',
            'existing_filename' => 'nullable|string',
            'existing_poster' => 'nullable|string',
        ]);

        $records = $this->loadRecords();

        $filename = $validated['existing_filename'] ?? '';
        $posterFilename = $validated['existing_poster'] ?? '';

        // Handle video upload
        if ($request->hasFile('video_file')) {
            $video = $request->file('video_file');
            $safeName = Str::slug(pathinfo($video->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . time() . '.' . $video->getClientOriginalExtension();
            $video->move(public_path('videos/reviews'), $safeName);
            $filename = $safeName;
        }

        // Handle poster upload
        if ($request->hasFile('poster_file')) {
            $poster = $request->file('poster_file');
            $safePosterName = Str::slug(pathinfo($poster->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . time() . '.' . $poster->getClientOriginalExtension();
            $poster->move(public_path('images/video-posters'), $safePosterName);
            $posterFilename = $safePosterName;
        } elseif (! empty($filename) && empty($posterFilename)) {
            $posterFilename = str_replace(pathinfo($filename, PATHINFO_EXTENSION), 'webp', $filename);
        }

        $id = 'ddt_video_review_' . str_pad((string) (count($records) + 1), 2, '0', STR_PAD_LEFT);

        $keywords = ! empty($validated['keywords'])
            ? array_values(array_filter(array_map('trim', explode(',', $validated['keywords']))))
            : [];

        $placements = [
            'home' => (bool) $request->input('placement_home', false),
            'gallery' => (bool) $request->input('placement_gallery', true),
            'tour' => (bool) $request->input('placement_tour', false),
        ];

        $durationSec = (int) $validated['duration'];

        $newEntry = [
            'id' => $id,
            'filename' => $filename,
            'poster_filename' => $posterFilename,
            'type' => 'video',
            'category' => $validated['category'],
            'tour_slug' => $validated['tour_slug'] ?: null,
            'tour_name' => $this->resolveTourName($validated['tour_slug'] ?? null),
            'title' => $validated['title'],
            'alt' => $validated['alt'] ?: $validated['title'],
            'caption' => $validated['caption'] ?: $validated['title'],
            'description' => $validated['description'] ?: '',
            'transcript' => $validated['transcript'] ?: '',
            'reviewer_name' => $validated['reviewer_name'],
            'reviewer_country' => $validated['reviewer_country'] ?: 'UAE',
            'reviewer_country_code' => strtoupper($validated['reviewer_country_code'] ?: 'AE'),
            'rating' => (float) $validated['rating'],
            'duration' => $durationSec,
            'duration_iso' => 'PT' . $durationSec . 'S',
            'date' => now()->toDateString(),
            'location' => $validated['location'] ?: 'Lahbab Red Dunes, Dubai, UAE',
            'keywords' => $keywords,
            'placements' => $placements,
            'is_active' => (bool) $request->input('is_active', true),
            'sort_order' => count($records) + 1,
        ];

        $records[] = $newEntry;
        $this->saveRecords($records);

        return redirect()->route('admin.video-reviews.index')->with('success', 'Customer video review created successfully with full SEO & AISEO fields!');
    }

    /**
     * Update an existing video review.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'reviewer_name' => 'required|string|max:255',
            'reviewer_country' => 'nullable|string|max:100',
            'reviewer_country_code' => 'nullable|string|max:10',
            'tour_slug' => 'nullable|string|max:100',
            'category' => 'required|string',
            'rating' => 'required|numeric|min:1|max:5',
            'duration' => 'required|integer|min:1|max:3600',
            'caption' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'transcript' => 'nullable|string',
            'alt' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'keywords' => 'nullable|string',
            'video_file' => 'nullable|mimes:mp4,mov,webm,m4v|max:65536',
            'poster_file' => 'nullable|image|mimes:jpeg,png,webp,jpg|max:10240',
        ]);

        $records = $this->loadRecords();
        $foundIndex = null;

        foreach ($records as $index => $item) {
            if (($item['id'] ?? '') === $id) {
                $foundIndex = $index;
                break;
            }
        }

        if ($foundIndex === null) {
            return redirect()->route('admin.video-reviews.index')->with('error', 'Video review not found.');
        }

        $entry = $records[$foundIndex];

        // Replace video if uploaded
        if ($request->hasFile('video_file')) {
            $video = $request->file('video_file');
            $safeName = Str::slug(pathinfo($video->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . time() . '.' . $video->getClientOriginalExtension();
            $video->move(public_path('videos/reviews'), $safeName);
            $entry['filename'] = $safeName;
        }

        // Replace poster if uploaded
        if ($request->hasFile('poster_file')) {
            $poster = $request->file('poster_file');
            $safePosterName = Str::slug(pathinfo($poster->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . time() . '.' . $poster->getClientOriginalExtension();
            $poster->move(public_path('images/video-posters'), $safePosterName);
            $entry['poster_filename'] = $safePosterName;
        }

        $keywords = ! empty($validated['keywords'])
            ? array_values(array_filter(array_map('trim', explode(',', $validated['keywords']))))
            : ($entry['keywords'] ?? []);

        $durationSec = (int) $validated['duration'];

        $entry['title'] = $validated['title'];
        $entry['reviewer_name'] = $validated['reviewer_name'];
        $entry['reviewer_country'] = $validated['reviewer_country'] ?: 'UAE';
        $entry['reviewer_country_code'] = strtoupper($validated['reviewer_country_code'] ?: 'AE');
        $entry['tour_slug'] = $validated['tour_slug'] ?: null;
        $entry['tour_name'] = $this->resolveTourName($validated['tour_slug'] ?? null);
        $entry['category'] = $validated['category'];
        $entry['rating'] = (float) $validated['rating'];
        $entry['duration'] = $durationSec;
        $entry['duration_iso'] = 'PT' . $durationSec . 'S';
        $entry['caption'] = $validated['caption'] ?: $validated['title'];
        $entry['description'] = $validated['description'] ?: '';
        $entry['transcript'] = $validated['transcript'] ?: '';
        $entry['alt'] = $validated['alt'] ?: $validated['title'];
        $entry['location'] = $validated['location'] ?: 'Lahbab Red Dunes, Dubai, UAE';
        $entry['keywords'] = $keywords;
        $entry['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : ($entry['is_active'] ?? true);

        if ($request->has('placements_submitted')) {
            $entry['placements'] = [
                'home' => (bool) $request->input('placement_home', false),
                'gallery' => (bool) $request->input('placement_gallery', false),
                'tour' => (bool) $request->input('placement_tour', false),
            ];
        }

        $records[$foundIndex] = $entry;
        $this->saveRecords($records);

        return redirect()->route('admin.video-reviews.index')->with('success', 'Video review updated successfully!');
    }

    /**
     * Quick toggle placement via AJAX.
     */
    public function togglePlacement(Request $request, string $id): JsonResponse
    {
        $placement = $request->input('placement');
        if (! in_array($placement, ['home', 'gallery', 'tour'], true)) {
            return response()->json(['success' => false, 'message' => 'Invalid placement'], 422);
        }

        $records = $this->loadRecords();
        $updated = false;
        $newValue = false;

        foreach ($records as &$item) {
            if (($item['id'] ?? '') === $id) {
                $current = (bool) ($item['placements'][$placement] ?? false);
                $newValue = ! $current;
                $item['placements'][$placement] = $newValue;
                $updated = true;
                break;
            }
        }

        if ($updated) {
            $this->saveRecords($records);
            return response()->json([
                'success' => true,
                'placement' => $placement,
                'value' => $newValue,
                'message' => ucfirst($placement) . ' placement updated successfully.',
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Video review not found'], 404);
    }

    /**
     * Quick toggle active status via AJAX.
     */
    public function toggleStatus(Request $request, string $id): JsonResponse
    {
        $records = $this->loadRecords();
        $updated = false;
        $newValue = false;

        foreach ($records as &$item) {
            if (($item['id'] ?? '') === $id) {
                $current = (bool) ($item['is_active'] ?? true);
                $newValue = ! $current;
                $item['is_active'] = $newValue;
                $updated = true;
                break;
            }
        }

        if ($updated) {
            $this->saveRecords($records);
            return response()->json([
                'success' => true,
                'is_active' => $newValue,
                'message' => 'Video review ' . ($newValue ? 'activated' : 'deactivated') . ' successfully.',
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Video review not found'], 404);
    }

    /**
     * Delete or deactivate a video review.
     */
    public function destroy(string $id): RedirectResponse
    {
        $records = $this->loadRecords();
        $filtered = array_values(array_filter($records, fn ($item) => ($item['id'] ?? '') !== $id));

        $this->saveRecords($filtered);

        return redirect()->route('admin.video-reviews.index')->with('success', 'Video review removed successfully.');
    }

    /**
     * Helper to resolve Tour name by slug.
     */
    protected function resolveTourName(?string $slug): string
    {
        if (empty($slug)) {
            return '';
        }

        try {
            $tour = Tour::where('slug', $slug)->first();
            return $tour ? $tour->name : ucwords(str_replace('-', ' ', $slug));
        } catch (\Throwable $e) {
            return ucwords(str_replace('-', ' ', $slug));
        }
    }
}
