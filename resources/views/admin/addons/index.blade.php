@extends('layouts.admin')

@section('page_title', 'Tour Addons')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="h4 fw-800 text-dark mb-1">Tour Addons Catalog</h2>
        <p class="text-muted small mb-0">Manage global add-on upgrades, pricing, and adoption across all desert safaris and tours.</p>
    </div>
    <button type="button" class="btn btn-primary rounded-pill px-4 fw-800 shadow-sm" data-bs-toggle="modal" data-bs-target="#addAddonModal">
        <i class="bi bi-plus-lg me-2"></i> Add New Addon
    </button>
</div>

<div class="card card-modern border-0 shadow-sm rounded-4 overflow-hidden bg-white">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover datatable" id="addonsTable">
                <thead class="table-light small text-uppercase fw-bold text-muted">
                    <tr>
                        <th class="ps-3">Addon Identity</th>
                        <th>Slug</th>
                        <th>Default Price</th>
                        <th>Tour Adoption</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th class="text-end pe-3 no-sort">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($addons as $addon)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary-subtle text-primary d-flex align-items-center justify-content-center rounded-circle border border-primary-subtle" style="width: 42px; height: 42px; font-size: 1.1rem; flex-shrink: 0;">
                                    <i class="bi bi-{{ $addon->icon ?: 'plus-lg' }}"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark fs-6">{{ $addon->name }}</div>
                                    <div class="text-muted small text-truncate" style="max-width: 280px;">{{ $addon->description }}</div>
                                </div>
                            </div>
                        </td>
                        <td><code class="text-primary bg-light px-2 py-1 rounded">{{ $addon->slug }}</code></td>
                        <td class="fw-800 text-dark">AED {{ number_format($addon->default_price, 2) }}</td>
                        <td>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold">
                                <i class="bi bi-link-45deg me-1"></i>{{ $addon->tours_count ?? $addon->tours->count() }} Tours
                            </span>
                        </td>
                        <td>
                            @if($addon->status === 'active')
                                <span class="badge bg-success text-capitalize px-3 py-1 rounded-pill">Active</span>
                            @else
                                <span class="badge bg-secondary text-capitalize px-3 py-1 rounded-pill">Inactive</span>
                            @endif
                        </td>
                        <td class="fw-bold text-muted">
                            {{ $addon->priority }}
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center edit-addon-btn" 
                                    style="width: 34px; height: 34px;"
                                    title="Edit Addon"
                                    data-id="{{ $addon->id }}"
                                    data-name="{{ $addon->name }}"
                                    data-slug="{{ $addon->slug }}"
                                    data-description="{{ $addon->description }}"
                                    data-icon="{{ $addon->icon }}"
                                    data-price="{{ $addon->default_price }}"
                                    data-status="{{ $addon->status }}"
                                    data-priority="{{ $addon->priority }}"
                                    data-action="{{ route('admin.addons.update', $addon->id) }}">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <form action="{{ route('admin.addons.destroy', $addon->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" 
                                        style="width: 34px; height: 34px;" 
                                        title="Delete Addon"
                                        onclick="return confirm('Are you sure you want to delete this addon? It will be detached from all linked tours.')">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No addons found in catalog.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add New Addon -->
<div class="modal fade" id="addAddonModal" tabindex="-1" aria-labelledby="addAddonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.addons.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-800 text-dark" id="addAddonModalLabel"><i class="bi bi-plus-circle text-primary me-2"></i>Add New Tour Addon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="addonName" class="form-label small fw-bold text-dark">Addon Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="addonName" class="form-control rounded-3" placeholder="e.g. Quad Biking (30 Mins)" required>
                    </div>
                    <div class="mb-3">
                        <label for="addonSlug" class="form-label small fw-bold text-dark">Slug (Optional - auto-generated if empty)</label>
                        <input type="text" name="slug" id="addonSlug" class="form-control rounded-3" placeholder="e.g. quad-biking-30-mins">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="addonPrice" class="form-label small fw-bold text-dark">Default Price (AED) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-dark fw-bold">AED</span>
                                <input type="number" step="0.01" min="0" name="default_price" id="addonPrice" class="form-control" placeholder="150.00" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <label for="addonIcon" class="form-label small fw-bold text-dark">Bootstrap Icon Class</label>
                            <input type="text" name="icon" id="addonIcon" class="form-control rounded-3" placeholder="e.g. plus-lg, bicycle, star-fill" value="plus-lg">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="addonStatus" class="form-label small fw-bold text-dark">Status</label>
                            <select name="status" id="addonStatus" class="form-select rounded-3">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="addonPriority" class="form-label small fw-bold text-dark">Priority Order</label>
                            <input type="number" name="priority" id="addonPriority" class="form-control rounded-3" value="1" required>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label for="addonDesc" class="form-label small fw-bold text-dark">Description</label>
                        <textarea name="description" id="addonDesc" rows="3" class="form-control rounded-3" placeholder="Short description of this add-on upgrade..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top p-3 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Save Addon</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Addon -->
<div class="modal fade" id="editAddonModal" tabindex="-1" aria-labelledby="editAddonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form id="editAddonForm" method="POST">
                @csrf
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-800 text-dark" id="editAddonModalLabel"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Tour Addon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="editAddonName" class="form-label small fw-bold text-dark">Addon Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editAddonName" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label for="editAddonSlug" class="form-label small fw-bold text-dark">Slug <span class="text-danger">*</span></label>
                        <input type="text" name="slug" id="editAddonSlug" class="form-control rounded-3" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="editAddonPrice" class="form-label small fw-bold text-dark">Default Price (AED) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-dark fw-bold">AED</span>
                                <input type="number" step="0.01" min="0" name="default_price" id="editAddonPrice" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <label for="editAddonIcon" class="form-label small fw-bold text-dark">Bootstrap Icon Class</label>
                            <input type="text" name="icon" id="editAddonIcon" class="form-control rounded-3">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="editAddonStatus" class="form-label small fw-bold text-dark">Status</label>
                            <select name="status" id="editAddonStatus" class="form-select rounded-3">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="editAddonPriority" class="form-label small fw-bold text-dark">Priority Order</label>
                            <input type="number" name="priority" id="editAddonPriority" class="form-control rounded-3" required>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label for="editAddonDesc" class="form-label small fw-bold text-dark">Description</label>
                        <textarea name="description" id="editAddonDesc" rows="3" class="form-control rounded-3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top p-3 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Update Addon</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.edit-addon-btn').on('click', function() {
        const btn = $(this);
        $('#editAddonName').val(btn.data('name'));
        $('#editAddonSlug').val(btn.data('slug'));
        $('#editAddonPrice').val(btn.data('price'));
        $('#editAddonIcon').val(btn.data('icon'));
        $('#editAddonStatus').val(btn.data('status'));
        $('#editAddonPriority').val(btn.data('priority'));
        $('#editAddonDesc').val(btn.data('description'));
        $('#editAddonForm').attr('action', btn.data('action'));
        
        const modal = new bootstrap.Modal(document.getElementById('editAddonModal'));
        modal.show();
    });
});
</script>
@endpush
@endsection
