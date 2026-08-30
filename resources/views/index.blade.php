@extends('layouts.app')

@section('content')
@php
if (!function_exists('renderReviewCardMarkup')) {
    function renderReviewCardMarkup($r) {
        $stars = '';
        for($i = 0; $i < 5; $i++) {
            $stars .= $i < floor($r->rating) ? '<i class="bi bi-star-fill text-warning"></i>' : '<i class="bi bi-star text-muted"></i>';
        }

        $sourceIcon = ($r->source == 'google') ? '<i class="bi bi-google text-primary"></i>' : '<i class="bi-star-fill text-success"></i>';
        $url = !empty($r->review_url) ? $r->review_url : '#';
        $avatar = !empty($r->reviewer_avatar_url) ? (str_starts_with($r->reviewer_avatar_url, 'http') ? $r->reviewer_avatar_url : asset($r->reviewer_avatar_url)) : asset('images/avatar-default.svg');
        $fallbackAvatar = asset('images/avatar-default.svg');

        return '
        <div class="review-card h-100 d-flex flex-column text-start">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <img src="' . htmlspecialchars($avatar) . '" alt="' . htmlspecialchars($r->reviewer_name) . '" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src=\'' . $fallbackAvatar . '\'">
                    <div>
                        <div class="fw-bold text-dark small">' . htmlspecialchars($r->reviewer_name) . '</div>
                        <div class="text-muted extra-small" style="font-size: 0.75rem;">' . ($r->published_date ? $r->published_date->format('M Y') : '') . '</div>
                    </div>
                </div>
                <div class="d-flex gap-1 small">' . $stars . '</div>
            </div>
            ' . ($r->review_title ? '<h3 class="h6 fw-bold mb-2 text-dark line-clamp-1">' . htmlspecialchars($r->review_title) . '</h3>' : '') . '
            <p class="text-dark small mb-3 flex-grow-1 line-clamp-3" style="font-size: 0.9rem;">"' . htmlspecialchars($r->review_text) . '"</p>
            <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top border-light">
                <span class="badge bg-light text-dark rounded-pill px-2 py-1 small fw-normal">' . $sourceIcon . ' ' . ucfirst($r->source) . '</span>
                <a href="' . htmlspecialchars($url) . '" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1" style="font-size: 0.8rem;">View</a>
            </div>
        </div>';
    }
}
@endphp

@push('preloads')
<link rel="preload" as="image" href="{{ asset('images/desert-safari-poster.avif') }}" fetchpriority="high">

