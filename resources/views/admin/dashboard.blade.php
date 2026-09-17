@extends('layouts.admin')

@section('page_title', 'Executive Dashboard')

@section('content')
<!-- Top Executive Header with Real-Time Database Sync & Live Refresh Trigger -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h2 class="h4 fw-800 text-dark mb-0">Executive Dashboard</h2>
            <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 small fw-bold d-inline-flex align-items-center gap-1.5">
                <span class="live-pulse-dot"></span> Live Real-Time Database Sync
            </span>
        </div>
        <p class="text-muted small mb-0">Direct live counts from database transactions. Zero caching, instant precision.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="text-muted extra-small text-uppercase fw-semibold d-none d-sm-inline" id="kpiSyncStatus" style="font-size: 0.72rem;">Live as of {{ now()->format('g:i:s A') }}</span>
        <button type="button" class="btn btn-white shadow-sm border rounded-pill px-3 py-2 fw-bold text-dark d-flex align-items-center gap-2" id="btnRefreshDashboardKpis" title="Recount all metrics live directly from database">
            <i class="bi bi-arrow-repeat text-primary fs-6" id="kpiSyncIcon"></i>
            <span>Refresh Metrics</span>
        </button>
    </div>
</div>

<!-- 6 Core Business Performance KPI Cards Row -->
<div class="row g-3 g-lg-4 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="stat-card-icon bg-primary-subtle text-primary mb-2 d-flex align-items-center justify-content-center rounded-circle" style="width:40px; height:40px; font-size:18px;">
                <i class="bi bi-calendar-event-fill"></i>
            </div>
            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size:0.72rem;">Total Bookings</div>
            <h3 class="fw-800 mb-0 text-dark kpi-number" id="kpiTotalBookings">{{ number_format($stats['total']) }}</h3>
            <span class="text-muted small" style="font-size: 0.7rem;">Active reservations</span>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="stat-card-icon bg-success-subtle text-success mb-2 d-flex align-items-center justify-content-center rounded-circle" style="width:40px; height:40px; font-size:18px;">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size:0.72rem;">Confirmed & Done</div>
            <h3 class="fw-800 mb-0 text-dark kpi-number" id="kpiConfirmedBookings">{{ number_format($stats['confirmed_and_completed'] ?? ($stats['confirmed'] + ($stats['completed'] ?? 0))) }}</h3>
            <span class="text-success small fw-bold" style="font-size: 0.7rem;"><span id="kpiConfirmedCount">{{ $stats['confirmed'] }}</span> Confirmed, <span id="kpiCompletedCount">{{ $stats['completed'] ?? 0 }}</span> Done</span>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="stat-card-icon bg-warning-subtle text-warning mb-2 d-flex align-items-center justify-content-center rounded-circle" style="width:40px; height:40px; font-size:18px;">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size:0.72rem;">Pending Bookings</div>
            <h3 class="fw-800 mb-0 text-dark kpi-number" id="kpiPendingBookings">{{ number_format($stats['pending']) }}</h3>
            <span class="text-warning small fw-bold" style="font-size: 0.7rem;"><i class="bi bi-hourglass-split me-1"></i>Awaiting review</span>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="stat-card-icon bg-info-subtle text-info mb-2 d-flex align-items-center justify-content-center rounded-circle" style="width:40px; height:40px; font-size:18px;">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size:0.72rem;">Collected Revenue</div>
            <h4 class="fw-800 mb-0 text-primary kpi-number" id="kpiRevenue">AED {{ number_format($stats['revenue']) }}</h4>
            <span class="text-muted small" style="font-size: 0.7rem;">Verified payments</span>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="stat-card-icon bg-secondary-subtle text-dark mb-2 d-flex align-items-center justify-content-center rounded-circle" style="width:40px; height:40px; font-size:18px;">
                <i class="bi bi-bag-check-fill"></i>
            </div>
            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size:0.72rem;">Avg Order Value</div>
            <h4 class="fw-800 mb-0 text-dark kpi-number" id="kpiAov">AED {{ number_format($stats['aov'] ?? 0) }}</h4>
            <span class="text-muted small" style="font-size: 0.7rem;">Per paid booking</span>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="stat-card-icon bg-danger-subtle text-danger mb-2 d-flex align-items-center justify-content-center rounded-circle" style="width:40px; height:40px; font-size:18px;">
                <i class="bi bi-percent"></i>
            </div>
            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size:0.72rem;">30d Conversion</div>
            <h3 class="fw-800 mb-0 text-dark kpi-number" id="kpiConversion">{{ $stats['conversion_rate'] ?? 0 }}%</h3>
            <span class="text-muted small" style="font-size: 0.7rem;">Visitors to bookings</span>
        </div>
    </div>
</div>

