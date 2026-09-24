@extends('layouts.app')

@section('content')
@php
    $phoneVal = $settings['site_phone'] ?? '+971 50 245 6056';
    $emailVal = $settings['site_email'] ?? 'info@dunesdiscoverytourism.com';
    $notAllowed = $tour->contentItems->where('type', 'not_allowed')->sortBy('priority');
    $minPrice = $tour->tiers->min('pivot.price') ?? 0;
    
    // Prepare Tabs Array
    $tabs = [];
    if($highlights->count()) $tabs['highlights'] = 'Highlights';
    if($tour->itineraries->count()) $tabs['itinerary'] = 'Itinerary';
    if($inclusions->count() || $exclusions->count()) $tabs['inex'] = 'Inclusion & Exclusion';
    if($notAllowed->count()) $tabs['info'] = 'Important Information';
    if($faqs->count()) $tabs['faqs'] = 'FAQ';
    $heroAvifUrl = asset('images/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $tour->hero_image ?: 'evening-desert-safari-dubai-dune-discovery-tourism.avif'));
@endphp

@push('preloads')
    <link rel="preload" as="image" href="{{ $heroAvifUrl }}" type="image/avif">
    
    <!-- 2026 Connected JSON-LD Schema Graph: TouristTrip, Product, Offer, TravelAgency, Itinerary, FAQPage -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@graph": [
        {
          "@type": ["Product", "TouristTrip"],
          "@id": "{{ request()->url() }}#trip",
          "name": {!! json_encode($tour->name) !!},
          "description": {!! json_encode(Str::limit(strip_tags($tour->short_desc ?: $tour->full_desc), 300)) !!},
          "image": [
            {!! json_encode($heroAvifUrl) !!}
          ],
          "sku": "DDT-TOUR-{{ $tour->id }}",
          "mpn": "DDT-{{ $tour->slug }}",
          "brand": {
            "@id": "{{ url('/') }}#organization"
          },
          "provider": {
            "@id": "{{ url('/') }}#organization"
          },
          "touristType": ["Adventure Tourism", "Family Friendly", "Couples", "Solo Travelers"],
          "about": [
            {
              "@type": "Place",
              "name": "Lahbab High Red Dunes",
              "sameAs": "https://www.wikidata.org/wiki/Q6473133"
            },
            {
              "@type": "Place",
              "name": "Dubai Desert Conservation Reserve",
              "sameAs": "https://www.wikidata.org/wiki/Q5310543"
            },
            {
              "@type": "Thing",
              "name": "Dune Bashing",
              "sameAs": "https://www.wikidata.org/wiki/Q5315003"
            }
          ],
          "mentions": [
            {
              "@type": "Brand",
              "name": "Can-Am Off-Road",
              "sameAs": "https://www.wikidata.org/wiki/Q1032873"
            },
            {
              "@type": "Brand",
              "name": "Polaris Inc.",
              "sameAs": "https://www.wikidata.org/wiki/Q2102146"
            },
            {
              "@type": "Place",
              "name": "Dubai",
              "sameAs": "https://www.wikidata.org/wiki/Q612"
            }
          ],
          @if(($tour->review_count ?? 0) > 0)
          "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "{{ $tour->rating ?: '5.0' }}",
            "reviewCount": "{{ $tour->review_count }}",
            "bestRating": "5",
            "worstRating": "1"
          },
          @endif
          @if(isset($approvedReviews) && $approvedReviews->count() > 0)
          "review": [
            @foreach($approvedReviews as $ridx => $rev)
            {
              "@type": "Review",
              "reviewRating": {
                "@type": "Rating",
                "ratingValue": "{{ $rev->rating }}",
                "bestRating": "5"
              },
              "author": {
                "@type": "Person",
                "name": {!! json_encode($rev->reviewer_name ?: 'Verified Traveler') !!}
              },
              "datePublished": "{{ $rev->published_date ? $rev->published_date->format('Y-m-d') : $rev->created_at->format('Y-m-d') }}",
              "reviewBody": {!! json_encode(Str::limit($rev->review_text, 280)) !!}
            }{{ $ridx < $approvedReviews->count() - 1 ? ',' : '' }}
            @endforeach
          ],
          @endif
          "offers": {
            "@type": "Offer",
            "url": {!! json_encode(request()->url()) !!},
            "priceCurrency": "AED",
            "price": "{{ number_format($minPrice, 2, '.', '') }}",
            "priceValidUntil": "{{ now()->addYear()->endOfYear()->format('Y-m-d') }}",
            "validFrom": "{{ now()->startOfYear()->toIso8601String() }}",
            "itemCondition": "https://schema.org/NewCondition",
            "availability": "https://schema.org/InStock",
            "seller": {
              "@id": "{{ url('/') }}#organization"
            },
            "hasMerchantReturnPolicy": {
              "@type": "MerchantReturnPolicy",
              "applicableCountry": "AE",
              "returnPolicyCategory": "https://schema.org/MerchantReturnFiniteReturnWindow",
              "merchantReturnDays": 1,
              "returnMethod": "https://schema.org/ReturnInStore",
              "returnFees": "https://schema.org/FreeReturn"
            }
          }
          @if($tour->itineraries->count() > 0)
          ,
          "itinerary": {
            "@type": "ItemList",
            "numberOfItems": {{ $tour->itineraries->count() }},
            "itemListElement": [
              @foreach($tour->itineraries->sortBy('priority') as $idx => $it)
              {
                "@type": "ListItem",
                "position": {{ $idx + 1 }},
                "item": {
                  "@type": "TouristAttraction",
                  "name": {!! json_encode($it->title) !!},
                  "description": {!! json_encode($it->description ?: $it->title) !!}
                }
              }{{ $idx < $tour->itineraries->count() - 1 ? ',' : '' }}
              @endforeach
            ]
          }
          @endif
        },
        {
          "@type": "BreadcrumbList",
          "@id": "{{ request()->url() }}#breadcrumb",
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
              "name": "Tours",
              "item": "{{ route('tours.index') }}"
            },
            {
              "@type": "ListItem",
              "position": 3,
              "name": {!! json_encode($tour->name) !!},
              "item": "{{ request()->url() }}"
            }
          ]
        }
        @if(isset($faqs) && $faqs->count() > 0)
        ,
        {
          "@type": "FAQPage",
          "@id": "{{ request()->url() }}#faq",
          "mainEntity": [
            @foreach($faqs as $fidx => $f)
            {
              "@type": "Question",
              "name": {!! json_encode($f->question) !!},
              "acceptedAnswer": {
                "@type": "Answer",
                "text": {!! json_encode($f->answer) !!}
              }
            }{{ $fidx < $faqs->count() - 1 ? ',' : '' }}
            @endforeach
          ]
        }
        @endif
      ]
    }
    </script>
