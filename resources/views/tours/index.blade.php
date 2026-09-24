@extends('layouts.app')

@section('content')
<!-- Page Header Section -->
<section class="py-10 bg-slate-950 text-white relative overflow-hidden" style="margin-top: calc(-1 * var(--header-h, 72px));">
    <div class="absolute inset-0 w-full h-full bg-[radial-gradient(ellipse_at_15%_20%,rgba(246,144,68,0.2)_0%,transparent_60%)]"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 pt-16">
        <nav aria-label="breadcrumb">
            <ol class="flex items-center gap-2 text-xs sm:text-sm text-white/70 mb-4">
                <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a></li>
                <li><span class="text-white/40">/</span></li>
                <li class="text-white font-semibold" aria-current="page">Tours</li>
            </ol>
        </nav>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <div class="flex flex-wrap gap-2 mb-3">
                    <span class="glass rounded-full px-3.5 py-1 text-xs inline-flex items-center gap-1.5">
                        <i class="bi bi-star-fill text-amber-400"></i>Rated 4.9/5 by 2,847+ Travelers
                    </span>
                    <span class="bg-emerald-600/90 rounded-full px-3.5 py-1 text-xs font-semibold text-white inline-flex items-center gap-1.5">
                        <i class="bi bi-patch-check-fill text-emerald-200"></i>DTCM Licensed Operator
                    </span>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-2">
                    @if(request('q'))
                        Search: "{{ request('q') }}"
                    @elseif($selectedCategorySlug)
                        {{ ucwords(str_replace('-', ' ', $selectedCategorySlug)) }}
                    @else
                        Explore Dubai Safari Tours & Experiences
                    @endif
                </h1>
                <p class="text-sm sm:text-base text-white/80 max-w-2xl leading-relaxed">Discover top-rated desert adventures, high-power dune buggies, skyline dhow cruises & city tours.</p>
            </div>
            <div class="hidden lg:block shrink-0">
                <span class="bg-primary/20 text-primary border border-primary/40 px-4 py-2 rounded-full font-bold text-sm inline-flex items-center gap-1.5">
                    <i class="bi bi-shield-check"></i>Best Price Guarantee
                </span>
            </div>
        </div>
    </div>
</section>

<!-- Regulatory E-E-A-T & Trust Bar -->
<section class="bg-slate-50 py-3.5 border-b border-slate-200 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <div class="flex items-center gap-2.5">
                <i class="bi bi-patch-check-fill text-primary text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">DTCM Licensed Operator</div>
                    <div class="text-slate-500 text-[11px]">Dubai Tourism Authority</div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <i class="bi bi-arrow-repeat text-emerald-600 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">100% Free Cancellation</div>
                    <div class="text-slate-500 text-[11px]">Full refund 24h prior</div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <i class="bi bi-award-fill text-amber-500 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">100% Halal Food</div>
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

<section class="py-10 sm:py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
@php
    $settingsService = app(\App\Services\SettingsService::class);
    $conciergePromoActive = ($settingsService->get('concierge_promo_active', '0') === '1');
    $conciergePromoDiscount = $settingsService->get('concierge_promo_discount', '5');
    $conciergePromoCode = $settingsService->get('concierge_promo_code', 'MATCH5');
