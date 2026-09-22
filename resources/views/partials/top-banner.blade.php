@php
    $settingsService = app(\App\Services\SettingsService::class);
    $topBannerActive = ($settingsService->get('promo_top_banner_enabled', $settingsService->get('top_promo_banner_active', '1'))) === '1';
    $topBannerBadge = $settingsService->get('promo_top_banner_badge', 'Limited Time Offer');
    $rawBannerText = $settingsService->get('promo_top_banner_text', $settingsService->get('top_promo_banner_text', 'Special Online Exclusive: Get 25% OFF on all Desert Safari Tours! • 100% Free 24h Cancellation'));
    $topBannerText = trim(preg_replace('/[\x{1F300}-\x{1F64F}\x{1F680}-\x{1F6FF}\x{1F900}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', $rawBannerText));
    $topBannerCode = $settingsService->get('promo_top_banner_code', $settingsService->get('top_promo_banner_code', 'DUNESWELCOME'));
@endphp

@if($topBannerActive)
<!-- Top Sticky Announcement Bar -->
<div id="dunesTopPromoBanner" class="py-2 px-3 text-white text-center relative z-30 shadow-sm flex items-center justify-center flex-wrap gap-2 text-xs sm:text-sm" style="background: linear-gradient(90deg, #111827 0%, #1f2937 50%, #0f172a 100%); border-bottom: 2px solid #F69044;">
    @if(!empty($topBannerBadge))
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold uppercase bg-amber-400 text-slate-950 tracking-wider">{{ $topBannerBadge }}</span>
    @endif
    <span class="font-bold flex items-center gap-1.5"><i class="bi bi-tag-fill text-amber-400"></i><span>{{ $topBannerText }}</span></span>
    <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-sm transition-all top-banner-copy-btn cursor-pointer" data-code="{{ $topBannerCode }}">
        <span>CODE: <strong class="font-mono font-black">{{ $topBannerCode }}</strong></span>
        <i class="bi bi-clipboard"></i>
    </button>
    <button type="button" class="text-white/60 hover:text-white transition-colors absolute right-3 top-1/2 -translate-y-1/2 p-1 text-xs cursor-pointer" aria-label="Dismiss announcement" onclick="document.getElementById('dunesTopPromoBanner').style.display='none'; if(window.syncHeaderHeight) window.syncHeaderHeight();">
        <i class="bi bi-x-lg"></i>
    </button>
</div>
@endif
