@extends('layouts.admin')

@section('page_title', 'Executive Dashboard')

@section('content')
<!-- 6 KPI Metric Cards Row -->
<div class="row g-3 g-lg-4 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="stat-card-icon bg-primary-subtle text-primary mb-2 d-flex align-items-center justify-content-center rounded-circle" style="width:40px; height:40px; font-size:18px;">
                <i class="bi bi-calendar-event-fill"></i>
            </div>
            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size:0.72rem;">Total Bookings</div>
            <h3 class="fw-800 mb-0 text-dark">{{ number_format($stats['total']) }}</h3>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="stat-card-icon bg-success-subtle text-success mb-2 d-flex align-items-center justify-content-center rounded-circle" style="width:40px; height:40px; font-size:18px;">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size:0.72rem;">Confirmed</div>
            <h3 class="fw-800 mb-0 text-dark">{{ number_format($stats['confirmed']) }}</h3>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="stat-card-icon bg-warning-subtle text-warning mb-2 d-flex align-items-center justify-content-center rounded-circle" style="width:40px; height:40px; font-size:18px;">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size:0.72rem;">Pending Inquiries</div>
            <h3 class="fw-800 mb-0 text-dark">{{ number_format($stats['pending']) }}</h3>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="stat-card-icon bg-info-subtle text-info mb-2 d-flex align-items-center justify-content-center rounded-circle" style="width:40px; height:40px; font-size:18px;">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size:0.72rem;">Collected Revenue</div>
            <h4 class="fw-800 mb-0 text-primary">AED {{ number_format($stats['revenue']) }}</h4>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="stat-card-icon bg-secondary-subtle text-dark mb-2 d-flex align-items-center justify-content-center rounded-circle" style="width:40px; height:40px; font-size:18px;">
                <i class="bi bi-bag-check-fill"></i>
            </div>
            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size:0.72rem;">Avg Order Value</div>
            <h4 class="fw-800 mb-0 text-dark">AED {{ number_format($stats['aov'] ?? 0) }}</h4>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="stat-card-icon bg-danger-subtle text-danger mb-2 d-flex align-items-center justify-content-center rounded-circle" style="width:40px; height:40px; font-size:18px;">
                <i class="bi bi-percent"></i>
            </div>
            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size:0.72rem;">30d Conversion</div>
            <h3 class="fw-800 mb-0 text-dark">{{ $stats['conversion_rate'] ?? 0 }}%</h3>
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
});
</script>
@endpush
@endsection
