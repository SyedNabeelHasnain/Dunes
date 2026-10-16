<!-- Real-Time Social Proof & Urgency Toast Component (Tailwind v4) -->
@php
    $spEnabled = (string) ($settings['social_proof_enabled'] ?? '1') === '1';
    $spMobileEnabled = (string) ($settings['social_proof_mobile_enabled'] ?? '1') === '1';
    $spInitialDelayMs = max(1, (int) ($settings['social_proof_initial_delay'] ?? 8)) * 1000;
    $spDurationMs = max(2, (int) ($settings['social_proof_duration'] ?? 6)) * 1000;
    $spIntervalMs = max(5, (int) ($settings['social_proof_interval'] ?? 24)) * 1000;
@endphp
@if($spEnabled)
<div id="dunesSocialProofToast" 
     class="fixed bottom-24 sm:bottom-6 start-4 sm:start-6 z-40 max-w-sm w-[calc(100%-2rem)] pb-safe opacity-0 translate-y-6 scale-95 pointer-events-none transition-all duration-500 ease-out {{ $spMobileEnabled ? '' : 'hidden sm:block' }}" 
     aria-live="polite" 
     role="status" 
     style="display: none;">
    <div class="p-3 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-200/90 flex items-center gap-3 relative">
        <button type="button" 
                class="absolute top-2 end-2.5 w-6 h-6 rounded-full text-slate-500 hover:text-slate-800 hover:bg-slate-100 flex items-center justify-center transition-colors cursor-pointer text-base leading-none" 
                id="socialProofCloseBtn" 
                aria-label="{{ __('ui.common.close') }}">&times;</button>
        
        <div class="relative shrink-0 w-12 h-12 rounded-xl overflow-hidden bg-slate-100 shadow-2xs">
            <img id="spTourImage" src="{{ asset('images/evening-desert-safari-dubai-hero.avif') }}" alt="Tour" class="w-full h-full object-cover" width="48" height="48" loading="lazy">
            <span class="absolute -bottom-1 -end-1 bg-white rounded-full p-0.5 shadow-2xs text-emerald-500 text-xs flex items-center justify-center" title="{{ __('ui.trust.instant_confirmation') }}">
                <i class="bi bi-patch-check-fill"></i>
            </span>
        </div>

        <div class="flex-1 min-w-0 pe-4">
            <div class="flex items-center gap-1 mb-0.5">
                <span class="font-bold text-slate-900 text-xs truncate max-w-[130px]" id="spCustomerName">Michael R.</span>
                <span class="text-slate-500 text-[11px]">booked</span>
            </div>
            <a href="{{ localized_route('tours.index') }}" id="spTourLink" class="block font-bold text-xs text-primary hover:text-primary-dark transition-colors truncate">
                Premium Evening Desert Safari
            </a>
            <div class="flex items-center gap-1.5 text-slate-500 text-[10px] mt-0.5">
                <span id="spTimeAgo" class="flex items-center gap-1"><i class="bi bi-clock"></i>12m ago</span>
                <span>&bull;</span>
                <span class="text-emerald-600 font-semibold flex items-center gap-0.5"><i class="bi bi-shield-check"></i> Verified</span>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    const MOBILE_ENABLED = {{ $spMobileEnabled ? 'true' : 'false' }};
    const INITIAL_DELAY_MS = {{ $spInitialDelayMs }};
    const DISPLAY_DURATION_MS = {{ $spDurationMs }};
    const INTERVAL_MS = {{ $spIntervalMs }};

    if (!MOBILE_ENABLED && window.innerWidth < 640) {
        return;
    }

    if (sessionStorage.getItem('dunes_sp_dismissed') === '1') {
        return;
    }

    const toastEl = document.getElementById('dunesSocialProofToast');
    if (!toastEl) return;

    const closeBtn = document.getElementById('socialProofCloseBtn');
    const imgEl = document.getElementById('spTourImage');
    const nameEl = document.getElementById('spCustomerName');
    const linkEl = document.getElementById('spTourLink');
    const timeEl = document.getElementById('spTimeAgo');

    let items = [];
    let currentIndex = 0;
    let hideTimeout = null;
    let nextTimeout = null;
    let isHovered = false;

    closeBtn?.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        hideToast();
        sessionStorage.setItem('dunes_sp_dismissed', '1');
        clearTimeout(nextTimeout);
        clearTimeout(hideTimeout);
    });

    toastEl.addEventListener('mouseenter', function() {
        isHovered = true;
        if (hideTimeout) clearTimeout(hideTimeout);
    });

    toastEl.addEventListener('mouseleave', function() {
        isHovered = false;
        if (toastEl.classList.contains('opacity-100')) {
            hideTimeout = setTimeout(hideToast, 2500);
        }
    });

    function showToast() {
        if (!MOBILE_ENABLED && window.innerWidth < 640) {
            return;
        }
        if (sessionStorage.getItem('dunes_sp_dismissed') === '1' || document.hidden) {
            nextTimeout = setTimeout(showToast, INTERVAL_MS);
            return;
        }

        if (!items.length) return;

        const item = items[currentIndex % items.length];
        currentIndex++;

        if (nameEl) nameEl.textContent = item.name || 'Guest';
        if (linkEl) {
            linkEl.textContent = item.tour || 'Dubai Desert Safari';
            linkEl.href = item.url || '#';
        }
        if (imgEl && item.image) {
            imgEl.src = item.image;
        }
        if (timeEl) {
            timeEl.innerHTML = '<i class="bi bi-clock me-1"></i>' + (item.time_ago || 'Just now');
        }

        toastEl.style.display = 'block';
        void toastEl.offsetWidth; // trigger reflow
        toastEl.classList.remove('opacity-0', 'translate-y-6', 'scale-95', 'pointer-events-none');
        toastEl.classList.add('opacity-100', 'translate-y-0', 'scale-100', 'pointer-events-auto');

        hideTimeout = setTimeout(function() {
            if (!isHovered) {
                hideToast();
            }
        }, DISPLAY_DURATION_MS);
    }

    function hideToast() {
        toastEl.classList.remove('opacity-100', 'translate-y-0', 'scale-100', 'pointer-events-auto');
        toastEl.classList.add('opacity-0', 'translate-y-6', 'scale-95', 'pointer-events-none');
        setTimeout(function() {
            if (toastEl.classList.contains('opacity-0')) {
                toastEl.style.display = 'none';
            }
        }, 500);

        const jitter = Math.floor(Math.random() * 4000);
        nextTimeout = setTimeout(showToast, INTERVAL_MS + jitter);
    }

    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearTimeout(hideTimeout);
            clearTimeout(nextTimeout);
        } else if (sessionStorage.getItem('dunes_sp_dismissed') !== '1') {
            nextTimeout = setTimeout(showToast, INITIAL_DELAY_MS);
        }
    });

    window.addEventListener('load', function() {
        setTimeout(function() {
            fetch('/ajax.php?action=get_social_proof', {
                headers: { 'Accept': 'application/json' }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data && data.success && Array.isArray(data.items) && data.items.length) {
                    items = data.items;
                    items.sort(function() { return 0.5 - Math.random(); });
                    nextTimeout = setTimeout(showToast, INITIAL_DELAY_MS);
                }
            })
            .catch(function(e) {});
        }, 1200);
    });
})();
</script>
@endif
