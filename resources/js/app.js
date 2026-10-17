/**
 * Dunes Discovery Tourism - Reactive State & UI Architecture
 * Mobile-First Alpine.js Primitives & Business Engine
 * Replaces Bootstrap JS components while preserving all core business logic.
 */

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);

window.Alpine = Alpine;

// =============================================================================
// 1. ALPINE.JS REACTIVE STORES
// =============================================================================

/**
 * 1.1 Currency Store: Multi-currency conversion (AED, USD, EUR, GBP, SAR, INR)
 */
Alpine.store('currency', {
    code: localStorage.getItem('dunes_currency') || 'AED',
    rates: {
        'AED': 1.0,
        'USD': 0.2723,
        'EUR': 0.2510,
        'GBP': 0.2150,
        'SAR': 1.0210,
        'INR': 22.85
    },
    symbols: {
        'AED': 'AED',
        'USD': '$',
        'EUR': '€',
        'GBP': '£',
        'SAR': 'SAR',
        'INR': '₹'
    },
    flags: {
        'AED': '\uD83C\uDDE6\uD83C\uDDEA',
        'USD': '\uD83C\uDDFA\uD83C\uDDF8',
        'EUR': '\uD83C\uDDEA\uD83C\uDDFA',
        'GBP': '\uD83C\uDDEC\uD83C\uDDE7',
        'SAR': '\uD83C\uDDF8\uD83C\uDDE6',
        'INR': '\uD83C\uDDEE\uD83C\uDDF3'
    },

    init() {
        if (window.DunesRates && typeof window.DunesRates === 'object') {
            this.rates = Object.assign({}, this.rates, window.DunesRates);
        }
        document.querySelectorAll('.currency-option[data-currency][data-rate]').forEach(btn => {
            const c = btn.dataset.currency;
            const r = parseFloat(btn.dataset.rate);
            if (c && !isNaN(r) && r > 0) {
                this.rates[c] = r;
            }
        });
        const saved = localStorage.getItem('dunes_currency') || 'AED';
        this.setCurrency(saved, false);
    },

    set(code) {
        this.setCurrency(code, true);
    },

    format(aed) {
        return this.formatDisplayPrice(aed);
    },

    setCurrency(code, showToast = false) {
        if (!this.rates[code]) return;
        this.code = code;
        localStorage.setItem('dunes_currency', code);
        try {
            document.cookie = `dunes_currency=${encodeURIComponent(code)};path=/;max-age=31536000;SameSite=Lax`;
        } catch (e) {}

        const flag = this.flags[code] || '';
        const symbol = this.symbols[code] || code;

        // Update UI text indicators
        document.querySelectorAll('.current-currency-flag').forEach(el => el.textContent = flag);
        document.querySelectorAll('.current-currency-code').forEach(el => el.textContent = code);

        // Update dropdown option states
        document.querySelectorAll('.currency-option').forEach(btn => {
            const isMatch = btn.dataset.currency === code;
            btn.classList.toggle('active', isMatch);
            const check = btn.querySelector('.checkmark');
            if (check) {
                check.classList.toggle('d-none', !isMatch);
                check.classList.toggle('hidden', !isMatch);
            }
        });

        // Re-evaluate all [data-aed] price elements across the portal
        this.convertAllPrices(code);

        // Re-calculate booking modal totals if present
        if (window.App && typeof window.App.updateTotal === 'function') {
            window.App.updateTotal();
        }

        if (showToast && window.App && typeof window.App.toast === 'function') {
            window.App.toast(`Display currency set to ${code} (${symbol})`, 'success');
        }

        try {
            window.dispatchEvent(new CustomEvent('currencyChanged', { detail: { currency: code, symbol: symbol } }));
        } catch (e) {}
    },

    formatPriceHtml(aedAmount, isOldPrice = false, showAedSuffix = true, isAddon = false) {
        const aed = parseFloat(aedAmount) || 0;
        const code = this.code || 'AED';
        const prefix = isAddon ? '+' : '';

        if (code === 'AED') {
            const formatted = Number(aed).toLocaleString('en-US', {
                minimumFractionDigits: (aed % 1 !== 0) ? 2 : 0,
                maximumFractionDigits: 2
            });
            return `${prefix}AED ${formatted}`;
        }

        const rate = this.rates[code] || 1;
        const symbol = this.symbols[code] || code;
        const converted = aed * rate;

        let formattedForeign;
        if (code === 'INR') {
            formattedForeign = `${prefix}${symbol}${Math.round(converted).toLocaleString('en-US')}`;
        } else if (code === 'SAR') {
            formattedForeign = `${prefix}${Math.round(converted).toLocaleString('en-US')} SAR`;
        } else {
            if (converted >= 10) {
                formattedForeign = `${prefix}${symbol}${Math.round(converted).toLocaleString('en-US')}`;
            } else {
                formattedForeign = `${prefix}${symbol}${Number(converted).toFixed(2)}`;
            }
        }

        if (isOldPrice) {
            return formattedForeign;
        }

        if (showAedSuffix) {
            const baseStr = Number(aed).toLocaleString('en-US', {
                minimumFractionDigits: (aed % 1 !== 0) ? 2 : 0,
                maximumFractionDigits: 2
            });
            return `${formattedForeign} <small class="text-muted fw-normal" style="font-size: 0.75em; letter-spacing: 0;">(${prefix}AED ${baseStr})</small>`;
        }

        return formattedForeign;
    },

    convertAllPrices(code) {
        const targetCode = code || this.code || 'AED';

        // 1. Update elements with explicit [data-aed]
        document.querySelectorAll('[data-aed]').forEach(el => {
            const aed = parseFloat(el.dataset.aed);
            if (isNaN(aed) || aed <= 0) return;

            const isOld = el.classList.contains('text-decoration-line-through') || 
                          el.classList.contains('old') || 
                          el.classList.contains('rc-old-price');
            const isAddon = el.dataset.isAddon === 'true' || el.textContent.trim().startsWith('+');
            const noSub = el.dataset.noSub === 'true' || isOld;

            el.innerHTML = this.formatPriceHtml(aed, isOld, !noSub, isAddon);
        });

        // 2. Auto-scan elements with price classes that might not yet have data-aed initialized
        const priceSelectors = [
            '.rc-cur-price', '.rc-old-price', '.tier-card-price .current', '.tier-card-price .old',
            '.package-option .text-primary', '.package-option .text-secondary'
        ];
        document.querySelectorAll(priceSelectors.join(',')).forEach(el => {
            if (!el.dataset.aed) {
                const txt = el.textContent.trim();
                const match = txt.match(/(?:AED|Dhs|د\.إ)\s*([0-9,]+(?:\.[0-9]{1,2})?)/i) || (el.dataset.price ? [null, el.dataset.price] : null);
                if (match) {
                    const val = parseFloat(String(match[1]).replace(/,/g, ''));
                    if (!isNaN(val) && val > 0) {
                        el.dataset.aed = val;
                        const isOld = el.classList.contains('text-decoration-line-through') || el.classList.contains('old') || el.classList.contains('rc-old-price');
                        el.innerHTML = this.formatPriceHtml(val, isOld, !isOld);
                    }
                }
            }
        });

        // 3. Update modal totals if available
        if (window.App && typeof window.App.updateTotal === 'function') {
            window.App.updateTotal();
        }
    },

    formatDisplayPrice(aedAmount) {
        const aed = parseFloat(aedAmount) || 0;
        const code = this.code || 'AED';
        const formattedAED = 'AED ' + Number(aed).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (code === 'AED') {
            return formattedAED;
        }

        const rate = this.rates[code] || 1;
        const converted = aed * rate;
        const symbol = this.symbols[code] || code;
        let formattedForeign;
        if (code === 'INR' || code === 'SAR') {
            formattedForeign = `${symbol} ${Number(converted).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        } else {
            formattedForeign = `${symbol}${Number(converted).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        }

        return `${formattedForeign} <span class="text-muted fw-normal small" style="font-size: 0.85em;">(~ ${formattedAED})</span>`;
    }
});

let _bodyScrollPos = 0;
let _bodyScrollLocked = false;
function lockBodyScroll() {
    if (_bodyScrollLocked) return;
    _bodyScrollPos = window.scrollY || window.pageYOffset || 0;
    document.body.style.position = 'fixed';
    document.body.style.top = `-${_bodyScrollPos}px`;
    document.body.style.width = '100%';
    document.body.style.overflow = 'hidden';
    document.body.classList.add('modal-open');
    _bodyScrollLocked = true;
}
function unlockBodyScroll() {
    if (!_bodyScrollLocked) return;
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.width = '';
    document.body.style.overflow = '';
    document.body.classList.remove('modal-open');
    window.scrollTo(0, _bodyScrollPos);
    _bodyScrollLocked = false;
}
window.lockBodyScroll = lockBodyScroll;
window.unlockBodyScroll = unlockBodyScroll;

/**
 * 1.2 Mobile Navigation Store
 */
Alpine.store('mobileNav', {
    open: false,
    toggle() {
        this.open = !this.open;
        if (this.open) {
            lockBodyScroll();
        } else {
            unlockBodyScroll();
        }
        const sheet = document.getElementById('mobileSheet') || document.getElementById('mainOffcanvas');
        if (sheet) sheet.classList.toggle('active', this.open);
    },
    close() {
        this.open = false;
        unlockBodyScroll();
        const sheet = document.getElementById('mobileSheet') || document.getElementById('mainOffcanvas');
        if (sheet) sheet.classList.remove('active');
    }
});

/**
 * 1.3 Reactive Modal & Drawer Store
 * Replaces Bootstrap Modal and Offcanvas with pure DOM & Alpine control.
 */
Alpine.store('modal', {
    active: null,
    data: {},
    backdropEl: null,

    getModalElement(id) {
        if (!id) return null;
        const map = {
            'booking': 'bookingModal',
            'safari-matcher': 'safariMatcherModal',
            'search': 'globalSearchModal',
            'welcome-offer': 'welcomeOfferModal',
            'exit-intent': 'exitIntentModal',
            'custom-safari': 'customSafariModal',
            'compare': 'compareDrawer',
            'whatsapp': 'whatsappModal',
            'legal': 'legalModal'
        };
        const elId = map[id] || (id.startsWith('#') ? id.slice(1) : id);
        return document.getElementById(elId);
    },

    open(id, data = {}) {
        if (id === 'whatsapp') {
            const formEnabled = (
                typeof window.WHATSAPP_FORM_ENABLED === 'undefined' ||
                window.WHATSAPP_FORM_ENABLED === '1' ||
                window.WHATSAPP_FORM_ENABLED === 1 ||
                window.WHATSAPP_FORM_ENABLED === true ||
                window.WHATSAPP_FORM_ENABLED === 'true' ||
                window.WHATSAPP_FORM_ENABLED === 'yes'
            );
            if (!formEnabled && window.App && typeof window.App.openWhatsApp === 'function') {
                window.App.openWhatsApp(data?.tourName || '', data?.href || '');
                return;
            }
        }

        const el = this.getModalElement(id);
        if (!el) {
            console.warn(`[Alpine.modal] Element for "${id}" not found.`);
            return;
        }

        // Close any other active modal first
        if (this.active && this.active !== id) {
            this.close(false);
        }

        this.active = id;
        this.data = data;
        lockBodyScroll();

        const isOffcanvas = el.classList.contains('offcanvas');

        // Cleanup any orphaned backdrops from previous modals
        this.removeBackdrops();

        // Display Element
        if (isOffcanvas) {
            el.classList.add('show', 'active');
            el.style.visibility = 'visible';
            el.removeAttribute('aria-hidden');
            el.setAttribute('aria-modal', 'true');
            if (id === 'compare' && Alpine.store('compare')) {
                Alpine.store('compare').renderDrawer();
            }
        } else {
            el.style.display = 'block';
            el.removeAttribute('aria-hidden');
            el.setAttribute('aria-modal', 'true');
            void el.offsetWidth; // Force CSS reflow for smooth fade transition
            el.classList.add('show', 'active');

            // Modal-specific state initializers
            if (id === 'booking' && window.App) {
                window.App.currentStep = 1;
                window.App.initPhoneInputs?.();

                const tourSel = document.getElementById('bookingTour');
                const titleEl = document.getElementById('bookingModalTitle');
                const wrapper = document.getElementById('tourSelectWrapper');

                if (data.tourId) {
                    window.App.preselectedTourId = String(data.tourId);
                    window.App.preselectedTierId = data.tierId ? String(data.tierId) : null;
                    if (tourSel) {
                        tourSel.value = String(data.tourId);
                        window.App.loadTiers?.(String(data.tourId));
                        if (titleEl && tourSel.selectedIndex >= 0 && tourSel.options[tourSel.selectedIndex]) {
                            titleEl.textContent = tourSel.options[tourSel.selectedIndex].text;
                        }
                    }
                    if (wrapper) wrapper.classList.add('hidden', 'd-none');
                } else {
                    window.App.preselectedTourId = null;
                    window.App.preselectedTierId = null;
                    if (titleEl) titleEl.textContent = 'Book Your Adventure';
                    if (wrapper) wrapper.classList.remove('hidden', 'd-none');
                }

                if (data.adults) {
                    const adultsInput = document.getElementById('bookingAdults');
                    if (adultsInput) adultsInput.value = data.adults;
                }
                window.App.syncTourGuestRules?.(data.tourId || (tourSel ? tourSel.value : null));
                if (data.children !== undefined) {
                    const childrenInput = document.getElementById('bookingChildren');
                    if (childrenInput) childrenInput.value = data.children;
                }
                if (data.infants !== undefined) {
                    const infantsInput = document.getElementById('bookingInfants');
                    if (infantsInput) infantsInput.value = data.infants;
                }
                if (data.date) {
                    const dateInput = document.getElementById('bookingDate');
                    if (dateInput) dateInput.value = data.date;
                }

                if (data.promo || data.coupon) {
                    const promoCode = String(data.promo || data.coupon).trim().toUpperCase();
                    window.pendingPromoCode = promoCode;
                    const promoInput = document.getElementById('bookingPromoInput');
                    const promoCodeEl = document.getElementById('bookingPromoCode');
                    if (promoInput) promoInput.value = promoCode;
                    if (promoCodeEl) promoCodeEl.value = promoCode;
                    setTimeout(() => {
                        if (typeof window.validateCurrentPromo === 'function') {
                            window.validateCurrentPromo();
                        }
                    }, 350);
                }

                window.App.updateStep?.();
            }

            if (id === 'whatsapp' && window.App) {
                window.App.initPhoneInputs?.();
            }

            if (id === 'search') {
                setTimeout(() => {
                    document.getElementById('globalSearchModalInput')?.focus();
                }, 80);
            }
        }

        // Trigger backward-compatible event triggers
        el.dispatchEvent(new CustomEvent(isOffcanvas ? 'shown.bs.offcanvas' : 'shown.bs.modal', { bubbles: true }));
        window.dispatchEvent(new CustomEvent('modal-opened', { detail: { id, data } }));
    },

    close(resetActive = true) {
        if (!this.active) return;
        const closingId = this.active;
        const el = this.getModalElement(closingId);

        if (el) {
            const isOffcanvas = el.classList.contains('offcanvas');
            el.classList.remove('show', 'active');
            if (isOffcanvas) {
                el.style.visibility = '';
                el.setAttribute('aria-hidden', 'true');
                el.removeAttribute('aria-modal');
                el.dispatchEvent(new CustomEvent('hidden.bs.offcanvas', { bubbles: true }));
            } else {
                el.style.display = 'none';
                el.setAttribute('aria-hidden', 'true');
                el.removeAttribute('aria-modal');
                el.dispatchEvent(new CustomEvent('hidden.bs.modal', { bubbles: true }));
            }

            if (closingId === 'booking' && window.App) {
                window.App.resetForm?.();
                window.App.preselectedTierId = null;
                window.App.preselectedTourId = null;
            }
        }

        this.removeBackdrops();

        if (resetActive) {
            this.active = null;
            this.data = {};
            unlockBodyScroll();
        }
        window.dispatchEvent(new CustomEvent('modal-closed', { detail: { id: closingId } }));
    },

    ensureBackdrop(isOffcanvas = false) {
        this.removeBackdrops();
        const backdrop = document.createElement('div');
        backdrop.className = isOffcanvas ? 'offcanvas-backdrop fade' : 'modal-backdrop fade';
        document.body.appendChild(backdrop);
        void backdrop.offsetWidth;
        backdrop.classList.add('show');
        this.backdropEl = backdrop;

        backdrop.addEventListener('click', () => {
            const currentEl = this.getModalElement(this.active);
            if (currentEl && currentEl.dataset.bsBackdrop === 'static') {
                currentEl.classList.add('modal-static');
                setTimeout(() => currentEl.classList.remove('modal-static'), 300);
                return;
            }
            this.close();
        });
    },

    removeBackdrops() {
        document.querySelectorAll('.modal-backdrop, .offcanvas-backdrop').forEach(b => b.remove());
        this.backdropEl = null;
    }
});

/**
 * 1.4 Comparison Store: Side-by-side tour analysis
 */
Alpine.store('compare', {
    items: JSON.parse(localStorage.getItem('dunes_compare') || '[]'),
    
    init() {
        // Synchronize with legacy dunes_compare_ids if present
        const idsStored = localStorage.getItem('dunes_compare_ids');
        if (idsStored && (!this.items || this.items.length === 0)) {
            try {
                const ids = JSON.parse(idsStored);
                this.items = ids;
            } catch(e) {}
        }
        this.syncUI();
    },

    getAllTours() {
        try {
            const raw = document.getElementById('dunesCompareTourData')?.textContent;
            if (raw) return JSON.parse(raw);
        } catch(e) {}
        return [];
    },

    getTourData(tourId) {
        const tours = this.getAllTours();
        return tours.find(t => String(t.id) === String(tourId)) || null;
    },

    getId(item) {
        if (!item) return '';
        return String(typeof item === 'object' ? item.id : item);
    },

    add(tour) {
        const id = this.getId(tour);
        if (!id) return;

        const exists = this.items.some(item => this.getId(item) === id);
        if (exists) return;

        if (this.items.length >= 3) {
            if (window.App && typeof window.App.toast === 'function') {
                window.App.toast('You can compare up to 3 safaris at a time. Please remove one first.', 'warning');
            }
            return;
        }

        const tourData = this.getTourData(id);
        this.items.push(tourData || { id: id });
        this.save();

        const tourName = tourData ? tourData.name : 'Tour';
        if (window.App && typeof window.App.toast === 'function') {
            window.App.toast(`Added "${tourName}" to comparison`, 'success');
        }
    },

    remove(id) {
        const targetId = String(id);
        this.items = this.items.filter(item => this.getId(item) !== targetId);
        this.save();
        this.renderDrawer();
    },

    toggle(btnOrId) {
        let id;
        if (btnOrId && typeof btnOrId === 'object' && btnOrId.nodeType) {
            id = btnOrId.dataset.tourId || btnOrId.dataset.tour || '';
        } else {
            id = String(btnOrId);
        }
        if (!id) return;

        const exists = this.items.some(item => this.getId(item) === id);
        if (exists) {
            this.remove(id);
        } else {
            this.add(id);
        }
    },

    clear() {
        this.items = [];
        this.save();
        this.renderDrawer();
    },

    loadBestsellers() {
        const all = this.getAllTours();
        const bestsellers = all.filter(t => t.is_bestseller).slice(0, 3);
        if (bestsellers.length > 0) {
            this.items = bestsellers;
        } else {
            this.items = all.slice(0, 3);
        }
        this.save();
        this.renderDrawer();
    },

    save() {
        try {
            localStorage.setItem('dunes_compare', JSON.stringify(this.items));
            const ids = this.items.map(item => this.getId(item));
            localStorage.setItem('dunes_compare_ids', JSON.stringify(ids));
        } catch(e) {}
        this.syncUI();
    },

    syncUI() {
        const count = this.items.length;
        const bar = document.getElementById('compareFloatingBar');
        const countLabel = document.getElementById('compareCountLabel');
        const bubbles = document.getElementById('compareThumbBubbles');

        if (countLabel) countLabel.textContent = count;

        if (bubbles) {
            bubbles.innerHTML = '';
            this.items.forEach(item => {
                const id = this.getId(item);
                const t = this.getTourData(id);
                if (t) {
                    const img = document.createElement('img');
                    img.src = t.thumb;
                    img.alt = t.name;
                    img.className = 'rounded-circle border border-warning';
                    img.style.width = '30px';
                    img.style.height = '30px';
                    img.style.objectFit = 'cover';
                    bubbles.appendChild(img);
                }
            });
        }

        if (bar) {
            if (count > 0) {
                bar.classList.remove('d-none');
            } else {
                bar.classList.add('d-none');
            }
        }

        // Synchronize page buttons
        const currentIds = this.items.map(i => this.getId(i));
        document.querySelectorAll('.btn-toggle-compare').forEach(btn => {
            const tid = String(btn.dataset.tourId);
            const textSpan = btn.querySelector('.compare-btn-text');
            if (currentIds.includes(tid)) {
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-warning', 'text-dark', 'fw-bold');
                if (textSpan) textSpan.textContent = 'Added';
            } else {
                btn.classList.add('btn-outline-secondary');
                btn.classList.remove('btn-warning', 'text-dark', 'fw-bold');
                if (textSpan) textSpan.textContent = 'Compare';
            }
        });
    },

    renderDrawer() {
        const emptyState = document.getElementById('compareEmptyState');
        const tableWrap = document.getElementById('compareTableWrapper');

        if (this.items.length === 0) {
            if (emptyState) emptyState.classList.remove('d-none');
            if (tableWrap) tableWrap.classList.add('d-none');
            return;
        }

        if (emptyState) emptyState.classList.add('d-none');
        if (tableWrap) tableWrap.classList.remove('d-none');

        const allTours = this.getAllTours();
        const selectedTours = this.items.map(i => allTours.find(x => String(x.id) === this.getId(i))).filter(Boolean);

        const rowHeader = document.getElementById('compareRowHeader');
        const rowPrice = document.getElementById('compareRowPrice');
        const rowDuration = document.getElementById('compareRowDuration');
        const rowVehicle = document.getElementById('compareRowVehicle');
        const rowDuneBashing = document.getElementById('compareRowDuneBashing');
        const rowDining = document.getElementById('compareRowDining');
        const rowShows = document.getElementById('compareRowShows');
        const rowInclusions = document.getElementById('compareRowInclusions');
        const rowCancellation = document.getElementById('compareRowCancellation');
        const rowAction = document.getElementById('compareRowAction');

        // Reset dynamic columns
        [rowHeader, rowPrice, rowDuration, rowVehicle, rowDuneBashing, rowDining, rowShows, rowInclusions, rowCancellation, rowAction].forEach(row => {
            if (!row) return;
            while (row.children.length > 1) {
                row.removeChild(row.lastChild);
            }
        });

        selectedTours.forEach(tour => {
            if (rowHeader) {
                const th = document.createElement('th');
                th.style.width = `${Math.floor(100 / (selectedTours.length + 1))}%`;
                th.style.verticalAlign = 'top';
                th.className = 'pb-3';
                th.innerHTML = `
                    <div class="position-relative mb-2 rounded-3 overflow-hidden" style="height: 120px;">
                        <img src="${tour.thumb}" alt="${tour.name}" style="width: 100%; height: 100%; object-fit: cover;">
                        <button type="button" class="btn btn-sm btn-dark rounded-circle position-absolute top-0 end-0 m-1 p-0 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;" onclick="Alpine.store('compare').remove('${tour.id}')" title="Remove from comparison">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <h6 class="fw-bold text-white mb-1 line-clamp-1">${tour.name}</h6>
                    <small class="text-warning"><i class="bi bi-star-fill me-1"></i>${tour.rating} (480+ Reviews)</small>
                `;
                rowHeader.appendChild(th);
            }

            if (rowPrice) {
                const tdPrice = document.createElement('td');
                tdPrice.className = 'py-3';
                tdPrice.innerHTML = `<span class="h5 fw-bold text-warning" data-aed="${tour.min_price}">AED ${Math.round(tour.min_price)}</span> <small class="text-white-50">/ person</small>`;
                rowPrice.appendChild(tdPrice);
            }

            if (rowDuration) {
                const td = document.createElement('td');
                td.className = 'py-3 text-light small';
                td.textContent = tour.duration;
                rowDuration.appendChild(td);
            }

            if (rowVehicle) {
                const td = document.createElement('td');
                td.className = 'py-3 text-light small';
                td.textContent = tour.vehicle;
                rowVehicle.appendChild(td);
            }

            if (rowDuneBashing) {
                const td = document.createElement('td');
                td.className = 'py-3 text-light small';
                td.textContent = tour.dune_bashing;
                rowDuneBashing.appendChild(td);
            }

            if (rowDining) {
                const td = document.createElement('td');
                td.className = 'py-3 text-light small';
                td.textContent = tour.dining;
                rowDining.appendChild(td);
            }

            if (rowShows) {
                const td = document.createElement('td');
                td.className = 'py-3 text-light small';
                td.textContent = tour.shows;
                rowShows.appendChild(td);
            }

            if (rowInclusions) {
                const td = document.createElement('td');
                td.className = 'py-3 text-light small';
                td.textContent = tour.inclusions;
                rowInclusions.appendChild(td);
            }

            if (rowCancellation) {
                const td = document.createElement('td');
                td.className = 'py-3 text-success small fw-bold';
                td.innerHTML = '<i class="bi bi-check2-circle me-1"></i>100% Free Refund up to 24h prior';
                rowCancellation.appendChild(td);
            }

            if (rowAction) {
                const td = document.createElement('td');
                td.className = 'py-3';
                td.innerHTML = `
                    <button type="button" class="btn btn-desert-animated w-100 rounded-pill py-2 fw-bold text-nowrap small shadow-sm" onclick="Alpine.store('modal').close(); Alpine.store('modal').open('booking', { tourId: '${tour.id}' });">
                        <i class="bi bi-calendar-check me-1"></i> Book This Tour
                    </button>
                `;
                rowAction.appendChild(td);
            }
        });

        // Convert freshly injected prices to currently active currency
        if (Alpine.store('currency')) {
            Alpine.store('currency').convertAllPrices();
        }
    }
});


// =============================================================================
// 2. GLOBAL MODAL & TRIGGER EVENT HANDLERS
// =============================================================================

document.addEventListener('click', (e) => {
    // 2.1 [data-action="open-booking"] Trigger
    const bookingBtn = e.target.closest('[data-action="open-booking"]');
    if (bookingBtn) {
        e.preventDefault();
        const tourId = bookingBtn.dataset.tour || bookingBtn.dataset.tourId || null;
        const tierId = bookingBtn.dataset.tier || bookingBtn.dataset.tierId || null;
        Alpine.store('modal').open('booking', { tourId, tierId });
        return;
    }

    // 2.2 Global [data-bs-toggle="modal"], [data-bs-target], [href^="#"] Triggers
    const modalTrigger = e.target.closest('[data-bs-toggle="modal"], [data-bs-toggle="offcanvas"], [data-bs-target]');
    if (modalTrigger) {
        const target = modalTrigger.dataset.bsTarget || modalTrigger.getAttribute('data-bs-target') || modalTrigger.getAttribute('href');
        if (target) {
            if (target === '#bookingModal') {
                e.preventDefault();
                Alpine.store('modal').open('booking', {
                    tourId: modalTrigger.dataset.tour || modalTrigger.dataset.tourId,
                    tierId: modalTrigger.dataset.tier || modalTrigger.dataset.tierId
                });
                return;
            }
            if (target === '#safariMatcherModal') {
                e.preventDefault();
                Alpine.store('modal').open('safari-matcher');
                return;
            }
            if (target === '#globalSearchModal') {
                e.preventDefault();
                Alpine.store('modal').open('search');
                return;
            }
            if (target === '#welcomeOfferModal') {
                e.preventDefault();
                Alpine.store('modal').open('welcome-offer');
                return;
            }
            if (target === '#customSafariModal') {
                e.preventDefault();
                Alpine.store('modal').open('custom-safari');
                return;
            }
            if (target === '#compareDrawer') {
                e.preventDefault();
                Alpine.store('modal').open('compare');
                return;
            }
            if (target === '#whatsappModal') {
                e.preventDefault();
                this.openWhatsApp(trigger.dataset?.tourName || '', '');
                return;
            }
            if (target === '#legalModal') {
                e.preventDefault();
                Alpine.store('modal').open('legal');
                return;
            }
        }
    }

    // 2.3 Close / Dismiss Buttons
    const dismissBtn = e.target.closest('[data-bs-dismiss="modal"], [data-bs-dismiss="offcanvas"], .btn-close-modal');
    if (dismissBtn) {
        e.preventDefault();
        Alpine.store('modal').close();
        return;
    }

    // 2.4 Currency switcher option click
    const curBtn = e.target.closest('.currency-option');
    if (curBtn) {
        e.preventDefault();
        const cur = curBtn.dataset.currency;
        if (cur) Alpine.store('currency').setCurrency(cur, true);
        return;
    }

    // 2.5 Compare toggle buttons on tour cards
    const compareBtn = e.target.closest('.btn-toggle-compare, [data-action="compare-toggle"]');
    if (compareBtn) {
        e.preventDefault();
        Alpine.store('compare').toggle(compareBtn);
        return;
    }

    // 2.6 Mobile Navigation toggles
    if (e.target.closest('#menuToggle')) {
        e.preventDefault();
        Alpine.store('mobileNav').toggle();
        return;
    }
    if (e.target.closest('#sheetClose, #sheetOverlay')) {
        e.preventDefault();
        Alpine.store('mobileNav').close();
        return;
    }
    const sheetLink = e.target.closest('#mobileSheet a');
    if (sheetLink && !sheetLink.classList.contains('nav-review-circle') && !sheetLink.closest('.nav-review-circle')) {
        Alpine.store('mobileNav').close();
    }
});

// Listen for Escape key to close active modals or drawer
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        Alpine.store('modal').close();
        Alpine.store('mobileNav').close();
        const popover = document.querySelector('.global-popover-overlay');
        if (popover) popover.remove();
    }
});


