<?php

namespace App\Services;

use App\Models\Review;
use App\Models\Tour;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReviewMediaGalleryService
{
    public const CACHE_KEY_ITEMS = 'site_guest_gallery_items';
    public const CACHE_KEY_HOMEPAGE = 'site_home_gallery_feed';
    public const CACHE_TTL_SECONDS = 43200; // 12 hours
    public const URL_STATUS_TTL_SECONDS = 86400; // 24 hours

    /**
     * Categories mapped to UI labels and search terms.
     */
    public const CATEGORIES = [
        'all' => [
            'id' => 'all',
            'name' => 'All Moments',
            'icon' => 'bi bi-grid-fill',
        ],
        'red_dunes' => [
            'id' => 'red_dunes',
            'name' => 'Red Dunes & Safari',
            'icon' => 'bi bi-compass-fill',
        ],
        'buggy_quad' => [
            'id' => 'buggy_quad',
            'name' => 'Buggy & Quad Biking',
            'icon' => 'bi bi-lightning-charge-fill',
        ],
        'camp_bbq' => [
            'id' => 'camp_bbq',
            'name' => 'Desert Camp & BBQ',
            'icon' => 'bi bi-fire',
        ],
        'sunset_camels' => [
            'id' => 'sunset_camels',
            'name' => 'Sunset & Camels',
            'icon' => 'bi bi-sunset-fill',
        ],
    ];

    /**
     * Retrieve verified, healthy gallery items with optional filters.
     * Excludes broken media links automatically.
     *
     * @param array<string, mixed> $options
     * @return array<int, array<string, mixed>>
     */
    public function getGalleryItems(array $options = []): array
    {
        $forceVerify = (bool) ($options['force_verify'] ?? false);

        // Fetch verified items list from cache or compute
        if ($forceVerify) {
            $items = $this->loadAndVerifyAllMedia(true);
        } else {
            $items = Cache::remember(self::CACHE_KEY_ITEMS, self::CACHE_TTL_SECONDS, function () {
                return $this->loadAndVerifyAllMedia(false);
            });
        }

        if (! is_array($items)) {
            $items = [];
        }

        // Apply filters
        $collection = collect($items);

        // Category filter
        $category = $options['category'] ?? 'all';
        if ($category && $category !== 'all') {
            $collection = $collection->filter(function ($item) use ($category) {
                return ($item['category'] ?? 'red_dunes') === $category;
            });
        }

        // Media type filter (image, video)
        $type = $options['type'] ?? 'all';
        if ($type && $type !== 'all') {
            $collection = $collection->filter(function ($item) use ($type) {
                return ($item['type'] ?? 'image') === $type;
            });
        }

        // Review source filter (google, tripadvisor, manual)
        $source = $options['source'] ?? 'all';
        if ($source && $source !== 'all') {
            $collection = $collection->filter(function ($item) use ($source) {
                return strtolower($item['source'] ?? '') === strtolower($source);
            });
        }

        // Tour relevance filter
        if (! empty($options['tour'])) {
            $collection = $this->filterByTourRelevance($collection, $options['tour']);
        }

        // Limit results if requested
        $limit = isset($options['limit']) ? (int) $options['limit'] : null;
        if ($limit && $limit > 0) {
            $collection = $collection->take($limit);
        }

        return array_values($collection->all());
    }

    /**
     * Get verified media items specifically curated for the homepage.
     *
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public function getHomepageItems(int $limit = 12): array
    {
        return Cache::remember(self::CACHE_KEY_HOMEPAGE, self::CACHE_TTL_SECONDS, function () use ($limit) {
            $all = $this->getGalleryItems(['limit' => 30]);

            if (empty($all)) {
                return [];
            }

            // Strive for balanced category and source diversity on the homepage
            $categories = ['red_dunes', 'buggy_quad', 'camp_bbq', 'sunset_camels'];
            $selected = collect();

            // Pick top 2 from each category first
            foreach ($categories as $cat) {
                $catItems = collect($all)->where('category', $cat)->take(3);
                $selected = $selected->concat($catItems);
            }

            // Fill remaining quota with other top reviews
            if ($selected->count() < $limit) {
                $remaining = collect($all)->reject(fn ($i) => $selected->contains('id', $i['id']))->take($limit - $selected->count());
                $selected = $selected->concat($remaining);
            }

            return array_values($selected->take($limit)->all());
        });
    }

    /**
     * Get verified media items relevant to a specific tour.
     *
     * @param Tour|int|string $tour
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public function getTourItems(Tour|int|string $tour, int $limit = 8): array
    {
        return $this->getGalleryItems([
            'tour' => $tour,
            'limit' => $limit,
        ]);
    }

    /**
     * Filter a media collection by matching keywords from a tour.
     */
    protected function filterByTourRelevance(Collection $collection, Tour|int|string $tour): Collection
    {
        $tourName = '';
        $tourSlug = '';

        if ($tour instanceof Tour) {
            $tourName = strtolower($tour->name ?? '');
            $tourSlug = strtolower($tour->slug ?? '');
        } elseif (is_string($tour)) {
            $tourSlug = strtolower($tour);
        }

        $isBuggyOrQuad = str_contains($tourName, 'buggy') || str_contains($tourName, 'quad') || str_contains($tourSlug, 'buggy') || str_contains($tourSlug, 'quad');
        $isCampOrEvening = str_contains($tourName, 'evening') || str_contains($tourName, 'bbq') || str_contains($tourName, 'dinner') || str_contains($tourSlug, 'evening') || str_contains($tourSlug, 'overnight');
        $isMorningOrSunrise = str_contains($tourName, 'morning') || str_contains($tourName, 'sunrise') || str_contains($tourSlug, 'morning');

        $matched = $collection->filter(function ($item) use ($isBuggyOrQuad, $isCampOrEvening, $isMorningOrSunrise) {
            $cat = $item['category'] ?? '';
            if ($isBuggyOrQuad && $cat === 'buggy_quad') {
                return true;
            }
            if ($isCampOrEvening && in_array($cat, ['camp_bbq', 'red_dunes', 'sunset_camels'])) {
                return true;
            }
            if ($isMorningOrSunrise && in_array($cat, ['sunset_camels', 'red_dunes'])) {
                return true;
            }
            return false;
        });

        // If specific matches are fewer than 4, backfill with top general items
        if ($matched->count() < 4) {
            $fillers = $collection->reject(fn ($i) => $matched->contains('id', $i['id']));
            $matched = $matched->concat($fillers);
        }

        return $matched;
    }

    /**
     * Extract raw media from approved reviews and verify each URL.
     */
    public function loadAndVerifyAllMedia(bool $force = false): array
    {
        $rawItems = $this->extractAllReviewMedia();
        return $this->filterHealthyItems($rawItems, $force);
    }

    /**
     * Extract all photo/video media items from approved customer reviews.
     * Excludes reviewer avatars and profile photos.
     *
     * @return array<int, array<string, mixed>>
     */
    public function extractAllReviewMedia(): array
    {
        $reviews = Review::where('status', 'approved')
            ->whereNotNull('photos')
            ->orderBy('published_date', 'desc')
            ->get();

        $items = [];
        $seenUrls = [];

        foreach ($reviews as $review) {
            $photos = $review->photos;
            if (! is_array($photos) || empty($photos)) {
                continue;
            }

            $reviewerAvatar = $review->reviewer_avatar_url ?: '';
            $reviewerProfile = $review->reviewer_profile_url ?: '';

            foreach ($photos as $mediaEntry) {
                // Support both string URL and associative array
                $url = is_array($mediaEntry) ? ($mediaEntry['url'] ?? '') : (string) $mediaEntry;
                $url = trim($url);

                if (empty($url) || isset($seenUrls[$url])) {
                    continue;
                }

                // Exclude avatars, profile photos, or system placeholders
                if ($this->isAvatarOrPlaceholder($url, $reviewerAvatar, $reviewerProfile)) {
                    continue;
                }

                $seenUrls[$url] = true;

                $type = $this->detectMediaType($url, $mediaEntry);
                $category = $this->deduceCategory(
                    $review->review_text ?? '',
                    $review->review_title ?? '',
                    $review->reviewer_name ?? ''
                );

                $textSnippet = trim((string) ($review->review_text ?? ''));
                if (mb_strlen($textSnippet) > 160) {
                    $textSnippet = Str::limit($textSnippet, 160);
                }

                $items[] = [
                    'id' => 'media_' . md5($url),
                    'url' => $url,
                    'type' => $type, // 'image' or 'video'
                    'thumbnail_url' => $type === 'video' ? ($mediaEntry['thumbnail'] ?? $url) : $url,
                    'category' => $category,
                    'category_label' => self::CATEGORIES[$category]['name'] ?? 'Desert Adventure',
                    'reviewer_name' => $review->reviewer_name ?: 'Verified Traveler',
                    'reviewer_avatar_url' => $reviewerAvatar ?: asset('images/avatar-default.svg'),
                    'rating' => (float) ($review->rating ?: 5.0),
                    'review_title' => $review->review_title ?: 'Desert Safari Experience',
                    'review_text' => $textSnippet,
                    'full_review_text' => $review->review_text ?? '',
                    'published_date' => $review->published_date ? $review->published_date->toDateString() : null,
                    'formatted_date' => $review->published_date ? $review->published_date->format('M d, Y') : null,
                    'source' => strtolower($review->source ?: 'google'),
                    'source_label' => strtolower($review->source ?: 'google') === 'google' ? 'Google Reviews' : (strtolower($review->source ?: '') === 'tripadvisor' ? 'TripAdvisor' : 'Direct Booking'),
                    'review_url' => $review->review_url ?: null,
                    'is_local_guide' => (bool) ($review->is_local_guide ?? false),
                    'likes_count' => (int) ($review->likes_count ?? 0),
                ];
            }
        }

        return $items;
    }

    /**
     * Filter items to include only verified, healthy URLs.
     */
    public function filterHealthyItems(array $items, bool $force = false): array
    {
        $healthy = [];

        foreach ($items as $item) {
            $url = $item['url'] ?? '';
            if (empty($url)) {
                continue;
            }

            if ($this->isUrlHealthy($url, $force)) {
                $healthy[] = $item;
            }
        }

        return $healthy;
    }

    /**
     * Check if a media URL is healthy.
     * Uses cached status to avoid repetitive HTTP checks.
     */
    public function isUrlHealthy(string $url, bool $force = false): bool
    {
        if (empty($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $cacheKey = 'gallery_media_status_' . md5($url);

        if (! $force && Cache::has($cacheKey)) {
            return (bool) Cache::get($cacheKey);
        }

        // Fast probe: HEAD request with 2.5s timeout
        try {
            $response = Http::timeout(2.5)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'image/*,video/*,*/*',
                ])
                ->head($url);

            $status = $response->status();

            // If HEAD is not allowed (e.g. 405 Method Not Allowed), retry with small GET
            if ($status === 405) {
                $response = Http::timeout(2.5)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                        'Range' => 'bytes=0-1024',
                    ])
                    ->get($url);
                $status = $response->status();
            }

            $isHealthy = ($status >= 200 && $status < 400);

            Cache::put($cacheKey, $isHealthy, self::URL_STATUS_TTL_SECONDS);

            if (! $isHealthy) {
                Log::notice("Review gallery excluded broken media link [HTTP {$status}]: {$url}");
            }

            return $isHealthy;
        } catch (\Throwable $e) {
            // Connection error, DNS failure or timeout -> treat as unhealthy
            Cache::put($cacheKey, false, self::URL_STATUS_TTL_SECONDS);
            Log::debug("Review gallery URL health probe exception: {$e->getMessage()} for {$url}");
            return false;
        }
    }

    /**
     * Mark a URL as broken (called via AJAX error reporting from the frontend).
     */
    public function markUrlAsBroken(string $url): void
    {
        if (empty($url)) {
            return;
        }

        $cacheKey = 'gallery_media_status_' . md5($url);
        Cache::put($cacheKey, false, self::URL_STATUS_TTL_SECONDS);

        // Invalidate aggregate gallery feeds so broken image is omitted on next load
        $this->clearCache();

        Log::info("Review gallery media URL reported broken & blacklisted: {$url}");
    }

    /**
     * Invalidate all gallery caches.
     */
    public function clearCache(): void
    {
        try {
            Cache::forget(self::CACHE_KEY_ITEMS);
            Cache::forget(self::CACHE_KEY_HOMEPAGE);
        } catch (\Throwable $e) {
            Log::warning('Error purging gallery caches: ' . $e->getMessage());
        }
    }

    /**
     * Calculate category statistics across verified gallery items.
     *
     * @param array<int, array<string, mixed>> $items
     * @return array<string, int>
     */
    public function getCategoryStats(array $items): array
    {
        $stats = [
            'all' => count($items),
            'red_dunes' => 0,
            'buggy_quad' => 0,
            'camp_bbq' => 0,
            'sunset_camels' => 0,
            'videos' => 0,
        ];

        foreach ($items as $item) {
            $cat = $item['category'] ?? 'red_dunes';
            if (isset($stats[$cat])) {
                $stats[$cat]++;
            }

            if (($item['type'] ?? '') === 'video') {
                $stats['videos']++;
            }
        }

        return $stats;
    }

    /**
     * Deduce activity category based on keywords in review text and title.
     */
    public function deduceCategory(string $text, string $title, ?string $reviewer = null): string
    {
        $combined = strtolower("{$title} {$text} {$reviewer}");

        // 1. Buggy & Quad Biking
        if (
            str_contains($combined, 'buggy') ||
            str_contains($combined, 'quad') ||
            str_contains($combined, 'atv') ||
            str_contains($combined, 'can-am') ||
            str_contains($combined, 'polaris') ||
            str_contains($combined, '1000cc') ||
            str_contains($combined, 'bike')
        ) {
            return 'buggy_quad';
        }

        // 2. Desert Camp & BBQ Entertainment
        if (
            str_contains($combined, 'bbq') ||
            str_contains($combined, 'dinner') ||
            str_contains($combined, 'buffet') ||
            str_contains($combined, 'camp') ||
            str_contains($combined, 'tanoura') ||
            str_contains($combined, 'fire show') ||
            str_contains($combined, 'belly dance') ||
            str_contains($combined, 'show') ||
            str_contains($combined, 'henna') ||
            str_contains($combined, 'shisha')
        ) {
            return 'camp_bbq';
        }

        // 3. Sunset & Camels
        if (
            str_contains($combined, 'sunset') ||
            str_contains($combined, 'sunrise') ||
            str_contains($combined, 'camel') ||
            str_contains($combined, 'sandboard') ||
            str_contains($combined, 'sand board') ||
            str_contains($combined, 'falcon') ||
            str_contains($combined, 'falconry')
        ) {
            return 'sunset_camels';
        }

        // Default to Red Dunes & Safari
        return 'red_dunes';
    }

    /**
     * Detect whether a media item is a video or an image.
     *
     * @param string $url
     * @param array<string, mixed>|string $rawEntry
     * @return string 'video'|'image'
     */
    protected function detectMediaType(string $url, $rawEntry): string
    {
        if (is_array($rawEntry) && isset($rawEntry['type']) && in_array($rawEntry['type'], ['video', 'image'], true)) {
            return $rawEntry['type'];
        }

        $lowerUrl = strtolower($url);

        // Common video extensions
        if (preg_match('/\.(mp4|webm|ogg|mov|m4v)(\?.*)?$/i', $lowerUrl)) {
            return 'video';
        }

        // Video platforms
        if (
            str_contains($lowerUrl, 'youtube.com') ||
            str_contains($lowerUrl, 'youtu.be') ||
            str_contains($lowerUrl, 'vimeo.com') ||
            str_contains($lowerUrl, 'tiktok.com') ||
            str_contains($lowerUrl, 'googlevideo.com')
        ) {
            return 'video';
        }

        return 'image';
    }

    /**
     * Check if a URL represents a reviewer avatar rather than authentic review experience media.
     */
    protected function isAvatarOrPlaceholder(string $url, string $reviewerAvatar, string $reviewerProfile): bool
    {
        if (empty($url)) {
            return true;
        }

        // Exact match with reviewer avatar or profile link
        if ($url === $reviewerAvatar || $url === $reviewerProfile) {
            return true;
        }

        $lower = strtolower($url);

        // Common avatar generators & static avatar filenames
        if (
            str_contains($lower, 'ui-avatars.com') ||
            str_contains($lower, 'avatar') ||
            str_contains($lower, 'profile_pic') ||
            str_contains($lower, 'profile-photo') ||
            str_contains($lower, 'user-profile') ||
            str_contains($lower, 'gravatar.com')
        ) {
            return true;
        }

        return false;
    }
}
