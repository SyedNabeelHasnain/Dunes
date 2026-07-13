@extends('layouts.admin')

@section('page_title', 'Edit Tour: ' . $tour->name)

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <a href="{{ route('admin.tours.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 fw-bold">
        <i class="bi bi-chevron-left me-1"></i> Back to List
    </a>
    
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold" onclick="openCategoryManager()">
            <i class="bi bi-folder2-open me-1"></i> Category Manager
        </button>
        <a href="{{ route('tours.show', $tour->slug) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold">
            <i class="bi bi-box-arrow-up-right me-1"></i> Preview Tour
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column (Core Forms & Details) -->
    <div class="col-lg-8">
        <div class="card card-modern border shadow-sm rounded-4 bg-white overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom ps-4 pe-4">
                <h5 class="fw-800 mb-0 text-dark">Core Information</h5>
            </div>
            <div class="card-body p-4 ps-4 pe-4">
                <form action="{{ route('admin.tours.update', $tour->id) }}" method="POST" enctype="multipart/form-data" id="editTourForm">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="tour_name" class="form-label fw-bold text-dark">Tour Name</label>
                        <input type="text" name="name" id="tour_name" class="form-control" value="{{ $tour->name }}" required style="height: 48px; border-radius: 8px;">
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label fw-bold text-dark">Category</label>
                        <select name="category_id" id="category_id" class="form-select" required style="height: 48px; border-radius: 8px;">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $tour->category_id === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="short_desc" class="form-label fw-bold text-dark">Short Description</label>
                        <textarea name="short_desc" id="short_desc" class="form-control" rows="2" required style="border-radius: 8px;">{{ $tour->short_desc }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label for="full_desc" class="form-label fw-bold text-dark">Full Description</label>
                        <textarea name="full_desc" id="full_desc" class="form-control" rows="6" required style="border-radius: 8px;">{{ $tour->full_desc }}</textarea>
                    </div>

                    <!-- Package Tiers -->
                    <h6 class="text-primary fw-800 text-uppercase small mb-3">Package Tiers Pricing</h6>
                    <div class="row g-3 mb-4">
                        @foreach($tiers as $tier)
                        @php
                            $pivot = $tour->tiers->firstWhere('id', $tier->id)->pivot ?? null;
                        @endphp
                        <div class="col-12 border-bottom pb-3 mb-2">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-3">
                                    <div class="fw-bold text-dark">{{ $tier->display_name }}</div>
                                    <div class="text-muted small">{{ $tier->name }}</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">AED</span>
                                        <input type="number" name="tiers[{{ $tier->id }}][price]" step="0.01" class="form-control" value="{{ $pivot ? $pivot->price : '' }}" placeholder="Price">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Old AED</span>
                                        <input type="number" name="tiers[{{ $tier->id }}][old_price]" step="0.01" class="form-control" value="{{ $pivot ? $pivot->old_price : '' }}" placeholder="Original">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select name="tiers[{{ $tier->id }}][price_type]" class="form-select form-select-sm">
                                        <option value="per person" {{ $pivot && $pivot->price_type === 'per person' ? 'selected' : '' }}>Per Person</option>
                                        <option value="per group" {{ $pivot && $pivot->price_type === 'per group' ? 'selected' : '' }}>Per Group</option>
                                        <option value="per car" {{ $pivot && $pivot->price_type === 'per car' ? 'selected' : '' }}>Per Car</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Addons -->
                    <h6 class="text-primary fw-800 text-uppercase small mb-3">Addons Pricing</h6>
                    <div class="row g-3 mb-4">
                        @foreach($addons as $addon)
                        @php
                            $pivotAddon = $tour->addons->firstWhere('id', $addon->id)->pivot ?? null;
                            $hasAddon = $pivotAddon !== null;
                        @endphp
                        <div class="col-md-6">
                            <div class="card p-3 border rounded-3 bg-white">
                                <div class="form-check mb-2">
                                    <input class="form-check-input addon-checkbox" type="checkbox" id="addon_{{ $addon->id }}" {{ $hasAddon ? 'checked' : '' }} onchange="toggleAddonPrice({{ $addon->id }})">
                                    <label class="form-check-label fw-bold text-dark small" for="addon_{{ $addon->id }}">
                                        {{ $addon->name }}
                                    </label>
                                </div>
                                <div class="input-group input-group-sm {{ $hasAddon ? '' : 'd-none' }}" id="addon_price_group_{{ $addon->id }}">
                                    <span class="input-group-text">AED</span>
                                    <input type="number" name="addons[{{ $addon->id }}][price]" id="addon_price_{{ $addon->id }}" step="0.01" class="form-control" value="{{ $pivotAddon ? $pivotAddon->price : '' }}" placeholder="0.00" {{ $hasAddon ? '' : 'disabled' }}>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </form>
            </div>
            <div class="card-footer bg-white border-top p-3 text-end ps-4 pe-4">
                <button type="submit" form="editTourForm" class="btn btn-primary rounded-pill px-5 py-2 fw-bold">Save Core Changes</button>
            </div>
        </div>

        <!-- Itinerary Builder Widget -->
        <div class="card card-modern border shadow-sm rounded-4 bg-white overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom ps-4 pe-4">
                <h5 class="fw-800 mb-0 text-dark">Itinerary Builder</h5>
            </div>
            <div class="card-body p-4 ps-4 pe-4">
                <!-- Existing Itinerary Items -->
                <div class="mb-4" id="itineraryListContainer">
                    @forelse($tour->itineraries as $it)
                    <div class="p-3 bg-light rounded-4 border mb-2" id="it_item_{{ $it->id }}">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <span class="badge bg-primary rounded-pill text-white px-3 py-1 me-2">{{ $it->time }}</span>
                                <strong class="text-dark">{{ $it->title }}</strong>
                                @if($it->duration)
                                    <span class="text-muted small ms-2"><i class="bi bi-clock me-1"></i>{{ $it->duration }}</span>
                                @endif
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="showEditItineraryForm({{ $it->id }})">Edit</button>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="deleteItinerary({{ $it->id }})">Delete</button>
                            </div>
                        </div>
                        <p class="text-muted small mt-2 mb-0">{{ $it->description }}</p>

                        <!-- Edit inline form -->
                        <form id="it_edit_form_{{ $it->id }}" class="d-none mt-3 border-top pt-3 itinerary-edit-form">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <input type="text" name="time" class="form-control form-control-sm" value="{{ $it->time }}" placeholder="Time (e.g. 03:00 PM)">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="title" class="form-control form-control-sm" value="{{ $it->title }}" placeholder="Title">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="duration" class="form-control form-control-sm" value="{{ $it->duration }}" placeholder="Duration">
                                </div>
                                <div class="col-md-2">
                                    <input type="number" name="priority" class="form-control form-control-sm" value="{{ $it->priority }}" placeholder="Sort Order">
                                </div>
                                <div class="col-12">
                                    <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Description">{{ $it->description }}</textarea>
                                </div>
                                <div class="col-12 text-end">
                                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3 me-1" onclick="hideEditItineraryForm({{ $it->id }})">Cancel</button>
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="saveItinerary({{ $it->id }})">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    @empty
                    <p class="text-muted small text-center py-3" id="noItineraryMsg">No itinerary items defined yet.</p>
                    @endforelse
                </div>

                <!-- Add New Itinerary Item -->
                <div class="p-3 bg-light rounded-4 border">
                    <h6 class="fw-bold mb-3 text-dark">Add New Step</h6>
                    <form id="newItineraryForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="time" id="newItTime" class="form-control form-control-sm" placeholder="Time (e.g. 2:30 PM)" required>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="title" id="newItTitle" class="form-control form-control-sm" placeholder="Activity Title" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="duration" id="newItDuration" class="form-control form-control-sm" placeholder="Duration (e.g. 30 Mins)">
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="priority" id="newItPriority" class="form-control form-control-sm" value="99" required>
                            </div>
                            <div class="col-12">
                                <textarea name="description" id="newItDesc" class="form-control form-control-sm" rows="2" placeholder="Describe this step"></textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">Add Step</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Content Item Assignments Widget -->
        <div class="card card-modern border shadow-sm rounded-4 bg-white overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom ps-4 pe-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-800 mb-0 text-dark">Tour Content Assignment</h5>
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="openAddContentItemModal()">Create New Item Globally</button>
            </div>
            <div class="card-body p-4 ps-4 pe-4">
                @php
                    $contentByType = \App\Models\ContentItem::orderBy('priority', 'asc')->get()->groupBy('type');
                    $assignedContentIds = $tour->contentItems->pluck('id')->toArray();
                @endphp
                <form id="tourContentForm">
                    @csrf
                    <div class="row g-3">
                        @foreach(['inclusion' => 'Inclusions', 'exclusion' => 'Exclusions', 'highlight' => 'Highlights', 'not_allowed' => 'Not Allowed'] as $typeKey => $label)
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 bg-light">
                                <h6 class="fw-bold small mb-3 text-dark border-bottom pb-2">{{ $label }}</h6>
                                <div class="content-items-list" style="max-height: 200px; overflow-y: auto;">
                                    @forelse($contentByType->get($typeKey, collect()) as $ci)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="{{ $typeKey }}[]" value="{{ $ci->id }}" id="ci_{{ $typeKey }}_{{ $ci->id }}" {{ in_array($ci->id, $assignedContentIds) ? 'checked' : '' }}>
                                        <label class="form-check-label small text-dark" for="ci_{{ $typeKey }}_{{ $ci->id }}">
                                            {{ $ci->title }}
                                        </label>
                                    </div>
                                    @empty
                                    <div class="text-muted small">No items. Create one globally.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold">Save Content Assignments</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column (Display Options, SEO, Media) -->
    <div class="col-lg-4">
        <div class="p-4 bg-light rounded-4 border mb-4 bg-white shadow-sm">
            <h6 class="text-primary fw-800 text-uppercase small mb-4">Display & Visibility Options</h6>
            
            <div class="mb-3">
                <label for="tour_duration" class="form-label fw-bold text-dark">Duration Info</label>
                <input type="text" name="duration" form="editTourForm" id="tour_duration" class="form-control" value="{{ $tour->duration }}" placeholder="e.g. 6 Hours">
            </div>

            <div class="mb-3">
                <label for="tour_pickup" class="form-label fw-bold text-dark">Pickup Time</label>
                <input type="text" name="pickup_time" form="editTourForm" id="tour_pickup" class="form-control" value="{{ $tour->pickup_time }}" placeholder="e.g. 2:30 PM">
            </div>

            <div class="mb-3">
                <label for="tour_dropoff" class="form-label fw-bold text-dark">Dropoff Time</label>
                <input type="text" name="dropoff_time" form="editTourForm" id="tour_dropoff" class="form-control" value="{{ $tour->dropoff_time }}" placeholder="e.g. 9:30 PM">
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label for="tour_min_age" class="form-label fw-bold text-dark">Min Age</label>
                    <input type="number" name="min_age" form="editTourForm" id="tour_min_age" class="form-control" value="{{ $tour->min_age }}">
                </div>
                <div class="col-6">
                    <label for="tour_group_size" class="form-label fw-bold text-dark">Group Size</label>
                    <input type="text" name="group_size" form="editTourForm" id="tour_group_size" class="form-control" value="{{ $tour->group_size }}" placeholder="e.g. Up to 6">
                </div>
            </div>

            <div class="mb-3">
                <label for="tour_languages" class="form-label fw-bold text-dark">Languages</label>
                <input type="text" name="languages" form="editTourForm" id="tour_languages" class="form-control" value="{{ $tour->languages }}">
            </div>

            <div class="mb-3">
                <label for="tour_priority" class="form-label fw-bold text-dark">Sort Order</label>
                <input type="number" name="priority" form="editTourForm" id="tour_priority" class="form-control" value="{{ $tour->priority }}">
            </div>

            <div class="mb-0">
                <label for="tour_status" class="form-label fw-bold text-dark">Visibility Status</label>
                <select name="status" form="editTourForm" id="tour_status" class="form-select">
                    <option value="active" {{ $tour->status === 'active' ? 'selected' : '' }}>Active & Published</option>
                    <option value="inactive" {{ $tour->status === 'inactive' ? 'selected' : '' }}>Hidden / Draft</option>
                </select>
            </div>
        </div>

        <!-- Featured Switches -->
        <div class="p-4 bg-light rounded-4 border mb-4 bg-white shadow-sm">
            <h6 class="text-primary fw-800 text-uppercase small mb-4">Attribution Flags</h6>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="is_featured" form="editTourForm" id="is_featured" value="1" {{ $tour->is_featured ? 'checked' : '' }}>
                <label class="form-check-label fw-bold text-dark" for="is_featured">Featured Tour</label>
            </div>
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" name="is_bestseller" form="editTourForm" id="is_bestseller" value="1" {{ $tour->is_bestseller ? 'checked' : '' }}>
                <label class="form-check-label fw-bold text-dark" for="is_bestseller">Bestseller Badge</label>
            </div>
        </div>

        <!-- SEO -->
        <div class="p-4 bg-light rounded-4 border mb-4 bg-white shadow-sm">
            <h6 class="text-primary fw-800 text-uppercase small mb-3">SEO Customization</h6>
            <div class="mb-3">
                <label for="meta_title" class="form-label fw-bold text-dark small">Meta Title</label>
                <input type="text" name="meta_title" form="editTourForm" id="meta_title" class="form-control form-control-sm" value="{{ $tour->meta_title }}">
            </div>
            <div class="mb-3">
                <label for="meta_desc" class="form-label fw-bold text-dark small">Meta Description</label>
                <textarea name="meta_desc" form="editTourForm" id="meta_desc" class="form-control form-control-sm" rows="3">{{ $tour->meta_desc }}</textarea>
            </div>
            <div class="mb-0">
                <label for="meta_keywords" class="form-label fw-bold text-dark small">Meta Keywords</label>
                <input type="text" name="meta_keywords" form="editTourForm" id="meta_keywords" class="form-control form-control-sm" value="{{ $tour->meta_keywords }}">
            </div>
        </div>

        <!-- Images -->
        <div class="p-4 bg-light rounded-4 border mb-4 bg-white shadow-sm">
            <h6 class="text-primary fw-800 text-uppercase small mb-4">Media uploads</h6>
            
            <div class="mb-3">
                <label for="hero_image" class="form-label fw-bold text-dark">Hero Banner Image</label>
                @if($tour->hero_image)
                    <div class="mb-2"><img src="{{ Storage::url($tour->hero_image) }}" style="max-height:80px; border-radius:6px; object-fit:cover;" class="w-100 border"></div>
                @endif
                <input type="file" name="hero_image" form="editTourForm" id="hero_image" class="form-control">
            </div>

            <div class="mb-0">
                <label for="thumb_image" class="form-label fw-bold text-dark">Thumbnail Image</label>
                @if($tour->thumb_image)
                    <div class="mb-2"><img src="{{ Storage::url($tour->thumb_image) }}" style="max-height:80px; border-radius:6px; object-fit:cover;" class="w-100 border"></div>
                @endif
                <input type="file" name="thumb_image" form="editTourForm" id="thumb_image" class="form-control">
            </div>
        </div>
    </div>
</div>

<!-- Category Manager Modal -->
<div class="modal fade" id="categoryManager" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow bg-white">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800 text-dark">Category Manager</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4">
                    <label class="fw-bold text-dark mb-2">Existing Categories</label>
                    <div id="catList" class="p-3 bg-light rounded-3 border">
                        @foreach($categories as $cat)
                            <span class="badge bg-white text-dark border me-1 mb-1 px-3 py-2 rounded-pill">{{ $cat->name }}</span>
                        @endforeach
                    </div>
                </div>
                
                <div class="mb-4 border-top pt-3">
                    <label for="newCat" class="fw-bold text-dark mb-2">Add New Category</label>
                    <div class="input-group">
                        <input type="text" id="newCat" class="form-control" placeholder="Category Name (e.g. Quad Biking)">
                        <button class="btn btn-primary" onclick="addCategory()">Add</button>
                    </div>
                </div>

                <div class="border-top pt-3">
                    <label class="fw-bold text-dark mb-2">Rename Category</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <select id="oldCat" class="form-select">
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <div class="input-group">
                                <input type="text" id="renCat" class="form-control" placeholder="New Name">
                                <button class="btn btn-secondary" onclick="renameCategory()">Rename</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Global Content Item Modal -->
<div class="modal fade" id="addContentItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow bg-white">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800 text-dark">Create New Content Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="newContentItemForm">
                    <div class="mb-3">
                        <label for="newCiType" class="form-label fw-bold text-dark">Type</label>
                        <select id="newCiType" class="form-select" required>
                            <option value="inclusion">Inclusion</option>
                            <option value="exclusion">Exclusion</option>
                            <option value="highlight">Highlight</option>
                            <option value="not_allowed">Not Allowed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="newCiTitle" class="form-label fw-bold text-dark">Title</label>
                        <input type="text" id="newCiTitle" class="form-control" required placeholder="e.g. Free Hotel Pickup">
                    </div>
                    <div class="mb-3">
                        <label for="newCiDesc" class="form-label fw-bold text-dark">Description (Optional)</label>
                        <input type="text" id="newCiDesc" class="form-control" placeholder="e.g. Within Dubai city limits">
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="newCiIcon" class="form-label fw-bold text-dark small">Icon Class</label>
                            <input type="text" id="newCiIcon" class="form-control form-control-sm" placeholder="bi-check2">
                        </div>
                        <div class="col-6">
                            <label for="newCiPriority" class="form-label fw-bold text-dark small">Sort Priority</label>
                            <input type="number" id="newCiPriority" class="form-control form-control-sm" value="99">
                        </div>
                    </div>
                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-secondary rounded-pill px-4 me-1" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Create & Load</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Category Manager
function openCategoryManager() {
    const m = new bootstrap.Modal(document.getElementById('categoryManager'));
    m.show();
}
function addCategory() {
    const name = $('#newCat').val().trim();
    if(!name) { alert('Category name cannot be empty'); return; }
    
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
            alert('Failed to add category.');
        }
    });
}
function renameCategory() {
    const oldName = $('#oldCat').val();
    const newName = $('#renCat').val().trim();
    if(!oldName || !newName) { alert('Provide both old and new names'); return; }
    
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
            alert('Failed to rename category.');
        }
    });
}

// Global Content Items
function openAddContentItemModal() {
    const m = new bootstrap.Modal(document.getElementById('addContentItemModal'));
    m.show();
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
            alert('Failed to create content item.');
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
            alert('Failed to save assignments.');
        }
    });
});

// Itinerary Builder Scripts
function showEditItineraryForm(id) {
    $(`#it_edit_form_${id}`).removeClass('d-none');
}
function hideEditItineraryForm(id) {
    $(`#it_edit_form_${id}`).addClass('d-none');
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
            alert('Failed to update itinerary item.');
        }
    });
}
function deleteItinerary(id) {
    if(!confirm('Delete this itinerary item?')) return;
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
                    $('#itineraryListContainer').html('<p class="text-muted small text-center py-3">No itinerary items defined yet.</p>');
                }
            }
        },
        error: function() {
            hideLoader();
            alert('Failed to delete.');
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
            alert('Failed to add step.');
        }
    });
});

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
@endpush
@endsection
