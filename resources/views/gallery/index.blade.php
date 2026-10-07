@extends('layouts.app')

@push('schema')
@if(!empty($schemaImages))
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'ImageGallery',
  'name' => $pageTitle,
  'description' => $pageDesc,
  'url' => $canonical,
  'image' => $schemaImages,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endif
@endpush

@section('content')
<!-- Page Header Section -->
<section class="hero-subpage bg-slate-950 text-white relative overflow-hidden" style="padding-top: calc(var(--header-h, 72px) + 2.5rem);">
    <div class="absolute inset-0 w-full h-full bg-[radial-gradient(ellipse_at_20%_25%,rgba(246,144,68,0.22)_0%,transparent_65%)] pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 pb-12 sm:pb-16">
        <nav aria-label="{{ __('ui.common.breadcrumb') ?? 'Breadcrumb' }}">
            <ol class="flex items-center gap-2 text-xs sm:text-sm text-white/70 mb-4">
                <li><a href="{{ localized_route('home') }}" class="hover:text-white transition-colors">{{ __('ui.nav.home') ?? 'Home' }}</a></li>
                <li><span class="text-white/40">/</span></li>
                <li class="text-white font-semibold" aria-current="page">{{ __('ui.gallery_section.eyebrow') ?? 'Guest Gallery' }}</li>
            </ol>
        </nav>

        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-6">
            <div class="max-w-3xl">
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span class="glass-dark text-white rounded-full px-3.5 py-1 text-xs font-semibold inline-flex items-center gap-1.5 shadow-xs">
                        <i class="bi bi-camera-fill text-amber-400"></i> {{ __('ui.gallery_section.eyebrow') ?? 'Verified Guest Moments' }}
                    </span>
                    <span class="bg-emerald-600/90 rounded-full px-3.5 py-1 text-xs font-semibold text-white inline-flex items-center gap-1.5 shadow-xs">
                        <i class="bi bi-patch-check-fill text-emerald-200"></i> {{ __('ui.trust_strip.dtcm_licensed') ?? 'DTCM Licensed' }}
                    </span>
                    <span class="bg-primary/20 text-primary border border-primary/40 rounded-full px-3.5 py-1 text-xs font-semibold inline-flex items-center gap-1.5">
                        <i class="bi bi-shield-check"></i> {{ __('ui.trust.best_price_guarantee') ?? '100% Authentic Photos' }}
                    </span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-3">
                    {{ __('ui.gallery_section.title') ?? 'Captured in the Dunes by Our Travelers' }}
                </h1>
                <p class="text-sm sm:text-base text-white/80 leading-relaxed max-w-2xl">
                    {{ __('ui.gallery_section.subtitle') ?? 'Authentic, unfiltered photos and videos posted by real guests on Google Maps and TripAdvisor. Zero stock imagery.' }}
                </p>
            </div>

            <!-- Stats Mini Bar -->
            <div class="grid grid-cols-3 gap-3 sm:gap-4 shrink-0 bg-white/5 border border-white/10 rounded-2xl p-3 sm:p-4 backdrop-blur-md">
                <div class="text-center px-2">
                    <div class="text-xl sm:text-2xl font-black text-amber-400">{{ count($filteredItems) }}+</div>
                    <div class="text-[10px] sm:text-xs text-white/70 uppercase tracking-wider font-semibold">Media Items</div>
                </div>
                <div class="text-center px-2 border-x border-white/10">
                    <div class="text-xl sm:text-2xl font-black text-white">4.9/5</div>
                    <div class="text-[10px] sm:text-xs text-white/70 uppercase tracking-wider font-semibold">Guest Rating</div>
                </div>
                <div class="text-center px-2">
                    <div class="text-xl sm:text-2xl font-black text-emerald-400">100%</div>
                    <div class="text-[10px] sm:text-xs text-white/70 uppercase tracking-wider font-semibold">Verified</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trust & Guarantee Ribbon -->
