@php
    $tour = $tour ?? null;
    $variant = $variant ?? 'mid-article';
    if (!$tour) return;

    $minPrice = $tour->tiers->min('pivot.price');
    if (!$minPrice || $minPrice <= 0) {
        $defaultPrices = [
            'evening-desert-safari-dubai' => 99,
            'morning-desert-safari-dubai' => 120,
            'overnight-desert-safari-dubai' => 350,
            'desert-safari-quad-biking-dubai' => 199,
            'dubai-city-tour' => 120,
            'abu-dhabi-city-tour-from-dubai' => 199,
            'dhow-cruise-catamaran-cruise-dinner-dubai' => 150,
            'dune-buggy-rental-dubai' => 599,
        ];
        $minPrice = $defaultPrices[$tour->slug] ?? 99;
    }

    $minTier = $tour->tiers->where('pivot.price', $minPrice)->first();
    $oldPrice = $minTier?->pivot?->old_price;
    if (!$oldPrice || $oldPrice <= $minPrice) {
        $oldPrice = round($minPrice * 1.35);
    }

    $tourImg = $tour->thumb_image ?: $tour->hero_image;
    $tourImgUrl = $tourImg ? asset('images/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $tourImg)) : asset('images/desert-safari-poster.avif');

    $whatsappNum = preg_replace('/[^0-9]/', '', $settings['site_whatsapp'] ?? '971502456056');
    $postTitle = isset($post) && $post ? $post->title : 'Dubai Desert Safari';
    $waText = "Hi Dunes Discovery! I am reading your guide '{$postTitle}' and would like to check availability for {$tour->name} (From AED {$minPrice}).";
    $waUrl = "https://wa.me/{$whatsappNum}?text=" . urlencode($waText);

    $highlightsMap = [
        'dune-buggy-rental-dubai' => [
            '1000cc Can-Am Maverick & Polaris Buggies',
            'Full Safety Gear & Certified Desert Guide',
            'Complimentary 4x4 Hotel Pickup & Drop-off',
            'Free 24-Hour Cancellation Guarantee'
        ],
        'desert-safari-quad-biking-dubai' => [
            '30-Min High-Power Self-Drive Quad Biking (ATV)',
            'Lahbab Red Dunes 4x4 Dune Bashing & Sandboarding',
            'Lavish BBQ Dinner Buffet & 3 Live Shows',
            'Free 24-Hour Cancellation Guarantee'
        ],
        'morning-desert-safari-dubai' => [
            'Sunrise Dune Bashing in Lahbab Red Dunes',
            'Sandboarding & Scenic Camel Riding',
            'Arabic Coffee, Gahwa & Light Refreshments',
            'Back to Hotel by Midday'
        ],
        'overnight-desert-safari-dubai' => [
            'Full Evening Safari + BBQ Dinner & Shows',
            'Overnight Bedouin Tent Stay & Stargazing',
            'Campfire Gathering & Desert Sunrise Trek',
            'Warm Bedouin Breakfast Included'
        ],
        'dubai-city-tour' => [
            'Burj Khalifa, Dubai Mall & Marina Highlights',
            'Historic Al Fahidi & Traditional Abra Boat Ride',
            'Gold & Spice Souk Guided Walking Exploration',
            'Door-to-Door Air-Conditioned Transport'
        ],
        'abu-dhabi-city-tour-from-dubai' => [
            'Full-Day Tour: Sheikh Zayed Grand Mosque Entry',
            'Emirates Palace, Heritage Village & Corniche',
            'Louvre Abu Dhabi Photo Stop',
            'Luxury Return Transport from Dubai'
        ],
        'dhow-cruise-catamaran-cruise-dinner-dubai' => [
            '2-Hour Dubai Marina & JBR Skyline Cruise',
            '5-Star International & Arabic Buffet Dinner',
            'Live Tanoura Dance Performance',
            'Open-Air Upper Deck & Air-Conditioned Lower Deck'
        ],
        'evening-desert-safari-dubai' => [
            '45-Min Extreme 4x4 Dune Bashing in Lahbab Red Dunes',
            'Sunset Photo Stop, Camel Riding & Sandboarding',
            'Lavish BBQ Dinner Buffet (Veg & Non-Veg Options)',
            '3 Live Entertainment Shows (Tanoura, Fire, Belly Dance)'
        ],
    ];

    $highlights = $highlightsMap[$tour->slug] ?? [
        'Licensed 4x4 Safari Captain & Hotel Transfers',
        'Sunset Photo Stop, Camel Riding & Sandboarding',
        'Lavish BBQ Dinner Buffet with Live Grill',
        'Free 24-Hour Cancellation Guarantee'
    ];
@endphp

@if ($variant === 'mid-article')
<!-- In-Article Interactive Booking Card (Mid-Content Conversion Block) -->
<div class="my-10 not-prose rounded-3xl border-2 border-primary/25 bg-gradient-to-br from-amber-500/5 via-white to-orange-500/10 p-5 sm:p-7 shadow-lg hover:shadow-xl transition-all duration-300 relative overflow-hidden group">
    <!-- Decorative Ambient Glow -->
    <div class="absolute -top-16 -right-16 w-44 h-44 bg-primary/10 rounded-full blur-2xl pointer-events-none group-hover:scale-125 transition-transform duration-700"></div>

    <!-- Header Pill Bar -->
    <div class="flex items-center justify-between flex-wrap gap-2.5 mb-5 pb-3.5 border-b border-slate-200/70">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary text-white text-xs font-black uppercase tracking-wider shadow-xs">
                <i class="bi bi-star-fill text-amber-300"></i> Recommended Experience
            </span>
            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-500 hidden sm:inline-flex">
                <i class="bi bi-geo-alt-fill text-primary"></i> Dubai, UAE
            </span>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1 text-emerald-700 font-bold text-xs bg-emerald-50 border border-emerald-200/80 px-2.5 py-0.5 rounded-full">
                <i class="bi bi-patch-check-fill text-emerald-600"></i> DET Licensed Operator
            </span>
        </div>
    </div>

    <!-- Card Content Grid -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
        <!-- Tour Image Thumbnail Column -->
        <div class="md:col-span-5 relative">
            <div class="relative overflow-hidden rounded-2xl aspect-[16/11] shadow-md group/img">
                <img src="{{ $tourImgUrl }}" 
                     alt="{{ $tour->name }} Dubai" 
                     class="w-full h-full object-cover group-hover/img:scale-105 transition-transform duration-500" 
                     loading="lazy" 
                     width="400" 
                     height="275">
                
                <!-- Duration Badge -->
                <span class="absolute bottom-3 left-3 bg-slate-950/85 backdrop-blur-md text-white text-[11px] font-bold px-2.5 py-1 rounded-full shadow-xs flex items-center gap-1.5">
                    <i class="bi bi-clock text-primary"></i>{{ $tour->duration }}
                </span>

                <!-- Rating Badge -->
                <span class="absolute top-3 right-3 bg-white/95 backdrop-blur-md text-slate-900 text-[11px] font-black px-2.5 py-1 rounded-full shadow-sm flex items-center gap-1">
                    <i class="bi bi-star-fill text-amber-500"></i>{{ $tour->rating ?: '4.9' }}
                    <span class="text-slate-500 font-semibold text-[10px]">({{ number_format($tour->review_count ?: 1247) }})</span>
                </span>

                @if ($tour->is_bestseller)
                <span class="absolute top-3 left-3 bg-red-600 text-white text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full shadow-xs flex items-center gap-1">
                    <i class="bi bi-fire text-amber-300"></i> Bestseller
                </span>
                @endif
            </div>
        </div>

        <!-- Tour Information & Action Column -->
        <div class="md:col-span-7 flex flex-col justify-between">
            <div>
                <h3 class="text-xl sm:text-2xl font-black text-slate-900 group-hover:text-primary transition-colors leading-snug mb-2">
                    <a href="{{ route('tours.show', $tour->slug) }}" class="text-inherit hover:text-primary transition-colors">
                        {{ $tour->name }}
                    </a>
                </h3>

                <p class="text-slate-600 text-xs sm:text-sm leading-relaxed mb-3 line-clamp-2">
                    {{ $tour->short_desc }}
                </p>

                <!-- Highlights List -->
                <ul class="space-y-1.5 mb-4 text-xs text-slate-700">
                    @foreach (array_slice($highlights, 0, 3) as $hl)
                    <li class="flex items-center gap-2">
                        <i class="bi bi-check-circle-fill text-emerald-500 shrink-0 text-sm"></i>
                        <span class="font-medium">{{ $hl }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            <!-- Price & Conversion Action Row -->
            <div class="pt-3 border-t border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-slate-500">Instant Online Booking</div>
                    <div class="flex items-baseline gap-2 mt-0.5">
                        <span class="text-2xl sm:text-3xl font-black text-primary leading-none" data-aed="{{ $minPrice }}">AED {{ number_format($minPrice) }}</span>
                        <span class="text-xs text-slate-500 font-semibold">/ person</span>
                        @if ($oldPrice > $minPrice)
                        <span class="line-through text-xs text-slate-400 font-medium" data-aed="{{ $oldPrice }}">AED {{ number_format($oldPrice) }}</span>
                        @endif
                    </div>
                    <div class="text-[11px] font-bold text-red-600 flex items-center gap-1 mt-1">
                        <i class="bi bi-fire text-red-500"></i> Selling fast for today &amp; tomorrow
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                    <button type="button" 
                            class="w-full sm:w-auto btn-desert-animated text-white font-extrabold text-xs sm:text-sm px-5 py-3 rounded-full shadow-md cursor-pointer inline-flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-95 transition-all"
                            data-action="open-booking" 
                            data-tour="{{ $tour->id }}"
                            @click="$store.modal.open('booking', { tourId: {{ $tour->id }} })">
                        <i class="bi bi-calendar-check-fill text-sm"></i>
                        <span>Book Online</span>
                    </button>

                    <a href="{{ $waUrl }}" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="w-full sm:w-auto btn-whatsapp-animated font-extrabold text-xs sm:text-sm px-4 py-3 rounded-full shadow-md inline-flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-95 transition-all text-white"
                       aria-label="Ask about {{ $tour->name }} on WhatsApp">
                        <i class="bi bi-whatsapp text-sm"></i>
                        <span>WhatsApp</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Trust Footer -->
    <div class="mt-4 pt-3 border-t border-slate-200/60 flex items-center justify-between flex-wrap gap-2 text-[11px] text-slate-500">
        <span class="flex items-center gap-1"><i class="bi bi-shield-check text-emerald-600"></i> 100% Verified Local Operator</span>
        <span class="flex items-center gap-1"><i class="bi bi-clock-history text-primary"></i> Instant E-Ticket Confirmation</span>
        <span class="flex items-center gap-1"><i class="bi bi-arrow-counterclockwise text-blue-600"></i> Free 24h Cancellation</span>
        <a href="{{ route('tours.show', $tour->slug) }}" class="font-bold text-primary hover:underline ml-auto flex items-center gap-1">
            <span>Full Tour Itinerary</span><i class="bi bi-chevron-right text-[10px]"></i>
        </a>
    </div>
</div>

@elseif ($variant === 'end-article')
<!-- End-of-Article Tour Conversion Showcase (Bottom of Post) -->
<div class="mt-12 mb-8 rounded-3xl border-2 border-primary/30 bg-slate-900 text-white p-6 sm:p-8 shadow-xl relative overflow-hidden">
    <!-- Ambient Background Accent -->
    <div class="absolute -bottom-20 -left-20 w-60 h-60 bg-primary/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10">
        <div class="text-center max-w-2xl mx-auto mb-6">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/20 border border-primary/40 text-amber-300 text-xs font-black uppercase tracking-wider mb-2">
                <i class="bi bi-compass-fill"></i> Experience It Firsthand
            </span>
            <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight leading-tight mb-2">
                Ready to Experience This in Dubai?
            </h2>
            <p class="text-slate-300 text-xs sm:text-sm">
                Book your spot with Dunes Discovery Tourism — official government-licensed UAE tour operator with 4.9★ rating from 1,200+ global travelers.
            </p>
        </div>

        <div class="bg-slate-950/70 backdrop-blur-md rounded-2xl border border-white/10 p-5 sm:p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                <div class="md:col-span-4">
                    <div class="relative overflow-hidden rounded-xl aspect-[16/10] shadow-md">
                        <img src="{{ $tourImgUrl }}" 
                             alt="{{ $tour->name }}" 
                             class="w-full h-full object-cover" 
                             loading="lazy">
                        <span class="absolute top-2.5 right-2.5 bg-slate-900/90 text-white text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                            <i class="bi bi-star-fill text-amber-400"></i>{{ $tour->rating ?: '4.9' }}
                        </span>
                    </div>
                </div>
                <div class="md:col-span-8 flex flex-col justify-between">
                    <div>
                        <div class="text-primary text-xs font-bold uppercase tracking-wider mb-1">{{ $tour->category?->name ?? 'Desert Adventure' }}</div>
                        <h3 class="text-lg sm:text-xl font-black text-white mb-2 leading-snug">
                            <a href="{{ route('tours.show', $tour->slug) }}" class="hover:text-primary transition-colors text-inherit">
                                {{ $tour->name }}
                            </a>
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-slate-300 mb-4">
                            @foreach ($highlights as $hl)
                            <div class="flex items-center gap-2">
                                <i class="bi bi-check2 text-emerald-400 shrink-0 font-bold"></i>
                                <span class="truncate">{{ $hl }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-3 border-t border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="text-[10px] text-slate-400 uppercase font-semibold">Special Direct Price</div>
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-2xl font-black text-primary" data-aed="{{ $minPrice }}">AED {{ number_format($minPrice) }}</span>
                                <span class="text-xs text-slate-400">/ person</span>
                                @if ($oldPrice > $minPrice)
                                <span class="line-through text-xs text-slate-500" data-aed="{{ $oldPrice }}">AED {{ number_format($oldPrice) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                            <button type="button" 
                                    class="w-full sm:w-auto btn-desert-animated text-white font-extrabold text-xs px-5 py-2.5 rounded-full shadow-md cursor-pointer inline-flex items-center justify-center gap-2"
                                    data-action="open-booking" 
                                    data-tour="{{ $tour->id }}"
                                    @click="$store.modal.open('booking', { tourId: {{ $tour->id }} })">
                                <i class="bi bi-calendar-check-fill"></i>
                                <span>Book Online Now</span>
                            </button>
                            <a href="{{ $waUrl }}" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="w-full sm:w-auto btn-whatsapp-animated font-extrabold text-xs px-4 py-2.5 rounded-full shadow-md inline-flex items-center justify-center gap-2 text-white">
                                <i class="bi bi-whatsapp"></i>
                                <span>WhatsApp Concierge</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (isset($recommendedTours) && $recommendedTours->count() > 0)
        <!-- Alternative Popular Options -->
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Other Top-Rated Experiences:</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach ($recommendedTours as $recTour)
                @php
                    $recMinPrice = $recTour->tiers->min('pivot.price') ?? 99;
                    $recImg = $recTour->thumb_image ?: $recTour->hero_image;
                    $recImgUrl = $recImg ? asset('images/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $recImg)) : asset('images/desert-safari-poster.avif');
                @endphp
                <div class="bg-white/5 border border-white/10 rounded-xl p-3 flex items-center justify-between gap-3 hover:bg-white/10 transition-colors">
                    <div class="flex items-center gap-3 min-w-0">
                        <img src="{{ $recImgUrl }}" alt="{{ $recTour->name }}" class="w-12 h-12 rounded-lg object-cover shrink-0" loading="lazy">
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-white truncate leading-snug">{{ $recTour->name }}</div>
                            <div class="text-[11px] text-primary font-black mt-0.5" data-aed="{{ $recMinPrice }}">From AED {{ number_format($recMinPrice) }}</div>
                        </div>
                    </div>
                    <button type="button" 
                            class="shrink-0 bg-primary/20 hover:bg-primary text-white text-[11px] font-bold px-3 py-1.5 rounded-full border border-primary/40 hover:border-primary transition-colors cursor-pointer"
                            data-action="open-booking" 
                            data-tour="{{ $recTour->id }}"
                            @click="$store.modal.open('booking', { tourId: {{ $recTour->id }} })">
                        Book
                    </button>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

@elseif ($variant === 'sidebar')
<!-- Contextual Sidebar Booking Card -->
<div class="rounded-2xl p-5 bg-gradient-to-br from-slate-900 to-slate-950 text-white shadow-xl border border-primary/30 relative overflow-hidden group">
    <!-- Ambient Accent Glow -->
    <div class="absolute -top-12 -right-12 w-32 h-32 bg-primary/20 rounded-full blur-2xl pointer-events-none group-hover:scale-125 transition-transform duration-700"></div>

    <div class="relative z-10">
        <div class="flex items-center justify-between gap-2 mb-3">
            <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider bg-primary text-white px-2 py-0.5 rounded-full">
                <i class="bi bi-star-fill text-amber-300"></i> Featured Tour
            </span>
            <span class="text-[11px] font-bold text-amber-400 flex items-center gap-1">
                <i class="bi bi-star-fill"></i>{{ $tour->rating ?: '4.9' }}
            </span>
        </div>

        <div class="relative rounded-xl overflow-hidden aspect-[16/10] mb-3 shadow-md">
            <img src="{{ $tourImgUrl }}" 
                 alt="{{ $tour->name }}" 
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                 loading="lazy">
            <span class="absolute bottom-2 left-2 bg-slate-950/80 backdrop-blur-md text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                <i class="bi bi-clock mr-1 text-primary"></i>{{ $tour->duration }}
            </span>
        </div>

        <h3 class="font-extrabold text-base text-white mb-1.5 leading-snug">
            <a href="{{ route('tours.show', $tour->slug) }}" class="text-inherit hover:text-primary transition-colors">
                {{ $tour->name }}
            </a>
        </h3>

        <div class="space-y-1 mb-4 text-[11px] text-slate-300">
            @foreach (array_slice($highlights, 0, 3) as $hl)
            <div class="flex items-center gap-1.5">
                <i class="bi bi-check2 text-emerald-400 font-bold shrink-0"></i>
                <span class="truncate">{{ $hl }}</span>
            </div>
            @endforeach
        </div>

        <div class="p-3 bg-white/5 rounded-xl border border-white/10 mb-4 flex items-baseline justify-between">
            <span class="text-[10px] text-slate-400 uppercase font-semibold">Starting Price</span>
            <div>
                <span class="text-lg font-black text-primary" data-aed="{{ $minPrice }}">AED {{ number_format($minPrice) }}</span>
                <span class="text-[10px] text-slate-400">/ person</span>
            </div>
        </div>

        <button type="button" 
                class="w-full btn-desert-animated text-white font-extrabold rounded-full py-2.5 text-xs transition-colors cursor-pointer shadow-md mb-2 flex items-center justify-center gap-1.5"
                data-action="open-booking" 
                data-tour="{{ $tour->id }}"
                @click="$store.modal.open('booking', { tourId: {{ $tour->id }} })">
            <i class="bi bi-calendar-check-fill"></i>
            <span>Book Online Now</span>
        </button>

        <a href="{{ $waUrl }}" 
           class="w-full btn-whatsapp-animated text-white font-bold rounded-full py-2 text-xs transition-colors flex items-center justify-center gap-1.5" 
           target="_blank" 
           rel="noopener noreferrer"
           aria-label="Inquire about {{ $tour->name }} on WhatsApp">
            <i class="bi bi-whatsapp"></i>
            <span>Ask on WhatsApp</span>
        </a>

        <div class="mt-3 text-center">
            <a href="{{ route('tours.show', $tour->slug) }}" class="text-[11px] text-slate-400 hover:text-primary transition-colors font-medium inline-flex items-center gap-1">
                <span>View Full Package Details</span>
                <i class="bi bi-arrow-right text-[10px]"></i>
            </a>
        </div>
    </div>
</div>

@elseif ($variant === 'mobile-sticky')
<!-- Sticky Mobile Conversion Bar (Appears on Scroll) -->
<aside class="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 px-4 py-2.5 sm:hidden shadow-2xl transition-all duration-300"
     x-data="{ showStickyBar: false }"
     x-init="window.addEventListener('scroll', () => { showStickyBar = window.scrollY > 400 })"
     x-show="showStickyBar"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="translate-y-full opacity-0"
     x-transition:enter-end="translate-y-0 opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="translate-y-0 opacity-100"
     x-transition:leave-end="translate-y-full opacity-0"
     x-cloak
     role="region"
     aria-label="Tour booking quick bar"
     style="display: none;">
    <div class="flex items-center justify-between gap-3">
        <div class="min-w-0">
            <div class="flex items-center gap-1 text-[10px] font-black uppercase text-primary tracking-wider">
                <i class="bi bi-fire text-red-500"></i>
                <span class="truncate">{{ $tour->name }}</span>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-sm font-black text-slate-900" data-aed="{{ $minPrice }}">AED {{ number_format($minPrice) }}</span>
                <span class="text-[10px] text-slate-500">/ person</span>
                <span class="text-[10px] text-amber-500 font-bold ml-1"><i class="bi bi-star-fill text-[9px]"></i> {{ $tour->rating ?: '4.9' }}</span>
            </div>
        </div>

        <div class="flex items-center gap-1.5 shrink-0">
            <a href="{{ $waUrl }}" 
               class="w-8 h-8 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center text-sm shadow-xs transition-transform active:scale-90"
               target="_blank" 
               rel="noopener noreferrer" 
               aria-label="Inquire on WhatsApp">
                <i class="bi bi-whatsapp"></i>
            </a>

            <button type="button" 
                    class="btn-desert-animated text-white font-extrabold text-xs px-3.5 py-1.5 rounded-full shadow-xs cursor-pointer inline-flex items-center gap-1"
                    data-action="open-booking" 
                    data-tour="{{ $tour->id }}"
                    @click="$store.modal.open('booking', { tourId: {{ $tour->id }} })">
                <i class="bi bi-calendar-check-fill text-[11px]"></i>
                <span>Book Now</span>
            </button>
        </div>
    </div>
</aside>
@endif
