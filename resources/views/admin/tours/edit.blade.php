@extends('layouts.admin')

@section('page_title', 'Edit Tour: ' . $tour->name)

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('admin.tours.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-primary bg-white border border-slate-200 rounded-full px-3 py-1.5 shadow-2xs hover:bg-slate-50 transition-all">
            <i class="bi bi-chevron-left text-xs"></i> Back to List
        </a>
        
        <div class="flex items-center gap-2">
            <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold shadow-2xs transition-all" onclick="openCategoryManager()">
                <i class="bi bi-folder2-open text-primary"></i> Category Manager
            </button>
            <a href="{{ route('tours.show', $tour->slug) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-sky-200 text-sky-600 bg-sky-50/50 hover:bg-sky-50 text-xs font-bold shadow-2xs transition-all">
                <i class="bi bi-box-arrow-up-right"></i> Preview Tour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Column (Core Forms & Details) -->
        <div class="lg:col-span-8 space-y-6">
            <!-- Core Information Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-sm font-black text-slate-900">Core Information</h2>
                    <span class="text-xs font-mono text-slate-400">ID: #{{ $tour->id }}</span>
                </div>
                
                <div class="p-6">
                    <form action="{{ route('admin.tours.update', $tour->id) }}" method="POST" enctype="multipart/form-data" id="editTourForm">
                        @csrf
                        @method('PUT')

                        <div class="space-y-4">
                            <div>
                                <label for="tour_name" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Tour Name *</label>
                                <input type="text" name="name" id="tour_name" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('name', $tour->name) }}" required>
                            </div>

                            <div>
                                <label for="category_id" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Category *</label>
                                <select name="category_id" id="category_id" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $tour->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="short_desc" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Short Description *</label>
                                <textarea name="short_desc" id="short_desc" rows="2" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" required>{{ old('short_desc', $tour->short_desc) }}</textarea>
                            </div>

                            <div>
                                <label for="full_desc" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Full Description *</label>
                                <textarea name="full_desc" id="full_desc" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm text-slate-800 wysiwyg-editor focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" rows="6" required>{{ old('full_desc', $tour->full_desc) }}</textarea>
                            </div>

                            <!-- Package Tiers -->
                            <div class="pt-4 border-t border-slate-100">
                                <h3 class="text-xs font-black uppercase tracking-wider text-primary mb-3">Package Tiers Pricing</h3>
                                <div class="space-y-3">
                                    @foreach($tiers as $tier)
                                    @php
                                        $pivot = $tour->tiers->firstWhere('id', $tier->id)?->pivot;
                                    @endphp
                                    <div class="bg-slate-50/80 rounded-xl border border-slate-200/80 p-3.5">
                                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                                            <div class="sm:col-span-3">
                                                <div class="text-xs font-bold text-slate-900">{{ $tier->display_name }}</div>
                                                <div class="text-[10px] text-slate-400 font-mono">{{ $tier->name }}</div>
                                            </div>
                                            <div class="sm:col-span-3">
                                                <div class="relative">
                                                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-xs font-bold text-slate-400">AED</span>
                                                    <input type="number" name="tiers[{{ $tier->id }}][price]" step="0.01" class="w-full rounded-xl border border-slate-200 bg-white pl-11 pr-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ $pivot ? $pivot->price : '' }}" placeholder="Price">
                                                </div>
                                            </div>
                                            <div class="sm:col-span-3">
                                                <div class="relative">
                                                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-xs font-bold text-slate-400">AED</span>
                                                    <input type="number" name="tiers[{{ $tier->id }}][old_price]" step="0.01" class="w-full rounded-xl border border-slate-200 bg-white pl-11 pr-2.5 py-1.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ $pivot ? $pivot->old_price : '' }}" placeholder="Original">
                                                </div>
                                            </div>
                                            <div class="sm:col-span-3">
                                                <select name="tiers[{{ $tier->id }}][price_type]" class="w-full rounded-xl border border-slate-200 bg-white px-2 py-1.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden">
                                                    <option value="per person" {{ $pivot && $pivot->price_type === 'per person' ? 'selected' : '' }}>Per Person</option>
                                                    <option value="per group" {{ $pivot && $pivot->price_type === 'per group' ? 'selected' : '' }}>Per Group</option>
                                                    <option value="per car" {{ $pivot && $pivot->price_type === 'per car' ? 'selected' : '' }}>Per Car</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Addons -->
                            <div class="pt-4 border-t border-slate-100">
                                <h3 class="text-xs font-black uppercase tracking-wider text-primary mb-3">Addons Pricing</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($addons as $addon)
                                    @php
                                        $pivotAddon = $tour->addons->firstWhere('id', $addon->id)?->pivot;
                                        $hasAddon = $pivotAddon !== null;
                                    @endphp
                                    <div class="p-3.5 border border-slate-200 rounded-xl bg-slate-50/50 space-y-2">
                                        <label class="flex items-center gap-2.5 cursor-pointer">
                                            <input class="rounded border-slate-300 text-primary focus:ring-primary addon-checkbox" type="checkbox" id="addon_{{ $addon->id }}" {{ $hasAddon ? 'checked' : '' }} onchange="toggleAddonPrice({{ $addon->id }})">
                                            <span class="text-xs font-bold text-slate-800">{{ $addon->name }}</span>
                                        </label>
                                        <div class="{{ $hasAddon ? '' : 'hidden' }}" id="addon_price_group_{{ $addon->id }}">
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xs font-bold text-slate-400">AED</span>
                                                <input type="number" name="addons[{{ $addon->id }}][price]" id="addon_price_{{ $addon->id }}" step="0.01" class="w-full rounded-xl border border-slate-200 bg-white pl-11 pr-3 py-1.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ $pivotAddon ? $pivotAddon->price : '' }}" placeholder="0.00" {{ $hasAddon ? '' : 'disabled' }}>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex justify-end">
                    <button type="submit" form="editTourForm" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition-all">
                        <i class="bi bi-save"></i> Save Core Changes
                    </button>
                </div>
            </div>

            <!-- Itinerary Builder Widget -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-sm font-black text-slate-900">Itinerary Builder</h2>
                    <span class="text-xs text-slate-400">{{ $tour->itineraries->count() }} Step(s)</span>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Existing Itinerary Items -->
                    <div class="space-y-3" id="itineraryListContainer">
                        @forelse($tour->itineraries as $it)
                        <div class="p-4 bg-slate-50/80 rounded-xl border border-slate-200/80" id="it_item_{{ $it->id }}">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary text-white">
                                        {{ $it->time }}
                                    </span>
                                    <strong class="text-xs font-bold text-slate-900">{{ $it->title }}</strong>
                                    @if($it->duration)
                                        <span class="text-xs text-slate-400 flex items-center gap-1">
                                            <i class="bi bi-clock"></i> {{ $it->duration }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" class="inline-flex items-center px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-bold transition shadow-2xs" onclick="showEditItineraryForm({{ $it->id }})">Edit</button>
                                    <button type="button" class="inline-flex items-center px-2.5 py-1 rounded-lg border border-rose-200 bg-white hover:bg-rose-50 text-rose-600 text-xs font-bold transition shadow-2xs" onclick="deleteItinerary({{ $it->id }})">Delete</button>
                                </div>
                            </div>
                            @if($it->description)
                                <p class="text-xs text-slate-500 mt-2 leading-relaxed">{{ $it->description }}</p>
                            @endif

                            <!-- Edit inline form -->
                            <form id="it_edit_form_{{ $it->id }}" class="hidden mt-3 border-t border-slate-200/80 pt-3 itinerary-edit-form">
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                                    <div class="sm:col-span-3">
                                        <input type="text" name="time" class="w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-800 outline-hidden focus:border-primary" value="{{ $it->time }}" placeholder="Time (e.g. 03:00 PM)">
                                    </div>
                                    <div class="sm:col-span-4">
                                        <input type="text" name="title" class="w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-800 outline-hidden focus:border-primary" value="{{ $it->title }}" placeholder="Title">
                                    </div>
                                    <div class="sm:col-span-3">
                                        <input type="text" name="duration" class="w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-800 outline-hidden focus:border-primary" value="{{ $it->duration }}" placeholder="Duration">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <input type="number" name="priority" class="w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-800 outline-hidden focus:border-primary" value="{{ $it->priority }}" placeholder="Sort">
                                    </div>
                                    <div class="sm:col-span-12">
                                        <textarea name="description" class="w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-800 outline-hidden focus:border-primary" rows="2" placeholder="Description">{{ $it->description }}</textarea>
                                    </div>
                                    <div class="sm:col-span-12 flex justify-end gap-1.5">
                                        <button type="button" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 text-xs font-bold" onclick="hideEditItineraryForm({{ $it->id }})">Cancel</button>
                                        <button type="button" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-primary text-white text-xs font-bold" onclick="saveItinerary({{ $it->id }})">Save</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        @empty
                        <p class="text-xs text-slate-400 text-center py-4" id="noItineraryMsg">No itinerary items defined yet.</p>
                        @endforelse
                    </div>

                    <!-- Add New Itinerary Item -->
                    <div class="p-4 bg-slate-50/80 rounded-xl border border-slate-200/80 space-y-3">
                        <h3 class="text-xs font-black uppercase text-slate-700 tracking-wider">Add New Step</h3>
                        <form id="newItineraryForm">
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                                <div class="sm:col-span-3">
                                    <input type="text" name="time" id="newItTime" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" placeholder="Time (e.g. 2:30 PM)" required>
                                </div>
                                <div class="sm:col-span-4">
                                    <input type="text" name="title" id="newItTitle" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" placeholder="Activity Title" required>
                                </div>
                                <div class="sm:col-span-3">
                                    <input type="text" name="duration" id="newItDuration" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" placeholder="Duration (e.g. 30 Mins)">
                                </div>
                                <div class="sm:col-span-2">
                                    <input type="number" name="priority" id="newItPriority" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" value="99" required>
                                </div>
                                <div class="sm:col-span-12">
                                    <textarea name="description" id="newItDesc" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" rows="2" placeholder="Describe this step"></textarea>
                                </div>
                                <div class="sm:col-span-12 flex justify-end">
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition-all">
                                        <i class="bi bi-plus-lg"></i> Add Step
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Content Item Assignments Widget -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-sm font-black text-slate-900">Tour Content Assignment</h2>
                    <button type="button" class="inline-flex items-center gap-1.5 text-xs font-bold text-primary hover:underline" onclick="openAddContentItemModal()">
                        <i class="bi bi-plus-circle"></i> Create New Item Globally
                    </button>
                </div>
                <div class="p-6">
                    @php
                        $contentByType = \App\Models\ContentItem::orderBy('priority', 'asc')->get()->groupBy('type');
                        $assignedContentIds = $tour->contentItems->pluck('id')->toArray();
                    @endphp
                    <form id="tourContentForm">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach(['inclusion' => 'Inclusions', 'exclusion' => 'Exclusions', 'highlight' => 'Highlights', 'not_allowed' => 'Not Allowed'] as $typeKey => $label)
                            <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50">
                                <h3 class="text-xs font-black uppercase text-slate-700 tracking-wider mb-3 pb-2 border-b border-slate-200 flex items-center justify-between">
                                    <span>{{ $label }}</span>
                                </h3>
                                <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                    @forelse($contentByType->get($typeKey, collect()) as $ci)
                                    <label class="flex items-center gap-2 cursor-pointer p-1.5 hover:bg-white rounded-lg transition-colors">
                                        <input class="rounded border-slate-300 text-primary focus:ring-primary" type="checkbox" name="{{ $typeKey }}[]" value="{{ $ci->id }}" id="ci_{{ $typeKey }}_{{ $ci->id }}" {{ in_array($ci->id, $assignedContentIds) ? 'checked' : '' }}>
                                        <span class="text-xs text-slate-800">{{ $ci->title }}</span>
                                    </label>
                                    @empty
                                    <div class="text-xs text-slate-400 italic py-2">No items. Create one globally.</div>
                                    @endforelse
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="flex justify-end mt-5">
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition-all">
                                <i class="bi bi-save"></i> Save Content Assignments
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column (Display Options, SEO, Media) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Display & Visibility Options -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4">
                <h3 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                    <i class="bi bi-sliders text-base"></i> Display & Visibility Options
                </h3>
                
                <div>
                    <label for="tour_duration" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Duration Info</label>
                    <input type="text" name="duration" form="editTourForm" id="tour_duration" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('duration', $tour->duration) }}" placeholder="e.g. 6 Hours">
                </div>

                <div>
                    <label for="tour_pickup" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Pickup Time</label>
                    <input type="text" name="pickup_time" form="editTourForm" id="tour_pickup" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('pickup_time', $tour->pickup_time) }}" placeholder="e.g. 2:30 PM">
                </div>

                <div>
                    <label for="tour_dropoff" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Dropoff Time</label>
                    <input type="text" name="dropoff_time" form="editTourForm" id="tour_dropoff" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('dropoff_time', $tour->dropoff_time) }}" placeholder="e.g. 9:30 PM">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="tour_min_age" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Min Age</label>
                        <input type="number" name="min_age" form="editTourForm" id="tour_min_age" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('min_age', $tour->min_age) }}">
                    </div>
                    <div>
                        <label for="tour_group_size" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Group Size</label>
                        <input type="text" name="group_size" form="editTourForm" id="tour_group_size" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('group_size', $tour->group_size) }}" placeholder="e.g. Up to 6">
                    </div>
                </div>

                <div>
                    <label for="tour_languages" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Languages</label>
                    <input type="text" name="languages" form="editTourForm" id="tour_languages" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('languages', $tour->languages) }}">
                </div>

                <div>
                    <label for="tour_priority" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Sort Order</label>
                    <input type="number" name="priority" form="editTourForm" id="tour_priority" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('priority', $tour->priority) }}">
                </div>

                <div>
                    <label for="tour_status" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Visibility Status</label>
                    <select name="status" form="editTourForm" id="tour_status" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden bg-white">
                        <option value="active" {{ old('status', $tour->status) === 'active' ? 'selected' : '' }}>Active & Published</option>
                        <option value="inactive" {{ old('status', $tour->status) === 'inactive' ? 'selected' : '' }}>Hidden / Draft</option>
                    </select>
                </div>
            </div>

            <!-- Featured Switches -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                    <i class="bi bi-bookmark-star text-base"></i> Attribution Flags
                </h3>
                <label class="flex items-center gap-3 cursor-pointer p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                    <input class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4" type="checkbox" name="is_featured" form="editTourForm" id="is_featured" value="1" {{ old('is_featured', $tour->is_featured) ? 'checked' : '' }}>
                    <span class="text-xs font-bold text-slate-800">Featured Tour</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                    <input class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4" type="checkbox" name="is_bestseller" form="editTourForm" id="is_bestseller" value="1" {{ old('is_bestseller', $tour->is_bestseller) ? 'checked' : '' }}>
                    <span class="text-xs font-bold text-slate-800">Bestseller Badge</span>
                </label>
            </div>

            <!-- SEO -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4">
                <h3 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                    <i class="bi bi-search text-base"></i> SEO Customization
                </h3>
                <div>
                    <label for="meta_title" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Meta Title</label>
                    <input type="text" name="meta_title" form="editTourForm" id="meta_title" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('meta_title', $tour->meta_title) }}">
                </div>
                <div>
                    <label for="meta_desc" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Meta Description</label>
                    <textarea name="meta_desc" form="editTourForm" id="meta_desc" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" rows="3">{{ old('meta_desc', $tour->meta_desc) }}</textarea>
                </div>
                <div>
                    <label for="meta_keywords" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Meta Keywords</label>
                    <input type="text" name="meta_keywords" form="editTourForm" id="meta_keywords" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('meta_keywords', $tour->meta_keywords) }}">
                </div>
            </div>

            <!-- Images -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4">
                <h3 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                    <i class="bi bi-images text-base"></i> Media Uploads
                </h3>
                
                <div>
                    <label for="hero_image" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Hero Banner Image</label>
                    @if($tour->hero_image)
                        <div class="mb-2"><img src="{{ Storage::url($tour->hero_image) }}" alt="{{ $tour->name }}" class="w-full h-20 rounded-xl object-cover border border-slate-200"></div>
                    @endif
                    <input type="file" name="hero_image" form="editTourForm" id="hero_image" class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                </div>

                <div>
                    <label for="thumb_image" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Thumbnail Image</label>
                    @if($tour->thumb_image)
                        <div class="mb-2"><img src="{{ Storage::url($tour->thumb_image) }}" alt="{{ $tour->name }}" class="w-full h-20 rounded-xl object-cover border border-slate-200"></div>
                    @endif
                    <input type="file" name="thumb_image" form="editTourForm" id="thumb_image" class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Manager Modal (Alpine.js) -->
