@extends('layouts.app')

@push('schema')
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
  '@graph' => [
    [
      '@type' => 'WebApplication',
      '@id' => $canonical . '#webapp',
      'name' => 'Dubai Desert Sunset & Golden Hour Time Calculator',
      'url' => $canonical,
      'applicationCategory' => 'TravelApplication',
      'operatingSystem' => 'All',
      'browserRequirements' => 'Requires JavaScript',
      'description' => $pageDesc,
      'provider' => [
        '@type' => 'TravelAgency',
        'name' => 'Dunes Discovery Tourism LLC',
        'url' => url('/'),
        'telephone' => '+971502456056',
      ],
      'offers' => [
        '@type' => 'Offer',
        'price' => '0',
        'priceCurrency' => 'AED',
      ],
    ],
    [
      '@type' => 'BreadcrumbList',
      '@id' => $canonical . '#breadcrumb',
      'itemListElement' => [
        [
          '@type' => 'ListItem',
          'position' => 1,
          'name' => __('ui.nav.home') ?? 'Home',
          'item' => localized_route('home'),
        ],
        [
          '@type' => 'ListItem',
          'position' => 2,
          'name' => 'Desert Sunset Times',
          'item' => $canonical,
        ],
      ],
    ],
    [
      '@type' => 'FAQPage',
      '@id' => $canonical . '#faq',
      'mainEntity' => [
        [
          '@type' => 'Question',
          'name' => 'What time does the sun set in the Dubai desert?',
          'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => 'Sunset in the Dubai desert ranges from 5:34 PM during the winter solstice in December to 7:16 PM during the summer solstice in June and July. In peak safari season (October to March), sunset occurs between 5:35 PM and 6:28 PM.',
          ],
        ],
        [
          '@type' => 'Question',
          'name' => 'When is the best time for golden hour photography on a desert safari?',
          'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => 'Golden hour begins approximately 35 to 45 minutes prior to official sunset. This is when the sun dips below a 6-degree angle, casting long shadows across the red dunes and turning the sand from gold to deep crimson.',
          ],
        ],
        [
          '@type' => 'Question',
          'name' => 'What time do evening desert safaris pick up to catch the sunset?',
          'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => 'Dunes Discovery safari 4x4 Land Cruisers depart from Dubai hotels between 2:30 PM and 3:30 PM depending on the season, arriving at the high Lahbab dunes 45 minutes before sunset for adrenaline dune bashing followed by peaceful sunset photography atop the crests.',
          ],
        ],
        [
          '@type' => 'Question',
          'name' => 'Does the temperature drop quickly in the desert after sunset?',
          'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => 'Yes. Desert sand does not retain heat after dark. In winter months (November to February), temperatures can drop from 28°C in the afternoon to 16°C after sunset, so light jackets or wraps are recommended for the evening camp BBQ and stargazing.',
          ],
        ],
      ],
    ],
  ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<!-- Hero Header Section -->
