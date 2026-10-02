@php
    $dropdownId = $switcherId ?? 'langCurrencyDropdownBtn';
    $curLocale = $currentLocale ?? app()->getLocale();
    $langs = $activeLanguages ?? \App\Models\Language::getActive();
    $curLang = $currentLanguage ?? ($langs->firstWhere('code', $curLocale) ?: $langs->first());

    $settingsService = app(\App\Services\SettingsService::class);
    $rateUsd = $settingsService->get('currency_rate_usd', '0.2723');
    $rateEur = $settingsService->get('currency_rate_eur', '0.2510');
    $rateGbp = $settingsService->get('currency_rate_gbp', '0.2150');
    $rateSar = $settingsService->get('currency_rate_sar', '1.0210');
    $rateInr = $settingsService->get('currency_rate_inr', '22.85');
@endphp
<div class="relative inline-block text-left" x-data="{ open: false, activeTab: 'lang' }" @click.outside="open = false" @keydown.escape.window="open = false">
    <button @click="open = !open" :aria-expanded="open" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold text-slate-800 bg-white border border-slate-200/90 shadow-2xs hover:border-primary/50 transition-all cursor-pointer min-h-[38px]" type="button" id="{{ $dropdownId }}" aria-label="Language and Currency Switcher">
        <span class="text-sm leading-none">{{ $curLang ? $curLang->flag_emoji : '🌐' }}</span>
        <span class="font-extrabold uppercase text-[11px]">{{ strtoupper($curLocale) }}</span>
        <span class="text-slate-300">/</span>
        <span class="current-currency-code font-extrabold text-[11px]">AED</span>
        <i class="bi bi-chevron-down text-[10px] text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
    </button>
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
         class="absolute {{ ($isRtl ?? false) ? 'left-0' : 'right-0' }} mt-2 w-64 rounded-2xl bg-white p-2.5 shadow-xl border border-slate-200 z-50 focus:outline-none"
         style="display: none;">
        <!-- Tabs Header -->
        <div class="flex items-center p-1 bg-slate-100 rounded-xl mb-2 text-xs font-bold">
            <button type="button" @click="activeTab = 'lang'" 
                    :class="activeTab === 'lang' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 py-1.5 rounded-lg text-center transition-all cursor-pointer flex items-center justify-center gap-1.5">
                <i class="bi bi-globe2 text-xs text-primary"></i>
                <span>Language</span>
            </button>
            <button type="button" @click="activeTab = 'curr'" 
                    :class="activeTab === 'curr' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 py-1.5 rounded-lg text-center transition-all cursor-pointer flex items-center justify-center gap-1.5">
                <i class="bi bi-currency-exchange text-xs text-amber-500"></i>
                <span>Currency</span>
            </button>
        </div>

        <!-- Language Tab Content -->
        <div x-show="activeTab === 'lang'" class="space-y-1">
            <div class="px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                Select Language
            </div>
            @foreach($langs as $language)
                @php
                    $isCur = $language->code === $curLocale;
                    $switchUrl = switch_locale_url($language->code);
                @endphp
                <a href="{{ $switchUrl }}"
                   class="w-full rounded-xl px-2.5 py-2 text-xs font-semibold {{ $isCur ? 'bg-orange-50/80 text-primary font-bold' : 'text-slate-700 hover:bg-slate-50 hover:text-primary' }} flex items-center justify-between transition-colors">
                    <span class="flex items-center gap-2">
                        <span class="text-base leading-none">{{ $language->flag_emoji }}</span>
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

        <!-- Currency Tab Content -->
        <div x-show="activeTab === 'curr'" class="space-y-1" style="display: none;">
            <div class="px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                Display Currency
            </div>
            <button type="button" @click="open = false" class="w-full rounded-xl px-2.5 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-primary flex items-center justify-between transition-colors currency-option active cursor-pointer" data-currency="AED" data-flag="&#x1F1E6;&#x1F1EA;" data-symbol="AED" data-rate="1">
                <span class="flex items-center gap-2"><span class="text-base">&#x1F1E6;&#x1F1EA;</span><span>AED <small class="text-slate-500">(د.إ)</small></span></span>
                <i class="bi bi-check2 text-primary font-bold checkmark"></i>
            </button>
            <button type="button" @click="open = false" class="w-full rounded-xl px-2.5 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-primary flex items-center justify-between transition-colors currency-option cursor-pointer" data-currency="USD" data-flag="&#x1F1FA;&#x1F1F8;" data-symbol="$" data-rate="{{ $rateUsd }}">
                <span class="flex items-center gap-2"><span class="text-base">&#x1F1FA;&#x1F1F8;</span><span>USD <small class="text-slate-500">($)</small></span></span>
                <i class="bi bi-check2 text-primary font-bold checkmark hidden d-none"></i>
            </button>
            <button type="button" @click="open = false" class="w-full rounded-xl px-2.5 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-primary flex items-center justify-between transition-colors currency-option cursor-pointer" data-currency="EUR" data-flag="&#x1F1EA;&#x1F1FA;" data-symbol="€" data-rate="{{ $rateEur }}">
                <span class="flex items-center gap-2"><span class="text-base">&#x1F1EA;&#x1F1FA;</span><span>EUR <small class="text-slate-500">(€)</small></span></span>
                <i class="bi bi-check2 text-primary font-bold checkmark hidden d-none"></i>
            </button>
            <button type="button" @click="open = false" class="w-full rounded-xl px-2.5 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-primary flex items-center justify-between transition-colors currency-option cursor-pointer" data-currency="GBP" data-flag="&#x1F1EC;&#x1F1E7;" data-symbol="£" data-rate="{{ $rateGbp }}">
                <span class="flex items-center gap-2"><span class="text-base">&#x1F1EC;&#x1F1E7;</span><span>GBP <small class="text-slate-500">(£)</small></span></span>
                <i class="bi bi-check2 text-primary font-bold checkmark hidden d-none"></i>
            </button>
            <button type="button" @click="open = false" class="w-full rounded-xl px-2.5 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-primary flex items-center justify-between transition-colors currency-option cursor-pointer" data-currency="SAR" data-flag="&#x1F1F8;&#x1F1E6;" data-symbol="SAR" data-rate="{{ $rateSar }}">
                <span class="flex items-center gap-2"><span class="text-base">&#x1F1F8;&#x1F1E6;</span><span>SAR <small class="text-slate-500">(﷼)</small></span></span>
                <i class="bi bi-check2 text-primary font-bold checkmark hidden d-none"></i>
            </button>
            <button type="button" @click="open = false" class="w-full rounded-xl px-2.5 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-primary flex items-center justify-between transition-colors currency-option cursor-pointer" data-currency="INR" data-flag="&#x1F1EE;&#x1F1F3;" data-symbol="₹" data-rate="{{ $rateInr }}">
                <span class="flex items-center gap-2"><span class="text-base">&#x1F1EE;&#x1F1F3;</span><span>INR <small class="text-slate-500">(₹)</small></span></span>
                <i class="bi bi-check2 text-primary font-bold checkmark hidden d-none"></i>
            </button>
        </div>
    </div>
</div>
