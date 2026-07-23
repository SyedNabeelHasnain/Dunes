@php
    $settings = \Illuminate\Support\Facades\Cache::remember('site_settings_cache', 86400, function() {
        return \App\Models\Setting::pluck('setting_value', 'setting_key')->all();
    });
    if (!is_array($settings)) {
        \Illuminate\Support\Facades\Cache::forget('site_settings_cache');
        $settings = \App\Models\Setting::pluck('setting_value', 'setting_key')->all();
    }

    $allTours = \Illuminate\Support\Facades\Cache::remember('site_tours_header_cache', 3600, function() {
        return \App\Models\Tour::where('status', 'active')->orderBy('priority', 'asc')->get();
    });
    if (!is_iterable($allTours) || $allTours instanceof \__PHP_Incomplete_Class) {
        \Illuminate\Support\Facades\Cache::forget('site_tours_header_cache');
        $allTours = \App\Models\Tour::where('status', 'active')->orderBy('priority', 'asc')->get();
    }
    
    $googleActive = isset($settings['google_active']) && $settings['google_active'] === '1';
    $gtmId = $settings['google_gtm_id'] ?? '';
    $ga4Id = $settings['google_ga4_id'] ?? '';
    $adsId = $settings['google_ads_id'] ?? '';
    $gVerify = $settings['google_site_verification'] ?? '';
    
    $metaActive = isset($settings['meta_active']) && $settings['meta_active'] === '1';
    $metaCapi = isset($settings['meta_capi_enabled']) && $settings['meta_capi_enabled'] === '1';
    $metaPixelId = $settings['meta_pixel_id'] ?? '';
    
    $rcSiteKey = $settings['recaptcha_site_key'] ?? '6LdOpWEsAAAAAOhnKd4WFFtJMrShRtMV33HdZBP6';
    $waPhone = $settings['site_whatsapp'] ?? '971502456056';
    $phone = $settings['site_phone'] ?? '+971 50 245 6056';
    $email = $settings['site_email'] ?? 'info@dunesdiscoverytourism.com';
    $cacheVer = $settings['cache_version'] ?? '1';

    $pageTitle = $pageTitle ?? 'Dunes Discovery Tourism | Dubai Desert Safari Tours';
    $pageDesc = $pageDesc ?? 'Book Dubai best desert safari tours from AED 99. Evening safari, city tours, dhow cruises with instant confirmation.';
    $pageKeys = $pageKeys ?? 'dubai desert safari,desert safari dubai,evening desert safari';
    $pageRobots = $pageRobots ?? 'index,follow';
    $canonical = $canonical ?? request()->url();
    $ogImage = $ogImage ?? asset('images/desert-safari-poster.avif');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDesc }}">
    <meta name="keywords" content="{{ $pageKeys }}">
    <meta name="robots" content="{{ $pageRobots }}">
    <meta name="author" content="Dunes Discovery Tourism">
    <link rel="canonical" href="{{ $canonical }}">
    
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
    <link href="{{ asset('assets/css/app.css') }}?v={{ $cacheVer }}" rel="stylesheet">
    <link rel="preload" href="{{ asset('assets/vendor/intl-tel-input/26.0.6/build/intlTelInput.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="{{ asset('assets/vendor/intl-tel-input/26.0.6/build/intlTelInput.css') }}"></noscript>

    <!-- Google Analytics & GTM -->
    @if($googleActive && !empty($ga4Id))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4Id }}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '{{ $ga4Id }}');
    </script>
    @endif

    @if($googleActive && !empty($gtmId))
        @if(strpos($gtmId, 'G-') !== 0 && $gtmId !== $ga4Id)
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          var loadGtm=function(){var s=document.createElement('script');s.async=true;s.src='https://www.googletagmanager.com/gtm.js?id={{ $gtmId }}';document.head.appendChild(s);};
          var loadGa=function(){var s=document.createElement('script');s.async=true;s.src='https://www.googletagmanager.com/gtag/js?id={{ $ga4Id }}';document.head.appendChild(s);};
          var start=function(){gtag('js', new Date());@if(!empty($ga4Id))gtag('config','{{ $ga4Id }}');@endif};
          var schedule=function(fn){if('requestIdleCallback'in window){requestIdleCallback(fn,{timeout:2000});}else{setTimeout(fn,1500);}};
          schedule(function(){loadGa();start();loadGtm();});
        </script>
        @endif
    @endif

    @if($googleActive && !empty($adsId))
        @php $adsTagId = (strpos($adsId, 'AW-') === 0) ? $adsId : 'AW-'.$adsId; @endphp
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          var loadAds=function(){var s=document.createElement('script');s.async=true;s.src='https://www.googletagmanager.com/gtag/js?id={{ $adsTagId }}';document.head.appendChild(s);};
          var start=function(){gtag('js', new Date());gtag('config','{{ $adsTagId }}');};
          var schedule=function(fn){if('requestIdleCallback'in window){requestIdleCallback(fn,{timeout:2000});}else{setTimeout(fn,2000);}};
          schedule(function(){loadAds();start();});
        </script>
    @endif

    <!-- Meta Pixel -->
    @if($metaActive && !empty($metaPixelId))
    <script>
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
    document,'script','https://connect.facebook.net/en_US/fbevents.js');
    fbq('init','{{ $metaPixelId }}');
    fbq('track','PageView');
    (function(){try{var p=new URLSearchParams(window.location.search);var c=p.get('fbclid');if(c){var ts=Math.floor(Date.now()/1000);var v='fb.1.'+ts+'.'+c;document.cookie='_fbc='+v+'; path=/; max-age='+(2*365*24*60*60)+'; SameSite=Lax';}}catch(e){}})();
    window.addEventListener('DOMContentLoaded',function(){
      var hasFbq = typeof window.fbq==='function';
      if(!hasFbq) return;
      var once=function(fn){var done=false;return function(){if(done)return;done=true;fn.apply(this,arguments);};};
      var on=function(el,ev,fn){try{el.addEventListener(ev,fn,{passive:true});}catch(e){el.addEventListener(ev,fn);}};
      var q=function(sel){return Array.prototype.slice.call(document.querySelectorAll(sel));};
      var isExternal=function(a){try{var u=new URL(a.href);return u.host!==location.host;}catch(e){return false;}};
      q('[data-action="open-booking"], [data-bs-target="#bookingModal"]').forEach(function(el){
        on(el,'click',function(){fbq('track','InitiateCheckout');});
      });
      q('a[href*="wa.me"], a[href*="api.whatsapp.com"], .btn-whatsapp-animated').forEach(function(el){
        on(el,'click',function(){fbq('track','Contact',{contact_point:'WhatsApp'});});
      });
      q('a[href^="tel:"]').forEach(function(el){
        on(el,'click',function(){fbq('track','Contact',{contact_point:'Phone'});});
      });
      q('a[href^="mailto:"]').forEach(function(el){
        on(el,'click',function(){fbq('track','Contact',{contact_point:'Email'});});
      });
      var cf=document.getElementById('contactForm');
      if(cf){cf.addEventListener('submit',once(function(){fbq('track','Lead',{form:'contact'});}));}
      var bf=document.getElementById('bookingForm');
      if(bf){bf.addEventListener('submit',once(function(){fbq('track','Lead',{form:'booking'});}));}
      document.body.addEventListener('click',function(e){
        var a=e.target.closest&&e.target.closest('a');
        if(!a) return;
        if(isExternal(a)){fbq('trackCustom','OutboundLink',{url:a.href});}
      },true);
    });
    </script>
    @endif

    <script src="https://www.google.com/recaptcha/enterprise.js?render={{ $rcSiteKey }}" async defer></script>
    <script>
        window.RECAPTCHA_SITE_KEY = @json($rcSiteKey);
        window.WHATSAPP_FORM_ENABLED = @json($settings['whatsapp_form_enabled'] ?? '1');
        window.CSRF_TOKEN = @json(csrf_token());
        window.MAPS_API_KEY = @json($settings['google_maps_api_key'] ?? '');
    </script>