<div x-data="{ open: false }" @open-category-modal.window="open = true" x-show="open" x-cloak class="relative z-50">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-center justify-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-5" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-black text-slate-900">Category Manager</h3>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Existing Categories</label>
                    <div id="catList" class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex flex-wrap gap-1.5">
                        @foreach($categories as $cat)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-white text-slate-800 border border-slate-200 shadow-2xs">{{ $cat->name }}</span>
                        @endforeach
                    </div>
                </div>
                
                <div class="pt-3 border-t border-slate-100">
                    <label for="newCat" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Add New Category</label>
                    <div class="flex gap-2">
                        <input type="text" id="newCat" class="flex-1 rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" placeholder="Category Name (e.g. Quad Biking)">
                        <button type="button" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white font-bold text-xs rounded-xl shadow-xs transition" onclick="addCategory()">Add</button>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100">
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Rename Category</label>
                    <div class="grid grid-cols-2 gap-2">
                        <select id="oldCat" class="rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 outline-hidden bg-white">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <div class="flex gap-2">
                            <input type="text" id="renCat" class="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 outline-hidden" placeholder="New Name">
                            <button type="button" class="px-3 py-2 bg-slate-700 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition" onclick="renameCategory()">Rename</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Global Content Item Modal (Alpine.js) -->
