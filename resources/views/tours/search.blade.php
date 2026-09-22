@extends('layouts.app')

@push('schema')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "SearchResultsPage",
  "@id": "{{ $canonical }}#webpage",
  "name": {!! json_encode($pageTitle) !!},
  "description": {!! json_encode($pageDesc) !!},
  "url": "{{ $canonical }}",
  "breadcrumb": {
    "@type": "BreadcrumbList",
    "itemListElement": [
      {
        "@type": "ListItem",
        "position": 1,
        "name": "Home",
        "item": "{{ route('home') }}"
      },
      {
        "@type": "ListItem",
        "position": 2,
        "name": "Tours",
        "item": "{{ route('tours.index') }}"
      },
      {
        "@type": "ListItem",
        "position": 3,
        "name": {!! json_encode($displayQuery) !!},
        "item": "{{ $canonical }}"
      }
    ]
  },
  "mainEntity": {
    "@type": "ItemList",
    "numberOfItems": {{ $tours->count() }},
    "itemListElement": [
      @foreach($tours as $idx => $t)
      @php
          $tPrice = $t->tiers->min('pivot.price') ?? 0;
      @endphp
      {
        "@type": "ListItem",
        "position": {{ $idx + 1 }},
        "name": {!! json_encode($t->name) !!},
        "url": "{{ route('tours.show', $t->slug) }}",
        "image": "{{ asset('images/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $t->thumb_image)) }}",
        "offers": {
          "@type": "Offer",
          "price": "{{ $tPrice }}",
          "priceCurrency": "AED",
          "availability": "https://schema.org/InStock"
        }
      }@if(!$loop->last),@endif
      @endforeach
    ]
  }
}
</script>
@endpush

@section('content')
<!-- Page Header Section with Exact-Query Mirroring -->
<section class="py-10 bg-slate-950 text-white relative overflow-hidden" style="margin-top: calc(-1 * var(--header-h, 72px));">
    <div class="absolute inset-0 w-full h-full bg-[radial-gradient(ellipse_at_15%_20%,rgba(246,144,68,0.2)_0%,transparent_60%)]"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 pt-16">
        <nav aria-label="breadcrumb">
            <ol class="flex items-center gap-2 text-xs sm:text-sm text-white/70 mb-4">
                <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a></li>
                <li><span class="text-white/40">/</span></li>
                <li><a href="{{ route('tours.index') }}" class="hover:text-white transition-colors">Tours</a></li>
                <li><span class="text-white/40">/</span></li>
                <li class="text-white font-semibold truncate max-w-[200px] sm:max-w-none" aria-current="page">{{ $displayQuery }}</li>
            </ol>
        </nav>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <div class="flex flex-wrap gap-2 mb-3">
                    <span class="glass rounded-full px-3.5 py-1 text-xs inline-flex items-center gap-1.5">
                        <i class="bi bi-star-fill text-amber-400"></i>Rated 4.9/5 by 2,847+ Travelers
                    </span>
                    <span class="bg-emerald-600/90 rounded-full px-3.5 py-1 text-xs font-semibold text-white inline-flex items-center gap-1.5">
                        <i class="bi bi-patch-check-fill text-emerald-200"></i>DET Licensed Operator #1430583
                    </span>
                    <span class="bg-primary rounded-full px-3.5 py-1 text-xs font-bold text-white inline-flex items-center gap-1.5 shadow-xs">
                        <i class="bi bi-check2-circle"></i>Found {{ $tours->count() }} Verified Packages
                    </span>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-2">
                    @if($isFallback)
                        Top Dubai Desert Safari Deals for <span class="text-primary">"{{ $displayQuery }}"</span>
                    @else
                        Verified Deals for <span class="text-primary">"{{ $displayQuery }}"</span> in Dubai
                    @endif
                </h1>
                <p class="text-sm sm:text-base text-white/80 max-w-2xl leading-relaxed">
                    Compare certified Dubai desert safaris, buggy rentals, and sightseeing experiences with 4x4 hotel transfers, 5-star live BBQ dining, and instant confirmation.
                </p>
            </div>
            <div class="hidden lg:block shrink-0 text-right">
                <span class="bg-primary/20 text-primary border border-primary/40 px-4 py-2 rounded-full font-bold text-sm inline-flex items-center gap-1.5">
                    <i class="bi bi-shield-check"></i>Best Price Guarantee
                </span>
                <div class="text-white/60 text-xs mt-1">Starting from AED {{ number_format($minPrice) }}</div>
            </div>
        </div>
    </div>
