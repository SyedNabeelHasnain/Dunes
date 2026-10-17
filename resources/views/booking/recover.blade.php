@php
    $pageRobots = 'noindex, follow';
    $googleActive = ($settings['google_active'] ?? '0') === '1';
    $metaActive = ($settings['meta_active'] ?? '0') === '1';
    $metaPixelId = $settings['meta_pixel_id'] ?? null;
    $waPhone = $settings['site_whatsapp'] ?? '971502456056';
    $tourImage = $tour?->hero_image ? asset('images/' . $tour->hero_image) : asset('images/desert-safari-poster.avif');
    $initialMethod = old('payment_method', $booking->payment_method ?: 'cash');
    if (!$ziinaActive && in_array($initialMethod, ['advance', 'full'])) {
        $initialMethod = 'cash';
    }
@endphp

@extends('layouts.app')

@section('title', 'Complete Your Reservation #' . $booking->reference . ' - Dunes Discovery Tourism')

@section('content')
<!-- Google Ads & GA4 Attribution: Begin Checkout Lineage -->
<script>
window.dataLayer = window.dataLayer || [];
window.dataLayer.push({
    event: 'begin_checkout',
    ecommerce: {
        currency: 'AED',
        value: {{ (float) $total }},
        coupon: @json($booking->coupon_code ?? ''),
        items: [{
            item_id: 'TOUR-{{ $booking->tour_id }}',
            item_name: @json($booking->tour_name ?? ($tour->name ?? 'Dubai Desert Safari')),
            item_category: 'Tours',
            item_variant: @json($booking->tier_name ?? ($tier->name ?? 'Standard')),
            price: {{ (float) $total }},
            quantity: 1
        }]
    },
    attribution: {
        booking_ref: @json($booking->reference),
        utm_source: @json($booking->utm_source ?? ''),
        utm_medium: @json($booking->utm_medium ?? ''),
        utm_campaign: @json($booking->utm_campaign ?? '')
    }
});
</script>

