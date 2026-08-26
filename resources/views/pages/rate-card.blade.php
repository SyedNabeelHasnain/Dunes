@extends('layouts.app')

@section('title', 'Official Tours & Pricing Rate Card | Dunes Discovery Tourism')

@push('styles')
<style>
@media print {
    /* Hide all non-printable elements */
    #header, .footer, .btn-circle-whatsapp, .whatsapp-floating-btn, .print-controls-bar, .modal, #tabBar, .toast-container, .visually-hidden-focusable {
        display: none !important;
    }
    body, main, #main {
        background: #ffffff !important;
        padding: 0 !important;
        margin: 0 !important;
        min-height: auto !important;
    }
    .rate-card-container {
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .print-page-break {
        page-break-before: always;
        break-before: page;
    }
    .print-card-break {
        break-inside: avoid;
        page-break-inside: avoid;
    }
    @page {
        size: A4 portrait;
        margin: 10mm 12mm 10mm 12mm;
    }
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}

.rate-card-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
    border-radius: 20px;
    position: relative;
    overflow: hidden;
    color: #ffffff;
    border: 1px solid #334155;
}
.rate-card-hero::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    width: 45%;
    background: radial-gradient(circle at center, rgba(246, 144, 68, 0.25) 0%, transparent 70%);
    pointer-events: none;
}
.tour-rate-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
}
.tour-rate-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08);
    border-color: #cbd5e1;
}
.tier-table-compact {
    width: 100%;
    border-collapse: collapse;
    background: #f8fafc;
    border-radius: 8px;
    overflow: hidden;
}
.tier-table-compact th {
    background: #0f172a;
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    padding: 6px 10px;
    text-transform: uppercase;
}
.tier-table-compact td {
    padding: 8px 10px;
    font-size: 12px;
    border-bottom: 1px solid #e2e8f0;
}
.tier-table-compact tr:last-child td {
    border-bottom: none;
}
.print-controls-bar {
    position: sticky;
    top: 75px;
    z-index: 100;
    backdrop-filter: blur(12px);
    background: rgba(255, 255, 255, 0.92);
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}
</style>
@endpush

