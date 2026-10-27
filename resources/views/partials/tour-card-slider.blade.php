@php
    $cardImages = $tour->getImageList();
    $aspect = $aspectClass ?? 'aspect-[16/10]';
    $catName = $categoryName ?? ($tour->category ? $tour->category->name : null);
    $bestseller = $isBestseller ?? $tour->is_bestseller;
    $featured = $isFeatured ?? $tour->is_featured;
    $totalCount = count($cardImages);
@endphp

<div class="relative overflow-hidden {{ $aspect }} w-full select-none"
     @if($totalCount >= 2)
     x-data="{
         activeIdx: 0,
         count: {{ $totalCount }},
         hasInteracted: false,
         startX: 0,
         prev(e) { if (e) { e.preventDefault(); e.stopPropagation(); } this.hasInteracted = true; this.activeIdx = (this.activeIdx - 1 + this.count) % this.count; },
         next(e) { if (e) { e.preventDefault(); e.stopPropagation(); } this.hasInteracted = true; this.activeIdx = (this.activeIdx + 1) % this.count; },
         go(idx, e) { if (e) { e.preventDefault(); e.stopPropagation(); } this.hasInteracted = true; this.activeIdx = idx; },
         touchStart(e) { this.hasInteracted = true; this.startX = e.changedTouches[0].screenX; },
         touchEnd(e) {
             const diff = e.changedTouches[0].screenX - this.startX;
             if (diff > 40) { this.prev(e); }
             else if (diff < -40) { this.next(e); }
         }
     }"
     @mouseenter.once="hasInteracted = true"
     @touchstart.passive="touchStart($event)"
     @touchend.passive="touchEnd($event)"
     @endif>

    <!-- Images / Slides -->
    <div class="w-full h-full relative">
        @foreach($cardImages as $idx => $imgUrl)
            @if($idx === 0)
                <img src="{{ $imgUrl }}"
                     width="400" height="250"
                     alt="{{ $tour->name }} Dubai Desert Safari photo 1"
                     loading="lazy"
                     decoding="async"
                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-opacity duration-300 ease-out"
                     :style="activeIdx === 0 ? 'opacity: 1; z-index: 10; pointer-events: auto;' : 'opacity: 0; z-index: 1; pointer-events: none;'"
                     style="opacity: 1; z-index: 10; pointer-events: auto;">
            @else
                <img :src="(hasInteracted || activeIdx === {{ $idx }}) ? '{{ $imgUrl }}' : ''"
                     width="400" height="250"
                     alt="{{ $tour->name }} Dubai Desert Safari photo {{ $idx + 1 }}"
                     loading="lazy"
                     decoding="async"
                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-opacity duration-300 ease-out"
                     :style="activeIdx === {{ $idx }} ? 'opacity: 1; z-index: 10; pointer-events: auto;' : 'opacity: 0; z-index: 1; pointer-events: none;'"
                     style="opacity: 0; z-index: 1; pointer-events: none;">
            @endif
        @endforeach
    </div>

    <!-- Top Badges -->
    <div class="absolute top-3 inset-x-3 flex items-center justify-between z-20 pointer-events-none">
        <div>
            @if($bestseller)
                <span class="bg-primary-accessible text-white text-xs font-bold px-3 py-1 rounded-full shadow-md inline-flex items-center gap-1">
                    <i class="bi bi-fire text-amber-200"></i>{{ __('ui.home_popular.bestseller') }}
                </span>
            @elseif($featured)
                <span class="bg-emerald-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-md inline-flex items-center gap-1">
                    <i class="bi bi-award"></i>{{ __('ui.common.featured') }}
                </span>
            @endif
        </div>
        @if($catName)
            <span class="glass-dark text-white text-xs font-semibold px-2.5 py-1 rounded-full inline-flex items-center gap-1 shadow-2xs">
                <i class="bi bi-tag-fill text-primary"></i>{{ $catName }}
            </span>
        @endif
    </div>

    <!-- Bottom Vignette -->
    <div class="absolute bottom-0 inset-x-0 h-16 bg-gradient-to-t from-black/70 via-black/25 to-transparent z-10 pointer-events-none"></div>

    @if($totalCount >= 2)
        <!-- Slider Navigation Arrows (Previous / Next) -->
        <button type="button"
                @click.prevent.stop="prev($event)"
                aria-label="Previous image"
                class="absolute start-2 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-slate-950/65 hover:bg-primary text-white flex items-center justify-center opacity-85 sm:opacity-0 group-hover:opacity-100 transition-all duration-200 shadow-md backdrop-blur-xs cursor-pointer focus:outline-hidden hover:scale-105 active:scale-95">
            <i class="bi bi-chevron-left text-xs rtl:rotate-180"></i>
        </button>
        <button type="button"
                @click.prevent.stop="next($event)"
                aria-label="Next image"
                class="absolute end-2 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-slate-950/65 hover:bg-primary text-white flex items-center justify-center opacity-85 sm:opacity-0 group-hover:opacity-100 transition-all duration-200 shadow-md backdrop-blur-xs cursor-pointer focus:outline-hidden hover:scale-105 active:scale-95">
            <i class="bi bi-chevron-right text-xs rtl:rotate-180"></i>
        </button>

        <!-- Bottom Controls: Counter Badge & Pagination Dots -->
        <div class="absolute bottom-2.5 inset-x-3 z-20 flex items-center justify-between pointer-events-none">
            <span class="bg-black/60 backdrop-blur-xs text-white text-[10px] font-bold px-2 py-0.5 rounded-full inline-flex items-center gap-1 shadow-2xs">
                <i class="bi bi-camera-fill text-[9px] text-amber-400"></i>
                <span x-text="(activeIdx + 1) + '/{{ $totalCount }}'">1/{{ $totalCount }}</span>
            </span>

            <div class="flex items-center gap-1 pointer-events-auto" @click.prevent.stop>
                @foreach($cardImages as $idx => $imgUrl)
                    <button type="button"
                            @click.prevent.stop="go({{ $idx }}, $event)"
                            aria-label="Photo {{ $idx + 1 }}"
                            class="relative p-1.5 flex items-center justify-center cursor-pointer min-w-[24px] min-h-[24px]">
                        <span class="block h-1.5 rounded-full transition-all duration-200"
                              :class="activeIdx === {{ $idx }} ? 'w-4 bg-amber-400 shadow-xs' : 'w-1.5 bg-white/60 hover:bg-white'"></span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif
</div>
