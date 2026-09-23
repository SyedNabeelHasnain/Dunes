<!-- Global Interactive Search Modal (Tailwind v4 + Alpine.js) -->
<div id="globalSearchModal"
     x-data="{}"
     x-show="$store.modal.active === 'search'"
     x-cloak
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
         class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity"
         @click="$store.modal.close()"></div>

    <!-- Dialog Panel -->
    <div class="min-h-full flex items-center justify-center p-3 sm:p-4 text-center">
        <div x-show="$store.modal.active === 'search'"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="w-full max-w-2xl transform overflow-hidden rounded-3xl bg-white text-left align-middle shadow-2xl transition-all border border-slate-200"
             @click.stop>
            
            <!-- Header -->
            <div class="flex items-center justify-between p-4 sm:p-6 pb-0">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary">
                        <i class="bi bi-search"></i> Search Dubai Safaris
                    </span>
                    <span class="hidden sm:inline-block text-xs text-slate-500">DET Licensed #1430583</span>
                </div>
                <button type="button" @click="$store.modal.close()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:text-slate-700 hover:bg-slate-200 flex items-center justify-center transition-colors cursor-pointer" aria-label="Close">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="p-4 sm:p-6">
                <form action="{{ route('tours.search') }}" method="GET" id="globalSearchForm">
                    <div class="relative mb-6">
                        <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-lg sm:text-xl"></i>
                        <input type="text" name="q" id="globalSearchModalInput" 
                               class="w-full pl-12 pr-28 py-3.5 sm:py-4 rounded-2xl bg-slate-50 border-2 border-slate-200 focus:border-primary focus:bg-white text-sm sm:text-base font-semibold text-slate-800 placeholder:text-slate-500 focus:outline-none transition-all" 
                               placeholder="Search safaris, buggies, VIP dining, 4x4..." 
                               autocomplete="off" 
                               required
                               x-init="$watch('$store.modal.active', v => { if (v === 'search') $nextTick(() => $el.focus()) })">
                        <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs sm:text-sm shadow-sm transition-colors cursor-pointer">
                            Search
                        </button>
                    </div>
                </form>

                <div class="trending-searches-wrapper">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-black uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                            <i class="bi bi-fire text-amber-500"></i> Popular Searches
                        </span>
                        <span class="hidden sm:inline-block text-[11px] text-slate-500">Press <kbd class="px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200 font-mono text-[10px]">Ctrl+K</kbd> anytime</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('tours.search', ['q' => 'Evening Desert Safari']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 hover:bg-orange-50 hover:text-primary text-slate-700 transition-colors border border-slate-200/80">
                            <i class="bi bi-sunset text-amber-500"></i> Evening Desert Safari
                        </a>
                        <a href="{{ route('tours.search', ['q' => 'Can-Am Dune Buggy']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 hover:bg-orange-50 hover:text-primary text-slate-700 transition-colors border border-slate-200/80">
                            <i class="bi bi-speedometer2 text-red-500"></i> Can-Am Dune Buggy
                        </a>
                        <a href="{{ route('tours.search', ['q' => 'Quad Biking Lahbab']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 hover:bg-orange-50 hover:text-primary text-slate-700 transition-colors border border-slate-200/80">
                            <i class="bi bi-compass text-primary"></i> Quad Biking Lahbab
                        </a>
                        <a href="{{ route('tours.search', ['q' => 'VIP Luxury Desert Safari']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 hover:bg-orange-50 hover:text-primary text-slate-700 transition-colors border border-slate-200/80">
                            <i class="bi bi-gem text-amber-500"></i> VIP Luxury Table
                        </a>
                        <a href="{{ route('tours.search', ['q' => 'Morning Desert Safari']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 hover:bg-orange-50 hover:text-primary text-slate-700 transition-colors border border-slate-200/80">
                            <i class="bi bi-sunrise text-sky-500"></i> Morning Safari
                        </a>
                        <a href="{{ route('tours.search', ['q' => 'Dubai Marina Dhow Cruise']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 hover:bg-orange-50 hover:text-primary text-slate-700 transition-colors border border-slate-200/80">
                            <i class="bi bi-water text-primary"></i> Marina Dhow Cruise
                        </a>
                        <a href="{{ route('tours.search', ['q' => 'Abu Dhabi City Tour']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 hover:bg-orange-50 hover:text-primary text-slate-700 transition-colors border border-slate-200/80">
                            <i class="bi bi-building text-slate-500"></i> Abu Dhabi City Tour
                        </a>
                        <a href="{{ route('tours.search', ['q' => 'Private Land Cruiser']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 hover:bg-orange-50 hover:text-primary text-slate-700 transition-colors border border-slate-200/80">
                            <i class="bi bi-car-front-fill text-emerald-500"></i> Private Land Cruiser
                        </a>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between p-4 px-6 bg-slate-50 border-t border-slate-200 text-xs">
                <span class="text-slate-500 flex items-center gap-1">
                    <i class="bi bi-patch-check-fill text-emerald-500"></i> 100% Free 24h Cancellation
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
            if (window.Alpine) {
                Alpine.store('modal').open('search');
            }
        }
    });
});
</script>
