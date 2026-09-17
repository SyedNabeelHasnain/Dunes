@extends('layouts.admin')

@section('page_title', 'Subscribers & Email Marketing Lists')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-800 text-dark mb-1">
                <i class="bi bi-people-fill text-primary me-2"></i>Email Subscribers & Audience Lists
            </h4>
            <div class="text-muted small">Manage newsletter subscribers, segment attributions, and marketing lists.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-dark rounded-pill px-3 py-2 btn-sm fw-bold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-file-earmark-arrow-up"></i> Import CSV
            </button>
            <a href="{{ route('admin.subscribers.export', request()->query()) }}" class="btn btn-outline-success rounded-pill px-3 py-2 btn-sm fw-bold shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-excel"></i> Export CSV
            </a>
            <button type="button" class="btn btn-primary rounded-pill px-4 py-2 btn-sm fw-bold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="bi bi-plus-lg"></i> Add Subscriber
            </button>
        </div>
    </div>

    <!-- Metric Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 fs-4">
                        <i class="bi bi-envelope-at"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small text-uppercase fw-bold">Total Audience</div>
                        <h4 class="fw-800 text-dark mb-0">{{ number_format($stats['total']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 fs-4">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small text-uppercase fw-bold">Active Subscribed</div>
                        <h4 class="fw-800 text-success mb-0">{{ number_format($stats['subscribed']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-secondary bg-opacity-10 text-secondary p-3 fs-4">
                        <i class="bi bi-bell-slash"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small text-uppercase fw-bold">Unsubscribed</div>
                        <h4 class="fw-800 text-secondary mb-0">{{ number_format($stats['unsubscribed']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-danger bg-opacity-10 text-danger p-3 fs-4">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small text-uppercase fw-bold">Bounced</div>
                        <h4 class="fw-800 text-danger mb-0">{{ number_format($stats['bounced']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('admin.subscribers.index') }}" method="GET" class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" name="search" class="form-control rounded-pill ps-5 bg-light border-0" placeholder="Search email, name, phone..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select rounded-pill bg-light border-0">
                        <option value="">All Statuses</option>
                        <option value="subscribed" {{ request('status') === 'subscribed' ? 'selected' : '' }}>Subscribed</option>
                        <option value="unsubscribed" {{ request('status') === 'unsubscribed' ? 'selected' : '' }}>Unsubscribed</option>
                        <option value="bounced" {{ request('status') === 'bounced' ? 'selected' : '' }}>Bounced</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="group_id" class="form-select rounded-pill bg-light border-0">
                        <option value="">All Groups / Segments</option>
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}" {{ request('group_id') == $g->id ? 'selected' : '' }}>{{ $g->name }} ({{ $g->subscribers_count }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="source" class="form-select rounded-pill bg-light border-0">
                        <option value="">All Sources</option>
                        <option value="footer" {{ request('source') === 'footer' ? 'selected' : '' }}>Footer Form</option>
                        <option value="booking_modal" {{ request('source') === 'booking_modal' ? 'selected' : '' }}>Booking Checkout</option>
                        <option value="welcome_modal" {{ request('source') === 'welcome_modal' ? 'selected' : '' }}>Welcome Popup</option>
                        <option value="admin_import" {{ request('source') === 'admin_import' ? 'selected' : '' }}>Admin / Import</option>
                    </select>
                </div>
                <div class="col-6 col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill w-100 fw-bold" title="Apply Filter">
                        <i class="bi bi-funnel"></i>
                    </button>
                    @if(request()->hasAny(['search', 'status', 'group_id', 'source']))
                        <a href="{{ route('admin.subscribers.index') }}" class="btn btn-light rounded-pill border" title="Clear Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Action Toolbar Form -->
    <form action="{{ route('admin.subscribers.bulk') }}" method="POST" id="bulkForm">
        @csrf
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="card-header bg-white border-0 py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAll">
                        <label class="form-check-label small fw-bold text-muted" for="selectAll">Select All</label>
                    </div>
                    <div id="bulkControls" class="d-none d-flex align-items-center gap-2">
                        <select name="action" id="bulkActionSelect" class="form-select form-select-sm rounded-pill" style="min-width: 150px;">
                            <option value="">Bulk Actions...</option>
                            <option value="assign_group">Assign to Group</option>
                            <option value="subscribe">Mark Subscribed</option>
                            <option value="unsubscribe">Mark Unsubscribed</option>
                            <option value="delete">Delete Selected</option>
                        </select>
                        <select name="target_group_id" id="bulkGroupSelect" class="form-select form-select-sm rounded-pill d-none" style="min-width: 170px;">
                            <option value="">Choose Target Group...</option>
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-dark rounded-pill px-3 fw-bold" id="bulkApplyBtn">Apply</button>
                    </div>
                </div>
                <div class="text-muted small">
                    Showing {{ $subscribers->firstItem() ?? 0 }}–{{ $subscribers->lastItem() ?? 0 }} of {{ $subscribers->total() }} subscribers
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th width="40" class="ps-4"></th>
                            <th>Subscriber Details</th>
                            <th>Status</th>
                            <th>Assigned Groups</th>
                            <th>Attribution</th>
                            <th>Date Subscribed</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscribers as $s)
                        <tr>
                            <td class="ps-4">
                                <input class="form-check-input row-select" type="checkbox" name="subscriber_ids[]" value="{{ $s->id }}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-light text-dark fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-size: 13px;">
                                        {{ strtoupper(substr($s->email, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $s->email }}</div>
                                        <div class="text-muted extra-small">
                                            {{ $s->full_name }} @if($s->phone) &bull; {{ $s->phone }} @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($s->status === 'subscribed')
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 fw-bold" style="cursor: pointer;" onclick="toggleStatus({{ $s->id }})">
                                        <i class="bi bi-check-circle-fill me-1"></i>Subscribed
                                    </span>
                                @elseif($s->status === 'unsubscribed')
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-1 fw-bold" style="cursor: pointer;" onclick="toggleStatus({{ $s->id }})">
                                        <i class="bi bi-dash-circle-fill me-1"></i>Unsubscribed
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1 fw-bold">
                                        <i class="bi bi-x-circle-fill me-1"></i>{{ ucfirst($s->status) }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @forelse($s->groups as $grp)
                                        <span class="badge bg-light text-dark border rounded-pill extra-small px-2 py-1">{{ $grp->name }}</span>
                                    @empty
                                        <span class="text-muted extra-small">No Group</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-muted border rounded-pill px-2 py-1 extra-small">{{ ucfirst(str_replace('_', ' ', $s->source)) }}</span>
                                @if($s->country)
                                    <span class="text-muted extra-small d-block mt-0.5"><i class="bi bi-geo-alt"></i> {{ $s->city ? $s->city . ', ' : '' }}{{ $s->country }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="small text-dark">{{ $s->subscribed_at ? $s->subscribed_at->format('M j, Y') : $s->created_at->format('M j, Y') }}</span>
                                <div class="text-muted extra-small">{{ $s->created_at->format('H:i') }}</div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-2.5 py-1 me-1" onclick="editSubscriber({{ json_encode($s) }}, {{ json_encode($s->groups->pluck('id')) }})" title="Edit Details">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger rounded-pill px-2.5 py-1" onclick="confirmDelete({{ $s->id }}, '{{ $s->email }}')" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-people display-6 text-muted opacity-50 d-block mb-3"></i>
                                <p class="fw-bold mb-1">No subscribers found</p>
                                <small>Try adjusting your search criteria or add new subscribers above.</small>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($subscribers->hasPages())
            <div class="card-footer bg-white border-0 py-3 px-4">
                {{ $subscribers->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </form>
</div>

<!-- Modal: Add Subscriber -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('admin.subscribers.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-dark text-white p-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Add New Subscriber</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control rounded-3" required placeholder="user@example.com">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold text-dark small">First Name</label>
                            <input type="text" name="first_name" class="form-control rounded-3" placeholder="John">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-dark small">Last Name</label>
                            <input type="text" name="last_name" class="form-control rounded-3" placeholder="Doe">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Phone Number (Optional)</label>
                        <input type="text" name="phone" class="form-control rounded-3" placeholder="+971 50 000 0000">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Subscription Status</label>
                        <select name="status" class="form-select rounded-3">
                            <option value="subscribed" selected>Subscribed (Active)</option>
                            <option value="unsubscribed">Unsubscribed</option>
                            <option value="bounced">Bounced</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold text-dark small">Assign to Groups / Segments</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($groups as $g)
                            <div class="form-check form-check-inline m-0">
                                <input class="form-check-input" type="checkbox" name="groups[]" value="{{ $g->id }}" id="createGrp_{{ $g->id }}" {{ $g->slug === 'general-newsletter' ? 'checked' : '' }}>
                                <label class="form-check-label small" for="createGrp_{{ $g->id }}">{{ $g->name }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Add Subscriber</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Subscriber -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-dark text-white p-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Subscriber</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="editEmail" class="form-control rounded-3" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold text-dark small">First Name</label>
                            <input type="text" name="first_name" id="editFirstName" class="form-control rounded-3">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-dark small">Last Name</label>
                            <input type="text" name="last_name" id="editLastName" class="form-control rounded-3">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Phone Number</label>
                        <input type="text" name="phone" id="editPhone" class="form-control rounded-3">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Status</label>
                        <select name="status" id="editStatus" class="form-select rounded-3">
                            <option value="subscribed">Subscribed (Active)</option>
                            <option value="unsubscribed">Unsubscribed</option>
                            <option value="bounced">Bounced</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold text-dark small">Assign to Groups</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($groups as $g)
                            <div class="form-check form-check-inline m-0">
                                <input class="form-check-input edit-grp-check" type="checkbox" name="groups[]" value="{{ $g->id }}" id="editGrp_{{ $g->id }}">
                                <label class="form-check-label small" for="editGrp_{{ $g->id }}">{{ $g->name }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Update Subscriber</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Import CSV -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('admin.subscribers.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-dark text-white p-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-arrow-up me-2 text-primary"></i>Import Subscribers CSV</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Upload a CSV file containing contacts. The file must include a header row with at least an <code>email</code> column (optional: <code>first_name</code>, <code>last_name</code>, <code>phone</code>).</p>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Select CSV File <span class="text-danger">*</span></label>
                        <input type="file" name="csv_file" class="form-control rounded-3" accept=".csv,text/csv" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Assign to Group (Optional)</label>
                        <select name="group_id" class="form-select rounded-3">
                            <option value="">Do not assign to specific group</option>
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="p-3 bg-light rounded-3 text-muted extra-small">
                        <strong>Duplicate handling:</strong> Existing contacts with matching emails will have their details updated without duplicating records.
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Upload & Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const rowSelects = document.querySelectorAll('.row-select');
    const bulkControls = document.getElementById('bulkControls');
    const bulkActionSelect = document.getElementById('bulkActionSelect');
    const bulkGroupSelect = document.getElementById('bulkGroupSelect');

    function updateBulkState() {
        const anyChecked = Array.from(rowSelects).some(cb => cb.checked);
        bulkControls.classList.toggle('d-none', !anyChecked);
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            rowSelects.forEach(cb => cb.checked = selectAll.checked);
            updateBulkState();
        });
    }

    rowSelects.forEach(cb => cb.addEventListener('change', updateBulkState));

    if (bulkActionSelect) {
        bulkActionSelect.addEventListener('change', function() {
            bulkGroupSelect.classList.toggle('d-none', this.value !== 'assign_group');
        });
    }

    document.getElementById('bulkForm').addEventListener('submit', function(e) {
        if (!bulkActionSelect.value) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Action Required', text: 'Please select a bulk action to perform.' });
            return;
        }
        if (bulkActionSelect.value === 'delete') {
            e.preventDefault();
            Swal.fire({
                title: 'Delete Selected Subscribers?',
                text: 'This will soft-delete all checked subscriber records.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete them'
            }).then((res) => {
                if (res.isConfirmed) document.getElementById('bulkForm').submit();
            });
        }
    });
});

function editSubscriber(sub, groupIds) {
    document.getElementById('editForm').action = `/admin/subscribers/${sub.id}`;
    document.getElementById('editEmail').value = sub.email;
    document.getElementById('editFirstName').value = sub.first_name || '';
    document.getElementById('editLastName').value = sub.last_name || '';
    document.getElementById('editPhone').value = sub.phone || '';
    document.getElementById('editStatus').value = sub.status;

    document.querySelectorAll('.edit-grp-check').forEach(cb => {
        cb.checked = groupIds.includes(parseInt(cb.value));
    });

    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function confirmDelete(id, email) {
    Swal.fire({
        title: 'Delete Subscriber?',
        html: `Are you sure you want to delete <strong>${email}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete'
    }).then((res) => {
        if (res.isConfirmed) {
            const form = document.getElementById('deleteForm');
            form.action = `/admin/subscribers/${id}`;
            form.submit();
        }
    });
}

function toggleStatus(id) {
    fetch(`/admin/subscribers/${id}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    }).then(res => res.json()).then(data => {
        if (data.success) {
            location.reload();
        }
    }).catch(err => {
        console.error(err);
    });
}
</script>
@endpush
@endsection
