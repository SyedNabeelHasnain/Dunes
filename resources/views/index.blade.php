@extends('layouts.app')

@section('content')
@php
if (!function_exists('renderReviewCardMarkup')) {
    function renderReviewCardMarkup($r) {
        $stars = '';
        for($i = 0; $i < 5; $i++) {
            $stars .= $i < floor($r->rating) ? '<i class="bi bi-star-fill text-amber-400"></i>' : '<i class="bi bi-star text-slate-300"></i>';
        }

        $isUgc = ($r->source === 'direct_ugc');
        $badgeText = $isUgc ? __('ui.reviews_section.verified_guest') : (($r->source == 'google') ? 'Google' : ucfirst($r->source));
        $sourceBadge = $isUgc 
            ? '<span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full px-2.5 py-0.5 text-xs font-bold"><i class="bi bi-patch-check-fill text-emerald-500"></i> ' . htmlspecialchars($badgeText) . '</span>'
            : '<span class="inline-flex items-center gap-1 bg-slate-100 text-slate-700 rounded-full px-2.5 py-0.5 text-xs font-medium">' . (($r->source == 'google') ? '<i class="bi bi-google text-blue-500"></i> Google' : '<i class="bi bi-star-fill text-emerald-500"></i> ' . ucfirst($r->source)) . '</span>';

        $url = !empty($r->review_url) ? $r->review_url : route('review.rate', ['ref' => 'guest']);
        $avatar = !empty($r->reviewer_avatar_url) ? (str_starts_with($r->reviewer_avatar_url, 'http') ? $r->reviewer_avatar_url : asset($r->reviewer_avatar_url)) : asset('images/avatar-default.svg');
        $fallbackAvatar = asset('images/avatar-default.svg');

        $photosHtml = '';
        if (!empty($r->photos) && is_array($r->photos) && count($r->photos) > 0) {
            $photosHtml .= '<div class="flex gap-1.5 mb-2 mt-1">';
            foreach (array_slice($r->photos, 0, 3) as $p) {
                $pUrl = asset($p);
                $photosHtml .= '<a href="' . htmlspecialchars($pUrl) . '" target="_blank" rel="noopener" class="rounded-lg overflow-hidden inline-block shadow-xs border border-slate-200 w-12 h-12 shrink-0"><img src="' . htmlspecialchars($pUrl) . '" alt="Traveler photo" class="w-full h-full object-cover" loading="lazy"></a>';
            }
            $photosHtml .= '</div>';
        }

        $actionText = $isUgc ? __('ui.reviews_section.submit_review') : __('ui.common.view_details');

        return '
        <div class="review-card h-full flex flex-col text-left rtl:text-right">
            <div class="flex justify-between items-center mb-3">
                <div class="flex items-center gap-2 min-w-0">
                    <img src="' . htmlspecialchars($avatar) . '" alt="' . htmlspecialchars($r->reviewer_name) . '" class="w-10 h-10 rounded-full object-cover shrink-0" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src=\'' . $fallbackAvatar . '\'">
                    <div class="min-w-0">
                        <div class="font-bold text-slate-900 text-sm truncate">' . htmlspecialchars($r->reviewer_name) . '</div>
                        <div class="text-slate-500 text-xs">' . ($r->published_date ? $r->published_date->format('M Y') : '') . '</div>
                    </div>
                </div>
                <div class="flex gap-0.5 text-xs shrink-0">' . $stars . '</div>
            </div>
            ' . ($r->review_title ? '<h3 class="text-sm font-bold mb-1.5 text-slate-900 line-clamp-1">' . htmlspecialchars($r->review_title) . '</h3>' : '') . '
            <p class="text-slate-600 text-sm mb-2 flex-grow line-clamp-3 leading-relaxed">"' . htmlspecialchars($r->review_text) . '"</p>
            ' . $photosHtml . '
            <div class="flex justify-between items-center mt-auto pt-3 border-t border-slate-200">
                ' . $sourceBadge . '
                <a href="' . htmlspecialchars($url) . '" ' . ($isUgc ? '' : 'target="_blank" rel="noopener"') . ' class="text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-primary hover:text-white border border-slate-300 hover:border-primary rounded-full px-3 py-1 transition-colors shadow-2xs">' . htmlspecialchars($actionText) . '</a>
            </div>
        </div>';
    }
}
@endphp

