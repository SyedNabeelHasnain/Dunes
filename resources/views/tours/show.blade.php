@extends('layouts.app')

@section('content')
@php
    $notAllowed = $tour->contentItems->where('type', 'not_allowed')->sortBy('priority');
    $minPrice = $tour->tiers->min('pivot.price') ?? 0;
    
    // Prepare Tabs Array
    $tabs = [];
    if($highlights->count()) $tabs['highlights'] = 'Highlights';
    if($tour->itineraries->count()) $tabs['itinerary'] = 'Itinerary';
    if($inclusions->count() || $exclusions->count()) $tabs['inex'] = 'Inclusion & Exclusion';
    if($notAllowed->count()) $tabs['info'] = 'Important Information';
    if($faqs->count()) $tabs['faqs'] = 'FAQ';
@endphp

@push('preloads')
    <link rel="preload" as="image" href="{{ asset('images/blog/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $tour->hero_image ?: 'evening-desert-safari-dubai-dune-discovery-tourism.avif')) }}" type="image/avif">
    
    <!-- 2026 Connected JSON-LD Schema Graph: TouristTrip, Product, Offer, TravelAgency, Itinerary, FAQPage -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@graph": [
        {
          "@@type": "TravelAgency",
          "@@id": "{{ route('home') }}#organization",
          "name": "Dunes Discovery Tourism LLC",
          "url": "{{ route('home') }}",
          "logo": "{{ asset('images/logo.png') }}",
          "image": "{{ asset('images/desert-safari-poster.avif') }}",
          "telephone": "{{ $phoneVal ?? '+971 50 245 6056' }}",
          "email": "info@dunesdiscoverytourism.com",
          "priceRange": "AED 79 - AED 1500",
          "address": {
            "@@type": "PostalAddress",
            "streetAddress": "Dubai Desert Safari Terminal, Al Aweer & Lahbab",
            "addressLocality": "Dubai",
            "addressRegion": "Dubai",
            "postalCode": "00000",
            "addressCountry": "AE"
          },
          "geo": {
            "@@type": "GeoCoordinates",
            "latitude": "25.2048",
            "longitude": "55.2708"
          },
          "sameAs": [
            "https://www.facebook.com/dunesdiscoverytourism",
            "https://www.instagram.com/dunesdiscoverytourism",
            "https://www.tripadvisor.com"
          ]
        },
        {
          "@@type": ["Product", "TouristTrip"],
          "@@id": "{{ request()->url() }}#trip",
          "name": {!! json_encode($tour->name) !!},
          "description": {!! json_encode(Str::limit(strip_tags($tour->short_desc ?: $tour->full_desc), 300)) !!},
          "image": [
            {!! json_encode(asset('images/blog/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $tour->hero_image ?: 'evening-desert-safari-dubai-dune-discovery-tourism.avif'))) !!}
          ],
          "sku": "DDT-TOUR-{{ $tour->id }}",
          "mpn": "DDT-{{ $tour->slug }}",
          "brand": {
            "@@id": "{{ route('home') }}#organization"
          },
          "provider": {
            "@@id": "{{ route('home') }}#organization"
          },
          "touristType": ["Adventure Tourism", "Family Friendly", "Couples", "Solo Travelers"],
          "aggregateRating": {
            "@@type": "AggregateRating",
            "ratingValue": "{{ $tour->rating ?: '4.9' }}",
            "reviewCount": "{{ $tour->review_count ?: '1247' }}",
            "bestRating": "5",
            "worstRating": "1"
          },
          "offers": {
            "@@type": "Offer",
            "url": {!! json_encode(request()->url()) !!},
            "priceCurrency": "AED",
            "price": "{{ number_format($minPrice, 2, '.', '') }}",
            "priceValidUntil": "2026-12-31",
            "validFrom": "{{ now()->startOfYear()->toIso8601String() }}",
            "itemCondition": "https://schema.org/NewCondition",
            "availability": "https://schema.org/InStock",
            "seller": {
              "@@id": "{{ route('home') }}#organization"
            },
            "hasMerchantReturnPolicy": {
              "@@type": "MerchantReturnPolicy",
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
            "@@type": "ItemList",
            "numberOfItems": {{ $tour->itineraries->count() }},
            "itemListElement": [
              @foreach($tour->itineraries->sortBy('priority') as $idx => $it)
              {
                "@@type": "ListItem",
                "position": {{ $idx + 1 }},
                "item": {
                  "@@type": "TouristAttraction",
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
          "@@type": "BreadcrumbList",
          "@@id": "{{ request()->url() }}#breadcrumb",
          "itemListElement": [
            {
              "@@type": "ListItem",
              "position": 1,
              "name": "Home",
              "item": "{{ route('home') }}"
            },
            {
              "@@type": "ListItem",
              "position": 2,
              "name": "Tours",
              "item": "{{ route('tours.index') }}"
            },
            {
              "@@type": "ListItem",
              "position": 3,
              "name": {!! json_encode($tour->name) !!},
              "item": "{{ request()->url() }}"
            }
          ]
        }
        @if(isset($faqs) && $faqs->count() > 0)
        ,
        {
          "@@type": "FAQPage",
          "@@id": "{{ request()->url() }}#faq",
          "mainEntity": [
            @foreach($faqs as $fidx => $f)
            {
              "@@type": "Question",
              "name": {!! json_encode($f->question) !!},
              "acceptedAnswer": {
                "@@type": "Answer",
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
@if(\App\Models\Setting::where('setting_key', 'meta_active')->value('setting_value') === '1')
@php $metaPixelId = \App\Models\Setting::where('setting_key', 'meta_pixel_id')->value('setting_value'); @endphp
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
<section class="tour-hero-modern position-relative d-flex align-items-end" style="min-height: 50vh; background: url('{{ asset('images/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $tour->hero_image)) }}') center/cover no-repeat; margin-top: -var(--header-h);">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(to bottom, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.85) 100%);"></div>
    <div class="container position-relative z-1 pb-5 text-white">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white text-opacity-75 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tours.index') }}" class="text-white text-opacity-75 text-decoration-none">Tours</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">{{ $tour->name }}</li>
            </ol>
        </nav>
        <div>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge rounded-pill glass px-3 py-2 fw-semibold">
                    <i class="bi bi-tag-fill me-1 text-primary"></i>{{ $tour->category ? $tour->category->name : 'Tour' }}
                </span>
                @if($tour->is_bestseller)
                <span class="badge rounded-pill bg-primary px-3 py-2 shadow-sm border-0">
                    <i class="bi bi-fire me-1"></i>Best Seller
                </span>
                @endif
                <span class="badge rounded-pill bg-success bg-opacity-75 px-3 py-2 text-white">
                    <i class="bi bi-shield-check me-1"></i>DTCM Licensed Operator
                </span>
            </div>
            <h1 class="display-3 fw-bold mb-4 text-white text-shadow">{{ $tour->name }}</h1>
            <div class="d-flex flex-wrap gap-4 align-items-center opacity-90">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-box-sm bg-primary rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-clock-fill text-white small"></i>
                    </div>
                    <span class="fw-medium">{{ $tour->duration }}</span>
                </div>
                <div class="d-flex align-items-center gap-2 border-start border-white border-opacity-25 ps-4">
                    <div class="text-warning">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <span class="fw-medium">{{ $tour->rating }} <small class="opacity-75">({{ number_format($tour->review_count) }} verified reviews)</small></span>
                </div>
                <div class="d-flex align-items-center gap-2 border-start border-white border-opacity-25 ps-4 d-none d-md-flex">
                    <i class="bi bi-geo-alt-fill text-primary"></i>
                    <span class="fw-medium">Hotel Pickup & Drop Included</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Regulatory E-E-A-T & Trust Bar -->
<section class="bg-light py-3 border-bottom">
    <div class="container">
        <div class="row g-3 text-center align-items-center">
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-patch-check-fill text-primary fs-5"></i>
                    <div class="text-start">
                        <div class="fw-bold text-dark small lh-1">DTCM Licensed</div>
                        <small class="text-muted" style="font-size: 11px;">UAE Tourism Authority</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-arrow-repeat text-success fs-5"></i>
                    <div class="text-start">
                        <div class="fw-bold text-dark small lh-1">100% Free Cancel</div>
                        <small class="text-muted" style="font-size: 11px;">Up to 24h in advance</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-award-fill text-warning fs-5"></i>
                    <div class="text-start">
                        <div class="fw-bold text-dark small lh-1">100% Halal Food</div>
                        <small class="text-muted" style="font-size: 11px;">Veg, Non-Veg & Jain</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-shield-lock-fill text-info fs-5"></i>
                    <div class="text-start">
                        <div class="fw-bold text-dark small lh-1">Secure Checkout</div>
                        <small class="text-muted" style="font-size: 11px;">Card / Cash on Pickup</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Content and Sidebar Section -->
<section class="section py-5 bg-white">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8">
                <!-- GEO & AI Search "Tour at a Glance" Quick Facts Card -->
                <div class="card border-0 bg-soft-primary rounded-4 p-4 mb-5 border-start border-4 border-primary shadow-sm">
                    <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-lightning-charge-fill"></i>Experience at a Glance
                    </h2>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-clock text-primary mt-1"></i>
                                <div>
                                    <strong class="d-block text-dark small">Duration:</strong>
                                    <span class="text-muted small">{{ $tour->duration ?: '6 - 7 Hours' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-truck text-primary mt-1"></i>
                                <div>
                                    <strong class="d-block text-dark small">Transfer Vehicle:</strong>
                                    <span class="text-muted small">4x4 Luxury Toyota Land Cruiser / Prado</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-geo-alt text-primary mt-1"></i>
                                <div>
                                    <strong class="d-block text-dark small">Destination:</strong>
                                    <span class="text-muted small">Lahbab High Red Dunes, Dubai, UAE</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-cup-hot text-primary mt-1"></i>
                                <div>
                                    <strong class="d-block text-dark small">Dining & Beverages:</strong>
                                    <span class="text-muted small">Live BBQ Buffet, Arabic Coffee & Dates</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-shield-check text-primary mt-1"></i>
                                <div>
                                    <strong class="d-block text-dark small">Safety & Insurance:</strong>
                                    <span class="text-muted small">Full Comprehensive Passenger Insurance</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-chat-dots text-primary mt-1"></i>
                                <div>
                                    <strong class="d-block text-dark small">Languages:</strong>
                                    <span class="text-muted small">{{ $tour->languages ?: 'English, Arabic, Hindi, Russian' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- About This Tour -->
                <div class="content-card mb-5 border-0 shadow-none p-0">
                    <h2 class="h3 fw-bold mb-4 d-flex align-items-center gap-3">
                        <span class="bg-soft-primary p-2 rounded-3 text-primary"><i class="bi bi-info-circle"></i></span>
                        About {{ $tour->name }}
                    </h2>
                    <div class="text-muted lead-sm fs-5 position-relative" style="line-height: 1.8;">
                        <div id="tourDescriptionText" class="line-clamp-3">
                            {!! nl2br(e($tour->full_desc)) !!}
                        </div>
                        <button class="btn btn-sm border-animated rounded-pill px-3 py-1 mt-3 shadow-sm fw-bold d-none text-primary" id="readMoreBtn" onclick="toggleDescription()">
                            Read More <i class="bi bi-chevron-down ms-1"></i>
                        </button>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                @if(count($tabs))
                <div class="mb-4">
                    <ul class="nav nav-pills tour-tabs" id="tourTabs" role="tablist">
                        @php $first = true; @endphp
                        @foreach($tabs as $key => $label)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill fw-bold {{ $first ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-{{ $key }}" type="button" role="tab">{{ $label }}</button>
                        </li>
                        @php $first = false; @endphp
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Tabs Content -->
                <div class="tab-content mb-5">
                    @php $first = true; @endphp
                    
                    <!-- Highlights -->
                    @if(isset($tabs['highlights']))
                    <div class="tab-pane fade {{ $first ? 'show active' : '' }}" id="tab-highlights" role="tabpanel">
                        <div class="row g-4">
                            @foreach($highlights as $h)
                            <div class="col-md-6">
                                <div class="d-flex gap-3 p-3 rounded-4 bg-light hover-shadow-sm transition-all border border-transparent hover-border-primary">
                                    <div class="bg-primary text-white rounded-circle p-2 flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-check2 fw-bold"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $h->title }}</div>
                                        @if($h->description)
                                        <small class="text-muted d-block mt-1">{{ $h->description }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @php $first = false; @endphp
                    @endif

                    <!-- Itinerary -->
                    @if(isset($tabs['itinerary']))
                    <div class="tab-pane fade {{ $first ? 'show active' : '' }}" id="tab-itinerary" role="tabpanel">
                        <div class="itinerary-modern ps-4 border-start border-primary border-opacity-25 position-relative ms-2">
                            @foreach($tour->itineraries->sortBy('priority') as $it)
                            <div class="itinerary-item position-relative pb-4 mb-4">
                                <div class="position-absolute start-0 top-0 translate-middle-x bg-primary rounded-circle border border-4 border-white shadow-sm" style="width: 22px; height: 22px; margin-left: -24px;"></div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="badge bg-soft-primary text-primary rounded-pill fw-bold px-3">{{ $it->time }}</div>
                                    @if($it->duration)
                                    <small class="text-muted fw-semibold"><i class="bi bi-clock me-1 text-primary"></i>{{ $it->duration }}</small>
                                    @endif
                                </div>
                                <h3 class="h5 fw-bold text-dark mb-2">{{ $it->title }}</h3>
                                <p class="text-muted mb-0 small opacity-75">{{ $it->description }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @php $first = false; @endphp
                    @endif

                    <!-- Inclusion & Exclusion -->
                    @if(isset($tabs['inex']))
                    <div class="tab-pane fade {{ $first ? 'show active' : '' }}" id="tab-inex" role="tabpanel">
                        <div class="row g-4">
                            @if($inclusions->count())
                            <div class="col-md-6">
                                <div class="card border-0 bg-soft-success p-4 rounded-4 border-start border-4 border-success h-100">
                                    <h3 class="h6 fw-bold mb-4 d-flex align-items-center gap-2 text-success">
                                        <i class="bi bi-check-circle-fill"></i>What's Included
                                    </h3>
                                    <ul class="list-unstyled mb-0">
                                        @foreach($inclusions as $inc)
                                        <li class="d-flex gap-2 mb-3 align-items-start">
                                             <i class="bi bi-check-lg text-success fw-bold mt-1"></i>
                                            <span class="text-dark fw-medium opacity-90">{{ $inc->title }}</span>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            @endif
                            @if($exclusions->count())
                            <div class="col-md-6">
                                <div class="card border-0 bg-soft-danger p-4 rounded-4 border-start border-4 border-danger h-100">
                                    <h3 class="h6 fw-bold mb-4 d-flex align-items-center gap-2 text-danger">
                                        <i class="bi bi-x-circle-fill"></i>Not Included
                                    </h3>
                                    <ul class="list-unstyled mb-0">
                                        @foreach($exclusions as $exc)
                                        <li class="d-flex gap-2 mb-3 align-items-start">
                                            <i class="bi bi-x-lg text-danger fw-bold mt-1"></i>
                                            <span class="text-dark fw-medium opacity-90">{{ $exc->title }}</span>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @php $first = false; @endphp
                    @endif

                    <!-- Important Information -->
                    @if(isset($tabs['info']))
                    <div class="tab-pane fade {{ $first ? 'show active' : '' }}" id="tab-info" role="tabpanel">
                        <div class="card border-0 bg-soft-warning p-4 rounded-4 border-start border-4 border-warning">
                            <h3 class="h6 fw-bold mb-3 d-flex align-items-center gap-2 text-warning">
                                <i class="bi bi-exclamation-triangle-fill"></i>Important Information
                            </h3>
                            <ul class="list-unstyled mb-0 row g-3">
                                @foreach($notAllowed as $na)
                                <li class="col-md-6 text-dark opacity-90 small d-flex gap-2 align-items-start fw-medium">
                                    <i class="bi bi-info-circle-fill text-warning"></i>
                                    <span>
                                        {{ $na->title }}
                                        @if($na->description)
                                         - <span class="fw-normal opacity-75">{{ $na->description }}</span>
                                        @endif
                                    </span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @php $first = false; @endphp
                    @endif

                    <!-- FAQs -->
                    @if(isset($tabs['faqs']))
                    <div class="tab-pane fade {{ $first ? 'show active' : '' }}" id="tab-faqs" role="tabpanel">
                        <div class="accordion accordion-flush" id="tourFaqAccordion">
                            @foreach($faqs as $index => $f)
                            <div class="accordion-item mb-3 border-animated rounded-4 shadow-sm overflow-hidden bg-white">
                                <h3 class="accordion-header">
                                    <button class="accordion-button collapsed fw-bold py-4 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#tour-faq-{{ $index }}">
                                        <i class="bi bi-question-circle-fill me-3 text-primary"></i>
                                        {{ $f->question }}
                                    </button>
                                </h3>
                                <div id="tour-faq-{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#tourFaqAccordion">
                                    <div class="accordion-body py-4 px-4 text-muted leading-relaxed">
                                        {{ $f->answer }}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @php $first = false; @endphp
                    @endif
                </div>

                <!-- Package Comparison Matrix (Feature Breakdown) -->
                @if($tour->tiers->count() > 1)
                <div class="card border-0 bg-light rounded-4 p-4 p-md-5 mb-5 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <div>
                            <h2 class="h4 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                                <i class="bi bi-ui-checks text-primary"></i>Package Comparison & Pricing
                            </h2>
                            <p class="text-muted small mb-0">Select the ideal tier for your group and budget.</p>
                        </div>
                        <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill fw-semibold">
                            <i class="bi bi-check2-all me-1"></i>Best Price Guarantee
                        </span>
                    </div>

                    <div class="row g-4">
                        @foreach($tour->tiers->sortBy('priority') as $tier)
                        @php
                            $tPrice = $tier->pivot?->price ?? 0;
                            $tOldPrice = $tier->pivot?->old_price ?? 0;
                            $saveAmt = ($tOldPrice > 0 && $tOldPrice > $tPrice) ? ($tOldPrice - $tPrice) : 0;
                        @endphp
                        <div class="col-md-{{ 12 / min(max($tour->tiers->count(), 1), 3) }}">
                            <div class="card h-100 border-2 rounded-4 p-4 transition-all hover-shadow-md position-relative {{ $tier->is_popular ? 'border-primary bg-white shadow-sm' : 'border-light bg-white' }}">
                                @if($tier->is_popular)
                                <div class="position-absolute top-0 start-50 translate-middle">
                                    <span class="badge bg-primary rounded-pill px-3 py-1 text-uppercase fw-bold shadow-sm" style="font-size: 10px; letter-spacing: 0.5px;">Most Popular</span>
                                </div>
                                @endif
                                <div class="text-center pt-2 mb-3 pb-3 border-bottom">
                                    <h3 class="h5 fw-bold text-dark mb-2">{{ $tier->name }}</h3>
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <span class="h3 fw-bold text-primary mb-0">AED {{ number_format($tPrice) }}</span>
                                        @if($saveAmt)
                                        <small class="text-muted text-decoration-line-through">AED {{ number_format($tOldPrice) }}</small>
                                        @endif
                                    </div>
                                    <small class="text-muted d-block mt-1">Per Person (All Inclusive)</small>
                                </div>

                                <ul class="list-unstyled mb-4 flex-grow-1">
                                    <li class="d-flex align-items-center gap-2 mb-2 small text-dark">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        <span>{{ $tour->duration }} Desert Experience</span>
                                    </li>
                                    <li class="d-flex align-items-center gap-2 mb-2 small text-dark">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        <span>4x4 Land Cruiser Transfers</span>
                                    </li>
                                    <li class="d-flex align-items-center gap-2 mb-2 small text-dark">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        <span>BBQ Buffet & Live Shows</span>
                                    </li>
                                    @if(stripos($tier->name, 'quad') !== false || stripos($tier->name, 'buggy') !== false)
                                    <li class="d-flex align-items-center gap-2 mb-2 small text-dark fw-bold">
                                        <i class="bi bi-check-circle-fill text-primary"></i>
                                        <span>Self-Drive ATV / Buggy Ride</span>
                                    </li>
                                    @endif
                                    @if(stripos($tier->name, 'vip') !== false || stripos($tier->name, 'private') !== false)
                                    <li class="d-flex align-items-center gap-2 mb-2 small text-dark fw-bold">
                                        <i class="bi bi-check-circle-fill text-warning"></i>
                                        <span>VIP Table Service & Chalet</span>
                                    </li>
                                    @endif
                                </ul>

                                <button type="button" class="btn {{ $tier->is_popular ? 'btn-desert-animated' : 'btn-outline-primary' }} w-100 rounded-pill py-2.5 fw-bold" data-action="open-booking" data-tour="{{ $tour->id }}" data-tier="{{ $tier->id }}">
                                    Select {{ $tier->name }}
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Contextual Cross-Tour Internal Links -->
                <div class="p-4 rounded-4 bg-white border border-light shadow-sm mb-4">
                    <h3 class="h6 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-compass text-primary"></i>Explore More Dubai Adventures
                    </h3>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ url('/dune-buggy-rental-dubai') }}" class="badge bg-light text-dark rounded-pill px-3 py-2 text-decoration-none border hover-border-primary">
                            <i class="bi bi-truck me-1 text-primary"></i>1000cc Dune Buggy Rental
                        </a>
                        <a href="{{ url('/evening-desert-safari-dubai') }}" class="badge bg-light text-dark rounded-pill px-3 py-2 text-decoration-none border hover-border-primary">
                            <i class="bi bi-sunset me-1 text-primary"></i>Evening Desert Safari
                        </a>
                        <a href="{{ url('/desert-safari-quad-biking-dubai') }}" class="badge bg-light text-dark rounded-pill px-3 py-2 text-decoration-none border hover-border-primary">
                            <i class="bi bi-speedometer2 me-1 text-primary"></i>Quad Biking Adventure
                        </a>
                        <a href="{{ url('/dhow-cruise-catamaran-cruise-dinner-dubai') }}" class="badge bg-light text-dark rounded-pill px-3 py-2 text-decoration-none border hover-border-primary">
                            <i class="bi bi-water me-1 text-primary"></i>Marina Dhow Cruise Dinner
                        </a>
                        <a href="{{ url('/abu-dhabi-city-tour-from-dubai') }}" class="badge bg-light text-dark rounded-pill px-3 py-2 text-decoration-none border hover-border-primary">
                            <i class="bi bi-building me-1 text-primary"></i>Abu Dhabi City Tour
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="sidebar-sticky" style="top: 100px;">
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4 bg-white">
                        <div class="card-body p-4">
                            <div class="text-center mb-4 pb-3 border-bottom">
                                <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 11px;">Starting from</small>
                                <div class="d-flex align-items-center justify-content-center gap-2 mt-1">
                                    <span class="h2 fw-bold text-primary mb-0">AED {{ number_format($minPrice) }}</span>
                                </div>
                            </div>

                            <!-- Urgency Widget -->
                            <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-dark rounded-4 p-3 mb-4 d-flex align-items-center gap-3">
                                <div class="icon-box-sm bg-warning text-dark rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                    <i class="bi bi-fire fs-6"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-uppercase tracking-wider text-warning-emphasis" style="font-size: 10px; letter-spacing: 0.5px;">HIGH DEMAND</div>
                                    <div class="small fw-bold text-dark" style="font-size: 12px;">14 people booked in the last 24h</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h3 class="h6 fw-bold mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-box-seam text-primary"></i>Select Package
                                </h3>
                                <div class="d-grid gap-3">
                                    @foreach($tour->tiers->sortBy('priority') as $tier)
                                    @php
                                        $tPrice = $tier->pivot?->price ?? 0;
                                        $tOldPrice = $tier->pivot?->old_price ?? 0;
                                        $save = ($tOldPrice > 0 && $tOldPrice > $tPrice) ? ($tOldPrice - $tPrice) : 0;
                                    @endphp
                                    <div class="package-option p-3 border rounded-4 position-relative cursor-pointer transition-all hover-shadow-sm {{ $tier->is_popular ? 'border-primary bg-soft-primary' : 'bg-white' }}" data-action="open-booking" data-tour="{{ $tour->id }}" data-tier="{{ $tier->id }}">
                                        @if($tier->is_popular)
                                        <span class="badge bg-primary position-absolute top-0 end-0 m-2 rounded-pill px-2 py-1" style="font-size: 9px; letter-spacing: 0.5px;">POPULAR</span>
                                        @endif
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="pe-3">
                                                <div class="fw-bold text-dark mb-1">{{ $tier->name }}</div>
                                                @if($tier->description)
                                                <small class="text-muted d-block line-clamp-1 opacity-75" style="font-size: 11px;">{{ $tier->description }}</small>
                                                @endif
                                            </div>
                                            <div class="text-end flex-shrink-0 {{ $tier->is_popular ? 'mt-4' : '' }}">
                                                <div class="d-flex flex-column align-items-end">
                                                    @if($save)
                                                    <div class="d-flex align-items-center gap-2">
                                                        <small class="text-secondary text-decoration-line-through fw-semibold" style="font-size: 11px;">AED {{ number_format($tOldPrice) }}</small>
                                                        <div class="fw-bold text-primary">AED {{ number_format($tPrice) }}</div>
                                                    </div>
                                                    @else
                                                    <div class="fw-bold text-primary">AED {{ number_format($tPrice) }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="d-grid gap-3">
                                <button class="btn btn-desert-animated btn-lg rounded-pill py-3 shadow-primary fw-bold border-0 transition-all hover-translate-up" data-bs-toggle="modal" data-bs-target="#bookingModal">
                                    <i class="bi bi-calendar-check-fill me-2"></i>Book Online Now
                                </button>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',\App\Models\Setting::where('setting_key', 'site_whatsapp')->value('setting_value') ?? '971502456056') }}?text={{ urlencode('Hi! I want to book ' . $tour->name) }}" class="btn btn-whatsapp-animated btn-lg rounded-pill py-3 fw-bold border-0 transition-all hover-translate-up" target="_blank" rel="noopener">
                                    <i class="bi bi-whatsapp me-2"></i>Inquire via WhatsApp
                                </a>
                            </div>

                            <!-- Trust Callouts -->
                            <div class="row g-2 mt-3 pt-3 text-center border-top">
                                <div class="col-4">
                                    <div class="p-2 rounded-3 bg-light">
                                        <i class="bi bi-lightning-charge-fill text-primary d-block mb-1 fs-6"></i>
                                        <span class="d-block fw-bold text-dark" style="font-size: 10px;">Instant Voucher</span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded-3 bg-light">
                                        <div class="d-block mb-1 text-success fw-bold" style="font-size: 13px;">24h</div>
                                        <span class="d-block fw-bold text-dark" style="font-size: 10px;">Free Cancel</span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded-3 bg-light">
                                        <i class="bi bi-shield-check text-info d-block mb-1 fs-6"></i>
                                        <span class="d-block fw-bold text-dark" style="font-size: 10px;">Ziina Verified</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mt-4 text-center">
                                <div class="col-6">
                                    <div class="bg-light p-3 rounded-4 border border-transparent hover-border-primary transition-all">
                                        <i class="bi bi-clock-history text-primary d-block mb-2 fs-5"></i>
                                        <small class="text-muted d-block ls-1 mb-1" style="font-size: 9px; font-weight: 700;">DURATION</small>
                                        <div class="fw-bold small text-dark">{{ $tour->duration }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light p-3 rounded-4 border border-transparent hover-border-primary transition-all">
                                        <i class="bi bi-translate text-primary d-block mb-2 fs-5"></i>
                                        <small class="text-muted d-block ls-1 mb-1" style="font-size: 9px; font-weight: 700;">LANGUAGES</small>
                                        <div class="fw-bold small text-dark">{{ $tour->languages }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 bg-dark text-white rounded-4 overflow-hidden shadow-lg">
                        <div class="card-body p-4 position-relative z-1">
                            <h3 class="h5 fw-bold text-light mb-3">Need Help?</h3>
                            <p class="small opacity-75 mb-4">Our travel experts are available 24/7 to help you with your booking.</p>
                            @php
                                $phoneVal = \App\Models\Setting::where('setting_key', 'site_phone')->value('setting_value') ?? '+971 50 245 6056';
                            @endphp
                            <a href="tel:{{ preg_replace('/[^0-9+]/','',$phoneVal) }}" class="d-flex align-items-center gap-3 text-white text-decoration-none mb-3 group">
                                <div class="bg-primary text-white rounded-circle p-2 group-hover-scale transition-all" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-telephone-fill"></i>
                                </div>
                                <div>
                                    <small class="d-block opacity-75">Call Us</small>
                                    <div class="fw-bold">{{ $phoneVal }}</div>
                                </div>
                            </a>
                        </div>
                        <div class="position-absolute top-0 start-0 w-100 h-100 opacity-25" style="background: radial-gradient(circle at 80% 20%, rgba(246, 144, 68, 0.2) 0%, transparent 60%);"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- You Might Also Like Section -->
@if($relatedTours->count())
<section class="section py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
             <h2 class="h3 fw-bold mb-0 d-flex align-items-center gap-3">
                <span class="bg-soft-primary p-2 rounded-3 text-primary"><i class="bi bi-compass"></i></span>
                You Might Also Like
            </h2>
            <a href="{{ route('tours.index') }}" class="btn btn-link text-decoration-none fw-bold">View All Tours <i class="bi bi-arrow-right ms-1"></i></a>
        </div>

        <div class="row g-4">
            @foreach($relatedTours as $t)
            @php $minPriceRel = $t->tiers->min('pivot.price') ?? 0; @endphp
            <div class="col-12 col-md-6 col-lg-3">
                <article class="card card-modern h-100 border-0 shadow-sm bg-white">
                    <a href="{{ route('tours.show', $t->slug) }}" class="text-decoration-none text-dark d-flex flex-column h-100">
                        <div class="card-img-wrapper position-relative overflow-hidden" style="aspect-ratio: 16/10;">
                            <img src="{{ asset('images/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $t->thumb_image)) }}" class="card-img-top w-100 h-100" alt="{{ $t->name }}" loading="lazy" style="object-fit: cover;">
                            @if($t->is_bestseller)
                            <span class="badge bg-primary position-absolute top-0 start-0 m-3 rounded-pill shadow-sm">
                                <i class="bi bi-fire me-1"></i>Best Seller
                            </span>
                            @endif
                            <div class="position-absolute bottom-0 start-0 w-100 p-3" style="background: linear-gradient(0deg, rgba(0,0,0,0.6) 0%, transparent 100%);">
                                <span class="badge glass text-white fw-semibold">
                                    <i class="bi bi-tag-fill me-1"></i>{{ $t->category ? $t->category->name : 'Tour' }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-4 d-flex flex-column flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="text-muted small">
                                    <i class="bi bi-clock me-1"></i>{{ $t->duration }}
                                </div>
                                <div class="text-warning small">
                                    <i class="bi bi-star-fill me-1"></i>{{ $t->rating }}
                                </div>
                            </div>
                            <h3 class="h5 fw-bold mb-3 line-clamp-2">{{ $t->name }}</h3>
                            <div class="d-flex justify-content-between align-items-center mt-auto pt-3">
                                <div>
                                    <small class="text-muted d-block" style="font-size: 10px; text-transform: uppercase; font-weight: 700;">Starting from</small>
                                    <span class="h5 fw-bold text-primary mb-0">AED {{ number_format($minPriceRel) }}</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn-circle-whatsapp fab-whatsapp" data-tour-name="{{ $t->name }}">
                                        <i class="bi bi-whatsapp"></i>
                                    </button>
                                    <div class="btn-circle-desert d-flex align-items-center justify-content-center">
                                        <i class="bi bi-arrow-right"></i>
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
@endif

<!-- Mobile Book Bar Sticky bottom -->
<div class="mobile-bookbar position-fixed bottom-0 start-0 w-100 bg-white p-3 border-top shadow-lg d-md-none d-flex align-items-center justify-content-between z-3 safe-area-bottom">
    <div>
        <small class="text-muted d-block opacity-75 fw-bold" style="font-size: 9px; letter-spacing: 1px; text-transform: uppercase;">Starting From</small>
        <div class="h4 fw-bold text-primary mb-0">AED {{ number_format($minPrice) }}</div>
    </div>
    <button class="btn btn-desert-animated rounded-pill px-4 py-2.5 shadow-sm fw-bold border-0" data-bs-toggle="modal" data-bs-target="#bookingModal" data-action="open-booking" data-tour="{{ $tour->id }}">
        <i class="bi bi-calendar-check-fill me-1"></i>Book Now
    </button>
</div>

@endsection

@push('scripts')
<script>
function toggleDescription() {
    const text = document.getElementById('tourDescriptionText');
    const btn = document.getElementById('readMoreBtn');
    if (text.classList.contains('line-clamp-3')) {
        text.classList.remove('line-clamp-3');
        btn.innerHTML = 'Read Less <i class="bi bi-chevron-up ms-1"></i>';
    } else {
        text.classList.add('line-clamp-3');
        btn.innerHTML = 'Read More <i class="bi bi-chevron-down ms-1"></i>';
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const text = document.getElementById('tourDescriptionText');
    if(text && text.scrollHeight > text.clientHeight) {
        document.getElementById('readMoreBtn').classList.remove('d-none');
    }
});
</script>
@endpush
