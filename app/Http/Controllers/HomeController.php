<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Faq;
use App\Models\FaqAssignment;
use App\Models\Review;
use App\Models\Tour;
use App\Services\ReviewMediaGalleryService;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    /**
     * Show the website front home page.
     */
    public function index()
    {
        try {
            $categories = Category::with(['tours' => function ($query) {
                $query->where('status', 'active')->with(['tiers', 'category'])->orderBy('priority', 'asc');
            }])->orderBy('priority', 'asc')->get();
        } catch (\Throwable $e) {
            $categories = collect();
        }

        try {
            $bestsellers = Tour::where('status', 'active')
                ->where('is_bestseller', true)
                ->with(['tiers', 'category'])
                ->orderBy('priority', 'asc')
                ->get();
        } catch (\Throwable $e) {
            $bestsellers = collect();
        }

        try {
            $reviews = Cache::remember('site_home_reviews_feed', 3600, function () {
                $featured = collect();

                try {
                    // 1. Primary DB query: approved reviews with valid text & reviewer name
                    $dbReviews = Review::where('status', 'approved')
                        ->whereNotNull('review_text')
                        ->where('review_text', '!=', '')
                        ->whereNotNull('reviewer_name')
                        ->where('reviewer_name', '!=', '')
                        ->where('rating', '>=', 4.0)
                        ->orderByRaw('is_featured DESC, rating DESC, published_date DESC')
                        ->limit(40)
                        ->get();

                    $featured = collect($dbReviews);
                } catch (\Throwable $e) {
                    $featured = collect();
                }

                $googleCount = $featured->where('source', 'google')->count();
                $tripCount = $featured->where('source', 'tripadvisor')->count();

                // 2. Resilient Fallback: If DB returned fewer than 12 reviews for either source, backfill from authentic reviews.json
                if ($googleCount < 12 || $tripCount < 12 || $featured->count() < 24) {
                    $jsonPath = database_path('seeders/data/reviews.json');
                    if (file_exists($jsonPath)) {
                        $rawJson = json_decode(file_get_contents($jsonPath), true) ?: [];
                        $jsonReviews = collect($rawJson)->map(function ($item) {
                            return (object) $item;
                        })->filter(function ($r) {
                            return ! empty($r->review_text) && ! empty($r->reviewer_name) && (float) ($r->rating ?? 0) >= 4.0;
                        });

                        if ($googleCount < 15) {
                            $neededGoogle = 15 - $googleCount;
                            $jsonGoogle = $jsonReviews->where('source', 'google')->take($neededGoogle);
                            $featured = $featured->concat($jsonGoogle);
                        }

                        if ($tripCount < 15) {
                            $neededTrip = 15 - $tripCount;
                            $jsonTrip = $jsonReviews->where('source', 'tripadvisor')->take($neededTrip);
                            $featured = $featured->concat($jsonTrip);
                        }
                    }
                }

                return $featured->unique('review_text')->values();
            });
            $reviews = collect($reviews);
        } catch (\Throwable $e) {
            $reviews = collect();
        }

        try {
            $generalFaqIds = FaqAssignment::where('entity_type', 'general')->pluck('faq_id');
            $faqs = Faq::whereIn('id', $generalFaqIds)->where('status', 'active')->orderBy('priority', 'asc')->limit(6)->get();
        } catch (\Throwable $e) {
            $faqs = collect();
        }

        try {
            $allActiveTours = Tour::where('status', 'active')
                ->with(['category', 'tiers'])
                ->orderBy('priority', 'asc')
                ->get()
                ->map(function ($t) {
                    $minPrice = $t->tiers->pluck('pivot.price')->filter(function ($p) {
                        return $p !== null && (float) $p > 0;
                    })->min();

                    if (! $minPrice) {
                        $minPrice = 79;
                    }

                    return [
                        'id' => (string) $t->id,
                        'name' => $t->name,
                        'slug' => $t->slug,
                        'category_slug' => $t->category ? $t->category->slug : 'desert-safari',
                        'category_name' => $t->category ? $t->category->name : 'Desert Safari',
                        'duration' => $t->duration ?: '4-6 Hours',
                        'rating' => (float) ($t->rating ?: 4.9),
                        'review_count' => (int) ($t->review_count ?: 500),
                        'min_price' => (float) $minPrice,
                        'short_desc' => $t->short_desc ?: 'Top-rated Dubai tour experience.',
                        'is_bestseller' => (bool) $t->is_bestseller,
                        'is_featured' => (bool) $t->is_featured,
                        'priority' => (int) $t->priority,
                    ];
                });
        } catch (\Throwable $e) {
            $allActiveTours = collect();
        }

        $settingsService = app(SettingsService::class);
        $currentLocale = app()->getLocale();
        $currentYear = date('Y');

        if ($currentLocale === 'en' || empty($currentLocale)) {
            $defaultTitle = "Dubai Desert Safari Tours {$currentYear} | Best Price from AED 79 | Dunes Discovery Tourism";
            $defaultDesc = 'Book top-rated Dubai Desert Safari, 1000cc Dune Buggy, Quad Biking, & Dhow Cruise dinners from AED 79. 4x4 Land Cruiser pickup, live BBQ, & 24h free cancellation.';
            $defaultKeys = 'dubai desert safari, desert safari dubai, evening desert safari dubai, dune buggy rental dubai, quad biking dubai, dhow cruise dubai, abu dhabi city tour';

            $pageTitle = $settingsService->get('seo_home_title') ?: $defaultTitle;
            $pageDesc = $settingsService->get('seo_home_description') ?: $defaultDesc;
            $pageKeys = $settingsService->get('seo_home_keywords') ?: $defaultKeys;
            $canonical = rtrim(route('home'), '/').'/';
        } else {
            $pageTitle = $settingsService->get("seo_home_title_{$currentLocale}") ?: __('ui.seo.home_title', ['year' => $currentYear]);
            $pageDesc = $settingsService->get("seo_home_description_{$currentLocale}") ?: __('ui.seo.home_description');
            $pageKeys = $settingsService->get("seo_home_keywords_{$currentLocale}") ?: __('ui.seo.home_keywords');
            $canonical = url('/'.$currentLocale);
        }

        $ogImageSetting = $settingsService->get('seo_home_og_image');
        $ogImage = resolve_og_image($ogImageSetting, 'images/evening-desert-safari-dubai-dune-discovery-tourism.avif');

        try {
            $galleryItems = app(ReviewMediaGalleryService::class)->getHomepageItems(12);
        } catch (\Throwable $e) {
            $galleryItems = [];
        }

        $pageRobots = ! empty(request()->query())
            ? 'noindex, follow'
            : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

        return view('index', compact('categories', 'bestsellers', 'reviews', 'faqs', 'allActiveTours', 'galleryItems', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage', 'pageRobots'));
    }
}