// =============================================================================
// 3. CORE BUSINESS & OPERATOR ENGINE (window.App / DunesApp)
// =============================================================================

const App = {
    currentStep: 1,
    selectedTier: null,
    selectedPrice: 0,
    selectedAddons: [],
    preselectedTierId: null,
    preselectedTourId: null,

    init() {
        this.initUTM();
        this.initHeader();
        this.initMobile();
        this.initModal();
        this.initBooking();
        this.initPaymentOptions();
        this.initForms();
        this.initQty();
        this.initFAQ();
        this.initTourSidebar();
        this.initHorizontalTabs();
        this.initWhatsApp();
        this.initTracking();
        this.initDateCards();
        this.initEmailVerification();
        this.initLegalModal();
        this.initSafariMatcher();
        this.initSunsetWidget();
        this.initCurrency();
        this.initPhoneInputs();
    },

    // Backward-compatibility references to Alpine currency store
    get currency() {
        return Alpine.store('currency');
    },

    initCurrency() {
        Alpine.store('currency').init();
    },

    setCurrency(code, showToast = false) {
        Alpine.store('currency').setCurrency(code, showToast);
    },

    formatPriceHtml(aed, isOld, showSuffix, isAddon) {
        return Alpine.store('currency').formatPriceHtml(aed, isOld, showSuffix, isAddon);
    },

    convertAllPrices(code) {
        Alpine.store('currency').convertAllPrices(code);
    },

    formatDisplayPrice(amt) {
        return Alpine.store('currency').formatDisplayPrice(amt);
    },

    toast(msg, type = 'success') {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        const icons = {
            success: '<i class="bi bi-check-circle-fill"></i>',
            error: '<i class="bi bi-x-circle-fill"></i>',
            warning: '<i class="bi bi-exclamation-triangle-fill"></i>',
            info: '<i class="bi bi-info-circle-fill"></i>'
        };

        const titles = {
            success: 'Success',
            error: 'Action Failed',
            warning: 'Notice',
            info: 'Information'
        };

        const t = document.createElement('div');
        t.className = 'toast ' + (type || 'info');
        t.setAttribute('role', 'alert');
        t.setAttribute('aria-live', 'assertive');
        t.innerHTML = `
            <div class="toast-icon">${icons[type] || icons.info}</div>
            <div class="toast-content">
                <div class="toast-title">${titles[type] || 'Notice'}</div>
                <p class="toast-message">${msg}</p>
            </div>
            <button type="button" class="toast-close" aria-label="Close notification"><i class="bi bi-x-lg"></i></button>
        `;

        const dismiss = () => {
            if (t._dismissed) return;
            t._dismissed = true;
            t.classList.add('toast-exit');
            setTimeout(() => {
                if (t.parentNode) t.remove();
            }, 260);
        };

        const closeBtn = t.querySelector('.toast-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dismiss();
            });
        }

        container.appendChild(t);

        let timer = setTimeout(dismiss, 5000);

        t.addEventListener('mouseenter', () => clearTimeout(timer));
        t.addEventListener('mouseleave', () => {
            timer = setTimeout(dismiss, 2500);
        });
    },

    initLegalModal() {
        const modal = document.getElementById('legalModal');
        if (!modal) return;

        const title = document.getElementById('legalModalTitle');
        const content = document.getElementById('legalModalContent');
        const loader = document.getElementById('legalModalLoader');
        const checkbox = document.getElementById('legalModalAgree');
        let currentTriggerCheckbox = null;

        document.addEventListener('click', (e) => {
            if (e.target.matches('.legal-link')) {
                e.preventDefault();
                const type = e.target.dataset.type;
                const pageTitle = type === 'terms-condition' ? 'Terms & Conditions' : 'Privacy Policy';

                const form = e.target.closest('form');
                if (form) {
                    currentTriggerCheckbox = form.querySelector('input[type="checkbox"][required]');
                }

                if (title) title.textContent = pageTitle;
                if (content) content.innerHTML = '';
                if (loader) loader.classList.remove('d-none');
                if (checkbox) checkbox.checked = currentTriggerCheckbox ? currentTriggerCheckbox.checked : false;

                Alpine.store('modal').open('legal');

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.CSRF_TOKEN || '';
                fetch('/ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: `action=get_legal_content&type=${type}&_token=${encodeURIComponent(csrfToken)}`
                })
                .then(r => r.text())
                .then(t => { try { return JSON.parse(t.replace(/^\uFEFF+/, '').trim()); } catch(e){ throw e; } })
                .then(data => {
                    if (loader) loader.classList.add('d-none');
                    if (content) {
                        content.innerHTML = data.success ? data.html : '<p class="text-danger text-center">Failed to load content.</p>';
                    }
                })
                .catch(() => {
                    if (loader) loader.classList.add('d-none');
                    if (content) content.innerHTML = '<p class="text-danger text-center">Network error.</p>';
                });
            }
        });

        if (checkbox) {
            checkbox.addEventListener('change', () => {
                if (currentTriggerCheckbox) {
                    currentTriggerCheckbox.checked = checkbox.checked;
                    currentTriggerCheckbox.dispatchEvent(new Event('change'));
                    if (checkbox.checked) {
                        setTimeout(() => Alpine.store('modal').close(), 300);
                    }
                }
            });
        }
    },

    _otpTimerInterval: null,
    _pendingSubmitAfterOtp: null,

    initEmailVerification() {
        if (!window.DunesConfig?.email_verification_required) {
            return;
        }
        const input = document.getElementById('bookingEmail');
        if (!input || input.dataset.otpVerifyBound === 'true') return;
        if (input.closest('form[action*="login"], form[action*="password"], form[action*="register"], .auth-card, #loginForm, .admin-portal')) return;
        input.dataset.otpVerifyBound = 'true';

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const sendOtpBtn = document.getElementById('sendEmailOtpBtn');
        const verifyOtpBtn = document.getElementById('verifyOtpBtn');
        const resendOtpBtn = document.getElementById('resendOtpBtn');
        const digitBoxes = Array.from(document.querySelectorAll('#otpDigitsContainer .otp-digit-box'));
        const masterOtpInput = document.getElementById('bookingOtpCode');

        if (sendOtpBtn) {
            sendOtpBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.startVerification(input);
            });
        }

        if (verifyOtpBtn) {
            verifyOtpBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.verifyOtpCode(input);
            });
        }

        if (resendOtpBtn) {
            resendOtpBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (!resendOtpBtn.disabled) {
                    this.startVerification(input);
                }
            });
        }

        // Bind interactive 6-digit OTP boxes (auto-advance, backspace, arrows, paste, auto-verify)
        const syncMasterOtp = () => {
            const code = digitBoxes.map(b => (b.value || '').replace(/\D/g, '')).join('');
            if (masterOtpInput) masterOtpInput.value = code;
            return code;
        };

        digitBoxes.forEach((box, idx) => {
            box.addEventListener('focus', () => {
                box.select();
            });

            box.addEventListener('keydown', (e) => {
                const errEl = document.getElementById('otpErrorMsg');
                if (errEl) {
                    errEl.classList.add('hidden');
                    errEl.classList.remove('flex');
                }

                if (e.key === 'Backspace') {
                    if (box.value) {
                        box.value = '';
                        syncMasterOtp();
                        e.preventDefault();
                    } else if (idx > 0) {
                        digitBoxes[idx - 1].value = '';
                        digitBoxes[idx - 1].focus();
                        syncMasterOtp();
                        e.preventDefault();
                    }
                    return;
                }

                if (e.key === 'ArrowLeft' && idx > 0) {
                    e.preventDefault();
                    digitBoxes[idx - 1].focus();
                    return;
                }

                if (e.key === 'ArrowRight' && idx < digitBoxes.length - 1) {
                    e.preventDefault();
                    digitBoxes[idx + 1].focus();
                    return;
                }

                if (e.key === 'Enter') {
                    e.preventDefault();
                    const code = syncMasterOtp();
                    if (code.length === 6) {
                        this.verifyOtpCode(input);
                    }
                    return;
                }

                // Allow Ctrl/Cmd + V, Tab, Delete
                if (e.ctrlKey || e.metaKey || ['Tab', 'Delete', 'Home', 'End'].includes(e.key)) {
                    return;
                }

                if (/^[0-9]$/.test(e.key)) {
                    e.preventDefault();
                    box.value = e.key;
                    const code = syncMasterOtp();
                    if (idx < digitBoxes.length - 1) {
                        digitBoxes[idx + 1].focus();
                    }
                    if (code.length === 6) {
                        this.verifyOtpCode(input);
                    }
                } else {
                    e.preventDefault();
                }
            });

            box.addEventListener('input', () => {
                const cleaned = (box.value || '').replace(/\D/g, '');
                if (cleaned.length > 1) {
                    // Mobile autofill or multi-char input into single box
                    cleaned.slice(0, 6).split('').forEach((ch, offset) => {
                        if (digitBoxes[idx + offset]) {
                            digitBoxes[idx + offset].value = ch;
                        }
                    });
                    const targetIdx = Math.min(idx + cleaned.length, digitBoxes.length - 1);
                    digitBoxes[targetIdx]?.focus();
                } else {
                    box.value = cleaned;
                    if (cleaned && idx < digitBoxes.length - 1) {
                        digitBoxes[idx + 1].focus();
                    }
                }
                const code = syncMasterOtp();
                if (code.length === 6) {
                    this.verifyOtpCode(input);
                }
            });

            box.addEventListener('paste', (e) => {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData)?.getData('text') || '';
                const digits = pasted.replace(/\D/g, '').slice(0, 6);
                if (!digits) return;

                digits.split('').forEach((d, i) => {
                    if (digitBoxes[i]) digitBoxes[i].value = d;
                });
                const nextFocus = Math.min(digits.length, digitBoxes.length - 1);
                digitBoxes[nextFocus]?.focus();

                const code = syncMasterOtp();
                if (code.length === 6) {
                    this.verifyOtpCode(input);
                }
            });
        });

        const triggerEmailCheck = (immediate = false) => {
            if (this._emailCheckDebounce) {
                clearTimeout(this._emailCheckDebounce);
                this._emailCheckDebounce = null;
            }
            const email = input.value.trim().toLowerCase();
            if (!email) {
                this.resetFieldState(input);
                return;
            }
            if (!emailRegex.test(email)) {
                return;
            }
            if (input.classList.contains('is-verified') && input.dataset.verifiedEmail === email) {
                return;
            }
            const otpWrapper = document.getElementById('otpFieldsWrapper');
            if (otpWrapper && !otpWrapper.classList.contains('hidden') && input.dataset.otpSentEmail === email) {
                return;
            }

            const spinner = document.getElementById('emailCheckSpinner');
            if (spinner) {
                spinner.classList.remove('hidden');
                spinner.classList.add('inline-flex');
            }
            if (sendOtpBtn) {
                sendOtpBtn.classList.add('hidden');
                sendOtpBtn.classList.remove('inline-flex');
            }

            const delay = immediate || this.isLocallyVerifiedEmail(email) ? 30 : 250;
            this._emailCheckDebounce = setTimeout(() => {
                this.checkEmailStatus(input);
            }, delay);
        };

        input.addEventListener('input', () => {
            const currentVal = input.value.trim().toLowerCase();
            if (input.classList.contains('is-verified') && input.dataset.verifiedEmail === currentVal) {
                return;
            }
            this.resetFieldState(input);
            if (emailRegex.test(currentVal)) {
                triggerEmailCheck(false);
            }
        });

        input.addEventListener('change', () => {
            const currentVal = input.value.trim().toLowerCase();
            if (emailRegex.test(currentVal)) {
                triggerEmailCheck(true);
            }
        });

        input.addEventListener('blur', async () => {
            if (this._emailCheckDebounce) {
                clearTimeout(this._emailCheckDebounce);
                this._emailCheckDebounce = null;
            }
            const email = input.value.trim().toLowerCase();
            input.classList.remove('shake-field', 'field-processing');

            if (!email) {
                this.resetFieldState(input);
                return;
            }

            if (!emailRegex.test(email)) {
                this.setEmailBadge('invalid', 'Invalid Email');
                this.showError(input, false);
                return;
            }

            if (input.classList.contains('is-verified') && input.dataset.verifiedEmail === email) {
                return;
            }

            const otpWrapper = document.getElementById('otpFieldsWrapper');
            if (otpWrapper && !otpWrapper.classList.contains('hidden') && input.dataset.otpSentEmail === email) {
                return;
            }

            await this.checkEmailStatus(input);
        });
    },

    _emailCheckDebounce: null,

    isLocallyVerifiedEmail(email) {
        if (!email) return false;
        try {
            const list = JSON.parse(localStorage.getItem('dunes_verified_emails') || '[]');
            return Array.isArray(list) && list.includes(email.trim().toLowerCase());
        } catch (e) {
            return false;
        }
    },

    rememberVerifiedEmail(email) {
        if (!email) return;
        const clean = email.trim().toLowerCase();
        try {
            localStorage.setItem('dunes_guest_email', clean);
            const list = JSON.parse(localStorage.getItem('dunes_verified_emails') || '[]');
            const arr = Array.isArray(list) ? list : [];
            if (!arr.includes(clean)) {
                arr.push(clean);
                localStorage.setItem('dunes_verified_emails', JSON.stringify(arr.slice(-20)));
            }
        } catch (e) {}
    },

    async checkEmailStatus(input) {
        if (!input) return false;
        const email = input.value.trim().toLowerCase();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email || !emailRegex.test(email)) return false;

        if (input.classList.contains('is-verified') && input.dataset.verifiedEmail === email) {
            return true;
        }

        const spinner = document.getElementById('emailCheckSpinner');
        const sendOtpBtn = document.getElementById('sendEmailOtpBtn');
        if (spinner) {
            spinner.classList.remove('hidden');
            spinner.classList.add('inline-flex');
        }
        if (sendOtpBtn) {
            sendOtpBtn.classList.add('hidden');
            sendOtpBtn.classList.remove('inline-flex');
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.CSRF_TOKEN || '';
            const res = await fetch('/ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: `action=check_email_status&email=${encodeURIComponent(email)}&_token=${encodeURIComponent(csrfToken)}`
            });
            const raw = await res.text();
            const data = JSON.parse(raw.replace(/^\uFEFF+/, '').trim());

            // Ensure the input value hasn't changed while fetch was in flight
            if (input.value.trim().toLowerCase() !== email) {
                return false;
            }

            if (data.success && data.verified) {
                this.markEmailVerified(input);
                return true;
            } else {
                const otpWrapper = document.getElementById('otpFieldsWrapper');
                if (!otpWrapper || otpWrapper.classList.contains('hidden')) {
                    this.showVerifyButton(input);
                }
                return false;
            }
        } catch (e) {
            if (input.value.trim().toLowerCase() === email) {
                this.showVerifyButton(input);
            }
            return false;
        } finally {
            if (spinner && input.value.trim().toLowerCase() === email) {
                spinner.classList.add('hidden');
                spinner.classList.remove('inline-flex');
            }
        }
    },

    setEmailBadge(state, text = '') {
        const badge = document.getElementById('emailStatusBadge');
        const container = document.getElementById('bookingEmailContainer');
        const helper = document.getElementById('emailHelperText');
        if (!badge) return;

        badge.className = 'items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold tracking-wide transition-all';

        if (!state || state === 'none') {
            badge.classList.add('hidden');
            if (container) container.classList.remove('border-emerald-400', 'border-rose-400', 'border-amber-400');
            if (helper) {
                helper.textContent = '';
                helper.classList.add('hidden');
            }
            return;
        }

        badge.classList.remove('hidden');
        badge.classList.add('inline-flex');

        if (state === 'verified') {
            badge.classList.add('bg-emerald-100', 'text-emerald-800', 'border', 'border-emerald-200');
            badge.innerHTML = `<i class="bi bi-patch-check-fill text-emerald-600"></i> <span>${text || 'Verified'}</span>`;
            if (container) {
                container.classList.remove('border-rose-400', 'border-amber-400');
                container.classList.add('border-emerald-400');
            }
            if (helper) {
                helper.textContent = 'Email verified — your instant booking voucher will be sent here.';
                helper.className = 'text-[11px] font-semibold text-emerald-600 mt-1 block';
            }
        } else if (state === 'unverified') {
            badge.classList.add('bg-amber-100', 'text-amber-800', 'border', 'border-amber-200');
            badge.innerHTML = `<i class="bi bi-shield-exclamation text-amber-600"></i> <span>${text || 'Verify Email'}</span>`;
            if (container) {
                container.classList.remove('border-emerald-400', 'border-rose-400');
                container.classList.add('border-amber-400');
            }
            if (helper) {
                helper.textContent = 'Click "Verify" to receive a quick 6-digit confirmation code.';
                helper.className = 'text-[11px] font-medium text-amber-700 mt-1 block';
            }
        } else if (state === 'sent') {
            badge.classList.add('bg-orange-100', 'text-orange-800', 'border', 'border-orange-200');
            badge.innerHTML = `<i class="bi bi-envelope-paper-fill text-orange-600"></i> <span>${text || 'Code Sent'}</span>`;
            if (container) {
                container.classList.remove('border-emerald-400', 'border-rose-400');
                container.classList.add('border-amber-400');
            }
            if (helper) {
                helper.classList.add('hidden');
            }
        } else if (state === 'invalid') {
            badge.classList.add('bg-rose-100', 'text-rose-700', 'border', 'border-rose-200');
            badge.innerHTML = `<i class="bi bi-exclamation-circle-fill text-rose-600"></i> <span>${text || 'Invalid Email'}</span>`;
            if (container) {
                container.classList.remove('border-emerald-400', 'border-amber-400');
                container.classList.add('border-rose-400');
            }
            if (helper) {
                helper.textContent = 'Please enter a valid email address (e.g., name@example.com).';
                helper.className = 'text-[11px] font-semibold text-rose-600 mt-1 block';
            }
        }
    },

    resetFieldState(input) {
        if (!input) return;
        input.classList.remove('is-verified', 'shake-field', 'field-valid', 'field-invalid', 'field-processing');
        delete input.dataset.isVerified;
        delete input.dataset.verifiedEmail;
        delete input.dataset.otpSentEmail;

        if (input.id === 'bookingEmail') {
            if (this._otpTimerInterval) {
                clearInterval(this._otpTimerInterval);
                this._otpTimerInterval = null;
            }
            this.setEmailBadge('none');

            const spinner = document.getElementById('emailCheckSpinner');
            const sendBtn = document.getElementById('sendEmailOtpBtn');
            const verifiedIcon = document.getElementById('emailVerifiedIcon');
            const otpWrapper = document.getElementById('otpFieldsWrapper');
            const otpError = document.getElementById('otpErrorMsg');

            if (spinner) { spinner.classList.add('hidden'); spinner.classList.remove('inline-flex'); }
            if (sendBtn) {
                sendBtn.classList.add('hidden');
                sendBtn.classList.remove('inline-flex');
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="bi bi-shield-lock-fill text-xs"></i><span>Verify</span>';
            }
            if (verifiedIcon) { verifiedIcon.classList.add('hidden'); verifiedIcon.classList.remove('inline-flex'); }
            if (otpWrapper) otpWrapper.classList.add('hidden');
            if (otpError) { otpError.classList.add('hidden'); otpError.classList.remove('flex'); }

            document.querySelectorAll('#otpDigitsContainer .otp-digit-box').forEach(b => { b.value = ''; });
            const masterOtp = document.getElementById('bookingOtpCode');
            if (masterOtp) masterOtp.value = '';
        }
    },

    showError(input, disableSubmit = false) {
        if (!input) return;
        input.classList.remove('field-processing');
        input.classList.add('field-invalid');
        input.classList.remove('is-verified', 'field-valid');

        const targetEl = (input.id === 'bookingEmail' ? document.getElementById('bookingEmailContainer') : null)
            || (input.id === 'bookingPhone' ? document.getElementById('bookingPhoneContainer') : null)
            || input;

        void targetEl.offsetWidth;
        targetEl.classList.add('shake-field');
        setTimeout(() => targetEl.classList.remove('shake-field'), 500);

        if (disableSubmit) {
            this.toggleSubmit(input.form, false);
        }
    },

    markEmailVerified(input) {
        if (!input) return;
        const cleanEmail = input.value.trim().toLowerCase();
        this.resetFieldState(input);
        input.classList.add('is-verified', 'field-valid');
        input.dataset.isVerified = 'true';
        input.dataset.verifiedEmail = cleanEmail;
        this.rememberVerifiedEmail(cleanEmail);

        if (input.id === 'bookingEmail') {
            const verifiedIcon = document.getElementById('emailVerifiedIcon');
            if (verifiedIcon) {
                verifiedIcon.classList.remove('hidden');
                verifiedIcon.classList.add('inline-flex');
            }
            this.setEmailBadge('verified', 'Verified');
        }

        this.toggleSubmit(input.form, true);

        if (this._pendingSubmitAfterOtp && this._pendingSubmitAfterOtp === input.closest('form')) {
            const pendingForm = this._pendingSubmitAfterOtp;
            this._pendingSubmitAfterOtp = null;
            setTimeout(() => {
                pendingForm.requestSubmit ? pendingForm.requestSubmit() : pendingForm.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
            }, 150);
        }
    },

    showVerifyButton(input) {
        if (!input || input.classList.contains('is-verified')) return;
        if (input.id === 'bookingEmail') {
            const sendBtn = document.getElementById('sendEmailOtpBtn');
            const verifiedIcon = document.getElementById('emailVerifiedIcon');
            const spinner = document.getElementById('emailCheckSpinner');
            if (spinner) { spinner.classList.add('hidden'); spinner.classList.remove('inline-flex'); }
            if (verifiedIcon) { verifiedIcon.classList.add('hidden'); verifiedIcon.classList.remove('inline-flex'); }
            if (sendBtn) {
                sendBtn.classList.remove('hidden');
                sendBtn.classList.add('inline-flex');
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="bi bi-shield-lock-fill text-xs"></i><span>Verify</span>';
            }
            this.setEmailBadge('unverified', 'Verify Email');
        }
    },

    async startVerification(input) {
        if (!input) return;
        const email = input.value.trim().toLowerCase();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email || !emailRegex.test(email)) {
            this.setEmailBadge('invalid', 'Invalid Email');
            this.showError(input, false);
            return;
        }

        const sendBtn = document.getElementById('sendEmailOtpBtn');
        const resendBtn = document.getElementById('resendOtpBtn');

        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span class="inline-block w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span><span>Sending</span>';
            sendBtn.classList.remove('hidden');
            sendBtn.classList.add('inline-flex');
        }
        if (resendBtn) {
            resendBtn.disabled = true;
        }

        try {
            const form = input.closest('form');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form?.querySelector('[name="_token"]')?.value || window.CSRF_TOKEN || '';
            const res = await fetch('/ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrf
                },
                body: `action=send_otp&email=${encodeURIComponent(email)}&csrf_token=${encodeURIComponent(csrf)}&_token=${encodeURIComponent(csrf)}`
            });
            const raw = await res.text();
            const data = JSON.parse(raw.replace(/^\uFEFF+/, '').trim());

            if (data.success) {
                // If email is already verified in the database, mark it verified immediately without asking for OTP
                if (data.already_verified || data.verified) {
                    this.markEmailVerified(input);
                    return;
                }
                input.dataset.otpSentEmail = email;
                this.toast('6-digit verification code sent to ' + email, 'success');
                if (sendBtn) {
                    sendBtn.classList.add('hidden');
                    sendBtn.classList.remove('inline-flex');
                }
                this.setEmailBadge('sent', 'Code Sent');
                this.showOtpInput(input);
            } else {
                const msg = data.message || 'Could not send verification code. Please try again.';
                this.toast(msg, 'error');
                // If already sent recently (cooldown), still show the OTP input box so user can type the code!
                if (msg.toLowerCase().includes('wait') && msg.toLowerCase().includes('before requesting')) {
                    input.dataset.otpSentEmail = email;
                    if (sendBtn) {
                        sendBtn.classList.add('hidden');
                        sendBtn.classList.remove('inline-flex');
                    }
                    this.showOtpInput(input);
                } else if (sendBtn) {
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = '<i class="bi bi-arrow-repeat text-xs"></i><span>Retry</span>';
                }
            }
        } catch (e) {
            this.toast('Network error — please check your connection and try again.', 'error');
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="bi bi-arrow-repeat text-xs"></i><span>Retry</span>';
            }
        }
    },

    showOtpInput(input) {
        const otpWrapper = document.getElementById('otpFieldsWrapper');
        if (!otpWrapper) return;

        const targetEmailEl = document.getElementById('otpTargetEmail');
        if (targetEmailEl && input?.value) {
            targetEmailEl.textContent = input.value.trim();
        }

        const errEl = document.getElementById('otpErrorMsg');
        if (errEl) {
            errEl.classList.add('hidden');
            errEl.classList.remove('flex');
        }

        const digitBoxes = Array.from(document.querySelectorAll('#otpDigitsContainer .otp-digit-box'));
        digitBoxes.forEach(b => { b.value = ''; });
        const masterOtp = document.getElementById('bookingOtpCode');
        if (masterOtp) masterOtp.value = '';

        otpWrapper.classList.remove('hidden');
        otpWrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        setTimeout(() => {
            digitBoxes[0]?.focus();
        }, 80);

        // Start 60-second resend countdown timer
        if (this._otpTimerInterval) {
            clearInterval(this._otpTimerInterval);
        }
        const timerEl = document.getElementById('otpTimer');
        const resendBtn = document.getElementById('resendOtpBtn');
        let cooldown = 60;

        const tick = () => {
            if (cooldown > 0) {
                if (timerEl) {
                    timerEl.textContent = `Resend available in 0:${String(cooldown).padStart(2, '0')}`;
                    timerEl.classList.remove('hidden');
                }
                if (resendBtn) {
                    resendBtn.disabled = true;
                    resendBtn.classList.add('opacity-50', 'pointer-events-none');
                }
                cooldown--;
            } else {
                clearInterval(this._otpTimerInterval);
                this._otpTimerInterval = null;
                if (timerEl) {
                    timerEl.textContent = "Didn't receive the code?";
                }
                if (resendBtn) {
                    resendBtn.disabled = false;
                    resendBtn.classList.remove('opacity-50', 'pointer-events-none');
                }
            }
        };
        tick();
        this._otpTimerInterval = setInterval(tick, 1000);
    },

    async verifyOtpCode(input) {
        if (!input) input = document.getElementById('bookingEmail');
        if (!input) return;

        const digitBoxes = Array.from(document.querySelectorAll('#otpDigitsContainer .otp-digit-box'));
        const masterOtp = document.getElementById('bookingOtpCode');
        const code = (digitBoxes.map(b => b.value).join('') || masterOtp?.value || '').replace(/\D/g, '');
        const errEl = document.getElementById('otpErrorMsg');
        const errText = document.getElementById('otpErrorText');
        const verifyBtn = document.getElementById('verifyOtpBtn');
        const digitsContainer = document.getElementById('otpDigitsContainer');

        if (code.length < 6) {
            if (errText) errText.textContent = 'Please enter all 6 digits of your verification code.';
            if (errEl) {
                errEl.classList.remove('hidden');
                errEl.classList.add('flex');
            }
            const firstEmpty = digitBoxes.find(b => !b.value) || digitBoxes[0];
            firstEmpty?.focus();
            return;
        }

        if (verifyBtn) {
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<span class="inline-block w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span><span>Verifying...</span>';
        }
        digitBoxes.forEach(b => { b.disabled = true; });

        const form = input.closest('form');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form?.querySelector('[name="_token"]')?.value || window.CSRF_TOKEN || '';

        try {
            const res = await fetch('/ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrf
                },
                body: `action=verify_otp&email=${encodeURIComponent(input.value.trim())}&otp=${encodeURIComponent(code)}&csrf_token=${encodeURIComponent(csrf)}&_token=${encodeURIComponent(csrf)}`
            });
            const raw = await res.text();
            const data = JSON.parse(raw.replace(/^\uFEFF+/, '').trim());

            if (data.success) {
                this.toast('Email verified successfully!', 'success');
                digitBoxes.forEach(b => { b.disabled = false; });
                if (verifyBtn) {
                    verifyBtn.disabled = false;
                    verifyBtn.innerHTML = '<i class="bi bi-check2-circle"></i><span>Verify Code</span>';
                }
                this.markEmailVerified(input);
            } else {
                digitBoxes.forEach(b => { b.disabled = false; });
                if (verifyBtn) {
                    verifyBtn.disabled = false;
                    verifyBtn.innerHTML = '<i class="bi bi-check2-circle"></i><span>Verify Code</span>';
                }
                if (errText) errText.textContent = data.message || 'Invalid verification code. Please check and try again.';
                if (errEl) {
                    errEl.classList.remove('hidden');
                    errEl.classList.add('flex');
                }
                if (digitsContainer) {
                    void digitsContainer.offsetWidth;
                    digitsContainer.classList.add('shake-field');
                    setTimeout(() => digitsContainer.classList.remove('shake-field'), 500);
                }
                digitBoxes[0]?.focus();
                digitBoxes[0]?.select();
            }
        } catch (e) {
            digitBoxes.forEach(b => { b.disabled = false; });
            if (verifyBtn) {
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = '<i class="bi bi-check2-circle"></i><span>Verify Code</span>';
            }
            if (errText) errText.textContent = 'Verification failed. Please check your connection and try again.';
            if (errEl) {
                errEl.classList.remove('hidden');
                errEl.classList.add('flex');
            }
        }
    },

    toggleSubmit(form, enable) {
        if (!form) return;
        const btn = form.querySelector('[type="submit"]');
        if (!btn) return;
        btn.disabled = false;
        const wrapper = btn.closest('.submit-tooltip-wrapper');
        if (wrapper) {
            wrapper.replaceWith(btn);
        }
    },

    initTracking() {
        document.addEventListener('click', e => {
            const link = e.target.closest('a');
            if (!link) return;
            const href = link.getAttribute('href');
            if (!href) return;

            if (href.startsWith('tel:')) {
                if (window.dataLayer) window.dataLayer.push({ event: 'contact', method: 'phone' });
                if (typeof window.gtag === 'function') window.gtag('event', 'contact', { method: 'phone' });
                if (typeof window.fbq === 'function') window.fbq('track', 'Contact');
            } else if (href.startsWith('mailto:')) {
                if (window.dataLayer) window.dataLayer.push({ event: 'contact', method: 'email' });
                if (typeof window.gtag === 'function') window.gtag('event', 'contact', { method: 'email' });
            }
        });
    },

    initDateCards() {
        const wrapper = document.getElementById('dateCardsWrapper');
        const input = document.getElementById('bookingDate');
        if (!wrapper || !input) return;

        // Calculate Dubai local time (GST = UTC+4)
        const now = new Date();
        const utcMs = now.getTime() + (now.getTimezoneOffset() * 60000);
        const dubaiNow = new Date(utcMs + (3600000 * 4));
        const dubaiHour = dubaiNow.getHours() + (dubaiNow.getMinutes() / 60);
        const canBookToday = dubaiHour < 13.5; // Cutoff at 1:30 PM (13:30) Asia/Dubai
        const startDay = canBookToday ? 0 : 1;

        let html = '';
        const days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

        for (let i = startDay; i <= (startDay + 30); i++) {
            const d = new Date(dubaiNow);
            d.setDate(dubaiNow.getDate() + (canBookToday ? i : (i - 1 + 1)));

            const year = d.getFullYear();
            const monthStr = String(d.getMonth() + 1).padStart(2, '0');
            const dayStr = String(d.getDate()).padStart(2, '0');
            const dateStr = `${year}-${monthStr}-${dayStr}`;

            const dayName = (i === 0 && canBookToday) ? 'Today' : days[d.getDay()];
            const dayNum = d.getDate();
            const monthName = months[d.getMonth()];

            html += `<div class="date-card" data-date="${dateStr}">
                <div class="day">${dayName}</div>
                <div class="date">${dayNum}</div>
                <div class="month">${monthName}</div>
            </div>`;
        }

        wrapper.innerHTML = html;

        wrapper.querySelectorAll('.date-card[data-date]').forEach(card => {
            card.addEventListener('click', () => {
                wrapper.querySelectorAll('.date-card').forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                input.value = card.dataset.date;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });

        document.getElementById('calendarTrigger')?.addEventListener('click', () => {
            try {
                input.showPicker();
            } catch (e) {
                input.click();
            }
        });

        document.getElementById('datePrev')?.addEventListener('click', () => {
            wrapper.scrollBy({ left: -220, behavior: 'smooth' });
        });
        document.getElementById('dateNext')?.addEventListener('click', () => {
            wrapper.scrollBy({ left: 220, behavior: 'smooth' });
        });

        input.addEventListener('change', () => {
            const val = input.value;
            wrapper.querySelectorAll('.date-card').forEach(c => c.classList.remove('selected'));
            const matchingCard = wrapper.querySelector(`.date-card[data-date="${val}"]`);
            if (matchingCard) {
                matchingCard.classList.add('selected');
                matchingCard.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }
        });
    },

    getFormattedPhone(input) {
        if (!input) return '';
        const rawVal = (input.value || '').trim();
        if (!rawVal) return '';

        const iti = input._iti;
        if (iti && typeof iti.getNumber === 'function') {
            try {
                const itiNum = iti.getNumber();
                if (itiNum && itiNum.startsWith('+') && itiNum.replace(/\D/g, '').length >= 8) {
                    return itiNum;
                }
            } catch (e) {}
        }

        let dialCode = '971';
        if (iti && typeof iti.getSelectedCountryData === 'function') {
            const countryData = iti.getSelectedCountryData();
            if (countryData && countryData.dialCode) {
                dialCode = String(countryData.dialCode);
            }
        }

        if (rawVal.startsWith('+')) {
            const cleanPlus = '+' + rawVal.replace(/\D/g, '');
            if (cleanPlus.length >= 9) return cleanPlus;
        }

        let digits = rawVal.replace(/\D/g, '');
        if (!digits) return rawVal;

        if (digits.startsWith('00')) {
            digits = digits.slice(2);
        }

        if (digits.startsWith(dialCode) && digits.length >= dialCode.length + 7) {
            digits = digits.slice(dialCode.length);
        }

        if (digits.startsWith('0') && digits.length >= 8) {
            digits = digits.replace(/^0+/, '');
        }

        return `+${dialCode}${digits}`;
    },

    isValidPhone(input) {
        if (!input) return true;
        const rawVal = (input.value || '').trim();
        if (!rawVal) {
            return !input.hasAttribute('required');
        }

        const iti = input._iti;
        if (iti && typeof iti.isValidNumber === 'function') {
            try {
                const nativeValid = iti.isValidNumber();
                if (nativeValid === true) return true;
            } catch (e) {}
        }

        const formatted = this.getFormattedPhone(input);
        const totalDigits = formatted.replace(/\D/g, '');

        let dialCode = '971';
        if (iti && typeof iti.getSelectedCountryData === 'function') {
            const countryData = iti.getSelectedCountryData();
            if (countryData && countryData.dialCode) {
                dialCode = String(countryData.dialCode);
            }
        }

        const nationalDigits = totalDigits.startsWith(dialCode)
            ? totalDigits.slice(dialCode.length)
            : rawVal.replace(/\D/g, '').replace(/^0+/, '');

        if (totalDigits.length < 8 || totalDigits.length > 15) return false;
        if (nationalDigits.length < 6 || nationalDigits.length > 12) return false;
        if (/^(\d)\1{5,}$/.test(nationalDigits)) return false;

        return true;
    },

    setPhoneFieldState(input, isValid) {
        if (!input) return;
        const isBookingPhone = input.id === 'bookingPhone';
        const container = isBookingPhone ? document.getElementById('bookingPhoneContainer') : input.closest('.welcome-phone-field, .phone-field');
        const errHelper = isBookingPhone ? document.getElementById('bookingPhoneError') : null;

        if (isValid) {
            input.classList.remove('is-invalid');
            if (container) container.classList.remove('border-rose-500', 'ring-2', 'ring-rose-500/20');
            if (errHelper) {
                errHelper.classList.add('hidden');
                errHelper.classList.remove('flex');
            }
        } else {
            input.classList.add('is-invalid');
            if (container) container.classList.add('border-rose-500', 'ring-2', 'ring-rose-500/20');
            if (errHelper) {
                errHelper.classList.remove('hidden');
                errHelper.classList.add('flex');
            }
        }
    },

    initPhoneInputs() {
        if (typeof window.intlTelInput === 'undefined') return;

        const utilsUrl = window.location.origin + '/assets/vendor/intl-tel-input/26.0.6/build/utils.js';
        const loadItiUtils = () => import(/* @vite-ignore */ utilsUrl);

        // Pre-attach v26 ESM utilities module so getNumber() and isValidNumber() work natively
        if (typeof window.intlTelInput.attachUtils === 'function' && !window.intlTelInput.startedLoadingUtilsScript) {
            try {
                window.intlTelInput.attachUtils(loadItiUtils);
            } catch (e) {}
        }

        const inputs = document.querySelectorAll('input[type="tel"], #bookingPhone, #waPhone, #welcomePhone, #phone');
        inputs.forEach(input => {
            if (!input || input.dataset.itiInitialized === 'true') return;
            input.dataset.itiInitialized = 'true';

            try {
                const iti = window.intlTelInput(input, {
                    loadUtils: loadItiUtils,
                    separateDialCode: true,
                    initialCountry: 'ae',
                    preferredCountries: ['ae', 'sa', 'om', 'kw', 'qa', 'bh', 'gb', 'us', 'in', 'de', 'ru'],
                    autoPlaceholder: 'aggressive',
                    customPlaceholder: function(selectedCountryPlaceholder) {
                        return selectedCountryPlaceholder ? selectedCountryPlaceholder : "50 123 4567";
                    }
                });

                input._iti = iti;

                input.addEventListener('countrychange', () => {
                    if (input.value.trim() !== '') {
                        this.setPhoneFieldState(input, this.isValidPhone(input));
                    }
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                });

                input.addEventListener('blur', () => {
                    if (input.value.trim() !== '') {
                        this.setPhoneFieldState(input, this.isValidPhone(input));
                    } else {
                        this.setPhoneFieldState(input, true);
                    }
                });

                input.addEventListener('input', () => {
                    if (input.classList.contains('is-invalid') && this.isValidPhone(input)) {
                        this.setPhoneFieldState(input, true);
                    }
                });
            } catch (err) {
                console.warn('intlTelInput initialization error:', err);
            }
        });
    },

    initWhatsApp() {
        const form = document.getElementById('whatsappForm');
        const startBtn = document.getElementById('startChatBtn');
        const nameInp = document.getElementById('waName');
        const phoneInp = document.getElementById('waPhone');
        const agreeInp = document.getElementById('waAgreement');

        this.initPhoneInputs();

        const check = () => {
            if (startBtn && nameInp && phoneInp) {
                const agreeValid = !agreeInp || agreeInp.checked;
                startBtn.disabled = !(nameInp.value.trim() && phoneInp.value.trim() && agreeValid);
            }
        };

        nameInp?.addEventListener('input', check);
        phoneInp?.addEventListener('input', check);
        agreeInp?.addEventListener('change', check);

        if (!this._waClickListenerBound) {
            this._waClickListenerBound = true;
            document.addEventListener('click', e => {
                // Find interactive container element (not an inner text span or icon)
                const el = e.target.closest(
                    'a[href*="wa.me"], a[href*="whatsapp.com"], a[href*="whatsapp:"], ' +
                    'button[data-action="whatsapp"], a[data-action="whatsapp"], ' +
                    '.fab-whatsapp, .btn-circle-whatsapp, .btn-whatsapp-animated, .btn-whatsapp, ' +
                    '[data-action="open-whatsapp"], [data-whatsapp]'
                ) || (
                    e.target.closest('.bi-whatsapp')
                        ? e.target.closest('a, button, [role="button"]')
                        : null
                );

                if (!el) return;

                // Don't intercept the submit button inside the modal itself or admin lead details
                if (el.id === 'startChatBtn' || el.closest('#whatsappForm') || el.closest('#whatsappLeadModal') || el.closest('.admin-wrapper')) return;

                // Extract URL (checking attribute, dataset, or property for Alpine :href or JS set href)
                const rawHref = el.getAttribute ? (el.getAttribute('href') || el.dataset?.href || '') : '';
                const href = (rawHref && rawHref !== '#' && !rawHref.startsWith('javascript:')) 
                    ? rawHref 
                    : (el.href && !el.href.endsWith('#') && !el.href.startsWith('javascript:') ? el.href : '');

                // Don't intercept purely social share links without target number
                if (href.includes('wa.me/?text=') || href.includes('whatsapp://send?text=')) return;

                // Verify this is a WhatsApp link or button
                const isWa = href.includes('wa.me') || href.includes('api.whatsapp.com') || href.includes('whatsapp:') ||
                             el.classList.contains('fab-whatsapp') || el.classList.contains('btn-circle-whatsapp') ||
                             el.classList.contains('btn-whatsapp-animated') || el.classList.contains('btn-whatsapp') ||
                             el.dataset?.action === 'whatsapp' || el.dataset?.action === 'open-whatsapp' ||
                             !!el.closest('.fab-whatsapp, .btn-circle-whatsapp, .btn-whatsapp-animated, .btn-whatsapp') ||
                             !!el.querySelector('.bi-whatsapp');

                if (!isWa) return;

                e.preventDefault();
                e.stopPropagation();

                let tourName = '';
                if (el.dataset?.tourName) {
                    tourName = el.dataset.tourName;
                } else if (el.closest('[data-tour-name]')?.dataset?.tourName) {
                    tourName = el.closest('[data-tour-name]').dataset.tourName;
                } else if (el.closest('.tour-card, article')?.querySelector('h2, h3, h4')) {
                    tourName = el.closest('.tour-card, article').querySelector('h2, h3, h4').innerText.trim();
                } else if (location.pathname.includes('/tours/') || document.querySelector('.tour-hero')) {
                    const h1 = document.querySelector('h1');
                    if (h1) tourName = h1.innerText.trim();
                }

                this.openWhatsApp(tourName, href);
            });
        }
    },

    openWhatsApp(tourName = '', directHref = '') {
        const modal = document.getElementById('whatsappModal');
        const formEnabled = (
            typeof window.WHATSAPP_FORM_ENABLED === 'undefined' ||
            window.WHATSAPP_FORM_ENABLED === '1' ||
            window.WHATSAPP_FORM_ENABLED === 1 ||
            window.WHATSAPP_FORM_ENABLED === true ||
            window.WHATSAPP_FORM_ENABLED === 'true' ||
            window.WHATSAPP_FORM_ENABLED === 'yes'
        );
        const defaultNum = (window.WHATSAPP_NUMBER || '971502456056').replace(/[^0-9]/g, '');

        let prefillMsg = '';
        if (directHref) {
            try {
                const u = new URL(directHref, window.location.origin);
                prefillMsg = u.searchParams.get('text') || '';
            } catch (e) {}
        }
        if (!prefillMsg) {
            const customDefault = window.WHATSAPP_PREFILL_MESSAGE || 'Hello, I would like to inquire about Dubai desert safaris.';
            prefillMsg = tourName ? `Hello, I would like to inquire about ${tourName}.` : customDefault;
        }
        const defaultMsgEncoded = encodeURIComponent(prefillMsg);
        const fallbackUrl = directHref || `https://wa.me/${defaultNum}?text=${defaultMsgEncoded}`;

        if (!formEnabled || !modal) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.CSRF_TOKEN || '';
            const fd = new FormData();
            fd.append('action', 'logWhatsApp');
            fd.append('csrf_token', csrfToken);
            fd.append('_token', csrfToken);
            fd.append('name', 'N/A');
            fd.append('phone', 'N/A');
            fd.append('tour_name', tourName || 'General Inquiry');
            fd.append('page_url', window.location.href);
            fd.append('message_text', prefillMsg);

            if (typeof window.gtag === 'function') {
                window.gtag('event', 'contact', {
                    method: 'whatsapp',
                    tour_name: tourName || 'General Inquiry'
                });
            }
            if (window.dataLayer) {
                window.dataLayer.push({
                    event: 'whatsapp_click',
                    tour_name: tourName || 'General Inquiry'
                });
            }
            if (typeof window.fbq === 'function') {
                window.fbq('track', 'Contact', { content_name: 'WhatsApp Click' });
            }

            // Open WhatsApp synchronously inside user click gesture to prevent popup blockers
            window.open(fallbackUrl, '_blank', 'noopener,noreferrer');

            fetch('/ajax.php', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: fd,
                keepalive: true
            }).catch(() => {});
            return;
        }

        const tourNameInp = document.getElementById('waTourName');
        const pageUrlInp = document.getElementById('waPageUrl');
        const msgTextInp = document.getElementById('waMessageText');
        const tourDisplay = document.getElementById('waTourNameDisplay');
        const errEl = document.getElementById('waError');

        if (errEl) errEl.classList.add('d-none');
        if (tourNameInp) tourNameInp.value = tourName || 'General Inquiry';
        if (pageUrlInp) pageUrlInp.value = window.location.href;
        if (msgTextInp) msgTextInp.value = prefillMsg;
        if (tourDisplay) tourDisplay.textContent = tourName || 'Dubai Desert Safari Experience';

        const map = {
            'gpsLat': 'waGpsLat',
            'gpsLng': 'waGpsLng',
            'gpsAccuracy': 'waGpsAccuracy',
            'gpsTimestamp': 'waGpsTimestamp',
            'gpsConsent': 'waGpsConsent',
            'gpsSource': 'waGpsSource'
        };
        for (const [srcId, destId] of Object.entries(map)) {
            const src = document.getElementById(srcId);
            const dest = document.getElementById(destId);
            if (src && dest && src.value) dest.value = src.value;
        }

        const startBtn = document.getElementById('startChatBtn');
        const nameInp = document.getElementById('waName');
        const phoneInp = document.getElementById('waPhone');
        const agreeInp = document.getElementById('waAgreement');

        // Pre-fill name and phone if already known from guest session or other forms
        if (nameInp && !nameInp.value) {
            const guestName = document.getElementById('bookingName')?.value
                || document.getElementById('welcomeName')?.value
                || localStorage.getItem('dunes_guest_name') || '';
            if (guestName) nameInp.value = guestName;
        }
        if (phoneInp && !phoneInp.value) {
            const guestPhone = document.getElementById('bookingPhone')?.value
                || document.getElementById('welcomePhone')?.value
                || localStorage.getItem('dunes_guest_phone') || '';
            if (guestPhone) phoneInp.value = guestPhone;
        }

        if (startBtn && nameInp && phoneInp) {
            const agreeValid = !agreeInp || agreeInp.checked;
            startBtn.disabled = !(nameInp.value.trim() && phoneInp.value.trim() && agreeValid);
        }

        this.initPhoneInputs();

        if (window.Alpine && Alpine.store('modal')) {
            Alpine.store('modal').open('whatsapp');
        } else if (modal) {
            modal.style.display = 'block';
            modal.classList.add('show', 'active');
            modal.removeAttribute('aria-hidden');
            modal.setAttribute('aria-modal', 'true');
            document.body.style.overflow = 'hidden';
            document.body.classList.add('modal-open');
        }
    },

    initUTM() {
        const p = new URLSearchParams(location.search);
        ['utm_source','utm_medium','utm_campaign','utm_term','utm_content'].forEach(k => {
            const v = p.get(k) || sessionStorage.getItem(k) || '';
            if (p.get(k)) sessionStorage.setItem(k, p.get(k));
            const fieldId = k.replace('utm_', 'utm').replace(/_([a-z])/g, (_, l) => l.toUpperCase());
            const el = document.getElementById(fieldId);
            if (el) el.value = v;
        });
    },

    initHeader() {
        const h = document.getElementById('header');
        if (!h) return;

        const updateHeight = () => {
            const banner = document.getElementById('dunesTopPromoBanner');
            const hHeight = h.offsetHeight || 72;
            document.documentElement.style.setProperty('--header-h', hHeight + 'px');
            if (banner && banner.offsetHeight) {
                document.documentElement.style.setProperty('--promo-banner-h', banner.offsetHeight + 'px');
            } else {
                document.documentElement.style.setProperty('--promo-banner-h', '0px');
            }
        };

        updateHeight();
        window.addEventListener('resize', updateHeight, { passive: true });
        window.addEventListener('orientationchange', updateHeight, { passive: true });

        if (typeof ResizeObserver !== 'undefined') {
            try {
                new ResizeObserver(updateHeight).observe(h);
            } catch (e) {}
        }

        let ly = 0;
        window.addEventListener('scroll', () => {
            const y = window.pageYOffset;
            if (y > 100) h.style.transform = y > ly ? 'translateY(-100%)' : 'translateY(0)';
            else h.style.transform = 'translateY(0)';
            ly = y;
        }, { passive: true });
    },

    initMobile() {
        // Controlled via Alpine.store('mobileNav')
    },

    initModal() {
        // Controlled via Alpine.store('modal')
    },

    initBooking() {
        const tourSelect = document.getElementById('bookingTour');
        const next = document.getElementById('nextStep');
        const prev = document.getElementById('headerBackBtn');
        const edit = document.getElementById('editStep1');
        const dateInput = document.getElementById('bookingDate');
        const locationInput = document.getElementById('bookingLocation');
        const tierInput = document.getElementById('selectedTier');
        const btnWrapper = document.getElementById('continueBtnWrapper');

        const triggerShakeAndHighlight = (el) => {
            if (!el) return;
            el.classList.remove('shake-field', 'missing-field-highlight');
            void el.offsetWidth;
            el.classList.add('shake-field', 'missing-field-highlight');
            setTimeout(() => el.classList.remove('shake-field'), 650);
            setTimeout(() => el.classList.remove('missing-field-highlight'), 1800);
        };

        const validateStep1 = (shakeIfInvalid = false) => {
            if (!next) return false;
            const tourWrapper = document.getElementById('tourSelectWrapper');
            const tourIsRequired = !tourWrapper || (!tourWrapper.classList.contains('hidden') && !tourWrapper.classList.contains('d-none'));
            const tour = tourSelect ? tourSelect.value : '';
            const tier = tierInput ? tierInput.value : '';
            const date = dateInput ? dateInput.value : '';
            const loc = locationInput ? locationInput.value.trim() : '';

            const missing = [];
            const missingElements = [];

            if (tourIsRequired && !tour) {
                missing.push('Tour');
                missingElements.push(document.getElementById('tourSelectWrapper') || tourSelect);
            }
            if (!tier) {
                missing.push('Package');
                missingElements.push(document.getElementById('tierCards')?.parentElement || document.getElementById('tierCards'));
            }
            if (!date) {
                missing.push('Date');
                missingElements.push(document.getElementById('dateCardsWrapper')?.parentElement || document.getElementById('dateCardsWrapper'));
            }
            if (!loc) {
                missing.push('Pickup Location');
                const locBox = locationInput ? locationInput.closest('.booking-location-wrapper') || locationInput.closest('.booking-field-container') : null;
                missingElements.push(locBox || locationInput);
            }

            const adultsVal = parseInt(document.getElementById('bookingAdults')?.value, 10) || 1;
            const isOvernightTour = (tour === '3') || (tourSelect?.options[tourSelect.selectedIndex]?.dataset?.slug || '').includes('overnight');
            if (isOvernightTour && (adultsVal < 2 || adultsVal % 2 !== 0)) {
                missing.push('Guests (min 2, multiples of 2)');
                missingElements.push(document.getElementById('bookingAdults')?.parentElement);
            }

            if (missing.length === 0) {
                next.classList.remove('btn-disabled-visual', 'opacity-50');
                if (btnWrapper) btnWrapper.removeAttribute('title');
                return true;
            } else {
                next.classList.add('btn-disabled-visual');
                const msg = 'Please select: ' + missing.join(', ');
                if (btnWrapper) btnWrapper.setAttribute('title', msg);

                if (shakeIfInvalid) {
                    missingElements.forEach(el => triggerShakeAndHighlight(el));
                    if (missingElements.length > 0 && missingElements[0]) {
                        missingElements[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                    this.toast(msg, 'error');
                }
                return false;
            }
        };

        this.validateStep1 = validateStep1;
        validateStep1(false);

        tourSelect?.addEventListener('change', () => {
            this.syncTourGuestRules(tourSelect.value);
            this.loadTiers(tourSelect.value);
            validateStep1(false);
        });

        tierInput?.addEventListener('change', () => validateStep1(false));
        dateInput?.addEventListener('change', () => validateStep1(false));
        dateInput?.addEventListener('input', () => validateStep1(false));
        locationInput?.addEventListener('input', () => validateStep1(false));
        locationInput?.addEventListener('change', () => validateStep1(false));

        const handleContinueClick = (e) => {
            if (this.currentStep === 1) {
                const isValid = validateStep1(false);
                if (!isValid) {
                    if (e) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                    validateStep1(true);
                    return false;
                }
                this.nextStep();
            }
        };

        next?.addEventListener('click', handleContinueClick);
        btnWrapper?.addEventListener('click', (e) => {
            if (e.target !== next && !next?.contains(e.target)) {
                handleContinueClick(e);
            }
        });
        prev?.addEventListener('click', () => this.prevStep());
        edit?.addEventListener('click', (e) => {
            e.preventDefault();
            this.currentStep = 1;
            this.updateStep();
        });

        document.getElementById('detectLocation')?.addEventListener('click', () => this.detectLocation());

        // Address Autocomplete: Google Places with OpenStreetMap fallback
        if (locationInput) {
            this.initLocationAutocomplete(locationInput);
        }

        this.initDraftAutoSave();
    },

    initLocationAutocomplete(locationInput) {
        if (!locationInput || locationInput.dataset.autocompleteBound === 'true') return;
        locationInput.dataset.autocompleteBound = 'true';

        // 1. Google Places Autocomplete if available or loadable
        const initGooglePlaces = () => {
            if (typeof window.google === 'undefined' || !window.google.maps || !window.google.maps.places) return false;
            if (locationInput.dataset.mapsInitialized === 'true') return true;

            try {
                const autocomplete = new window.google.maps.places.Autocomplete(locationInput, {
                    types: ['establishment', 'geocode'],
                    fields: ['formatted_address', 'geometry', 'name'],
                    componentRestrictions: { country: 'ae' }
                });

                let dropdownState = 'IDLE';
                let lastSelectedValue = '';

                const updateDropdownState = () => {
                    document.querySelectorAll('.pac-container').forEach(pac => {
                        if (dropdownState === 'SEARCHING') {
                            pac.classList.remove('pac-force-hidden');
                        } else {
                            pac.classList.add('pac-force-hidden');
                        }
                    });
                };

                locationInput.addEventListener('keydown', (e) => {
                    if (e.key !== 'Tab' && e.key !== 'Enter') {
                        dropdownState = 'SEARCHING';
                        updateDropdownState();
                    }
                });

                locationInput.addEventListener('input', () => {
                    if (locationInput.value === lastSelectedValue) return;
                    dropdownState = locationInput.value.trim().length === 0 ? 'IDLE' : 'SEARCHING';
                    updateDropdownState();
                });

                autocomplete.addListener('place_changed', () => {
                    dropdownState = 'SELECTED';
                    updateDropdownState();

                    const place = autocomplete.getPlace();
                    if (place.formatted_address) {
                        locationInput.value = place.formatted_address;
                    } else if (place.name) {
                        locationInput.value = place.name;
                    }
                    lastSelectedValue = locationInput.value;
                    locationInput.dispatchEvent(new Event('input', { bubbles: true }));
                    locationInput.dispatchEvent(new Event('change', { bubbles: true }));
                    locationInput.blur();
                });

                locationInput.dataset.mapsInitialized = 'true';
                return true;
            } catch (err) {
                console.warn('Google Places init error:', err);
                return false;
            }
        };

        if (initGooglePlaces()) return;

        // 2. OpenStreetMap / Photon Fallback & Comprehensive Dubai Pickups
        const popularLocations = [
            { name: 'Dubai Marina', detail: 'Dubai Marina & JBR area, Dubai' },
            { name: 'Downtown Dubai', detail: 'Burj Khalifa, Dubai Mall & Downtown area' },
            { name: 'Palm Jumeirah', detail: 'Palm Jumeirah Island & Luxury Resorts, Dubai' },
            { name: 'Business Bay', detail: 'Business Bay & Canal area, Dubai' },
            { name: 'Jumeirah Beach Residence (JBR)', detail: 'The Walk, JBR Beach, Dubai' },
            { name: 'Al Barsha', detail: 'Mall of the Emirates & Al Barsha, Dubai' },
            { name: 'Jumeirah Lake Towers (JLT)', detail: 'JLT Clusters & Lakes, Dubai' },
            { name: 'Deira Dubai', detail: 'Deira Gold Souk & Creek area, Dubai' },
            { name: 'Bur Dubai', detail: 'Al Fahidi & Meena Bazaar area, Dubai' },
            { name: 'Atlantis, The Palm & The Royal', detail: 'Crescent Rd, Palm Jumeirah, Dubai' },
            { name: 'Burj Al Arab & Madinat Jumeirah', detail: 'Jumeirah Beach Rd, Dubai' },
            { name: 'Dubai International Airport (DXB)', detail: 'Terminals 1, 2 & 3, Dubai' },
            { name: 'Dubai Hills Estate', detail: 'Dubai Hills Mall & Boulevard, Dubai' },
            { name: 'Dubai Creek Harbour', detail: 'Ras Al Khor, Dubai' },
            { name: 'Bluewaters Island', detail: 'Ain Dubai, Bluewaters, Dubai' },
            { name: 'City Walk & Al Wasl', detail: 'Al Safa & City Walk area, Dubai' },
            { name: 'Jumeirah Village Circle (JVC)', detail: 'JVC & Al Barsha South, Dubai' },
            { name: 'Al Rigga & Deira City Centre', detail: 'Port Saeed, Deira, Dubai' },
            { name: 'Abu Dhabi City Center', detail: 'Corniche & Abu Dhabi Hotels' },
            { name: 'Yas Island, Abu Dhabi', detail: 'Yas Hotels & Theme Parks, Abu Dhabi' }
        ];

        const wrapper = locationInput.closest('.booking-location-wrapper') || locationInput.parentElement;
        if (getComputedStyle(wrapper).position === 'static') {
            wrapper.style.position = 'relative';
        }

        let dropdown = wrapper.querySelector('.osm-autocomplete-dropdown');
        if (!dropdown) {
            dropdown = document.createElement('div');
            dropdown.className = 'osm-autocomplete-dropdown shadow-2xl rounded-2xl border border-slate-200/90 bg-white overflow-hidden';
            dropdown.style.cssText = 'position:absolute; top:calc(100% + 6px); left:0; right:0; z-index:60; background:#ffffff; display:none; max-height:260px; overflow-y:auto; box-shadow:0 20px 40px -15px rgba(0,0,0,0.18); border-radius:16px; border:1px solid #e2e8f0;';
            wrapper.appendChild(dropdown);
        }

        let debounceTimer = null;

        const renderResults = (items) => {
            dropdown.innerHTML = '';
            // Only render and show if the input is actively focused
            if (!items || items.length === 0 || document.activeElement !== locationInput) {
                dropdown.style.display = 'none';
                return;
            }

            items.forEach(item => {
                const el = document.createElement('div');
                el.className = 'osm-autocomplete-item px-3.5 py-2.5 text-left flex items-center gap-3 border-b border-slate-100 last:border-b-0 hover:bg-orange-50/70 transition-colors cursor-pointer select-none';
                el.innerHTML = `
                    <div class="w-7 h-7 rounded-full bg-orange-100/80 text-primary flex items-center justify-center shrink-0 text-xs shadow-2xs">
                        <i class="bi bi-geo-alt-fill text-xs"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="font-extrabold text-slate-900 text-xs truncate leading-tight">${item.name}</div>
                        <div class="text-[11px] font-medium text-slate-500 truncate mt-0.5">${item.detail || 'Dubai, United Arab Emirates'}</div>
                    </div>
                    <i class="bi bi-arrow-up-left text-slate-300 text-xs shrink-0"></i>
                `;
                el.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    // Set chosen value
                    locationInput.value = item.name + (item.detail ? ', ' + item.detail : '');

                    // Immediately hide & clear dropdown
                    clearTimeout(debounceTimer);
                    dropdown.innerHTML = '';
                    dropdown.style.display = 'none';

                    // Mark that selection just occurred to prevent input event from re-opening
                    locationInput.dataset.justSelected = 'true';
                    locationInput.dispatchEvent(new Event('input', { bubbles: true }));
                    locationInput.dispatchEvent(new Event('change', { bubbles: true }));

                    // Blur input as selection is complete
                    locationInput.blur();

                    if (typeof this.validateStep1 === 'function') {
                        this.validateStep1(false);
                    }
                });
                dropdown.appendChild(el);
            });

            dropdown.style.display = 'block';
        };

        const showPopular = () => renderResults(popularLocations);

        const searchLocations = async (query) => {
            if (document.activeElement !== locationInput) {
                dropdown.style.display = 'none';
                return;
            }

            const q = query.trim().toLowerCase();
            // 1. Instant local filter
            const localMatches = popularLocations.filter(p => 
                p.name.toLowerCase().includes(q) || 
                p.detail.toLowerCase().includes(q)
            );

            if (localMatches.length > 0 && document.activeElement === locationInput) {
                renderResults(localMatches);
            }

            // 2. Fetch from Photon with lang=en for granular street/hotel addresses
            try {
                const url = `https://photon.komoot.io/api/?q=${encodeURIComponent(query)}&lat=25.2048&lon=55.2708&limit=8&lang=en`;
                const res = await fetch(url);
                if (res.ok) {
                    const data = await res.json();
                    if (data && data.features && data.features.length > 0 && document.activeElement === locationInput) {
                        const photonResults = data.features.map(f => {
                            const props = f.properties || {};
                            const name = props['name:en'] || props.name || props.street || query;
                            const parts = [props.district, props.city, props.country].filter(Boolean);
                            return {
                                name: name,
                                detail: parts.join(', ') || 'United Arab Emirates'
                            };
                        });

                        const seen = new Set();
                        const merged = [];
                        [...localMatches, ...photonResults].forEach(item => {
                            const key = item.name.toLowerCase().trim();
                            if (!seen.has(key)) {
                                seen.add(key);
                                merged.push(item);
                            }
                        });
                        if (merged.length > 0 && document.activeElement === locationInput) {
                            renderResults(merged.slice(0, 8));
                            return;
                        }
                    }
                }
            } catch (e) {}

            if (localMatches.length === 0 && document.activeElement === locationInput) {
                renderResults([{ name: query, detail: 'Dubai, UAE' }, ...popularLocations.slice(0, 4)]);
            }
        };

        locationInput.addEventListener('focus', () => {
            if (locationInput.dataset.justSelected === 'true') {
                return;
            }
            const val = locationInput.value.trim();
            if (val.length >= 2) {
                searchLocations(val);
            }
        });

        locationInput.addEventListener('input', () => {
            if (locationInput.dataset.justSelected === 'true') {
                locationInput.dataset.justSelected = 'false';
                dropdown.style.display = 'none';
                return;
            }

            if (document.activeElement !== locationInput) {
                dropdown.style.display = 'none';
                return;
            }

            const val = locationInput.value.trim();
            clearTimeout(debounceTimer);
            if (!val) {
                dropdown.style.display = 'none';
                return;
            }
            debounceTimer = setTimeout(() => {
                if (document.activeElement === locationInput) {
                    searchLocations(val);
                }
            }, 150);
        });

        locationInput.addEventListener('blur', () => {
            setTimeout(() => {
                dropdown.style.display = 'none';
            }, 180);
        });

        locationInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                dropdown.style.display = 'none';
            }
        });

        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    },

    initDraftAutoSave() {
        let draftTimer = null;
        let activeDraftId = null;

        const triggerDraftSave = () => {
            clearTimeout(draftTimer);
            draftTimer = setTimeout(async () => {
                const name = document.getElementById('bookingName')?.value.trim();
                const email = document.getElementById('bookingEmail')?.value.trim();
                const phoneEl = document.getElementById('bookingPhone');
                const phone = this.getFormattedPhone(phoneEl);

                if ((name && name.length >= 2) || (email && email.includes('@')) || (phone && phone.length >= 7)) {
                    const tourId = document.getElementById('bookingTour')?.value;
                    const tierId = document.getElementById('selectedTier')?.value || this.selectedTier;
                    const date = document.getElementById('bookingDate')?.value;
                    const adults = document.getElementById('bookingAdults')?.value || 1;
                    const children = document.getElementById('bookingChildren')?.value || 0;
                    const location = document.getElementById('bookingLocation')?.value;
                    const baseTotal = this.calculateBaseTotal();

                    try {
                        const res = await fetch('/api/v1/booking/draft', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify({
                                draft_id: activeDraftId,
                                name: name,
                                email: email,
                                phone: phone,
                                tour_id: tourId,
                                tier_id: tierId,
                                date: date,
                                adults: adults,
                                children: children,
                                pickup_location: location,
                                subtotal: baseTotal,
                                total: baseTotal
                            })
                        });
                        const data = await res.json();
                        if (data.success && data.draft_id) {
                            activeDraftId = data.draft_id;
                        }
                    } catch (e) {}
                }
            }, 800);
        };

        ['bookingName', 'bookingEmail', 'bookingPhone'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', triggerDraftSave);
        });
    },

    initPaymentOptions() {
        const container = document.getElementById('paymentOptions');
        if (!container) return;
        const options = container.querySelectorAll('.payment-option');
        const methodInput = document.getElementById('paymentMethod');
        const select = (val) => {
            options.forEach(o => o.classList.toggle('selected', o.dataset.value === val));
            if (methodInput) methodInput.value = val;
            this.updateTotal();
        };
        options.forEach(o => o.addEventListener('click', () => select(o.dataset.value)));
        select(methodInput?.value || options[0]?.dataset.value || 'cash');
    },

    syncTourGuestRules(tourId) {
        const adultsInput = document.getElementById('bookingAdults');
        const tourSelect = document.getElementById('bookingTour');
        if (!adultsInput) return;

        let isOvernight = false;
        if (tourId) {
            const sTourId = String(tourId);
            if (sTourId === '3') {
                isOvernight = true;
            } else if (tourSelect && tourSelect.selectedIndex >= 0) {
                const opt = tourSelect.options[tourSelect.selectedIndex];
                const slug = opt?.dataset?.slug || '';
                if (slug.includes('overnight')) {
                    isOvernight = true;
                }
            }
        }

        if (isOvernight) {
            adultsInput.min = '2';
            adultsInput.step = '2';
            let v = parseInt(adultsInput.value, 10) || 2;
            if (v < 2) v = 2;
            else if (v % 2 !== 0) v += 1;
            adultsInput.value = v;
        } else {
            adultsInput.min = '1';
            adultsInput.step = '1';
            let v = parseInt(adultsInput.value, 10) || 1;
            if (v < 1) v = 1;
            adultsInput.value = v;
        }
        if (typeof this.updateTotal === 'function') {
            this.updateTotal();
        }
    },

    async loadTiers(tourId) {
        this.syncTourGuestRules(tourId);
        const container = document.getElementById('tierCards');
        const addons = document.getElementById('addonsSection');
        const addonList = document.getElementById('addonList');

        if (!container || !tourId) {
            if (container) container.innerHTML = '<div class="tier-placeholder">Select a tour to see packages</div>';
            if (addons) addons.style.display = 'none';
            this.selectedTier = null;
            const tInp = document.getElementById('selectedTier');
            if (tInp) { tInp.value = ''; tInp.dispatchEvent(new Event('change', { bubbles: true })); }
            this.selectedPrice = 0;
            this.selectedAddons = [];
            this.updateTotal();
            return;
        }

        container.innerHTML = '<div class="tier-placeholder"><div class="spinner-border text-primary mb-2" role="status"></div><div>Loading packages...</div></div>';

        this.selectedTier = null;
        const tInp2 = document.getElementById('selectedTier');
        if (tInp2) { tInp2.value = ''; tInp2.dispatchEvent(new Event('change', { bubbles: true })); }
        this.selectedPrice = 0;
        this.selectedAddons = [];
        this.updateTotal();

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.CSRF_TOKEN || '';
            const res = await fetch('/ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: 'action=getTiers&tour_id=' + tourId + '&_token=' + encodeURIComponent(csrfToken)
            });
            const raw = await res.text();
            const data = JSON.parse(raw.replace(/^\uFEFF+/, '').trim());

            if (data.tiers?.length) {
                let h = '';
                data.tiers.forEach(t => {
                    const save = t.old_price > t.price ? Math.round(((t.old_price - t.price) / t.old_price) * 100) : 0;
                    const pType = (t.price_type || 'per person').toLowerCase();
                    let guestCount = parseInt(t.guest_count || t.capacity || 0, 10);
                    if (guestCount <= 0) {
                        if (/(6|six)/i.test(t.name || '')) guestCount = 6;
                        else if (/(4|four)/i.test(t.name || '')) guestCount = 4;
                        else if (/(2|two)/i.test(t.name || '')) guestCount = 2;
                        else if (/(1|single|solo)/i.test(t.name || '')) guestCount = 1;
                        else guestCount = 1;
                    }
                    h += `<div class="tier-card${t.is_popular ? ' popular' : ''}" data-tier="${t.id}" data-price="${t.price}" data-child-price="${t.child_price || 0}" data-name="${t.name}" data-price-type="${pType}" data-guest-count="${guestCount}" data-capacity="${guestCount}">
                        ${t.is_popular ? '<div class="tier-popular-badge">Popular</div>' : ''}
                        <div class="tier-card-inner">
                            <div class="tier-card-info">
                                <h4>${t.name}</h4>
                                <p>${t.description || ''}</p>
                            </div>
                            <div class="tier-card-right">
                                <div class="tier-card-price">
                                    ${save ? `<div class="old" data-aed="${t.old_price}">AED ${t.old_price}</div>` : ''}
                                    <div class="current" data-aed="${t.price}">AED ${t.price}</div>
                                </div>
                                <div class="tier-card-check"><i class="bi bi-check-lg"></i></div>
                            </div>
                        </div>
                    </div>`;
                });
                container.innerHTML = h;

                container.querySelectorAll('.tier-card').forEach(c => {
                    c.addEventListener('click', () => this.selectTier(c));
                    if (this.preselectedTierId && c.dataset.tier === this.preselectedTierId) {
                        this.selectTier(c);
                    }
                });

                const pendingCode = window.pendingPromoCode || document.getElementById('bookingPromoInput')?.value || document.getElementById('bookingPromoCode')?.value;
                if (pendingCode && !window.appliedPromoCoupon && typeof window.validateCurrentPromo === 'function') {
                    window.validateCurrentPromo();
                }
            } else {
                container.innerHTML = '<div class="tier-placeholder">No packages available for this tour.</div>';
            }

            if (data.addons?.length) {
                let ah = '';
                data.addons.forEach(a => {
                    const iconName = a.icon ? (a.icon.startsWith('bi-') ? a.icon : 'bi-' + a.icon) : 'bi-plus-circle';
                    const visualHtml = a.image 
                        ? `<img src="/images/${a.image}" alt="${a.name}" class="w-8 h-8 rounded-xl object-cover shrink-0 border border-orange-200/60 shadow-2xs" loading="lazy">`
                        : `<div class="w-8 h-8 rounded-xl bg-orange-50 text-primary border border-orange-200/60 flex items-center justify-center shrink-0 text-sm"><i class="${iconName}"></i></div>`;
                    ah += `<div class="addon-card-horizontal group relative flex flex-col justify-between p-3.5 sm:p-4 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-primary/60 hover:shadow-md transition-all cursor-pointer select-none shrink-0" data-addon="${a.id}" data-price="${a.price}">
                        <input type="checkbox" name="addons[]" value="${a.id}" class="sr-only">
                        <div class="addon-check-abs hidden absolute top-3 right-3 w-5 h-5 rounded-full bg-emerald-500 text-white items-center justify-center text-[10px] font-black shadow-2xs">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5 mb-2 pr-6">
                                ${visualHtml}
                                <h5 class="font-extrabold text-slate-900 text-xs sm:text-sm leading-tight truncate" title="${a.name}">${a.name}</h5>
                            </div>
                            <p class="text-[11px] text-slate-500 font-medium line-clamp-2 mb-3 leading-relaxed min-h-[32px]">${a.description || 'Optional safari enhancement'}</p>
                        </div>
                        <div class="mt-auto pt-2.5 border-t border-slate-100 flex items-center justify-between gap-2">
                            <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-800 font-black text-xs font-mono border border-slate-200/80 shadow-2xs" data-aed="${a.price}" data-is-addon="true">+AED ${parseFloat(a.price).toFixed(2)}</span>
                            <span class="addon-status-label inline-flex items-center gap-1 text-[11px] font-extrabold text-primary bg-orange-50 hover:bg-orange-100 border border-orange-200/80 px-2.5 py-0.5 rounded-full transition-all">+ Add</span>
                        </div>
                    </div>`;
                });
                addonList.innerHTML = ah;
                addonList.style.display = 'flex';
                if (addons) addons.style.display = 'block';

                Alpine.store('currency').convertAllPrices();

                addonList.querySelectorAll('.addon-card-horizontal').forEach(item => {
                    item.addEventListener('click', e => {
                        if (e.target.tagName !== 'INPUT') {
                            const inp = item.querySelector('input');
                            if (inp) inp.checked = !inp.checked;
                        }
                        const isChecked = item.querySelector('input')?.checked || false;
                        item.classList.toggle('selected', isChecked);
                        const checkBadge = item.querySelector('.addon-check-abs');
                        if (checkBadge) {
                            if (isChecked) {
                                checkBadge.classList.remove('hidden');
                                checkBadge.classList.add('flex');
                            } else {
                                checkBadge.classList.add('hidden');
                                checkBadge.classList.remove('flex');
                            }
                        }
                        const statusLabel = item.querySelector('.addon-status-label');
                        if (statusLabel) {
                            if (isChecked) {
                                statusLabel.textContent = '✓ Added';
                                statusLabel.className = 'addon-status-label inline-flex items-center gap-1 text-[11px] font-black text-white bg-emerald-500 border border-emerald-600 px-2.5 py-0.5 rounded-full shadow-2xs transition-all';
                            } else {
                                statusLabel.textContent = '+ Add';
                                statusLabel.className = 'addon-status-label inline-flex items-center gap-1 text-[11px] font-extrabold text-primary bg-orange-50 border border-orange-200/80 px-2.5 py-0.5 rounded-full transition-all';
                            }
                        }
                        this.updateAddons();
                    });
                });
            } else {
                if (addons) addons.style.display = 'none';
            }
        } catch (e) {
            container.innerHTML = '<div class="tier-placeholder text-danger"><i class="bi bi-exclamation-circle me-2"></i>Error loading packages</div>';
        }
    },

    selectTier(card) {
        document.querySelectorAll('.tier-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        const tierInput = document.getElementById('selectedTier');
        if (tierInput) {
            tierInput.value = card.dataset.tier;
            tierInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
        this.selectedTier = card.dataset.tier;
        this.selectedPrice = parseFloat(card.dataset.price);

        const targetGuests = parseInt(card.dataset.guestCount || card.dataset.capacity, 10);
        const bookingAdults = document.getElementById('bookingAdults');
        if (bookingAdults && targetGuests > 0) {
            bookingAdults.value = targetGuests;
        }

        this.updateTotal();

        const pendingCode = window.pendingPromoCode || document.getElementById('bookingPromoInput')?.value || document.getElementById('bookingPromoCode')?.value;
        if (pendingCode && !window.appliedPromoCoupon && typeof window.validateCurrentPromo === 'function') {
            window.validateCurrentPromo();
        }

        if (window.innerWidth < 992) {
            card.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
    },

    updateAddons() {
        this.selectedAddons = [];
        document.querySelectorAll('.addon-card-horizontal input:checked').forEach(inp => {
            const item = inp.closest('.addon-card-horizontal');
            this.selectedAddons.push({
                id: inp.value,
                price: parseFloat(item.dataset.price)
            });
        });
        this.updateTotal();
    },

    calculateBaseTotal() {
        const adults = parseInt(document.getElementById('bookingAdults')?.value) || 1;
        const children = parseInt(document.getElementById('bookingChildren')?.value) || 0;
        let price = this.selectedPrice || 0;
        const selectedTierCard = document.querySelector('.tier-card.selected');
        if (price <= 0 && selectedTierCard && selectedTierCard.dataset.price) {
            price = parseFloat(selectedTierCard.dataset.price) || 0;
            this.selectedPrice = price;
        }
        const priceType = (selectedTierCard?.dataset.priceType || 'per person').toLowerCase();
        const rawChildPrice = parseFloat(selectedTierCard?.dataset.childPrice || 0);
        const childPrice = rawChildPrice > 0 ? rawChildPrice : (price * 0.70);
        let total = 0;
        if (['per buggy', 'per vehicle', 'per group', 'private', 'per tent', 'per package'].includes(priceType)) {
            let capacity = parseInt(selectedTierCard?.dataset.capacity || selectedTierCard?.dataset.guestCount, 10) || 0;
            if (capacity <= 0) {
                if (/(6|six)/i.test(selectedTierCard?.dataset.name || '')) capacity = 6;
                else if (/(4|four)/i.test(selectedTierCard?.dataset.name || '')) capacity = 4;
                else if (/(2|two)/i.test(selectedTierCard?.dataset.name || '')) capacity = 2;
                else if (/(1|single|solo)/i.test(selectedTierCard?.dataset.name || '')) capacity = 1;
                else capacity = 2;
            }
            const vehicles = Math.ceil(Math.max(1, adults + children) / capacity);
            total = price * vehicles;
        } else {
            total = (price * adults) + (childPrice * children);
        }
        this.selectedAddons.forEach(a => total += a.price);
        return total;
    },

    updateTotal() {
        const adults = parseInt(document.getElementById('bookingAdults')?.value) || 1;
        const children = parseInt(document.getElementById('bookingChildren')?.value) || 0;
        const selectedTierCard = document.querySelector('.tier-card.selected');
        const priceType = (selectedTierCard?.dataset.priceType || 'per person').toLowerCase();
        const rawChildPrice = parseFloat(selectedTierCard?.dataset.childPrice || 0);
        const childPrice = rawChildPrice > 0 ? rawChildPrice : (this.selectedPrice * 0.70);

        let baseTotal = 0;
        if (['per buggy', 'per vehicle', 'per group', 'private', 'per tent', 'per package'].includes(priceType)) {
            let capacity = parseInt(selectedTierCard?.dataset.capacity || selectedTierCard?.dataset.guestCount, 10) || 0;
            if (capacity <= 0) {
                if (/(6|six)/i.test(selectedTierCard?.dataset.name || '')) capacity = 6;
                else if (/(4|four)/i.test(selectedTierCard?.dataset.name || '')) capacity = 4;
                else if (/(2|two)/i.test(selectedTierCard?.dataset.name || '')) capacity = 2;
                else if (/(1|single|solo)/i.test(selectedTierCard?.dataset.name || '')) capacity = 1;
                else capacity = 2;
            }
            const vehicles = Math.ceil(Math.max(1, adults + children) / capacity);
            baseTotal = this.selectedPrice * vehicles;
        } else {
            baseTotal = (this.selectedPrice * adults) + (childPrice * children);
        }
        this.selectedAddons.forEach(a => baseTotal += a.price);

        // Apply promo coupon if active
        let discount = 0;
        if (window.appliedPromoCoupon) {
            const c = window.appliedPromoCoupon;
            if (c.discount_type === 'percentage') {
                discount = (baseTotal * parseFloat(c.discount_value)) / 100;
                if (c.max_discount && discount > parseFloat(c.max_discount)) {
                    discount = parseFloat(c.max_discount);
                }
            } else if (c.discount_type === 'fixed') {
                discount = Math.min(baseTotal, parseFloat(c.discount_value));
            } else if (c.discount_type === 'per_person') {
                discount = parseFloat(c.discount_value) * (adults + children);
            }
            discount = Math.min(baseTotal, Math.max(0, discount));

            const promoSavingsText = document.getElementById('promoSavingsText');
            if (promoSavingsText) {
                promoSavingsText.textContent = `AED ${discount.toFixed(2)} saved (${c.discount_type === 'percentage' ? Math.round(c.discount_value) + '% OFF' : 'Discount Applied'})`;
            }
        }

        let total = Math.max(0, baseTotal - discount);

        const totalEl = document.getElementById('bookingTotal');
        const summaryTotalEl = document.getElementById('summaryTotal');
        const method = document.getElementById('paymentMethod')?.value || 'cash';
        const payInput = document.getElementById('paymentAmount');
        const container = document.getElementById('paymentOptions');
        const advancePercent = parseFloat(container?.dataset.advancePercent) || 0;
        let payNow = 0;
        if (method === 'advance') payNow = (total * advancePercent) / 100;
        else if (method === 'full' || method === 'cash') payNow = total;

        const formatMoney = (v) => Alpine.store('currency').formatDisplayPrice(v);
        if (totalEl) {
            totalEl.innerHTML = formatMoney(total);
            totalEl.setAttribute('data-aed', total);
        }
        if (summaryTotalEl) {
            if (discount > 0) {
                summaryTotalEl.innerHTML = `<span class="line-through text-slate-400 text-xs me-2">${formatMoney(baseTotal)}</span> <span class="text-emerald-600 font-extrabold">${formatMoney(total)}</span>`;
            } else {
                summaryTotalEl.innerHTML = formatMoney(total);
            }
        }
        if (payInput) payInput.value = Number(payNow).toFixed(2);

        const submitBtn = document.getElementById('submitBooking');
        if (submitBtn) {
            if (method === 'cash') {
                submitBtn.innerHTML = '<span>Confirm Booking</span> <i class="bi bi-check-lg text-lg"></i>';
            } else {
                const cur = Alpine.store('currency').code;
                const converted = payNow * (Alpine.store('currency').rates[cur] || 1);
                const approx = cur !== 'AED' ? ` (~ ${Alpine.store('currency').symbols[cur]}${Math.round(converted)})` : '';
                submitBtn.innerHTML = `<span>Pay AED ${Number(payNow).toFixed(2)}${approx}</span> <i class="bi bi-credit-card text-lg"></i>`;
            }
        }
    },

    nextStep() {
        if (!this.validateStep(this.currentStep)) return;
        if (this.currentStep < 2) {
            if (window.dataLayer) {
                const tourSel = document.getElementById('bookingTour');
                const tourName = tourSel ? tourSel.options[tourSel.selectedIndex]?.text : '-';
                const tierCard = document.querySelector('.tier-card.selected');
                const tierName = tierCard ? tierCard.dataset.name : '-';
                const adults = parseInt(document.getElementById('bookingAdults')?.value) || 1;
                const children = parseInt(document.getElementById('bookingChildren')?.value) || 0;

                let total = (this.selectedPrice * adults) + (this.selectedPrice * 0.7 * children);
                this.selectedAddons.forEach(a => total += a.price);

                window.dataLayer.push({ ecommerce: null });
                window.dataLayer.push({
                    event: 'begin_checkout',
                    ecommerce: {
                        currency: 'AED',
                        value: total,
                        items: [{
                            item_id: this.selectedTier,
                            item_name: tourName + ' - ' + tierName,
                            price: this.selectedPrice,
                            quantity: adults + children,
                            item_category: 'Tours',
                            item_variant: tierName
                        }]
                    }
                });
            }

            this.currentStep++;
            this.updateStep();
        }
    },

    prevStep() {
        if (this.currentStep > 1) {
            this.currentStep--;
            this.updateStep();
        }
    },

    updateStep() {
        document.querySelectorAll('.step-content').forEach(s => {
            const stepNum = parseInt(s.dataset.step);
            if (stepNum === this.currentStep) {
                s.classList.remove('hidden', 'd-none');
                s.classList.add('active');
            } else {
                s.classList.add('hidden', 'd-none');
                s.classList.remove('active');
            }
        });

        const subtitle = document.getElementById('bookingModalSubtitle');
        if (subtitle) {
            subtitle.textContent = `Step ${this.currentStep} of 2`;
            subtitle.classList.remove('hidden', 'd-none');
        }

        const backBtn = document.getElementById('headerBackBtn');
        if (backBtn) {
            if (this.currentStep <= 1) {
                backBtn.classList.add('hidden', 'd-none');
            } else {
                backBtn.classList.remove('hidden', 'd-none');
            }
        }

        const nextBtn = document.getElementById('nextStep');
        const submitBtn = document.getElementById('submitBooking');

        if (nextBtn) {
            if (this.currentStep < 2) {
                nextBtn.classList.remove('hidden', 'd-none');
                nextBtn.classList.add('inline-flex', 'd-inline-flex');
            } else {
                nextBtn.classList.add('hidden', 'd-none');
                nextBtn.classList.remove('inline-flex', 'd-inline-flex');
            }
        }
        if (submitBtn) {
            if (this.currentStep === 2) {
                submitBtn.classList.remove('hidden', 'd-none');
                submitBtn.classList.add('inline-flex', 'd-inline-flex');
            } else {
                submitBtn.classList.add('hidden', 'd-none');
                submitBtn.classList.remove('inline-flex', 'd-inline-flex');
            }
        }

        if (this.currentStep === 2) {
            this.updateSummary();
            this.updateTotal();
            const emailInput = document.getElementById('bookingEmail');
            if (emailInput && window.DunesConfig?.email_verification_required) {
                if (!emailInput.value.trim()) {
                    try {
                        const savedEmail = localStorage.getItem('dunes_guest_email') || '';
                        if (savedEmail) emailInput.value = savedEmail;
                    } catch (e) {}
                }
                if (emailInput.value.trim()) {
                    this.checkEmailStatus(emailInput);
                }
            }
        }
    },

    updateSummary() {
        const tourSel = document.getElementById('bookingTour');
        const tierCard = document.querySelector('.tier-card.selected');
        const tourName = tourSel ? tourSel.options[tourSel.selectedIndex]?.text : '-';
        const tierName = tierCard ? tierCard.dataset.name : '-';

        const sTour = document.getElementById('summaryTourName');
        const sTier = document.getElementById('summaryTierName');

        if (sTour) sTour.textContent = tourName;
        if (sTier) sTier.textContent = tierName;
    },

    validateStep(step) {
        if (step === 1) {
            return this.validateStep1 ? this.validateStep1(true) : true;
        }
        if (step === 2) {
            if (!document.getElementById('paymentMethod')?.value) {
                this.toast('Please select a payment option', 'error');
                return false;
            }
        }
        return true;
    },

    resetForm() {
        document.getElementById('bookingForm')?.reset();
        const emailInput = document.getElementById('bookingEmail');
        if (emailInput) this.resetFieldState(emailInput);
        const phoneInput = document.getElementById('bookingPhone');
        if (phoneInput) this.setPhoneFieldState(phoneInput, true);
        const errEl = document.getElementById('bookingError');
        if (errEl) {
            errEl.classList.add('hidden', 'd-none');
            errEl.textContent = '';
        }

        const tierCards = document.getElementById('tierCards');
        if (tierCards) tierCards.innerHTML = '<div class="tier-placeholder">Select a tour to see packages</div>';
        const selTier = document.getElementById('selectedTier');
        if (selTier) selTier.value = '';
        const bTotal = document.getElementById('bookingTotal');
        if (bTotal) {
            bTotal.textContent = 'AED 0.00';
            bTotal.setAttribute('data-aed', '0');
        }
        const pMethod = document.getElementById('paymentMethod');
        if (pMethod) pMethod.value = 'cash';
        const pAmt = document.getElementById('paymentAmount');
        if (pAmt) pAmt.value = '0';

        const paymentContainer = document.getElementById('paymentOptions');
        if (paymentContainer) {
            paymentContainer.querySelectorAll('.payment-option').forEach(o => o.classList.toggle('selected', o.dataset.value === 'cash'));
        }
        const addons = document.getElementById('addonsSection');
        if (addons) addons.style.display = 'none';

        this.currentStep = 1;
        this.selectedTier = null;
        this.selectedPrice = 0;
        this.selectedAddons = [];
        this.syncTourGuestRules(null);
        this._pendingSubmitAfterOtp = null;
        window.pendingPromoCode = null;
        if (typeof window.removeCurrentPromo === 'function') {
            window.removeCurrentPromo(true);
        }

        const titleEl = document.getElementById('bookingModalTitle');
        const wrapper = document.getElementById('tourSelectWrapper');
        if (titleEl) titleEl.textContent = 'Book Your Adventure';
        if (wrapper) wrapper.classList.remove('hidden', 'd-none');

        this.updateStep();

        const dateWrapper = document.getElementById('dateCardsWrapper');
        if (dateWrapper) dateWrapper.querySelectorAll('.date-card').forEach(c => c.classList.remove('selected'));
    },

    async detectLocation() {
        const inp = document.getElementById('bookingLocation');
        const btn = document.getElementById('detectLocation');
        const gpsLat = document.getElementById('gpsLat');
        const gpsLng = document.getElementById('gpsLng');
        const gpsAcc = document.getElementById('gpsAccuracy');
        const gpsTs = document.getElementById('gpsTimestamp');
        const gpsConsent = document.getElementById('gpsConsent');
        const gpsSource = document.getElementById('gpsSource');
        const gpsAddr = document.getElementById('gpsAddress');

        if (!inp || !btn) return;

        const orig = btn.innerHTML;
        if (gpsConsent) gpsConsent.value = 'Requested';
        if (gpsSource) gpsSource.value = 'GPS (User Consented)';
        btn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i>';
        btn.disabled = true;

        const resetUi = () => {
            btn.innerHTML = orig;
            btn.disabled = false;
        };

        try {
            if ('geolocation' in navigator) {
                const pos = await new Promise((res, rej) => navigator.geolocation.getCurrentPosition(res, rej, { timeout: 10000 }));
                if (gpsLat) gpsLat.value = pos.coords.latitude;
                if (gpsLng) gpsLng.value = pos.coords.longitude;
                if (gpsAcc) gpsAcc.value = typeof pos.coords.accuracy === 'number' ? String(pos.coords.accuracy) : 'Not Available';
                if (gpsTs) gpsTs.value = pos.timestamp ? String(pos.timestamp) : String(Date.now());
                if (gpsConsent) gpsConsent.value = 'Yes';
                if (gpsSource) gpsSource.value = 'GPS (User Consented)';

                const addr = await this.reverseGeocode(pos.coords.latitude, pos.coords.longitude);
                if (addr) {
                    inp.value = addr;
                    inp.dispatchEvent(new Event('change', { bubbles: true }));
                    if (gpsAddr) gpsAddr.value = addr;
                }
                this.toast('Location detected', 'success');
            } else {
                if (gpsConsent) gpsConsent.value = 'Not Available';
                if (gpsSource) gpsSource.value = 'Not Available';
                await this.ipLocation(inp);
            }
        } catch (e) {
            if (gpsConsent) gpsConsent.value = 'Denied/Failed';
            if (gpsSource) gpsSource.value = 'Not Available';
            if (gpsLat) gpsLat.value = '';
            if (gpsLng) gpsLng.value = '';
            if (gpsAcc) gpsAcc.value = 'Not Available';
            if (gpsTs) gpsTs.value = 'Not Available';
            await this.ipLocation(inp);
        }
        resetUi();
    },

    async reverseGeocode(lat, lng) {
        try {
            const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
            const t = await res.text();
            const d = JSON.parse(t.replace(/^\uFEFF+/, '').trim());
            if (d?.address) {
                const a = d.address;
                return [a.road, a.suburb || a.neighbourhood, a.city || a.town].filter(Boolean).join(', ');
            }
        } catch (e) {}
        return null;
    },

    async ipLocation(inp) {
        try {
            const res = await fetch('/api/v1/geoip');
            if (res.ok) {
                const d = await res.json();
                const locCity = d.city || d.region;
                if (locCity) {
                    inp.value = [d.city, d.region].filter(Boolean).join(', ');
                    inp.dispatchEvent(new Event('input', { bubbles: true }));
                    inp.dispatchEvent(new Event('change', { bubbles: true }));
                    if (typeof this.validateStep1 === 'function') {
                        this.validateStep1(false);
                    }
                    this.toast('Approximate location detected', 'success');
                    return;
                }
            }
        } catch (e) {}
        inp.value = 'Dubai, UAE';
        inp.dispatchEvent(new Event('input', { bubbles: true }));
        inp.dispatchEvent(new Event('change', { bubbles: true }));
        if (typeof this.validateStep1 === 'function') {
            this.validateStep1(false);
        }
    },

    initForms() {
        document.querySelectorAll('form').forEach(form => {
            const method = (form.getAttribute('method') || 'GET').toUpperCase();
            const rawAction = form.getAttribute('action') || '';

            // Strictly exclude GET forms, authentication, admin CMS, search, review, newsletter, and unsubscribe forms
            if (
                method === 'GET' ||
                form.matches('form[action*="login"], form[action*="password"], form[action*="register"], form[action*="logout"], #logout-form, .auth-form, .admin-form, #welcomeOfferForm, #globalSearchForm, #publicNewsletterForm, #reviewSubmitForm') ||
                form.closest('.auth-card') ||
                rawAction.includes('/search') ||
                rawAction.includes('/review') ||
                rawAction.includes('/unsubscribe') ||
                rawAction.includes('/newsletter') ||
                window.location.pathname.startsWith('/login') ||
                window.location.pathname.startsWith('/admin') ||
                window.location.pathname.startsWith('/password')
            ) {
                return;
            }

            const isTargetAjaxForm =
                form.matches('#bookingForm, #contactForm, #whatsappModalForm, #whatsappForm, #quickPaymentForm') ||
                !!form.querySelector('input[name="action"]') ||
                rawAction === '/ajax.php' ||
                rawAction.endsWith('/booking/checkout');

            if (!isTargetAjaxForm) {
                return;
            }

            form.addEventListener('submit', async e => {
                e.preventDefault();

                const errEl = document.getElementById('bookingError');
                if (errEl) {
                    errEl.classList.add('hidden', 'd-none');
                    errEl.textContent = '';
                }

                if (typeof window.validateForm === 'function') {
                    if (!window.validateForm(form, null, true)) {
                        if (errEl) {
                            errEl.textContent = 'Please complete all required fields before continuing.';
                            errEl.classList.remove('hidden', 'd-none');
                        } else {
                            this.toast('Please complete all required fields before continuing.', 'error');
                        }
                        return;
                    }
                } else if (!form.checkValidity()) {
                    const invalidFields = form.querySelectorAll(':invalid');
                    invalidFields.forEach(field => this.showError(field, false));
                    if (invalidFields.length > 0) invalidFields[0].focus();
                    if (errEl) {
                        errEl.textContent = 'Please complete all required fields before continuing.';
                        errEl.classList.remove('hidden', 'd-none');
                    }
                    return;
                }

                // Resilient International Phone / WhatsApp Validation & Normalization
                let phoneError = null;
                const phoneInputs = form.querySelectorAll('input[type="tel"], #bookingPhone, #waPhone, #welcomePhone, #phone');
                phoneInputs.forEach(inp => {
                    const val = inp.value.trim();
                    if (inp.hasAttribute('required') || val !== '') {
                        if (!this.isValidPhone(inp)) {
                            phoneError = 'Please enter a valid phone or WhatsApp number.';
                            this.setPhoneFieldState(inp, false);
                            this.showError(inp, false);
                            inp.focus();
                        } else {
                            this.setPhoneFieldState(inp, true);
                        }
                    }
                });

                if (phoneError) {
                    if (errEl) {
                        errEl.textContent = phoneError;
                        errEl.classList.remove('hidden', 'd-none');
                    } else {
                        this.toast(phoneError, 'error');
                    }
                    return;
                }

                // Interactive Email OTP Verification Check for Booking Flow
                const emailInput = form.querySelector('#bookingEmail');
                if (emailInput && window.DunesConfig?.email_verification_required && !emailInput.classList.contains('is-verified') && emailInput.dataset.isVerified !== 'true') {
                    const emailVal = emailInput.value.trim().toLowerCase();
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                    if (!emailVal || !emailRegex.test(emailVal)) {
                        this.setEmailBadge('invalid', 'Invalid Email');
                        this.showError(emailInput, false);
                        emailInput.focus();
                        return;
                    }

                    const otpWrapper = document.getElementById('otpFieldsWrapper');
                    const digitBoxes = Array.from(document.querySelectorAll('#otpDigitsContainer .otp-digit-box'));
                    const enteredOtp = digitBoxes.map(b => b.value).join('').replace(/\D/g, '');

                    if (otpWrapper && !otpWrapper.classList.contains('hidden')) {
                        this._pendingSubmitAfterOtp = form;
                        if (enteredOtp.length === 6) {
                            await this.verifyOtpCode(emailInput);
                        } else {
                            const otpErr = document.getElementById('otpErrorMsg');
                            const otpErrTxt = document.getElementById('otpErrorText');
                            if (otpErrTxt) otpErrTxt.textContent = 'Please enter the 6-digit verification code sent to your email.';
                            if (otpErr) {
                                otpErr.classList.remove('hidden');
                                otpErr.classList.add('flex');
                            }
                            otpWrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                            const firstEmpty = digitBoxes.find(b => !b.value) || digitBoxes[0];
                            firstEmpty?.focus();
                        }
                        return;
                    }

                    // Check if already verified in session/database before requesting OTP
                    try {
                        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.CSRF_TOKEN || '';
                        const checkRes = await fetch('/ajax.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-CSRF-TOKEN': csrf
                            },
                            body: `action=check_email_status&email=${encodeURIComponent(emailVal)}&_token=${encodeURIComponent(csrf)}`
                        });
                        const checkRaw = await checkRes.text();
                        const checkData = JSON.parse(checkRaw.replace(/^\uFEFF+/, '').trim());
                        if (checkData.success && checkData.verified) {
                            emailInput.classList.add('is-verified', 'field-valid');
                            emailInput.dataset.isVerified = 'true';
                            this.setEmailBadge('verified', 'Verified');
                        } else {
                            this._pendingSubmitAfterOtp = form;
                            await this.startVerification(emailInput);
                            return;
                        }
                    } catch (e) {
                        this._pendingSubmitAfterOtp = form;
                        await this.startVerification(emailInput);
                        return;
                    }
                }

                const btn = form.querySelector('[type="submit"]');
                const orig = btn?.innerHTML;
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i>Processing...';
                }

                try {
                    const fd = new FormData(form);
                    const action = String(fd.get('action') || '');

                    // Inject E.164 formatted phone numbers into FormData without mutating visual input
                    phoneInputs.forEach(inp => {
                        const formatted = this.getFormattedPhone(inp);
                        if (formatted && inp.name) {
                            fd.set(inp.name, formatted);
                            try { localStorage.setItem('dunes_guest_phone', formatted); } catch (e) {}
                        }
                    });
                    if (fd.get('name')) {
                        try { localStorage.setItem('dunes_guest_name', String(fd.get('name'))); } catch (e) {}
                    }

                    // Google reCAPTCHA Enterprise
                    if ((action === 'booking' || action === 'contact' || action === 'logWhatsApp') && window.RECAPTCHA_SITE_KEY) {
                        try {
                            if (typeof window.grecaptcha === 'undefined' || !window.grecaptcha.enterprise || typeof window.grecaptcha.enterprise.execute !== 'function') {
                                await new Promise(resolve => {
                                    const s = document.createElement('script');
                                    s.src = 'https://www.google.com/recaptcha/enterprise.js?render=' + window.RECAPTCHA_SITE_KEY;
                                    s.async = true;
                                    s.onload = resolve;
                                    document.head.appendChild(s);
                                });
                            }
                            await new Promise(r => window.grecaptcha.enterprise.ready(r));
                            const token = await window.grecaptcha.enterprise.execute(window.RECAPTCHA_SITE_KEY, { action });
                            fd.set('g-recaptcha-response', token);
                        } catch (e) {
                            console.error('ReCaptcha error:', e);
                            if (errEl) {
                                errEl.textContent = 'Security verification failed. Please refresh and try again.';
                                errEl.classList.remove('hidden', 'd-none');
                            } else {
                                this.toast('Security verification failed. Please refresh and try again.', 'error');
                            }
                            return;
                        }
                    }

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.CSRF_TOKEN || '';
                    if (!fd.has('_token') && csrfToken) {
                        fd.append('_token', csrfToken);
                    }

                    const endpoint = form.getAttribute('action') || '/ajax.php';
                    const res = await fetch(endpoint, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        body: fd
                    });
                    const raw = await res.text();
                    let data = null;
                    try { data = JSON.parse(raw.replace(/^\uFEFF+/, '').trim()); } catch (e) { data = null; }

                    if (!data) {
                        if (errEl) {
                            errEl.textContent = 'Payment could not be initiated. Please try again.';
                            errEl.classList.remove('hidden', 'd-none');
                        } else {
                            this.toast('Payment could not be initiated. Please try again.', 'error');
                        }
                        return;
                    }

                    if (data.success) {
                        const targetUrl = data.redirect_url || (data.reference ? ('/thankyou?ref=' + encodeURIComponent(data.reference)) : null);

                        // Engagement & Lead tracking for contact / lead forms (NEVER Google Ads booking conversion)
                        if (action === 'logWhatsApp') {
                            if (typeof window.gtag === 'function') {
                                window.gtag('event', 'generate_lead', { form_name: 'whatsapp_lead_form' });
                            }
                            if (window.dataLayer) {
                                window.dataLayer.push({
                                    event: 'generate_lead',
                                    form_name: 'whatsapp_lead_form'
                                });
                            }
                            if (typeof window.fbq === 'function') {
                                window.fbq('track', 'Lead', { content_name: 'WhatsApp Lead Form' });
                            }
                        } else if (action === 'contact' || action === 'submit_contact') {
                            if (typeof window.gtag === 'function') {
                                window.gtag('event', 'generate_lead', { form_name: 'contact_form' });
                            }
                            if (window.dataLayer) {
                                window.dataLayer.push({
                                    event: 'generate_lead',
                                    form_name: 'contact_form'
                                });
                            }
                            if (typeof window.fbq === 'function') {
                                window.fbq('track', 'Lead', { content_name: 'Contact Form' });
                            }
                        }

                        if (targetUrl) {
                            if (action === 'logWhatsApp') {
                                Alpine.store('modal').close();
                                form.reset();
                                const win = window.open(targetUrl, '_blank');
                                if (!win) {
                                    window.location.href = targetUrl;
                                }
                                return;
                            }
                            window.location.href = targetUrl;
                            return;
                        }

                        this.toast(data.message || 'Success!', 'success');
                        if (data.reference) {
                            setTimeout(() => this.toast('Reference: ' + data.reference, 'success'), 1500);
                        }

                        Alpine.store('modal').close();
                        form.reset();
                    } else {
                        if (data.require_verification && emailInput) {
                            emailInput.classList.remove('is-verified');
                            delete emailInput.dataset.isVerified;
                            this._pendingSubmitAfterOtp = form;
                            await this.startVerification(emailInput);
                            return;
                        }

                        let msg = data.message || 'An error occurred';
                        if (data.errors && typeof data.errors === 'object') {
                            const errorList = Object.values(data.errors).flat();
                            if (errorList.length > 0) msg = errorList.join('<br>');
                        }
                        if (errEl) {
                            errEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>' + msg;
                            errEl.classList.remove('hidden', 'd-none');
                        } else {
                            this.toast(msg, 'error');
                        }
                    }
                } catch (e) {
                    if (errEl) {
                        errEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>Network error. Please try again.';
                        errEl.classList.remove('hidden', 'd-none');
                    } else {
                        this.toast('Network error. Please try again.', 'error');
                    }
                } finally {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = orig;
                    }
                }
            });
        });
    },

    initQty() {
        // Generic Quantity +/- Triggers
        document.querySelectorAll('button[data-action="minus"], button[data-action="plus"]').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.dataset.target;
                if (!target) return;
                const inp = document.getElementById('booking' + target.charAt(0).toUpperCase() + target.slice(1));
                if (!inp) return;

                let v = parseInt(inp.value) || 0;
                const min = parseInt(inp.min) || 0;
                const max = parseInt(inp.max) || 99;
                const step = parseInt(inp.step) || 1;

                if (btn.dataset.action === 'minus') v = Math.max(min, v - step);
                else if (btn.dataset.action === 'plus') v = Math.min(max, v + step);

                inp.value = v;

                if (target === 'adults') {
                    const cards = Array.from(document.querySelectorAll('#tierCards .tier-card'));
                    const matchingCard = cards.find(c => parseInt(c.dataset.guestCount || c.dataset.capacity, 10) === v);
                    if (matchingCard && !matchingCard.classList.contains('selected')) {
                        this.selectTier(matchingCard);
                        return;
                    }
                }

                this.updateTotal();
                if (typeof this.validateStep1 === 'function') {
                    this.validateStep1(false);
                }
            });
        });

        // Dedicated Safari Customizer & Modal Guest Quantity Increment / Decrement
        const bindCounter = (minusId, plusId, countId, min, max, onChange) => {
            const minusBtn = document.getElementById(minusId);
            const plusBtn = document.getElementById(plusId);
            const countEl = document.getElementById(countId);
            if (!minusBtn || !plusBtn) return;

            minusBtn.addEventListener('click', () => {
                let current = parseInt(countEl?.innerText || countEl?.value || '1');
                if (current > min) {
                    current--;
                    if (countEl) {
                        if (countEl.innerText !== undefined) countEl.innerText = current;
                        if (countEl.value !== undefined) countEl.value = current;
                    }
                    if (typeof onChange === 'function') onChange(current);
                }
            });

            plusBtn.addEventListener('click', () => {
                let current = parseInt(countEl?.innerText || countEl?.value || '1');
                if (current < max) {
                    current++;
                    if (countEl) {
                        if (countEl.innerText !== undefined) countEl.innerText = current;
                        if (countEl.value !== undefined) countEl.value = current;
                    }
                    if (typeof onChange === 'function') onChange(current);
                }
            });
        };

        bindCounter('customizerAdultsMinus', 'customizerAdultsPlus', 'customizerAdultsCount', 1, 30);
        bindCounter('customizerChildrenMinus', 'customizerChildrenPlus', 'customizerChildrenCount', 0, 20);
        bindCounter('modalAdultsMinus', 'modalAdultsPlus', 'modalAdultsCount', 1, 30);
        bindCounter('modalChildrenMinus', 'modalChildrenPlus', 'modalChildrenCount', 0, 20);
    },

    initFAQ() {
        document.querySelectorAll('.faq-q').forEach(q => {
            q.addEventListener('click', () => {
                const item = q.closest('.faq-item');
                const answer = item?.querySelector('.faq-a');
                const inner = answer?.querySelector('.faq-a-inner');
                const isOpen = q.classList.contains('active');

                document.querySelectorAll('.faq-q.active').forEach(oq => {
                    oq.classList.remove('active');
                    const oAnswer = oq.closest('.faq-item')?.querySelector('.faq-a');
                    if (oAnswer) oAnswer.style.maxHeight = '0';
                });

                if (!isOpen && answer && inner) {
                    q.classList.add('active');
                    answer.style.maxHeight = inner.scrollHeight + 'px';
                }
            });
        });
    },

    initTourSidebar() {
        const list = document.getElementById('sidebarTierList');
        if (!list) return;

        const book = document.getElementById('sidebarBookOnline');
        const wa = document.getElementById('sidebarBookWhatsapp');
        const priceEl = document.querySelector('.book-card-price .amount');

        const buildWa = (tierName) => {
            if (!wa) return;
            const base = wa.getAttribute('href');
            wa.dataset.baseHref = wa.dataset.baseHref || base;

            try {
                const u = new URL(wa.dataset.baseHref, location.origin);
                const t = tierName ? (' Package: ' + tierName + '.') : '';
                u.searchParams.set('text', decodeURIComponent(u.searchParams.get('text') || '') + t);
                wa.setAttribute('href', u.pathname + u.search);
            } catch (e) {}
        };

        const apply = (btn) => {
            list.querySelectorAll('.sidebar-tier').forEach(b => b.classList.remove('is-active'));
            btn.classList.add('is-active');

            const tierId = btn.dataset.tier;
            const tierName = btn.dataset.name || '';
            const price = btn.dataset.price ? parseFloat(btn.dataset.price) : 0;

            if (book) book.dataset.tier = tierId;
            if (priceEl && price) priceEl.textContent = 'AED ' + Math.round(price).toLocaleString();
            buildWa(tierName);
        };

        list.querySelectorAll('.sidebar-tier').forEach(btn => btn.addEventListener('click', () => apply(btn)));
        const first = list.querySelector('.sidebar-tier.is-popular') || list.querySelector('.sidebar-tier');
        if (first) apply(first);
    },

    initHorizontalTabs() {
        const tabs = document.getElementById('tourTabs');
        if (!tabs) return;
        let isDown = false, startX = 0, scrollLeft = 0;

        const onDown = e => {
            isDown = true;
            startX = (e.pageX || e.touches?.[0]?.pageX || 0);
            scrollLeft = tabs.scrollLeft;
        };
        const onMove = e => {
            if (!isDown) return;
            const x = (e.pageX || e.touches?.[0]?.pageX || 0);
            const walk = (startX - x);
            tabs.scrollLeft = scrollLeft + walk;
        };
        const onUp = () => { isDown = false; };

        tabs.addEventListener('mousedown', onDown);
        tabs.addEventListener('mousemove', onMove);
        window.addEventListener('mouseup', onUp);
        tabs.addEventListener('touchstart', onDown, { passive: true });
        tabs.addEventListener('touchmove', onMove, { passive: true });
        tabs.addEventListener('touchend', onUp);
        tabs.addEventListener('wheel', e => {
            if (Math.abs(e.deltaY) > 0) {
                e.preventDefault();
                tabs.scrollLeft += e.deltaY;
            }
        }, { passive: false });
    },

    openBooking(tourId, tierId = null) {
        Alpine.store('modal').open('booking', { tourId: String(tourId), tierId: tierId ? String(tierId) : null });
    },

    initSafariMatcher() {
        const section = document.getElementById('safariMatcherSection');
        if (!section) return;

        let activeTours = [];
        try {
            const raw = document.getElementById('activeToursDataset')?.textContent;
            if (raw) activeTours = JSON.parse(raw);
        } catch (e) {}

        if (!activeTours.length) {
            const tourSelect = document.getElementById('bookingTour');
            if (tourSelect) {
                for (let i = 0; i < tourSelect.options.length; i++) {
                    const opt = tourSelect.options[i];
                    if (opt.value) {
                        activeTours.push({
                            id: String(opt.value),
                            name: opt.text,
                            slug: opt.text.toLowerCase().replace(/[^a-z0-9]+/g, '-'),
                            category_slug: opt.text.toLowerCase().includes('city') ? 'city-tour' : (opt.text.toLowerCase().includes('cruise') ? 'water-activity' : 'desert-safari'),
                            category_name: opt.text.toLowerCase().includes('city') ? 'City Tour' : (opt.text.toLowerCase().includes('cruise') ? 'Water Cruise' : 'Desert Safari'),
                            duration: '4-6 Hours',
                            rating: 4.9,
                            min_price: 79,
                            short_desc: opt.text + ' - Top rated Dubai experience with 5-star service.'
                        });
                    }
                }
            }
        }

        let answers = { experience: null, time: null, style: null };

        const step1 = document.getElementById('quizStep1');
        const step2 = document.getElementById('quizStep2');
        const step3 = document.getElementById('quizStep3');
        const result = document.getElementById('quizResult');

        const stepNum = document.getElementById('quizStepNum');
        const stepTitle = document.getElementById('quizStepTitle');
        const progressText = document.getElementById('quizProgressText');

        const matchedTitle = document.getElementById('quizMatchedTitle');
        const matchedCategory = document.getElementById('quizMatchedCategory');
        const matchedMeta = document.getElementById('quizMatchedMeta');
        const matchedPrice = document.getElementById('quizMatchedPrice');
        const bookBtn = document.getElementById('quizBookBtn');
        const resetBtn = document.getElementById('quizResetBtn');

        let matchedTour = activeTours[0] || null;

        const calculateBestMatch = () => {
            if (!activeTours.length) return;

            let pool = activeTours.filter(t => {
                const catLow = (t.category_slug || '').toLowerCase();
                const nameLow = (t.name || '').toLowerCase();
                const slugLow = (t.slug || '').toLowerCase();

                if (answers.experience === 'desert') {
                    return catLow.includes('desert') || slugLow.includes('desert') || slugLow.includes('safari');
                } else if (answers.experience === 'city') {
                    return catLow.includes('city') || slugLow.includes('city') || slugLow.includes('abu-dhabi') || slugLow.includes('sightseeing');
                } else if (answers.experience === 'water') {
                    return catLow.includes('water') || slugLow.includes('cruise') || slugLow.includes('dhow') || slugLow.includes('catamaran') || slugLow.includes('yacht');
                } else if (answers.experience === 'quad_buggy') {
                    return slugLow.includes('buggy') || slugLow.includes('quad') || nameLow.includes('quad') || nameLow.includes('buggy') || slugLow.includes('atv');
                }
                return true;
            });

            if (!pool.length) pool = activeTours;

            let candidates = pool.map(t => {
                let score = 0;
                const nameLow = (t.name || '').toLowerCase();
                const slugLow = (t.slug || '').toLowerCase();
                const descLow = (t.short_desc || '').toLowerCase();
                const haystack = `${nameLow} ${slugLow} ${descLow}`;

                if (answers.time === 'morning') {
                    if (haystack.includes('morning') || haystack.includes('sunrise') || haystack.includes('half day') || slugLow.includes('morning')) score += 50;
                } else if (answers.time === 'evening') {
                    if (haystack.includes('evening') || haystack.includes('sunset') || haystack.includes('dinner') || haystack.includes('bbq') || slugLow.includes('evening')) score += 50;
                } else if (answers.time === 'overnight') {
                    if (haystack.includes('overnight') || haystack.includes('camping') || slugLow.includes('overnight')) score += 60;
                }

                if (answers.style === 'thrill') {
                    if (haystack.includes('quad') || haystack.includes('buggy') || haystack.includes('bashing') || haystack.includes('extreme')) score += 40;
                } else if (answers.style === 'luxury') {
                    if (haystack.includes('vip') || haystack.includes('luxury') || haystack.includes('private') || haystack.includes('abu dhabi') || haystack.includes('catamaran')) score += 40;
                } else if (answers.style === 'family') {
                    if (haystack.includes('city') || haystack.includes('evening') || haystack.includes('cruise') || haystack.includes('family') || t.is_bestseller) score += 30;
                }

                if (t.is_bestseller) score += 15;
                if (t.is_featured) score += 10;
                score -= (t.priority || 10);

                return { tour: t, score: score };
            });

            candidates.sort((a, b) => b.score - a.score);
            matchedTour = candidates[0]?.tour || pool[0];

            if (matchedTitle) matchedTitle.textContent = matchedTour.name;
            if (matchedCategory) matchedCategory.textContent = matchedTour.category_name || 'Experience';
            if (matchedMeta) {
                matchedMeta.innerHTML = `<i class="bi bi-clock me-1"></i>${matchedTour.duration || '4-6 Hours'} • ★ ${matchedTour.rating || 4.9} (${matchedTour.review_count || '500+'} Reviews)`;
            }
            if (matchedPrice) {
                const pVal = Math.round(matchedTour.min_price || 79);
                matchedPrice.dataset.aed = pVal;
                matchedPrice.textContent = 'AED ' + pVal;
                Alpine.store('currency')?.convertAllPrices();
            }
        };

        section.querySelectorAll('.quiz-choice-card').forEach(card => {
            card.addEventListener('click', () => {
                const step = card.dataset.step;
                const val = card.dataset.val;

                if (step === '1') {
                    answers.experience = val;
                    step1.classList.add('d-none');
                    step2.classList.remove('d-none');
                    stepNum.textContent = '2';
                    stepTitle.textContent = 'Preferred Timing';
                    progressText.textContent = 'Step 2 of 3';
                } else if (step === '2') {
                    answers.time = val;
                    step2.classList.add('d-none');
                    step3.classList.remove('d-none');
                    stepNum.textContent = '3';
                    stepTitle.textContent = 'Group Style';
                    progressText.textContent = 'Step 3 of 3';
                } else if (step === '3') {
                    answers.style = val;
                    calculateBestMatch();
                    step3.classList.add('d-none');
                    result.classList.remove('d-none');
                    stepNum.textContent = '✓';
                    stepTitle.textContent = 'Your Matched Tour';
                    progressText.textContent = 'Matched!';
                }
            });
        });

        if (bookBtn) {
            bookBtn.addEventListener('click', () => {
                if (matchedTour && matchedTour.id) {
                    Alpine.store('modal').open('booking', { tourId: matchedTour.id });
                    const promoInput = document.getElementById('bookingPromoCode');
                    if (promoInput) {
                        promoInput.value = 'MATCH5';
                        if (typeof window.validateCurrentPromo === 'function') {
                            setTimeout(() => window.validateCurrentPromo(), 400);
                        }
                    }
                }
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                answers = { experience: null, time: null, style: null };
                result.classList.add('d-none');
                step2.classList.add('d-none');
                step3.classList.add('d-none');
                step1.classList.remove('d-none');
                stepNum.textContent = '1';
                stepTitle.textContent = 'Choose Experience Type';
                progressText.textContent = 'Step 1 of 3';
            });
        }
    },

    initSunsetWidget() {
        const label = document.getElementById('sunsetCountdownLabel');
        if (!label) return;

        const getDubaiSunset = (date) => {
            const lat = 25.2048;
            const lng = 55.2708;
            const rad = Math.PI / 180;
            const deg = 180 / Math.PI;

            const start = new Date(date.getFullYear(), 0, 0);
            const diff = date - start;
            const oneDay = 1000 * 60 * 60 * 24;
            const dayOfYear = Math.floor(diff / oneDay);

            const lngHour = lng / 15;
            const t = dayOfYear + ((18 - lngHour) / 24);
            const M = (0.9856 * t) - 3.289;
            let L = M + (1.916 * Math.sin(M * rad)) + (0.020 * Math.sin(2 * M * rad)) + 282.634;
            L = ((L % 360) + 360) % 360;

            let RA = deg * Math.atan(0.91764 * Math.tan(L * rad));
            RA = ((RA % 360) + 360) % 360;
            const Lquadrant = Math.floor(L / 90) * 90;
            const RAquadrant = Math.floor(RA / 90) * 90;
            RA = (RA + (Lquadrant - RAquadrant)) / 15;

            const sinDec = 0.39782 * Math.sin(L * rad);
            const cosDec = Math.cos(Math.asin(sinDec));
            const zenith = 90.833;
            const cosH = (Math.cos(zenith * rad) - (sinDec * Math.sin(lat * rad))) / (cosDec * Math.cos(lat * rad));

            if (cosH > 1 || cosH < -1) return { hours: 18, minutes: 30 };
            const H = (deg * Math.acos(cosH)) / 15;
            const T = H + RA - (0.06571 * t) - 6.622;
            let UT = T - lngHour;
            UT = ((UT % 24) + 24) % 24;
            const localHour = UT + 4;
            const hours = Math.floor(localHour);
            const minutes = Math.round((localHour - hours) * 60);
            return { hours, minutes };
        };

        const updateCountdown = () => {
            const now = new Date();
            const utcTime = now.getTime() + (now.getTimezoneOffset() * 60000);
            const dubaiTime = new Date(utcTime + (3600000 * 4));

            const sunsetCalc = getDubaiSunset(dubaiTime);
            const sunsetToday = new Date(dubaiTime);
            sunsetToday.setHours(sunsetCalc.hours, sunsetCalc.minutes, 0, 0);

            const displayHour = sunsetCalc.hours > 12 ? sunsetCalc.hours - 12 : sunsetCalc.hours;
            const displayMin = sunsetCalc.minutes.toString().padStart(2, '0');
            const sunsetFormatted = `${displayHour}:${displayMin} PM`;

            const diffMs = sunsetToday - dubaiTime;
            if (diffMs > 0) {
                const diffHrs = Math.floor(diffMs / 3600000);
                const diffMins = Math.floor((diffMs % 3600000) / 60000);
                label.innerHTML = `Sunset: ${sunsetFormatted} • Golden Hour in ${diffHrs > 0 ? diffHrs + 'h ' : ''}${diffMins}m`;
            } else {
                label.innerHTML = `Stargazing Safari Live Tonight • Clear Skies`;
            }
        };

        updateCountdown();
        setInterval(updateCountdown, 60000);
    }
};

