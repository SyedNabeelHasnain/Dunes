<div class="legal-modal-content" x-data="{ lang: 'en' }">
    <div class="flex items-center justify-between flex-wrap gap-2 pb-3 mb-4 border-b border-slate-200">
        <div>
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-orange-50 text-primary border border-orange-200/60">Official Policy</span>
            <h4 class="text-lg font-black text-slate-900 mt-1" id="modalLegalTitle" x-text="lang === 'ar' ? '{{ addslashes($page->title_ar ?: $page->title) }}' : '{{ addslashes($page->title) }}'">{{ $page->title }}</h4>
        </div>
        <div class="inline-flex rounded-xl p-1 bg-slate-100 border border-slate-200 text-xs font-semibold" role="group">
            <button type="button" class="px-3 py-1 rounded-lg transition-colors cursor-pointer" :class="lang === 'en' ? 'bg-primary text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" @click="lang = 'en'">EN</button>
            <button type="button" class="px-3 py-1 rounded-lg transition-colors cursor-pointer" :class="lang === 'ar' ? 'bg-primary text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" @click="lang = 'ar'">عربي</button>
        </div>
    </div>

    <!-- English View -->
    <div x-show="lang === 'en'" class="space-y-4">
        @if($page->description)
            <div class="p-3.5 bg-slate-50 rounded-2xl text-slate-600 text-xs sm:text-sm border-l-4 border-primary leading-relaxed">
                {!! nl2br(e($page->description)) !!}
            </div>
        @endif

        @foreach ($sections as $section)
            <div>
                <h6 class="font-bold text-slate-900 text-sm mb-1">{{ $section->heading }}</h6>
                @if($section->subheading)
                    <div class="text-slate-500 text-xs mb-2">{{ $section->subheading }}</div>
                @endif

                @if($section->items->count() > 0)
                    <ul class="space-y-2 text-xs sm:text-sm text-slate-600">
                        @foreach($section->items as $item)
                            <li class="flex items-start gap-2">
                                <i class="bi bi-check2 text-primary shrink-0 mt-0.5 font-bold"></i>
                                <span>{{ $item->content }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Arabic View -->
    <div x-show="lang === 'ar'" class="text-right space-y-4" dir="rtl" style="display: none;">
        @if($page->description_ar || $page->description)
            <div class="p-3.5 bg-slate-50 rounded-2xl text-slate-600 text-xs sm:text-sm border-r-4 border-primary leading-relaxed">
                {!! nl2br(e($page->description_ar ?: $page->description)) !!}
            </div>
        @endif

        @foreach ($sections as $section)
            <div>
                <h6 class="font-bold text-slate-900 text-sm mb-1">{{ $section->heading_ar ?: $section->heading }}</h6>
                @if($section->subheading_ar || $section->subheading)
                    <div class="text-slate-500 text-xs mb-2">{{ $section->subheading_ar ?: $section->subheading }}</div>
                @endif

                @if($section->items->count() > 0)
                    <ul class="space-y-2 text-xs sm:text-sm text-slate-600 pr-0">
                        @foreach($section->items as $item)
                            <li class="flex items-start gap-2">
                                <i class="bi bi-check2 text-primary shrink-0 mt-0.5 font-bold"></i>
                                <span>{{ $item->content_ar ?: $item->content }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </div>

    <div class="pt-4 mt-6 border-t border-slate-200 flex justify-between items-center flex-wrap gap-2 text-xs">
        <a href="{{ url('/' . $page->slug) }}" target="_blank" rel="noopener noreferrer" class="text-primary font-bold hover:underline inline-flex items-center gap-1">
            <span>View Full Document</span>
            <i class="bi bi-box-arrow-up-right text-[10px]"></i>
        </a>
        <span class="text-slate-400">Dunes Discovery Tourism LLC &bull; Dubai</span>
    </div>
</div>