<!-- Schema.org 2026 Connected Knowledge Graph: TravelAgency, LocalBusiness, WebSite, FAQPage, VideoObject -->
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": ["TravelAgency", "LocalBusiness"],
      "@@id": "{{ route('home') }}#organization",
      "name": "Dunes Discovery Tourism LLC",
      "alternateName": ["Dunes Discovery", "Dunes Discovery Tourism Dubai"],
      "url": "{{ route('home') }}",
      "logo": "{{ asset('images/logo.png') }}",
      "image": "{{ asset('images/desert-safari-poster.avif') }}",
      "description": "Licensed Dubai Destination Management Company offering premium Desert Safaris, 1000cc Dune Buggy Rentals, Quad Biking, Dhow Cruise Dinners, and Abu Dhabi City Tours.",
      "telephone": "+971 50 245 6056",
      "email": "info@dunesdiscoverytourism.com",
      "priceRange": "AED 79 - AED 1500",
      "currenciesAccepted": "AED, USD, EUR, GBP",
      "paymentAccepted": "Cash, Credit Card, Debit Card, Ziina",
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
      "openingHoursSpecification": {
        "@@type": "OpeningHoursSpecification",
        "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
        "opens": "00:00",
        "closes": "23:59"
      },
      "aggregateRating": {
        "@@type": "AggregateRating",
        "ratingValue": "4.9",
        "reviewCount": "2847",
        "bestRating": "5",
        "worstRating": "1"
      },
      "hasMerchantReturnPolicy": {
        "@@type": "MerchantReturnPolicy",
        "applicableCountry": "AE",
        "returnPolicyCategory": "https://schema.org/MerchantReturnFiniteReturnWindow",
        "merchantReturnDays": 1,
        "returnMethod": "https://schema.org/ReturnInStore",
        "returnFees": "https://schema.org/FreeReturn"
      },
      "sameAs": [
        "https://www.facebook.com/dunesdiscoverytourism",
        "https://www.instagram.com/dunesdiscoverytourism",
        "https://www.tripadvisor.com"
      ]
    },
    {
      "@@type": "WebSite",
      "@@id": "{{ route('home') }}#website",
      "url": "{{ route('home') }}",
      "name": "Dunes Discovery Tourism",
      "publisher": {
        "@@id": "{{ route('home') }}#organization"
      },
      "potentialAction": {
        "@@type": "SearchAction",
        "target": "{{ route('tours.index') }}?category={search_term_string}",
        "query-input": "required name=search_term_string"
      }
    },
    {
      "@@type": "VideoObject",
      "@@id": "{{ route('home') }}#video",
      "name": "Dubai Desert Safari Experience - Dunes Discovery Tourism",
      "description": "Experience thrilling dune bashing across the Lahbab Red Dunes, sandboarding, 1000cc dune buggy rentals, and 5-star live BBQ dinner under the desert stars.",
      "thumbnailUrl": ["{{ asset('images/desert-safari-poster.avif') }}"],
      "uploadDate": "2026-01-01T00:00:00+04:00",
      "contentUrl": "{{ asset('images/desert-safar-dubai-tour-short-dune-discovery-tourism.mp4') }}",
      "publisher": {
        "@@id": "{{ route('home') }}#organization"
      }
    }
    @if(isset($faqs) && $faqs->count() > 0)
    ,
    {
      "@@type": "FAQPage",
      "@@id": "{{ route('home') }}#faq",
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

<!-- Modern Hero Section -->
<section class="hero-modern position-relative d-flex align-items-center justify-content-center overflow-hidden" style="min-height: 90vh;">
    <video class="hero-video position-absolute top-0 start-0 w-100 h-100" autoplay loop muted playsinline id="heroVideo" poster="{{ asset('images/desert-safari-poster.avif') }}" fetchpriority="high" aria-hidden="true" style="object-fit: cover; z-index: -1;">
        <track kind="captions" src="" label="English" srclang="en">
    </video>
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(0, 0, 0, 0.4) 0%, rgba(0, 0, 0, 0.8) 100%); z-index: -1;"></div>

    <div class="container position-relative z-1 text-center text-white" style="margin-top:-5vh;">
        <div class="d-flex justify-content-center mb-2">
            @include('partials.sunset-weather-widget')
        </div>
        <div class="badge rounded-pill glass px-3 py-2 mb-4">
            <i class="bi bi-star-fill text-warning me-2"></i>
            <span class="fw-semibold">Rated 4.9/5 by 2,847+ Travelers</span>
        </div>
        <h1 class="display-3 fw-bold mb-3 text-white">Top-Rated <span class="text-gradient-primary">Dubai Desert Safari</span> Tours & Adventures</h1>
        <p class="lead mb-5 mx-auto opacity-90" style="max-width: 750px;">Experience thrilling dune bashing across the Lahbab Red Dunes, 1000cc dune buggy rentals, magical sunsets, authentic live BBQ dinner, and 5-star entertainment under the desert stars.</p>

        <div class="d-flex flex-column flex-md-row gap-3 justify-content-center align-items-center mb-5">
            <a href="#" class="btn btn-desert-animated btn-lg rounded-pill px-5 py-3 shadow-primary fw-bold" data-bs-toggle="modal" data-bs-target="#bookingModal">
                <i class="bi bi-calendar-check me-2"></i>Book Online Now
            </a>
            <a href="#" class="btn btn-desert-animated-dark btn-lg rounded-pill px-5 py-3 fw-bold d-inline-flex align-items-center gap-2" data-action="open-booking" data-tour="1" data-tier="1">
                <span class="fw-bold me-2 text-white">Starting from</span>
                <span class="fs-4 fw-bold text-primary">AED 79</span>
            </a>
        </div>

        <div class="container mb-4">
            <div class="row justify-content-center">
                <div class="col-lg-9 text-center">
                    <p class="h5 fw-bold text-white mb-2">Dubai Desert Safari with Luxury Land Cruiser Pick & Drop</p>
                    <p class="text-white-50 mb-0 px-2 px-md-5">Enjoy a premium desert safari experience with chauffeur-driven Luxury Land Cruiser hotel pickup and drop-off. Comfortable seating, professional drivers, and hassle-free transfers included with every booking.</p>
                </div>
            </div>
        </div>

        <div class="row g-4 justify-content-center opacity-75">
            <div class="col-4 col-md-auto">
                <div class="h3 fw-bold mb-0 text-white">10K+</div>
                <small class="text-uppercase fw-semibold" style="font-size: 10px; letter-spacing: 1px;">Happy Guests</small>
            </div>
            <div class="col-4 col-md-auto border-start border-end border-white border-opacity-25 px-md-4">
                <div class="h3 fw-bold mb-0 text-white">4.9/5</div>
                <small class="text-uppercase fw-semibold" style="font-size: 10px; letter-spacing: 1px;">Top Rated</small>
            </div>
            <div class="col-4 col-md-auto">
                <div class="h3 fw-bold mb-0 text-white">24/7</div>
                <small class="text-uppercase fw-semibold" style="font-size: 10px; letter-spacing: 1px;">Support</small>
            </div>
        </div>
    </div>
