@extends('layouts.admin')

@section('page_title', 'Analytics & Traffic Intelligence')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h4 class="fw-800 text-dark mb-1">Traffic & Acquisition Intelligence</h4>
        <p class="text-muted small mb-0">Real-time visitor telemetry, traffic channels, referring websites, and UTM campaign attribution.</p>
    </div>
    
    <div class="d-flex align-items-center gap-2">
        <form method="GET" action="{{ route('admin.analytics.index') }}" class="d-flex align-items-center gap-2">
            <label for="days" class="small fw-bold text-dark text-nowrap">Timeframe:</label>
            <select name="days" id="days" class="form-select form-select-sm rounded-pill border shadow-sm px-3 fw-bold" onchange="this.form.submit()" style="width: 150px;">
                <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 Days</option>
                <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 Days</option>
                <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 Days</option>
            </select>
        </form>
    </div>
</div>

<!-- 4 Key Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-muted small fw-bold text-uppercase">Total Pageviews</span>
                <span class="badge bg-primary-subtle text-primary rounded-circle p-2"><i class="bi bi-eye-fill fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-dark mb-1">{{ number_format($totalPageviews) }}</h3>
            <span class="text-muted small">Total HTTP requests</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-muted small fw-bold text-uppercase">Human Pageviews</span>
                <span class="badge bg-success-subtle text-success rounded-circle p-2"><i class="bi bi-person-check-fill fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-dark mb-1">{{ number_format($humanPageviews) }}</h3>
            <span class="text-muted small">Excludes bots & automated crawlers</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-muted small fw-bold text-uppercase">Unique Sessions</span>
                <span class="badge bg-info-subtle text-info rounded-circle p-2"><i class="bi bi-people-fill fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-dark mb-1">{{ number_format($uniqueVisitors) }}</h3>
            <span class="text-muted small">Distinct user sessions</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-muted small fw-bold text-uppercase">Unique Human IPs</span>
                <span class="badge bg-warning-subtle text-warning rounded-circle p-2"><i class="bi bi-globe fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-dark mb-1">{{ number_format($uniqueIPs) }}</h3>
            <span class="text-muted small">Distinct IP addresses</span>
        </div>
    </div>
</div>

<!-- Visual Analytics Charts Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-800 text-dark mb-1"><i class="bi bi-graph-up text-primary me-2"></i>Daily Traffic Trend</h6>
                    <span class="text-muted small">Pageviews & Human Visits progression over the last {{ $days }} days</span>
                </div>
                <span class="badge bg-light text-dark border px-3 py-1 rounded-pill small">Daily Telemetry</span>
            </div>
            <div style="height: 280px;">
                <canvas id="trafficTrendChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-800 text-dark mb-0"><i class="bi bi-pie-chart-fill text-warning me-2"></i>Traffic Sources</h6>
                <span class="badge bg-light text-muted border">Channels</span>
            </div>
            <div style="height: 220px; position: relative;">
                <canvas id="trafficSourcesChart"></canvas>
            </div>
            <div class="mt-3 small text-muted text-center">
                Attribution grouped by Google Ads, Organic Search, Social, WhatsApp & Direct.
            </div>
        </div>
    </div>
</div>

