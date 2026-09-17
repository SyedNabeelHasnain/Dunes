<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Faq;
use App\Models\FaqAssignment;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TourController extends Controller
{
    /**
     * Display the tour catalog.
     */
    public function index(Request $request)
    {
        if ($request->filled('q') || $request->filled('search')) {
            $searchTerm = trim((string) ($request->input('q') ?? $request->input('search')));
            return redirect()->route('tours.search', ['q' => $searchTerm]);
        }

        $selectedCategorySlug = $request->input('category');
        $categories = Category::orderBy('priority', 'asc')->get();
        $query = Tour::where('status', 'active')->with(['tiers', 'category']);
        
        if ($selectedCategorySlug) {
            $category = Category::where('slug', $selectedCategorySlug)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }
        
        $tours = $query->orderBy('priority', 'asc')->get();
        
        $settingsService = app(\App\Services\SettingsService::class);
        $currentYear = date('Y');
        $defaultTitle = "Top Dubai Desert Safari & City Tours ({$currentYear}) | Best Deals | Dunes Discovery";
        $defaultDesc = "Explore top-rated Dubai desert safaris, 1000cc dune buggy rentals, dhow cruise dinners, and luxury Abu Dhabi city tours. Instant confirmation & 24h free cancellation.";
        $defaultKeys = "dubai desert safari tours, dune buggy dubai, quad biking dubai, dhow cruise dubai, abu dhabi city tour";

        $pageTitle = $settingsService->get('seo_tours_title') ?: $defaultTitle;
        $pageDesc = $settingsService->get('seo_tours_description') ?: $defaultDesc;
        $pageKeys = $settingsService->get('seo_tours_keywords') ?: $defaultKeys;
        $ogImageSetting = $settingsService->get('seo_tours_og_image');
        $ogImage = $ogImageSetting ? asset(ltrim($ogImageSetting, '/')) : asset('images/desert-safari-poster.avif');
        $canonical = route('tours.index');
        
        return view('tours.index', compact('categories', 'tours', 'selectedCategorySlug', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage'));
    }

    public function showBuggy()
    {
        return $this->show('dune-buggy-rental-dubai');
    }

    /**
     * Display details for a specific tour.
     */
    public function show(string $slug)
    {
        $query = Tour::where('slug', $slug)
            ->with(['itineraries', 'tiers', 'addons', 'contentItems', 'category']);

        if (!auth()->check()) {
            $query->where('status', 'active');
        }

        $tour = $query->first();

        if (!$tour) {
            abort(404);
        }

        // Separate content items by type
        $highlights = $tour->contentItems->where('type', 'highlight')->sortBy('priority');
        $inclusions = $tour->contentItems->where('type', 'inclusion')->sortBy('priority');
        $exclusions = $tour->contentItems->where('type', 'exclusion')->sortBy('priority');

        // Fetch assigned FAQs
        $faqIds = FaqAssignment::where('entity_type', 'tour')
            ->where('entity_id', $tour->id)
            ->pluck('faq_id');
            
        $faqs = Faq::whereIn('id', $faqIds)
            ->where('status', 'active')
            ->orderBy('priority', 'asc')
            ->get();

        // Fallback to general FAQs if no specific tour FAQs are assigned
        if ($faqs->isEmpty()) {
            $generalFaqIds = FaqAssignment::where('entity_type', 'general')
                ->pluck('faq_id');
                
            $faqs = Faq::whereIn('id', $generalFaqIds)
                ->where('status', 'active')
                ->orderBy('priority', 'asc')
                ->limit(6)
                ->get();
        }

        // Fetch related tours (same category, excluding current tour)
        $relatedTours = Tour::where('category_id', $tour->category_id)
            ->where('id', '!=', $tour->id)
            ->where('status', 'active')
            ->with(['tiers', 'category'])
            ->orderBy('priority', 'asc')
            ->limit(3)
            ->get();

        // If not enough related tours, fill with other featured/bestseller tours
        if ($relatedTours->count() < 3) {
            $extraTours = Tour::where('id', '!=', $tour->id)
                ->where('status', 'active')
                ->whereNotIn('id', $relatedTours->pluck('id'))
                ->with(['tiers', 'category'])
                ->orderBy('is_featured', 'desc')
                ->orderBy('priority', 'asc')
                ->limit(3 - $relatedTours->count())
                ->get();
                
            $relatedTours = $relatedTours->concat($extraTours);
        }

        // Dynamic High-CTR SEO Metadata
        $minPrice = $tour->tiers->min('pivot.price') ?? 0;
        $currentYear = date('Y');
        
        $priceText = $minPrice > 0 ? "From AED " . number_format($minPrice) : "Best Rates";
        $pageTitle = $tour->meta_title ?: "{$tour->name} Dubai {$currentYear}: {$priceText} | Dunes Discovery";
        
        $defaultDesc = "Book {$tour->name} in Dubai. Luxury 4x4 Land Cruiser transfers, live BBQ dining, thrilling dune bashing, and 24/7 WhatsApp assistance. Instant confirmation {$priceText}.";
        $pageDesc = $tour->meta_desc ?: (strlen($tour->short_desc ?? '') > 50 ? strip_tags($tour->short_desc) : $defaultDesc);
        
        $pageKeys = $tour->meta_keywords ?: strtolower("{$tour->name}, {$tour->name} dubai, book {$tour->name}, desert safari dubai, dubai tours {$currentYear}");
        $canonical = url('/' . $tour->slug);
        
        $imgFile = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $tour->hero_image ?: 'evening-desert-safari-dubai-dune-discovery-tourism.avif');
        $ogImage = asset('images/' . $imgFile);

        // Top verified reviews for Schema.org review rich snippets
        $approvedReviews = \App\Models\Review::where('status', 'approved')
            ->where('rating', '>=', 4.5)
            ->latest('published_date')
            ->take(5)
            ->get();

        // Track form load timestamp for analytics
        session(["form_load.booking_{$tour->id}" => microtime(true)]);

        return view('tours.show', compact('tour', 'highlights', 'inclusions', 'exclusions', 'faqs', 'relatedTours', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage', 'minPrice', 'approvedReviews'));
    }

    /**
     * Search tours with dynamic SERP query matching & GEO intent optimization.
     */
    public function search(Request $request)
    {
        $rawQuery = (string) ($request->input('q') ?? $request->input('search') ?? '');
        $cleanQuery = trim(preg_replace('/[^\p{L}\p{N}\s\-\+\&]/u', ' ', strip_tags($rawQuery)));
        $cleanQuery = preg_replace('/\s+/', ' ', $cleanQuery);

        if (empty($cleanQuery)) {
            return redirect()->route('tours.index');
        }

        $categories = Category::orderBy('priority', 'asc')->get();
        $allActiveTours = Tour::where('status', 'active')->with(['tiers', 'category'])->get();

        // 1. Intent Detection
        $intent = $this->analyzeSearchIntent($cleanQuery);

        // 2. Score and Rank Tours based on Search Query & Intent
        $scoredTours = $this->scoreAndRankTours($allActiveTours, $cleanQuery, $intent);

        $isFallback = false;
        if ($scoredTours->isEmpty()) {
            $isFallback = true;
            // Graceful fallback to top 3 active bestsellers
            $tours = Tour::where('status', 'active')
                ->where(function ($q) {
                    $q->where('is_bestseller', true)->orWhere('is_featured', true);
                })
                ->with(['tiers', 'category'])
                ->orderBy('priority', 'asc')
                ->take(3)
                ->get();

            if ($tours->isEmpty()) {
                $tours = Tour::where('status', 'active')->with(['tiers', 'category'])->take(3)->get();
            }
        } else {
            $tours = $scoredTours;
        }

        // 3. Dynamic Price Calculation
        $minPrice = $tours->flatMap->tiers->min('pivot.price') ?? 120;
        if ($minPrice <= 0) {
            $minPrice = 120;
        }

        $displayQuery = Str::headline($cleanQuery);
        $currentYear = date('Y');

        // 4. Exact-Query SERP Snippets (Triggers Google Query Bolding + Higher CTR)
        $priceText = "from AED " . number_format($minPrice);
        $pageTitle = "{$displayQuery} Dubai ({$currentYear} Deals {$priceText}) | Dunes Discovery";
        $pageDesc = "Looking for {$displayQuery}? Compare verified Dubai desert safari packages with 4x4 hotel pickup, 5-star live BBQ dinner, and instant confirmation. DET Licensed #1430583.";
        $pageKeys = strtolower("{$cleanQuery}, {$cleanQuery} dubai, best {$cleanQuery} dubai, desert safari dubai, dunes discovery");
        $canonical = route('tours.search', ['q' => $cleanQuery]);
        $robotsMeta = $isFallback ? 'noindex, follow' : 'index, follow, max-snippet:-1, max-image-preview:large';
        $pageRobots = $robotsMeta;

        $ogImage = asset('images/desert-safari-poster.avif');
        if ($tours->isNotEmpty() && $tours->first()->hero_image) {
            $imgFile = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $tours->first()->hero_image);
            $ogImage = asset('images/' . $imgFile);
        }

        // 5. Generative Engine Optimization (GEO) Direct-Answer Synthesis
        $aiOverview = $this->generateGeoDirectAnswer($cleanQuery, $intent, $minPrice, $tours->count());
        if ($isFallback) {
            $aiOverview['title'] = "Recommended Dubai Desert Safaris (Bestsellers)";
            $aiOverview['summary'] = "While no specific package directly matches \"{$displayQuery}\", here are Dubai's highest-rated desert safari adventures. Each package includes 4x4 hotel transfers, red dune bashing in Lahbab, 5-star halal live BBQ dinner, and live entertainment shows.";
        }

        return view('tours.search', compact(
            'cleanQuery',
            'displayQuery',
            'tours',
            'categories',
            'intent',
            'isFallback',
            'minPrice',
            'aiOverview',
            'pageTitle',
            'pageDesc',
            'pageKeys',
            'canonical',
            'robotsMeta',
            'pageRobots',
            'ogImage'
        ));
    }

    /**
     * Analyze search query intent vector for Generative Engine Optimization (GEO).
     */
    protected function analyzeSearchIntent(string $query): array
    {
        $q = strtolower($query);

        // Quad Biking / ATV
        if (preg_match('/\b(quad|atv|four wheeler|quads|yamaha|raptor)\b/i', $q)) {
            return [
                'type' => 'quad_biking',
                'badge' => 'Quad Biking & ATV Adventures',
                'icon' => 'bi-speedometer2',
                'pills' => ['250cc Quad', '350cc Raptor', 'Quad + Evening BBQ', 'Lahbab Red Dunes', 'Under AED 250'],
                'ai_title' => 'Quad Biking Safaris in Dubai: Key Highlights & Rates',
                'summary' => 'Quad biking safaris in Dubai feature high-performance 250cc-400cc automatic ATVs in the open high red dunes of Lahbab. No driver\'s license is required for ages 16+. Packages include full protective gear (helmet, goggles), professional guide marshals, and optional 4x4 Land Cruiser hotel pickup.',
                'specs' => [
                    'Engine Sizes' => '250cc – 400cc Automatic',
                    'License Required' => 'No (Age 16+)',
                    'Location' => 'Lahbab Red Dunes',
                    'Safety Gear' => 'Helmets & Goggles Included',
                ]
            ];
        }

        // Dune Buggy
        if (preg_match('/\b(buggy|buggies|can-am|canam|polaris|rzr|maverick)\b/i', $q)) {
            return [
                'type' => 'dune_buggy',
                'badge' => 'High-Power 1000cc Dune Buggies',
                'icon' => 'bi-ev-front',
                'pills' => ['1000cc Can-Am Maverick', 'Polaris RZR XP', '2-Seater Buggy', '4-Seater Buggy', 'Buggy + Dinner Show'],
                'ai_title' => 'Dubai Dune Buggy Rentals: Specifications & Experience Guide',
                'summary' => 'Dune buggy tours in Dubai provide high-octane 1000cc Can-Am Maverick X3 Turbo and Polaris RZR XP self-drive expeditions across Lahbab Desert. Equipped with full tubular roll cages, 4-point racing harnesses, automatic transmission, and sandboarding.',
                'specs' => [
                    'Engine Power' => '1000cc Turbo (195-200 HP)',
                    'Seater Capacity' => '1, 2, or 4 Passengers',
                    'License Required' => 'No (Age 16+)',
                    'Terrain' => 'High Lahbab Desert Dunes',
                ]
            ];
        }

        // VIP / Luxury / Private
        if (preg_match('/\b(vip|luxury|private|premium|royal|exclusive|table service)\b/i', $q)) {
            return [
                'type' => 'vip_luxury',
                'badge' => 'VIP & Private Desert Safaris',
                'icon' => 'bi-gem',
                'pills' => ['Private 4x4 Land Cruiser', 'VIP Table Service', 'Falconry Experience', '5-Star Live BBQ Buffet'],
                'ai_title' => 'VIP Desert Safari in Dubai: Exclusive Services & Camp Perks',
                'summary' => 'VIP Desert Safari packages in Dubai upgrade standard safaris with private door-to-door 4x4 Land Cruiser pickup, reserved air-conditioned VIP lounge seating, and live table-served 5-star international BBQ dining with dedicated waiter service.',
                'specs' => [
                    'Transfer Type' => 'Private 4x4 Land Cruiser',
                    'Camp Seating' => 'Reserved VIP Lounge Area',
                    'Dinner Service' => 'Live Table Waiter Service',
                    'Entertainment' => 'Front-Row Fire & Tanoura Shows',
                ]
            ];
        }

        // Morning / Sunrise
        if (preg_match('/\b(morning|sunrise|early|dawn)\b/i', $q)) {
            return [
                'type' => 'morning',
                'badge' => 'Morning Desert Safari & Sunrise',
                'icon' => 'bi-sunrise',
                'pills' => ['4x4 Dune Bashing', 'Camel Trekking', 'Sandboarding', 'Sunrise Photo Stop', 'Under AED 150'],
                'ai_title' => 'Morning Desert Safari in Dubai: Timing, Itinerary & Benefits',
                'summary' => 'Morning Desert Safaris operate daily from 8:00 AM to 12:30 PM, taking advantage of pleasant morning temperatures and crisp desert light. Includes 45 minutes of extreme red dune bashing, camel rides, sandboarding, and hotel pickup.',
                'specs' => [
                    'Schedule' => '8:00 AM – 12:30 PM Daily',
                    'Dune Bashing' => '45 Minutes (Lahbab)',
                    'Activities' => 'Sandboarding & Camel Rides',
                    'Ideal For' => 'Early Risers & Quick Layovers',
                ]
            ];
        }

        // Overnight
        if (preg_match('/\b(overnight|camping|camp|stargazing|sleep)\b/i', $q)) {
            return [
                'type' => 'overnight',
                'badge' => 'Overnight Desert Safari & Camping',
                'icon' => 'bi-moon-stars',
                'pills' => ['Bedouin Tent Stay', 'Campfire Stargazing', 'Arabic Breakfast', 'Sunrise Photography'],
                'ai_title' => 'Overnight Dubai Desert Safari: Bedouin Camping & Stargazing',
                'summary' => 'Overnight Desert Safaris feature the full evening safari program (dune bashing, live shows, BBQ dinner) followed by peaceful desert camping in Bedouin tents with sleeping bags, campfire stargazing, Arabian breakfast at sunrise, and morning dune trekking.',
                'specs' => [
                    'Duration' => '17 Hours (3:00 PM – 8:30 AM Next Day)',
                    'Accommodation' => 'Private Bedouin Tents & Mattresses',
                    'Meals Included' => '5-Star BBQ Dinner + Fresh Breakfast',
                    'Experience' => 'Night Stargazing & Sunrise Views',
                ]
            ];
        }

        // Dhow Cruise & Water
        if (preg_match('/\b(dhow|cruise|boat|marina cruise|creek cruise|yacht)\b/i', $q)) {
            return [
                'type' => 'dhow_cruise',
                'badge' => 'Dubai Marina & Creek Dinner Cruises',
                'icon' => 'bi-water',
                'pills' => ['Dubai Marina 5-Star', 'Dubai Creek Classic', 'Upper Deck Seating', 'Live Tanoura Show'],
                'ai_title' => 'Dubai Marina & Creek Dhow Cruise: Dinner & Skyline Sights',
                'summary' => 'Dhow cruise dinners glide past Dubai Marina\'s skyscrapers or historic Dubai Creek. Enjoy 2 hours of scenic cruising, a 5-star international buffet dinner with vegetarian & non-vegetarian dishes, soft beverages, and live Tanoura dancing.',
                'specs' => [
                    'Cruising Time' => '8:30 PM – 10:30 PM (2 Hours)',
                    'Dining' => '5-Star International Buffet',
                    'Atmosphere' => 'Open-Air Upper Deck & AC Lower Deck',
                    'Entertainment' => 'Traditional Live Tanoura Dance',
                ]
            ];
        }

        // Default Evening / General Desert Safari
        return [
            'type' => 'evening_safari',
            'badge' => 'Dubai Desert Safari Experiences',
            'icon' => 'bi-sun-fill',
            'pills' => ['4x4 Hotel Pickup', 'Halal Live BBQ Dinner', '3 Live Entertainment Shows', 'Camel Ride & Sandboarding', 'VIP Table Upgrade'],
            'ai_title' => 'Dubai Desert Safari Tour Guide: What is Included & What to Expect',
            'summary' => 'The quintessential Dubai desert safari features 4x4 Land Cruiser hotel pickup, 40-45 minutes of exhilarating dune bashing across Lahbab Red Dunes, camel riding, sandboarding, 3 live stage shows (Tanoura, Fire Show, Belly Dance), and an open 5-star live BBQ dinner buffet.',
            'specs' => [
                'Operating Hours' => '2:30 PM – 9:30 PM Daily',
                'Pickup & Dropoff' => 'Door-to-Door 4x4 Hotel Transfers',
                'Dining' => '5-Star Live BBQ Buffet (100% Halal)',
                'License' => 'DET Licensed Operator #1430583',
            ]
        ];
    }

    /**
     * Score and rank active tours by multi-field elastic match.
     */
    protected function scoreAndRankTours($allTours, string $query, array $intent)
    {
        $qLower = strtolower($query);
        $stopWords = ['in', 'the', 'a', 'an', 'to', 'for', 'of', 'and', 'dubai', 'tour', 'tours', 'best', 'deals', 'packages'];
        $tokens = array_filter(explode(' ', $qLower), function ($t) use ($stopWords) {
            return strlen($t) > 1 && !in_array($t, $stopWords);
        });

        $scored = [];

        foreach ($allTours as $tour) {
            $score = 0;
            $tName = strtolower($tour->name);
            $tDesc = strtolower(($tour->short_desc ?? '') . ' ' . ($tour->full_desc ?? ''));
            $tKeys = strtolower($tour->meta_keywords ?? '');
            $catSlug = strtolower($tour->category ? $tour->category->slug : '');
            $catName = strtolower($tour->category ? $tour->category->name : '');
            $tierNames = strtolower($tour->tiers->pluck('name')->implode(' '));

            // Exact phrase matches
            if (str_contains($tName, $qLower)) {
                $score += 60;
            }
            if (str_contains($tKeys, $qLower)) {
                $score += 35;
            }
            if (str_contains($catSlug, $qLower) || str_contains($catName, $qLower)) {
                $score += 30;
            }

            // Token matches
            foreach ($tokens as $token) {
                if (str_contains($tName, $token)) {
                    $score += 15;
                }
                if (str_contains($tKeys, $token)) {
                    $score += 10;
                }
                if (str_contains($catSlug, $token) || str_contains($catName, $token)) {
                    $score += 10;
                }
                if (str_contains($tierNames, $token)) {
                    $score += 8;
                }
                if (str_contains($tDesc, $token)) {
                    $score += 4;
                }
            }

            // Only apply intent boost & quality boost if the tour actually matched the search terms
            $hasTermMatch = (
                str_contains($tName, $qLower) ||
                str_contains($tKeys, $qLower) ||
                str_contains($catSlug, $qLower) ||
                str_contains($catName, $qLower)
            );

            if (!$hasTermMatch) {
                foreach ($tokens as $token) {
                    if (str_contains($tName, $token) || str_contains($tKeys, $token) || str_contains($catSlug, $token) || str_contains($catName, $token) || str_contains($tierNames, $token) || str_contains($tDesc, $token)) {
                        $hasTermMatch = true;
                        break;
                    }
                }
            }

            if ($hasTermMatch) {
                // Intent-specific boosts
                if ($intent['type'] === 'quad_biking' && (str_contains($tName, 'quad') || str_contains($tName, 'atv') || str_contains($tierNames, 'quad'))) {
                    $score += 40;
                }
                if ($intent['type'] === 'dune_buggy' && (str_contains($tName, 'buggy') || str_contains($tName, 'can-am') || str_contains($tName, 'polaris'))) {
                    $score += 40;
                }
                if ($intent['type'] === 'vip_luxury' && (str_contains($tName, 'vip') || str_contains($tName, 'private') || str_contains($tierNames, 'vip'))) {
                    $score += 35;
                }
                if ($intent['type'] === 'morning' && str_contains($tName, 'morning')) {
                    $score += 35;
                }
                if ($intent['type'] === 'overnight' && str_contains($tName, 'overnight')) {
                    $score += 35;
                }
                if ($intent['type'] === 'dhow_cruise' && (str_contains($tName, 'cruise') || str_contains($tName, 'dhow') || $catSlug === 'water-activity')) {
                    $score += 35;
                }

                // Quality score boosts
                if ($tour->is_bestseller) {
                    $score += 5;
                }
                if ($tour->is_featured) {
                    $score += 3;
                }

                $scored[] = [
                    'tour' => $tour,
                    'score' => $score,
                ];
            }
        }

        usort($scored, function ($a, $b) {
            if ($a['score'] === $b['score']) {
                return $a['tour']->priority <=> $b['tour']->priority;
            }
            return $b['score'] <=> $a['score'];
        });

        return collect(array_column($scored, 'tour'));
    }

    /**
     * Synthesize GEO direct-answer metadata for search engines & AI overviews.
     */
    protected function generateGeoDirectAnswer(string $query, array $intent, float $minPrice, int $resultCount): array
    {
        $formattedPrice = number_format($minPrice);
        return [
            'title' => $intent['ai_title'],
            'summary' => $intent['summary'],
            'quick_stats' => array_merge($intent['specs'], [
                'Starting Price' => "From AED {$formattedPrice}",
                'Verified Packages' => "{$resultCount} Available",
                'Operator License' => 'DET #1430583 (Govt. Approved)'
            ]),
            'verified_note' => "All tours operated by Dunes Discovery Tourism LLC are licensed by the Dubai Department of Economy and Tourism (DET License #1430583) and include 24-hour free cancellation and instant confirmation."
        ];
    }

    /**
     * Display the interactive Build Your Own Safari Customizer.
     */
    public function customizer(Request $request)
    {
        $allTours = Tour::where('status', 'active')->with(['tiers', 'addons'])->orderBy('priority', 'asc')->get();
        $categories = Category::orderBy('priority', 'asc')->get();

        $pageTitle = "Build Your Own Dubai Desert Safari (Customizer 2026) | Dunes Discovery";
        $pageDesc = "Customize your bespoke Dubai desert safari experience. Configure private Land Cruisers, 1000cc Can-Am buggies, 400cc quad bikes, and VIP waiter table service with live real-time pricing.";
        $pageKeys = "custom desert safari dubai, build your own safari dubai, bespoke desert safari, private land cruiser safari, vip desert safari customizer";
        $canonical = route('tours.customizer');
        $ogImage = asset('images/desert-safari-poster.avif');

        return view('tours.customizer', compact('allTours', 'categories', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage'));
    }
}