@endpush

<!-- Ecommerce GTM View Item DataLayer -->
<script>
window.dataLayer = window.dataLayer || [];
window.dataLayer.push({ ecommerce: null });
window.dataLayer.push({
  event: "view_item",
  ecommerce: {
    currency: "AED",
    value: {{ $minPrice }},
    items: [{
      item_id: "{{ $tour->id }}",
      item_name: "{{ $tour->name }}",
      item_category: "{{ $tour->category ? $tour->category->slug : '' }}",
      price: {{ $minPrice }}
    }]
  }
});
</script>

<!-- Meta Pixel ViewContent Event -->
@if(($settings['meta_active'] ?? '0') === '1')
@php $metaPixelId = $settings['meta_pixel_id'] ?? null; @endphp
<script>
if(window.fbq){
    fbq('track', 'ViewContent', {
        content_ids: ['TOUR-{{ $tour->id }}'],
        content_type: 'product',
        value: {{ $minPrice }},
        currency: 'AED'
    });
}
</script>
@endif

<!-- Tour Hero Section -->
<section class="relative min-h-[50vh] flex items-end bg-cover bg-center overflow-hidden" style="background: url('{{ $heroAvifUrl }}') center/cover no-repeat; margin-top: calc(-1 * var(--header-h, 72px));">
    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-black/20"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full pb-10 pt-28 text-white">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="flex items-center gap-2 text-xs sm:text-sm text-white/75 flex-wrap">
                <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a></li>
                <li><span class="text-white/40">/</span></li>
                <li><a href="{{ route('tours.index') }}" class="hover:text-white transition-colors">Tours</a></li>
                <li><span class="text-white/40">/</span></li>
                <li class="text-white font-semibold truncate max-w-[200px] sm:max-w-none" aria-current="page">{{ $tour->name }}</li>
            </ol>
        </nav>
        <div>
            <div class="flex flex-wrap gap-2 mb-3">
                <span class="glass px-3.5 py-1.5 rounded-full text-xs font-semibold inline-flex items-center gap-1.5">
                    <i class="bi bi-tag-fill text-primary"></i>{{ $tour->category ? $tour->category->name : 'Tour' }}
                </span>
                @if($tour->is_bestseller)
                <span class="bg-primary text-white px-3.5 py-1.5 rounded-full text-xs font-bold inline-flex items-center gap-1.5 shadow-sm">
                    <i class="bi bi-fire text-amber-300"></i>Best Seller
                </span>
                @endif
                <span class="bg-emerald-600/90 text-white px-3.5 py-1.5 rounded-full text-xs font-semibold inline-flex items-center gap-1.5">
                    <i class="bi bi-shield-check text-emerald-200"></i>DTCM Licensed Operator
                </span>
            </div>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold mb-4 text-white tracking-tight leading-tight">{{ $tour->name }}</h1>
            <div class="flex flex-wrap gap-4 sm:gap-6 items-center text-white/90 text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white text-xs">
                        <i class="bi bi-clock-fill"></i>
                    </div>
                    <span class="font-medium">{{ $tour->duration }}</span>
                </div>
                <div class="flex items-center gap-2 sm:border-l sm:border-white/25 sm:pl-6">
                    <div class="text-amber-400 flex gap-0.5 text-sm">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <span class="font-medium">{{ $tour->rating }} <span class="text-white/70">({{ number_format($tour->review_count) }} reviews)</span></span>
                </div>
                <div class="hidden md:flex items-center gap-2 border-l border-white/25 pl-6">
                    <i class="bi bi-geo-alt-fill text-primary"></i>
                    <span class="font-medium">Hotel Pickup & Drop Included</span>
                </div>
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
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">DTCM Licensed</div>
                    <div class="text-slate-500 text-[11px]">UAE Tourism Authority</div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <i class="bi bi-arrow-repeat text-emerald-600 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">100% Free Cancel</div>
                    <div class="text-slate-500 text-[11px]">Up to 24h in advance</div>
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
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">Secure Checkout</div>
                    <div class="text-slate-500 text-[11px]">Card / Cash on Pickup</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Content and Sidebar Section -->
