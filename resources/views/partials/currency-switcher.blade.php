@php
    $settingsService = app(\App\Services\SettingsService::class);
    $rateUsd = $settingsService->get('currency_rate_usd', '0.2723');
    $rateEur = $settingsService->get('currency_rate_eur', '0.2510');
    $rateGbp = $settingsService->get('currency_rate_gbp', '0.2150');
    $rateSar = $settingsService->get('currency_rate_sar', '1.0210');
    $rateInr = $settingsService->get('currency_rate_inr', '22.85');
    $dropdownId = $switcherId ?? 'currencyDropdownBtn';
@endphp
<div class="relative inline-block text-left" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
    <button @click="open = !open" :aria-expanded="open" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold text-slate-800 bg-white border border-slate-200/90 shadow-sm hover:border-primary/50 transition-all cursor-pointer min-h-[38px]" type="button" id="{{ $dropdownId }}">
        <span class="current-currency-flag">&#x1F1E6;&#x1F1EA;</span>
        <span class="current-currency-code font-extrabold ml-1">AED</span>
        <i class="bi bi-chevron-down text-[10px] text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
    </button>
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
         class="absolute right-0 mt-2 w-48 rounded-2xl bg-white p-2 shadow-xl border border-slate-200 z-50 focus:outline-none"
         style="display: none;">
        <div class="px-2 py-1 text-[11px] font-extrabold uppercase tracking-wider text-slate-500">
            Display Currency
        </div>
        <div class="space-y-1 mt-1">
            <button type="button" @click="open = false" class="w-full rounded-xl px-2.5 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-primary flex items-center justify-between transition-colors currency-option active" data-currency="AED" data-flag="&#x1F1E6;&#x1F1EA;" data-symbol="AED" data-rate="1">
                <span class="flex items-center gap-2"><span class="text-base">&#x1F1E6;&#x1F1EA;</span><span>AED <small class="text-slate-500">(د.إ)</small></span></span>
                <i class="bi bi-check2 text-primary font-bold checkmark"></i>
            </button>
            <button type="button" @click="open = false" class="w-full rounded-xl px-2.5 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-primary flex items-center justify-between transition-colors currency-option" data-currency="USD" data-flag="&#x1F1FA;&#x1F1F8;" data-symbol="$" data-rate="{{ $rateUsd }}">
                <span class="flex items-center gap-2"><span class="text-base">&#x1F1FA;&#x1F1F8;</span><span>USD <small class="text-slate-500">($)</small></span></span>
                <i class="bi bi-check2 text-primary font-bold checkmark hidden"></i>
            </button>
            <button type="button" @click="open = false" class="w-full rounded-xl px-2.5 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-primary flex items-center justify-between transition-colors currency-option" data-currency="EUR" data-flag="&#x1F1EA;&#x1F1FA;" data-symbol="€" data-rate="{{ $rateEur }}">
                <span class="flex items-center gap-2"><span class="text-base">&#x1F1EA;&#x1F1FA;</span><span>EUR <small class="text-slate-500">(€)</small></span></span>
                <i class="bi bi-check2 text-primary font-bold checkmark hidden"></i>
            </button>
            <button type="button" @click="open = false" class="w-full rounded-xl px-2.5 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-primary flex items-center justify-between transition-colors currency-option" data-currency="GBP" data-flag="&#x1F1EC;&#x1F1E7;" data-symbol="£" data-rate="{{ $rateGbp }}">
                <span class="flex items-center gap-2"><span class="text-base">&#x1F1EC;&#x1F1E7;</span><span>GBP <small class="text-slate-500">(£)</small></span></span>
                <i class="bi bi-check2 text-primary font-bold checkmark hidden"></i>
            </button>
            <button type="button" @click="open = false" class="w-full rounded-xl px-2.5 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-primary flex items-center justify-between transition-colors currency-option" data-currency="SAR" data-flag="&#x1F1F8;&#x1F1E6;" data-symbol="SAR" data-rate="{{ $rateSar }}">
                <span class="flex items-center gap-2"><span class="text-base">&#x1F1F8;&#x1F1E6;</span><span>SAR <small class="text-slate-500">(﷼)</small></span></span>
                <i class="bi bi-check2 text-primary font-bold checkmark hidden"></i>
            </button>
            <button type="button" @click="open = false" class="w-full rounded-xl px-2.5 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-primary flex items-center justify-between transition-colors currency-option" data-currency="INR" data-flag="&#x1F1EE;&#x1F1F3;" data-symbol="₹" data-rate="{{ $rateInr }}">
                <span class="flex items-center gap-2"><span class="text-base">&#x1F1EE;&#x1F1F3;</span><span>INR <small class="text-slate-500">(₹)</small></span></span>
                <i class="bi bi-check2 text-primary font-bold checkmark hidden"></i>
            </button>
        </div>
    </div>
</div>