<div class="bg-slate-50 min-h-screen py-8 sm:py-12 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Top Security & Trust Header -->
        <div class="flex items-center justify-between flex-wrap gap-3 mb-6 pb-4 border-b border-slate-200/80">
            <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
                <a href="{{ localized_route('home') }}" class="hover:text-primary transition-colors flex items-center gap-1">
                    <i class="bi bi-house-door-fill"></i> Home
                </a>
                <i class="bi bi-chevron-right text-[10px] text-slate-300 rtl:rotate-180"></i>
                <span class="text-slate-800">Checkout Recovery</span>
                <i class="bi bi-chevron-right text-[10px] text-slate-300 rtl:rotate-180"></i>
                <span class="font-mono text-primary font-extrabold">#{{ $booking->reference }}</span>
            </div>

            <div class="flex items-center gap-3 text-xs font-bold text-slate-600 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <i class="bi bi-shield-check text-emerald-600"></i> DET License #1430583
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white text-slate-700 border border-slate-200 shadow-2xs">
                    <i class="bi bi-lock-fill text-primary"></i> 256-Bit SSL Encrypted
                </span>
            </div>
        </div>

        <!-- Courtesy Hold Notice Banner -->
        <div class="mb-8 rounded-2xl bg-gradient-to-r from-orange-50 via-amber-50 to-orange-50 border-2 border-orange-200/80 p-4 sm:p-5 shadow-xs flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-primary text-white flex items-center justify-center shrink-0 shadow-xs text-lg">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <div class="text-sm sm:text-base font-black text-slate-900 leading-tight">
                        We have placed a temporary courtesy hold on your reservation!
                    </div>
                    <div class="text-xs text-slate-600 mt-0.5">
                        Your spots for <strong>{{ $booking->tour_name ?? ($tour->name ?? 'Desert Safari') }}</strong> are reserved. Finalize your details below to confirm.
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 bg-white/90 px-3.5 py-1.5 rounded-full border border-orange-200 text-xs font-black text-primary font-mono shadow-2xs shrink-0">
                <i class="bi bi-hourglass-split animate-pulse"></i>
                <span>Hold Active: <span id="countdownTimer">14:59</span></span>
            </div>
        </div>

        <!-- Main Checkout Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Left Column: Guest Details & Payment Form (7 cols) -->
            <div class="lg:col-span-7 space-y-6">

                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between pb-5 mb-6 border-b border-slate-100">
                        <div>
                            <span class="text-[11px] font-black uppercase tracking-wider text-primary block">Step 2 of 2</span>
                            <h1 class="text-xl sm:text-2xl font-black text-slate-900">Review & Confirm Reservation</h1>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-orange-50 border border-orange-200 text-primary text-xs font-extrabold font-mono">
                            #{{ $booking->reference }}
                        </span>
                    </div>

                    <!-- Checkout Recovery Form -->
                    <form id="recoveryCheckoutForm" method="POST" action="{{ localized_route('booking.recover.process', ['reference' => $booking->reference]) }}" class="space-y-5">
                        @csrf
                        <input type="hidden" name="payment_method" id="selectedPaymentMethod" value="{{ $initialMethod }}">
                        <input type="hidden" name="gps_lat" id="gpsLat" value="{{ request('gps_lat', '') }}">
                        <input type="hidden" name="gps_lng" id="gpsLng" value="{{ request('gps_lng', '') }}">
                        <input type="hidden" name="gps_accuracy" id="gpsAccuracy" value="">
                        <input type="hidden" name="gps_timestamp" id="gpsTimestamp" value="">
                        <input type="hidden" name="gps_consent" id="gpsConsent" value="No">

                        <!-- Full Name -->
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-800 mb-1.5" for="guestName">
                                {{ __('ui.booking.name') }} <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative rounded-2xl bg-slate-50 border border-slate-200 focus-within:border-primary focus-within:bg-white focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                                <input type="text" 
                                       name="name" 
                                       id="guestName" 
                                       value="{{ old('name', $booking->name) }}" 
                                       required 
                                       autocomplete="name"
                                       class="w-full px-4 py-3.5 bg-transparent text-sm sm:text-base font-bold text-slate-900 border-0 focus:outline-none placeholder:text-slate-400">
                            </div>
                        </div>

                        <!-- Email Address + Verification Badge -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-800" for="guestEmail">
                                    {{ __('ui.booking.email') }} <span class="text-rose-500">*</span>
                                </label>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    <i class="bi bi-patch-check-fill"></i> Verified Guest Email
                                </span>
                            </div>
                            <div class="relative rounded-2xl bg-slate-50 border border-slate-200 focus-within:border-primary focus-within:bg-white focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                                <input type="email" 
                                       name="email" 
                                       id="guestEmail" 
                                       value="{{ old('email', $booking->email) }}" 
                                       required 
                                       autocomplete="email"
                                       class="w-full px-4 py-3.5 bg-transparent text-sm sm:text-base font-bold text-slate-900 border-0 focus:outline-none placeholder:text-slate-400">
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">Your official digital voucher and receipt will be dispatched to this email address.</p>
                        </div>

                        <!-- Phone / WhatsApp Number -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-800" for="guestPhone">
                                    {{ __('ui.booking.phone') }} <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[10px] font-bold text-emerald-600 inline-flex items-center gap-1">
                                    <i class="bi bi-whatsapp"></i> Captain Contact Channel
                                </span>
                            </div>
                            <div class="relative rounded-2xl bg-slate-50 border border-slate-200 focus-within:border-primary focus-within:bg-white focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                                <input type="tel" 
                                       name="phone" 
                                       id="guestPhone" 
                                       value="{{ old('phone', $booking->phone) }}" 
                                       required 
                                       autocomplete="tel"
                                       placeholder="+971 50 123 4567"
                                       class="w-full px-4 py-3.5 bg-transparent text-sm sm:text-base font-bold text-slate-900 border-0 focus:outline-none placeholder:text-slate-400">
                            </div>
                        </div>

                        <!-- Pickup Location in Dubai with GPS Detector -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-800" for="guestLocation">
                                    {{ __('ui.booking.pickup_location') }} <span class="text-rose-500">*</span>
                                </label>
                                <button type="button" 
                                        id="gpsDetectorBtn" 
                                        class="inline-flex items-center gap-1 text-[11px] font-bold text-primary hover:text-primary-dark transition-colors cursor-pointer">
                                    <i class="bi bi-crosshair"></i> Auto-Detect My Location
                                </button>
                            </div>
                            <div class="relative rounded-2xl bg-slate-50 border border-slate-200 focus-within:border-primary focus-within:bg-white focus-within:ring-2 focus-within:ring-primary/20 transition-all flex items-center">
                                <span class="ps-4 pe-2 text-primary shrink-0"><i class="bi bi-geo-alt-fill text-base"></i></span>
                                <input type="text" 
                                       name="location" 
                                       id="guestLocation" 
                                       value="{{ old('location', $booking->pickup_location) }}" 
                                       required 
                                       placeholder="e.g. Atlantis The Palm, Dubai Marina Mall, or Villa Address"
                                       class="w-full py-3.5 pe-4 bg-transparent text-sm sm:text-base font-bold text-slate-900 border-0 focus:outline-none placeholder:text-slate-400">
                            </div>
                            <p id="gpsStatusMessage" class="text-[11px] font-semibold text-emerald-600 mt-1 hidden"></p>
                        </div>

                        <!-- Special Requests / Dietary -->
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-800 mb-1.5" for="guestRequests">
                                {{ __('ui.booking.special_requests') }}
                            </label>
                            <textarea name="requests" 
                                      id="guestRequests" 
                                      rows="2" 
                                      placeholder="e.g. Vegetarian/Jain meal requirements, infant car seat, anniversary celebration..."
                                      class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm font-medium text-slate-900 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all placeholder:text-slate-400">{{ old('requests', $booking->special_requests) }}</textarea>
                        </div>

                        <!-- Payment Method Selection Cards -->
                        <div class="pt-4 border-t border-slate-100">
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-800 mb-3">
                                Select Payment Method <span class="text-rose-500">*</span>
                            </label>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" role="radiogroup" aria-label="Payment Method Selection">
                                
                                <!-- Cash on Arrival -->
                                <div class="payment-card cursor-pointer p-4 rounded-2xl transition-all relative flex flex-col justify-between {{ $initialMethod === 'cash' ? 'border-2 border-primary bg-orange-50/70 shadow-xs ring-2 ring-primary/20' : 'border border-slate-200 bg-white hover:border-primary/50 shadow-2xs' }}"
                                     data-method="cash"
                                     role="radio"
                                     aria-checked="{{ $initialMethod === 'cash' ? 'true' : 'false' }}">
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="w-8 h-8 rounded-xl bg-orange-100 text-primary flex items-center justify-center font-bold text-base">
                                                <i class="bi bi-cash-stack"></i>
                                            </span>
                                            <span class="radio-dot w-4 h-4 rounded-full flex items-center justify-center {{ $initialMethod === 'cash' ? 'border border-primary bg-primary' : 'border border-slate-300 bg-white' }}">
                                                <span class="w-1.5 h-1.5 rounded-full bg-white {{ $initialMethod === 'cash' ? '' : 'hidden' }}"></span>
                                            </span>
                                        </div>
                                        <div class="font-extrabold text-slate-900 text-sm">Cash on Pickup</div>
                                        <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">Pay directly to safari captain on pickup date.</p>
                                    </div>
                                    <div class="mt-3 pt-2 border-t border-slate-200/60 font-black text-slate-800 text-xs font-mono">
                                        AED {{ number_format($total, 2) }}
                                    </div>
                                </div>

                                @if($ziinaActive)
                                <!-- Advance Payment (Deposit) -->
                                <div class="payment-card cursor-pointer p-4 rounded-2xl transition-all relative flex flex-col justify-between {{ $initialMethod === 'advance' ? 'border-2 border-primary bg-orange-50/70 shadow-xs ring-2 ring-primary/20' : 'border border-slate-200 bg-white hover:border-primary/50 shadow-2xs' }}"
                                     data-method="advance"
                                     role="radio"
                                     aria-checked="{{ $initialMethod === 'advance' ? 'true' : 'false' }}">
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="w-8 h-8 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center font-bold text-base">
                                                <i class="bi bi-pie-chart-fill"></i>
                                            </span>
                                            <span class="radio-dot w-4 h-4 rounded-full flex items-center justify-center {{ $initialMethod === 'advance' ? 'border border-primary bg-primary' : 'border border-slate-300 bg-white' }}">
                                                <span class="w-1.5 h-1.5 rounded-full bg-white {{ $initialMethod === 'advance' ? '' : 'hidden' }}"></span>
                                            </span>
                                        </div>
                                        <div class="font-extrabold text-slate-900 text-sm">Advance ({{ $advancePercent }}%)</div>
                                        <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">Pay deposit now via Card/Apple Pay; rest on pickup.</p>
                                    </div>
                                    <div class="mt-3 pt-2 border-t border-slate-200/60 font-black text-primary text-xs font-mono">
                                        AED {{ number_format($advanceAmount, 2) }}
                                    </div>
                                </div>

                                <!-- Full Payment (100%) -->
                                <div class="payment-card cursor-pointer p-4 rounded-2xl transition-all relative flex flex-col justify-between {{ $initialMethod === 'full' ? 'border-2 border-primary bg-orange-50/70 shadow-xs ring-2 ring-primary/20' : 'border border-slate-200 bg-white hover:border-primary/50 shadow-2xs' }}"
                                     data-method="full"
                                     role="radio"
                                     aria-checked="{{ $initialMethod === 'full' ? 'true' : 'false' }}">
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-base">
                                                <i class="bi bi-credit-card-2-front-fill"></i>
                                            </span>
                                            <span class="radio-dot w-4 h-4 rounded-full flex items-center justify-center {{ $initialMethod === 'full' ? 'border border-primary bg-primary' : 'border border-slate-300 bg-white' }}">
                                                <span class="w-1.5 h-1.5 rounded-full bg-white {{ $initialMethod === 'full' ? '' : 'hidden' }}"></span>
                                            </span>
                                        </div>
                                        <div class="font-extrabold text-slate-900 text-sm">Full Online (100%)</div>
                                        <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">Zero hassle on arrival. Instant digital confirmation.</p>
                                    </div>
                                    <div class="mt-3 pt-2 border-t border-slate-200/60 font-black text-primary text-xs font-mono">
                                        AED {{ number_format($fullAmount, 2) }}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Terms of Service Agreement -->
                        <div class="pt-2">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="agree_terms" id="agreeTerms" value="1" checked required class="mt-1 w-4 h-4 rounded text-primary focus:ring-primary border-slate-300">
                                <span class="text-xs text-slate-600 leading-snug">
                                    I agree to the <a href="{{ localized_route('terms') }}" target="_blank" class="text-primary font-bold hover:underline">Terms of Service</a> & <a href="{{ localized_route('cancellation') }}" target="_blank" class="text-primary font-bold hover:underline">Cancellation Policy</a> (Free cancellation with full refund up to 24 hours prior to departure).
                                </span>
                            </label>
                        </div>

                        <!-- Error Message Banner -->
                        <div id="checkoutErrorBox" class="hidden p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill text-base shrink-0"></i>
                            <span id="checkoutErrorMessage">Unable to process booking. Please check the fields and try again.</span>
                        </div>

                        <!-- Action Submit Button -->
                        <div class="pt-2">
                            <button type="submit" 
                                    id="submitCheckoutBtn" 
                                    class="w-full py-4 px-6 rounded-2xl bg-primary hover:bg-primary-dark text-white font-black text-base shadow-lg shadow-orange-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:pointer-events-none min-h-[56px]">
                                <span id="submitBtnSpinner" class="hidden w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                <span id="submitBtnText">
                                    @if($initialMethod === 'cash')
                                        Confirm Cash on Arrival Booking &rarr;
                                    @elseif($initialMethod === 'advance')
                                        Pay Advance AED {{ number_format($advanceAmount, 2) }} 💳
                                    @else
                                        Pay Full AED {{ number_format($fullAmount, 2) }} 💳
                                    @endif
                                </span>
                            </button>
                            <p class="text-[11px] text-center text-slate-400 mt-2 flex items-center justify-center gap-1.5">
                                <i class="bi bi-shield-lock-fill text-emerald-500"></i> No booking fees &bull; Instant WhatsApp & Email Confirmation
                            </p>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Column: Sticky Safari Summary Card (5 cols) -->
            <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-24">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    
                    <!-- Tour Hero Thumbnail -->
                    <div class="relative h-44 sm:h-52 w-full overflow-hidden bg-slate-900">
                        <img src="{{ $tourImage }}" alt="{{ $booking->tour_name ?? 'Safari' }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/30 to-transparent"></div>
                        <div class="absolute bottom-3.5 left-4 right-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-primary text-white text-[10px] font-black uppercase tracking-wide mb-1.5 shadow-2xs">
                                {{ $booking->tier_name ?? ($tier->name ?? 'Desert Safari') }}
                            </span>
                            <h2 class="text-lg font-black text-white leading-tight drop-shadow-sm">
                                {{ $booking->tour_name ?? ($tour->name ?? 'Dubai Desert Safari') }}
                            </h2>
                        </div>
                    </div>

                    <!-- Safari Details Breakdown -->
                    <div class="p-6 space-y-4 text-xs">
                        <div class="grid grid-cols-2 gap-3 pb-4 border-b border-slate-100">
                            <div>
                                <span class="text-slate-400 uppercase font-bold text-[10px] block">Safari Date</span>
                                <span class="font-black text-slate-800 text-sm flex items-center gap-1.5 mt-0.5">
                                    <i class="bi bi-calendar-event text-primary"></i>
                                    {{ \Carbon\Carbon::parse($booking->tour_date)->format('M d, Y') }}
                                </span>
                            </div>
                            <div>
                                <span class="text-slate-400 uppercase font-bold text-[10px] block">Guest Count</span>
                                <span class="font-black text-slate-800 text-sm flex items-center gap-1.5 mt-0.5">
                                    <i class="bi bi-people text-primary"></i>
                                    {{ $booking->adults }} Adult(s)@if($booking->children > 0), {{ $booking->children }} Child(ren)@endif
                                </span>
                            </div>
                        </div>

                        <!-- Addons if any -->
                        @if($booking->addons && $booking->addons->count())
                        <div class="pb-4 border-b border-slate-100">
                            <span class="text-slate-400 uppercase font-bold text-[10px] block mb-2">Selected Upgrades</span>
                            <div class="space-y-1.5">
                                @foreach($booking->addons as $addon)
                                <div class="flex items-center justify-between text-slate-700">
                                    <span>&bull; {{ $addon->name }} (x{{ $addon->pivot->quantity ?? 1 }})</span>
                                    <span class="font-bold font-mono">AED {{ number_format($addon->pivot->price * ($addon->pivot->quantity ?? 1), 2) }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Itemized Price Summary -->
                        <div class="space-y-2 pt-1 pb-4 border-b border-slate-100">
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Experience Base Fare:</span>
                                <span class="font-bold font-mono text-slate-800">AED {{ number_format($subtotal, 2) }}</span>
                            </div>
                            @if($addonsTotal > 0)
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Experience Addons:</span>
                                <span class="font-bold font-mono text-slate-800">AED {{ number_format($addonsTotal, 2) }}</span>
                            </div>
                            @endif
                            @if($discount > 0)
                            <div class="flex items-center justify-between text-emerald-600 font-bold">
                                <span class="flex items-center gap-1">
                                    <i class="bi bi-tag-fill"></i> Promo ({{ $booking->coupon_code }}):
                                </span>
                                <span class="font-mono">- AED {{ number_format($discount, 2) }}</span>
                            </div>
                            @endif
                            <div class="flex items-center justify-between text-slate-900 pt-2 border-t border-slate-100">
                                <span class="text-sm font-black uppercase">Total Payable:</span>
                                <span class="text-xl font-black text-primary font-mono" id="summaryTotalText">AED {{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        <!-- Dynamic Due Split Info -->
                        <div id="paymentDueSplit" class="{{ $initialMethod === 'advance' ? '' : 'hidden' }} p-3 rounded-xl bg-sky-50 border border-sky-200 text-sky-900">
                            <div class="flex items-center justify-between font-bold">
                                <span>Due Now ({{ $advancePercent }}% Deposit):</span>
                                <span class="font-mono text-primary font-black">AED {{ number_format($advanceAmount, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-sky-700 mt-1">
                                <span>Balance on Arrival:</span>
                                <span class="font-mono font-bold">AED {{ number_format($total - $advanceAmount, 2) }}</span>
                            </div>
                        </div>

                        <!-- Guarantees & Features -->
                        <div class="space-y-2 pt-2 text-[11px] text-slate-600">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-check-circle-fill text-emerald-500 text-sm"></i>
                                <span>Free cancellation up to 24h before tour start</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="bi bi-check-circle-fill text-emerald-500 text-sm"></i>
                                <span>Doorstep pickup & drop-off across Dubai hotels</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="bi bi-check-circle-fill text-emerald-500 text-sm"></i>
                                <span>All-inclusive: 4x4 Dune Bashing, BBQ Dinner & Shows</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instant WhatsApp Safari Concierge Assistance -->
                <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-3xl p-5 border border-emerald-200/80 shadow-xs flex items-center justify-between gap-4">
                    <div>
                        <div class="text-xs font-black uppercase tracking-wider text-emerald-800">Need Assistance?</div>
                        <p class="text-xs text-emerald-700 mt-0.5">Have questions or want custom adjustments? Speak with our 24/7 Safari Concierge.</p>
                    </div>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $waPhone) }}?text={{ rawurlencode('Hi Dunes Discovery, I am reviewing my booking #' . $booking->reference . ' for ' . $booking->tour_name . '. Can you assist me?') }}" 
                       target="_blank" 
                       rel="noopener"
                       class="shrink-0 px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-black text-xs inline-flex items-center gap-1.5 shadow-sm transition-colors">
                        <i class="bi bi-whatsapp"></i> Chat Now
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentCards = document.querySelectorAll('.payment-card');
    const selectedInput = document.getElementById('selectedPaymentMethod');
    const submitBtn = document.getElementById('submitCheckoutBtn');
    const submitBtnText = document.getElementById('submitBtnText');
    const submitBtnSpinner = document.getElementById('submitBtnSpinner');
    const paymentDueSplit = document.getElementById('paymentDueSplit');
    const errorBox = document.getElementById('checkoutErrorBox');
    const errorMsg = document.getElementById('checkoutErrorMessage');
    const form = document.getElementById('recoveryCheckoutForm');
    const gpsBtn = document.getElementById('gpsDetectorBtn');
    const gpsStatus = document.getElementById('gpsStatusMessage');
    const locationInput = document.getElementById('guestLocation');

    const totalVal = {{ (float) $total }};
    const advanceVal = {{ (float) $advanceAmount }};
    const fullVal = {{ (float) $fullAmount }};

    function updatePaymentMethod(method) {
        selectedInput.value = method;

        paymentCards.forEach(card => {
            const isMatch = card.dataset.method === method;
            card.setAttribute('aria-checked', isMatch ? 'true' : 'false');
            
            const dotWrapper = card.querySelector('.radio-dot');
            const dotInner = dotWrapper ? dotWrapper.querySelector('span') : null;

            if (isMatch) {
                card.classList.remove('border-slate-200', 'bg-white', 'hover:border-primary/50');
                card.classList.add('border-2', 'border-primary', 'bg-orange-50/70', 'shadow-xs', 'ring-2', 'ring-primary/20');
                if (dotWrapper) {
                    dotWrapper.classList.remove('border-slate-300', 'bg-white');
                    dotWrapper.classList.add('border-primary', 'bg-primary');
                }
                if (dotInner) dotInner.classList.remove('hidden');
            } else {
                card.classList.remove('border-2', 'border-primary', 'bg-orange-50/70', 'shadow-xs', 'ring-2', 'ring-primary/20');
                card.classList.add('border', 'border-slate-200', 'bg-white', 'hover:border-primary/50');
                if (dotWrapper) {
                    dotWrapper.classList.remove('border-primary', 'bg-primary');
                    dotWrapper.classList.add('border-slate-300', 'bg-white');
                }
                if (dotInner) dotInner.classList.add('hidden');
            }
        });

        if (paymentDueSplit) {
            paymentDueSplit.classList.toggle('hidden', method !== 'advance');
        }

        if (method === 'cash') {
            submitBtnText.textContent = 'Confirm Cash on Arrival Booking \u2192';
        } else if (method === 'advance') {
            submitBtnText.textContent = 'Pay Advance AED ' + advanceVal.toFixed(2) + ' \uD83D\uDCB3';
        } else {
            submitBtnText.textContent = 'Pay Full AED ' + fullVal.toFixed(2) + ' \uD83D\uDCB3';
        }
    }

    paymentCards.forEach(card => {
        card.addEventListener('click', function() {
            updatePaymentMethod(this.dataset.method);
        });
        card.addEventListener('keydown', function(e) {
            if (e.key === ' ' || e.key === 'Enter') {
                e.preventDefault();
                updatePaymentMethod(this.dataset.method);
            }
        });
    });

    // GPS Auto-Detection
    if (gpsBtn && locationInput) {
        gpsBtn.addEventListener('click', function() {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser.');
                return;
            }

            gpsBtn.disabled = true;
            gpsBtn.innerHTML = '<span class="inline-block w-3 h-3 border-2 border-primary border-t-transparent rounded-full animate-spin"></span> Locating...';

            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    const accuracy = pos.coords.accuracy;

                    document.getElementById('gpsLat').value = lat;
                    document.getElementById('gpsLng').value = lng;
                    document.getElementById('gpsAccuracy').value = accuracy;
                    document.getElementById('gpsTimestamp').value = new Date().toISOString();
                    document.getElementById('gpsConsent').value = 'Yes';

                    // Reverse geocode via Nominatim
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.display_name) {
                                locationInput.value = data.display_name;
                            } else {
                                locationInput.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                            }
                            gpsStatus.textContent = '✓ GPS location pinpointed successfully';
                            gpsStatus.classList.remove('hidden');
                        })
                        .catch(() => {
                            locationInput.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                            gpsStatus.textContent = '✓ GPS coordinates detected';
                            gpsStatus.classList.remove('hidden');
                        })
                        .finally(() => {
                            gpsBtn.disabled = false;
                            gpsBtn.innerHTML = '<i class="bi bi-crosshair"></i> Auto-Detect My Location';
                        });
                },
                function(err) {
                    gpsBtn.disabled = false;
                    gpsBtn.innerHTML = '<i class="bi bi-crosshair"></i> Auto-Detect My Location';
                    gpsStatus.textContent = 'Unable to fetch GPS. Please type your hotel or pickup address.';
                    gpsStatus.classList.remove('hidden', 'text-emerald-600');
                    gpsStatus.classList.add('text-rose-600');
                },
                { enableHighAccuracy: true, timeout: 8000 }
            );
        });
    }

    // Countdown Timer (15 minutes simulation)
    let holdSeconds = 14 * 60 + 59;
    const timerEl = document.getElementById('countdownTimer');
    if (timerEl) {
        const timerInterval = setInterval(() => {
            if (holdSeconds <= 0) {
                clearInterval(timerInterval);
                timerEl.textContent = 'Expiring soon';
                return;
            }
            holdSeconds--;
            const m = Math.floor(holdSeconds / 60);
            const s = holdSeconds % 60;
            timerEl.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
        }, 1000);
    }

    // AJAX Submission Handling
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            errorBox.classList.add('hidden');
            submitBtn.disabled = true;
            submitBtnSpinner.classList.remove('hidden');
            const originalText = submitBtnText.textContent;
            submitBtnText.textContent = 'Securing Your Booking...';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, data })))
            .then(({ status, ok, data }) => {
                if (ok && data.success && data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    const msg = (data && data.message) ? data.message : 'Unable to confirm booking. Please review your details.';
                    errorMsg.textContent = msg;
                    errorBox.classList.remove('hidden');
                    submitBtn.disabled = false;
                    submitBtnSpinner.classList.add('hidden');
                    submitBtnText.textContent = originalText;
                }
            })
            .catch(err => {
                errorMsg.textContent = 'Network error occurred. Please try again or contact WhatsApp support.';
                errorBox.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtnSpinner.classList.add('hidden');
                submitBtnText.textContent = originalText;
            });
        });
    }
});
</script>
@endsection