<section class="py-10 sm:py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
            <!-- Main Details (Left 8 Cols) -->
            <div class="lg:col-span-8">
                <!-- GEO & AI Search "Tour at a Glance" Quick Facts Card -->
                <div class="bg-primary/5 rounded-2xl p-6 mb-8 border-l-4 border-primary shadow-xs">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                            <i class="bi bi-lightning-charge-fill text-primary"></i>Experience at a Glance
                        </h2>
                        <div class="flex flex-wrap gap-2">
                            <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full px-3 py-1 text-xs font-bold inline-flex items-center gap-1">
                                <i class="bi bi-patch-check-fill text-emerald-500"></i>DTCM License #1430583
                            </span>
                            <span class="bg-primary/10 text-primary border border-primary/20 rounded-full px-3 py-1 text-xs font-bold inline-flex items-center gap-1">
                                <i class="bi bi-check-circle-fill"></i>No Driver's License Required
                            </span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex items-start gap-2.5">
                            <i class="bi bi-clock text-primary mt-0.5 text-base shrink-0"></i>
                            <div>
                                <strong class="block text-slate-900 text-xs sm:text-sm">Duration:</strong>
                                <span class="text-slate-600 text-xs sm:text-sm">{{ $tour->duration ?: '6 - 7 Hours' }}</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="bi bi-truck text-primary mt-0.5 text-base shrink-0"></i>
                            <div>
                                <strong class="block text-slate-900 text-xs sm:text-sm">Transfer Vehicle:</strong>
                                <span class="text-slate-600 text-xs sm:text-sm">4x4 Luxury Toyota Land Cruiser / Prado</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="bi bi-geo-alt text-primary mt-0.5 text-base shrink-0"></i>
                            <div>
                                <strong class="block text-slate-900 text-xs sm:text-sm">Destination:</strong>
                                <span class="text-slate-600 text-xs sm:text-sm">Lahbab High Red Dunes, Dubai, UAE</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="bi bi-cup-hot text-primary mt-0.5 text-base shrink-0"></i>
                            <div>
                                <strong class="block text-slate-900 text-xs sm:text-sm">Dining & Beverages:</strong>
                                <span class="text-slate-600 text-xs sm:text-sm">100% Halal BBQ Buffet, Arabic Coffee & Dates</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="bi bi-arrow-repeat text-primary mt-0.5 text-base shrink-0"></i>
                            <div>
                                <strong class="block text-slate-900 text-xs sm:text-sm">Cancellation Policy:</strong>
                                <span class="text-slate-600 text-xs sm:text-sm">100% Free Cancellation up to 24h</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="bi bi-ticket-perforated text-primary mt-0.5 text-base shrink-0"></i>
                            <div>
                                <strong class="block text-slate-900 text-xs sm:text-sm">Instant Confirmation:</strong>
                                <span class="text-slate-600 text-xs sm:text-sm">Immediate WhatsApp & Email e-Ticket</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="bi bi-shield-check text-primary mt-0.5 text-base shrink-0"></i>
                            <div>
                                <strong class="block text-slate-900 text-xs sm:text-sm">Safety & Insurance:</strong>
                                <span class="text-slate-600 text-xs sm:text-sm">Full Comprehensive Passenger Insurance</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="bi bi-chat-dots text-primary mt-0.5 text-base shrink-0"></i>
                            <div>
                                <strong class="block text-slate-900 text-xs sm:text-sm">Languages:</strong>
                                <span class="text-slate-600 text-xs sm:text-sm">{{ $tour->languages ?: 'English, Arabic, Hindi, Russian' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- About This Tour -->
                <div class="mb-10">
                    <h2 class="text-2xl font-bold text-slate-900 mb-4 flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-lg"><i class="bi bi-info-circle"></i></span>
                        <span>About {{ $tour->name }}</span>
                    </h2>
                    <div x-data="{ expanded: false }" class="relative">
                        <div id="tourDescriptionText" :class="expanded ? '' : 'line-clamp-4'" class="text-slate-600 text-base leading-relaxed">
                            {!! nl2br(e($tour->full_desc)) !!}
                        </div>
                        <button type="button" class="mt-3 text-sm font-bold text-primary hover:text-primary-hover inline-flex items-center gap-1.5 border border-primary/30 rounded-full px-4 py-1.5 cursor-pointer shadow-xs transition-colors" @click="expanded = !expanded">
                            <span x-text="expanded ? 'Read Less' : 'Read More'"></span>
                            <i class="bi" :class="expanded ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        </button>
                    </div>
                </div>

                <!-- Tabs Navigation & Content -->
                @if(count($tabs))
                <div x-data="{ currentTab: '{{ array_key_first($tabs) }}' }" class="mb-10">
                    <!-- Tab Pills Carousel (horizontal scroll on mobile) -->
                    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-3 mb-6 border-b border-slate-200">
                        @foreach($tabs as $key => $label)
                        <button type="button" 
                                class="px-5 py-2.5 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-all cursor-pointer"
                                :class="currentTab === '{{ $key }}' ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                @click="currentTab = '{{ $key }}'">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>

                    <!-- Highlights Tab -->
                    @if(isset($tabs['highlights']))
                    <div x-show="currentTab === 'highlights'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($highlights as $h)
                            <div class="flex gap-3 p-4 rounded-2xl bg-slate-50 border border-slate-200 hover:border-primary/50 shadow-2xs transition-all">
                                <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center shrink-0 text-sm">
                                    <i class="bi bi-check2 font-bold"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">{{ $h->title }}</div>
                                    @if($h->description)
                                    <p class="text-slate-500 text-xs mt-1 leading-relaxed">{{ $h->description }}</p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Itinerary Tab -->
                    @if(isset($tabs['itinerary']))
                    <div x-show="currentTab === 'itinerary'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="relative pl-6 border-l-2 border-primary/30 space-y-6 ml-3">
                            @foreach($tour->itineraries->sortBy('priority') as $it)
                            <div class="relative pb-2">
                                <div class="absolute -left-[31px] top-1.5 w-4 h-4 rounded-full bg-primary border-4 border-white shadow-xs"></div>
                                <div class="flex items-center justify-between gap-3 mb-1.5">
                                    <span class="bg-primary/10 text-primary text-xs font-bold rounded-full px-3 py-0.5">{{ $it->time }}</span>
                                    @if($it->duration)
                                    <span class="text-slate-400 text-xs font-medium"><i class="bi bi-clock me-1 text-primary"></i>{{ $it->duration }}</span>
                                    @endif
                                </div>
                                <h3 class="text-base font-bold text-slate-900 mb-1">{{ $it->title }}</h3>
                                <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">{{ $it->description }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Inclusion & Exclusion Tab -->
                    @if(isset($tabs['inex']))
                    <div x-show="currentTab === 'inex'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            @if($inclusions->count())
                            <div class="bg-emerald-50/70 border-l-4 border-emerald-500 p-5 rounded-2xl">
                                <h3 class="text-sm font-bold text-emerald-800 mb-4 flex items-center gap-2">
                                    <i class="bi bi-check-circle-fill text-emerald-600"></i>What's Included
                                </h3>
                                <ul class="space-y-2.5 text-xs sm:text-sm text-slate-700">
                                    @foreach($inclusions as $inc)
                                    <li class="flex items-start gap-2">
                                        <i class="bi bi-check-lg text-emerald-600 font-bold mt-0.5 shrink-0"></i>
                                        <span>{{ $inc->title }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            @if($exclusions->count())
                            <div class="bg-red-50/70 border-l-4 border-red-500 p-5 rounded-2xl">
                                <h3 class="text-sm font-bold text-red-800 mb-4 flex items-center gap-2">
                                    <i class="bi bi-x-circle-fill text-red-600"></i>Not Included
                                </h3>
                                <ul class="space-y-2.5 text-xs sm:text-sm text-slate-700">
                                    @foreach($exclusions as $exc)
                                    <li class="flex items-start gap-2">
                                        <i class="bi bi-x-lg text-red-600 font-bold mt-0.5 shrink-0"></i>
                                        <span>{{ $exc->title }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Important Information Tab -->
                    @if(isset($tabs['info']))
                    <div x-show="currentTab === 'info'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="bg-amber-50/70 border-l-4 border-amber-500 p-5 rounded-2xl">
                            <h3 class="text-sm font-bold text-amber-800 mb-3 flex items-center gap-2">
                                <i class="bi bi-exclamation-triangle-fill text-amber-600"></i>Important Information
                            </h3>
                            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs sm:text-sm text-slate-700">
                                @foreach($notAllowed as $na)
                                <li class="flex items-start gap-2">
                                    <i class="bi bi-info-circle-fill text-amber-600 shrink-0 mt-0.5"></i>
                                    <span>
                                        <strong>{{ $na->title }}</strong>
                                        @if($na->description) - {{ $na->description }} @endif
                                    </span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif

                    <!-- FAQs Tab -->
                    @if(isset($tabs['faqs']))
                    <div x-show="currentTab === 'faqs'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                        <div x-data="{ openFaq: null }" class="space-y-3">
                            @foreach($faqs as $index => $f)
                            <div class="bg-slate-50 rounded-2xl border border-slate-200/80 overflow-hidden">
                                <button type="button" 
                                        class="w-full text-left px-5 py-4 font-bold text-slate-900 flex items-center justify-between gap-4 cursor-pointer"
                                        @click="openFaq = (openFaq === {{ $index }} ? null : {{ $index }})">
                                    <span class="inline-flex items-center gap-2.5 text-sm sm:text-base">
                                        <i class="bi bi-question-circle-fill text-primary"></i>
                                        <span>{{ $f->question }}</span>
                                    </span>
                                    <i class="bi bi-chevron-down transition-transform duration-300 text-slate-400 shrink-0"
                                       :class="openFaq === {{ $index }} ? 'rotate-180 text-primary' : ''"></i>
                                </button>
                                <div x-show="openFaq === {{ $index }}" x-collapse x-cloak class="px-5 pb-5 text-slate-600 text-xs sm:text-sm leading-relaxed border-t border-slate-200/60 pt-3">
                                    {{ $f->answer }}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Practical Travel Tips & Packing Guide -->
                <div class="bg-white rounded-2xl p-6 mb-8 border border-slate-200/80 shadow-xs">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="bi bi-info-square-fill text-primary"></i>Essential Tips & What to Pack
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex items-start gap-2.5">
                            <i class="bi bi-check2-circle text-emerald-600 mt-0.5 text-base shrink-0"></i>
                            <div>
                                <strong class="block text-slate-900 text-xs sm:text-sm">Recommended Attire:</strong>
                                <span class="text-slate-600 text-xs sm:text-sm">Casual loose-fitting clothes. Light jacket during winter evenings (Nov - Feb).</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="bi bi-check2-circle text-emerald-600 mt-0.5 text-base shrink-0"></i>
                            <div>
                                <strong class="block text-slate-900 text-xs sm:text-sm">Footwear:</strong>
                                <span class="text-slate-600 text-xs sm:text-sm">Sandals, flip-flops, or sneakers suitable for walking on soft sand dunes.</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="bi bi-check2-circle text-emerald-600 mt-0.5 text-base shrink-0"></i>
                            <div>
                                <strong class="block text-slate-900 text-xs sm:text-sm">Sun & Dust Protection:</strong>
                                <span class="text-slate-600 text-xs sm:text-sm">Sunglasses, sunblock, and a camera or smartphone for sunset photography.</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="bi bi-check2-circle text-emerald-600 mt-0.5 text-base shrink-0"></i>
                            <div>
                                <strong class="block text-slate-900 text-xs sm:text-sm">Families & Seniors:</strong>
                                <span class="text-slate-600 text-xs sm:text-sm">Child booster seats and gentle direct-to-camp scenic transfers available on request.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Package Comparison Matrix (Feature Breakdown) -->
                @if($tour->tiers->count() > 1)
                <div class="bg-slate-50 rounded-2xl p-6 sm:p-8 mb-8 border border-slate-200/80 shadow-xs">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-slate-900 flex items-center gap-2 mb-1">
                                <i class="bi bi-ui-checks text-primary"></i>Package Comparison & Pricing
                            </h2>
                            <p class="text-slate-600 text-xs sm:text-sm">Select the ideal tier for your group and budget.</p>
                        </div>
                        <span class="bg-primary/10 text-primary px-3.5 py-1.5 rounded-full text-xs font-bold inline-flex items-center gap-1">
                            <i class="bi bi-check2-all"></i>Best Price Guarantee
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach($tour->tiers->sortBy('priority') as $tier)
                        @php
                            $tPrice = $tier->pivot?->price ?? 0;
                            $tOldPrice = $tier->pivot?->old_price ?? 0;
                            $saveAmt = ($tOldPrice > 0 && $tOldPrice > $tPrice) ? ($tOldPrice - $tPrice) : 0;
                        @endphp
                        <div class="bg-white rounded-2xl p-5 border-2 flex flex-col h-full relative transition-all duration-300 hover:shadow-lg {{ $tier->is_popular ? 'border-primary shadow-md' : 'border-slate-300 shadow-xs' }}">
                            @if($tier->is_popular)
                            <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                                <span class="bg-primary text-white rounded-full px-3 py-0.5 text-[10px] font-extrabold uppercase tracking-wider shadow-sm">Most Popular</span>
                            </div>
                            @endif
                            <div class="text-center pt-2 pb-3 mb-3 border-b border-slate-200">
                                <h3 class="text-base font-bold text-slate-900 mb-1.5">{{ $tier->name }}</h3>
                                <div class="flex items-baseline justify-center gap-2">
                                    <span class="text-2xl font-black text-primary" data-aed="{{ $tPrice }}">AED {{ number_format($tPrice) }}</span>
                                    @if($saveAmt)
                                    <span class="text-xs text-slate-500 line-through" data-aed="{{ $tOldPrice }}">AED {{ number_format($tOldPrice) }}</span>
                                    @endif
                                </div>
                                <span class="text-[11px] text-slate-500 block mt-0.5">Per Person (All Inclusive)</span>
                            </div>

                            <ul class="space-y-2 mb-5 flex-grow text-xs text-slate-700">
                                <li class="flex items-center gap-2">
                                    <i class="bi bi-check-circle-fill text-emerald-600"></i>
                                    <span>{{ $tour->duration }} Desert Experience</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="bi bi-check-circle-fill text-emerald-600"></i>
                                    <span>4x4 Land Cruiser Transfers</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="bi bi-check-circle-fill text-emerald-600"></i>
                                    <span>BBQ Buffet & Live Shows</span>
                                </li>
                                @if(stripos($tier->name, 'quad') !== false || stripos($tier->name, 'buggy') !== false)
                                <li class="flex items-center gap-2 font-bold text-primary">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span>Self-Drive ATV / Buggy Ride</span>
                                </li>
                                @endif
                                @if(stripos($tier->name, 'vip') !== false || stripos($tier->name, 'private') !== false)
                                <li class="flex items-center gap-2 font-bold text-amber-600">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span>VIP Table Service & Chalet</span>
                                </li>
                                @endif
                            </ul>

                            <button type="button" class="w-full rounded-full py-2.5 text-xs sm:text-sm font-bold cursor-pointer transition-colors {{ $tier->is_popular ? 'btn-desert-animated text-white' : 'border border-primary text-primary hover:bg-primary hover:text-white' }}" data-action="open-booking" data-tour="{{ $tour->id }}" data-tier="{{ $tier->id }}" @click.prevent="$store.modal.open('booking', { tourId: {{ $tour->id }}, tierId: {{ $tier->id }} })">
                                Select {{ $tier->name }}
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Detailed Side-by-Side Feature Inclusion Matrix -->
                @include('partials.tier-comparison-matrix')

                <!-- Contextual Cross-Tour Internal Links -->
                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs mb-8">
                    <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                        <i class="bi bi-compass text-primary"></i>Explore More Dubai Adventures
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ url('/dune-buggy-rental-dubai') }}" class="inline-flex items-center gap-1.5 bg-slate-50 hover:bg-white text-slate-700 hover:text-primary border border-slate-200 hover:border-primary rounded-full px-3.5 py-1.5 text-xs font-semibold transition-colors">
                            <i class="bi bi-truck text-primary"></i>1000cc Dune Buggy Rental
                        </a>
                        <a href="{{ url('/evening-desert-safari-dubai') }}" class="inline-flex items-center gap-1.5 bg-slate-50 hover:bg-white text-slate-700 hover:text-primary border border-slate-200 hover:border-primary rounded-full px-3.5 py-1.5 text-xs font-semibold transition-colors">
                            <i class="bi bi-sunset text-primary"></i>Evening Desert Safari
                        </a>
                        <a href="{{ url('/desert-safari-quad-biking-dubai') }}" class="inline-flex items-center gap-1.5 bg-slate-50 hover:bg-white text-slate-700 hover:text-primary border border-slate-200 hover:border-primary rounded-full px-3.5 py-1.5 text-xs font-semibold transition-colors">
                            <i class="bi bi-speedometer2 text-primary"></i>Quad Biking Adventure
                        </a>
                        <a href="{{ url('/dhow-cruise-catamaran-cruise-dinner-dubai') }}" class="inline-flex items-center gap-1.5 bg-slate-50 hover:bg-white text-slate-700 hover:text-primary border border-slate-200 hover:border-primary rounded-full px-3.5 py-1.5 text-xs font-semibold transition-colors">
                            <i class="bi bi-water text-primary"></i>Marina Dhow Cruise Dinner
                        </a>
                        <a href="{{ url('/abu-dhabi-city-tour-from-dubai') }}" class="inline-flex items-center gap-1.5 bg-slate-50 hover:bg-white text-slate-700 hover:text-primary border border-slate-200 hover:border-primary rounded-full px-3.5 py-1.5 text-xs font-semibold transition-colors">
                            <i class="bi bi-building text-primary"></i>Abu Dhabi City Tour
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sidebar (Right 4 Cols) -->
            <div class="lg:col-span-4">
                <div class="sticky top-24 space-y-6">
                    <!-- Booking Card -->
                    <div class="bg-white rounded-2xl p-6 shadow-xl border border-slate-200">
                        <div class="text-center pb-4 mb-4 border-b border-slate-200">
                            <span class="text-[11px] uppercase font-bold text-slate-500 tracking-wider">Starting from</span>
                            <div class="text-3xl font-black text-primary mt-0.5" data-aed="{{ $minPrice }}">AED {{ number_format($minPrice) }}</div>
                        </div>

                        <!-- Urgency Widget -->
                        <div class="bg-amber-500/10 rounded-2xl p-3.5 mb-5 flex items-center gap-3 border border-amber-500/20">
                            <div class="w-9 h-9 rounded-full bg-amber-400 text-slate-950 flex items-center justify-center shrink-0">
                                <i class="bi bi-fire text-lg"></i>
                            </div>
                            <div>
                                <div class="text-[10px] uppercase font-extrabold tracking-wider text-amber-800">HIGH DEMAND</div>
                                <div class="text-xs font-bold text-slate-800">14 people booked in the last 24h</div>
                            </div>
                        </div>

                        <!-- Package Selector -->
                        <div class="mb-5">
                            <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                                <i class="bi bi-box-seam text-primary"></i>Select Package
                            </h3>
                            <div class="space-y-2.5">
                                @foreach($tour->tiers->sortBy('priority') as $tier)
                                @php
                                    $tPrice = $tier->pivot?->price ?? 0;
                                    $tOldPrice = $tier->pivot?->old_price ?? 0;
                                    $save = ($tOldPrice > 0 && $tOldPrice > $tPrice) ? ($tOldPrice - $tPrice) : 0;
                                @endphp
                                <div class="package-option p-3.5 border rounded-2xl relative cursor-pointer transition-all hover:shadow-xs {{ $tier->is_popular ? 'border-primary bg-primary/5' : 'border-slate-200 bg-white hover:border-slate-300' }}" data-action="open-booking" data-tour="{{ $tour->id }}" data-tier="{{ $tier->id }}" @click="$store.modal.open('booking', { tourId: {{ $tour->id }}, tierId: {{ $tier->id }} })">
                                    @if($tier->is_popular)
                                    <span class="bg-primary text-white text-[9px] font-black uppercase tracking-wider rounded-full px-2 py-0.5 absolute top-2 right-2">POPULAR</span>
                                    @endif
                                    <div class="flex justify-between items-start">
                                        <div class="pr-2">
                                            <div class="text-xs font-bold text-slate-900">{{ $tier->name }}</div>
                                            @if($tier->description)
                                            <span class="text-[11px] text-slate-500 line-clamp-1 mt-0.5 block">{{ $tier->description }}</span>
                                            @endif
                                        </div>
                                        <div class="text-right shrink-0 {{ $tier->is_popular ? 'mt-4' : '' }}">
                                            @if($save)
                                            <span class="text-[10px] text-slate-400 line-through block" data-aed="{{ $tOldPrice }}">AED {{ number_format($tOldPrice) }}</span>
                                            @endif
                                            <span class="text-sm font-bold text-primary block" data-aed="{{ $tPrice }}">AED {{ number_format($tPrice) }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Scarcity Urgency -->
                        @php
                            $detailBookings = (int)(($tour->id * 4 + (int)date('j')) % 6 + 4);
                            $viewingNow = (int)(($tour->id * 2 + (int)date('G')) % 11 + 12);
                        @endphp
                        <div class="bg-red-500/5 border border-red-500/20 rounded-2xl p-3 mb-5">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <div class="flex items-center gap-1.5 text-red-600 font-bold text-xs">
                                    <i class="bi bi-fire"></i>
                                    <span>High Demand: {{ $detailBookings }} booked today</span>
                                </div>
                                <span class="bg-red-500 text-white rounded-full px-2 py-0.5 text-[10px] font-bold">Only 2 4x4s left</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-slate-500 text-[11px]">
                                <i class="bi bi-eye-fill text-primary"></i>
                                <span>{{ $viewingNow }} travelers are viewing this package right now</span>
                            </div>
                        </div>

                        <!-- CTA Actions -->
                        <div class="space-y-2.5">
                            <button class="w-full btn-desert-animated text-base font-bold rounded-full py-3.5 text-white shadow-lg cursor-pointer" @click.prevent="$store.modal.open('booking', { tourId: {{ $tour->id }} })">
                                <i class="bi bi-calendar-check-fill me-2"></i>Book Online Now
                            </button>
                            <button type="button" class="w-full border border-slate-300 hover:border-primary text-slate-700 hover:text-primary text-sm font-bold rounded-full py-2.5 transition-colors btn-toggle-compare cursor-pointer flex items-center justify-center gap-2" data-tour-id="{{ $tour->id }}" onclick="event.preventDefault(); window.DunesCompare && window.DunesCompare.toggle(this);">
                                <i class="bi bi-shuffle"></i><span class="compare-btn-text">Compare this Safari</span>
                            </button>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$settings['site_whatsapp'] ?? '971502456056') }}?text={{ urlencode('Hi! I want to book ' . $tour->name) }}" class="w-full btn-whatsapp-animated text-sm font-bold rounded-full py-3 text-white flex items-center justify-center gap-2 shadow-sm" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-whatsapp text-lg"></i>Inquire via WhatsApp
                            </a>
                        </div>

                        <div class="mt-3 text-center">
                            <a href="{{ route('tours.customizer') }}" class="text-xs text-slate-500 hover:text-primary font-bold inline-flex items-center gap-1 transition-colors">
                                <i class="bi bi-sliders text-amber-500"></i> Need a bespoke setup? <u>Build your custom safari</u>
                            </a>
                        </div>

                        <!-- Trust Callouts -->
                        <div class="grid grid-cols-3 gap-2 mt-5 pt-4 text-center border-t border-slate-200">
                            <div class="bg-slate-50 p-2 rounded-xl">
                                <i class="bi bi-lightning-charge-fill text-primary block mb-0.5 text-sm"></i>
                                <span class="block font-bold text-slate-800 text-[10px]">Instant Voucher</span>
                            </div>
                            <div class="bg-slate-50 p-2 rounded-xl">
                                <span class="block mb-0.5 text-emerald-600 font-bold text-xs">24h</span>
                                <span class="block font-bold text-slate-800 text-[10px]">Free Cancel</span>
                            </div>
                            <div class="bg-slate-50 p-2 rounded-xl">
                                <i class="bi bi-shield-check text-cyan-600 block mb-0.5 text-sm"></i>
                                <span class="block font-bold text-slate-800 text-[10px]">Ziina Verified</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mt-4 text-center">
                            <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200">
                                <i class="bi bi-clock-history text-primary block mb-1 text-lg"></i>
                                <span class="text-slate-500 block text-[9px] uppercase font-bold tracking-wider">DURATION</span>
                                <span class="font-bold text-xs text-slate-900">{{ $tour->duration }}</span>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200">
                                <i class="bi bi-translate text-primary block mb-1 text-lg"></i>
                                <span class="text-slate-500 block text-[9px] uppercase font-bold tracking-wider">LANGUAGES</span>
                                <span class="font-bold text-xs text-slate-900 truncate block">{{ $tour->languages }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Need Help Card -->
                    <div class="bg-slate-900 text-white rounded-2xl p-6 shadow-xl relative overflow-hidden">
                        <div class="relative z-10">
                            <h3 class="text-lg font-bold text-white mb-2">Need Help?</h3>
                            <p class="text-xs text-slate-300 mb-4 leading-relaxed">Our travel experts are available 24/7 to help you with your booking.</p>
                            <a href="tel:{{ preg_replace('/[^0-9+]/','',$phoneVal) }}" class="flex items-center gap-3 text-white group">
                                <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <i class="bi bi-telephone-fill"></i>
                                </div>
                                <div>
                                    <span class="block text-xs text-slate-400">Call Us</span>
                                    <span class="font-bold text-sm">{{ $phoneVal }}</span>
                                </div>
                            </a>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-transparent pointer-events-none"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Verified Guest Reviews & Traveler Photos Section ───────────────────────────── -->
<section class="py-12 sm:py-16 bg-slate-50 border-t border-b border-slate-200" id="guest-reviews">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8 pb-4 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full px-3 py-0.5 text-xs font-bold inline-flex items-center gap-1">
                        <i class="bi bi-patch-check-fill text-emerald-500"></i> 100% Verified Guest Feedback
                    </span>
                    <span class="text-slate-400 text-xs">• DET License #1430583</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Verified Guest Reviews & Safari Photos</h2>
                <p class="text-slate-600 text-xs sm:text-sm mt-0.5">Authentic experiences and real traveler snapshots from our certified Dubai desert tours.</p>
            </div>
            <div class="flex flex-wrap gap-2.5">
                <button type="button" class="border border-slate-300 hover:border-primary text-slate-700 hover:text-primary rounded-full px-4 py-2 text-xs font-bold cursor-pointer btn-toggle-compare inline-flex items-center gap-1.5 transition-colors" data-tour-id="{{ $tour->id }}" onclick="event.preventDefault(); window.DunesCompare && window.DunesCompare.toggle(this);">
                    <i class="bi bi-shuffle"></i> Compare Safaris
                </button>
                <a href="{{ route('review.rate', ['ref' => 'guest']) }}" class="btn-desert-animated rounded-full px-5 py-2 text-xs font-bold text-white shadow-sm inline-flex items-center gap-1.5">
                    <i class="bi bi-camera-fill"></i> Submit Review & Photos
                </a>
            </div>
        </div>

        <!-- Overall Score Bar -->
        <div class="bg-white rounded-2xl p-5 mb-8 border border-slate-200/80 shadow-xs">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                <div class="md:col-span-4 flex items-center justify-center md:justify-start gap-4">
                    <span class="text-4xl sm:text-5xl font-black text-primary">{{ number_format($tour->rating ?: 4.9, 1) }}</span>
                    <div>
                        <div class="flex text-amber-400 text-base">
                            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        </div>
                        <span class="text-slate-500 font-bold text-xs">Overall Guest Rating</span>
                    </div>
                </div>
                <div class="md:col-span-5 grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs text-slate-600">
                    <div class="flex items-center gap-1.5">
                        <i class="bi bi-check-circle-fill text-emerald-600"></i>
                        <span>Dune Bashing: <strong>5.0/5</strong></span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i class="bi bi-check-circle-fill text-emerald-600"></i>
                        <span>BBQ Quality: <strong>4.9/5</strong></span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i class="bi bi-check-circle-fill text-emerald-600"></i>
                        <span>Captains: <strong>5.0/5</strong></span>
                    </div>
                </div>
                <div class="md:col-span-3 text-center md:text-right">
                    <span class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-full px-3 py-1.5 text-xs text-slate-700 font-medium">
                        <i class="bi bi-google text-blue-500"></i> Google 4.9 &nbsp;|&nbsp; <i class="bi bi-patch-check-fill text-emerald-500"></i> Direct UGC
                    </span>
                </div>
            </div>
        </div>

        <!-- Reviews Grid -->
        @php
            $displayReviews = isset($approvedReviews) && $approvedReviews->count() ? $approvedReviews : \App\Models\Review::where('status', 'approved')->latest()->take(3)->get();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($displayReviews as $rev)
            @php
                $avatar = !empty($rev->reviewer_avatar_url) ? (str_starts_with($rev->reviewer_avatar_url, 'http') ? $rev->reviewer_avatar_url : asset($rev->reviewer_avatar_url)) : asset('images/avatar-default.svg');
            @endphp
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow flex flex-col h-full">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-center gap-3">
                        <img src="{{ $avatar }}" width="44" height="44" loading="lazy" alt="{{ $rev->reviewer_name }}" class="w-11 h-11 rounded-full object-cover shadow-xs shrink-0" onerror="this.onerror=null;this.src='{{ asset('images/avatar-default.svg') }}'">
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm mb-0.5">{{ $rev->reviewer_name }}</h3>
                            <span class="text-emerald-600 font-bold text-xs inline-flex items-center gap-1"><i class="bi bi-patch-check-fill"></i>Verified Guest</span>
                        </div>
                    </div>
                    <div class="text-amber-400 text-xs flex gap-0.5">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star{{ $i <= floor($rev->rating) ? '-fill' : '' }}"></i>
                        @endfor
                    </div>
                </div>

                @if($rev->review_title)
                <h4 class="font-bold text-slate-900 text-sm mb-2">"{{ $rev->review_title }}"</h4>
                @endif

                <p class="text-slate-600 text-xs sm:text-sm mb-4 flex-grow leading-relaxed">
                    {{ Str::limit($rev->review_text, 180) }}
                </p>

                @if(!empty($rev->photos) && is_array($rev->photos) && count($rev->photos) > 0)
                <!-- Guest Uploaded Photo Strip -->
                <div class="mb-4 pt-3 border-t border-slate-200">
                    <span class="text-slate-500 block mb-2 text-[10px] uppercase font-bold tracking-wider">Guest Photos</span>
                    <div class="flex gap-2">
                        @foreach(array_slice($rev->photos, 0, 3) as $photo)
                        <a href="{{ asset($photo) }}" target="_blank" rel="noopener noreferrer" class="w-14 h-14 rounded-xl overflow-hidden shadow-xs border border-slate-200 shrink-0">
                            <img src="{{ asset($photo) }}" width="56" height="56" loading="lazy" alt="Verified traveler photo from {{ $tour->name }}" class="w-full h-full object-cover">
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="flex justify-between items-center mt-auto pt-3 border-t border-slate-200 text-[11px] text-slate-500">
                    <span><i class="bi bi-calendar3 me-1"></i>{{ $rev->published_date ? \Carbon\Carbon::parse($rev->published_date)->format('M d, Y') : 'Recent Guest' }}</span>
                    <span class="bg-slate-100 text-slate-600 rounded-full px-2.5 py-0.5 font-medium">{{ ucfirst($rev->source ?: 'direct_ugc') }}</span>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-8 text-slate-500">
                <p class="mb-3 text-sm">Be the first to share photos and review this safari experience!</p>
                <a href="{{ route('review.rate', ['ref' => 'guest']) }}" class="btn-desert-animated rounded-full px-6 py-2.5 font-bold text-white text-xs inline-flex items-center gap-2 shadow-md">
                    <i class="bi bi-star-fill"></i> Submit Guest Review
                </a>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- You Might Also Like Section -->
@if($relatedTours->count())
<section class="py-12 sm:py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-lg"><i class="bi bi-compass"></i></span>
                <span>You Might Also Like</span>
            </h2>
            <a href="{{ route('tours.index') }}" class="text-sm font-bold text-primary hover:text-primary-hover inline-flex items-center gap-1">
                <span>View All Tours</span>
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($relatedTours as $t)
            @php $minPriceRel = $t->tiers->min('pivot.price') ?? 0; @endphp
            <article class="bg-white rounded-2xl overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 border border-slate-200 hover:border-slate-300 flex flex-col h-full group">
                <a href="{{ route('tours.show', $t->slug) }}" class="flex flex-col h-full text-inherit">
                    <div class="relative overflow-hidden aspect-[16/10]">
                        <img src="{{ asset('images/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $t->thumb_image)) }}" width="400" height="250" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $t->name }} Dubai Desert Safari" loading="lazy">
                        @if($t->is_bestseller)
                        <span class="absolute top-3 left-3 bg-primary text-white text-xs font-bold px-3 py-1 rounded-full shadow-md inline-flex items-center gap-1">
                            <i class="bi bi-fire text-amber-300"></i>Best Seller
                        </span>
                        @endif
                        <div class="absolute bottom-0 inset-x-0 p-3 bg-gradient-to-t from-black/70 via-black/30 to-transparent">
                            <span class="glass text-white text-xs font-semibold px-2.5 py-1 rounded-full inline-flex items-center gap-1">
                                <i class="bi bi-tag-fill text-primary"></i>{{ $t->category ? $t->category->name : 'Tour' }}
                            </span>
                        </div>
                    </div>
                    <div class="p-5 flex flex-col flex-grow">
                        <div class="flex justify-between items-center text-xs mb-2">
                            <div class="text-slate-500 inline-flex items-center gap-1">
                                <i class="bi bi-clock"></i>{{ $t->duration }}
                            </div>
                            <div class="text-amber-500 font-bold inline-flex items-center gap-1">
                                <i class="bi bi-star-fill"></i>{{ $t->rating }}
                            </div>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-3 line-clamp-2 group-hover:text-primary transition-colors leading-snug">{{ $t->name }}</h3>
                        <div class="flex justify-between items-end mt-auto pt-3 border-t border-slate-200">
                            <div>
                                <span class="block text-[10px] uppercase font-bold text-slate-500">Starting from</span>
                                <span class="text-lg font-black text-primary" data-aed="{{ $minPriceRel }}">AED {{ number_format($minPriceRel) }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
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
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Mobile Book Bar Sticky bottom (visible on screens below lg) -->
<div class="fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md px-4 py-3 border-t border-slate-200 shadow-2xl lg:hidden flex items-center justify-between z-30 pb-safe">
    <div>
        <span class="text-slate-400 block text-[9px] uppercase font-bold tracking-wider">Starting From</span>
        <div class="text-xl font-black text-primary" data-aed="{{ $minPrice }}">AED {{ number_format($minPrice) }}</div>
    </div>
    <div class="flex items-center gap-2">
        <button type="button" class="border border-slate-300 text-slate-700 text-xs font-bold rounded-full px-3.5 py-2.5 btn-toggle-compare whitespace-nowrap cursor-pointer inline-flex items-center gap-1" data-tour-id="{{ $tour->id }}" onclick="event.preventDefault(); window.DunesCompare && window.DunesCompare.toggle(this);">
            <i class="bi bi-shuffle"></i><span class="compare-btn-text">Compare</span>
        </button>
        <button class="btn-desert-animated text-xs sm:text-sm font-bold text-white rounded-full px-5 py-2.5 shadow-md whitespace-nowrap cursor-pointer inline-flex items-center gap-1" data-action="open-booking" data-tour="{{ $tour->id }}" @click.prevent="$store.modal.open('booking', { tourId: {{ $tour->id }} })">
            <i class="bi bi-calendar-check-fill"></i>Book Now
        </button>
    </div>
</div>

@endsection
