@extends('layouts.app')

@section('title', 'Official Tours & Pricing Rate Card | Dunes Discovery Tourism')
@section('meta_description', 'Official verified rates and package pricing for Dubai Desert Safaris, Dune Buggy Rentals, City Tours, and Luxury Marina Dinner Cruises by Dunes Discovery Tourism.')

@push('styles')
<style>
/* Page Scope Styling */
.rc-page {
    background-color: #F8FAFC !important;
    min-height: 100vh;
}

.rc-hero {
    background: linear-gradient(135deg, #0F172A 0%, #1E293B 60%, #334155 100%) !important;
    border-radius: 24px !important;
    position: relative;
    overflow: hidden;
    color: #FFFFFF !important;
    border: 1px solid #334155 !important;
    box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.4) !important;
}
.rc-hero::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    width: 45%;
    background: radial-gradient(circle at 85% 50%, rgba(246, 144, 68, 0.25) 0%, transparent 70%);
    pointer-events: none;
}
.rc-hero h1 {
    color: #FFFFFF !important;
}
.rc-hero p {
    color: #CBD5E1 !important;
}

.rc-pill {
    background: rgba(255, 255, 255, 0.1) !important;
    border: 1px solid rgba(255, 255, 255, 0.18) !important;
    color: #FFFFFF !important;
    padding: 6px 14px !important;
    border-radius: 8px !important;
    font-size: 13px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
}

.rc-pickup-banner {
    background: linear-gradient(90deg, #FFF7ED 0%, #FFEDD5 100%) !important;
    border: 2px solid #FDBA74 !important;
    border-radius: 16px !important;
    padding: 16px 24px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 16px !important;
    box-shadow: 0 4px 15px rgba(246, 144, 68, 0.1) !important;
}

.rc-cat-header {
    background: #FFFFFF !important;
    border: 1px solid #E2E8F0 !important;
    border-left: 6px solid #F69044 !important;
    border-radius: 12px !important;
    padding: 14px 22px !important;
    margin-bottom: 20px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.02) !important;
}
.rc-cat-header h2 {
    font-size: 19px !important;
    font-weight: 800 !important;
    color: #0F172A !important;
    letter-spacing: -0.01em !important;
    margin: 0 !important;
    text-transform: uppercase !important;
}

/* 1 Tour Per Row Luxury Card */
.rc-tour-card {
    background: #FFFFFF !important;
    border: 1px solid #E2E8F0 !important;
    border-radius: 20px !important;
    overflow: hidden !important;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04) !important;
    margin-bottom: 24px !important;
}
.rc-tour-card:hover {
    border-color: #CBD5E1 !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 14px 30px rgba(0, 0, 0, 0.08) !important;
}

.rc-img-wrap {
    position: relative !important;
    height: 100% !important;
    min-height: 220px !important;
    background: #0F172A !important;
    overflow: hidden !important;
}
.rc-img-wrap img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    transition: transform 0.4s ease !important;
}
.rc-tour-card:hover .rc-img-wrap img {
    transform: scale(1.04) !important;
}

.rc-tour-title {
    font-size: 18px !important;
    font-weight: 800 !important;
    color: #0F172A !important;
    line-height: 1.3 !important;
    letter-spacing: -0.01em !important;
}

.rc-tour-desc {
    font-size: 13px !important;
    color: #475569 !important;
    line-height: 1.55 !important;
    margin-bottom: 14px !important;
}

.rc-inc-pill {
    font-size: 11px !important;
    background: #F1F5F9 !important;
    color: #334155 !important;
    padding: 4px 10px !important;
    border-radius: 6px !important;
    font-weight: 600 !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 5px !important;
    border: 1px solid #E2E8F0 !important;
}
.rc-inc-pill i {
    color: #F69044 !important;
}

.rc-tier-box {
    background: #F8FAFC !important;
    border: 1px solid #E2E8F0 !important;
    border-radius: 14px !important;
    padding: 16px !important;
    height: 100% !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: space-between !important;
}

