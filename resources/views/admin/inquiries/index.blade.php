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
                        <th class="ps-4">Date</th>
                        <th>Customer</th>
                        <th>Subject</th>
                        <th class="text-center">Status</th>
                        <th class="pe-4 text-end no-sort">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inquiries as $c)
                    <tr>
                        <td class="ps-4">
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

                                <form action="{{ route('admin.inquiries.destroy', $c->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="Delete" onclick="return confirm('Delete this inquiry?')">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
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
});
</script>
@endpush
@endsection
