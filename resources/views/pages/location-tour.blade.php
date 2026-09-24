@extends('layouts.app')

@push('schema')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebPage",
      "@id": "{{ $canonical }}#webpage",
      "url": "{{ $canonical }}",
      "name": "{{ $locationData['meta_title'] }}",
      "description": "{{ $locationData['meta_desc'] }}",
      "isPartOf": {
        "@type": "WebSite",
        "@id": "{{ url('/') }}#website",
        "name": "Dunes Discovery Tourism",
        "url": "{{ url('/') }}"
      },
      "breadcrumb": {
        "@type": "BreadcrumbList",
        "itemListElement": [
          {
            "@type": "ListItem",
            "position": 1,
            "name": "Home",
            "item": "{{ rtrim(url('/'), '/') }}/"
          },
          {
            "@type": "ListItem",
            "position": 2,
            "name": "Desert Safari Dubai",
            "item": "{{ route('tours.index') }}"
          },
          {
            "@type": "ListItem",
            "position": 3,
            "name": "{{ $locationData['name'] }} Pickup",
            "item": "{{ $canonical }}"
          }
        ]
      }
    },
    {
      "@type": "TouristTrip",
      "@id": "{{ $canonical }}#trip",
      "name": "{{ $locationData['headline'] }}",
      "description": "{{ $locationData['subheadline'] }}",
      "provider": {
        "@type": "TravelAgency",
        "name": "Dunes Discovery Tourism LLC",
        "url": "{{ url('/') }}",
        "telephone": "+971502456056",
        "license": "DET License #1430583"
      },
      "touristType": ["Adventure Tourists", "Couples", "Families", "Luxury Travelers"],
      "itinerary": {
        "@type": "ItemList",
        "numberOfItems": 6,
        "itemListElement": [
          {
            "@type": "ListItem",
            "position": 1,
            "name": "Doorstep Pickup from {{ $locationData['name'] }} ({{ $locationData['pickup_window'] }})"
          },
          {
            "@type": "ListItem",
            "position": 2,
            "name": "High Dune Bashing at Lahbab Red Dunes"
          },
          {
            "@type": "ListItem",
            "position": 3,
            "name": "Sunset Photography & Sandboarding"
          },
          {
            "@type": "ListItem",
            "position": 4,
            "name": "Bedouin Camp Welcome (Arabic Coffee, Dates & Camel Riding)"
          },
          {
            "@type": "ListItem",
            "position": 5,
            "name": "5-Star Live BBQ Buffet with Tanoura & Fire Shows"
          },
          {
            "@type": "ListItem",
            "position": 6,
            "name": "Return Drop-off to {{ $locationData['name'] }} ({{ $locationData['return_time'] }})"
          }
        ]
      },
      "areaServed": {
        "@type": "Place",
        "name": "{{ $locationData['district'] }}",
        "geo": {
          "@type": "GeoCoordinates",
          "latitude": {{ $locationData['geo']['lat'] }},
          "longitude": {{ $locationData['geo']['lng'] }}
        }
      },
      "offers": {
        "@type": "AggregateOffer",
        "priceCurrency": "AED",
        "lowPrice": "99",
        "highPrice": "450",
        "offerCount": "{{ count($safariTours) }}"
      },
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.9",
        "reviewCount": "1250",
        "bestRating": "5",
        "worstRating": "1"
      }
    },
    {
      "@type": "FAQPage",
      "@id": "{{ $canonical }}#faq",
      "mainEntity": [
        @foreach($locationData['faqs'] as $index => $faq)
        {
          "@type": "Question",
          "name": {!! json_encode($faq['q']) !!},
          "acceptedAnswer": {
            "@type": "Answer",
            "text": {!! json_encode($faq['a']) !!}
          }
        }{{ $loop->last ? '' : ',' }}
        @endforeach
      ]
    }
  ]
}
</script>
@endpush