<section class="hero-subpage bg-slate-950 text-white relative overflow-hidden" style="padding-top: calc(var(--header-h, 72px) + 2.5rem);">
    <div class="absolute inset-0 w-full h-full bg-[radial-gradient(ellipse_at_30%_25%,rgba(246,144,68,0.25)_0%,transparent_65%)] pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 pb-12 sm:pb-16">
        <nav aria-label="{{ __('ui.common.breadcrumb') ?? 'Breadcrumb' }}">
            <ol class="flex items-center gap-2 text-xs sm:text-sm text-white/70 mb-4">
                <li><a href="{{ localized_route('home') }}" class="hover:text-white transition-colors">{{ __('ui.nav.home') ?? 'Home' }}</a></li>
                <li><span class="text-white/40">/</span></li>
                <li class="text-white font-semibold" aria-current="page">Dubai Desert Sunset Times</li>
            </ol>
        </nav>

        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-6">
            <div class="max-w-3xl">
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span class="rounded-full px-3.5 py-1 text-xs font-bold bg-amber-500/20 border border-amber-500/40 text-amber-400 inline-flex items-center gap-1.5 shadow-xs">
                        <i class="bi bi-sunset-fill"></i> Astronomical Solar Data {{ $currentYear }}
                    </span>
                    <span class="bg-emerald-600/90 rounded-full px-3.5 py-1 text-xs font-semibold text-white inline-flex items-center gap-1.5 shadow-xs">
                        <i class="bi bi-geo-alt-fill text-emerald-200"></i> Lahbab Red Dunes (25.04° N, 55.67° E)
                    </span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-3">
                    Dubai Desert Sunset Times & Photography Guide
                </h1>
                <p class="text-sm sm:text-base text-white/80 leading-relaxed max-w-2xl">
                    Plan your red dunes expedition with precision. Real-time astronomical countdown to today’s golden hour, complete 12-month sunset calendar, and expert tips for capturing Dubai's legendary desert skies.
                </p>
            </div>

            <!-- Live Sunset Countdown Card -->
            <div class="shrink-0 w-full lg:w-auto bg-white/5 border border-white/15 rounded-3xl p-5 sm:p-6 backdrop-blur-md shadow-2xl">
                <div class="flex items-center gap-2 text-xs font-semibold text-amber-400 mb-2 uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    <span>Live Dubai Solar Station</span>
                </div>
                <div class="text-2xl sm:text-3xl font-black text-white mb-1" id="sunsetCountdownLabel">
                    Sunset: Calculating...
                </div>
                <div class="text-xs text-white/70 flex items-center gap-2">
                    <i class="bi bi-clock-history text-amber-400"></i>
                    <span>Coordinates: 25.04° N • GST (UTC+4)</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Current Month Quick Status Ribbon -->
<section class="bg-gradient-to-r from-amber-500/10 via-orange-500/10 to-transparent border-y border-amber-500/20 py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs sm:text-sm">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-amber-500 text-slate-950 font-black flex items-center justify-center shrink-0">
                    <i class="bi bi-calendar-event"></i>
                </span>
                <div>
                    <span class="font-bold text-slate-900">{{ $currentMonthData['month'] }} Sunset Window:</span>
                    <span class="text-slate-600 font-medium ml-1">Approx. {{ $currentMonthData['sunset'] }} • Golden Hour: {{ $currentMonthData['golden_hour'] }}</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-900 font-bold text-xs">
                    {{ $currentMonthData['rating'] }}
                </span>
                <a href="#monthly-calendar" class="text-primary font-bold hover:underline inline-flex items-center gap-1">
                    <span>View All 12 Months</span>
                    <i class="bi bi-arrow-down"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Main Guide Content -->
