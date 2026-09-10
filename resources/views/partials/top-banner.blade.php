@php
    $settingsService = app(\App\Services\SettingsService::class);
    $topBannerActive = $settingsService->get('top_promo_banner_active', '1') === '1';
    $topBannerText = $settingsService->get('top_promo_banner_text', '🎟️ First-Time Traveler? Claim 25% OFF Your Desert Safari Today with Code FIRST25! • 100% Free 24h Cancellation');
    $topBannerCode = $settingsService->get('top_promo_banner_code', 'FIRST25');
@endphp

@if($topBannerActive)
<!-- Top Sticky Announcement Bar -->
<div id="dunesTopPromoBanner" class="py-2 px-3 text-white text-center position-relative z-3 shadow-sm d-flex align-items-center justify-content-center flex-wrap gap-2" style="background: linear-gradient(90deg, #111827 0%, #1f2937 50%, #0f172a 100%); border-bottom: 2px solid #F58F43; font-size: 0.85rem;">
    <span class="fw-bold">{{ $topBannerText }}</span>
    <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 py-0 fw-800 text-dark d-inline-flex align-items-center gap-1 shadow-sm top-banner-copy-btn" data-code="{{ $topBannerCode }}" style="font-size: 0.75rem; height: 26px;">
        <span>CODE: <strong class="font-monospace">{{ $topBannerCode }}</strong></span>
        <i class="bi bi-clipboard"></i>
    </button>
    <button type="button" class="btn-close btn-close-white ms-2 shadow-none position-absolute end-0 me-3" style="font-size: 0.65rem;" aria-label="Dismiss announcement" onclick="document.getElementById('dunesTopPromoBanner').style.display='none'; if(window.syncHeaderHeight) window.syncHeaderHeight();"></button>
</div>
@endif