window.App = App;
window.DunesApp = App;
window.DunesCompare = Alpine.store('compare');
window.openWhatsApp = function(tourName = '', directHref = '') {
    App.openWhatsApp(tourName, directHref);
};


// =============================================================================
// 4. GLOBAL REVIEW POPOVER ENGINE (taCircle & googleCircle)
// =============================================================================

const reviewData = {
    'taCircle': {
        title: 'TripAdvisor',
        logo: '/images/tripadvisor-color-logo.svg',
        score: '4.9',
        url: 'https://www.tripadvisor.com/Attraction_Review-g295424-d29026644-Reviews-Dunes_Discovery-Dubai_Emirate_of_Dubai.html',
        btnText: 'Read Reviews'
    },
    'googleCircle': {
        title: 'Google Reviews',
        logo: '/images/google-logo.png',
        score: '5.0',
        url: 'https://www.google.com/maps/search/?api=1&query=Google&query_place_id=ChIJbWsIEIVEdEER4uHEhb2dbcQ',
        btnText: 'See Reviews'
    },
    'desktopTaBadge': {
        title: 'TripAdvisor',
        logo: '/images/tripadvisor-color-logo.svg',
        score: '4.9',
        url: 'https://www.tripadvisor.com/Attraction_Review-g295424-d29026644-Reviews-Dunes_Discovery-Dubai_Emirate_of_Dubai.html',
        btnText: 'Read Reviews'
    },
    'desktopGoogleBadge': {
        title: 'Google Reviews',
        logo: '/images/google-logo.png',
        score: '5.0',
        url: 'https://www.google.com/maps/search/?api=1&query=Google&query_place_id=ChIJbWsIEIVEdEER4uHEhb2dbcQ',
        btnText: 'See Reviews'
    },
    'taCircleDrawer': {
        title: 'TripAdvisor',
        logo: '/images/tripadvisor-color-logo.svg',
        score: '4.9',
        url: 'https://www.tripadvisor.com/Attraction_Review-g295424-d29026644-Reviews-Dunes_Discovery-Dubai_Emirate_of_Dubai.html',
        btnText: 'Read Reviews'
    },
    'googleCircleDrawer': {
        title: 'Google Reviews',
        logo: '/images/google-logo.png',
        score: '5.0',
        url: 'https://www.google.com/maps/search/?api=1&query=Google&query_place_id=ChIJbWsIEIVEdEER4uHEhb2dbcQ',
        btnText: 'See Reviews'
    }
};