<section class="py-12 sm:py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- 3 Pillars of Desert Sunset -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 shadow-xs">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-2xl mb-4">
                    <i class="bi bi-camera"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Golden Hour Photography</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Occurs 35–45 minutes before sunset. Low-angle sunlight casts long dramatic shadows, carving out the sharp ripples and knife-edge ridges of the high red dunes.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 shadow-xs">
                <div class="w-12 h-12 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center text-2xl mb-4">
                    <i class="bi bi-sunset"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Civil Twilight & Blue Hour</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Spans 20–25 minutes after the sun slips below the horizon. The sand glows with a subtle violet-pink gradient, making it prime time for camel trek silhouettes.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 shadow-xs">
                <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center text-2xl mb-4">
                    <i class="bi bi-stars"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Night Stargazing & Camp BBQ</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Once darkness falls, the vast desert sky opens up away from Dubai's city light glare. Enjoy open-air Tanoura dance, fire shows, and stargazing around the campfire.
                </p>
            </div>
        </div>

        <!-- 12-Month Sunset Reference Table -->
        <div class="mb-16 scroll-mt-20" id="monthly-calendar">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 mb-6">
                <div>
                    <span class="text-xs font-bold text-primary uppercase tracking-wider">Lahbab Red Dunes Calendar</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        Dubai Desert Sunset & Golden Hour Calendar (Monthly)
                    </h2>
                </div>
                <span class="text-xs text-slate-500 italic">*Times represent midpoint monthly averages for Lahbab Desert coordinates.</span>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-sm">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-900 text-white text-xs uppercase font-bold tracking-wider">
                        <tr>
                            <th scope="col" class="py-4 px-4 sm:px-6">Month</th>
                            <th scope="col" class="py-4 px-4">Sunset Time</th>
                            <th scope="col" class="py-4 px-4">Golden Hour Window</th>
                            <th scope="col" class="py-4 px-4">Dusk Temperature</th>
                            <th scope="col" class="py-4 px-4">Conditions</th>
                            <th scope="col" class="py-4 px-4 sm:px-6">Photography Focus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($monthlySchedule as $num => $row)
                        <tr class="{{ $num === $currentMonthNumber ? 'bg-amber-50/70 font-semibold text-slate-900' : 'hover:bg-slate-50' }} transition-colors">
                            <td class="py-3.5 px-4 sm:px-6 flex items-center gap-2">
                                @if($num === $currentMonthNumber)
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                @endif
                                <span>{{ $row['month'] }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-amber-700 font-bold whitespace-nowrap">{{ $row['sunset'] }}</td>
                            <td class="py-3.5 px-4 whitespace-nowrap">{{ $row['golden_hour'] }}</td>
                            <td class="py-3.5 px-4 whitespace-nowrap">{{ $row['dusk_temp'] }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs {{ $num === $currentMonthNumber ? 'bg-amber-200 text-amber-900' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $row['rating'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 text-xs text-slate-600 leading-snug">{{ $row['photo_tip'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recommended Sunset Safari Tours -->
        @if($sunsetTours->isNotEmpty())
        <div class="mb-16">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 mb-8">
                <div>
                    <span class="text-xs font-bold text-primary uppercase tracking-wider">Experience Sunset Live</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        Top Rated Sunset Safaris by Dunes Discovery
                    </h2>
                </div>
                <a href="{{ localized_route('tours.index') }}" class="text-primary font-bold text-sm hover:underline inline-flex items-center gap-1">
                    <span>Explore All Packages</span>
                    <i class="bi bi-arrow-right rtl:rotate-180"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($sunsetTours as $t)
                <div class="group bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col">
                    <div class="relative aspect-16/10 overflow-hidden bg-slate-100">
                        @php
                            $img = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $t->hero_image ?: 'evening-desert-safari-dubai-dune-discovery-tourism.avif');
                        @endphp
                        <img src="{{ asset('images/' . $img) }}" alt="{{ $t->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                        <div class="absolute top-3 start-3">
                            <span class="bg-slate-950/80 backdrop-blur-md text-amber-400 border border-white/10 rounded-full px-3 py-1 text-xs font-bold shadow-xs">
                                <i class="bi bi-sunset-fill"></i> Prime Sunset Tour
                            </span>
                        </div>
                    </div>
                    <div class="p-5 flex flex-col flex-grow justify-between">
                        <div>
                            <div class="flex items-center gap-1 text-amber-400 text-xs mb-2">
                                <i class="bi bi-star-fill"></i>
                                <span class="font-black text-slate-900">{{ number_format($t->rating ?: 4.9, 1) }}</span>
                                <span class="text-slate-400">({{ $t->review_count ?: 450 }}+ reviews)</span>
                            </div>
                            <h4 class="font-extrabold text-slate-900 text-base mb-2 group-hover:text-primary transition-colors">
                                <a href="{{ localized_route('tours.show', $t->slug) }}">{{ $t->name }}</a>
                            </h4>
                            <p class="text-slate-600 text-xs line-clamp-2 leading-relaxed mb-4">
                                {{ $t->short_desc }}
                            </p>
                        </div>
                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] text-slate-400 block">From</span>
                                <span class="text-lg font-black text-slate-900">AED {{ number_format($t->tiers->min('pivot.price') ?: 150) }}</span>
                            </div>
                            <a href="{{ localized_route('tours.show', $t->slug) }}" class="btn-desert-animated text-xs font-bold rounded-full px-4 py-2 text-white shadow-xs">
                                View Details &rarr;
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- FAQ Section -->
        <div class="max-w-4xl mx-auto mb-12">
            <div class="text-center mb-8">
                <span class="text-xs font-bold text-primary uppercase tracking-wider">Expert Guidance</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Frequently Asked Questions About Desert Sunset
                </h2>
            </div>

            <div class="space-y-3" x-data="{ active: null }">
                <div class="rounded-2xl border border-slate-200 overflow-hidden">
                    <button type="button" @click="active = active === 1 ? null : 1" class="w-full text-left p-4 sm:p-5 font-bold text-slate-900 flex justify-between items-center bg-slate-50 hover:bg-slate-100/60 transition-colors">
                        <span>What time does the sun set in the Dubai desert?</span>
                        <i class="bi bi-chevron-down transition-transform" :class="{ 'rotate-180': active === 1 }"></i>
                    </button>
                    <div x-show="active === 1" x-collapse class="p-4 sm:p-5 text-sm text-slate-600 leading-relaxed border-t border-slate-200 bg-white">
                        Sunset in the Dubai desert ranges from 5:34 PM during the winter solstice in December to 7:16 PM during the summer solstice in June and July. In peak safari season (October to March), sunset occurs between 5:35 PM and 6:28 PM.
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 overflow-hidden">
                    <button type="button" @click="active = active === 2 ? null : 2" class="w-full text-left p-4 sm:p-5 font-bold text-slate-900 flex justify-between items-center bg-slate-50 hover:bg-slate-100/60 transition-colors">
                        <span>When is the best time for golden hour photography on safari?</span>
                        <i class="bi bi-chevron-down transition-transform" :class="{ 'rotate-180': active === 2 }"></i>
                    </button>
                    <div x-show="active === 2" x-collapse class="p-4 sm:p-5 text-sm text-slate-600 leading-relaxed border-t border-slate-200 bg-white">
                        Golden hour begins approximately 35 to 45 minutes prior to official sunset. This is when the sun dips below a 6-degree angle, casting long shadows across the red dunes and turning the sand from gold to deep crimson.
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 overflow-hidden">
                    <button type="button" @click="active = active === 3 ? null : 3" class="w-full text-left p-4 sm:p-5 font-bold text-slate-900 flex justify-between items-center bg-slate-50 hover:bg-slate-100/60 transition-colors">
                        <span>What time do evening safaris pick up to catch the sunset?</span>
                        <i class="bi bi-chevron-down transition-transform" :class="{ 'rotate-180': active === 3 }"></i>
                    </button>
                    <div x-show="active === 3" x-collapse class="p-4 sm:p-5 text-sm text-slate-600 leading-relaxed border-t border-slate-200 bg-white">
                        Dunes Discovery safari 4x4 Land Cruisers depart from Dubai hotels between 2:30 PM and 3:30 PM depending on the season, arriving at the high Lahbab dunes 45 minutes before sunset for adrenaline dune bashing followed by peaceful sunset photography atop the crests.
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 overflow-hidden">
                    <button type="button" @click="active = active === 4 ? null : 4" class="w-full text-left p-4 sm:p-5 font-bold text-slate-900 flex justify-between items-center bg-slate-50 hover:bg-slate-100/60 transition-colors">
                        <span>Does the temperature drop quickly in the desert after sunset?</span>
                        <i class="bi bi-chevron-down transition-transform" :class="{ 'rotate-180': active === 4 }"></i>
                    </button>
                    <div x-show="active === 4" x-collapse class="p-4 sm:p-5 text-sm text-slate-600 leading-relaxed border-t border-slate-200 bg-white">
                        Yes. Desert sand does not retain heat after dark. In winter months (November to February), temperatures can drop from 28°C in the afternoon to 16°C after sunset, so light jackets or wraps are recommended for the evening camp BBQ and stargazing.
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Bottom CTA Strip -->
<section class="py-12 bg-slate-950 text-white text-center border-t border-slate-800">
    <div class="max-w-4xl mx-auto px-4">
        <h2 class="text-2xl sm:text-3xl font-extrabold mb-3">Witness Dubai's Crimson Sunset First-Hand</h2>
        <p class="text-white/70 text-sm max-w-xl mx-auto mb-6">
            Reserve your 4x4 Land Cruiser hotel pickup, 5-star live BBQ dinner, and front-row seat to the Lahbab Red Dunes sunset today.
        </p>
        <div class="flex flex-wrap gap-3 justify-center items-center">
            <button type="button" class="btn-desert-animated px-8 py-3 rounded-full text-sm font-bold shadow-lg cursor-pointer" @click="$store.modal.open('booking')">
                <i class="bi bi-calendar-check mr-1.5"></i> Book Evening Safari
            </button>
            <a href="{{ localized_route('gallery.index') }}" class="border border-white/20 hover:bg-white/10 px-6 py-3 rounded-full text-sm font-bold transition-colors">
                <i class="bi bi-camera mr-1.5"></i> Browse Sunset Gallery
            </a>
        </div>
    </div>
</section>
@endsection
