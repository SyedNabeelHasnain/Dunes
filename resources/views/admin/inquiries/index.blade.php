@extends('layouts.admin')

@section('page_title', 'Inquiries & Messages')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="h4 fw-800 text-dark mb-1">Customer Contact Inquiries Hub</h2>
        <p class="text-muted small mb-0">Manage customer inquiries from website contact forms, track response times, and reply quickly.</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <a href="{{ route('admin.inquiries.export', request()->query()) }}" class="btn btn-white shadow-sm border-0 rounded-pill px-3 py-2 fw-bold text-dark d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-arrow-down text-success fs-5"></i>
            <span>Export CSV</span>
        </a>
    </div>
</div>

<!-- 4 Key Performance Metric Cards -->
<div class="row g-3 g-lg-4 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">Total Inquiries</span>
                <span class="badge bg-primary-subtle text-primary rounded-circle p-2"><i class="bi bi-envelope-paper-fill fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-dark mb-0">{{ number_format($stats['total'] ?? 0) }}</h3>
            <span class="text-muted small" style="font-size: 0.75rem;">All contact submissions</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">Action Needed</span>
                <span class="badge bg-danger-subtle text-danger rounded-circle p-2"><i class="bi bi-exclamation-circle-fill fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-danger mb-0">{{ number_format($stats['new'] ?? 0) }}</h3>
            <span class="text-danger small fw-bold" style="font-size: 0.75rem;"><i class="bi bi-bell-fill me-1"></i>Unread inquiries</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">In Review</span>
                <span class="badge bg-warning-subtle text-warning rounded-circle p-2"><i class="bi bi-eye-fill fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-dark mb-0">{{ number_format($stats['read'] ?? 0) }}</h3>
            <span class="text-muted small" style="font-size: 0.75rem;">Viewed / in progress</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">Resolution Rate</span>
                <span class="badge bg-success-subtle text-success rounded-circle p-2"><i class="bi bi-check2-circle fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-success mb-0">{{ $stats['response_rate'] ?? 0 }}%</h3>
            <span class="text-muted small" style="font-size: 0.75rem;">{{ $stats['replied'] ?? 0 }} replied to customers</span>
        </div>
    </div>
</div>

<!-- 2 Interactive Analytics Charts -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card card-modern bg-white border-0 shadow-sm rounded-4 p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-800 text-dark mb-1"><i class="bi bi-graph-up text-primary me-2"></i>14-Day Inquiries Acquisition Trend</h6>
                    <span class="text-muted small">Daily volume of incoming contact requests</span>
                </div>
            </div>
            <div style="height: 220px;">
                <canvas id="inquiriesTrendChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-modern bg-white border-0 shadow-sm rounded-4 p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-800 text-dark mb-1"><i class="bi bi-pie-chart-fill text-warning me-2"></i>Status Breakdown</h6>
                    <span class="text-muted small">Resolution pipeline</span>
                </div>
            </div>
            <div style="height: 220px; position: relative;">
                <canvas id="statusBreakdownChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Multi-Parameter Filter Toolbar -->
<div class="card card-modern border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
    <form method="GET" action="{{ route('admin.inquiries.index') }}">
        <div class="row g-2 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label for="inquirySearch" class="form-label small fw-bold text-dark mb-1">Search Messages</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" id="inquirySearch" class="form-control border-start-0" placeholder="Name, Email, Subject, Message..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <label for="statusFilter" class="form-label small fw-bold text-dark mb-1">Status</label>
                <select name="status" id="statusFilter" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>Action Needed (New)</option>
                    <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>In Review (Read)</option>
                    <option value="replied" {{ request('status') === 'replied' ? 'selected' : '' }}>Resolved (Replied)</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label for="fromDate" class="form-label small fw-bold text-dark mb-1">From Date</label>
                <input type="date" name="from_date" id="fromDate" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-lg-3 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold" title="Apply Filter"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                <a href="{{ route('admin.inquiries.index') }}" class="btn btn-light border" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </div>
    </form>
</div>