</section>

<!-- Regulatory E-E-A-T & Trust Bar -->
<section class="bg-light py-3 border-bottom shadow-sm">
    <div class="container">
        <div class="row g-3 text-center align-items-center">
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-patch-check-fill text-primary fs-5"></i>
                    <div class="text-start">
                        <div class="fw-bold text-dark small lh-1">DTCM Licensed Operator</div>
                        <small class="text-muted" style="font-size: 11px;">Dubai Tourism Authority</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-arrow-repeat text-success fs-5"></i>
                    <div class="text-start">
                        <div class="fw-bold text-dark small lh-1">100% Free Cancellation</div>
                        <small class="text-muted" style="font-size: 11px;">Full refund 24h prior</small>
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
                        <div class="fw-bold text-dark small lh-1">Secure Reservation</div>
                        <small class="text-muted" style="font-size: 11px;">Online / Cash on Pickup</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Bar Section -->
<div class="stats-bar bg-dark py-4 shadow-lg position-relative z-2" style="border-radius: 0;">
    <div class="container">
        <div class="row g-0 text-center stats-grid">
            <div class="col-4 col-lg-2 stats-item">
                <i class="bi bi-trophy text-primary fs-3 mb-2 d-block"></i>
                <div class="h4 fw-bold text-white mb-0">#1</div>
                <small class="text-white text-opacity-50 small">Desert Safari</small>
            </div>
            <div class="col-4 col-lg-2 stats-item">
                <i class="bi bi-shield-check text-primary fs-3 mb-2 d-block"></i>
                <div class="h4 fw-bold text-white mb-0">100%</div>
                <small class="text-white text-opacity-50 small">Secure Pay</small>
            </div>
            <div class="col-4 col-lg-2 stats-item">
                <i class="bi bi-clock-history text-primary fs-3 mb-2 d-block"></i>
                <div class="h4 fw-bold text-white mb-0">Fast</div>
                <small class="text-white text-opacity-50 small">Booking</small>
            </div>
            <div class="col-4 col-lg-2 stats-item">
                <i class="bi bi-truck text-primary fs-3 mb-2 d-block"></i>
                <div class="h4 fw-bold text-white mb-0">25+</div>
                <small class="text-white text-opacity-50 small">Vehicles</small>
            </div>
            <div class="col-4 col-lg-2 stats-item">
                <i class="bi bi-geo-alt text-primary fs-3 mb-2 d-block"></i>
                <div class="h4 fw-bold text-white mb-0">Local</div>
                <small class="text-white text-opacity-50 small">Expert Guides</small>
            </div>
            <div class="col-4 col-lg-2 stats-item">
                <i class="bi bi-star text-primary fs-3 mb-2 d-block"></i>
                <div class="h4 fw-bold text-white mb-0">Best</div>
                <small class="text-white text-opacity-50 small">Price Promise</small>
            </div>
        </div>
    </div>
</div>

