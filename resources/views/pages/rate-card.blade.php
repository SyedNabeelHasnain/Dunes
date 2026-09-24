@extends('layouts.app')

@section('title', 'Official Tours & Pricing Rate Card | Dunes Discovery Tourism')
@section('meta_description', 'Official verified rates and package pricing for Dubai Desert Safaris, Dune Buggy Rentals, City Tours, and Luxury Marina Dinner Cruises by Dunes Discovery Tourism.')

@push('styles')
<style>
@media print {
    #header, footer, .whatsapp-floating-btn, .rc-floating-bar, [data-floating-pill], .modal, .toast-container, .rc-btn-action {
        display: none !important;
    }
    body, main, #main, .rc-page {
        background: #FFFFFF !important;
        padding: 0 !important;
        margin: 0 !important;
        min-height: auto !important;
    }
    .max-w-7xl {
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    @page {
        size: A4 portrait;
        margin: 10mm 12mm 10mm 12mm;
    }
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .rc-tour-card {
        break-inside: avoid;
        page-break-inside: avoid;
        border: 1px solid #CBD5E1 !important;
        box-shadow: none !important;
        margin-bottom: 16px !important;
    }
}
</style>
@endpush

@section('content')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebPage",
      "@id": "{{ route('rate-card') }}#webpage",
      "url": "{{ route('rate-card') }}",
      "name": "Official Dubai Desert Safaris & Tours Rate Card | Dunes Discovery",
      "description": "Verified official rates and pricing for Dubai desert safaris, quad biking, dune buggy rentals, marina dhow cruises, and private VIP tours.",
      "isPartOf": {
        "@type": "WebSite",
        "@id": "{{ route('home') }}#website",
        "url": "{{ route('home') }}",
        "name": "Dunes Discovery Tourism LLC Dubai"
      },
      "breadcrumb": {
        "@type": "BreadcrumbList",
        "@id": "{{ route('rate-card') }}#breadcrumb",
        "itemListElement": [
          {
            "@type": "ListItem",
            "position": 1,
            "name": "Home",
            "item": "{{ rtrim(route('home'), '/') }}/"
          },
          {
            "@type": "ListItem",
            "position": 2,
            "name": "Rate Card",
            "item": "{{ route('rate-card') }}"
          }
        ]
      },
      "publisher": {
        "@type": "TravelAgency",
        "@id": "{{ route('home') }}#organization",
        "name": "Dunes Discovery Tourism L.L.C"
      }
    }
  ]
}
</script>
<div class="rc-page py-6 sm:py-10 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Top Floating Toolbar -->
        <div class="rc-floating-bar sticky top-20 z-30 p-3 sm:p-4 mb-6 flex flex-wrap items-center justify-between gap-3 bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200/80 shadow-md">
            <div class="flex items-center gap-2.5">
                <a href="{{ route('tours.index') }}" class="border border-slate-300 hover:border-primary text-slate-700 hover:text-primary rounded-full px-3.5 py-1.5 text-xs font-bold inline-flex items-center gap-1 transition-colors">
                    <i class="bi bi-arrow-left"></i> Back to Tours
                </a>
                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full px-3 py-1 text-xs font-bold inline-flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span> Verified Rates
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="btn-desert-animated rounded-full px-4 py-1.5 text-xs font-bold text-white shadow-sm inline-flex items-center gap-1.5 cursor-pointer" onclick="window.print()">
                    <i class="bi bi-printer-fill"></i> Print / Save as PDF
                </button>
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}?text={{ urlencode('Hello Dunes Discovery Tourism, I am viewing your official rate card and would like to make an inquiry.') }}" target="_blank" rel="noopener" class="btn-whatsapp-animated rounded-full px-4 py-1.5 text-xs font-bold text-white inline-flex items-center gap-1.5 shadow-sm">
                    <i class="bi bi-whatsapp"></i> WhatsApp Booking
                </a>
            </div>
        </div>

        <!-- Official Hero Banner -->
        <div class="p-6 sm:p-10 mb-8 rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800 text-white border border-slate-700 shadow-xl relative overflow-hidden">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center relative z-10">
                <div class="lg:col-span-8">
                    <div class="flex items-center gap-3 mb-3">
                        <img src="{{ asset('images/logo-white.png') }}" alt="Dunes Discovery Tourism" width="160" height="46" class="h-10 w-auto">
                        <span class="bg-amber-400 text-slate-950 font-bold rounded-full px-3 py-0.5 text-[11px] uppercase tracking-wider">
                            Official 2026 Price Guide
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight mb-2">
                        Dubai Desert Safaris & Tours <span class="text-primary">Rate Card</span>
                    </h1>
                    <p class="text-slate-300 text-xs sm:text-sm max-w-xl leading-relaxed mb-4">
                        Official tour portfolio & pricing catalog by Dunes Discovery Tourism LLC. Direct operator rates with best price guarantee across all UAE excursions.
                    </p>
                    <div class="flex flex-wrap gap-2 text-white text-xs">
                        <span class="bg-white/10 border border-white/20 rounded-lg px-3 py-1.5 inline-flex items-center gap-1.5">
                            <i class="bi bi-telephone-fill text-primary"></i> {{ $phone }}
                        </span>
                        <span class="bg-white/10 border border-white/20 rounded-lg px-3 py-1.5 inline-flex items-center gap-1.5">
                            <i class="bi bi-whatsapp text-emerald-400"></i> WhatsApp: {{ $waPhone }}
                        </span>
                        <span class="bg-white/10 border border-white/20 rounded-lg px-3 py-1.5 inline-flex items-center gap-1.5">
                            <i class="bi bi-envelope-fill text-primary"></i> {{ $email }}
                        </span>
                        <span class="bg-white/10 border border-white/20 rounded-lg px-3 py-1.5 inline-flex items-center gap-1.5">
                            <i class="bi bi-globe text-primary"></i> dunesdiscoverytourism.com
                        </span>
                    </div>
                </div>
                <div class="lg:col-span-4 hidden lg:flex justify-end">
                    <div class="p-5 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/15 text-left min-w-[220px]">
                        <div class="text-slate-300 text-xs mb-1">Customer Ratings & Trust</div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-2xl font-black text-white">4.9 / 5.0</span>
                            <div class="text-amber-400 text-xs flex gap-0.5">
                                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                            </div>
                        </div>
                        <div class="text-slate-300 text-xs">TripAdvisor & Google Verified</div>
                    </div>
                </div>
            </div>
            <div class="absolute -right-16 -bottom-16 w-80 h-80 rounded-full bg-primary/20 blur-3xl pointer-events-none"></div>
        </div>

        <!-- Free Doorstep Pickup Banner -->
        <div class="mb-8 p-5 sm:p-6 rounded-2xl bg-gradient-to-r from-orange-50 to-amber-50 border-2 border-orange-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-primary text-white flex items-center justify-center text-xl shrink-0 shadow-xs">
                    <i class="bi bi-car-front-fill"></i>
                </div>
                <div>
                    <div class="font-extrabold text-slate-900 text-sm sm:text-base leading-snug">
                        COMPLIMENTARY 4X4 DOORSTEP HOTEL PICKUP & DROP-OFF INCLUDED
                    </div>
                    <div class="text-slate-600 text-xs sm:text-sm mt-0.5">
                        Enjoy seamless door-to-door transportation in clean, air-conditioned Toyota Land Cruisers from any hotel, residence, or cruise terminal across Dubai & Sharjah.
                    </div>
                </div>
            </div>
            <span class="bg-slate-900 text-white rounded-full px-3.5 py-1.5 text-xs font-bold whitespace-nowrap shrink-0 inline-flex items-center gap-1">
                <i class="bi bi-check-circle-fill text-emerald-400"></i> Zero Hidden Fees
            </span>
        </div>

        <!-- Tours Grouped by Category -->
        @foreach($categories as $cat)
            @if($cat->tours && $cat->tours->count() > 0)
            <div class="mb-10">
                <div class="bg-white border border-slate-200 border-l-4 border-l-primary rounded-xl px-5 py-3.5 mb-5 flex items-center justify-between shadow-xs">
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900 uppercase tracking-tight m-0">
                        {{ $cat->name }}
                    </h2>
                    <span class="bg-slate-100 text-slate-600 rounded-full px-3 py-1 text-xs font-bold">
                        {{ $cat->tours->count() }} Available {{ Str::plural('Experience', $cat->tours->count()) }}
                    </span>
                </div>

                <div class="space-y-6">
                @foreach($cat->tours as $t)
                <div class="rc-tour-card bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs hover:shadow-md transition-shadow">
                    <div class="grid grid-cols-1 md:grid-cols-12">
                        <!-- Left: Image & Badge (3 cols) -->
                        <div class="md:col-span-3 relative min-h-[180px] bg-slate-900">
                            @php
                                $imgFile = !empty($t->hero_image) ? preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $t->hero_image) : 'desert-safari-poster.avif';
                            @endphp
                            <img src="{{ asset('images/' . $imgFile) }}" width="400" height="250" alt="{{ $t->name }} Dubai Desert Safari" loading="lazy" class="w-full h-full object-cover">
                            <div class="absolute top-2.5 left-2.5 flex flex-col gap-1.5">
                                @if($t->is_bestseller)
                                <span class="bg-amber-400 text-slate-950 font-bold rounded-full px-2.5 py-0.5 text-[10px] uppercase shadow-xs">
                                    ⭐ Bestseller
                                </span>
                                @endif
                                <span class="bg-slate-900/80 text-white font-semibold rounded-full px-2.5 py-0.5 text-[10px]">
                                    ⏱ {{ $t->duration }}
                                </span>
                            </div>
                        </div>

                        <!-- Middle: Tour Details & Inclusions (5 cols) -->
                        <div class="md:col-span-5 p-5 flex flex-col justify-between">
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 mb-1 leading-snug">
                                    {{ $t->name }}
                                </h3>
                                
                                <div class="flex flex-wrap gap-3 text-xs text-slate-500 mb-3">
                                    @if($t->pickup_time)
                                    <span><i class="bi bi-clock-history mr-1 text-primary"></i>{{ $t->pickup_time }} - {{ $t->dropoff_time }}</span>
                                    @endif
                                    <span><i class="bi bi-star-fill text-amber-400 mr-1"></i>{{ $t->rating ?? '4.9' }} ({{ $t->review_count ?? '500+' }} Reviews)</span>
                                </div>

                                <p class="text-slate-600 text-xs sm:text-sm leading-relaxed mb-4">
                                    {{ Str::limit($t->short_desc, 180) }}
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-1.5">
                                <span class="bg-slate-100 text-slate-700 rounded-md px-2 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1 border border-slate-200">
                                    <i class="bi bi-check-circle-fill text-primary"></i> Free 4x4 Pickup
                                </span>
                                <span class="bg-slate-100 text-slate-700 rounded-md px-2 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1 border border-slate-200">
                                    <i class="bi bi-check-circle-fill text-primary"></i> Professional Guide
                                </span>
                                <span class="bg-slate-100 text-slate-700 rounded-md px-2 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1 border border-slate-200">
                                    <i class="bi bi-check-circle-fill text-primary"></i> Refreshments
                                </span>
                                @if(str_contains(strtolower($t->name), 'evening') || str_contains(strtolower($t->name), 'cruise'))
                                <span class="bg-slate-100 text-slate-700 rounded-md px-2 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1 border border-slate-200">
                                    <i class="bi bi-check-circle-fill text-primary"></i> Buffet Dinner
                                </span>
                                <span class="bg-slate-100 text-slate-700 rounded-md px-2 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1 border border-slate-200">
                                    <i class="bi bi-check-circle-fill text-primary"></i> Live Shows
                                </span>
                                @endif
                            </div>
                        </div>

                        <!-- Right: Pricing & Package Tiers Matrix (4 cols) -->
                        <div class="md:col-span-4 p-5 border-t md:border-t-0 md:border-l border-slate-200 bg-slate-50 flex flex-col justify-between">
                            <div>
                                <div class="text-[11px] uppercase font-bold tracking-wider text-slate-400 mb-2">
                                    Available Package Tiers
                                </div>

                                @if($t->tiers && $t->tiers->count() > 0)
                                    <div class="space-y-2">
                                    @foreach($t->tiers as $tier)
                                    <div class="flex items-center justify-between py-1.5 border-b border-dashed border-slate-300 last:border-b-0 text-xs">
                                        <div class="pr-2">
                                            <div class="font-bold text-slate-800">{{ $tier->name }}</div>
                                            @if($tier->description)
                                            <span class="text-slate-400 text-[10px] block line-clamp-1">{{ Str::limit($tier->description, 35) }}</span>
                                            @endif
                                        </div>
                                        <div class="text-right shrink-0">
                                            @if(!empty($tier->pivot->old_price))
                                            <span class="text-slate-400 line-through text-[11px] mr-1" data-aed="{{ $tier->pivot->old_price }}">AED {{ number_format($tier->pivot->old_price) }}</span>
                                            @endif
                                            <span class="font-black text-primary text-sm" data-aed="{{ $tier->pivot->price }}">AED {{ number_format($tier->pivot->price) }}</span>
                                            <span class="text-slate-400 text-[9px] block">/ {{ $tier->pivot->price_type ?? 'person' }}</span>
                                        </div>
                                    </div>
                                    @endforeach
                                    </div>
                                @else
                                    <div class="flex items-center justify-between py-1.5 text-xs">
                                        <div class="font-bold text-slate-800">Standard Experience</div>
                                        <div class="text-right">
                                            <span class="font-black text-primary text-sm" data-aed="{{ $t->price }}">AED {{ number_format($t->price) }}</span>
                                            <span class="text-slate-400 text-[9px] block">/ person</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="pt-4 mt-3 border-t border-slate-200 flex gap-2 rc-btn-action">
                                <a href="{{ route('tours.show', $t->slug) }}" class="flex-1 text-center border border-slate-300 hover:border-slate-800 text-slate-800 rounded-full py-2 text-xs font-bold transition-colors">
                                    Details <i class="bi bi-arrow-right"></i>
                                </a>
                                <button type="button" class="btn-desert-animated rounded-full px-4 py-2 text-xs font-bold text-white shadow-xs cursor-pointer" data-action="open-booking" data-tour="{{ $t->id }}" @click="$store.modal.open('booking', { tourId: {{ $t->id }} })">
                                    Book Tour
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                </div>
            </div>
            @endif
        @endforeach

        <!-- Add-Ons & Extras Section -->
        @if($globalAddons && $globalAddons->count() > 0)
        <div class="mb-10">
            <div class="bg-white border border-slate-200 border-l-4 border-l-primary rounded-xl px-5 py-3.5 mb-5 flex items-center justify-between shadow-xs">
                <h2 class="text-base sm:text-lg font-extrabold text-slate-900 uppercase tracking-tight m-0">
                    Safari Add-Ons & Custom Upgrades
                </h2>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($globalAddons as $addon)
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex justify-between items-center">
                        <div class="pr-2">
                            <div class="font-bold text-slate-900 text-xs">{{ $addon->name }}</div>
                            @if($addon->description)
                            <span class="text-slate-500 text-[10px] block line-clamp-1">{{ Str::limit($addon->description, 45) }}</span>
                            @endif
                        </div>
                        <div class="font-black text-primary text-sm whitespace-nowrap" data-aed="{{ $addon->default_price ?: $addon->price ?: 0 }}">
                            AED {{ number_format($addon->default_price ?: $addon->price ?: 0) }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Why Choose Us & Guarantees -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 text-center shadow-xs">
                <i class="bi bi-shield-check text-2xl text-primary mb-2 block"></i>
                <h3 class="font-bold text-slate-900 text-xs sm:text-sm mb-1">Best Price Guarantee</h3>
                <p class="text-slate-500 text-xs mb-0">Direct operator pricing with zero middleman commissions.</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 text-center shadow-xs">
                <i class="bi bi-arrow-counterclockwise text-2xl text-primary mb-2 block"></i>
                <h3 class="font-bold text-slate-900 text-xs sm:text-sm mb-1">Free Cancellation</h3>
                <p class="text-slate-500 text-xs mb-0">100% full refund up to 24 hours prior to tour departure.</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 text-center shadow-xs">
                <i class="bi bi-car-front-fill text-2xl text-primary mb-2 block"></i>
                <h3 class="font-bold text-slate-900 text-xs sm:text-sm mb-1">Doorstep 4x4 Pickup</h3>
                <p class="text-slate-500 text-xs mb-0">Comfortable hotel pickup across all Dubai & Sharjah locations.</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 text-center shadow-xs">
                <i class="bi bi-whatsapp text-2xl text-emerald-500 mb-2 block"></i>
                <h3 class="font-bold text-slate-900 text-xs sm:text-sm mb-1">Instant Support</h3>
                <p class="text-slate-500 text-xs mb-0">Dedicated 24/7 safari concierge on WhatsApp & Phone.</p>
            </div>
        </div>

        <!-- Bottom VIP / Custom Booking Box -->
        <div class="p-8 sm:p-12 rounded-3xl bg-gradient-to-br from-slate-950 to-slate-900 text-white text-center shadow-xl border border-slate-700">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white mb-2">Corporate Events & Custom VIP Camps</h2>
            <p class="text-slate-300 text-xs sm:text-sm max-w-xl mx-auto mb-6 leading-relaxed">
                Planning a group excursion, private corporate desert party, or VIP luxury setup? Connect directly with our tour specialists for custom itineraries and group rates.
            </p>
            <div class="flex flex-wrap justify-center gap-3">
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}?text={{ urlencode('Hello Dunes Discovery Tourism, I would like to request a custom group / corporate tour quote.') }}" target="_blank" rel="noopener" class="btn-whatsapp-animated rounded-full px-6 py-3 font-bold text-white text-xs sm:text-sm inline-flex items-center gap-2 shadow-sm">
                    <i class="bi bi-whatsapp text-lg"></i> Chat on WhatsApp
                </a>
                <button type="button" class="btn-desert-animated rounded-full px-6 py-3 font-bold text-white text-xs sm:text-sm inline-flex items-center gap-2 shadow-sm cursor-pointer" onclick="window.print()">
                    <i class="bi bi-printer-fill"></i> Save / Print Rate Card
                </button>
            </div>
        </div>

    </div>
</div>

@if($autoPrint)
@push('scripts')
<script>
window.addEventListener('load', function() {
    setTimeout(function() {
        window.print();
    }, 600);
});
</script>
@endpush
@endif
@endsection