<!-- Inquiries Table -->
<div class="card card-modern border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4 p-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover datatable" id="inquiriesTable">
                <thead class="table-light small text-uppercase fw-bold text-muted">
                    <tr>
                        <th class="ps-4 no-sort no-export" style="width: 36px;">
                            <input type="checkbox" class="form-check-input inquiries-select-all" title="Select All">
                        </th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Subject</th>
                        <th class="text-center">Status</th>
                        <th class="pe-4 text-end no-sort">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inquiries as $c)
                    <tr>
                        <td class="ps-4 no-export">
                            <input type="checkbox" class="form-check-input inquiries-row-select" value="{{ $c->id }}">
                        </td>
                        <td data-order="{{ $c->created_at ? $c->created_at->timestamp : 0 }}">
                            <div class="small fw-bold text-dark">
                                {{ $c->created_at ? $c->created_at->format('M j, Y') : '' }}
                            </div>
                            <div class="text-muted small" style="font-size: 0.72rem;">
                                {{ $c->created_at ? $c->created_at->format('g:ia') : '' }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $c->name }}</div>
                            <div class="text-muted small">{{ $c->email }}</div>
                        </td>
                        <td>
                            <div class="fw-medium text-dark">{{ $c->subject }}</div>
                        </td>
                        <td class="text-center">
                            @php
                                $badgeColor = [
                                    'new' => 'danger',
                                    'read' => 'warning',
                                    'replied' => 'success'
                                ][$c->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $badgeColor }} text-capitalize px-3 py-1 rounded-pill">{{ $c->status }}</span>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.inquiries.show', $c->id) }}" class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="View Detail">
                                    <i class="bi bi-eye-fill"></i>
                                </a>

                                <form action="{{ route('admin.inquiries.status', $c->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="replied">
                                    <button type="submit" class="btn btn-sm btn-outline-success rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="Mark Replied" {{ $c->status === 'replied' ? 'disabled' : '' }}>
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>

                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center btn-delete-inquiry" 
                                        style="width: 34px; height: 34px;" 
                                        title="Permanently Delete Inquiry"
                                        data-id="{{ $c->id }}"
                                        data-name="{{ $c->name }}"
                                        data-email="{{ $c->email }}"
                                        data-subject="{{ $c->subject }}">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-envelope-x fs-1 d-block mb-2 text-muted opacity-50"></i>
                            No contact inquiries match your filter criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Floating Batch Bulk Action Toolbar -->
