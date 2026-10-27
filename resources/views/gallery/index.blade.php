@extends('layouts.app')

@push('schema')
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
  '@graph' => array_values(array_filter(array_merge([
    !empty($schemaImages) ? [
      '@type' => 'ImageGallery',
      '@id' => $canonical . '#gallery',
      'name' => $pageTitle,
      'description' => $pageDesc,
      'url' => $canonical,
      'image' => $schemaImages,
    ] : null,
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
          'name' => __('ui.gallery_section.eyebrow') ?? 'Guest Moments Gallery',
          'item' => $canonical,
        ],
      ],
    ],
  ], $schemaVideos ?? []))),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
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
                    <div class="text-xl sm:text-2xl font-black text-amber-400">10,000+</div>
                    <div class="text-[10px] sm:text-xs text-white/70 uppercase tracking-wider font-semibold">{{ __('ui.common.happy_guests') ?? 'Happy Guests' }}</div>
                </div>
                <div class="text-center px-2 border-x border-white/10">
                    <div class="text-xl sm:text-2xl font-black text-white">4.9/5</div>
                    <div class="text-[10px] sm:text-xs text-white/70 uppercase tracking-wider font-semibold">{{ __('ui.gallery_section.guest_rating') ?? 'Guest Rating' }}</div>
                </div>
                <div class="text-center px-2">
                    <div class="text-xl sm:text-2xl font-black text-emerald-400">2,847+</div>
                    <div class="text-[10px] sm:text-xs text-white/70 uppercase tracking-wider font-semibold">{{ __('ui.gallery_section.verified_reviews') ?? 'Verified Reviews' }}</div>
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
        touchStartX: 0,
        copiedShare: false,
        canShare: (typeof navigator !== 'undefined' && typeof navigator.share === 'function'),
        canScrollLeft: false,
        canScrollRight: false,
        isDraggingPills: false,
        hasDraggedPills: false,
        dragStartX: 0,
        dragScrollLeft: 0,
        init() {
            try {
                const params = new URLSearchParams(window.location.search);
                const targetId = params.get('item') || (window.location.hash ? window.location.hash.replace('#', '') : null);
                if (targetId) {
                    const found = this.items.find(i => String(i.id) === String(targetId) || String(i.id) === 'media_' + targetId);
                    if (found) {
                        this.openLightbox(found, false);
                    }
                }
            } catch (e) {}
            this.$nextTick(() => {
                this.updatePillScrollState();
                setTimeout(() => this.updatePillScrollState(), 150);
            });
            window.addEventListener('resize', () => this.updatePillScrollState(), { passive: true });
        },
        updatePillScrollState() {
            const el = this.$refs.categoryPills;
            if (!el) return;
            this.canScrollLeft = el.scrollLeft > 6;
            this.canScrollRight = (el.scrollLeft + el.clientWidth) < (el.scrollWidth - 6);
        },
        scrollCategories(delta) {
            const el = this.$refs.categoryPills;
            if (!el) return;
            el.scrollBy({ left: delta, behavior: 'smooth' });
            setTimeout(() => this.updatePillScrollState(), 250);
            setTimeout(() => this.updatePillScrollState(), 500);
        },
        onPillWheel(e) {
            const el = this.$refs.categoryPills;
            if (!el || el.scrollWidth <= el.clientWidth) return;
            if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
                e.preventDefault();
                el.scrollLeft += e.deltaY;
                this.updatePillScrollState();
            }
        },
        startPillDrag(e) {
            const el = this.$refs.categoryPills;
            if (!el) return;
            this.isDraggingPills = true;
            this.hasDraggedPills = false;
            this.dragStartX = e.pageX - el.offsetLeft;
            this.dragScrollLeft = el.scrollLeft;
        },
        movePillDrag(e) {
            if (!this.isDraggingPills) return;
            const el = this.$refs.categoryPills;
            if (!el) return;
            const x = e.pageX - el.offsetLeft;
            const walk = (x - this.dragStartX) * 1.4;
            if (Math.abs(walk) > 6) {
                this.hasDraggedPills = true;
                e.preventDefault();
                el.scrollLeft = this.dragScrollLeft - walk;
                this.updatePillScrollState();
            }
        },
        endPillDrag() {
            this.isDraggingPills = false;
            setTimeout(() => { this.hasDraggedPills = false; }, 50);
        },
        updateUrl(itemId = null) {
            try {
                const url = new URL(window.location);
                if (itemId) {
                    url.searchParams.set('item', itemId);
                } else {
                    url.searchParams.delete('item');
                }
                window.history.replaceState({}, '', url);
            } catch (e) {}
        },
        get currentShareUrl() {
            if (!this.activeItem) return window.location.href;
            try {
                const url = new URL(window.location.origin + window.location.pathname);
                url.searchParams.set('item', this.activeItem.id);
                return url.toString();
            } catch (e) {
                return window.location.href;
            }
        },
        copyShareLink() {
            navigator.clipboard.writeText(this.currentShareUrl).then(() => {
                this.copiedShare = true;
                setTimeout(() => { this.copiedShare = false; }, 2500);
            });
        },
        nativeShare() {
            if (this.canShare && this.activeItem) {
                try {
                    navigator.share({
                        title: this.activeItem.review_title || 'Dubai Desert Safari Guest Photo',
                        text: 'Verified moments from Dunes Discovery Tourism in Dubai:',
                        url: this.currentShareUrl,
                    }).catch(() => {});
                } catch (e) {
                    this.copyShareLink();
                }
            } else {
                this.copyShareLink();
            }
        },
        filterCategory(cat, evt = null) {
            if (this.hasDraggedPills) return;
            this.category = cat;
            if (evt && evt.currentTarget && evt.currentTarget.scrollIntoView) {
                evt.currentTarget.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                setTimeout(() => this.updatePillScrollState(), 350);
            }
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
        openLightbox(item, push = true) {
            const currentList = this.visibleItems;
            this.currentIndex = currentList.findIndex(i => i.id === item.id);
            if (this.currentIndex === -1) this.currentIndex = 0;
            this.activeItem = currentList[this.currentIndex] || item;
            this.lightboxOpen = true;
            document.body.classList.add('overflow-hidden');
            if (push && this.activeItem) {
                this.updateUrl(this.activeItem.id);
            }
        },
        closeLightbox() {
            this.lightboxOpen = false;
            this.activeItem = null;
            this.copiedShare = false;
            document.body.classList.remove('overflow-hidden');
            this.updateUrl(null);
        },
        nextItem() {
            const list = this.visibleItems;
            if (!list.length) return;
            this.currentIndex = (this.currentIndex + 1) % list.length;
            this.activeItem = list[this.currentIndex];
            if (this.activeItem) {
                this.updateUrl(this.activeItem.id);
            }
        },
        prevItem() {
            const list = this.visibleItems;
            if (!list.length) return;
            this.currentIndex = (this.currentIndex - 1 + list.length) % list.length;
            this.activeItem = list[this.currentIndex];
            if (this.activeItem) {
                this.updateUrl(this.activeItem.id);
            }
        },
        reportBrokenLink(url, cardElement) {
            this.items = this.items.filter(i => i.url !== url);
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

        <!-- Gallery Section Intro Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <i class="bi bi-camera-fill text-primary"></i>
                    <span>Authentic Guest Photo Gallery</span>
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Verified guest photos captured across Dubai desert safaris, quad tracks, and Bedouin camps.
                </p>
            </div>
            @if(!empty($videoReviews) && count($videoReviews) > 0)
            <div class="shrink-0">
                <a href="#guest-video-reels" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold transition-all shadow-2xs hover:shadow-xs cursor-pointer">
                    <i class="bi bi-camera-video-fill text-rose-600"></i>
                    <span>Watch All {{ count($videoReviews) }} Video Reviews</span>
                    <i class="bi bi-arrow-down text-[10px]"></i>
                </a>
            </div>
            @endif
        </div>

        <!-- Filter Controls Bar -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8 pb-6 border-b border-slate-200">
            <!-- Category Pills with Desktop & Mobile Scrollable Reveal Container -->
            <div class="relative flex-1 min-w-0 flex items-center">
                <!-- Left Reveal Gradient & Scroll Button -->
                <div x-show="canScrollLeft"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-x-2"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-x-0"
                     x-transition:leave-end="opacity-0 -translate-x-2"
                     class="absolute start-0 inset-y-0 z-10 flex items-center pe-6 bg-gradient-to-r rtl:bg-gradient-to-l from-white via-white/95 to-transparent pointer-events-none"
                     style="display: none;">
                    <button type="button"
                            @click="scrollCategories(-260)"
                            class="pointer-events-auto w-9 h-9 rounded-full bg-white border border-slate-200/90 text-slate-700 hover:text-primary hover:border-primary/40 shadow-md hover:shadow-lg flex items-center justify-center transition-all cursor-pointer active:scale-95"
                            aria-label="{{ __('ui.gallery_section.prev') ?? 'Scroll left' }}">
                        <i class="bi bi-chevron-left rtl:rotate-180 text-xs font-bold"></i>
                    </button>
                </div>

                <!-- Scrollable Category Pills Strip -->
                <div x-ref="categoryPills"
                     @scroll.passive="updatePillScrollState()"
                     @wheel="onPillWheel($event)"
                     @mousedown="startPillDrag($event)"
                     @mousemove="movePillDrag($event)"
                     @mouseup="endPillDrag()"
                     @mouseleave="endPillDrag()"
                     :class="isDraggingPills ? 'cursor-grabbing select-none' : 'cursor-grab'"
                     class="flex items-center gap-2 overflow-x-auto py-1 scrollbar-none -mx-4 px-4 sm:mx-0 sm:px-1 snap-x scroll-smooth w-full">
                    <button type="button" 
                        @click="filterCategory('all', $event)" 
                        :class="category === 'all' ? 'bg-primary text-white shadow-sm ring-2 ring-primary/20' : 'bg-slate-100 hover:bg-slate-200/80 text-slate-700 hover:text-slate-900'"
                        class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-all duration-200 cursor-pointer inline-flex items-center gap-2 min-h-[40px] snap-start shrink-0">
                        <i class="bi bi-grid-fill"></i>
                        <span>{{ __('ui.gallery_section.filter_all') ?? 'All Moments' }}</span>
                    </button>

                    <button type="button" 
                        @click="filterCategory('red_dunes', $event)" 
                        :class="category === 'red_dunes' ? 'bg-primary text-white shadow-sm ring-2 ring-primary/20' : 'bg-slate-100 hover:bg-slate-200/80 text-slate-700 hover:text-slate-900'"
                        class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-all duration-200 cursor-pointer inline-flex items-center gap-2 min-h-[40px] snap-start shrink-0">
                        <i class="bi bi-compass-fill"></i>
                        <span>{{ __('ui.gallery_section.filter_red_dunes') ?? 'Red Dunes & Safari' }}</span>
                    </button>

                    <button type="button" 
                        @click="filterCategory('buggy_quad', $event)" 
                        :class="category === 'buggy_quad' ? 'bg-primary text-white shadow-sm ring-2 ring-primary/20' : 'bg-slate-100 hover:bg-slate-200/80 text-slate-700 hover:text-slate-900'"
                        class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-all duration-200 cursor-pointer inline-flex items-center gap-2 min-h-[40px] snap-start shrink-0">
                        <i class="bi bi-lightning-charge-fill"></i>
                        <span>{{ __('ui.gallery_section.filter_buggy_quad') ?? 'Buggies & Quads' }}</span>
                    </button>

                    <button type="button" 
                        @click="filterCategory('camp_bbq', $event)" 
                        :class="category === 'camp_bbq' ? 'bg-primary text-white shadow-sm ring-2 ring-primary/20' : 'bg-slate-100 hover:bg-slate-200/80 text-slate-700 hover:text-slate-900'"
                        class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-all duration-200 cursor-pointer inline-flex items-center gap-2 min-h-[40px] snap-start shrink-0">
                        <i class="bi bi-fire"></i>
                        <span>{{ __('ui.gallery_section.filter_camp_bbq') ?? 'Camp & BBQ' }}</span>
                    </button>

                    <button type="button" 
                        @click="filterCategory('sunset_camels', $event)" 
                        :class="category === 'sunset_camels' ? 'bg-primary text-white shadow-sm ring-2 ring-primary/20' : 'bg-slate-100 hover:bg-slate-200/80 text-slate-700 hover:text-slate-900'"
                        class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-all duration-200 cursor-pointer inline-flex items-center gap-2 min-h-[40px] snap-start shrink-0">
                        <i class="bi bi-sunset-fill"></i>
                        <span>{{ __('ui.gallery_section.filter_sunset_camels') ?? 'Sunset & Camels' }}</span>
                    </button>

                    <button type="button" 
                        @click="filterCategory('city_landmarks', $event)" 
                        :class="category === 'city_landmarks' ? 'bg-primary text-white shadow-sm ring-2 ring-primary/20' : 'bg-slate-100 hover:bg-slate-200/80 text-slate-700 hover:text-slate-900'"
                        class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-all duration-200 cursor-pointer inline-flex items-center gap-2 min-h-[40px] snap-start shrink-0">
                        <i class="bi bi-building"></i>
                        <span>{{ __('ui.gallery_section.filter_city_landmarks') ?? 'City Tours & Landmarks' }}</span>
                    </button>

                    <button type="button" 
                        @click="filterCategory('marina_cruise', $event)" 
                        :class="category === 'marina_cruise' ? 'bg-primary text-white shadow-sm ring-2 ring-primary/20' : 'bg-slate-100 hover:bg-slate-200/80 text-slate-700 hover:text-slate-900'"
                        class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-all duration-200 cursor-pointer inline-flex items-center gap-2 min-h-[40px] snap-start shrink-0">
                        <i class="bi bi-water"></i>
                        <span>{{ __('ui.gallery_section.filter_marina_cruise') ?? 'Dhow & Marina Cruise' }}</span>
                    </button>

                    @if(($categoryStats['videos'] ?? 0) > 0)
                    <button type="button" 
                        @click="filterCategory('videos', $event)" 
                        :class="category === 'videos' ? 'bg-primary text-white shadow-sm ring-2 ring-primary/20' : 'bg-slate-100 hover:bg-slate-200/80 text-slate-700 hover:text-slate-900'"
                        class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-all duration-200 cursor-pointer inline-flex items-center gap-2 min-h-[40px] snap-start shrink-0">
                        <i class="bi bi-camera-video-fill"></i>
                        <span>{{ __('ui.gallery_section.filter_videos') ?? 'Video Clips' }}</span>
                    </button>
                    @endif
                </div>

                <!-- Right Reveal Gradient & Scroll Button -->
                <div x-show="canScrollRight"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-x-2"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-x-0"
                     x-transition:leave-end="opacity-0 translate-x-2"
                     class="absolute end-0 inset-y-0 z-10 flex items-center ps-8 bg-gradient-to-l rtl:bg-gradient-to-r from-white via-white/95 to-transparent pointer-events-none"
                     style="display: none;">
                    <button type="button"
                            @click="scrollCategories(260)"
                            class="pointer-events-auto w-9 h-9 rounded-full bg-white border border-slate-200/90 text-slate-700 hover:text-primary hover:border-primary/40 shadow-md hover:shadow-lg flex items-center justify-center transition-all cursor-pointer active:scale-95"
                            aria-label="{{ __('ui.gallery_section.next') ?? 'Scroll right' }}">
                        <i class="bi bi-chevron-right rtl:rotate-180 text-xs font-bold"></i>
                    </button>
                </div>
            </div>

            <!-- Source Filter Segment -->
            <div class="flex items-center gap-1.5 shrink-0 bg-slate-100 p-1 rounded-full self-start lg:self-auto max-w-full overflow-x-auto scrollbar-none">
                <button type="button" 
                    @click="filterSource('all')"
                    :class="source === 'all' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="px-3 py-1.5 rounded-full text-xs transition-all cursor-pointer whitespace-nowrap min-h-[36px]">
                    All Sources
                </button>
                <button type="button" 
                    @click="filterSource('ddt')"
                    :class="source === 'ddt' ? 'bg-white text-amber-600 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="px-3 py-1.5 rounded-full text-xs transition-all cursor-pointer inline-flex items-center gap-1 whitespace-nowrap min-h-[36px]">
                    <i class="bi bi-camera-fill text-amber-500 text-xs"></i>
                    DDT Moments
                </button>
                <button type="button" 
                    @click="filterSource('google')"
                    :class="source === 'google' ? 'bg-white text-blue-600 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="px-3 py-1.5 rounded-full text-xs transition-all cursor-pointer inline-flex items-center gap-1 whitespace-nowrap min-h-[36px]">
                    <img src="{{ asset('images/Google-G.avif') }}" alt="Google" class="w-3.5 h-3.5 inline-block" width="14" height="14" onerror="this.onerror=null;this.src='{{ asset('images/google.svg') }}'">
                    Google
                </button>
                <button type="button" 
                    @click="filterSource('tripadvisor')"
                    :class="source === 'tripadvisor' ? 'bg-white text-emerald-700 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="px-3 py-1.5 rounded-full text-xs transition-all cursor-pointer inline-flex items-center gap-1 whitespace-nowrap min-h-[36px]">
                    <i class="bi bi-star-fill text-emerald-500 text-xs"></i>
                    TripAdvisor
                </button>
            </div>
        </div>

        <!-- Media Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6" id="galleryGrid">
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
                                <template x-if="item.duration">
                                    <span class="absolute bottom-2.5 end-2.5 bg-slate-950/85 backdrop-blur-xs text-white text-[10px] font-mono font-bold px-2 py-0.5 rounded-md flex items-center gap-1 shadow-sm pointer-events-none">
                                        <i class="bi bi-clock-fill text-amber-400 text-[9px]"></i>
                                        <span x-text="Math.floor(item.duration / 60) + ':' + String(item.duration % 60).padStart(2, '0')"></span>
                                    </span>
                                </template>
                            </div>
                        </template>

                        <template x-if="item.type !== 'video'">
                            <img :src="item.url" 
                                 :alt="item.alt || item.review_title || 'Guest Safari Photo'" 
                                 :title="item.title || item.review_title"
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
                            <template x-if="item.type === 'video'">
                                <span class="bg-gradient-to-r from-red-600 to-amber-600 text-white rounded-full px-2.5 py-1 text-[11px] font-bold flex items-center gap-1 shadow-xs">
                                    <i class="bi bi-camera-video-fill text-[10px]"></i>
                                    <span>Verified Video</span>
                                </span>
                            </template>
                            <template x-if="item.type !== 'video' && item.source === 'ddt'">
                                <span class="bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-full px-2.5 py-1 text-[11px] font-bold flex items-center gap-1 shadow-xs">
                                    <i class="bi bi-camera-fill text-[10px]"></i>
                                    <span>DDT Moments</span>
                                </span>
                            </template>
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
                            <template x-if="item.source !== 'google' && item.source !== 'tripadvisor' && item.source !== 'ddt'">
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
         class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 select-none"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @touchstart.passive="touchStartX = $event.changedTouches[0].screenX"
         @touchend.passive="const diff = $event.changedTouches[0].screenX - touchStartX; if (diff > 45) prevItem(); else if (diff < -45) nextItem();"
         role="dialog"
         aria-modal="true">

        <!-- Darkened Backdrop Overlay (clicking backdrop closes lightbox) -->
        <div class="fixed inset-0 bg-slate-950/95 backdrop-blur-md transition-opacity cursor-pointer"
             @click="closeLightbox()"></div>

        <!-- Close Button Top Right -->
        <button type="button" 
                @click.stop="closeLightbox()" 
                class="absolute top-4 end-4 sm:top-6 sm:end-6 z-50 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 text-white flex items-center justify-center transition-all cursor-pointer border border-white/20 shadow-lg hover:scale-105 active:scale-95">
            <i class="bi bi-x-lg text-lg"></i>
            <span class="sr-only">Close</span>
        </button>

        <!-- Previous Button -->
        <button type="button" 
                @click.stop="prevItem()" 
                class="absolute start-2 sm:start-6 top-1/2 -translate-y-1/2 z-50 w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-white/10 hover:bg-white/25 text-white flex items-center justify-center transition-all cursor-pointer border border-white/20 shadow-lg hover:scale-105 active:scale-95">
            <i class="bi bi-chevron-left text-xl"></i>
            <span class="sr-only">Previous</span>
        </button>

        <!-- Next Button -->
        <button type="button" 
                @click.stop="nextItem()" 
                class="absolute end-2 sm:end-6 top-1/2 -translate-y-1/2 z-50 w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-white/10 hover:bg-white/25 text-white flex items-center justify-center transition-all cursor-pointer border border-white/20 shadow-lg hover:scale-105 active:scale-95">
            <i class="bi bi-chevron-right text-xl"></i>
            <span class="sr-only">Next</span>
        </button>

        <!-- Modal Content Container -->
        <div class="relative z-20 w-full max-w-5xl max-h-[92vh] flex flex-col lg:flex-row bg-slate-900 rounded-3xl overflow-hidden border border-white/10 shadow-2xl" 
             @click.stop>

            <!-- Media Preview Area -->
            <div class="relative lg:w-3/5 bg-black flex items-center justify-center min-h-[240px] sm:min-h-[340px] max-h-[48vh] sm:max-h-[60vh] lg:max-h-[85vh] p-2">
                <template x-if="activeItem && activeItem.type === 'video'">
                    <div class="w-full h-full flex items-center justify-center">
                        <video :src="activeItem.url" controls autoplay class="max-w-full max-h-[46vh] sm:max-h-[58vh] lg:max-h-[80vh] rounded-xl object-contain"></video>
                    </div>
                </template>
                <template x-if="activeItem && activeItem.type !== 'video'">
                    <img :src="activeItem.url" 
                         :alt="activeItem.alt || activeItem.review_title || 'Guest Safari Photo'" 
                         :title="activeItem.title || activeItem.review_title"
                         class="max-w-full max-h-[46vh] sm:max-h-[58vh] lg:max-h-[80vh] rounded-xl object-contain select-none"
                         referrerpolicy="no-referrer"
                         x-on:error="reportBrokenLink(activeItem.url); nextItem();">
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
                                    <template x-if="activeItem && activeItem.source === 'ddt'">
                                        <span class="text-amber-400 font-bold text-xs inline-flex items-center gap-1">
                                            <i class="bi bi-camera-fill text-xs text-amber-400"></i>
                                            DDT Moments
                                        </span>
                                    </template>
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
                                <template x-if="activeItem && activeItem.location">
                                    <div class="text-[11px] text-white/60 flex items-center gap-1 mt-1">
                                        <i class="bi bi-geo-alt-fill text-amber-400 text-xs"></i>
                                        <span x-text="activeItem.location"></span>
                                    </div>
                                </template>
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
                    <p class="text-white/80 text-sm leading-relaxed mb-4 whitespace-pre-line" x-text="activeItem ? (activeItem.full_review_text || activeItem.review_text) : ''"></p>

                    <!-- Spoken Transcript Box (AISEO & Accessibility) -->
                    <template x-if="activeItem && activeItem.transcript">
                        <div class="mb-5 p-3.5 bg-slate-800/90 rounded-2xl border border-white/10 text-xs">
                            <div class="font-bold text-amber-400 mb-1 flex items-center gap-1.5">
                                <i class="bi bi-chat-quote-fill"></i> Spoken Customer Review Transcript:
                            </div>
                            <p class="text-slate-200 italic leading-relaxed" x-text="'&ldquo;' + activeItem.transcript + '&rdquo;'"></p>
                        </div>
                    </template>
                </div>

                <!-- Social Sharing Bar (Deep-Link Magnet) -->
                <div class="py-2.5 px-3 bg-slate-800/80 rounded-2xl border border-white/10 mb-3 flex items-center justify-between gap-2 flex-wrap">
                    <span class="text-xs font-bold text-slate-300 flex items-center gap-1.5">
                        <i class="bi bi-share text-amber-400"></i> Share Moment:
                    </span>
                    <div class="flex items-center gap-1.5">
                        <!-- 1-Click Copy Link -->
                        <button type="button" 
                                @click="copyShareLink()"
                                class="px-2.5 py-1.5 rounded-xl text-[11px] font-bold transition-all inline-flex items-center gap-1 cursor-pointer shadow-2xs border"
                                :class="copiedShare ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-slate-700/80 hover:bg-slate-600 text-slate-200 border-white/10'">
                            <i :class="copiedShare ? 'bi bi-check2 text-xs' : 'bi bi-link-45deg text-sm'"></i>
                            <span x-text="copiedShare ? 'Copied!' : 'Copy Link'"></span>
                        </button>
                        
                        <!-- WhatsApp Share -->
                        <a :href="'https://api.whatsapp.com/send?text=' + encodeURIComponent('Check out this verified Dubai desert safari photo: ' + currentShareUrl)" 
                           target="_blank" 
                           rel="noopener"
                           class="px-2.5 py-1.5 rounded-xl text-[11px] font-bold bg-emerald-600/80 hover:bg-emerald-600 text-white border border-emerald-500/30 transition-all inline-flex items-center gap-1 cursor-pointer">
                            <i class="bi bi-whatsapp text-xs"></i> WhatsApp
                        </a>

                        <!-- Mobile Native Share -->
                        <template x-if="canShare">
                            <button type="button" 
                                    @click="nativeShare()"
                                    class="p-1.5 rounded-xl bg-slate-700/80 hover:bg-slate-600 text-slate-200 border border-white/10 transition-all inline-flex items-center justify-center cursor-pointer"
                                    title="More share options">
                                <i class="bi bi-three-dots text-xs"></i>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Action CTAs -->
                <div class="pt-4 border-t border-white/10 space-y-3">
                    <template x-if="activeItem && activeItem.tour_slug">
                        <a :href="'/' + activeItem.tour_slug"
                           class="btn-desert-animated w-full py-3 px-4 rounded-xl font-bold text-sm text-white shadow-lg flex items-center justify-center gap-2 cursor-pointer text-center">
                            <i class="bi bi-compass-fill text-base"></i>
                            <span x-text="'Book ' + (activeItem.tour_name || 'This Tour')"></span>
                        </a>
                    </template>

                    <template x-if="!activeItem || !activeItem.tour_slug">
                        <button type="button" 
                                class="btn-desert-animated w-full py-3 px-4 rounded-xl font-bold text-sm text-white shadow-lg flex items-center justify-center gap-2 cursor-pointer"
                                @click="closeLightbox(); $store.modal.open('booking');">
                            <i class="bi bi-calendar-check-fill text-base"></i>
                            <span>{{ __('ui.gallery_section.book_this') ?? 'Book This Safari Adventure' }}</span>
                        </button>
                    </template>

                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $settings['site_whatsapp'] ?? '971502456056') }}?text={{ urlencode('Hello Dunes Discovery, I saw your guest gallery photos and want to book a desert safari!') }}" 
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

