@extends('layouts.admin')

@section('page_title', 'Create New Tour')

@section('content')
<div class="space-y-6">
    <!-- Top Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.tours.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-primary bg-white border border-slate-200 rounded-full px-3 py-1.5 shadow-2xs hover:bg-slate-50 transition-all">
            <i class="bi bi-chevron-left text-xs"></i> Back to List
        </a>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h1 class="text-base font-black text-slate-900">New Tour Inventory Details</h1>
            <span class="text-xs text-slate-400">All required fields marked *</span>
        </div>
        
        <div class="p-6">
            <form action="{{ route('admin.tours.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Left Column (Core Details) -->
                    <div class="lg:col-span-8 space-y-6">
                        <!-- Basic Information -->
                        <div class="bg-slate-50/80 rounded-2xl border border-slate-200/80 p-5 space-y-4">
                            <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                                <i class="bi bi-info-circle text-base"></i> Basic Information
                            </h2>
                            
                            <div>
                                <label for="tour_name" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Tour Name *</label>
                                <input type="text" name="name" id="tour_name" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('name') }}" placeholder="e.g. Premium Desert Safari" required>
                                @error('name')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="category_id" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Category *</label>
                                <select name="category_id" id="category_id" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="short_desc" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Short Description (Excerpt) *</label>
                                <textarea name="short_desc" id="short_desc" rows="2" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" placeholder="Brief tagline shown on cards" required>{{ old('short_desc') }}</textarea>
                                @error('short_desc')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="full_desc" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Full Description *</label>
                                <textarea name="full_desc" id="full_desc" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm text-slate-800 wysiwyg-editor focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" rows="6" placeholder="Detailed description shown on details page" required>{{ old('full_desc') }}</textarea>
                                @error('full_desc')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Package Tiers Pricing Assignment -->
                        <div class="bg-slate-50/80 rounded-2xl border border-slate-200/80 p-5 space-y-4">
                            <div>
                                <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                                    <i class="bi bi-tags text-base"></i> Package Tiers (Pricing)
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">Assign prices for this tour across the package tiers. Leave price blank if the tier is not offered for this tour.</p>
                            </div>
                            
                            <div class="space-y-3">
                                @foreach($tiers as $tier)
                                <div class="bg-white rounded-xl border border-slate-200 p-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                                        <div class="sm:col-span-4">
                                            <div class="text-sm font-bold text-slate-900">{{ $tier->display_name }}</div>
                                            <div class="text-xs text-slate-400 font-mono">{{ $tier->name }}</div>
                                        </div>
                                        <div class="sm:col-span-3">
                                            <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Price (AED)</label>
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xs font-bold text-slate-400">AED</span>
                                                <input type="number" name="tiers[{{ $tier->id }}][price]" step="0.01" class="w-full rounded-xl border border-slate-200 pl-11 pr-3 py-1.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="sm:col-span-3">
                                            <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Original Price (AED)</label>
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xs font-bold text-slate-400">AED</span>
                                                <input type="number" name="tiers[{{ $tier->id }}][old_price]" step="0.01" class="w-full rounded-xl border border-slate-200 pl-11 pr-3 py-1.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" placeholder="Original">
                                            </div>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Price Type</label>
                                            <select name="tiers[{{ $tier->id }}][price_type]" class="w-full rounded-xl border border-slate-200 px-2 py-1.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden bg-white">
                                                <option value="per person">Per Person</option>
                                                <option value="per group">Per Group</option>
                                                <option value="per car">Per Car</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Addons Pricing Assignment -->
                        <div class="bg-slate-50/80 rounded-2xl border border-slate-200/80 p-5 space-y-4">
                            <div>
                                <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                                    <i class="bi bi-puzzle text-base"></i> Addons Pricing
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">Select the addons available for this tour and customize their prices.</p>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($addons as $addon)
                                <div class="p-3.5 border border-slate-200 rounded-xl bg-white space-y-2">
                                    <label class="flex items-center gap-2.5 cursor-pointer">
                                        <input class="rounded border-slate-300 text-primary focus:ring-primary addon-checkbox" type="checkbox" id="addon_{{ $addon->id }}" onchange="toggleAddonPrice({{ $addon->id }})">
                                        <span class="text-xs font-bold text-slate-800">{{ $addon->name }}</span>
                                    </label>
                                    <div class="hidden" id="addon_price_group_{{ $addon->id }}">
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xs font-bold text-slate-400">AED</span>
                                            <input type="number" name="addons[{{ $addon->id }}][price]" id="addon_price_{{ $addon->id }}" step="0.01" class="w-full rounded-xl border border-slate-200 pl-11 pr-3 py-1.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" placeholder="0.00" disabled>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Right Column (Display Options & Media) -->
                    <div class="lg:col-span-4 space-y-6">
                        <!-- Display & Visibility -->
                        <div class="bg-slate-50/80 rounded-2xl border border-slate-200/80 p-5 space-y-4">
                            <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                                <i class="bi bi-sliders text-base"></i> Display & Visibility
                            </h2>
                            
                            <div>
                                <label for="duration" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Duration *</label>
                                <input type="text" name="duration" id="duration" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('duration') }}" placeholder="e.g. 6 Hours" required>
                            </div>

                            <div>
                                <label for="pickup_time" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Pickup Time Range *</label>
                                <input type="text" name="pickup_time" id="pickup_time" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('pickup_time') }}" placeholder="e.g. 2:30 PM - 3:00 PM" required>
                            </div>

                            <div>
                                <label for="dropoff_time" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Dropoff Time Range *</label>
                                <input type="text" name="dropoff_time" id="dropoff_time" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('dropoff_time') }}" placeholder="e.g. 9:00 PM - 9:30 PM" required>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="min_age" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Min Age *</label>
                                    <input type="number" name="min_age" id="min_age" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('min_age', 3) }}" required>
                                </div>
                                <div>
                                    <label for="group_size" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Group Size</label>
                                    <input type="text" name="group_size" id="group_size" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('group_size') }}" placeholder="e.g. Up to 6">
                                </div>
                            </div>

                            <div>
                                <label for="languages" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Languages Supported *</label>
                                <input type="text" name="languages" id="languages" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('languages', 'English, Arabic') }}" required>
                            </div>

                            <div>
                                <label for="priority" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Priority Sort Order *</label>
                                <input type="number" name="priority" id="priority" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('priority', 99) }}" required>
                            </div>

                            <div>
                                <label for="status" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Status *</label>
                                <select name="status" id="status" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" required>
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active & Published</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Hidden / Draft</option>
                                </select>
                            </div>
                        </div>

                        <!-- Attribution Flags -->
                        <div class="bg-slate-50/80 rounded-2xl border border-slate-200/80 p-5 space-y-3">
                            <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                                <i class="bi bi-bookmark-star text-base"></i> Attribution Flags
                            </h2>
                            
                            <label class="flex items-center gap-3 cursor-pointer p-2.5 bg-white rounded-xl border border-slate-200">
                                <input class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4" type="checkbox" name="is_featured" id="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                <span class="text-xs font-bold text-slate-800">Featured Tour</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer p-2.5 bg-white rounded-xl border border-slate-200">
                                <input class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4" type="checkbox" name="is_bestseller" id="is_bestseller" value="1" {{ old('is_bestseller') ? 'checked' : '' }}>
                                <span class="text-xs font-bold text-slate-800">Bestseller Badge</span>
                            </label>
                        </div>

                        <!-- Media Uploads -->
                        <div class="bg-slate-50/80 rounded-2xl border border-slate-200/80 p-5 space-y-4">
                            <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                                <i class="bi bi-images text-base"></i> Media Uploads
                            </h2>
                            
                            <div>
                                <label for="hero_image" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Hero Banner Image</label>
                                <input type="file" name="hero_image" id="hero_image" class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                                <p class="text-[11px] text-slate-400 mt-1">1920x800 recommended. Max 4MB.</p>
                            </div>

                            <div>
                                <label for="thumb_image" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Thumbnail Image</label>
                                <input type="file" name="thumb_image" id="thumb_image" class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                                <p class="text-[11px] text-slate-400 mt-1">600x400 recommended. Max 2MB.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Footer -->
                    <div class="lg:col-span-12 flex justify-end pt-4 border-t border-slate-100">
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-sm shadow-xs transition-all">
                            <i class="bi bi-check-lg"></i> Create Tour Inventory
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleAddonPrice(id) {
    const isChecked = document.getElementById('addon_' + id).checked;
    const priceGroup = document.getElementById('addon_price_group_' + id);
    const priceInput = document.getElementById('addon_price_' + id);

    if (isChecked) {
        priceGroup.classList.remove('hidden');
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
@endsection
