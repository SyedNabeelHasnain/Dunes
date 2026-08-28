@extends('layouts.admin')

@section('page_title', 'Pricing Tiers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="h4 fw-800 text-dark mb-1">Pricing Tiers Hierarchy</h2>
        <p class="text-muted small mb-0">Configure package tiers (Standard, VIP, Glamping, Premium) and their global feature descriptions.</p>
    </div>
    <button type="button" class="btn btn-primary rounded-pill px-4 fw-800 shadow-sm" data-bs-toggle="modal" data-bs-target="#addTierModal">
        <i class="bi bi-plus-lg me-2"></i> Add New Tier
    </button>
</div>

<div class="card card-modern border-0 shadow-sm rounded-4 overflow-hidden bg-white">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover" id="tiersTable">
                <thead class="table-light small text-uppercase fw-bold text-muted">
                    <tr>
                        <th class="ps-3">Tier Identity</th>
                        <th>Internal Name</th>
                        <th>Slug</th>
                        <th>Tour Adoption</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tiers as $tier)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary-subtle text-primary d-flex align-items-center justify-content-center rounded-circle border border-primary-subtle" style="width: 42px; height: 42px; font-size: 1.1rem; flex-shrink: 0;">
                                    <i class="bi bi-{{ $tier->icon ?: 'star-fill' }}"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                                        {{ $tier->display_name }}
                                        @if($tier->is_popular)
                                            <span class="badge bg-warning text-white rounded-pill px-2 py-0 small" style="font-size:0.65rem;"><i class="bi bi-fire me-1"></i>Popular</span>
                                        @endif
                                    </div>
                                    <div class="text-muted small text-truncate" style="max-width: 280px;">{{ $tier->description }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="fw-medium text-dark">{{ $tier->name }}</td>
                        <td><code class="text-primary bg-light px-2 py-1 rounded">{{ $tier->slug }}</code></td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold">
                                <i class="bi bi-link-45deg me-1"></i>{{ $tier->tours_count ?? $tier->tours->count() }} Tours
                            </span>
                        </td>
                        <td>
                            @if($tier->status === 'active')
                                <span class="badge bg-success text-capitalize px-3 py-1 rounded-pill">Active</span>
                            @else
                                <span class="badge bg-secondary text-capitalize px-3 py-1 rounded-pill">Inactive</span>
                            @endif
                        </td>
                        <td class="fw-bold text-muted">
                            {{ $tier->priority }}
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center edit-tier-btn" 
                                    style="width: 34px; height: 34px;"
                                    title="Edit Tier"
                                    data-id="{{ $tier->id }}"
                                    data-name="{{ $tier->name }}"
                                    data-display-name="{{ $tier->display_name }}"
                                    data-slug="{{ $tier->slug }}"
                                    data-description="{{ $tier->description }}"
                                    data-icon="{{ $tier->icon }}"
                                    data-popular="{{ $tier->is_popular ? '1' : '0' }}"
                                    data-status="{{ $tier->status }}"
                                    data-priority="{{ $tier->priority }}"
                                    data-action="{{ route('admin.tiers.update', $tier->id) }}">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <form action="{{ route('admin.tiers.destroy', $tier->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" 
                                        style="width: 34px; height: 34px;" 
                                        title="Delete Tier"
                                        onclick="return confirm('Are you sure you want to delete this pricing tier? It will be detached from all linked tours.')">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No pricing tiers defined.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add New Tier -->
<div class="modal fade" id="addTierModal" tabindex="-1" aria-labelledby="addTierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.tiers.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-800 text-dark" id="addTierModalLabel"><i class="bi bi-plus-circle text-primary me-2"></i>Add New Pricing Tier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="tierDisplayName" class="form-label small fw-bold text-dark">Display Name <span class="text-danger">*</span></label>
                            <input type="text" name="display_name" id="tierDisplayName" class="form-control rounded-3" placeholder="e.g. VIP Royal Dining" required>
                        </div>
                        <div class="col-6">
                            <label for="tierName" class="form-label small fw-bold text-dark">Internal Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="tierName" class="form-control rounded-3" placeholder="e.g. VIP Royal" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="tierSlug" class="form-label small fw-bold text-dark">Slug (Optional)</label>
                            <input type="text" name="slug" id="tierSlug" class="form-control rounded-3" placeholder="e.g. vip-royal">
                        </div>
                        <div class="col-6">
                            <label for="tierIcon" class="form-label small fw-bold text-dark">Icon Class</label>
                            <input type="text" name="icon" id="tierIcon" class="form-control rounded-3" placeholder="e.g. star-fill, trophy, gem" value="star-fill">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="tierStatus" class="form-label small fw-bold text-dark">Status</label>
                            <select name="status" id="tierStatus" class="form-select rounded-3">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="tierPriority" class="form-label small fw-bold text-dark">Priority Order</label>
                            <input type="number" name="priority" id="tierPriority" class="form-control rounded-3" value="1" required>
                        </div>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_popular" value="1" id="addTierPopular">
                        <label class="form-check-label small fw-bold text-dark" for="addTierPopular">Mark as "Popular / Best Value" Badge</label>
                    </div>
                    <div class="mb-0">
                        <label for="tierDesc" class="form-label small fw-bold text-dark">Description</label>
                        <textarea name="description" id="tierDesc" rows="3" class="form-control rounded-3" placeholder="Key inclusions or features of this tier..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top p-3 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Save Tier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Tier -->
<div class="modal fade" id="editTierModal" tabindex="-1" aria-labelledby="editTierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form id="editTierForm" method="POST">
                @csrf
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-800 text-dark" id="editTierModalLabel"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Pricing Tier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="editTierDisplayName" class="form-label small fw-bold text-dark">Display Name <span class="text-danger">*</span></label>
                            <input type="text" name="display_name" id="editTierDisplayName" class="form-control rounded-3" required>
                        </div>
                        <div class="col-6">
                            <label for="editTierName" class="form-label small fw-bold text-dark">Internal Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editTierName" class="form-control rounded-3" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="editTierSlug" class="form-label small fw-bold text-dark">Slug <span class="text-danger">*</span></label>
                            <input type="text" name="slug" id="editTierSlug" class="form-control rounded-3" required>
                        </div>
                        <div class="col-6">
                            <label for="editTierIcon" class="form-label small fw-bold text-dark">Icon Class</label>
                            <input type="text" name="icon" id="editTierIcon" class="form-control rounded-3">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="editTierStatus" class="form-label small fw-bold text-dark">Status</label>
                            <select name="status" id="editTierStatus" class="form-select rounded-3">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="editTierPriority" class="form-label small fw-bold text-dark">Priority Order</label>
                            <input type="number" name="priority" id="editTierPriority" class="form-control rounded-3" required>
                        </div>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_popular" value="1" id="editTierPopular">
                        <label class="form-check-label small fw-bold text-dark" for="editTierPopular">Mark as "Popular / Best Value" Badge</label>
                    </div>
                    <div class="mb-0">
                        <label for="editTierDesc" class="form-label small fw-bold text-dark">Description</label>
                        <textarea name="description" id="editTierDesc" rows="3" class="form-control rounded-3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top p-3 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Update Tier</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.edit-tier-btn').on('click', function() {
        const btn = $(this);
        $('#editTierName').val(btn.data('name'));
        $('#editTierDisplayName').val(btn.data('display-name'));
        $('#editTierSlug').val(btn.data('slug'));
        $('#editTierIcon').val(btn.data('icon'));
        $('#editTierStatus').val(btn.data('status'));
        $('#editTierPriority').val(btn.data('priority'));
        $('#editTierDesc').val(btn.data('description'));
        $('#editTierPopular').prop('checked', btn.data('popular') == '1');
        $('#editTierForm').attr('action', btn.data('action'));
        
        const modal = new bootstrap.Modal(document.getElementById('editTierModal'));
        modal.show();
    });
});
</script>
@endpush
@endsection
