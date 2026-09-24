@php
    if (!function_exists('try_get_compare_tours')) {
        function try_get_compare_tours() {
            try {
                return \App\Models\Tour::where('status', 'active')->with(['tiers'])->orderBy('priority', 'asc')->get();
            } catch (\Throwable $e) {
                return collect();
            }
        }
    }
    $sourceTours = (isset($allTours) && count($allTours)) ? $allTours : try_get_compare_tours();
    $compareToursData = $sourceTours->map(function($t) {
        $minPrice = $t->tiers ? $t->tiers->pluck('pivot.price')->filter(fn($p) => (float)$p > 0)->min() : 99;
        $minPrice = $minPrice ? (float)$minPrice : 99;
        
        $nameLower = strtolower($t->name);
        $slugLower = strtolower($t->slug);

        // Vehicle type inference
        if (str_contains($slugLower, 'buggy') || str_contains($nameLower, 'buggy')) {
            $vehicle = '1000cc Can-Am / Polaris Buggy + 4x4 Transfer';
            $duneBashing = 'Self-Drive Guided High Dunes (1-2 Hours)';
            $dining = 'Cold Water & Soft Drinks (Buffet Optional)';
            $shows = 'Optional Camp Addon';
            $inclusions = 'Safety Helmet, Goggles, Sandboarding, Desert Guide';
        } elseif (str_contains($slugLower, 'quad') || str_contains($nameLower, 'quad')) {
            $vehicle = '4x4 Land Cruiser Pickup + 350cc/400cc Quad Bike';
            $duneBashing = '30-60 Mins Quad Track + 4x4 Dune Bashing';
            $dining = '5-Star Live BBQ Buffet (Veg, Non-Veg & Jain)';
            $shows = 'Live Tanoura, Fire Dance & Belly Dance';
            $inclusions = 'Quad Bike, Camel Ride, Sandboarding, Henna, BBQ Dinner';
        } elseif (str_contains($slugLower, 'vip') || str_contains($nameLower, 'vip') || str_contains($nameLower, 'luxury')) {
            $vehicle = 'Luxury 4x4 Toyota Land Cruiser (Doorstep Pickup)';
            $duneBashing = '45 Mins Extreme High Red Dunes (Lahbab)';
            $dining = 'VIP Raised Table with Private Waiter & 5-Star Live BBQ';
            $shows = 'Priority Front-Row Live Shows (Tanoura, Fire, Belly)';
            $inclusions = 'VIP Table Service, Camel Ride, Sandboarding, Henna, Shisha';
        } elseif (str_contains($slugLower, 'morning') || str_contains($nameLower, 'morning')) {
            $vehicle = '4x4 Toyota Land Cruiser (Doorstep Hotel Pickup)';
            $duneBashing = '40 Mins Morning High Red Dunes';
            $dining = 'Light Arabic Breakfast & Refreshments';
            $shows = 'Not Applicable (Morning Safari)';
            $inclusions = 'Camel Riding, Sandboarding, Arabic Coffee & Photography';
        } elseif (str_contains($slugLower, 'overnight') || str_contains($nameLower, 'overnight')) {
            $vehicle = '4x4 Toyota Land Cruiser (Doorstep Hotel Pickup)';
            $duneBashing = '45 Mins Sunset High Red Dunes';
            $dining = 'Live BBQ Dinner + Fresh Morning Bedouin Breakfast';
            $shows = 'Full Evening Cultural Shows + Stargazing by Campfire';
            $inclusions = 'Overnight Bedouin Tent, Sleeping Bag, Sunrise Watch, BBQ';
        } else {
            $vehicle = '4x4 Toyota Land Cruiser (Doorstep Hotel Pickup)';
            $duneBashing = '40-45 Mins High Red Dunes (Lahbab Desert)';
            $dining = '5-Star Live BBQ Buffet (Veg, Non-Veg & Jain options)';
            $shows = '3 Live Cultural Shows (Tanoura, Fire & Belly Dance)';
            $inclusions = 'Camel Riding, Sandboarding, Henna, Arabic Coffee, BBQ';
        }

        $thumb = $t->thumb_image ? asset('images/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $t->thumb_image)) : asset('images/desert-safari-poster.avif');

        return [
            'id' => (string)$t->id,
            'name' => $t->name,
            'slug' => $t->slug,
            'thumb' => $thumb,
            'duration' => $t->duration ?: '6-7 Hours',
            'rating' => (float)($t->rating ?: 4.9),
            'min_price' => $minPrice,
            'is_bestseller' => (bool)$t->is_bestseller,
            'vehicle' => $vehicle,
            'dune_bashing' => $duneBashing,
            'dining' => $dining,
            'shows' => $shows,
            'inclusions' => $inclusions,
        ];
    });
@endphp

<!-- Comparison Dataset Script -->
<script id="dunesCompareTourData" type="application/json">
{!! json_encode($compareToursData) !!}
</script>

<!-- Floating Comparison Bar (Sticky Bottom Pill) -->
<div id="compareFloatingBar" 
     x-data="{}" 
     x-show="$store.compare.items && $store.compare.items.length > 0"
     x-cloak
     class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 mb-safe max-w-[94%] w-auto transition-all">
    <div class="flex items-center gap-3 px-4 py-2.5 rounded-full shadow-2xl bg-slate-950/95 border border-primary backdrop-blur-md">
        <div class="flex items-center -space-x-2" id="compareThumbBubbles"></div>
        <div class="text-white text-xs font-bold pe-2 border-r border-slate-700 hidden sm:block">
            <span id="compareCountLabel" x-text="$store.compare.items ? $store.compare.items.length : 0">0</span>/3 Selected
        </div>
        <button type="button" 
                class="px-3.5 py-1.5 rounded-full bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-extrabold text-xs shadow-sm hover:shadow-md transition-all whitespace-nowrap cursor-pointer flex items-center gap-1.5" 
                id="openCompareDrawerBtn" 
                @click="$store.modal.open('compare')">
            <i class="bi bi-shuffle"></i>
            <span>Compare Safaris</span>
        </button>
        <button type="button" 
                class="text-slate-400 hover:text-white p-0 text-xs transition-colors cursor-pointer" 
                id="clearCompareBtn" 
                @click="$store.compare.clear()" 
                title="Clear all">
            <i class="bi bi-x-circle-fill"></i>
        </button>
    </div>
</div>

<!-- Comparison Drawer (Bottom Sheet) -->
<div id="compareDrawer"
     x-data="{}"
     x-show="$store.modal.active === 'compare'"
     x-cloak
     class="fixed inset-0 z-50 overflow-hidden"
     role="dialog"
     aria-modal="true"
     @keydown.escape.window="$store.modal.close()">

    <!-- Backdrop -->
    <div x-show="$store.modal.active === 'compare'"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"
         @click="$store.modal.close()"></div>

    <!-- Drawer Panel (Slides up from bottom) -->
    <div class="fixed inset-x-0 bottom-0 max-h-[88vh] h-[85vh] flex flex-col rounded-t-[2rem] bg-slate-950 border-t-2 border-primary shadow-2xl text-white overflow-hidden transition-transform"
         x-show="$store.modal.active === 'compare'"
         x-transition:enter="transform transition ease-out duration-300"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transform transition ease-in duration-200"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         @click.stop>

        <!-- Header -->
        <div class="flex items-center justify-between px-4 sm:px-6 py-3.5 border-b border-slate-800 bg-slate-900/90 backdrop-blur-md sticky top-0 z-10">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-full bg-primary/20 text-primary flex items-center justify-center shrink-0">
                    <i class="bi bi-shuffle text-lg"></i>
                </span>
                <div>
                    <h5 class="font-extrabold text-sm sm:text-base text-white leading-tight">Compare Safari Experiences</h5>
                    <p class="text-slate-300 text-xs hidden sm:block">Side-by-side comparison of vehicles, dune bashing, dinner & entertainment</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" 
                        class="px-3 py-1 rounded-full border border-slate-700 bg-slate-800/80 hover:bg-slate-700 text-white text-xs font-semibold whitespace-nowrap transition-colors cursor-pointer flex items-center gap-1.5" 
                        id="drawerPresetBtn"
                        @click="$store.compare.loadBestsellers()">
                    <i class="bi bi-lightning-fill text-amber-400"></i>
                    <span>Compare Top 3</span>
                </button>
                <button type="button" 
                        class="w-8 h-8 rounded-full bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 flex items-center justify-center transition-colors cursor-pointer" 
                        @click="$store.modal.close()" 
                        aria-label="Close">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="p-3 sm:p-6 overflow-y-auto flex-1">
            <!-- Empty State -->
            <div id="compareEmptyState" class="text-center py-12" x-show="!$store.compare.items || $store.compare.items.length === 0">
                <div class="w-16 h-16 rounded-full bg-slate-900 border border-slate-800 text-slate-300 flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="bi bi-compass"></i>
                </div>
                <h5 class="text-base sm:text-lg font-bold text-white mb-2">No Safaris Selected for Comparison</h5>
                <p class="text-slate-300 text-xs sm:text-sm max-w-md mx-auto mb-6">
                    Click the <strong class="text-white">+ Compare</strong> button on any safari card, or click below to analyze our Top 3 most popular Dubai experiences side-by-side:
                </p>
                <button type="button" 
                        class="px-5 py-2.5 rounded-full bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-extrabold text-xs sm:text-sm shadow-md transition-all cursor-pointer inline-flex items-center gap-2" 
                        @click="$store.compare.loadBestsellers()">
                    <i class="bi bi-stars"></i>
                    <span>Compare Top 3 Bestsellers</span>
                </button>
            </div>

            <!-- Comparison Table / Grid -->
            <div id="compareTableWrapper" class="overflow-x-auto" x-show="$store.compare.items && $store.compare.items.length > 0">
                <table class="w-full text-left text-sm border-collapse min-w-[640px]" id="compareTable">
                    <thead>
                        <tr id="compareRowHeader" class="border-b border-slate-700">
                            <th class="w-44 min-w-[176px] pb-4 text-xs font-bold uppercase tracking-wider text-slate-300">Feature</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/80 text-xs sm:text-sm">
                        <tr id="compareRowPrice">
                            <td class="py-3 font-semibold text-slate-300 flex items-center gap-2"><i class="bi bi-cash-stack text-amber-400"></i>Starting Price</td>
                        </tr>
                        <tr id="compareRowDuration">
                            <td class="py-3 font-semibold text-slate-300 flex items-center gap-2"><i class="bi bi-clock-history text-amber-400"></i>Duration</td>
                        </tr>
                        <tr id="compareRowVehicle">
                            <td class="py-3 font-semibold text-slate-300 flex items-center gap-2"><i class="bi bi-truck text-amber-400"></i>Vehicle & Transfer</td>
                        </tr>
                        <tr id="compareRowDuneBashing">
                            <td class="py-3 font-semibold text-slate-300 flex items-center gap-2"><i class="bi bi-speedometer2 text-amber-400"></i>Dune Bashing</td>
                        </tr>
                        <tr id="compareRowDining">
                            <td class="py-3 font-semibold text-slate-300 flex items-center gap-2"><i class="bi bi-cup-hot-fill text-amber-400"></i>Camp & Dinner</td>
                        </tr>
                        <tr id="compareRowShows">
                            <td class="py-3 font-semibold text-slate-300 flex items-center gap-2"><i class="bi bi-fire text-amber-400"></i>Live Shows</td>
                        </tr>
                        <tr id="compareRowInclusions">
                            <td class="py-3 font-semibold text-slate-300 flex items-center gap-2"><i class="bi bi-gift-fill text-amber-400"></i>Inclusions</td>
                        </tr>
                        <tr id="compareRowCancellation">
                            <td class="py-3 font-semibold text-slate-300 flex items-center gap-2"><i class="bi bi-shield-check text-emerald-400"></i>Cancellation</td>
                        </tr>
                        <tr id="compareRowAction">
                            <td class="py-3 font-semibold text-slate-300">Book Experience</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Bridge window.DunesCompare with Alpine store
    if (window.Alpine && Alpine.store('compare')) {
        window.DunesCompare = Alpine.store('compare');
        window.DunesCompare.init();
    }
});
</script>
@endpush
