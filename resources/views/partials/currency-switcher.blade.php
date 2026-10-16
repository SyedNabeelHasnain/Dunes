@php
    $settingsService = app(\App\Services\SettingsService::class);
    $rateUsd = $settingsService->get('currency_rate_usd', '0.2723');
    $rateEur = $settingsService->get('currency_rate_eur', '0.2510');
    $rateGbp = $settingsService->get('currency_rate_gbp', '0.2150');
    $rateSar = $settingsService->get('currency_rate_sar', '1.0210');
    $rateInr = $settingsService->get('currency_rate_inr', '22.85');
    $dropdownId = $switcherId ?? 'currencyDropdownBtn';
@endphp
<div class="relative inline-block text-start" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
    <button @click="open = !open" :aria-expanded="open" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold text-slate-800 bg-white border border-slate-200/90 shadow-sm hover:border-primary/50 transition-all cursor-pointer min-h-[38px]" type="button" id="{{ $dropdownId }}">
        <i class="bi bi-currency-exchange text-xs text-amber-500 leading-none"></i>
        <span class="current-currency-code font-extrabold ms-1">AED</span>
        <i class="bi bi-chevron-down text-[10px] text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
    </button>
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
         class="absolute end-0 mt-2 w-48 rounded-2xl bg-white p-2 shadow-xl border border-slate-200 z-50 focus:outline-none"
         style="display: none;">
        <div class="px-2 py-1 text-[11px] font-extrabold uppercase tracking-wider text-slate-500">
            {{ __('ui.common.currency') }}
        </div>
        <div class="space-y-1 mt-1">
            <button type="button" @click="open = false" class="w-full rounded-xl px-2.5 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-primary flex items-center justify-between transition-colors currency-option active" data-currency="AED" data-flag="د.إ" data-symbol="AED" data-rate="1">
                <span class="flex items-center gap-2"><span class="w-6 h-5 rounded-md bg-slate-100 text-slate-700 font-bold text-[10px] inline-flex items-center justify-center shrink-0">د.إ</span><span>AED <small class="text-slate-400 font-normal">(UAE Dirham)</small></span></span>
                <i class="bi bi-check2 text-primary font-bold checkmark"></i>
            </button>
            <button type="button" @click="open = false" class="w-full rounded-xl px-2.5 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-primary flex items-center justify-between transition-colors currency-option" data-currency="USD" data-flag="$" data-symbol="$" data-rate="{{ $rateUsd }}">
                <span class="flex items-center gap-2"><span class="w-6 h-5 rounded-md bg-slate-100 text-slate-700 font-bold text-[11px] inline-flex items-center justify-center shrink-0">$</span><span>USD <small class="text-slate-400 font-normal">(US Dollar)</small></span></span>
                <i class="bi bi-check2 text-primary font-bold checkmark hidden"></i>
            </button>
            <button type="button" @click="open = false" class="w-full rounded-xl px-2.5 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-primary flex items-center justify-between transition-colors currency-option" data-currency="EUR" data-flag="€" data-symbol="€" data-rate="{{ $rateEur }}">
                <span class="flex items-center gap-2"><span class="w-6 h-5 rounded-md bg-slate-100 text-slate-700 font-bold text-[11px] inline-flex items-center justify-center shrink-0">€</span><span>EUR <small class="text-slate-400 font-normal">(Euro)</small></span></span>
                <i class="bi bi-check2 text-primary font-bold checkmark hidden"></i>
            </button>
            <button type="button" @click="open = false" class="w-full rounded-xl px-2.5 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-primary flex items-center justify-between transition-colors currency-option" data-currency="GBP" data-flag="£" data-symbol="£" data-rate="{{ $rateGbp }}">
                <span class="flex items-center gap-2"><span class="w-6 h-5 rounded-md bg-slate-100 text-slate-700 font-bold text-[11px] inline-flex items-center justify-center shrink-0">£</span><span>GBP <small class="text-slate-400 font-normal">(British Pound)</small></span></span>
                <i class="bi bi-check2 text-primary font-bold checkmark hidden"></i>
            </button>
            <button type="button" @click="open = false" class="w-full rounded-xl px-2.5 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-primary flex items-center justify-between transition-colors currency-option" data-currency="SAR" data-flag="﷼" data-symbol="SAR" data-rate="{{ $rateSar }}">
                <span class="flex items-center gap-2"><span class="w-6 h-5 rounded-md bg-slate-100 text-slate-700 font-bold text-[11px] inline-flex items-center justify-center shrink-0">﷼</span><span>SAR <small class="text-slate-400 font-normal">(Saudi Riyal)</small></span></span>
                <i class="bi bi-check2 text-primary font-bold checkmark hidden"></i>
            </button>
            <button type="button" @click="open = false" class="w-full rounded-xl px-2.5 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-primary flex items-center justify-between transition-colors currency-option" data-currency="INR" data-flag="₹" data-symbol="₹" data-rate="{{ $rateInr }}">
                <span class="flex items-center gap-2"><span class="w-6 h-5 rounded-md bg-slate-100 text-slate-700 font-bold text-[11px] inline-flex items-center justify-center shrink-0">₹</span><span>INR <small class="text-slate-400 font-normal">(Indian Rupee)</small></span></span>
                <i class="bi bi-check2 text-primary font-bold checkmark hidden"></i>
            </button>
        </div>
    </div>
</div>