<!-- Category Silos Section: Explore Dubai by Adventure Type -->
<section class="section py-5 bg-white">
    <div class="container py-lg-3">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Explore Dubai <span class="text-primary">by Experience</span></h2>
            <p class="text-muted lead mx-auto" style="max-width: 650px;">Choose from our signature desert expeditions, high-power self-drive rentals, skyline cruises, and cultural city tours.</p>
        </div>

        <div class="row g-4">
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100 border-0 rounded-4 shadow-sm overflow-hidden transition-all hover-translate-up bg-light">
                    <div class="p-4 d-flex flex-column h-100">
                        <div class="icon-box-md bg-soft-primary text-primary rounded-3 mb-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-sunset-fill fs-4"></i>
                        </div>
                        <h3 class="h5 fw-bold text-dark mb-2">Evening Desert Safari</h3>
                        <p class="text-muted small flex-grow-1">Red dune bashing, camel rides, sandboarding, live Tanoura & fire shows, plus a 5-star live BBQ dinner.</p>
                        <a href="{{ url('/evening-desert-safari-dubai') }}" class="btn btn-outline-primary btn-sm rounded-pill fw-bold mt-2">
                            Explore Safaris <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100 border-0 rounded-4 shadow-sm overflow-hidden transition-all hover-translate-up bg-light">
                    <div class="p-4 d-flex flex-column h-100">
                        <div class="icon-box-md bg-soft-primary text-primary rounded-3 mb-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-speedometer2 fs-4"></i>
                        </div>
                        <h3 class="h5 fw-bold text-dark mb-2">Dune Buggy & ATV</h3>
                        <p class="text-muted small flex-grow-1">Self-drive 1000cc Can-Am Maverick and Polaris RZR buggies across the untamed Lahbab dunes.</p>
                        <a href="{{ url('/dune-buggy-rental-dubai') }}" class="btn btn-outline-primary btn-sm rounded-pill fw-bold mt-2">
                            Explore Buggies <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100 border-0 rounded-4 shadow-sm overflow-hidden transition-all hover-translate-up bg-light">
                    <div class="p-4 d-flex flex-column h-100">
                        <div class="icon-box-md bg-soft-primary text-primary rounded-3 mb-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-water fs-4"></i>
                        </div>
                        <h3 class="h5 fw-bold text-dark mb-2">Marina Dhow Cruise</h3>
                        <p class="text-muted small flex-grow-1">Gourmet international buffet dinner cruise along the illuminated Dubai Marina & JBR skyline.</p>
                        <a href="{{ url('/dhow-cruise-catamaran-cruise-dinner-dubai') }}" class="btn btn-outline-primary btn-sm rounded-pill fw-bold mt-2">
                            Explore Cruises <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100 border-0 rounded-4 shadow-sm overflow-hidden transition-all hover-translate-up bg-light">
                    <div class="p-4 d-flex flex-column h-100">
                        <div class="icon-box-md bg-soft-primary text-primary rounded-3 mb-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-building fs-4"></i>
                        </div>
                        <h3 class="h5 fw-bold text-dark mb-2">Abu Dhabi City Tour</h3>
                        <p class="text-muted small flex-grow-1">Chauffeured full-day luxury sightseeing tour to the Sheikh Zayed Grand Mosque & Louvre Museum.</p>
                        <a href="{{ url('/abu-dhabi-city-tour-from-dubai') }}" class="btn btn-outline-primary btn-sm rounded-pill fw-bold mt-2">
                            Explore Tours <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Popular Tours Section -->
<section class="section py-5">
    <div class="container py-lg-4">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Popular <span class="text-primary">Tours</span></h2>
            <p class="text-muted lead mx-auto" style="max-width: 600px;">Handpicked experiences for unforgettable memories in the heart of Dubai.</p>
        </div>

        <div class="row g-4 mb-5">
            @foreach($bestsellers as $t)
                @php
                    $minPrice = $t->tiers->min('pivot.price') ?? 0;
                    $category = $categories->firstWhere('id', $t->category_id);
                @endphp
                <div class="col-12 col-md-6 col-lg-3">
                    <article class="card card-modern h-100 border-0 shadow-sm">
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
                                        <i class="bi bi-tag-fill me-1"></i>{{ $category ? $category->name : 'Tours' }}
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
                                        <span class="h5 fw-bold text-primary mb-0">AED {{ number_format($minPrice) }}</span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn-circle-whatsapp fab-whatsapp" data-tour-name="{{ $t->name }}" aria-label="Book {{ $t->name }} via WhatsApp">
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

        <div class="text-center">
            <a href="{{ route('tours.index') }}" class="btn btn-desert-animated-dark btn-lg rounded-pill px-5 py-3 fw-bold">
                View All Tours <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Interactive Safari Matcher Quiz -->
@include('partials.safari-matcher-quiz')

