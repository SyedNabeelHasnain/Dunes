@extends('layouts.app')

@section('content')
@push('preloads')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@graph": [
    {
      "@type": "AboutPage",
      "@id": "{{ route('about') }}#webpage",
      "url": "{{ route('about') }}",
      "name": "About Dunes Discovery Tourism Dubai",
      "description": "About Dunes Discovery Tourism LLC - Licensed Dubai Destination Management Company offering premium Desert Safaris, Dune Buggy rentals, and luxury tours since 2018.",
      "breadcrumb": {
        "@type": "BreadcrumbList",
        "itemListElement": [
          {
            "@type": "ListItem",
            "position": 1,
            "name": "Home",
            "item": "{{ route('home') }}"
          },
          {
            "@type": "ListItem",
            "position": 2,
            "name": "About Us",
            "item": "{{ route('about') }}"
          }
        ]
      },
      "mainEntity": {
        "@type": "TravelAgency",
        "@id": "{{ route('home') }}#organization",
        "name": "{{ $settings['site_name'] ?? 'Dunes Discovery Tourism LLC' }}",
        "url": "{{ route('home') }}",
        "logo": "{{ asset('images/logo.png') }}",
        "telephone": "{{ $settings['site_phone'] ?? '+971 50 245 6056' }}",
        "email": "{{ $settings['site_email'] ?? 'info@dunesdiscoverytourism.com' }}",
        "foundingDate": "2018",
        "address": {
          "@type": "PostalAddress",
          "streetAddress": "{{ $settings['site_address'] ?? 'Al Fahidi, Bur Dubai, Dubai' }}",
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
<section class="py-10 bg-slate-950 text-white relative overflow-hidden" style="margin-top: calc(-1 * var(--header-h, 72px));">
    <div class="absolute inset-0 w-full h-full bg-[radial-gradient(ellipse_at_15%_20%,rgba(246,144,68,0.18)_0%,transparent_60%)]"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 pt-16">
        <nav aria-label="breadcrumb">
            <ol class="flex items-center gap-2 text-xs sm:text-sm text-white/70 mb-4">
                <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a></li>
                <li><span class="text-white/40">/</span></li>
                <li class="text-white font-semibold" aria-current="page">About Us</li>
            </ol>
        </nav>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <div class="flex flex-wrap gap-2 mb-3">
                    <span class="glass rounded-full px-3.5 py-1 text-xs inline-flex items-center gap-1.5">
                        <i class="bi bi-calendar3 text-primary"></i>Trusted Since 2018
                    </span>
                    <span class="bg-emerald-600/90 rounded-full px-3.5 py-1 text-xs font-semibold text-white inline-flex items-center gap-1.5">
                        <i class="bi bi-patch-check-fill text-emerald-200"></i>DTCM Licensed Operator
                    </span>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-2">About Dunes Discovery Tourism</h1>
                <p class="text-sm sm:text-base text-white/80 max-w-2xl leading-relaxed">Your licensed destination management partner for authentic Arabian desert expeditions & luxury Dubai tours.</p>
            </div>
            <div class="hidden lg:block shrink-0">
                <span class="bg-primary/20 text-primary border border-primary/40 px-4 py-2 rounded-full font-bold text-sm inline-flex items-center gap-1.5">
                    <i class="bi bi-star-fill text-amber-400"></i>10,000+ Happy Guests
                </span>
            </div>
        </div>
    </div>
</section>

<!-- Regulatory E-E-A-T & Trust Bar -->
<section class="bg-slate-50 py-3.5 border-b border-slate-200 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <div class="flex items-center gap-2.5">
                <i class="bi bi-patch-check-fill text-primary text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">DTCM Licensed Operator</div>
                    <div class="text-slate-500 text-[11px]">Dubai Tourism Authority</div>
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
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">100% Halal Food</div>
                    <div class="text-slate-500 text-[11px]">Veg, Non-Veg & Jain</div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <i class="bi bi-truck text-cyan-600 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">25+ Luxury 4x4 Fleet</div>
                    <div class="text-slate-500 text-[11px]">Land Cruiser 300 Series</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Story Section -->
<section class="py-12 sm:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-6">
                <div class="relative">
                    <img src="{{ asset('images/dubai-desert-safari-tour-dune-discovery-tourism.avif') }}" alt="Dunes Discovery Story" class="w-full rounded-2xl shadow-xl object-cover" onerror="this.src='https://placehold.co/800x600/F58F43/white?text=Our+Story'">
                    <div class="hidden sm:block absolute -bottom-6 -right-6 bg-primary text-white p-5 rounded-2xl shadow-2xl">
                        <div class="text-2xl font-black mb-0.5">6+ Years</div>
                        <span class="text-xs text-white/80 block">Of Excellence</span>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-6">
                <div class="lg:pl-6">
                    <div class="inline-flex items-center gap-2 mb-3">
                        <span class="bg-primary/10 text-primary px-3.5 py-1 rounded-full text-xs font-bold">OUR STORY</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-4 tracking-tight leading-tight">Crafting Unforgettable Arabian Experiences</h2>
                    <p class="text-slate-700 text-base sm:text-lg mb-4 leading-relaxed font-medium">Founded in 2018, Dunes Discovery Tourism has grown from a small family operation to one of Dubai's most trusted tour companies.</p>
                    <p class="text-slate-600 text-sm sm:text-base mb-6 leading-relaxed">Our passion for the Arabian desert and commitment to exceptional service has made us the preferred choice for travelers from around the world. We specialize in authentic desert safari experiences that blend adventure, culture, and comfort.</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 text-sm">
                                <i class="bi bi-check-lg font-bold"></i>
                            </div>
                            <span class="font-bold text-slate-800 text-xs sm:text-sm">Licensed & Insured</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 text-sm">
                                <i class="bi bi-check-lg font-bold"></i>
                            </div>
                            <span class="font-bold text-slate-800 text-xs sm:text-sm">Modern Fleet</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Grid -->
<section class="py-12 sm:py-20 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10 sm:mb-14">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-3 tracking-tight">Why Choose Us</h2>
            <p class="text-slate-600 text-base sm:text-lg max-w-xl mx-auto leading-relaxed">We go the extra mile to ensure your Dubai adventure is nothing short of perfect.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-lg transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xl mb-4">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Licensed & Insured</h3>
                <p class="text-slate-600 text-sm leading-relaxed">Fully licensed by Dubai Tourism with comprehensive insurance for all guests, ensuring your peace of mind.</p>
            </div>
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-lg transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xl mb-4">
                    <i class="bi bi-people"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Expert Team</h3>
                <p class="text-slate-600 text-sm leading-relaxed">Professional drivers with years of desert experience and multilingual guides who know the dunes like no one else.</p>
            </div>
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-lg transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xl mb-4">
                    <i class="bi bi-trophy"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Award Winning</h3>
                <p class="text-slate-600 text-sm leading-relaxed">Consistently rated 4.8+ stars across Google, TripAdvisor, and other platforms for our service quality.</p>
            </div>
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-lg transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xl mb-4">
                    <i class="bi bi-truck"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Modern Fleet</h3>
                <p class="text-slate-600 text-sm leading-relaxed">Well-maintained Toyota Land Cruisers equipped with the latest safety features and powerful air conditioning.</p>
            </div>
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-lg transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xl mb-4">
                    <i class="bi bi-heart"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Guest First</h3>
                <p class="text-slate-600 text-sm leading-relaxed">Personalized service with attention to dietary needs, celebrations, and special requests to make it yours.</p>
            </div>
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-lg transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xl mb-4">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Best Value</h3>
                <p class="text-slate-600 text-sm leading-relaxed">Competitive prices with no hidden fees. What you see is what you pay. Quality adventure at the right price.</p>
            </div>
        </div>
    </div>
</section>

<!-- Stats Bar Section -->
<section class="py-8 bg-slate-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-3 lg:grid-cols-6 gap-4 text-center">
            <div class="p-2">
                <i class="bi bi-trophy text-primary text-2xl mb-1.5 block"></i>
                <div class="text-xl font-bold text-white mb-0.5">#1</div>
                <div class="text-slate-300 text-xs">Desert Safari</div>
            </div>
            <div class="p-2">
                <i class="bi bi-shield-check text-primary text-2xl mb-1.5 block"></i>
                <div class="text-xl font-bold text-white mb-0.5">100%</div>
                <div class="text-slate-300 text-xs">Secure Pay</div>
            </div>
            <div class="p-2">
                <i class="bi bi-clock-history text-primary text-2xl mb-1.5 block"></i>
                <div class="text-xl font-bold text-white mb-0.5">Fast</div>
                <div class="text-slate-300 text-xs">Booking</div>
            </div>
            <div class="p-2">
                <i class="bi bi-truck text-primary text-2xl mb-1.5 block"></i>
                <div class="text-xl font-bold text-white mb-0.5">25+</div>
                <div class="text-slate-300 text-xs">Vehicles</div>
            </div>
            <div class="p-2">
                <i class="bi bi-geo-alt text-primary text-2xl mb-1.5 block"></i>
                <div class="text-xl font-bold text-white mb-0.5">Local</div>
                <div class="text-slate-300 text-xs">Expert Guides</div>
            </div>
            <div class="p-2">
                <i class="bi bi-star text-primary text-2xl mb-1.5 block"></i>
                <div class="text-xl font-bold text-white mb-0.5">Best</div>
                <div class="text-slate-300 text-xs">Price Promise</div>
            </div>
        </div>
    </div>
</section>
@endsection