@section('content')
<div class="container py-4 py-lg-5 rate-card-container">

    <!-- Top Action Bar for Screen Viewers -->
    <div class="print-controls-bar p-3 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('tours.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Back to Tours
            </a>
            <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-2 rounded-pill">
                ⭐ Live Updated Rate Card
            </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-desert-animated btn-sm rounded-pill px-4 py-2 fw-bold" onclick="window.print()">
                <i class="bi bi-printer-fill me-1"></i> Print / Save as PDF
            </button>
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}?text={{ urlencode('Hello Dunes Discovery Tourism, I am looking at your tour rate card and would like to inquire about booking.') }}" target="_blank" rel="noopener" class="btn btn-whatsapp-animated btn-sm rounded-pill px-3 py-2 fw-bold">
                <i class="bi bi-whatsapp me-1"></i> WhatsApp Booking
            </a>
        </div>
    </div>

    <!-- Official Header & Banner -->
    <div class="rate-card-hero p-4 p-lg-5 mb-4 shadow-sm">
        <div class="row align-items-center g-4 position-relative z-2">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ asset('images/logo-white.png') }}" alt="Dunes Discovery Tourism" style="height: 42px; width: auto;">
                    <span class="badge bg-warning text-dark fw-800 rounded-pill px-3 py-1 text-uppercase" style="font-size: 11px;">
                        Official 2026 Rates
                    </span>
                </div>
                <h1 class="display-6 fw-900 text-white mb-2">Dubai Desert Safaris & Tours <span class="text-primary">Rate Card</span></h1>
                <p class="text-white-50 mb-3" style="max-width: 620px;">
                    Direct operator prices for luxury 4x4 desert safaris, quad biking, dune buggies, marina dinner cruises & city sightseeing tours across Dubai and Abu Dhabi.
                </p>
                <div class="d-flex flex-wrap gap-3 text-white small">
                    <span class="d-flex align-items-center gap-1 bg-white bg-opacity-10 px-3 py-1.5 rounded-3 border border-white border-opacity-15">
                        <i class="bi bi-telephone-fill text-primary"></i> {{ $phone }}
                    </span>
                    <span class="d-flex align-items-center gap-1 bg-white bg-opacity-10 px-3 py-1.5 rounded-3 border border-white border-opacity-15">
                        <i class="bi bi-whatsapp text-success"></i> WhatsApp: {{ $waPhone }}
                    </span>
                    <span class="d-flex align-items-center gap-1 bg-white bg-opacity-10 px-3 py-1.5 rounded-3 border border-white border-opacity-15">
                        <i class="bi bi-globe text-primary"></i> dunesdiscoverytourism.com
                    </span>
                    <span class="d-flex align-items-center gap-1 bg-white bg-opacity-10 px-3 py-1.5 rounded-3 border border-white border-opacity-15">
                        <i class="bi bi-shield-check text-success"></i> DTCM Licensed & Insured
                    </span>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end d-none d-lg-block">
                <div class="p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-15 d-inline-block text-start" style="min-width: 200px;">
                    <div class="text-white-50 small mb-1">Customer Reviews</div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="fs-4 fw-bold text-white">4.9 / 5.0</span>
                        <div class="text-warning small">
                            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        </div>
                    </div>
                    <div class="text-white-50 small">TripAdvisor & Google Verified</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tours Catalog by Category -->
    @foreach($categories as $cat)
        @if($cat->tours && $cat->tours->count() > 0)
        <div class="mb-5">
            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                <div class="bg-primary rounded-1" style="width: 4px; height: 22px;"></div>
                <h2 class="h4 fw-bold mb-0 text-dark">{{ $cat->name }}</h2>
                <span class="badge bg-secondary-subtle text-secondary ms-auto rounded-pill px-3">{{ $cat->tours->count() }} Experiences</span>
            </div>

            <div class="row g-4">
                @foreach($cat->tours as $t)
                <div class="col-12 col-md-6 print-card-break">
                    <div class="tour-rate-card p-4 shadow-sm">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h3 class="h5 fw-bold text-dark mb-0">{{ $t->name }}</h3>
                                @if($t->is_bestseller)
                                <span class="badge bg-warning text-dark fw-bold rounded-pill px-2.5 py-1 text-uppercase" style="font-size: 10px;">
                                    ⭐ Bestseller
                                </span>
                                @endif
                            </div>

                            <div class="d-flex flex-wrap gap-3 small text-muted mb-3">
                                <span><i class="bi bi-clock me-1 text-primary"></i>{{ $t->duration }}</span>
                                @if($t->pickup_time)
                                <span><i class="bi bi-geo-alt me-1 text-primary"></i>Pickup: {{ $t->pickup_time }}</span>
                                @endif
                                <span><i class="bi bi-star-fill text-warning me-1"></i>{{ $t->rating ?? '4.9' }}</span>
                            </div>

                            <p class="small text-secondary mb-3" style="line-height: 1.5;">
                                {{ Str::limit($t->short_desc, 170) }}
                            </p>
                        </div>

                        <div>
                            <!-- Packages / Tiers Table -->
                            @if($t->tiers && $t->tiers->count() > 0)
                            <div class="mb-3">
                                <table class="tier-table-compact">
                                    <thead>
                                        <tr>
                                            <th>Package Option</th>
                                            <th class="text-end">Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($t->tiers as $tier)
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $tier->name }}</div>
                                                @if($tier->description)
                                                <div class="text-muted" style="font-size: 10.5px;">{{ $tier->description }}</div>
                                                @endif
                                            </td>
                                            <td class="text-end text-nowrap">
                                                @if(!empty($tier->pivot->old_price))
                                                <del class="text-muted small me-1">AED {{ number_format($tier->pivot->old_price) }}</del>
                                                @endif
                                                <span class="fw-800 text-primary fs-6">AED {{ number_format($tier->pivot->price) }}</span>
                                                <span class="text-muted" style="font-size: 10px;">/ {{ $tier->pivot->price_type ?? 'person' }}</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif

                            <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                                <a href="{{ route('tours.show', $t->slug) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    View Full Details <i class="bi bi-arrow-right"></i>
                                </a>
                                <button type="button" class="btn btn-desert-animated btn-sm rounded-pill px-3" data-action="open-booking" data-tour-id="{{ $t->id }}">
                                    Book Now
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

    <!-- Popular Add-Ons & Extras -->
    @if($globalAddons && $globalAddons->count() > 0)
    <div class="mb-5 print-card-break">
        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
            <div class="bg-primary rounded-1" style="width: 4px; height: 22px;"></div>
            <h2 class="h4 fw-bold mb-0 text-dark">Available Safari Add-Ons & Upgrades</h2>
        </div>

        <div class="card card-modern p-4 border-0 shadow-sm">
            <div class="row g-3">
                @foreach($globalAddons as $addon)
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="p-3 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold text-dark small">{{ $addon->name }}</div>
                            @if($addon->description)
                            <div class="text-muted" style="font-size: 10.5px;">{{ Str::limit($addon->description, 50) }}</div>
                            @endif
                        </div>
                        <div class="fw-800 text-primary fs-6 text-nowrap ms-2">
                            AED {{ number_format($addon->price) }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Booking Highlights & Guarantees -->
    <div class="row g-4 mb-4 print-card-break">
        <div class="col-12 col-md-3">
            <div class="card p-3 text-center border-0 shadow-sm h-100 rounded-4 bg-light">
                <i class="bi bi-shield-check text-primary fs-2 mb-2"></i>
                <h3 class="h6 fw-bold text-dark mb-1">Best Price Guarantee</h3>
                <p class="text-muted mb-0 small">Direct operator pricing with zero middleman markups.</p>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card p-3 text-center border-0 shadow-sm h-100 rounded-4 bg-light">
                <i class="bi bi-arrow-counterclockwise text-primary fs-2 mb-2"></i>
                <h3 class="h6 fw-bold text-dark mb-1">Free Cancellation</h3>
                <p class="text-muted mb-0 small">100% full refund up to 24 hours prior to tour departure.</p>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card p-3 text-center border-0 shadow-sm h-100 rounded-4 bg-light">
                <i class="bi bi-car-front-fill text-primary fs-2 mb-2"></i>
                <h3 class="h6 fw-bold text-dark mb-1">Doorstep Hotel Pickup</h3>
                <p class="text-muted mb-0 small">Comfortable 4x4 AC pickup across Dubai & Sharjah.</p>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card p-3 text-center border-0 shadow-sm h-100 rounded-4 bg-light">
                <i class="bi bi-whatsapp text-success fs-2 mb-2"></i>
                <h3 class="h6 fw-bold text-dark mb-1">Instant Support</h3>
                <p class="text-muted mb-0 small">24/7 dedicated safari captains on WhatsApp.</p>
            </div>
        </div>
    </div>

    <!-- Booking Call To Action -->
    <div class="p-4 p-lg-5 rounded-4 bg-dark text-white text-center shadow print-card-break position-relative overflow-hidden">
        <div class="position-relative z-2">
            <h2 class="display-6 fw-bold text-white mb-2">Custom Group & Corporate Bookings</h2>
            <p class="text-white-50 lead fs-6 mb-4 mx-auto" style="max-width: 600px;">
                Traveling with a group, family, or corporate team? Contact our team for customized itineraries, private VIP camps, and exclusive discounts.
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}" target="_blank" rel="noopener" class="btn btn-whatsapp-animated rounded-pill px-4 py-2.5 fw-bold">
                    <i class="bi bi-whatsapp me-2 fs-5"></i> Chat with Tour Specialist
                </a>
                <button type="button" class="btn btn-desert-animated rounded-pill px-4 py-2.5 fw-bold" onclick="window.print()">
                    <i class="bi bi-printer-fill me-2"></i> Download / Print Rate Card
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