<!-- Guest Reviews Marquee Section -->
<section class="reviews-section bg-soft-primary py-5 overflow-hidden">
    <div class="container mb-5 py-lg-4">
        <div class="text-center">
            <h2 class="display-5 fw-bold mb-3">What Our <span class="text-primary">Guests Say</span></h2>
            <p class="text-muted lead">Real reviews from real travelers around the world.</p>
        </div>
    </div>

    @php
        $googleReviews = $reviews->where('source', 'google');
        $tripReviews = $reviews->where('source', 'tripadvisor');
    @endphp

    <div class="reviews-marquee mb-4">
        <div class="reviews-track d-flex gap-4">
            @foreach($googleReviews as $r)
                {!! renderReviewCardMarkup($r) !!}
            @endforeach
            @foreach($googleReviews as $r)
                {!! renderReviewCardMarkup($r) !!}
            @endforeach
        </div>
    </div>

    <div class="reviews-marquee reverse">
        <div class="reviews-track d-flex gap-4">
            @foreach($tripReviews as $r)
                {!! renderReviewCardMarkup($r) !!}
            @endforeach
            @foreach($tripReviews as $r)
                {!! renderReviewCardMarkup($r) !!}
            @endforeach
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="section py-5">
    <div class="container py-lg-4">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Why <span class="text-primary">Choose Us</span></h2>
            <p class="text-muted lead mx-auto" style="max-width: 600px;">Trusted by thousands of travelers worldwide for premium desert experiences.</p>
        </div>

        <div class="row g-4">
            <div class="col-12 col-md-4">
                <div class="card card-modern h-100 p-4 p-lg-5 text-center border-0 shadow-sm">
                    <div class="icon-box mx-auto mb-3 d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle" style="width: 60px; height: 60px; font-size: 24px;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3 class="h4 fw-bold mb-3">Best Price Guarantee</h3>
                    <p class="text-muted mb-0">We match any competitor price. No hidden fees, what you see is what you pay.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card card-modern h-100 p-4 p-lg-5 text-center border-0 shadow-sm">
                    <div class="icon-box mx-auto mb-3 d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle" style="width: 60px; height: 60px; font-size: 24px;">
                        <i class="bi bi-lightning-charge"></i>
                    </div>
                    <h3 class="h4 fw-bold mb-3">Instant Confirmation</h3>
                    <p class="text-muted mb-0">Receive immediate booking confirmation via email and WhatsApp.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card card-modern h-100 p-4 p-lg-5 text-center border-0 shadow-sm">
                    <div class="icon-box mx-auto mb-3 d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle" style="width: 60px; height: 60px; font-size: 24px;">
                        <i class="bi bi-calendar-x"></i>
                    </div>
                    <h3 class="h4 fw-bold mb-3">Free Cancellation</h3>
                    <p class="text-muted mb-0">Cancel up to 24 hours before for a full refund. Flexibility guaranteed.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- GEO & AI Search Entity Knowledge Guide -->
