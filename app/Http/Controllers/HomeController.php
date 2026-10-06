<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Faq;
use App\Models\FaqAssignment;
use App\Models\Review;
use App\Models\Tour;
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
                // Primary collection: high-priority featured approved reviews
                $featured = Review::where('status', 'approved')
                    ->where('is_featured', true)
                    ->orderBy('published_date', 'desc')
                    ->limit(10)
                    ->get();

                $featuredIds = $featured->pluck('id')->all();
                $googleCount = $featured->where('source', 'google')->count();
                $tripCount = $featured->where('source', 'tripadvisor')->count();

                // Ensure high-quality Google reviews are prominently included even if not manually marked featured
                if ($googleCount < 5) {
                    $extraGoogle = Review::where('status', 'approved')
                        ->where('source', 'google')
                        ->whereNotIn('id', $featuredIds)
                        ->where('rating', '>=', 4.0)
                        ->orderBy('published_date', 'desc')
                        ->limit(8 - $googleCount)
                        ->get();

                    $featured = $featured->concat($extraGoogle);
                    $featuredIds = $featured->pluck('id')->all();
                }

                // Ensure TripAdvisor reviews are populated for the alternating marquee track
                if ($tripCount < 5) {
                    $extraTrip = Review::where('status', 'approved')
                        ->where('source', 'tripadvisor')
                        ->whereNotIn('id', $featuredIds)
                        ->where('rating', '>=', 4.0)
                        ->orderBy('published_date', 'desc')
                        ->limit(8 - $tripCount)
                        ->get();

                    $featured = $featured->concat($extraTrip);
                    $featuredIds = $featured->pluck('id')->all();
                }

                // If overall count is still under 8, backfill with any high-rated approved reviews
                if ($featured->count() < 8) {
                    $fillers = Review::where('status', 'approved')
                        ->whereNotIn('id', $featuredIds)
                        ->where('rating', '>=', 4.0)
                        ->orderBy('published_date', 'desc')
                        ->limit(12 - $featured->count())
                        ->get();

                    $featured = $featured->concat($fillers);
                }

                return $featured;
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
        $ogImage = $ogImageSetting ? asset(ltrim($ogImageSetting, '/')) : asset('images/desert-safari-poster.avif');

        return view('index', compact('categories', 'bestsellers', 'reviews', 'faqs', 'allActiveTours', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage'));
    }
}