<!-- Traffic Acquisition & Referrers Row -->
<div class="row g-4 mb-4">
    <!-- Traffic Sources Breakdown -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden h-100">
            <div class="card-header bg-white py-3 border-bottom ps-4 pe-4 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-800 mb-0 text-dark"><i class="bi bi-compass text-primary me-2"></i>Acquisition Channels & Traffic Sources</h6>
                    <span class="text-muted small">Origin channels of visiting traffic</span>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3">{{ $trafficSources->count() }} Channels</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 no-datatable">
                        <thead class="table-light small text-uppercase fw-bold text-muted">
                            <tr>
                                <th class="ps-4">Channel / Source</th>
                                <th class="text-center">Pageviews</th>
                                <th class="text-center">Unique Visitors</th>
                                <th class="text-end pe-4">Traffic Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalChannelViews = $trafficSources->sum('views') ?: 1; @endphp
                            @forelse($trafficSources as $src)
                            @php
                                $percent = round(($src->views / $totalChannelViews) * 100, 1);
                                $iconClass = match($src->channel) {
                                    'Google Ads' => 'bi-google text-danger',
                                    'Google Organic' => 'bi-search text-success',
                                    'Facebook' => 'bi-facebook text-primary',
                                    'Instagram' => 'bi-instagram text-danger',
                                    'WhatsApp' => 'bi-whatsapp text-success',
                                    'Bing Organic' => 'bi-browser-edge text-info',
                                    'TikTok' => 'bi-tiktok text-dark',
                                    'Direct / Bookmark' => 'bi-bookmark-star-fill text-warning',
                                    default => 'bi-box-arrow-up-right text-muted'
                                };
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi {{ $iconClass }} fs-5"></i>
                                        <strong class="text-dark">{{ $src->channel }}</strong>
                                    </div>
                                </td>
                                <td class="text-center fw-bold text-dark">{{ number_format($src->views) }}</td>
                                <td class="text-center text-muted fw-medium">{{ number_format($src->visitors) }}</td>
                                <td class="text-end pe-4">
                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px; max-width: 60px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percent }}%"></div>
                                        </div>
                                        <span class="small fw-bold text-muted" style="min-width: 42px;">{{ $percent }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No traffic channel telemetry available.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Referring Domains -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden h-100">
            <div class="card-header bg-white py-3 border-bottom ps-4 pe-4 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-800 mb-0 text-dark"><i class="bi bi-link-45deg text-success me-2"></i>Top Referring Websites & Referrers</h6>
                    <span class="text-muted small">External sites sending traffic to Dunes</span>
                </div>
                <span class="badge bg-light text-muted border">{{ $topReferrers->count() }} Referrers</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 no-datatable">
                        <thead class="table-light small text-uppercase fw-bold text-muted">
                            <tr>
                                <th class="ps-4">Referrer URL</th>
                                <th class="text-center">Views</th>
                                <th class="text-end pe-4">Unique Visitors</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topReferrers as $ref)
                            <tr>
                                <td class="ps-4">
                                    <a href="{{ $ref->referrer }}" target="_blank" rel="noopener noreferrer" class="text-primary fw-bold font-monospace small text-truncate d-inline-block" style="max-width: 280px;" title="{{ $ref->referrer }}">
                                        {{ $ref->referrer }} <i class="bi bi-box-arrow-up-right small ms-1"></i>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary rounded-pill px-3">{{ number_format($ref->views) }}</span>
                                </td>
                                <td class="text-end pe-4 fw-bold text-muted">
                                    {{ number_format($ref->visitors) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">No external referring URLs recorded for this timeframe.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- UTM Campaigns & Top Pages Row -->
<div class="row g-4 mb-4">
    <!-- UTM Campaigns Table -->
    @if($campaigns->count() > 0)
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom ps-4 pe-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-800 mb-0 text-dark"><i class="bi bi-megaphone-fill text-danger me-2"></i>Active UTM Marketing Campaigns</h6>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">{{ $campaigns->count() }} Campaigns</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 no-datatable">
                        <thead class="table-light small text-uppercase fw-bold text-muted">
                            <tr>
                                <th class="ps-4">Campaign Name</th>
                                <th>Source</th>
                                <th>Medium</th>
                                <th class="text-center">Pageviews</th>
                                <th class="text-end pe-4">Unique Visitors</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($campaigns as $camp)
                            <tr>
                                <td class="ps-4 fw-bold text-dark">{{ $camp->utm_campaign }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $camp->utm_source }}</span></td>
                                <td><span class="badge bg-light text-muted border">{{ $camp->utm_medium }}</span></td>
                                <td class="text-center fw-800 text-primary">{{ number_format($camp->views) }}</td>
                                <td class="text-end pe-4 fw-bold text-muted">{{ number_format($camp->visitors) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Top Pages Table -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden h-100">
            <div class="card-header bg-white py-3 border-bottom ps-4 pe-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-800 mb-0 text-dark"><i class="bi bi-file-earmark-text text-primary me-2"></i>Top Most Visited Pages</h6>
                <span class="badge bg-light text-muted border">Human Traffic</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 no-datatable">
                        <thead class="table-light small text-uppercase fw-bold text-muted">
                            <tr>
                                <th class="ps-4">Page URI</th>
                                <th class="text-center">Views</th>
                                <th class="text-end pe-4">Unique Visitors</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPages as $page)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-dark font-monospace small text-break">{{ $page->request_uri }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary rounded-pill px-3">{{ number_format($page->views) }}</span>
                                </td>
                                <td class="text-end pe-4 fw-bold text-muted">
                                    {{ number_format($page->visitors) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">No pageview data recorded for this timeframe.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Countries & Devices -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom ps-4 pe-4">
                <h6 class="fw-800 mb-0 text-dark"><i class="bi bi-globe-americas text-info me-2"></i>Top Visitor Geographies</h6>
            </div>
            <div class="card-body p-4">
                @forelse($topCountries as $c)
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <span class="fw-bold text-dark small"><i class="bi bi-geo-alt-fill text-danger me-2"></i>{{ $c->country }}</span>
                    <span class="badge bg-light text-dark border rounded-pill px-3">{{ number_format($c->count) }} views</span>
                </div>
                @empty
                <p class="text-muted small mb-0 text-center">No location telemetry available.</p>
                @endforelse
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom ps-4 pe-4">
                <h6 class="fw-800 mb-0 text-dark"><i class="bi bi-display text-primary me-2"></i>Device Breakdown</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-2 text-center">
                    @foreach($devices as $dev)
                    <div class="col-4">
                        <div class="p-3 bg-light rounded-3 border">
                            <i class="bi {{ $dev->device_type === 'Mobile' ? 'bi-phone text-success' : ($dev->device_type === 'Tablet' ? 'bi-tablet text-warning' : 'bi-laptop text-primary') }} fs-4 d-block mb-1"></i>
                            <strong class="d-block text-dark small">{{ $dev->device_type ?: 'Desktop' }}</strong>
                            <span class="text-muted small">{{ number_format($dev->count) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Request Logs Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom ps-4 pe-4 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-800 mb-0 text-dark"><i class="bi bi-activity text-danger me-2"></i>Live Request Logs Telemetry</h6>
            <span class="text-muted small">Real-time HTTP requests captured by Dunes VisitorTracker engine.</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 no-datatable">
                <thead class="table-light small text-uppercase fw-bold text-muted">
                    <tr>
                        <th class="ps-4">Timestamp</th>
                        <th>IP & Location</th>
                        <th>Request URI</th>
                        <th>Device / OS / Browser</th>
                        <th class="text-end pe-4">Indicator</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="ps-4 text-nowrap">
                            <span class="fw-bold text-dark small d-block">{{ \Carbon\Carbon::parse($log->request_timestamp)->format('M d, Y') }}</span>
                            <span class="text-muted small">{{ \Carbon\Carbon::parse($log->request_timestamp)->format('H:i:s') }}</span>
                        </td>
                        <td>
                            <strong class="text-dark font-monospace small d-block">{{ $log->client_ip }}</strong>
                            <span class="text-muted small">{{ ($log->city ?: 'Unknown') . ', ' . ($log->country ?: '') }}</span>
                        </td>
                        <td class="font-monospace small text-primary text-break">
                            {{ $log->request_uri }}
                        </td>
                        <td class="small text-muted">
                            <span class="badge bg-light text-dark border me-1">{{ $log->device_type ?: 'Desktop' }}</span>
                            {{ $log->os_name }} / {{ $log->browser_name }}
                        </td>
                        <td class="text-end pe-4">
                            @if($log->bot_indicator === 'Likely Human')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Human</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3">Bot/System</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No request logs found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-top p-3 ps-4 pe-4">
        {{ $logs->appends(['days' => $days])->links('pagination::bootstrap-5') }}
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // 1. Daily Traffic Trend Chart
    const trendCtx = document.getElementById('trafficTrendChart');
    if (trendCtx) {
        const trendData = @json($dailyTrend);
        const labels = trendData.map(d => d.date);
        const totalViews = trendData.map(d => d.total_views);
        const humanViews = trendData.map(d => d.human_views);
        const humanSessions = trendData.map(d => d.human_sessions);

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Human Pageviews',
                        data: humanViews,
                        borderColor: '#F58F43',
                        backgroundColor: 'rgba(245, 143, 67, 0.15)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2.5,
                        pointRadius: 3
                    },
                    {
                        label: 'Unique Sessions',
                        data: humanSessions,
                        borderColor: '#20c997',
                        backgroundColor: 'transparent',
                        borderDash: [5, 5],
                        borderWidth: 2,
                        tension: 0.35,
                        pointRadius: 2
                    },
                    {
                        label: 'Total Requests',
                        data: totalViews,
                        borderColor: '#adb5bd',
                        backgroundColor: 'transparent',
                        borderWidth: 1.5,
                        tension: 0.35,
                        pointRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11, weight: 'bold' } } },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { grid: { color: '#f0f0f0' }, beginAtZero: true }
                }
            }
        });
    }

    // 2. Traffic Sources Donut Chart
    const sourcesCtx = document.getElementById('trafficSourcesChart');
    if (sourcesCtx) {
        const sourcesData = @json($trafficSources);
        const sourceLabels = sourcesData.map(s => s.channel);
        const sourceViews = sourcesData.map(s => s.views);

        const colors = ['#F58F43', '#198754', '#0d6efd', '#d63384', '#20c997', '#ffc107', '#6c757d', '#0dcaf0'];

        new Chart(sourcesCtx, {
            type: 'doughnut',
            data: {
                labels: sourceLabels,
                datasets: [{
                    data: sourceViews,
                    backgroundColor: colors.slice(0, sourceLabels.length),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
                },
                cutout: '65%'
            }
        });
    }
});
</script>
@endpush
@endsection