<div x-data="{ open: false }" @open-content-modal.window="open = true" x-show="open" x-cloak class="relative z-50">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-center justify-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-black text-slate-900">Create New Content Item</h3>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
            </div>
            <form id="newContentItemForm" class="space-y-4">
                <div>
                    <label for="newCiType" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Type</label>
                    <select id="newCiType" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden bg-white" required>
                        <option value="inclusion">Inclusion</option>
                        <option value="exclusion">Exclusion</option>
                        <option value="highlight">Highlight</option>
                        <option value="not_allowed">Not Allowed</option>
                    </select>
                </div>
                <div>
                    <label for="newCiTitle" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Title</label>
                    <input type="text" id="newCiTitle" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden" required placeholder="e.g. Free Hotel Pickup">
                </div>
                <div>
                    <label for="newCiDesc" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Description (Optional)</label>
                    <input type="text" id="newCiDesc" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden" placeholder="e.g. Within Dubai city limits">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="newCiIcon" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Icon Class</label>
                        <input type="text" id="newCiIcon" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 outline-hidden" placeholder="bi-check2">
                    </div>
                    <div>
                        <label for="newCiPriority" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Sort Priority</label>
                        <input type="number" id="newCiPriority" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 outline-hidden" value="99">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition">Create & Load</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Category Manager