<section class="bg-slate-50 py-3.5 border-b border-slate-200 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <div class="flex items-center gap-2.5">
                <i class="bi bi-google text-blue-500 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">Google Verified</div>
                    <div class="text-slate-500 text-[11px]">Direct from Google Maps</div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <i class="bi bi-star-fill text-emerald-600 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">TripAdvisor Reviews</div>
                    <div class="text-slate-500 text-[11px]">Traveler Choice Excellence</div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <i class="bi bi-camera-reels-fill text-amber-500 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">Real Dune Action</div>
                    <div class="text-slate-500 text-[11px]">Live Photos & Video Clips</div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <i class="bi bi-shield-check text-cyan-600 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">Zero Stock Images</div>
                    <div class="text-slate-500 text-[11px]">Authentic Guest Experiences</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Gallery Section with Alpine Filter & Lightbox -->
<section class="py-10 sm:py-16 bg-white min-h-screen" 
    x-data="{
        category: '{{ $category ?? 'all' }}',
        source: '{{ $source ?? 'all' }}',
        activeItem: null,
        lightboxOpen: false,
        items: @js($filteredItems),
        currentIndex: 0,
        filterCategory(cat) {
            this.category = cat;
        },
        filterSource(src) {
            this.source = src;
        },
        get visibleItems() {
            return this.items.filter(item => {
                const matchCat = (this.category === 'all') || 
                    (this.category === 'videos' ? item.type === 'video' : item.category === this.category);
                const matchSrc = (this.source === 'all') || (item.source.toLowerCase() === this.source.toLowerCase());
                return matchCat && matchSrc;
            });
        },
        openLightbox(item) {
            const currentList = this.visibleItems;
            this.currentIndex = currentList.findIndex(i => i.id === item.id);
            if (this.currentIndex === -1) this.currentIndex = 0;
            this.activeItem = currentList[this.currentIndex] || item;
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
            if (cardElement) {
                cardElement.remove();
            }
            fetch('{{ route('api.gallery.report-broken') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ url: url })
            }).catch(() => {});
        }
    }"
    @keydown.escape.window="closeLightbox()"
    @keydown.arrow-right.window="lightboxOpen && nextItem()"
    @keydown.arrow-left.window="lightboxOpen && prevItem()">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Filter Controls Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pb-6 border-b border-slate-200">
            <!-- Category Pills -->
            <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto pb-2 md:pb-0 scrollbar-none">
                <button type="button" 
                    @click="filterCategory('all')" 
                    :class="category === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                    class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-colors cursor-pointer inline-flex items-center gap-1.5">
                    <i class="bi bi-grid-fill"></i>
                    <span>{{ __('ui.gallery_section.filter_all') ?? 'All Moments' }}</span>
                    <span :class="category === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'" class="px-2 py-0.5 rounded-full text-[11px] font-semibold">{{ $categoryStats['all'] ?? count($filteredItems) }}</span>
                </button>

                <button type="button" 
                    @click="filterCategory('red_dunes')" 
                    :class="category === 'red_dunes' ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                    class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-colors cursor-pointer inline-flex items-center gap-1.5">
                    <i class="bi bi-compass-fill"></i>
                    <span>{{ __('ui.gallery_section.filter_red_dunes') ?? 'Red Dunes & Safari' }}</span>
                    <span :class="category === 'red_dunes' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'" class="px-2 py-0.5 rounded-full text-[11px] font-semibold">{{ $categoryStats['red_dunes'] ?? 0 }}</span>
                </button>

                <button type="button" 
                    @click="filterCategory('buggy_quad')" 
                    :class="category === 'buggy_quad' ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                    class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-colors cursor-pointer inline-flex items-center gap-1.5">
                    <i class="bi bi-lightning-charge-fill"></i>
                    <span>{{ __('ui.gallery_section.filter_buggy_quad') ?? 'Buggies & Quads' }}</span>
                    <span :class="category === 'buggy_quad' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'" class="px-2 py-0.5 rounded-full text-[11px] font-semibold">{{ $categoryStats['buggy_quad'] ?? 0 }}</span>
                </button>

                <button type="button" 
                    @click="filterCategory('camp_bbq')" 
                    :class="category === 'camp_bbq' ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                    class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-colors cursor-pointer inline-flex items-center gap-1.5">
                    <i class="bi bi-fire"></i>
                    <span>{{ __('ui.gallery_section.filter_camp_bbq') ?? 'Camp & BBQ' }}</span>
                    <span :class="category === 'camp_bbq' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'" class="px-2 py-0.5 rounded-full text-[11px] font-semibold">{{ $categoryStats['camp_bbq'] ?? 0 }}</span>
                </button>

                <button type="button" 
                    @click="filterCategory('sunset_camels')" 
                    :class="category === 'sunset_camels' ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                    class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-colors cursor-pointer inline-flex items-center gap-1.5">
                    <i class="bi bi-sunset-fill"></i>
                    <span>{{ __('ui.gallery_section.filter_sunset_camels') ?? 'Sunset & Camels' }}</span>
                    <span :class="category === 'sunset_camels' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'" class="px-2 py-0.5 rounded-full text-[11px] font-semibold">{{ $categoryStats['sunset_camels'] ?? 0 }}</span>
                </button>

                @if(($categoryStats['videos'] ?? 0) > 0)
                <button type="button" 
                    @click="filterCategory('videos')" 
                    :class="category === 'videos' ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                    class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-colors cursor-pointer inline-flex items-center gap-1.5">
                    <i class="bi bi-camera-video-fill"></i>
                    <span>{{ __('ui.gallery_section.filter_videos') ?? 'Videos' }}</span>
                    <span :class="category === 'videos' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'" class="px-2 py-0.5 rounded-full text-[11px] font-semibold">{{ $categoryStats['videos'] }}</span>
                </button>
                @endif
            </div>

            <!-- Source Filter Segment -->
            <div class="flex items-center gap-1.5 shrink-0 bg-slate-100 p-1 rounded-full self-start md:self-auto">
                <button type="button" 
                    @click="filterSource('all')"
                    :class="source === 'all' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="px-3 py-1.5 rounded-full text-xs transition-all cursor-pointer">
                    All Sources
                </button>
                <button type="button" 
                    @click="filterSource('google')"
                    :class="source === 'google' ? 'bg-white text-blue-600 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="px-3 py-1.5 rounded-full text-xs transition-all cursor-pointer inline-flex items-center gap-1">
                    <img src="{{ asset('images/Google-G.avif') }}" alt="Google" class="w-3.5 h-3.5 inline-block" width="14" height="14" onerror="this.onerror=null;this.src='{{ asset('images/google.svg') }}'">
                    Google
                </button>
                <button type="button" 
                    @click="filterSource('tripadvisor')"
                    :class="source === 'tripadvisor' ? 'bg-white text-emerald-700 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="px-3 py-1.5 rounded-full text-xs transition-all cursor-pointer inline-flex items-center gap-1">
                    <i class="bi bi-star-fill text-emerald-500 text-xs"></i>
                    TripAdvisor
                </button>
            </div>
        </div>

        <!-- Media Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6" id="galleryGrid">
            <template x-for="item in visibleItems" :key="item.id">
                <div class="gallery-item-card group relative bg-slate-900 rounded-2xl overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col cursor-pointer border border-slate-200/60"
                     @click="openLightbox(item)">

                    <!-- Media Container -->
                    <div class="relative w-full aspect-4/3 overflow-hidden bg-slate-950">
                        <template x-if="item.type === 'video'">
                            <div class="relative w-full h-full flex items-center justify-center">
                                <img :src="item.thumbnail_url" 
                                     :alt="item.review_title || 'Guest Safari Video'" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                                     loading="lazy"
                                     referrerpolicy="no-referrer"
                                     x-on:error="reportBrokenLink(item.url, $el.closest('.gallery-item-card'))">
                                <div class="absolute inset-0 bg-slate-950/40 flex items-center justify-center group-hover:bg-slate-950/20 transition-colors">
                                    <span class="w-12 h-12 rounded-full bg-primary text-white flex items-center justify-center shadow-lg transform group-hover:scale-110 transition-transform">
                                        <i class="bi bi-play-fill text-2xl ml-0.5"></i>
                                    </span>
                                </div>
                            </div>
                        </template>

                        <template x-if="item.type !== 'video'">
                            <img :src="item.url" 
                                 :alt="item.review_title || 'Guest Safari Photo'" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                                 loading="lazy"
                                 referrerpolicy="no-referrer"
                                 x-on:error="reportBrokenLink(item.url, $el.closest('.gallery-item-card'))">
                        </template>

                        <!-- Top Badges Overlay -->
                        <div class="absolute top-2.5 inset-x-2.5 flex items-center justify-between pointer-events-none">
                            <!-- Category Badge -->
                            <span class="bg-slate-900/80 backdrop-blur-md text-white border border-white/10 rounded-full px-2.5 py-1 text-[11px] font-semibold flex items-center gap-1 shadow-xs">
                                <span x-text="item.category_label"></span>
                            </span>

                            <!-- Source Badge -->
                            <template x-if="item.source === 'google'">
                                <span class="bg-white/95 backdrop-blur-md text-blue-700 rounded-full px-2.5 py-1 text-[11px] font-bold flex items-center gap-1 shadow-xs">
                                    <img src="{{ asset('images/Google-G.avif') }}" alt="Google" class="w-3 h-3 inline-block" width="12" height="12" onerror="this.onerror=null;this.src='{{ asset('images/google.svg') }}'">
                                    <span>Google</span>
                                </span>
                            </template>
                            <template x-if="item.source === 'tripadvisor'">
                                <span class="bg-emerald-600/95 backdrop-blur-md text-white rounded-full px-2.5 py-1 text-[11px] font-bold flex items-center gap-1 shadow-xs">
                                    <i class="bi bi-star-fill text-amber-300 text-[10px]"></i>
                                    <span>TripAdvisor</span>
                                </span>
                            </template>
                            <template x-if="item.source !== 'google' && item.source !== 'tripadvisor'">
                                <span class="bg-amber-500 text-white rounded-full px-2.5 py-1 text-[11px] font-bold flex items-center gap-1 shadow-xs">
                                    <i class="bi bi-patch-check-fill text-[10px]"></i>
                                    <span>{{ __('ui.reviews_section.verified_guest') ?? 'Verified' }}</span>
                                </span>
                            </template>
                        </div>

                        <!-- Hover Expand Icon Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end justify-between p-3">
                            <span class="text-white text-xs font-semibold inline-flex items-center gap-1">
                                <i class="bi bi-arrows-angle-expand text-amber-400"></i> Tap to View Fullscreen
                            </span>
                            <span class="text-amber-400 text-xs flex gap-0.5">
                                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-3.5 bg-white flex flex-col flex-grow border-t border-slate-100">
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <div class="flex items-center gap-2 truncate">
                                <div class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs shrink-0">
                                    <span x-text="item.reviewer_name ? item.reviewer_name.charAt(0).toUpperCase() : 'T'"></span>
                                </div>
                                <span class="font-bold text-slate-900 text-xs truncate" x-text="item.reviewer_name"></span>
                            </div>
                            <span class="text-[11px] text-slate-400 shrink-0" x-text="item.formatted_date"></span>
                        </div>

                        <p class="text-slate-600 text-xs line-clamp-2 leading-relaxed" x-text="item.review_text"></p>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="visibleItems.length === 0" x-cloak class="text-center py-16 bg-slate-50 rounded-3xl border border-dashed border-slate-300 my-8">
            <div class="w-16 h-16 rounded-full bg-slate-200 text-slate-400 flex items-center justify-center text-3xl mx-auto mb-4">
                <i class="bi bi-camera"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-1">No media found for this filter</h3>
            <p class="text-sm text-slate-500 mb-4">Try selecting "All Moments" to view our complete collection of verified guest photos.</p>
            <button type="button" @click="category = 'all'; source = 'all';" class="btn-desert-animated text-xs font-bold rounded-full px-5 py-2 text-white shadow-xs">
                Reset Filters
            </button>
        </div>

    </div>

    <!-- ── Fullscreen Interactive Lightbox Modal ─────────────────────────────── -->
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

        <!-- Close Button Top Right -->
        <button type="button" 
                @click="closeLightbox()" 
                class="absolute top-4 right-4 sm:top-6 sm:right-6 z-50 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 text-white flex items-center justify-center transition-colors cursor-pointer border border-white/20">
            <i class="bi bi-x-lg text-lg"></i>
            <span class="sr-only">Close</span>
        </button>

        <!-- Previous Button -->
        <button type="button" 
                @click="prevItem()" 
                class="absolute left-2 sm:left-6 top-1/2 -translate-y-1/2 z-40 w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-white/10 hover:bg-white/25 text-white flex items-center justify-center transition-colors cursor-pointer border border-white/20">
            <i class="bi bi-chevron-left text-xl rtl:rotate-180"></i>
            <span class="sr-only">Previous</span>
        </button>

        <!-- Next Button -->
        <button type="button" 
                @click="nextItem()" 
                class="absolute right-2 sm:right-6 top-1/2 -translate-y-1/2 z-40 w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-white/10 hover:bg-white/25 text-white flex items-center justify-center transition-colors cursor-pointer border border-white/20">
            <i class="bi bi-chevron-right text-xl rtl:rotate-180"></i>
            <span class="sr-only">Next</span>
        </button>

        <!-- Modal Content Container -->
        <div class="relative z-20 w-full max-w-5xl max-h-[92vh] flex flex-col lg:flex-row bg-slate-900 rounded-3xl overflow-hidden border border-white/10 shadow-2xl" 
             @click.outside="closeLightbox()">

            <!-- Media Preview Area -->
            <div class="relative lg:w-3/5 bg-black flex items-center justify-center min-h-[320px] sm:min-h-[440px] max-h-[60vh] lg:max-h-[85vh] p-2">
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

            <!-- Review Details Panel -->
            <div class="lg:w-2/5 p-5 sm:p-6 bg-slate-900 text-white flex flex-col justify-between overflow-y-auto max-h-[40vh] lg:max-h-[85vh]">
                <div>
                    <!-- Reviewer Header -->
                    <div class="flex items-center justify-between gap-3 mb-4 pb-4 border-b border-white/10">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-full bg-primary/20 text-primary border border-primary/30 flex items-center justify-center font-black text-base shrink-0">
                                <span x-text="activeItem ? activeItem.reviewer_name.charAt(0).toUpperCase() : 'T'"></span>
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-base leading-tight" x-text="activeItem ? activeItem.reviewer_name : 'Verified Guest'"></h4>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <template x-if="activeItem && activeItem.source === 'google'">
                                        <span class="text-blue-400 font-bold text-xs inline-flex items-center gap-1">
                                            <img src="{{ asset('images/Google-G.avif') }}" alt="Google" class="w-3 h-3 inline-block" width="12" height="12" onerror="this.onerror=null;this.src='{{ asset('images/google.svg') }}'">
                                            Google Verified
                                        </span>
                                    </template>
                                    <template x-if="activeItem && activeItem.source === 'tripadvisor'">
                                        <span class="text-emerald-400 font-bold text-xs inline-flex items-center gap-1">
                                            <i class="bi bi-star-fill text-xs text-amber-400"></i>
                                            TripAdvisor Review
                                        </span>
                                    </template>
                                    <span class="text-white/40 text-xs">•</span>
                                    <span class="text-xs text-white/60" x-text="activeItem ? activeItem.formatted_date : ''"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Rating Badge -->
                        <div class="text-amber-400 text-sm flex gap-0.5">
                            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        </div>
                    </div>

                    <!-- Category Pill -->
                    <div class="mb-3">
                        <span class="bg-primary/20 text-primary border border-primary/30 rounded-full px-3 py-1 text-xs font-semibold inline-flex items-center gap-1.5">
                            <i class="bi bi-tag-fill"></i>
                            <span x-text="activeItem ? activeItem.category_label : 'Desert Safari'"></span>
                        </span>
                    </div>

                    <!-- Title & Full Review Text -->
                    <template x-if="activeItem && activeItem.review_title">
                        <h5 class="text-lg font-bold text-white mb-2 leading-snug" x-text="activeItem.review_title"></h5>
                    </template>
                    <p class="text-white/80 text-sm leading-relaxed mb-6 whitespace-pre-line" x-text="activeItem ? (activeItem.full_review_text || activeItem.review_text) : ''"></p>
                </div>

                <!-- Action CTAs -->
                <div class="pt-4 border-t border-white/10 space-y-3">
                    <button type="button" 
                            class="btn-desert-animated w-full py-3 px-4 rounded-xl font-bold text-sm text-white shadow-lg flex items-center justify-center gap-2 cursor-pointer"
                            @click="closeLightbox(); $store.modal.open('booking');">
                        <i class="bi bi-calendar-check-fill text-base"></i>
                        <span>{{ __('ui.gallery_section.book_this') ?? 'Book This Safari Adventure' }}</span>
                    </button>

                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $waPhone ?? '971502456056') }}?text={{ urlencode('Hello Dunes Discovery, I saw your guest gallery photos and want to book a desert safari!') }}" 
                       target="_blank" 
                       rel="noopener"
                       class="w-full py-2.5 px-4 rounded-xl font-bold text-xs text-emerald-400 bg-emerald-950/50 hover:bg-emerald-900/50 border border-emerald-500/30 flex items-center justify-center gap-2 transition-colors">
                        <i class="bi bi-whatsapp text-sm"></i>
                        <span>Chat with Safari Specialist on WhatsApp</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Bottom Booking Promotion Banner -->