function toggleReviewPopover(element, event) {
    if (!element) return;
    try {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }

        const existing = document.querySelector('.global-popover-overlay');
        const currentTriggerId = existing ? existing.dataset.triggerId : null;

        if (existing) existing.remove();
        document.querySelectorAll('.nav-review-circle').forEach(el => el.classList.remove('active'));

        const elemId = element.id || '';
        if (currentTriggerId && currentTriggerId === elemId) return;

        const data = reviewData[elemId] || (elemId.toLowerCase().includes('google') ? reviewData['googleCircle'] : reviewData['taCircle']);
        if (!data) return;

        const popover = document.createElement('div');
        popover.className = 'global-popover-overlay';
        popover.dataset.triggerId = elemId;

        popover.innerHTML = `
            <div class="review-popover-header">
                <img src="${data.logo}" alt="${data.title}" class="review-popover-logo">
            </div>
            <div class="review-popover-score">${data.score}</div>
            <div class="review-popover-stars">
                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
            </div>
            <a href="${data.url}" target="_blank" rel="noopener noreferrer" class="review-popover-btn">
                ${data.btnText} <i class="bi bi-arrow-right"></i>
            </a>
        `;

        popover.style.position = 'fixed';
        popover.style.zIndex = '2147483647';
        popover.style.display = 'block';
        popover.style.visibility = 'visible';
        popover.style.opacity = '1';
        popover.style.backgroundColor = 'white';
        popover.style.transform = 'none';

        const btnRect = element.getBoundingClientRect();
        const popoverWidth = 220;
        const margin = 10;

        let left = btnRect.left + (btnRect.width / 2) - (popoverWidth / 2);
        if (left < margin) left = margin;
        else if (left + popoverWidth > window.innerWidth - margin) left = window.innerWidth - margin - popoverWidth;

        const top = btnRect.bottom + 12;
        popover.style.top = `${top}px`;
        popover.style.left = `${left}px`;

        const arrowX = (btnRect.left + btnRect.width / 2) - left;
        popover.style.setProperty('--arrow-left', `${arrowX}px`);

        document.body.appendChild(popover);
        element.classList.add('active');
    } catch (e) {
        console.error('Popover Error:', e);
    }
}

