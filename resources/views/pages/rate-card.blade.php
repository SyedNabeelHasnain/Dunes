@extends('layouts.app')

@section('title', 'Official Tours & Pricing Rate Card | Dunes Discovery Tourism')
@section('meta_description', 'Official verified rates and package pricing for Dubai Desert Safaris, Dune Buggy Rentals, City Tours, and Luxury Marina Dinner Cruises by Dunes Discovery Tourism.')

@push('styles')
<style>
/* Page Wrapper */
.rate-card-wrapper {
    background-color: #F8FAFC;
    min-height: 100vh;
}

/* Hero Dark Card */
.rate-hero-banner {
    background: linear-gradient(135deg, #0F172A 0%, #1E293B 60%, #334155 100%) !important;
    border-radius: 24px;
    position: relative;
    overflow: hidden;
    color: #FFFFFF !important;
    border: 1px solid #334155;
    box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.4);
}
.rate-hero-banner::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    width: 45%;
    background: radial-gradient(circle at 85% 50%, rgba(246, 144, 68, 0.25) 0%, transparent 70%);
    pointer-events: none;
}
.rate-hero-banner h1 {
    color: #FFFFFF !important;
}
.rate-hero-banner p {
    color: #CBD5E1 !important;
}

/* Free Pickup Marquee Banner */
.pickup-marquee-banner {
    background: linear-gradient(90deg, #FFF7ED 0%, #FFEDD5 100%) !important;
    border: 2px solid #FDBA74 !important;
    border-radius: 16px;
    padding: 14px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    box-shadow: 0 4px 15px rgba(246, 144, 68, 0.1);
}

/* Category Headers */
.category-section-header {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-left: 6px solid #F69044;
    border-radius: 12px;
    padding: 14px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 6px rgba(0,0,0,0.02);
}
.category-section-header h2 {
    font-size: 19px;
    font-weight: 800;
    color: #0F172A;
    letter-spacing: -0.01em;
    margin: 0;
    text-transform: uppercase;
}

/* 1 Tour Per Row Luxury Card */
.tour-row-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
    margin-bottom: 24px;
}
.tour-row-card:hover {
    border-color: #CBD5E1;
    transform: translateY(-2px);
    box-shadow: 0 14px 30px rgba(0, 0, 0, 0.08);
}

.tour-row-img-wrap {
    position: relative;
    height: 100%;
    min-height: 220px;
    background: #0F172A;
    overflow: hidden;
}
.tour-row-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}
.tour-row-card:hover .tour-row-img-wrap img {
    transform: scale(1.04);
}

.tour-row-title {
    font-size: 18px;
    font-weight: 800;
    color: #0F172A;
    line-height: 1.3;
    letter-spacing: -0.01em;
}

.tour-row-desc {
    font-size: 13px;
    color: #475569;
    line-height: 1.55;
    margin-bottom: 14px;
}

.inclusions-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.inc-pill {
    font-size: 11px;
    background: #F1F5F9;
    color: #334155;
    padding: 4px 10px;
    border-radius: 6px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    border: 1px solid #E2E8F0;
}
.inc-pill i {
    color: #F69044;
}

