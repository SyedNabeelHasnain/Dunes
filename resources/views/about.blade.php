@extends('layouts.app')

@section('content')
@push('preloads')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": "AboutPage",
      "@@id": "{{ route('about') }}#webpage",
      "url": "{{ route('about') }}",
      "name": "About Dunes Discovery Tourism Dubai",
      "description": "About Dunes Discovery Tourism LLC - Licensed Dubai Destination Management Company offering premium Desert Safaris, Dune Buggy rentals, and luxury tours since 2018.",
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
            "name": "About Us",
            "item": "{{ route('about') }}"
          }
        ]
      },
      "mainEntity": {
        "@@type": "TravelAgency",
        "@@id": "{{ route('home') }}#organization",
        "name": "Dunes Discovery Tourism LLC",
        "url": "{{ route('home') }}",
        "logo": "{{ asset('images/logo.png') }}",
        "telephone": "+971 50 245 6056",
        "email": "info@dunesdiscoverytourism.com",
        "foundingDate": "2018",
        "address": {
          "@@type": "PostalAddress",
          "streetAddress": "Dubai Desert Safari Terminal, Al Aweer & Lahbab",
          "addressLocality": "Dubai",
          "addressRegion": "Dubai",
          "postalCode": "00000",
          "addressCountry": "AE"
        }
      }
    }
  ]
}
</script>
@endpush

<!-- Page Header Section -->
<section class="page-header py-4 bg-dark text-white position-relative overflow-hidden" style="margin-top: -var(--header-h);">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at 15% 20%, rgba(246, 144, 68, 0.15) 0%, transparent 60%);"></div>
    <div class="container position-relative z-1 pt-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white text-opacity-75 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">About Us</li>
            </ol>
        </nav>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
            <div>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <span class="badge glass rounded-pill px-3 py-1.5">
                        <i class="bi bi-calendar3 me-1 text-primary"></i>Trusted Since 2018
                    </span>
                    <span class="badge bg-success bg-opacity-75 rounded-pill px-3 py-1.5 text-white">
                        <i class="bi bi-patch-check-fill me-1"></i>DTCM Licensed Operator
                    </span>
                </div>
                <h1 class="display-4 fw-bold text-white mb-2">About Dunes Discovery Tourism</h1>
                <p class="lead text-white text-opacity-75 mb-0">Your licensed destination management partner for authentic Arabian desert expeditions & luxury Dubai tours.</p>
            </div>
            <div class="d-none d-lg-block text-end">
                <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill fw-bold fs-6">
                    <i class="bi bi-star-fill text-warning me-1"></i>10,000+ Happy Guests
                </span>
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
                    <i class="bi bi-truck text-info fs-5"></i>
                    <div class="text-start">
                        <div class="fw-bold text-dark small lh-1">25+ Luxury 4x4 Fleet</div>
                        <small class="text-muted" style="font-size: 11px;">Land Cruiser 300 Series</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section py-5">
    <div class="container py-lg-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="position-relative">
                    <img src="{{ asset('images/dubai-desert-safari-tour-dune-discovery-tourism.avif') }}" alt="Dunes Discovery Story" class="img-fluid rounded-4 shadow-lg" onerror="this.src='https://placehold.co/800x600/F58F43/white?text=Our+Story'">
                    <div class="position-absolute bottom-0 end-0 bg-primary text-white p-4 rounded-4 shadow-lg d-none d-md-block" style="margin-bottom: -30px; margin-right: -30px;">
                        <div class="h4 fw-bold mb-0">6+ Years</div>
                        <p class="small mb-0 opacity-75">Of Excellence</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ps-lg-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-bold">OUR STORY</span>
                    </div>
                    <h2 class="display-6 fw-bold mb-4">Crafting Unforgettable Arabian Experiences</h2>
                    <p class="text-secondary mb-4 lead">Founded in 2018, Dunes Discovery Tourism has grown from a small family operation to one of Dubai's most trusted tour companies.</p>
                    <p class="text-secondary mb-4">Our passion for the Arabian desert and commitment to exceptional service has made us the preferred choice for travelers from around the world. We specialize in authentic desert safari experiences that blend adventure, culture, and comfort.</p>
                    <div class="row g-4 mt-2">
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-box-sm bg-primary-subtle text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-check-lg fw-bold"></i>
                                </div>
                                <span class="fw-semibold">Licensed & Insured</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-box-sm bg-primary-subtle text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-check-lg fw-bold"></i>
                                </div>
                                <span class="fw-semibold">Modern Fleet</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section py-5 bg-light">
    <div class="container py-lg-4">
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold mb-3">Why Choose Us</h2>
            <p class="text-secondary mx-auto" style="max-width: 600px;">We go the extra mile to ensure your Dubai adventure is nothing short of perfect.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="card card-modern h-100 p-4 border-0 shadow-sm bg-white">
                    <div class="icon-box mb-3 d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle" style="width: 50px; height: 50px; font-size: 20px;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3 class="h4 fw-bold">Licensed & Insured</h3>
                    <p class="text-secondary mb-0">Fully licensed by Dubai Tourism with comprehensive insurance for all guests, ensuring your peace of mind.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card card-modern h-100 p-4 border-0 shadow-sm bg-white">
                    <div class="icon-box mb-3 d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle" style="width: 50px; height: 50px; font-size: 20px;">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3 class="h4 fw-bold">Expert Team</h3>
                    <p class="text-secondary mb-0">Professional drivers with years of desert experience and multilingual guides who know the dunes like no one else.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card card-modern h-100 p-4 border-0 shadow-sm bg-white">
                    <div class="icon-box mb-3 d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle" style="width: 50px; height: 50px; font-size: 20px;">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <h3 class="h4 fw-bold">Award Winning</h3>
                    <p class="text-secondary mb-0">Consistently rated 4.8+ stars across Google, TripAdvisor, and other platforms for our service quality.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card card-modern h-100 p-4 border-0 shadow-sm bg-white">
                    <div class="icon-box mb-3 d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle" style="width: 50px; height: 50px; font-size: 20px;">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h3 class="h4 fw-bold">Modern Fleet</h3>
                    <p class="text-secondary mb-0">Well-maintained Toyota Land Cruisers equipped with the latest safety features and powerful air conditioning.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card card-modern h-100 p-4 border-0 shadow-sm bg-white">
                    <div class="icon-box mb-3 d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle" style="width: 50px; height: 50px; font-size: 20px;">
                        <i class="bi bi-heart"></i>
                    </div>
                    <h3 class="h4 fw-bold">Guest First</h3>
                    <p class="text-secondary mb-0">Personalized service with attention to dietary needs, celebrations, and special requests to make it yours.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card card-modern h-100 p-4 border-0 shadow-sm bg-white">
                    <div class="icon-box mb-3 d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle" style="width: 50px; height: 50px; font-size: 20px;">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <h3 class="h4 fw-bold">Best Value</h3>
                    <p class="text-secondary mb-0">Competitive prices with no hidden fees. What you see is what you pay. Quality adventure at the right price.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section py-5">
    <div class="container">
        <div class="bg-dark rounded-4 p-2 p-lg-3 shadow-xl position-relative overflow-hidden">
            <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at 10% 20%, rgba(246, 144, 68, 0.12) 0%, transparent 55%);"></div>
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
</section>
@endsection
