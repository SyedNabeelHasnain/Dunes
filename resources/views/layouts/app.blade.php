@php
    if (!isset($settings) || !is_array($settings)) {
        $settings = [];
    }

    try {
        $allTours = \Illuminate\Support\Facades\Cache::remember('site_tours_header_cache', 3600, function() {
            return \App\Models\Tour::where('status', 'active')->orderBy('priority', 'asc')->get();
        });
    } catch (\Throwable $e) {
        $allTours = null;
    }
    if (!is_iterable($allTours) || $allTours instanceof \__PHP_Incomplete_Class) {
        try { \Illuminate\Support\Facades\Cache::forget('site_tours_header_cache'); } catch (\Throwable $e) {}
        try { $allTours = \App\Models\Tour::where('status', 'active')->orderBy('priority', 'asc')->get(); } catch (\Throwable $e) { $allTours = collect(); }
    }
    
    $googleActive = isset($settings['google_active']) && $settings['google_active'] === '1';
    $gtmId = $settings['google_gtm_id'] ?? '';
    $ga4Id = $settings['google_ga4_id'] ?? '';
    $adsId = $settings['google_ads_id'] ?? 'AW-17859624049';
    $conversionLabel = $settings['google_conversion_label'] ?? 'eR3SCLimtvobEPH4kMRC';
    $conversionSendTo = (!empty($adsId) && !empty($conversionLabel)) ? "{$adsId}/{$conversionLabel}" : '';
    $gVerify = $settings['google_site_verification'] ?? '';
    
    $metaActive = isset($settings['meta_active']) && $settings['meta_active'] === '1';
    $metaCapi = isset($settings['meta_capi_enabled']) && $settings['meta_capi_enabled'] === '1';
    $metaPixelId = $settings['meta_pixel_id'] ?? '';
    
    $rcSiteKey = $settings['recaptcha_site_key'] ?? '6LdOpWEsAAAAAOhnKd4WFFtJMrShRtMV33HdZBP6';
    $waPhone = $settings['site_whatsapp'] ?? '971502456056';
    $phone = $settings['site_phone'] ?? '+971 50 245 6056';
    $email = $settings['site_email'] ?? 'info@dunesdiscoverytourism.com';
    $conciergePromoActive = isset($settings['concierge_promo_active']) && $settings['concierge_promo_active'] === '1';
    $conciergePromoDiscount = $settings['concierge_promo_discount'] ?? '5';

    $currentYear = date('Y');
    $pageTitle = $pageTitle ?? "Dunes Discovery Tourism | Dubai Desert Safari Tours ({$currentYear})";
    $pageDesc = $pageDesc ?? 'Book Dubai best desert safari tours from AED 99. Evening safari, city tours, dhow cruises with instant confirmation.';
    $pageKeys = $pageKeys ?? 'dubai desert safari,desert safari dubai,evening desert safari';
    $pageRobots = $pageRobots ?? 'index,follow';
    $canonical = $canonical ?? (request()->is('/') ? rtrim(url('/'), '/') . '/' : request()->url());
    $ogImage = $ogImage ?? asset('images/desert-safari-poster.avif');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    @if(!empty($adsId))
    <!-- Google tag (gtag.js) {{ $adsId }} (Sitewide First-Party Conversion Tag) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $adsId }}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', '{{ $adsId }}', {
        'allow_enhanced_conversions': true
      });
      @if($googleActive && !empty($ga4Id))
      gtag('config', '{{ $ga4Id }}');
      @endif

      // Helper function to delay opening a URL until a gtag event is sent.
      function gtagSendEvent(url, params) {
        var navigated = false;
        var callback = function () {
          if (!navigated && typeof url === 'string' && url) {
            navigated = true;
            window.location = url;
          }
        };

        // Fallback safety timeout (1.5s) in case beacon takes too long
        setTimeout(callback, 1500);

        var eventData = Object.assign({
          'event_callback': callback,
          'event_timeout': 2000,
          @if(!empty($conversionSendTo))
          'send_to': '{{ $conversionSendTo }}'
          @endif
        }, params || {});

        if (typeof gtag === 'function') {
          gtag('event', 'conversion', eventData);
          gtag('event', 'conversion_event_submit_lead_form', eventData);
        } else {
          callback();
        }
        return false;
      }
      window.gtagSendEvent = gtagSendEvent;

      function gtagReportConversion(url, params) {
        return gtagSendEvent(url, params);
      }
      window.gtagReportConversion = gtagReportConversion;
    </script>
    @else
    <script>
      window.gtagSendEvent = function(url) { if (url) window.location = url; return false; };
      window.gtagReportConversion = window.gtagSendEvent;
    </script>
    @endif

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>window.CSRF_TOKEN = "{{ csrf_token() }}";</script>
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDesc }}">
    <meta name="keywords" content="{{ $pageKeys }}">
    <meta name="robots" content="{{ $pageRobots ?? 'index, follow, max-image-preview:large' }}">
    <meta name="author" content="Dunes Discovery Tourism">
    <link rel="canonical" href="{{ $canonical }}">
    <link rel="alternate" hreflang="en" href="{{ $canonical }}">
    <link rel="alternate" hreflang="x-default" href="{{ $canonical }}">
    
    <link rel="preconnect" href="https://www.googletagmanager.com">
    <link rel="preconnect" href="https://connect.facebook.net">
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">
    <link rel="dns-prefetch" href="https://connect.facebook.net">

    <!-- OpenGraph Metadata -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDesc }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="Dunes Discovery Tourism">
    <meta property="og:locale" content="en_US">
    
    <!-- Twitter Card Metadata -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDesc }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    
    <meta name="geo.region" content="AE-DU">
    <meta name="geo.placename" content="Dubai">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#F69044">
    <meta name="msapplication-TileColor" content="#F69044">

    @if(!empty($gVerify))
    <meta name="google-site-verification" content="{{ $gVerify }}">
    @endif
    
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-192.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- Stylesheets: Tailwind v4 Token System & Vendor Assets via Vite -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/intl-tel-input/26.0.6/build/intlTelInput.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

    <script>
      var initThirdPartyTracking = function() {
          if (window._trackingInitialized) return;
          window._trackingInitialized = true;

          @if($metaActive && !empty($metaPixelId))
          !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
          n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
          n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
          t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
          document,'script','https://connect.facebook.net/en_US/fbevents.js');
          if(window.fbq){ fbq('init', '{{ $metaPixelId }}'); fbq('track', 'PageView'); }
          @endif
      };

      if ('requestIdleCallback' in window) {
          requestIdleCallback(function() { setTimeout(initThirdPartyTracking, 2000); });
      } else {
          setTimeout(initThirdPartyTracking, 3500);
      }
      ['touchstart', 'scroll', 'pointermove'].forEach(function(ev) {
          window.addEventListener(ev, initThirdPartyTracking, { once: true, passive: true });
      });
    </script>

    @stack('preloads')

    <!-- Global Unified @graph JSON-LD Entity Knowledge Graph & Speakable Schema -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@graph": [
        {
          "@@type": "TravelAgency",
          "@@id": "{{ url('/') }}#organization",
          "name": "{{ $settings['site_name'] ?? 'Dunes Discovery Tourism LLC' }}",
          "legalName": "Dunes Discovery Tourism LLC",
          "alternateName": ["Dunes Discovery", "Dunes Discovery Tourism", "Dunes Discovery Dubai"],
          "description": "{{ $settings['site_description'] ?? 'Licensed Dubai Destination Management Company offering premium Desert Safaris, 1000cc Dune Buggy Rentals, Quad Biking, Dhow Cruise Dinners, and Abu Dhabi City Tours.' }}",
          "url": "{{ url('/') }}",
          "logo": "{{ asset('images/logo.png') }}",
          "image": "{{ asset('images/desert-safari-poster.avif') }}",
          "telephone": "{{ $phone }}",
          "email": "{{ $email }}",
          "priceRange": "AED 79 - AED 1500",
          "currenciesAccepted": "AED, USD, EUR, GBP",
          "paymentAccepted": "Cash, Credit Card, Debit Card, Ziina",
          "identifier": {
            "@@type": "PropertyValue",
            "propertyID": "DET Tourism License",
            "value": "{{ $settings['site_det_license'] ?? '1430583' }}"
          },
          "hasCredential": {
            "@@type": "EducationalOccupationalCredential",
            "name": "Dubai Department of Economy and Tourism (DET) Tourism Operator License",
            "credentialCategory": "license",
            "recognizedBy": {
              "@@type": "GovernmentOrganization",
              "name": "Dubai Department of Economy and Tourism",
              "url": "https://www.dubaitourism.gov.ae"
            }
          },
          "knowsAbout": [
            "https://en.wikipedia.org/wiki/Dubai",
            "https://en.wikipedia.org/wiki/Desert_safari",
            "https://en.wikipedia.org/wiki/Dune_bashing",
            "Dubai Desert Safari",
            "Dune Buggy Rental Dubai",
            "Quad Biking Dubai",
            "Lahbab Red Dunes",
            "Dubai Tourism"
          ],
          "areaServed": [
            {
              "@@type": "AdministrativeArea",
              "name": "Dubai",
              "sameAs": "https://www.wikidata.org/wiki/Q612"
            },
            {
              "@@type": "Country",
              "name": "United Arab Emirates",
              "sameAs": "https://www.wikidata.org/wiki/Q878"
            }
          ],
          @if(request()->is('/'))
          "aggregateRating": {
            "@@type": "AggregateRating",
            "ratingValue": "4.9",
            "reviewCount": "2847",
            "bestRating": "5",
            "worstRating": "1"
          },
          @endif
          "address": {
            "@@type": "PostalAddress",
            "addressLocality": "Dubai",
            "addressRegion": "Dubai",
            "addressCountry": "AE"
          },
          "geo": {
            "@@type": "GeoCoordinates",
            "latitude": 25.2048,
            "longitude": 55.2708
          },
          "contactPoint": [
            {
              "@@type": "ContactPoint",
              "telephone": "{{ $phone }}",
              "contactType": "customer service",
              "areaServed": "AE",
              "availableLanguage": ["en", "ar"]
            },
            {
              "@@type": "ContactPoint",
              "telephone": "+971502456056",
              "contactType": "reservations",
              "areaServed": "AE",
              "availableLanguage": ["en", "ar"]
            }
          ],
          "openingHoursSpecification": {
            "@@type": "OpeningHoursSpecification",
            "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
            "opens": "00:00",
            "closes": "23:59"
          },
          "sameAs": [
            "https://www.facebook.com/dunesdiscoverytourism",
            "https://www.instagram.com/dunesdiscoverytourism",
            "https://www.tripadvisor.com"
          ],
          "termsOfService": "{{ route('terms') }}",
          "privacyPolicy": "{{ route('privacy') }}",
          "hasMerchantReturnPolicy": {
            "@@type": "MerchantReturnPolicy",
            "applicableCountry": "AE",
            "returnPolicyCategory": "https://schema.org/MerchantReturnFiniteReturnWindow",
            "merchantReturnDays": 1,
            "returnMethod": "https://schema.org/ReturnInStore",
            "returnFees": "https://schema.org/FreeReturn"
          }
        },
        {
          "@@type": "WebSite",
          "@@id": "{{ url('/') }}#website",
          "url": "{{ url('/') }}",
          "name": "Dunes Discovery Tourism",
          "publisher": {
            "@@id": "{{ url('/') }}#organization"
          },
          "potentialAction": {
            "@@type": "SearchAction",
            "target": {
              "@@type": "EntryPoint",
              "urlTemplate": "{{ url('/tours') }}?search={search_term_string}"
            },
            "query-input": "required name=search_term_string"
          },
          "inLanguage": "en"
        },
        {
          "@@type": "WebPage",
          "@@id": "{{ $canonical }}#webpage",
          "url": "{{ $canonical }}",
          "name": {!! json_encode($pageTitle) !!},
          "description": {!! json_encode($pageDesc) !!},
          "isPartOf": {
            "@@id": "{{ url('/') }}#website"
          },
          "speakable": {
            "@@type": "SpeakableSpecification",
            "cssSelector": ["#header", "#footer", "h1", ".hero-title", ".page-summary"]
          }
        }
      ]
    }
    </script>
    @stack('schema')
