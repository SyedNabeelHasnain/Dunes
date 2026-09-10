<!-- Real-Time Social Proof & Urgency Toast Component -->
<div id="dunesSocialProofToast" class="social-proof-toast-wrapper" aria-live="polite" role="status" style="display: none;">
    <div class="social-proof-card shadow-lg rounded-4 p-2.5 bg-white border d-flex align-items-center gap-3 position-relative">
        <button type="button" class="social-proof-close-btn" id="socialProofCloseBtn" aria-label="Dismiss">&times;</button>
        <div class="social-proof-thumb-wrapper flex-shrink-0 position-relative">
            <img id="spTourImage" src="{{ asset('images/evening-desert-safari-dubai-hero.avif') }}" alt="Tour" class="social-proof-thumb rounded-3" width="54" height="54" loading="lazy">
            <span class="social-proof-verified-pill" title="Verified Guest Booking">
                <i class="bi bi-patch-check-fill text-success"></i>
            </span>
        </div>
        <div class="social-proof-info flex-grow-1 overflow-hidden pe-2">
            <div class="d-flex align-items-center gap-1.5 mb-0.5">
                <span class="social-proof-name fw-bold text-dark text-truncate" id="spCustomerName">Michael R.</span>
                <span class="social-proof-action text-muted small">booked</span>
            </div>
            <a href="{{ route('tours.index') }}" id="spTourLink" class="social-proof-tour-title d-block fw-semibold text-truncate text-primary text-decoration-none small">
                Premium Evening Desert Safari
            </a>
            <div class="social-proof-meta d-flex align-items-center gap-2 text-muted mt-0.5" style="font-size: 11px;">
                <span id="spTimeAgo"><i class="bi bi-clock me-1"></i>12m ago</span>
                <span>&bull;</span>
                <span class="text-success fw-semibold"><i class="bi bi-shield-check me-0.5"></i> Verified Booking</span>
            </div>
        </div>
    </div>
</div>

<style>
.social-proof-toast-wrapper {
    position: fixed;
    bottom: 24px;
    left: 24px;
    z-index: 1040;
    max-width: 360px;
    width: calc(100% - 48px);
    opacity: 0;
    transform: translateY(24px) scale(0.96);
    pointer-events: none;
    transition: transform 0.45s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.45s ease;
}

.social-proof-toast-wrapper.sp-visible {
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: auto;
}

.social-proof-card {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(0, 0, 0, 0.08) !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12), 0 2px 8px rgba(0, 0, 0, 0.04) !important;
}

.social-proof-close-btn {
    position: absolute;
    top: 6px;
    right: 8px;
    background: transparent;
    border: none;
    font-size: 18px;
    line-height: 1;
    color: #94a3b8;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 50%;
    transition: color 0.15s ease, background-color 0.15s ease;
}

.social-proof-close-btn:hover {
    color: #1e293b;
    background: #f1f5f9;
}

.social-proof-thumb-wrapper {
    width: 54px;
    height: 54px;
}

.social-proof-thumb {
    width: 54px;
    height: 54px;
    object-fit: cover;
    display: block;
    background: #f1f5f9;
}

.social-proof-verified-pill {
    position: absolute;
    bottom: -4px;
    right: -4px;
    background: #ffffff;
    border-radius: 50%;
    font-size: 14px;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}

.social-proof-name {
    font-size: 13px;
    max-width: 140px;
}

.social-proof-tour-title {
    font-size: 12.5px;
    line-height: 1.3;
    transition: color 0.15s ease;
}

.social-proof-tour-title:hover {
    color: #e47d33 !important;
    text-decoration: underline !important;
}

@media (max-width: 768px) {
    .social-proof-toast-wrapper {
        bottom: 85px;
        left: 14px;
        right: 14px;
        width: calc(100% - 28px);
        max-width: 350px;
    }
}
</style>

<script>
(function() {
    'use strict';

    // Respect user dismissal for current session
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

    // Dismiss permanently for this session
    closeBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        hideToast();
        sessionStorage.setItem('dunes_sp_dismissed', '1');
        clearTimeout(nextTimeout);
        clearTimeout(hideTimeout);
    });

    // Pause hiding when user hovers to view details
    toastEl.addEventListener('mouseenter', function() {
        isHovered = true;
        if (hideTimeout) clearTimeout(hideTimeout);
    });

    toastEl.addEventListener('mouseleave', function() {
        isHovered = false;
        if (toastEl.classList.contains('sp-visible')) {
            hideTimeout = setTimeout(hideToast, 2500);
        }
    });

    function showToast() {
        // Don't show if dismissed or page is hidden in background tab
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
        // Force reflow for smooth transition
        void toastEl.offsetWidth;
        toastEl.classList.add('sp-visible');

        hideTimeout = setTimeout(function() {
            if (!isHovered) {
                hideToast();
            }
        }, 6000);
    }

    function hideToast() {
        toastEl.classList.remove('sp-visible');
        setTimeout(function() {
            if (!toastEl.classList.contains('sp-visible')) {
                toastEl.style.display = 'none';
            }
        }, 500);

        // Schedule next appearance in 20-28 seconds
        const delay = Math.floor(Math.random() * 8000) + 20000;
        nextTimeout = setTimeout(showToast, delay);
    }

    // Handle tab visibility (pause when inactive)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearTimeout(hideTimeout);
            clearTimeout(nextTimeout);
        } else if (sessionStorage.getItem('dunes_sp_dismissed') !== '1') {
            nextTimeout = setTimeout(showToast, 8000);
        }
    });

    // Fetch social proof data after page is settled (zero LCP/CLS impact)
    window.addEventListener('load', function() {
        setTimeout(function() {
            fetch('/ajax.php?action=get_social_proof', {
                headers: { 'Accept': 'application/json' }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data && data.success && Array.isArray(data.items) && data.items.length) {
                    items = data.items;
                    // Shuffle items slightly so returning users see variety
                    items.sort(function() { return 0.5 - Math.random(); });
                    // First appearance at 7.5 seconds
                    nextTimeout = setTimeout(showToast, 7500);
                }
            })
            .catch(function(e) {
                // Fallback silently if offline
            });
        }, 1500);
    });
})();
</script>