@push('preloads')
<link rel="preload" as="image" href="{{ asset('images/desert-safari-poster.avif') }}" fetchpriority="high">

<!-- Homepage-Specific Connected Schema Graph: VideoObject and FAQPage -->
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@graph": [
    {
      "@type": "VideoObject",
      "@id": "{{ url('/') }}#video",
      "name": "Dubai Desert Safari Experience - Dunes Discovery Tourism",
      "description": "Experience thrilling dune bashing across the Lahbab Red Dunes, sandboarding, 1000cc dune buggy rentals, and 5-star live BBQ dinner under the desert stars.",
      "thumbnailUrl": ["{{ asset('images/desert-safari-poster.avif') }}"],
      "uploadDate": "2026-01-01T00:00:00+04:00",
      "contentUrl": "{{ asset('images/desert-safar-dubai-tour-short-dune-discovery-tourism.mp4') }}",
      "publisher": {
        "@id": "{{ url('/') }}#organization"
      }
    }
    @if(isset($faqs) && $faqs->count() > 0)
    ,
    {
      "@type": "FAQPage",
      "@id": "{{ $canonical }}#faq",
      "isPartOf": {
        "@id": "{{ $canonical }}#webpage"
      },
      "mainEntity": [
        @foreach($faqs as $fidx => $f)
        {
          "@type": "Question",
          "name": {!! json_encode($f->question) !!},
          "acceptedAnswer": {
            "@type": "Answer",
            "text": {!! json_encode($f->answer) !!}
          }
        }{{ $fidx < $faqs->count() - 1 ? ',' : '' }}
        @endforeach
      ]
    }
    @endif
  ]
}
</script>
@endpush

<!-- Modern Hero Section -->
<section class="hero-modern relative min-h-[92vh] flex items-center justify-center overflow-hidden pt-28 sm:pt-36 pb-16 sm:pb-20">
    <video class="hero-video absolute inset-0 w-full h-full object-cover -z-10" autoplay loop muted playsinline id="heroVideo" poster="{{ asset('images/desert-safari-poster.avif') }}" fetchpriority="high" aria-hidden="true">
        <track kind="captions" src="" label="English" srclang="en">
    </video>
    <div class="absolute inset-0 w-full h-full bg-gradient-to-b from-slate-950/75 via-slate-950/65 to-slate-950/90 -z-10"></div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center text-white py-6 sm:py-10">
        <div class="flex justify-center mb-2">
            @include('partials.sunset-weather-widget')
        </div>
        <div class="inline-flex items-center gap-2 rounded-full bg-slate-900/85 border border-white/20 text-white backdrop-blur-md px-4 py-1.5 mb-5 text-xs sm:text-sm shadow-lg">
            <i class="bi bi-star-fill text-amber-400"></i>
            <span class="font-semibold text-white">Rated 4.9/5 by 2,847+ Travelers</span>
        </div>
        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold mb-4 text-white tracking-tight leading-tight">
            Top-Rated <span class="text-gradient-primary">Dubai Desert Safari</span> Tours & Adventures
        </h1>
        <p class="text-base sm:text-lg lg:text-xl mb-8 mx-auto text-white/90 max-w-2xl font-light leading-relaxed">
            Experience thrilling dune bashing across the Lahbab Red Dunes, 1000cc dune buggy rentals, magical sunsets, authentic live BBQ dinner, and 5-star entertainment under the desert stars.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center items-center mb-10">
            <button type="button" class="btn-desert-animated text-base sm:text-lg font-bold rounded-full px-8 py-3.5 shadow-xl text-white inline-flex items-center justify-center gap-2 cursor-pointer w-full sm:w-auto" @click="$store.modal.open('booking')">
                <i class="bi bi-calendar-check text-lg"></i>
                <span>{{ __('ui.common.book_online_now') }}</span>
            </button>
            <button type="button" class="inline-flex items-center justify-center gap-2 text-base font-bold rounded-full px-6 py-3.5 border border-primary/60 text-primary bg-slate-900/60 backdrop-blur-md hover:bg-slate-900/80 transition-all cursor-pointer w-full sm:w-auto" @click="$store.modal.open('safari-matcher')">
                <i class="bi bi-compass text-amber-400 text-lg"></i>
                <span>{{ __('ui.home_concierge.quiz_btn') }}</span>
                <span class="bg-amber-400 text-slate-950 font-bold rounded-full px-2 py-0.5 text-[10px]">5% OFF</span>
            </button>
            <button type="button" class="btn-desert-animated-dark text-base font-bold rounded-full px-6 py-3.5 inline-flex items-center justify-center gap-2 cursor-pointer w-full sm:w-auto" data-action="open-booking" data-tour="1" data-tier="1" @click="$store.modal.open('booking', { tourId: 1, tierId: 1 })">
                <span class="font-bold text-white text-sm">{{ __('ui.common.starting_from') }}</span>
                <span class="text-xl font-black text-primary" data-aed="79">{{ __('ui.common.aed') }} 79</span>
            </button>
        </div>

        <div class="max-w-3xl mx-auto mb-8 bg-black/25 backdrop-blur-xs rounded-2xl p-4 sm:p-5 border border-white/10">
            <h2 class="text-base sm:text-lg font-bold text-white mb-1.5">Dubai Desert Safari with Luxury Land Cruiser Pick & Drop</h2>
            <p class="text-xs sm:text-sm text-white/80 leading-relaxed">Enjoy a premium desert safari experience with chauffeur-driven Luxury Land Cruiser hotel pickup and drop-off. Comfortable seating, professional drivers, and hassle-free transfers included with every booking.</p>
        </div>

        <div class="flex justify-center items-center gap-6 sm:gap-12 opacity-85 text-center">
            <div>
                <div class="text-2xl sm:text-3xl font-black text-white">10K+</div>
                <div class="uppercase font-semibold text-[10px] sm:text-xs tracking-wider text-slate-300">{{ __('ui.common.happy_guests') }}</div>
            </div>
            <div class="border-s border-e border-white/25 px-6 sm:px-12">
                <div class="text-2xl sm:text-3xl font-black text-white">4.9/5</div>
                <div class="uppercase font-semibold text-[10px] sm:text-xs tracking-wider text-slate-300">{{ __('ui.trust_strip.top_rated') }}</div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-black text-white">24/7</div>
                <div class="uppercase font-semibold text-[10px] sm:text-xs tracking-wider text-slate-300">{{ __('ui.trust_strip.support_247') }}</div>
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
                <i class="bi bi-shield-lock-fill text-cyan-600 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">{{ __('ui.trust_strip.secure_checkout') }}</div>
                    <div class="text-slate-500 text-[11px]">{{ __('ui.trust_strip.secure_checkout_desc') }}</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Bar Section -->
