@extends('layouts.app')

@push('schema')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": "WebPage",
      "@@id": "{{ $canonical }}#webpage",
      "url": "{{ $canonical }}",
      "name": "{{ $locationData['meta_title'] }}",
      "description": "{{ $locationData['meta_desc'] }}",
      "isPartOf": {
        "@@type": "WebSite",
        "@@id": "{{ url('/') }}#website",
        "name": "Dunes Discovery Tourism",
        "url": "{{ url('/') }}"
      },
      "breadcrumb": {
        "@@type": "BreadcrumbList",
        "itemListElement": [
          {
            "@@type": "ListItem",
            "position": 1,
            "name": "Home",
            "item": "{{ url('/') }}"
          },
          {
            "@@type": "ListItem",
            "position": 2,
            "name": "Desert Safari Dubai",
            "item": "{{ route('tours.index') }}"
          },
          {
            "@@type": "ListItem",
            "position": 3,
            "name": "{{ $locationData['name'] }} Pickup",
            "item": "{{ $canonical }}"
          }
        ]
      }
    },
    {
      "@@type": "TouristTrip",
      "@@id": "{{ $canonical }}#trip",
      "name": "{{ $locationData['headline'] }}",
      "description": "{{ $locationData['subheadline'] }}",
      "provider": {
        "@@type": "TravelAgency",
        "name": "Dunes Discovery Tourism LLC",
        "url": "{{ url('/') }}",
        "telephone": "+971502456056",
        "license": "DET License #1430583"
      },
      "touristType": ["Adventure Tourists", "Couples", "Families", "Luxury Travelers"],
      "itinerary": {
        "@@type": "ItemList",
        "numberOfItems": 6,
        "itemListElement": [
          {
            "@@type": "ListItem",
            "position": 1,
            "name": "Doorstep Pickup from {{ $locationData['name'] }} ({{ $locationData['pickup_window'] }})"
          },
          {
            "@@type": "ListItem",
            "position": 2,
            "name": "High Dune Bashing at Lahbab Red Dunes"
          },
          {
            "@@type": "ListItem",
            "position": 3,
            "name": "Sunset Photography & Sandboarding"
          },
          {
            "@@type": "ListItem",
            "position": 4,
            "name": "Bedouin Camp Welcome (Arabic Coffee, Dates & Camel Riding)"
          },
          {
            "@@type": "ListItem",
            "position": 5,
            "name": "5-Star Live BBQ Buffet with Tanoura & Fire Shows"
          },
          {
            "@@type": "ListItem",
            "position": 6,
            "name": "Return Drop-off to {{ $locationData['name'] }} ({{ $locationData['return_time'] }})"
          }
        ]
      },
      "areaServed": {
        "@@type": "Place",
        "name": "{{ $locationData['district'] }}",
        "geo": {
          "@@type": "GeoCoordinates",
          "latitude": {{ $locationData['geo']['lat'] }},
          "longitude": {{ $locationData['geo']['lng'] }}
        }
      },
      "offers": {
        "@@type": "AggregateOffer",
        "priceCurrency": "AED",
        "lowPrice": "99",
        "highPrice": "450",
        "offerCount": "{{ count($safariTours) }}"
      },
      "aggregateRating": {
        "@@type": "AggregateRating",
        "ratingValue": "4.9",
        "reviewCount": "1250",
        "bestRating": "5",
        "worstRating": "1"
      }
    },
    {
      "@@type": "FAQPage",
      "@@id": "{{ $canonical }}#faq",
      "mainEntity": [
        @foreach($locationData['faqs'] as $index => $faq)
        {
          "@@type": "Question",
          "name": "{!! addslashes($faq['q']) !!}",
          "acceptedAnswer": {
            "@@type": "Answer",
            "text": "{!! addslashes($faq['a']) !!}"
          }
        }{{ $loop->last ? '' : ',' }}
        @endforeach
      ]
    }
  ]
}
</script>
@endpush

