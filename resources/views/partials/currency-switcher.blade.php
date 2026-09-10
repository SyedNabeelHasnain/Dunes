@php
    $settingsService = app(\App\Services\SettingsService::class);
    $rateUsd = $settingsService->get('currency_rate_usd', '0.2723');
    $rateEur = $settingsService->get('currency_rate_eur', '0.2510');
    $rateGbp = $settingsService->get('currency_rate_gbp', '0.2150');
    $rateSar = $settingsService->get('currency_rate_sar', '1.0210');
    $rateInr = $settingsService->get('currency_rate_inr', '22.85');
    $dropdownId = $switcherId ?? 'currencyDropdownBtn';
@endphp
<div class="dropdown currency-switcher-dropdown d-inline-block">
    <button class="btn btn-outline-light btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 border-opacity-25 shadow-sm text-dark bg-white" type="button" id="{{ $dropdownId }}" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 13px; border: 1px solid rgba(0,0,0,0.12); min-height: 38px;">
        <span class="current-currency-flag">🇦🇪</span>
        <span class="current-currency-code fw-bold ms-1">AED</span>
        <i class="bi bi-chevron-down ms-1 text-muted" style="font-size: 10px;"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 p-2 mt-2" aria-labelledby="{{ $dropdownId }}" style="min-width: 175px; z-index: 1060;">
        <li><h6 class="dropdown-header text-uppercase small fw-bold text-muted py-1" style="font-size: 11px; letter-spacing: 0.5px;">Display Currency</h6></li>
        <li>
            <button type="button" class="dropdown-item rounded-3 py-2 d-flex align-items-center justify-content-between currency-option active" data-currency="AED" data-flag="🇦🇪" data-symbol="AED" data-rate="1">
                <span><span class="me-2">🇦🇪</span>AED <small class="text-muted">(د.إ)</small></span>
                <i class="bi bi-check2 checkmark"></i>
            </button>
        </li>
        <li>
            <button type="button" class="dropdown-item rounded-3 py-2 d-flex align-items-center justify-content-between currency-option" data-currency="USD" data-flag="🇺🇸" data-symbol="$" data-rate="{{ $rateUsd }}">
                <span><span class="me-2">🇺🇸</span>USD <small class="text-muted">($)</small></span>
                <i class="bi bi-check2 checkmark d-none"></i>
            </button>
        </li>
        <li>
            <button type="button" class="dropdown-item rounded-3 py-2 d-flex align-items-center justify-content-between currency-option" data-currency="EUR" data-flag="🇪🇺" data-symbol="€" data-rate="{{ $rateEur }}">
                <span><span class="me-2">🇪🇺</span>EUR <small class="text-muted">(€)</small></span>
                <i class="bi bi-check2 checkmark d-none"></i>
            </button>
        </li>
        <li>
            <button type="button" class="dropdown-item rounded-3 py-2 d-flex align-items-center justify-content-between currency-option" data-currency="GBP" data-flag="🇬🇧" data-symbol="£" data-rate="{{ $rateGbp }}">
                <span><span class="me-2">🇬🇧</span>GBP <small class="text-muted">(£)</small></span>
                <i class="bi bi-check2 checkmark d-none"></i>
            </button>
        </li>
        <li>
            <button type="button" class="dropdown-item rounded-3 py-2 d-flex align-items-center justify-content-between currency-option" data-currency="SAR" data-flag="🇸🇦" data-symbol="SAR" data-rate="{{ $rateSar }}">
                <span><span class="me-2">🇸🇦</span>SAR <small class="text-muted">(﷼)</small></span>
                <i class="bi bi-check2 checkmark d-none"></i>
            </button>
        </li>
        <li>
            <button type="button" class="dropdown-item rounded-3 py-2 d-flex align-items-center justify-content-between currency-option" data-currency="INR" data-flag="🇮🇳" data-symbol="₹" data-rate="{{ $rateInr }}">
                <span><span class="me-2">🇮🇳</span>INR <small class="text-muted">(₹)</small></span>
                <i class="bi bi-check2 checkmark d-none"></i>
            </button>
        </li>
    </ul>
</div>