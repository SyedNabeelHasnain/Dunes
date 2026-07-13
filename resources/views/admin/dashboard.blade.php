@extends('layouts.admin')

@section('page_title', 'Dashboard')

@section('content')
<!-- KPI Cards -->
<div class="row g-3 g-lg-4 mb-4 row-scrollable-mobile">
    <div class="col-6 col-lg-3">
        <div class="card-modern h-100 p-3 bg-white border shadow-sm rounded-4">
            <div class="stat-card-icon bg-primary-subtle text-primary mb-3 d-flex align-items-center justify-content-center rounded-circle" style="width:45px; height:45px; font-size:20px;">
                <i class="bi bi-calendar-event"></i>
            </div>
            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size:0.75rem;">Total Bookings</div>
            <h3 class="fw-800 mb-0 text-dark">{{ number_format($stats['total']) }}</h3>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card-modern h-100 p-3 bg-white border shadow-sm rounded-4">
            <div class="stat-card-icon bg-warning-subtle text-warning mb-3 d-flex align-items-center justify-content-center rounded-circle" style="width:45px; height:45px; font-size:20px;">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size:0.75rem;">Pending</div>
            <h3 class="fw-800 mb-0 text-dark">{{ number_format($stats['pending']) }}</h3>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card-modern h-100 p-3 bg-white border shadow-sm rounded-4">
            <div class="stat-card-icon bg-info-subtle text-info mb-3 d-flex align-items-center justify-content-center rounded-circle" style="width:45px; height:45px; font-size:20px;">
                <i class="bi bi-chat-left-text"></i>
            </div>
            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size:0.75rem;">Top Tours Count</div>
            <h3 class="fw-800 mb-0 text-dark">{{ number_format($stats['confirmed']) }}</h3>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card-modern h-100 p-3 bg-white border shadow-sm rounded-4">
            <div class="stat-card-icon bg-success-subtle text-success mb-3 d-flex align-items-center justify-content-center rounded-circle" style="width:45px; height:45px; font-size:20px;">
                <i class="bi bi-wallet2"></i>
            </div>
            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size:0.75rem;">Revenue</div>
            <h3 class="fw-800 mb-0 text-dark">AED {{ number_format($stats['revenue']) }}</h3>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Activity Table -->
    <div class="col-xl-8">
        <div class="card card-modern h-100 bg-white border shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center ps-4 pe-4">
                <h6 class="fw-800 mb-0 text-uppercase small text-muted">Recent Activity</h6>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-light btn-sm rounded-pill px-3 fw-bold small text-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 no-datatable table-hover">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Booking Ref</th>
                                <th>Customer</th>
                                <th>Tour / Activity</th>
                                <th>Total</th>
                                <th class="pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentBookings as $b)
                            <tr>
                                <td class="ps-4">
                                    <a href="{{ route('admin.bookings.show', $b->id) }}" class="fw-800 text-decoration-none text-dark">#{{ $b->reference }}</a>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $b->name }}</div>
                                    <div class="text-muted small">{{ $b->email }}</div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark">{{ $b->tour_name }}</div>
                                    <div class="text-muted small">{{ $b->tour_date ? $b->tour_date->format('M j, Y') : '' }}</div>
                                </td>
                                <td class="fw-800 text-primary">AED {{ number_format($b->total) }}</td>
                                <td class="pe-4">
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
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No recent bookings found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar actions & Widgets -->
    <div class="col-xl-4">
        <!-- Quick Payment Link Card -->
        <div class="card card-modern border-0 shadow-sm mb-4 bg-primary text-white rounded-4 overflow-hidden">
            <div class="card-header border-bottom border-white border-opacity-10 bg-transparent py-3 ps-4 pe-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box bg-white text-primary rounded-circle d-flex align-items-center justify-content-center mb-0" style="width: 40px; height: 40px; font-size: 18px;">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-800 mb-0">Quick Payment Request</h6>
                        <div class="small text-dark opacity-75" style="font-size: 0.8rem;">Send a custom payment link instantly</div>
                    </div>
                </div>
            </div>
            <div class="card-body ps-4 pe-4">
                <form id="quickPaymentForm">
                    @csrf
                    <div class="mb-2">
                        <input type="text" name="name" class="form-control border-0" placeholder="Customer Name" required style="height:45px; border-radius:10px;">
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <input type="email" name="email" class="form-control border-0" placeholder="Email" required style="height:45px; border-radius:10px;">
                        </div>
                        <div class="col-6">
                            <input type="tel" name="phone" class="form-control border-0" placeholder="Phone" required style="height:45px; border-radius:10px;">
                        </div>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="description" class="form-control border-0" placeholder="Description (e.g. Private Safari)" required style="height:45px; border-radius:10px;">
                    </div>
                    <div class="mb-0">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-0 text-dark fw-bold" style="border-radius:10px 0 0 10px;">AED</span>
                            <input type="number" name="amount" step="0.01" class="form-control border-0" placeholder="0.00" required style="height:45px; border-radius:0 10px 10px 0;">
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer border-white border-opacity-10 bg-transparent p-3 ps-4 pe-4">
                <button type="submit" form="quickPaymentForm" class="btn btn-desert-animated w-100 fw-bold rounded-pill py-2 shadow-sm border-0">Create & Send Link <i class="bi bi-arrow-right ms-2"></i></button>
            </div>
        </div>

        <!-- Quick management buttons -->
        <div class="card card-modern mb-4 bg-white border shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 ps-4 pe-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center mb-0" style="width: 40px; height: 40px; font-size: 18px;">
                        <i class="bi bi-grid-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-800 mb-0 text-dark">Quick Management</h6>
                        <div class="small text-muted" style="font-size: 0.8rem;">Manage common tasks efficiently</div>
                    </div>
                </div>
            </div>
            <div class="card-body p-3 ps-4 pe-4">
                <div class="d-grid gap-3">
                    <a href="{{ route('admin.tours.create') }}" class="btn btn-desert-animated text-start px-4 py-3 rounded-pill d-flex align-items-center gap-3 border-0">
                        <i class="bi bi-plus-circle-fill fs-5"></i>
                        <span class="fw-bold">Create New Tour</span>
                    </a>
                    <a href="{{ route('admin.pricing.index') }}" class="btn btn-desert-animated text-start px-4 py-3 rounded-pill d-flex align-items-center gap-3 border-0">
                        <i class="bi bi-currency-exchange fs-5"></i>
                        <span class="fw-bold">Update Pricing</span>
                    </a>
                    <a href="{{ route('admin.faqs.create') }}" class="btn btn-desert-animated text-start px-4 py-3 rounded-pill d-flex align-items-center gap-3 border-0">
                        <i class="bi bi-question-diamond-fill fs-5"></i>
                        <span class="fw-bold">Manage FAQs</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Support widget -->
        <div class="card card-modern border-0 shadow-sm bg-primary text-white rounded-4 overflow-hidden mb-4">
            <div class="card-header border-bottom border-white border-opacity-10 bg-transparent py-3 ps-4 pe-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box bg-white text-primary rounded-circle d-flex align-items-center justify-content-center mb-0" style="width: 40px; height: 40px; font-size: 18px;">
                        <i class="bi bi-headset"></i>
                    </div>
                    <div>
                        <h6 class="fw-800 mb-0">Need Support?</h6>
                        <div class="small text-white opacity-75" style="font-size: 0.8rem;">Get help with your CMS</div>
                    </div>
                </div>
            </div>
            <div class="card-body ps-4 pe-4">
                <p class="small opacity-75 mb-0" style="font-size: 0.85rem;">If you need help managing your CMS or adding new features, contact the developers.</p>
            </div>
            <div class="card-footer border-white border-opacity-10 bg-transparent p-3 ps-4 pe-4">
                <a href="mailto:syednabeeljavedzaidi@gmail.com" class="btn btn-desert-animated w-100 fw-bold rounded-pill py-2 shadow-sm border-0">Contact Support <i class="bi bi-arrow-right ms-2"></i></a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
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
                        title: 'Success!',
                        text: data.message,
                        showCancelButton: true,
                        confirmButtonText: 'Copy Link',
                        cancelButtonText: 'WhatsApp customer',
                        confirmButtonColor: '#F58F43',
                        cancelButtonColor: '#25D366'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            navigator.clipboard.writeText(data.payment.link).then(() => {
                                Swal.fire('Copied!', 'Payment link copied to clipboard.', 'success');
                            });
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            const waUrl = `https://wa.me/${data.payment.phone.replace(/[^0-9]/g, '')}?text=${encodeURIComponent('Hi ' + data.payment.name + ', here is your custom payment link for ' + data.payment.notes + ': ' + data.payment.link)}`;
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
