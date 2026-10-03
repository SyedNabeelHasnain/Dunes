@extends('layouts.app')

@section('content')
@push('preloads')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@graph": [
    {
      "@type": "AboutPage",
      "@id": "{{ $canonical }}#webpage",
      "url": "{{ $canonical }}",
      "name": {!! json_encode($pageTitle) !!},
      "description": {!! json_encode($pageDesc) !!},
      "inLanguage": "{{ $currentLocale ?? app()->getLocale() ?? 'en' }}",
      "breadcrumb": {
        "@type": "BreadcrumbList",
        "itemListElement": [
          {
            "@type": "ListItem",
            "position": 1,
            "name": "{{ __('ui.nav.home') ?? 'Home' }}",
            "item": "{{ localized_route('home') }}"
          },
          {
            "@type": "ListItem",
            "position": 2,
            "name": "{{ __('ui.nav.about') ?? 'About Us' }}",
            "item": "{{ $canonical }}"
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
<section class="hero-subpage bg-slate-950 text-white relative overflow-hidden" style="margin-top: calc(-1 * var(--header-h, 72px)); padding-top: calc(var(--header-h, 72px) + 2.25rem); padding-bottom: 3rem;">
    <div class="absolute inset-0 w-full h-full bg-[radial-gradient(ellipse_at_15%_20%,rgba(246,144,68,0.18)_0%,transparent_60%)]"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <nav aria-label="breadcrumb">
            <ol class="flex items-center gap-2 text-xs sm:text-sm text-white/70 mb-4">
                <li><a href="{{ localized_route('home') }}" class="hover:text-white transition-colors">{{ __('ui.nav.home') }}</a></li>
                <li><span class="text-white/40">/</span></li>
                <li class="text-white font-semibold" aria-current="page">{{ __('ui.nav.about') }}</li>
            </ol>
        </nav>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <div class="flex flex-wrap gap-2 mb-3">
                    <span class="glass-dark text-white rounded-full px-3.5 py-1 text-xs font-semibold inline-flex items-center gap-1.5 shadow-xs">
                        <i class="bi bi-calendar3 text-primary"></i>{{ __('ui.about.trusted_since') }}
                    </span>
                    <span class="bg-emerald-600/90 rounded-full px-3.5 py-1 text-xs font-semibold text-white inline-flex items-center gap-1.5 shadow-xs">
                        <i class="bi bi-patch-check-fill text-emerald-200"></i>{{ __('ui.about.licensed_operator') }}
                    </span>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-2">{{ __('ui.about.title') }}</h1>
                <p class="text-sm sm:text-base text-white/80 max-w-2xl leading-relaxed">{{ __('ui.about.subtitle') }}</p>
            </div>
            <div class="hidden lg:block shrink-0">
                <span class="bg-primary/20 text-primary border border-primary/40 px-4 py-2 rounded-full font-bold text-sm inline-flex items-center gap-1.5">
                    <i class="bi bi-star-fill text-amber-400"></i>{{ __('ui.about.happy_guests') }}
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
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">{{ __('ui.trust_strip.dtcm_licensed') }}</div>
                    <div class="text-slate-500 text-[11px]">{{ __('ui.trust_strip.dtcm_desc') }}</div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <i class="bi bi-arrow-repeat text-emerald-600 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">{{ __('ui.trust_strip.free_cancel') }}</div>
                    <div class="text-slate-500 text-[11px]">{{ __('ui.trust_strip.free_cancel_desc') }}</div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <i class="bi bi-award-fill text-amber-500 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">{{ __('ui.trust_strip.halal_food') }}</div>
                    <div class="text-slate-500 text-[11px]">{{ __('ui.trust_strip.halal_food_desc') }}</div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <i class="bi bi-truck text-cyan-600 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">{{ __('ui.trust_strip.vehicles_fleet') }}</div>
                    <div class="text-slate-500 text-[11px]">{{ __('ui.trust_strip.vehicles_fleet_desc') }}</div>
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
                    <img src="{{ asset('images/dubai-desert-safari-tour-dune-discovery-tourism.avif') }}" alt="Dunes Discovery Tourism Desert Safari Experience Dubai" width="800" height="600" loading="lazy" class="w-full rounded-2xl shadow-xl object-cover" onerror="this.src='https://placehold.co/800x600/F58F43/white?text=Our+Story'">
                    <div class="hidden sm:block absolute -bottom-6 -right-6 bg-primary text-white p-5 rounded-2xl shadow-2xl">
                        <div class="text-2xl font-black mb-0.5">{{ __('ui.about_story.years_exp') }}</div>
                        <span class="text-xs text-white/80 block">{{ __('ui.about_story.years_sub') }}</span>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-6">
                <div class="lg:pl-6">
                    <div class="inline-flex items-center gap-2 mb-3">
                        <span class="bg-primary/10 text-primary px-3.5 py-1 rounded-full text-xs font-bold">{{ __('ui.about_story.badge') }}</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-4 tracking-tight leading-tight">{{ __('ui.about_story.title') }}</h2>
                    <p class="text-slate-700 text-base sm:text-lg mb-4 leading-relaxed font-medium">{{ __('ui.about_story.p1') }}</p>
                    <p class="text-slate-600 text-sm sm:text-base mb-6 leading-relaxed">{{ __('ui.about_story.p2') }}</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 text-sm">
                                <i class="bi bi-check-lg font-bold"></i>
                            </div>
                            <span class="font-bold text-slate-800 text-xs sm:text-sm">{{ __('ui.about_story.licensed_insured') }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 text-sm">
                                <i class="bi bi-check-lg font-bold"></i>
                            </div>
                            <span class="font-bold text-slate-800 text-xs sm:text-sm">{{ __('ui.about_story.modern_fleet') }}</span>
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
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-3 tracking-tight">{{ __('ui.why_choose_us.title') }}</h2>
            <p class="text-slate-600 text-base sm:text-lg max-w-xl mx-auto leading-relaxed">{{ __('ui.why_choose_us.subtitle_about') }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-lg transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xl mb-4">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">{{ __('ui.why_choose_us.licensed_insured') }}</h3>
                <p class="text-slate-600 text-sm leading-relaxed">{{ __('ui.why_choose_us.licensed_insured_desc') }}</p>
            </div>
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-lg transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xl mb-4">
                    <i class="bi bi-people"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">{{ __('ui.why_choose_us.expert_team') }}</h3>
                <p class="text-slate-600 text-sm leading-relaxed">{{ __('ui.why_choose_us.expert_team_desc') }}</p>
            </div>
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-lg transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xl mb-4">
                    <i class="bi bi-trophy"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">{{ __('ui.why_choose_us.award_winning') }}</h3>
                <p class="text-slate-600 text-sm leading-relaxed">{{ __('ui.why_choose_us.award_winning_desc') }}</p>
            </div>
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-lg transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xl mb-4">
                    <i class="bi bi-truck"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">{{ __('ui.why_choose_us.modern_fleet') }}</h3>
                <p class="text-slate-600 text-sm leading-relaxed">{{ __('ui.why_choose_us.modern_fleet_desc') }}</p>
            </div>
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-lg transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xl mb-4">
                    <i class="bi bi-heart"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">{{ __('ui.why_choose_us.guest_first') }}</h3>
                <p class="text-slate-600 text-sm leading-relaxed">{{ __('ui.why_choose_us.guest_first_desc') }}</p>
            </div>
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-lg transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xl mb-4">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">{{ __('ui.why_choose_us.best_value') }}</h3>
                <p class="text-slate-600 text-sm leading-relaxed">{{ __('ui.why_choose_us.best_value_desc') }}</p>
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
                <div class="text-slate-300 text-xs">{{ __('ui.trust_strip.no_1_safari') }}</div>
            </div>
            <div class="p-2">
                <i class="bi bi-shield-check text-primary text-2xl mb-1.5 block"></i>
                <div class="text-xl font-bold text-white mb-0.5">100%</div>
                <div class="text-slate-300 text-xs">{{ __('ui.trust_strip.secure_pay') }}</div>
            </div>
            <div class="p-2">
                <i class="bi bi-clock-history text-primary text-2xl mb-1.5 block"></i>
                <div class="text-xl font-bold text-white mb-0.5">Fast</div>
                <div class="text-slate-300 text-xs">{{ __('ui.trust_strip.fast_booking') }}</div>
            </div>
            <div class="p-2">
                <i class="bi bi-truck text-primary text-2xl mb-1.5 block"></i>
                <div class="text-xl font-bold text-white mb-0.5">25+</div>
                <div class="text-slate-300 text-xs">{{ __('ui.trust_strip.vehicles_fleet') }}</div>
            </div>
            <div class="p-2">
                <i class="bi bi-geo-alt text-primary text-2xl mb-1.5 block"></i>
                <div class="text-xl font-bold text-white mb-0.5">Local</div>
                <div class="text-slate-300 text-xs">{{ __('ui.why_choose_us.expert_team') }}</div>
            </div>
            <div class="p-2">
                <i class="bi bi-star text-primary text-2xl mb-1.5 block"></i>
                <div class="text-xl font-bold text-white mb-0.5">Best</div>
                <div class="text-slate-300 text-xs">{{ __('ui.why_choose_us.best_price') }}</div>
            </div>
        </div>
    </div>
</section>
@endsection
