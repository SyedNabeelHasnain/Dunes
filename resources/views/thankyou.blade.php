@extends('layouts.app')

@section('content')
@php
    $googleActive = ($settings['google_active'] ?? '0') === '1';
    $metaActive = ($settings['meta_active'] ?? '0') === '1';
    $metaPixelId = $settings['meta_pixel_id'] ?? null;
    $adsIdSetting = $settings['google_ads_id'] ?? null;
    $whatsappVal = $settings['site_whatsapp'] ?? '971502456056';
@endphp

<!-- Google Ads Conversion Tag: Submit lead form (AW-17859624049/eR3SCLimtvobEPH4kMRC) -->
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('event', 'conversion', {
    'send_to': 'AW-17859624049/eR3SCLimtvobEPH4kMRC'
    @if($booking && $booking->total)
    , 'value': {{ (float)$booking->total }},
    'currency': 'AED',
    'transaction_id': '{{ $booking->reference }}'
    @endif
});
gtag('event', 'conversion_event_submit_lead_form', {
    'send_to': 'AW-17859624049/eR3SCLimtvobEPH4kMRC'
    @if($booking && $booking->total)
    , 'value': {{ (float)$booking->total }},
    'currency': 'AED',
    'transaction_id': '{{ $booking->reference }}'
    @endif
});
</script>

@if($booking && ($paymentStatus === 'completed' || $method === 'cash'))
    <!-- Google Ecommerce Purchase Conversion -->
    @if($googleActive)
    <script>
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
        event: 'purchase',
        ecommerce: {
            transaction_id: '{{ $booking->reference }}',
            value: {{ $booking->total }},
            currency: 'AED',
            items: [{
                item_name: '{{ $booking->tour_name }}',
                item_id: '{{ $booking->tour_id }}',
                price: {{ $booking->total }},
                item_category: 'Tours',
                quantity: 1
            }]
        }
    });
    </script>
    @endif

    <!-- Meta Pixel Purchase Conversion -->
    @if($metaActive && !empty($metaPixelId))
    <script>
    if(window.fbq){
        fbq('track', 'Purchase', {
            value: {{ $booking->total }},
            currency: 'AED',
            content_ids: ['TOUR-{{ $booking->tour_id }}'],
            content_type: 'product'
        }, {
            eventID: 'BOOK-{{ $booking->reference }}'
        });
    }
    </script>
    @endif
@endif