@push('styles')
<style>
.loc-page {
    background-color: #0B1120;
    color: #F8FAFC;
    min-height: 100vh;
}
.loc-hero {
    position: relative;
    padding: 130px 0 70px;
    background: radial-gradient(circle at 80% 20%, rgba(246, 144, 68, 0.18) 0%, transparent 60%),
                radial-gradient(circle at 10% 80%, rgba(30, 41, 59, 0.8) 0%, transparent 70%),
                #0F172A;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.loc-badge-primary {
    background: rgba(246, 144, 68, 0.15);
    color: #F69044;
    border: 1px solid rgba(246, 144, 68, 0.35);
    font-size: 0.85rem;
    padding: 6px 14px;
    border-radius: 9999px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 700;
}
.loc-badge-dark {
    background: rgba(255, 255, 255, 0.06);
    color: #E2E8F0;
    border: 1px solid rgba(255, 255, 255, 0.12);
    font-size: 0.82rem;
    padding: 6px 14px;
    border-radius: 9999px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.loc-stats-pill {
    background: rgba(30, 41, 59, 0.7);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    padding: 14px 20px;
    transition: transform 0.2s ease, border-color 0.2s ease;
}
.loc-stats-pill:hover {
    transform: translateY(-2px);
    border-color: rgba(246, 144, 68, 0.4);
}
.loc-card-feature {
    background: #1E293B;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    padding: 24px;
    height: 100%;
    transition: all 0.25s ease;
}
.loc-card-feature:hover {
    transform: translateY(-4px);
    border-color: rgba(246, 144, 68, 0.4);
    box-shadow: 0 16px 32px -12px rgba(246, 144, 68, 0.15);
}
.loc-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: rgba(246, 144, 68, 0.15);
    color: #F69044;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    margin-bottom: 16px;
}
.loc-tour-card {
    background: #1E293B;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 22px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: 100%;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.loc-tour-card:hover {
    transform: translateY(-5px);
    border-color: #F69044;
    box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.6);
}
.loc-tour-img-wrap {
    position: relative;
    height: 220px;
    overflow: hidden;
}
.loc-tour-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}
.loc-tour-card:hover .loc-tour-img-wrap img {
    transform: scale(1.05);
}
.loc-tour-body {
    padding: 22px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}
.loc-timeline-dot {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #F69044;
    color: #0F172A;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
    box-shadow: 0 0 16px rgba(246, 144, 68, 0.5);
}
.loc-accordion .accordion-item {
    background: #1E293B;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px !important;
    margin-bottom: 12px;
    overflow: hidden;
}
.loc-accordion .accordion-button {
    background: #1E293B;
    color: #FFFFFF;
    font-weight: 700;
    box-shadow: none !important;
    padding: 18px 22px;
}
.loc-accordion .accordion-button:not(.collapsed) {
    color: #F69044;
    background: rgba(246, 144, 68, 0.08);
}
.loc-accordion .accordion-button::after {
    filter: invert(1);
}
.loc-accordion .accordion-body {
    background: #1E293B;
    color: #CBD5E1;
    line-height: 1.7;
    padding: 20px 22px;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}
