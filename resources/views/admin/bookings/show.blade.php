@extends('layouts.admin')

@section('page_title', 'Booking #' . $booking->reference)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 fw-bold mb-2">
            <i class="bi bi-chevron-left me-1"></i> Back to List
        </a>
        <h2 class="h4 fw-800 text-dark mb-0">Booking #{{ $booking->reference }}</h2>
    </div>
    
    <div class="d-flex gap-2">
        @if($booking->status === 'pending')
            <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST" class="d-inline">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="confirmed">
                <input type="hidden" name="payment_status" value="{{ $booking->payment_status }}">
                <input type="hidden" name="balance_due" value="{{ $booking->balance_due }}">
                <button type="submit" class="btn btn-success rounded-pill px-4 py-2 fw-bold">Confirm Booking</button>
            </form>
            <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST" class="d-inline">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="cancelled">
                <input type="hidden" name="payment_status" value="{{ $booking->payment_status }}">
                <input type="hidden" name="balance_due" value="{{ $booking->balance_due }}">
                <button type="submit" class="btn btn-danger rounded-pill px-4 py-2 fw-bold">Cancel Booking</button>
            </form>
        @endif
        @if($booking->status === 'confirmed')
            <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST" class="d-inline">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="completed">
                <input type="hidden" name="payment_status" value="{{ $booking->payment_status }}">
                <input type="hidden" name="balance_due" value="{{ $booking->balance_due }}">
                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">Mark Completed</button>
            </form>
        @endif
    </div>
</div>

