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
        
        $currentYear = date('Y');
        $pageTitle = "Top Dubai Desert Safari & City Tours ({$currentYear}) | Best Deals | Dunes Discovery";
        $pageDesc = "Explore top-rated Dubai desert safaris, 1000cc dune buggy rentals, dhow cruise dinners, and luxury Abu Dhabi city tours. Instant confirmation & 24h free cancellation.";
        $pageKeys = "dubai desert safari tours, dune buggy dubai, quad biking dubai, dhow cruise dubai, abu dhabi city tour";
        $canonical = route('tours.index');
        $ogImage = asset('images/desert-safari-poster.avif');
        
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
        $tour = Tour::where('slug', $slug)
            ->with(['itineraries', 'tiers', 'addons', 'contentItems', 'category'])
            ->first();

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
        $pageDesc = $tour->meta_description ?: (strlen($tour->short_desc ?? '') > 50 ? strip_tags($tour->short_desc) : $defaultDesc);
        
        $pageKeys = $tour->meta_keywords ?: strtolower("{$tour->name}, {$tour->name} dubai, book {$tour->name}, desert safari dubai, dubai tours {$currentYear}");
        $canonical = url('/' . $tour->slug);
        
        $imgFile = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $tour->hero_image ?: 'evening-desert-safari-dubai-dune-discovery-tourism.avif');
        $ogImage = asset('images/blog/' . $imgFile);

        // Track form load timestamp for analytics
        session(["form_load.booking_{$tour->id}" => microtime(true)]);

        return view('tours.show', compact('tour', 'highlights', 'inclusions', 'exclusions', 'faqs', 'relatedTours', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage', 'minPrice'));
    }
}
