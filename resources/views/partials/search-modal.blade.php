@php
    try {
        $searchCatalogArray = \Illuminate\Support\Facades\Cache::remember('site_search_modal_catalog_v4', 3600, function() {
            return \App\Models\Tour::where('status', 'active')
                ->with(['tiers', 'category'])
                ->orderBy('priority', 'asc')
                ->get()
                ->map(function($t) {
                    $minPrice = $t->tiers->min('pivot.price') ?? 0;
                    $thumb = $t->thumb_image ?: $t->hero_image ?: 'desert-safari-poster.avif';
                    $thumb = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $thumb);
                    return [
                        'id' => (int) $t->id,
                        'name' => (string) $t->name,
                        'slug' => (string) $t->slug,
                        'url' => url('/' . $t->slug),
                        'category' => $t->category ? (string) $t->category->name : 'Desert Safari',
                        'category_slug' => $t->category ? (string) $t->category->slug : 'desert-safari',
                        'duration' => (string) ($t->duration ?: '4-6 Hours'),
                        'rating' => (float) ($t->rating ?: 4.9),
                        'reviews_count' => (int) ($t->review_count ?: 850),
                        'price' => (float) $minPrice,
                        'price_formatted' => $minPrice > 0 ? 'From AED ' . number_format($minPrice) : 'Best Rates',
                        'image' => asset('images/' . $thumb),
                        'is_bestseller' => (bool) $t->is_bestseller,
                        'is_featured' => (bool) $t->is_featured,
                        'badge' => $t->is_bestseller ? 'Bestseller' : ($t->is_featured ? 'Popular' : ($t->category ? (string) $t->category->name : 'Safari')),
                        'highlights' => $t->short_desc ? \Illuminate\Support\Str::limit(strip_tags($t->short_desc), 85) : 'Luxury 4x4 Transfers, Red Dunes & BBQ Dinner',
                        'keywords' => strtolower($t->name . ' ' . ($t->meta_keywords ?? '') . ' ' . ($t->category ? (string) $t->category->name : '') . ' ' . ($t->short_desc ?? '')),
                    ];
                })->values()->all();
        });
        if (!is_array($searchCatalogArray)) {
            $searchCatalogArray = [];
        }
    } catch (\Throwable $e) {
        $searchCatalogArray = [];
    }
@endphp

