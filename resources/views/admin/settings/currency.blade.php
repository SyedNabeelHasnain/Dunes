@extends('layouts.admin')

@section('page_title', 'Multi-Currency & Exchange Rates')

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-slate-100 bg-white">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full border border-slate-200 bg-slate-50 text-primary flex items-center justify-center text-xl shrink-0">
                    <i class="bi bi-currency-exchange"></i>
                </div>
                <div>
                    <h5 class="text-base font-extrabold text-slate-900 leading-tight">Multi-Currency & Foreign Exchange Rates</h5>
                    <div class="text-xs text-slate-500 mt-0.5">Manage real-time currency conversion rates for global guests (Base: 1 AED).</div>
                </div>
            </div>
            <div>
                <button type="button" id="btnSyncRates" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-[#e07b32] transition">
                    <i class="bi bi-arrow-repeat text-sm" id="syncIcon"></i>
                    <span id="syncText">Sync Live Rates Now</span>
                </button>
            </div>
        </div>
    </div>

    <div class="p-6">
        <div id="syncAlert"></div>

        <!-- Sync Info Banner -->
        <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="max-w-2xl">
                    <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <i class="bi bi-broadcast text-[10px]"></i> Live Exchange Feed Active
                        </span>
                        <span class="text-slate-300">&bull;</span>
                        <span class="text-xs text-slate-600">Source: <strong class="text-slate-800">Open Exchange Rates API</strong></span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed mb-0">
                        Automated sync scheduled daily at <strong class="text-slate-800">02:00 AM (UAE Time)</strong>. Rates are cached and instantly reflected across all booking tours, add-ons, and pricing cards for international visitors.
                    </p>
                </div>
                <div class="lg:text-right shrink-0">
                    <div class="text-[11px] font-semibold text-slate-500">Last Synchronized:</div>
                    <div class="text-sm font-extrabold text-slate-800 mt-0.5" id="lastSyncedDisplay">
                        <i class="bi bi-clock-history mr-1 text-primary"></i>{{ $rates['synced_at'] }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Form for Manual Adjustment & Fine Tuning -->
        <form action="{{ route('admin.settings.update') }}" method="POST" id="currencyRatesForm">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-6">
                <!-- USD -->
                <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <span class="text-2xl">🇺🇸</span>
                                <div>
                                    <h6 class="text-xs font-bold text-slate-900 leading-tight">US Dollar (USD)</h6>
                                    <span class="text-[11px] text-slate-500">Symbol: $</span>
                                </div>
                            </div>
                            <span class="rounded-full border border-slate-200 bg-white px-2.5 py-0.5 text-[11px] font-bold text-slate-600">1 AED =</span>
                        </div>
                        <div class="relative flex rounded-xl shadow-2xs mb-2">
                            <span class="inline-flex items-center px-3.5 rounded-l-xl border border-r-0 border-slate-200 bg-white text-xs font-bold text-slate-600">$</span>
                            <input type="number" step="0.0001" name="currency_rate_usd" id="rate_usd" class="w-full rounded-r-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-bold text-slate-800 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $rates['usd'] }}" required>
                        </div>
                    </div>
                    <div class="text-[11px] text-slate-500 mt-1">Standard peg: approx 0.2723 (1 USD &approx; 3.6725 AED)</div>
                </div>

                <!-- EUR -->
                <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <span class="text-2xl">🇪🇺</span>
                                <div>
                                    <h6 class="text-xs font-bold text-slate-900 leading-tight">Euro (EUR)</h6>
                                    <span class="text-[11px] text-slate-500">Symbol: €</span>
                                </div>
                            </div>
                            <span class="rounded-full border border-slate-200 bg-white px-2.5 py-0.5 text-[11px] font-bold text-slate-600">1 AED =</span>
                        </div>
                        <div class="relative flex rounded-xl shadow-2xs mb-2">
                            <span class="inline-flex items-center px-3.5 rounded-l-xl border border-r-0 border-slate-200 bg-white text-xs font-bold text-slate-600">€</span>
                            <input type="number" step="0.0001" name="currency_rate_eur" id="rate_eur" class="w-full rounded-r-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-bold text-slate-800 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $rates['eur'] }}" required>
                        </div>
                    </div>
                    <div class="text-[11px] text-slate-500 mt-1">Floating rate against AED (e.g. 0.2341 - 0.2550)</div>
                </div>

                <!-- GBP -->
                <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <span class="text-2xl">🇬🇧</span>
                                <div>
                                    <h6 class="text-xs font-bold text-slate-900 leading-tight">British Pound (GBP)</h6>
                                    <span class="text-[11px] text-slate-500">Symbol: £</span>
                                </div>
                            </div>
                            <span class="rounded-full border border-slate-200 bg-white px-2.5 py-0.5 text-[11px] font-bold text-slate-600">1 AED =</span>
                        </div>
                        <div class="relative flex rounded-xl shadow-2xs mb-2">
                            <span class="inline-flex items-center px-3.5 rounded-l-xl border border-r-0 border-slate-200 bg-white text-xs font-bold text-slate-600">£</span>
                            <input type="number" step="0.0001" name="currency_rate_gbp" id="rate_gbp" class="w-full rounded-r-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-bold text-slate-800 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $rates['gbp'] }}" required>
                        </div>
                    </div>
                    <div class="text-[11px] text-slate-500 mt-1">Floating rate against AED (e.g. 0.2010 - 0.2200)</div>
                </div>

                <!-- SAR -->
                <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <span class="text-2xl">🇸🇦</span>
                                <div>
                                    <h6 class="text-xs font-bold text-slate-900 leading-tight">Saudi Riyal (SAR)</h6>
                                    <span class="text-[11px] text-slate-500">Symbol: SAR (﷼)</span>
                                </div>
                            </div>
                            <span class="rounded-full border border-slate-200 bg-white px-2.5 py-0.5 text-[11px] font-bold text-slate-600">1 AED =</span>
                        </div>
                        <div class="relative flex rounded-xl shadow-2xs mb-2">
                            <span class="inline-flex items-center px-3.5 rounded-l-xl border border-r-0 border-slate-200 bg-white text-xs font-bold text-slate-600">SAR</span>
                            <input type="number" step="0.0001" name="currency_rate_sar" id="rate_sar" class="w-full rounded-r-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-bold text-slate-800 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $rates['sar'] }}" required>
                        </div>
                    </div>
                    <div class="text-[11px] text-slate-500 mt-1">GCC peg: approx 1.0210 (1 AED &approx; 1.0210 SAR)</div>
                </div>

                <!-- INR -->
                <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <span class="text-2xl">🇮🇳</span>
                                <div>
                                    <h6 class="text-xs font-bold text-slate-900 leading-tight">Indian Rupee (INR)</h6>
                                    <span class="text-[11px] text-slate-500">Symbol: ₹</span>
                                </div>
                            </div>
                            <span class="rounded-full border border-slate-200 bg-white px-2.5 py-0.5 text-[11px] font-bold text-slate-600">1 AED =</span>
                        </div>
                        <div class="relative flex rounded-xl shadow-2xs mb-2">
                            <span class="inline-flex items-center px-3.5 rounded-l-xl border border-r-0 border-slate-200 bg-white text-xs font-bold text-slate-600">₹</span>
                            <input type="number" step="0.01" name="currency_rate_inr" id="rate_inr" class="w-full rounded-r-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-bold text-slate-800 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $rates['inr'] }}" required>
                        </div>
                    </div>
                    <div class="text-[11px] text-slate-500 mt-1">Floating rate against AED (e.g. 22.85 - 25.90)</div>
                </div>

                <!-- Live Conversion Calculator Card -->
                <div class="p-5 bg-amber-500/5 rounded-2xl border border-primary/20 flex flex-col justify-between">
                    <div>
                        <h6 class="text-xs font-black uppercase tracking-wider text-primary mb-3 flex items-center gap-1.5">
                            <i class="bi bi-calculator"></i> Quick Conversion Matrix (100 AED)
                        </h6>
                        <div class="flex flex-col gap-2 text-xs mt-3" id="quickMatrix">
                            <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                                <span class="text-slate-600 font-medium">🇺🇸 USD:</span>
                                <strong id="calc_usd" class="font-extrabold text-slate-900">$27.23</strong>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                                <span class="text-slate-600 font-medium">🇪🇺 EUR:</span>
                                <strong id="calc_eur" class="font-extrabold text-slate-900">€25.10</strong>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                                <span class="text-slate-600 font-medium">🇬🇧 GBP:</span>
                                <strong id="calc_gbp" class="font-extrabold text-slate-900">£21.50</strong>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                                <span class="text-slate-600 font-medium">🇸🇦 SAR:</span>
                                <strong id="calc_sar" class="font-extrabold text-slate-900">102.10 SAR</strong>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-slate-600 font-medium">🇮🇳 INR:</span>
                                <strong id="calc_inr" class="font-extrabold text-slate-900">₹2,285</strong>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-primary/10 text-[11px] text-slate-500">
                        Updates dynamically as rate inputs change.
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-[#e07b32] transition">
                    <i class="bi bi-save"></i> Save Exchange Rates
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnSync = document.getElementById('btnSyncRates');
    const syncIcon = document.getElementById('syncIcon');
    const syncText = document.getElementById('syncText');
    const syncAlert = document.getElementById('syncAlert');
    const lastSyncedDisplay = document.getElementById('lastSyncedDisplay');

    const inputUsd = document.getElementById('rate_usd');
    const inputEur = document.getElementById('rate_eur');
    const inputGbp = document.getElementById('rate_gbp');
    const inputSar = document.getElementById('rate_sar');
    const inputInr = document.getElementById('rate_inr');

    function updateMatrix() {
        const usd = parseFloat(inputUsd.value) || 0;
        const eur = parseFloat(inputEur.value) || 0;
        const gbp = parseFloat(inputGbp.value) || 0;
        const sar = parseFloat(inputSar.value) || 0;
        const inr = parseFloat(inputInr.value) || 0;

        document.getElementById('calc_usd').textContent = '$' + (100 * usd).toFixed(2);
        document.getElementById('calc_eur').textContent = '€' + (100 * eur).toFixed(2);
        document.getElementById('calc_gbp').textContent = '£' + (100 * gbp).toFixed(2);
        document.getElementById('calc_sar').textContent = (100 * sar).toFixed(2) + ' SAR';
        document.getElementById('calc_inr').textContent = '₹' + Math.round(100 * inr).toLocaleString('en-US');
    }

    [inputUsd, inputEur, inputGbp, inputSar, inputInr].forEach(input => {
        if (input) input.addEventListener('input', updateMatrix);
    });
    updateMatrix();

    if (btnSync) {
        btnSync.addEventListener('click', function () {
            btnSync.disabled = true;
            syncIcon.classList.add('spin-animation');
            syncText.textContent = 'Synchronizing...';
            syncAlert.innerHTML = '';

            fetch("{{ route('admin.settings.sync-currency') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                btnSync.disabled = false;
                syncIcon.classList.remove('spin-animation');
                syncText.textContent = 'Sync Live Rates Now';

                if (data.success && data.rates) {
                    inputUsd.value = data.rates.usd;
                    inputEur.value = data.rates.eur;
                    inputGbp.value = data.rates.gbp;
                    inputSar.value = data.rates.sar;
                    inputInr.value = data.rates.inr;

                    if (data.rates.synced_at) {
                        lastSyncedDisplay.innerHTML = '<i class="bi bi-clock-history mr-1 text-primary"></i>' + data.rates.synced_at;
                    }

                    updateMatrix();

                    syncAlert.innerHTML = `
                        <div class="flex items-center justify-between p-4 mb-5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 text-xs shadow-xs">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-check-circle-fill text-emerald-600 text-sm"></i>
                                <span>${data.message}</span>
                            </div>
                            <button type="button" class="text-emerald-600 hover:text-emerald-800 p-1" onclick="this.closest('.flex').remove()"><i class="bi bi-x-lg"></i></button>
                        </div>
                    `;
                } else {
                    throw new Error(data.message || 'Failed to sync rates.');
                }
            })
            .catch(err => {
                btnSync.disabled = false;
                syncIcon.classList.remove('spin-animation');
                syncText.textContent = 'Sync Live Rates Now';

                syncAlert.innerHTML = `
                    <div class="flex items-center justify-between p-4 mb-5 rounded-xl border border-rose-200 bg-rose-50 text-rose-800 text-xs shadow-xs">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill text-rose-600 text-sm"></i>
                            <span>${err.message || 'Sync failed. Please try again.'}</span>
                        </div>
                        <button type="button" class="text-rose-600 hover:text-rose-800 p-1" onclick="this.closest('.flex').remove()"><i class="bi bi-x-lg"></i></button>
                    </div>
                `;
            });
        });
    }
});
</script>
<style>
.spin-animation {
    display: inline-block;
    animation: spinIcon 1s linear infinite;
}
@keyframes spinIcon {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endpush
