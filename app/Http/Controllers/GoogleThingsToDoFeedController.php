<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Tour;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class GoogleThingsToDoFeedController extends Controller
{
    /**
     * Cache duration for feeds: 3600 seconds (1 hour).
     */
    protected int $cacheTtl = 3600;

    /**
     * Return Google Things to Do Products XML Feed.
     */
    public function productsXml(): Response
    {
        $this->ensureFeedEnabled();

        $content = Cache::remember('gttd_products_xml', $this->cacheTtl, function () {
            return $this->buildProductsXml();
        });

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Return Google Things to Do Options & Pricing XML Feed.
     */
    public function optionsXml(): Response
    {
        $this->ensureFeedEnabled();

        $content = Cache::remember('gttd_options_xml', $this->cacheTtl, function () {
            return $this->buildOptionsXml();
        });

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Return Google Things to Do Operators XML Feed.
     */
    public function operatorsXml(): Response
    {
        $this->ensureFeedEnabled();

        $content = Cache::remember('gttd_operators_xml', $this->cacheTtl, function () {
            return $this->buildOperatorsXml();
        });

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Return Unified Consolidated Google Things to Do XML Feed.
     */
    public function unifiedXml(): Response
    {
        $this->ensureFeedEnabled();

        $content = Cache::remember('gttd_unified_xml', $this->cacheTtl, function () {
            return $this->buildUnifiedXml();
        });

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Return Google Things to Do REST / JSON Feed.
     */
    public function feedJson(): JsonResponse
    {
        $this->ensureFeedEnabled();

        $data = Cache::remember('gttd_feed_json', $this->cacheTtl, function () {
            return [
                'metadata' => [
                    'feed_name' => 'Google Things To Do Catalog Feed',
                    'feed_version' => '1.0',
                    'generation_timestamp' => now()->toIso8601String(),
                    'publisher' => 'Dunes Discovery Tourism L.L.C.',
                    'license' => 'Dubai DET #1430583',
                ],
                'operator' => $this->getOperatorData(),
                'products' => $this->getProductsData(),
                'options' => $this->getOptionsData(),
            ];
        });

        return response()->json($data, 200, [
            'Cache-Control' => 'public, max-age=3600',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Flush all GTTD feed caches.
     */
    public static function flushCache(): void
    {
        Cache::forget('gttd_products_xml');
        Cache::forget('gttd_options_xml');
        Cache::forget('gttd_operators_xml');
        Cache::forget('gttd_unified_xml');
        Cache::forget('gttd_feed_json');
    }

    /**
     * Ensure the feed is enabled in settings.
     */
    protected function ensureFeedEnabled(): void
    {
        $enabled = Setting::where('setting_key', 'gttd_feed_enabled')->value('setting_value');
        if ($enabled !== null && $enabled === '0') {
            abort(404, 'Google Things To Do feed is currently disabled in Portal Settings.');
        }
    }

    /**
     * Operator metadata.
     */
    public function getOperatorData(): array
    {
        $settings = Setting::pluck('setting_value', 'setting_key');
        $placeId = $settings['google_place_id'] ?? 'ChIJbWsIEIVEdEER4uHEhb2dbcQ';
        $siteName = $settings['site_name'] ?? 'Dunes Discovery Tourism L.L.C.';
        $phone = $settings['site_phone'] ?? '+971 50 245 6056';
        $address = $settings['site_address'] ?? 'Al Fahidi, Bur Dubai, Dubai, United Arab Emirates';
        $postalCode = $settings['site_postal_code'] ?? '00000';
        $cid = $settings['google_cid'] ?? '14185012580795441634';

        return [
            'operator_id' => $placeId,
            'name' => $siteName,
            'google_business_profile_name' => $placeId,
            'google_cid' => $cid,
            'phone' => $phone,
            'url' => rtrim(url('/'), '/') . '/',
            'address' => [
                'street_address' => $address,
                'locality' => 'Dubai',
                'region' => 'Dubai',
                'country_code' => 'AE',
                'postal_code' => $postalCode,
            ],
            'license_number' => $settings['company_license_number'] ?? '1430583',
        ];
    }

    /**
     * Build Products list.
     */
    public function getProductsData(): array
    {
        $tours = Tour::where('status', 'active')
            ->with(['category', 'tiers', 'contentItems'])
            ->orderBy('priority', 'asc')
            ->get();

        $operator = $this->getOperatorData();
        $products = [];

        foreach ($tours as $tour) {
            $poi = $this->resolvePoiForTour($tour);
            $landingUrl = url('/tours/' . $tour->slug) . '?utm_source=google&utm_medium=things_to_do&utm_campaign=gttd_free';

            $heroImage = $tour->hero_image ?: $tour->thumb_image;
            $imageUrl = $heroImage ? (str_starts_with($heroImage, 'http') ? $heroImage : asset('images/' . ltrim($heroImage, '/'))) : asset('images/desert-safari-poster.avif');

            // Format duration
            $isoDuration = $this->toIsoDuration($tour->duration);

            // Clean description
            $desc = strip_tags($tour->short_desc ?: $tour->full_desc ?: $tour->name);
            $desc = trim(preg_replace('/\s+/', ' ', $desc));

            // Extract features / inclusions
            $features = [];
            if ($tour->contentItems && $tour->contentItems->count() > 0) {
                foreach ($tour->contentItems->take(5) as $ci) {
                    if (!empty($ci->title)) {
                        $features[] = strip_tags($ci->title);
                    }
                }
            }
            if (empty($features)) {
                $features = [
                    'Professional Licensed Safari Guide & Driver',
                    'Comfortable 4x4 Air-Conditioned Vehicle Pickup',
                    'Dune Bashing & Desert Photographic Opportunities',
                    'Instant Booking Confirmation & 24/7 Concierge Support',
                ];
            }

            $products[] = [
                'product_id' => 'dunes-tour-' . $tour->id,
                'operator_id' => $operator['operator_id'],
                'title' => $tour->name,
                'description' => $desc,
                'landing_page_url' => $landingUrl,
                'inventory_type' => 'EXPERIENCE',
                'category' => $tour->category?->name ?? 'Desert Safari',
                'duration' => $isoDuration,
                'duration_human' => $tour->duration ?: '6 Hours',
                'ratings' => [
                    'average_rating' => (float) ($tour->rating ?: 4.9),
                    'rating_count' => (int) ($tour->review_count ?: 2840),
                ],
                'location' => [
                    'poi_place_id' => $poi['place_id'],
                    'poi_name' => $poi['name'],
                    'latitude' => $poi['lat'],
                    'longitude' => $poi['lng'],
                    'locality' => 'Dubai',
                    'country_code' => 'AE',
                ],
                'media' => [
                    'image_url' => $imageUrl,
                ],
                'features' => $features,
                'is_bestseller' => (bool) $tour->is_bestseller,
                'is_featured' => (bool) $tour->is_featured,
            ];
        }

        return $products;
    }

    /**
     * Build Options & Pricing list.
     */
    public function getOptionsData(): array
    {
        $tours = Tour::where('status', 'active')
            ->with(['tiers'])
            ->orderBy('priority', 'asc')
            ->get();

        $options = [];

        foreach ($tours as $tour) {
            $productId = 'dunes-tour-' . $tour->id;
            $tourUrl = url('/tours/' . $tour->slug);

            if ($tour->tiers && $tour->tiers->count() > 0) {
                foreach ($tour->tiers as $tier) {
                    $priceNum = (float) ($tier->pivot->price ?? 0);
                    $oldPriceNum = (float) ($tier->pivot->old_price ?? 0);
                    $bookingUrl = $tourUrl . '?tier=' . ($tier->slug ?? $tier->id) . '&utm_source=google&utm_medium=things_to_do&utm_campaign=gttd_free';

                    $options[] = [
                        'option_id' => 'dunes-opt-' . $tour->id . '-' . $tier->id,
                        'product_id' => $productId,
                        'title' => $tour->name . ' - ' . ($tier->display_name ?: $tier->name),
                        'tier_slug' => $tier->slug,
                        'currency' => 'AED',
                        'amount' => number_format($priceNum, 2, '.', ''),
                        'price_micros' => $this->toMicros($priceNum),
                        'original_amount' => $oldPriceNum > $priceNum ? number_format($oldPriceNum, 2, '.', '') : null,
                        'original_price_micros' => $oldPriceNum > $priceNum ? $this->toMicros($oldPriceNum) : null,
                        'landing_page_url' => $bookingUrl,
                    ];
                }
            } else {
                // Fallback option if no tiers attached
                $bookingUrl = $tourUrl . '?utm_source=google&utm_medium=things_to_do&utm_campaign=gttd_free';
                $options[] = [
                    'option_id' => 'dunes-opt-' . $tour->id . '-default',
                    'product_id' => $productId,
                    'title' => $tour->name . ' - Standard Ticket',
                    'tier_slug' => 'standard',
                    'currency' => 'AED',
                    'amount' => '99.00',
                    'price_micros' => 99000000,
                    'original_amount' => '150.00',
                    'original_price_micros' => 150000000,
                    'landing_page_url' => $bookingUrl,
                ];
            }
        }

        return $options;
    }

    /**
     * Resolve Point of Interest for a given tour.
     */
    protected function resolvePoiForTour(Tour $tour): array
    {
        $slug = strtolower($tour->slug);
        $cat = strtolower($tour->category?->name ?? '');

        // 1. Water Sports / Dhow Cruise / Marina
        if (str_contains($slug, 'dhow') || str_contains($slug, 'cruise') || str_contains($slug, 'marina') || str_contains($cat, 'cruise')) {
            return [
                'name' => 'Dubai Marina & Dhow Cruise Canal',
                'place_id' => 'ChIJY3Y7ZvhDXz4RcKk7a-JqY6U',
                'lat' => 25.0805,
                'lng' => 55.1403,
            ];
        }

        // 2. City Tours / Downtown Dubai / Burj Khalifa
        if (str_contains($slug, 'city') || str_contains($slug, 'burj') || str_contains($cat, 'city')) {
            return [
                'name' => 'Downtown Dubai & Burj Khalifa',
                'place_id' => 'ChIJv_Q67d5DXz4R3M_4y_iN8t8',
                'lat' => 25.1972,
                'lng' => 55.2744,
            ];
        }

        // 3. Al Marmoom Desert Conservation Reserve
        if (str_contains($slug, 'marmoom') || str_contains($slug, 'conservation')) {
            return [
                'name' => 'Al Marmoom Desert Conservation Reserve',
                'place_id' => 'ChIJY8o_Z0mldj4RsJ1b9YJ-P7Q',
                'lat' => 24.8789,
                'lng' => 55.3370,
            ];
        }

        // 4. Default: Lahbab Red Dunes Dubai (Primary Safari Location)
        return [
            'name' => 'Lahbab Red Dunes Desert Dubai',
            'place_id' => 'ChIJt7e4-c9xdj4R_hY6W5xrqU8',
            'lat' => 24.9754,
            'lng' => 55.5928,
        ];
    }

    /**
     * Convert human duration to ISO 8601 duration (e.g. PT6H, PT45M).
     */
    public function toIsoDuration(?string $duration): string
    {
        if (empty($duration)) {
            return 'PT6H';
        }
        if (preg_match('/(\d+)\s*(hour|hr|h)/i', $duration, $m)) {
            return 'PT' . $m[1] . 'H';
        }
        if (preg_match('/(\d+)\s*(minute|min|m)/i', $duration, $m)) {
            return 'PT' . $m[1] . 'M';
        }
        if (stripos($duration, 'full day') !== false) {
            return 'PT10H';
        }
        if (stripos($duration, 'overnight') !== false) {
            return 'PT18H';
        }
        return 'PT6H';
    }

    /**
     * Build Products XML string.
     */
    protected function buildProductsXml(): string
    {
        $products = $this->getProductsData();
        $now = now()->toIso8601String();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<feed xmlns="http://www.google.com/schemas/things-to-do/1.0">' . "\n";
        $xml .= "  <metadata>\n";
        $xml .= "    <processing_instruction>PROCESS_ALL</processing_instruction>\n";
        $xml .= "    <generation_timestamp>{$now}</generation_timestamp>\n";
        $xml .= "  </metadata>\n";
        $xml .= "  <products>\n";

        foreach ($products as $p) {
            $title = $this->escapeXml($p['title']);
            $desc = $this->escapeXml($p['description']);
            $url = $this->escapeXml($p['landing_page_url']);
            $img = $this->escapeXml($p['media']['image_url']);
            $cat = $this->escapeXml($p['category']);

            $xml .= "    <product>\n";
            $xml .= "      <product_id>{$p['product_id']}</product_id>\n";
            $xml .= "      <operator_id>{$p['operator_id']}</operator_id>\n";
            $xml .= "      <inventory_types>\n";
            $xml .= "        <inventory_type>{$p['inventory_type']}</inventory_type>\n";
            $xml .= "      </inventory_types>\n";
            $xml .= "      <title lang=\"en\">{$title}</title>\n";
            $xml .= "      <description lang=\"en\">{$desc}</description>\n";
            $xml .= "      <category>{$cat}</category>\n";
            $xml .= "      <landing_page_url>{$url}</landing_page_url>\n";
            $xml .= "      <duration>{$p['duration']}</duration>\n";
            $xml .= "      <ratings>\n";
            $xml .= "        <average_rating>{$p['ratings']['average_rating']}</average_rating>\n";
            $xml .= "        <rating_count>{$p['ratings']['rating_count']}</rating_count>\n";
            $xml .= "      </ratings>\n";
            $xml .= "      <location>\n";
            $xml .= "        <place_id>{$p['location']['poi_place_id']}</place_id>\n";
            $xml .= "        <lat_lng>\n";
            $xml .= "          <latitude>{$p['location']['latitude']}</latitude>\n";
            $xml .= "          <longitude>{$p['location']['longitude']}</longitude>\n";
            $xml .= "        </lat_lng>\n";
            $xml .= "        <locality>{$p['location']['locality']}</locality>\n";
            $xml .= "        <country_code>{$p['location']['country_code']}</country_code>\n";
            $xml .= "      </location>\n";
            $xml .= "      <media>\n";
            $xml .= "        <image>\n";
            $xml .= "          <url>{$img}</url>\n";
            $xml .= "        </image>\n";
            $xml .= "      </media>\n";
            $xml .= "      <features>\n";
            foreach ($p['features'] as $f) {
                $fEsc = $this->escapeXml($f);
                $xml .= "        <feature>{$fEsc}</feature>\n";
            }
            $xml .= "      </features>\n";
            $xml .= "    </product>\n";
        }

        $xml .= "  </products>\n";
        $xml .= '</feed>';

        return $xml;
    }

    /**
     * Build Options XML string.
     */
    protected function buildOptionsXml(): string
    {
        $options = $this->getOptionsData();
        $now = now()->toIso8601String();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<feed xmlns="http://www.google.com/schemas/things-to-do/1.0">' . "\n";
        $xml .= "  <metadata>\n";
        $xml .= "    <processing_instruction>PROCESS_ALL</processing_instruction>\n";
        $xml .= "    <generation_timestamp>{$now}</generation_timestamp>\n";
        $xml .= "  </metadata>\n";
        $xml .= "  <options>\n";

        foreach ($options as $opt) {
            $title = $this->escapeXml($opt['title']);
            $url = $this->escapeXml($opt['landing_page_url']);

            $xml .= "    <option>\n";
            $xml .= "      <option_id>{$opt['option_id']}</option_id>\n";
            $xml .= "      <product_id>{$opt['product_id']}</product_id>\n";
            $xml .= "      <title lang=\"en\">{$title}</title>\n";
            $xml .= "      <pricing>\n";
            $xml .= "        <currency>{$opt['currency']}</currency>\n";
            $xml .= "        <price_micros>{$opt['price_micros']}</price_micros>\n";
            $xml .= "        <amount>{$opt['amount']}</amount>\n";
            if (!empty($opt['original_amount'])) {
                $xml .= "        <original_amount>{$opt['original_amount']}</original_amount>\n";
                $xml .= "        <original_price_micros>{$opt['original_price_micros']}</original_price_micros>\n";
            }
            $xml .= "      </pricing>\n";
            $xml .= "      <landing_page_url>{$url}</landing_page_url>\n";
            $xml .= "    </option>\n";
        }

        $xml .= "  </options>\n";
        $xml .= '</feed>';

        return $xml;
    }

    /**
     * Build Operators XML string.
     */
    protected function buildOperatorsXml(): string
    {
        $op = $this->getOperatorData();
        $now = now()->toIso8601String();

        $name = $this->escapeXml($op['name']);
        $phone = $this->escapeXml($op['phone']);
        $url = $this->escapeXml($op['url']);
        $street = $this->escapeXml($op['address']['street_address']);
        $locality = $this->escapeXml($op['address']['locality']);
        $region = $this->escapeXml($op['address']['region']);
        $country = $this->escapeXml($op['address']['country_code']);
        $postal = $this->escapeXml($op['address']['postal_code']);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<feed xmlns="http://www.google.com/schemas/things-to-do/1.0">' . "\n";
        $xml .= "  <metadata>\n";
        $xml .= "    <processing_instruction>PROCESS_ALL</processing_instruction>\n";
        $xml .= "    <generation_timestamp>{$now}</generation_timestamp>\n";
        $xml .= "  </metadata>\n";
        $xml .= "  <operators>\n";
        $xml .= "    <operator>\n";
        $xml .= "      <operator_id>{$op['operator_id']}</operator_id>\n";
        $xml .= "      <name>{$name}</name>\n";
        $xml .= "      <google_business_profile_name>{$op['google_business_profile_name']}</google_business_profile_name>\n";
        $xml .= "      <phone>{$phone}</phone>\n";
        $xml .= "      <url>{$url}</url>\n";
        $xml .= "      <address>\n";
        $xml .= "        <street_address>{$street}</street_address>\n";
        $xml .= "        <locality>{$locality}</locality>\n";
        $xml .= "        <region>{$region}</region>\n";
        $xml .= "        <country_code>{$country}</country_code>\n";
        $xml .= "        <postal_code>{$postal}</postal_code>\n";
        $xml .= "      </address>\n";
        $xml .= "    </operator>\n";
        $xml .= "  </operators>\n";
        $xml .= '</feed>';

        return $xml;
    }

    /**
     * Build Unified Consolidated XML string.
     */
    protected function buildUnifiedXml(): string
    {
        $now = now()->toIso8601String();
        $op = $this->getOperatorData();
        $products = $this->getProductsData();
        $options = $this->getOptionsData();

        $name = $this->escapeXml($op['name']);
        $phone = $this->escapeXml($op['phone']);
        $url = $this->escapeXml($op['url']);
        $street = $this->escapeXml($op['address']['street_address']);
        $locality = $this->escapeXml($op['address']['locality']);
        $region = $this->escapeXml($op['address']['region']);
        $country = $this->escapeXml($op['address']['country_code']);
        $postal = $this->escapeXml($op['address']['postal_code']);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<feed xmlns="http://www.google.com/schemas/things-to-do/1.0">' . "\n";
        $xml .= "  <metadata>\n";
        $xml .= "    <processing_instruction>PROCESS_ALL</processing_instruction>\n";
        $xml .= "    <generation_timestamp>{$now}</generation_timestamp>\n";
        $xml .= "  </metadata>\n";

        // Operators block
        $xml .= "  <operators>\n";
        $xml .= "    <operator>\n";
        $xml .= "      <operator_id>{$op['operator_id']}</operator_id>\n";
        $xml .= "      <name>{$name}</name>\n";
        $xml .= "      <google_business_profile_name>{$op['google_business_profile_name']}</google_business_profile_name>\n";
        $xml .= "      <phone>{$phone}</phone>\n";
        $xml .= "      <url>{$url}</url>\n";
        $xml .= "      <address>\n";
        $xml .= "        <street_address>{$street}</street_address>\n";
        $xml .= "        <locality>{$locality}</locality>\n";
        $xml .= "        <region>{$region}</region>\n";
        $xml .= "        <country_code>{$country}</country_code>\n";
        $xml .= "        <postal_code>{$postal}</postal_code>\n";
        $xml .= "      </address>\n";
        $xml .= "    </operator>\n";
        $xml .= "  </operators>\n";

        // Products block
        $xml .= "  <products>\n";
        foreach ($products as $p) {
            $title = $this->escapeXml($p['title']);
            $desc = $this->escapeXml($p['description']);
            $pUrl = $this->escapeXml($p['landing_page_url']);
            $img = $this->escapeXml($p['media']['image_url']);
            $cat = $this->escapeXml($p['category']);

            $xml .= "    <product>\n";
            $xml .= "      <product_id>{$p['product_id']}</product_id>\n";
            $xml .= "      <operator_id>{$p['operator_id']}</operator_id>\n";
            $xml .= "      <inventory_types>\n";
            $xml .= "        <inventory_type>{$p['inventory_type']}</inventory_type>\n";
            $xml .= "      </inventory_types>\n";
            $xml .= "      <title lang=\"en\">{$title}</title>\n";
            $xml .= "      <description lang=\"en\">{$desc}</description>\n";
            $xml .= "      <category>{$cat}</category>\n";
            $xml .= "      <landing_page_url>{$pUrl}</landing_page_url>\n";
            $xml .= "      <duration>{$p['duration']}</duration>\n";
            $xml .= "      <ratings>\n";
            $xml .= "        <average_rating>{$p['ratings']['average_rating']}</average_rating>\n";
            $xml .= "        <rating_count>{$p['ratings']['rating_count']}</rating_count>\n";
            $xml .= "      </ratings>\n";
            $xml .= "      <location>\n";
            $xml .= "        <place_id>{$p['location']['poi_place_id']}</place_id>\n";
            $xml .= "        <lat_lng>\n";
            $xml .= "          <latitude>{$p['location']['latitude']}</latitude>\n";
            $xml .= "          <longitude>{$p['location']['longitude']}</longitude>\n";
            $xml .= "        </lat_lng>\n";
            $xml .= "        <locality>{$p['location']['locality']}</locality>\n";
            $xml .= "        <country_code>{$p['location']['country_code']}</country_code>\n";
            $xml .= "      </location>\n";
            $xml .= "      <media>\n";
            $xml .= "        <image>\n";
            $xml .= "          <url>{$img}</url>\n";
            $xml .= "        </image>\n";
            $xml .= "      </media>\n";
            $xml .= "      <features>\n";
            foreach ($p['features'] as $f) {
                $fEsc = $this->escapeXml($f);
                $xml .= "        <feature>{$fEsc}</feature>\n";
            }
            $xml .= "      </features>\n";
            $xml .= "    </product>\n";
        }
        $xml .= "  </products>\n";

        // Options block
        $xml .= "  <options>\n";
        foreach ($options as $opt) {
            $optTitle = $this->escapeXml($opt['title']);
            $optUrl = $this->escapeXml($opt['landing_page_url']);

            $xml .= "    <option>\n";
            $xml .= "      <option_id>{$opt['option_id']}</option_id>\n";
            $xml .= "      <product_id>{$opt['product_id']}</product_id>\n";
            $xml .= "      <title lang=\"en\">{$optTitle}</title>\n";
            $xml .= "      <pricing>\n";
            $xml .= "        <currency>{$opt['currency']}</currency>\n";
            $xml .= "        <price_micros>{$opt['price_micros']}</price_micros>\n";
            $xml .= "        <amount>{$opt['amount']}</amount>\n";
            if (!empty($opt['original_amount'])) {
                $xml .= "        <original_amount>{$opt['original_amount']}</original_amount>\n";
                $xml .= "        <original_price_micros>{$opt['original_price_micros']}</original_price_micros>\n";
            }
            $xml .= "      </pricing>\n";
            $xml .= "      <landing_page_url>{$optUrl}</landing_page_url>\n";
            $xml .= "    </option>\n";
        }
        $xml .= "  </options>\n";

        $xml .= '</feed>';

        return $xml;
    }

    /**
     * Escape XML special characters.
     */
    protected function escapeXml(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * Convert float amount to exact micro-units (1 AED = 1,000,000 micros).
     */
    protected function toMicros(float $amount): int
    {
        if (function_exists('bcmul')) {
            return (int) bcmul(number_format($amount, 2, '.', ''), '1000000', 0);
        }

        return (int) round($amount * 1000000);
    }
}