<!-- Preloaded Catalog JSON Script for 0ms Client Matching (Clean and Safe from Attribute Quotes) -->
<script type="application/json" id="siteSearchCatalogData">
{!! json_encode($searchCatalogArray, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
</script>

<!-- Global Interactive Search Modal (Tailwind v4 + Alpine.js) -->
<div id="globalSearchModal"
     x-data="globalSearchModal({
         searchUrl: '{{ route('tours.search') }}',
         liveSearchUrl: '{{ route('tours.search.live') }}'
     })"
     x-show="$store.modal.active === 'search'"
     x-cloak
     style="display: none;"
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="globalSearchModalLabel" 
     role="dialog" 
     aria-modal="true"
     @keydown.escape.window="$store.modal.close()">
     
    <!-- Backdrop -->
    <div x-show="$store.modal.active === 'search'"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity z-0"
         @click="$store.modal.close()"></div>

    <!-- Dialog Panel -->
    <div class="relative z-10 min-h-full flex items-center justify-center p-2.5 sm:p-4 text-center">
        <div x-show="$store.modal.active === 'search'"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="relative z-10 w-full max-w-2xl transform overflow-hidden rounded-3xl bg-white text-left align-middle shadow-2xl transition-all border border-slate-200"
             @click.stop>
            
            <!-- Luxury Orange & Gold Ambient Top Accent Bar -->
            <div class="h-1.5 w-full bg-gradient-to-r from-orange-500 via-amber-400 to-yellow-500"></div>

            <!-- Header -->
            <div class="flex items-center justify-between p-4 sm:p-5 pb-3">
                <div class="flex items-center gap-2 sm:gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-primary/10 text-primary border border-primary/20">
                        <i class="bi bi-search"></i> Search Dubai Safaris
                    </span>
                    <span class="hidden sm:inline-block text-[11px] font-semibold text-slate-400">DET Licensed #1430583</span>
                </div>
                <button type="button" 
                        @click="$store.modal.close()" 
                        class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 hover:text-slate-800 hover:bg-slate-200 flex items-center justify-center transition-colors cursor-pointer" 
                        aria-label="Close search modal">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="p-4 sm:p-6 pt-0">
                <!-- Search Input Form -->
                <form action="{{ route('tours.search') }}" method="GET" id="globalSearchForm" @submit="handleEnter($event)">
                    <div class="relative mb-3.5">
                        <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg sm:text-xl"></i>
                        <input type="text" 
                               name="q" 
                               id="globalSearchModalInput" 
                               x-ref="searchInput"
                               x-model="query"
                               @input="handleInput"
                               @keydown.arrow-down.prevent="navigateDown"
                               @keydown.arrow-up.prevent="navigateUp"
                               @keydown.enter="handleEnter($event)"
                               @keydown.escape.prevent="handleEscape"
                               class="w-full pl-11 sm:pl-12 pr-28 sm:pr-32 py-3.5 sm:py-4 rounded-2xl bg-slate-50 border-2 border-slate-200 focus:border-primary focus:bg-white text-sm sm:text-base font-semibold text-slate-900 placeholder:text-slate-400 focus:outline-none transition-all shadow-2xs" 
                               placeholder="Search safaris, buggies, quad biking, cruises..." 
                               autocomplete="off" 
                               required>
                        
                        <!-- Input Action Controls (Clear + Loading Indicator) -->
                        <div class="absolute right-20 sm:right-24 top-1/2 -translate-y-1/2 flex items-center gap-1.5">
                            <span x-show="loading" class="text-primary text-sm inline-flex items-center animate-spin" title="Searching live catalog...">
                                <i class="bi bi-arrow-clockwise"></i>
                            </span>
                            <button type="button" 
                                    x-show="query.length > 0" 
                                    @click="clearQuery" 
                                    class="w-6 h-6 rounded-full bg-slate-200 hover:bg-slate-300 text-slate-600 flex items-center justify-center transition-colors text-xs cursor-pointer"
                                    aria-label="Clear query">
                                <i class="bi bi-x-lg text-[10px]"></i>
                            </button>
                        </div>

                        <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 px-4 py-2 sm:py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs sm:text-sm shadow-xs transition-colors cursor-pointer">
                            Search
                        </button>
                    </div>
                </form>

                <!-- STATE 1: INITIAL STATE (query.length < 2) -->
                <div x-show="!isSearching" class="space-y-4">
                    <!-- Popular Searches -->
                    <div class="trending-searches-wrapper">
                        <div class="flex items-center justify-between mb-2.5">
                            <span class="text-[11px] font-black uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                                <i class="bi bi-fire text-amber-500"></i> Popular Searches
                            </span>
                            <span class="hidden sm:inline-block text-[11px] text-slate-400">Press <kbd class="px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200 font-mono text-[10px]">Ctrl+K</kbd> anytime</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="setQuery('Evening Desert Safari')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 hover:bg-orange-50 hover:text-primary text-slate-700 transition-colors border border-slate-200/80 cursor-pointer">
                                <i class="bi bi-sunset text-amber-500"></i> Evening Desert Safari
                            </button>
                            <button type="button" @click="setQuery('Can-Am Dune Buggy')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 hover:bg-orange-50 hover:text-primary text-slate-700 transition-colors border border-slate-200/80 cursor-pointer">
                                <i class="bi bi-speedometer2 text-red-500"></i> Can-Am Dune Buggy
                            </button>
                            <button type="button" @click="setQuery('Quad Biking Lahbab')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 hover:bg-orange-50 hover:text-primary text-slate-700 transition-colors border border-slate-200/80 cursor-pointer">
                                <i class="bi bi-compass text-primary"></i> Quad Biking Lahbab
                            </button>
                            <button type="button" @click="setQuery('VIP Luxury Desert Safari')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 hover:bg-orange-50 hover:text-primary text-slate-700 transition-colors border border-slate-200/80 cursor-pointer">
                                <i class="bi bi-gem text-amber-500"></i> VIP Luxury Table
                            </button>
                            <button type="button" @click="setQuery('Morning Desert Safari')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 hover:bg-orange-50 hover:text-primary text-slate-700 transition-colors border border-slate-200/80 cursor-pointer">
                                <i class="bi bi-sunrise text-sky-500"></i> Morning Safari
                            </button>
                            <button type="button" @click="setQuery('Dubai Marina Dhow Cruise')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 hover:bg-orange-50 hover:text-primary text-slate-700 transition-colors border border-slate-200/80 cursor-pointer">
                                <i class="bi bi-water text-primary"></i> Marina Dhow Cruise
                            </button>
                            <button type="button" @click="setQuery('Abu Dhabi City Tour')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 hover:bg-orange-50 hover:text-primary text-slate-700 transition-colors border border-slate-200/80 cursor-pointer">
                                <i class="bi bi-building text-slate-500"></i> Abu Dhabi City Tour
                            </button>
                            <button type="button" @click="setQuery('Private Land Cruiser')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 hover:bg-orange-50 hover:text-primary text-slate-700 transition-colors border border-slate-200/80 cursor-pointer">
                                <i class="bi bi-car-front-fill text-emerald-500"></i> Private Land Cruiser
                            </button>
                        </div>
                    </div>
                </div>

                <!-- STATE 2: LIVE SEARCH RESULTS & FILTERS (query.length >= 2) -->
                <div x-show="isSearching" class="space-y-3">
                    <!-- Real-Time Category Narrowing Filters Bar -->
                    <div x-show="availableCategories.length > 1" class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
                        <button type="button" 
                                @click="selectCategory('all')"
                                class="px-3 py-1 rounded-full text-xs font-bold transition-all cursor-pointer shrink-0 inline-flex items-center gap-1"
                                :class="categoryFilter === 'all' ? 'bg-primary text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                            <span>All</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full" 
                                  :class="categoryFilter === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'" 
                                  x-text="totalMatchCount"></span>
                        </button>
                        <template x-for="cat in availableCategories" :key="cat.slug">
                            <button type="button" 
                                    @click="selectCategory(cat.slug)"
                                    class="px-3 py-1 rounded-full text-xs font-bold transition-all cursor-pointer shrink-0 inline-flex items-center gap-1"
                                    :class="categoryFilter === cat.slug ? 'bg-primary text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                                <span x-text="cat.name"></span>
                                <span class="text-[10px] px-1.5 py-0.2 rounded-full" 
                                      :class="categoryFilter === cat.slug ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'" 
                                      x-text="cat.count"></span>
                            </button>
                        </template>
                    </div>

                    <!-- Results Count & Keyboard Navigation Hint -->
                    <div class="flex items-center justify-between text-xs text-slate-500 px-1 pt-0.5">
                        <span class="font-semibold">
                            Showing <strong class="text-slate-900" x-text="filteredResults.length"></strong> matching experience<span x-show="filteredResults.length !== 1">s</span>
                        </span>
                        <span class="text-[11px] text-slate-400 hidden sm:inline-flex items-center gap-1">
                            Navigate <kbd class="px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200 font-mono text-[9px]">↑</kbd> <kbd class="px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200 font-mono text-[9px]">↓</kbd> • Select <kbd class="px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200 font-mono text-[9px]">Enter</kbd>
                        </span>
                    </div>

                    <!-- Scrollable Live Results List -->
                    <div x-ref="resultsList" class="max-h-[360px] sm:max-h-[400px] overflow-y-auto space-y-2 pr-1 divide-y divide-slate-100 scrollbar-thin">
                        <template x-for="(tour, index) in filteredResults" :key="tour.id || index">
                            <a :href="tour.url" 
                               :data-selected="selectedIndex === index"
                               class="group flex items-center justify-between p-2.5 sm:p-3 rounded-2xl transition-all cursor-pointer border pt-2.5"
                               :class="selectedIndex === index ? 'bg-orange-50/80 border-orange-300 ring-2 ring-primary/20 shadow-2xs' : 'bg-white hover:bg-slate-50 border-transparent hover:border-slate-200'">
                                <div class="flex items-center gap-3 sm:gap-3.5 min-w-0">
                                    <!-- Tour Thumbnail -->
                                    <div class="w-16 h-14 sm:w-20 sm:h-16 rounded-xl overflow-hidden bg-slate-100 shrink-0 relative border border-slate-200/80 shadow-2xs">
                                        <img :src="tour.image" :alt="tour.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                                        <span x-show="tour.badge" 
                                              class="absolute bottom-1 left-1 px-1.5 py-0.5 rounded text-[8px] sm:text-[9px] font-black uppercase tracking-wider text-white shadow-2xs"
                                              :class="tour.is_bestseller ? 'bg-orange-500' : 'bg-slate-900/80'"
                                              x-text="tour.badge"></span>
                                    </div>
                                    <!-- Tour Metadata -->
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 sm:gap-2 mb-0.5">
                                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 truncate max-w-[120px] sm:max-w-none" x-text="tour.category"></span>
                                            <span class="text-slate-300">•</span>
                                            <span class="text-[10px] font-bold text-slate-500 flex items-center gap-0.5 shrink-0">
                                                <i class="bi bi-clock text-[9px]"></i> <span x-text="tour.duration"></span>
                                            </span>
                                            <span class="text-slate-300 hidden sm:inline">•</span>
                                            <span class="hidden sm:inline-flex text-[10px] font-bold text-amber-500 items-center gap-0.5 shrink-0">
                                                <i class="bi bi-star-fill text-[9px]"></i> <span x-text="tour.rating"></span>
                                                <span class="text-slate-400 font-normal">(<span x-text="tour.reviews_count"></span>)</span>
                                            </span>
                                        </div>
                                        <h6 class="text-xs sm:text-sm font-extrabold text-slate-900 group-hover:text-primary transition-colors truncate" x-html="highlightMatch(tour.name)"></h6>
                                        <p class="text-[11px] text-slate-500 truncate hidden sm:block mt-0.5" x-text="tour.highlights"></p>
                                    </div>
                                </div>
                                <!-- Price & Action CTA -->
                                <div class="text-right shrink-0 pl-3">
                                    <span class="text-[9px] sm:text-[10px] text-slate-400 block font-bold uppercase tracking-wider">Starts From</span>
                                    <div class="text-xs sm:text-sm font-black text-primary font-mono whitespace-nowrap" x-text="tour.price_formatted"></div>
                                    <div class="text-[10px] font-bold text-slate-400 group-hover:text-primary flex items-center justify-end gap-1 mt-0.5 transition-colors">
                                        <span class="hidden sm:inline">View</span>
                                        <i class="bi bi-arrow-right text-xs group-hover:translate-x-0.5 transition-transform"></i>
                                    </div>
                                </div>
                            </a>
                        </template>

                        <!-- Zero Results Empty State -->
                        <div x-show="filteredResults.length === 0 && !loading" class="py-8 px-4 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-primary border border-amber-200/60 flex items-center justify-center mx-auto mb-3 text-xl shadow-2xs">
                                <i class="bi bi-search"></i>
                            </div>
                            <h6 class="text-sm font-extrabold text-slate-900 mb-1">
                                No safaris found matching "<span class="text-primary font-black" x-text="query"></span>"
                            </h6>
                            <p class="text-xs text-slate-500 max-w-sm mx-auto mb-4">
                                Try searching for terms like <button type="button" @click="setQuery('safari')" class="text-primary font-bold hover:underline">safari</button>, <button type="button" @click="setQuery('buggy')" class="text-primary font-bold hover:underline">buggy</button>, <button type="button" @click="setQuery('quad')" class="text-primary font-bold hover:underline">quad</button>, or <button type="button" @click="setQuery('dinner')" class="text-primary font-bold hover:underline">dinner</button>.
                            </p>
                            <div class="inline-flex items-center gap-2">
                                <button type="button" 
                                        @click="if(window.App && typeof window.App.openWhatsApp === 'function'){ window.App.openWhatsApp(query); }"
                                        class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-2xs transition-colors cursor-pointer">
                                    <i class="bi bi-whatsapp"></i>
                                    <span>Ask Safari Concierge on WhatsApp</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- View All Results on Full Page Button -->
                    <div x-show="filteredResults.length > 0" class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-400 text-[11px]">Instant live match</span>
                        <a :href="searchUrl + '?q=' + encodeURIComponent(query)" class="font-bold text-primary hover:text-primary-dark transition-colors inline-flex items-center gap-1">
                            <span>View all results on full page</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between p-3.5 sm:p-4 px-5 sm:px-6 bg-slate-50 border-t border-slate-200 text-xs">
                <span class="text-slate-500 flex items-center gap-1 font-medium">
                    <i class="bi bi-patch-check-fill text-emerald-500 text-sm"></i> 100% Free 24h Cancellation
                </span>
                <a href="{{ route('tours.index') }}" class="font-bold text-primary hover:text-primary-dark transition-colors inline-flex items-center gap-1">
                    <span>Browse full catalog</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('keydown', function(e) {
        if ((e.key === '/' || ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k')) && 
            !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
            e.preventDefault();
            if (window.Alpine && Alpine.store('modal')) {
                Alpine.store('modal').open('search');
            }
        }
    });
});
</script>