<section class="section py-5 bg-white border-top">
    <div class="container py-lg-3">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Dubai Desert Safari <span class="text-primary">Essential Guide</span></h2>
            <p class="text-muted lead mx-auto" style="max-width: 700px;">Key facts, locations, and guidelines to help you plan the perfect Arabian desert adventure.</p>
        </div>

        <div class="row g-4">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-0 bg-soft-primary p-4 rounded-4 shadow-sm border-start border-4 border-primary">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-geo-alt-fill text-primary fs-4"></i>
                        <h3 class="h5 fw-bold text-dark mb-0">Location & Dunes</h3>
                    </div>
                    <p class="text-muted small mb-0">Our desert safaris take place in the iconic <strong>Lahbab Red Dunes</strong> (Big Red) and the Dubai Desert Conservation Area, celebrated for deep terracotta-colored sand dunes reaching heights over 300 feet.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-0 bg-soft-primary p-4 rounded-4 shadow-sm border-start border-4 border-primary">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-truck text-primary fs-4"></i>
                        <h3 class="h5 fw-bold text-dark mb-0">4x4 Fleet & Safety</h3>
                    </div>
                    <p class="text-muted small mb-0">Every transfer is conducted in modern, climate-controlled <strong>4x4 Toyota Land Cruisers</strong> equipped with reinforced roll cages, comprehensive passenger insurance, and RTA-certified desert marshals.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-0 bg-soft-primary p-4 rounded-4 shadow-sm border-start border-4 border-primary">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-cup-hot-fill text-primary fs-4"></i>
                        <h3 class="h5 fw-bold text-dark mb-0">Dining & Dietary Options</h3>
                    </div>
                    <p class="text-muted small mb-0">Experience a 5-star <strong>100% Halal live BBQ buffet</strong> prepared fresh at our Bedouin-style camp, featuring dedicated counters for Vegetarian, Non-Vegetarian, Jain, and Gluten-Free dining options.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-0 bg-soft-primary p-4 rounded-4 shadow-sm border-start border-4 border-primary">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-clock-history text-primary fs-4"></i>
                        <h3 class="h5 fw-bold text-dark mb-0">Timing & Duration</h3>
                    </div>
                    <p class="text-muted small mb-0"><strong>Evening Safaris</strong> run from 2:30 PM to 9:30 PM (6-7 hours total). <strong>Morning Safaris</strong> run from 7:00 AM to 11:30 AM (4 hours). Chauffeur hotel pick and drop is included across Dubai & Sharjah.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-0 bg-soft-primary p-4 rounded-4 shadow-sm border-start border-4 border-primary">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-people-fill text-primary fs-4"></i>
                        <h3 class="h5 fw-bold text-dark mb-0">Family & Child Safety</h3>
                    </div>
                    <p class="text-muted small mb-0">Families with children or seniors can request <strong>child safety booster seats</strong> and gentle non-dune-bashing direct scenic transfers to the camp for a relaxing evening experience.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-0 bg-soft-primary p-4 rounded-4 shadow-sm border-start border-4 border-primary">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-patch-check-fill text-primary fs-4"></i>
                        <h3 class="h5 fw-bold text-dark mb-0">Booking & Cancellation</h3>
                    </div>
                    <p class="text-muted small mb-0">Reserve instantly with <strong>zero advance payment</strong> (Cash on Pickup) or secure card payment. Enjoy <strong>100% full refund</strong> on cancellations made up to 24 hours prior to departure.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQs Section -->
<section class="section py-5 bg-light">
    <div class="container py-lg-4">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Common <span class="text-primary">Questions</span></h2>
            <p class="text-muted lead">Everything you need to know about our desert safari tours.</p>
        </div>
        <div class="accordion accordion-flush mx-auto" id="faqAccordion" style="max-width: 800px;">
            @foreach($faqs as $index => $f)
            <div class="accordion-item border-animated bg-white mb-3 rounded-4 border-0 shadow-sm">
                <h3 class="accordion-header">
                    <button class="accordion-button collapsed rounded-4 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq-{{ $index }}">
                        {{ $f->question }}
                    </button>
                </h3>
                <div id="faq-{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-muted">
                        {{ $f->answer }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-5 pb-3">
            <a href="{{ route('faq') }}" class="btn btn-desert-animated rounded-pill px-5 py-3 d-inline-flex align-items-center gap-2">
                View All FAQs <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- CTA booking banner -->
<section class="cta-section py-5 py-lg-6 position-relative text-white">
    <div class="container position-relative z-1 text-center py-4">
        <h2 class="display-4 fw-bold mb-3 text-white">Ready for Your Desert Adventure?</h2>
        <p class="lead mb-4 opacity-90 mx-auto" style="max-width: 620px;">Book now and create memories that last a lifetime. Free cancellation up to 24 hours before.</p>
        <div class="pt-2">
            <a href="#" class="btn btn-cta-white btn-lg rounded-pill px-5 py-3.5 fw-bold fs-5" data-bs-toggle="modal" data-bs-target="#bookingModal">
                <i class="bi bi-calendar-check-fill me-2 text-primary"></i>Book Your Tour Now
            </a>
        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var loaded = false;
    function loadHeroVideo() {
        if (loaded) return;
        loaded = true;
        var v = document.getElementById('heroVideo');
        if (v && !v.querySelector('source')) {
            var s = document.createElement('source');
            s.src = "{{ asset('images/desert-safar-dubai-tour-short-dune-discovery-tourism.mp4') }}";
            s.type = "video/mp4";
            v.appendChild(s);
            v.load();
        }
    }
    setTimeout(loadHeroVideo, 4000);
    ['touchstart', 'scroll', 'pointermove'].forEach(function(ev) {
        window.addEventListener(ev, loadHeroVideo, { once: true, passive: true });
    });
});
</script>
@endpush

@endsection
