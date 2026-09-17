@extends('layouts.admin')

@section('page_title', 'Bookings & Orders')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="h4 fw-800 text-dark mb-1">Bookings & Reservations</h2>
        <p class="text-muted small mb-0">Manage customer tour bookings, payment reconciliations, and instant WhatsApp support.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.bookings.export', request()->query()) }}" class="btn btn-outline-success rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-file-earmark-spreadsheet me-2"></i> Export CSV
        </a>
    </div>
</div>

<!-- 4 Booking Summary Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">Total Bookings</span>
                <span class="badge bg-primary-subtle text-primary rounded-circle p-2"><i class="bi bi-calendar-check-fill fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-dark mb-0">{{ number_format($stats['total'] ?? 0) }}</h3>
            <span class="text-muted small" style="font-size: 0.75rem;">All tour reservations</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">Confirmed / Paid</span>
                <span class="badge bg-success-subtle text-success rounded-circle p-2"><i class="bi bi-check-circle-fill fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-dark mb-0">{{ number_format($stats['confirmed'] ?? 0) }}</h3>
            <span class="text-success small fw-bold" style="font-size: 0.75rem;"><i class="bi bi-shield-check me-1"></i>Active & Confirmed</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">Pending Inquiries</span>
                <span class="badge bg-warning-subtle text-warning rounded-circle p-2"><i class="bi bi-clock-history fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-dark mb-0">{{ number_format($stats['pending'] ?? 0) }}</h3>
            <span class="text-warning small fw-bold" style="font-size: 0.75rem;"><i class="bi bi-hourglass-split me-1"></i>Awaiting confirmation</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">Collected Revenue</span>
                <span class="badge bg-info-subtle text-info rounded-circle p-2"><i class="bi bi-cash-stack fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-primary mb-0">AED {{ number_format($stats['revenue'] ?? 0) }}</h3>
            <span class="text-muted small" style="font-size: 0.75rem;">AOV: AED {{ number_format($stats['aov'] ?? 0) }}</span>
        </div>
    </div>
</div>

