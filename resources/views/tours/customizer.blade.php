@extends('layouts.app')

@push('schema')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": "Service",
      "@@id": "{{ route('tours.customizer') }}#service",
      "name": "Custom Dubai Desert Safari Builder & Configurator",
      "provider": {
        "@@type": "TravelAgency",
        "name": "Dunes Discovery Tourism LLC",
        "url": "{{ url('/') }}",
        "telephone": "+971502456056"
      },
      "serviceType": "Desert Safari Customization",
      "description": "Design and build your bespoke Dubai desert safari with private 4x4 Land Cruisers, 1000cc Can-Am buggies, 400cc quad bikes, and VIP table service.",
      "areaServed": "Dubai, United Arab Emirates",
      "offers": {
        "@@type": "AggregateOffer",
        "priceCurrency": "AED",
        "lowPrice": "120",
        "highPrice": "1500"
      }
    },
    {
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
          "name": "Build Your Own Safari",
          "item": "{{ route('tours.customizer') }}"
        }
      ]
    }
  ]
}
</script>
@endpush

@section('content')
<!-- Page Header Section -->
<section class="page-header py-4 bg-dark text-white position-relative overflow-hidden" style="margin-top: calc(-1 * var(--header-h));">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at 20% 20%, rgba(246, 144, 68, 0.22) 0%, transparent 65%);"></div>
    <div class="container position-relative z-1 pt-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white text-opacity-75 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tours.index') }}" class="text-white text-opacity-75 text-decoration-none">Tours</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Custom Safari Builder</li>
            </ol>
        </nav>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
            <div>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <span class="badge rounded-pill px-3 py-1.5" style="background: rgba(246, 144, 68, 0.2); border: 1px solid #F69044; color: #F69044;">
                        <i class="bi bi-sliders me-1"></i> Interactive Safari Customizer
                    </span>
                    <span class="badge bg-success bg-opacity-75 rounded-pill px-3 py-1.5 text-white">
                        <i class="bi bi-patch-check-fill me-1"></i>DET Licensed #1430583
                    </span>
                    <span class="badge bg-warning text-dark rounded-pill px-3 py-1.5 fw-bold">
                        ⚡ Real-Time Live Pricing
                    </span>
                </div>
                <h1 class="display-4 fw-800 text-white mb-2">
                    Build Your Own Dubai Desert Safari
                </h1>
                <p class="lead text-white text-opacity-75 mb-0" style="max-width: 750px;">
                    Customize every detail of your Dubai desert expedition: private vehicles, high-power Can-Am buggies, 400cc quad bikes, and elevated VIP dining. Transparent live pricing with zero hidden fees.
                </p>
            </div>
            <div class="d-none d-lg-block text-end flex-shrink-0">
                <button type="button" class="btn btn-outline-warning rounded-pill px-3 py-2 fw-bold small" data-bs-toggle="modal" data-bs-target="#safariMatcherModal">
                    <i class="bi bi-stars me-1"></i> Try Safari Matcher AI
                </button>
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
                        <div class="fw-bold text-dark small lh-1">DTCM Licensed Operator</div>
                        <small class="text-muted" style="font-size: 11px;">Govt. Approved Fleet</small>
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
                        <div class="fw-bold text-dark small lh-1">100% Halal Live BBQ</div>
                        <small class="text-muted" style="font-size: 11px;">Veg, Non-Veg & Jain</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-shield-lock-fill text-info fs-5"></i>
                    <div class="text-start">
                        <div class="fw-bold text-dark small lh-1">Pay Online or Pickup</div>
                        <small class="text-muted" style="font-size: 11px;">Cards, Cash & Bank Transfer</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Customizer Interactive Builder Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row g-4">
            
            <!-- Left Column: Customizer Steps (8 Cols) -->
            <div class="col-12 col-lg-8">
                
                <!-- Step 1: Base Safari -->
                <div class="card border-0 rounded-4 shadow-sm p-4 mb-4" style="background: #FAF8F5; border: 1px solid rgba(246, 144, 68, 0.2) !important;">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">1</span>
                        <h4 class="fw-800 text-dark mb-0 fs-5">Select Base Safari Experience</h4>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <div class="custom-card p-3 rounded-4 cursor-pointer transition-all h-100 selected" data-group="base" data-name="Standard Evening Safari" data-price="150" data-tour-id="1">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="fs-3">🌇</span>
                                    <span class="badge bg-warning text-dark fw-bold rounded-pill">Popular</span>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">Sunset Evening Red Dunes</h6>
                                <p class="text-muted small mb-2" style="font-size: 0.8rem; line-height: 1.4;">
                                    Dune bashing, sunset photo stop, camel ride, sandboarding, 5-star live BBQ dinner & 3 cultural shows.
                                </p>
                                <div class="fw-800 text-primary fs-6" data-aed="150">AED 150 <small class="text-muted fw-normal" style="font-size: 11px;">/ guest</small></div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="custom-card p-3 rounded-4 cursor-pointer transition-all h-100" data-group="base" data-name="VIP Luxury Evening Safari" data-price="250" data-tour-id="2">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="fs-3">👑</span>
                                    <span class="badge bg-dark text-warning fw-bold rounded-pill">VIP Perks</span>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">VIP Luxury Red Dunes Safari</h6>
                                <p class="text-muted small mb-2" style="font-size: 0.8rem; line-height: 1.4;">
                                    Extreme Lahbab dune bashing, reserved elevated VIP table with private dedicated waiter & table service.
                                </p>
                                <div class="fw-800 text-primary fs-6" data-aed="250">AED 250 <small class="text-muted fw-normal" style="font-size: 11px;">/ guest</small></div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="custom-card p-3 rounded-4 cursor-pointer transition-all h-100" data-group="base" data-name="Morning Desert Safari" data-price="120" data-tour-id="4">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="fs-3">🌅</span>
                                    <span class="badge bg-info text-white fw-bold rounded-pill">Cooler</span>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">Fresh Morning Desert Adventure</h6>
                                <p class="text-muted small mb-2" style="font-size: 0.8rem; line-height: 1.4;">
                                    Crisp morning air, 40-min high dune drive, sunrise photos, camel ride & sandboarding. Back by 12:30 PM.
                                </p>
                                <div class="fw-800 text-primary fs-6" data-aed="120">AED 120 <small class="text-muted fw-normal" style="font-size: 11px;">/ guest</small></div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="custom-card p-3 rounded-4 cursor-pointer transition-all h-100" data-group="base" data-name="Overnight Stargazing Safari" data-price="350" data-tour-id="5">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="fs-3">🌌</span>
                                    <span class="badge bg-success text-white fw-bold rounded-pill">Glamping</span>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">Overnight Desert Camp & Stargazing</h6>
                                <p class="text-muted small mb-2" style="font-size: 0.8rem; line-height: 1.4;">
                                    Full evening safari + private overnight Bedouin tent, campfire stargazing & freshly cooked sunrise breakfast.
                                </p>
                                <div class="fw-800 text-primary fs-6" data-aed="350">AED 350 <small class="text-muted fw-normal" style="font-size: 11px;">/ guest</small></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Transfer Logistics -->
                <div class="card border-0 rounded-4 shadow-sm p-4 mb-4" style="background: #FAF8F5; border: 1px solid rgba(246, 144, 68, 0.2) !important;">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">2</span>
                        <h4 class="fw-800 text-dark mb-0 fs-5">Choose Vehicle & Transfer Logistics</h4>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <div class="custom-card p-3 rounded-4 cursor-pointer transition-all h-100 selected" data-group="transfer" data-name="Shared 4x4 Land Cruiser" data-price="0" data-type="flat">
                                <div class="fs-3 mb-2">🚙</div>
                                <h6 class="fw-bold text-dark mb-1">Shared 4x4 Pickup</h6>
                                <p class="text-muted small mb-2" style="font-size: 0.8rem; line-height: 1.4;">
                                    Doorstep hotel pickup in a 6-passenger Land Cruiser shared with other friendly travelers.
                                </p>
                                <div class="fw-bold text-success fs-6">INCLUDED FREE</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="custom-card p-3 rounded-4 cursor-pointer transition-all h-100" data-group="transfer" data-name="Private 7-Seater Land Cruiser" data-price="350" data-type="flat">
                                <div class="fs-3 mb-2">💎</div>
                                <h6 class="fw-bold text-dark mb-1">Private 7-Seater 4x4</h6>
                                <p class="text-muted small mb-2" style="font-size: 0.8rem; line-height: 1.4;">
                                    Exclusive Land Cruiser strictly for your party. Flexible pickup timing & customized dune drive intensity.
                                </p>
                                <div class="fw-800 text-primary fs-6" data-aed="350">+AED 350 <small class="text-muted fw-normal" style="font-size: 11px;">/ vehicle</small></div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="custom-card p-3 rounded-4 cursor-pointer transition-all h-100" data-group="transfer" data-name="VIP Luxury SUV / Range Rover" data-price="750" data-type="flat">
                                <div class="fs-3 mb-2">🏎️</div>
                                <h6 class="fw-bold text-dark mb-1">VIP Luxury Range Rover</h6>
                                <p class="text-muted small mb-2" style="font-size: 0.8rem; line-height: 1.4;">
                                    Ultra-luxury VIP SUV pickup with premium chilled amenities, cold towels, and first-class chauffeur.
                                </p>
                                <div class="fw-800 text-primary fs-6" data-aed="750">+AED 750 <small class="text-muted fw-normal" style="font-size: 11px;">/ vehicle</small></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Desert Adrenaline & Motorized Sports -->
                <div class="card border-0 rounded-4 shadow-sm p-4 mb-4" style="background: #FAF8F5; border: 1px solid rgba(246, 144, 68, 0.2) !important;">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">3</span>
                        <h4 class="fw-800 text-dark mb-0 fs-5">Add Desert Adrenaline & Self-Drive</h4>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="custom-card p-3 rounded-4 cursor-pointer transition-all h-100 selected" data-group="sports" data-name="No Motorized Sports" data-price="0" data-type="per_person">
                                <div class="fs-3 mb-2">🐪</div>
                                <h6 class="fw-bold text-dark mb-1">Scenic Only</h6>
                                <p class="text-muted small mb-2" style="font-size: 0.78rem; line-height: 1.35;">
                                    Enjoy 4x4 dune bashing, camel riding, and sandboarding without motor sports.
                                </p>
                                <div class="fw-bold text-success fs-6">INCLUDED</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="custom-card p-3 rounded-4 cursor-pointer transition-all h-100" data-group="sports" data-name="30-Min 250cc Quad Biking" data-price="120" data-type="per_person">
                                <div class="fs-3 mb-2">🏍️</div>
                                <h6 class="fw-bold text-dark mb-1">30-Min 250cc Quad</h6>
                                <p class="text-muted small mb-2" style="font-size: 0.78rem; line-height: 1.35;">
                                    Dedicated desert quad track session with helmet, goggles & guide assistance.
                                </p>
                                <div class="fw-800 text-primary fs-6" data-aed="120">+AED 120 <small class="text-muted fw-normal" style="font-size: 11px;">/ guest</small></div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="custom-card p-3 rounded-4 cursor-pointer transition-all h-100" data-group="sports" data-name="60-Min 400cc Quad Biking" data-price="220" data-type="per_person">
                                <div class="fs-3 mb-2">⚡</div>
                                <h6 class="fw-bold text-dark mb-1">60-Min 400cc Quad</h6>
                                <p class="text-muted small mb-2" style="font-size: 0.78rem; line-height: 1.35;">
                                    Open red dunes exploration with high-power Yamaha 400cc automatic ATV.
                                </p>
                                <div class="fw-800 text-primary fs-6" data-aed="220">+AED 220 <small class="text-muted fw-normal" style="font-size: 11px;">/ guest</small></div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="custom-card p-3 rounded-4 cursor-pointer transition-all h-100" data-group="sports" data-name="1000cc Can-Am Buggy (2-Seater)" data-price="550" data-type="flat">
                                <div class="fs-3 mb-2">🏎️</div>
                                <h6 class="fw-bold text-dark mb-1">1000cc Can-Am Buggy</h6>
                                <p class="text-muted small mb-2" style="font-size: 0.78rem; line-height: 1.35;">
                                    2-Seater Can-Am Maverick Turbo buggy with roll cage & 4-point racing harness.
                                </p>
                                <div class="fw-800 text-primary fs-6" data-aed="550">+AED 550 <small class="text-muted fw-normal" style="font-size: 11px;">/ vehicle</small></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Dining & Camp Upgrades (Multi-Select) -->
                <div class="card border-0 rounded-4 shadow-sm p-4 mb-4" style="background: #FAF8F5; border: 1px solid rgba(246, 144, 68, 0.2) !important;">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">4</span>
                        <h4 class="fw-800 text-dark mb-0 fs-5">Dining & Luxury Camp Additions</h4>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="custom-addon-item p-3 rounded-4 bg-white border d-flex align-items-center justify-content-between gap-2 cursor-pointer transition-all" data-addon="vip_table" data-name="VIP Table & Private Waiter" data-price="60" data-type="per_person">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="form-check m-0">
                                        <input class="form-check-input addon-checkbox" type="checkbox" id="addonVipTable" style="transform: scale(1.2);">
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">VIP Raised Table & Private Waiter</div>
                                        <small class="text-muted" style="font-size: 11px;">No buffet lines, table-side food service</small>
                                    </div>
                                </div>
                                <div class="fw-bold text-primary small text-nowrap" data-aed="60">+AED 60/guest</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="custom-addon-item p-3 rounded-4 bg-white border d-flex align-items-center justify-content-between gap-2 cursor-pointer transition-all" data-addon="shisha" data-name="Private Table Shisha" data-price="50" data-type="flat">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="form-check m-0">
                                        <input class="form-check-input addon-checkbox" type="checkbox" id="addonShisha" style="transform: scale(1.2);">
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">Premium Shisha at Your Table</div>
                                        <small class="text-muted" style="font-size: 11px;">Choice of Double Apple, Mint, Grape</small>
                                    </div>
                                </div>
                                <div class="fw-bold text-primary small text-nowrap" data-aed="50">+AED 50/table</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="custom-addon-item p-3 rounded-4 bg-white border d-flex align-items-center justify-content-between gap-2 cursor-pointer transition-all" data-addon="falcon" data-name="Falconry Keepsake Photo" data-price="40" data-type="per_person">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="form-check m-0">
                                        <input class="form-check-input addon-checkbox" type="checkbox" id="addonFalcon" style="transform: scale(1.2);">
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">Royal Falconry Photo & Glove</div>
                                        <small class="text-muted" style="font-size: 11px;">Hold a trained UAE falcon for iconic photos</small>
                                    </div>
                                </div>
                                <div class="fw-bold text-primary small text-nowrap" data-aed="40">+AED 40/guest</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="custom-addon-item p-3 rounded-4 bg-white border d-flex align-items-center justify-content-between gap-2 cursor-pointer transition-all" data-addon="cake" data-name="Desert Birthday / Anniversary Cake" data-price="80" data-type="flat">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="form-check m-0">
                                        <input class="form-check-input addon-checkbox" type="checkbox" id="addonCake" style="transform: scale(1.2);">
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">Celebration Cake & Music</div>
                                        <small class="text-muted" style="font-size: 11px;">Chocolate or Vanilla with personalized message</small>
                                    </div>
                                </div>
                                <div class="fw-bold text-primary small text-nowrap" data-aed="80">+AED 80/cake</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Party Size -->
                <div class="card border-0 rounded-4 shadow-sm p-4" style="background: #FAF8F5; border: 1px solid rgba(246, 144, 68, 0.2) !important;">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">5</span>
                        <h4 class="fw-800 text-dark mb-0 fs-5">Set Number of Guests</h4>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <div class="p-3 bg-white rounded-4 border d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-bold text-dark">Adults (Age 11+)</div>
                                    <small class="text-muted" style="font-size: 11px;">Full activity & dinner access</small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-outline-secondary rounded-circle p-0 d-flex align-items-center justify-content-center" id="customizerAdultsMinus" style="width: 36px; height: 36px;">
                                        <i class="bi bi-dash fs-5"></i>
                                    </button>
                                    <span class="fw-800 fs-5 px-2" id="customizerAdultsCount">2</span>
                                    <button type="button" class="btn btn-outline-primary rounded-circle p-0 d-flex align-items-center justify-content-center" id="customizerAdultsPlus" style="width: 36px; height: 36px;">
                                        <i class="bi bi-plus fs-5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="p-3 bg-white rounded-4 border d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-bold text-dark">Children (Age 3 - 10)</div>
                                    <small class="text-muted" style="font-size: 11px;">30% discount on base package</small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-outline-secondary rounded-circle p-0 d-flex align-items-center justify-content-center" id="customizerChildrenMinus" style="width: 36px; height: 36px;">
                                        <i class="bi bi-dash fs-5"></i>
                                    </button>
                                    <span class="fw-800 fs-5 px-2" id="customizerChildrenCount">0</span>
                                    <button type="button" class="btn btn-outline-primary rounded-circle p-0 d-flex align-items-center justify-content-center" id="customizerChildrenPlus" style="width: 36px; height: 36px;">
                                        <i class="bi bi-plus fs-5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Live Sticky Summary Receipt (4 Cols) -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 rounded-4 shadow-lg p-4 sticky-top" style="top: calc(var(--header-h, 80px) + 20px); background: #0B1120; border: 1.5px solid rgba(246, 144, 68, 0.35) !important; color: #ffffff;">
                    
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-white border-opacity-10">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-4">🧾</span>
                            <div>
                                <h5 class="fw-800 text-white mb-0 fs-6">Custom Safari Spec</h5>
                                <small class="text-white-50" style="font-size: 11px;">Live Calculation</small>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 fw-bold" style="font-size: 10px;">
                            Verified Rates
                        </span>
                    </div>

                    <!-- Itemized Specs List -->
                    <div class="mb-3 small" style="font-size: 0.85rem;">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-white-50">Base Safari:</span>
                            <span class="fw-bold text-white text-end" id="summaryBaseName">Standard Evening</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-white-50">Transfer:</span>
                            <span class="fw-bold text-white text-end" id="summaryTransferName">Shared 4x4</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-white-50">Motorsports:</span>
                            <span class="fw-bold text-white text-end" id="summarySportsName">Scenic Only</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-white-50">Addons:</span>
                            <span class="fw-bold text-white text-end" id="summaryAddonsName">None</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-white-50">Party Size:</span>
                            <span class="fw-bold text-white text-end" id="summaryGuests">2 Adults</span>
                        </div>
                    </div>

                    <!-- Price Subtotal -->
                    <div class="pt-3 border-top border-white border-opacity-10 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-white-50 small">Estimated Total:</span>
                            <div class="text-end">
                                <span class="display-6 fw-800 text-warning lh-1" id="customizerTotalDisplay" data-aed="300">AED 300</span>
                                <small class="text-white-50 d-block" style="font-size: 11px;">inclusive of all taxes & DTCM fees</small>
                            </div>
                        </div>
                    </div>

                    <!-- Promo Code Input for MATCH5 / SAVE5 -->
                    <div class="mb-3">
                        <div class="input-group rounded-pill overflow-hidden border border-secondary border-opacity-25" style="background: rgba(255, 255, 255, 0.05);">
                            <input type="text" class="form-control border-0 bg-transparent text-white font-monospace text-uppercase px-3 small" id="customizerPromoInput" placeholder="Promo code (e.g. MATCH5)" style="font-size: 0.8rem;">
                            <button class="btn btn-outline-warning border-0 px-3 fw-bold small" type="button" id="customizerApplyPromoBtn">Apply</button>
                        </div>
                        <div class="small text-success fw-bold mt-1 d-none" id="customizerPromoNotice">
                            <i class="bi bi-check-circle-fill me-1"></i> 5% Discount Applied!
                        </div>
                    </div>

                    <!-- Action CTAs -->
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-desert-animated btn-lg rounded-pill py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2" id="customizerBookNowBtn">
                            <i class="bi bi-calendar-check-fill"></i>
                            <span>Book Custom Safari Online</span>
                        </button>
                        <a href="#" target="_blank" rel="noopener" class="btn btn-whatsapp-animated rounded-pill py-2.5 fw-bold d-flex align-items-center justify-content-center gap-2" id="customizerWhatsAppBtn">
                            <i class="bi bi-whatsapp"></i>
                            <span>Inquire via WhatsApp</span>
                        </a>
                    </div>

                    <div class="text-center mt-3 pt-2 border-top border-white border-opacity-10">
                        <small class="text-white-50" style="font-size: 11px;">
                            <i class="bi bi-shield-check text-success me-1"></i> Free cancellation up to 24h before tour
                        </small>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<style>
