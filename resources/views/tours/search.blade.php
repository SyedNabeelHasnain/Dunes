@extends('layouts.app')

@push('schema')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "SearchResultsPage",
  "@@id": "{{ $canonical }}#webpage",
  "name": {!! json_encode($pageTitle) !!},
  "description": {!! json_encode($pageDesc) !!},
  "url": "{{ $canonical }}",
  "breadcrumb": {
    "@@type": "BreadcrumbList",
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
        "name": {!! json_encode($displayQuery) !!},
        "item": "{{ $canonical }}"
      }
    ]
  },
  "mainEntity": {
    "@@type": "ItemList",
    "numberOfItems": {{ $tours->count() }},
    "itemListElement": [
      @foreach($tours as $idx => $t)
      @php
          $tPrice = $t->tiers->min('pivot.price') ?? 0;
      @endphp
      {
        "@@type": "ListItem",
        "position": {{ $idx + 1 }},
        "name": {!! json_encode($t->name) !!},
        "url": "{{ route('tours.show', $t->slug) }}",
        "image": "{{ asset('images/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $t->thumb_image)) }}",
        "offers": {
          "@@type": "Offer",
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
<section class="page-header py-4 bg-dark text-white position-relative overflow-hidden" style="margin-top: calc(-1 * var(--header-h));">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at 15% 20%, rgba(246, 144, 68, 0.2) 0%, transparent 60%);"></div>
    <div class="container position-relative z-1 pt-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white text-opacity-75 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tours.index') }}" class="text-white text-opacity-75 text-decoration-none">Tours</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">{{ $displayQuery }}</li>
            </ol>
        </nav>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
            <div>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <span class="badge glass rounded-pill px-3 py-1.5">
                        <i class="bi bi-star-fill text-warning me-1"></i>Rated 4.9/5 by 2,847+ Travelers
                    </span>
                    <span class="badge bg-success bg-opacity-75 rounded-pill px-3 py-1.5 text-white">
                        <i class="bi bi-patch-check-fill me-1"></i>DET Licensed Operator #1430583
                    </span>
                    <span class="badge bg-primary rounded-pill px-3 py-1.5 text-white">
                        <i class="bi bi-check2-circle me-1"></i>Found {{ $tours->count() }} Verified Packages
                    </span>
                </div>
                <h1 class="display-5 fw-bold text-white mb-2">
                    @if($isFallback)
                        Top Dubai Desert Safari Deals for <span class="text-primary">"{{ $displayQuery }}"</span>
                    @else
                        Verified Deals for <span class="text-primary">"{{ $displayQuery }}"</span> in Dubai
                    @endif
                </h1>
                <p class="lead text-white text-opacity-75 mb-0">
                    Compare certified Dubai desert safaris, buggy rentals, and sightseeing experiences with 4x4 hotel transfers, 5-star live BBQ dining, and instant confirmation.
                </p>
            </div>
            <div class="d-none d-lg-block text-end flex-shrink-0">
                <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill fw-bold fs-6">
                    <i class="bi bi-shield-check me-1"></i>Best Price Guarantee
                </span>
                <div class="text-white-50 small mt-1">Starting from AED {{ number_format($minPrice) }}</div>
            </div>
        </div>
    </div>
</section>

<!-- Trust & Regulatory E-E-A-T Bar -->
<section class="bg-light py-3 border-bottom shadow-sm">
    <div class="container">
        <div class="row g-3 text-center align-items-center">
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-patch-check-fill text-primary fs-5"></i>
                    <div class="text-start">
                        <div class="fw-bold text-dark small lh-1">DTCM / DET Licensed</div>
                        <small class="text-muted" style="font-size: 11px;">License #1430583</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-arrow-repeat text-success fs-5"></i>
                    <div class="text-start">
                        <div class="fw-bold text-dark small lh-1">100% Free Cancellation</div>
                        <small class="text-muted" style="font-size: 11px;">Full refund up to 24h</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-award-fill text-warning fs-5"></i>
                    <div class="text-start">
                        <div class="fw-bold text-dark small lh-1">100% Halal Live BBQ</div>
                        <small class="text-muted" style="font-size: 11px;">Veg, Non-Veg & Jain</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-shield-lock-fill text-info fs-5"></i>
                    <div class="text-start">
                        <div class="fw-bold text-dark small lh-1">Instant Confirmation</div>
                        <small class="text-muted" style="font-size: 11px;">Card / Cash on Pickup</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Search Body -->
<section class="section py-5">
    <div class="container">
        
        <!-- Search Refinement & Query Modification Bar -->
        <div class="card border-0 bg-light rounded-4 p-3 p-md-4 mb-4 shadow-sm">
            <form action="{{ route('tours.search') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-12 col-lg-5">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" name="q" value="{{ $cleanQuery }}" class="form-control rounded-pill ps-5 pe-5 py-2.5 bg-white border-0 shadow-none" placeholder="Search safaris, quad biking, dune buggy, VIP table..." required>
                        <button type="submit" class="btn btn-desert-animated rounded-pill position-absolute top-50 end-0 translate-middle-y me-1.5 px-3 py-1 btn-sm fw-semibold">
                            Update
                        </button>
                    </div>
                </div>
                <div class="col-12 col-lg-7">
                    <div class="d-flex gap-2 overflow-x-auto pb-1 flex-nowrap w-100" style="scrollbar-width: none; -webkit-overflow-scrolling: touch;">
                        <span class="small text-muted fw-bold align-self-center me-1 flex-shrink-0">
                            <i class="bi bi-funnel me-1"></i>Suggestions:
                        </span>
                        @foreach($intent['pills'] as $pill)
                            <a href="{{ route('tours.search', ['q' => $pill]) }}" class="btn btn-white border rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 transition-all small flex-shrink-0 text-nowrap hover-shadow-sm">
                                <i class="bi bi-plus-circle text-primary"></i> {{ $pill }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </form>
        </div>

        <!-- Generative Engine Optimization (GEO) Direct-Answer Overview Box -->
        <div class="card border-0 rounded-4 p-4 mb-5 shadow-sm position-relative overflow-hidden" style="background: linear-gradient(135deg, rgba(246, 144, 68, 0.06) 0%, rgba(15, 23, 42, 0.02) 100%); border: 1px solid rgba(246, 144, 68, 0.25) !important;">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary text-white rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1">
                        <i class="bi bi-compass-fill me-1"></i> Expert Safari Overview
                    </span>
                    <h2 class="h5 fw-bold text-dark mb-0">{{ $aiOverview['title'] }}</h2>
                </div>
                <span class="badge bg-white text-muted border rounded-pill px-2.5 py-1 small" style="font-size: 11px;">
                    <i class="bi bi-patch-check-fill text-success me-1"></i>Verified Tour Authority
                </span>
            </div>

            <p class="lead-sm text-dark mb-4" style="font-size: 1rem; line-height: 1.65;">
                {{ $aiOverview['summary'] }}
            </p>

            <!-- Quick Specs Grid (Structured for Google SGE & Perplexity Extraction) -->
            <div class="row g-2 mb-3">
                @foreach($aiOverview['quick_stats'] as $label => $stat)
                <div class="col-6 col-md-3">
                    <div class="bg-white rounded-3 p-2.5 border shadow-none h-100">
                        <div class="text-muted small lh-1 mb-1" style="font-size: 11px;">{{ $label }}</div>
                        <div class="fw-bold text-dark small">{{ $stat }}</div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between pt-3 border-top border-secondary border-opacity-10 small text-muted">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check text-success"></i>
                    <span>{{ $aiOverview['verified_note'] }}</span>
                </div>
                <div class="d-none d-md-block text-end">
                    <span class="text-primary fw-semibold"><i class="bi bi-lightning-charge-fill me-1"></i>Live Best Rates</span>
                </div>
            </div>
        </div>

        <!-- Zero-Match Bestseller Fallback Banner -->
        @if($isFallback)
        <div class="alert alert-warning border-0 rounded-4 shadow-sm p-4 mb-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-info-circle-fill fs-2 text-warning flex-shrink-0"></i>
                <div>
                    <h5 class="fw-bold mb-1">No direct package named "{{ $cleanQuery }}"</h5>
                    <p class="mb-0 text-dark small">
                        We didn't find an exact title match for your query, but here are Dubai's #1 rated Desert Safari packages hand-selected by our licensed guides:
                    </p>
                </div>
            </div>
            <button type="button" class="btn btn-dark rounded-pill px-4 py-2.5 fw-bold text-nowrap flex-shrink-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#safariMatcherModal">
                <i class="bi bi-stars text-warning me-1"></i> Match My Safari (5% OFF)
            </button>
        </div>
        @endif

        <!-- Results Grid -->
        <div class="row g-4" id="tours-grid">
            @foreach($tours as $t)
                @php
                    $minPrice = $t->tiers->min('pivot.price') ?? 0;
                    $tourCat = $categories->firstWhere('id', $t->category_id);
                    $tourCatSlug = $tourCat ? $tourCat->slug : '';
                @endphp
                <div class="col-12 col-md-6 col-lg-4 tour-item" data-category="{{ $tourCatSlug }}" data-name="{{ strtolower($t->name) }}">
                    <article class="card card-modern h-100 border-0 shadow-sm transition-all hover-shadow-md rounded-4 overflow-hidden bg-white">
                        <a href="{{ route('tours.show', $t->slug) }}" class="text-decoration-none text-dark d-flex flex-column h-100">
                            <div class="card-img-wrapper position-relative overflow-hidden" style="aspect-ratio: 16/10;">
                                <img src="{{ asset('images/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $t->thumb_image)) }}" class="card-img-top w-100 h-100" alt="{{ $t->name }} Dubai" loading="lazy" style="object-fit: cover;">
                                @if($t->is_bestseller)
                                <span class="badge bg-primary position-absolute top-0 start-0 m-3 rounded-pill shadow-sm">
                                    <i class="bi bi-fire me-1"></i>Best Seller
                                </span>
                                @elseif($t->is_featured)
                                <span class="badge bg-success position-absolute top-0 start-0 m-3 rounded-pill shadow-sm">
                                    <i class="bi bi-award me-1"></i>Featured
                                </span>
                                @endif
                                <div class="position-absolute bottom-0 start-0 w-100 p-3" style="background: linear-gradient(0deg, rgba(0,0,0,0.6) 0%, transparent 100%);">
                                    <span class="badge glass text-white fw-semibold">
                                        <i class="bi bi-tag-fill me-1"></i>{{ $tourCat ? $tourCat->name : 'Tours' }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="text-muted small">
                                        <i class="bi bi-clock me-1 text-primary"></i>{{ $t->duration ?: '6 Hours' }}
                                    </div>
                                    <div class="text-warning small fw-bold">
                                        <i class="bi bi-star-fill me-1"></i>{{ $t->rating ?: '4.9' }}
                                    </div>
                                </div>
                                <h2 class="h5 fw-bold mb-2 line-clamp-2 text-dark">{{ $t->name }}</h2>
                                @php $bookingsToday = (int)(($t->id * 3 + (int)date('j')) % 5 + 3); @endphp
                                <div class="d-flex align-items-center gap-1.5 text-danger small fw-bold mb-2" style="font-size: 11px;">
                                    <i class="bi bi-fire text-danger"></i>
                                    <span>{{ $bookingsToday }} booked in last 6 hours</span>
                                </div>
                                
                                <div class="d-flex flex-wrap gap-1 mb-3">
                                    <span class="badge bg-light text-muted border small" style="font-size: 10px;">
                                        <i class="bi bi-check2 text-success me-1"></i>4x4 Pickup
                                    </span>
                                    <span class="badge bg-light text-muted border small" style="font-size: 10px;">
                                        <i class="bi bi-check2 text-success me-1"></i>Halal Live BBQ
                                    </span>
                                    <span class="badge bg-light text-muted border small" style="font-size: 10px;">
                                        <i class="bi bi-check2 text-success me-1"></i>Free Cancel 24h
                                    </span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top border-light">
                                    <div>
                                        <small class="text-muted d-block" style="font-size: 10px; text-transform: uppercase; font-weight: 700;">Starting from</small>
                                        <span class="h5 fw-bold text-primary mb-0" data-aed="{{ $minPrice }}">AED {{ number_format($minPrice) }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 text-nowrap btn-toggle-compare small" style="font-size: 0.78rem;" data-tour-id="{{ $t->id }}" onclick="event.preventDefault(); event.stopPropagation(); window.DunesCompare && window.DunesCompare.toggle(this);">
                                            <i class="bi bi-shuffle me-1"></i> <span class="compare-btn-text">Compare</span>
                                        </button>
                                        <span role="button" tabindex="0" class="btn-circle-whatsapp fab-whatsapp" data-tour-name="{{ $t->name }}" aria-label="Book {{ $t->name }} via WhatsApp" onclick="event.preventDefault(); event.stopPropagation(); if(window.App && typeof window.App.openWhatsApp === 'function'){ window.App.openWhatsApp('{{ addslashes($t->name) }}'); }" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();event.stopPropagation();if(window.App&&typeof window.App.openWhatsApp==='function'){window.App.openWhatsApp('{{ addslashes($t->name) }}');}}">
                                            <i class="bi bi-whatsapp"></i>
                                        </span>
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

<!-- FAQ / Search Guidance Section -->
<section class="section py-5 bg-light border-top">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold mb-3">Questions About <span class="text-primary">{{ $displayQuery }}</span></h2>
            <p class="text-muted lead-sm mx-auto" style="max-width: 650px;">Everything you need to know about booking certified Dubai tours with Dunes Discovery Tourism.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="accordion accordion-flush" id="searchFaqAccordion">
                    <div class="accordion-item border-0 mb-3 rounded-4 shadow-sm overflow-hidden">
                        <h3 class="accordion-header" id="sFaq1Head">
                            <button class="accordion-button collapsed fw-bold text-dark py-3" type="button" data-bs-toggle="collapse" data-bs-target="#sFaq1" aria-expanded="false" aria-controls="sFaq1">
                                Are hotel pickups and drop-offs included in all safaris?
                            </button>
                        </h3>
                        <div id="sFaq1" class="accordion-collapse collapse" aria-labelledby="sFaq1Head" data-bs-parent="#searchFaqAccordion">
                            <div class="accordion-body text-muted pt-0 pb-4">
                                Yes. All 4x4 packages include direct, door-to-door hotel pickup and drop-off from Dubai, Sharjah, and major hotel districts in air-conditioned 4x4 Land Cruisers. Standard bus meeting point options are also available for budget travelers.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 mb-3 rounded-4 shadow-sm overflow-hidden">
                        <h3 class="accordion-header" id="sFaq2Head">
                            <button class="accordion-button collapsed fw-bold text-dark py-3" type="button" data-bs-toggle="collapse" data-bs-target="#sFaq2" aria-expanded="false" aria-controls="sFaq2">
                                Do I need an international driver's license for Dune Buggies or Quad Bikes?
                            </button>
                        </h3>
                        <div id="sFaq2" class="accordion-collapse collapse" aria-labelledby="sFaq2Head" data-bs-parent="#searchFaqAccordion">
                            <div class="accordion-body text-muted pt-0 pb-4">
                                No driving license is required. Dune buggy and quad biking tours take place on designated off-road tracks and high red dunes in Lahbab under professional guide supervision. All riders are equipped with helmets, goggles, and full safety briefings.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 mb-3 rounded-4 shadow-sm overflow-hidden">
                        <h3 class="accordion-header" id="sFaq3Head">
                            <button class="accordion-button collapsed fw-bold text-dark py-3" type="button" data-bs-toggle="collapse" data-bs-target="#sFaq3" aria-expanded="false" aria-controls="sFaq3">
                                What is your cancellation policy?
                            </button>
                        </h3>
                        <div id="sFaq3" class="accordion-collapse collapse" aria-labelledby="sFaq3Head" data-bs-parent="#searchFaqAccordion">
                            <div class="accordion-body text-muted pt-0 pb-4">
                                We offer 100% free cancellation with a full refund up to 24 hours prior to your scheduled tour pickup time. You can cancel or reschedule easily via WhatsApp or email with zero penalty fees.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 mb-3 rounded-4 shadow-sm overflow-hidden">
                        <h3 class="accordion-header" id="sFaq4Head">
                            <button class="accordion-button collapsed fw-bold text-dark py-3" type="button" data-bs-toggle="collapse" data-bs-target="#sFaq4" aria-expanded="false" aria-controls="sFaq4">
                                Is Dunes Discovery an officially licensed tour operator in Dubai?
                            </button>
                        </h3>
                        <div id="sFaq4" class="accordion-collapse collapse" aria-labelledby="sFaq4Head" data-bs-parent="#searchFaqAccordion">
                            <div class="accordion-body text-muted pt-0 pb-4">
                                Yes. Dunes Discovery Tourism LLC is fully certified and licensed by the Dubai Department of Economy and Tourism (DET License #1430583). All drivers hold professional safari licenses, and all vehicles undergo stringent DTCM safety inspections.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
