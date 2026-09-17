@php
    try {
        $settings = \Illuminate\Support\Facades\Cache::remember('site_settings_cache', 86400, function() {
            return \App\Models\Setting::pluck('setting_value', 'setting_key')->all();
        });
    } catch (\Throwable $e) {
        $settings = null;
    }
    if (!is_array($settings)) {
        try { \Illuminate\Support\Facades\Cache::forget('site_settings_cache'); } catch (\Throwable $e) {}
        try { $settings = \App\Models\Setting::pluck('setting_value', 'setting_key')->all(); } catch (\Throwable $e) { $settings = []; }
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
    
    $cssFile = public_path('assets/css/app.min.css');
    $cacheVer = file_exists($cssFile) ? filemtime($cssFile) : ($settings['cache_version'] ?? time());

    $currentYear = date('Y');
    $pageTitle = $pageTitle ?? "Dunes Discovery Tourism | Dubai Desert Safari Tours ({$currentYear})";
    $pageDesc = $pageDesc ?? 'Book Dubai best desert safari tours from AED 99. Evening safari, city tours, dhow cruises with instant confirmation.';
    $pageKeys = $pageKeys ?? 'dubai desert safari,desert safari dubai,evening desert safari';
    $pageRobots = $pageRobots ?? 'index,follow';
    $canonical = $canonical ?? request()->url();
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
    <meta name="robots" content="{{ $pageRobots }}">
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

    <!-- Stylesheets -->
    <link href="{{ asset('assets/vendor/bootstrap/5.3.2/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="preload" href="{{ asset('assets/vendor/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css') }}"></noscript>
    <link href="{{ asset('assets/css/app.min.css') }}?v={{ $cacheVer }}" rel="stylesheet">
    <link rel="preload" href="{{ asset('assets/vendor/intl-tel-input/26.0.6/build/intlTelInput.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="{{ asset('assets/vendor/intl-tel-input/26.0.6/build/intlTelInput.css') }}"></noscript>

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
          "name": "Dunes Discovery Tourism",
          "legalName": "Dunes Discovery Tourism LLC",
          "url": "{{ url('/') }}",
          "logo": "{{ asset('images/logo.png') }}",
          "image": "{{ asset('images/desert-safari-poster.avif') }}",
          "telephone": "{{ $phone }}",
          "email": "{{ $email }}",
          "priceRange": "AED 99 - AED 1299",
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
          "aggregateRating": {
            "@@type": "AggregateRating",
            "ratingValue": "4.9",
            "reviewCount": "1247",
            "bestRating": "5",
            "worstRating": "1"
          },
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
          "privacyPolicy": "{{ route('privacy') }}"
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
    <style>
        .footer a:hover, .hover-white:hover { color: #fff !important; transition: color 0.15s ease; }
    </style>
    @stack('schema')
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- WCAG 2.2 SC 2.4.1 Skip to Main Content Link -->
    <a href="#main" class="visually-hidden-focusable btn btn-primary position-absolute top-0 start-0 z-3 p-3 m-2 shadow">Skip to main content</a>

    @if($googleActive && !empty($gtmId) && strpos($gtmId, 'G-') !== 0)
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif
    @if($metaActive && !empty($metaPixelId))
    <noscript><img height="1" width="1" style="display:none" alt="" src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1"/></noscript>
    @endif

    <!-- Header Navigation -->
    <header id="header" class="fixed-top transition-all" style="z-index: 1045;">
        @include('partials.top-banner')
        <nav class="navbar navbar-expand-lg navbar-light glass-nav sticky-sm-top sticky-md-top sticky-lg-top">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center p-0" href="{{ route('home') }}">
                    <img src="{{ asset('images/logo.png') }}" alt="Dunes Discovery Tourism" width="160" height="103" class="img-fluid logo-img" fetchpriority="high" style="height: auto; max-height: 46px; object-fit: contain;">
                </a>
                
                <!-- Mobile Review Badges & Buttons -->
                <div class="d-flex align-items-center gap-2 d-lg-none">
                    <div class="nav-review-circle" onclick="toggleReviewPopover(this, event)" id="taCircle" style="position: relative; cursor: pointer;">
                        <img src="{{ asset('images/tripadvisor-color-logo.svg') }}" alt="TripAdvisor">
                    </div>
                    <div class="nav-review-circle" onclick="toggleReviewPopover(this, event)" id="googleCircle" style="position: relative; cursor: pointer;">
                        <img src="{{ asset('images/Google-G.avif') }}" alt="Google">
                    </div>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}" class="btn-circle-whatsapp" aria-label="WhatsApp" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i></a>
                    <a href="#" class="btn-circle-desert-light" data-action="open-booking" aria-label="Book Now"><i class="bi bi-calendar-check"></i></a>
                    <button class="navbar-toggler border-0 shadow-none p-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mainOffcanvas" aria-controls="mainOffcanvas" aria-label="Menu">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>

                <!-- Navigation Sidebar for Mobile & Desktop -->
                <div class="offcanvas offcanvas-end border-0 rounded-start-4" tabindex="-1" id="mainOffcanvas" aria-labelledby="mainOffcanvasLabel" style="max-width: 75%;">
                    <div class="offcanvas-header border-bottom py-3">
                        <div class="offcanvas-title d-flex align-items-center" id="mainOffcanvasLabel">
                            <img src="{{ asset('images/logo.png') }}" alt="Dunes Discovery" width="140" height="90" class="img-fluid" style="height: auto; max-height: 40px; object-fit: contain;">
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-lg-none">
                                @include('partials.currency-switcher', ['switcherId' => 'mobileCurrencyDropdownBtn'])
                            </div>
                            <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                    </div>
                    <div class="offcanvas-body p-4 p-lg-0">
                        <ul class="navbar-nav mx-auto mb-4 mb-lg-0 gap-lg-1 text-nowrap">
                            <li class="nav-item">
                                <a class="nav-link px-3 px-lg-2 py-2 rounded-3 {{ request()->routeIs('home') ? 'active nav-active-pill' : '' }}" href="{{ route('home') }}">Home</a>
                            </li>
                            <li class="nav-item dropdown align-items-center flex-wrap w-100 w-lg-auto">
                                <div class="d-flex flex-wrap align-items-stretch w-100 rounded-3 position-relative {{ request()->routeIs('tours.*') ? 'nav-active-pill-wrapper' : '' }}">
                                    <a class="nav-link px-3 px-lg-2 py-2 flex-grow-1 rounded-start-3 {{ request()->routeIs('tours.*') ? 'active nav-active-pill' : '' }}" href="{{ route('tours.index') }}">Tours</a>
                                    <a class="nav-link px-3 py-2 dropdown-toggle dropdown-toggle-split d-flex align-items-center justify-content-center rounded-end-3 border-start border-primary border-opacity-10 {{ request()->routeIs('tours.*') ? 'active text-white' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 44px; min-height: 44px;">
                                        <span class="visually-hidden">Toggle Dropdown</span>
                                    </a>
                                    <ul class="dropdown-menu border-0 shadow-lg rounded-4 overflow-hidden p-0 mt-2 dropdown-animated-border">
                                        @foreach($allTours as $t)
                                        <li><a class="dropdown-item rounded-0 py-3 position-relative animated-divider-item" href="{{ route('tours.show', $t->slug) }}">{{ $t->name }}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3 px-lg-2 py-2 rounded-3 {{ request()->routeIs('about') ? 'active nav-active-pill' : '' }}" href="{{ route('about') }}">About Us</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3 px-lg-2 py-2 rounded-3 {{ request()->routeIs('blog.*') ? 'active nav-active-pill' : '' }}" href="{{ route('blog.index') }}">Blog</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3 px-lg-2 py-2 rounded-3 {{ request()->routeIs('faq') ? 'active nav-active-pill' : '' }}" href="{{ route('faq') }}">FAQ</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3 px-lg-2 py-2 rounded-3 {{ request()->routeIs('contact') ? 'active nav-active-pill' : '' }}" href="{{ route('contact') }}">Contact</a>
                            </li>
                        </ul>
                        <div class="d-flex flex-column flex-lg-row gap-3 align-items-stretch align-items-lg-center">
                            <div class="d-none d-lg-block">
                                @include('partials.currency-switcher', ['switcherId' => 'desktopCurrencyDropdownBtn'])
                            </div>
                            <a class="btn btn-whatsapp-animated rounded-pill px-4 fw-semibold d-inline-flex align-items-center justify-content-center gap-2" href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}" target="_blank" rel="noopener">
                                <i class="bi bi-whatsapp fs-5"></i>WhatsApp
                            </a>
                            <a class="btn btn-desert-animated rounded-pill px-4 py-2 fw-bold shadow-primary d-inline-flex align-items-center justify-content-center gap-2" href="#" data-action="open-booking" data-bs-dismiss="offcanvas">
                                <i class="bi bi-calendar-check fs-5"></i>Book Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
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
    <footer class="footer bg-dark text-white pt-5 pb-4">
        <div class="container">
            <div class="row g-4 mb-5">
                <div class="col-12 col-lg-3">
                    <img src="{{ asset('images/logo-white.png') }}" alt="Dunes Discovery Tourism" width="160" height="46" class="mb-3" style="height: auto; width: 160px; object-fit: contain;">
                    <p class="text-white-50 small pe-lg-2">Your trusted partner for unforgettable Dubai desert safari and city tour experiences since 2018. Licensed by Dubai Economy & Tourism (DET License: 1430583).</p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="https://instagram.com/dunesdiscoverytourism" target="_blank" rel="noopener" class="btn btn-outline-light btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" aria-label="Follow Dunes Discovery Tourism on Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="https://facebook.com/dunesdiscoverytourism" target="_blank" rel="noopener" class="btn btn-outline-light btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" aria-label="Follow Dunes Discovery Tourism on Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}" target="_blank" rel="noopener" class="btn btn-outline-light btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" aria-label="Chat with Dunes Discovery Tourism on WhatsApp"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <h3 class="h6 fw-bold text-uppercase mb-3 text-warning">Desert Safaris</h3>
                    <ul class="list-unstyled mb-0 d-grid gap-2">
                        <li><a href="{{ route('tours.show', 'evening-desert-safari-dubai') }}" class="text-white-50 text-decoration-none small hover-white">Evening Safari</a></li>
                        <li><a href="{{ route('tours.show', 'morning-desert-safari-dubai') }}" class="text-white-50 text-decoration-none small hover-white">Morning Safari</a></li>
                        <li><a href="{{ route('tours.show', 'overnight-desert-safari-dubai') }}" class="text-white-50 text-decoration-none small hover-white">Overnight Safari</a></li>
                        <li><a href="{{ route('tours.show', 'desert-safari-quad-biking-dubai') }}" class="text-white-50 text-decoration-none small hover-white">Quad Biking Safari</a></li>
                        <li><a href="{{ route('tours.show', 'luxury-vip-desert-safari-dubai') }}" class="text-white-50 text-decoration-none small hover-white">VIP Desert Safari</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h3 class="h6 fw-bold text-uppercase mb-3 text-warning">Tours & Cruises</h3>
                    <ul class="list-unstyled mb-0 d-grid gap-2">
                        <li><a href="{{ route('tours.show', 'dubai-city-tour') }}" class="text-white-50 text-decoration-none small hover-white">Dubai City Tour</a></li>
                        <li><a href="{{ route('tours.show', 'abu-dhabi-city-tour-from-dubai') }}" class="text-white-50 text-decoration-none small hover-white">Abu Dhabi Tour</a></li>
                        <li><a href="{{ route('tours.show', 'dhow-cruise-catamaran-cruise-dinner-dubai') }}" class="text-white-50 text-decoration-none small hover-white">Marina Cruise</a></li>
                        <li><a href="{{ route('rate-card') }}" class="text-white-50 text-decoration-none small hover-white"><i class="bi bi-file-earmark-pdf text-warning me-1"></i>Rate Card (PDF)</a></li>
                        <li><a href="{{ route('blog.index') }}" class="text-white-50 text-decoration-none small hover-white">Travel Guides & Blog</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h3 class="h6 fw-bold text-uppercase mb-3 text-warning">Trust & Policies</h3>
                    <ul class="list-unstyled mb-0 d-grid gap-2">
                        <li><a href="{{ route('terms') }}" class="text-white-50 text-decoration-none small hover-white">Terms & Conditions</a></li>
                        <li><a href="{{ route('privacy') }}" class="text-white-50 text-decoration-none small hover-white">Privacy Policy</a></li>
                        <li><a href="{{ route('cookies') }}" class="text-white-50 text-decoration-none small hover-white">Cookie Policy</a></li>
                        <li><a href="{{ route('cancellation') }}" class="text-white-50 text-decoration-none small hover-white">Cancellation & Refund</a></li>
                        <li><a href="{{ route('payment.security') }}" class="text-white-50 text-decoration-none small hover-white">Payment Security</a></li>
                        <li><a href="{{ route('safety.waiver') }}" class="text-white-50 text-decoration-none small hover-white">Safety & Waiver</a></li>
                        <li><a href="{{ route('ai.editorial') }}" class="text-white-50 text-decoration-none small hover-white">AI & Editorial Policy</a></li>
                        <li><a href="{{ route('responsible.tourism') }}" class="text-white-50 text-decoration-none small hover-white">Responsible Tourism</a></li>
                    </ul>
                </div>
                <div class="col-12 col-lg-3">
                    <h3 class="h6 fw-bold text-uppercase mb-3 text-warning">Contact & Help</h3>
                    <ul class="list-unstyled mb-0 d-grid gap-2">
                        <li><a href="tel:{{ preg_replace('/[^0-9+]/','',$phone) }}" class="text-white-50 text-decoration-none small d-flex align-items-center gap-2 hover-white"><i class="bi bi-telephone text-primary"></i>{{ $phone }}</a></li>
                        <li><a href="mailto:{{ $email }}" class="text-white-50 text-decoration-none small d-flex align-items-center gap-2 hover-white"><i class="bi bi-envelope text-primary"></i>{{ $email }}</a></li>
                        <li><a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}" target="_blank" rel="noopener" class="text-white-50 text-decoration-none small d-flex align-items-center gap-2 hover-white"><i class="bi bi-whatsapp text-primary"></i>24/7 WhatsApp Chat</a></li>
                        <li class="text-white-50 small d-flex align-items-center gap-2"><i class="bi bi-geo-alt text-primary"></i>{{ $settings['site_address'] ?? 'Dubai, United Arab Emirates' }}</li>
                    </ul>

                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <a href="{{ $settings['social_tripadvisor'] ?? 'https://www.tripadvisor.com/Attraction_Review-g295424-d29026644-Reviews-Dunes_Discovery-Dubai_Emirate_of_Dubai.html' }}" target="_blank" rel="noopener" class="footer-badge d-flex align-items-center text-decoration-none p-1 px-2 rounded-2 bg-black bg-opacity-40 border border-secondary border-opacity-25">
                            <img src="{{ asset('images/tripadvisor-logo-circle-owl-icon-black-green.png') }}" alt="TripAdvisor" class="footer-badge-logo" style="width:20px; height:20px; margin-right:6px;">
                            <div class="footer-badge-header">
                                <span class="footer-badge-score text-white fw-bold small">4.9</span>
                                <div class="footer-badge-stars text-warning small" style="font-size:9px;">
                                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                                </div>
                            </div>
                        </a>
                        <a href="{{ $settings['social_google'] ?? 'https://search.google.com/local/writereview?placeid=ChIJbWsIEIVEdEER4uHEhb2dbcQ' }}" target="_blank" rel="noopener" class="footer-badge d-flex align-items-center text-decoration-none p-1 px-2 rounded-2 bg-black bg-opacity-40 border border-secondary border-opacity-25">
                            <img src="https://www.gstatic.com/marketing-cms/assets/images/d5/dc/cfe9ce8b4425b410b49b7f2dd3f3/g.webp=s48-fcrop64=1,00000000ffffffff-rw" alt="Google" class="footer-badge-logo" style="width:20px; height:20px; margin-right:6px;">
                            <div class="footer-badge-header">
                                <span class="footer-badge-score text-white fw-bold small">5.0</span>
                                <div class="footer-badge-stars text-warning small" style="font-size:9px;">
                                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Bar with Legal Links and Payment Badges -->
            <div class="border-top border-secondary border-opacity-50 pt-4">
                <div class="d-flex flex-column flex-lg-row align-items-center justify-content-between gap-3 text-center text-lg-start">
                    <div>
                        <p class="text-white-50 small mb-1">&copy; {{ date('Y') }} {{ $settings['site_name'] ?? 'Dunes Discovery Tourism L.L.C.' }} {{ $settings['site_copyright'] ?? 'All rights reserved.' }} Department of Economy & Tourism License #{{ $settings['company_license_number'] ?? '1430583' }}.</p>
                        <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-2 small">
                            <a href="{{ route('terms') }}" class="text-white-50 text-decoration-none hover-white">Terms & Conditions</a>
                            <span class="text-white-50">&bull;</span>
                            <a href="{{ route('privacy') }}" class="text-white-50 text-decoration-none hover-white">Privacy Policy</a>
                            <span class="text-white-50">&bull;</span>
                            <a href="{{ route('cookies') }}" class="text-white-50 text-decoration-none hover-white">Cookie Policy</a>
                            <span class="text-white-50">&bull;</span>
                            <a href="{{ route('cancellation') }}" class="text-white-50 text-decoration-none hover-white">100% Refund Policy</a>
                            <span class="text-white-50">&bull;</span>
                            <a href="{{ route('payment.security') }}" class="text-white-50 text-decoration-none hover-white">Payment Security</a>
                            <span class="text-white-50">&bull;</span>
                            <a href="{{ route('safety.waiver') }}" class="text-white-50 text-decoration-none hover-white">Safety Waiver</a>
                            <span class="text-white-50">&bull;</span>
                            <a href="{{ route('ai.editorial') }}" class="text-white-50 text-decoration-none hover-white">AI Policy</a>
                            <span class="text-white-50">&bull;</span>
                            <a href="{{ route('responsible.tourism') }}" class="text-white-50 text-decoration-none hover-white">Sustainability</a>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center gap-2 footer-trust-icons bg-black bg-opacity-30 px-3 py-2 rounded-pill border border-secondary border-opacity-25 flex-wrap">
                        <span class="text-white-50 small me-1"><i class="bi bi-shield-lock-fill text-success me-1"></i>Secure Checkout:</span>
                        <img src="{{ asset('images/visa-card.svg') }}" alt="Visa" width="32" height="20" style="height: 18px; width: auto; object-fit: contain;">
                        <img src="{{ asset('images/mastercard.svg') }}" alt="Mastercard" width="28" height="20" style="height: 18px; width: auto; object-fit: contain;">
                        <img src="{{ asset('images/americanexpress.svg') }}" alt="American Express" width="28" height="20" style="height: 18px; width: auto; object-fit: contain;">
                        <img src="{{ asset('images/applepay.svg') }}" alt="Apple Pay" width="32" height="20" style="height: 18px; width: auto; object-fit: contain;">
                        <img src="{{ asset('images/googlepay.svg') }}" alt="Google Pay" width="32" height="20" style="height: 18px; width: auto; object-fit: contain;">
                        <img src="{{ asset('images/ziina-icon.png') }}" alt="Ziina Payment Gateway" width="18" height="18" style="filter: invert(1); opacity: 0.85;">
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Global TripAdvisor and Google Reviews Popover JS -->
    <script>
    const reviewData = {
        'taCircle': {
            title: 'TripAdvisor',
            logo: 'https://static.tacdn.com/img2/brand_refresh_2025/logos/wordmark.svg',
            score: '4.9',
            url: 'https://www.tripadvisor.com/Attraction_Review-g295424-d29026644-Reviews-Dunes_Discovery-Dubai_Emirate_of_Dubai.html',
            btnText: 'Read Reviews'
        },
        'googleCircle': {
            title: 'Google Reviews',
            logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/2f/Google_2015_logo.svg/250px-Google_2015_logo.svg.png',
            score: '5.0',
            url: 'https://www.google.com/maps/search/?api=1&query=Google&query_place_id=ChIJbWsIEIVEdEER4uHEhb2dbcQ',
            btnText: 'See Reviews'
        }
    };

    function toggleReviewPopover(element, event) {
        try {
            event.stopPropagation();
            event.preventDefault();

            const existing = document.querySelector('.global-popover-overlay');
            const currentTriggerId = existing ? existing.dataset.triggerId : null;

            if (existing) {
                existing.remove();
            }

            document.querySelectorAll('.nav-review-circle').forEach(el => el.classList.remove('active'));

            if (currentTriggerId === element.id) {
                return;
            }

            const data = reviewData[element.id];
            if (!data) return;

            const popover = document.createElement('div');
            popover.className = 'global-popover-overlay';
            popover.dataset.triggerId = element.id;

            popover.innerHTML = `
                <div class="review-popover-header">
                    <img src="${data.logo}" alt="${data.title}" class="review-popover-logo">
                </div>
                <div class="review-popover-score">${data.score}</div>
                <div class="review-popover-stars">
                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <a href="${data.url}" target="_blank" class="review-popover-btn">
                    ${data.btnText} <i class="bi bi-arrow-right"></i>
                </a>
            `;

            popover.style.position = 'fixed';
            popover.style.zIndex = '2147483647';
            popover.style.display = 'block';
            popover.style.visibility = 'visible';
            popover.style.opacity = '1';
            popover.style.backgroundColor = 'white';
            popover.style.transform = 'none';

            const btnRect = element.getBoundingClientRect();
            const popoverWidth = 220;
            const margin = 10;

            let left = btnRect.left + (btnRect.width / 2) - (popoverWidth / 2);

            if (left < margin) left = margin;
            else if (left + popoverWidth > window.innerWidth - margin) left = window.innerWidth - margin - popoverWidth;

            const top = btnRect.bottom + 12;

            popover.style.top = `${top}px`;
            popover.style.left = `${left}px`;

            const arrowX = (btnRect.left + btnRect.width / 2) - left;
            popover.style.setProperty('--arrow-left', `${arrowX}px`);

            document.body.appendChild(popover);
            element.classList.add('active');

        } catch (e) {
            console.error('Popover Error:', e);
        }
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.global-popover-overlay') && !e.target.closest('.nav-review-circle')) {
            const existing = document.querySelector('.global-popover-overlay');
            if (existing) existing.remove();
            document.querySelectorAll('.nav-review-circle').forEach(el => el.classList.remove('active'));
        }
    });
    </script>

    @include('partials.booking-modal')
    @include('partials.welcome-offer-modal')
    @include('partials.social-proof')
    @include('partials.comparison-drawer')

    <!-- Global Toast Container for App.toast notifications -->
    <div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;" aria-live="polite" aria-atomic="true"></div>

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
    <script src="{{ asset('assets/vendor/bootstrap/5.3.2/js/bootstrap.bundle.min.js') }}" defer></script>
    <script src="{{ asset('assets/vendor/intl-tel-input/26.0.6/build/intlTelInput.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/app.min.js') }}?v={{ file_exists(public_path('assets/js/app.min.js')) ? filemtime(public_path('assets/js/app.min.js')) : time() }}" defer></script>

    @stack('scripts')
</body>
</html>