function openCategoryManager() {
    window.dispatchEvent(new CustomEvent('open-category-modal'));
}
function addCategory() {
    const name = $('#newCat').val().trim();
    if(!name) { Swal.fire('Error', 'Category name cannot be empty.', 'error'); return; }
    
    showLoader();
    $.ajax({
        url: "{{ route('admin.categories.create') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            name: name
        },
        success: function(res) {
            hideLoader();
            if(res.success) {
                location.reload();
            }
        },
        error: function(xhr) {
            hideLoader();
            Swal.fire('Error', 'Failed to add category.', 'error');
        }
    });
}
function renameCategory() {
    const oldName = $('#oldCat').val();
    const newName = $('#renCat').val().trim();
    if(!oldName || !newName) { Swal.fire('Error', 'Provide both old and new names.', 'error'); return; }
    
    showLoader();
    $.ajax({
        url: "{{ route('admin.categories.rename') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            old: oldName,
            new: newName
        },
        success: function(res) {
            hideLoader();
            if(res.success) {
                location.reload();
            }
        },
        error: function(xhr) {
            hideLoader();
            Swal.fire('Error', 'Failed to rename category.', 'error');
        }
    });
}

// Global Content Items
function openAddContentItemModal() {
    window.dispatchEvent(new CustomEvent('open-content-modal'));
}
$('#newContentItemForm').on('submit', function(e) {
    e.preventDefault();
    showLoader();
    
    const data = {
        _token: "{{ csrf_token() }}",
        type: $('#newCiType').val(),
        title: $('#newCiTitle').val(),
        description: $('#newCiDesc').val(),
        icon: $('#newCiIcon').val(),
        priority: $('#newCiPriority').val()
    };
    
    $.ajax({
        url: "{{ route('admin.tours.content-items.create') }}",
        type: "POST",
        data: data,
        success: function(res) {
            hideLoader();
            if(res.success) {
                location.reload();
            }
        },
        error: function(xhr) {
            hideLoader();
            Swal.fire('Error', 'Failed to create content item.', 'error');
        }
    });
});

