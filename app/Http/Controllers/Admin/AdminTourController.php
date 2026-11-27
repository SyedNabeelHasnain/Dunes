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
            'category_id' => 'required|integer|exists:categories,id',
            'short_desc' => 'required',
            'full_desc' => 'required',
            'duration' => 'required|string|max:255',
            'pickup_time' => 'nullable|string|max:255',
            'dropoff_time' => 'nullable|string|max:255',
            'video_url' => 'nullable|string|max:500',
            'rating' => 'nullable|numeric|min:1|max:5',
            'review_count' => 'nullable|integer|min:0',
            'priority' => 'required|integer',
            'status' => 'required|string|in:active,inactive',
            'hero_image' => $request->hasFile('hero_image') ? 'file|mimes:jpeg,png,jpg,webp,avif|max:8192' : 'nullable|string|max:500',
            'thumb_image' => $request->hasFile('thumb_image') ? 'file|mimes:jpeg,png,jpg,webp,avif|max:8192' : 'nullable|string|max:500',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        $ext = strtolower((string) ($value->guessExtension() ?: $value->getClientOriginalExtension()));
                        if (! in_array($ext, ['jpeg', 'jpg', 'png', 'webp', 'avif'], true) || $value->getSize() > 8388608) {
                            $fail('Each gallery image upload must be a valid JPG, PNG, WebP, or AVIF image under 8MB.');
                        }
                    } elseif (! is_string($value) || strlen($value) > 500) {
                        $fail('Each gallery image reference must be a valid path string.');
                    }
                },
            ],
        ]);

        $data = $this->normalizeLocalizedData(
            $request->except(['tiers', 'addons', 'hero_image', 'thumb_image', 'gallery_images', 'gallery_images_submitted', 'remove_gallery_images']),
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

        // Handle hero image (uploaded file or selected from media library)
        if ($request->hasFile('hero_image')) {
            $data['hero_image'] = $request->file('hero_image')->store('tours/hero', 'public');
            try {
                app(\App\Services\MediaManagerService::class)->scanAndSyncFile(public_path('storage/' . $data['hero_image']), 'tours');
            } catch (\Throwable $e) {}
        } elseif ($request->filled('hero_image')) {
            $data['hero_image'] = ltrim(trim($request->input('hero_image')), '/');
        }

        // Handle thumbnail image
        if ($request->hasFile('thumb_image')) {
            $data['thumb_image'] = $request->file('thumb_image')->store('tours/thumb', 'public');
            try {
                app(\App\Services\MediaManagerService::class)->scanAndSyncFile(public_path('storage/' . $data['thumb_image']), 'tours');
            } catch (\Throwable $e) {}
        } elseif ($request->filled('thumb_image')) {
            $data['thumb_image'] = ltrim(trim($request->input('thumb_image')), '/');
        }

        // Handle gallery images (array of strings or files)
        $galleryPaths = [];
        if ($request->has('gallery_images') && is_array($request->gallery_images)) {
            foreach ($request->gallery_images as $gItem) {
                if ($gItem instanceof \Illuminate\Http\UploadedFile && $gItem->isValid()) {
                    $path = $gItem->store('tours/gallery', 'public');
                    $galleryPaths[] = $path;
                    try {
                        app(\App\Services\MediaManagerService::class)->scanAndSyncFile(public_path('storage/' . $path), 'tours');
                    } catch (\Throwable $e) {}
                } elseif (is_string($gItem) && ! empty(trim($gItem))) {
                    $galleryPaths[] = ltrim(trim($gItem), '/');
                }
            }
        }
        $data['gallery_images'] = array_values(array_unique($galleryPaths));

        $tour = Tour::create($data);

        // Sync Tiers & Addons
        if ($request->has('tiers')) {
            $hasChildPriceCol = \Illuminate\Support\Facades\Schema::hasColumn('tour_tiers', 'child_price');
            $tiersData = [];
            foreach ($request->input('tiers') as $tierId => $pivot) {
                if (isset($pivot['price']) && $pivot['price'] !== '' && $pivot['price'] !== null) {
                    $tierRow = [
                        'price' => (float) $pivot['price'],
                        'old_price' => ! empty($pivot['old_price']) ? (float) $pivot['old_price'] : null,
                        'price_type' => $pivot['price_type'] ?? 'per person',
                    ];
                    if ($hasChildPriceCol && isset($pivot['child_price']) && $pivot['child_price'] !== '') {
                        $tierRow['child_price'] = (float) $pivot['child_price'];
                    }
                    if (isset($pivot['guest_count']) && $pivot['guest_count'] !== '') {
                        $tierRow['guest_count'] = (int) $pivot['guest_count'];
                    }
                    $tiersData[$tierId] = $tierRow;
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
            'category_id' => 'required|integer|exists:categories,id',
            'short_desc' => 'required',
            'full_desc' => 'required',
            'duration' => 'required|string|max:255',
            'pickup_time' => 'nullable|string|max:255',
            'dropoff_time' => 'nullable|string|max:255',
            'video_url' => 'nullable|string|max:500',
            'rating' => 'nullable|numeric|min:1|max:5',
            'review_count' => 'nullable|integer|min:0',
            'priority' => 'required|integer',
            'status' => 'required|string|in:active,inactive',
            'hero_image' => $request->hasFile('hero_image') ? 'file|mimes:jpeg,png,jpg,webp,avif|max:8192' : 'nullable|string|max:500',
            'thumb_image' => $request->hasFile('thumb_image') ? 'file|mimes:jpeg,png,jpg,webp,avif|max:8192' : 'nullable|string|max:500',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        $ext = strtolower((string) ($value->guessExtension() ?: $value->getClientOriginalExtension()));
                        if (! in_array($ext, ['jpeg', 'jpg', 'png', 'webp', 'avif'], true) || $value->getSize() > 8388608) {
                            $fail('Each gallery image upload must be a valid JPG, PNG, WebP, or AVIF image under 8MB.');
                        }
                    } elseif (! is_string($value) || strlen($value) > 500) {
                        $fail('Each gallery image reference must be a valid path string.');
                    }
                },
            ],
        ]);

        $data = $this->normalizeLocalizedData(
            $request->except(['tiers', 'addons', 'hero_image', 'thumb_image', 'gallery_images', 'gallery_images_submitted', 'remove_gallery_images']),
            ['name', 'short_desc', 'full_desc', 'meta_title', 'meta_desc', 'meta_keywords'],
            $tour
        );
        if ($request->filled('slug') || empty($tour->slug)) {
            $slugSource = $request->filled('slug') ? $request->input('slug') : $this->extractSlugSource($request->name);
            $baseSlug = Str::slug($slugSource);
            if (! empty($baseSlug)) {
                $slug = $baseSlug;
                $counter = 1;
                while (Tour::where('slug', $slug)->where('id', '!=', $tour->id)->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }
                $data['slug'] = $slug;
            }
        } else {
            unset($data['slug']);
        }
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_bestseller'] = $request->boolean('is_bestseller');

        if ($request->hasFile('hero_image')) {
            $data['hero_image'] = $request->file('hero_image')->store('tours/hero', 'public');
            try {
                app(\App\Services\MediaManagerService::class)->scanAndSyncFile(public_path('storage/' . $data['hero_image']), 'tours');
            } catch (\Throwable $e) {}
        } elseif ($request->has('hero_image')) {
            $data['hero_image'] = ltrim(trim((string) $request->input('hero_image')), '/');
        }

        if ($request->hasFile('thumb_image')) {
            $data['thumb_image'] = $request->file('thumb_image')->store('tours/thumb', 'public');
            try {
                app(\App\Services\MediaManagerService::class)->scanAndSyncFile(public_path('storage/' . $data['thumb_image']), 'tours');
            } catch (\Throwable $e) {}
        } elseif ($request->has('thumb_image')) {
            $data['thumb_image'] = ltrim(trim((string) $request->input('thumb_image')), '/');
        }

        // Manage gallery images
        if ($request->has('gallery_images') && is_array($request->gallery_images)) {
            $currentGallery = [];
            foreach ($request->gallery_images as $gItem) {
                if ($gItem instanceof \Illuminate\Http\UploadedFile && $gItem->isValid()) {
                    $path = $gItem->store('tours/gallery', 'public');
                    $currentGallery[] = $path;
                    try {
                        app(\App\Services\MediaManagerService::class)->scanAndSyncFile(public_path('storage/' . $path), 'tours');
                    } catch (\Throwable $e) {}
                } elseif (is_string($gItem) && ! empty(trim($gItem))) {
                    $currentGallery[] = ltrim(trim($gItem), '/');
                }
            }
            $data['gallery_images'] = array_values(array_unique($currentGallery));
        } elseif ($request->boolean('gallery_images_submitted')) {
            $data['gallery_images'] = [];
        } elseif ($request->has('remove_gallery_images') && is_array($request->remove_gallery_images)) {
            $currentGallery = is_array($tour->gallery_images) ? $tour->gallery_images : [];
            $data['gallery_images'] = array_values(array_diff($currentGallery, $request->remove_gallery_images));
        }

        $tour->update($data);

        try {
            if (method_exists($tour, 'recordRevision')) {
                $tour->refresh();
                $tour->recordRevision('Updated tour content via CMS editor');
            }
        } catch (\Throwable) {
        }

        // Sync Tiers & Addons
        if ($request->has('tiers')) {
            $hasChildPriceCol = \Illuminate\Support\Facades\Schema::hasColumn('tour_tiers', 'child_price');
            $tiersData = [];
            foreach ($request->input('tiers') as $tierId => $pivot) {
                if (isset($pivot['price']) && $pivot['price'] !== '' && $pivot['price'] !== null) {
                    $tierRow = [
                        'price' => (float) $pivot['price'],
                        'old_price' => ! empty($pivot['old_price']) ? (float) $pivot['old_price'] : null,
                        'price_type' => $pivot['price_type'] ?? 'per person',
                    ];
                    if ($hasChildPriceCol && isset($pivot['child_price']) && $pivot['child_price'] !== '') {
                        $tierRow['child_price'] = (float) $pivot['child_price'];
                    }
                    if (isset($pivot['guest_count']) && $pivot['guest_count'] !== '') {
                        $tierRow['guest_count'] = (int) $pivot['guest_count'];
                    }
                    $tiersData[$tierId] = $tierRow;
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
        } else {
            $tour->addons()->detach();
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
            'guest_count' => 'nullable|integer|min:1|max:100',
            'icon' => 'nullable|string|max:100',
            'badge' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
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
            'guest_count' => $request->filled('guest_count') ? (int) $request->guest_count : 1,
            'icon' => $request->icon ?: 'star-fill',
            'badge' => $request->input('badge'),
            'color' => $request->input('color'),
            'is_popular' => $request->has('is_popular') ? 1 : 0,
            'status' => $request->status,
            'priority' => (int) $request->priority,
        ]);

        $this->clearTourCaches();

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
            'guest_count' => 'nullable|integer|min:1|max:100',
            'icon' => 'nullable|string|max:100',
            'badge' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'is_popular' => 'nullable',
            'status' => 'required|string|in:active,inactive',
            'priority' => 'required|integer',
        ]);

        $tier->update([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'slug' => Str::slug($request->slug),
            'description' => $request->description,
            'guest_count' => $request->filled('guest_count') ? (int) $request->guest_count : 1,
            'icon' => $request->icon ?: 'star-fill',
            'badge' => $request->has('badge') ? $request->input('badge') : $tier->badge,
            'color' => $request->has('color') ? $request->input('color') : $tier->color,
            'is_popular' => $request->has('is_popular') ? 1 : 0,
            'status' => $request->status,
            'priority' => (int) $request->priority,
        ]);

        $this->clearTourCaches();

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

        $this->clearTourCaches();

        return redirect()->route('admin.tiers.index')->with('success', 'Pricing tier deleted successfully.');
    }

    /**
     * Display listing of Addons with Sales Analytics.
     */
    public function addons()
    {
        $addons = Addon::withCount('tours')->orderBy('priority', 'asc')->get();
        $totalBookingsCount = Booking::whereIn('status', ['confirmed', 'completed'])->count();

        $addonStats = DB::table('booking_addons')
            ->join('bookings', 'booking_addons.booking_id', '=', 'bookings.id')
            ->whereNull('bookings.deleted_at')
            ->whereIn('bookings.status', ['confirmed', 'completed'])
            ->select(
                'booking_addons.addon_id',
                DB::raw('COUNT(*) as booked_count'),
                DB::raw('COALESCE(SUM(booking_addons.price * COALESCE(booking_addons.quantity, 1)), 0) as booked_revenue')
            )
            ->groupBy('booking_addons.addon_id')
            ->get()
            ->keyBy('addon_id');

        $totalAddonsRevenue = 0.0;
        $totalAddonsBooked = 0;

        foreach ($addons as $addon) {
            $stat = $addonStats->get($addon->id);
            $bookedCount = $stat ? (int) $stat->booked_count : 0;
            $bookedRevenue = $stat ? (float) $stat->booked_revenue : 0.0;
            $attachmentRate = $totalBookingsCount > 0 ? round(($bookedCount / $totalBookingsCount) * 100, 1) : 0;

            $addon->times_booked = $bookedCount;
            $addon->total_revenue = $bookedRevenue;
            $addon->attachment_rate = $attachmentRate;

            $totalAddonsBooked += $bookedCount;
            $totalAddonsRevenue += $bookedRevenue;
        }

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
            'price_type' => 'nullable|string|in:per_person,per_group',
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
            'price_type' => $request->input('price_type', 'per_person'),
            'status' => $request->status,
            'priority' => (int) $request->priority,
        ]);

        $this->clearTourCaches();

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
            'price_type' => 'nullable|string|in:per_person,per_group',
            'status' => 'required|string|in:active,inactive',
            'priority' => 'required|integer',
        ]);

        $addon->update([
            'name' => $request->name,
            'slug' => Str::slug($request->slug),
            'description' => $request->description,
            'icon' => $request->icon ?: 'plus-lg',
            'default_price' => (float) $request->default_price,
            'price_type' => $request->input('price_type', $addon->price_type ?: 'per_person'),
            'status' => $request->status,
            'priority' => (int) $request->priority,
        ]);

        $this->clearTourCaches();

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

        $this->clearTourCaches();

        return redirect()->route('admin.addons.index')->with('success', 'Addon deleted successfully.');
    }

    /**
     * Display Pricing Grid Matrix.
     */
    public function pricing()
    {
        $tours = Tour::where('status', 'active')->with(['tiers', 'category'])->orderBy('priority', 'asc')->get();
        $tiers = Tier::where('status', 'active')->orderBy('priority', 'asc')->get();

        return view('admin.pricing.index', compact('tours', 'tiers'));
    }

    /**
     * Save pricing changes in bulk.
     */
    public function updatePricing(Request $request)
    {
        $request->validate([
            'pricing' => 'nullable|array',
            'pricing.*' => 'array',
            'pricing.*.*.price' => 'nullable|numeric|min:0',
            'pricing.*.*.old_price' => 'nullable|numeric|min:0',
            'pricing.*.*.child_price' => 'nullable|numeric|min:0',
            'pricing.*.*.guest_count' => 'nullable|integer|min:1|max:500',
            'pricing.*.*.price_type' => 'nullable|string|max:50',
        ]);

        $pricing = $request->input('pricing', []);
        $hasChildPriceCol = \Illuminate\Support\Facades\Schema::hasColumn('tour_tiers', 'child_price');
        $tourIds = array_map('intval', array_keys($pricing));
        $toursById = ! empty($tourIds) ? Tour::whereIn('id', $tourIds)->get()->keyBy('id') : collect();

        foreach ($pricing as $tourId => $tiers) {
            $tour = $toursById->get((int) $tourId);
            if ($tour) {
                $existingPivots = \Illuminate\Support\Facades\DB::table('tour_tiers')
                    ->where('tour_id', $tour->id)
                    ->get()
                    ->keyBy('tier_id');

                $syncData = [];
                foreach ($tiers as $tierId => $prices) {
                    if (isset($prices['price']) && $prices['price'] !== '') {
                        $prev = $existingPivots->get((int) $tierId);
                        $tierRow = [
                            'price' => (float) $prices['price'],
                            'old_price' => ! empty($prices['old_price']) ? (float) $prices['old_price'] : null,
                            'price_type' => $prices['price_type'] ?? ($prev->price_type ?? 'per person'),
                        ];
                        if ($hasChildPriceCol) {
                            if (isset($prices['child_price']) && $prices['child_price'] !== '') {
                                $tierRow['child_price'] = (float) $prices['child_price'];
                            } elseif ($prev && isset($prev->child_price) && $prev->child_price !== null) {
                                $tierRow['child_price'] = (float) $prev->child_price;
                            }
                        }
                        if (isset($prices['guest_count']) && $prices['guest_count'] !== '') {
                            $tierRow['guest_count'] = (int) $prices['guest_count'];
                        } elseif ($prev && isset($prev->guest_count) && $prev->guest_count !== null) {
                            $tierRow['guest_count'] = (int) $prev->guest_count;
                        }
                        $syncData[$tierId] = $tierRow;
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

        $this->clearTourCaches();

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

        $data = $this->normalizeLocalizedData($request->all(), ['title', 'description'], $itinerary);
        $itinerary->update($data);

        $this->clearTourCaches();

        return response()->json(['success' => true, 'message' => 'Itinerary item updated successfully.']);
    }

    /**
     * Delete an itinerary item.
     */
    public function deleteItinerary(string $id)
    {
        $itinerary = Itinerary::findOrFail($id);
        $itinerary->delete();

        $this->clearTourCaches();

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

        $this->clearTourCaches();

        return response()->json(['success' => true, 'message' => 'Content item created successfully.', 'item' => $item]);
    }

    /**
     * Update a global content item.
     */
    public function updateContentItem(Request $request, int $id)
    {
        $item = ContentItem::findOrFail($id);

        $request->validate([
            'type' => 'nullable|string|in:inclusion,exclusion,highlight,not_allowed',
            'title' => 'required',
            'description' => 'nullable',
            'icon' => 'nullable|string|max:255',
            'priority' => 'nullable|integer',
        ]);

        $data = $this->normalizeLocalizedData($request->all(), ['title', 'description'], $item);
        if (! $request->filled('type')) {
            unset($data['type']);
        }
        if (! $request->filled('priority')) {
            unset($data['priority']);
        }
        $item->update($data);

        $this->clearTourCaches();

        return response()->json(['success' => true, 'message' => 'Content item updated successfully.', 'item' => $item]);
    }

    /**
     * Delete a global content item and detach from all tours.
     */
    public function deleteContentItem(int $id)
    {
        $item = ContentItem::findOrFail($id);
        $item->tours()->detach();
        $item->delete();

        $this->clearTourCaches();

        return response()->json(['success' => true, 'message' => 'Content item deleted successfully.']);
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

        $this->clearTourCaches();

        return response()->json(['success' => true, 'message' => 'Tour content items assigned successfully.']);
    }

    /**
     * Add a Category.
     */
    public function addCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $newName = trim($request->input('name'));
        $newSlug = Str::slug($newName);

        $existing = Category::all()->first(function (Category $c) use ($newName, $newSlug) {
            return $c->slug === $newSlug || strcasecmp((string) $c->getTranslation('name', 'en'), $newName) === 0;
        });

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'A category with this name already exists.'], 422);
        }

        $category = Category::create([
            'name' => ['en' => $newName],
            'slug' => $newSlug,
        ]);

        $this->clearTourCaches();

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

        $oldName = trim($request->input('old'));
        $oldSlug = Str::slug($oldName);
        $newName = trim($request->input('new'));
        $newSlug = Str::slug($newName);

        $category = Category::all()->first(function (Category $c) use ($oldName, $oldSlug) {
            return $c->slug === $oldSlug
                || strcasecmp((string) $c->getTranslation('name', 'en'), $oldName) === 0
                || strcasecmp((string) $c->name, $oldName) === 0;
        });

        if (! $category) {
            abort(404, 'Category not found.');
        }

        $category->setTranslation('name', 'en', $newName);
        $category->slug = $newSlug;
        $category->save();

        $this->clearTourCaches();

        return response()->json(['success' => true, 'message' => 'Category renamed successfully.']);
    }

    /**
     * Update a Category by ID.
     */
    public function updateCategory(Request $request, int $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:100',
            'priority' => 'nullable|integer',
        ]);

        $newName = trim((string) $request->input('name'));
        $newSlug = Str::slug($newName) ?: 'category-'.$category->id;

        if (Category::where('slug', $newSlug)->where('id', '!=', $category->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Another category already uses this name/slug.'], 422);
        }

        $category->setTranslation('name', 'en', $newName);
        $category->slug = $newSlug;
        if ($request->has('icon')) {
            $category->icon = $request->input('icon');
        }
        if ($request->filled('priority')) {
            $category->priority = (int) $request->input('priority');
        }
        $category->save();

        $this->clearTourCaches();

        return response()->json(['success' => true, 'message' => 'Category updated successfully.', 'category' => $category]);
    }

    /**
     * Delete a Category by ID (guarded against active tour references).
     */
    public function deleteCategory(int $id)
    {
        $category = Category::withCount('tours')->findOrFail($id);

        if ($category->tours_count > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete category '{$category->name}' because {$category->tours_count} tour(s) are assigned to it. Reassign those tours first.",
            ], 422);
        }

        $category->delete();
        $this->clearTourCaches();

        return response()->json(['success' => true, 'message' => 'Category deleted successfully.']);
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
        Cache::forget('feeds_tours_rss_xml');
        $locales = array_unique(array_merge(['en', 'ar', 'ru', 'es', 'it'], Language::getActiveCodes()));
        foreach ($locales as $loc) {
            Cache::forget('site_active_tours_search_'.$loc);
            Cache::forget('site_search_modal_catalog_v4_'.$loc);
        }
        Cache::forget('sitemap_tours_xml');
        Cache::forget('sitemap_images_xml');
        Cache::forget('sitemap_pages_xml');
        \App\Http\Controllers\SitemapController::flushAllSitemapCaches();
        \App\Http\Controllers\GoogleThingsToDoFeedController::flushCache();
    }
}