<div id="inquiriesBulkBar" class="bulk-action-bar">
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary rounded-pill px-2 py-1"><span id="inquiriesSelectedCount">0</span></span>
        <span class="fw-semibold small text-white">selected</span>
    </div>
    <div class="vr bg-secondary opacity-50" style="height: 20px;"></div>
    <div class="btn-group btn-group-sm">
        <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-arrow-repeat me-1 text-warning"></i> Set Status
        </button>
        <ul class="dropdown-menu dropdown-menu-dark shadow-lg">
            <li><a class="dropdown-item inquiries-bulk-action" href="javascript:void(0)" data-action="status_read"><i class="bi bi-eye text-warning me-2"></i>Mark as Read</a></li>
            <li><a class="dropdown-item inquiries-bulk-action" href="javascript:void(0)" data-action="status_replied"><i class="bi bi-check-circle text-success me-2"></i>Mark as Replied</a></li>
            <li><a class="dropdown-item inquiries-bulk-action" href="javascript:void(0)" data-action="status_new"><i class="bi bi-bell text-danger me-2"></i>Mark as New</a></li>
        </ul>
    </div>
    <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 inquiries-bulk-action" data-action="delete">
        <i class="bi bi-trash3 me-1"></i> Delete
    </button>
    <button type="button" class="btn btn-sm btn-link text-white-50 p-0 ms-1 text-decoration-none" id="inquiriesBulkClear" title="Deselect all">
        <i class="bi bi-x-lg"></i>
    </button>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // 14-Day Inquiries Acquisition Trend Chart
    const trendCtx = document.getElementById('inquiriesTrendChart');
    if (trendCtx) {
        const trendData = @json($trendData);
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.date),
                datasets: [{
                    label: 'Inquiries',
                    data: trendData.map(d => d.count),
                    borderColor: '#F58F43',
                    backgroundColor: 'rgba(245, 143, 67, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#F58F43',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { color: '#f5f5f5' } },
                    y: { beginAtZero: true, grid: { color: '#f5f5f5' }, ticks: { stepSize: 1 } }
                }
            }
        });
    }

    // Status Breakdown Doughnut Chart
    const statusCtx = document.getElementById('statusBreakdownChart');
    if (statusCtx) {
        const statusData = @json($statusBreakdown);
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Action Needed (New)', 'In Review (Read)', 'Resolved (Replied)'],
                datasets: [{
                    data: [statusData.new || 0, statusData.read || 0, statusData.replied || 0],
                    backgroundColor: ['#ef4444', '#f59e0b', '#10b981'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } }
                },
                cutout: '65%'
            }
        });
    }

    // Inquiries Batch Bulk Selection & Processing
    const $inqBulkBar = $('#inquiriesBulkBar');
    const $inqSelectAll = $('.inquiries-select-all');
    const $inqCountBadge = $('#inquiriesSelectedCount');

    function updateInqBulkBar() {
        const checkedBoxes = $('.inquiries-row-select:checked');
        const count = checkedBoxes.length;
        $inqCountBadge.text(count);
        if (count > 0) {
            $inqBulkBar.addClass('show');
        } else {
            $inqBulkBar.removeClass('show');
        }
    }

    $inqSelectAll.on('change', function() {
        $('.inquiries-row-select').prop('checked', $(this).is(':checked'));
        updateInqBulkBar();
    });

    $(document).on('change', '.inquiries-row-select', function() {
        const total = $('.inquiries-row-select').length;
        const checked = $('.inquiries-row-select:checked').length;
        $inqSelectAll.prop('checked', total > 0 && total === checked);
        updateInqBulkBar();
    });

    $('#inquiriesBulkClear').on('click', function() {
        $('.inquiries-row-select, .inquiries-select-all').prop('checked', false);
        updateInqBulkBar();
    });

    // Single inquiry delete button trigger
    $(document).on('click', '.btn-delete-inquiry', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const name = $(this).data('name') || 'Customer';
        const email = $(this).data('email') || '';
        const subject = $(this).data('subject') || '';
        promptPermanentDeleteInquiry(id, name, email, subject);
    });

    $('.inquiries-bulk-action').on('click', function(e) {
        e.preventDefault();
        const action = $(this).data('action');
        const selectedIds = $('.inquiries-row-select:checked').map(function() { return $(this).val(); }).get();

        if (selectedIds.length === 0) return;

        const isDelete = action === 'delete';

        if (isDelete) {
            // Step 1: Caution Dialog for Bulk Inquiries Purge
            Swal.fire({
                title: '⚠️ CAUTION: Permanent Bulk Inquiries Purge',
                html: `
                    <div class="text-start small text-secondary">
                        <div class="alert alert-danger py-2 px-3 mb-3 border-danger border-opacity-25 bg-danger bg-opacity-10 text-danger fw-semibold">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            <strong>IRREVERSIBLE ACTION:</strong> You are about to permanently eradicate <strong class="text-dark">${selectedIds.length} contact inquiries</strong> from the database.
                        </div>
                        <div class="card bg-light border-0 p-2.5 mb-3">
                            <div class="fw-bold text-dark mb-1 small text-uppercase" style="font-size: 11px;">The following records will be permanently erased:</div>
                            <ul class="mb-0 ps-3 text-muted" style="font-size: 12px; line-height: 1.6;">
                                <li>All ${selectedIds.length} customer messages, inquiries & sender details</li>
                                <li>All associated visitor telemetry, IP tracking & request logs</li>
                                <li>Complete attribution, UTM tags & analytics footprints</li>
                            </ul>
                        </div>
                        <p class="mb-0 text-muted">Are you sure you want to proceed to final verification?</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Proceed to Verification <i class="bi bi-arrow-right ms-1"></i>',
                cancelButtonText: 'Cancel (Keep Inquiries)',
                focusCancel: true
            }).then((step1) => {
                if (!step1.isConfirmed) return;

                // Step 2: Final Safeguard Bulk Purge Double Confirmation
                Swal.fire({
                    title: '🔒 Double Confirmation: Bulk Purge',
                    html: `
                        <div class="text-start small">
                            <p class="text-dark mb-2">To permanently purge all <strong>${selectedIds.length}</strong> selected inquiry record(s) and their analytics footprints, type <span class="badge bg-danger text-white font-monospace">DELETE</span> below:</p>
                        </div>
                    `,
                    input: 'text',
                    inputPlaceholder: 'Type DELETE to confirm',
                    inputAttributes: {
                        autocapitalize: 'characters',
                        style: 'text-align: center; font-family: monospace; font-weight: bold; font-size: 1.1rem; letter-spacing: 2px;'
                    },
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#b02a37',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> PURGE ALL SELECTED RECORDS',
                    cancelButtonText: 'Abort',
                    focusCancel: true,
                    showLoaderOnConfirm: true,
                    preConfirm: (inputValue) => {
                        if (inputValue !== 'DELETE') {
                            Swal.showValidationMessage('Verification mismatch! You must type exactly "DELETE" in capital letters to authorize bulk deletion.');
                            return false;
                        }
                        return $.ajax({
                            url: '{{ route("admin.inquiries.bulk") }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                ids: selectedIds,
                                action: 'delete'
                            },
                            headers: { 'Accept': 'application/json' }
                        }).then(response => {
                            if (!response.success) {
                                throw new Error(response.message || 'Failed to process bulk action.');
                            }
                            return response;
                        }).catch(error => {
                            const msg = error.responseJSON ? error.responseJSON.message : error.message;
                            Swal.showValidationMessage(`Bulk Purge Failed: ${msg}`);
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((step2Result) => {
                    if (step2Result.isConfirmed && step2Result.value) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Purged!',
                            text: step2Result.value.message || 'Selected inquiries permanently deleted.',
                            timer: 1600,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                });
            });
            return;
        }

        const actionLabel = 'mark as ' + action.replace('status_', '');

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to ${actionLabel} for ${selectedIds.length} selected inquiry message(s).`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#F58F43',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Update',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#appLoader').fadeIn(200);
                $.ajax({
                    url: '{{ route("admin.inquiries.bulk") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: selectedIds,
                        action: action
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: res.message || 'Inquiries updated successfully.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        $('#appLoader').fadeOut(200);
                        const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error processing bulk action.';
                        Swal.fire('Failed', msg, 'error');
                    }
                });
            }
        });
    });
});