<div class="stats-bar bg-slate-950 py-5 shadow-lg relative z-20">
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
</div>

<!-- Category Silos Section: Explore Dubai by Adventure Type -->
<section class="py-12 sm:py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10 sm:mb-12">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-3 tracking-tight">
                {{ __('ui.home_categories.title') }} <span class="text-primary">{{ __('ui.home_categories.title_highlight') }}</span>
            </h2>
            <p class="text-slate-600 text-base sm:text-lg max-w-2xl mx-auto leading-relaxed">
                {{ __('ui.home_categories.subtitle') }}
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="group bg-slate-50 hover:bg-white rounded-2xl p-6 border border-slate-200 hover:border-primary/50 shadow-xs hover:shadow-lg transition-all duration-300 flex flex-col h-full hover:-translate-y-1">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-4 text-2xl group-hover:scale-110 transition-transform">
                    <i class="bi bi-sunset-fill"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">{{ __('ui.home_categories.evening_title') }}</h3>
                <p class="text-slate-600 text-sm mb-4 flex-grow leading-relaxed">{{ __('ui.home_categories.evening_desc') }}</p>
                <a href="{{ url('/evening-desert-safari-dubai') }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-primary hover:text-primary-hover transition-colors mt-auto">
                    <span>{{ __('ui.home_categories.evening_btn') }}</span>
                    <i class="bi bi-arrow-right rtl:rotate-180 group-hover:translate-x-1 rtl:group-hover:-translate-x-1 transition-transform"></i>
                </a>
            </div>

            <div class="group bg-slate-50 hover:bg-white rounded-2xl p-6 border border-slate-200 hover:border-primary/50 shadow-xs hover:shadow-lg transition-all duration-300 flex flex-col h-full hover:-translate-y-1">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-4 text-2xl group-hover:scale-110 transition-transform">
                    <i class="bi bi-speedometer2"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">{{ __('ui.home_categories.buggy_title') }}</h3>
                <p class="text-slate-600 text-sm mb-4 flex-grow leading-relaxed">{{ __('ui.home_categories.buggy_desc') }}</p>
                <a href="{{ url('/dune-buggy-rental-dubai') }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-primary hover:text-primary-hover transition-colors mt-auto">
                    <span>{{ __('ui.home_categories.buggy_btn') }}</span>
                    <i class="bi bi-arrow-right rtl:rotate-180 group-hover:translate-x-1 rtl:group-hover:-translate-x-1 transition-transform"></i>
                </a>
            </div>

            <div class="group bg-slate-50 hover:bg-white rounded-2xl p-6 border border-slate-200 hover:border-primary/50 shadow-xs hover:shadow-lg transition-all duration-300 flex flex-col h-full hover:-translate-y-1">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-4 text-2xl group-hover:scale-110 transition-transform">
                    <i class="bi bi-water"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">{{ __('ui.home_categories.cruise_title') }}</h3>
                <p class="text-slate-600 text-sm mb-4 flex-grow leading-relaxed">{{ __('ui.home_categories.cruise_desc') }}</p>
                <a href="{{ url('/dhow-cruise-catamaran-cruise-dinner-dubai') }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-primary hover:text-primary-hover transition-colors mt-auto">
                    <span>{{ __('ui.home_categories.cruise_btn') }}</span>
                    <i class="bi bi-arrow-right rtl:rotate-180 group-hover:translate-x-1 rtl:group-hover:-translate-x-1 transition-transform"></i>
                </a>
            </div>

            <div class="group bg-slate-50 hover:bg-white rounded-2xl p-6 border border-slate-200 hover:border-primary/50 shadow-xs hover:shadow-lg transition-all duration-300 flex flex-col h-full hover:-translate-y-1">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-4 text-2xl group-hover:scale-110 transition-transform">
                    <i class="bi bi-building"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">{{ __('ui.home_categories.city_title') }}</h3>
                <p class="text-slate-600 text-sm mb-4 flex-grow leading-relaxed">{{ __('ui.home_categories.city_desc') }}</p>
                <a href="{{ url('/abu-dhabi-city-tour-from-dubai') }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-primary hover:text-primary-hover transition-colors mt-auto">
                    <span>{{ __('ui.home_categories.city_btn') }}</span>
                    <i class="bi bi-arrow-right rtl:rotate-180 group-hover:translate-x-1 rtl:group-hover:-translate-x-1 transition-transform"></i>
                </a>
            </div>
        </div>

        <div class="mt-8 p-6 sm:p-8 rounded-2xl shadow-sm bg-gradient-to-r from-slate-900 via-slate-850 to-slate-900 border border-primary/30">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-6">
                <div class="text-center lg:text-left rtl:lg:text-right">
                    <div class="flex flex-wrap items-center justify-center lg:justify-start rtl:lg:justify-start gap-2 mb-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold bg-primary/20 text-primary border border-primary/40">
                            <i class="bi bi-compass"></i> {{ __('ui.home_concierge.badge') }}
                        </span>
                        <span class="bg-amber-400 text-slate-950 rounded-full px-2.5 py-0.5 text-xs font-bold">
                            {{ __('ui.home_concierge.discount_badge') }}
                        </span>
                    </div>
                    <h2 class="font-extrabold text-white text-xl sm:text-2xl mb-1.5">{{ __('ui.home_concierge.heading') }}</h2>
                    <p class="text-slate-300 text-sm">{{ __('ui.home_concierge.subheading') }}</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 shrink-0 w-full sm:w-auto">
                    <button type="button" class="btn-desert-animated text-sm font-bold rounded-full px-5 py-3 text-white inline-flex items-center justify-center gap-2 cursor-pointer shadow-md" @click="$store.modal.open('safari-matcher')">
                        <i class="bi bi-compass"></i> {{ __('ui.home_concierge.quiz_btn') }}
                    </button>
                    <a href="{{ route('tours.customizer') }}" class="border border-white/40 hover:border-white text-white text-sm font-bold rounded-full px-5 py-3 inline-flex items-center justify-center gap-2 transition-colors">
                        <i class="bi bi-sliders"></i> {{ __('ui.home_concierge.custom_btn') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Popular Tours Section -->
<section class="py-12 sm:py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10 sm:mb-12">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-3 tracking-tight">
                {{ __('ui.home_popular.title') }} <span class="text-primary">{{ __('ui.home_popular.title_highlight') }}</span>
            </h2>
            <p class="text-slate-600 text-base sm:text-lg max-w-xl mx-auto leading-relaxed">
                {{ __('ui.home_popular.subtitle') }}
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            @foreach($bestsellers as $t)
                @php
                    $minPrice = $t->tiers->min('pivot.price') ?? 0;
                    $category = $categories->firstWhere('id', $t->category_id);
                @endphp
                <article class="bg-white rounded-2xl overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 border border-slate-200 hover:border-slate-300 flex flex-col h-full group">
                    <a href="{{ route('tours.show', $t->slug) }}" class="flex flex-col h-full text-inherit">
                        <div class="relative overflow-hidden aspect-[16/10]">
                            <img src="{{ asset('images/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $t->thumb_image)) }}" width="400" height="250" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $t->name }} Dubai Desert Safari" loading="lazy">
                            @if($t->is_bestseller)
                            <span class="absolute top-3 start-3 bg-primary text-white text-xs font-bold px-3 py-1 rounded-full shadow-md inline-flex items-center gap-1">
                                <i class="bi bi-fire text-amber-300"></i>{{ __('ui.home_popular.bestseller') }}
                            </span>
                            @endif
                            <div class="absolute bottom-0 inset-x-0 p-3 bg-gradient-to-t from-black/70 via-black/30 to-transparent">
                                <span class="glass text-white text-xs font-semibold px-2.5 py-1 rounded-full inline-flex items-center gap-1">
                                    <i class="bi bi-tag-fill text-primary"></i>{{ $category ? $category->name : 'Tours' }}
                                </span>
                            </div>
                        </div>
                        <div class="p-5 flex flex-col flex-grow">
                            <div class="flex justify-between items-center text-xs mb-2">
                                <div class="text-slate-500 inline-flex items-center gap-1">
                                    <i class="bi bi-clock"></i>{{ $t->duration }}
                                </div>
                                <div class="text-amber-500 font-bold inline-flex items-center gap-1">
                                    <i class="bi bi-star-fill"></i>{{ $t->rating }}
                                </div>
                            </div>
                            <h3 class="text-base font-bold text-slate-900 mb-2 line-clamp-2 group-hover:text-primary transition-colors leading-snug">{{ $t->name }}</h3>
                            @php $homeBookings = (int)(($t->id * 3 + (int)date('j')) % 5 + 3); @endphp
                            <div class="inline-flex items-center gap-1.5 text-red-600 text-xs font-bold mb-3">
                                <i class="bi bi-fire text-red-500"></i>
                                <span>{{ __('ui.home_popular.booked_recent', ['count' => $homeBookings]) }}</span>
                            </div>
                            <div class="flex justify-between items-end mt-auto pt-3 border-t border-slate-200">
                                <div>
                                    <span class="block text-[10px] uppercase font-bold text-slate-500">{{ __('ui.home_popular.starting_from') }}</span>
                                    <span class="text-lg font-black text-primary" data-aed="{{ $minPrice }}">{{ __('ui.common.aed') }} {{ number_format($minPrice) }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" class="border border-slate-300 hover:border-primary text-slate-700 bg-white hover:text-primary text-xs font-semibold rounded-full px-2.5 py-1 transition-colors btn-toggle-compare inline-flex items-center gap-1 cursor-pointer" data-tour-id="{{ $t->id }}" onclick="event.preventDefault(); event.stopPropagation(); window.DunesCompare && window.DunesCompare.toggle(this);">
                                        <i class="bi bi-shuffle"></i> <span class="compare-btn-text">{{ __('ui.home_popular.compare') }}</span>
                                    </button>
                                    <span role="button" tabindex="0" class="btn-circle-whatsapp fab-whatsapp w-8 h-8 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center cursor-pointer shadow-xs transition-transform hover:scale-105" data-tour-name="{{ $t->name }}" aria-label="Book {{ $t->name }} via WhatsApp" onclick="event.preventDefault(); event.stopPropagation(); if(window.App && typeof window.App.openWhatsApp === 'function'){ window.App.openWhatsApp('{{ addslashes($t->name) }}'); }" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();event.stopPropagation();if(window.App&&typeof window.App.openWhatsApp==='function'){window.App.openWhatsApp('{{ addslashes($t->name) }}');}}">
                                        <i class="bi bi-whatsapp text-sm"></i>
                                    </span>
                                    <div class="w-8 h-8 rounded-full bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white flex items-center justify-center transition-colors">
                                        <i class="bi bi-arrow-right rtl:rotate-180 text-sm"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </article>
            @endforeach
        </div>

        <div class="text-center">
            <a href="{{ route('tours.index') }}" class="btn-desert-animated-dark text-base font-bold rounded-full px-8 py-3.5 inline-flex items-center gap-2 shadow-md">
                <span>{{ __('ui.home_popular.view_all') }}</span>
                <i class="bi bi-arrow-right rtl:rotate-180"></i>
            </a>
        </div>
    </div>
