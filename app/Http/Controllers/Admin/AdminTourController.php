<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Category;
use App\Models\Tier;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminTourController extends Controller
{
    /**
     * Display a listing of tours.
     */
    public function index()
    {
        $tours = Tour::with('category')->orderBy('priority', 'asc')->get();
        return view('admin.tours.index', compact('tours'));
    }

    /**
     * Show the form for creating a new tour.
     */
    public function create()
    {
        $categories = Category::all();
        $tiers = Tier::where('status', 'active')->get();
        $addons = Addon::where('status', 'active')->get();
        return view('admin.tours.create', compact('categories', 'tiers', 'addons'));
    }

    /**
     * Store a newly created tour in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'short_desc' => 'required|string',
            'full_desc' => 'required|string',
            'duration' => 'required|string|max:255',
            'pickup_time' => 'required|string|max:255',
            'dropoff_time' => 'required|string|max:255',
            'priority' => 'required|integer',
            'status' => 'required|string|in:active,inactive',
            'hero_image' => 'nullable|image|max:4096',
            'thumb_image' => 'nullable|image|max:2048',
        ]);

        $data = $request->except(['tiers', 'addons', 'hero_image', 'thumb_image']);
        $data['slug'] = Str::slug($request->name);

        // Handle image uploads
        if ($request->hasFile('hero_image')) {
            $data['hero_image'] = $request->file('hero_image')->store('tours/hero', 'public');
        }
        if ($request->hasFile('thumb_image')) {
            $data['thumb_image'] = $request->file('thumb_image')->store('tours/thumb', 'public');
        }

        $tour = Tour::create($data);

        // Sync Tiers & Addons
        if ($request->has('tiers')) {
            $tiersData = [];
            foreach ($request->input('tiers') as $tierId => $pivot) {
                if (isset($pivot['price'])) {
                    $tiersData[$tierId] = [
                        'price' => (float)$pivot['price'],
                        'old_price' => isset($pivot['old_price']) ? (float)$pivot['old_price'] : null,
                        'price_type' => $pivot['price_type'] ?? 'per person'
                    ];
                }
            }
            $tour->tiers()->sync($tiersData);
        }

        if ($request->has('addons')) {
            $addonsData = [];
            foreach ($request->input('addons') as $addonId => $pivot) {
                if (isset($pivot['price'])) {
                    $addonsData[$addonId] = [
                        'price' => (float)$pivot['price']
                    ];
                }
            }
            $tour->addons()->sync($addonsData);
        }

        return redirect()->route('admin.tours.index')->with('success', 'Tour created successfully.');
    }

    /**
     * Show the form for editing the specified tour.
     */
    public function edit(string $id)
    {
        $tour = Tour::with(['tiers', 'addons'])->findOrFail($id);
        $categories = Category::all();
        $tiers = Tier::where('status', 'active')->get();
        $addons = Addon::where('status', 'active')->get();
        return view('admin.tours.edit', compact('tour', 'categories', 'tiers', 'addons'));
    }

    /**
     * Update the specified tour in storage.
     */
    public function update(Request $request, string $id)
    {
        $tour = Tour::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'short_desc' => 'required|string',
            'full_desc' => 'required|string',
            'duration' => 'required|string|max:255',
            'pickup_time' => 'required|string|max:255',
            'dropoff_time' => 'required|string|max:255',
            'priority' => 'required|integer',
            'status' => 'required|string|in:active,inactive',
        ]);

        $data = $request->except(['tiers', 'addons', 'hero_image', 'thumb_image']);
        $data['slug'] = Str::slug($request->name);

        if ($request->hasFile('hero_image')) {
            $data['hero_image'] = $request->file('hero_image')->store('tours/hero', 'public');
        }
        if ($request->hasFile('thumb_image')) {
            $data['thumb_image'] = $request->file('thumb_image')->store('tours/thumb', 'public');
        }

        $tour->update($data);

        // Sync Tiers & Addons
        if ($request->has('tiers')) {
            $tiersData = [];
            foreach ($request->input('tiers') as $tierId => $pivot) {
                if (isset($pivot['price'])) {
                    $tiersData[$tierId] = [
                        'price' => (float)$pivot['price'],
                        'old_price' => !empty($pivot['old_price']) ? (float)$pivot['old_price'] : null,
                        'price_type' => $pivot['price_type'] ?? 'per person'
                    ];
                }
            }
            $tour->tiers()->sync($tiersData);
        }

        if ($request->has('addons')) {
            $addonsData = [];
            foreach ($request->input('addons') as $addonId => $pivot) {
                if (isset($pivot['price'])) {
                    $addonsData[$addonId] = [
                        'price' => (float)$pivot['price']
                    ];
                }
            }
            $tour->addons()->sync($addonsData);
        }

        return redirect()->route('admin.tours.index')->with('success', 'Tour updated successfully.');
    }

    /**
     * Remove the specified tour from storage.
     */
    public function destroy(string $id)
    {
        $tour = Tour::findOrFail($id);
        $tour->delete();
        return redirect()->route('admin.tours.index')->with('success', 'Tour deleted successfully.');
    }

    /**
     * Display listing of Tiers.
     */
    public function Tiers()
    {
        $tiers = Tier::orderBy('priority', 'asc')->get();
        return view('admin.tiers.index', compact('tiers'));
    }

    /**
     * Display listing of Addons.
     */
    public function Addons()
    {
        $addons = Addon::orderBy('priority', 'asc')->get();
        return view('admin.addons.index', compact('addons'));
    }

    /**
     * Display Pricing Grid Matrix.
     */
    public function Pricing()
    {
        $tours = Tour::where('status', 'active')->with('tiers')->orderBy('priority', 'asc')->get();
        $tiers = Tier::where('status', 'active')->orderBy('priority', 'asc')->get();
        return view('admin.pricing.index', compact('tours', 'tiers'));
    }

    /**
     * Save pricing changes in bulk.
     */
    public function updatePricing(Request $request)
    {
        $pricing = $request->input('pricing', []);

        foreach ($pricing as $tourId => $tiers) {
            $tour = Tour::find($tourId);
            if ($tour) {
                $syncData = [];
                foreach ($tiers as $tierId => $prices) {
                    if (isset($prices['price']) && $prices['price'] !== '') {
                        $syncData[$tierId] = [
                            'price' => (float)$prices['price'],
                            'old_price' => !empty($prices['old_price']) ? (float)$prices['old_price'] : null,
                            'price_type' => $prices['price_type'] ?? 'per person'
                        ];
                    }
                }
                $tour->tiers()->syncWithoutDetaching($syncData);
            }
        }

        return redirect()->route('admin.pricing.index')->with('success', 'Pricing updated successfully.');
    }

    /**
     * Add an itinerary item to a tour.
     */
    public function addItinerary(Request $request, string $id)
    {
        $tour = Tour::findOrFail($id);

        $request->validate([
            'time' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'duration' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'priority' => 'required|integer',
            'description' => 'nullable|string',
        ]);

        $tour->itineraries()->create($request->all());

        return response()->json(['success' => true, 'message' => 'Itinerary item added successfully.']);
    }

    /**
     * Update an itinerary item.
     */
    public function updateItinerary(Request $request, string $id)
    {
        $itinerary = \App\Models\Itinerary::findOrFail($id);

        $request->validate([
            'time' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'duration' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'priority' => 'required|integer',
            'description' => 'nullable|string',
        ]);

        $itinerary->update($request->all());

        return response()->json(['success' => true, 'message' => 'Itinerary item updated successfully.']);
    }

    /**
     * Delete an itinerary item.
     */
    public function deleteItinerary(string $id)
    {
        $itinerary = \App\Models\Itinerary::findOrFail($id);
        $itinerary->delete();

        return response()->json(['success' => true, 'message' => 'Itinerary item deleted successfully.']);
    }

    /**
     * Add a content item globally.
     */
    public function addContentItem(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:inclusion,exclusion,highlight,not_allowed',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'priority' => 'required|integer',
        ]);

        $item = \App\Models\ContentItem::create($request->all());

        return response()->json(['success' => true, 'message' => 'Content item created successfully.', 'item' => $item]);
    }

    /**
     * Set content assignments for a tour.
     */
    public function setTourContent(Request $request, string $id)
    {
        $tour = Tour::findOrFail($id);

        $contentIds = [];
        foreach (['inclusion', 'exclusion', 'highlight', 'not_allowed'] as $type) {
            if ($request->has($type)) {
                foreach ($request->input($type) as $ciId) {
                    $contentIds[] = (int)$ciId;
                }
            }
        }

        $tour->contentItems()->sync($contentIds);

        return response()->json(['success' => true, 'message' => 'Tour content items assigned successfully.']);
    }

    /**
     * Add a Category.
     */
    public function addCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        $category = Category::create([
            'name' => trim($request->input('name')),
            'slug' => Str::slug($request->input('name'))
        ]);

        return response()->json(['success' => true, 'message' => 'Category added successfully.', 'category' => $category]);
    }

    /**
     * Rename a Category.
     */
    public function renameCategory(Request $request)
    {
        $request->validate([
            'old' => 'required|string|max:255',
            'new' => 'required|string|max:255',
        ]);

        $category = Category::where('name', $request->input('old'))
            ->orWhere('slug', Str::slug($request->input('old')))
            ->firstOrFail();

        $category->update([
            'name' => trim($request->input('new')),
            'slug' => Str::slug($request->input('new'))
        ]);

        return response()->json(['success' => true, 'message' => 'Category renamed successfully.']);
    }
}