window.toggleReviewPopover = toggleReviewPopover;
window.reviewData = reviewData;

document.addEventListener('click', function(e) {
    const trigger = e.target.closest('#taCircle, #googleCircle, #desktopTaBadge, #desktopGoogleBadge, #taCircleDrawer, #googleCircleDrawer, .nav-review-circle');
    if (trigger) {
        if (!trigger.hasAttribute('onclick')) {
            toggleReviewPopover(trigger, e);
        }
        return;
    }
    if (!e.target.closest('.global-popover-overlay')) {
        const existing = document.querySelector('.global-popover-overlay');
        if (existing) existing.remove();
        document.querySelectorAll('.nav-review-circle').forEach(el => el.classList.remove('active'));
    }
});


// =============================================================================
// 5. BOOTSTRAP JS REPLACEMENT COMPATIBILITY SHIM
// =============================================================================

window.bootstrap = window.bootstrap || {};

const createModalBridge = (element) => ({
    show() {
        const id = typeof element === 'string' ? element : (element?.id || '');
        const map = {
            'bookingModal': 'booking',
            'safariMatcherModal': 'safari-matcher',
            'globalSearchModal': 'search',
            'welcomeOfferModal': 'welcome-offer',
            'customSafariModal': 'custom-safari',
            'compareDrawer': 'compare',
            'whatsappModal': 'whatsapp',
            'legalModal': 'legal'
        };
        Alpine.store('modal').open(map[id] || id);
    },
    hide() {
        Alpine.store('modal').close();
    },
    toggle() {
        if (Alpine.store('modal').active) Alpine.store('modal').close();
        else this.show();
    },
    dispose() {}
});