<!-- 2 Interactive Analytics Charts -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card card-modern bg-white border-0 shadow-sm rounded-4 p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-800 text-dark mb-1"><i class="bi bi-graph-up-arrow text-primary me-2"></i>14-Day Bookings & Revenue Trend</h6>
                    <span class="text-muted small">Daily volume of reservations and collected payments</span>
                </div>
            </div>
            <div style="height: 220px;">
                <canvas id="bookingsTrendChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-modern bg-white border-0 shadow-sm rounded-4 p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-800 text-dark mb-1"><i class="bi bi-pie-chart-fill text-success me-2"></i>Status Distribution</h6>
                    <span class="text-muted small">Confirmed vs pending vs cancelled</span>
                </div>
            </div>
            <div style="height: 220px; position: relative;">
                <canvas id="bookingStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filters Toolbar -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
    <form method="GET" action="{{ route('admin.bookings.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-lg-3 col-md-6">
                <label for="searchQuery" class="form-label small fw-bold text-dark">Keyword Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" id="searchQuery" class="form-control border-start-0" placeholder="Ref, name, phone, email..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-lg-2 col-md-3">
                <label for="statusFilter" class="form-label small fw-bold text-dark">Booking Status</label>
                <select name="status" id="statusFilter" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Abandoned Checkouts (Drafts)</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-3">
                <label for="paymentFilter" class="form-label small fw-bold text-dark">Payment Status</label>
                <select name="payment_status" id="paymentFilter" class="form-select">
                    <option value="">All Payments</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial (Advance)</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="cancelled" {{ request('payment_status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-3">
                <label for="fromDate" class="form-label small fw-bold text-dark">Tour From</label>
                <input type="date" name="from_date" id="fromDate" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-lg-2 col-md-3">
                <label for="toDate" class="form-label small fw-bold text-dark">Tour To</label>
                <input type="date" name="to_date" id="toDate" class="form-control" value="{{ request('to_date') }}">
            </div>
            <div class="col-lg-1 col-md-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold" title="Filter"><i class="bi bi-funnel-fill"></i></button>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-light border" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </div>
    </form>
</div>

<div class="card card-modern border-0 shadow-sm rounded-4 overflow-hidden bg-white p-3 mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover datatable" id="bookingsTable">
                <thead class="table-light small text-uppercase fw-bold text-muted">
                    <tr>
                        <th class="ps-4 no-sort no-export" style="width: 36px;">
                            <input type="checkbox" class="form-check-input bulk-select-all" title="Select All">
                        </th>
                        <th>Ref & Time</th>
                        <th>Customer</th>
                        <th>Tour / Activity</th>
                        <th>Tour Date</th>
                        <th>Total & Paid</th>
                        <th class="text-center">Status</th>
                        <th class="pe-4 text-end no-sort">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $b)
                    <tr>
                        <td class="ps-4 no-export">
                            <input type="checkbox" class="form-check-input bulk-row-select" value="{{ $b->id }}">
                        </td>
                        <td data-order="{{ $b->created_at ? $b->created_at->timestamp : 0 }}">
                            <div class="fw-800 text-dark">#{{ $b->reference }}</div>
                            <div class="text-muted small" style="font-size: 0.75rem;">{{ $b->created_at ? $b->created_at->format('M j, Y g:ia') : '' }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $b->name }}</div>
                            <div class="text-muted small font-monospace">{{ $b->phone }}</div>
                            <div class="text-muted small">{{ $b->email }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $b->tour_name }}</div>
                            <div class="d-flex align-items-center gap-1 flex-wrap mt-1">
                                <span class="badge bg-light text-primary border rounded-pill" style="font-size: 0.72rem;">
                                    {{ $b->tier_name ?: ($b->tier ? $b->tier->display_name : 'Standard') }}
                                </span>
                                @if($b->addons && $b->addons->count() > 0)
                                    <span class="badge bg-success-subtle text-success rounded-pill" style="font-size: 0.7rem;" title="{{ $b->addons->pluck('addon_name')->implode(', ') }}">
                                        +{{ $b->addons->count() }} Addon{{ $b->addons->count() > 1 ? 's' : '' }}
                                    </span>
                                @endif
                            </div>
                            <span class="small text-muted">({{ $b->adults }} Adults, {{ $b->children }} Child)</span>
                        </td>
                        <td data-order="{{ $b->tour_date ? $b->tour_date->timestamp : 0 }}">
                            <div class="small fw-bold text-dark">{{ $b->tour_date ? $b->tour_date->format('M j, Y') : 'Open Date' }}</div>
                            @if($b->pickup_time)
                                <div class="text-muted small" style="font-size: 0.72rem;"><i class="bi bi-clock me-1"></i>{{ $b->pickup_time }}</div>
                            @endif
                        </td>
                        <td data-order="{{ (float)$b->total }}">
                            <div class="fw-800 text-primary">AED {{ number_format($b->total) }}</div>
                            <div class="small text-muted" style="font-size: 0.72rem;">
                                @if($b->payment_status === 'paid')
                                    Paid: <strong class="text-success">AED {{ number_format($b->payment_amount ?: $b->total) }}</strong> | Due: AED 0
                                @elseif($b->payment_status === 'partial')
                                    Paid: <strong class="text-warning">AED {{ number_format($b->payment_amount) }}</strong> | Due: AED {{ number_format($b->balance_due) }}
                                @else
                                    Paid: <strong class="text-secondary">AED 0</strong> | Due: AED {{ number_format($b->total) }}
                                @endif
                            </div>
                        </td>
                        <td class="text-center" data-order="{{ $b->status }}">
                            @php
                                $badgeColor = [
                                    'pending' => 'warning',
                                    'confirmed' => 'success',
                                    'completed' => 'info',
                                    'cancelled' => 'danger',
                                    'draft' => 'secondary'
                                ];
                                $bCol = $badgeColor[$b->status] ?? 'secondary';
                            @endphp
                            @if($b->status === 'draft')
                                <span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1 rounded-pill fw-bold" style="font-size: 0.72rem;">Abandoned Lead</span>
                            @else
                                <span class="badge bg-{{ $bCol }} text-capitalize px-3 py-1 rounded-pill">{{ $b->status }}</span>
                            @endif
                            <div class="small mt-1 text-capitalize text-muted" style="font-size: 0.7rem;">
                                Payment: <span class="fw-bold text-dark">{{ $b->payment_status }}</span>
                            </div>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex justify-content-end gap-2 align-items-center">
                                @if($b->status === 'draft')
                                    @php
                                        $waVal = preg_replace('/[^0-9]/', '', $b->phone);
                                        $waMsg = 'Hi ' . $b->name . '! We noticed you started reserving the ' . $b->tour_name . ' on Dunes Discovery. Your spot is held for ' . ($b->tour_date ? $b->tour_date->format('M j') : 'your chosen date') . '. Would you like help completing your reservation with code FIRST25 (25% OFF)?';
                                    @endphp
                                    <a href="https://wa.me/{{ $waVal }}?text={{ urlencode($waMsg) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-warning text-dark rounded-pill px-3 py-1 fw-bold shadow-sm d-flex align-items-center gap-1" title="Send WhatsApp Recovery">
                                        <i class="bi bi-whatsapp"></i>
                                        <span>Recover</span>
                                    </a>
                                @else
                                    <a href="{{ route('admin.bookings.show', $b->id) }}" class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="View Booking Details">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <a href="{{ route('admin.bookings.ticket', $b->id) }}" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="Download E-Ticket PDF">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>
                                    @php
                                        $waVal = preg_replace('/[^0-9]/', '', $b->phone);
                                        $waMsg = 'Hi ' . $b->name . '! This is Dunes Discovery regarding your booking #' . $b->reference;
                                    @endphp
                                    <a href="https://wa.me/{{ $waVal }}?text={{ urlencode($waMsg) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-success rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="WhatsApp Customer">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                @endif
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center btn-delete-booking" style="width: 34px; height: 34px;" title="Permanently Delete Booking & All Records" onclick="promptPermanentDelete({{ $b->id }}, '{{ $b->reference }}', '{{ addslashes($b->name) }}')">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x fs-1 d-block mb-2 text-muted opacity-50"></i>
                            No bookings match your current filter parameters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Floating Batch Bulk Action Toolbar -->