.custom-card {
    background: #ffffff;
    border: 1.5px solid rgba(0, 0, 0, 0.08);
}
.custom-card:hover {
    transform: translateY(-2px);
    border-color: #F69044 !important;
    box-shadow: 0 10px 20px -5px rgba(246, 144, 68, 0.2);
}
.custom-card.selected {
    border-color: #F69044 !important;
    background: #FFF9F3 !important;
    box-shadow: 0 0 0 2px rgba(246, 144, 68, 0.35);
}
.custom-addon-item:hover {
    border-color: #F69044 !important;
}
.custom-addon-item.selected {
    border-color: #F69044 !important;
    background: #FFF9F3 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // State
    const state = {
        base: { name: 'Sunset Evening Red Dunes', price: 150, tourId: 1 },
        transfer: { name: 'Shared 4x4 Pickup', price: 0, type: 'flat' },
        sports: { name: 'Scenic Only', price: 0, type: 'per_person' },
        addons: [],
        adults: 2,
        children: 0,
        discountPercent: 0,
    };

    // DOM Elements
    const adultsCountEl = document.getElementById('customizerAdultsCount');
    const childrenCountEl = document.getElementById('customizerChildrenCount');
    const summaryBaseEl = document.getElementById('summaryBaseName');
    const summaryTransferEl = document.getElementById('summaryTransferName');
    const summarySportsEl = document.getElementById('summarySportsName');
    const summaryAddonsEl = document.getElementById('summaryAddonsName');
    const summaryGuestsEl = document.getElementById('summaryGuests');
    const totalDisplayEl = document.getElementById('customizerTotalDisplay');
    const promoInputEl = document.getElementById('customizerPromoInput');
    const promoNoticeEl = document.getElementById('customizerPromoNotice');
    const applyPromoBtn = document.getElementById('customizerApplyPromoBtn');
    const bookNowBtn = document.getElementById('customizerBookNowBtn');
    const whatsAppBtn = document.getElementById('customizerWhatsAppBtn');

    // Selection handlers for radio groups (base, transfer, sports)
    ['base', 'transfer', 'sports'].forEach(group => {
        document.querySelectorAll(`.custom-card[data-group="${group}"]`).forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll(`.custom-card[data-group="${group}"]`).forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');

                state[group] = {
                    name: this.getAttribute('data-name'),
                    price: parseFloat(this.getAttribute('data-price') || 0),
                    type: this.getAttribute('data-type') || 'per_person',
                    tourId: this.getAttribute('data-tour-id') || null
                };

                updateCalculation();
            });
        });
    });

    // Selection handlers for multi-select addons
    document.querySelectorAll('.custom-addon-item').forEach(item => {
        const checkbox = item.querySelector('.addon-checkbox');

        item.addEventListener('click', function(e) {
            if (e.target !== checkbox) {
                checkbox.checked = !checkbox.checked;
            }
            toggleAddon(item, checkbox.checked);
        });

        if (checkbox) {
            checkbox.addEventListener('change', function() {
                toggleAddon(item, this.checked);
            });
        }
    });

    function toggleAddon(item, isChecked) {
        const addonKey = item.getAttribute('data-addon');
        const name = item.getAttribute('data-name');
        const price = parseFloat(item.getAttribute('data-price') || 0);
        const type = item.getAttribute('data-type') || 'per_person';

        if (isChecked) {
            item.classList.add('selected');
            if (!state.addons.find(a => a.key === addonKey)) {
                state.addons.push({ key: addonKey, name, price, type });
            }
        } else {
            item.classList.remove('selected');
            state.addons = state.addons.filter(a => a.key !== addonKey);
        }

        updateCalculation();
    }

    // Guest counter adjustments
    document.getElementById('customizerAdultsMinus').addEventListener('click', () => {
        if (state.adults > 1) {
            state.adults--;
            adultsCountEl.innerText = state.adults;
            updateCalculation();
        }
    });
    document.getElementById('customizerAdultsPlus').addEventListener('click', () => {
        if (state.adults < 30) {
            state.adults++;
            adultsCountEl.innerText = state.adults;
            updateCalculation();
        }
    });

    document.getElementById('customizerChildrenMinus').addEventListener('click', () => {
        if (state.children > 0) {
            state.children--;
            childrenCountEl.innerText = state.children;
            updateCalculation();
        }
    });
    document.getElementById('customizerChildrenPlus').addEventListener('click', () => {
        if (state.children < 20) {
            state.children++;
            childrenCountEl.innerText = state.children;
            updateCalculation();
        }
    });

    // Promo code
    if (applyPromoBtn) {
        applyPromoBtn.addEventListener('click', () => {
            const code = (promoInputEl.value || '').trim().toUpperCase();
            if (code === 'MATCH5' || code === 'SAVE5' || code === 'WELCOME5') {
                state.discountPercent = 5;
                promoNoticeEl.classList.remove('d-none');
                promoNoticeEl.innerText = `Promo ${code} applied (5% OFF)`;
                updateCalculation();
            } else {
                state.discountPercent = 0;
                promoNoticeEl.classList.remove('d-none');
                promoNoticeEl.className = 'small text-danger fw-bold mt-1';
                promoNoticeEl.innerText = 'Invalid promo code. Try MATCH5 or SAVE5';
                updateCalculation();
            }
        });
    }

    // Calculation Engine
    function updateCalculation() {
        const totalGuests = state.adults + state.children;
        const childMultiplier = 0.70; // 30% discount on child base price

        // Base price
        let baseTotal = (state.base.price * state.adults) + (state.base.price * childMultiplier * state.children);

        // Transfer price
        let transferTotal = (state.transfer.type === 'flat') ? state.transfer.price : (state.transfer.price * totalGuests);

        // Sports price
        let sportsTotal = (state.sports.type === 'flat') ? state.sports.price : (state.sports.price * state.adults);

        // Addons price
        let addonsTotal = 0;
        state.addons.forEach(a => {
            if (a.type === 'flat') {
                addonsTotal += a.price;
            } else {
                addonsTotal += (a.price * totalGuests);
            }
        });

        let subtotal = baseTotal + transferTotal + sportsTotal + addonsTotal;
        let discountAmount = (subtotal * state.discountPercent) / 100;
        let finalTotal = Math.max(0, Math.round(subtotal - discountAmount));

        // Update Summary DOM
        summaryBaseEl.innerText = state.base.name;
        summaryTransferEl.innerText = state.transfer.name;
        summarySportsEl.innerText = state.sports.name;

        if (state.addons.length > 0) {
            summaryAddonsEl.innerText = state.addons.map(a => a.name).join(', ');
        } else {
            summaryAddonsEl.innerText = 'None';
        }

        let guestsText = `${state.adults} Adult${state.adults > 1 ? 's' : ''}`;
        if (state.children > 0) {
            guestsText += `, ${state.children} Child${state.children > 1 ? 'ren' : ''}`;
        }
        summaryGuestsEl.innerText = guestsText;

        totalDisplayEl.setAttribute('data-aed', finalTotal);
        totalDisplayEl.innerText = `AED ${finalTotal}`;

        // Dynamic currency conversion
        if (window.DunesApp && typeof window.DunesApp.updatePrices === 'function') {
            window.DunesApp.updatePrices();
        }

        // WhatsApp pre-filled text
        let waMessage = `Hi Dunes Discovery! I built a custom safari on your portal:%0A%0A` +
            `• Base: ${encodeURIComponent(state.base.name)}%0A` +
            `• Vehicle: ${encodeURIComponent(state.transfer.name)}%0A` +
            `• Motorsports: ${encodeURIComponent(state.sports.name)}%0A` +
            `• Addons: ${encodeURIComponent(summaryAddonsEl.innerText)}%0A` +
            `• Guests: ${encodeURIComponent(guestsText)}%0A` +
            `• Total: AED ${finalTotal}%0A%0A` +
            `Could you please check availability for this custom setup?`;

        whatsAppBtn.href = `https://wa.me/{{ preg_replace('/[^0-9]/','',(string)(\App\Models\Setting::where('setting_key', 'site_whatsapp')->value('setting_value') ?? '971502456056')) }}?text=${waMessage}`;
    }

    // Book Now Handler: transfer custom spec to Booking Modal
    if (bookNowBtn) {
        bookNowBtn.addEventListener('click', function() {
            const bookingModalEl = document.getElementById('bookingModal');
            if (!bookingModalEl) return;

            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const bModal = bootstrap.Modal.getOrCreateInstance(bookingModalEl);
                bModal.show();
            }

            // Set Tour ID in select
            const tourSelect = document.getElementById('bookingTour');
            if (tourSelect && state.base.tourId) {
                tourSelect.value = state.base.tourId;
                tourSelect.dispatchEvent(new Event('change'));
            }

            // Set Adults
            const adultsInput = document.getElementById('bookingAdults');
            if (adultsInput) {
                adultsInput.value = state.adults;
            }

            // Set Children
            const childrenInput = document.getElementById('bookingChildren');
            if (childrenInput) {
                childrenInput.value = state.children;
            }

            // Populate Special Requests with full custom spec
            const requestsInput = document.getElementById('bookingRequests');
            if (requestsInput) {
                const customSpec = `[CUSTOM SAFARI BUILDER SPEC]\n` +
                    `Vehicle: ${state.transfer.name}\n` +
                    `Motorsports: ${state.sports.name}\n` +
                    `Addons: ${summaryAddonsEl.innerText}\n` +
                    `Calculated Total: AED ${totalDisplayEl.getAttribute('data-aed') || totalDisplayEl.innerText}`;
                requestsInput.value = customSpec;
            }

            // Apply promo code if entered
            const promo = (promoInputEl.value || '').trim();
            if (promo) {
                const promoInput = document.getElementById('bookingPromoCode');
                if (promoInput) promoInput.value = promo;
                if (typeof window.validateCurrentPromo === 'function') {
                    setTimeout(() => window.validateCurrentPromo(), 400);
                }
            }
        });
    }

    // Initial calculation
    updateCalculation();
});
</script>
@endsection
