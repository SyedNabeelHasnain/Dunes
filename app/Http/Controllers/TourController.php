<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Faq;
use App\Models\FaqAssignment;
use App\Models\Tour;
use Illuminate\Http\Request;

class TourController extends Controller
{
    /**
     * Display the tour catalog.
     */
    public function index(Request $request)
    {
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
        
        return view('tours.index', compact('categories', 'tours', 'selectedCategorySlug'));
    }

    /**
     * Display details for a specific tour.
     */
    public function show(string $slug)
    {
        $tour = Tour::where('slug', $slug)
            ->where('status', 'active')
            ->with(['itineraries', 'tiers', 'addons', 'contentItems', 'category'])
            ->firstOrFail();

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

        // Track form load timestamp for analytics
        session(["form_load.booking_{$tour->id}" => microtime(true)]);

        return view('tours.show', compact('tour', 'highlights', 'inclusions', 'exclusions', 'faqs', 'relatedTours'));
    }
}