<!-- Booking Status & Settings Update Panel -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card card-modern border shadow-sm rounded-4 p-4 bg-white h-100">
            <h6 class="text-primary fw-800 text-uppercase small mb-4">Customer Details</h6>
            <div class="mb-3">
                <label class="text-muted small fw-bold">Full Name</label>
                <div class="fw-bold fs-5 text-dark">{{ $booking->name }}</div>
            </div>
            <div class="mb-3">
                <label class="text-muted small fw-bold">Contact Details</label>
                <div class="fw-medium text-dark">{{ $booking->email }}</div>
                <div class="fw-medium text-dark">{{ $booking->phone }}</div>
            </div>
            <div class="mb-3">
                <label class="text-muted small fw-bold">Pickup Location</label>
                <div class="fw-medium text-dark">{{ $booking->pickup_location }}</div>
            </div>
            @if($booking->special_requests)
            <div class="mb-0">
                <label class="text-muted small fw-bold">Special Notes</label>
                <div class="p-3 bg-light rounded-3 small border mt-1 text-dark">{{ $booking->special_requests }}</div>
            </div>
            @endif
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card card-modern border shadow-sm rounded-4 p-4 bg-white h-100">
            <h6 class="text-primary fw-800 text-uppercase small mb-4">Reservation Info</h6>
            <div class="mb-3">
                <label class="text-muted small fw-bold">Tour / Activity</label>
                <div class="fw-bold fs-5 text-dark">{{ $booking->tour_name }}</div>
                <div class="badge bg-light text-primary border rounded-pill mt-1">{{ $booking->tier_name }}</div>
            </div>
            <div class="mb-3">
                <label class="text-muted small fw-bold">Date & Guests</label>
                <div class="fw-medium text-dark"><i class="bi bi-calendar-event me-2"></i>{{ $booking->tour_date ? $booking->tour_date->format('l, M j, Y') : '' }}</div>
                <div class="fw-medium text-dark"><i class="bi bi-people me-2"></i>{{ $booking->adults }} Adults, {{ $booking->children }} Children</div>
            </div>
            <div class="mb-3">
                <label class="text-muted small fw-bold">Financial Summary</label>
                <div class="fw-800 fs-3 text-primary">AED {{ number_format($booking->total) }}</div>
            </div>
            <div class="mb-3">
                <label class="text-muted small fw-bold">Payment</label>
                <div class="fw-medium text-dark">Method: <span class="text-capitalize">{{ $booking->payment_method }}</span></div>
                <div class="fw-medium text-dark">Status: <span class="text-capitalize">{{ $booking->payment_status }}</span></div>
                <div class="fw-medium text-dark">Paid: AED {{ number_format($booking->payment_amount ?? 0) }}</div>
                <div class="fw-medium text-dark">Balance: AED {{ number_format($booking->balance_due ?? 0) }}</div>
            </div>
            @if($booking->addons->count() > 0)
            <div>
                <label class="text-muted small fw-bold mb-2 d-block">Selected Addons</label>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($booking->addons as $addon)
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-plus-lg text-success me-1"></i>{{ $addon->addon_name }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Detailed Settings Update Form (Payment status & Balance updates) -->
<div class="card card-modern border shadow-sm rounded-4 p-4 mb-4 bg-white">
    <h6 class="text-primary fw-800 text-uppercase small mb-4">Edit Booking Details</h6>
    <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-4">
                <label for="status" class="form-label fw-bold text-dark">Booking Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="completed" {{ $booking->status === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="payment_status" class="form-label fw-bold text-dark">Payment Status</label>
                <select name="payment_status" id="payment_status" class="form-select">
                    <option value="unpaid" {{ $booking->payment_status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="partial" {{ $booking->payment_status === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="paid" {{ $booking->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="failed" {{ $booking->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="cancelled" {{ $booking->payment_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="balance_due" class="form-label fw-bold text-dark">Balance Due (AED)</label>
                <input type="number" name="balance_due" id="balance_due" step="0.01" class="form-control" value="{{ $booking->balance_due }}">
            </div>
            <div class="col-md-6">
                <label for="pickup_location" class="form-label fw-bold text-dark">Pickup Location</label>
                <input type="text" name="pickup_location" id="pickup_location" class="form-control" value="{{ $booking->pickup_location }}">
            </div>
            <div class="col-md-6">
                <label for="special_requests" class="form-label fw-bold text-dark">Special Requests</label>
                <textarea name="special_requests" id="special_requests" class="form-control" rows="2">{{ $booking->special_requests }}</textarea>
            </div>
            <div class="col-12 mt-4">
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Update Details</button>
            </div>
        </div>
    </form>
</div>

<!-- Payments Link Management Panel -->
<div class="card card-modern border shadow-sm rounded-4 p-4 bg-white">
    <h6 class="text-primary fw-800 text-uppercase small mb-4">Payment Links Management</h6>
    
    <h6 class="fw-bold mb-3 text-dark">Payment Links History</h6>
    <div class="table-responsive mb-4">
        <table class="table table-sm table-hover align-middle border" id="paymentLinksTable">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Notes</th>
                    <th>Status</th>
                    <th>Link</th>
                    <th>Resend</th>
                </tr>
            </thead>
            <tbody>
                @if($booking->ziina_payment_intent_id && $booking->payments->where('payment_intent_id', $booking->ziina_payment_intent_id)->count() === 0)
                <tr>
                    <td>{{ $booking->created_at->format('Y-m-d H:i') }}</td>
                    <td>AED {{ number_format($booking->payment_amount) }}</td>
                    <td><span class="text-muted small italic">Initial Booking Link</span></td>
                    <td>
                        @php
                            $disp = ($booking->ziina_status === 'requires_payment_instrument' || $booking->ziina_status === 'pending') ? 'Pending' : ucfirst($booking->ziina_status);
                            $badge = ($disp === 'Pending') ? 'warning' : 'secondary';
                        @endphp
                        <span class="badge bg-{{ $badge }}">{{ $disp }}</span>
                    </td>
                    <td>
                        <a href="{{ $booking->ziina_redirect_url }}" target="_blank" class="small text-truncate d-inline-block text-primary" style="max-width:150px;">{{ $booking->ziina_redirect_url }}</a>
                    </td>
                    <td>
                        @if($booking->ziina_redirect_url && $booking->status !== 'completed' && $booking->ziina_status !== 'paid' && $booking->ziina_status !== 'completed')
                        <div class="btn-group btn-group-sm">
                            @php
                                $waPhone = preg_replace('/[^0-9]/', '', $booking->phone);
                                $waText = "Hello {$booking->name}, please use this link to complete your payment for booking #{$booking->reference}: {$booking->ziina_redirect_url}";
                            @endphp
                            <a href="https://wa.me/{{ $waPhone }}?text={{ urlencode($waText) }}" target="_blank" rel="noopener" class="btn btn-success" title="Resend via WhatsApp"><i class="bi bi-whatsapp"></i></a>
                            <button type="button" class="btn btn-secondary resend-email-btn" data-link="{{ $booking->ziina_redirect_url }}" data-amount="{{ $booking->payment_amount }}" title="Resend via Email"><i class="bi bi-envelope"></i></button>
                        </div>
                        @endif
                    </td>
                </tr>
                @endif
                
                @forelse($booking->payments as $p)
                <tr>
                    <td>{{ $p->created_at->format('Y-m-d H:i') }}</td>
                    <td>AED {{ number_format($p->amount) }}</td>
                    <td><span class="small text-dark">{{ $p->notes ?: '-' }}</span></td>
                    <td>
                        @php
                            $dispP = ($p->status === 'requires_payment_instrument' || $p->status === 'pending') ? 'Pending' : ucfirst($p->status);
                            $badgeP = ($dispP === 'Pending') ? 'warning' : 'secondary';
                        @endphp
                        <span class="badge bg-{{ $badgeP }}">{{ $dispP }}</span>
                    </td>
                    <td>
                        <a href="{{ $p->payment_url }}" target="_blank" class="small text-truncate d-inline-block text-primary" style="max-width:150px;">{{ $p->payment_url }}</a>
                    </td>
                    <td>
                        @if($p->payment_url && $booking->status !== 'completed' && $p->status !== 'paid' && $p->status !== 'completed')
                        <div class="btn-group btn-group-sm">
                            @php
                                $waPhone = preg_replace('/[^0-9]/', '', $booking->phone);
                                $waText = "Hello {$booking->name}, please use this link to complete your payment for booking #{$booking->reference}: {$p->payment_url}";
                            @endphp
                            <a href="https://wa.me/{{ $waPhone }}?text={{ urlencode($waText) }}" target="_blank" rel="noopener" class="btn btn-success" title="Resend via WhatsApp"><i class="bi bi-whatsapp"></i></a>
                            <button type="button" class="btn btn-secondary resend-email-btn" data-link="{{ $p->payment_url }}" data-amount="{{ $p->amount }}" title="Resend via Email"><i class="bi bi-envelope"></i></button>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                    @if(!$booking->ziina_payment_intent_id)
                    <tr>
                        <td colspan="6" class="text-center py-3 text-muted">No custom payment links created.</td>
                    </tr>
                    @endif
                @endforelse
            </tbody>
        </table>
    </div>

    @if($booking->status !== 'completed')
    <h6 class="fw-bold mb-3 text-dark">Create New Payment Link</h6>
    <form id="createPaymentLinkForm" class="row g-3 align-items-center">
        @csrf
        <div class="col-md-4">
            <div class="form-floating">
                <input type="number" name="amount" id="payment_amount" step="0.01" class="form-control" value="{{ $booking->balance_due > 0 ? $booking->balance_due : '' }}" required placeholder="0.00">
                <label for="payment_amount" class="text-dark">Amount (AED)</label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-floating">
                <input type="text" name="notes" id="payment_notes" class="form-control" placeholder="e.g. Balance Payment">
                <label for="payment_notes" class="text-dark">Notes (Optional)</label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="d-flex gap-2">
                <button type="submit" name="send_method" value="none" class="btn btn-primary rounded-pill fw-bold w-100"><i class="bi bi-link-45deg me-1"></i> Create Only</button>
                <button type="submit" name="send_method" value="whatsapp" class="btn btn-success rounded-pill fw-bold px-3" title="Create & Send WhatsApp"><i class="bi bi-whatsapp"></i></button>
                <button type="submit" name="send_method" value="email" class="btn btn-secondary rounded-pill fw-bold px-3" title="Create & Send Email"><i class="bi bi-envelope"></i></button>
            </div>
        </div>
    </form>
    @endif
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Create custom payment link
    $('#createPaymentLinkForm').on('submit', function(e) {
        e.preventDefault();
        
        // Determine button clicked
        const submitter = e.originalEvent.submitter;
        const sendMethod = submitter ? submitter.value : 'none';
        
        showLoader();
        
        const data = {
            _token: "{{ csrf_token() }}",
            amount: $('#payment_amount').val(),
            notes: $('#payment_notes').val(),
            send_method: sendMethod
        };
        
        $.ajax({
            url: "{{ route('admin.bookings.payment-link', $booking->id) }}",
            type: "POST",
            data: data,
            success: function(res) {
                hideLoader();
                if(res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Link Created',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        if(res.redirect_url) {
                            window.open(res.redirect_url, '_blank');
                        }
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                hideLoader();
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to create payment link.';
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    // Resend payment link email
    $('.resend-email-btn').on('click', function(e) {
        e.preventDefault();
        const link = $(this).data('link');
        const amount = $(this).data('amount');
        
        showLoader();
        
        $.ajax({
            url: "{{ route('admin.bookings.resend-payment', $booking->id) }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                link: link,
                amount: amount
            },
            success: function(res) {
                hideLoader();
                if(res.success) {
                    Swal.fire('Success', res.message, 'success');
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function(xhr) {
                hideLoader();
                Swal.fire('Error', 'Failed to resend payment email.', 'error');
            }
        });
    });
});
</script>
@endpush
@endsection
