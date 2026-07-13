@extends('layouts.admin')

@section('page_title', 'Create New Tour')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.tours.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 fw-bold">
        <i class="bi bi-chevron-left me-1"></i> Back to List
    </a>
</div>

<div class="card card-modern border shadow-sm rounded-4 bg-white overflow-hidden mb-5">
    <div class="card-header bg-white py-3 border-bottom ps-4 pe-4">
        <h5 class="fw-800 mb-0 text-dark">New Tour Inventory Details</h5>
    </div>
    
    <div class="card-body p-4 ps-4 pe-4">
        <form action="{{ route('admin.tours.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-4">
                <!-- Left Column (Core Details) -->
                <div class="col-lg-8">
                    <div class="p-4 bg-light rounded-4 border mb-4">
                        <h6 class="text-primary fw-800 text-uppercase small mb-4">Basic Information</h6>
                        
                        <div class="mb-3">
                            <label for="tour_name" class="form-label fw-bold text-dark">Tour Name</label>
                            <input type="text" name="name" id="tour_name" class="form-control" placeholder="e.g. Premium Desert Safari" required style="height: 48px; border-radius: 8px;">
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label fw-bold text-dark">Category</label>
                            <select name="category_id" id="category_id" class="form-select" required style="height: 48px; border-radius: 8px;">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="short_desc" class="form-label fw-bold text-dark">Short Description (Excerpt)</label>
                            <textarea name="short_desc" id="short_desc" class="form-control" rows="2" placeholder="Brief tagline shown on cards" required style="border-radius: 8px;"></textarea>
                        </div>

                        <div class="mb-0">
                            <label for="full_desc" class="form-label fw-bold text-dark">Full Description</label>
                            <textarea name="full_desc" id="full_desc" class="form-control" rows="6" placeholder="Detailed description shown on details page" required style="border-radius: 8px;"></textarea>
                        </div>
                    </div>

                    <!-- Package Tiers Pricing Assignment -->
                    <div class="p-4 bg-light rounded-4 border mb-4">
                        <h6 class="text-primary fw-800 text-uppercase small mb-4">Package Tiers (Pricing)</h6>
                        <p class="text-muted small mb-3">Assign prices for this tour across the package tiers. Leave price blank if the tier is not offered for this tour.</p>
                        
                        <div class="row g-3">
                            @foreach($tiers as $tier)
                            <div class="col-12 border-bottom pb-3 mb-2">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-3">
                                        <div class="fw-bold text-dark">{{ $tier->display_name }}</div>
                                        <div class="text-muted small">{{ $tier->name }}</div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">AED</span>
                                            <input type="number" name="tiers[{{ $tier->id }}][price]" step="0.01" class="form-control" placeholder="Price">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Old AED</span>
                                            <input type="number" name="tiers[{{ $tier->id }}][old_price]" step="0.01" class="form-control" placeholder="Original">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <select name="tiers[{{ $tier->id }}][price_type]" class="form-select form-select-sm">
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
                    <div class="p-4 bg-light rounded-4 border mb-4">
                        <h6 class="text-primary fw-800 text-uppercase small mb-4">Addons Pricing</h6>
                        <p class="text-muted small mb-3">Select the addons available for this tour and customize their prices.</p>
                        
                        <div class="row g-3">
                            @foreach($addons as $addon)
                            <div class="col-md-6">
                                <div class="card p-3 border rounded-3 bg-white">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input addon-checkbox" type="checkbox" id="addon_{{ $addon->id }}" onchange="toggleAddonPrice({{ $addon->id }})">
                                        <label class="form-check-label fw-bold text-dark small" for="addon_{{ $addon->id }}">
                                            {{ $addon->name }}
                                        </label>
                                    </div>
                                    <div class="input-group input-group-sm d-none" id="addon_price_group_{{ $addon->id }}">
                                        <span class="input-group-text">AED</span>
                                        <input type="number" name="addons[{{ $addon->id }}][price]" id="addon_price_{{ $addon->id }}" step="0.01" class="form-control" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Right Column (Display Options & Media) -->
                <div class="col-lg-4">
                    <div class="p-4 bg-light rounded-4 border mb-4">
                        <h6 class="text-primary fw-800 text-uppercase small mb-4">Display & Visibility</h6>
                        
                        <div class="mb-3">
                            <label for="duration" class="form-label fw-bold text-dark">Duration (Hours)</label>
                            <input type="text" name="duration" id="duration" class="form-control" placeholder="e.g. 6 Hours" required style="border-radius: 8px;">
                        </div>

                        <div class="mb-3">
                            <label for="pickup_time" class="form-label fw-bold text-dark">Pickup Time Range</label>
                            <input type="text" name="pickup_time" id="pickup_time" class="form-control" placeholder="e.g. 2:30 PM - 3:00 PM" required style="border-radius: 8px;">
                        </div>

                        <div class="mb-3">
                            <label for="dropoff_time" class="form-label fw-bold text-dark">Dropoff Time Range</label>
                            <input type="text" name="dropoff_time" id="dropoff_time" class="form-control" placeholder="e.g. 9:00 PM - 9:30 PM" required style="border-radius: 8px;">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="min_age" class="form-label fw-bold text-dark">Min Age</label>
                                <input type="number" name="min_age" id="min_age" class="form-control" value="3" required style="border-radius: 8px;">
                            </div>
                            <div class="col-6">
                                <label for="group_size" class="form-label fw-bold text-dark">Group Size</label>
                                <input type="text" name="group_size" id="group_size" class="form-control" placeholder="e.g. Up to 6" style="border-radius: 8px;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="languages" class="form-label fw-bold text-dark">Languages Supported</label>
                            <input type="text" name="languages" id="languages" class="form-control" value="English, Arabic" required style="border-radius: 8px;">
                        </div>

                        <div class="mb-3">
                            <label for="priority" class="form-label fw-bold text-dark">Priority Sort Order</label>
                            <input type="number" name="priority" id="priority" class="form-control" value="99" required style="border-radius: 8px;">
                        </div>

                        <div class="mb-0">
                            <label for="status" class="form-label fw-bold text-dark">Status</label>
                            <select name="status" id="status" class="form-select" required style="border-radius: 8px;">
                                <option value="active">Active & Published</option>
                                <option value="inactive">Hidden / Draft</option>
                            </select>
                        </div>
                    </div>

                    <div class="p-4 bg-light rounded-4 border mb-4">
                        <h6 class="text-primary fw-800 text-uppercase small mb-4">Attribution Flags</h6>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1">
                            <label class="form-check-label fw-bold text-dark" for="is_featured">Featured Tour</label>
                        </div>

                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="is_bestseller" id="is_bestseller" value="1">
                            <label class="form-check-label fw-bold text-dark" for="is_bestseller">Bestseller Badge</label>
                        </div>
                    </div>

                    <div class="p-4 bg-light rounded-4 border mb-4">
                        <h6 class="text-primary fw-800 text-uppercase small mb-4">Media uploads</h6>
                        
                        <div class="mb-3">
                            <label for="hero_image" class="form-label fw-bold text-dark">Hero Banner Image</label>
                            <input type="file" name="hero_image" id="hero_image" class="form-control">
                            <div class="form-text">Dimensions: 1920x800 recommended. Max 4MB.</div>
                        </div>

                        <div class="mb-0">
                            <label for="thumb_image" class="form-label fw-bold text-dark">Thumbnail Image</label>
                            <input type="file" name="thumb_image" id="thumb_image" class="form-control">
                            <div class="form-text">Dimensions: 600x400 recommended. Max 2MB.</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 text-end mt-4">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold">Create Tour Inventory</button>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
function toggleAddonPrice(id) {
    const isChecked = document.getElementById('addon_' + id).checked;
    const priceGroup = document.getElementById('addon_price_group_' + id);
    const priceInput = document.getElementById('addon_price_' + id);

    if (isChecked) {
        priceGroup.classList.remove('d-none');
        priceInput.removeAttribute('disabled');
        priceInput.setAttribute('required', 'true');
    } else {
        priceGroup.classList.add('d-none');
        priceInput.setAttribute('disabled', 'true');
        priceInput.removeAttribute('required');
        priceInput.value = '';
    }
}
</script>
@endsection