<!-- Real-Time Customer Communications & Leads Hub Summary -->
<div class="card card-modern border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
    <div class="row g-3 align-items-center">
        <div class="col-lg-3 col-sm-6 border-end-lg">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; font-size: 18px;">
                    <i class="bi bi-envelope-exclamation-fill"></i>
                </div>
                <div>
                    <div class="text-muted extra-small text-uppercase fw-bold">Action Needed Inquiries</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fw-800 text-danger fs-5 kpi-number" id="kpiNewInquiries">{{ number_format($stats['new_inquiries'] ?? 0) }}</span>
                        <a href="{{ route('admin.inquiries.index') }}" class="small text-decoration-none fw-semibold">View Inquiries <i class="bi bi-chevron-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 border-end-lg">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; font-size: 18px;">
                    <i class="bi bi-whatsapp"></i>
                </div>
                <div>
                    <div class="text-muted extra-small text-uppercase fw-bold">WhatsApp Leads Captured</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fw-800 text-success fs-5 kpi-number" id="kpiWhatsappLeads">{{ number_format($stats['whatsapp_leads'] ?? 0) }}</span>
                        <a href="{{ route('admin.whatsapp.leads') }}" class="small text-decoration-none fw-semibold">View Leads <i class="bi bi-chevron-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 border-end-lg">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; font-size: 18px;">
                    <i class="bi bi-inbox-fill"></i>
                </div>
                <div>
                    <div class="text-muted extra-small text-uppercase fw-bold">Total Contact Messages</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fw-800 text-dark fs-5 kpi-number" id="kpiTotalInquiries">{{ number_format($stats['total_inquiries'] ?? 0) }}</span>
                        <span class="text-muted extra-small">Website submissions</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; font-size: 18px;">
                    <i class="bi bi-file-earmark-diff-fill"></i>
                </div>
                <div>
                    <div class="text-muted extra-small text-uppercase fw-bold">Incomplete Drafts</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fw-800 text-muted fs-5 kpi-number" id="kpiDrafts">{{ number_format($stats['drafts'] ?? 0) }}</span>
                        <span class="text-muted extra-small">Unfinished checkouts</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Grossing Tours & Quick Action Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card card-modern h-100 bg-white border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-800 text-dark mb-1"><i class="bi bi-trophy-fill text-warning me-2"></i>Top Performing Tours</h6>
                    <span class="text-muted small">Top grossing experiences by revenue & booking volume</span>
                </div>
                <a href="{{ route('admin.tours.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 fw-bold small">Manage Inventory</a>
            </div>
            <div style="height: 220px;">
                <canvas id="topToursChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <!-- Quick Payment Link Card -->
        <div class="card card-modern border-0 shadow-sm bg-primary text-white rounded-4 overflow-hidden h-100">
            <div class="card-header border-bottom border-white border-opacity-10 bg-transparent py-3 ps-4 pe-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box bg-white text-primary rounded-circle d-flex align-items-center justify-content-center mb-0" style="width: 36px; height: 36px; font-size: 16px;">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-800 mb-0 text-white">Instant Payment Link</h6>
                        <div class="small text-white opacity-75" style="font-size: 0.75rem;">Generate Ziina checkout link on the fly</div>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <form id="quickPaymentForm">
                    @csrf
                    <div class="mb-2">
                        <input type="text" name="name" class="form-control form-control-sm border-0" placeholder="Customer Full Name" required style="border-radius:8px;">
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <input type="email" name="email" class="form-control form-control-sm border-0" placeholder="Email" required style="border-radius:8px;">
                        </div>
                        <div class="col-6">
                            <input type="tel" name="phone" class="form-control form-control-sm border-0" placeholder="Phone (+971...)" required style="border-radius:8px;">
                        </div>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="description" class="form-control form-control-sm border-0" placeholder="Activity / Service Description" required style="border-radius:8px;">
                    </div>
                    <div class="mb-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-0 text-dark fw-bold" style="border-radius:8px 0 0 8px;">AED</span>
                            <input type="number" name="amount" step="0.01" class="form-control border-0" placeholder="0.00" required style="border-radius:0 8px 8px 0;">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark w-100 fw-bold rounded-pill py-2 shadow-sm border-0">Generate & Share Link <i class="bi bi-arrow-right ms-1"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Email Marketing & Audience Performance Card -->
