<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\Category;
use App\Models\ContentItem;
use App\Models\Itinerary;
use App\Models\Language;
use App\Models\Tier;
use App\Models\Tour;
use App\Traits\NormalizesLocalizedInputs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminTourController extends Controller
{
    use NormalizesLocalizedInputs;
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
        $languages = Language::getActive();

        return view('admin.tours.create', compact('categories', 'tiers', 'addons', 'languages'));
    }

    /**
     * Store a newly created tour in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'category_id' => 'required|integer',
            'short_desc' => 'required',
            'full_desc' => 'required',
            'duration' => 'required|string|max:255',
            'pickup_time' => 'nullable|string|max:255',
            'dropoff_time' => 'nullable|string|max:255',
            'priority' => 'required|integer',
            'status' => 'required|string|in:active,inactive',
            'hero_image' => 'nullable|image|max:5120',
            'thumb_image' => 'nullable|image|max:3072',
        ]);

        $data = $this->normalizeLocalizedData(
            $request->except(['tiers', 'addons', 'hero_image', 'thumb_image']),
            ['name', 'short_desc', 'full_desc', 'meta_title', 'meta_desc', 'meta_keywords']
        );
        $baseSlug = Str::slug($this->extractSlugSource($request->name));
        $slug = ! empty($baseSlug) ? $baseSlug : 'tour-'.time();
        $counter = 1;
        while (Tour::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }
        $data['slug'] = $slug;
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_bestseller'] = $request->boolean('is_bestseller');

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
                if (isset($pivot['price']) && $pivot['price'] !== '' && $pivot['price'] !== null) {
                    $tiersData[$tierId] = [
                        'price' => (float) $pivot['price'],
                        'old_price' => ! empty($pivot['old_price']) ? (float) $pivot['old_price'] : null,
                        'price_type' => $pivot['price_type'] ?? 'per person',
                    ];
                }
            }
            $tour->tiers()->sync($tiersData);
        }

        if ($request->has('addons')) {
            $addonsData = [];
            foreach ($request->input('addons') as $addonId => $pivot) {
                if (isset($pivot['price']) && $pivot['price'] !== '' && $pivot['price'] !== null) {
                    $addonsData[$addonId] = [
                        'price' => (float) $pivot['price'],
                    ];
                }
            }
            $tour->addons()->sync($addonsData);
        }

        $this->clearTourCaches();

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
        $languages = Language::getActive();

        return view('admin.tours.edit', compact('tour', 'categories', 'tiers', 'addons', 'languages'));
    }

    /**
     * Update the specified tour in storage.
     */
    public function update(Request $request, string $id)
    {
        $tour = Tour::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'category_id' => 'required|integer',
            'short_desc' => 'required',
            'full_desc' => 'required',
            'duration' => 'required|string|max:255',
            'pickup_time' => 'nullable|string|max:255',
            'dropoff_time' => 'nullable|string|max:255',
            'priority' => 'required|integer',
            'status' => 'required|string|in:active,inactive',
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:5120',
            'thumb_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:3072',
        ]);

        $data = $this->normalizeLocalizedData(
            $request->except(['tiers', 'addons', 'hero_image', 'thumb_image']),
            ['name', 'short_desc', 'full_desc', 'meta_title', 'meta_desc', 'meta_keywords']
        );
        if ($request->has('name')) {
            $baseSlug = Str::slug($this->extractSlugSource($request->name));
            if (! empty($baseSlug)) {
                $slug = $baseSlug;
                $counter = 1;
                while (Tour::where('slug', $slug)->where('id', '!=', $tour->id)->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }
                $data['slug'] = $slug;
            }
        }
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_bestseller'] = $request->boolean('is_bestseller');

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
                if (isset($pivot['price']) && $pivot['price'] !== '' && $pivot['price'] !== null) {
                    $tiersData[$tierId] = [
                        'price' => (float) $pivot['price'],
                        'old_price' => ! empty($pivot['old_price']) ? (float) $pivot['old_price'] : null,
                        'price_type' => $pivot['price_type'] ?? 'per person',
                    ];
                }
            }
            $tour->tiers()->sync($tiersData);
        }

        if ($request->has('addons')) {
            $addonsData = [];
            foreach ($request->input('addons') as $addonId => $pivot) {
                if (isset($pivot['price']) && $pivot['price'] !== '' && $pivot['price'] !== null) {
                    $addonsData[$addonId] = [
                        'price' => (float) $pivot['price'],
                    ];
                }
            }
            $tour->addons()->sync($addonsData);
        }

        $this->clearTourCaches();

        return redirect()->route('admin.tours.index')->with('success', 'Tour updated successfully.');
    }

    /**
     * Remove the specified tour from storage.
     */
    public function destroy(string $id)
    {
        $tour = Tour::findOrFail($id);
        $tour->delete();
        $this->clearTourCaches();

        return redirect()->route('admin.tours.index')->with('success', 'Tour deleted successfully.');
    }

    /**
     * Display listing of Tiers.
     */
    public function tiers()
    {
        $tiers = Tier::withCount('tours')->orderBy('priority', 'asc')->get();

        return view('admin.tiers.index', compact('tiers'));
    }

    /**
     * Store a newly created pricing tier.
     */
    public function storeTier(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'display_name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:tiers,slug',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'is_popular' => 'nullable',
            'status' => 'required|string|in:active,inactive',
            'priority' => 'required|integer',
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);
        if (Tier::where('slug', $slug)->exists()) {
            $slug .= '-'.time();
        }

        Tier::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'slug' => $slug,
            'description' => $request->description,
            'icon' => $request->icon ?: 'star-fill',
            'is_popular' => $request->has('is_popular') ? 1 : 0,
            'status' => $request->status,
            'priority' => (int) $request->priority,
        ]);

        return redirect()->route('admin.tiers.index')->with('success', 'Pricing tier created successfully.');
    }

    /**
     * Update the specified pricing tier.
     */
    public function updateTier(Request $request, string $id)
    {
        $tier = Tier::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'display_name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tiers,slug,'.$tier->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'is_popular' => 'nullable',
            'status' => 'required|string|in:active,inactive',
            'priority' => 'required|integer',
        ]);

        $tier->update([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'slug' => Str::slug($request->slug),
            'description' => $request->description,
            'icon' => $request->icon ?: 'star-fill',
            'is_popular' => $request->has('is_popular') ? 1 : 0,
            'status' => $request->status,
            'priority' => (int) $request->priority,
        ]);

        return redirect()->route('admin.tiers.index')->with('success', 'Pricing tier updated successfully.');
    }

    /**
     * Delete the specified pricing tier.
     */
    public function deleteTier(string $id)
    {
        $tier = Tier::findOrFail($id);
        $tier->tours()->detach();
        $tier->delete();

        return redirect()->route('admin.tiers.index')->with('success', 'Pricing tier deleted successfully.');
    }

    /**
     * Display listing of Addons with Sales Analytics.
     */
    public function addons()
    {
        $addons = Addon::withCount('tours')->orderBy('priority', 'asc')->get();
        $totalBookingsCount = Booking::where('status', '!=', 'draft')->count();

        foreach ($addons as $addon) {
            $bookedCount = DB::table('booking_addons')->where('addon_id', $addon->id)->count();
            $bookedRevenue = (float) DB::table('booking_addons')->where('addon_id', $addon->id)->sum('price');
            $attachmentRate = $totalBookingsCount > 0 ? round(($bookedCount / $totalBookingsCount) * 100, 1) : 0;

            $addon->times_booked = $bookedCount;
            $addon->total_revenue = $bookedRevenue;
            $addon->attachment_rate = $attachmentRate;
        }

        $totalAddonsRevenue = (float) DB::table('booking_addons')->sum('price');
        $totalAddonsBooked = DB::table('booking_addons')->count();

        return view('admin.addons.index', compact('addons', 'totalAddonsRevenue', 'totalAddonsBooked'));
    }

    /**
     * Store a newly created addon.
     */
    public function storeAddon(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:addons,slug',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'default_price' => 'required|numeric|min:0',
            'status' => 'required|string|in:active,inactive',
            'priority' => 'required|integer',
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);
        if (Addon::where('slug', $slug)->exists()) {
            $slug .= '-'.time();
        }

        Addon::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'icon' => $request->icon ?: 'plus-lg',
            'default_price' => (float) $request->default_price,
            'status' => $request->status,
            'priority' => (int) $request->priority,
        ]);

        return redirect()->route('admin.addons.index')->with('success', 'Addon created successfully.');
    }

    /**
     * Update the specified addon.
     */
    public function updateAddon(Request $request, string $id)
    {
        $addon = Addon::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:addons,slug,'.$addon->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'default_price' => 'required|numeric|min:0',
            'status' => 'required|string|in:active,inactive',
            'priority' => 'required|integer',
        ]);

        $addon->update([
            'name' => $request->name,
            'slug' => Str::slug($request->slug),
            'description' => $request->description,
            'icon' => $request->icon ?: 'plus-lg',
            'default_price' => (float) $request->default_price,
            'status' => $request->status,
            'priority' => (int) $request->priority,
        ]);

        return redirect()->route('admin.addons.index')->with('success', 'Addon updated successfully.');
    }

    /**
     * Delete the specified addon.
     */
    public function deleteAddon(string $id)
    {
        $addon = Addon::findOrFail($id);
        $addon->tours()->detach();
        $addon->delete();

        return redirect()->route('admin.addons.index')->with('success', 'Addon deleted successfully.');
    }

    /**
     * Display Pricing Grid Matrix.
     */
    public function pricing()
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
                            'price' => (float) $prices['price'],
                            'old_price' => ! empty($prices['old_price']) ? (float) $prices['old_price'] : null,
                            'price_type' => $prices['price_type'] ?? 'per person',
                        ];
                    }
                }
                $tour->tiers()->syncWithoutDetaching($syncData);
            }
        }

        $this->clearTourCaches();

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
            'title' => 'required',
            'duration' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'priority' => 'required|integer',
            'description' => 'nullable',
        ]);

        $data = $this->normalizeLocalizedData($request->all(), ['title', 'description']);
        $tour->itineraries()->create($data);

        return response()->json(['success' => true, 'message' => 'Itinerary item added successfully.']);
    }

    /**
     * Update an itinerary item.
     */
    public function updateItinerary(Request $request, string $id)
    {
        $itinerary = Itinerary::findOrFail($id);

        $request->validate([
            'time' => 'required|string|max:255',
            'title' => 'required',
            'duration' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'priority' => 'required|integer',
            'description' => 'nullable',
        ]);

        $data = $this->normalizeLocalizedData($request->all(), ['title', 'description']);
        $itinerary->update($data);

        return response()->json(['success' => true, 'message' => 'Itinerary item updated successfully.']);
    }

    /**
     * Delete an itinerary item.
     */
    public function deleteItinerary(string $id)
    {
        $itinerary = Itinerary::findOrFail($id);
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
            'title' => 'required',
            'description' => 'nullable',
            'icon' => 'nullable|string|max:255',
            'priority' => 'required|integer',
        ]);

        $data = $this->normalizeLocalizedData($request->all(), ['title', 'description']);
        $item = ContentItem::create($data);

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
                    $contentIds[] = (int) $ciId;
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
            'slug' => Str::slug($request->input('name')),
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
            'slug' => Str::slug($request->input('new')),
        ]);

        return response()->json(['success' => true, 'message' => 'Category renamed successfully.']);
    }

    /**
     * Toggle active/inactive status of a tour.
     */
    public function toggleStatus(string $id)
    {
        $tour = Tour::findOrFail($id);
        $tour->status = $tour->status === 'active' ? 'inactive' : 'active';
        $tour->save();

        $this->clearTourCaches();

        return response()->json([
            'success' => true,
            'status' => $tour->status,
            'message' => 'Tour status updated to '.ucfirst($tour->status).'.',
        ]);
    }

    /**
     * Clear all tour related caches across all supported locales and feeds.
     */
    protected function clearTourCaches(): void
    {
        Cache::forget('site_tours_header_cache');
        Cache::forget('site_home_cache');
        Cache::forget('site_active_tours_search');
        Cache::forget('site_search_modal_catalog_v4');
        foreach (['en', 'ar', 'ru', 'es', 'it'] as $loc) {
            Cache::forget('site_active_tours_search_'.$loc);
            Cache::forget('site_search_modal_catalog_v4_'.$loc);
        }
        Cache::forget('sitemap_tours_xml');
        Cache::forget('sitemap_images_xml');
        Cache::forget('gttd_products_xml');
        Cache::forget('gttd_options_xml');
        Cache::forget('gttd_feed_xml');
        Cache::forget('gttd_feed_json');
    }
}