// Save content assignments for this tour
$('#tourContentForm').on('submit', function(e) {
    e.preventDefault();
    showLoader();
    
    $.ajax({
        url: "{{ route('admin.tours.content.set', $tour->id) }}",
        type: "POST",
        data: $(this).serialize(),
        success: function(res) {
            hideLoader();
            if(res.success) {
                Swal.fire('Success', res.message, 'success');
            }
        },
        error: function(xhr) {
            hideLoader();
            Swal.fire('Error', 'Failed to save assignments.', 'error');
        }
    });
});

// Itinerary Builder Scripts
function showEditItineraryForm(id) {
    $(`#it_edit_form_${id}`).removeClass('hidden').removeClass('d-none');
}
function hideEditItineraryForm(id) {
    $(`#it_edit_form_${id}`).addClass('hidden');
}
function saveItinerary(id) {
    showLoader();
    const data = $(`#it_edit_form_${id}`).serialize() + `&_token={{ csrf_token() }}`;
    
    $.ajax({
        url: `/admin/itinerary/${id}/update`,
        type: "POST",
        data: data,
        success: function(res) {
            hideLoader();
            if(res.success) {
                location.reload();
            }
        },
        error: function() {
            hideLoader();
            Swal.fire('Error', 'Failed to update itinerary item.', 'error');
        }
    });
}
function deleteItinerary(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'Delete this itinerary item? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            showLoader();
            $.ajax({
                url: `/admin/itinerary/${id}/delete`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    hideLoader();
                    if(res.success) {
                        $(`#it_item_${id}`).remove();
                        if($('#itineraryListContainer').children().length === 0) {
                            $('#itineraryListContainer').html('<p class="text-xs text-slate-400 text-center py-4">No itinerary items defined yet.</p>');
                        }
                    }
                },
                error: function() {
                    hideLoader();
                    Swal.fire('Error', 'Failed to delete itinerary item.', 'error');
                }
            });
        }
    });
}
$('#newItineraryForm').on('submit', function(e) {
    e.preventDefault();
    showLoader();
    const data = $(this).serialize() + `&_token={{ csrf_token() }}`;
    
    $.ajax({
        url: "{{ route('admin.tours.itinerary.add', $tour->id) }}",
        type: "POST",
        data: data,
        success: function(res) {
            hideLoader();
            if(res.success) {
                location.reload();
            }
        },
        error: function() {
            hideLoader();
            Swal.fire('Error', 'Failed to add itinerary step.', 'error');
        }
    });
});

function toggleAddonPrice(id) {
    const isChecked = document.getElementById('addon_' + id).checked;
    const priceGroup = document.getElementById('addon_price_group_' + id);
    const priceInput = document.getElementById('addon_price_' + id);

    if (isChecked) {
        priceGroup.classList.remove('hidden');
        priceGroup.classList.remove('d-none');
        priceInput.removeAttribute('disabled');
        priceInput.setAttribute('required', 'true');
    } else {
        priceGroup.classList.add('hidden');
        priceInput.setAttribute('disabled', 'true');
        priceInput.removeAttribute('required');
        priceInput.value = '';
    }
}
</script>
@endpush
@endsection
