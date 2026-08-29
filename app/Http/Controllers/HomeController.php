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
        $categories = Category::with(['tours' => function ($query) {
            $query->where('status', 'active')->with(['tiers', 'category'])->orderBy('priority', 'asc');
        }])->orderBy('priority', 'asc')->get();

        $bestsellers = Tour::where('status', 'active')
            ->where('is_bestseller', true)
            ->with(['tiers', 'category'])
            ->orderBy('priority', 'asc')
            ->get();

        $generalFaqIds = \App\Models\FaqAssignment::where('entity_type', 'general')->pluck('faq_id');
        $faqs = \App\Models\Faq::whereIn('id', $generalFaqIds)->where('status', 'active')->orderBy('priority', 'asc')->limit(6)->get();

        $currentYear = date('Y');
        $pageTitle = "Dubai Desert Safari Tours {$currentYear} | Best Price from AED 79 | Dunes Discovery Tourism";
        $pageDesc = "Book top-rated Dubai Desert Safari, 1000cc Dune Buggy, Quad Biking, & Dhow Cruise dinners from AED 79. 4x4 Land Cruiser pickup, live BBQ, & 24h free cancellation.";
        $pageKeys = "dubai desert safari, desert safari dubai, evening desert safari dubai, dune buggy rental dubai, quad biking dubai, dhow cruise dubai, abu dhabi city tour";
        $canonical = route('home');
        $ogImage = asset('images/desert-safari-poster.avif');

        return view('index', compact('categories', 'bestsellers', 'reviews', 'faqs', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage'));
    }
}