<section class="section py-5" style="margin-top: 5vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <!-- Success Header Card -->
                <div class="card card-modern border-0 shadow-sm rounded-4 p-4 p-lg-5 mb-4 text-center bg-white">
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 p-4" style="width: 80px; height: 80px;">
                            <i class="bi bi-check-lg text-success fs-1"></i>
                        </div>
                    </div>
                    <h1 class="fw-800 mb-2">Thank You!</h1>
                    <p class="text-muted lead mb-0">We are thrilled that you chose Dunes Discovery Tourism.<br>Your adventure awaits!</p>
                </div>

                <!-- Details Card -->
                <div class="card card-modern border-0 shadow-sm rounded-4 p-4 p-lg-5 bg-white">
                    @if(!$booking)
                        <div class="text-center">
                            <h2 class="fw-800 mb-3 text-dark">Booking Not Found</h2>
                            <p class="text-muted mb-0">We couldn't retrieve the booking details at this moment.</p>
                        </div>
                    @else
                        @php
                            $title = 'Booking Received';
                            $subtitle = 'We have received your booking request.';
                            if($method === 'advance' && $paymentStatus === 'completed'){
                                $title = 'Advance Payment Received';
                                $subtitle = 'Your booking slot is held and confirmed for your selected date.';
                            } elseif($method === 'full' && $paymentStatus === 'completed'){
                                $title = 'Payment Successful';
                                $subtitle = 'Your booking is confirmed.';
                            } elseif($method !== 'cash' && $paymentStatus === 'pending'){
                                $title = 'Payment Processing';
                                $subtitle = 'We are verifying your payment status.';
                            } elseif($method !== 'cash' && $paymentStatus === 'failed'){
                                $title = 'Payment Failed';
                                $subtitle = 'Your payment could not be completed.';
                            } elseif($method === 'cash'){
                                $title = 'Booking Received';
                                $subtitle = 'Pay on pickup.';
                            }
                        @endphp
                        <h2 class="fw-800 mb-2 text-dark">{{ $title }}</h2>
                        <p class="text-muted">{{ $subtitle }}</p>
                        <p class="fw-semibold text-primary">Dunes Discovery will contact you shortly to confirm the exact pickup time.</p>
                        
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-4 h-100 border">
                                    <div class="text-muted small fw-bold">Reference</div>
                                    <div class="fw-800 text-dark fs-5">#{{ $booking->reference }}</div>
                                    <div class="text-muted small fw-bold mt-3">Tour</div>
                                    <div class="fw-semibold text-dark">{{ $booking->tour_name }}</div>
                                    <div class="text-muted small fw-bold mt-3">Date</div>
                                    <div class="fw-semibold text-dark">{{ $booking->tour_date ? $booking->tour_date->format('M j, Y') : '' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-4 h-100 border">
                                    <div class="text-muted small fw-bold">Payment Method</div>
                                    <div class="fw-semibold text-capitalize text-dark">{{ $method }}</div>
                                    @if($booking->coupon_code && $booking->discount_amount > 0)
                                    <div class="text-muted small fw-bold mt-2">Original Subtotal</div>
                                    <div class="fw-semibold text-muted text-decoration-line-through">AED {{ number_format($booking->original_total, 2) }}</div>
                                    <div class="text-muted small fw-bold mt-2">Promo Discount ({{ $booking->coupon_code }})</div>
                                    <div class="fw-bold text-success">-AED {{ number_format($booking->discount_amount, 2) }}</div>
                                    @endif
                                    <div class="text-muted small fw-bold mt-3">Total</div>
                                    <div class="fw-800 text-primary fs-5">AED {{ number_format($booking->total, 2) }}</div>
                                    <div class="text-muted small fw-bold mt-3">Paid</div>
                                    <div class="fw-semibold text-success">AED {{ number_format($booking->payment_amount ?? 0, 2) }}</div>
                                    <div class="text-muted small fw-bold mt-3">Balance Due</div>
                                    <div class="fw-semibold text-danger">AED {{ number_format($booking->balance_due ?? 0, 2) }}</div>
                                </div>
                            </div>
                        </div>

                        @if($method === 'full' && $paymentStatus === 'completed')
                            <div class="mt-4 p-3 bg-white border rounded-4">
                                <div class="fw-bold mb-2 text-dark">Invoice Summary</div>
                                <div class="d-flex justify-content-between text-muted small"><span>Subtotal</span><span>AED {{ number_format($booking->subtotal, 2) }}</span></div>
                                <div class="d-flex justify-content-between text-muted small"><span>Addons</span><span>AED {{ number_format($booking->addons_total, 2) }}</span></div>
                                @if($booking->coupon_code && $booking->discount_amount > 0)
                                <div class="d-flex justify-content-between text-success small"><span>Promo Code ({{ $booking->coupon_code }})</span><span>-AED {{ number_format($booking->discount_amount, 2) }}</span></div>
                                @endif
                                <div class="d-flex justify-content-between fw-bold mt-2 text-dark"><span>Total Paid</span><span>AED {{ number_format($booking->payment_amount, 2) }}</span></div>
                            </div>
                        @endif
                    @endif
                </div>

                @if($booking)
                <!-- Official E-Ticket Voucher Card -->
                <div class="card card-modern border-0 shadow-sm rounded-4 p-4 p-lg-5 mt-4 bg-white text-center position-relative overflow-hidden" style="border-top: 4px solid #F58F43 !important;">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10 text-primary p-3 mb-3 mx-auto" style="width: 64px; height: 64px;">
                        <i class="bi bi-ticket-perforated-fill fs-2"></i>
                    </div>
                    <h3 class="fw-800 text-dark mb-2">Your Official E-Ticket & Boarding Pass</h3>
                    <p class="text-muted mb-4 mx-auto" style="max-width: 540px;">
                        Your DTCM-certified digital voucher with real-time driver verification QR code is ready. You can present it directly from your phone or download an official PDF copy.
                    </p>
                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                        <a href="{{ route('booking.voucher', $booking->reference) }}" target="_blank" rel="noopener noreferrer" class="btn btn-desert-animated rounded-pill px-4 py-3 fw-bold shadow-sm d-inline-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-phone fs-5"></i> View Digital Boarding Pass
                        </a>
                        <a href="{{ route('booking.voucher.pdf', $booking->reference) }}" class="btn btn-outline-dark rounded-pill px-4 py-3 fw-bold d-inline-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-file-earmark-pdf fs-5 text-danger"></i> Download PDF Voucher
                        </a>
                    </div>
                </div>
                @endif

                <!-- Action Links -->
                <div class="card card-modern border-0 shadow-sm rounded-4 p-4 p-lg-5 mt-4 bg-white">
                    <h4 class="fw-800 mb-4 text-center text-dark">What's Next?</h4>
                    <div class="row g-2 justify-content-center">
                        <div class="col-md-4">
                            <a href="{{ route('home') }}" class="btn btn-desert-animated w-100 fw-bold rounded-pill py-3">
                                <i class="bi bi-house-door-fill me-1"></i> Home
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('tours.index') }}" class="btn btn-desert-animated-dark w-100 fw-bold rounded-pill py-3">
                                <i class="bi bi-compass-fill me-1"></i> Explore Tours
                            </a>
                        </div>
                        <div class="col-md-4">
                            @php
                                $waLink = "https://wa.me/" . preg_replace('/[^0-9]/','',$whatsappVal);
                                if($booking) {
                                    $waMsg = "Hi Dunes Discovery Tourism, I have a question regarding my booking #".$booking->reference;
                                    $waLink .= "?text=" . urlencode($waMsg);
                                }
                            @endphp
                            <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn btn-whatsapp-animated w-100 fw-bold rounded-pill py-3">
                                <i class="bi bi-whatsapp me-1"></i> WhatsApp Us
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection
