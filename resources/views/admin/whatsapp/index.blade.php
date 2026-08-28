@extends('layouts.admin')

@section('page_title', 'WhatsApp Leads & Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="h4 fw-800 text-dark mb-1">WhatsApp Leads Hub & Analytics</h2>
        <p class="text-muted small mb-0">Track real-time chat click leads, customer phone numbers, tour preferences, and visitor telemetry.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.whatsapp.export', request()->query()) }}" class="btn btn-outline-success rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-file-earmark-spreadsheet me-2"></i> Export CSV
        </a>
        <a href="{{ route('admin.whatsapp.settings') }}" class="btn btn-light border rounded-pill px-3 fw-bold shadow-sm">
            <i class="bi bi-gear-fill me-1 text-primary"></i> Settings
        </a>
    </div>
</div>

<!-- 4 Key Performance Metric Cards -->
<div class="row g-3 g-lg-4 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">Total WhatsApp Leads</span>
                <span class="badge bg-success-subtle text-success rounded-circle p-2"><i class="bi bi-whatsapp fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-dark mb-0">{{ number_format($stats['total'] ?? 0) }}</h3>
            <span class="text-muted small" style="font-size: 0.75rem;">All-time chat click inquiries</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">Today's Leads</span>
                <span class="badge bg-primary-subtle text-primary rounded-circle p-2"><i class="bi bi-calendar2-day-fill fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-dark mb-0">{{ number_format($stats['today'] ?? 0) }}</h3>
            <span class="text-success small fw-bold" style="font-size: 0.75rem;"><i class="bi bi-lightning-fill me-1"></i>Active today</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">This Month</span>
                <span class="badge bg-info-subtle text-info rounded-circle p-2"><i class="bi bi-graph-up fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-dark mb-0">{{ number_format($stats['this_month'] ?? 0) }}</h3>
            <span class="text-muted small" style="font-size: 0.75rem;">Current month acquisition</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">Mobile Traffic %</span>
                <span class="badge bg-warning-subtle text-warning rounded-circle p-2"><i class="bi bi-phone-fill fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-dark mb-0">{{ $stats['mobile_pct'] ?? 0 }}%</h3>
            <span class="text-muted small" style="font-size: 0.75rem;">Mobile vs desktop users</span>
        </div>
    </div>
</div>

<!-- 2 Interactive Analytics Charts -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card card-modern bg-white border-0 shadow-sm rounded-4 p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-800 text-dark mb-1"><i class="bi bi-graph-up-arrow text-success me-2"></i>14-Day WhatsApp Leads Trend</h6>
                    <span class="text-muted small">Daily volume of customer WhatsApp inquiries</span>
                </div>
            </div>
            <div style="height: 220px;">
                <canvas id="leadsTrendChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-modern bg-white border-0 shadow-sm rounded-4 p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-800 text-dark mb-1"><i class="bi bi-pie-chart-fill text-primary me-2"></i>Tour Interest</h6>
                    <span class="text-muted small">Top inquired experiences</span>
                </div>
            </div>
            <div style="height: 220px; position: relative;">
                <canvas id="tourBreakdownChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Multi-Parameter Filter Toolbar -->
<div class="card card-modern border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
    <form method="GET" action="{{ route('admin.whatsapp.leads') }}">
        <div class="row g-2 align-items-end">
            <div class="col-lg-3 col-md-6">
                <label for="leadSearch" class="form-label small fw-bold text-dark mb-1">Search Leads</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" id="leadSearch" class="form-control border-start-0" placeholder="Name, Phone, Tour..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <label for="tourFilter" class="form-label small fw-bold text-dark mb-1">Tour Package</label>
                <select name="tour_name" id="tourFilter" class="form-select">
                    <option value="">All Tours</option>
                    @foreach($availableTours as $tName)
                        <option value="{{ $tName }}" {{ request('tour_name') === $tName ? 'selected' : '' }}>{{ $tName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label for="deviceFilter" class="form-label small fw-bold text-dark mb-1">Device Type</label>
                <select name="device_type" id="deviceFilter" class="form-select">
                    <option value="">All Devices</option>
                    <option value="mobile" {{ request('device_type') === 'mobile' ? 'selected' : '' }}>Mobile</option>
                    <option value="desktop" {{ request('device_type') === 'desktop' ? 'selected' : '' }}>Desktop</option>
                    <option value="tablet" {{ request('device_type') === 'tablet' ? 'selected' : '' }}>Tablet</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label for="fromDate" class="form-label small fw-bold text-dark mb-1">From Date</label>
                <input type="date" name="from_date" id="fromDate" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-lg-2 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold" title="Apply Filter"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                <a href="{{ route('admin.whatsapp.leads') }}" class="btn btn-light border" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </div>
    </form>
</div>

<div class="card card-modern border-0 shadow-sm rounded-4 overflow-hidden bg-white p-3 mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover datatable" id="whatsappLeadsTable">
                <thead class="table-light small text-uppercase fw-bold text-muted">
                    <tr>
                        <th class="ps-4">Date & Time</th>
                        <th>Customer</th>
                        <th>Interest Context</th>
                        <th>Message Snippet</th>
                        <th>Location</th>
                        <th>Device</th>
                        <th class="pe-4 text-end no-sort">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                    <tr>
                        <td class="ps-4">
                            <div class="small fw-bold text-dark">
                                {{ \Carbon\Carbon::parse($lead->created_at)->format('M j, Y') }}
                            </div>
                            <div class="text-muted small" style="font-size: 0.72rem;">
                                {{ \Carbon\Carbon::parse($lead->created_at)->format('g:ia') }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $lead->name ?: 'Visitor' }}</div>
                            <div class="text-success small fw-bold font-monospace" style="font-size: 0.75rem;">
                                {{ $lead->phone }}
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fw-bold small">
                                {{ $lead->tour_name ?: 'General Inquiry' }}
                            </span>
                        </td>
                        <td class="text-muted small" style="max-width: 220px;">
                            {{ Str::limit($lead->message_text, 65) }}
                        </td>
                        <td>
                            <div class="small text-dark fw-semibold">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $lead->city ?: 'Unknown' }}, {{ $lead->country ?: '' }}
                            </div>
                        </td>
                        <td>
                            <div class="small text-muted text-capitalize">
                                <span class="badge bg-light text-dark border">{{ $lead->device_type ?: 'Desktop' }}</span>
                            </div>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <button class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center view-lead-btn" 
                                        style="width: 34px; height: 34px;" 
                                        title="View Full Lead Details"
                                        data-name="{{ $lead->name }}"
                                        data-phone="{{ $lead->phone }}"
                                        data-context="{{ $lead->tour_name ?: 'General Inquiry' }}"
                                        data-url="{{ $lead->page_url }}"
                                        data-msg="{{ $lead->message_text }}"
                                        data-ip="{{ $lead->client_ip ?? $lead->ip_address ?? 'Not Available' }}"
                                        data-location="{{ ($lead->city ?? 'Unknown') . ', ' . ($lead->country ?? '') }}"
                                        data-device="{{ ucfirst($lead->device_type ?? '-') }} ({{ $lead->os_name ?? '-' }} / {{ $lead->browser_name ?? '-' }})">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-whatsapp fs-1 d-block mb-2 text-muted opacity-50"></i>
                            No WhatsApp leads found matching your criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="leadDetailsModal" tabindex="-1" aria-labelledby="leadDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 shadow border-0 bg-white">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800 text-dark" id="leadDetailsModalLabel">WhatsApp Lead Telemetry & Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <!-- Client Details -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-4 h-100 border">
                            <h6 class="text-success fw-800 text-uppercase small mb-3"><i class="bi bi-person-fill me-1"></i> Customer Identity</h6>
                            <div class="mb-3">
                                <label class="text-muted small fw-bold d-block">Customer Name</label>
                                <span class="fw-bold text-dark fs-5" id="modalCustomerName">-</span>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small fw-bold d-block">Phone Number</label>
                                <strong class="fs-6 text-success font-monospace" id="modalCustomerPhone">-</strong>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small fw-bold d-block">Interest Context</label>
                                <span class="badge bg-primary text-white" id="modalTourName">-</span>
                            </div>
                            <div>
                                <label class="text-muted small fw-bold d-block">Source Page</label>
                                <a href="#" target="_blank" class="small text-primary text-decoration-none text-truncate d-block" id="modalPageUrl">-</a>
                            </div>
                        </div>
                    </div>

                    <!-- Telemetry -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-4 h-100 border">
                            <h6 class="text-primary fw-800 text-uppercase small mb-3"><i class="bi bi-geo-alt-fill me-1"></i> Visitor Telemetry</h6>
                            <div class="mb-3">
                                <label class="text-muted small fw-bold d-block">Client IP Address</label>
                                <strong class="text-dark font-monospace small" id="modalClientIp">-</strong>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small fw-bold d-block">Estimated Location</label>
                                <strong class="text-dark" id="modalLocation">-</strong>
                            </div>
                            <div>
                                <label class="text-muted small fw-bold d-block">Device Environment</label>
                                <strong class="text-dark small" id="modalDevice">-</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Message text -->
                    <div class="col-12">
                        <div class="p-3 bg-light rounded-4 border">
                            <label class="text-muted small fw-bold d-block mb-2">Prefilled Customer Message</label>
                            <p class="mb-0 text-dark small p-3 bg-white border rounded-3" id="modalMessageText" style="white-space: pre-wrap; line-height: 1.6;">-</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                <a href="#" id="modalDirectChatBtn" target="_blank" class="btn btn-success rounded-pill px-4 fw-bold">
                    <i class="bi bi-whatsapp me-1"></i> Open Chat on WhatsApp
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // 14-Day Acquisition Trend Chart
    const trendCtx = document.getElementById('leadsTrendChart');
    if (trendCtx) {
        const trendData = @json($trendData);
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.date),
                datasets: [{
                    label: 'WhatsApp Leads',
                    data: trendData.map(d => d.count),
                    borderColor: '#25D366',
                    backgroundColor: 'rgba(37, 211, 102, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#25D366',
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

    // Tour Breakdown Doughnut Chart
    const tourCtx = document.getElementById('tourBreakdownChart');
    if (tourCtx) {
        const tourData = @json($tourBreakdown);
        new Chart(tourCtx, {
            type: 'doughnut',
            data: {
                labels: tourData.map(t => t.tour_label.length > 20 ? t.tour_label.substring(0, 20) + '...' : t.tour_label),
                datasets: [{
                    data: tourData.map(t => t.count),
                    backgroundColor: ['#F58F43', '#25D366', '#3b82f6', '#8b5cf6', '#ec4899'],
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

    // View Details Modal Handler
    $('.view-lead-btn').on('click', function() {
        const btn = $(this);
        const name = btn.data('name') || 'Visitor';
        const phone = btn.data('phone') || 'No Phone';
        const context = btn.data('context') || 'General Inquiry';
        const url = btn.data('url') || '#';
        const msg = btn.data('msg') || 'No message text provided.';
        const ip = btn.data('ip') || '-';
        const loc = btn.data('location') || 'Unknown Location';
        const device = btn.data('device') || '-';

        $('#modalCustomerName').text(name);
        $('#modalCustomerPhone').text(phone);
        $('#modalTourName').text(context);
        $('#modalPageUrl').attr('href', url).text(url);
        $('#modalClientIp').text(ip);
        $('#modalLocation').text(loc);
        $('#modalDevice').text(device);
        $('#modalMessageText').text(msg);

        if (phone && phone !== 'No Phone') {
            const cleanPhone = phone.replace(/[^0-9]/g, '');
            const chatMsg = encodeURIComponent('Hi ' + name + '! Thanks for reaching out to Dunes Discovery Tourism regarding ' + context + '. How can we assist you?');
            $('#modalDirectChatBtn').attr('href', `https://wa.me/${cleanPhone}?text=${chatMsg}`).show();
        } else {
            $('#modalDirectChatBtn').hide();
        }

        $('#leadDetailsModal').modal('show');
    });
});
</script>
@endpush
@endsection
