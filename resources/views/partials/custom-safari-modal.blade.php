<!-- Build Your Own Safari Customizer Modal -->
<div class="modal fade" id="customSafariModal" tabindex="-1" aria-labelledby="customSafariModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-sm-down">
        <div class="modal-content border-0 shadow-lg" style="background: #0B1120; border: 1.5px solid rgba(246, 144, 68, 0.4) !important; border-radius: 28px; color: #ffffff; overflow: hidden; box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.9);">
            
            <!-- Modal Header -->
            <div class="modal-header border-bottom border-white border-opacity-10 py-3 px-4 position-relative z-2 bg-dark">
                <div class="w-100 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge rounded-pill px-2.5 py-1 small" style="background: rgba(246, 144, 68, 0.2); border: 1px solid #F69044; color: #F69044; font-size: 11px;">
                                <i class="bi bi-sliders me-1"></i> Interactive Customizer
                            </span>
                            <span class="badge bg-success bg-opacity-75 rounded-pill px-2 py-0.5 small text-white" style="font-size: 10px;">
                                DET #1430583
                            </span>
                        </div>
                        <h5 class="modal-title fw-800 text-white mb-0" id="customSafariModalLabel">
                            Build Your Own Dubai Desert Safari
                        </h5>
                    </div>
                    <button type="button" class="btn btn-outline-light rounded-circle p-2 d-flex align-items-center justify-content-center shadow-none border-0" data-bs-dismiss="modal" aria-label="Close" style="background: rgba(255, 255, 255, 0.08); width: 38px; height: 38px;">
                        <i class="bi bi-x-lg fs-6 text-white"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body with Scrollable Area -->
            <div class="modal-body p-4 position-relative z-1" style="max-height: calc(85vh - 120px); overflow-y: auto;">
                <div class="row g-4">
                    
                    <!-- Left: Customizer Options -->
                    <div class="col-12 col-lg-7">
                        
                        <!-- Step 1: Base -->
                        <div class="mb-4">
                            <label class="fw-bold text-white small text-uppercase mb-2 d-block" style="letter-spacing: 0.5px;">
                                1. Select Base Experience
                            </label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="modal-custom-card p-3 rounded-4 cursor-pointer transition-all h-100 selected" data-group="modal-base" data-name="Standard Evening Red Dunes" data-price="150" data-tour-id="1">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span><i class="bi bi-sunset fs-5 text-warning"></i></span>
                                            <span class="badge bg-warning text-dark fw-bold rounded-pill" style="font-size: 9px;">Popular</span>
                                        </div>
                                        <div class="fw-bold text-white small lh-1 mb-1">Standard Evening</div>
                                        <div class="text-white-50" style="font-size: 11px;">Dune bashing & BBQ show</div>
                                        <div class="fw-bold text-warning small mt-2" data-aed="150">AED 150/guest</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="modal-custom-card p-3 rounded-4 cursor-pointer transition-all h-100" data-group="modal-base" data-name="VIP Luxury Evening Safari" data-price="250" data-tour-id="2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span><i class="bi bi-award fs-5 text-warning"></i></span>
                                            <span class="badge bg-dark text-warning border border-warning rounded-pill" style="font-size: 9px;">VIP</span>
                                        </div>
                                        <div class="fw-bold text-white small lh-1 mb-1">VIP Luxury Safari</div>
                                        <div class="text-white-50" style="font-size: 11px;">VIP table & waiter service</div>
                                        <div class="fw-bold text-warning small mt-2" data-aed="250">AED 250/guest</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="modal-custom-card p-3 rounded-4 cursor-pointer transition-all h-100" data-group="modal-base" data-name="Morning Desert Safari" data-price="120" data-tour-id="4">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span><i class="bi bi-sunrise fs-5 text-warning"></i></span>
                                            <span class="badge bg-info text-white rounded-pill" style="font-size: 9px;">Cooler</span>
                                        </div>
                                        <div class="fw-bold text-white small lh-1 mb-1">Morning Safari</div>
                                        <div class="text-white-50" style="font-size: 11px;">Cool air & sunrise photos</div>
                                        <div class="fw-bold text-warning small mt-2" data-aed="120">AED 120/guest</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="modal-custom-card p-3 rounded-4 cursor-pointer transition-all h-100" data-group="modal-base" data-name="Overnight Stargazing Safari" data-price="350" data-tour-id="5">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span><i class="bi bi-moon-stars fs-5 text-warning"></i></span>
                                            <span class="badge bg-success text-white rounded-pill" style="font-size: 9px;">Glamping</span>
                                        </div>
                                        <div class="fw-bold text-white small lh-1 mb-1">Overnight Safari</div>
                                        <div class="text-white-50" style="font-size: 11px;">Bedouin tent & breakfast</div>
                                        <div class="fw-bold text-warning small mt-2" data-aed="350">AED 350/guest</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Vehicle -->
                        <div class="mb-4">
                            <label class="fw-bold text-white small text-uppercase mb-2 d-block" style="letter-spacing: 0.5px;">
                                2. Transfer Option
                            </label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="modal-custom-card p-2.5 rounded-3 cursor-pointer transition-all h-100 selected" data-group="modal-transfer" data-name="Shared 4x4 Land Cruiser" data-price="0" data-type="flat">
                                        <div class="fw-bold text-white small">Shared 4x4</div>
                                        <div class="text-success fw-bold" style="font-size: 10px;">FREE</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="modal-custom-card p-2.5 rounded-3 cursor-pointer transition-all h-100" data-group="modal-transfer" data-name="Private 7-Seater 4x4" data-price="350" data-type="flat">
                                        <div class="fw-bold text-white small">Private 4x4</div>
                                        <div class="text-warning fw-bold" style="font-size: 10px;" data-aed="350">+AED 350 flat</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="modal-custom-card p-2.5 rounded-3 cursor-pointer transition-all h-100" data-group="modal-transfer" data-name="VIP Range Rover" data-price="750" data-type="flat">
                                        <div class="fw-bold text-white small">VIP SUV</div>
                                        <div class="text-warning fw-bold" style="font-size: 10px;" data-aed="750">+AED 750 flat</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Sports -->
                        <div class="mb-4">
                            <label class="fw-bold text-white small text-uppercase mb-2 d-block" style="letter-spacing: 0.5px;">
                                3. Desert Motorsports
                            </label>
                            <div class="row g-2">
                                <div class="col-3">
                                    <div class="modal-custom-card p-2 rounded-3 cursor-pointer transition-all h-100 selected" data-group="modal-sports" data-name="Scenic Only" data-price="0" data-type="per_person">
                                        <div class="fw-bold text-white" style="font-size: 11px;">None</div>
                                        <div class="text-success fw-bold" style="font-size: 9px;">INCLUDED</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="modal-custom-card p-2 rounded-3 cursor-pointer transition-all h-100" data-group="modal-sports" data-name="250cc Quad Biking" data-price="120" data-type="per_person">
                                        <div class="fw-bold text-white" style="font-size: 11px;">250cc Quad</div>
                                        <div class="text-warning fw-bold" style="font-size: 9px;" data-aed="120">+AED 120</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="modal-custom-card p-2 rounded-3 cursor-pointer transition-all h-100" data-group="modal-sports" data-name="400cc Quad Biking" data-price="220" data-type="per_person">
                                        <div class="fw-bold text-white" style="font-size: 11px;">400cc Quad</div>
                                        <div class="text-warning fw-bold" style="font-size: 9px;" data-aed="220">+AED 220</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="modal-custom-card p-2 rounded-3 cursor-pointer transition-all h-100" data-group="modal-sports" data-name="1000cc Can-Am Buggy" data-price="550" data-type="flat">
                                        <div class="fw-bold text-white" style="font-size: 11px;">1000cc Buggy</div>
                                        <div class="text-warning fw-bold" style="font-size: 9px;" data-aed="550">+AED 550</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Addons -->
                        <div class="mb-3">
                            <label class="fw-bold text-white small text-uppercase mb-2 d-block" style="letter-spacing: 0.5px;">
                                4. Optional Camp Luxuries
                            </label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="modal-addon-item p-2 rounded-3 border d-flex align-items-center justify-content-between cursor-pointer" data-addon="vip_table" data-name="VIP Table Service" data-price="60" data-type="per_person" style="background: rgba(30, 41, 59, 0.6); border-color: rgba(255, 255, 255, 0.1) !important;">
                                        <div class="d-flex align-items-center gap-2">
                                            <input class="form-check-input m-0 modal-addon-check" type="checkbox">
                                            <span class="text-white small" style="font-size: 11px;">VIP Table & Waiter</span>
                                        </div>
                                        <span class="text-warning small" style="font-size: 10px;" data-aed="60">+AED 60</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="modal-addon-item p-2 rounded-3 border d-flex align-items-center justify-content-between cursor-pointer" data-addon="shisha" data-name="Private Table Shisha" data-price="50" data-type="flat" style="background: rgba(30, 41, 59, 0.6); border-color: rgba(255, 255, 255, 0.1) !important;">
                                        <div class="d-flex align-items-center gap-2">
                                            <input class="form-check-input m-0 modal-addon-check" type="checkbox">
                                            <span class="text-white small" style="font-size: 11px;">Table Shisha</span>
                                        </div>
                                        <span class="text-warning small" style="font-size: 10px;" data-aed="50">+AED 50</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right: Summary & Instant Checkout -->
                    <div class="col-12 col-lg-5">
                        <div class="card border-0 rounded-4 p-3 h-100" style="background: rgba(15, 23, 42, 0.85); border: 1.5px solid rgba(246, 144, 68, 0.3) !important;">
                            
                            <!-- Guest Counter -->
                            <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3 mb-3" style="background: rgba(255, 255, 255, 0.05);">
                                <div>
                                    <div class="fw-bold text-white small">Guests (Adults)</div>
                                    <small class="text-white-50" style="font-size: 10px;">Age 11+ years</small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle p-0 text-white" id="modalAdultsMinus" style="width: 28px; height: 28px;">-</button>
                                    <span class="fw-bold fs-6 px-1" id="modalAdultsCount">2</span>
                                    <button type="button" class="btn btn-sm btn-outline-warning rounded-circle p-0 text-warning" id="modalAdultsPlus" style="width: 28px; height: 28px;">+</button>
                                </div>
                            </div>

                            <!-- Live Specs Breakdown -->
                            <div class="mb-3 small" style="font-size: 12px;">
                                <div class="d-flex justify-content-between text-white-50 mb-1">
                                    <span>Base:</span>
                                    <strong class="text-white" id="modalSummaryBase">Standard Evening</strong>
                                </div>
                                <div class="d-flex justify-content-between text-white-50 mb-1">
                                    <span>Transfer:</span>
                                    <strong class="text-white" id="modalSummaryTransfer">Shared 4x4</strong>
                                </div>
                                <div class="d-flex justify-content-between text-white-50 mb-1">
                                    <span>Sports:</span>
                                    <strong class="text-white" id="modalSummarySports">Scenic Only</strong>
                                </div>
                                <div class="d-flex justify-content-between text-white-50 mb-1">
                                    <span>Addons:</span>
                                    <strong class="text-white" id="modalSummaryAddons">None</strong>
                                </div>
                            </div>

                            <!-- Total Display -->
                            <div class="pt-2 border-top border-white border-opacity-10 mb-3 text-center">
                                <small class="text-white-50 d-block" style="font-size: 10px; text-transform: uppercase; font-weight: 700;">Live Estimated Total</small>
                                <div class="display-6 fw-800 text-warning lh-1 my-1" id="modalCustomizerTotal" data-aed="300">AED 300</div>
                                <small class="text-success fw-bold" style="font-size: 10px;"><i class="bi bi-shield-check me-1"></i>Best Price Guarantee • Free Cancel 24h</small>
                            </div>

                            <!-- CTAs -->
                            <div class="d-grid gap-2 mt-auto">
                                <button type="button" class="btn btn-desert-animated w-100 py-2.5 rounded-pill fw-bold small" id="modalCustomizerBookBtn">
                                    <i class="bi bi-calendar-check-fill me-1"></i> Book Custom Safari
                                </button>
                                <a href="#" target="_blank" rel="noopener" class="btn btn-whatsapp-animated w-100 py-2 rounded-pill fw-bold small" id="modalCustomizerWaBtn">
                                    <i class="bi bi-whatsapp me-1"></i> WhatsApp Inquire
                                </a>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<style>