window.bootstrap.Modal = class {
    constructor(el) { return createModalBridge(el); }
    static getInstance(el) { return createModalBridge(el); }
    static getOrCreateInstance(el) { return createModalBridge(el); }
};

window.bootstrap.Offcanvas = class {
    constructor(el) { return createModalBridge(el); }
    static getInstance(el) { return createModalBridge(el); }
    static getOrCreateInstance(el) { return createModalBridge(el); }
};

window.bootstrap.Tooltip = class {
    constructor(el) { this.el = el; }
    static getInstance() { return { dispose() {}, hide() {}, show() {}, enable() {}, disable() {} }; }
    show() {}
    hide() {}
    dispose() {}
    enable() {}
    disable() {}
    setContent() {}
};


// =============================================================================
// 6. ALPINE.JS DATA COMPONENTS
// =============================================================================

/**
 * 6.1 Safari Matcher Modal Component
 */
Alpine.data('safariMatcherModal', (config = {}) => ({
    step: 1,
    answers: { group: null, vibe: null, perk: null },
    loading: false,
    result: {
        id: '1',
        name: 'Standard Evening Desert Safari',
        slug: 'evening-desert-safari-dubai',
        min_price: 120,
        rating: 4.9,
        duration: '6-7 Hours',
        thumb: '/images/desert-safari-poster.avif'
    },
    reasons: [],
    conciergePromoActive: !!config.conciergePromoActive,
    conciergePromoCode: config.conciergePromoCode || '',
    waPhone: config.waPhone || '971502456056',

    selectOption(field, val) {
        this.answers[field] = val;
        if (this.step === 1) {
            this.step = 2;
        } else if (this.step === 2) {
            this.step = 3;
        } else if (this.step === 3) {
            this.calculateResult();
        }
    },

    calculateResult() {
        this.loading = true;
        this.step = 4;
        
        setTimeout(() => {
            let catalog = [];
            const compareScript = document.getElementById('dunesCompareTourData');
            if (compareScript) {
                try { catalog = JSON.parse(compareScript.textContent || '[]'); } catch(e) {}
            }
            if (!catalog || catalog.length === 0) {
                catalog = [
                    { id: '1', name: 'Standard Evening Desert Safari', slug: 'evening-desert-safari-dubai', min_price: 120, rating: 4.9, duration: '6-7 Hours', thumb: '/images/desert-safari-poster.avif' },
                    { id: '2', name: 'VIP Luxury Desert Safari with Table Service', slug: 'vip-desert-safari', min_price: 250, rating: 4.9, duration: '6-7 Hours', thumb: '/images/desert-safari-poster.avif' },
                    { id: '3', name: 'Evening Desert Safari with Quad Biking', slug: 'quad-biking-safari', min_price: 180, rating: 4.9, duration: '6-7 Hours', thumb: '/images/desert-safari-poster.avif' },
                    { id: '4', name: 'Morning Desert Safari with Camel Ride', slug: 'morning-desert-safari', min_price: 130, rating: 4.8, duration: '4 Hours', thumb: '/images/desert-safari-poster.avif' },
                    { id: '5', name: 'Overnight Desert Safari & Camping', slug: 'overnight-desert-safari', min_price: 350, rating: 4.9, duration: '18 Hours', thumb: '/images/desert-safari-poster.avif' },
                    { id: '6', name: 'Dubai Marina Luxury Dhow Cruise Dinner', slug: 'dhow-cruise-marina', min_price: 150, rating: 4.8, duration: '3 Hours', thumb: '/images/desert-safari-poster.avif' }
                ];
            }
            
            let best = catalog[0];
            let maxScore = -999;
            
            catalog.forEach(t => {
                let score = 0;
                const n = (t.name || '').toLowerCase();
                const s = (t.slug || '').toLowerCase();
                
                if (this.answers.vibe === 'morning' && (s.includes('morning') || n.includes('morning'))) score += 50;
                if (this.answers.vibe === 'overnight' && (s.includes('overnight') || n.includes('overnight'))) score += 50;
                if (this.answers.vibe === 'cruise' && (s.includes('cruise') || s.includes('dhow') || n.includes('cruise'))) score += 60;
                if (this.answers.vibe === 'evening' && !s.includes('morning') && !s.includes('overnight') && !s.includes('cruise')) score += 30;
                
                if (this.answers.perk === 'quad_buggy' && (s.includes('quad') || s.includes('buggy') || n.includes('quad') || n.includes('buggy'))) score += 45;
                if (this.answers.perk === 'vip_service' && (s.includes('vip') || n.includes('vip') || s.includes('luxury'))) score += 45;
                if (this.answers.perk === 'private_car' && (s.includes('private') || s.includes('vip'))) score += 35;
                if (this.answers.perk === 'all_inclusive' && (s.includes('evening') || s.includes('standard') || s.includes('red-dunes'))) score += 30;
                
                if (this.answers.group === 'adventure' && (s.includes('quad') || s.includes('buggy') || s.includes('red-dunes'))) score += 30;
                if (this.answers.group === 'luxury' && (s.includes('vip') || s.includes('private'))) score += 30;
                if (this.answers.group === 'family' && (s.includes('standard') || s.includes('evening') || s.includes('morning'))) score += 25;
                if (this.answers.group === 'budget') {
                    if (t.min_price && t.min_price < 150) score += 25;
                    if (t.is_bestseller) score += 15;
                }
                
                if (score > maxScore) {
                    maxScore = score;
                    best = t;
                }
            });
            
            const matched = Object.assign({}, best);
            matched.thumb = matched.thumb || (matched.thumb_image ? '/images/' + matched.thumb_image : '/images/desert-safari-poster.avif');
            this.result = matched;
            
            const bullets = [];
            if (this.answers.group === 'family') bullets.push('Optimized for families: gentle pacing, spacious camp & child-friendly activities.');
            else if (this.answers.group === 'adventure') bullets.push('Adrenaline-packed: extreme red dunes bashing & optional ATV self-drive.');
            else if (this.answers.group === 'luxury') bullets.push('VIP experience: premium comfort, luxury 4x4 & priority table hospitality.');
            else bullets.push("Best value: Dubai's highest-rated classic experience at guaranteed best rates.");

            if (this.answers.vibe === 'morning') bullets.push('Crisp morning timing: cooler desert temperatures and sunrise dunes.');
            else if (this.answers.vibe === 'overnight') bullets.push('Magical overnight stay: authentic Bedouin tent, stargazing & sunrise breakfast.');
            else if (this.answers.vibe === 'cruise') bullets.push('Dubai Marina skyline: tranquil waters, live shows & 5-star international buffet.');
            else bullets.push('Golden hour sunset: prime dune photography & evening cultural live performances.');

            if (this.answers.perk === 'quad_buggy') bullets.push('Includes your desired high-power Quad / Buggy desert track session.');
            else if (this.answers.perk === 'vip_service') bullets.push('Includes exclusive VIP table service with private dedicated waiter.');
            else if (this.answers.perk === 'private_car') bullets.push('Available with private door-to-door Land Cruiser transfers.');
            else bullets.push('All-inclusive: dune bashing, camel riding, sandboarding & 5-star live BBQ dinner.');

            this.reasons = bullets;
            this.loading = false;
        }, 600);
    },

    resetQuiz() {
        this.step = 1;
        this.answers = { group: null, vibe: null, perk: null };
        this.loading = false;
        this.result = {
            id: '1',
            name: 'Standard Evening Desert Safari',
            slug: 'evening-desert-safari-dubai',
            min_price: 120,
            rating: 4.9,
            duration: '6-7 Hours',
            thumb: '/images/desert-safari-poster.avif'
        };
        this.reasons = [];
    },

    book() {
        const targetTourId = this.result ? this.result.id : null;
        const promoCode = this.conciergePromoActive ? this.conciergePromoCode : '';

        if (window.Alpine && Alpine.store('modal')) {
            Alpine.store('modal').open('booking', {
                tourId: targetTourId,
                promo: promoCode
            });
        }
        if (targetTourId) {
            const tourSelect = document.getElementById('bookingTour');
            if (tourSelect) {
                tourSelect.value = targetTourId;
                tourSelect.dispatchEvent(new Event('change'));
            }
        }
        if (promoCode) {
            const promoInput = document.getElementById('bookingPromoCode');
            if (promoInput) {
                promoInput.value = promoCode;
                if (typeof window.validateCurrentPromo === 'function') {
                    setTimeout(() => window.validateCurrentPromo(), 300);
                }
            }
        }
    },

    get waUrl() {
        if (!this.result) return '#';
        const codeText = this.conciergePromoActive && this.conciergePromoCode ? ` with code ${this.conciergePromoCode}` : '';
        const text = encodeURIComponent(`Hi Dunes Discovery! Your Safari Match Concierge recommended "${this.result.name}" for my party${codeText}. Could you please share availability and details?`);
        return `https://wa.me/${this.waPhone}?text=${text}`;
    }
}));

