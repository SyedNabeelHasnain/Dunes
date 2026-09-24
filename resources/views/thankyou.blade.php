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

<section class="py-12 bg-slate-50 min-h-[85vh] flex items-center">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 w-full">

        <!-- Success Header Card -->
        <div class="bg-white rounded-3xl shadow-xs border border-slate-200 p-6 sm:p-10 mb-6 text-center">
            <div class="inline-flex items-center justify-center rounded-full bg-emerald-50 text-emerald-600 w-20 h-20 mb-4 text-3xl">
                <i class="bi bi-check-lg"></i>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-slate-900 mb-2">Thank You!</h1>
            <p class="text-slate-600 text-base sm:text-lg leading-relaxed">
                We are thrilled that you chose Dunes Discovery Tourism.<br class="hidden sm:inline">Your adventure awaits!
            </p>
        </div>

        <!-- Details Card -->
        <div class="bg-white rounded-3xl shadow-xs border border-slate-200 p-6 sm:p-10 mb-6">
            @if(!$booking)
                <div class="text-center py-6">
                    <h2 class="text-xl font-bold text-slate-900 mb-2">Booking Not Found</h2>
                    <p class="text-slate-500 text-sm">We couldn't retrieve the booking details at this moment.</p>
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
                <h2 class="text-2xl font-extrabold text-slate-900 mb-1">{{ $title }}</h2>
                <p class="text-slate-500 text-sm mb-3">{{ $subtitle }}</p>
                <div class="p-3.5 bg-amber-500/10 border border-amber-500/20 rounded-xl mb-6">
                    <p class="font-semibold text-primary text-sm flex items-center gap-2 mb-0">
                        <i class="bi bi-info-circle-fill shrink-0"></i>
                        <span>Dunes Discovery will contact you shortly to confirm the exact pickup time.</span>
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Left: Tour Details -->
                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-200 space-y-3.5">
                        <div>
                            <div class="text-slate-500 text-xs font-bold uppercase tracking-wider">Reference</div>
                            <div class="font-extrabold text-slate-900 text-lg sm:text-xl font-mono">#{{ $booking->reference }}</div>
                        </div>
                        <div>
                            <div class="text-slate-500 text-xs font-bold uppercase tracking-wider">Tour</div>
                            <div class="font-semibold text-slate-800 text-sm sm:text-base">{{ $booking->tour_name }}</div>
                        </div>
                        <div>
                            <div class="text-slate-500 text-xs font-bold uppercase tracking-wider">Date</div>
                            <div class="font-semibold text-slate-800 text-sm sm:text-base">{{ $booking->tour_date ? $booking->tour_date->format('M j, Y') : '' }}</div>
                        </div>
                    </div>

                    <!-- Right: Financial Summary -->
                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                        <div>
                            <div class="text-slate-500 text-xs font-bold uppercase tracking-wider">Payment Method</div>
                            <div class="font-semibold capitalize text-slate-800 text-sm sm:text-base">{{ $method }}</div>
                        </div>
                        @if($booking->coupon_code && $booking->discount_amount > 0)
                        <div>
                            <div class="text-slate-500 text-xs font-bold uppercase tracking-wider">Original Subtotal</div>
                            <div class="font-semibold text-slate-500 line-through text-sm">AED {{ number_format($booking->original_total, 2) }}</div>
                            <div class="text-slate-500 text-xs font-bold uppercase tracking-wider mt-1">Promo Discount ({{ $booking->coupon_code }})</div>
                            <div class="font-bold text-emerald-600 text-sm">-AED {{ number_format($booking->discount_amount, 2) }}</div>
                        </div>
                        @endif
                        <div class="pt-2 border-t border-slate-200">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-500 font-medium">Total Package:</span>
                                <span class="font-extrabold text-primary text-lg">AED {{ number_format($booking->total, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-xs text-slate-600 mt-1">
                                <span>Paid Online:</span>
                                <span class="font-semibold text-emerald-600">AED {{ number_format($booking->payment_amount ?? 0, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-xs text-slate-600 mt-1">
                                <span>Balance Due:</span>
                                <span class="font-semibold text-rose-600">AED {{ number_format($booking->balance_due ?? 0, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($method === 'full' && $paymentStatus === 'completed')
                    <div class="mt-6 p-4 bg-white border border-slate-200 rounded-2xl space-y-1.5 text-xs sm:text-sm">
                        <div class="font-bold text-slate-900 text-sm mb-2">Invoice Summary</div>
                        <div class="flex justify-between text-slate-500"><span>Subtotal</span><span>AED {{ number_format($booking->subtotal, 2) }}</span></div>
                        <div class="flex justify-between text-slate-500"><span>Addons</span><span>AED {{ number_format($booking->addons_total, 2) }}</span></div>
                        @if($booking->coupon_code && $booking->discount_amount > 0)
                        <div class="flex justify-between text-emerald-600"><span>Promo Code ({{ $booking->coupon_code }})</span><span>-AED {{ number_format($booking->discount_amount, 2) }}</span></div>
                        @endif
                        <div class="flex justify-between font-bold pt-2 border-t border-slate-200 text-slate-900"><span>Total Paid</span><span>AED {{ number_format($booking->payment_amount, 2) }}</span></div>
                    </div>
                @endif
            @endif
        </div>

        @if($booking)
        <!-- Official E-Ticket Voucher Card -->
        <div class="bg-white rounded-3xl shadow-xs border border-slate-200 border-t-4 border-t-primary p-6 sm:p-10 mb-6 text-center relative overflow-hidden">
            <div class="inline-flex items-center justify-center rounded-full bg-amber-500/15 text-primary w-16 h-16 mb-4 mx-auto text-2xl">
                <i class="bi bi-ticket-perforated-fill"></i>
            </div>
            <h3 class="text-xl sm:text-2xl font-black text-slate-900 mb-2">Your Official E-Ticket & Boarding Pass</h3>
            <p class="text-slate-500 text-sm sm:text-base mb-6 max-w-xl mx-auto leading-relaxed">
                Your DTCM-certified digital voucher with real-time driver verification QR code is ready. You can present it directly from your phone or download an official PDF copy.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-3">
                <a href="{{ route('booking.voucher', $booking->reference) }}" target="_blank" rel="noopener noreferrer" class="btn-desert-animated font-bold rounded-full px-6 py-3.5 text-white shadow-md inline-flex items-center justify-center gap-2 text-sm sm:text-base">
                    <i class="bi bi-phone text-lg"></i> View Digital Boarding Pass
                </a>
                <a href="{{ route('booking.voucher.pdf', $booking->reference) }}" class="border border-slate-800 hover:bg-slate-900 hover:text-white text-slate-800 font-bold rounded-full px-6 py-3.5 transition-colors inline-flex items-center justify-center gap-2 text-sm sm:text-base">
                    <i class="bi bi-file-earmark-pdf text-rose-500 text-lg"></i> Download PDF Voucher
                </a>
            </div>
        </div>
        @endif

        <!-- Action Links -->
        <div class="bg-white rounded-3xl shadow-xs border border-slate-200 p-6 sm:p-10">
            <h4 class="text-lg font-extrabold text-slate-900 mb-4 text-center">What's Next?</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <a href="{{ route('home') }}" class="btn-desert-animated w-full font-bold rounded-full py-3.5 text-white text-center inline-flex items-center justify-center gap-2 text-sm shadow-sm">
                    <i class="bi bi-house-door-fill"></i> Home
                </a>
                <a href="{{ route('tours.index') }}" class="btn-desert-animated-dark w-full font-bold rounded-full py-3.5 text-white text-center inline-flex items-center justify-center gap-2 text-sm shadow-sm">
                    <i class="bi bi-compass-fill"></i> Explore Tours
                </a>
                @php
                    $waLink = "https://wa.me/" . preg_replace('/[^0-9]/','',$whatsappVal);
                    if($booking) {
                        $waMsg = "Hi Dunes Discovery Tourism, I have a question regarding my booking #".$booking->reference;
                        $waLink .= "?text=" . urlencode($waMsg);
                    }
                @endphp
                <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn-whatsapp-animated w-full font-bold rounded-full py-3.5 text-white text-center inline-flex items-center justify-center gap-2 text-sm shadow-sm">
                    <i class="bi bi-whatsapp"></i> WhatsApp Us
                </a>
            </div>
        </div>

    </div>
</section>
@endsection