<section class="py-12 sm:py-16 bg-slate-950 text-white relative overflow-hidden border-t border-slate-800">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="rounded-full px-3.5 py-1 text-xs font-bold bg-primary/20 border border-primary/40 text-primary inline-flex items-center gap-1.5 mb-4">
            <i class="bi bi-stars"></i> Make Memories Like These
        </span>
        <h2 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight mb-4">
            Ready to Capture Your Own Desert Moments?
        </h2>
        <p class="text-slate-300 text-sm sm:text-base max-w-2xl mx-auto mb-8 leading-relaxed">
            Join thousands of delighted travelers across Lahbab’s crimson dunes. Instant confirmation, 4x4 Land Cruiser hotel transfers, 5-star live BBQ, and free 24h cancellations.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
            <button type="button" class="btn-desert-animated text-sm sm:text-base font-bold rounded-full px-8 py-3.5 shadow-xl text-white inline-flex items-center justify-center gap-2 cursor-pointer w-full sm:w-auto" @click="$store.modal.open('booking')">
                <i class="bi bi-calendar-check text-lg"></i>
                <span>{{ __('ui.common.book_online_now') ?? 'Book Desert Safari Now' }}</span>
            </button>
            <a href="{{ localized_route('tours.index') }}" class="inline-flex items-center justify-center gap-2 text-sm sm:text-base font-bold rounded-full px-6 py-3.5 border border-white/20 text-white hover:bg-white/10 transition-colors w-full sm:w-auto">
                <i class="bi bi-compass"></i>
                <span>{{ __('ui.common.view_all_tours') ?? 'Explore All Packages' }}</span>
            </a>
        </div>
    </div>
</section>
@endsection