/**
 * 6.2 Safari Matcher Inline Quiz Component
 */
Alpine.data('safariMatcherQuiz', (config = {}) => ({
    step: 1,
    answers: { type: null, time: null, style: null },
    matchedTour: {
        title: 'Evening Desert Safari Dubai',
        category: 'Desert Safari',
        duration: '6 Hours',
        rating: '4.9 (1,200+ Reviews)',
        desc: 'Our top-rated Dubai red dunes safari with 4x4 dune bashing, camel riding, sandboarding, 5-star live BBQ dinner buffet and 3 cultural shows.',
        price: 'AED 150',
        tourId: '1'
    },
    conciergePromoActive: !!config.conciergePromoActive,
    conciergePromoCode: config.conciergePromoCode || '',

    selectChoice(stepNum, val) {
        if (stepNum === 1) {
            this.answers.type = val;
            this.step = 2;
        } else if (stepNum === 2) {
            this.answers.time = val;
            this.step = 3;
        } else if (stepNum === 3) {
            this.answers.style = val;
            this.calculateMatch();
            this.step = 4;
        }
    },

    calculateMatch() {
        if (this.answers.type === 'quad_buggy') {
            this.matchedTour = {
                title: 'Can-Am Dune Buggy & Quad Safari',
                category: 'Motorsports',
                duration: '4-5 Hours',
                rating: '4.9 (850+ Reviews)',
                desc: 'Self-drive powerful 1000cc Can-Am Turbo or 400cc ATV quad bikes across open high red dunes with professional lead marshal.',
                price: 'AED 350',
                tourId: '3'
            };
        } else if (this.answers.type === 'water') {
            this.matchedTour = {
                title: 'Dubai Marina Luxury Dhow Cruise Dinner',
                category: 'Cruises',
                duration: '3 Hours',
                rating: '4.8 (640+ Reviews)',
                desc: 'Glide past illuminated skyscrapers of Dubai Marina and JBR on a glass-enclosed luxury catamaran with 5-star international buffet.',
                price: 'AED 150',
                tourId: '6'
            };
        } else if (this.answers.time === 'morning') {
            this.matchedTour = {
                title: 'Morning Desert Safari with Camel Trek',
                category: 'Morning Safari',
                duration: '4 Hours',
                rating: '4.8 (720+ Reviews)',
                desc: 'Enjoy crisp morning desert air, soft red sunrise dunes, thrilling dune bashing, sandboarding, and traditional camel riding.',
                price: 'AED 130',
                tourId: '4'
            };
        } else if (this.answers.time === 'overnight') {
            this.matchedTour = {
                title: 'Overnight Stargazing Safari & Camp',
                category: 'Overnight Glamping',
                duration: '18 Hours',
                rating: '4.9 (410+ Reviews)',
                desc: 'Full evening desert safari followed by private Bedouin tent stay, night campfire stargazing, and fresh cooked morning breakfast.',
                price: 'AED 350',
                tourId: '5'
            };
        } else if (this.answers.style === 'luxury') {
            this.matchedTour = {
                title: 'VIP Luxury Desert Safari with Table Service',
                category: 'VIP Luxury',
                duration: '6-7 Hours',
                rating: '4.9 (980+ Reviews)',
                desc: 'Private 4x4 Land Cruiser hotel pickup, VIP elevated stage-side table, dedicated private waiter service, and premium BBQ dinner.',
                price: 'AED 250',
                tourId: '2'
            };
        } else {
            this.matchedTour = {
                title: 'Premium Evening Desert Safari Dubai',
                category: 'Desert Safari',
                duration: '6-7 Hours',
                rating: '4.9 (1,200+ Reviews)',
                desc: "Dubai's flagship red dunes experience with extreme dune bashing, camel riding, sandboarding, 5-star live BBQ dinner and 3 cultural shows.",
                price: 'AED 150',
                tourId: '1'
            };
        }
    },

    resetQuiz() {
        this.step = 1;
        this.answers = { type: null, time: null, style: null };
    },

    bookMatched() {
        if (window.Alpine && Alpine.store('modal')) {
            Alpine.store('modal').open('booking', {
                tourId: this.matchedTour.tourId,
                promo: this.conciergePromoActive ? this.conciergePromoCode : ''
            });
        }
    }
}));