</head>
<body class="flex flex-col min-h-screen bg-slate-50 text-slate-900 antialiased font-sans selection:bg-orange-500 selection:text-white" x-data="{}" :class="{ 'overflow-hidden': $store.mobileNav.open || $store.modal.active }">

    <!-- WCAG 2.2 SC 2.4.1 Skip to Main Content Link -->
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2.5 focus:bg-primary focus:text-white focus:rounded-xl focus:shadow-lg focus:font-bold">Skip to main content</a>

    @if($googleActive && !empty($gtmId) && strpos($gtmId, 'G-') !== 0)
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif
    @if($metaActive && !empty($metaPixelId))
    <noscript><img height="1" width="1" style="display:none" alt="" src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1"/></noscript>
    @endif

    <!-- Header Navigation -->
    <header id="header" class="fixed top-0 inset-x-0 transition-all z-40">
        @include('partials.top-banner')
        <nav class="glass-nav border-b border-slate-200/80 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 sm:h-18">
                    <!-- Brand Logo -->
                    <a class="flex items-center flex-shrink-0" href="{{ route('home') }}">
                        <img src="{{ asset('images/logo.png') }}" alt="Dunes Discovery Tourism" width="160" height="103" class="h-9 sm:h-10 w-auto object-contain" fetchpriority="high">
                    </a>

                    <!-- Desktop Nav Links (Hidden on mobile/tablet) -->
                    <ul class="hidden lg:flex items-center gap-1 xl:gap-2 text-sm font-semibold text-slate-700">
                        <li>
                            <a class="px-3 py-2 rounded-xl transition-all {{ request()->routeIs('home') ? 'bg-slate-900 text-white font-bold shadow-xs' : 'hover:text-primary hover:bg-orange-50/60' }}" href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                            <div class="inline-flex items-center rounded-xl {{ request()->routeIs('tours.*') ? 'bg-slate-900 text-white font-bold shadow-xs' : '' }}">
                                <a class="px-3 py-2 rounded-l-xl transition-all {{ request()->routeIs('tours.*') ? 'text-white' : 'hover:text-primary hover:bg-orange-50/60' }}" href="{{ route('tours.index') }}">Tours</a>
                                <button type="button" @click="open = !open" :aria-expanded="open" class="px-1.5 py-2 rounded-r-xl transition-all hover:opacity-80 cursor-pointer" aria-label="Toggle Tours Submenu">
                                    <i class="bi bi-chevron-down text-[10px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                                </button>
                            </div>
                            <!-- Tours Dropdown Menu -->
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                 class="absolute left-0 mt-2 w-72 rounded-2xl bg-white p-2 shadow-xl border border-slate-100 z-50 focus:outline-none"
                                 style="display: none;">
                                <a class="flex items-center gap-2 px-3 py-2.5 rounded-xl font-bold text-primary hover:bg-orange-50 transition-colors text-xs" href="{{ route('tours.customizer') }}">
                                    <i class="bi bi-sliders text-amber-500"></i>
                                    <span>Build Your Own Safari</span>
                                </a>
                                <div class="my-1 border-t border-slate-100"></div>
                                <div class="max-h-72 overflow-y-auto space-y-0.5">
                                    @foreach($allTours as $t)
                                    <a class="block px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-orange-50 hover:text-primary transition-colors line-clamp-1" href="{{ route('tours.show', $t->slug) }}">
                                        {{ $t->name }}
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                        </li>
                        <li>
                            <a class="px-3 py-2 rounded-xl transition-all {{ request()->routeIs('about') ? 'bg-slate-900 text-white font-bold shadow-xs' : 'hover:text-primary hover:bg-orange-50/60' }}" href="{{ route('about') }}">About Us</a>
                        </li>
                        <li>
                            <a class="px-3 py-2 rounded-xl transition-all {{ request()->routeIs('blog.*') ? 'bg-slate-900 text-white font-bold shadow-xs' : 'hover:text-primary hover:bg-orange-50/60' }}" href="{{ route('blog.index') }}">Blog</a>
                        </li>
                        <li>
                            <a class="px-3 py-2 rounded-xl transition-all {{ request()->routeIs('faq') ? 'bg-slate-900 text-white font-bold shadow-xs' : 'hover:text-primary hover:bg-orange-50/60' }}" href="{{ route('faq') }}">FAQ</a>
                        </li>
                        <li>
                            <a class="px-3 py-2 rounded-xl transition-all {{ request()->routeIs('contact') ? 'bg-slate-900 text-white font-bold shadow-xs' : 'hover:text-primary hover:bg-orange-50/60' }}" href="{{ route('contact') }}">Contact</a>
                        </li>
                    </ul>

                    <!-- Desktop Right Actions (Hidden on mobile/tablet) -->
                    <div class="hidden lg:flex items-center gap-2 xl:gap-3">
                        <button type="button" class="hidden 2xl:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold text-primary border border-primary/30 hover:border-primary hover:bg-orange-50/50 transition-all cursor-pointer shadow-2xs" @click="$store.modal.open('safari-matcher')" aria-label="Safari Match Concierge">
                            <i class="bi bi-compass text-amber-500"></i>
                            <span>Safari Concierge</span>
                            @if($conciergePromoActive)
                            <span class="px-1.5 py-0.5 rounded-full bg-amber-400 text-slate-950 text-[9px] font-black">{{ $conciergePromoDiscount }}% OFF</span>
                            @endif
                        </button>
                        <button type="button" class="w-9 h-9 rounded-full bg-white border border-slate-200/90 shadow-2xs flex items-center justify-center text-slate-600 hover:text-primary hover:border-primary/50 transition-all cursor-pointer" @click="$store.modal.open('search')" title="Search Dubai tours" aria-label="Search Dubai tours">
                            <i class="bi bi-search text-xs"></i>
                        </button>
                        @include('partials.currency-switcher', ['switcherId' => 'desktopCurrencyDropdownBtn'])
                        <a class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full font-bold text-xs bg-emerald-500 hover:bg-emerald-600 text-white shadow-xs hover:shadow-sm transition-all cursor-pointer" href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp"></i><span>WhatsApp</span>
                        </a>
                        <a class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full font-extrabold text-xs text-white bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 shadow-sm hover:shadow-md transition-all cursor-pointer" href="#" data-action="open-booking">
                            <i class="bi bi-calendar-check"></i><span>Book Now</span>
                        </a>
                    </div>

                    <!-- Mobile App Bar Actions (Thumb-Friendly, uncluttered) -->
                    <div class="flex items-center gap-1.5 sm:gap-2 lg:hidden">
                        <!-- Review Circle Popover Triggers -->
                        <div class="nav-review-circle w-8 h-8 rounded-full bg-white shadow-xs flex items-center justify-center cursor-pointer relative" onclick="toggleReviewPopover(this, event)" id="taCircle" title="TripAdvisor Reviews">
                            <img src="{{ asset('images/tripadvisor-color-logo.svg') }}" alt="TripAdvisor" class="w-5 h-5 object-contain">
                        </div>
                        <div class="nav-review-circle w-8 h-8 rounded-full bg-white shadow-xs flex items-center justify-center cursor-pointer relative" onclick="toggleReviewPopover(this, event)" id="googleCircle" title="Google Reviews">
                            <img src="{{ asset('images/Google-G.avif') }}" alt="Google" class="w-5 h-5 object-contain">
                        </div>

                        <!-- Quick Search Button -->
                        <button type="button" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200 flex items-center justify-center transition-colors cursor-pointer text-xs" @click="$store.modal.open('search')" aria-label="Search Dubai Tours">
                            <i class="bi bi-search"></i>
                        </button>

                        <!-- WhatsApp Direct Button -->
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 hover:bg-emerald-100 flex items-center justify-center transition-colors cursor-pointer text-xs" aria-label="WhatsApp" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp"></i>
                        </a>

                        <!-- Compact Book Now Button -->
                        <a href="#" class="px-2.5 sm:px-3.5 py-1.5 rounded-full bg-gradient-to-r from-orange-500 to-amber-500 text-white font-extrabold text-[11px] sm:text-xs shadow-xs flex items-center gap-1 cursor-pointer" data-action="open-booking" aria-label="Book Now">
                            <i class="bi bi-calendar-check"></i>
                            <span class="hidden sm:inline">Book</span>
                        </a>

                        <!-- Hamburger Drawer Trigger -->
                        <button class="p-1.5 sm:p-2 rounded-xl text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer focus:outline-none" type="button" @click="$store.mobileNav.toggle()" aria-label="Menu">
                            <i class="bi bi-list text-xl sm:text-2xl"></i>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Alpine-Powered Mobile Offcanvas Drawer -->
        <div x-show="$store.mobileNav.open" 
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 z-50 backdrop-blur-xs lg:hidden"
             @click="$store.mobileNav.close()"
             style="display: none;"></div>

        <div x-show="$store.mobileNav.open"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="fixed inset-y-0 right-0 max-w-xs sm:max-w-sm w-full bg-white z-50 shadow-2xl p-5 flex flex-col justify-between overflow-y-auto lg:hidden"
             style="display: none;"
             id="mainOffcanvas">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <img src="{{ asset('images/logo.png') }}" alt="Dunes Discovery" width="140" height="90" class="h-9 w-auto object-contain">
                    <div class="flex items-center gap-2">
                        @include('partials.currency-switcher', ['switcherId' => 'mobileCurrencyDropdownBtn'])
                        <button type="button" class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 flex items-center justify-center transition-colors cursor-pointer" @click="$store.mobileNav.close()" aria-label="Close">
                            <i class="bi bi-x-lg text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- Search Input in Drawer -->
                <form action="{{ route('tours.search') }}" method="GET" class="relative mb-3">
                    <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="q" class="w-full rounded-full pl-9 pr-4 py-2 bg-slate-100 text-slate-800 text-xs font-semibold border-0 focus:ring-2 focus:ring-primary focus:outline-none placeholder:text-slate-400" placeholder="Search safaris, buggies, VIP..." required>
                </form>

                <!-- Interactive Features CTAs -->
                <div class="space-y-2 mb-4">
                    <button type="button" class="w-full rounded-2xl p-3 flex items-center justify-between text-left border border-amber-400/30 shadow-xs cursor-pointer" style="background: linear-gradient(135deg, #1E293B, #0F172A);" @click="$store.mobileNav.close(); $store.modal.open('safari-matcher');">
                        <div class="flex items-center gap-2.5">
                            <span class="text-amber-400 text-lg"><i class="bi bi-compass"></i></span>
                            <div>
                                <div class="font-bold text-white text-xs leading-none">Safari Match Concierge</div>
                                <small class="text-white/60 text-[10px]">Find ideal tour in 30 seconds</small>
                            </div>
                        </div>
                        @if($conciergePromoActive)
                        <span class="px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 font-black text-[9px]">{{ $conciergePromoDiscount }}% OFF</span>
                        @endif
                    </button>
                    <a href="{{ route('tours.customizer') }}" class="w-full rounded-2xl p-2.5 flex items-center justify-between text-left border border-slate-200 bg-slate-50 hover:bg-orange-50 text-slate-800 font-bold text-xs transition-colors" @click="$store.mobileNav.close()">
                        <span class="flex items-center gap-2">
                            <i class="bi bi-sliders text-primary"></i>
                            <span>Build Your Own Safari</span>
                        </span>
                        <i class="bi bi-chevron-right text-slate-400 text-[10px]"></i>
                    </a>
                </div>

                <!-- Navigation Links List -->
                <nav class="space-y-1">
                    <a class="block px-3 py-2.5 rounded-xl text-sm font-bold transition-all {{ request()->routeIs('home') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}" href="{{ route('home') }}" @click="$store.mobileNav.close()">Home</a>
                    <a class="block px-3 py-2.5 rounded-xl text-sm font-bold transition-all {{ request()->routeIs('tours.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}" href="{{ route('tours.index') }}" @click="$store.mobileNav.close()">All Tours</a>
                    <a class="block px-3 py-2.5 rounded-xl text-sm font-bold transition-all {{ request()->routeIs('about') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}" href="{{ route('about') }}" @click="$store.mobileNav.close()">About Us</a>
                    <a class="block px-3 py-2.5 rounded-xl text-sm font-bold transition-all {{ request()->routeIs('blog.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}" href="{{ route('blog.index') }}" @click="$store.mobileNav.close()">Travel Blog</a>
                    <a class="block px-3 py-2.5 rounded-xl text-sm font-bold transition-all {{ request()->routeIs('faq') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}" href="{{ route('faq') }}" @click="$store.mobileNav.close()">FAQ</a>
                    <a class="block px-3 py-2.5 rounded-xl text-sm font-bold transition-all {{ request()->routeIs('contact') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}" href="{{ route('contact') }}" @click="$store.mobileNav.close()">Contact</a>
                </nav>
            </div>

            <!-- Drawer Bottom Direct Contact -->
            <div class="pt-4 border-t border-slate-100 space-y-2">
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}" target="_blank" rel="noopener" class="w-full py-2.5 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-xs transition-colors">
                    <i class="bi bi-whatsapp"></i><span>Chat on WhatsApp</span>
                </a>
                <button type="button" class="w-full py-2.5 rounded-full bg-primary hover:bg-primary-dark text-white font-extrabold text-xs flex items-center justify-center gap-2 shadow-xs transition-colors cursor-pointer" @click="$store.mobileNav.close(); $store.modal.open('booking');">
                    <i class="bi bi-calendar-check"></i><span>Book Online Now</span>
                </button>
            </div>
        </div>
    </header>

    <script>
        function syncHeaderHeight() {
            var h = document.getElementById('header');
            if (h) {
                var height = h.offsetHeight || 72;
                document.documentElement.style.setProperty('--header-h', height + 'px');
            }
        }
        syncHeaderHeight();
        window.addEventListener('resize', syncHeaderHeight);
        window.addEventListener('DOMContentLoaded', syncHeaderHeight);
        window.addEventListener('load', syncHeaderHeight);
        if (window.ResizeObserver) {
            var headerObserver = new ResizeObserver(function() { syncHeaderHeight(); });
            var headerEl = document.getElementById('header');
            if (headerEl) headerObserver.observe(headerEl);
        }
    </script>

    <!-- Main Content -->
    <main id="main" tabindex="-1">
        @yield('content')
    </main>

    <!-- Newsletter Subscription Section -->
    @include('partials.newsletter-subscription')

    <!-- Footer Section -->
    <footer class="bg-slate-950 text-slate-300 pt-14 pb-8 border-t border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-8 lg:gap-10 mb-12">
                <!-- Col 1: Brand & Bio -->
                <div class="sm:col-span-2 lg:col-span-4">
                    <img src="{{ asset('images/logo-white.png') }}" alt="Dunes Discovery Tourism" width="160" height="46" class="h-9 w-auto object-contain mb-4">
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed max-w-sm mb-4">
                        Your trusted partner for unforgettable Dubai desert safari and city tour experiences since 2018. Licensed by Dubai Economy & Tourism (DET License: 1430583).
                    </p>
                    <div class="flex items-center gap-3">
                        <a href="https://instagram.com/dunesdiscoverytourism" target="_blank" rel="noopener" class="w-9 h-9 rounded-full border border-slate-700 bg-slate-900/60 hover:bg-slate-800 hover:border-slate-500 text-slate-300 hover:text-white flex items-center justify-center transition-colors text-sm" aria-label="Follow Dunes Discovery Tourism on Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="https://facebook.com/dunesdiscoverytourism" target="_blank" rel="noopener" class="w-9 h-9 rounded-full border border-slate-700 bg-slate-900/60 hover:bg-slate-800 hover:border-slate-500 text-slate-300 hover:text-white flex items-center justify-center transition-colors text-sm" aria-label="Follow Dunes Discovery Tourism on Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-full border border-slate-700 bg-slate-900/60 hover:bg-emerald-600 hover:border-emerald-500 text-slate-300 hover:text-white flex items-center justify-center transition-colors text-sm" aria-label="Chat with Dunes Discovery Tourism on WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                </div>

                <!-- Col 2: Desert Safaris -->
                <div class="col-span-1 lg:col-span-2">
                    <h3 class="text-xs font-black uppercase tracking-wider text-amber-400 mb-3.5">Desert Safaris</h3>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><a href="{{ route('tours.show', 'evening-desert-safari-dubai') }}" class="text-slate-400 hover:text-white transition-colors">Evening Safari</a></li>
                        <li><a href="{{ route('tours.show', 'morning-desert-safari-dubai') }}" class="text-slate-400 hover:text-white transition-colors">Morning Safari</a></li>
                        <li><a href="{{ route('tours.show', 'overnight-desert-safari-dubai') }}" class="text-slate-400 hover:text-white transition-colors">Overnight Safari</a></li>
                        <li><a href="{{ route('tours.show', 'desert-safari-quad-biking-dubai') }}" class="text-slate-400 hover:text-white transition-colors">Quad Biking Safari</a></li>
                        <li><a href="{{ route('tours.show', 'luxury-vip-desert-safari-dubai') }}" class="text-slate-400 hover:text-white transition-colors">VIP Desert Safari</a></li>
                    </ul>
                </div>

                <!-- Col 3: Tours & Cruises -->
                <div class="col-span-1 lg:col-span-2">
                    <h3 class="text-xs font-black uppercase tracking-wider text-amber-400 mb-3.5">Tours & Cruises</h3>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><a href="{{ route('tours.show', 'dubai-city-tour') }}" class="text-slate-400 hover:text-white transition-colors">Dubai City Tour</a></li>
                        <li><a href="{{ route('tours.show', 'abu-dhabi-city-tour-from-dubai') }}" class="text-slate-400 hover:text-white transition-colors">Abu Dhabi Tour</a></li>
                        <li><a href="{{ route('tours.show', 'dhow-cruise-catamaran-cruise-dinner-dubai') }}" class="text-slate-400 hover:text-white transition-colors">Marina Cruise</a></li>
                        <li><a href="{{ route('rate-card') }}" class="text-slate-400 hover:text-white transition-colors inline-flex items-center gap-1.5"><i class="bi bi-file-earmark-pdf text-amber-400"></i>Rate Card (PDF)</a></li>
                        <li><a href="{{ route('blog.index') }}" class="text-slate-400 hover:text-white transition-colors">Travel Guides & Blog</a></li>
                    </ul>
                </div>

                <!-- Col 4: Trust & Policies -->
                <div class="col-span-1 lg:col-span-2">
                    <h3 class="text-xs font-black uppercase tracking-wider text-amber-400 mb-3.5">Trust & Policies</h3>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><a href="{{ route('terms') }}" class="text-slate-400 hover:text-white transition-colors">Terms & Conditions</a></li>
                        <li><a href="{{ route('privacy') }}" class="text-slate-400 hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="{{ route('cookies') }}" class="text-slate-400 hover:text-white transition-colors">Cookie Policy</a></li>
                        <li><a href="{{ route('cancellation') }}" class="text-slate-400 hover:text-white transition-colors">Cancellation & Refund</a></li>
                        <li><a href="{{ route('payment.security') }}" class="text-slate-400 hover:text-white transition-colors">Payment Security</a></li>
                        <li><a href="{{ route('safety.waiver') }}" class="text-slate-400 hover:text-white transition-colors">Safety & Waiver</a></li>
                        <li><a href="{{ route('ai.editorial') }}" class="text-slate-400 hover:text-white transition-colors">AI & Editorial Policy</a></li>
                        <li><a href="{{ route('responsible.tourism') }}" class="text-slate-400 hover:text-white transition-colors">Responsible Tourism</a></li>
                    </ul>
                </div>

                <!-- Col 5: Contact & Help -->
                <div class="sm:col-span-2 lg:col-span-2">
                    <h3 class="text-xs font-black uppercase tracking-wider text-amber-400 mb-3.5">Contact & Help</h3>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><a href="tel:{{ preg_replace('/[^0-9+]/','',$phone) }}" class="text-slate-400 hover:text-white transition-colors inline-flex items-center gap-2"><i class="bi bi-telephone text-primary"></i><span>{{ $phone }}</span></a></li>
                        <li><a href="mailto:{{ $email }}" class="text-slate-400 hover:text-white transition-colors inline-flex items-center gap-2"><i class="bi bi-envelope text-primary"></i><span class="break-all">{{ $email }}</span></a></li>
                        <li><a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}" target="_blank" rel="noopener" class="text-slate-400 hover:text-white transition-colors inline-flex items-center gap-2"><i class="bi bi-whatsapp text-emerald-400"></i><span>24/7 WhatsApp</span></a></li>
                        <li class="text-slate-400 inline-flex items-start gap-2"><i class="bi bi-geo-alt text-primary shrink-0 mt-0.5"></i><span>{{ $settings['site_address'] ?? 'Dubai, United Arab Emirates' }}</span></li>
                    </ul>

                    <div class="flex flex-wrap gap-2 mt-4">
                        <a href="{{ $settings['social_tripadvisor'] ?? 'https://www.tripadvisor.com/Attraction_Review-g295424-d29026644-Reviews-Dunes_Discovery-Dubai_Emirate_of_Dubai.html' }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 p-1.5 px-2.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition-colors">
                            <img src="{{ asset('images/tripadvisor-logo-circle-owl-icon-black-green.png') }}" alt="TripAdvisor" class="w-5 h-5 object-contain">
                            <div>
                                <div class="text-white font-bold text-xs leading-none">4.9</div>
                                <div class="text-amber-400 text-[9px] flex gap-0.5 mt-0.5">
                                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                                </div>
                            </div>
                        </a>
                        <a href="{{ $settings['social_google'] ?? 'https://search.google.com/local/writereview?placeid=ChIJbWsIEIVEdEER4uHEhb2dbcQ' }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 p-1.5 px-2.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition-colors">
                            <img src="https://www.gstatic.com/marketing-cms/assets/images/d5/dc/cfe9ce8b4425b410b49b7f2dd3f3/g.webp=s48-fcrop64=1,00000000ffffffff-rw" alt="Google" class="w-5 h-5 object-contain">
                            <div>
                                <div class="text-white font-bold text-xs leading-none">5.0</div>
                                <div class="text-amber-400 text-[9px] flex gap-0.5 mt-0.5">
                                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Bar with Legal Links and Payment Badges -->
            <div class="border-t border-slate-800/80 pt-6">
                <div class="flex flex-col lg:flex-row items-center justify-between gap-4 text-center lg:text-left">
                    <div>
                        <p class="text-slate-500 text-xs mb-1">&copy; {{ date('Y') }} {{ $settings['site_name'] ?? 'Dunes Discovery Tourism L.L.C.' }} {{ $settings['site_copyright'] ?? 'All rights reserved.' }} Department of Economy & Tourism License #{{ $settings['company_license_number'] ?? '1430583' }}.</p>
                        <div class="flex flex-wrap justify-center lg:justify-start items-center gap-x-2.5 gap-y-1 text-xs text-slate-400">
                            <a href="{{ route('terms') }}" class="hover:text-white transition-colors">Terms & Conditions</a>
                            <span class="text-slate-600">&bull;</span>
                            <a href="{{ route('privacy') }}" class="hover:text-white transition-colors">Privacy Policy</a>
                            <span class="text-slate-600">&bull;</span>
                            <a href="{{ route('cookies') }}" class="hover:text-white transition-colors">Cookie Policy</a>
                            <span class="text-slate-600">&bull;</span>
                            <a href="{{ route('cancellation') }}" class="hover:text-white transition-colors">100% Refund Policy</a>
                            <span class="text-slate-600">&bull;</span>
                            <a href="{{ route('payment.security') }}" class="hover:text-white transition-colors">Payment Security</a>
                            <span class="text-slate-600">&bull;</span>
                            <a href="{{ route('safety.waiver') }}" class="hover:text-white transition-colors">Safety Waiver</a>
                            <span class="text-slate-600">&bull;</span>
                            <a href="{{ route('ai.editorial') }}" class="hover:text-white transition-colors">AI Policy</a>
                            <span class="text-slate-600">&bull;</span>
                            <a href="{{ route('responsible.tourism') }}" class="hover:text-white transition-colors">Sustainability</a>
                        </div>
                    </div>
                    <div class="inline-flex items-center gap-2 bg-slate-900 border border-slate-800 px-4 py-2 rounded-full flex-wrap justify-center">
                        <span class="text-slate-400 text-xs inline-flex items-center gap-1"><i class="bi bi-shield-lock-fill text-emerald-400"></i>Secure Checkout:</span>
                        <img src="{{ asset('images/visa-card.svg') }}" alt="Visa" width="32" height="20" class="h-4 w-auto object-contain">
                        <img src="{{ asset('images/mastercard.svg') }}" alt="Mastercard" width="28" height="20" class="h-4 w-auto object-contain">
                        <img src="{{ asset('images/americanexpress.svg') }}" alt="American Express" width="28" height="20" class="h-4 w-auto object-contain">
                        <img src="{{ asset('images/applepay.svg') }}" alt="Apple Pay" width="32" height="20" class="h-4 w-auto object-contain">
                        <img src="{{ asset('images/googlepay.svg') }}" alt="Google Pay" width="32" height="20" class="h-4 w-auto object-contain">
                        <img src="{{ asset('images/ziina-icon.png') }}" alt="Ziina Payment Gateway" width="18" height="18" class="h-4 w-auto object-contain brightness-200">
                    </div>
                </div>
            </div>
        </div>
    </footer>

    @include('partials.booking-modal')
    @include('partials.welcome-offer-modal')
    @include('partials.social-proof')
    @include('partials.comparison-drawer')
    @include('partials.search-modal')
    @include('partials.safari-matcher-modal')
    @if(($settings['exit_intent_promo_active'] ?? '0') === '1')
        @include('partials.exit-intent-modal')
    @endif
    @include('partials.custom-safari-modal')

    <!-- Global Toast Container for App.toast notifications -->
    <div id="toastContainer" class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 p-3 pointer-events-none" aria-live="polite" aria-atomic="true"></div>

    <script>
        window.DunesRates = {
            'AED': 1.0,
            'USD': {{ (float) ($settings['currency_rate_usd'] ?? 0.2723) }},
            'EUR': {{ (float) ($settings['currency_rate_eur'] ?? 0.2510) }},
            'GBP': {{ (float) ($settings['currency_rate_gbp'] ?? 0.2150) }},
            'SAR': {{ (float) ($settings['currency_rate_sar'] ?? 1.0210) }},
            'INR': {{ (float) ($settings['currency_rate_inr'] ?? 22.85) }}
        };
    </script>

    <!-- Scripts -->
    <script src="{{ asset('assets/vendor/intl-tel-input/26.0.6/build/intlTelInput.min.js') }}" defer></script>

    @stack('scripts')
</body>
</html>
