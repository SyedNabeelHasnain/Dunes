@extends('layouts.admin')

@section('page_title', 'Analytics & Visitor Intelligence')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h4 class="fw-800 text-dark mb-1">Traffic & Visitor Analytics</h4>
        <p class="text-muted small mb-0">Real-time visitor telemetry, pageviews, and request logs captured by Dunes Engine.</p>
    </div>
    
    <div class="d-flex align-items-center gap-2">
        <form method="GET" action="{{ route('admin.analytics.index') }}" class="d-flex align-items-center gap-2">
            <label for="days" class="small fw-bold text-dark text-nowrap">Timeframe:</label>
            <select name="days" id="days" class="form-select form-select-sm rounded-pill border shadow-sm px-3" onchange="this.form.submit()" style="width: 140px;">
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
            <span class="text-muted small">Excludes bots & crawlers</span>
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

<div class="row g-4 mb-4">
    <!-- Top Pages Table -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden h-100">
            <div class="card-header bg-white py-3 border-bottom ps-4 pe-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-800 mb-0 text-dark">Top Most Visited Pages</h6>
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
                <h6 class="fw-800 mb-0 text-dark">Top Visitor Geographies</h6>
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
                <h6 class="fw-800 mb-0 text-dark">Device Breakdown</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-2 text-center">
                    @foreach($devices as $dev)
                    <div class="col-4">
                        <div class="p-3 bg-light rounded-3 border">
                            <i class="bi {{ $dev->device_type === 'Mobile' ? 'bi-phone' : ($dev->device_type === 'Tablet' ? 'bi-tablet' : 'bi-laptop') }} fs-4 text-primary d-block mb-1"></i>
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
            <h6 class="fw-800 mb-0 text-dark">Live Request Logs Telemetry</h6>
            <span class="text-muted small">Real-time HTTP requests captured by VisitorTracker middleware.</span>
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
@endsection
