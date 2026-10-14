@if($tour)
<div class="my-8 rounded-2xl bg-gradient-to-br from-amber-500/10 via-slate-900 to-slate-950 border border-amber-500/30 p-5 sm:p-6 text-white shadow-xl relative overflow-hidden">
    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-primary/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="flex flex-col md:flex-row items-center gap-6 relative z-10">
        <div class="w-full md:w-44 h-36 rounded-xl overflow-hidden shrink-0 border border-white/10 shadow-md">
            <img src="{{ resolve_tour_image($tour->thumb_image ?: $tour->hero_image) }}" alt="{{ $tour->name }}" class="w-full h-full object-cover">
        </div>
        <div class="flex-1 text-center md:text-left">
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/20 border border-amber-500/40 text-amber-300 text-[10px] sm:text-xs font-bold uppercase tracking-wider mb-2">
                <i class="bi bi-star-fill text-amber-400"></i> Recommended Desert Experience
            </div>
            <h4 class="text-base sm:text-lg font-bold text-white mb-1.5">
                {{ $tour->name }}
            </h4>
            <p class="text-xs text-slate-300 line-clamp-2 mb-3">
                {{ $tour->short_desc }}
            </p>
            <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 text-xs text-slate-400">
                <span class="flex items-center gap-1"><i class="bi bi-clock text-amber-400"></i> {{ $tour->duration ?? '6 Hours' }}</span>
                <span class="flex items-center gap-1"><i class="bi bi-shield-check text-emerald-400"></i> DET Licensed Guide</span>
                <span class="flex items-center gap-1"><i class="bi bi-patch-check-fill text-primary"></i> Free Cancellation</span>
            </div>
        </div>
        <div class="shrink-0 flex flex-col items-center md:items-end w-full md:w-auto pt-3 md:pt-0 border-t md:border-t-0 border-white/10">
            @php
                $startingPrice = $tour->tiers->min('price') ?: 150;
            @endphp
            <div class="text-[11px] text-slate-400 uppercase tracking-wider">Starting From</div>
            <div class="text-2xl font-black text-amber-400 font-mono mb-2" x-data x-text="$store.currency.format({{ $startingPrice }})">
                AED {{ $startingPrice }}
            </div>
            <div class="flex items-center gap-2 w-full md:w-auto">
                <a href="{{ localized_route('tours.show', $tour->slug) }}" class="flex-1 md:flex-initial inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-md transition-all">
                    <span>Explore Tour</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
                <button type="button" @click="$store.modal.open('booking', { tourId: '{{ $tour->id }}', tourName: '{{ addslashes($tour->name) }}' })" class="inline-flex items-center justify-center gap-1 px-3.5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold border border-white/20 transition-all">
                    Book Now
                </button>
            </div>
        </div>
    </div>
</div>
@endif