@section('content')
<div class="bg-slate-950 text-slate-100 min-h-screen">

    <!-- ── HERO SECTION ──────────────────────────────────────────────────────── -->
    <section class="relative pt-28 pb-14 sm:pt-36 sm:pb-20 border-b border-white/10 overflow-hidden" style="background: radial-gradient(circle at 80% 20%, rgba(246, 144, 68, 0.18) 0%, transparent 60%), radial-gradient(circle at 10% 80%, rgba(30, 41, 59, 0.8) 0%, transparent 70%), #0B1120;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-5">
                <ol class="flex items-center gap-2 text-xs text-white/60 flex-wrap">
                    <li><a href="{{ url('/') }}" class="hover:text-white transition-colors inline-flex items-center gap-1"><i class="bi bi-house-door"></i> Home</a></li>
                    <li class="text-white/30">/</li>
                    <li><a href="{{ route('tours.index') }}" class="hover:text-white transition-colors">Desert Safaris</a></li>
                    <li class="text-white/30">/</li>
                    <li class="text-amber-400 font-medium" aria-current="page">{{ $locationData['name'] }} Pickup</li>
                </ol>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                <div class="lg:col-span-8">
                    <!-- Badges -->
                    <div class="flex flex-wrap gap-2.5 mb-4">
                        <span class="inline-flex items-center gap-1.5 bg-amber-500/15 border border-amber-500/35 text-amber-400 text-xs px-3.5 py-1.5 rounded-full font-bold">
                            <i class="bi bi-geo-alt-fill"></i> Official {{ $locationData['name'] }} Hub
                        </span>
                        <span class="inline-flex items-center gap-1.5 bg-white/5 border border-white/10 text-slate-300 text-xs px-3.5 py-1.5 rounded-full font-medium">
                            <i class="bi bi-shield-check text-emerald-400"></i> DET License #1430583
                        </span>
                        <span class="inline-flex items-center gap-1.5 bg-white/5 border border-white/10 text-slate-300 text-xs px-3.5 py-1.5 rounded-full font-medium">
                            <i class="bi bi-stars text-amber-400"></i> 4.9/5 (1,250+ Reviews)
                        </span>
                    </div>

                    <!-- Headline & Subheadline -->
                    <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight mb-4">
                        {{ $locationData['headline'] }}
                    </h1>
                    <p class="text-slate-300 text-base sm:text-lg leading-relaxed mb-8 max-w-3xl font-light">
                        {{ $locationData['subheadline'] }} Enjoy seamless, stress-free desert adventures with verified door-to-door 4x4 transfers, desert master captains, live BBQ buffet feasts, and sunset dune bashing.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3.5 mb-8">
                        <a href="#safari-packages" class="btn-desert-animated font-bold rounded-full px-7 py-3.5 text-white text-center shadow-lg inline-flex items-center justify-center gap-2 text-sm sm:text-base">
                            <i class="bi bi-compass"></i> View Safaris & Reserve Seats
                        </a>
                        <a href="https://wa.me/971502456056?text={{ urlencode('Hi Dunes Discovery Tourism, I am staying in ' . $locationData['name'] . ' and would like to inquire about Desert Safari hotel pickup.') }}" target="_blank" rel="noopener" class="border border-white/25 hover:bg-white/10 text-white font-bold rounded-full px-6 py-3.5 text-center transition-colors inline-flex items-center justify-center gap-2 text-sm sm:text-base">
                            <i class="bi bi-whatsapp text-emerald-400"></i> WhatsApp Concierge
                        </a>
                    </div>

                    <!-- Quick Spec Pills -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-slate-800/80 backdrop-blur-md border border-white/10 rounded-2xl p-3.5 text-center transition-all hover:border-amber-500/40">
                            <span class="text-white/50 block text-[10px] uppercase font-bold tracking-wider mb-1">Pickup Window</span>
                            <span class="font-bold text-amber-400 text-xs sm:text-sm flex items-center justify-center gap-1"><i class="bi bi-clock"></i>{{ $locationData['pickup_window'] }}</span>
                        </div>
                        <div class="bg-slate-800/80 backdrop-blur-md border border-white/10 rounded-2xl p-3.5 text-center transition-all hover:border-amber-500/40">
                            <span class="text-white/50 block text-[10px] uppercase font-bold tracking-wider mb-1">Return Time</span>
                            <span class="font-bold text-white text-xs sm:text-sm flex items-center justify-center gap-1"><i class="bi bi-moon-stars"></i>{{ $locationData['return_time'] }}</span>
                        </div>
                        <div class="bg-slate-800/80 backdrop-blur-md border border-white/10 rounded-2xl p-3.5 text-center transition-all hover:border-amber-500/40">
                            <span class="text-white/50 block text-[10px] uppercase font-bold tracking-wider mb-1">Transit Time</span>
                            <span class="font-bold text-white text-xs sm:text-sm flex items-center justify-center gap-1"><i class="bi bi-speedometer2"></i>40-50 Mins Direct</span>
                        </div>
                        <div class="bg-slate-800/80 backdrop-blur-md border border-white/10 rounded-2xl p-3.5 text-center transition-all hover:border-amber-500/40">
                            <span class="text-white/50 block text-[10px] uppercase font-bold tracking-wider mb-1">Pickup Fleet</span>
                            <span class="font-bold text-emerald-400 text-xs sm:text-sm flex items-center justify-center gap-1"><i class="bi bi-truck"></i>4x4 Land Cruiser</span>
                        </div>
                    </div>
                </div>

                <!-- Desktop Trust Card Overlay -->
                <div class="hidden lg:block lg:col-span-4">
                    <div class="bg-slate-900/85 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-2xl space-y-5">
                        <div class="flex items-center gap-3.5 pb-4 border-b border-white/10">
                            <div class="w-12 h-12 rounded-2xl bg-amber-500/15 text-primary flex items-center justify-center text-2xl shrink-0">
                                <i class="bi bi-patch-check-fill"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-base leading-tight">Doorstep Guaranteed</h4>
                                <span class="text-white/50 text-xs">Zero bus transfers or walking</span>
                            </div>
                        </div>

                        <ul class="space-y-3.5 text-xs text-slate-300 list-none p-0">
                            <li class="flex items-start gap-2.5">
                                <i class="bi bi-check2-circle text-emerald-400 text-base shrink-0 mt-0.5"></i>
                                <span><strong class="text-white">Hotel Lobby Pickup:</strong> Direct lobby pickup from all major hotels & residences across {{ $locationData['name'] }}.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <i class="bi bi-check2-circle text-emerald-400 text-base shrink-0 mt-0.5"></i>
                                <span><strong class="text-white">Live WhatsApp Updates:</strong> Driver name, vehicle plate, and live GPS location shared prior to arrival.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <i class="bi bi-check2-circle text-emerald-400 text-base shrink-0 mt-0.5"></i>
                                <span><strong class="text-white">Full Luggage Accommodation:</strong> Spacious 300-series Land Cruiser trunks suitable for airport transfers.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <i class="bi bi-check2-circle text-emerald-400 text-base shrink-0 mt-0.5"></i>
                                <span><strong class="text-white">Pay on Pickup:</strong> Reserve with 20% advance or full payment via Ziina, or cash on departure.</span>
                            </li>
                        </ul>

                        <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-dashed border-amber-500/40 text-center">
                            <span class="text-amber-400 font-bold text-xs flex items-center justify-center gap-1.5 mb-0.5">
                                <i class="bi bi-lightning-charge-fill"></i> Same-Day Pickup Available
                            </span>
                            <span class="text-white/60 text-[11px]">Book before 01:30 PM for today's evening safari</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── WHY BOOK FROM THIS LOCATION ───────────────────────────────────────── -->
    <section class="py-14 sm:py-20 bg-slate-900 border-b border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="inline-block bg-amber-400 text-slate-950 text-xs font-bold uppercase tracking-wider px-3.5 py-1 rounded-full mb-2.5">Seamless Logistics</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mb-2">Why Book Your Desert Safari from {{ $locationData['name'] }}?</h2>
                <p class="text-slate-400 text-sm sm:text-base leading-relaxed">Skip congested group bus meeting points. Our licensed safari fleet brings VIP desert transfers right to your doorstep.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
                <div class="bg-slate-800/80 border border-white/10 rounded-2xl p-6 transition-all hover:-translate-y-1 hover:border-amber-500/40 hover:shadow-xl">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/15 text-primary flex items-center justify-center text-xl mb-4">
                        <i class="bi bi-building"></i>
                    </div>
                    <h3 class="font-bold text-white text-base mb-1.5">Direct Door-to-Door</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">We pick you up directly from your hotel lobby, Airbnb apartment tower, or private villa anywhere in {{ $locationData['district'] }}.</p>
                </div>
                <div class="bg-slate-800/80 border border-white/10 rounded-2xl p-6 transition-all hover:-translate-y-1 hover:border-amber-500/40 hover:shadow-xl">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/15 text-primary flex items-center justify-center text-xl mb-4">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <h3 class="font-bold text-white text-base mb-1.5">WhatsApp Coordination</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">Receive a live WhatsApp dispatch message with your captain's name, phone number, and exact vehicle ETA 30 minutes before pickup.</p>
                </div>
                <div class="bg-slate-800/80 border border-white/10 rounded-2xl p-6 transition-all hover:-translate-y-1 hover:border-amber-500/40 hover:shadow-xl">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/15 text-primary flex items-center justify-center text-xl mb-4">
                        <i class="bi bi-luggage"></i>
                    </div>
                    <h3 class="font-bold text-white text-base mb-1.5">Luggage Friendly</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">Checking out today? Store your travel bags safely in our secure vehicle trunk during the safari. Drop-off at DXB airport also available.</p>
                </div>
                <div class="bg-slate-800/80 border border-white/10 rounded-2xl p-6 transition-all hover:-translate-y-1 hover:border-amber-500/40 hover:shadow-xl">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/15 text-primary flex items-center justify-center text-xl mb-4">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3 class="font-bold text-white text-base mb-1.5">DTCM Certified Fleet</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">Full roll-cage protected Toyota Land Cruisers with comprehensive passenger insurance and expert licensed desert drivers.</p>
                </div>
            </div>

            <!-- Landmarks Covered -->
            <div class="p-5 sm:p-6 rounded-2xl bg-slate-800/60 border border-white/10 flex flex-col md:flex-row items-start md:items-center gap-4">
                <div class="md:w-1/3">
                    <h4 class="font-bold text-white text-sm sm:text-base flex items-center gap-2 mb-0.5">
                        <i class="bi bi-pin-map text-amber-400"></i> Prime Landmarks Covered:
                    </h4>
                    <span class="text-slate-400 text-xs">Any hotel, resort, or tower across {{ $locationData['district'] }}</span>
                </div>
                <div class="md:w-2/3 flex flex-wrap gap-2">
                    @foreach($locationData['landmarks'] as $landmark)
                        <span class="inline-flex items-center gap-1.5 bg-white/5 border border-white/10 text-slate-200 text-xs px-3 py-1 rounded-full">
                            <i class="bi bi-check-circle-fill text-amber-400"></i> {{ $landmark }}
                        </span>
                    @endforeach
                    <span class="inline-flex items-center gap-1.5 bg-amber-500/15 border border-amber-500/30 text-amber-400 text-xs px-3 py-1 rounded-full font-semibold">
                        + All Private Airbnbs & Villas
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- ── SAFARI PACKAGES GRID ──────────────────────────────────────────────── -->
    <section class="py-14 sm:py-20" id="safari-packages">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-10">
                <div>
                    <span class="inline-block bg-amber-400 text-slate-950 text-xs font-bold uppercase tracking-wider px-3.5 py-1 rounded-full mb-2">Verified Packages</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mb-1">Desert Safaris Available from {{ $locationData['name'] }}</h2>
                    <p class="text-slate-400 text-sm">Choose your preferred experience level. All packages include direct doorstep transfers.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 bg-slate-900 border border-slate-700 text-slate-300 text-xs px-3.5 py-1.5 rounded-full font-medium">
                        <i class="bi bi-shield-lock text-emerald-400"></i> Best Price Guarantee
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($safariTours as $tour)
                    @php
                        $minPrice = $tour->tiers->pluck('pivot.price')->filter(function ($p) {
                            return $p !== null && (float)$p > 0;
                        })->min();
                        $minPrice = $minPrice ? (float)$minPrice : 99.0;
                        $firstTier = $tour->tiers->first();
                        $heroImg = $tour->hero_image ? asset('images/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $tour->hero_image)) : asset('images/desert-safari-poster.avif');
                    @endphp
                    <div class="bg-slate-900 border border-white/10 rounded-2xl overflow-hidden flex flex-col h-full transition-all duration-300 hover:-translate-y-1 hover:border-primary hover:shadow-2xl">
                        <!-- Image Wrap -->
                        <div class="relative h-56 overflow-hidden">
                            <img src="{{ $heroImg }}" width="400" height="224" alt="{{ $tour->name }} from {{ $locationData['name'] }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-500 hover:scale-105">
                            <div class="absolute top-3 left-3">
                                <span class="inline-flex items-center gap-1 bg-slate-950/80 text-amber-400 border border-amber-400/30 backdrop-blur-md rounded-full px-2.5 py-1 text-xs font-bold">
                                    <i class="bi bi-star-fill text-amber-400"></i> {{ $tour->rating ?: '4.9' }} ({{ $tour->review_count ?: '480+' }})
                                </span>
                            </div>
                            <div class="absolute top-3 right-3">
                                <span class="bg-primary text-white text-xs font-bold rounded-full px-3 py-1 shadow-md">
                                    {{ $locationData['name'] }} Pickup
                                </span>
                            </div>
                            <div class="absolute bottom-0 inset-x-0 p-3 bg-gradient-to-t from-slate-950 via-slate-950/70 to-transparent">
                                <span class="text-white/75 text-xs inline-flex items-center gap-1"><i class="bi bi-clock"></i> {{ $tour->duration ?: '6-7 Hours' }} &bull; Evening</span>
                            </div>
                        </div>

                        <div class="p-6 flex flex-col flex-grow">
                            <h3 class="font-bold text-white text-lg mb-2 leading-snug">{{ $tour->name }}</h3>
                            <p class="text-slate-400 text-xs leading-relaxed mb-4 flex-grow">
                                {{ Str::limit($tour->short_desc ?: 'Premium Dubai desert safari experience with dune bashing, sunset photography, live shows & BBQ dinner.', 120) }}
                            </p>

                            <ul class="space-y-2 border-y border-white/10 py-3 mb-4 text-xs text-slate-300 list-none p-0">
                                <li class="flex items-center gap-2">
                                    <i class="bi bi-check-circle-fill text-amber-400 shrink-0"></i>
                                    <span>Doorstep pickup from {{ $locationData['name'] }}</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="bi bi-check-circle-fill text-amber-400 shrink-0"></i>
                                    <span>Red dune bashing (Lahbab Desert)</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="bi bi-check-circle-fill text-amber-400 shrink-0"></i>
                                    <span>Live 5-star BBQ dinner & 3 cultural shows</span>
                                </li>
                            </ul>

                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">From</span>
                                    <span class="text-xl font-extrabold text-amber-400" data-aed="{{ $minPrice }}">AED {{ number_format($minPrice, 0) }}</span>
                                    <span class="text-slate-400 text-xs">/ person</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" class="border border-slate-700 hover:border-slate-500 text-slate-300 rounded-full px-2.5 py-1 text-xs whitespace-nowrap btn-toggle-compare cursor-pointer" data-tour-id="{{ $tour->id }}" onclick="event.preventDefault(); event.stopPropagation(); window.DunesCompare && window.DunesCompare.toggle(this);">
                                        <i class="bi bi-shuffle me-0.5"></i> <span class="compare-btn-text">Compare</span>
                                    </button>
                                    <a href="{{ route('tours.show', $tour->slug) }}" class="border border-white/20 hover:bg-white/10 text-white rounded-full px-3 py-1 text-xs font-semibold inline-flex items-center gap-1 transition-colors">
                                        Details <i class="bi bi-chevron-right text-[10px]"></i>
                                    </a>
                                </div>
                            </div>

                            <button type="button" class="btn-desert-animated w-full font-bold rounded-full py-3 text-white text-sm shadow-md btn-book-location cursor-pointer inline-flex items-center justify-center gap-1.5"
                                    data-tour-id="{{ $tour->id }}"
                                    data-tier-id="{{ $firstTier ? $firstTier->id : '' }}"
                                    data-location-name="{{ $locationData['name'] }}">
                                <i class="bi bi-calendar-check"></i> Book from {{ $locationData['name'] }}
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12 text-slate-400">
                        <p>No tours currently listed. Please contact our WhatsApp concierge for custom departures.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- ── ITINERARY & TIMELINE ──────────────────────────────────────────────── -->
    <section class="py-14 sm:py-20 bg-slate-900 border-y border-white/5">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="inline-block bg-amber-400 text-slate-950 text-xs font-bold uppercase tracking-wider px-3.5 py-1 rounded-full mb-2">Experience Schedule</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mb-2">Your Safari Day Timeline from {{ $locationData['name'] }}</h2>
                <p class="text-slate-400 text-sm">Here is what a typical evening desert safari looks like from your doorstep pickup to drop-off.</p>
            </div>

            <div class="space-y-4">
                <!-- Step 1 -->
                <div class="bg-slate-800/80 border border-white/10 rounded-2xl p-5 sm:p-6 flex items-start gap-4">
                    <div class="w-9 h-9 rounded-full bg-primary text-slate-950 font-black flex items-center justify-center text-sm shadow-md shrink-0">1</div>
                    <div>
                        <span class="inline-block bg-amber-400/20 text-amber-400 font-bold text-xs px-2.5 py-0.5 rounded-full mb-1.5">{{ $locationData['pickup_window'] }}</span>
                        <h3 class="font-bold text-white text-base mb-1">Doorstep Hotel / Residence Pickup</h3>
                        <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-0">Your safari captain pulls up in an air-conditioned 4x4 Toyota Land Cruiser at your hotel or tower entrance in {{ $locationData['district'] }}. Sit back and enjoy the scenic 45-minute highway drive toward the red dunes.</p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="bg-slate-800/80 border border-white/10 rounded-2xl p-5 sm:p-6 flex items-start gap-4">
                    <div class="w-9 h-9 rounded-full bg-primary text-slate-950 font-black flex items-center justify-center text-sm shadow-md shrink-0">2</div>
                    <div>
                        <span class="inline-block bg-amber-400/20 text-amber-400 font-bold text-xs px-2.5 py-0.5 rounded-full mb-1.5">03:45 PM - 04:15 PM</span>
                        <h3 class="font-bold text-white text-base mb-1">Desert Arrival & Quad Buggy Warm-up</h3>
                        <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-0">Arrive at our desert staging area in Lahbab. Tire pressure is adjusted for extreme sand drifting. Guests who added Quad Biking or Can-Am Maverick rentals can take their high-powered ride across the dunes.</p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="bg-slate-800/80 border border-white/10 rounded-2xl p-5 sm:p-6 flex items-start gap-4">
                    <div class="w-9 h-9 rounded-full bg-primary text-slate-950 font-black flex items-center justify-center text-sm shadow-md shrink-0">3</div>
                    <div>
                        <span class="inline-block bg-amber-400/20 text-amber-400 font-bold text-xs px-2.5 py-0.5 rounded-full mb-1.5">04:30 PM - 05:15 PM</span>
                        <h3 class="font-bold text-white text-base mb-1">High Red Dune Bashing Adventure</h3>
                        <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-0">Experience 40 to 45 minutes of adrenaline-pumping dune bashing across the highest red sand peaks of Dubai, driven by DTCM licensed desert masters.</p>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="bg-slate-800/80 border border-white/10 rounded-2xl p-5 sm:p-6 flex items-start gap-4">
                    <div class="w-9 h-9 rounded-full bg-primary text-slate-950 font-black flex items-center justify-center text-sm shadow-md shrink-0">4</div>
                    <div>
                        <span class="inline-block bg-amber-400/20 text-amber-400 font-bold text-xs px-2.5 py-0.5 rounded-full mb-1.5">05:15 PM - 05:45 PM</span>
                        <h3 class="font-bold text-white text-base mb-1">Golden Hour Sunset Stop & Sandboarding</h3>
                        <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-0">Stop at the summit of the tallest virgin dune for panoramic sunset photos overlooking the endless desert. Grab a custom sandboard and surf down the silky dunes.</p>
                    </div>
                </div>

                <!-- Step 5 -->
                <div class="bg-slate-800/80 border border-white/10 rounded-2xl p-5 sm:p-6 flex items-start gap-4">
                    <div class="w-9 h-9 rounded-full bg-primary text-slate-950 font-black flex items-center justify-center text-sm shadow-md shrink-0">5</div>
                    <div>
                        <span class="inline-block bg-amber-400/20 text-amber-400 font-bold text-xs px-2.5 py-0.5 rounded-full mb-1.5">06:00 PM - 08:45 PM</span>
                        <h3 class="font-bold text-white text-base mb-1">Bedouin Camp, 5-Star Live BBQ & Shows</h3>
                        <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-0">Arrive at our authentic desert fortress camp. Enjoy complimentary camel riding, Arabian coffee (Gahwa), fresh dates, henna body painting, and a sumptuous 5-star live BBQ buffet accompanied by live Fire, Tanoura, and Belly Dance performances.</p>
                    </div>
                </div>

                <!-- Step 6 -->
                <div class="bg-slate-800/80 border border-white/10 rounded-2xl p-5 sm:p-6 flex items-start gap-4">
                    <div class="w-9 h-9 rounded-full bg-primary text-slate-950 font-black flex items-center justify-center text-sm shadow-md shrink-0">6</div>
                    <div>
                        <span class="inline-block bg-amber-400/20 text-amber-400 font-bold text-xs px-2.5 py-0.5 rounded-full mb-1.5">{{ $locationData['return_time'] }}</span>
                        <h3 class="font-bold text-white text-base mb-1">Comfortable Drop-off Back at {{ $locationData['name'] }}</h3>
                        <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-0">Relax in climate-controlled comfort as your captain drives you directly back to your hotel or residence doorstep in {{ $locationData['name'] }}.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── REVIEWS STRIP ─────────────────────────────────────────────────────── -->
    @if(count($reviews) > 0)
    <section class="py-14 sm:py-20 bg-slate-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="inline-block bg-amber-400 text-slate-950 text-xs font-bold uppercase tracking-wider px-3.5 py-1 rounded-full mb-2">Guest Feedback</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mb-2">What Travelers Say About Our Doorstep Pickup</h2>
                <p class="text-slate-400 text-sm">Real experiences from travelers picked up from hotels and residences across Dubai.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($reviews->take(3) as $rev)
                    <div class="bg-slate-900 border border-white/10 rounded-2xl p-6 flex flex-col h-full">
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-amber-400 flex gap-1 text-sm">
                                @for($i = 0; $i < ($rev->rating ?: 5); $i++)
                                    <i class="bi bi-star-fill"></i>
                                @endfor
                            </div>
                            <span class="inline-flex items-center gap-1 bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 rounded-full px-2.5 py-0.5 text-xs font-semibold">
                                <i class="bi bi-patch-check-fill"></i> Verified Guest
                            </span>
                        </div>
                        <p class="text-slate-300 text-xs sm:text-sm leading-relaxed flex-grow mb-6">
                            "{{ Str::limit($rev->comment ?: 'Incredible experience! The driver arrived exactly on time at our hotel lobby. Dune bashing was thrilling and the live BBQ show was phenomenal.', 180) }}"
                        </p>
                        <div class="flex items-center gap-3 pt-4 border-t border-white/10">
                            <div class="w-9 h-9 rounded-full bg-amber-400 text-slate-950 font-bold flex items-center justify-center text-xs">
                                {{ strtoupper(substr($rev->author_name ?: 'G', 0, 1)) }}
                            </div>
                            <div>
                                <h4 class="text-white font-bold text-xs sm:text-sm mb-0">{{ $rev->author_name ?: 'Verified Traveler' }}</h4>
                                <span class="text-slate-400 text-[11px] block">{{ $rev->author_location ?: 'Dubai Tourist' }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- ── DISTRICT FAQ ACCORDION (Pure Alpine.js) ────────────────────────── -->
    <section class="py-14 sm:py-20 bg-slate-900 border-t border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="inline-block bg-amber-400 text-slate-950 text-xs font-bold uppercase tracking-wider px-3.5 py-1 rounded-full mb-2">Got Questions?</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mb-2">Frequently Asked Questions: {{ $locationData['name'] }} Pickup</h2>
                <p class="text-slate-400 text-sm">Everything you need to know about timings, vehicles, luggage, and booking.</p>
            </div>

            <div class="space-y-3" x-data="{ activeFaq: 0 }">
                @foreach($locationData['faqs'] as $index => $faq)
                    <div class="bg-slate-800/90 border border-white/10 rounded-2xl overflow-hidden transition-all duration-200">
                        <button type="button"
                                @click="activeFaq = (activeFaq === {{ $index }} ? null : {{ $index }})"
                                :aria-expanded="activeFaq === {{ $index }}"
                                class="w-full text-left px-5 sm:px-6 py-4 sm:py-5 flex items-center justify-between gap-4 font-bold transition-colors cursor-pointer"
                                :class="activeFaq === {{ $index }} ? 'text-amber-400 bg-amber-500/10' : 'text-white hover:text-amber-400'">
                            <span class="text-sm sm:text-base font-bold">{{ $faq['q'] }}</span>
                            <svg class="w-5 h-5 shrink-0 transition-transform duration-300"
                                 :class="activeFaq === {{ $index }} ? 'rotate-180 text-amber-400' : 'text-slate-400'"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="activeFaq === {{ $index }}"
                             x-collapse
                             class="px-5 sm:px-6 py-4 sm:py-5 text-slate-300 leading-relaxed text-xs sm:text-sm border-t border-white/5 bg-slate-800/50">
                            {!! $faq['a'] !!}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ── CROSS-HUB PROGRAMMATIC SEO INTERLINKING ──────────────────────────── -->
    <section class="py-14 sm:py-20 bg-slate-950 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="inline-block bg-amber-400 text-slate-950 text-xs font-bold uppercase tracking-wider px-3.5 py-1 rounded-full mb-2">Dubai & UAE Network</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mb-2">Other Dubai Safari Pickup Hubs</h2>
                <p class="text-slate-400 text-sm">Staying in a different part of the city? Explore our dedicated pickup schedules across Dubai & Sharjah.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($allLocations as $locKey => $loc)
                    @if($locKey !== $locationData['key'])
                        <a href="{{ url('/' . $loc['slug']) }}" class="bg-slate-900/60 border border-white/10 hover:border-amber-500/50 hover:bg-slate-900 rounded-2xl p-5 transition-all hover:-translate-y-1 block group">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-bold text-white text-sm sm:text-base group-hover:text-amber-400 transition-colors">{{ $loc['name'] }}</h3>
                                <i class="bi bi-arrow-right text-amber-400 transition-transform group-hover:translate-x-1"></i>
                            </div>
                            <p class="text-slate-400 text-xs leading-relaxed mb-3">{{ Str::limit($loc['district'], 55) }}</p>
                            <div class="text-amber-400 text-xs flex items-center gap-1.5 font-medium">
                                <i class="bi bi-clock"></i> Pickup: {{ $loc['pickup_window'] }}
                            </div>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <!-- ── BOTTOM HIGH-CONVERSION CTA BANNER ─────────────────────────────────── -->
    <section class="py-16 sm:py-20 bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 border-t border-amber-500/30">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-bold mb-4">
                <i class="bi bi-shield-check"></i> Free Cancellation Up to 24h Before Tour
            </div>
            <h2 class="text-2xl sm:text-4xl font-black text-white tracking-tight mb-4">
                Ready for the High Red Dunes from {{ $locationData['name'] }}?
            </h2>
            <p class="text-slate-300 text-sm sm:text-base max-w-2xl mx-auto leading-relaxed mb-8">
                Reserve your seats in 60 seconds with 20% advance or pay cash on pickup. Instant WhatsApp confirmation with full driver credentials.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-3.5">
                <button type="button" class="btn-desert-animated font-bold rounded-full px-8 py-4 text-white text-sm sm:text-base shadow-xl btn-book-location cursor-pointer inline-flex items-center justify-center gap-2"
                        data-tour-id="{{ $safariTours->first() ? $safariTours->first()->id : '' }}"
                        data-location-name="{{ $locationData['name'] }}">
                    <i class="bi bi-calendar2-check"></i> Book Desert Safari Now
                </button>
                <a href="https://wa.me/971502456056?text={{ urlencode('Hi Dunes Discovery Tourism, I want to book a Desert Safari from ' . $locationData['name'] . '. Please share details.') }}" target="_blank" rel="noopener" class="border border-white/20 hover:bg-white/10 text-white font-bold rounded-full px-6 py-4 text-sm sm:text-base transition-colors inline-flex items-center justify-center gap-2">
                    <i class="bi bi-whatsapp text-emerald-400"></i> Ask via WhatsApp
                </a>
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

            // 1. Primary path: Alpine modal store
            if (window.Alpine && window.Alpine.store && window.Alpine.store('modal')) {
                window.Alpine.store('modal').open('booking', { tourId: tourId, tierId: tierId });
                var locInput = document.getElementById('bookingLocation');
                if (locInput && locName) {
                    locInput.value = locName;
                    locInput.dispatchEvent(new Event('input', { bubbles: true }));
                    locInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            } else if (window.App && typeof window.App.openBooking === 'function') {
                window.App.openBooking(tourId, tierId);
                var locInput = document.getElementById('bookingLocation');
                if (locInput && locName) {
                    locInput.value = locName;
                    locInput.dispatchEvent(new Event('input', { bubbles: true }));
                    locInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        });
    });
});
</script>
@endpush