<!-- ── Dedicated Guest Video Reviews Section ({All Videos}) ────────────────────── -->
@if(!empty($videoReviews) && count($videoReviews) > 0)
<section class="py-12 sm:py-20 bg-slate-900 text-white relative overflow-hidden border-t border-slate-800" id="guest-video-reels"
    x-data="{
        videos: @js($videoReviews),
        activeVideo: null,
        modalOpen: false,
        openModal(v) {
            this.activeVideo = v;
            this.modalOpen = true;
            document.body.classList.add('overflow-hidden');
            this.$nextTick(() => {
                const player = this.$refs.galleryReelsPlayer;
                if (player) {
                    player.currentTime = 0;
                    player.play().catch(() => {});
                }
            });
        },
        closeModal() {
            const player = this.$refs.galleryReelsPlayer;
            if (player) { player.pause(); }
            this.modalOpen = false;
            this.activeVideo = null;
            document.body.classList.remove('overflow-hidden');
        },
        scrollReels(delta) {
            const el = this.$refs.reelsTrack;
            if (el) el.scrollBy({ left: delta, behavior: 'smooth' });
        }
    }">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Section Header with Controls -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8 sm:mb-12">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/20 text-rose-300 text-xs font-bold border border-rose-500/30 mb-3">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                    <span class="w-2 h-2 rounded-full bg-rose-500 -ml-4"></span>
                    <span>Real Customer Video Reviews</span>
                </div>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight">
                    Watch Unfiltered Guest Stories & Video Reviews
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 mt-2 max-w-2xl leading-relaxed">
                    Real, self-recorded customer feedback filmed on the dunes, inside 4-seater buggies, and under the desert stars with Captain Malik.
                </p>
            </div>

            <!-- Scroll Buttons -->
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" 
                        @click="scrollReels(-320)" 
                        class="w-10 h-10 rounded-full bg-slate-800 hover:bg-slate-700 text-white flex items-center justify-center border border-slate-700 transition-all cursor-pointer shadow-sm hover:scale-105 active:scale-95"
                        aria-label="Previous videos">
                    <i class="bi bi-chevron-left text-sm"></i>
                </button>
                <button type="button" 
                        @click="scrollReels(320)" 
                        class="w-10 h-10 rounded-full bg-slate-800 hover:bg-slate-700 text-white flex items-center justify-center border border-slate-700 transition-all cursor-pointer shadow-sm hover:scale-105 active:scale-95"
                        aria-label="Next videos">
                    <i class="bi bi-chevron-right text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Horizontal Scrolling Reels Track -->
        <div x-ref="reelsTrack"
             class="flex gap-4 sm:gap-6 overflow-x-auto pb-4 snap-x snap-mandatory scrollbar-none -mx-4 px-4 sm:mx-0 sm:px-0">
            @foreach($videoReviews as $v)
                <div class="shrink-0 w-64 sm:w-72 group relative bg-slate-800 rounded-2xl overflow-hidden shadow-lg border border-slate-700 hover:border-amber-500/60 transition-all duration-300 cursor-pointer snap-start flex flex-col"
                     @click="openModal(@js($v))">
                    
                    <!-- 9:16 Video Thumbnail Container -->
                    <div class="relative aspect-9/16 w-full overflow-hidden bg-slate-950">
                        <img src="{{ $v['poster_url'] }}" 
                             alt="{{ $v['title'] }}" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             loading="lazy">
                        
                        <!-- Top Badges Overlay -->
                        <div class="absolute top-3 inset-x-3 flex items-center justify-between pointer-events-none z-10">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-950/80 backdrop-blur-md text-[10px] font-bold text-white border border-white/10 shadow-xs">
                                <i class="bi bi-patch-check-fill text-amber-400"></i>
                                <span>Verified Guest</span>
                            </span>
                            @if(!empty($v['reviewer_country']))
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-950/80 backdrop-blur-md text-[10px] font-semibold text-slate-200 border border-white/10">
                                <span>{{ $v['reviewer_country'] }}</span>
                            </span>
                            @endif
                        </div>

                        <!-- Center Play Button Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/20 to-transparent flex items-center justify-center group-hover:bg-slate-950/30 transition-colors">
                            <span class="w-14 h-14 rounded-full bg-gradient-to-r from-red-600 to-amber-500 text-white flex items-center justify-center shadow-xl transform group-hover:scale-115 transition-transform duration-300">
                                <i class="bi bi-play-fill text-3xl ml-1"></i>
                            </span>
                        </div>

                        <!-- Bottom Meta on Thumbnail -->
                        <div class="absolute bottom-3 inset-x-3 pointer-events-none z-10">
                            <div class="flex items-center justify-between text-white text-[11px] mb-1">
                                <span class="font-bold flex items-center gap-1 text-amber-400">
                                    <i class="bi bi-star-fill text-[10px]"></i>
                                    <span>5.0</span>
                                </span>
                                <span class="bg-slate-950/80 backdrop-blur-xs px-2 py-0.5 rounded text-[10px] font-mono font-semibold">
                                    {{ floor(($v['duration'] ?? 30) / 60) }}:{{ str_pad(($v['duration'] ?? 30) % 60, 2, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>
                            <h3 class="font-bold text-white text-xs sm:text-sm line-clamp-2 leading-snug drop-shadow-md">
                                {{ $v['title'] }}
                            </h3>
                            <p class="text-[11px] text-slate-300 line-clamp-1 mt-0.5">
                                {{ $v['reviewer_name'] }} • {{ $v['tour_name'] }}
                            </p>
                        </div>
                    </div>

                    <!-- Card Bottom Snippet -->
                    <div class="p-3 bg-slate-800/90 flex flex-col justify-between flex-1 border-t border-slate-700/60">
                        <p class="text-[11px] text-slate-300 line-clamp-2 italic">
                            "{{ Str::limit($v['caption'], 85) }}"
                        </p>
                        <div class="mt-2 pt-2 border-t border-slate-700/50 flex items-center justify-between text-[11px]">
                            <span class="text-amber-400 font-semibold inline-flex items-center gap-1 group-hover:underline">
                                <i class="bi bi-play-circle text-xs"></i> Play Review
                            </span>
                            <span class="text-slate-400 text-[10px]">{{ $v['formatted_date'] }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- ── Fullscreen Video Playback Modal ────────────────────────────────────── -->
    <div x-show="modalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         role="dialog"
         aria-modal="true">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-950/95 backdrop-blur-md cursor-pointer"
             @click="closeModal()"></div>

        <!-- Close Button -->
        <button type="button" 
                @click="closeModal()" 
                class="absolute top-4 end-4 sm:top-6 sm:end-6 z-50 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 text-white flex items-center justify-center transition-all cursor-pointer border border-white/20 shadow-lg hover:scale-105 active:scale-95">
            <i class="bi bi-x-lg text-lg"></i>
            <span class="sr-only">Close video</span>
        </button>

        <!-- Modal Container -->
        <div class="relative z-10 w-full max-w-lg sm:max-w-xl md:max-w-2xl bg-slate-900 rounded-3xl overflow-hidden shadow-2xl border border-slate-800 flex flex-col max-h-[92vh]"
             @click.outside="closeModal()"
             @keydown.escape.window="closeModal()">
            
            <!-- Video Player -->
            <div class="relative w-full bg-black aspect-9/16 sm:aspect-video flex items-center justify-center max-h-[60vh] overflow-hidden">
                <template x-if="activeVideo">
                    <video x-ref="galleryReelsPlayer" 
                           :src="activeVideo.video_url || activeVideo.url" 
                           :poster="activeVideo.poster_url || activeVideo.thumbnail_url"
                           controls 
                           playsinline
                           preload="metadata"
                           class="w-full h-full object-contain max-h-[60vh]">
                    </video>
                </template>
            </div>

            <!-- Video Info & Booking CTA -->
            <div class="p-4 sm:p-6 bg-slate-900 text-white overflow-y-auto" x-show="activeVideo">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 text-[11px] font-bold border border-amber-500/30 mb-1.5">
                            <i class="bi bi-patch-check-fill text-amber-400"></i>
                            <span x-text="activeVideo ? activeVideo.tour_name : 'Dubai Desert Safari'"></span>
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-white line-clamp-2" x-text="activeVideo ? activeVideo.title : ''"></h3>
                        <p class="text-xs text-slate-400 mt-0.5" x-text="activeVideo ? (activeVideo.reviewer_name + ' • ' + activeVideo.reviewer_country + ' • ' + activeVideo.formatted_date) : ''"></p>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="flex items-center gap-1 text-amber-400 text-xs font-bold justify-end">
                            <i class="bi bi-star-fill"></i>
                            <span x-text="activeVideo ? activeVideo.rating.toFixed(1) : '5.0'"></span>
                        </div>
                        <span class="text-[10px] text-slate-400">Verified Experience</span>
                    </div>
                </div>

                <!-- Quote / Transcript -->
                <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700/60 mb-4" x-show="activeVideo && (activeVideo.transcript || activeVideo.caption)">
                    <p class="text-xs text-slate-300 italic" x-text="activeVideo ? ('“' + (activeVideo.transcript || activeVideo.caption) + '”') : ''"></p>
                </div>

                <!-- Direct Action Buttons -->
                <div class="flex flex-col sm:flex-row items-center gap-2 pt-2 border-t border-slate-800">
                    <template x-if="activeVideo && activeVideo.tour_slug">
                        <a :href="'/' + activeVideo.tour_slug"
                           class="btn-desert-animated w-full sm:flex-1 py-3 px-4 rounded-xl font-bold text-xs sm:text-sm text-white shadow-lg flex items-center justify-center gap-2 text-center cursor-pointer">
                            <i class="bi bi-compass-fill"></i>
                            <span x-text="'Book ' + (activeVideo.tour_name || 'This Tour')"></span>
                        </a>
                    </template>
                    <button type="button" 
                            class="w-full sm:flex-1 py-3 px-4 rounded-xl font-bold text-xs sm:text-sm text-slate-200 bg-slate-800 hover:bg-slate-700 border border-slate-700 flex items-center justify-center gap-2 transition-colors cursor-pointer"
                            @click="closeModal(); $store.modal.open('booking');">
                        <i class="bi bi-calendar-check-fill text-amber-400"></i>
                        <span>{{ __('ui.common.book_online_now') ?? 'Book Desert Safari' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

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