/**
 * 2-Step Cautionary Double Confirmation for Permanent Single Contact Inquiry Deletion
 */
window.promptPermanentDeleteInquiry = function(id, name, email, subject) {
    const verifyTarget = (email && email.trim()) ? email.trim() : (name ? name.trim() : 'CONFIRM');

    // Step 1: Caution Dialog
    Swal.fire({
        title: '⚠️ CAUTION: Permanent Inquiry Deletion',
        html: `
            <div class="text-start small text-secondary">
                <div class="alert alert-danger py-2 px-3 mb-3 border-danger border-opacity-25 bg-danger bg-opacity-10 text-danger fw-semibold">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>IRREVERSIBLE ACTION:</strong> You are about to permanently eradicate contact inquiry <strong class="text-dark">#${id} (${name})</strong> from the database.
                </div>
                <div class="card bg-light border-0 p-2.5 mb-3">
                    <div class="fw-bold text-dark mb-1 small text-uppercase" style="font-size: 11px;">The following records will be permanently purged:</div>
                    <ul class="mb-0 ps-3 text-muted" style="font-size: 12px; line-height: 1.6;">
                        <li>Contact Inquiry Record (${email || 'No Email'})</li>
                        <li>Customer message content & subject ("${subject || 'Inquiry'}")</li>
                        <li>Visitor Analytics, Telemetry & Request Logs (${id})</li>
                        <li>Client IP & Geolocation Audit Trail</li>
                    </ul>
                </div>
                <p class="mb-0 text-muted">Are you sure you want to proceed to the final verification?</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Proceed to Final Confirmation <i class="bi bi-arrow-right ms-1"></i>',
        cancelButtonText: 'Cancel (Keep Inquiry)',
        focusCancel: true
    }).then((step1Result) => {
        if (!step1Result.isConfirmed) return;

        // Step 2: Final Safeguard Double Confirmation
        Swal.fire({
            title: '🔒 Double Confirmation Required',
            html: `
                <div class="text-start small">
                    <p class="text-dark mb-2">To prevent accidental deletion, please type the customer email below to authorize permanent destruction:</p>
                    <div class="text-center my-3">
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 fs-6 font-monospace py-2 px-3">
                            ${verifyTarget}
                        </span>
                    </div>
                </div>
            `,
            input: 'text',
            inputPlaceholder: `Type ${verifyTarget} to confirm`,
            inputAttributes: {
                autocapitalize: 'off',
                autocorrect: 'off',
                autocomplete: 'off',
                style: 'text-align: center; font-family: monospace; font-weight: bold; font-size: 1.1rem; letter-spacing: 1px;'
            },
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#b02a37',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> PERMANENTLY PURGE EVERYTHING',
            cancelButtonText: 'Abort',
            focusCancel: true,
            showLoaderOnConfirm: true,
            preConfirm: (inputValue) => {
                if (!inputValue || inputValue.trim().toLowerCase() !== verifyTarget.toLowerCase()) {
                    Swal.showValidationMessage(`Verification mismatch! You must type exactly "${verifyTarget}" to authorize deletion.`);
                    return false;
                }
                return $.ajax({
                    url: `/admin/inquiries/${id}`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    headers: { 'Accept': 'application/json' }
                }).then(response => {
                    if (!response.success) {
                        throw new Error(response.message || 'Failed to delete inquiry.');
                    }
                    return response;
                }).catch(error => {
                    const msg = error.responseJSON ? error.responseJSON.message : error.message;
                    Swal.showValidationMessage(`Purge Failed: ${msg}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((step2Result) => {
            if (step2Result.isConfirmed && step2Result.value) {
                Swal.fire({
                    icon: 'success',
                    title: 'Purged!',
                    text: step2Result.value.message || 'Inquiry and all analytics footprints permanently deleted.',
                    timer: 1600,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            }
        });
    });
};
</script>
@endpush
@endsection
