@php
    if (!isset($settings) || (!is_array($settings) && !($settings instanceof \ArrayAccess))) {
        try {
            $settings = app(\App\Services\SettingsService::class)->all();
        } catch (\Throwable $e) {
            $settings = [];
        }
    } elseif (is_countable($settings) && count($settings) === 0) {
        try {
            $settings = app(\App\Services\SettingsService::class)->all();
        } catch (\Throwable $e) {
            $settings = [];
        }
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
    $adsId = normalize_google_ads_id($settings['google_ads_id'] ?? 'AW-17859624049');
    $primaryGtagId = !empty($adsId) ? $adsId : ($googleActive && !empty($ga4Id) ? $ga4Id : '');
    $conversionLabel = $settings['google_conversion_label'] ?? 'eR3SCLimtvobEPH4kMRC';
    $conversionSendTo = (!empty($adsId) && !empty($conversionLabel)) ? "{$adsId}/{$conversionLabel}" : '';
    $gVerify = $settings['google_site_verification'] ?? config('services.google.site_verification') ?? env('GOOGLE_SITE_VERIFICATION', '');
    $bingVerify = $settings['bing_site_verification'] ?? config('services.bing.site_verification') ?? env('BING_SITE_VERIFICATION', '');
    
    $metaActive = isset($settings['meta_active']) && $settings['meta_active'] === '1';
    $metaCapi = isset($settings['meta_capi_enabled']) && $settings['meta_capi_enabled'] === '1';
    $metaPixelId = $settings['meta_pixel_id'] ?? '';
    
    $rcSiteKey = $settings['recaptcha_site_key'] ?? '6LdOpWEsAAAAAOhnKd4WFFtJMrShRtMV33HdZBP6';
    $waPhone = $settings['site_whatsapp'] ?? '971502456056';
    $phone = $settings['site_phone'] ?? '+971 50 245 6056';
    $email = $settings['site_email'] ?? 'info@dunesdiscoverytourism.com';
    $conciergePromoActive = isset($settings['concierge_promo_active']) && $settings['concierge_promo_active'] === '1';
    $conciergePromoDiscount = $settings['concierge_promo_discount'] ?? '5';

    $siteLat = $settings['site_latitude'] ?? '25.2048';
    $siteLng = $settings['site_longitude'] ?? '55.2708';
    $googlePlaceId = $settings['google_place_id'] ?? 'ChIJbWsIEIVEdEER4uHEhb2dbcQ';
    $googleCid = $settings['google_cid'] ?? '14154142641213989346';
    $googleReviewUrl = $settings['google_review_url'] ?? (!empty($googlePlaceId) ? "https://search.google.com/local/writereview?placeid={$googlePlaceId}" : '');
    $detLicense = $settings['company_license_number'] ?? ($settings['site_det_license'] ?? '1430583');
    $sitePostalCode = $settings['site_postal_code'] ?? '00000';
    $siteAddress = $settings['site_address'] ?? 'Al Fahidi, Bur Dubai, Dubai, United Arab Emirates';
    $cmsSvc = app(\App\Services\CmsContentService::class);
    $headerMenuItems = $cmsSvc->getMenuItems('header');

    $currentYear = date('Y');
    $currentLocale = $currentLocale ?? app()->getLocale() ?? 'en';
    $activeLanguages = $activeLanguages ?? \App\Models\Language::getActive();
    $yieldTitle = trim($__env->yieldContent('title') ?: $__env->yieldContent('page_title'));
    $yieldDesc = trim($__env->yieldContent('meta_description'));
    $pageTitle = $pageTitle ?? ($yieldTitle ?: "Dunes Discovery Tourism | Dubai Desert Safari Tours ({$currentYear})");
    $pageDesc = $pageDesc ?? ($yieldDesc ?: 'Book Dubai best desert safari tours from AED 99. Evening safari, city tours, dhow cruises with instant confirmation.');
    $pageKeys = $pageKeys ?? 'dubai desert safari,desert safari dubai,evening desert safari';
    $pageRobots = $pageRobots ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
    $canonical = $canonical ?? (request()->is('/') ? rtrim(url('/'), '/') . '/' : request()->url());
    $ogImage = resolve_og_image($ogImage ?? null);
    $ogImageExt = strtolower(pathinfo((string) parse_url((string) $ogImage, PHP_URL_PATH), PATHINFO_EXTENSION));
    $ogImageType = match ($ogImageExt) {
        'jpg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        default => 'image/avif',
    };
    $ogType = $ogType ?? 'website';
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLocale }}" dir="{{ ($isRtl ?? ($currentLocale === 'ar')) ? 'rtl' : 'ltr' }}">
<head>
    @if(($isRtl ?? false) || ($currentLocale ?? app()->getLocale()) === 'ar')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        [dir="rtl"], [dir="rtl"] body, [dir="rtl"] h1, [dir="rtl"] h2, [dir="rtl"] h3, [dir="rtl"] h4, [dir="rtl"] h5, [dir="rtl"] h6, [dir="rtl"] p, [dir="rtl"] span, [dir="rtl"] a, [dir="rtl"] input, [dir="rtl"] button, [dir="rtl"] select, [dir="rtl"] textarea, [dir="rtl"] .font-sans, [dir="rtl"] .font-heading {
            font-family: 'Cairo', system-ui, -apple-system, sans-serif !important;
        }
    </style>
    @endif

    @if(!empty($primaryGtagId))
    <!-- Google tag (gtag.js) {{ $primaryGtagId }} (Decoupled Sitewide Tag) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $primaryGtagId }}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      @if(!empty($adsId))
      gtag('config', '{{ $adsId }}', {
        'allow_enhanced_conversions': true
      });
      @endif
      @if($googleActive && !empty($ga4Id))
      gtag('config', '{{ $ga4Id }}');
      @endif

      // Helper function to delay opening a URL until an event callback fires (without sending unwanted conversions)
      function gtagSendEvent(url, eventName, params) {
        var navigated = false;
        var callback = function () {
          if (!navigated && typeof url === 'string' && url) {
            navigated = true;
            window.location = url;
          }
        };

        // Fallback safety timeout (1.5s) in case beacon takes too long
        setTimeout(callback, 1500);

        if (typeof eventName === 'object' && eventName !== null && !params) {
          params = eventName;
          eventName = null;
        }

        var eventData = Object.assign({
          'event_callback': callback,
          'event_timeout': 1500
        }, params || {});

        if (typeof gtag === 'function' && eventName && eventName !== 'conversion') {
          gtag('event', eventName, eventData);
        } else {
          callback();
        }
        return false;
      }
      window.gtagSendEvent = gtagSendEvent;

      function gtagReportConversion(url) {
        if (typeof url === 'string' && url) {
          window.location = url;
        }
        return false;
      }
      window.gtagReportConversion = gtagReportConversion;
    </script>
    @else
    <script>
      window.gtagSendEvent = function(url) { if (url) window.location = url; return false; };
      window.gtagReportConversion = window.gtagSendEvent;
    </script>
    @endif

    @if($metaActive && !empty($metaPixelId))
    <!-- Meta Pixel Code with Immediate Synchronous fbq Stub -->
    <script>
      !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
      n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
      n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
      t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
      document,'script','https://connect.facebook.net/en_US/fbevents.js');
      fbq('init', '{{ $metaPixelId }}');
      fbq('track', 'PageView');
    </script>
    @else
    <script>
      window.fbq = window.fbq || function() {};
    </script>
    @endif

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        window.CSRF_TOKEN = "{{ csrf_token() }}";
        window.WHATSAPP_FORM_ENABLED = "{{ (string)($settings['whatsapp_form_enabled'] ?? '1') }}";
        window.WHATSAPP_NUMBER = "{{ (string)($settings['site_whatsapp'] ?? '971502456056') }}";
    </script>
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDesc }}">
    <meta name="keywords" content="{{ $pageKeys }}">
    <meta name="robots" content="{{ $pageRobots }}">
    <meta name="author" content="Dunes Discovery Tourism">
    @if(!($isErrorPage ?? false))
    <link rel="canonical" href="{{ $canonical }}">
    @endif
    @if(!str_contains(strtolower($pageRobots ?? ''), 'noindex') && !($isErrorPage ?? false))
    <link rel="alternate" hreflang="x-default" href="{{ switch_locale_url('en') }}">
    @if(isset($activeLanguages) && is_iterable($activeLanguages))
        @foreach($activeLanguages as $langItem)
            <link rel="alternate" hreflang="{{ $langItem->code }}" href="{{ switch_locale_url($langItem->code) }}">
        @endforeach
    @else
        <link rel="alternate" hreflang="en" href="{{ switch_locale_url('en') }}">
    @endif
    @endif
    
    <link rel="preconnect" href="https://www.googletagmanager.com">
    <link rel="preconnect" href="https://connect.facebook.net">
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">
    <link rel="dns-prefetch" href="https://connect.facebook.net">

    <!-- OpenGraph Metadata -->
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDesc }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:type" content="{{ $ogImageType }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="Dunes Discovery Tourism">
    @php
        $ogLocaleMap = [
            'en' => 'en_US',
            'ar' => 'ar_AE',
            'ru' => 'ru_RU',
            'es' => 'es_ES',
            'it' => 'it_IT',
            'de' => 'de_DE',
            'fr' => 'fr_FR',
            'zh' => 'zh_CN',
        ];
        $activeLocaleCode = $currentLocale ?? app()->getLocale() ?? 'en';
        $currentOgLocale = $ogLocaleMap[$activeLocaleCode] ?? str_replace('-', '_', $activeLocaleCode);
    @endphp
    <meta property="og:locale" content="{{ $currentOgLocale }}">
    @if(isset($activeLanguages) && is_iterable($activeLanguages))
        @foreach($activeLanguages as $langItem)
            @if($langItem->code !== $activeLocaleCode)
                <meta property="og:locale:alternate" content="{{ $ogLocaleMap[$langItem->code] ?? str_replace('-', '_', $langItem->code) }}">
            @endif
        @endforeach
    @endif
    
    <!-- Twitter Card Metadata -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDesc }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    
    <meta name="geo.region" content="AE-DU">
    <meta name="geo.placename" content="Dubai">
    <meta name="geo.position" content="{{ $siteLat }};{{ $siteLng }}">
    <meta name="ICBM" content="{{ $siteLat }}, {{ $siteLng }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#F69044">
    <meta name="msapplication-TileColor" content="#F69044">

    @if(!empty($gVerify))
    <meta name="google-site-verification" content="{{ $gVerify }}">
    @endif
    @if(!empty($bingVerify))
    <meta name="msvalidate.01" content="{{ $bingVerify }}">
    @endif
    
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-192.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="alternate" type="application/rss+xml" title="Dunes Discovery Tourism Travel Blog" href="{{ url('/feed.xml') }}">

    <!-- Prevent Alpine.js modal/drawer flashing during page hydration -->
    <style>[x-cloak] { display: none !important; }</style>

    <!-- Stylesheets: Tailwind v4 Token System & Vendor Assets via Vite -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/intl-tel-input/26.0.6/build/intlTelInput.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')


    @stack('preloads')

    <!-- Global Unified @graph JSON-LD Entity Knowledge Graph & Speakable Schema -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@graph": [
        {
          "@@type": "TravelAgency",
          "@@id": {!! json_encode(url('/') . '#organization', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
          "name": {!! json_encode($settings['site_name'] ?? 'Dunes Discovery Tourism LLC', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
          "legalName": "Dunes Discovery Tourism LLC",
          "alternateName": ["Dunes Discovery", "Dunes Discovery Tourism", "Dunes Discovery Dubai"],
          "description": {!! json_encode($settings['site_description'] ?? 'Licensed Dubai Destination Management Company offering premium Desert Safaris, 1000cc Dune Buggy Rentals, Quad Biking, Dhow Cruise Dinners, and Abu Dhabi City Tours.', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
          "url": {!! json_encode(url('/'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
          "logo": {!! json_encode(asset('images/logo.png'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
          "image": {!! json_encode(!empty($settings['site_default_image']) ? (str_starts_with($settings['site_default_image'], 'http') ? $settings['site_default_image'] : asset(ltrim($settings['site_default_image'], '/'))) : (!empty($settings['seo_default_og_image']) ? (str_starts_with($settings['seo_default_og_image'], 'http') ? $settings['seo_default_og_image'] : asset(ltrim($settings['seo_default_og_image'], '/'))) : asset('images/desert-safari-poster.avif')), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
          "telephone": {!! json_encode($phone, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
          "email": {!! json_encode($email, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
          "priceRange": "AED 79 - AED 1500",
          "currenciesAccepted": "AED, USD, EUR, GBP",
          "paymentAccepted": "Cash, Credit Card, Debit Card, Ziina",
          "identifier": [
            {
              "@@type": "PropertyValue",
              "propertyID": "DET Tourism License",
              "value": {!! json_encode($detLicense, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
            }
            @if(!empty($googlePlaceId))
            ,{
              "@@type": "PropertyValue",
              "propertyID": "Google Place ID",
              "value": {!! json_encode($googlePlaceId, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
            }
            @endif
            @if(!empty($googleCid))
            ,{
              "@@type": "PropertyValue",
              "propertyID": "Google CID",
              "value": {!! json_encode($googleCid, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
            }
            @endif
          ],
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
          @if(request()->is('/') || request()->routeIs('home') || request()->routeIs('locale.home'))
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
            "streetAddress": {!! json_encode($siteAddress, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
            "addressLocality": "Dubai",
            "addressRegion": "Dubai",
            "postalCode": {!! json_encode($sitePostalCode, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
            "addressCountry": "AE"
          },
          "geo": {
            "@@type": "GeoCoordinates",
            "latitude": {{ (float)$siteLat }},
            "longitude": {{ (float)$siteLng }}
          },
          "hasMap": {!! json_encode("https://maps.google.com/?q={$siteLat},{$siteLng}", JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
          "contactPoint": [
            {
              "@@type": "ContactPoint",
              "telephone": {!! json_encode($phone, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
              "contactType": "customer service",
              "areaServed": "AE",
              "availableLanguage": {!! json_encode(isset($activeLanguages) ? $activeLanguages->pluck('code')->toArray() : ['en', 'ar', 'ru', 'es', 'it'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
            },
            {
              "@@type": "ContactPoint",
              "telephone": "+971502456056",
              "contactType": "reservations",
              "areaServed": "AE",
              "availableLanguage": {!! json_encode(isset($activeLanguages) ? $activeLanguages->pluck('code')->toArray() : ['en', 'ar', 'ru', 'es', 'it'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
            }
          ],
          "openingHoursSpecification": {
            "@@type": "OpeningHoursSpecification",
            "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
            "opens": "00:00",
            "closes": "23:59"
          },
          "sameAs": [
            @if(!empty($googleReviewUrl))
            {!! json_encode($googleReviewUrl, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
            @endif
            @if(!empty($googleCid))
            {!! json_encode("https://maps.google.com/?cid={$googleCid}", JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
            @endif
            "https://www.facebook.com/dunesdiscoverytourism",
            "https://www.instagram.com/dunesdiscoverytourism",
            "https://www.tripadvisor.com"
          ],
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
          "@@id": {!! json_encode(url('/') . '#website', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
          "url": {!! json_encode(url('/'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
          "name": "Dunes Discovery Tourism",
          "publisher": {
            "@@id": {!! json_encode(url('/') . '#organization', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
          },
          "potentialAction": {
            "@@type": "SearchAction",
            "target": {
              "@@type": "EntryPoint",
              "urlTemplate": {!! json_encode(localized_route('tours.search') . '?q={search_term_string}', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
            },
            "query-input": "required name=search_term_string"
          },
          "inLanguage": {!! json_encode(isset($activeLanguages) ? $activeLanguages->pluck('code')->toArray() : ['en', 'ar', 'ru', 'es', 'it'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
        },
        {
          "@@type": {!! json_encode($schemaPageType ?? 'WebPage', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
          "@@id": {!! json_encode($canonical . '#webpage', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
          "url": {!! json_encode($canonical, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
          "name": {!! json_encode($pageTitle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
          "description": {!! json_encode($pageDesc, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
          "inLanguage": {!! json_encode($currentLocale, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
          "isPartOf": {
            "@@id": {!! json_encode(url('/') . '#website', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
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
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:start-4 focus:z-50 focus:px-4 focus:py-2.5 focus:bg-primary focus:text-white focus:rounded-xl focus:shadow-lg focus:font-bold">Skip to main content</a>

    @if($googleActive && !empty($gtmId) && str_starts_with($gtmId, 'GTM-'))
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
                <div class="flex items-center justify-between h-16 sm:h-18 lg:h-20 xl:h-22">
                    <!-- Brand Logo & Trust Badges -->
                    <div class="flex items-center gap-3 xl:gap-4 flex-shrink-0">
                        <a class="flex items-center hover:opacity-90 transition-opacity" href="{{ localized_route('home') }}" aria-label="Dunes Discovery Tourism">
                            <img src="{{ asset('images/logo.png') }}" alt="Dunes Discovery Tourism" width="180" height="116" class="h-10 sm:h-12 lg:h-14 xl:h-16 w-auto object-contain transition-all" fetchpriority="high">
                        </a>

                        <!-- Desktop Trust Badges (Google & TripAdvisor with Live Ratings) -->
                        <div class="hidden xl:flex items-center gap-2 border-s border-slate-200/90 ps-3">
                            <!-- Google Reviews Badge -->
                            <a href="{{ $settings['social_google'] ?? 'https://search.google.com/local/writereview?placeid=ChIJbWsIEIVEdEER4uHEhb2dbcQ' }}" 
                               target="_blank" 
                               rel="noopener" 
                               id="desktopGoogleBadge"
                               class="nav-review-circle inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white hover:bg-slate-50 border border-slate-200/90 shadow-2xs hover:shadow-xs transition-all cursor-pointer group"
                               onclick="toggleReviewPopover(this, event)"
                               title="Google Reviews: 5.0 Rating">
                                <img src="{{ asset('images/Google-G.avif') }}" alt="Google" width="16" height="16" class="w-4 h-4 object-contain">
                                <span class="text-xs font-black text-slate-800 group-hover:text-primary transition-colors">5.0</span>
                                <div class="flex text-amber-400 text-[10px]">
                                    <i class="bi bi-star-fill"></i>
                                </div>
                                <span class="text-[10px] text-slate-400 font-semibold hidden xl:inline">Google</span>
                            </a>

                            <!-- TripAdvisor Reviews Badge -->
                            <a href="{{ $settings['social_tripadvisor'] ?? 'https://www.tripadvisor.com/Attraction_Review-g295424-d29026644-Reviews-Dunes_Discovery-Dubai_Emirate_of_Dubai.html' }}" 
                               target="_blank" 
                               rel="noopener" 
                               id="desktopTaBadge"
                               class="nav-review-circle inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white hover:bg-slate-50 border border-slate-200/90 shadow-2xs hover:shadow-xs transition-all cursor-pointer group"
                               onclick="toggleReviewPopover(this, event)"
                               title="TripAdvisor Reviews: 4.9 Rating">
                                <img src="{{ asset('images/tripadvisor-color-logo.svg') }}" alt="TripAdvisor" width="18" height="18" class="w-4.5 h-4.5 object-contain">
                                <span class="text-xs font-black text-slate-800 group-hover:text-emerald-600 transition-colors">4.9</span>
                                <div class="flex text-emerald-600 text-[10px]">
                                    <i class="bi bi-star-fill"></i>
                                </div>
                                <span class="text-[10px] text-slate-400 font-semibold hidden xl:inline">TripAdvisor</span>
                            </a>
                        </div>
                    </div>

                    <!-- Desktop Nav Links (Dynamic from CMS, with tours dropdown support) -->
                    <ul class="hidden lg:flex items-center gap-1 xl:gap-2 text-sm font-semibold text-slate-700">
                        @if($headerMenuItems->count() > 0)
                            @foreach($headerMenuItems as $mItem)
                                @php
                                    $isToursItem = ($mItem->route_name === 'tours.index' || $mItem->url === '/tours' || str_ends_with(rtrim($mItem->url, '/'), '/tours'));
                                    $isActive = false;
                                    if (!empty($mItem->route_name)) {
                                        $isActive = request()->routeIs($mItem->route_name, 'locale.' . $mItem->route_name)
                                            || ($isToursItem && request()->routeIs('tours.*', 'locale.tours.*'))
                                            || (str_starts_with($mItem->route_name, 'blog') && request()->routeIs('blog.*', 'locale.blog.*'));
                                    } elseif (!empty($mItem->url)) {
                                        $cleanPath = trim($mItem->url, '/');
                                        $isActive = request()->is($cleanPath, $cleanPath . '/*', '{locale}/' . $cleanPath, '{locale}/' . $cleanPath . '/*');
                                    }
                                @endphp

                                @if($isToursItem)
                                    <li class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                                        <div class="inline-flex items-center rounded-xl {{ $isActive ? 'bg-slate-900 text-white font-bold shadow-xs' : '' }}">
                                            <a class="px-3 py-2 rounded-s-xl transition-all {{ $isActive ? 'text-white' : 'hover:text-primary hover:bg-orange-50/60' }}" href="{{ $mItem->resolved_url }}" target="{{ $mItem->target ?? '_self' }}">{{ $mItem->label }}</a>
                                            <button type="button" @click="open = !open" :aria-expanded="open" class="px-1.5 py-2 rounded-e-xl transition-all hover:opacity-80 cursor-pointer" aria-label="{{ $mItem->label }}">
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
                                             class="absolute start-0 mt-2 w-72 rounded-2xl bg-white p-2 shadow-xl border border-slate-200 z-50 focus:outline-none"
                                             style="display: none;">
                                            <a class="flex items-center gap-2 px-3 py-2.5 rounded-xl font-bold text-primary hover:bg-orange-50 transition-colors text-xs" href="{{ localized_route('tours.customizer') }}">
                                                <i class="bi bi-sliders text-amber-500"></i>
                                                <span>{{ __('ui.nav.customizer') }}</span>
                                            </a>
                                            <div class="my-1 border-t border-slate-200"></div>
                                            <div class="max-h-72 overflow-y-auto space-y-0.5">
                                                @foreach($allTours as $t)
                                                <a class="block px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-orange-50 hover:text-primary transition-colors line-clamp-1" href="{{ localized_route('tours.show', $t->slug) }}">
                                                    {{ $t->name }}
                                                </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    </li>
                                @else
                                    <li>
                                        <a class="px-3 py-2 rounded-xl transition-all {{ $isActive ? 'bg-slate-900 text-white font-bold shadow-xs' : 'hover:text-primary hover:bg-orange-50/60' }}" href="{{ $mItem->resolved_url }}" target="{{ $mItem->target ?? '_self' }}">{{ $mItem->label }}</a>
                                    </li>
                                @endif
                            @endforeach
                        @else
                            {{-- Fallback default links if no header items in DB --}}
                            <li class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                                <div class="inline-flex items-center rounded-xl {{ request()->routeIs('tours.*', 'locale.tours.*') ? 'bg-slate-900 text-white font-bold shadow-xs' : '' }}">
                                    <a class="px-3 py-2 rounded-s-xl transition-all {{ request()->routeIs('tours.*', 'locale.tours.*') ? 'text-white' : 'hover:text-primary hover:bg-orange-50/60' }}" href="{{ localized_route('tours.index') }}">{{ __('ui.nav.tours_short') ?? __('ui.nav.tours') }}</a>
                                    <button type="button" @click="open = !open" :aria-expanded="open" class="px-1.5 py-2 rounded-e-xl transition-all hover:opacity-80 cursor-pointer" aria-label="{{ __('ui.nav.tours_short') ?? __('ui.nav.tours') }}">
                                        <i class="bi bi-chevron-down text-[10px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                                    </button>
                                </div>
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                     class="absolute start-0 mt-2 w-72 rounded-2xl bg-white p-2 shadow-xl border border-slate-200 z-50 focus:outline-none"
                                     style="display: none;">
                                    <a class="flex items-center gap-2 px-3 py-2.5 rounded-xl font-bold text-primary hover:bg-orange-50 transition-colors text-xs" href="{{ localized_route('tours.customizer') }}">
                                        <i class="bi bi-sliders text-amber-500"></i>
                                        <span>{{ __('ui.nav.customizer') }}</span>
                                    </a>
                                    <div class="my-1 border-t border-slate-200"></div>
                                    <div class="max-h-72 overflow-y-auto space-y-0.5">
                                        @foreach($allTours as $t)
                                        <a class="block px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-orange-50 hover:text-primary transition-colors line-clamp-1" href="{{ localized_route('tours.show', $t->slug) }}">
                                            {{ $t->name }}
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                            </li>
                            <li>
                                <a class="px-3 py-2 rounded-xl transition-all {{ request()->routeIs('about', 'locale.about') ? 'bg-slate-900 text-white font-bold shadow-xs' : 'hover:text-primary hover:bg-orange-50/60' }}" href="{{ localized_route('about') }}">{{ __('ui.nav.about_short') ?? __('ui.nav.about') }}</a>
                            </li>
                            <li>
                                <a class="px-3 py-2 rounded-xl transition-all {{ request()->routeIs('blog.*', 'locale.blog.*') ? 'bg-slate-900 text-white font-bold shadow-xs' : 'hover:text-primary hover:bg-orange-50/60' }}" href="{{ localized_route('blog.index') }}">{{ __('ui.nav.blogs') ?? __('ui.nav.blog') }}</a>
                            </li>
                            <li>
                                <a class="px-3 py-2 rounded-xl transition-all {{ request()->routeIs('faq', 'locale.faq') ? 'bg-slate-900 text-white font-bold shadow-xs' : 'hover:text-primary hover:bg-orange-50/60' }}" href="{{ localized_route('faq') }}">{{ __('ui.nav.faq') }}</a>
                            </li>
                            <li>
                                <a class="px-3 py-2 rounded-xl transition-all {{ request()->routeIs('contact', 'locale.contact') ? 'bg-slate-900 text-white font-bold shadow-xs' : 'hover:text-primary hover:bg-orange-50/60' }}" href="{{ localized_route('contact') }}">{{ __('ui.nav.contact') }}</a>
                            </li>
                        @endif
                    </ul>

                    <!-- Desktop Right Actions (Hidden on mobile/tablet) -->
                    <div class="hidden lg:flex items-center gap-2 xl:gap-3">
                        <button type="button" class="w-9 h-9 rounded-full bg-white border border-slate-200/90 shadow-2xs flex items-center justify-center text-slate-600 hover:text-primary hover:border-primary/50 transition-all cursor-pointer" @click="$store.modal.open('search')" title="{{ __('ui.nav.search') }}" aria-label="{{ __('ui.nav.search') }}">
                            <i class="bi bi-search text-xs"></i>
                        </button>
                        @include('partials.language-currency-switcher', ['switcherId' => 'desktopLangCurrencyDropdownBtn'])
                        <a class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full font-bold text-xs bg-emerald-500 hover:bg-emerald-600 text-white shadow-xs hover:shadow-sm transition-all cursor-pointer btn-whatsapp" href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}" target="_blank" rel="noopener" data-action="whatsapp">
                            <i class="bi bi-whatsapp"></i><span>{{ __('ui.common.whatsapp') }}</span>
                        </a>
                        <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full font-extrabold text-xs text-white bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 shadow-sm hover:shadow-md transition-all cursor-pointer" data-action="open-booking">
                            <i class="bi bi-calendar-check"></i><span>{{ __('ui.common.book_now') }}</span>
                        </button>
                    </div>

                    <!-- Mobile App Bar Actions (with Google & TripAdvisor Trust Badges with Ratings) -->
                    <div class="flex items-center gap-1 sm:gap-1.5 lg:hidden">
                        <!-- Google Review Trust Badge (Mobile with 5.0 Rating) -->
                        <a href="{{ $settings['social_google'] ?? 'https://search.google.com/local/writereview?placeid=ChIJbWsIEIVEdEER4uHEhb2dbcQ' }}" 
                           target="_blank" 
                           rel="noopener" 
                           id="googleCircle" 
                           class="nav-review-circle inline-flex items-center gap-1 px-1.5 sm:px-2 py-1 sm:py-1.5 rounded-full bg-white border border-slate-200/90 shadow-2xs hover:bg-slate-50 transition-all cursor-pointer min-h-[32px] sm:min-h-[36px] shrink-0"
                           onclick="toggleReviewPopover(this, event)"
                           title="{{ __('ui.common.google_reviews') }}">
                            <img src="{{ asset('images/Google-G.avif') }}" alt="Google" width="15" height="15" class="w-3.5 h-3.5 object-contain shrink-0">
                            <span class="text-[11px] sm:text-xs font-black text-slate-800 leading-none">5.0</span>
                            <i class="bi bi-star-fill text-amber-400 text-[9px] sm:text-[10px] leading-none"></i>
                        </a>

                        <!-- TripAdvisor Review Trust Badge (Mobile with 4.9 Rating) -->
                        <a href="{{ $settings['social_tripadvisor'] ?? 'https://www.tripadvisor.com/Attraction_Review-g295424-d29026644-Reviews-Dunes_Discovery-Dubai_Emirate_of_Dubai.html' }}" 
                           target="_blank" 
                           rel="noopener" 
                           id="taCircle" 
                           class="nav-review-circle inline-flex items-center gap-1 px-1.5 sm:px-2 py-1 sm:py-1.5 rounded-full bg-white border border-slate-200/90 shadow-2xs hover:bg-slate-50 transition-all cursor-pointer min-h-[32px] sm:min-h-[36px] shrink-0"
                           onclick="toggleReviewPopover(this, event)"
                           title="{{ __('ui.common.tripadvisor_reviews') }}">
                            <img src="{{ asset('images/tripadvisor-color-logo.svg') }}" alt="TripAdvisor" width="16" height="16" class="w-3.5 h-3.5 sm:w-4 sm:h-4 object-contain shrink-0">
                            <span class="text-[11px] sm:text-xs font-black text-slate-800 leading-none">4.9</span>
                            <i class="bi bi-star-fill text-emerald-600 text-[9px] sm:text-[10px] leading-none"></i>
                        </a>

                        <!-- Quick Search Button (Min 44x44px Hit Target) -->
                        <button type="button" class="w-11 h-11 rounded-full text-slate-700 hover:bg-slate-100 flex items-center justify-center transition-colors cursor-pointer text-sm shrink-0 focus:outline-none focus:ring-2 focus:ring-primary/40" @click="$store.modal.open('search')" aria-label="{{ __('ui.nav.search_placeholder') }}">
                            <i class="bi bi-search"></i>
                        </button>

                        <!-- WhatsApp Direct Button (Min 44x44px Hit Target) -->
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}" class="w-11 h-11 rounded-full text-emerald-600 hover:bg-emerald-50 flex items-center justify-center transition-colors cursor-pointer text-base btn-whatsapp shrink-0 focus:outline-none focus:ring-2 focus:ring-emerald-500/40" aria-label="{{ __('ui.common.whatsapp') }}" target="_blank" rel="noopener" data-action="whatsapp">
                            <i class="bi bi-whatsapp"></i>
                        </a>

                        <!-- Hamburger Drawer Trigger (Min 44x44px Hit Target) -->
                        <button id="menuToggle" class="w-11 h-11 rounded-xl text-slate-800 hover:bg-slate-100 flex items-center justify-center transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary/40 shrink-0" type="button" data-action="toggle-mobile-menu" @click.stop.prevent="$store.mobileNav.toggle()" :aria-expanded="$store.mobileNav.open" aria-controls="mobileSheet" aria-label="{{ __('ui.nav.all_tours') ?? 'Menu' }}">
                            <i class="bi bi-list text-2xl"></i>
                        </button>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Alpine-Powered Mobile Offcanvas Drawer (Direct child of body to prevent CSS transform containing-block clipping) -->
    <div x-show="$store.mobileNav.open" 
         id="sheetOverlay"
         x-cloak
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/60 z-[60] backdrop-blur-xs lg:hidden"
         @click.stop.prevent="$store.mobileNav.close()"
         style="display: none;"></div>

    <div x-show="$store.mobileNav.open"
         x-cloak
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="ltr:translate-x-full rtl:-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="ltr:translate-x-full rtl:-translate-x-full"
         class="fixed inset-y-0 end-0 max-w-xs sm:max-w-sm w-full bg-white z-[65] shadow-2xl p-5 flex flex-col justify-between overflow-y-auto overscroll-contain lg:hidden"
         style="display: none;"
         role="dialog"
         aria-modal="true"
         aria-label="{{ __('ui.nav.all_tours') ?? 'Navigation Menu' }}"
         id="mobileSheet">
        <div>
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 mb-4">
                <a href="{{ localized_route('home') }}" @click="$store.mobileNav.close()" class="flex items-center hover:opacity-90 transition-opacity" aria-label="Dunes Discovery Tourism">
                    <img src="{{ asset('images/logo.png') }}" alt="Dunes Discovery Tourism" width="160" height="103" class="h-10 sm:h-11 w-auto object-contain">
                </a>
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <!-- Review Popover Triggers in Mobile Drawer Header with Ratings -->
                    <a href="{{ $settings['social_tripadvisor'] ?? 'https://www.tripadvisor.com/Attraction_Review-g295424-d29026644-Reviews-Dunes_Discovery-Dubai_Emirate_of_Dubai.html' }}" 
                       target="_blank" 
                       rel="noopener" 
                       id="taCircleDrawer" 
                       class="nav-review-circle inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-50 border border-slate-200 shadow-2xs hover:bg-slate-100 transition-all cursor-pointer" 
                       onclick="toggleReviewPopover(this, event)" 
                       title="{{ __('ui.common.tripadvisor_reviews') }}">
                        <img src="{{ asset('images/tripadvisor-color-logo.svg') }}" alt="TripAdvisor" width="16" height="16" class="w-4 h-4 object-contain">
                        <span class="text-xs font-black text-slate-800">4.9</span>
                        <i class="bi bi-star-fill text-emerald-600 text-[10px]"></i>
                    </a>
                    <a href="{{ $settings['social_google'] ?? 'https://search.google.com/local/writereview?placeid=ChIJbWsIEIVEdEER4uHEhb2dbcQ' }}" 
                       target="_blank" 
                       rel="noopener" 
                       id="googleCircleDrawer" 
                       class="nav-review-circle inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-50 border border-slate-200 shadow-2xs hover:bg-slate-100 transition-all cursor-pointer" 
                       onclick="toggleReviewPopover(this, event)" 
                       title="{{ __('ui.common.google_reviews') }}">
                        <img src="{{ asset('images/Google-G.avif') }}" alt="Google" width="14" height="14" class="w-3.5 h-3.5 object-contain">
                        <span class="text-xs font-black text-slate-800">5.0</span>
                        <i class="bi bi-star-fill text-amber-400 text-[10px]"></i>
                    </a>
                    @include('partials.language-currency-switcher', ['switcherId' => 'mobileLangCurrencyDropdownBtn'])
                    <button id="sheetClose" type="button" class="w-10 h-10 rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 flex items-center justify-center transition-colors cursor-pointer" @click.stop.prevent="$store.mobileNav.close()" aria-label="{{ __('ui.common.close') }}">
                        <i class="bi bi-x-lg text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Search Input in Drawer (16px on mobile to eliminate iOS zoom) -->
            <form action="{{ localized_route('tours.search') }}" method="GET" class="relative mb-3">
                <i class="bi bi-search absolute start-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                <input type="text" name="q" aria-label="{{ __('ui.nav.search_placeholder') }}" class="w-full rounded-full ps-9 pe-4 py-2.5 bg-slate-100 text-slate-800 text-base sm:text-xs font-semibold border-0 focus:ring-2 focus:ring-primary focus:outline-none placeholder:text-slate-400" placeholder="{{ __('ui.nav.search_placeholder') }}" required>
            </form>

            <!-- Interactive Features CTAs -->
            <div class="space-y-2 mb-4">
                <button type="button" class="w-full rounded-2xl p-3 flex items-center justify-between text-start border border-amber-400/30 shadow-xs cursor-pointer min-h-[48px]" style="background: linear-gradient(135deg, #1E293B, #0F172A);" @click="$store.mobileNav.close(); $store.modal.open('safari-matcher');">
                    <div class="flex items-center gap-2.5">
                        <span class="text-amber-400 text-lg"><i class="bi bi-compass"></i></span>
                        <div>
                            <div class="font-bold text-white text-xs leading-none">{{ __('ui.footer.safari_concierge_btn') }}</div>
                            <small class="text-white/60 text-[10px]">{{ __('ui.footer.safari_concierge_desc') }}</small>
                        </div>
                    </div>
                    @if($conciergePromoActive)
                    <span class="px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 font-black text-[9px]">{{ $conciergePromoDiscount }}% OFF</span>
                    @endif
                </button>
                <a href="{{ localized_route('tours.customizer') }}" class="w-full rounded-2xl p-2.5 flex items-center justify-between text-start border border-slate-200 bg-slate-50 hover:bg-orange-50 text-slate-800 font-bold text-xs transition-colors min-h-[44px]" @click="$store.mobileNav.close()">
                    <span class="flex items-center gap-2">
                        <i class="bi bi-sliders text-primary"></i>
                        <span>{{ __('ui.nav.customizer') }}</span>
                    </span>
                    <i class="bi bi-chevron-right text-slate-500 text-[10px] rtl:rotate-180"></i>
                </a>
            </div>

            <!-- Navigation Links List (≥44px Touch Targets, Dynamic from CMS) -->
            <nav class="space-y-1">
                @if($headerMenuItems->count() > 0)
                    @foreach($headerMenuItems as $mItem)
                        @php
                            $isToursItem = ($mItem->route_name === 'tours.index' || $mItem->url === '/tours' || str_ends_with(rtrim($mItem->url, '/'), '/tours'));
                            $isActive = false;
                            if (!empty($mItem->route_name)) {
                                $isActive = request()->routeIs($mItem->route_name, 'locale.' . $mItem->route_name)
                                    || ($isToursItem && request()->routeIs('tours.*', 'locale.tours.*'))
                                    || (str_starts_with($mItem->route_name, 'blog') && request()->routeIs('blog.*', 'locale.blog.*'));
                            } elseif (!empty($mItem->url)) {
                                $cleanPath = trim($mItem->url, '/');
                                $isActive = request()->is($cleanPath, $cleanPath . '/*', '{locale}/' . $cleanPath, '{locale}/' . $cleanPath . '/*');
                            }
                        @endphp
                        <a class="block px-3.5 py-3 rounded-xl text-sm font-bold transition-all min-h-[44px] flex items-center {{ $isActive ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}" href="{{ $mItem->resolved_url }}" target="{{ $mItem->target ?? '_self' }}" @click="$store.mobileNav.close()">{{ $mItem->label }}</a>
                    @endforeach
                @else
                    <a class="block px-3.5 py-3 rounded-xl text-sm font-bold transition-all min-h-[44px] flex items-center {{ request()->routeIs('tours.*', 'locale.tours.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}" href="{{ localized_route('tours.index') }}" @click="$store.mobileNav.close()">{{ __('ui.nav.tours_short') ?? __('ui.nav.tours') }}</a>
                    <a class="block px-3.5 py-3 rounded-xl text-sm font-bold transition-all min-h-[44px] flex items-center {{ request()->routeIs('about', 'locale.about') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}" href="{{ localized_route('about') }}" @click="$store.mobileNav.close()">{{ __('ui.nav.about_short') ?? __('ui.nav.about') }}</a>
                    <a class="block px-3.5 py-3 rounded-xl text-sm font-bold transition-all min-h-[44px] flex items-center {{ request()->routeIs('blog.*', 'locale.blog.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}" href="{{ localized_route('blog.index') }}" @click="$store.mobileNav.close()">{{ __('ui.nav.blogs') ?? __('ui.nav.blog') }}</a>
                    <a class="block px-3.5 py-3 rounded-xl text-sm font-bold transition-all min-h-[44px] flex items-center {{ request()->routeIs('faq', 'locale.faq') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}" href="{{ localized_route('faq') }}" @click="$store.mobileNav.close()">{{ __('ui.nav.faq') }}</a>
                    <a class="block px-3.5 py-3 rounded-xl text-sm font-bold transition-all min-h-[44px] flex items-center {{ request()->routeIs('contact', 'locale.contact') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}" href="{{ localized_route('contact') }}" @click="$store.mobileNav.close()">{{ __('ui.nav.contact') }}</a>
                @endif
            </nav>
        </div>

        <!-- Drawer Bottom Direct Contact (Safe Area Inset Aware) -->
        <div class="pt-4 border-t border-slate-200 space-y-2" style="padding-bottom: max(1.25rem, env(safe-area-inset-bottom, 1.25rem));">
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}" target="_blank" rel="noopener" class="w-full h-11 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-xs transition-colors btn-whatsapp" data-action="whatsapp" @click="$store.mobileNav.close();">
                <i class="bi bi-whatsapp text-sm"></i><span>{{ __('ui.common.whatsapp_chat') }}</span>
            </a>
            <button type="button" class="w-full h-11 rounded-full bg-primary hover:bg-primary-dark text-white font-extrabold text-xs flex items-center justify-center gap-2 shadow-xs transition-colors cursor-pointer" @click="$store.mobileNav.close(); $store.modal.open('booking');">
                <i class="bi bi-calendar-check text-sm"></i><span>{{ __('ui.common.book_online') }}</span>
            </button>
        </div>
    </div>

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
                    <p class="text-slate-300 text-xs sm:text-sm leading-relaxed max-w-sm mb-4">
                        {{ __('ui.footer.about_text') }}
                    </p>
                    <div class="flex items-center gap-3">
                        <a href="https://instagram.com/dunesdiscoverytourism" target="_blank" rel="noopener" class="w-9 h-9 rounded-full border border-slate-700 bg-slate-900/60 hover:bg-slate-800 hover:border-slate-500 text-slate-300 hover:text-white flex items-center justify-center transition-colors text-sm" aria-label="Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="https://facebook.com/dunesdiscoverytourism" target="_blank" rel="noopener" class="w-9 h-9 rounded-full border border-slate-700 bg-slate-900/60 hover:bg-slate-800 hover:border-slate-500 text-slate-300 hover:text-white flex items-center justify-center transition-colors text-sm" aria-label="Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-full border border-slate-700 bg-slate-900/60 hover:bg-emerald-600 hover:border-emerald-500 text-slate-300 hover:text-white flex items-center justify-center transition-colors text-sm" aria-label="{{ __('ui.common.whatsapp') }}">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                    <div class="mt-4">
                        <button type="button" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-xs font-bold text-white bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 shadow-sm hover:shadow-md transition-all cursor-pointer" @click="$store.modal.open('safari-matcher')" aria-label="{{ __('ui.footer.safari_concierge_btn') }}">
                            <i class="bi bi-compass text-white"></i>
                            <span>{{ __('ui.footer.safari_concierge_btn') }}</span>
                            @if($conciergePromoActive)
                            <span class="px-1.5 py-0.5 rounded-full bg-black/30 text-amber-200 text-[9px] font-black">{{ $conciergePromoDiscount }}% OFF</span>
                            @endif
                        </button>
                    </div>
                </div>

                @php
                    $cmsSvc = app(\App\Services\CmsContentService::class);
                    $footerSafaris = $cmsSvc->getMenuItems('footer_desert_safaris');
                    $footerTours = $cmsSvc->getMenuItems('footer_tours_cruises');
                    $footerPolicies = $cmsSvc->getMenuItems('footer_trust_policies');
                @endphp

                <!-- Col 2: Desert Safaris -->
                <div class="col-span-1 lg:col-span-2">
                    <h3 class="text-xs font-black uppercase tracking-wider text-amber-400 mb-3.5">{{ __('ui.footer.desert_safaris') }}</h3>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        @if($footerSafaris->count() > 0)
                            @foreach($footerSafaris as $fItem)
                            <li><a href="{{ $fItem->resolved_url }}" target="{{ $fItem->target }}" class="text-slate-300 hover:text-white transition-colors">{{ $fItem->label }}</a></li>
                            @endforeach
                        @else
                            <li><a href="{{ localized_route('tours.show', 'evening-desert-safari-dubai') }}" class="text-slate-300 hover:text-white transition-colors">{{ __('ui.footer.evening_safari') }}</a></li>
                            <li><a href="{{ localized_route('tours.show', 'morning-desert-safari-dubai') }}" class="text-slate-300 hover:text-white transition-colors">{{ __('ui.footer.morning_safari') }}</a></li>
                            <li><a href="{{ localized_route('tours.show', 'overnight-desert-safari-dubai') }}" class="text-slate-300 hover:text-white transition-colors">{{ __('ui.footer.overnight_safari') }}</a></li>
                            <li><a href="{{ localized_route('tours.show', 'desert-safari-quad-biking-dubai') }}" class="text-slate-300 hover:text-white transition-colors">{{ __('ui.footer.quad_biking') }}</a></li>
                            <li><a href="{{ localized_route('tours.show', 'evening-desert-safari-dubai') }}" class="text-slate-300 hover:text-white transition-colors">{{ __('ui.footer.vip_safari') }}</a></li>
                        @endif
                    </ul>
                </div>

                <!-- Col 3: Tours & Cruises -->
                <div class="col-span-1 lg:col-span-2">
                    <h3 class="text-xs font-black uppercase tracking-wider text-amber-400 mb-3.5">{{ __('ui.footer.tours_cruises') }}</h3>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        @if($footerTours->count() > 0)
                            @foreach($footerTours as $fItem)
                            <li><a href="{{ $fItem->resolved_url }}" target="{{ $fItem->target }}" class="text-slate-300 hover:text-white transition-colors">{{ $fItem->label }}</a></li>
                            @endforeach
                        @else
                            <li><a href="{{ localized_route('tours.show', 'dubai-city-tour') }}" class="text-slate-300 hover:text-white transition-colors">{{ __('ui.footer.dubai_city_tour') }}</a></li>
                            <li><a href="{{ localized_route('tours.show', 'abu-dhabi-city-tour-from-dubai') }}" class="text-slate-300 hover:text-white transition-colors">{{ __('ui.footer.abu_dhabi_tour') }}</a></li>
                            <li><a href="{{ localized_route('tours.show', 'dhow-cruise-catamaran-cruise-dinner-dubai') }}" class="text-slate-300 hover:text-white transition-colors">{{ __('ui.footer.marina_cruise') }}</a></li>
                            <li><a href="{{ localized_route('rate-card') }}" class="text-slate-300 hover:text-white transition-colors inline-flex items-center gap-1.5"><i class="bi bi-file-earmark-pdf text-amber-400"></i>{{ __('ui.footer.rate_card_pdf') }}</a></li>
                            <li><a href="{{ localized_route('gallery.index') }}" class="text-slate-300 hover:text-white transition-colors">{{ __('ui.nav.gallery') }}</a></li>
                            <li><a href="{{ localized_route('blog.index') }}" class="text-slate-300 hover:text-white transition-colors">{{ __('ui.nav.blog') }}</a></li>
                            <li><a href="{{ localized_route('sunset.guide') }}" class="text-slate-300 hover:text-white transition-colors">Dubai Desert Sunset Times</a></li>
                            <li><a href="{{ localized_route('press.kit') }}" class="text-slate-300 hover:text-white transition-colors">Press Kit &amp; Partner Badges</a></li>
                        @endif
                    </ul>
                </div>

                <!-- Col 4: Trust & Policies -->
                <div class="col-span-1 lg:col-span-2">
                    <h3 class="text-xs font-black uppercase tracking-wider text-amber-400 mb-3.5">{{ __('ui.footer.trust_policies') }}</h3>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        @if($footerPolicies->count() > 0)
                            @foreach($footerPolicies as $fItem)
                            <li><a href="{{ $fItem->resolved_url }}" target="{{ $fItem->target }}" class="text-slate-300 hover:text-white transition-colors">{{ $fItem->label }}</a></li>
                            @endforeach
                        @else
                            <li><a href="{{ localized_route('terms') }}" class="text-slate-300 hover:text-white transition-colors">{{ __('ui.footer.terms') }}</a></li>
                            <li><a href="{{ localized_route('privacy') }}" class="text-slate-300 hover:text-white transition-colors">{{ __('ui.footer.privacy') }}</a></li>
                            <li><a href="{{ localized_route('cookies') }}" class="text-slate-300 hover:text-white transition-colors">{{ __('ui.footer.cookies') }}</a></li>
                            <li><a href="{{ localized_route('cancellation') }}" class="text-slate-300 hover:text-white transition-colors">{{ __('ui.footer.refund') }}</a></li>
                            <li><a href="{{ localized_route('payment.security') }}" class="text-slate-300 hover:text-white transition-colors">{{ __('ui.footer.payment_security') }}</a></li>
                        @endif
                    </ul>
                </div>

                <!-- Col 5: Contact & Help -->
                <div class="col-span-1 sm:col-span-1 lg:col-span-2">
                    <h3 class="text-xs font-black uppercase tracking-wider text-amber-400 mb-3.5">{{ __('ui.footer.contact_help') }}</h3>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><a href="tel:{{ preg_replace('/[^0-9+]/','',$phone) }}" class="text-slate-300 hover:text-white transition-colors inline-flex items-center gap-2"><i class="bi bi-telephone text-primary"></i><span>{{ $phone }}</span></a></li>
                        <li><a href="mailto:{{ $email }}" class="text-slate-300 hover:text-white transition-colors inline-flex items-center gap-2"><i class="bi bi-envelope text-primary"></i><span class="break-all">{{ $email }}</span></a></li>
                        <li><a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}" target="_blank" rel="noopener" class="text-slate-300 hover:text-white transition-colors inline-flex items-center gap-2 btn-whatsapp cursor-pointer" data-action="whatsapp"><i class="bi bi-whatsapp text-emerald-400"></i><span>24/7 WhatsApp</span></a></li>
                        <li class="text-slate-300 inline-flex items-start gap-2"><i class="bi bi-geo-alt text-primary shrink-0 mt-0.5"></i><span>{{ $settings['site_address'] ?? 'Dubai, United Arab Emirates' }}</span></li>
                    </ul>

                    <div class="flex flex-wrap gap-2 mt-4">
                        <a href="{{ $settings['social_tripadvisor'] ?? 'https://www.tripadvisor.com/Attraction_Review-g295424-d29026644-Reviews-Dunes_Discovery-Dubai_Emirate_of_Dubai.html' }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 p-1.5 px-2.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition-colors">
                            <img src="{{ asset('images/tripadvisor-logo-circle-owl-icon-black-green.png') }}" alt="TripAdvisor" width="20" height="20" loading="lazy" class="w-5 h-5 object-contain">
                            <div>
                                <div class="text-white font-bold text-xs leading-none">4.9</div>
                                <div class="text-amber-400 text-[9px] flex gap-0.5 mt-0.5">
                                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                                </div>
                            </div>
                        </a>
                        <a href="{{ $settings['social_google'] ?? 'https://search.google.com/local/writereview?placeid=ChIJbWsIEIVEdEER4uHEhb2dbcQ' }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 p-1.5 px-2.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition-colors">
                            <img src="{{ asset('images/Google-G.avif') }}" alt="Google" width="20" height="20" loading="lazy" class="w-5 h-5 object-contain">
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
                <div class="flex flex-col lg:flex-row items-center justify-between gap-4 text-center lg:text-start">
                    <div>
                        <p class="text-slate-400 text-xs mb-1">{{ __('ui.footer.license_line', ['year' => date('Y'), 'name' => $settings['site_name'] ?? 'Dunes Discovery Tourism L.L.C.', 'license' => $settings['company_license_number'] ?? '1430583']) }}</p>
                        <div class="flex flex-wrap justify-center lg:justify-start items-center gap-x-3.5 gap-y-2 text-xs text-slate-300">
                            <a href="{{ localized_route('terms') }}" class="hover:text-white transition-colors py-1">{{ __('ui.footer.terms') }}</a>
                            <span class="text-slate-500">&bull;</span>
                            <a href="{{ localized_route('privacy') }}" class="hover:text-white transition-colors py-1">{{ __('ui.footer.privacy') }}</a>
                            <span class="text-slate-500">&bull;</span>
                            <a href="{{ localized_route('cookies') }}" class="hover:text-white transition-colors py-1">{{ __('ui.footer.cookies') }}</a>
                            <span class="text-slate-500">&bull;</span>
                            <a href="{{ localized_route('cancellation') }}" class="hover:text-white transition-colors py-1">{{ __('ui.footer.refund_100') }}</a>
                            <span class="text-slate-500">&bull;</span>
                            <a href="{{ localized_route('payment.security') }}" class="hover:text-white transition-colors py-1">{{ __('ui.footer.payment_security') }}</a>
                            <span class="text-slate-500">&bull;</span>
                            <a href="{{ localized_route('safety.waiver') }}" class="hover:text-white transition-colors py-1">{{ __('ui.footer.safety_waiver') }}</a>
                            <span class="text-slate-500">&bull;</span>
                            <a href="{{ localized_route('ai.editorial') }}" class="hover:text-white transition-colors py-1">{{ __('ui.footer.ai_editorial') }}</a>
                            <span class="text-slate-500">&bull;</span>
                            <a href="{{ localized_route('responsible.tourism') }}" class="hover:text-white transition-colors py-1">{{ __('ui.footer.responsible_tourism') }}</a>
                        </div>
                    </div>
                    <div class="inline-flex items-center gap-2 bg-slate-900 border border-slate-800 px-4 py-2.5 rounded-2xl lg:rounded-full flex-wrap justify-center">
                        <span class="text-slate-300 text-xs inline-flex items-center gap-1 font-semibold"><i class="bi bi-shield-lock-fill text-emerald-400"></i>{{ __('ui.footer.secure_checkout') }}</span>
                        <span class="bg-white rounded px-2 py-0.5 inline-flex items-center justify-center shadow-2xs h-6"><img src="{{ asset('images/visa-card.svg') }}" alt="Visa" width="32" height="20" class="h-3.5 w-auto object-contain"></span>
                        <span class="bg-white rounded px-2 py-0.5 inline-flex items-center justify-center shadow-2xs h-6"><img src="{{ asset('images/mastercard.svg') }}" alt="Mastercard" width="28" height="20" class="h-3.5 w-auto object-contain"></span>
                        <span class="bg-white rounded px-2 py-0.5 inline-flex items-center justify-center shadow-2xs h-6"><img src="{{ asset('images/americanexpress.svg') }}" alt="American Express" width="28" height="20" class="h-3.5 w-auto object-contain"></span>
                        <span class="bg-white rounded px-2 py-0.5 inline-flex items-center justify-center shadow-2xs h-6"><img src="{{ asset('images/applepay.svg') }}" alt="Apple Pay" width="32" height="20" class="h-3.5 w-auto object-contain"></span>
                        <span class="bg-white rounded px-2 py-0.5 inline-flex items-center justify-center shadow-2xs h-6"><img src="{{ asset('images/googlepay.svg') }}" alt="Google Pay" width="32" height="20" class="h-3.5 w-auto object-contain"></span>
                        <span class="bg-white rounded px-2 py-0.5 inline-flex items-center justify-center shadow-2xs h-6"><img src="{{ asset('images/ziina-icon.png') }}" alt="Ziina Payment Gateway" width="18" height="18" class="h-3.5 w-auto object-contain"></span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    @include('partials.booking-modal')
    @include('partials.welcome-offer-modal')
    @if(($settings['social_proof_enabled'] ?? '1') === '1')
        @include('partials.social-proof')
    @endif
    @include('partials.comparison-drawer')
    @include('partials.search-modal')
    @if(($settings['safari_matcher_enabled'] ?? '1') === '1')
        @include('partials.safari-matcher-modal')
    @endif
    @if(($settings['exit_intent_promo_active'] ?? '0') === '1')
        @include('partials.exit-intent-modal')
    @endif
    @include('partials.custom-safari-modal')
    @include('partials.whatsapp-modal')

    <!-- Global Toast Container for App.toast notifications (Elevated on mobile to prevent blocking sticky bottom bars) -->
    <div id="toastContainer" class="toast-container fixed top-[calc(var(--header-h,72px)+0.75rem)] sm:top-auto sm:bottom-5 end-4 sm:end-5 z-[9999] flex flex-col gap-2.5 max-w-sm w-[calc(100%-2rem)] sm:w-full pointer-events-none" aria-live="polite" aria-atomic="true"></div>

    <script>
        window.DunesRates = {
            'AED': 1.0,
            'USD': {{ (float) ($settings['currency_rate_usd'] ?? 0.2723) }},
            'EUR': {{ (float) ($settings['currency_rate_eur'] ?? 0.2510) }},
            'GBP': {{ (float) ($settings['currency_rate_gbp'] ?? 0.2150) }},
            'SAR': {{ (float) ($settings['currency_rate_sar'] ?? 1.0210) }},
            'INR': {{ (float) ($settings['currency_rate_inr'] ?? 22.85) }}
        };
        window.DunesConfig = {
            email_verification_required: {{ ($settings['email_verification_required'] ?? '1') === '1' ? 'true' : 'false' }},
            site_phone: @json($settings['site_phone'] ?? '+971 50 245 6056'),
            site_whatsapp: @json($settings['site_whatsapp'] ?? '971502456056')
        };
    </script>

    <!-- Scripts -->
    <script src="{{ asset('assets/vendor/intl-tel-input/26.0.6/build/intlTelInput.min.js') }}"></script>

    @stack('scripts')
</body>
</html>