.modal-custom-card {
    background: rgba(30, 41, 59, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.08);
}
.modal-custom-card:hover {
    border-color: #F69044 !important;
}
.modal-custom-card.selected {
    border-color: #F69044 !important;
    background: rgba(246, 144, 68, 0.18) !important;
}
.modal-addon-item.selected {
    border-color: #F69044 !important;
    background: rgba(246, 144, 68, 0.18) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('customSafariModal');
    if (!modalEl) return;

    const modalState = {
        base: { name: 'Standard Evening Red Dunes', price: 150, tourId: 1 },
        transfer: { name: 'Shared 4x4 Land Cruiser', price: 0, type: 'flat' },
        sports: { name: 'Scenic Only', price: 0, type: 'per_person' },
        addons: [],
        adults: 2
    };

    const adultsCountEl = document.getElementById('modalAdultsCount');
    const summaryBaseEl = document.getElementById('modalSummaryBase');
    const summaryTransferEl = document.getElementById('modalSummaryTransfer');
    const summarySportsEl = document.getElementById('modalSummarySports');
    const summaryAddonsEl = document.getElementById('modalSummaryAddons');
    const totalEl = document.getElementById('modalCustomizerTotal');
    const bookBtn = document.getElementById('modalCustomizerBookBtn');
    const waBtn = document.getElementById('modalCustomizerWaBtn');

    // Radio groups
    ['modal-base', 'modal-transfer', 'modal-sports'].forEach(group => {
        document.querySelectorAll(`.modal-custom-card[data-group="${group}"]`).forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll(`.modal-custom-card[data-group="${group}"]`).forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');

                const key = group.replace('modal-', '');
                modalState[key] = {
                    name: this.getAttribute('data-name'),
                    price: parseFloat(this.getAttribute('data-price') || 0),
                    type: this.getAttribute('data-type') || 'per_person',
                    tourId: this.getAttribute('data-tour-id') || null
                };

                updateModalCalc();
            });
        });
    });

    // Addons
    document.querySelectorAll('.modal-addon-item').forEach(item => {
        const chk = item.querySelector('.modal-addon-check');
        item.addEventListener('click', function(e) {
            if (e.target !== chk) chk.checked = !chk.checked;
            toggleModalAddon(item, chk.checked);
        });
        if (chk) {
            chk.addEventListener('change', function() {
                toggleModalAddon(item, this.checked);
            });
        }
    });

    function toggleModalAddon(item, isChecked) {
        const addonKey = item.getAttribute('data-addon');
        const name = item.getAttribute('data-name');
        const price = parseFloat(item.getAttribute('data-price') || 0);
        const type = item.getAttribute('data-type') || 'per_person';

        if (isChecked) {
            item.classList.add('selected');
            if (!modalState.addons.find(a => a.key === addonKey)) {
                modalState.addons.push({ key: addonKey, name, price, type });
            }
        } else {
            item.classList.remove('selected');
            modalState.addons = modalState.addons.filter(a => a.key !== addonKey);
        }

        updateModalCalc();
    }

    // Guest counter
    document.getElementById('modalAdultsMinus').addEventListener('click', () => {
        if (modalState.adults > 1) {
            modalState.adults--;
            adultsCountEl.innerText = modalState.adults;
            updateModalCalc();
        }
    });
    document.getElementById('modalAdultsPlus').addEventListener('click', () => {
        if (modalState.adults < 30) {
            modalState.adults++;
            adultsCountEl.innerText = modalState.adults;
            updateModalCalc();
        }
    });

    function updateModalCalc() {
        let baseTotal = modalState.base.price * modalState.adults;
        let transferTotal = (modalState.transfer.type === 'flat') ? modalState.transfer.price : (modalState.transfer.price * modalState.adults);
        let sportsTotal = (modalState.sports.type === 'flat') ? modalState.sports.price : (modalState.sports.price * modalState.adults);
        let addonsTotal = 0;
        modalState.addons.forEach(a => {
            addonsTotal += (a.type === 'flat') ? a.price : (a.price * modalState.adults);
        });

        let grandTotal = baseTotal + transferTotal + sportsTotal + addonsTotal;

        summaryBaseEl.innerText = modalState.base.name;
        summaryTransferEl.innerText = modalState.transfer.name;
        summarySportsEl.innerText = modalState.sports.name;
        summaryAddonsEl.innerText = modalState.addons.length ? modalState.addons.map(a => a.name).join(', ') : 'None';

        totalEl.setAttribute('data-aed', grandTotal);
        totalEl.innerText = `AED ${grandTotal}`;

        if (window.DunesApp && typeof window.DunesApp.updatePrices === 'function') {
            window.DunesApp.updatePrices();
        }

        // WhatsApp
        const waMsg = `Hi Dunes Discovery! I configured a custom safari: Base: ${encodeURIComponent(modalState.base.name)}, Vehicle: ${encodeURIComponent(modalState.transfer.name)}, Sports: ${encodeURIComponent(modalState.sports.name)}, Addons: ${encodeURIComponent(summaryAddonsEl.innerText)}, Guests: ${modalState.adults} Adults, Total: AED ${grandTotal}. Can you check availability?`;
        waBtn.href = `https://wa.me/{{ preg_replace('/[^0-9]/','',(string)($settings['site_whatsapp'] ?? '971502456056')) }}?text=${waMsg}`;
    }

    if (bookBtn) {
        bookBtn.addEventListener('click', function() {
            const m = bootstrap.Modal.getInstance(modalEl);
            if (m) m.hide();

            setTimeout(() => {
                const bookingModalEl = document.getElementById('bookingModal');
                if (!bookingModalEl) return;

                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const bModal = bootstrap.Modal.getOrCreateInstance(bookingModalEl);
                    bModal.show();
                }

                if (modalState.base.tourId) {
                    const tourSelect = document.getElementById('bookingTour');
                    if (tourSelect) {
                        tourSelect.value = modalState.base.tourId;
                        tourSelect.dispatchEvent(new Event('change'));
                    }
                }

                const adultsInput = document.getElementById('bookingAdults');
                if (adultsInput) adultsInput.value = modalState.adults;

                const requestsInput = document.getElementById('bookingRequests');
                if (requestsInput) {
                    requestsInput.value = `[CUSTOM BUILDER SPEC]\nVehicle: ${modalState.transfer.name}\nMotorsports: ${modalState.sports.name}\nAddons: ${summaryAddonsEl.innerText}\nEstimated Total: AED ${totalEl.getAttribute('data-aed') || totalEl.innerText}`;
                }
            }, 350);
        });
    }

    window.openSafariCustomizer = function() {
        const m = bootstrap.Modal.getOrCreateInstance(modalEl);
        m.show();
    };

    updateModalCalc();
});
</script>
