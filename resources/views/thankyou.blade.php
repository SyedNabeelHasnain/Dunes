@extends('layouts.app')

@section('content')
@php
    $googleActive = \App\Models\Setting::where('setting_key', 'google_active')->value('setting_value') === '1';
    $metaActive = \App\Models\Setting::where('setting_key', 'meta_active')->value('setting_value') === '1';
    $metaPixelId = \App\Models\Setting::where('setting_key', 'meta_pixel_id')->value('setting_value');
    $adsIdSetting = \App\Models\Setting::where('setting_key', 'google_ads_id')->value('setting_value');
    $whatsappVal = \App\Models\Setting::where('setting_key', 'site_whatsapp')->value('setting_value') ?? '971502456056';
@endphp

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

    <!-- Google Ads Specific Conversion -->
    @if($googleActive && !empty($adsIdSetting))
        @php
            if(strpos($adsIdSetting, 'AW-') === 0) {
                $conversionId = $adsIdSetting;
            } else {
                $conversionId = 'AW-' . preg_replace('/[^0-9]/', '', $adsIdSetting);
            }
        @endphp
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('event', 'conversion_event_page_view', {
            'send_to': '{{ $conversionId }}',
            'transaction_id': '{{ $booking->reference }}',
            'value': {{ $booking->total }},
            'currency': 'AED'
        });
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
                                    <div class="text-muted small fw-bold mt-3">Total</div>
                                    <div class="fw-800 text-primary fs-5">AED {{ number_format($booking->total) }}</div>
                                    <div class="text-muted small fw-bold mt-3">Paid</div>
                                    <div class="fw-semibold text-success">AED {{ number_format($booking->payment_amount ?? 0) }}</div>
                                    <div class="text-muted small fw-bold mt-3">Balance Due</div>
                                    <div class="fw-semibold text-danger">AED {{ number_format($booking->balance_due ?? 0) }}</div>
                                </div>
                            </div>
                        </div>

                        @if($method === 'full' && $paymentStatus === 'completed')
                            <div class="mt-4 p-3 bg-white border rounded-4">
                                <div class="fw-bold mb-2 text-dark">Invoice Summary</div>
                                <div class="d-flex justify-content-between text-muted small"><span>Subtotal</span><span>AED {{ number_format($booking->subtotal) }}</span></div>
                                <div class="d-flex justify-content-between text-muted small"><span>Addons</span><span>AED {{ number_format($booking->addons_total) }}</span></div>
                                <div class="d-flex justify-content-between fw-bold mt-2 text-dark"><span>Total Paid</span><span>AED {{ number_format($booking->payment_amount) }}</span></div>
                            </div>
                        @endif
                    @endif
                </div>

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