<div class="card card-modern border-0 shadow-sm rounded-4 bg-white mb-4 p-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-box bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; font-size: 22px;">
                <i class="bi bi-megaphone-fill"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h6 class="fw-800 text-dark mb-0">Email Marketing & Audience Hub</h6>
                    <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-0.5 small fw-bold">Live</span>
                </div>
                <div class="text-muted small">Manage subscriber lists, automated campaigns, and deliverability performance.</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('admin.campaigns.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-sm d-flex align-items-center gap-1.5">
                <i class="bi bi-send-fill"></i> Create Campaign
            </a>
            <a href="{{ route('admin.subscribers.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold d-flex align-items-center gap-1.5">
                <i class="bi bi-people-fill"></i> Audience Lists
            </a>
            <a href="{{ route('admin.email-templates.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold d-flex align-items-center gap-1.5">
                <i class="bi bi-palette-fill"></i> Templates
            </a>
            <a href="{{ route('admin.settings.mail') }}" class="btn btn-light border btn-sm rounded-pill px-3 fw-bold text-muted" title="Mailer Settings">
                <i class="bi bi-gear-fill"></i> SMTP
            </a>
        </div>
    </div>
    <div class="row g-3 mt-2 pt-2 border-top">
        <div class="col-6 col-md-3">
            <div class="p-2 rounded-3 bg-light d-flex align-items-center gap-2.5">
                <i class="bi bi-people text-primary fs-4 ms-1"></i>
                <div>
                    <div class="text-muted extra-small text-uppercase fw-bold">Active Subscribers</div>
                    <div class="fw-800 text-dark fs-6">{{ number_format($stats['subscribers_count'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-2 rounded-3 bg-light d-flex align-items-center gap-2.5">
                <i class="bi bi-broadcast text-warning fs-4 ms-1"></i>
                <div>
                    <div class="text-muted extra-small text-uppercase fw-bold">Total Campaigns</div>
                    <div class="fw-800 text-dark fs-6">{{ number_format($stats['campaigns_count'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-2 rounded-3 bg-light d-flex align-items-center gap-2.5">
                <i class="bi bi-check2-circle text-success fs-4 ms-1"></i>
                <div>
                    <div class="text-muted extra-small text-uppercase fw-bold">Dispatched Broadcasts</div>
                    <div class="fw-800 text-success fs-6">{{ number_format($stats['campaigns_sent'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-2 rounded-3 bg-light d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted extra-small text-uppercase fw-bold">Campaign Delivery</div>
                    <div class="text-success fw-bold small"><i class="bi bi-shield-check me-1"></i> Ready & Armed</div>
                </div>
                <a href="{{ route('admin.campaigns.index') }}" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-1 text-decoration-none extra-small fw-bold">
                    View Hub <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Bookings Table -->
<div class="card card-modern border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4 p-3">
    <div class="card-header bg-white py-2 border-0 d-flex justify-content-between align-items-center ps-2 pe-2 mb-2">
        <div>
            <h6 class="fw-800 mb-0 text-dark"><i class="bi bi-activity text-primary me-2"></i>Recent Booking Inquiries & Reservations</h6>
            <span class="text-muted small">Latest reservations processed by the system</span>
        </div>
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm">View All Bookings</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover datatable" id="recentBookingsTable">
                <thead class="table-light small text-uppercase fw-bold text-muted">
                    <tr>
                        <th class="ps-4">Booking Ref</th>
                        <th>Customer</th>
                        <th>Tour / Activity</th>
                        <th>Total</th>
                        <th class="text-center">Status</th>
                        <th class="pe-4 text-end no-sort">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentBookings as $b)
                    <tr>
                        <td class="ps-4">
                            <a href="{{ route('admin.bookings.show', $b->id) }}" class="fw-800 text-decoration-none text-dark">#{{ $b->reference }}</a>
                            <div class="text-muted small" style="font-size: 0.72rem;">{{ $b->created_at ? $b->created_at->format('M j, g:ia') : '' }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $b->name }}</div>
                            <div class="text-muted small font-monospace">{{ $b->phone }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $b->tour_name }}</div>
                            <div class="text-muted small">{{ $b->tour_date ? $b->tour_date->format('M j, Y') : 'Open Date' }}</div>
                        </td>
                        <td class="fw-800 text-primary">AED {{ number_format($b->total) }}</td>
                        <td class="text-center">
                            @php
                                $badgeColor = [
                                    'pending' => 'warning',
                                    'confirmed' => 'success',
                                    'completed' => 'info',
                                    'cancelled' => 'danger'
                                ][$b->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $badgeColor }} text-capitalize px-3 py-1 rounded-pill">{{ $b->status }}</span>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.bookings.show', $b->id) }}" class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="View Details">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                @php
                                    $waVal = preg_replace('/[^0-9]/', '', $b->phone);
                                    $waMsg = 'Hi ' . $b->name . '! This is Dunes Discovery regarding your booking #' . $b->reference;
                                @endphp
                                <a href="https://wa.me/{{ $waVal }}?text={{ urlencode($waMsg) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-success rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="WhatsApp Customer">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No recent bookings found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Top Tours Revenue Breakdown Chart
    const topToursCtx = document.getElementById('topToursChart');
    if (topToursCtx) {
        const topToursData = @json($topTours);
        const labels = topToursData.map(t => t.tour_name ? t.tour_name.substring(0, 24) + '...' : 'Tour');
        const revenues = topToursData.map(t => t.revenue || 0);

        new Chart(topToursCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (AED)',
                    data: revenues,
                    backgroundColor: '#F58F43',
                    borderRadius: 8,
                    barPercentage: 0.55
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'AED ' + Number(context.raw).toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { color: '#f5f5f5' }, beginAtZero: true },
                    y: { grid: { display: false }, ticks: { font: { size: 11, weight: 'bold' } } }
                }
            }
        });
    }

    // Quick Payment Form AJAX Handler
    $('#quickPaymentForm').on('submit', function(e) {
        e.preventDefault();
        showLoader();

        const formData = $(this).serialize();

        $.ajax({
            url: "{{ route('admin.quick-payment') }}",
            type: "POST",
            data: formData,
            success: function(data) {
                hideLoader();
                if(data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Link Created!',
                        text: data.message,
                        showCancelButton: true,
                        confirmButtonText: '<i class="bi bi-clipboard me-1"></i> Copy Link',
                        cancelButtonText: '<i class="bi bi-whatsapp me-1"></i> WhatsApp Customer',
                        confirmButtonColor: '#F58F43',
                        cancelButtonColor: '#25D366'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            navigator.clipboard.writeText(data.payment.link).then(() => {
                                Swal.fire('Copied!', 'Payment link copied to clipboard.', 'success');
                            });
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            const waUrl = `https://wa.me/${data.payment.phone.replace(/[^0-9]/g, '')}?text=${encodeURIComponent('Hi ' + data.payment.name + ', here is your payment link from Dunes Discovery Tourism: ' + data.payment.link)}`;
                            window.open(waUrl, '_blank');
                        }
                    });
                    $('#quickPaymentForm')[0].reset();
                }
            },
            error: function(xhr) {
                hideLoader();
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: msg
                });
            }
        });
    });

    // Real-Time KPI Refresh with Shimmer Placeholders
    $('#btnRefreshDashboardKpis').on('click', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const $icon = $('#kpiSyncIcon');
        const $status = $('#kpiSyncStatus');

        $btn.prop('disabled', true);
        $icon.addClass('kpi-sync-spin');
        $status.html('<span class="text-primary fw-bold"><i class="bi bi-hourglass-split me-1"></i>Counting live from database...</span>');

        // Render shimmer loaders as placeholders on all counter cards
        $('.kpi-number').each(function() {
            $(this).data('cached-html', $(this).html());
            $(this).html('<span class="counter-shimmer"></span>');
        });

        $.ajax({
            url: "{{ route('admin.api.kpis') }}",
            type: "GET",
            dataType: "json",
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache'
            },
            success: function(res) {
                $icon.removeClass('kpi-sync-spin');
                $btn.prop('disabled', false);

                if (res && res.success && res.stats) {
                    const s = res.stats;
                    const nowTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });

                    $('#kpiTotalBookings').text(Number(s.total).toLocaleString());
                    $('#kpiConfirmedBookings').text(Number(s.confirmed_and_completed || (s.confirmed + s.completed)).toLocaleString());
                    $('#kpiConfirmedCount').text(Number(s.confirmed).toLocaleString());
                    $('#kpiCompletedCount').text(Number(s.completed).toLocaleString());
                    $('#kpiPendingBookings').text(Number(s.pending).toLocaleString());
                    $('#kpiRevenue').text('AED ' + Number(s.revenue).toLocaleString());
                    $('#kpiAov').text('AED ' + Number(s.aov).toLocaleString());
                    $('#kpiConversion').text(s.conversion_rate + '%');

                    $('#kpiNewInquiries').text(Number(s.new_inquiries).toLocaleString());
                    $('#kpiWhatsappLeads').text(Number(s.whatsapp_leads).toLocaleString());
                    $('#kpiTotalInquiries').text(Number(s.total_inquiries).toLocaleString());
                    $('#kpiDrafts').text(Number(s.drafts).toLocaleString());

                    $status.html(`Live as of <span class="fw-bold text-success">${nowTime}</span>`);

                    // Brief toast notification
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'All Metrics Recounted Live!'
                    });
                } else {
                    $('.kpi-number').each(function() {
                        $(this).html($(this).data('cached-html'));
                    });
                    $status.text('Recount failed.');
                }
            },
            error: function(xhr) {
                $icon.removeClass('kpi-sync-spin');
                $btn.prop('disabled', false);
                $('.kpi-number').each(function() {
                    $(this).html($(this).data('cached-html'));
                });
                $status.html('<span class="text-danger">Sync error</span>');
            }
        });
    });
});
</script>
@endpush
@endsection