.loc-hub-card {
    background: rgba(30, 41, 59, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    padding: 18px;
    text-decoration: none;
    display: block;
    transition: all 0.2s ease;
}
.loc-hub-card:hover {
    background: #1E293B;
    border-color: #F69044;
    transform: translateY(-3px);
}
.btn-desert-animated {
    background: linear-gradient(135deg, #F69044 0%, #EA580C 100%);
    color: #FFFFFF !important;
    border: none;
    font-weight: 700;
    transition: all 0.25s ease;
}
.btn-desert-animated:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(246, 144, 68, 0.5);
    color: #FFFFFF !important;
}
.landmark-tag {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #E2E8F0;
    border-radius: 9999px;
    padding: 6px 14px;
    font-size: 0.82rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
</style>
@endpush

@section('content')
<div class="loc-page">

    <!-- ── HERO SECTION ──────────────────────────────────────────────────────── -->
    <section class="loc-hero">
        <div class="container">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb small mb-0" style="--bs-breadcrumb-divider-color: rgba(255,255,255,0.4);">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-white-50 text-decoration-none"><i class="bi bi-house-door me-1"></i>Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('tours.index') }}" class="text-white-50 text-decoration-none">Desert Safaris</a></li>
                    <li class="breadcrumb-item active text-warning" aria-current="page">{{ $locationData['name'] }} Pickup</li>
                </ol>
            </nav>

            <div class="row align-items-center g-5">
                <div class="col-12 col-lg-8">
                    <!-- Badges -->
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="loc-badge-primary">
                            <i class="bi bi-geo-alt-fill"></i> Official {{ $locationData['name'] }} Hub
                        </span>
                        <span class="loc-badge-dark">
                            <i class="bi bi-shield-check text-success"></i> DET License #1430583
                        </span>
                        <span class="loc-badge-dark">
                            <i class="bi bi-stars text-warning"></i> 4.9/5 (1,250+ Reviews)
                        </span>
                    </div>

                    <!-- Headline & Subheadline -->
                    <h1 class="display-5 fw-bold text-white mb-3">
                        {{ $locationData['headline'] }}
                    </h1>
                    <p class="lead text-light opacity-90 mb-4" style="max-width: 720px; font-size: 1.15rem; line-height: 1.7;">
                        {{ $locationData['subheadline'] }} Enjoy seamless, stress-free desert adventures with verified door-to-door 4x4 transfers, desert master captains, live BBQ buffet feasts, and sunset dune bashing.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="d-flex flex-wrap gap-3 mb-5">
                        <a href="#safari-packages" class="btn btn-desert-animated btn-lg rounded-pill px-4 py-3 shadow">
                            <i class="bi bi-compass me-2"></i> View Safaris & Reserve Seats
                        </a>
                        <a href="https://wa.me/971502456056?text={{ urlencode('Hi Dunes Discovery Tourism, I am staying in ' . $locationData['name'] . ' and would like to inquire about Desert Safari hotel pickup.') }}" target="_blank" rel="noopener" class="btn btn-outline-light btn-lg rounded-pill px-4 py-3">
                            <i class="bi bi-whatsapp text-success me-2"></i> WhatsApp Concierge
                        </a>
                    </div>

                    <!-- Quick Spec Pills -->
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="loc-stats-pill text-center">
                                <small class="text-white-50 d-block mb-1 text-uppercase fw-bold" style="font-size: 0.72rem;">Pickup Window</small>
                                <span class="fw-bold text-warning small"><i class="bi bi-clock me-1"></i>{{ $locationData['pickup_window'] }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="loc-stats-pill text-center">
                                <small class="text-white-50 d-block mb-1 text-uppercase fw-bold" style="font-size: 0.72rem;">Return Time</small>
                                <span class="fw-bold text-white small"><i class="bi bi-moon-stars me-1"></i>{{ $locationData['return_time'] }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="loc-stats-pill text-center">
                                <small class="text-white-50 d-block mb-1 text-uppercase fw-bold" style="font-size: 0.72rem;">Transit Time</small>
                                <span class="fw-bold text-white small"><i class="bi bi-speedometer2 me-1"></i>40-50 Mins Direct</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="loc-stats-pill text-center">
                                <small class="text-white-50 d-block mb-1 text-uppercase fw-bold" style="font-size: 0.72rem;">Pickup Fleet</small>
                                <span class="fw-bold text-success small"><i class="bi bi-truck me-1"></i>4x4 Land Cruiser</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4 d-none d-lg-block">
                    <!-- Trust Card Overlay -->
                    <div class="p-4 rounded-4" style="background: rgba(30, 41, 59, 0.85); border: 1px solid rgba(255, 255, 255, 0.12); backdrop-filter: blur(16px); box-shadow: 0 20px 50px rgba(0,0,0,0.5);">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(246, 144, 68, 0.2); color: #F69044;">
                                <i class="bi bi-patch-check-fill fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-white mb-0">Doorstep Guaranteed</h6>
                                <small class="text-white-50">Zero bus transfers or walking</small>
                            </div>
                        </div>

                        <ul class="list-unstyled d-flex flex-column gap-3 mb-4 small text-light">
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check2-circle text-success fs-6 mt-0.5"></i>
                                <span><strong>Hotel Lobby Pickup:</strong> Direct lobby pickup from all major hotels & residences across {{ $locationData['name'] }}.</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check2-circle text-success fs-6 mt-0.5"></i>
                                <span><strong>Live WhatsApp Updates:</strong> Driver name, vehicle license plate, and live location shared prior to arrival.</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check2-circle text-success fs-6 mt-0.5"></i>
                                <span><strong>Full Luggage Accommodation:</strong> Clean 300-series Land Cruiser trunks suitable for airport transfers.</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check2-circle text-success fs-6 mt-0.5"></i>
                                <span><strong>Pay on Pickup:</strong> Reserve with 20% advance or full payment via Ziina, or cash on departure.</span>
                            </li>
                        </ul>

                        <div class="p-3 rounded-3 text-center" style="background: rgba(246, 144, 68, 0.1); border: 1px dashed rgba(246, 144, 68, 0.4);">
                            <small class="text-warning fw-bold d-block"><i class="bi bi-lightning-charge-fill me-1"></i> Same-Day Pickup Available</small>
                            <span class="text-white-50 small">Book before 01:30 PM for today's evening safari</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── WHY BOOK FROM THIS LOCATION ───────────────────────────────────────── -->
    <section class="py-5" style="background: #0F172A; border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
        <div class="container py-4">
            <div class="text-center max-w-700 mx-auto mb-5">
                <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold text-uppercase mb-2" style="font-size: 0.75rem;">Seamless Logistics</span>
                <h2 class="h2 fw-bold text-white mb-2">Why Book Your Desert Safari from {{ $locationData['name'] }}?</h2>
                <p class="text-white-50">Skip congested group bus meeting points. Our licensed safari fleet brings VIP desert transfers right to your doorstep.</p>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="loc-card-feature">
                        <div class="loc-icon-box"><i class="bi bi-building"></i></div>
                        <h5 class="fw-bold text-white mb-2">Direct Door-to-Door</h5>
                        <p class="text-white-50 small mb-0">We pick you up directly from your hotel lobby, Airbnb apartment tower, or private villa anywhere in {{ $locationData['district'] }}.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="loc-card-feature">
                        <div class="loc-icon-box"><i class="bi bi-whatsapp"></i></div>
                        <h5 class="fw-bold text-white mb-2">WhatsApp Coordination</h5>
                        <p class="text-white-50 small mb-0">Receive a live WhatsApp dispatch message with your captain's name, phone number, and exact vehicle ETA 30 minutes before pickup.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="loc-card-feature">
                        <div class="loc-icon-box"><i class="bi bi-luggage"></i></div>
                        <h5 class="fw-bold text-white mb-2">Luggage Friendly</h5>
                        <p class="text-white-50 small mb-0">Checking out today? Store your travel bags safely in our secure vehicle trunk during the safari. Drop-off at DXB airport also available.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="loc-card-feature">
                        <div class="loc-icon-box"><i class="bi bi-shield-check"></i></div>
                        <h5 class="fw-bold text-white mb-2">DTCM Certified Fleet</h5>
                        <p class="text-white-50 small mb-0">Full roll-cage protected Toyota Land Cruisers with comprehensive passenger insurance and expert licensed desert drivers.</p>
                    </div>
                </div>
            </div>

            <!-- Landmarks Covered -->
            <div class="p-4 rounded-4" style="background: rgba(30, 41, 59, 0.4); border: 1px solid rgba(255, 255, 255, 0.08);">
                <div class="row align-items-center g-3">
                    <div class="col-12 col-md-4">
                        <h6 class="fw-bold text-white mb-1"><i class="bi bi-pin-map text-warning me-2"></i>Prime Landmarks Covered:</h6>
                        <small class="text-white-50">Any hotel, resort, or tower across {{ $locationData['district'] }}</small>
                    </div>
                    <div class="col-12 col-md-8">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($locationData['landmarks'] as $landmark)
                                <span class="landmark-tag"><i class="bi bi-check-circle-fill text-warning"></i> {{ $landmark }}</span>
                            @endforeach
                            <span class="landmark-tag text-warning">+ All Private Airbnbs & Villas</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── SAFARI PACKAGES GRID ──────────────────────────────────────────────── -->
    <section class="py-5" id="safari-packages">
        <div class="container py-4">
            <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-5">
                <div>
                    <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold text-uppercase mb-2" style="font-size: 0.75rem;">Verified Packages</span>
                    <h2 class="h2 fw-bold text-white mb-1">Desert Safaris Available from {{ $locationData['name'] }}</h2>
                    <p class="text-white-50 mb-0">Choose your preferred experience level. All packages include direct doorstep transfers.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-dark border border-secondary text-white-50 py-2 px-3 rounded-pill small">
                        <i class="bi bi-shield-lock text-success me-1"></i> Best Price Guarantee
                    </span>
                </div>
            </div>

            <div class="row g-4">
                @forelse($safariTours as $tour)
                    @php
                        $minPrice = $tour->tiers->pluck('pivot.price')->filter(function ($p) {
                            return $p !== null && (float)$p > 0;
                        })->min();
                        $minPrice = $minPrice ? (float)$minPrice : 99.0;
                        $firstTier = $tour->tiers->first();
                        $heroImg = $tour->hero_image ? asset('images/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $tour->hero_image)) : asset('images/desert-safari-poster.avif');
                    @endphp
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="loc-tour-card">
                            <div class="loc-tour-img-wrap">
                                <img src="{{ $heroImg }}" alt="{{ $tour->name }} from {{ $locationData['name'] }}" loading="lazy">
                                <div class="position-absolute top-0 start-0 m-3">
                                    <span class="badge bg-dark bg-opacity-75 text-warning border border-warning border-opacity-25 rounded-pill px-2.5 py-1.5 small">
                                        <i class="bi bi-star-fill text-warning me-1"></i> {{ $tour->rating ?: '4.9' }} ({{ $tour->review_count ?: '480+' }})
                                    </span>
                                </div>
                                <div class="position-absolute top-0 end-0 m-3">
                                    <span class="badge bg-primary text-white rounded-pill px-2.5 py-1.5 small fw-bold">
                                        {{ $locationData['name'] }} Pickup
                                    </span>
                                </div>
                                <div class="position-absolute bottom-0 start-0 end-0 p-3" style="background: linear-gradient(to top, rgba(15, 23, 42, 0.95), transparent);">
                                    <span class="text-white-50 small"><i class="bi bi-clock me-1"></i>{{ $tour->duration ?: '6-7 Hours' }} • Evening</span>
                                </div>
                            </div>

                            <div class="loc-tour-body">
                                <h3 class="h5 fw-bold text-white mb-2">{{ $tour->name }}</h3>
                                <p class="text-white-50 small mb-3 flex-grow-1" style="line-height: 1.6;">
                                    {{ Str::limit($tour->short_desc ?: 'Premium Dubai desert safari experience with dune bashing, sunset photography, live shows & BBQ dinner.', 120) }}
                                </p>

                                <ul class="list-unstyled small text-light d-flex flex-column gap-1.5 mb-4 border-top border-bottom border-white border-opacity-10 py-3">
                                    <li class="d-flex align-items-center gap-2">
                                        <i class="bi bi-check-circle-fill text-warning"></i>
                                        <span>Doorstep pickup from {{ $locationData['name'] }}</span>
                                    </li>
                                    <li class="d-flex align-items-center gap-2">
                                        <i class="bi bi-check-circle-fill text-warning"></i>
                                        <span>Red dune bashing (Lahbab Desert)</span>
                                    </li>
                                    <li class="d-flex align-items-center gap-2">
                                        <i class="bi bi-check-circle-fill text-warning"></i>
                                        <span>Live 5-star BBQ dinner & 3 cultural shows</span>
                                    </li>
                                </ul>

                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div>
                                        <small class="text-white-50 d-block" style="font-size: 0.72rem;">FROM</small>
                                        <span class="h4 fw-bold text-warning mb-0" data-aed="{{ $minPrice }}">AED {{ number_format($minPrice, 0) }}</span>
                                        <small class="text-white-50" style="font-size: 0.75rem;">/ person</small>
                                    </div>
                                    <div class="d-flex align-items-center gap-1.5">
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 text-nowrap btn-toggle-compare small" style="font-size: 0.78rem;" data-tour-id="{{ $tour->id }}" onclick="event.preventDefault(); event.stopPropagation(); window.DunesCompare && window.DunesCompare.toggle(this);">
                                            <i class="bi bi-shuffle me-1"></i> <span class="compare-btn-text">Compare</span>
                                        </button>
                                        <a href="{{ route('tours.show', $tour->slug) }}" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5">
                                            Details <i class="bi bi-chevron-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>

                                <button type="button" class="btn btn-desert-animated w-100 rounded-pill py-2.5 fw-bold btn-book-location shadow-sm"
                                        data-tour-id="{{ $tour->id }}"
                                        data-tier-id="{{ $firstTier ? $firstTier->id : '' }}"
                                        data-location-name="{{ $locationData['name'] }}">
                                    <i class="bi bi-calendar-check me-1.5"></i> Book from {{ $locationData['name'] }}
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5 text-white-50">
                        <p>No tours currently listed. Please contact our WhatsApp concierge for custom departures.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- ── ITINERARY & TIMELINE ──────────────────────────────────────────────── -->
    <section class="py-5" style="background: #0F172A; border-top: 1px solid rgba(255, 255, 255, 0.05); border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
        <div class="container py-4">
            <div class="text-center max-w-700 mx-auto mb-5">
                <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold text-uppercase mb-2" style="font-size: 0.75rem;">Experience Schedule</span>
                <h2 class="h2 fw-bold text-white mb-2">Your Safari Day Timeline from {{ $locationData['name'] }}</h2>
                <p class="text-white-50">Here is what a typical evening desert safari looks like from your doorstep pickup to drop-off.</p>
            </div>

            <div class="row g-4 justify-content-center">
                <div class="col-12 col-lg-10">
                    <div class="d-flex flex-column gap-4">
                        <!-- Step 1 -->
                        <div class="p-4 rounded-4 d-flex align-items-start gap-4" style="background: #1E293B; border: 1px solid rgba(255, 255, 255, 0.08);">
                            <div class="loc-timeline-dot">1</div>
                            <div>
                                <span class="badge bg-warning text-dark fw-bold mb-2">{{ $locationData['pickup_window'] }}</span>
                                <h4 class="h5 fw-bold text-white mb-1">Doorstep Hotel / Residence Pickup</h4>
                                <p class="text-white-50 small mb-0">Your safari captain pulls up in an air-conditioned 4x4 Toyota Land Cruiser at your hotel or tower entrance in {{ $locationData['district'] }}. Sit back and enjoy the scenic 45-minute highway drive toward the red dunes.</p>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="p-4 rounded-4 d-flex align-items-start gap-4" style="background: #1E293B; border: 1px solid rgba(255, 255, 255, 0.08);">
                            <div class="loc-timeline-dot">2</div>
                            <div>
                                <span class="badge bg-warning text-dark fw-bold mb-2">03:45 PM – 04:15 PM</span>
                                <h4 class="h5 fw-bold text-white mb-1">Desert Arrival & Quad Buggy Warm-up</h4>
                                <p class="text-white-50 small mb-0">Arrive at our desert staging area in Lahbab. Tire pressure is adjusted for extreme sand drifting. Guests who added Quad Biking or Can-Am Maverick rentals can take their high-powered ride across the dunes.</p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="p-4 rounded-4 d-flex align-items-start gap-4" style="background: #1E293B; border: 1px solid rgba(255, 255, 255, 0.08);">
                            <div class="loc-timeline-dot">3</div>
                            <div>
                                <span class="badge bg-warning text-dark fw-bold mb-2">04:30 PM – 05:15 PM</span>
                                <h4 class="h5 fw-bold text-white mb-1">High Red Dune Bashing Adventure</h4>
                                <p class="text-white-50 small mb-0">Experience 40 to 45 minutes of adrenaline-pumping dune bashing across the highest red sand peaks of Dubai, driven by DTCM licensed desert masters.</p>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="p-4 rounded-4 d-flex align-items-start gap-4" style="background: #1E293B; border: 1px solid rgba(255, 255, 255, 0.08);">
                            <div class="loc-timeline-dot">4</div>
                            <div>
                                <span class="badge bg-warning text-dark fw-bold mb-2">05:15 PM – 05:45 PM</span>
                                <h4 class="h5 fw-bold text-white mb-1">Golden Hour Sunset Stop & Sandboarding</h4>
                                <p class="text-white-50 small mb-0">Stop at the summit of the tallest virgin dune for panoramic sunset photos overlooking the endless desert. Grab a custom sandboard and surf down the silky dunes.</p>
                            </div>
                        </div>

                        <!-- Step 5 -->
                        <div class="p-4 rounded-4 d-flex align-items-start gap-4" style="background: #1E293B; border: 1px solid rgba(255, 255, 255, 0.08);">
                            <div class="loc-timeline-dot">5</div>
                            <div>
                                <span class="badge bg-warning text-dark fw-bold mb-2">06:00 PM – 08:45 PM</span>
                                <h4 class="h5 fw-bold text-white mb-1">Bedouin Camp, 5-Star Live BBQ & Shows</h4>
                                <p class="text-white-50 small mb-0">Arrive at our authentic desert fortress camp. Enjoy complimentary camel riding, Arabian coffee (Gahwa), fresh dates, henna body painting, and a sumptuous 5-star live BBQ buffet accompanied by live Fire, Tanoura, and Belly Dance performances.</p>
                            </div>
                        </div>

                        <!-- Step 6 -->
                        <div class="p-4 rounded-4 d-flex align-items-start gap-4" style="background: #1E293B; border: 1px solid rgba(255, 255, 255, 0.08);">
                            <div class="loc-timeline-dot">6</div>
                            <div>
                                <span class="badge bg-warning text-dark fw-bold mb-2">{{ $locationData['return_time'] }}</span>
                                <h4 class="h5 fw-bold text-white mb-1">Comfortable Drop-off Back at {{ $locationData['name'] }}</h4>
                                <p class="text-white-50 small mb-0">Relax in climate-controlled comfort as your captain drives you directly back to your hotel or residence doorstep in {{ $locationData['name'] }}.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── REVIEWS STRIP ─────────────────────────────────────────────────────── -->
    @if(count($reviews) > 0)
    <section class="py-5" style="background: #0B1120;">
        <div class="container py-4">
            <div class="text-center max-w-700 mx-auto mb-5">
                <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold text-uppercase mb-2" style="font-size: 0.75rem;">Guest Feedback</span>
                <h2 class="h2 fw-bold text-white mb-2">What Travelers Say About Our Doorstep Pickup</h2>
                <p class="text-white-50">Real experiences from travelers picked up from hotels and residences across Dubai.</p>
            </div>

            <div class="row g-4">
                @foreach($reviews->take(3) as $rev)
                    <div class="col-12 col-md-4">
                        <div class="p-4 rounded-4 h-100 d-flex flex-column" style="background: #1E293B; border: 1px solid rgba(255, 255, 255, 0.08);">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="text-warning">
                                    @for($i = 0; $i < ($rev->rating ?: 5); $i++)
                                        <i class="bi bi-star-fill"></i>
                                    @endfor
                                </div>
                                <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 rounded-pill small">
                                    <i class="bi bi-patch-check-fill me-1"></i> Verified Guest
                                </span>
                            </div>
                            <p class="text-white-50 small flex-grow-1 mb-3" style="line-height: 1.7;">
                                "{{ Str::limit($rev->comment ?: 'Incredible experience! The driver arrived exactly on time at our hotel lobby. Dune bashing was thrilling and the live BBQ show was phenomenal.', 180) }}"
                            </p>
                            <div class="d-flex align-items-center gap-3 pt-3 border-top border-white border-opacity-10">
                                <div class="rounded-circle bg-warning text-dark fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                    {{ strtoupper(substr($rev->author_name ?: 'G', 0, 1)) }}
                                </div>
                                <div>
                                    <h6 class="text-white fw-bold small mb-0">{{ $rev->author_name ?: 'Verified Traveler' }}</h6>
                                    <small class="text-white-50" style="font-size: 0.72rem;">{{ $rev->author_location ?: 'Dubai Tourist' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- ── DISTRICT FAQ ACCORDION ────────────────────────────────────────────── -->
    <section class="py-5" style="background: #0F172A; border-top: 1px solid rgba(255, 255, 255, 0.05);">
        <div class="container py-4">
            <div class="text-center max-w-700 mx-auto mb-5">
                <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold text-uppercase mb-2" style="font-size: 0.75rem;">Got Questions?</span>
                <h2 class="h2 fw-bold text-white mb-2">Frequently Asked Questions: {{ $locationData['name'] }} Pickup</h2>
                <p class="text-white-50">Everything you need to know about timings, vehicles, luggage, and booking.</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    <div class="accordion loc-accordion" id="locationFaqAccordion">
                        @foreach($locationData['faqs'] as $index => $faq)
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="faqHeading{{ $index }}">
                                    <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse{{ $index }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="faqCollapse{{ $index }}">
                                        {{ $faq['q'] }}
                                    </button>
                                </h3>
                                <div id="faqCollapse{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="faqHeading{{ $index }}" data-bs-parent="#locationFaqAccordion">
                                    <div class="accordion-body">
                                        {{ $faq['a'] }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── CROSS-HUB PROGRAMMATIC SEO INTERLINKING ──────────────────────────── -->
    <section class="py-5" style="background: #0B1120; border-top: 1px solid rgba(255, 255, 255, 0.05);">
        <div class="container py-4">
            <div class="text-center max-w-700 mx-auto mb-5">
                <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold text-uppercase mb-2" style="font-size: 0.75rem;">Dubai & UAE Network</span>
                <h2 class="h2 fw-bold text-white mb-2">Other Dubai Safari Pickup Hubs</h2>
                <p class="text-white-50">Staying in a different part of the city? Explore our dedicated pickup schedules across Dubai & Sharjah.</p>
            </div>

            <div class="row g-3">
                @foreach($allLocations as $locKey => $loc)
                    @if($locKey !== $locationData['key'])
                        <div class="col-12 col-sm-6 col-lg-4">
                            <a href="{{ url('/' . $loc['slug']) }}" class="loc-hub-card">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="fw-bold text-white mb-0">{{ $loc['name'] }}</h6>
                                    <i class="bi bi-arrow-right text-warning"></i>
                                </div>
                                <p class="text-white-50 small mb-2" style="font-size: 0.78rem; line-height: 1.5;">{{ Str::limit($loc['district'], 55) }}</p>
                                <div class="d-flex align-items-center gap-2 small text-warning" style="font-size: 0.75rem;">
                                    <i class="bi bi-clock"></i> Pickup: {{ $loc['pickup_window'] }}
                                </div>
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <!-- ── BOTTOM HIGH-CONVERSION CTA BANNER ─────────────────────────────────── -->
    <section class="py-5" style="background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%); border-top: 1px solid rgba(246, 144, 68, 0.3);">
        <div class="container py-4 text-center">
            <div class="max-w-700 mx-auto">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-warning bg-opacity-20 text-warning border border-warning border-opacity-30 small fw-bold mb-3">
                    <i class="bi bi-shield-check"></i> Free Cancellation Up to 24h Before Tour
                </div>
                <h2 class="display-6 fw-bold text-white mb-3">
                    Ready for the High Red Dunes from {{ $locationData['name'] }}?
                </h2>
                <p class="text-light opacity-90 lead mb-4" style="font-size: 1.1rem;">
                    Reserve your seats in 60 seconds with 20% advance or pay cash on pickup. Instant WhatsApp confirmation with full driver credentials.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <button type="button" class="btn btn-desert-animated btn-lg rounded-pill px-5 py-3.5 shadow-lg fw-bold btn-book-location"
                            data-tour-id="{{ $safariTours->first() ? $safariTours->first()->id : '' }}"
                            data-location-name="{{ $locationData['name'] }}">
                        <i class="bi bi-calendar2-check me-2"></i> Book Desert Safari Now
                    </button>
                    <a href="https://wa.me/971502456056?text={{ urlencode('Hi Dunes Discovery Tourism, I want to book a Desert Safari from ' . $locationData['name'] . '. Please share details.') }}" target="_blank" rel="noopener" class="btn btn-outline-light btn-lg rounded-pill px-4 py-3.5">
                        <i class="bi bi-whatsapp text-success me-2"></i> Ask via WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-book-location').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var tourId = this.dataset.tourId;
            var tierId = this.dataset.tierId || null;
            var locName = this.dataset.locationName;

            if (window.App && typeof window.App.openBooking === 'function') {
                window.App.openBooking(tourId, tierId);
                var locInput = document.getElementById('bookingLocation');
                if (locInput && locName) {
                    locInput.value = locName;
                    locInput.dispatchEvent(new Event('input', { bubbles: true }));
                    locInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            } else {
                var modalEl = document.getElementById('bookingModal');
                if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    var m = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    m.show();
                    var tourSel = document.getElementById('bookingTour');
                    if (tourSel && tourId) {
                        tourSel.value = tourId;
                        tourSel.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    var locInput = document.getElementById('bookingLocation');
                    if (locInput && locName) {
                        locInput.value = locName;
                    }
                }
            }
        });
    });
});
</script>
@endpush
