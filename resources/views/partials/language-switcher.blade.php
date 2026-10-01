@php
    $dropdownId = $switcherId ?? 'languageDropdownBtn';
    $curLocale = $currentLocale ?? app()->getLocale();
    $langs = $activeLanguages ?? \App\Models\Language::getActive();
    $curLang = $currentLanguage ?? ($langs->firstWhere('code', $curLocale) ?: $langs->first());
@endphp
<div class="relative inline-block text-left" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
    <button @click="open = !open" :aria-expanded="open" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold text-slate-800 bg-white border border-slate-200/90 shadow-sm hover:border-primary/50 transition-all cursor-pointer min-h-[38px]" type="button" id="{{ $dropdownId }}">
        <span class="text-base leading-none">{{ $curLang ? ($curLang->flag ?: ($curLang->code === 'ar' ? '🇦🇪' : '🇬🇧')) : '🌐' }}</span>
        <span class="font-extrabold uppercase text-[11px]">{{ strtoupper($curLocale) }}</span>
        <i class="bi bi-chevron-down text-[10px] text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
    </button>
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
         class="absolute {{ ($isRtl ?? false) ? 'left-0' : 'right-0' }} mt-2 w-52 rounded-2xl bg-white p-2 shadow-xl border border-slate-200 z-50 focus:outline-none"
         style="display: none;">
        <div class="px-2 py-1 text-[11px] font-extrabold uppercase tracking-wider text-slate-500">
            Select Language
        </div>
        <div class="space-y-1 mt-1">
            @foreach($langs as $language)
                @php
                    $isCur = $language->code === $curLocale;
                    $switchUrl = switch_locale_url($language->code);
                @endphp
                <a href="{{ $switchUrl }}"
                   class="w-full rounded-xl px-2.5 py-2 text-xs font-semibold {{ $isCur ? 'bg-orange-50/80 text-primary font-bold' : 'text-slate-700 hover:bg-slate-50 hover:text-primary' }} flex items-center justify-between transition-colors">
                    <span class="flex items-center gap-2">
                        <span class="text-base leading-none">{{ $language->flag ?: ($language->code === 'ar' ? '🇦🇪' : '🇬🇧') }}</span>
                        <span>{{ $language->name }}</span>
                        @if($language->native_name && $language->native_name !== $language->name)
                            <small class="text-slate-400 font-normal">({{ $language->native_name }})</small>
                        @endif
                    </span>
                    @if($isCur)
                        <i class="bi bi-check2 text-primary font-bold"></i>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>