</section>

<!-- Interactive Safari Matcher Quiz -->
@include('partials.safari-matcher-quiz')

<!-- Guest Reviews Marquee Section -->
<section class="reviews-section bg-primary/5 py-12 sm:py-16 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8 text-center">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-3 tracking-tight">
            {{ __('ui.home_reviews.title') }} <span class="text-primary">{{ __('ui.home_reviews.title_highlight') }}</span>
        </h2>
        <p class="text-slate-600 text-base sm:text-lg">{{ __('ui.home_reviews.subtitle') }}</p>
    </div>

    @php
        $googleReviews = $reviews->where('source', 'google');
        $tripReviews = $reviews->where('source', 'tripadvisor');
    @endphp

    <div class="reviews-marquee mb-5">
        <div class="reviews-track flex gap-4">
            @foreach($googleReviews as $r)
                {!! renderReviewCardMarkup($r) !!}
            @endforeach
            @foreach($googleReviews as $r)
                {!! renderReviewCardMarkup($r) !!}
            @endforeach
        </div>
    </div>

    <div class="reviews-marquee reverse">
        <div class="reviews-track flex gap-4">
            @foreach($tripReviews as $r)
                {!! renderReviewCardMarkup($r) !!}
            @endforeach
            @foreach($tripReviews as $r)
                {!! renderReviewCardMarkup($r) !!}
            @endforeach
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-12 sm:py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10 sm:mb-12">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-3 tracking-tight">
                {{ __('ui.why_choose_us.title') }}
            </h2>
            <p class="text-slate-600 text-base sm:text-lg max-w-xl mx-auto leading-relaxed">
                {{ __('ui.why_choose_us.subtitle_home') }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-slate-50 hover:bg-white rounded-2xl p-6 sm:p-8 text-center border border-slate-200 hover:border-primary/40 shadow-xs hover:shadow-lg transition-all duration-300">
                <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-primary/10 text-primary flex items-center justify-center text-2xl">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h3 class="text-lg sm:text-xl font-bold text-slate-900 mb-2">{{ __('ui.why_choose_us.best_price') }}</h3>
                <p class="text-slate-600 text-sm leading-relaxed">{{ __('ui.why_choose_us.best_price_desc') }}</p>
            </div>
            <div class="bg-slate-50 hover:bg-white rounded-2xl p-6 sm:p-8 text-center border border-slate-200 hover:border-primary/40 shadow-xs hover:shadow-lg transition-all duration-300">
                <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-primary/10 text-primary flex items-center justify-center text-2xl">
                    <i class="bi bi-lightning-charge"></i>
                </div>
                <h3 class="text-lg sm:text-xl font-bold text-slate-900 mb-2">{{ __('ui.why_choose_us.instant_confirm') }}</h3>
                <p class="text-slate-600 text-sm leading-relaxed">{{ __('ui.why_choose_us.instant_confirm_desc') }}</p>
            </div>
            <div class="bg-slate-50 hover:bg-white rounded-2xl p-6 sm:p-8 text-center border border-slate-200 hover:border-primary/40 shadow-xs hover:shadow-lg transition-all duration-300">
                <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-primary/10 text-primary flex items-center justify-center text-2xl">
                    <i class="bi bi-calendar-x"></i>
                </div>
                <h3 class="text-lg sm:text-xl font-bold text-slate-900 mb-2">{{ __('ui.why_choose_us.free_cancel') }}</h3>
                <p class="text-slate-600 text-sm leading-relaxed">{{ __('ui.why_choose_us.free_cancel_desc') }}</p>
            </div>
        </div>
    </div>
</section>

<!-- GEO & AI Search Entity Knowledge Guide -->
<section class="py-12 sm:py-16 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10 sm:mb-12">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-3 tracking-tight">
                {{ __('ui.guide_section.title') }} <span class="text-primary">{{ __('ui.guide_section.title_highlight') }}</span>
            </h2>
            <p class="text-slate-600 text-base sm:text-lg max-w-2xl mx-auto leading-relaxed">
                {{ __('ui.guide_section.subtitle') }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-xs border-s-4 border-primary hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-lg shrink-0">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">{{ __('ui.guide_section.loc_title') }}</h3>
                </div>
                <p class="text-slate-600 text-sm leading-relaxed">{{ __('ui.guide_section.loc_desc') }}</p>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-xs border-s-4 border-primary hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-lg shrink-0">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">{{ __('ui.guide_section.fleet_title') }}</h3>
                </div>
                <p class="text-slate-600 text-sm leading-relaxed">{{ __('ui.guide_section.fleet_desc') }}</p>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-xs border-s-4 border-primary hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-lg shrink-0">
                        <i class="bi bi-cup-hot-fill"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">{{ __('ui.guide_section.dining_title') }}</h3>
                </div>
                <p class="text-slate-600 text-sm leading-relaxed">{{ __('ui.guide_section.dining_desc') }}</p>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-xs border-s-4 border-primary hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-lg shrink-0">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">{{ __('ui.guide_section.timing_title') }}</h3>
                </div>
                <p class="text-slate-600 text-sm leading-relaxed">{{ __('ui.guide_section.timing_desc') }}</p>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-xs border-s-4 border-primary hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-lg shrink-0">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">{{ __('ui.guide_section.family_title') }}</h3>
                </div>
                <p class="text-slate-600 text-sm leading-relaxed">{{ __('ui.guide_section.family_desc') }}</p>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-xs border-s-4 border-primary hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-lg shrink-0">
                        <i class="bi bi-patch-check-fill"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">{{ __('ui.guide_section.booking_title') }}</h3>
                </div>
                <p class="text-slate-600 text-sm leading-relaxed">{{ __('ui.guide_section.booking_desc') }}</p>
            </div>
        </div>
    </div>
</section>

<!-- FAQs Section -->
<section class="py-12 sm:py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10 sm:mb-12">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-3 tracking-tight">
                {{ __('ui.faq_section.common_questions') }}
            </h2>
            <p class="text-slate-600 text-base sm:text-lg">{{ __('ui.faq_section.subtitle') }}</p>
        </div>

        <div x-data="{ activeFaq: null }" class="max-w-3xl mx-auto space-y-3">
            @foreach($faqs as $index => $f)
            <div class="bg-slate-50 hover:bg-slate-50/80 rounded-2xl border border-slate-200 overflow-hidden transition-all duration-200">
                <button type="button" 
                        class="w-full text-left px-5 sm:px-6 py-4.5 font-bold text-slate-900 flex items-center justify-between gap-4 cursor-pointer"
                        @click="activeFaq = (activeFaq === {{ $index }} ? null : {{ $index }})">
                    <span class="text-sm sm:text-base">{{ $f->question }}</span>
                    <i class="bi bi-chevron-down transition-transform duration-300 text-slate-500 shrink-0"
                       :class="activeFaq === {{ $index }} ? 'rotate-180 text-primary' : ''"></i>
                </button>
                <div x-show="activeFaq === {{ $index }}" 
                     x-collapse 
                     x-cloak 
                     class="px-5 sm:px-6 pb-5 text-slate-600 text-sm leading-relaxed border-t border-slate-200 pt-3">
                    {{ $f->answer }}
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-10">
            <a href="{{ route('faq') }}" class="btn-desert-animated text-sm sm:text-base font-bold rounded-full px-8 py-3.5 inline-flex items-center gap-2 shadow-md">
                <span>{{ __('ui.faq_section.view_all') }}</span>
                <i class="bi bi-arrow-right rtl:rotate-180"></i>
            </a>
        </div>
    </div>
</section>

<!-- CTA booking banner -->
<section class="cta-section py-16 sm:py-24 relative bg-slate-950 text-white overflow-hidden border-t border-slate-800">
    <!-- Ambient Desert Glow Effects -->
    <div class="absolute -top-24 start-1/2 -translate-x-1/2 rtl:translate-x-1/2 w-[700px] h-[350px] bg-gradient-to-b from-orange-500/20 via-amber-500/10 to-transparent blur-3xl pointer-events-none -z-0"></div>
    <div class="absolute -bottom-24 end-10 w-96 h-96 bg-primary/10 rounded-full blur-3xl pointer-events-none -z-0"></div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-slate-900/90 border border-amber-400/30 text-amber-300 text-xs font-bold uppercase tracking-wider mb-5 shadow-lg">
            <i class="bi bi-patch-check-fill text-amber-400"></i> {{ __('ui.home_cta.license_badge') }}
        </div>
        <h2 class="text-3xl sm:text-5xl font-black mb-4 text-white tracking-tight leading-tight">
            {{ __('ui.home_cta.title_prefix') }} <span class="bg-gradient-to-r from-orange-400 via-amber-300 to-yellow-400 bg-clip-text text-transparent">{{ __('ui.home_cta.title_highlight') }}</span>?
        </h2>
        <p class="text-base sm:text-lg mb-8 text-slate-300 max-w-2xl mx-auto leading-relaxed font-normal">
            {{ __('ui.home_cta.subtitle') }}
        </p>
        <div class="flex flex-wrap justify-center gap-4">
            <button type="button" class="btn-desert-animated text-base sm:text-lg font-bold rounded-full px-8 py-3.5 shadow-xl inline-flex items-center gap-2 cursor-pointer text-white" @click="$store.modal.open('booking')">
                <i class="bi bi-calendar-check text-lg"></i>
                <span>{{ __('ui.home_cta.book_btn') }}</span>
            </button>
            @php $waNumClean = preg_replace('/[^0-9]/', '', $settings['whatsapp_phone'] ?? '971502456056'); @endphp
            <a href="https://wa.me/{{ $waNumClean }}?text={{ urlencode('Hi Dunes Discovery Tourism! I would like to inquire about booking a desert safari.') }}" target="_blank" rel="noopener" class="btn-whatsapp-animated border border-slate-700 hover:border-white bg-slate-900/80 hover:bg-slate-900 text-white text-base font-bold rounded-full px-6 py-3.5 inline-flex items-center gap-2 shadow-md transition-all cursor-pointer" data-action="whatsapp">
                <i class="bi bi-whatsapp text-emerald-400 text-lg"></i>
                <span>{{ __('ui.home_cta.whatsapp_btn') }}</span>
            </a>
        </div>
        <div class="flex flex-wrap justify-center items-center gap-4 sm:gap-8 mt-10 text-slate-300 text-xs sm:text-sm font-medium">
            <div class="inline-flex items-center gap-1.5"><i class="bi bi-check-circle-fill text-amber-400"></i> {{ __('ui.home_cta.badge_cancel') }}</div>
            <div class="inline-flex items-center gap-1.5"><i class="bi bi-check-circle-fill text-amber-400"></i> {{ __('ui.home_cta.badge_transfers') }}</div>
            <div class="inline-flex items-center gap-1.5"><i class="bi bi-check-circle-fill text-amber-400"></i> {{ __('ui.home_cta.badge_payment') }}</div>
            <div class="inline-flex items-center gap-1.5"><i class="bi bi-check-circle-fill text-amber-400"></i> {{ __('ui.home_cta.badge_voucher') }}</div>
        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var loaded = false;
    function loadHeroVideo() {
        if (loaded) return;
        loaded = true;
        var v = document.getElementById('heroVideo');
        if (v && !v.querySelector('source')) {
            var s = document.createElement('source');
            s.src = "{{ asset('images/desert-safar-dubai-tour-short-dune-discovery-tourism.mp4') }}";
            s.type = "video/mp4";
            v.appendChild(s);
            v.load();
        }
    }
    setTimeout(loadHeroVideo, 4000);
    ['touchstart', 'scroll', 'pointermove'].forEach(function(ev) {
        window.addEventListener(ev, loadHeroVideo, { once: true, passive: true });
    });
});
</script>
@endpush

@endsection
