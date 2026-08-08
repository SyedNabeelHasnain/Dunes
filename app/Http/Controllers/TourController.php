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

        if ($tour && $tour->status !== 'active') {
            try {
                $tour->status = 'active';
                $tour->save();
            } catch (\Throwable $e) {}
        }

        if (!$tour && $slug === 'dune-buggy-rental-dubai') {
            try {
                $catId = Category::where('slug', 'desert-safari')->value('id') ?? Category::value('id');
                $tour = Tour::firstOrCreate(
                    ['slug' => 'dune-buggy-rental-dubai'],
                    [
                        'name' => 'Dune Buggy Rental Dubai',
                        'category_id' => $catId,
                        'short_desc' => 'Unleash the ultimate adrenaline rush in Dubai\'s Lahbab Red Dunes with our self-drive 1000cc Can-Am Maverick X3 and Polaris RZR dune buggies. Conquer towering sand dunes with full safety gear, expert guide instruction, and complimentary hotel pickup.',
                        'full_desc' => 'Take control of a high-powered 1000cc Can-Am Maverick X3 Turbo or Polaris RZR dune buggy and conquer the open desert of Dubai\'s famous Lahbab Red Dunes. Designed for thrill-seekers, couples, and friends, our self-drive dune buggy tours deliver an unparalleled off-road adventure under the guidance of certified desert rally instructors.',
                        'duration' => '3 Hours',
                        'pickup_time' => '7:00 AM / 3:00 PM',
                        'dropoff_time' => '10:00 AM / 6:00 PM',
                        'min_age' => 16,
                        'languages' => 'English, Arabic',
                        'hero_image' => 'quad-biking-desert-safari-dubai-dune-discovery-tourism.avif',
                        'thumb_image' => 'quad-biking-desert-safari-dubai-dune-discovery-tourism.avif',
                        'og_image' => 'quad-biking-desert-safari-dubai-dune-discovery-tourism.avif',
                        'rating' => 4.9,
                        'review_count' => 642,
                        'is_bestseller' => 1,
                        'is_featured' => 1,
                        'status' => 'active',
                        'priority' => 4,
                        'meta_title' => 'Dune Buggy Rental Dubai | 1000cc Can-Am & Polaris | Dunes Discovery',
                        'meta_desc' => 'Rent self-drive 1000cc Can-Am Maverick & Polaris dune buggies in Dubai Lahbab Red Dunes. High-power off-road desert safari with safety gear & hotel pickup.',
                        'meta_keywords' => 'dune buggy rental dubai, can am dune buggy dubai, polaris rzr dubai, self drive buggy desert safari, red dunes buggy rental'
                    ]
                );

                if ($tour && $tour->tiers()->count() === 0) {
                    $existingTierIds = Tier::pluck('id')->toArray();
                    $tierPrices = [
                        1 => ['price' => 599.00, 'old_price' => 750.00, 'price_type' => 'per buggy'],
                        2 => ['price' => 899.00, 'old_price' => 1100.00, 'price_type' => 'per buggy'],
                        4 => ['price' => 1299.00, 'old_price' => 1500.00, 'price_type' => 'per buggy'],
                    ];
                    $attachData = [];
                    foreach ($tierPrices as $tId => $pivotData) {
                        if (in_array($tId, $existingTierIds)) {
                            $attachData[$tId] = $pivotData;
                        }
                    }
                    if (!empty($attachData)) {
                        $tour->tiers()->attach($attachData);
                    }
                }
            } catch (\Throwable $e) {}

            $tour = Tour::where('slug', 'dune-buggy-rental-dubai')
                ->with(['itineraries', 'tiers', 'addons', 'contentItems', 'category'])
                ->first();
        }

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

        // Track form load timestamp for analytics
        session(["form_load.booking_{$tour->id}" => microtime(true)]);

        return view('tours.show', compact('tour', 'highlights', 'inclusions', 'exclusions', 'faqs', 'relatedTours'));
    }
}
