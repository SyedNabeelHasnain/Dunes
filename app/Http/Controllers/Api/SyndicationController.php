<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Tour;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SyndicationController extends Controller
{
    /**
     * Cache TTL in seconds (30 minutes).
     */
    protected int $cacheTtl = 1800;

    /**
     * Return public JSON syndication feed of active tours.
     */
    public function tours(Request $request): JsonResponse
    {
        $category = trim((string) $request->input('category', ''));
        $slug = trim((string) $request->input('tour', $request->input('slug', '')));
        $partnerId = trim((string) $request->input('partner_id', $request->input('partner', '')));

        // Exclude partner_id from cache key so arbitrary partner strings cannot flood cache storage
        $cacheKey = 'syndication_tours_' . md5("{$category}_{$slug}");

        $data = Cache::remember($cacheKey, $this->cacheTtl, function () use ($category, $slug) {
            return $this->buildSyndicationPayload($category, $slug);
        });

        if ($partnerId !== '' && ! empty($data['tours']) && is_array($data['tours'])) {
            foreach ($data['tours'] as &$item) {
                $baseTourUrl = $item['url'] ?? url('/' . ($item['slug'] ?? ''));
                $item['booking_url'] = $baseTourUrl . '?' . http_build_query([
                    'utm_source' => $partnerId,
                    'utm_medium' => 'syndication_api',
                    'utm_campaign' => 'partner_' . ($item['slug'] ?? 'tour'),
                ]);
            }
            unset($item);
        }

        return response()->json($data, 200, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }

    /**
     * Build the syndication dataset.
     */
    protected function buildSyndicationPayload(string $categoryFilter, string $slugFilter, string $partnerId = ''): array
    {
        try {
            $settings = Setting::pluck('setting_value', 'setting_key')->all();
        } catch (\Throwable $e) {
            $settings = [];
        }

        $phone = $settings['site_phone'] ?? '+971 50 245 6056';
        $whatsapp = $settings['site_whatsapp'] ?? '971502456056';
        $license = $settings['company_license_number'] ?? ($settings['site_det_license'] ?? '1430583');

        $query = Tour::with(['category', 'tiers'])->where('status', 'active')->orderBy('priority', 'asc');

        if ($slugFilter !== '') {
            $query->where('slug', $slugFilter);
        }

        if ($categoryFilter !== '') {
            $escapedCategory = addcslashes($categoryFilter, '%_!');
            $query->whereHas('category', function ($q) use ($categoryFilter, $escapedCategory) {
                $q->where('slug', $categoryFilter)
                    ->orWhereRaw("name LIKE ? ESCAPE '!'", ["%{$escapedCategory}%"]);
            });
        }

        $tours = $query->get();

        $items = [];
        foreach ($tours as $tour) {
            // Determine starting price
            $startingPrice = 0.00;
            if ($tour->tiers->isNotEmpty()) {
                $startingPrice = (float) $tour->tiers->min(function ($t) {
                    return $t->pivot->price ?? 0;
                });
            }

            // Determine authoritative timings
            $timings = $this->resolveAuthoritativeTimings($tour);

            // Build tracking URL
            $bookingUrl = url('/' . $tour->slug);
            if ($partnerId !== '') {
                $bookingUrl .= '?' . http_build_query([
                    'utm_source' => $partnerId,
                    'utm_medium' => 'syndication_api',
                    'utm_campaign' => 'partner_' . $tour->slug,
                ]);
            }

            // Resolve image URLs
            $images = [];
            foreach ($tour->getImageList() as $img) {
                $images[] = $img;
            }

            $tiersData = [];
            foreach ($tour->tiers as $tier) {
                $tiersData[] = [
                    'id' => $tier->id,
                    'name' => $tier->display_name ?: $tier->name,
                    'price_aed' => (float) ($tier->pivot->price ?? 0),
                    'old_price_aed' => (float) ($tier->pivot->old_price ?? 0),
                    'price_type' => $tier->pivot->price_type ?? 'per_person',
                    'guest_count' => (int) ($tier->pivot->guest_count ?? 1),
                ];
            }

            $items[] = [
                'id' => $tour->id,
                'slug' => $tour->slug,
                'name' => $tour->name,
                'category' => [
                    'id' => $tour->category?->id,
                    'name' => $tour->category?->name ?? 'Desert Safari',
                    'slug' => $tour->category?->slug ?? 'desert-safari',
                ],
                'duration' => $tour->duration ?: '6 Hours',
                'timings' => [
                    'pickup' => $timings['pickup'],
                    'dropoff' => $timings['dropoff'],
                    'schedule_notes' => $timings['summary'],
                ],
                'schedule' => [
                    'pickup_window' => $timings['pickup'],
                    'dropoff_window' => $timings['dropoff'],
                    'summary' => $timings['summary'],
                ],
                'rating' => (float) ($tour->rating ?: 4.9),
                'review_count' => (int) ($tour->review_count ?: 2847),
                'starting_price_aed' => $startingPrice > 0 ? $startingPrice : 79.00,
                'currency' => 'AED',
                'tiers' => $tiersData,
                'short_description' => strip_tags((string) $tour->short_desc),
                'hero_image' => $images[0] ?? url('/images/placeholder.webp'),
                'gallery_images' => array_slice($images, 1, 5),
                'url' => url('/' . $tour->slug),
                'canonical_url' => url('/' . $tour->slug),
                'booking_url' => $bookingUrl,
                'schema_org' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'TouristTrip',
                    'name' => $tour->name,
                    'offers' => [
                        '@type' => 'Offer',
                        'price' => $startingPrice > 0 ? $startingPrice : 79.00,
                        'priceCurrency' => 'AED',
                    ],
                ],
                'cancellation_policy' => 'Free cancellation up to 24 hours prior to departure',
                'is_bestseller' => (bool) $tour->is_bestseller,
                'is_featured' => (bool) $tour->is_featured,
            ];
        }

        return [
            'status' => 'success',
            'syndication_version' => '1.0',
            'syndicated_at' => now()->toIso8601String(),
            'operator' => [
                'name' => 'Dunes Discovery Tourism LLC',
                'license_det' => (string) $license,
                'portal_url' => url('/'),
                'official_support' => $phone,
            ],
            'provider' => [
                'brand' => 'Dunes Discovery Tourism LLC',
                'license' => 'DET Tourism License #' . $license,
                'verified_operator' => true,
                'rating' => 4.9,
                'review_count' => 2847,
                'website' => url('/'),
                'phone' => $phone,
                'whatsapp' => 'https://wa.me/' . preg_replace('/[^0-9]/', '', $whatsapp),
                'currency' => 'AED',
            ],
            'count' => count($items),
            'total_tours' => count($items),
            'tours' => $items,
        ];
    }

    /**
     * Resolve authoritative operational timings matching user guidelines.
     */
    protected function resolveAuthoritativeTimings(Tour $tour): array
    {
        $slug = strtolower($tour->slug);
        $name = strtolower($tour->name);
        $dbPickup = trim((string) ($tour->pickup_time ?? ''));
        $dbDropoff = trim((string) ($tour->dropoff_time ?? ''));

        if (str_contains($slug, 'morning') || str_contains($name, 'morning')) {
            $pickup = $dbPickup !== '' ? $dbPickup : '07:00 AM – 07:30 AM';
            $dropoff = $dbDropoff !== '' ? $dbDropoff : '10:30 AM – 11:00 AM';

            return [
                'pickup' => $pickup,
                'dropoff' => $dropoff,
                'summary' => "Morning Red Dunes Safari: {$pickup} Pickup · {$dropoff} Return (Back before midday)",
            ];
        }

        if (str_contains($slug, 'overnight') || str_contains($name, 'overnight')) {
            $pickup = $dbPickup !== '' ? $dbPickup : '02:45 PM – 03:15 PM';
            $dropoff = $dbDropoff !== '' ? $dbDropoff : '06:30 AM – 07:00 AM (Next Day)';

            return [
                'pickup' => $pickup,
                'dropoff' => $dropoff,
                'summary' => "Overnight Bedouin Safari: {$pickup} Pickup · {$dropoff} Return",
            ];
        }

        if (str_contains($slug, 'quad') || str_contains($slug, 'buggy') || str_contains($name, 'quad') || str_contains($name, 'buggy')) {
            $pickup = $dbPickup !== '' ? $dbPickup : '02:00 PM – 02:30 PM';
            $dropoff = $dbDropoff !== '' ? $dbDropoff : '09:00 PM – 09:30 PM';

            return [
                'pickup' => $pickup,
                'dropoff' => $dropoff,
                'summary' => "Self-Drive Quad & Buggy Safari: {$pickup} Pickup · {$dropoff} Return",
            ];
        }

        // Standard Evening Safari
        $pickup = $dbPickup !== '' ? $dbPickup : '02:30 PM – 03:00 PM';
        $dropoff = $dbDropoff !== '' ? $dbDropoff : '09:00 PM – 09:30 PM';

        return [
            'pickup' => $pickup,
            'dropoff' => $dropoff,
            'summary' => "Evening Red Dunes Safari & BBQ: {$pickup} Pickup · {$dropoff} Return",
        ];
    }
}
