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
      "@id": "{{ $canonical }}#webpage",
      "url": "{{ $canonical }}",
      "name": {!! json_encode($pageTitle) !!},
      "description": {!! json_encode($pageDesc) !!},
      "inLanguage": "{{ $currentLocale ?? app()->getLocale() ?? 'en' }}",
      "isPartOf": {
        "@type": "WebSite",
        "@id": "{{ url('/') }}#website",
        "url": "{{ url('/') }}",
        "name": "Dunes Discovery Tourism LLC Dubai"
      },
      "breadcrumb": {
        "@type": "BreadcrumbList",
        "@id": "{{ $canonical }}#breadcrumb",
        "itemListElement": [
          {
            "@type": "ListItem",
            "position": 1,
            "name": "{{ __('ui.nav.home') ?? 'Home' }}",
            "item": "{{ localized_route('home') }}"
          },
          {
            "@type": "ListItem",
            "position": 2,
            "name": "Rate Card",
            "item": "{{ $canonical }}"
          }
        ]
      },
      "publisher": {
        "@type": "TravelAgency",
        "@id": "{{ url('/') }}#organization",
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
                    <i class="bi bi-arrow-left rtl:rotate-180"></i> {{ __('ui.rate_card.back_to_tours') }}
                </a>
                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full px-3 py-1 text-xs font-bold inline-flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span> {{ __('ui.rate_card.verified_rates') }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="btn-desert-animated rounded-full px-4 py-1.5 text-xs font-bold text-white shadow-sm inline-flex items-center gap-1.5 cursor-pointer" onclick="window.print()">
                    <i class="bi bi-printer-fill"></i> {{ __('ui.rate_card.print_pdf') }}
                </button>
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}?text={{ urlencode('Hello Dunes Discovery Tourism, I am viewing your official rate card and would like to make an inquiry.') }}" target="_blank" rel="noopener" class="btn-whatsapp btn-whatsapp-animated rounded-full px-4 py-1.5 text-xs font-bold text-white inline-flex items-center gap-1.5 shadow-sm cursor-pointer" data-action="whatsapp" data-tour-name="Official Rate Card Inquiry">
                    <i class="bi bi-whatsapp"></i> {{ __('ui.rate_card.whatsapp_booking') }}
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
                            {{ __('ui.rate_card.official_guide') }}
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight mb-2">
                        {{ __('ui.rate_card.title') }} <span class="text-primary">{{ __('ui.rate_card.title_highlight') }}</span>
                    </h1>
                    <p class="text-slate-300 text-xs sm:text-sm max-w-xl leading-relaxed mb-4">
                        {{ __('ui.rate_card.hero_desc') }}
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
                    <div class="p-5 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/15 text-start min-w-[220px]">
                        <div class="text-slate-300 text-xs mb-1">{{ __('ui.rate_card.customer_ratings') }}</div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-2xl font-black text-white">4.9 / 5.0</span>
                            <div class="text-amber-400 text-xs flex gap-0.5">
                                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                            </div>
                        </div>
                        <div class="text-slate-300 text-xs">{{ __('ui.rate_card.verified_platform') }}</div>
                    </div>
                </div>
            </div>
            <div class="absolute -end-16 -bottom-16 w-80 h-80 rounded-full bg-primary/20 blur-3xl pointer-events-none"></div>
        </div>

        <!-- Free Doorstep Pickup Banner -->
        <div class="mb-8 p-5 sm:p-6 rounded-2xl bg-gradient-to-r from-orange-50 to-amber-50 border-2 border-orange-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-primary text-white flex items-center justify-center text-xl shrink-0 shadow-xs">
                    <i class="bi bi-car-front-fill"></i>
                </div>
                <div>
                    <div class="font-extrabold text-slate-900 text-sm sm:text-base leading-snug">
                        {{ __('ui.rate_card.complimentary_pickup_title') }}
                    </div>
                    <div class="text-slate-600 text-xs sm:text-sm mt-0.5">
                        {{ __('ui.rate_card.complimentary_pickup_desc') }}
                    </div>
                </div>
            </div>
            <span class="bg-slate-900 text-white rounded-full px-3.5 py-1.5 text-xs font-bold whitespace-nowrap shrink-0 inline-flex items-center gap-1">
                <i class="bi bi-check-circle-fill text-emerald-400"></i> {{ __('ui.rate_card.zero_hidden_fees') }}
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
                        {{ $cat->tours->count() }} {{ __('ui.rate_card.available_experiences') }}
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
                            <div class="absolute top-2.5 left-2.5 rtl:left-auto rtl:right-2.5 flex flex-col gap-1.5">
                                @if($t->is_bestseller)
                                <span class="bg-amber-400 text-slate-950 font-bold rounded-full px-2.5 py-0.5 text-[10px] uppercase shadow-xs">
                                    ⭐ {{ __('ui.home_popular.bestseller') }}
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
                                    <span><i class="bi bi-clock-history mr-1 rtl:mr-0 rtl:ml-1 text-primary"></i>{{ $t->pickup_time }} - {{ $t->dropoff_time }}</span>
                                    @endif
                                    <span><i class="bi bi-star-fill text-amber-400 mr-1 rtl:mr-0 rtl:ml-1"></i>{{ $t->rating ?? '4.9' }} ({{ $t->review_count ?? '500+' }})</span>
                                </div>

                                <p class="text-slate-600 text-xs sm:text-sm leading-relaxed mb-4">
                                    {{ Str::limit($t->short_desc, 180) }}
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-1.5">
                                <span class="bg-slate-100 text-slate-700 rounded-md px-2 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1 border border-slate-200">
                                    <i class="bi bi-check-circle-fill text-primary"></i> {{ __('ui.rate_card.free_pickup') }}
                                </span>
                                <span class="bg-slate-100 text-slate-700 rounded-md px-2 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1 border border-slate-200">
                                    <i class="bi bi-check-circle-fill text-primary"></i> {{ __('ui.rate_card.pro_guide') }}
                                </span>
                                <span class="bg-slate-100 text-slate-700 rounded-md px-2 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1 border border-slate-200">
                                    <i class="bi bi-check-circle-fill text-primary"></i> {{ __('ui.rate_card.refreshments') }}
                                </span>
                                @if(str_contains(strtolower($t->name), 'evening') || str_contains(strtolower($t->name), 'cruise'))
                                <span class="bg-slate-100 text-slate-700 rounded-md px-2 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1 border border-slate-200">
                                    <i class="bi bi-check-circle-fill text-primary"></i> {{ __('ui.rate_card.buffet_dinner') }}
                                </span>
                                <span class="bg-slate-100 text-slate-700 rounded-md px-2 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1 border border-slate-200">
                                    <i class="bi bi-check-circle-fill text-primary"></i> {{ __('ui.rate_card.live_shows') }}
                                </span>
                                @endif
                            </div>
                        </div>

                        <!-- Right: Pricing & Package Tiers Matrix (4 cols) -->
                        <div class="md:col-span-4 p-5 border-t md:border-t-0 md:border-l rtl:md:border-l-0 rtl:md:border-r border-slate-200 bg-slate-50 flex flex-col justify-between">
                            <div>
                                <div class="text-[11px] uppercase font-bold tracking-wider text-slate-400 mb-2">
                                    {{ __('ui.rate_card.available_tiers') }}
                                </div>

                                @if($t->tiers && $t->tiers->count() > 0)
                                    <div class="space-y-2">
                                    @foreach($t->tiers as $tier)
                                    <div class="flex items-center justify-between py-1.5 border-b border-dashed border-slate-300 last:border-b-0 text-xs">
                                        <div class="pe-2">
                                            <div class="font-bold text-slate-800">{{ $tier->name }}</div>
                                            @if($tier->description)
                                            <span class="text-slate-400 text-[10px] block line-clamp-1">{{ Str::limit($tier->description, 35) }}</span>
                                            @endif
                                        </div>
                                        <div class="text-end shrink-0">
                                            @if(!empty($tier->pivot->old_price))
                                            <span class="text-slate-400 line-through text-[11px] me-1" data-aed="{{ $tier->pivot->old_price }}">AED {{ number_format($tier->pivot->old_price) }}</span>
                                            @endif
                                            <span class="font-black text-primary text-sm" data-aed="{{ $tier->pivot->price }}">AED {{ number_format($tier->pivot->price) }}</span>
                                            <span class="text-slate-400 text-[9px] block">/ {{ $tier->pivot->price_type && $tier->pivot->price_type !== 'person' ? $tier->pivot->price_type : __('ui.rate_card.per_person') }}</span>
                                        </div>
                                    </div>
                                    @endforeach
                                    </div>
                                @else
                                    <div class="flex items-center justify-between py-1.5 text-xs">
                                        <div class="font-bold text-slate-800">{{ __('ui.rate_card.standard_exp') }}</div>
                                        <div class="text-end">
                                            <span class="font-black text-primary text-sm" data-aed="{{ $t->price }}">AED {{ number_format($t->price) }}</span>
                                            <span class="text-slate-400 text-[9px] block">/ {{ __('ui.rate_card.per_person') }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="pt-4 mt-3 border-t border-slate-200 flex gap-2 rc-btn-action">
                                <a href="{{ route('tours.show', $t->slug) }}" class="flex-1 text-center border border-slate-300 hover:border-slate-800 text-slate-800 rounded-full py-2 text-xs font-bold transition-colors">
                                    {{ __('ui.rate_card.details') }} <i class="bi bi-arrow-right rtl:rotate-180"></i>
                                </a>
                                <button type="button" class="btn-desert-animated rounded-full px-4 py-2 text-xs font-bold text-white shadow-xs cursor-pointer" data-action="open-booking" data-tour="{{ $t->id }}" @click="$store.modal.open('booking', { tourId: {{ $t->id }} })">
                                    {{ __('ui.rate_card.book_tour') }}
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
                    {{ __('ui.rate_card.addons_title') }}
                </h2>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($globalAddons as $addon)
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex justify-between items-center">
                        <div class="pr-2 rtl:pr-0 rtl:pl-2">
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
                <h3 class="font-bold text-slate-900 text-xs sm:text-sm mb-1">{{ __('ui.why_choose_us.best_price') }}</h3>
                <p class="text-slate-500 text-xs mb-0">{{ __('ui.why_choose_us.direct_pricing') }}</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 text-center shadow-xs">
                <i class="bi bi-arrow-counterclockwise text-2xl text-primary mb-2 block"></i>
                <h3 class="font-bold text-slate-900 text-xs sm:text-sm mb-1">{{ __('ui.why_choose_us.free_cancel') }}</h3>
                <p class="text-slate-500 text-xs mb-0">{{ __('ui.why_choose_us.free_cancel_desc') }}</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 text-center shadow-xs">
                <i class="bi bi-car-front-fill text-2xl text-primary mb-2 block"></i>
                <h3 class="font-bold text-slate-900 text-xs sm:text-sm mb-1">{{ __('ui.why_choose_us.doorstep_pickup') }}</h3>
                <p class="text-slate-500 text-xs mb-0">{{ __('ui.why_choose_us.doorstep_pickup_desc') }}</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 text-center shadow-xs">
                <i class="bi bi-whatsapp text-2xl text-emerald-500 mb-2 block"></i>
                <h3 class="font-bold text-slate-900 text-xs sm:text-sm mb-1">{{ __('ui.why_choose_us.instant_support') }}</h3>
                <p class="text-slate-500 text-xs mb-0">{{ __('ui.why_choose_us.instant_support_desc') }}</p>
            </div>
        </div>

        <!-- Bottom VIP / Custom Booking Box -->
        <div class="p-8 sm:p-12 rounded-3xl bg-gradient-to-br from-slate-950 to-slate-900 text-white text-center shadow-xl border border-slate-700">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white mb-2">{{ __('ui.rate_card.corporate_title') }}</h2>
            <p class="text-slate-300 text-xs sm:text-sm max-w-xl mx-auto mb-6 leading-relaxed">
                {{ __('ui.rate_card.corporate_desc') }}
            </p>
            <div class="flex flex-wrap justify-center gap-3">
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}?text={{ urlencode('Hello Dunes Discovery Tourism, I would like to request a custom group / corporate tour quote.') }}" target="_blank" rel="noopener" class="btn-whatsapp btn-whatsapp-animated rounded-full px-6 py-3 font-bold text-white text-xs sm:text-sm inline-flex items-center gap-2 shadow-sm cursor-pointer" data-action="whatsapp" data-tour-name="Corporate Group Tour Inquiry">
                    <i class="bi bi-whatsapp text-lg"></i> {{ __('ui.rate_card.chat_whatsapp') }}
                </a>
                <button type="button" class="btn-desert-animated rounded-full px-6 py-3 font-bold text-white text-xs sm:text-sm inline-flex items-center gap-2 shadow-sm cursor-pointer" onclick="window.print()">
                    <i class="bi bi-printer-fill"></i> {{ __('ui.rate_card.save_print') }}
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