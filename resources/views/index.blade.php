@extends('layouts.app')

@section('content')
@php
if (!function_exists('renderReviewCardMarkup')) {
    function renderReviewCardMarkup($r) {
        if (is_array($r)) {
            $r = (object) $r;
        }
        if (!is_object($r)) {
            return '';
        }

        $rating = (float) ($r->rating ?? 5.0);
        $fullStars = (int) floor($rating);
        $hasHalf = ($rating - $fullStars) >= 0.3 && ($rating - $fullStars) <= 0.7;
        $stars = '';
        for($i = 0; $i < 5; $i++) {
            if ($i < $fullStars) {
                $stars .= '<i class="bi bi-star-fill text-amber-400"></i>';
            } elseif ($hasHalf && $i === $fullStars) {
                $stars .= '<i class="bi bi-star-half text-amber-400"></i>';
            } else {
                $stars .= '<i class="bi bi-star text-slate-300"></i>';
            }
        }

        $sourceLower = strtolower((string)($r->source ?? 'google'));
        $isUgc = ($sourceLower === 'direct_ugc');
        $isGoogle = ($sourceLower === 'google');
        $isTripAdvisor = ($sourceLower === 'tripadvisor');

        if ($isGoogle) {
            $sourceBadge = '<span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-800 border border-blue-200/60 rounded-full px-2.5 py-0.5 text-xs font-semibold shadow-2xs"><img src="' . asset('images/Google-G.avif') . '" alt="Google" class="w-3.5 h-3.5 inline-block shrink-0" width="14" height="14" loading="lazy"> Google Verified</span>';
        } elseif ($isTripAdvisor) {
            $sourceBadge = '<span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200/60 rounded-full px-2.5 py-0.5 text-xs font-semibold shadow-2xs"><i class="bi bi-star-fill text-emerald-600"></i> TripAdvisor</span>';
        } elseif ($isUgc) {
            $sourceBadge = '<span class="inline-flex items-center gap-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-full px-2.5 py-0.5 text-xs font-bold shadow-2xs"><i class="bi bi-patch-check-fill text-amber-600"></i> ' . htmlspecialchars(__('ui.reviews_section.verified_guest')) . '</span>';
        } else {
            $sourceBadge = '<span class="inline-flex items-center gap-1 bg-slate-100 text-slate-700 rounded-full px-2.5 py-0.5 text-xs font-medium">' . ucfirst($sourceLower) . '</span>';
        }

        $url = !empty($r->review_url) ? $r->review_url : route('review.rate', ['ref' => 'guest']);
        $reviewerAvatar = !empty($r->reviewer_avatar_url) ? (string) $r->reviewer_avatar_url : '';
        $avatar = !empty($reviewerAvatar) ? (str_starts_with($reviewerAvatar, 'http') ? $reviewerAvatar : asset($reviewerAvatar)) : asset('images/avatar-default.svg');
        $fallbackAvatar = asset('images/avatar-default.svg');

        $dateFormatted = '';
        if (!empty($r->published_date)) {
            try {
                $dateFormatted = ($r->published_date instanceof \DateTimeInterface)
                    ? $r->published_date->format('M Y')
                    : \Carbon\Carbon::parse($r->published_date)->format('M Y');
            } catch (\Throwable $e) {
                $dateFormatted = '';
            }
        }

        $photos = $r->photos ?? [];
        if (is_string($photos)) {
            $photos = json_decode($photos, true) ?: [];
        }
        $photosHtml = '';
        if (is_array($photos) && count($photos) > 0) {
            $photosHtml .= '<div class="flex gap-1.5 mb-2 mt-1">';
            foreach (array_slice($photos, 0, 3) as $p) {
                if (!is_string($p) || empty($p)) continue;
                $pUrl = str_starts_with($p, 'http') ? $p : asset($p);
                $photosHtml .= '<a href="' . htmlspecialchars($pUrl) . '" target="_blank" rel="noopener noreferrer" class="rounded-lg overflow-hidden inline-block shadow-xs border border-slate-200 w-12 h-12 shrink-0"><img src="' . htmlspecialchars($pUrl) . '" alt="Traveler photo" class="w-full h-full object-cover" loading="lazy"></a>';
            }
            $photosHtml .= '</div>';
        }

        $actionText = $isUgc ? __('ui.reviews_section.submit_review') : __('ui.common.view_details');
        $rawName = trim((string)($r->reviewer_name ?? ''));
        $reviewerName = htmlspecialchars(!empty($rawName) ? $rawName : 'Verified Guest');
        $reviewTitle = !empty($r->review_title) ? htmlspecialchars((string)$r->review_title) : '';
        $rawText = trim((string)($r->review_text ?? ''));
        $reviewText = htmlspecialchars(!empty($rawText) ? $rawText : 'Outstanding desert safari experience with Dunes Discovery. Highly recommended for visitors in Dubai!');

        return '
        <div class="review-card h-full flex flex-col text-start">
            <div class="flex justify-between items-center mb-3">
                <div class="flex items-center gap-2 min-w-0">
                    <img src="' . htmlspecialchars($avatar) . '" alt="' . $reviewerName . '" class="w-10 h-10 rounded-full object-cover shrink-0 shadow-2xs" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src=\'' . $fallbackAvatar . '\'">
                    <div class="min-w-0">
                        <div class="font-bold text-slate-900 text-sm truncate">' . $reviewerName . '</div>
                        <div class="text-slate-500 text-xs">' . htmlspecialchars($dateFormatted) . '</div>
                    </div>
                </div>
                <div class="flex gap-0.5 text-xs shrink-0">' . $stars . '</div>
            </div>
            ' . ($reviewTitle ? '<h3 class="text-sm font-bold mb-1.5 text-slate-900 line-clamp-1">' . $reviewTitle . '</h3>' : '') . '
            <p class="text-slate-600 text-sm mb-2 flex-grow line-clamp-3 leading-relaxed">"' . $reviewText . '"</p>
            ' . $photosHtml . '
            <div class="flex justify-between items-center mt-auto pt-3 border-t border-slate-200">
                ' . $sourceBadge . '
                <a href="' . htmlspecialchars($url) . '" ' . ($isUgc ? '' : 'target="_blank" rel="noopener noreferrer"') . ' class="text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-primary hover:text-white border border-slate-300 hover:border-primary rounded-full px-3 py-1 transition-colors shadow-2xs">' . htmlspecialchars($actionText) . '</a>
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
                <div class="text-center lg:text-start">
                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 mb-2">
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
                                <span class="glass-dark text-white text-xs font-semibold px-2.5 py-1 rounded-full inline-flex items-center gap-1">
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
        $reviewsCollection = collect($reviews ?? []);
        $googleReviews = $reviewsCollection->filter(function ($r) {
            $src = is_object($r) ? ($r->source ?? '') : (is_array($r) ? ($r['source'] ?? '') : '');
            return strtolower((string)$src) === 'google';
        });
        if ($googleReviews->isEmpty()) {
            $googleReviews = $reviewsCollection->take(10);
        }
        $tripReviews = $reviewsCollection->filter(function ($r) {
            $src = is_object($r) ? ($r->source ?? '') : (is_array($r) ? ($r['source'] ?? '') : '');
            return strtolower((string)$src) === 'tripadvisor';
        });
        if ($tripReviews->isEmpty()) {
            $tripReviews = $reviewsCollection->skip(5)->take(10);
        }
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

<!-- ── Real Moments Captured by Our Guests (Reviews Media Gallery) ────────────── -->
@if(!empty($galleryItems) && count($galleryItems) > 0)
<section class="py-12 sm:py-20 bg-slate-900 text-white relative overflow-hidden" 
    x-data="{
        category: 'all',
        items: @js($galleryItems),
        activeItem: null,
        lightboxOpen: false,
        currentIndex: 0,
        get visibleItems() {
            if (this.category === 'all') return this.items.slice(0, 8);
            const filtered = this.items.filter(item => {
                if (this.category === 'videos') return item.type === 'video';
                return item.category === this.category;
            });
            return filtered.length ? filtered.slice(0, 8) : this.items.slice(0, 8);
        },
        openLightbox(item) {
            const list = this.visibleItems;
            this.currentIndex = list.findIndex(i => i.id === item.id);
            if (this.currentIndex === -1) this.currentIndex = 0;
            this.activeItem = list[this.currentIndex] || item;
            this.lightboxOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        closeLightbox() {
            this.lightboxOpen = false;
            this.activeItem = null;
            document.body.classList.remove('overflow-hidden');
        },
        nextItem() {
            const list = this.visibleItems;
            if (!list.length) return;
            this.currentIndex = (this.currentIndex + 1) % list.length;
            this.activeItem = list[this.currentIndex];
        },
        prevItem() {
            const list = this.visibleItems;
            if (!list.length) return;
            this.currentIndex = (this.currentIndex - 1 + list.length) % list.length;
            this.activeItem = list[this.currentIndex];
        },
        reportBrokenLink(url, cardElement) {
            if (cardElement) { cardElement.remove(); }
            fetch('{{ route('api.gallery.report-broken') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ url: url })
            }).catch(() => {});
        }
    }"
    @keydown.escape.window="closeLightbox()"
    @keydown.arrow-right.window="lightboxOpen && nextItem()"
    @keydown.arrow-left.window="lightboxOpen && prevItem()">

    <div class="absolute inset-0 w-full h-full bg-[radial-gradient(ellipse_at_80%_20%,rgba(246,144,68,0.15)_0%,transparent_60%)] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Section Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10 pb-6 border-b border-white/10">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-primary/20 border border-primary/40 text-primary px-3.5 py-1 text-xs font-bold mb-3 shadow-xs">
                    <i class="bi bi-camera-fill"></i>
                    <span>{{ __('ui.gallery_section.eyebrow') ?? 'Verified Guest Moments' }}</span>
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight mb-2">
                    {{ __('ui.gallery_section.title') ?? 'Captured in the Dunes by Our Travelers' }}
                </h2>
                <p class="text-white/70 text-sm sm:text-base max-w-2xl leading-relaxed">
                    {{ __('ui.gallery_section.subtitle') ?? 'Authentic, unfiltered photos and videos posted by real guests on Google Maps and TripAdvisor.' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ localized_route('gallery.index') }}" class="btn-desert-animated text-xs sm:text-sm font-bold rounded-full px-5 py-2.5 text-white shadow-md inline-flex items-center gap-2 cursor-pointer whitespace-nowrap">
                    <span>{{ __('ui.gallery_section.view_all') ?? 'Explore Full Gallery' }}</span>
                    <i class="bi bi-arrow-right rtl:rotate-180"></i>
                </a>
            </div>
        </div>

        <!-- Filter Tabs for Home -->
        <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-8 scrollbar-none">
            <button type="button" @click="category = 'all'" 
                :class="category === 'all' ? 'bg-primary text-white shadow-sm' : 'bg-white/10 hover:bg-white/20 text-white/80'"
                class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold transition-all cursor-pointer whitespace-nowrap inline-flex items-center gap-1.5">
                <i class="bi bi-grid-fill"></i>
                <span>{{ __('ui.gallery_section.filter_all') ?? 'All Moments' }}</span>
            </button>
            <button type="button" @click="category = 'red_dunes'" 
                :class="category === 'red_dunes' ? 'bg-primary text-white shadow-sm' : 'bg-white/10 hover:bg-white/20 text-white/80'"
                class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold transition-all cursor-pointer whitespace-nowrap inline-flex items-center gap-1.5">
                <i class="bi bi-compass-fill"></i>
                <span>{{ __('ui.gallery_section.filter_red_dunes') ?? 'Red Dunes' }}</span>
            </button>
            <button type="button" @click="category = 'buggy_quad'" 
                :class="category === 'buggy_quad' ? 'bg-primary text-white shadow-sm' : 'bg-white/10 hover:bg-white/20 text-white/80'"
                class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold transition-all cursor-pointer whitespace-nowrap inline-flex items-center gap-1.5">
                <i class="bi bi-lightning-charge-fill"></i>
                <span>{{ __('ui.gallery_section.filter_buggy_quad') ?? 'Buggies & Quads' }}</span>
            </button>
            <button type="button" @click="category = 'camp_bbq'" 
                :class="category === 'camp_bbq' ? 'bg-primary text-white shadow-sm' : 'bg-white/10 hover:bg-white/20 text-white/80'"
                class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold transition-all cursor-pointer whitespace-nowrap inline-flex items-center gap-1.5">
                <i class="bi bi-fire"></i>
                <span>{{ __('ui.gallery_section.filter_camp_bbq') ?? 'Camp & BBQ' }}</span>
            </button>
            <button type="button" @click="category = 'sunset_camels'" 
                :class="category === 'sunset_camels' ? 'bg-primary text-white shadow-sm' : 'bg-white/10 hover:bg-white/20 text-white/80'"
                class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold transition-all cursor-pointer whitespace-nowrap inline-flex items-center gap-1.5">
                <i class="bi bi-sunset-fill"></i>
                <span>{{ __('ui.gallery_section.filter_sunset_camels') ?? 'Sunset & Camels' }}</span>
            </button>
        </div>

        <!-- Grid of Media Items -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-5">
            <template x-for="item in visibleItems" :key="item.id">
                <div class="gallery-home-card group relative bg-slate-950 rounded-2xl overflow-hidden shadow-lg border border-white/10 cursor-pointer flex flex-col"
                     @click="openLightbox(item)">
                    <div class="relative w-full aspect-4/3 overflow-hidden">
                        <template x-if="item.type === 'video'">
                            <div class="relative w-full h-full flex items-center justify-center">
                                <img :src="item.thumbnail_url" 
                                     :alt="item.review_title || 'Guest Safari Video'" 
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" 
                                     loading="lazy"
                                     referrerpolicy="no-referrer"
                                     x-on:error="reportBrokenLink(item.url, $el.closest('.gallery-home-card'))">
                                <div class="absolute inset-0 bg-slate-950/40 flex items-center justify-center group-hover:bg-slate-950/20 transition-colors">
                                    <span class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center shadow-lg">
                                        <i class="bi bi-play-fill text-xl ml-0.5"></i>
                                    </span>
                                </div>
                            </div>
                        </template>
                        <template x-if="item.type !== 'video'">
                            <img :src="item.url" 
                                 :alt="item.review_title || 'Guest Safari Photo'" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" 
                                 loading="lazy"
                                 referrerpolicy="no-referrer"
                                 x-on:error="reportBrokenLink(item.url, $el.closest('.gallery-home-card'))">
                        </template>

                        <!-- Top Source Pill -->
                        <div class="absolute top-2 inset-x-2 flex items-center justify-between pointer-events-none">
                            <span class="bg-slate-950/80 backdrop-blur-md text-white text-[10px] font-bold px-2 py-0.5 rounded-full border border-white/10" x-text="item.category_label"></span>
                            <template x-if="item.source === 'google'">
                                <span class="bg-white/95 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1 shadow-2xs">
                                    <img src="{{ asset('images/Google-G.avif') }}" alt="Google" class="w-2.5 h-2.5 inline-block" width="10" height="10" onerror="this.onerror=null;this.src='{{ asset('images/google.svg') }}'"> Google
                                </span>
                            </template>
                            <template x-if="item.source === 'tripadvisor'">
                                <span class="bg-emerald-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1 shadow-2xs">
                                    <i class="bi bi-star-fill text-[9px] text-amber-300"></i> TripAdvisor
                                </span>
                            </template>
                        </div>

                        <!-- Hover Bottom Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-3">
                            <div class="flex items-center justify-between gap-1 mb-1">
                                <span class="text-white text-xs font-bold truncate" x-text="item.reviewer_name"></span>
                                <div class="text-amber-400 text-[10px] flex gap-0.5">
                                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                                </div>
                            </div>
                            <p class="text-white/80 text-[11px] line-clamp-1" x-text="item.review_text"></p>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Mobile View All CTA -->
        <div class="mt-8 text-center md:hidden">
            <a href="{{ localized_route('gallery.index') }}" class="btn-desert-animated text-xs font-bold rounded-full px-6 py-3 text-white shadow-md inline-flex items-center gap-2">
                <span>{{ __('ui.gallery_section.view_all') ?? 'Explore Full Gallery' }} ({{ count($galleryItems) }}+ Photos)</span>
                <i class="bi bi-arrow-right rtl:rotate-180"></i>
            </a>
        </div>
    </div>

    <!-- ── Fullscreen Interactive Lightbox Modal for Home ───────────────────────── -->
    <div x-show="lightboxOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 bg-slate-950/95 backdrop-blur-md"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         role="dialog"
         aria-modal="true">

        <button type="button" 
                @click="closeLightbox()" 
                class="absolute top-4 right-4 sm:top-6 sm:right-6 z-50 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 text-white flex items-center justify-center transition-colors cursor-pointer border border-white/20">
            <i class="bi bi-x-lg text-lg"></i>
            <span class="sr-only">Close</span>
        </button>

        <button type="button" 
                @click="prevItem()" 
                class="absolute left-2 sm:left-6 top-1/2 -translate-y-1/2 z-40 w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-white/10 hover:bg-white/25 text-white flex items-center justify-center transition-colors cursor-pointer border border-white/20">
            <i class="bi bi-chevron-left text-xl rtl:rotate-180"></i>
            <span class="sr-only">Previous</span>
        </button>

        <button type="button" 
                @click="nextItem()" 
                class="absolute right-2 sm:right-6 top-1/2 -translate-y-1/2 z-40 w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-white/10 hover:bg-white/25 text-white flex items-center justify-center transition-colors cursor-pointer border border-white/20">
            <i class="bi bi-chevron-right text-xl rtl:rotate-180"></i>
            <span class="sr-only">Next</span>
        </button>

        <div class="relative z-20 w-full max-w-5xl max-h-[92vh] flex flex-col lg:flex-row bg-slate-900 rounded-3xl overflow-hidden border border-white/10 shadow-2xl" 
             @click.outside="closeLightbox()">

            <div class="relative lg:w-3/5 bg-black flex items-center justify-center min-h-[300px] sm:min-h-[420px] max-h-[60vh] lg:max-h-[85vh] p-2">
                <template x-if="activeItem && activeItem.type === 'video'">
                    <div class="w-full h-full flex items-center justify-center">
                        <video :src="activeItem.url" controls autoplay class="max-w-full max-h-[58vh] lg:max-h-[80vh] rounded-xl object-contain"></video>
                    </div>
                </template>
                <template x-if="activeItem && activeItem.type !== 'video'">
                    <img :src="activeItem.url" 
                         :alt="activeItem.review_title || 'Guest Safari Photo'" 
                         class="max-w-full max-h-[58vh] lg:max-h-[80vh] rounded-xl object-contain select-none"
                         referrerpolicy="no-referrer">
                </template>
            </div>

            <div class="lg:w-2/5 p-5 sm:p-6 bg-slate-900 text-white flex flex-col justify-between overflow-y-auto max-h-[40vh] lg:max-h-[85vh]">
                <div>
                    <div class="flex items-center justify-between gap-3 mb-4 pb-4 border-b border-white/10">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary/20 text-primary border border-primary/30 flex items-center justify-center font-black text-sm shrink-0">
                                <span x-text="activeItem ? activeItem.reviewer_name.charAt(0).toUpperCase() : 'T'"></span>
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-sm sm:text-base leading-tight" x-text="activeItem ? activeItem.reviewer_name : 'Verified Guest'"></h4>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <template x-if="activeItem && activeItem.source === 'google'">
                                        <span class="text-blue-400 font-bold text-xs inline-flex items-center gap-1">
                                            <img src="{{ asset('images/Google-G.avif') }}" alt="Google" class="w-3 h-3 inline-block" width="12" height="12" onerror="this.onerror=null;this.src='{{ asset('images/google.svg') }}'">
                                            Google
                                        </span>
                                    </template>
                                    <template x-if="activeItem && activeItem.source === 'tripadvisor'">
                                        <span class="text-emerald-400 font-bold text-xs inline-flex items-center gap-1">
                                            <i class="bi bi-star-fill text-xs text-amber-400"></i> TripAdvisor
                                        </span>
                                    </template>
                                    <span class="text-white/40 text-xs">•</span>
                                    <span class="text-xs text-white/60" x-text="activeItem ? activeItem.formatted_date : ''"></span>
                                </div>
                            </div>
                        </div>
                        <div class="text-amber-400 text-sm flex gap-0.5">
                            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        </div>
                    </div>

                    <div class="mb-3">
                        <span class="bg-primary/20 text-primary border border-primary/30 rounded-full px-3 py-1 text-xs font-semibold inline-flex items-center gap-1.5">
                            <i class="bi bi-tag-fill"></i>
                            <span x-text="activeItem ? activeItem.category_label : 'Desert Safari'"></span>
                        </span>
                    </div>

                    <template x-if="activeItem && activeItem.review_title">
                        <h5 class="text-base sm:text-lg font-bold text-white mb-2 leading-snug" x-text="activeItem.review_title"></h5>
                    </template>
                    <p class="text-white/80 text-xs sm:text-sm leading-relaxed mb-6 whitespace-pre-line" x-text="activeItem ? (activeItem.full_review_text || activeItem.review_text) : ''"></p>
                </div>

                <div class="pt-4 border-t border-white/10 space-y-3">
                    <button type="button" 
                            class="btn-desert-animated w-full py-3 px-4 rounded-xl font-bold text-sm text-white shadow-lg flex items-center justify-center gap-2 cursor-pointer"
                            @click="closeLightbox(); $store.modal.open('booking');">
                        <i class="bi bi-calendar-check-fill text-base"></i>
                        <span>{{ __('ui.gallery_section.book_this') ?? 'Book This Safari Adventure' }}</span>
                    </button>
                    <a href="{{ localized_route('gallery.index') }}" class="w-full py-2.5 px-4 rounded-xl font-bold text-xs text-center text-white/80 hover:text-white bg-white/10 hover:bg-white/20 transition-colors block">
                        {{ __('ui.gallery_section.view_all') ?? 'View All Guest Photos & Videos' }} &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

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
                        class="w-full text-start px-5 sm:px-6 py-4.5 font-bold text-slate-900 flex items-center justify-between gap-4 cursor-pointer"
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
