@php
    $settingsService = app(\App\Services\SettingsService::class);
    $conciergePromoActive = ($settingsService->get('concierge_promo_active', '0') === '1');
@endphp
@extends('layouts.app')

@push('schema')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Service",
      "@id": {!! json_encode(($canonical ?? localized_route('tours.customizer')) . '#service', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
      "name": "Custom Dubai Desert Safari Builder & Configurator",
      "inLanguage": {!! json_encode($currentLocale ?? app()->getLocale() ?? 'en', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
      "provider": {
        "@type": "TravelAgency",
        "name": "Dunes Discovery Tourism LLC",
        "url": {!! json_encode(url('/'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
        "telephone": "+971502456056"
      },
      "serviceType": "Desert Safari Customization",
      "description": "Design and build your bespoke Dubai desert safari with private 4x4 Land Cruisers, 1000cc Can-Am buggies, 400cc quad bikes, and VIP table service.",
      "areaServed": "Dubai, United Arab Emirates",
      "offers": {
        "@type": "AggregateOffer",
        "priceCurrency": "AED",
        "lowPrice": "120",
        "highPrice": "1500",
        "offerCount": "6"
      }
    },
    {
      "@type": "BreadcrumbList",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": {!! json_encode(__('ui.nav.home') ?? 'Home', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
          "item": {!! json_encode(localized_route('home'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": {!! json_encode(__('ui.nav.tours') ?? 'Tours', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
          "item": {!! json_encode(localized_route('tours.index'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "Build Your Own Safari",
          "item": {!! json_encode($canonical ?? localized_route('tours.customizer'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        }
      ]
    }
  ]
}
</script>
@endpush

@section('content')
<!-- Page Header Section -->
<section class="hero-subpage bg-slate-950 text-white relative overflow-hidden" style="padding-top: calc(var(--header-h, 72px) + 2.5rem);">
    <div class="absolute inset-0 w-full h-full bg-[radial-gradient(ellipse_at_20%_20%,rgba(246,144,68,0.22)_0%,transparent_65%)]"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <nav aria-label="{{ __('ui.common.breadcrumb') }}">
            <ol class="flex items-center gap-2 text-xs sm:text-sm text-white/70 mb-4">
                <li><a href="{{ localized_route('home') }}" class="hover:text-white transition-colors">{{ __('ui.nav.home') ?? 'Home' }}</a></li>
                <li><span class="text-white/40">/</span></li>
                <li><a href="{{ localized_route('tours.index') }}" class="hover:text-white transition-colors">{{ __('ui.nav.tours') ?? 'Tours' }}</a></li>
                <li><span class="text-white/40">/</span></li>
                <li class="text-white font-semibold" aria-current="page">Custom Safari Builder</li>
            </ol>
        </nav>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <div class="flex flex-wrap gap-2 mb-3">
                    <span class="rounded-full px-3.5 py-1 text-xs font-bold bg-primary/20 border border-primary/40 text-primary inline-flex items-center gap-1.5">
                        <i class="bi bi-sliders"></i> Interactive Safari Customizer
                    </span>
                    <span class="bg-emerald-600/90 rounded-full px-3.5 py-1 text-xs font-semibold text-white inline-flex items-center gap-1.5">
                        <i class="bi bi-patch-check-fill text-emerald-200"></i>DET Licensed #1430583
                    </span>
                    <span class="bg-amber-400 text-slate-950 rounded-full px-3.5 py-1 text-xs font-bold inline-flex items-center gap-1.5">
                        <i class="bi bi-lightning-charge-fill"></i> Real-Time Live Pricing
                    </span>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-2">
                    Build Your Own Dubai Desert Safari
                </h1>
                <p class="text-sm sm:text-base text-white/80 max-w-2xl leading-relaxed">
                    Customize every detail of your Dubai desert expedition: private vehicles, high-power Can-Am buggies, 400cc quad bikes, and elevated VIP dining. Transparent live pricing with zero hidden fees.
                </p>
            </div>
            <div class="hidden lg:block shrink-0">
                <button type="button" class="border border-amber-400 text-amber-400 hover:bg-amber-400 hover:text-slate-950 rounded-full px-4 py-2 text-xs font-bold transition-colors cursor-pointer inline-flex items-center gap-1.5" @click="$store.modal.open('safari-matcher')">
                    <i class="bi bi-compass"></i> Try Safari Match Concierge
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Trust & Regulatory E-E-A-T Bar -->
<section class="bg-slate-50 py-3.5 border-b border-slate-200 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <div class="flex items-center gap-2.5">
                <i class="bi bi-patch-check-fill text-primary text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">DTCM Licensed Operator</div>
                    <div class="text-slate-500 text-[11px]">Govt. Approved Fleet</div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <i class="bi bi-arrow-repeat text-emerald-600 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">100% Free Cancellation</div>
                    <div class="text-slate-500 text-[11px]">Full refund 24h prior</div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <i class="bi bi-award-fill text-amber-500 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">100% Halal Live BBQ</div>
                    <div class="text-slate-500 text-[11px]">Veg, Non-Veg & Jain</div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <i class="bi bi-shield-lock-fill text-cyan-600 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">Pay Online or Pickup</div>
                    <div class="text-slate-500 text-[11px]">Cards, Cash & Bank Transfer</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Customizer Interactive Builder Section -->
<section class="py-10 sm:py-16 pb-28 lg:pb-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
            
            <!-- Left Column: Customizer Steps (8 Cols) -->
            <div class="lg:col-span-8 space-y-6">

                <!-- Mobile 5-Step Progress Bar (In-Flow, Zero-Collision) -->
                <div class="lg:hidden relative z-10 bg-white rounded-2xl border border-slate-200 p-2 shadow-xs overflow-x-auto scrollbar-none flex items-center gap-1.5 snap-x mb-2">
                    <a href="#step-1" class="snap-start shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-orange-50 text-primary text-xs font-bold border border-orange-200/80">
                        <span class="w-5 h-5 rounded-full bg-primary text-white text-[10px] font-black flex items-center justify-center">1</span>
                        <span>Base</span>
                    </a>
                    <a href="#step-2" class="snap-start shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 text-slate-700 hover:text-primary text-xs font-semibold border border-slate-200">
                        <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 text-[10px] font-bold flex items-center justify-center">2</span>
                        <span>Vehicle</span>
                    </a>
                    <a href="#step-3" class="snap-start shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 text-slate-700 hover:text-primary text-xs font-semibold border border-slate-200">
                        <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 text-[10px] font-bold flex items-center justify-center">3</span>
                        <span>Motorsport</span>
                    </a>
                    <a href="#step-4" class="snap-start shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 text-slate-700 hover:text-primary text-xs font-semibold border border-slate-200">
                        <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 text-[10px] font-bold flex items-center justify-center">4</span>
                        <span>Add-ons</span>
                    </a>
                    <a href="#step-5" class="snap-start shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 text-slate-700 hover:text-primary text-xs font-semibold border border-slate-200">
                        <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 text-[10px] font-bold flex items-center justify-center">5</span>
                        <span>Guests</span>
                    </a>
                </div>
                
                <!-- Step 1: Base Safari -->
                <div id="step-1" class="scroll-mt-32 bg-amber-50/30 rounded-2xl p-6 border border-primary/20 shadow-xs">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-7 h-7 rounded-full bg-primary text-white font-bold text-sm flex items-center justify-center shrink-0">1</span>
                        <h4 class="font-bold text-slate-900 text-base sm:text-lg">Select Base Safari Experience</h4>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="custom-card p-4 rounded-2xl cursor-pointer transition-all bg-white border-2 border-primary bg-primary/5 shadow-xs selected" data-group="base" data-name="Standard Evening Safari" data-price="79" data-tour-id="1">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-2xl text-amber-500"><i class="bi bi-sunset"></i></span>
                                <span class="bg-amber-400 text-slate-950 font-bold rounded-full px-2.5 py-0.5 text-xs">Popular</span>
                            </div>
                            <h6 class="font-bold text-slate-900 text-sm mb-1">Sunset Evening Red Dunes</h6>
                            <p class="text-slate-500 text-xs mb-3 leading-relaxed">
                                Dune bashing, sunset photo stop, camel ride, sandboarding, 5-star live BBQ dinner & 3 cultural shows.
                            </p>
                            <div class="font-black text-primary text-base" data-aed="79">{{ __('ui.common.aed') }} 79 <span class="text-slate-400 font-normal text-xs">/ guest</span></div>
                        </div>

                        <div class="custom-card p-4 rounded-2xl cursor-pointer transition-all bg-white border-2 border-slate-200 hover:border-primary/50 shadow-xs" data-group="base" data-name="VIP Luxury Evening Safari" data-price="130" data-tour-id="1">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-2xl text-amber-500"><i class="bi bi-award"></i></span>
                                <span class="bg-slate-900 text-amber-400 font-bold rounded-full px-2.5 py-0.5 text-xs">VIP Perks</span>
                            </div>
                            <h6 class="font-bold text-slate-900 text-sm mb-1">VIP Luxury Red Dunes Safari</h6>
                            <p class="text-slate-500 text-xs mb-3 leading-relaxed">
                                Extreme Lahbab dune bashing, reserved elevated VIP table with private dedicated waiter & table service.
                            </p>
                            <div class="font-black text-primary text-base" data-aed="130">{{ __('ui.common.aed') }} 130 <span class="text-slate-400 font-normal text-xs">/ guest</span></div>
                        </div>

                        <div class="custom-card p-4 rounded-2xl cursor-pointer transition-all bg-white border-2 border-slate-200 hover:border-primary/50 shadow-xs" data-group="base" data-name="Morning Desert Safari" data-price="140" data-tour-id="2">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-2xl text-amber-500"><i class="bi bi-sunrise"></i></span>
                                <span class="bg-cyan-600 text-white font-bold rounded-full px-2.5 py-0.5 text-xs">Cooler</span>
                            </div>
                            <h6 class="font-bold text-slate-900 text-sm mb-1">Fresh Morning Desert Adventure</h6>
                            <p class="text-slate-500 text-xs mb-3 leading-relaxed">
                                Crisp morning air, 25-30 min red dune drive, pristine desert photography, camel ride & sandboarding. Back by 11:00 AM.
                            </p>
                            <div class="font-black text-primary text-base" data-aed="140">{{ __('ui.common.aed') }} 140 <span class="text-slate-400 font-normal text-xs">/ guest</span></div>
                        </div>

                        <div class="custom-card p-4 rounded-2xl cursor-pointer transition-all bg-white border-2 border-slate-200 hover:border-primary/50 shadow-xs" data-group="base" data-name="Overnight Stargazing Safari" data-price="399" data-tour-id="3">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-2xl text-amber-500"><i class="bi bi-moon-stars"></i></span>
                                <span class="bg-emerald-600 text-white font-bold rounded-full px-2.5 py-0.5 text-xs">Glamping</span>
                            </div>
                            <h6 class="font-bold text-slate-900 text-sm mb-1">Overnight Desert Camp & Stargazing</h6>
                            <p class="text-slate-500 text-xs mb-3 leading-relaxed">
                                Full evening safari + private overnight Bedouin tent, campfire stargazing & freshly cooked sunrise breakfast.
                            </p>
                            <div class="font-black text-primary text-base" data-aed="399">{{ __('ui.common.aed') }} 399 <span class="text-slate-400 font-normal text-xs">/ guest</span></div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Transfer Logistics -->
                <div id="step-2" class="scroll-mt-32 bg-amber-50/30 rounded-2xl p-6 border border-primary/20 shadow-xs">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-7 h-7 rounded-full bg-primary text-white font-bold text-sm flex items-center justify-center shrink-0">2</span>
                        <h4 class="font-bold text-slate-900 text-base sm:text-lg">Choose Vehicle & Transfer Logistics</h4>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="custom-card p-4 rounded-2xl cursor-pointer transition-all bg-white border-2 border-primary bg-primary/5 shadow-xs selected" data-group="transfer" data-name="Shared 4x4 Land Cruiser" data-price="0" data-type="flat">
                            <div class="text-2xl text-amber-500 mb-2"><i class="bi bi-car-front-fill"></i></div>
                            <h6 class="font-bold text-slate-900 text-sm mb-1">Shared 4x4 Pickup</h6>
                            <p class="text-slate-500 text-xs mb-3 leading-relaxed">
                                Doorstep hotel pickup in a 6-passenger Land Cruiser shared with other friendly travelers.
                            </p>
                            <div class="font-bold text-emerald-600 text-xs">INCLUDED FREE</div>
                        </div>

                        <div class="custom-card p-4 rounded-2xl cursor-pointer transition-all bg-white border-2 border-slate-200 hover:border-primary/50 shadow-xs" data-group="transfer" data-name="Private 7-Seater Land Cruiser" data-price="350" data-type="flat">
                            <div class="text-2xl text-amber-500 mb-2"><i class="bi bi-gem"></i></div>
                            <h6 class="font-bold text-slate-900 text-sm mb-1">Private 7-Seater 4x4</h6>
                            <p class="text-slate-500 text-xs mb-3 leading-relaxed">
                                Exclusive Land Cruiser strictly for your party. Flexible pickup timing & customized dune drive intensity.
                            </p>
                            <div class="font-black text-primary text-sm" data-aed="350">+AED 350 <span class="text-slate-400 font-normal text-xs">/ vehicle</span></div>
                        </div>

                        <div class="custom-card p-4 rounded-2xl cursor-pointer transition-all bg-white border-2 border-slate-200 hover:border-primary/50 shadow-xs" data-group="transfer" data-name="VIP Luxury SUV / Range Rover" data-price="750" data-type="flat">
                            <div class="text-2xl text-amber-500 mb-2"><i class="bi bi-speedometer2"></i></div>
                            <h6 class="font-bold text-slate-900 text-sm mb-1">VIP Luxury Range Rover</h6>
                            <p class="text-slate-500 text-xs mb-3 leading-relaxed">
                                Ultra-luxury VIP SUV pickup with premium chilled amenities, cold towels, and first-class chauffeur.
                            </p>
                            <div class="font-black text-primary text-sm" data-aed="750">+AED 750 <span class="text-slate-400 font-normal text-xs">/ vehicle</span></div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Desert Adrenaline & Motorized Sports -->
                <div id="step-3" class="scroll-mt-32 bg-amber-50/30 rounded-2xl p-6 border border-primary/20 shadow-xs">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-7 h-7 rounded-full bg-primary text-white font-bold text-sm flex items-center justify-center shrink-0">3</span>
                        <h4 class="font-bold text-slate-900 text-base sm:text-lg">Add Desert Adrenaline & Self-Drive</h4>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="custom-card p-4 rounded-2xl cursor-pointer transition-all bg-white border-2 border-primary bg-primary/5 shadow-xs selected" data-group="sports" data-name="No Motorized Sports" data-price="0" data-type="per_person">
                            <div class="text-2xl text-amber-500 mb-2"><i class="bi bi-compass"></i></div>
                            <h6 class="font-bold text-slate-900 text-sm mb-1">Scenic Only</h6>
                            <p class="text-slate-500 text-xs mb-3 leading-relaxed">
                                Enjoy 4x4 dune bashing, camel riding, and sandboarding without motor sports.
                            </p>
                            <div class="font-bold text-emerald-600 text-xs">INCLUDED</div>
                        </div>

                        <div class="custom-card p-4 rounded-2xl cursor-pointer transition-all bg-white border-2 border-slate-200 hover:border-primary/50 shadow-xs" data-group="sports" data-name="30-Min 250cc Quad Biking" data-price="120" data-type="per_person">
                            <div class="text-2xl text-amber-500 mb-2"><i class="bi bi-bicycle"></i></div>
                            <h6 class="font-bold text-slate-900 text-sm mb-1">30-Min 250cc Quad</h6>
                            <p class="text-slate-500 text-xs mb-3 leading-relaxed">
                                Dedicated desert quad track session with helmet, goggles & guide assistance.
                            </p>
                            <div class="font-black text-primary text-sm" data-aed="120">+AED 120 <span class="text-slate-400 font-normal text-xs">/ guest</span></div>
                        </div>

                        <div class="custom-card p-4 rounded-2xl cursor-pointer transition-all bg-white border-2 border-slate-200 hover:border-primary/50 shadow-xs" data-group="sports" data-name="60-Min 400cc Quad Biking" data-price="220" data-type="per_person">
                            <div class="text-2xl text-amber-500 mb-2"><i class="bi bi-lightning-charge"></i></div>
                            <h6 class="font-bold text-slate-900 text-sm mb-1">60-Min 400cc Quad</h6>
                            <p class="text-slate-500 text-xs mb-3 leading-relaxed">
                                Open red dunes exploration with high-power Yamaha 400cc automatic ATV.
                            </p>
                            <div class="font-black text-primary text-sm" data-aed="220">+AED 220 <span class="text-slate-400 font-normal text-xs">/ guest</span></div>
                        </div>

                        <div class="custom-card p-4 rounded-2xl cursor-pointer transition-all bg-white border-2 border-slate-200 hover:border-primary/50 shadow-xs" data-group="sports" data-name="1000cc Can-Am Buggy (2-Seater)" data-price="550" data-type="flat">
                            <div class="text-2xl text-amber-500 mb-2"><i class="bi bi-speedometer2"></i></div>
                            <h6 class="font-bold text-slate-900 text-sm mb-1">1000cc Can-Am Buggy</h6>
                            <p class="text-slate-500 text-xs mb-3 leading-relaxed">
                                2-Seater Can-Am Maverick Turbo buggy with roll cage & 4-point racing harness.
                            </p>
                            <div class="font-black text-primary text-sm" data-aed="550">+AED 550 <span class="text-slate-400 font-normal text-xs">/ vehicle</span></div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Dining & Camp Upgrades (Multi-Select) -->
                <div id="step-4" class="scroll-mt-32 bg-amber-50/30 rounded-2xl p-6 border border-primary/20 shadow-xs">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-7 h-7 rounded-full bg-primary text-white font-bold text-sm flex items-center justify-center shrink-0">4</span>
                        <h4 class="font-bold text-slate-900 text-base sm:text-lg">Dining & Luxury Camp Additions</h4>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="custom-addon-item p-4 rounded-2xl bg-white border border-slate-200 hover:border-primary/50 flex items-center justify-between gap-3 cursor-pointer transition-all shadow-xs" data-addon="vip_table" data-name="VIP Table & Private Waiter" data-price="60" data-type="per_person">
                            <div class="flex items-center gap-3">
                                <input class="addon-checkbox rounded text-primary focus:ring-primary w-4 h-4 cursor-pointer" type="checkbox" id="addonVipTable">
                                <div>
                                    <div class="font-bold text-slate-900 text-xs sm:text-sm">VIP Raised Table & Private Waiter</div>
                                    <span class="text-slate-500 text-[11px] block">No buffet lines, table-side food service</span>
                                </div>
                            </div>
                            <div class="font-bold text-primary text-xs whitespace-nowrap" data-aed="60">+AED 60/guest</div>
                        </div>

                        <div class="custom-addon-item p-4 rounded-2xl bg-white border border-slate-200 hover:border-primary/50 flex items-center justify-between gap-3 cursor-pointer transition-all shadow-xs" data-addon="shisha" data-name="Private Table Shisha" data-price="50" data-type="flat">
                            <div class="flex items-center gap-3">
                                <input class="addon-checkbox rounded text-primary focus:ring-primary w-4 h-4 cursor-pointer" type="checkbox" id="addonShisha">
                                <div>
                                    <div class="font-bold text-slate-900 text-xs sm:text-sm">Premium Shisha at Your Table</div>
                                    <span class="text-slate-500 text-[11px] block">Choice of Double Apple, Mint, Grape</span>
                                </div>
                            </div>
                            <div class="font-bold text-primary text-xs whitespace-nowrap" data-aed="50">+AED 50/table</div>
                        </div>

                        <div class="custom-addon-item p-4 rounded-2xl bg-white border border-slate-200 hover:border-primary/50 flex items-center justify-between gap-3 cursor-pointer transition-all shadow-xs" data-addon="falcon" data-name="Falconry Keepsake Photo" data-price="40" data-type="per_person">
                            <div class="flex items-center gap-3">
                                <input class="addon-checkbox rounded text-primary focus:ring-primary w-4 h-4 cursor-pointer" type="checkbox" id="addonFalcon">
                                <div>
                                    <div class="font-bold text-slate-900 text-xs sm:text-sm">Royal Falconry Photo & Glove</div>
                                    <span class="text-slate-500 text-[11px] block">Hold a trained UAE falcon for iconic photos</span>
                                </div>
                            </div>
                            <div class="font-bold text-primary text-xs whitespace-nowrap" data-aed="40">+AED 40/guest</div>
                        </div>

                        <div class="custom-addon-item p-4 rounded-2xl bg-white border border-slate-200 hover:border-primary/50 flex items-center justify-between gap-3 cursor-pointer transition-all shadow-xs" data-addon="cake" data-name="Desert Birthday / Anniversary Cake" data-price="80" data-type="flat">
                            <div class="flex items-center gap-3">
                                <input class="addon-checkbox rounded text-primary focus:ring-primary w-4 h-4 cursor-pointer" type="checkbox" id="addonCake">
                                <div>
                                    <div class="font-bold text-slate-900 text-xs sm:text-sm">Celebration Cake & Music</div>
                                    <span class="text-slate-500 text-[11px] block">Chocolate or Vanilla with personalized message</span>
                                </div>
                            </div>
                            <div class="font-bold text-primary text-xs whitespace-nowrap" data-aed="80">+AED 80/cake</div>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Party Size -->
                <div id="step-5" class="scroll-mt-32 bg-amber-50/30 rounded-2xl p-6 border border-primary/20 shadow-xs">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-7 h-7 rounded-full bg-primary text-white font-bold text-sm flex items-center justify-center shrink-0">5</span>
                        <h4 class="font-bold text-slate-900 text-base sm:text-lg">Set Number of Guests</h4>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 bg-white rounded-2xl border border-slate-200 flex items-center justify-between shadow-xs">
                            <div>
                                <div class="font-bold text-slate-900 text-sm">Adults (Age 11+)</div>
                                <span class="text-slate-500 text-[11px]">Full activity & dinner access</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="w-11 h-11 rounded-full border border-slate-300 hover:border-slate-400 flex items-center justify-center text-slate-700 cursor-pointer min-h-[44px]" id="customizerAdultsMinus" aria-label="Decrease Adults">
                                    <i class="bi bi-dash text-lg"></i>
                                </button>
                                <span class="font-black text-lg px-2 text-slate-900" id="customizerAdultsCount">2</span>
                                <button type="button" class="w-11 h-11 rounded-full border border-primary bg-primary text-white hover:bg-primary-hover flex items-center justify-center cursor-pointer min-h-[44px]" id="customizerAdultsPlus" aria-label="Increase Adults">
                                    <i class="bi bi-plus text-lg"></i>
                                </button>
                            </div>
                        </div>

                        <div class="p-4 bg-white rounded-2xl border border-slate-200 flex items-center justify-between shadow-xs">
                            <div>
                                <div class="font-bold text-slate-900 text-sm">Children (Age 3 - 10)</div>
                                <span class="text-slate-500 text-[11px]">30% discount on base package</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="w-11 h-11 rounded-full border border-slate-300 hover:border-slate-400 flex items-center justify-center text-slate-700 cursor-pointer min-h-[44px]" id="customizerChildrenMinus" aria-label="Decrease Children">
                                    <i class="bi bi-dash text-lg"></i>
                                </button>
                                <span class="font-black text-lg px-2 text-slate-900" id="customizerChildrenCount">0</span>
                                <button type="button" class="w-11 h-11 rounded-full border border-primary bg-primary text-white hover:bg-primary-hover flex items-center justify-center cursor-pointer min-h-[44px]" id="customizerChildrenPlus" aria-label="Increase Children">
                                    <i class="bi bi-plus text-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Live Sticky Summary Receipt (4 Cols) -->
            <div class="lg:col-span-4">
                <div class="sticky top-24 bg-slate-950 text-white rounded-2xl p-6 shadow-2xl border border-primary/35">
                    
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-white/10">
                        <div class="flex items-center gap-2.5">
                            <span class="text-xl text-amber-400"><i class="bi bi-receipt"></i></span>
                            <div>
                                <h5 class="font-bold text-white text-base">Custom Safari Spec</h5>
                                <span class="text-slate-300 text-xs">Live Calculation</span>
                            </div>
                        </div>
                        <span class="bg-emerald-500/20 text-emerald-400 rounded-full px-2.5 py-0.5 text-[10px] font-bold">
                            Verified Rates
                        </span>
                    </div>

                    <!-- Itemized Specs List -->
                    <div class="space-y-2 mb-4 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-300">Base Safari:</span>
                            <span class="font-bold text-white text-end" id="summaryBaseName">Standard Evening</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-300">Transfer:</span>
                            <span class="font-bold text-white text-end" id="summaryTransferName">Shared 4x4</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-300">Motorsports:</span>
                            <span class="font-bold text-white text-end" id="summarySportsName">Scenic Only</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-300">Addons:</span>
                            <span class="font-bold text-white text-end" id="summaryAddonsName">None</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-300">Party Size:</span>
                            <span class="font-bold text-white text-end" id="summaryGuests">2 Adults</span>
                        </div>
                    </div>

                    <!-- Price Subtotal -->
                    <div class="pt-4 border-t border-white/20 mb-4">
                        <div class="flex justify-between items-baseline mb-1">
                            <span class="text-slate-300 text-xs">Estimated Total:</span>
                            <div class="text-end">
                                <span class="text-3xl font-black text-amber-400" id="customizerTotalDisplay" data-aed="300">{{ __('ui.common.aed') }} 300</span>
                                <span class="text-slate-300 block text-[10px] mt-0.5">inclusive of all taxes & DTCM fees</span>
                            </div>
                        </div>
                    </div>

                    <!-- Promo Code Input (e.g. DUNESWELCOME) -->
                    <div class="mb-5">
                        <div class="flex rounded-full overflow-hidden border border-white/20 bg-white/5">
                            <input type="text" class="w-full bg-transparent text-white font-mono uppercase px-4 py-2 text-xs focus:outline-none placeholder-slate-500" id="customizerPromoInput" placeholder="{{ __('ui.booking.promo_placeholder') }}">
                            <button class="bg-amber-400 hover:bg-amber-500 text-slate-950 font-bold px-4 py-2 text-xs transition-colors cursor-pointer shrink-0" type="button" id="customizerApplyPromoBtn">{{ __('ui.booking.apply') }}</button>
                        </div>
                        <div class="text-xs text-emerald-400 font-bold mt-1.5 hidden" id="customizerPromoNotice">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('ui.booking.promo_applied') }}
                        </div>
                    </div>

                    <!-- Action CTAs -->
                    <div class="space-y-2.5">
                        <button type="button" class="w-full btn-desert-animated font-bold rounded-full py-3.5 text-white text-sm shadow-md flex items-center justify-center gap-2 cursor-pointer" id="customizerBookNowBtn">
                            <i class="bi bi-calendar-check-fill"></i>
                            <span>{{ __('ui.common.book_online') }}</span>
                        </button>
                        <a href="#" target="_blank" rel="noopener" class="btn-whatsapp w-full btn-whatsapp-animated rounded-full py-3 font-bold text-white text-sm flex items-center justify-center gap-2 shadow-xs cursor-pointer" id="customizerWhatsAppBtn" data-action="whatsapp" data-tour-name="Bespoke Dubai Desert Safari">
                            <i class="bi bi-whatsapp"></i>
                            <span>{{ __('ui.common.whatsapp_inquire') }}</span>
                        </a>
                    </div>

                    <div class="text-center mt-4 pt-3 border-t border-white/10">
                        <span class="text-slate-400 text-[11px] inline-flex items-center gap-1">
                            <i class="bi bi-shield-check text-emerald-400"></i> Free cancellation up to 24h before tour
                        </span>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<!-- Sticky Mobile Bottom Calculation Bar (visible on screens below lg) -->
<div x-data x-show="!$store.modal.active && !$store.mobileNav.open" x-cloak class="fixed bottom-0 inset-x-0 bg-slate-950/95 backdrop-blur-md px-4 py-3 border-t border-white/15 shadow-2xl lg:hidden flex items-center justify-between z-40 pb-safe" style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom, 0.75rem));">
    <div>
        <span class="text-slate-400 block text-[9px] uppercase font-bold tracking-wider">Estimated Total</span>
        <div class="text-xl font-black text-amber-400" id="customizerMobileTotalDisplay" data-aed="300">{{ __('ui.common.aed') }} 300</div>
    </div>
    <div class="flex items-center gap-2">
        <a href="#" target="_blank" rel="noopener" class="w-11 h-11 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs cursor-pointer" id="customizerMobileWhatsAppBtn" aria-label="Inquire via WhatsApp">
            <i class="bi bi-whatsapp text-lg"></i>
        </a>
        <button type="button" class="btn-desert-animated text-xs sm:text-sm font-bold text-white rounded-full px-5 py-2.5 shadow-md whitespace-nowrap cursor-pointer inline-flex items-center justify-center gap-1.5 min-h-[44px] h-11" id="customizerMobileBookBtn">
            <i class="bi bi-calendar-check-fill"></i>
            <span>{{ __('ui.common.book_online') }}</span>
        </button>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // State
    const state = {
        base: { name: 'Sunset Evening Red Dunes', price: 79, tourId: 1 },
        transfer: { name: 'Shared 4x4 Pickup', price: 0, type: 'flat' },
        sports: { name: 'Scenic Only', price: 0, type: 'per_person' },
        addons: [],
        adults: 2,
        children: 0,
        discountPercent: 0,
        discountType: null,
        discountValue: 0,
        discountAmount: 0,
        maxDiscount: 0,
    };

    function computeSubtotal() {
        const totalGuests = state.adults + state.children;
        const childMultiplier = 0.70;
        const baseTotal = (state.base.price * state.adults) + (state.base.price * childMultiplier * state.children);
        const transferTotal = (state.transfer.type === 'flat') ? state.transfer.price : (state.transfer.price * totalGuests);
        let sportsTotal = 0;
        if (state.sports.price > 0) {
            if (state.sports.name && (state.sports.name.includes('Buggy') || state.sports.name.includes('Can-Am'))) {
                sportsTotal = 550 * Math.max(1, Math.ceil(state.adults / 2));
            } else if (state.sports.type === 'flat') {
                sportsTotal = state.sports.price;
            } else {
                sportsTotal = state.sports.price * state.adults;
            }
        }
        let addonsTotal = 0;
        state.addons.forEach(a => {
            addonsTotal += (a.type === 'flat') ? a.price : (a.price * totalGuests);
        });
        return baseTotal + transferTotal + sportsTotal + addonsTotal;
    }

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
                document.querySelectorAll(`.custom-card[data-group="${group}"]`).forEach(c => {
                    c.classList.remove('border-primary', 'bg-primary/5', 'selected');
                    c.classList.add('border-slate-200');
                });
                this.classList.remove('border-slate-200');
                this.classList.add('border-primary', 'bg-primary/5', 'selected');

                state[group] = {
                    name: this.getAttribute('data-name'),
                    price: parseFloat(this.getAttribute('data-price') || 0),
                    type: this.getAttribute('data-type') || 'per_person',
                    tourId: this.getAttribute('data-tour-id') || null
                };

                if (group === 'base') {
                    const isOvernight = (state.base.tourId == 3 || (state.base.name && state.base.name.toLowerCase().includes('overnight')));
                    if (isOvernight) {
                        if (state.adults < 2) {
                            state.adults = 2;
                        } else if (state.adults % 2 !== 0) {
                            state.adults += 1;
                        }
                        if (adultsCountEl) adultsCountEl.innerText = state.adults;
                    }
                }

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
            item.classList.add('border-primary', 'bg-primary/5', 'selected');
            item.classList.remove('border-slate-200');
            if (!state.addons.find(a => a.key === addonKey)) {
                state.addons.push({ key: addonKey, name, price, type });
            }
        } else {
            item.classList.remove('border-primary', 'bg-primary/5', 'selected');
            item.classList.add('border-slate-200');
            state.addons = state.addons.filter(a => a.key !== addonKey);
        }

        updateCalculation();
    }

    // Guest counter adjustments
    document.getElementById('customizerAdultsMinus').addEventListener('click', () => {
        const isOvernight = (state.base.tourId == 3 || (state.base.name && state.base.name.toLowerCase().includes('overnight')));
        const minAdults = isOvernight ? 2 : 1;
        const step = isOvernight ? 2 : 1;
        if (state.adults > minAdults) {
            state.adults -= step;
            if (isOvernight && state.adults % 2 !== 0) state.adults--;
            if (state.adults < minAdults) state.adults = minAdults;
            adultsCountEl.innerText = state.adults;
            updateCalculation();
        }
    });
    document.getElementById('customizerAdultsPlus').addEventListener('click', () => {
        const isOvernight = (state.base.tourId == 3 || (state.base.name && state.base.name.toLowerCase().includes('overnight')));
        const step = isOvernight ? 2 : 1;
        if (state.adults < 30) {
            state.adults += step;
            if (isOvernight && state.adults % 2 !== 0) state.adults++;
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
        applyPromoBtn.addEventListener('click', async () => {
            const code = (promoInputEl.value || '').trim().toUpperCase();
            if (!code) {
                state.discountPercent = 0;
                state.discountType = null;
                state.discountValue = 0;
                state.discountAmount = 0;
                state.maxDiscount = 0;
                promoNoticeEl.classList.add('hidden');
                updateCalculation();
                return;
            }

            try {
                const subtotal = computeSubtotal();
                const resp = await fetch('/api/v1/coupon/validate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        code: code,
                        subtotal: subtotal || 100,
                        adults: state.adults,
                        children: state.children,
                        guests: Math.max(1, state.adults + state.children),
                        tour_id: state.base.tourId ? parseInt(state.base.tourId, 10) : null
                    })
                });
                const data = await resp.json();
                if (data.success && data.coupon) {
                    state.discountType = data.coupon.discount_type || 'percentage';
                    state.discountValue = parseFloat(data.coupon.discount_value || data.coupon.discount_percent || 0);
                    state.discountAmount = parseFloat(data.coupon.discount_amount || 0);
                    state.maxDiscount = parseFloat(data.coupon.max_discount || 0);
                    state.discountPercent = state.discountType === 'percentage' ? state.discountValue : 0;
                    promoNoticeEl.classList.remove('hidden', 'text-red-400');
                    promoNoticeEl.classList.add('text-emerald-400');
                    const label = state.discountType === 'percentage'
                        ? `${state.discountValue}% OFF`
                        : (state.discountType === 'per_person' ? `AED ${state.discountValue}/guest OFF` : `AED ${state.discountValue} OFF`);
                    promoNoticeEl.innerText = `Promo ${code} applied (${label})`;
                    updateCalculation();
                } else {
                    state.discountPercent = 0;
                    state.discountType = null;
                    state.discountValue = 0;
                    state.discountAmount = 0;
                    state.maxDiscount = 0;
                    promoNoticeEl.classList.remove('hidden', 'text-emerald-400');
                    promoNoticeEl.classList.add('text-red-400');
                    promoNoticeEl.innerText = data.message || 'Invalid or inactive promo code.';
                    updateCalculation();
                }
            } catch (err) {
                state.discountPercent = 0;
                state.discountType = null;
                state.discountValue = 0;
                state.discountAmount = 0;
                state.maxDiscount = 0;
                promoNoticeEl.classList.remove('hidden', 'text-emerald-400');
                promoNoticeEl.classList.add('text-red-400');
                promoNoticeEl.innerText = 'Unable to validate promo code.';
                updateCalculation();
            }
        });

        if (promoInputEl) {
            promoInputEl.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    applyPromoBtn.click();
                }
            });
        }
    }

    // Calculation Engine
    function updateCalculation() {
        const totalGuests = Math.max(1, state.adults + state.children);
        const subtotal = computeSubtotal();
        let discountAmount = 0;
        if (state.discountType === 'percentage' && state.discountValue > 0) {
            discountAmount = (subtotal * state.discountValue) / 100;
            if (state.maxDiscount > 0 && discountAmount > state.maxDiscount) {
                discountAmount = state.maxDiscount;
            }
        } else if (state.discountType === 'per_person' && state.discountValue > 0) {
            discountAmount = state.discountValue * totalGuests;
            if (state.maxDiscount > 0 && discountAmount > state.maxDiscount) {
                discountAmount = state.maxDiscount;
            }
        } else if (state.discountType === 'fixed' && state.discountValue > 0) {
            discountAmount = state.discountValue;
        } else if (state.discountAmount > 0) {
            discountAmount = state.discountAmount;
        }
        if (discountAmount > subtotal) discountAmount = subtotal;
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
        const mobileTotalDisplayEl = document.getElementById('customizerMobileTotalDisplay');
        if (mobileTotalDisplayEl) {
            mobileTotalDisplayEl.setAttribute('data-aed', finalTotal);
            mobileTotalDisplayEl.innerText = `AED ${finalTotal}`;
        }

        // Dynamic currency conversion
        if (window.Alpine && window.Alpine.store('currency')) {
            window.Alpine.store('currency').convertAllPrices();
        } else if (window.DunesApp && typeof window.DunesApp.convertAllPrices === 'function') {
            window.DunesApp.convertAllPrices();
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

        whatsAppBtn.href = `https://wa.me/{{ preg_replace('/[^0-9]/','',(string)($settings['site_whatsapp'] ?? '971502456056')) }}?text=${waMessage}`;
        const mobileWhatsAppBtn = document.getElementById('customizerMobileWhatsAppBtn');
        if (mobileWhatsAppBtn) {
            mobileWhatsAppBtn.href = whatsAppBtn.href;
        }
    }

    // Book Now Handler: transfer custom spec to Booking Modal
    const mobileBookBtn = document.getElementById('customizerMobileBookBtn');
    if (mobileBookBtn && bookNowBtn) {
        mobileBookBtn.addEventListener('click', () => bookNowBtn.click());
    }

    if (bookNowBtn) {
        bookNowBtn.addEventListener('click', function() {
            const promo = (promoInputEl ? promoInputEl.value : '').trim();
            const subtotalVal = parseFloat(computeSubtotal().toFixed(2));
            const calcAed = parseInt(totalDisplayEl.getAttribute('data-aed') || totalDisplayEl.innerText.replace(/[^0-9]/g, ''), 10) || subtotalVal;
            const customQuote = {
                is_custom_builder: 1,
                base_tour_id: state.base.tourId,
                base_name: state.base.name,
                base_price: state.base.price,
                transfer_name: state.transfer.name,
                transfer_price: state.transfer.price,
                transfer_type: state.transfer.type,
                sports_name: state.sports.name,
                sports_price: state.sports.price,
                sports_type: state.sports.type,
                addons: state.addons.map(a => ({ key: a.key, name: a.name, price: a.price, type: a.type })),
                adults: state.adults,
                children: state.children,
                subtotal: subtotalVal,
                total: calcAed,
                promo: promo || undefined,
                spec_text: `[CUSTOM SAFARI BUILDER SPEC]\n` +
                    `• Base Safari: ${state.base.name} (AED ${state.base.price}/guest)\n` +
                    `• Transfer Vehicle: ${state.transfer.name} (${state.transfer.price > 0 ? '+AED ' + state.transfer.price : 'Included'})\n` +
                    `• Motorsports: ${state.sports.name} (${state.sports.price > 0 ? '+AED ' + state.sports.price + (state.sports.type === 'per_person' ? '/guest' : '') : 'Included'})\n` +
                    `• Camp Addons: ${state.addons.length > 0 ? state.addons.map(a => a.name + ' (+AED ' + a.price + ')').join(', ') : 'None'}\n` +
                    `• Party Size: ${state.adults} Adults${state.children > 0 ? ', ' + state.children + ' Children' : ''}\n` +
                    `• Guaranteed Total: AED ${calcAed}`
            };

            if (window.Alpine && window.Alpine.store('modal')) {
                window.Alpine.store('modal').open('booking', { 
                    tourId: state.base.tourId,
                    adults: state.adults,
                    children: state.children,
                    promo: promo || undefined,
                    customQuote: customQuote
                });
            }

            // Set Tour ID in select
            const tourSelect = document.getElementById('bookingTour');
            if (tourSelect && state.base.tourId) {
                tourSelect.value = state.base.tourId;
            }

            // Set Adults & Children
            const adultsInput = document.getElementById('bookingAdults');
            if (adultsInput) adultsInput.value = state.adults;
            const childrenInput = document.getElementById('bookingChildren');
            if (childrenInput) childrenInput.value = state.children;

            // Populate Special Requests with full custom spec
            const requestsInput = document.getElementById('bookingRequests');
            if (requestsInput) {
                requestsInput.value = customQuote.spec_text;
            }

            // Set custom quote inputs in form
            const isCustomInp = document.getElementById('bookingIsCustomBuilder');
            if (isCustomInp) isCustomInp.value = '1';
            const customDataInp = document.getElementById('bookingCustomQuoteData');
            if (customDataInp) customDataInp.value = JSON.stringify(customQuote);

            // Apply promo code if entered
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
@endpush
@endsection
