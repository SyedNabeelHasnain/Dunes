<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Review;
use App\Models\Tour;
use Illuminate\Http\Request;

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
            $reviews = Review::where('status', 'approved')
                ->where('is_featured', true)
                ->orderBy('published_date', 'desc')
                ->limit(10)
                ->get();
        } catch (\Throwable $e) {
            $reviews = collect();
        }

        try {
            $generalFaqIds = \App\Models\FaqAssignment::where('entity_type', 'general')->pluck('faq_id');
            $faqs = \App\Models\Faq::whereIn('id', $generalFaqIds)->where('status', 'active')->orderBy('priority', 'asc')->limit(6)->get();
        } catch (\Throwable $e) {
            $faqs = collect();
        }

        try {
            $allActiveTours = Tour::where('status', 'active')
                ->with(['category', 'tiers' => function ($query) {
                    $query->orderBy('price', 'asc');
                }])
                ->orderBy('priority', 'asc')
                ->get()
                ->map(function ($t) {
                    $minPrice = $t->tiers->min('price') ?: 79;
                    return [
                        'id' => (string)$t->id,
                        'name' => $t->name,
                        'slug' => $t->slug,
                        'category_slug' => $t->category ? $t->category->slug : 'desert-safari',
                        'category_name' => $t->category ? $t->category->name : 'Desert Safari',
                        'duration' => $t->duration ?: '4-6 Hours',
                        'rating' => (float)($t->rating ?: 4.9),
                        'review_count' => (int)($t->review_count ?: 500),
                        'min_price' => (float)$minPrice,
                        'short_desc' => $t->short_desc ?: 'Top-rated Dubai tour experience.',
                        'is_bestseller' => (bool)$t->is_bestseller,
                        'is_featured' => (bool)$t->is_featured,
                        'priority' => (int)$t->priority,
                    ];
                });
        } catch (\Throwable $e) {
            $allActiveTours = collect();
        }

        $currentYear = date('Y');
        $pageTitle = "Dubai Desert Safari Tours {$currentYear} | Best Price from AED 79 | Dunes Discovery Tourism";
        $pageDesc = "Book top-rated Dubai Desert Safari, 1000cc Dune Buggy, Quad Biking, & Dhow Cruise dinners from AED 79. 4x4 Land Cruiser pickup, live BBQ, & 24h free cancellation.";
        $pageKeys = "dubai desert safari, desert safari dubai, evening desert safari dubai, dune buggy rental dubai, quad biking dubai, dhow cruise dubai, abu dhabi city tour";
        $canonical = route('home');
        $ogImage = asset('images/desert-safari-poster.avif');

        return view('index', compact('categories', 'bestsellers', 'reviews', 'faqs', 'allActiveTours', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage'));
    }
}