</head>
<body class="d-flex flex-column min-vh-100">

    @if($googleActive && !empty($gtmId) && strpos($gtmId, 'G-') !== 0)
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif
    @if($metaActive && !empty($metaPixelId))
    <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1"/></noscript>
    @endif

    <!-- Header Navigation -->
    <header id="header" class="fixed-top transition-all">
        <nav class="navbar navbar-expand-lg navbar-light glass-nav sticky-sm-top sticky-md-top sticky-lg-top">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center p-0" href="{{ route('home') }}">
                    <img src="{{ asset('images/logo.png') }}" alt="Dunes Discovery Tourism" width="160" height="46" class="img-fluid logo-img" fetchpriority="high">
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
                            <img src="{{ asset('images/logo.png') }}" alt="Dunes Discovery" width="140" height="40" class="img-fluid">
                        </div>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body p-4 p-lg-0">
                        <ul class="navbar-nav mx-auto mb-4 mb-lg-0 gap-lg-1 text-nowrap">
                            <li class="nav-item">
                                <a class="nav-link px-3 px-lg-2 py-2 rounded-3 {{ request()->routeIs('home') ? 'active bg-primary-subtle text-primary fw-semibold' : '' }}" href="{{ route('home') }}">Home</a>
                            </li>
                            <li class="nav-item dropdown align-items-center flex-wrap w-100 w-lg-auto">
                                <div class="d-flex flex-wrap align-items-stretch w-100 rounded-3 position-relative {{ request()->routeIs('tours.*') ? 'bg-primary-subtle' : '' }}">
                                    <a class="nav-link px-3 px-lg-2 py-2 flex-grow-1 rounded-start-3 {{ request()->routeIs('tours.*') ? 'active text-primary fw-semibold' : '' }}" href="{{ route('tours.index') }}">Tours</a>
                                    <a class="nav-link px-2 py-2 dropdown-toggle dropdown-toggle-split d-flex align-items-center rounded-end-3 border-start border-primary border-opacity-10 {{ request()->routeIs('tours.*') ? 'active text-primary' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                                <a class="nav-link px-3 px-lg-2 py-2 rounded-3 {{ request()->routeIs('about') ? 'active bg-primary-subtle text-primary fw-semibold' : '' }}" href="{{ route('about') }}">About Us</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3 px-lg-2 py-2 rounded-3 {{ request()->routeIs('blog.*') ? 'active bg-primary-subtle text-primary fw-semibold' : '' }}" href="{{ route('blog.index') }}">Blog</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3 px-lg-2 py-2 rounded-3 {{ request()->routeIs('faq') ? 'active bg-primary-subtle text-primary fw-semibold' : '' }}" href="{{ route('faq') }}">FAQ</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3 px-lg-2 py-2 rounded-3 {{ request()->routeIs('contact') ? 'active bg-primary-subtle text-primary fw-semibold' : '' }}" href="{{ route('contact') }}">Contact</a>
                            </li>
                        </ul>
                        <div class="d-flex flex-column flex-lg-row gap-3 align-items-stretch align-items-lg-center">
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

    <!-- Main Content -->
    <main id="main">
        @yield('content')
    </main>

    <!-- Footer Section -->
    <footer class="footer bg-dark text-white pt-5 pb-4">
        <div class="container">
            <div class="row g-4 mb-5">
                <div class="col-12 col-lg-4">
                    <img src="{{ asset('images/logo-white.png') }}" alt="Dunes Discovery Tourism" width="160" height="46" class="mb-4" style="height: auto; width: 160px; object-fit: contain;">
                    <p class="text-white-50 small pe-lg-5">Your trusted partner for unforgettable Dubai desert safari and city tour experiences since 2018. We specialize in creating memories that last a lifetime.</p>
                    <div class="d-flex gap-3 mt-4">
                        <a href="https://instagram.com/dunesdiscoverytourism" target="_blank" rel="noopener" class="btn btn-outline-light btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" aria-label="Follow Dunes Discovery Tourism on Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="https://facebook.com/dunesdiscoverytourism" target="_blank" rel="noopener" class="btn btn-outline-light btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" aria-label="Follow Dunes Discovery Tourism on Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}" target="_blank" rel="noopener" class="btn btn-outline-light btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" aria-label="Chat with Dunes Discovery Tourism on WhatsApp"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <h4 class="h6 fw-bold text-uppercase mb-4">Desert Safaris</h4>
                    <ul class="list-unstyled mb-0 d-grid gap-2">
                        <li><a href="{{ route('tours.show', 'evening-desert-safari-dubai') }}" class="text-white-50 text-decoration-none small">Evening Safari</a></li>
                        <li><a href="{{ route('tours.show', 'morning-desert-safari-dubai') }}" class="text-white-50 text-decoration-none small">Morning Safari</a></li>
                        <li><a href="{{ route('tours.show', 'overnight-desert-safari-dubai') }}" class="text-white-50 text-decoration-none small">Overnight Safari</a></li>
                        <li><a href="{{ route('tours.show', 'desert-safari-quad-biking-dubai') }}" class="text-white-50 text-decoration-none small">Quad Biking Safari</a></li>
                    </ul>
                    <a href="https://www.tripadvisor.com/Attraction_Review-g295424-d29026644-Reviews-Dunes_Discovery-Dubai_Emirate_of_Dubai.html" target="_blank" rel="noopener" class="footer-badge mt-4 d-flex align-items-center text-decoration-none">
                        <img src="{{ asset('images/tripadvisor-logo-circle-owl-icon-black-green.png') }}" alt="TripAdvisor" class="footer-badge-logo" style="width:24px; height:24px; margin-right:8px;">
                        <div class="footer-badge-header">
                            <span class="footer-badge-score text-white fw-bold">4.9</span>
                            <div class="footer-badge-stars text-warning small" style="font-size:10px;">
                                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-lg-2">
                    <h4 class="h6 fw-bold text-uppercase mb-4">Tours & Cruises</h4>
                    <ul class="list-unstyled mb-0 d-grid gap-2">
                        <li><a href="{{ route('tours.show', 'dubai-city-tour') }}" class="text-white-50 text-decoration-none small">Dubai City Tour</a></li>
                        <li><a href="{{ route('tours.show', 'abu-dhabi-city-tour-from-dubai') }}" class="text-white-50 text-decoration-none small">Abu Dhabi Tour</a></li>
                        <li><a href="{{ route('tours.show', 'dhow-cruise-catamaran-cruise-dinner-dubai') }}" class="text-white-50 text-decoration-none small">Marina Cruise</a></li>
                    </ul>
                    <a href="https://www.google.com/maps/search/?api=1&query=Google&query_place_id=ChIJbWsIEIVEdEER4uHEhb2dbcQ" target="_blank" rel="noopener" class="footer-badge mt-4 d-flex align-items-center text-decoration-none">
                        <img src="https://www.gstatic.com/marketing-cms/assets/images/d5/dc/cfe9ce8b4425b410b49b7f2dd3f3/g.webp=s48-fcrop64=1,00000000ffffffff-rw" alt="Google" class="footer-badge-logo" style="width:24px; height:24px; margin-right:8px;">
                        <div class="footer-badge-header">
                            <span class="footer-badge-score text-white fw-bold">5.0</span>
                            <div class="footer-badge-stars text-warning small" style="font-size:10px;">
                                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-lg-4">
                    <h4 class="h6 fw-bold text-uppercase mb-4">Contact Us</h4>
                    <ul class="list-unstyled mb-0 d-grid gap-3">
                        <li><a href="tel:{{ preg_replace('/[^0-9+]/','',$phone) }}" class="text-white-50 text-decoration-none small d-flex align-items-center gap-2"><i class="bi bi-telephone text-primary"></i>{{ $phone }}</a></li>
                        <li><a href="mailto:{{ $email }}" class="text-white-50 text-decoration-none small d-flex align-items-center gap-2"><i class="bi bi-envelope text-primary"></i>{{ $email }}</a></li>
                        <li><a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}" target="_blank" rel="noopener" class="text-white-50 text-decoration-none small d-flex align-items-center gap-2"><i class="bi bi-whatsapp text-primary"></i>WhatsApp Chat</a></li>
                        <li class="text-white-50 small d-flex align-items-center gap-2"><i class="bi bi-geo-alt text-primary"></i>Dubai, United Arab Emirates</li>
                    </ul>
                </div>
            </div>
            <div class="border-top border-secondary pt-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <p class="text-white-50 small mb-0">&copy; {{ date('Y') }} Dunes Discovery Tourism. All rights reserved.</p>
                    <div class="d-flex align-items-center gap-3 footer-trust-icons">
                        <img src="{{ asset('images/ziina-icon.png') }}" alt="Ziina Payment Gateway" width="18" height="18" style="filter: invert(1); opacity: 0.6;">
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

    <!-- Scripts -->
    <script src="{{ asset('assets/vendor/bootstrap/5.3.2/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/intl-tel-input/26.0.6/build/intlTelInput.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}?v={{ $cacheVer }}"></script>

    @stack('scripts')
</body>
</html>