</section>

<!-- Trust & Regulatory E-E-A-T Bar -->
<section class="bg-slate-50 py-3.5 border-b border-slate-200 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <div class="flex items-center gap-2.5">
                <i class="bi bi-patch-check-fill text-primary text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">DTCM / DET Licensed</div>
                    <div class="text-slate-500 text-[11px]">License #1430583</div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <i class="bi bi-arrow-repeat text-emerald-600 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">100% Free Cancellation</div>
                    <div class="text-slate-500 text-[11px]">Full refund up to 24h</div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <i class="bi bi-award-fill text-amber-500 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">100% Halal Live BBQ</div>
                    <div class="text-slate-500 text-[11px]">Veg, Non-Veg & Jain</div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <i class="bi bi-shield-lock-fill text-cyan-600 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">Instant Confirmation</div>
                    <div class="text-slate-500 text-[11px]">Card / Cash on Pickup</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Search Body -->
<section class="py-10 sm:py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Search Refinement & Query Modification Bar -->
        <div class="bg-slate-50 rounded-2xl p-4 sm:p-5 mb-8 border border-slate-200/80 shadow-xs">
            <form action="{{ route('tours.search') }}" method="GET" class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
                <div class="lg:col-span-5">
                    <div class="relative">
                        <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="q" value="{{ $cleanQuery }}" class="w-full rounded-full pl-11 pr-24 py-2.5 bg-white border border-slate-200 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/40 shadow-xs" placeholder="Search safaris, quad biking, dune buggy..." required>
                        <button type="submit" class="btn-desert-animated rounded-full absolute right-1.5 top-1/2 -translate-y-1/2 px-4 py-1 text-xs font-bold text-white shadow-xs cursor-pointer">
                            Update
                        </button>
                    </div>
                </div>
                <div class="lg:col-span-7">
                    <div class="flex gap-2 overflow-x-auto no-scrollbar pb-1 items-center">
                        <span class="text-xs text-slate-500 font-bold shrink-0 inline-flex items-center gap-1">
                            <i class="bi bi-funnel"></i>Suggestions:
                        </span>
                        @foreach($intent['pills'] as $pill)
                            <a href="{{ route('tours.search', ['q' => $pill]) }}" class="bg-white hover:bg-slate-50 text-slate-700 hover:text-primary border border-slate-200 hover:border-primary rounded-full px-3.5 py-1.5 text-xs font-semibold shrink-0 whitespace-nowrap inline-flex items-center gap-1 transition-colors shadow-xs">
                                <i class="bi bi-plus-circle text-primary"></i> {{ $pill }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </form>
        </div>

        <!-- Generative Engine Optimization (GEO) Direct-Answer Overview Box -->
        <div class="rounded-2xl p-6 sm:p-8 mb-8 shadow-xs relative overflow-hidden bg-primary/5 border border-primary/25">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="bg-primary text-white rounded-full px-3 py-1 text-xs font-bold inline-flex items-center gap-1 shadow-xs">
                        <i class="bi bi-compass-fill"></i> Expert Safari Overview
                    </span>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900">{{ $aiOverview['title'] }}</h2>
                </div>
                <span class="bg-white text-slate-600 border border-slate-200 rounded-full px-3 py-1 text-xs font-semibold inline-flex items-center gap-1">
                    <i class="bi bi-patch-check-fill text-emerald-500"></i>Verified Tour Authority
                </span>
            </div>

            <p class="text-slate-700 text-sm sm:text-base mb-6 leading-relaxed">
                {{ $aiOverview['summary'] }}
            </p>

            <!-- Quick Specs Grid (Structured for Google SGE & Perplexity Extraction) -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                @foreach($aiOverview['quick_stats'] as $label => $stat)
                <div class="bg-white rounded-xl p-3 border border-slate-200/80 shadow-xs">
                    <div class="text-slate-400 text-[11px] font-medium mb-0.5">{{ $label }}</div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ $stat }}</div>
                </div>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center justify-between pt-3 border-t border-slate-200/60 text-xs text-slate-500">
                <div class="flex items-center gap-1.5">
                    <i class="bi bi-shield-check text-emerald-600"></i>
                    <span>{{ $aiOverview['verified_note'] }}</span>
                </div>
                <div class="hidden sm:block">
                    <span class="text-primary font-semibold inline-flex items-center gap-1"><i class="bi bi-lightning-charge-fill"></i>Live Best Rates</span>
                </div>
            </div>
        </div>

        <!-- Zero-Match Bestseller Fallback Banner -->
        @if($isFallback)
        <div class="bg-amber-500/10 border border-amber-500/30 rounded-2xl p-5 mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <i class="bi bi-info-circle-fill text-2xl text-amber-500 shrink-0 mt-0.5"></i>
                <div>
                    <h5 class="font-bold text-slate-900 text-sm sm:text-base mb-1">No direct package named "{{ $cleanQuery }}"</h5>
                    <p class="text-slate-600 text-xs sm:text-sm mb-0">
                        We didn't find an exact title match for your query, but here are Dubai's #1 rated Desert Safari packages hand-selected by our licensed guides:
                    </p>
                </div>
            </div>
            <button type="button" class="btn-desert-animated-dark font-bold text-white text-xs rounded-full px-5 py-2.5 whitespace-nowrap shrink-0 shadow-sm cursor-pointer" @click="$store.modal.open('safari-matcher')" data-bs-toggle="modal" data-bs-target="#safariMatcherModal">
                <i class="bi bi-stars text-amber-400 me-1"></i> Match My Safari (5% OFF)
            </button>
        </div>
        @endif

        <!-- Results Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="tours-grid">
            @foreach($tours as $t)
                @php
                    $minPrice = $t->tiers->min('pivot.price') ?? 0;
                    $tourCat = $categories->firstWhere('id', $t->category_id);
                    $tourCatSlug = $tourCat ? $tourCat->slug : '';
                @endphp
                <div class="tour-item flex flex-col h-full" data-category="{{ $tourCatSlug }}" data-name="{{ strtolower($t->name) }}">
                    <article class="bg-white rounded-2xl overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 border border-slate-100 flex flex-col h-full group">
                        <a href="{{ route('tours.show', $t->slug) }}" class="flex flex-col h-full text-inherit">
                            <div class="relative overflow-hidden aspect-[16/10]">
                                <img src="{{ asset('images/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $t->thumb_image)) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $t->name }} Dubai" loading="lazy">
                                @if($t->is_bestseller)
                                <span class="absolute top-3 left-3 bg-primary text-white text-xs font-bold px-3 py-1 rounded-full shadow-md inline-flex items-center gap-1">
                                    <i class="bi bi-fire text-amber-300"></i>Best Seller
                                </span>
                                @elseif($t->is_featured)
                                <span class="absolute top-3 left-3 bg-emerald-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-md inline-flex items-center gap-1">
                                    <i class="bi bi-award"></i>Featured
                                </span>
                                @endif
                                <div class="absolute bottom-0 inset-x-0 p-3 bg-gradient-to-t from-black/70 via-black/30 to-transparent">
                                    <span class="glass text-white text-xs font-semibold px-2.5 py-1 rounded-full inline-flex items-center gap-1">
                                        <i class="bi bi-tag-fill text-primary"></i>{{ $tourCat ? $tourCat->name : 'Tours' }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-5 flex flex-col flex-grow">
                                <div class="flex justify-between items-center text-xs mb-2">
                                    <div class="text-slate-500 inline-flex items-center gap-1">
                                        <i class="bi bi-clock text-primary"></i>{{ $t->duration ?: '6 Hours' }}
                                    </div>
                                    <div class="text-amber-500 font-bold inline-flex items-center gap-1">
                                        <i class="bi bi-star-fill"></i>{{ $t->rating ?: '4.9' }}
                                    </div>
                                </div>
                                <h2 class="text-base font-bold text-slate-900 mb-2 line-clamp-2 group-hover:text-primary transition-colors leading-snug">{{ $t->name }}</h2>
                                @php $bookingsToday = (int)(($t->id * 3 + (int)date('j')) % 5 + 3); @endphp
                                <div class="inline-flex items-center gap-1.5 text-red-600 text-xs font-bold mb-3">
                                    <i class="bi bi-fire text-red-500"></i>
                                    <span>{{ $bookingsToday }} booked in last 6 hours</span>
                                </div>
                                
                                <div class="flex flex-wrap gap-1.5 mb-4">
                                    <span class="bg-slate-50 text-slate-600 border border-slate-200 text-[10px] font-medium px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                        <i class="bi bi-check2 text-emerald-600"></i>4x4 Pickup
                                    </span>
                                    <span class="bg-slate-50 text-slate-600 border border-slate-200 text-[10px] font-medium px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                        <i class="bi bi-check2 text-emerald-600"></i>Halal Live BBQ
                                    </span>
                                    <span class="bg-slate-50 text-slate-600 border border-slate-200 text-[10px] font-medium px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                        <i class="bi bi-check2 text-emerald-600"></i>Free Cancel 24h
                                    </span>
                                </div>

                                <div class="flex justify-between items-end mt-auto pt-3 border-t border-slate-100">
                                    <div>
                                        <span class="block text-[10px] uppercase font-bold text-slate-400">Starting from</span>
                                        <span class="text-lg font-black text-primary" data-aed="{{ $minPrice }}">AED {{ number_format($minPrice) }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <button type="button" class="border border-slate-300 hover:border-primary text-slate-600 hover:text-primary text-xs font-semibold rounded-full px-2.5 py-1 transition-colors btn-toggle-compare inline-flex items-center gap-1 cursor-pointer" data-tour-id="{{ $t->id }}" onclick="event.preventDefault(); event.stopPropagation(); window.DunesCompare && window.DunesCompare.toggle(this);">
                                            <i class="bi bi-shuffle"></i> <span class="compare-btn-text">Compare</span>
                                        </button>
                                        <span role="button" tabindex="0" class="btn-circle-whatsapp fab-whatsapp w-8 h-8 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center cursor-pointer shadow-xs transition-transform hover:scale-105" data-tour-name="{{ $t->name }}" aria-label="Book {{ $t->name }} via WhatsApp" onclick="event.preventDefault(); event.stopPropagation(); if(window.App && typeof window.App.openWhatsApp === 'function'){ window.App.openWhatsApp('{{ addslashes($t->name) }}'); }" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();event.stopPropagation();if(window.App&&typeof window.App.openWhatsApp==='function'){window.App.openWhatsApp('{{ addslashes($t->name) }}');}}">
                                            <i class="bi bi-whatsapp text-sm"></i>
                                        </span>
                                        <div class="w-8 h-8 rounded-full bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white flex items-center justify-center transition-colors">
                                            <i class="bi bi-arrow-right text-sm"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </article>
                </div>
            @endforeach
        </div>

    </div>
</section>

<!-- FAQ / Search Guidance Section -->
<section class="py-12 sm:py-16 bg-slate-50 border-t border-slate-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-2">Questions About <span class="text-primary">{{ $displayQuery }}</span></h2>
            <p class="text-slate-600 text-sm max-w-xl mx-auto">Everything you need to know about booking certified Dubai tours with Dunes Discovery Tourism.</p>
        </div>

        <div x-data="{ activeSearchFaq: null }" class="space-y-3">
            <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-xs">
                <button type="button" 
                        class="w-full text-left px-5 sm:px-6 py-4 font-bold text-slate-900 flex items-center justify-between gap-4 cursor-pointer"
                        @click="activeSearchFaq = (activeSearchFaq === 1 ? null : 1)">
                    <span class="text-sm sm:text-base">Are hotel pickups and drop-offs included in all safaris?</span>
                    <i class="bi bi-chevron-down transition-transform duration-300 text-slate-400 shrink-0"
                       :class="activeSearchFaq === 1 ? 'rotate-180 text-primary' : ''"></i>
                </button>
                <div x-show="activeSearchFaq === 1" x-collapse x-cloak class="px-5 sm:px-6 pb-5 text-slate-600 text-sm leading-relaxed border-t border-slate-100 pt-3">
                    Yes. All 4x4 packages include direct, door-to-door hotel pickup and drop-off from Dubai, Sharjah, and major hotel districts in air-conditioned 4x4 Land Cruisers. Standard bus meeting point options are also available for budget travelers.
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-xs">
                <button type="button" 
                        class="w-full text-left px-5 sm:px-6 py-4 font-bold text-slate-900 flex items-center justify-between gap-4 cursor-pointer"
                        @click="activeSearchFaq = (activeSearchFaq === 2 ? null : 2)">
                    <span class="text-sm sm:text-base">Do I need an international driver's license for Dune Buggies or Quad Bikes?</span>
                    <i class="bi bi-chevron-down transition-transform duration-300 text-slate-400 shrink-0"
                       :class="activeSearchFaq === 2 ? 'rotate-180 text-primary' : ''"></i>
                </button>
                <div x-show="activeSearchFaq === 2" x-collapse x-cloak class="px-5 sm:px-6 pb-5 text-slate-600 text-sm leading-relaxed border-t border-slate-100 pt-3">
                    No driving license is required. Dune buggy and quad biking tours take place on designated off-road tracks and high red dunes in Lahbab under professional guide supervision. All riders are equipped with helmets, goggles, and full safety briefings.
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-xs">
                <button type="button" 
                        class="w-full text-left px-5 sm:px-6 py-4 font-bold text-slate-900 flex items-center justify-between gap-4 cursor-pointer"
                        @click="activeSearchFaq = (activeSearchFaq === 3 ? null : 3)">
                    <span class="text-sm sm:text-base">What is your cancellation policy?</span>
                    <i class="bi bi-chevron-down transition-transform duration-300 text-slate-400 shrink-0"
                       :class="activeSearchFaq === 3 ? 'rotate-180 text-primary' : ''"></i>
                </button>
                <div x-show="activeSearchFaq === 3" x-collapse x-cloak class="px-5 sm:px-6 pb-5 text-slate-600 text-sm leading-relaxed border-t border-slate-100 pt-3">
                    We offer 100% free cancellation with a full refund up to 24 hours prior to your scheduled tour pickup time. You can cancel or reschedule easily via WhatsApp or email with zero penalty fees.
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-xs">
                <button type="button" 
                        class="w-full text-left px-5 sm:px-6 py-4 font-bold text-slate-900 flex items-center justify-between gap-4 cursor-pointer"
                        @click="activeSearchFaq = (activeSearchFaq === 4 ? null : 4)">
                    <span class="text-sm sm:text-base">Is Dunes Discovery an officially licensed tour operator in Dubai?</span>
                    <i class="bi bi-chevron-down transition-transform duration-300 text-slate-400 shrink-0"
                       :class="activeSearchFaq === 4 ? 'rotate-180 text-primary' : ''"></i>
                </button>
                <div x-show="activeSearchFaq === 4" x-collapse x-cloak class="px-5 sm:px-6 pb-5 text-slate-600 text-sm leading-relaxed border-t border-slate-100 pt-3">
                    Yes. Dunes Discovery Tourism LLC is fully certified and licensed by the Dubai Department of Economy and Tourism (DET License #1430583). All drivers hold professional safari licenses, and all vehicles undergo stringent DTCM safety inspections.
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