@endphp

        <!-- Safari Match Concierge Recommendation Banner -->
        <div class="rounded-2xl p-6 sm:p-8 mb-8 shadow-sm relative overflow-hidden bg-gradient-to-r from-slate-900 via-slate-850 to-slate-900 border border-primary/30">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-6 relative z-10">
                <div class="text-center lg:text-left">
                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 mb-2">
                        <span class="rounded-full px-3 py-1 font-bold text-xs bg-primary/20 border border-primary/40 text-primary inline-flex items-center gap-1">
                            <i class="bi bi-stars"></i> Interactive Concierge
                        </span>
                        @if($conciergePromoActive)
                        <span class="bg-amber-400 text-slate-950 rounded-full px-2.5 py-0.5 text-xs font-bold inline-flex items-center gap-1">
                            <i class="bi bi-gift-fill"></i> {{ $conciergePromoDiscount }}% OFF Match Bonus
                        </span>
                        @endif
                    </div>
                    <h3 class="font-extrabold text-white text-xl sm:text-2xl mb-1.5">Not sure which Dubai Safari to choose?</h3>
                    <p class="text-slate-300 text-xs sm:text-sm max-w-2xl leading-relaxed">
                        @if($conciergePromoActive)
                        Answer 3 quick questions about your group style, timing, and must-have perks. Our <strong>Safari Match Concierge</strong> will recommend your ideal adventure and unlock an instant <strong>{{ $conciergePromoDiscount }}% promo code ({{ $conciergePromoCode }})</strong>.
                        @else
                        Answer 3 quick questions about your group style, timing, and must-have perks. Our <strong>Safari Match Concierge</strong> will instantly recommend your ideal desert adventure tailored to your party.
                        @endif
                    </p>
                </div>
                <div class="shrink-0 w-full sm:w-auto text-center lg:text-right">
                    <button type="button" class="w-full sm:w-auto btn-desert-animated rounded-full px-6 py-3 font-bold text-white text-sm shadow-md inline-flex items-center justify-center gap-2 cursor-pointer" @click="$store.modal.open('safari-matcher')">
                        <i class="bi bi-compass text-base"></i>
                        <span>Launch Safari Concierge</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Interactive Search & Category Filter Controls -->
        <div class="bg-slate-50 rounded-2xl p-4 sm:p-5 mb-8 border border-slate-200/80 shadow-xs">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
                <div class="lg:col-span-4">
                    <div class="relative">
                        <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" id="tourSearchInput" class="w-full rounded-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/40 shadow-xs" placeholder="Search safaris, buggies, cruises..." oninput="handleTourSearch(this.value)">
                    </div>
                </div>
                <div class="lg:col-span-8">
                    <div class="flex gap-2 overflow-x-auto no-scrollbar pb-1">
                        <button onclick="filterTours('')" data-category="" class="filter-btn {{ !$selectedCategorySlug ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-300 hover:border-primary' }} rounded-full px-4 py-2 text-xs sm:text-sm font-semibold flex items-center gap-1.5 shrink-0 whitespace-nowrap cursor-pointer transition-colors">
                            <i class="bi bi-grid-fill"></i> All ({{ $tours->count() }})
                        </button>
                        @foreach($categories as $cat)
                            @php
                                $catCount = $tours->where('category_id', $cat->id)->count();
                                $iconMap = [
                                    'desert-safari' => 'bi-sun-fill',
                                    'city-tour' => 'bi-building-fill',
                                    'water-activity' => 'bi-water',
                                    'day-trip' => 'bi-map-fill'
                                ];
                                $icon = $iconMap[$cat->slug] ?? 'bi-compass-fill';
                            @endphp
                            <button onclick="filterTours('{{ $cat->slug }}')" data-category="{{ $cat->slug }}" class="filter-btn {{ $selectedCategorySlug === $cat->slug ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:border-slate-300' }} rounded-full px-4 py-2 text-xs sm:text-sm font-semibold flex items-center gap-1.5 shrink-0 whitespace-nowrap cursor-pointer transition-colors">
                                <i class="bi {{ $icon }}"></i> {{ $cat->name }} ({{ $catCount }})
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        @if($tours->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="tours-grid">
            @foreach($tours as $t)
                @php
                    $minPrice = $t->tiers->min('pivot.price') ?? 0;
                    $tourCat = $categories->firstWhere('id', $t->category_id);
                    $tourCatSlug = $tourCat ? $tourCat->slug : '';
                @endphp
                <div class="tour-item flex flex-col h-full" data-category="{{ $tourCatSlug }}" data-name="{{ strtolower($t->name) }}">
                    <article class="bg-white rounded-2xl overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 border border-slate-200 hover:border-slate-300 flex flex-col h-full group">
                        <a href="{{ route('tours.show', $t->slug) }}" class="flex flex-col h-full text-inherit">
                            <div class="relative overflow-hidden aspect-[16/10]">
                                <img src="{{ asset('images/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $t->thumb_image)) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $t->name }} Dubai" loading="lazy">
                                @if($t->is_bestseller)
                                <span class="absolute top-3 left-3 bg-primary text-white text-xs font-bold px-3 py-1 rounded-full shadow-md inline-flex items-center gap-1">
                                    <i class="bi bi-fire text-amber-300"></i>Best Seller
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
                                        <i class="bi bi-clock text-primary"></i>{{ $t->duration }}
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

                                <div class="flex justify-between items-end mt-auto pt-3 border-t border-slate-200">
                                    <div>
                                        <span class="block text-[10px] uppercase font-bold text-slate-500">Starting from</span>
                                        <span class="text-lg font-black text-primary" data-aed="{{ $minPrice }}">AED {{ number_format($minPrice) }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <button type="button" class="border border-slate-300 hover:border-primary text-slate-700 bg-white hover:text-primary text-xs font-semibold rounded-full px-2.5 py-1 transition-colors btn-toggle-compare inline-flex items-center gap-1 cursor-pointer" data-tour-id="{{ $t->id }}" onclick="event.preventDefault(); event.stopPropagation(); window.DunesCompare && window.DunesCompare.toggle(this);">
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

        <div id="no-tours-message" class="text-center py-12" style="display: none;">
            <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl text-slate-500">
                <i class="bi bi-search"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 mb-2">No Tours Found</h2>
            <p class="text-slate-500 mb-6 text-sm">We couldn't find any tours matching your criteria.</p>
            <button onclick="resetFilters()" class="btn-desert-animated-dark font-bold text-white text-sm rounded-full px-6 py-3 cursor-pointer shadow-md">View All Tours</button>
        </div>
        @else
        <div class="text-center py-12">
            <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl text-slate-500">
                <i class="bi bi-search"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 mb-2">No Tours Found</h2>
            <p class="text-slate-500 mb-6 text-sm">We couldn't find any tours matching your search query. Try exploring all our amazing experiences!</p>
            <a href="{{ route('tours.index') }}" class="btn-desert-animated-dark font-bold text-white text-sm rounded-full px-6 py-3 inline-block shadow-md">View All Tours</a>
        </div>
        @endif
    </div>
</section>

<!-- GEO & AI Direct-Answer Catalog Buyer's Guide -->
<section class="py-12 sm:py-16 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10 sm:mb-12">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-3 tracking-tight">
                Dubai Tour Selection <span class="text-primary">Guide & FAQ</span>
            </h2>
            <p class="text-slate-600 text-base sm:text-lg max-w-xl mx-auto leading-relaxed">
                Expert tips to help you choose the best desert safari or city adventure for your group.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2.5 mb-2.5">
                    <i class="bi bi-sun-fill text-amber-500 text-xl"></i>
                    <h3 class="text-base font-bold text-slate-900">Best for First-Timers</h3>
                </div>
                <p class="text-slate-600 text-sm leading-relaxed">The <strong>Evening Desert Safari</strong> offers the complete Dubai experience: dune bashing in Lahbab Red Dunes, camel ride, sandboarding, 5-star live BBQ dinner, and live shows.</p>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2.5 mb-2.5">
                    <i class="bi bi-speedometer2 text-primary text-xl"></i>
                    <h3 class="text-base font-bold text-slate-900">Best for Thrill-Seekers</h3>
                </div>
                <p class="text-slate-600 text-sm leading-relaxed">Choose our <strong>1000cc Dune Buggy (Can-Am / Polaris)</strong> or <strong>Quad Biking ATV tours</strong> for self-drive high-speed excitement across open dunes with full safety gear included.</p>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2.5 mb-2.5">
                    <i class="bi bi-star-fill text-amber-500 text-xl"></i>
                    <h3 class="text-base font-bold text-slate-900">Best for Luxury & VIPs</h3>
                </div>
                <p class="text-slate-600 text-sm leading-relaxed">Book the <strong>VIP Chalet Desert Safari</strong> or <strong>Marina Catamaran Dinner Cruise</strong> featuring private air-conditioned seating, dedicated waiter service, and gourmet cuisine.</p>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
let currentCategory = '';
let currentSearch = '';

function applyTourFilters() {
    const items = document.querySelectorAll('.tour-item');
    let hasVisible = false;

    items.forEach(item => {
        const itemCat = (item.dataset.category || '').toLowerCase();
        const itemName = (item.dataset.name || '').toLowerCase();

        const matchesCat = !currentCategory || itemCat === currentCategory.toLowerCase();
        const matchesSearch = !currentSearch || itemName.includes(currentSearch.toLowerCase());

        if (matchesCat && matchesSearch) {
            item.style.display = 'flex';
            hasVisible = true;
        } else {
            item.style.display = 'none';
        }
    });

    const noResults = document.getElementById('no-tours-message');
    if (noResults) {
        noResults.style.display = hasVisible ? 'none' : 'block';
    }
}

function filterTours(category) {
    currentCategory = category;
    document.querySelectorAll('.filter-btn').forEach(btn => {
        if (btn.dataset.category === category) {
            btn.classList.remove('bg-white', 'text-slate-700', 'border', 'border-slate-200');
            btn.classList.add('bg-slate-900', 'text-white', 'shadow-xs');
        } else {
            btn.classList.remove('bg-slate-900', 'text-white', 'shadow-xs');
            btn.classList.add('bg-white', 'text-slate-700', 'border', 'border-slate-200');
        }
    });

    const url = new URL(window.location);
    if (category) {
        url.searchParams.set('category', category);
    } else {
        url.searchParams.delete('category');
    }
    window.history.pushState({}, '', url);

    applyTourFilters();
}

function handleTourSearch(val) {
    currentSearch = (val || '').trim();
    applyTourFilters();
}

function resetFilters() {
    const input = document.getElementById('tourSearchInput');
    if (input) input.value = '';
    currentSearch = '';
    filterTours('');
}

window.addEventListener('popstate', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const cat = urlParams.get('category') || '';
    filterTours(cat);
});
</script>
@endpush

@push('preloads')
<!-- Schema.org 2026 CollectionPage, ItemList & BreadcrumbList -->
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@graph": [
    {
      "@type": "CollectionPage",
      "@id": "{{ route('tours.index') }}#webpage",
      "url": "{{ route('tours.index') }}",
      "name": "Dubai Desert Safari Tours & City Experiences | Dunes Discovery",
      "description": "Browse and book the best Dubai desert safari tours, dune buggy rentals, quad biking, and dhow cruise dinners with Dunes Discovery Tourism.",
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
          }
        ]
      },
      "mainEntity": {
        "@type": "ItemList",
        "numberOfItems": {{ $tours->count() }},
        "itemListElement": [
          @foreach($tours as $idx => $t)
          {
            "@type": "ListItem",
            "position": {{ $idx + 1 }},
            "name": {!! json_encode($t->name) !!},
            "url": "{{ route('tours.show', $t->slug) }}"
          }{{ $idx < $tours->count() - 1 ? ',' : '' }}
          @endforeach
        ]
      }
    }
  ]
}
</script>
@endpush
@endsection
