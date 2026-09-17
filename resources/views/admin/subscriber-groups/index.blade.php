@extends('layouts.admin')

@section('page_title', 'Subscriber Groups & Segments')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-800 text-dark mb-1">
                <i class="bi bi-diagram-3-fill text-primary me-2"></i>Subscriber Groups & Audience Segments
            </h4>
            <div class="text-muted small">Organize your audience into targeted lists for precision email marketing and promotions.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.subscribers.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 btn-sm fw-bold shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-people"></i> View All Subscribers
            </a>
            <button type="button" class="btn btn-primary rounded-pill px-4 py-2 btn-sm fw-bold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createGroupModal">
                <i class="bi bi-plus-lg"></i> Create Group
            </button>
        </div>
    </div>

    <!-- Metric Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 fs-4">
                        <i class="bi bi-collection"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small text-uppercase fw-bold">Total Groups</div>
                        <h4 class="fw-800 text-dark mb-0">{{ $groups->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-info bg-opacity-10 text-info p-3 fs-4">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small text-uppercase fw-bold">System Segments</div>
                        <h4 class="fw-800 text-info mb-0">{{ $groups->where('is_system', true)->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 fs-4">
                        <i class="bi bi-sliders"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small text-uppercase fw-bold">Custom Segments</div>
                        <h4 class="fw-800 text-success mb-0">{{ $groups->where('is_system', false)->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3 fs-4">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small text-uppercase fw-bold">Active Tagged</div>
                        <h4 class="fw-800 text-warning mb-0">{{ number_format($groups->sum('active_subscribers_count')) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Groups Table Card -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 text-muted extra-small text-uppercase fw-800">Group Name & Slug</th>
                        <th class="text-muted extra-small text-uppercase fw-800">Type</th>
                        <th class="text-muted extra-small text-uppercase fw-800">Description</th>
                        <th class="text-center text-muted extra-small text-uppercase fw-800">Active / Total Subscribers</th>
                        <th class="text-center text-muted extra-small text-uppercase fw-800">Created</th>
                        <th class="text-end pe-4 text-muted extra-small text-uppercase fw-800">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groups as $group)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle p-2 d-flex align-items-center justify-content-center {{ $group->is_system ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-secondary' }}" style="width: 40px; height: 40px;">
                                    <i class="bi {{ $group->is_system ? 'bi-shield-check' : 'bi-tags' }} fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $group->name }}</div>
                                    <div class="text-muted small font-monospace"><span class="badge bg-light text-secondary border px-2 py-1">{{ $group->slug }}</span></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($group->is_system)
                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 small fw-bold">
                                    <i class="bi bi-shield-lock me-1"></i> System Auto-Segment
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-dark border rounded-pill px-2.5 py-1 small fw-bold">
                                    <i class="bi bi-person me-1"></i> Custom Group
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="text-muted small text-truncate" style="max-width: 320px;" title="{{ $group->description }}">
                                {{ $group->description ?: 'No description provided.' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.subscribers.index', ['group_id' => $group->id]) }}" class="text-decoration-none">
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold">
                                    {{ number_format($group->active_subscribers_count) }} Active
                                </span>
                                <span class="text-muted small ms-1">/ {{ number_format($group->subscribers_count) }} Total</span>
                            </a>
                        </td>
                        <td class="text-center text-muted small">
                            {{ $group->created_at ? $group->created_at->format('M d, Y') : 'System' }}
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('admin.subscribers.index', ['group_id' => $group->id]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1" title="View Subscribers in this Group">
                                    <i class="bi bi-people"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 edit-group-btn" 
                                    data-id="{{ $group->id }}"
                                    data-name="{{ $group->name }}"
                                    data-description="{{ $group->description }}"
                                    data-is-system="{{ $group->is_system ? '1' : '0' }}"
                                    title="Edit Group">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @if(!$group->is_system)
                                    <form action="{{ route('admin.subscriber-groups.destroy', $group->id) }}" method="POST" class="d-inline delete-group-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 btn-delete-group" data-name="{{ $group->name }}" title="Delete Group">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-sm btn-light rounded-pill px-2.5 py-1 text-muted border-0" disabled title="System groups cannot be deleted">
                                        <i class="bi bi-lock"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-diagram-3 text-muted display-4 mb-3 d-block opacity-50"></i>
                            <h6 class="fw-bold text-dark">No Subscriber Groups Found</h6>
                            <p class="text-muted small">Create your first audience group to segment campaigns.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Group Modal -->
<div class="modal fade" id="createGroupModal" tabindex="-1" aria-labelledby="createGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-light p-4 pb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                        <i class="bi bi-diagram-3-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-800 text-dark mb-0" id="createGroupModalLabel">Create Audience Group</h5>
                        <div class="text-muted extra-small">Targeted email segment for marketing dispatches</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.subscriber-groups.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 pt-2">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Group Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-3 py-2" placeholder="e.g. VIP Desert Campers, Summer 2026 Leads" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Description</label>
                        <textarea name="description" class="form-control rounded-3" rows="3" placeholder="Brief note about audience criteria or campaign purpose..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light p-3 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Create Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Group Modal -->
<div class="modal fade" id="editGroupModal" tabindex="-1" aria-labelledby="editGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-light p-4 pb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                        <i class="bi bi-pencil-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-800 text-dark mb-0" id="editGroupModalLabel">Edit Audience Group</h5>
                        <div class="text-muted extra-small">Update group details</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editGroupForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4 pt-2">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Group Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editGroupName" class="form-control rounded-3 py-2" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Description</label>
                        <textarea name="description" id="editGroupDescription" class="form-control rounded-3" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light p-3 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Update Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = new bootstrap.Modal(document.getElementById('editGroupModal'));
    const editForm = document.getElementById('editGroupForm');
    const editName = document.getElementById('editGroupName');
    const editDesc = document.getElementById('editGroupDescription');

    document.querySelectorAll('.edit-group-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const desc = this.dataset.description;

            editForm.action = `/admin/subscriber-groups/${id}`;
            editName.value = name;
            editDesc.value = desc || '';
            editModal.show();
        });
    });

    document.querySelectorAll('.btn-delete-group').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const groupName = this.dataset.name;
            const form = this.closest('form');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete Audience Group?',
                    text: `Are you sure you want to delete "${groupName}"? Subscribers in this group will not be deleted, only unlinked from this group.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm(`Are you sure you want to delete "${groupName}"?`)) {
                    form.submit();
                }
            }
        });
    });
});
</script>
@endpush
@endsection
