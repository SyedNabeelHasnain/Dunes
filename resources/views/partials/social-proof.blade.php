<!-- Real-Time Social Proof & Urgency Toast Component (Tailwind v4) -->
<div id="dunesSocialProofToast" 
     class="fixed bottom-24 sm:bottom-6 left-4 sm:left-6 z-40 max-w-sm w-[calc(100%-2rem)] pb-safe opacity-0 translate-y-6 scale-95 pointer-events-none transition-all duration-500 ease-out" 
     aria-live="polite" 
     role="status" 
     style="display: none;">
    <div class="p-3 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-200/90 flex items-center gap-3 relative">
        <button type="button" 
                class="absolute top-2 right-2.5 w-6 h-6 rounded-full text-slate-500 hover:text-slate-800 hover:bg-slate-100 flex items-center justify-center transition-colors cursor-pointer text-base leading-none" 
                id="socialProofCloseBtn" 
                aria-label="Dismiss">&times;</button>
        
        <div class="relative shrink-0 w-12 h-12 rounded-xl overflow-hidden bg-slate-100 shadow-2xs">
            <img id="spTourImage" src="{{ asset('images/evening-desert-safari-dubai-hero.avif') }}" alt="Tour" class="w-full h-full object-cover" width="48" height="48" loading="lazy">
            <span class="absolute -bottom-1 -right-1 bg-white rounded-full p-0.5 shadow-2xs text-emerald-500 text-xs flex items-center justify-center" title="Verified Guest Booking">
                <i class="bi bi-patch-check-fill"></i>
            </span>
        </div>

        <div class="flex-1 min-w-0 pr-4">
            <div class="flex items-center gap-1 mb-0.5">
                <span class="font-bold text-slate-900 text-xs truncate max-w-[130px]" id="spCustomerName">Michael R.</span>
                <span class="text-slate-500 text-[11px]">booked</span>
            </div>
            <a href="{{ route('tours.index') }}" id="spTourLink" class="block font-bold text-xs text-primary hover:text-primary-dark transition-colors truncate">
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
        if (sessionStorage.getItem('dunes_sp_dismissed') === '1' || document.hidden) {
            nextTimeout = setTimeout(showToast, 15000);
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
        }, 6000);
    }

    function hideToast() {
        toastEl.classList.remove('opacity-100', 'translate-y-0', 'scale-100', 'pointer-events-auto');
        toastEl.classList.add('opacity-0', 'translate-y-6', 'scale-95', 'pointer-events-none');
        setTimeout(function() {
            if (toastEl.classList.contains('opacity-0')) {
                toastEl.style.display = 'none';
            }
        }, 500);

        const delay = Math.floor(Math.random() * 8000) + 20000;
        nextTimeout = setTimeout(showToast, delay);
    }

    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearTimeout(hideTimeout);
            clearTimeout(nextTimeout);
        } else if (sessionStorage.getItem('dunes_sp_dismissed') !== '1') {
            nextTimeout = setTimeout(showToast, 8000);
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
                    nextTimeout = setTimeout(showToast, 7500);
                }
            })
            .catch(function(e) {});
        }, 1500);
    });
})();
</script>
