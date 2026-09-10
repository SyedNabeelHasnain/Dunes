@extends('layouts.admin')

@section('page_title', 'Multi-Currency & Exchange Rates')

@section('content')
<div class="card card-modern border shadow-sm rounded-4 bg-white overflow-hidden mb-5">
    <div class="card-header bg-white py-3 border-bottom ps-4 pe-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-light text-primary rounded-circle border d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.3rem;">
                    <i class="bi bi-currency-exchange"></i>
                </div>
                <div>
                    <h5 class="fw-800 mb-0 text-dark">Multi-Currency & Foreign Exchange Rates</h5>
                    <div class="text-muted small">Manage real-time currency conversion rates for global guests (Base: 1 AED).</div>
                </div>
            </div>
            <div>
                <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-2" id="btnSyncRates">
                    <i class="bi bi-arrow-repeat" id="syncIcon"></i>
                    <span id="syncText">Sync Live Rates Now</span>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-4 ps-4 pe-4">
        <div id="syncAlert"></div>

        <!-- Sync Info Banner -->
        <div class="p-4 bg-light rounded-4 border mb-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold">
                            <i class="bi bi-broadcast me-1"></i> Live Exchange Feed Active
                        </span>
                        <span class="text-muted small">&bull;</span>
                        <span class="text-muted small">Source: <strong>Open Exchange Rates API</strong></span>
                    </div>
                    <p class="text-dark mb-0 small">
                        Automated sync scheduled daily at <strong>02:00 AM (UAE Time)</strong>. Rates are cached and instantly reflected across all booking tours, add-ons, and pricing cards for international visitors.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="text-muted small">Last Synchronized:</div>
                    <div class="fw-bold text-dark fs-6" id="lastSyncedDisplay">
                        <i class="bi bi-clock-history me-1 text-primary"></i>{{ $rates['synced_at'] }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Form for Manual Adjustment & Fine Tuning -->
        <form action="{{ route('admin.settings.update') }}" method="POST" id="currencyRatesForm">
            @csrf

            <div class="row g-4 mb-4">
                <!-- USD -->
                <div class="col-md-6 col-xl-4">
                    <div class="p-4 bg-light rounded-4 border h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fs-4">🇺🇸</span>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">US Dollar (USD)</h6>
                                    <span class="text-muted small">Symbol: $</span>
                                </div>
                            </div>
                            <span class="badge bg-white text-dark border rounded-pill px-2 py-1 small fw-bold">1 AED =</span>
                        </div>
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-white fw-bold">$</span>
                            <input type="number" step="0.0001" name="currency_rate_usd" id="rate_usd" class="form-control fw-bold fs-6" value="{{ $rates['usd'] }}" required>
                        </div>
                        <div class="text-muted small">Standard peg: approx 0.2723 (1 USD &approx; 3.6725 AED)</div>
                    </div>
                </div>

                <!-- EUR -->
                <div class="col-md-6 col-xl-4">
                    <div class="p-4 bg-light rounded-4 border h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fs-4">🇪🇺</span>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Euro (EUR)</h6>
                                    <span class="text-muted small">Symbol: €</span>
                                </div>
                            </div>
                            <span class="badge bg-white text-dark border rounded-pill px-2 py-1 small fw-bold">1 AED =</span>
                        </div>
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-white fw-bold">€</span>
                            <input type="number" step="0.0001" name="currency_rate_eur" id="rate_eur" class="form-control fw-bold fs-6" value="{{ $rates['eur'] }}" required>
                        </div>
                        <div class="text-muted small">Floating rate against AED (e.g. 0.2341 - 0.2550)</div>
                    </div>
                </div>

                <!-- GBP -->
                <div class="col-md-6 col-xl-4">
                    <div class="p-4 bg-light rounded-4 border h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fs-4">🇬🇧</span>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">British Pound (GBP)</h6>
                                    <span class="text-muted small">Symbol: £</span>
                                </div>
                            </div>
                            <span class="badge bg-white text-dark border rounded-pill px-2 py-1 small fw-bold">1 AED =</span>
                        </div>
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-white fw-bold">£</span>
                            <input type="number" step="0.0001" name="currency_rate_gbp" id="rate_gbp" class="form-control fw-bold fs-6" value="{{ $rates['gbp'] }}" required>
                        </div>
                        <div class="text-muted small">Floating rate against AED (e.g. 0.2010 - 0.2200)</div>
                    </div>
                </div>

                <!-- SAR -->
                <div class="col-md-6 col-xl-4">
                    <div class="p-4 bg-light rounded-4 border h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fs-4">🇸🇦</span>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Saudi Riyal (SAR)</h6>
                                    <span class="text-muted small">Symbol: SAR (﷼)</span>
                                </div>
                            </div>
                            <span class="badge bg-white text-dark border rounded-pill px-2 py-1 small fw-bold">1 AED =</span>
                        </div>
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-white fw-bold">SAR</span>
                            <input type="number" step="0.0001" name="currency_rate_sar" id="rate_sar" class="form-control fw-bold fs-6" value="{{ $rates['sar'] }}" required>
                        </div>
                        <div class="text-muted small">GCC peg: approx 1.0210 (1 AED &approx; 1.0210 SAR)</div>
                    </div>
                </div>

                <!-- INR -->
                <div class="col-md-6 col-xl-4">
                    <div class="p-4 bg-light rounded-4 border h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fs-4">🇮🇳</span>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Indian Rupee (INR)</h6>
                                    <span class="text-muted small">Symbol: ₹</span>
                                </div>
                            </div>
                            <span class="badge bg-white text-dark border rounded-pill px-2 py-1 small fw-bold">1 AED =</span>
                        </div>
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-white fw-bold">₹</span>
                            <input type="number" step="0.01" name="currency_rate_inr" id="rate_inr" class="form-control fw-bold fs-6" value="{{ $rates['inr'] }}" required>
                        </div>
                        <div class="text-muted small">Floating rate against AED (e.g. 22.85 - 25.90)</div>
                    </div>
                </div>

                <!-- Live Conversion Calculator Card -->
                <div class="col-md-6 col-xl-4">
                    <div class="p-4 bg-primary-subtle rounded-4 border border-primary-subtle h-100 d-flex flex-column justify-content-between">
                        <div>
                            <h6 class="text-primary fw-800 text-uppercase small mb-2">
                                <i class="bi bi-calculator me-1"></i> Quick Conversion Matrix (100 AED)
                            </h6>
                            <div class="d-flex flex-column gap-1 small mt-3" id="quickMatrix">
                                <div class="d-flex justify-content-between">
                                    <span>🇺🇸 USD:</span>
                                    <strong id="calc_usd">$27.23</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>🇪🇺 EUR:</span>
                                    <strong id="calc_eur">€25.10</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>🇬🇧 GBP:</span>
                                    <strong id="calc_gbp">£21.50</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>🇸🇦 SAR:</span>
                                    <strong id="calc_sar">102.10 SAR</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>🇮🇳 INR:</span>
                                    <strong id="calc_inr">₹2,285</strong>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 pt-2 border-top border-primary-subtle text-muted small">
                            Updates dynamically as rate inputs change.
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2.5 fw-bold shadow-sm">
                    <i class="bi bi-save me-2"></i>Save Exchange Rates
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
                        lastSyncedDisplay.innerHTML = '<i class="bi bi-clock-history me-1 text-primary"></i>' + data.rates.synced_at;
                    }

                    updateMatrix();

                    syncAlert.innerHTML = `
                        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4 border-0 shadow-sm" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i> ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                    <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4 border-0 shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> ${err.message || 'Sync failed. Please try again.'}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
