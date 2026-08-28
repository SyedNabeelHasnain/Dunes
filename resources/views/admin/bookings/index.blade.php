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

<!-- Booking Summary Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase">Total Bookings</span>
                <span class="badge bg-primary-subtle text-primary rounded-circle p-2"><i class="bi bi-calendar-check-fill fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-dark mb-0">{{ number_format($stats['total'] ?? 0) }}</h3>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase">Confirmed / Paid</span>
                <span class="badge bg-success-subtle text-success rounded-circle p-2"><i class="bi bi-check-circle-fill fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-dark mb-0">{{ number_format($stats['confirmed'] ?? 0) }}</h3>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase">Pending Inquiries</span>
                <span class="badge bg-warning-subtle text-warning rounded-circle p-2"><i class="bi bi-clock-history fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-dark mb-0">{{ number_format($stats['pending'] ?? 0) }}</h3>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase">Collected Revenue</span>
                <span class="badge bg-info-subtle text-info rounded-circle p-2"><i class="bi bi-cash-stack fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-primary mb-0">AED {{ number_format($stats['revenue'] ?? 0) }}</h3>
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

<!-- Bookings Table -->
<div class="card card-modern border-0 shadow-sm rounded-4 overflow-hidden bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover no-datatable" id="bookingsTable">
                <thead class="table-light small text-uppercase fw-bold text-muted">
                    <tr>
                        <th class="ps-4">Ref & Time</th>
                        <th>Customer</th>
                        <th>Tour / Activity</th>
                        <th>Tour Date</th>
                        <th>Total & Paid</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $b)
                    <tr>
                        <td class="ps-4">
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
                            <div class="badge bg-light text-primary border rounded-pill mt-1" style="font-size: 0.72rem;">
                                {{ $b->tier_name ?: ($b->tier ? $b->tier->display_name : 'Standard') }}
                            </div>
                            <span class="small text-muted ms-1">({{ $b->adults }} Adults, {{ $b->children }} Child)</span>
                        </td>
                        <td>
                            <div class="small fw-bold text-dark">{{ $b->tour_date ? $b->tour_date->format('M j, Y') : 'Open Date' }}</div>
                            @if($b->pickup_time)
                                <div class="text-muted small" style="font-size: 0.72rem;"><i class="bi bi-clock me-1"></i>{{ $b->pickup_time }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-800 text-primary">AED {{ number_format($b->total) }}</div>
                            <div class="small text-muted" style="font-size: 0.72rem;">
                                Paid: <strong class="text-success">AED {{ number_format($b->payment_amount) }}</strong> | Due: AED {{ number_format($b->balance_due) }}
                            </div>
                        </td>
                        <td>
                            @php
                                $badgeColor = [
                                    'pending' => 'warning',
                                    'confirmed' => 'success',
                                    'completed' => 'info',
                                    'cancelled' => 'danger'
                                ][$b->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $badgeColor }} text-capitalize px-3 py-1 rounded-pill">{{ $b->status }}</span>
                            <div class="small mt-1 text-capitalize text-muted" style="font-size: 0.7rem;">
                                Payment: <span class="fw-bold text-dark">{{ $b->payment_status }}</span>
                            </div>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.bookings.show', $b->id) }}" class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="View Booking Details">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                @php
                                    $waVal = preg_replace('/[^0-9]/', '', $b->phone);
                                    $waMsg = 'Hi ' . $b->name . '! This is Dunes Discovery regarding your booking #' . $b->reference;
                                @endphp
                                <a href="https://wa.me/{{ $waVal }}?text={{ urlencode($waMsg) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-success rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="WhatsApp Customer">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
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

<div class="mt-4">
    {{ $bookings->links('pagination::bootstrap-5') }}
</div>
@endsection