/**
 * 6.3 Custom Safari Modal Component
 */
Alpine.data('customSafariModal', (config = {}) => ({
    base: { name: 'Standard Evening Red Dunes', price: 150, tourId: 1 },
    transfer: { name: 'Shared 4x4 Land Cruiser', price: 0, type: 'flat' },
    sports: { name: 'Scenic Only', price: 0, type: 'per_person' },
    addons: [],
    adults: 2,
    waPhone: config.waPhone || '971502456056',

    hasAddon(key) {
        return this.addons.some(a => a.key === key);
    },
    toggleAddon(addon) {
        if (this.hasAddon(addon.key)) {
            this.addons = this.addons.filter(a => a.key !== addon.key);
        } else {
            this.addons.push(addon);
        }
    },
    get total() {
        let baseTotal = this.base.price * this.adults;
        let transferTotal = (this.transfer.type === 'flat') ? this.transfer.price : (this.transfer.price * this.adults);
        let sportsTotal = (this.sports.type === 'flat') ? this.sports.price : (this.sports.price * this.adults);
        let addonsTotal = 0;
        this.addons.forEach(a => {
            addonsTotal += (a.type === 'flat') ? a.price : (a.price * this.adults);
        });
        return baseTotal + transferTotal + sportsTotal + addonsTotal;
    },
    get summaryAddons() {
        return this.addons.length ? this.addons.map(a => a.name).join(', ') : 'None';
    },
    get waUrl() {
        const msg = `Hi Dunes Discovery! I configured a custom safari: Base: ${encodeURIComponent(this.base.name)}, Vehicle: ${encodeURIComponent(this.transfer.name)}, Sports: ${encodeURIComponent(this.sports.name)}, Addons: ${encodeURIComponent(this.summaryAddons)}, Guests: ${this.adults} Adults, Total: AED ${this.total}. Can you check availability?`;
        return `https://wa.me/${this.waPhone}?text=${msg}`;
    },
    book() {
        if (window.Alpine && Alpine.store('modal')) {
            Alpine.store('modal').close();
        }
        setTimeout(() => {
            if (window.Alpine && Alpine.store('modal')) {
                Alpine.store('modal').open('booking', {
                    tourId: this.base.tourId,
                    adults: this.adults,
                    requests: `[CUSTOM BUILDER SPEC]\nVehicle: ${this.transfer.name}\nMotorsports: ${this.sports.name}\nAddons: ${this.summaryAddons}\nEstimated Total: AED ${this.total}`
                });
            }
            const tourSelect = document.getElementById('bookingTour');
            if (tourSelect && this.base.tourId) {
                tourSelect.value = this.base.tourId;
                tourSelect.dispatchEvent(new Event('change'));
            }
            const adultsInput = document.getElementById('bookingAdults');
            if (adultsInput) adultsInput.value = this.adults;
            const reqInput = document.getElementById('bookingRequests');
            if (reqInput) {
                reqInput.value = `[CUSTOM BUILDER SPEC]\nVehicle: ${this.transfer.name}\nMotorsports: ${this.sports.name}\nAddons: ${this.summaryAddons}\nEstimated Total: AED ${this.total}`;
            }
        }, 300);
    }
}));

/**
 * 6.4 Global Search Modal Auto-Complete & Real-time Narrowing Component
 */
Alpine.data('globalSearchModal', (config = {}) => ({
    query: '',
    categoryFilter: 'all',
    catalog: Array.isArray(config.catalog) ? config.catalog : [],
    serverResults: [],
    serverQuery: '',
    loading: false,
    selectedIndex: -1,
    searchUrl: config.searchUrl || '/search',
    liveSearchUrl: config.liveSearchUrl || '/search/live',
    debounceTimer: null,

    init() {
        this.loadCatalog();
        this.$watch('$store.modal.active', active => {
            if (active === 'search') {
                if (!Array.isArray(this.catalog) || this.catalog.length === 0) {
                    this.loadCatalog();
                }
                this.$nextTick(() => {
                    const input = this.$refs.searchInput;
                    if (input) {
                        input.focus();
                        input.select();
                    }
                });
            } else {
                this.selectedIndex = -1;
            }
        });
    },

    loadCatalog() {
        // 1. Try reading from DOM json script tag if catalog is empty
        if (!Array.isArray(this.catalog) || this.catalog.length === 0) {
            const dataEl = document.getElementById('siteSearchCatalogData');
            if (dataEl && dataEl.textContent) {
                try {
                    const parsed = JSON.parse(dataEl.textContent.trim());
                    if (Array.isArray(parsed) && parsed.length > 0) {
                        this.catalog = parsed;
                        return;
                    }
                } catch (e) {}
            }
        }

        // 2. Fetch full catalog in background if empty
        if (!Array.isArray(this.catalog) || this.catalog.length === 0) {
            const targetUrl = new URL(this.liveSearchUrl || '/search/live', window.location.origin);
            targetUrl.searchParams.set('all', '1');
            fetch(targetUrl.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success && Array.isArray(data.results)) {
                    this.catalog = data.results;
                }
            })
            .catch(() => {});
        }
    },

    get isSearching() {
        return this.query.trim().length >= 2;
    },

    get matchedTours() {
        const q = (this.query || '').trim().toLowerCase();
        if (q.length < 2) return [];

        // If server results match current query and category, use them
        if (this.serverQuery === q && Array.isArray(this.serverResults) && this.serverResults.length > 0) {
            return this.serverResults;
        }

        // Instant 0ms client-side search across preloaded catalog
        const catalogList = Array.isArray(this.catalog) ? this.catalog : [];
        if (catalogList.length === 0) {
            return Array.isArray(this.serverResults) ? this.serverResults : [];
        }

        const tokens = q.split(/\s+/).filter(t => t.length > 1);

        return catalogList.filter(tour => {
            if (!tour || typeof tour !== 'object') return false;
            const name = (tour.name || '').toLowerCase();
            const cat = (tour.category || '').toLowerCase();
            const keywords = (tour.keywords || '').toLowerCase();
            const highlights = (tour.highlights || '').toLowerCase();

            if (name.includes(q) || cat.includes(q) || keywords.includes(q) || highlights.includes(q)) {
                return true;
            }

            if (tokens.length > 1) {
                return tokens.every(t => name.includes(t) || cat.includes(t) || keywords.includes(t) || highlights.includes(t));
            }

            return tokens.some(t => name.includes(t) || cat.includes(t) || keywords.includes(t));
        }).sort((a, b) => {
            const aName = (a.name || '').toLowerCase();
            const bName = (b.name || '').toLowerCase();
            const aExact = aName.includes(q) ? 1 : 0;
            const bExact = bName.includes(q) ? 1 : 0;
            if (aExact !== bExact) return bExact - aExact;
            if (a.is_bestseller !== b.is_bestseller) return (b.is_bestseller ? 1 : 0) - (a.is_bestseller ? 1 : 0);
            return (a.priority || 0) - (b.priority || 0);
        });
    },

    get availableCategories() {
        const list = this.matchedTours;
        if (!Array.isArray(list) || list.length === 0) return [];

        const map = new Map();
        list.forEach(t => {
            if (!t || typeof t !== 'object') return;
            const name = t.category || 'Desert Safari';
            const slug = t.category_slug || 'desert-safari';
            if (!map.has(slug)) {
                map.set(slug, { name, slug, count: 0 });
            }
            map.get(slug).count++;
        });

        return Array.from(map.values());
    },

    get totalMatchCount() {
        return this.matchedTours.length;
    },

    get filteredResults() {
        const list = this.matchedTours;
        if (!Array.isArray(list)) return [];
        if (this.categoryFilter === 'all') {
            return list;
        }
        return list.filter(t => t && ((t.category_slug === this.categoryFilter) || (t.category === this.categoryFilter)));
    },

    handleInput() {
        const q = this.query.trim();
        this.selectedIndex = -1;

        if (q.length < 2) {
            this.serverResults = [];
            this.serverQuery = '';
            this.loading = false;
            this.categoryFilter = 'all';
            return;
        }

        clearTimeout(this.debounceTimer);
        this.loading = true;
        this.debounceTimer = setTimeout(() => {
            this.fetchServerResults();
        }, 180);
    },

    async fetchServerResults() {
        const q = this.query.trim();
        if (q.length < 2) {
            this.loading = false;
            return;
        }

        try {
            const url = new URL(this.liveSearchUrl || '/search/live', window.location.origin);
            url.searchParams.set('q', q);
            if (this.categoryFilter !== 'all') {
                url.searchParams.set('category', this.categoryFilter);
            }

            let res = await fetch(url.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });

            // Fallback to AJAX gateway if direct live search endpoint returned non-200
            if (!res.ok) {
                const ajaxUrl = new URL('/ajax.php', window.location.origin);
                ajaxUrl.searchParams.set('action', 'live_search');
                ajaxUrl.searchParams.set('q', q);
                if (this.categoryFilter !== 'all') {
                    ajaxUrl.searchParams.set('category', this.categoryFilter);
                }
                res = await fetch(ajaxUrl.toString(), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
            }

            if (res.ok) {
                const data = await res.json();
                if (data && data.success && Array.isArray(data.results)) {
                    if (this.query.trim().toLowerCase() === q.toLowerCase()) {
                        this.serverResults = data.results;
                        this.serverQuery = q.toLowerCase();
                    }
                }
            }
        } catch (e) {
            // Silently fall back to client catalog
        } finally {
            this.loading = false;
        }
    },

    selectCategory(slug) {
        this.categoryFilter = slug;
        this.selectedIndex = -1;
    },

    clearQuery() {
        this.query = '';
        this.serverResults = [];
        this.serverQuery = '';
        this.categoryFilter = 'all';
        this.selectedIndex = -1;
        this.loading = false;
        this.$nextTick(() => {
            this.$refs.searchInput?.focus();
        });
    },

    setQuery(text) {
        this.query = text;
        this.categoryFilter = 'all';
        this.handleInput();
        this.$nextTick(() => {
            this.$refs.searchInput?.focus();
        });
    },

    navigateDown() {
        const count = this.filteredResults.length;
        if (count === 0) return;
        this.selectedIndex = (this.selectedIndex + 1) % count;
        this.scrollToSelected();
    },

    navigateUp() {
        const count = this.filteredResults.length;
        if (count === 0) return;
        this.selectedIndex = (this.selectedIndex - 1 + count) % count;
        this.scrollToSelected();
    },

    scrollToSelected() {
        this.$nextTick(() => {
            const listEl = this.$refs.resultsList;
            if (!listEl) return;
            const activeEl = listEl.querySelector('[data-selected="true"]');
            if (activeEl) {
                activeEl.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
        });
    },

    handleEnter(e) {
        if (this.selectedIndex >= 0 && this.filteredResults[this.selectedIndex]) {
            e.preventDefault();
            window.location.href = this.filteredResults[this.selectedIndex].url;
        }
        // Otherwise allow form to submit naturally
    },

    handleEscape() {
        if (this.query.trim().length > 0) {
            this.clearQuery();
        } else {
            Alpine.store('modal').close();
        }
    },

    highlightMatch(text) {
        if (!text) return '';
        const q = this.query.trim();
        if (q.length < 2) return text;

        const words = q.split(/\s+/).filter(w => w.length > 1).map(w => w.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
        if (words.length === 0) return text;

        try {
            const regex = new RegExp('(' + words.join('|') + ')', 'gi');
            return text.replace(regex, '<mark class="bg-amber-100 text-orange-950 font-black px-0.5 rounded">$1</mark>');
        } catch (e) {
            return text;
        }
    }
}));


// =============================================================================
// 7. INITIALIZE ALPINE & APP ENGINE
// =============================================================================

Alpine.start();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => App.init());
} else {
    App.init();
}

// Global spinner animation keyframe injection
const spinStyle = document.createElement('style');
spinStyle.textContent = '@keyframes spin{to{transform:rotate(360deg)}}.spin{animation:spin 1s linear infinite}';
document.head.appendChild(spinStyle);
