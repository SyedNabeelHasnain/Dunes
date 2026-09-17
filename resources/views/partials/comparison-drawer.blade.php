@php
    if (!function_exists('try_get_compare_tours')) {
        function try_get_compare_tours() {
            try {
                return \App\Models\Tour::where('status', 'active')->with(['tiers'])->orderBy('priority', 'asc')->get();
            } catch (\Throwable $e) {
                return collect();
            }
        }
    }
    $sourceTours = (isset($allTours) && count($allTours)) ? $allTours : try_get_compare_tours();
    $compareToursData = $sourceTours->map(function($t) {
        $minPrice = $t->tiers ? $t->tiers->pluck('pivot.price')->filter(fn($p) => (float)$p > 0)->min() : 99;
        $minPrice = $minPrice ? (float)$minPrice : 99;
        
        $nameLower = strtolower($t->name);
        $slugLower = strtolower($t->slug);

        // Vehicle type inference
        if (str_contains($slugLower, 'buggy') || str_contains($nameLower, 'buggy')) {
            $vehicle = '1000cc Can-Am / Polaris Buggy + 4x4 Transfer';
            $duneBashing = 'Self-Drive Guided High Dunes (1-2 Hours)';
            $dining = 'Cold Water & Soft Drinks (Buffet Optional)';
            $shows = 'Optional Camp Addon';
            $inclusions = 'Safety Helmet, Goggles, Sandboarding, Desert Guide';
        } elseif (str_contains($slugLower, 'quad') || str_contains($nameLower, 'quad')) {
            $vehicle = '4x4 Land Cruiser Pickup + 350cc/400cc Quad Bike';
            $duneBashing = '30-60 Mins Quad Track + 4x4 Dune Bashing';
            $dining = '5-Star Live BBQ Buffet (Veg, Non-Veg & Jain)';
            $shows = 'Live Tanoura, Fire Dance & Belly Dance';
            $inclusions = 'Quad Bike, Camel Ride, Sandboarding, Henna, BBQ Dinner';
        } elseif (str_contains($slugLower, 'vip') || str_contains($nameLower, 'vip') || str_contains($nameLower, 'luxury')) {
            $vehicle = 'Luxury 4x4 Toyota Land Cruiser (Doorstep Pickup)';
            $duneBashing = '45 Mins Extreme High Red Dunes (Lahbab)';
            $dining = 'VIP Raised Table with Private Waiter & 5-Star Live BBQ';
            $shows = 'Priority Front-Row Live Shows (Tanoura, Fire, Belly)';
            $inclusions = 'VIP Table Service, Camel Ride, Sandboarding, Henna, Shisha';
        } elseif (str_contains($slugLower, 'morning') || str_contains($nameLower, 'morning')) {
            $vehicle = '4x4 Toyota Land Cruiser (Doorstep Hotel Pickup)';
            $duneBashing = '40 Mins Morning High Red Dunes';
            $dining = 'Light Arabic Breakfast & Refreshments';
            $shows = 'Not Applicable (Morning Safari)';
            $inclusions = 'Camel Riding, Sandboarding, Arabic Coffee & Photography';
        } elseif (str_contains($slugLower, 'overnight') || str_contains($nameLower, 'overnight')) {
            $vehicle = '4x4 Toyota Land Cruiser (Doorstep Hotel Pickup)';
            $duneBashing = '45 Mins Sunset High Red Dunes';
            $dining = 'Live BBQ Dinner + Fresh Morning Bedouin Breakfast';
            $shows = 'Full Evening Cultural Shows + Stargazing by Campfire';
            $inclusions = 'Overnight Bedouin Tent, Sleeping Bag, Sunrise Watch, BBQ';
        } else {
            $vehicle = '4x4 Toyota Land Cruiser (Doorstep Hotel Pickup)';
            $duneBashing = '40-45 Mins High Red Dunes (Lahbab Desert)';
            $dining = '5-Star Live BBQ Buffet (Veg, Non-Veg & Jain options)';
            $shows = '3 Live Cultural Shows (Tanoura, Fire & Belly Dance)';
            $inclusions = 'Camel Riding, Sandboarding, Henna, Arabic Coffee, BBQ';
        }

        $thumb = $t->thumb_image ? asset('images/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $t->thumb_image)) : asset('images/desert-safari-poster.avif');

        return [
            'id' => (string)$t->id,
            'name' => $t->name,
            'slug' => $t->slug,
            'thumb' => $thumb,
            'duration' => $t->duration ?: '6-7 Hours',
            'rating' => (float)($t->rating ?: 4.9),
            'min_price' => $minPrice,
            'is_bestseller' => (bool)$t->is_bestseller,
            'vehicle' => $vehicle,
            'dune_bashing' => $duneBashing,
            'dining' => $dining,
            'shows' => $shows,
            'inclusions' => $inclusions,
        ];
    });
@endphp

<!-- Comparison Dataset Script -->
<script id="dunesCompareTourData" type="application/json">
{!! json_encode($compareToursData) !!}
</script>

<!-- Floating Comparison Bar (Sticky Bottom Pill) -->
<div id="compareFloatingBar" class="position-fixed bottom-0 start-50 translate-middle-x mb-4 z-3 d-none transition-all" style="max-width: 92%; width: auto;">
    <div class="d-flex align-items-center gap-3 px-3 py-2.5 rounded-pill shadow-lg" style="background: rgba(15, 23, 42, 0.95); border: 1.5px solid #F69044; backdrop-filter: blur(16px);">
        <div class="d-flex align-items-center gap-2" id="compareThumbBubbles"></div>
        <div class="text-white small fw-bold pe-2 border-end border-white border-opacity-20 d-none d-sm-block">
            <span id="compareCountLabel">0</span>/3 Selected
        </div>
        <button type="button" class="btn btn-sm btn-desert-animated rounded-pill px-3 py-1.5 fw-bold text-nowrap" id="openCompareDrawerBtn" data-bs-toggle="offcanvas" data-bs-target="#compareDrawer">
            <i class="bi bi-shuffle me-1"></i> Compare Safaris
        </button>
        <button type="button" class="btn btn-link text-white-50 p-0 text-decoration-none small" id="clearCompareBtn" title="Clear all">
            <i class="bi bi-x-circle-fill"></i>
        </button>
    </div>
</div>

<!-- Comparison Drawer (Offcanvas Bottom) -->
<div class="offcanvas offcanvas-bottom border-0 text-white" tabindex="-1" id="compareDrawer" aria-labelledby="compareDrawerLabel" style="height: 85vh; background: #0B1120; border-top: 2px solid #F69044 !important; border-radius: 28px 28px 0 0;">
    <div class="offcanvas-header border-bottom border-white border-opacity-10 py-3 px-4 sticky-top bg-dark bg-opacity-95">
        <div class="d-flex align-items-center gap-2">
            <span class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: rgba(246, 144, 68, 0.2); color: #F69044;">
                <i class="bi bi-shuffle fs-5"></i>
            </span>
            <div>
                <h5 class="offcanvas-title fw-bold text-white mb-0" id="compareDrawerLabel">Compare Safari Experiences</h5>
                <small class="text-white-50">Side-by-side comparison of vehicles, dune bashing, dinner & entertainment</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1 text-nowrap" id="drawerPresetBtn">
                <i class="bi bi-lightning-fill text-warning me-1"></i> Compare Top 3
            </button>
            <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
    </div>

    <div class="offcanvas-body p-3 p-md-4 overflow-y-auto">
        <!-- Empty State -->
        <div id="compareEmptyState" class="text-center py-5 d-none">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background: rgba(255, 255, 255, 0.05); color: #94A3B8;">
                <i class="bi bi-compass fs-2"></i>
            </div>
            <h5 class="fw-bold text-white mb-2">No Safaris Selected for Comparison</h5>
            <p class="text-white-50 small mb-4" style="max-width: 480px; margin: 0 auto;">
                Click the <strong>"+ Compare"</strong> button on any safari package card, or click below to analyze our Top 3 most popular Dubai experiences side-by-side:
            </p>
            <button type="button" class="btn btn-desert-animated rounded-pill px-4 py-2.5 fw-bold" onclick="window.DunesCompare.loadBestsellers()">
                <i class="bi bi-stars me-1.5"></i> Compare Top 3 Bestsellers
            </button>
        </div>

        <!-- Comparison Table / Grid -->
        <div id="compareTableWrapper" class="table-responsive">
            <table class="table table-dark table-borderless align-middle mb-0" id="compareTable" style="background: transparent; min-width: 680px;">
                <thead>
                    <tr id="compareRowHeader" class="border-bottom border-white border-opacity-10">
                        <th style="width: 180px; min-width: 180px;" class="text-white-50 small text-uppercase fw-bold pb-3">Feature</th>
                        <!-- Dynamic Tour Columns injected by JS -->
                    </tr>
                </thead>
                <tbody>
                    <tr id="compareRowPrice" class="border-bottom border-white border-opacity-5">
                        <td class="text-white-50 small fw-bold py-3"><i class="bi bi-cash-stack text-warning me-2"></i>Starting Price</td>
                    </tr>
                    <tr id="compareRowDuration" class="border-bottom border-white border-opacity-5">
                        <td class="text-white-50 small fw-bold py-3"><i class="bi bi-clock-history text-warning me-2"></i>Duration</td>
                    </tr>
                    <tr id="compareRowVehicle" class="border-bottom border-white border-opacity-5">
                        <td class="text-white-50 small fw-bold py-3"><i class="bi bi-truck text-warning me-2"></i>Transfer & Vehicle</td>
                    </tr>
                    <tr id="compareRowDuneBashing" class="border-bottom border-white border-opacity-5">
                        <td class="text-white-50 small fw-bold py-3"><i class="bi bi-speedometer2 text-warning me-2"></i>Dune Bashing</td>
                    </tr>
                    <tr id="compareRowDining" class="border-bottom border-white border-opacity-5">
                        <td class="text-white-50 small fw-bold py-3"><i class="bi bi-cup-hot-fill text-warning me-2"></i>Camp & Dinner</td>
                    </tr>
                    <tr id="compareRowShows" class="border-bottom border-white border-opacity-5">
                        <td class="text-white-50 small fw-bold py-3"><i class="bi bi-fire text-warning me-2"></i>Live Shows</td>
                    </tr>
                    <tr id="compareRowInclusions" class="border-bottom border-white border-opacity-5">
                        <td class="text-white-50 small fw-bold py-3"><i class="bi bi-gift-fill text-warning me-2"></i>Inclusions</td>
                    </tr>
                    <tr id="compareRowCancellation" class="border-bottom border-white border-opacity-5">
                        <td class="text-white-50 small fw-bold py-3"><i class="bi bi-shield-check text-success me-2"></i>Cancellation</td>
                    </tr>
                    <tr id="compareRowAction">
                        <td class="text-white-50 small fw-bold py-3">Book Experience</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    'use strict';

    let allToursDataset = [];
    try {
        const raw = document.getElementById('dunesCompareTourData')?.textContent;
        if (raw) allToursDataset = JSON.parse(raw);
    } catch(e) {
        allToursDataset = [];
    }

    const DunesCompare = {
        storageKey: 'dunes_compare_ids',
        selectedIds: [],

        init() {
            try {
                const stored = localStorage.getItem(this.storageKey);
                if (stored) {
                    this.selectedIds = JSON.parse(stored).slice(0, 3);
                }
            } catch (e) {
                this.selectedIds = [];
            }

            this.bindEvents();
            this.syncUI();
        },

        bindEvents() {
            document.getElementById('clearCompareBtn')?.addEventListener('click', () => {
                this.clear();
            });

            document.getElementById('drawerPresetBtn')?.addEventListener('click', () => {
                this.loadBestsellers();
            });

            document.getElementById('compareDrawer')?.addEventListener('show.bs.offcanvas', () => {
                this.renderDrawer();
            });
        },

        save() {
            try {
                localStorage.setItem(this.storageKey, JSON.stringify(this.selectedIds));
            } catch(e) {}
            this.syncUI();
        },

        toggle(btn) {
            const tourId = String(btn.dataset.tourId);
            if (this.selectedIds.includes(tourId)) {
                this.remove(tourId);
            } else {
                this.add(tourId);
            }
        },

        add(tourId) {
            tourId = String(tourId);
            if (this.selectedIds.includes(tourId)) return;

            if (this.selectedIds.length >= 3) {
                if (window.App && typeof window.App.toast === 'function') {
                    window.App.toast('You can compare up to 3 safaris at a time. Please remove one first.', 'warning');
                }
                return;
            }

            this.selectedIds.push(tourId);
            this.save();

            if (window.App && typeof window.App.toast === 'function') {
                const t = allToursDataset.find(x => x.id === tourId);
                window.App.toast(`Added "${t ? t.name : 'Tour'}" to comparison`, 'success');
            }
        },

        remove(tourId) {
            tourId = String(tourId);
            this.selectedIds = this.selectedIds.filter(id => id !== tourId);
            this.save();
            this.renderDrawer();
        },

        clear() {
            this.selectedIds = [];
            this.save();
            this.renderDrawer();
        },

        loadBestsellers() {
            const bestsellers = allToursDataset.filter(t => t.is_bestseller).slice(0, 3);
            if (bestsellers.length > 0) {
                this.selectedIds = bestsellers.map(t => t.id);
            } else {
                this.selectedIds = allToursDataset.slice(0, 3).map(t => t.id);
            }
            this.save();
            this.renderDrawer();
        },

        syncUI() {
            const count = this.selectedIds.length;
            const bar = document.getElementById('compareFloatingBar');
            const countLabel = document.getElementById('compareCountLabel');
            const bubbles = document.getElementById('compareThumbBubbles');

            if (countLabel) countLabel.textContent = count;

            if (bubbles) {
                bubbles.innerHTML = '';
                this.selectedIds.forEach(id => {
                    const t = allToursDataset.find(x => x.id === id);
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

            // Sync "+ Compare" button states on page
            document.querySelectorAll('.btn-toggle-compare').forEach(btn => {
                const tid = String(btn.dataset.tourId);
                const textSpan = btn.querySelector('.compare-btn-text');
                if (this.selectedIds.includes(tid)) {
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

            if (this.selectedIds.length === 0) {
                if (emptyState) emptyState.classList.remove('d-none');
                if (tableWrap) tableWrap.classList.add('d-none');
                return;
            }

            if (emptyState) emptyState.classList.add('d-none');
            if (tableWrap) tableWrap.classList.remove('d-none');

            const selectedTours = this.selectedIds.map(id => allToursDataset.find(x => x.id === id)).filter(Boolean);

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

            // Reset dynamic cells, preserve first feature cell
            [rowHeader, rowPrice, rowDuration, rowVehicle, rowDuneBashing, rowDining, rowShows, rowInclusions, rowCancellation, rowAction].forEach(row => {
                while (row.children.length > 1) {
                    row.removeChild(row.lastChild);
                }
            });

            selectedTours.forEach(tour => {
                // Header cell
                const th = document.createElement('th');
                th.style.width = `${Math.floor(100 / (selectedTours.length + 1))}%`;
                th.style.verticalAlign = 'top';
                th.className = 'pb-3';
                th.innerHTML = `
                    <div class="position-relative mb-2 rounded-3 overflow-hidden" style="height: 120px;">
                        <img src="${tour.thumb}" alt="${tour.name}" style="width: 100%; height: 100%; object-fit: cover;">
                        <button type="button" class="btn btn-sm btn-dark rounded-circle position-absolute top-0 end-0 m-1 p-0 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;" onclick="window.DunesCompare.remove('${tour.id}')" title="Remove from comparison">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <h6 class="fw-bold text-white mb-1 line-clamp-1">${tour.name}</h6>
                    <small class="text-warning"><i class="bi bi-star-fill me-1"></i>${tour.rating} (480+ Reviews)</small>
                `;
                rowHeader.appendChild(th);

                // Price cell
                const tdPrice = document.createElement('td');
                tdPrice.className = 'py-3';
                tdPrice.innerHTML = `<span class="h5 fw-bold text-warning" data-aed="${tour.min_price}">AED ${Math.round(tour.min_price)}</span> <small class="text-white-50">/ person</small>`;
                rowPrice.appendChild(tdPrice);

                // Duration cell
                const tdDuration = document.createElement('td');
                tdDuration.className = 'py-3 text-light small';
                tdDuration.textContent = tour.duration;
                rowDuration.appendChild(tdDuration);

                // Vehicle cell
                const tdVehicle = document.createElement('td');
                tdVehicle.className = 'py-3 text-light small';
                tdVehicle.textContent = tour.vehicle;
                rowVehicle.appendChild(tdVehicle);

                // Dune Bashing cell
                const tdDune = document.createElement('td');
                tdDune.className = 'py-3 text-light small';
                tdDune.textContent = tour.dune_bashing;
                rowDuneBashing.appendChild(tdDune);

                // Dining cell
                const tdDining = document.createElement('td');
                tdDining.className = 'py-3 text-light small';
                tdDining.textContent = tour.dining;
                rowDining.appendChild(tdDining);

                // Shows cell
                const tdShows = document.createElement('td');
                tdShows.className = 'py-3 text-light small';
                tdShows.textContent = tour.shows;
                rowShows.appendChild(tdShows);

                // Inclusions cell
                const tdInc = document.createElement('td');
                tdInc.className = 'py-3 text-light small';
                tdInc.textContent = tour.inclusions;
                rowInclusions.appendChild(tdInc);

                // Cancellation cell
                const tdCancel = document.createElement('td');
                tdCancel.className = 'py-3 text-success small fw-bold';
                tdCancel.innerHTML = '<i class="bi bi-check2-circle me-1"></i>100% Free Refund up to 24h prior';
                rowCancellation.appendChild(tdCancel);

                // Action cell
                const tdAction = document.createElement('td');
                tdAction.className = 'py-3';
                tdAction.innerHTML = `
                    <button type="button" class="btn btn-desert-animated w-100 rounded-pill py-2 fw-bold text-nowrap small shadow-sm" onclick="bootstrap.Offcanvas.getInstance(document.getElementById('compareDrawer'))?.hide(); if(window.App && typeof window.App.openBooking === 'function'){ window.App.openBooking('${tour.id}'); }">
                        <i class="bi bi-calendar-check me-1"></i> Book This Tour
                    </button>
                `;
                rowAction.appendChild(tdAction);
            });

            // Re-run currency conversion on newly injected price elements
            if (window.App && typeof window.App.convertAllPrices === 'function' && window.App.currency) {
                window.App.convertAllPrices(window.App.currency.code);
            }
        }
    };

    window.DunesCompare = DunesCompare;
    document.addEventListener('DOMContentLoaded', () => {
        DunesCompare.init();
    });
})();
</script>
@endpush