<div id="bookingBulkBar" class="bulk-action-bar">
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary rounded-pill px-2 py-1"><span id="bookingSelectedCount">0</span></span>
        <span class="fw-semibold small text-white">selected</span>
    </div>
    <div class="vr bg-secondary opacity-50" style="height: 20px;"></div>
    <div class="btn-group btn-group-sm">
        <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-arrow-repeat me-1 text-warning"></i> Set Status
        </button>
        <ul class="dropdown-menu dropdown-menu-dark shadow-lg">
            <li><a class="dropdown-item booking-bulk-action" href="javascript:void(0)" data-action="status_confirmed"><i class="bi bi-check-circle text-success me-2"></i>Confirmed</a></li>
            <li><a class="dropdown-item booking-bulk-action" href="javascript:void(0)" data-action="status_completed"><i class="bi bi-check2-all text-info me-2"></i>Completed</a></li>
            <li><a class="dropdown-item booking-bulk-action" href="javascript:void(0)" data-action="status_pending"><i class="bi bi-clock-history text-warning me-2"></i>Pending</a></li>
            <li><a class="dropdown-item booking-bulk-action" href="javascript:void(0)" data-action="status_cancelled"><i class="bi bi-x-circle text-danger me-2"></i>Cancelled</a></li>
        </ul>
    </div>
    <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 booking-bulk-action" data-action="delete">
        <i class="bi bi-trash3 me-1"></i> Delete
    </button>
    <button type="button" class="btn btn-sm btn-link text-white-50 p-0 ms-1 text-decoration-none" id="bookingBulkClear" title="Deselect all">
        <i class="bi bi-x-lg"></i>
    </button>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // 14-Day Bookings & Revenue Trend Chart
    const trendCtx = document.getElementById('bookingsTrendChart');
    if (trendCtx) {
        const trendData = @json($trendData);
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.date),
                datasets: [
                    {
                        label: 'Bookings Count',
                        data: trendData.map(d => d.count),
                        borderColor: '#F58F43',
                        backgroundColor: 'rgba(245, 143, 67, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.35,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Revenue (AED)',
                        data: trendData.map(d => d.revenue),
                        borderColor: '#10b981',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [4, 4],
                        pointBackgroundColor: '#10b981',
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { color: '#f5f5f5' } },
                    y: { type: 'linear', position: 'left', beginAtZero: true, grid: { color: '#f5f5f5' }, ticks: { stepSize: 1 } },
                    y1: { type: 'linear', position: 'right', beginAtZero: true, grid: { display: false } }
                }
            }
        });
    }

    // Status Distribution Doughnut Chart
    const statusCtx = document.getElementById('bookingStatusChart');
    if (statusCtx) {
        const statusData = @json($statusDistribution);
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Confirmed', 'Pending', 'Completed', 'Cancelled'],
                datasets: [{
                    data: [statusData.confirmed || 0, statusData.pending || 0, statusData.completed || 0, statusData.cancelled || 0],
                    backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#ef4444'],
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

    // Batch Bulk Selection & Processing
    const $bulkBar = $('#bookingBulkBar');
    const $selectAll = $('.bulk-select-all');
    const $countBadge = $('#bookingSelectedCount');

    function updateBulkBar() {
        const checkedBoxes = $('.bulk-row-select:checked');
        const count = checkedBoxes.length;
        $countBadge.text(count);
        if (count > 0) {
            $bulkBar.addClass('show');
        } else {
            $bulkBar.removeClass('show');
        }
    }

    $selectAll.on('change', function() {
        $('.bulk-row-select').prop('checked', $(this).is(':checked'));
        updateBulkBar();
    });

    $(document).on('change', '.bulk-row-select', function() {
        const total = $('.bulk-row-select').length;
        const checked = $('.bulk-row-select:checked').length;
        $selectAll.prop('checked', total > 0 && total === checked);
        updateBulkBar();
    });

    $('#bookingBulkClear').on('click', function() {
        $('.bulk-row-select, .bulk-select-all').prop('checked', false);
        updateBulkBar();
    });

    $('.booking-bulk-action').on('click', function(e) {
        e.preventDefault();
        const action = $(this).data('action');
        const selectedIds = $('.bulk-row-select:checked').map(function() { return $(this).val(); }).get();

        if (selectedIds.length === 0) return;

        const isDelete = action === 'delete';

        if (isDelete) {
            // Step 1: Caution Dialog for Bulk Deletion
            Swal.fire({
                title: '⚠️ CAUTION: Permanent Bulk Deletion',
                html: `
                    <div class="text-start small text-secondary">
                        <div class="alert alert-danger py-2 px-3 mb-3 border-danger border-opacity-25 bg-danger bg-opacity-10 text-danger fw-semibold">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            <strong>IRREVERSIBLE BULK ACTION:</strong> You are about to permanently eradicate <strong class="text-dark">${selectedIds.length}</strong> selected booking(s) from the database.
                        </div>
                        <p class="mb-2 text-dark">This will permanently delete all associated payment history, booked addons, analytics logs, and guest reviews for all selected records.</p>
                        <p class="mb-0 text-muted">Are you sure you want to proceed to the final confirmation?</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Proceed to Final Confirmation <i class="bi bi-arrow-right ms-1"></i>',
                cancelButtonText: 'Cancel (Keep Bookings)',
                focusCancel: true
            }).then((step1Result) => {
                if (!step1Result.isConfirmed) return;

                // Step 2: Final Safeguard Double Confirmation
                Swal.fire({
                    title: '🔒 Confirm Bulk Deletion',
                    html: `
                        <div class="text-start small">
                            <p class="text-dark mb-2">To confirm permanent deletion of <strong>${selectedIds.length}</strong> bookings and all related records, type <strong>DELETE</strong> in capital letters below:</p>
                        </div>
                    `,
                    input: 'text',
                    inputPlaceholder: 'DELETE',
                    inputAttributes: {
                        autocapitalize: 'characters',
                        style: 'text-align: center; font-family: monospace; font-weight: bold; font-size: 1.1rem; letter-spacing: 2px;'
                    },
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#b02a37',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> PURGE ALL SELECTED',
                    cancelButtonText: 'Abort',
                    focusCancel: true,
                    showLoaderOnConfirm: true,
                    preConfirm: (inputValue) => {
                        if (!inputValue || inputValue.trim() !== 'DELETE') {
                            Swal.showValidationMessage('Verification failed! You must type "DELETE" exactly.');
                            return false;
                        }
                        return $.ajax({
                            url: '{{ route("admin.bookings.bulk") }}',
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
                            text: step2Result.value.message || 'Selected bookings permanently deleted.',
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

        // Standard status change bulk action
        const actionLabel = 'update status to ' + action.replace('status_', '');
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to ${actionLabel} for ${selectedIds.length} selected booking(s).`,
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
                    url: '{{ route("admin.bookings.bulk") }}',
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
                            text: res.message || 'Bookings updated successfully.',
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
 * 2-Step Cautionary Double Confirmation for Permanent Single Booking Deletion
 */
window.promptPermanentDelete = function(id, reference, name) {
    // Step 1: Caution Dialog
    Swal.fire({
        title: '⚠️ CAUTION: Permanent Booking Deletion',
        html: `
            <div class="text-start small text-secondary">
                <div class="alert alert-danger py-2 px-3 mb-3 border-danger border-opacity-25 bg-danger bg-opacity-10 text-danger fw-semibold">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>IRREVERSIBLE ACTION:</strong> You are about to permanently eradicate Booking <span class="font-monospace fw-bold">#${reference}</span> from the database.
                </div>
                <p class="mb-2 text-dark"><strong>Customer:</strong> ${name || 'N/A'}</p>
                <div class="card bg-light border-0 p-2.5 mb-3">
                    <div class="fw-bold text-dark mb-1 small text-uppercase" style="font-size: 11px;">The following records will be permanently purged:</div>
                    <ul class="mb-0 ps-3 text-muted" style="font-size: 12px; line-height: 1.6;">
                        <li>Primary Booking Record & Guest Information</li>
                        <li>All Booked Add-ons, Buggies & Extra Selections</li>
                        <li>Complete Payment History & Gateway Transaction Records</li>
                        <li>Visitor Analytics, Telemetry & Request Logs</li>
                        <li>Guest Reviews submitted for this booking</li>
                        <li>Coupon usage quota will be released back to pool</li>
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
        cancelButtonText: 'Cancel (Keep Booking)',
        focusCancel: true
    }).then((step1Result) => {
        if (!step1Result.isConfirmed) return;

        // Step 2: Final Safeguard Double Confirmation
        Swal.fire({
            title: '🔒 Double Confirmation Required',
            html: `
                <div class="text-start small">
                    <p class="text-dark mb-2">To prevent accidental deletion, please type the booking reference below to authorize permanent destruction:</p>
                    <div class="text-center my-3">
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 fs-6 font-monospace py-2 px-3">
                            ${reference}
                        </span>
                    </div>
                </div>
            `,
            input: 'text',
            inputPlaceholder: `Type ${reference} to confirm`,
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
                if (!inputValue || inputValue.trim() !== reference) {
                    Swal.showValidationMessage(`Reference mismatch! You must type exactly "${reference}" to authorize deletion.`);
                    return false;
                }
                return $.ajax({
                    url: `/admin/bookings/${id}`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    headers: { 'Accept': 'application/json' }
                }).then(response => {
                    if (!response.success) {
                        throw new Error(response.message || 'Failed to delete booking.');
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
                    text: step2Result.value.message || `Booking #${reference} was permanently deleted.`,
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