.rc-tier-row {
    padding: 8px 0 !important;
    border-bottom: 1px dashed #CBD5E1 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}
.rc-tier-row:last-child {
    border-bottom: none !important;
    padding-bottom: 0 !important;
}
.rc-tier-row:first-child {
    padding-top: 0 !important;
}

.rc-tier-name {
    font-size: 13px !important;
    font-weight: 700 !important;
    color: #1E293B !important;
}
.rc-tier-sub {
    font-size: 11px !important;
    color: #64748B !important;
}

.rc-cur-price {
    font-size: 16px !important;
    font-weight: 800 !important;
    color: #D95300 !important;
}
.rc-old-price {
    font-size: 11.5px !important;
    text-decoration: line-through !important;
    color: #94A3B8 !important;
    margin-right: 4px !important;
}

.rc-floating-bar {
    position: sticky !important;
    top: 76px !important;
    z-index: 99 !important;
    backdrop-filter: blur(12px) !important;
    background: rgba(255, 255, 255, 0.95) !important;
    border: 1px solid rgba(0,0,0,0.08) !important;
    border-radius: 16px !important;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06) !important;
}

/* Print CSS */
@media print {
    #header, .footer, .btn-circle-whatsapp, .whatsapp-floating-btn, .rc-floating-bar, .modal, #tabBar, .toast-container, .visually-hidden-focusable, .rc-btn-action {
        display: none !important;
    }
    body, main, #main, .rc-page {
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
    .rc-tour-card {
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
<div class="rc-page py-4 py-lg-5" style="background-color: #F8FAFC !important;">
    <div class="container">

        <!-- Top Floating Toolbar -->
        <div class="rc-floating-bar p-3 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3" style="background: rgba(255, 255, 255, 0.95) !important; border-radius: 16px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
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
        <div class="rc-hero p-4 p-lg-5 mb-4" style="background: linear-gradient(135deg, #0F172A 0%, #1E293B 60%, #334155 100%) !important; color: #FFFFFF !important; border-radius: 24px; border: 1px solid #334155; position: relative; overflow: hidden; box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.4);">
            <div class="row align-items-center g-4 position-relative z-2">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="{{ asset('images/logo-white.png') }}" alt="Dunes Discovery Tourism" style="height: 44px; width: auto;">
                        <span class="badge bg-warning text-dark fw-bold rounded-pill px-3 py-1 text-uppercase" style="font-size: 11px;">
                            ⭐ Official 2026 Price Guide
                        </span>
                    </div>
                    <h1 class="display-6 fw-bold mb-2" style="color: #FFFFFF !important; letter-spacing: -0.02em;">
                        Dubai Desert Safaris & Tours <span style="color: #F69044 !important;">Rate Card</span>
                    </h1>
                    <p class="mb-3 fs-6" style="color: #CBD5E1 !important; max-width: 620px; line-height: 1.55;">
                        Official tour portfolio & pricing catalog by Dunes Discovery Tourism LLC. Direct operator rates with best price guarantee across all UAE excursions.
                    </p>
                    <div class="d-flex flex-wrap gap-2 text-white small">
                        <span class="rc-pill">
                            <i class="bi bi-telephone-fill" style="color: #F69044;"></i> {{ $phone }}
                        </span>
                        <span class="rc-pill">
                            <i class="bi bi-whatsapp" style="color: #25D366;"></i> WhatsApp: {{ $waPhone }}
                        </span>
                        <span class="rc-pill">
                            <i class="bi bi-envelope-fill" style="color: #F69044;"></i> {{ $email }}
                        </span>
                        <span class="rc-pill">
                            <i class="bi bi-globe" style="color: #F69044;"></i> dunesdiscoverytourism.com
                        </span>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end d-none d-lg-block">
                    <div class="p-3.5 rounded-4 border d-inline-block text-start" style="background: rgba(255,255,255,0.08) !important; border-color: rgba(255,255,255,0.15) !important; min-width: 220px; border-radius: 16px;">
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
        <div class="rc-pickup-banner mb-5" style="background: linear-gradient(90deg, #FFF7ED 0%, #FFEDD5 100%) !important; border: 2px solid #FDBA74 !important; border-radius: 16px; padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; box-shadow: 0 4px 15px rgba(246, 144, 68, 0.1);">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white shadow-sm" style="width: 46px; height: 46px; font-size: 22px; flex-shrink: 0; background-color: #F69044 !important;">
                    <i class="bi bi-car-front-fill"></i>
                </div>
                <div>
                    <div class="fw-800 text-dark" style="font-size: 15px; letter-spacing: -0.01em; color: #0F172A !important;">
                        🚐 COMPLIMENTARY 4X4 DOORSTEP HOTEL PICKUP & DROP-OFF INCLUDED
                    </div>
                    <div class="text-secondary small" style="font-size: 13px; color: #475569 !important;">
                        Enjoy seamless door-to-door transportation in clean, air-conditioned Toyota Land Cruisers from any hotel, residence, or cruise terminal across Dubai & Sharjah.
                    </div>
                </div>
            </div>
            <div class="d-none d-md-block text-end text-nowrap">
                <span class="badge bg-dark text-white px-3.5 py-2 rounded-pill fw-bold" style="font-size: 11px;">
                    ✓ Zero Hidden Fees
                </span>
            </div>
        </div>

        <!-- Tours Grouped by Category (Dynamic 1 Tour Per Row) -->
        @foreach($categories as $cat)
            @if($cat->tours && $cat->tours->count() > 0)
            <div class="mb-5">
                <div class="rc-cat-header" style="background: #FFFFFF !important; border: 1px solid #E2E8F0 !important; border-left: 6px solid #F69044 !important; border-radius: 12px; padding: 14px 22px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
                    <h2 style="font-size: 19px !important; font-weight: 800 !important; color: #0F172A !important; letter-spacing: -0.01em !important; margin: 0 !important; text-transform: uppercase !important;">
                        {{ $cat->name }}
                    </h2>
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1.5 fw-bold">
                        {{ $cat->tours->count() }} Available {{ Str::plural('Experience', $cat->tours->count()) }}
                    </span>
                </div>

                @foreach($cat->tours as $t)
                <div class="rc-tour-card" style="background: #FFFFFF !important; border: 1px solid #E2E8F0 !important; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04); margin-bottom: 24px;">
                    <div class="row g-0">
                        <!-- Left: Image & Badge -->
                        <div class="col-12 col-md-3">
                            <div class="rc-img-wrap" style="position: relative; height: 100%; min-height: 220px; background: #0F172A; overflow: hidden;">
                                @if(!empty($t->hero_image))
                                    @php
                                        $imgFile = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $t->hero_image);
                                    @endphp
                                    <img src="{{ asset('images/' . $imgFile) }}" alt="{{ $t->name }}" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <img src="{{ asset('images/desert-safari-poster.avif') }}" alt="{{ $t->name }}" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
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
                                    <h3 class="rc-tour-title mb-0" style="font-size: 18px !important; font-weight: 800 !important; color: #0F172A !important; line-height: 1.3 !important; letter-spacing: -0.01em !important;">
                                        {{ $t->name }}
                                    </h3>
                                </div>
                                
                                <div class="d-flex flex-wrap gap-3 small text-muted mb-2.5">
                                    @if($t->pickup_time)
                                    <span><i class="bi bi-clock-history me-1 text-primary" style="color: #F69044 !important;"></i>{{ $t->pickup_time }} - {{ $t->dropoff_time }}</span>
                                    @endif
                                    <span><i class="bi bi-star-fill text-warning me-1"></i>{{ $t->rating ?? '4.9' }} ({{ $t->review_count ?? '500+' }} Reviews)</span>
                                </div>

                                <p class="rc-tour-desc" style="font-size: 13px !important; color: #475569 !important; line-height: 1.55 !important; margin-bottom: 14px !important;">
                                    {{ Str::limit($t->short_desc, 180) }}
                                </p>
                            </div>

                            <div>
                                <div class="d-flex flex-wrap gap-1.5">
                                    <span class="rc-inc-pill"><i class="bi bi-check-circle-fill" style="color: #F69044;"></i> Free 4x4 Pickup</span>
                                    <span class="rc-inc-pill"><i class="bi bi-check-circle-fill" style="color: #F69044;"></i> Professional Guide</span>
                                    <span class="rc-inc-pill"><i class="bi bi-check-circle-fill" style="color: #F69044;"></i> Refreshments</span>
                                    @if(str_contains(strtolower($t->name), 'evening') || str_contains(strtolower($t->name), 'cruise'))
                                    <span class="rc-inc-pill"><i class="bi bi-check-circle-fill" style="color: #F69044;"></i> Buffet Dinner</span>
                                    <span class="rc-inc-pill"><i class="bi bi-check-circle-fill" style="color: #F69044;"></i> Live Shows</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right: Pricing & Package Tiers Matrix -->
                        <div class="col-12 col-md-4 p-3.5 p-lg-4 border-start border-light bg-light bg-opacity-50">
                            <div class="rc-tier-box" style="background: #F8FAFC !important; border: 1px solid #E2E8F0 !important; border-radius: 14px; padding: 16px; height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
                                <div>
                                    <div class="text-uppercase fw-bold text-secondary mb-2" style="font-size: 11px; letter-spacing: 0.5px; color: #64748B !important;">
                                        Available Package Tiers
                                    </div>

                                    @if($t->tiers && $t->tiers->count() > 0)
                                        @foreach($t->tiers as $tier)
                                        <div class="rc-tier-row" style="padding: 8px 0 !important; border-bottom: 1px dashed #CBD5E1 !important; display: flex !important; align-items: center !important; justify-content: space-between !important;">
                                            <div>
                                                <div class="rc-tier-name" style="font-size: 13px !important; font-weight: 700 !important; color: #1E293B !important;">{{ $tier->name }}</div>
                                                @if($tier->description)
                                                <div class="rc-tier-sub" style="font-size: 11px !important; color: #64748B !important;">{{ Str::limit($tier->description, 35) }}</div>
                                                @endif
                                            </div>
                                            <div class="text-end">
                                                @if(!empty($tier->pivot->old_price))
                                                <span class="rc-old-price" style="font-size: 11.5px !important; text-decoration: line-through !important; color: #94A3B8 !important; margin-right: 4px !important;">AED {{ number_format($tier->pivot->old_price) }}</span>
                                                @endif
                                                <span class="rc-cur-price" style="font-size: 16px !important; font-weight: 800 !important; color: #D95300 !important;">AED {{ number_format($tier->pivot->price) }}</span>
                                                <div class="text-muted" style="font-size: 9.5px;">/ {{ $tier->pivot->price_type ?? 'person' }}</div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="rc-tier-row" style="padding: 8px 0 !important; border-bottom: 1px dashed #CBD5E1 !important; display: flex !important; align-items: center !important; justify-content: space-between !important;">
                                            <div>
                                                <div class="rc-tier-name" style="font-size: 13px !important; font-weight: 700 !important; color: #1E293B !important;">Standard Experience</div>
                                            </div>
                                            <div class="text-end">
                                                <span class="rc-cur-price" style="font-size: 16px !important; font-weight: 800 !important; color: #D95300 !important;">AED {{ number_format($t->price) }}</span>
                                                <div class="text-muted" style="font-size: 9.5px;">/ person</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="pt-3 mt-2 border-top d-flex gap-2 rc-btn-action">
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
            <div class="rc-cat-header" style="background: #FFFFFF !important; border: 1px solid #E2E8F0 !important; border-left: 6px solid #F69044 !important; border-radius: 12px; padding: 14px 22px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
                <h2 style="font-size: 19px !important; font-weight: 800 !important; color: #0F172A !important; letter-spacing: -0.01em !important; margin: 0 !important; text-transform: uppercase !important;">
                    Safari Add-Ons & Custom Upgrades
                </h2>
            </div>

            <div class="card p-4 border-0 rounded-4 shadow-sm bg-white" style="border-radius: 20px; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);">
                <div class="row g-3">
                    @foreach($globalAddons as $addon)
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="p-3 rounded-3 bg-light border d-flex justify-content-between align-items-center" style="border-radius: 12px;">
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 13px; color: #0F172A !important;">{{ $addon->name }}</div>
                                @if($addon->description)
                                <div class="text-muted" style="font-size: 11px; color: #64748B !important;">{{ Str::limit($addon->description, 45) }}</div>
                                @endif
                            </div>
                            <div class="fw-800 fs-6 text-nowrap ms-2" style="color: #D95300 !important; font-weight: 800;">
                                AED {{ number_format($addon->default_price ?: $addon->price ?: 0) }}
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
                <div class="card p-3.5 text-center border-0 shadow-sm h-100 rounded-4 bg-white" style="border-radius: 16px; padding: 20px;">
                    <i class="bi bi-shield-check fs-2 mb-2" style="color: #F69044;"></i>
                    <h3 class="h6 fw-bold text-dark mb-1" style="color: #0F172A !important;">Best Price Guarantee</h3>
                    <p class="text-muted mb-0 small" style="color: #64748B !important;">Direct operator pricing with zero middleman commissions.</p>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card p-3.5 text-center border-0 shadow-sm h-100 rounded-4 bg-white" style="border-radius: 16px; padding: 20px;">
                    <i class="bi bi-arrow-counterclockwise fs-2 mb-2" style="color: #F69044;"></i>
                    <h3 class="h6 fw-bold text-dark mb-1" style="color: #0F172A !important;">Free Cancellation</h3>
                    <p class="text-muted mb-0 small" style="color: #64748B !important;">100% full refund up to 24 hours prior to tour departure.</p>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card p-3.5 text-center border-0 shadow-sm h-100 rounded-4 bg-white" style="border-radius: 16px; padding: 20px;">
                    <i class="bi bi-car-front-fill fs-2 mb-2" style="color: #F69044;"></i>
                    <h3 class="h6 fw-bold text-dark mb-1" style="color: #0F172A !important;">Doorstep 4x4 Pickup</h3>
                    <p class="text-muted mb-0 small" style="color: #64748B !important;">Comfortable hotel pickup across all Dubai & Sharjah locations.</p>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card p-3.5 text-center border-0 shadow-sm h-100 rounded-4 bg-white" style="border-radius: 16px; padding: 20px;">
                    <i class="bi bi-whatsapp fs-2 mb-2" style="color: #25D366;"></i>
                    <h3 class="h6 fw-bold text-dark mb-1" style="color: #0F172A !important;">Instant Support</h3>
                    <p class="text-muted mb-0 small" style="color: #64748B !important;">Dedicated 24/7 safari concierge on WhatsApp & Phone.</p>
                </div>
            </div>
        </div>

        <!-- Bottom VIP / Custom Booking Box -->
        <div class="p-4 p-lg-5 rounded-4 bg-dark text-white text-center shadow position-relative overflow-hidden" style="background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%) !important; border-radius: 24px; border: 1px solid #334155;">
            <div class="position-relative z-2">
                <h2 class="display-6 fw-bold text-white mb-2" style="color: #FFFFFF !important;">Corporate Events & Custom VIP Camps</h2>
                <p class="text-white-50 lead fs-6 mb-4 mx-auto" style="color: #CBD5E1 !important; max-width: 650px;">
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