.tier-pricing-box {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 14px;
    padding: 16px;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.tier-item-row {
    padding: 8px 0;
    border-bottom: 1px dashed #CBD5E1;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.tier-item-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.tier-item-row:first-child {
    padding-top: 0;
}
.tier-item-name {
    font-size: 13px;
    font-weight: 700;
    color: #1E293B;
}
.tier-item-sub {
    font-size: 11px;
    color: #64748B;
}
.tier-item-price {
    text-align: right;
}
.tier-item-price .cur-price {
    font-size: 16px;
    font-weight: 800;
    color: #D95300;
}
.tier-item-price .old-price {
    font-size: 11.5px;
    text-decoration: line-through;
    color: #94A3B8;
    margin-right: 4px;
}

.print-floating-bar {
    position: sticky;
    top: 76px;
    z-index: 99;
    backdrop-filter: blur(12px);
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}

/* Print CSS */
@media print {
    #header, .footer, .btn-circle-whatsapp, .whatsapp-floating-btn, .print-floating-bar, .modal, #tabBar, .toast-container, .visually-hidden-focusable, .btn-card-action {
        display: none !important;
    }
    body, main, #main, .rate-card-wrapper {
        background: #FFFFFF !important;
        padding: 0 !important;
        margin: 0 !important;
        min-height: auto !important;
    }
    .container {
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
    .tour-row-card {
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
<div class="rate-card-wrapper py-4 py-lg-5">
    <div class="container">

        <!-- Top Floating Toolbar -->
        <div class="print-floating-bar p-3 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('tours.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Back to Tours
                </a>
                <span class="badge bg-success-subtle text-success fw-bold px-3 py-2 rounded-pill">
                    ● Real-Time Database Verified
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-desert-animated btn-sm rounded-pill px-4 py-2 fw-bold" onclick="window.print()">
                    <i class="bi bi-printer-fill me-1"></i> Print / Save as PDF
                </button>
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}?text={{ urlencode('Hello Dunes Discovery Tourism, I am viewing your official rate card and would like to make an inquiry.') }}" target="_blank" rel="noopener" class="btn btn-whatsapp-animated btn-sm rounded-pill px-3 py-2 fw-bold">
                    <i class="bi bi-whatsapp me-1"></i> WhatsApp Booking
                </a>
            </div>
        </div>

        <!-- Official Hero Banner (Explicit High Contrast) -->
        <div class="rate-hero-banner p-4 p-lg-5 mb-4" style="background: linear-gradient(135deg, #0F172A 0%, #1E293B 60%, #334155 100%) !important; color: #FFFFFF !important;">
            <div class="row align-items-center g-4 position-relative z-2">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="{{ asset('images/logo-white.png') }}" alt="Dunes Discovery Tourism" style="height: 44px; width: auto;">
                        <span class="badge bg-warning text-dark fw-bold rounded-pill px-3 py-1 text-uppercase" style="font-size: 11px;">
                            ⭐ Official 2026 Price Guide
                        </span>
                    </div>
                    <h1 class="display-6 fw-bold mb-2" style="color: #FFFFFF !important; letter-spacing: -0.02em;">
                        Dubai Desert Safaris & Tours <span class="text-primary" style="color: #F69044 !important;">Rate Card</span>
                    </h1>
                    <p class="mb-3 fs-6" style="color: #CBD5E1 !important; max-width: 620px; line-height: 1.55;">
                        Official tour portfolio & pricing catalog by Dunes Discovery Tourism LLC. Direct operator rates with best price guarantee across all UAE excursions.
                    </p>
                    <div class="d-flex flex-wrap gap-2 text-white small">
                        <span class="d-flex align-items-center gap-1.5 px-3 py-1.5 rounded-3 border" style="background: rgba(255,255,255,0.1) !important; color: #FFFFFF !important; border-color: rgba(255,255,255,0.18) !important;">
                            <i class="bi bi-telephone-fill" style="color: #F69044;"></i> {{ $phone }}
                        </span>
                        <span class="d-flex align-items-center gap-1.5 px-3 py-1.5 rounded-3 border" style="background: rgba(255,255,255,0.1) !important; color: #FFFFFF !important; border-color: rgba(255,255,255,0.18) !important;">
                            <i class="bi bi-whatsapp" style="color: #25D366;"></i> WhatsApp: {{ $waPhone }}
                        </span>
                        <span class="d-flex align-items-center gap-1.5 px-3 py-1.5 rounded-3 border" style="background: rgba(255,255,255,0.1) !important; color: #FFFFFF !important; border-color: rgba(255,255,255,0.18) !important;">
                            <i class="bi bi-envelope-fill" style="color: #F69044;"></i> {{ $email }}
                        </span>
                        <span class="d-flex align-items-center gap-1.5 px-3 py-1.5 rounded-3 border" style="background: rgba(255,255,255,0.1) !important; color: #FFFFFF !important; border-color: rgba(255,255,255,0.18) !important;">
                            <i class="bi bi-globe" style="color: #F69044;"></i> dunesdiscoverytourism.com
                        </span>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end d-none d-lg-block">
                    <div class="p-3.5 rounded-4 border d-inline-block text-start" style="background: rgba(255,255,255,0.08) !important; border-color: rgba(255,255,255,0.15) !important; min-width: 220px;">
                        <div class="small mb-1" style="color: #CBD5E1 !important;">Customer Ratings & Trust</div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fs-3 fw-bold text-white">4.9 / 5.0</span>
                            <div class="text-warning">
                                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                            </div>
                        </div>
                        <div class="small" style="color: #CBD5E1 !important;">TripAdvisor & Google Verified</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Marquee Key Value Banner: Free Doorstep Pickup -->
        <div class="pickup-marquee-banner mb-5">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white shadow-sm" style="width: 44px; height: 44px; font-size: 22px; flex-shrink: 0;">
                    <i class="bi bi-car-front-fill"></i>
                </div>
                <div>
                    <div class="fw-800 text-dark" style="font-size: 15px; letter-spacing: -0.01em;">
                        🚐 COMPLIMENTARY 4X4 DOORSTEP HOTEL PICKUP & DROP-OFF INCLUDED
                    </div>
                    <div class="text-secondary small" style="font-size: 12.5px;">
                        Enjoy seamless door-to-door transportation in clean, air-conditioned Toyota Land Cruisers from any hotel, residence, or cruise terminal across Dubai & Sharjah.
                    </div>
                </div>
            </div>
            <div class="d-none d-md-block text-end text-nowrap">
                <span class="badge bg-dark text-white px-3 py-2 rounded-pill fw-bold" style="font-size: 11px;">
                    ✓ Zero Hidden Fees
                </span>
            </div>
        </div>

        <!-- Tours Grouped by Category (Dynamic 1 Tour Per Row) -->
        @foreach($categories as $cat)
            @if($cat->tours && $cat->tours->count() > 0)
            <div class="mb-5">
                <div class="category-section-header">
                    <div class="d-flex align-items-center gap-2">
                        <h2>{{ $cat->name }}</h2>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1.5 fw-bold">
                        {{ $cat->tours->count() }} Available {{ Str::plural('Experience', $cat->tours->count()) }}
                    </span>
                </div>

                @foreach($cat->tours as $t)
                <div class="tour-row-card">
                    <div class="row g-0">
                        <!-- Left: Image & Badge -->
                        <div class="col-12 col-md-3">
                            <div class="tour-row-img-wrap">
                                @if(!empty($t->hero_image))
                                    @php
                                        $imgFile = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $t->hero_image);
                                    @endphp
                                    <img src="{{ asset('images/' . $imgFile) }}" alt="{{ $t->name }}" loading="lazy">
                                @else
                                    <img src="{{ asset('images/desert-safari-poster.avif') }}" alt="{{ $t->name }}" loading="lazy">
                                @endif
                                <div class="position-absolute top-0 start-0 m-2.5 d-flex flex-column gap-1.5">
                                    @if($t->is_bestseller)
                                    <span class="badge bg-warning text-dark fw-bold rounded-pill px-2.5 py-1 text-uppercase" style="font-size: 9.5px;">
                                        ⭐ Bestseller
                                    </span>
                                    @endif
                                    <span class="badge bg-dark bg-opacity-75 text-white fw-semibold rounded-pill px-2.5 py-1" style="font-size: 9.5px;">
                                        ⏱ {{ $t->duration }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Middle: Tour Details & Inclusions -->
                        <div class="col-12 col-md-5 p-3.5 p-lg-4 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-1.5">
                                    <h3 class="tour-row-title mb-0">{{ $t->name }}</h3>
                                </div>
                                
                                <div class="d-flex flex-wrap gap-3 small text-muted mb-2.5">
                                    @if($t->pickup_time)
                                    <span><i class="bi bi-clock-history me-1 text-primary"></i>{{ $t->pickup_time }} - {{ $t->dropoff_time }}</span>
                                    @endif
                                    <span><i class="bi bi-star-fill text-warning me-1"></i>{{ $t->rating ?? '4.9' }} ({{ $t->review_count ?? '500+' }} Reviews)</span>
                                </div>

                                <p class="tour-row-desc">
                                    {{ Str::limit($t->short_desc, 180) }}
                                </p>
                            </div>

                            <div>
                                <div class="inclusions-pills">
                                    <span class="inc-pill"><i class="bi bi-check-circle-fill"></i> Free 4x4 Pickup</span>
                                    <span class="inc-pill"><i class="bi bi-check-circle-fill"></i> Professional Guide</span>
                                    <span class="inc-pill"><i class="bi bi-check-circle-fill"></i> Refreshments</span>
                                    @if(str_contains(strtolower($t->name), 'evening') || str_contains(strtolower($t->name), 'cruise'))
                                    <span class="inc-pill"><i class="bi bi-check-circle-fill"></i> Buffet Dinner</span>
                                    <span class="inc-pill"><i class="bi bi-check-circle-fill"></i> Live Shows</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right: Pricing & Package Tiers Matrix -->
                        <div class="col-12 col-md-4 p-3.5 p-lg-4 border-start border-light bg-light bg-opacity-50">
                            <div class="tier-pricing-box">
                                <div>
                                    <div class="text-uppercase fw-bold text-secondary mb-2" style="font-size: 11px; letter-spacing: 0.5px;">
                                        Available Package Tiers
                                    </div>

                                    @if($t->tiers && $t->tiers->count() > 0)
                                        @foreach($t->tiers as $tier)
                                        <div class="tier-item-row">
                                            <div>
                                                <div class="tier-item-name">{{ $tier->name }}</div>
                                                @if($tier->description)
                                                <div class="tier-item-sub">{{ Str::limit($tier->description, 35) }}</div>
                                                @endif
                                            </div>
                                            <div class="tier-item-price">
                                                @if(!empty($tier->pivot->old_price))
                                                <span class="old-price">AED {{ number_format($tier->pivot->old_price) }}</span>
                                                @endif
                                                <span class="cur-price">AED {{ number_format($tier->pivot->price) }}</span>
                                                <div class="text-muted" style="font-size: 9.5px;">/ {{ $tier->pivot->price_type ?? 'person' }}</div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="tier-item-row">
                                            <div>
                                                <div class="tier-item-name">Standard Experience</div>
                                            </div>
                                            <div class="tier-item-price">
                                                <span class="cur-price">AED {{ number_format($t->price) }}</span>
                                                <div class="text-muted" style="font-size: 9.5px;">/ person</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="pt-3 mt-2 border-top d-flex gap-2 btn-card-action">
                                    <a href="{{ route('tours.show', $t->slug) }}" class="btn btn-outline-dark btn-sm rounded-pill flex-grow-1 fw-bold" style="font-size: 11.5px;">
                                        Details <i class="bi bi-arrow-right"></i>
                                    </a>
                                    <button type="button" class="btn btn-desert-animated btn-sm rounded-pill px-3 fw-bold" data-action="open-booking" data-tour-id="{{ $t->id }}" style="font-size: 11.5px;">
                                        Book Tour
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        @endforeach

        <!-- Add-Ons & Extras Section -->
        @if($globalAddons && $globalAddons->count() > 0)
        <div class="mb-5">
            <div class="category-section-header">
                <h2>Safari Add-Ons & Custom Upgrades</h2>
            </div>

            <div class="card p-4 border-0 rounded-4 shadow-sm bg-white">
                <div class="row g-3">
                    @foreach($globalAddons as $addon)
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="p-3 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 13px;">{{ $addon->name }}</div>
                                @if($addon->description)
                                <div class="text-muted" style="font-size: 11px;">{{ Str::limit($addon->description, 45) }}</div>
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

        <!-- Why Choose Us & Guarantees -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-3">
                <div class="card p-3.5 text-center border-0 shadow-sm h-100 rounded-4 bg-white">
                    <i class="bi bi-shield-check text-primary fs-2 mb-2"></i>
                    <h3 class="h6 fw-bold text-dark mb-1">Best Price Guarantee</h3>
                    <p class="text-muted mb-0 small">Direct operator pricing with zero middleman commissions.</p>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card p-3.5 text-center border-0 shadow-sm h-100 rounded-4 bg-white">
                    <i class="bi bi-arrow-counterclockwise text-primary fs-2 mb-2"></i>
                    <h3 class="h6 fw-bold text-dark mb-1">Free Cancellation</h3>
                    <p class="text-muted mb-0 small">100% full refund up to 24 hours prior to tour departure.</p>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card p-3.5 text-center border-0 shadow-sm h-100 rounded-4 bg-white">
                    <i class="bi bi-car-front-fill text-primary fs-2 mb-2"></i>
                    <h3 class="h6 fw-bold text-dark mb-1">Doorstep 4x4 Pickup</h3>
                    <p class="text-muted mb-0 small">Comfortable hotel pickup across all Dubai & Sharjah locations.</p>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card p-3.5 text-center border-0 shadow-sm h-100 rounded-4 bg-white">
                    <i class="bi bi-whatsapp text-success fs-2 mb-2"></i>
                    <h3 class="h6 fw-bold text-dark mb-1">Instant Support</h3>
                    <p class="text-muted mb-0 small">Dedicated 24/7 safari concierge on WhatsApp & Phone.</p>
                </div>
            </div>
        </div>

        <!-- Bottom VIP / Custom Booking Box -->
        <div class="p-4 p-lg-5 rounded-4 bg-dark text-white text-center shadow position-relative overflow-hidden">
            <div class="position-relative z-2">
                <h2 class="display-6 fw-bold text-white mb-2">Corporate Events & Custom VIP Camps</h2>
                <p class="text-white-50 lead fs-6 mb-4 mx-auto" style="max-width: 650px;">
                    Planning a group excursion, private corporate desert party, or VIP luxury setup? Connect directly with our tour specialists for custom itineraries and group rates.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}?text={{ urlencode('Hello Dunes Discovery Tourism, I would like to request a custom group / corporate tour quote.') }}" target="_blank" rel="noopener" class="btn btn-whatsapp-animated rounded-pill px-4 py-2.5 fw-bold">
                        <i class="bi bi-whatsapp me-2 fs-5"></i> Chat on WhatsApp
                    </a>
                    <button type="button" class="btn btn-desert-animated rounded-pill px-4 py-2.5 fw-bold" onclick="window.print()">
                        <i class="bi bi-printer-fill me-2"></i> Save / Print Rate Card
                    </button>
                </div>
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