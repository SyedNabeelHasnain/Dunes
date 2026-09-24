@php
    if (!function_exists('try_get_modal_tours')) {
        function try_get_modal_tours() {
            try {
                return \App\Models\Tour::where('status', 'active')->orderBy('priority', 'asc')->get();
            } catch (\Throwable $e) {
                return collect();
            }
        }
    }
    $modalTours = isset($allTours) && count($allTours) ? $allTours : try_get_modal_tours();
    $minDate = date('Y-m-d', strtotime('+1 day'));
    $ziinaActive = isset($settings['ziina_active']) ? ($settings['ziina_active'] === '1') : false;
    $advancePercent = (int)($settings['ziina_advance_percent'] ?? 10);
@endphp

<!-- Global Interactive Booking Modal (Tailwind v4 + Alpine.js) -->
<div id="bookingModal" 
     x-data="{}" 
     x-show="$store.modal.active === 'booking'" 
     x-cloak
     style="display: none;"
     class="fixed inset-0 z-50 overflow-y-auto" 
     role="dialog" 
     aria-modal="true"
     @keydown.escape.window="$store.modal.close()">

    <!-- Backdrop -->
    <div x-show="$store.modal.active === 'booking'"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity z-0"
         @click="$store.modal.close()"></div>

    <!-- Modal Dialog Panel (Bottom Sheet on Mobile, Centered Modal on Desktop) -->
    <div class="relative z-10 min-h-full flex items-end sm:items-center justify-center p-0 sm:p-4 text-center">
        <div x-show="$store.modal.active === 'booking'"
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-y-full sm:translate-y-4 sm:scale-95 opacity-0"
             x-transition:enter-end="translate-y-0 sm:scale-100 opacity-100"
             x-transition:leave="transform transition ease-in duration-200"
             x-transition:leave-start="translate-y-0 sm:scale-100 opacity-100"
             x-transition:leave-end="translate-y-full sm:translate-y-4 sm:scale-95 opacity-0"
             class="relative z-10 w-full sm:max-w-2xl lg:max-w-3xl rounded-t-3xl sm:rounded-3xl bg-white text-left align-middle shadow-2xl transition-all border border-slate-200 overflow-hidden flex flex-col max-h-[92vh] sm:max-h-[88vh]"
             @click.stop>

            <!-- Modal Header -->
            <div class="flex items-center justify-between py-3.5 px-4 sm:px-6 border-b border-slate-200 bg-white sticky top-0 z-20 shrink-0">
                <div class="flex items-center gap-3">
                    <button type="button" class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-colors cursor-pointer hidden" id="headerBackBtn">
                        <i class="bi bi-chevron-left text-sm"></i>
                    </button>
                    <div>
                        <h5 class="text-base sm:text-lg font-black text-slate-900 leading-tight" id="bookingModalTitle">Book Your Adventure</h5>
                        <div class="text-primary text-xs font-extrabold hidden" id="bookingModalSubtitle">Step 1 of 2</div>
                    </div>
                </div>
                <button type="button" 
                        class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:text-slate-800 hover:bg-slate-200 flex items-center justify-center transition-colors cursor-pointer" 
                        @click="$store.modal.close()" 
                        aria-label="Close">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <!-- Modal Body & Form -->
            <div class="flex-1 overflow-y-auto bg-slate-50 min-h-0">
                <form id="bookingForm" autocomplete="off" class="h-full flex flex-col needs-validation">
                    @csrf
                    <!-- Honeypot anti-spam field -->
                    <div style="position:absolute;left:-9999px">
                        <input type="text" name="website_url" value="" tabindex="-1" autocomplete="off">
                    </div>
                    <input type="hidden" name="action" value="booking">
                    <input type="hidden" name="utm_source" id="utmSource">
                    <input type="hidden" name="utm_medium" id="utmMedium">
                    <input type="hidden" name="utm_campaign" id="utmCampaign">
                    <input type="hidden" name="gps_lat" id="gpsLat">
                    <input type="hidden" name="gps_lng" id="gpsLng">
                    <input type="hidden" name="gps_address" id="gpsAddress">
                    <input type="hidden" name="gps_accuracy" id="gpsAccuracy">
                    <input type="hidden" name="gps_timestamp" id="gpsTimestamp">
                    <input type="hidden" name="gps_consent" id="gpsConsent" value="No">
                    <input type="hidden" name="gps_source" id="gpsSource" value="Not Available">
                    <input type="hidden" name="payment_method" id="paymentMethod" value="cash">
                    <input type="hidden" name="payment_amount" id="paymentAmount" value="0">

                    <div class="booking-scroll-area p-4 sm:p-6 flex-1">

                        <!-- Step 1: Select Tour, Package and Date -->
                        <div class="step-content active" data-step="1">
                            <div class="mb-5" id="tourSelectWrapper">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-800 mb-2" for="bookingTour">Choose Tour</label>
                                <div class="relative rounded-2xl bg-white shadow-2xs border border-slate-200 overflow-hidden focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                                    <select class="w-full px-4 py-3.5 bg-transparent font-bold text-slate-900 text-sm focus:outline-none cursor-pointer" id="bookingTour" name="tour_id" required autocomplete="off">
                                        <option value="">Select a tour...</option>
                                        @foreach($modalTours as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-5">
                                <div class="text-xs font-black uppercase tracking-wider text-slate-800 mb-2">Select Package</div>
                                <div class="tier-cards" id="tierCards">
                                    <div class="text-center py-6 text-slate-400">
                                        <i class="bi bi-cursor-fill text-3xl mb-1 block"></i>
                                        <small class="font-bold text-xs">Select a tour above to view packages</small>
                                    </div>
                                </div>
                                <input type="hidden" name="tier_id" id="selectedTier" required>
                            </div>

                            <div class="mb-5">
                                <div class="flex items-center justify-between mb-2 gap-2 flex-wrap">
                                    <div class="text-xs font-black uppercase tracking-wider text-slate-800">When</div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs cursor-pointer" id="calendarTrigger">
                                            <i class="bi bi-calendar3 text-primary"></i>
                                            <span>Select from Calendar</span>
                                        </button>
                                        <div class="date-nav flex items-center gap-1">
                                            <button type="button" class="w-8 h-8 rounded-full bg-white border border-slate-200 hover:bg-slate-50 flex items-center justify-center text-slate-700 shadow-2xs transition-colors cursor-pointer" id="datePrev" aria-label="Previous date"><i class="bi bi-chevron-left text-xs"></i></button>
                                            <button type="button" class="w-8 h-8 rounded-full bg-white border border-slate-200 hover:bg-slate-50 flex items-center justify-center text-slate-700 shadow-2xs transition-colors cursor-pointer" id="dateNext" aria-label="Next date"><i class="bi bi-chevron-right text-xs"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="date-cards-wrapper flex gap-2.5 overflow-x-auto pb-2 scrollbar-none" id="dateCardsWrapper"></div>
                                <input type="date" class="sr-only" name="date" id="bookingDate" required min="{{ $minDate }}" autocomplete="off">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 mb-3">
                                <div class="sm:col-span-4 lg:col-span-3">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-800 mb-2" for="bookingAdults">Guests</label>
                                    <div class="flex items-center justify-between bg-white shadow-2xs rounded-2xl border border-slate-200 p-1 h-[52px]">
                                        <button type="button" class="w-10 h-10 flex items-center justify-center text-primary hover:bg-orange-50 rounded-xl transition-colors cursor-pointer" data-action="minus" data-target="adults" aria-label="Decrease guest count">
                                            <i class="bi bi-dash-circle-fill text-lg"></i>
                                        </button>
                                        <input type="number" class="w-10 text-center font-black text-slate-900 border-0 bg-transparent p-0 text-base focus:outline-none" name="adults" id="bookingAdults" value="1" min="1" max="50" readonly autocomplete="off">
                                        <button type="button" class="w-10 h-10 flex items-center justify-center text-primary hover:bg-orange-50 rounded-xl transition-colors cursor-pointer" data-action="plus" data-target="adults" aria-label="Increase guest count">
                                            <i class="bi bi-plus-circle-fill text-lg"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" name="children" id="bookingChildren" value="0">
                                </div>
                                <div class="sm:col-span-8 lg:col-span-9">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-800 mb-2" for="bookingLocation">Pickup Location</label>
                                    <div class="relative rounded-2xl bg-white shadow-2xs border border-slate-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 overflow-hidden flex items-center h-[52px] transition-all">
                                        <span class="pl-3.5 pr-2 text-primary"><i class="bi bi-geo-alt-fill text-base"></i></span>
                                        <input type="text" class="flex-1 bg-transparent font-bold text-slate-900 text-sm border-0 focus:outline-none placeholder:text-slate-400" name="location" id="bookingLocation" required placeholder="Hotel / Residence in Dubai" autocomplete="street-address">
                                        <button class="px-3.5 h-full text-slate-400 hover:text-primary hover:bg-slate-50 border-l border-slate-100 transition-colors cursor-pointer" type="button" id="detectLocation" aria-label="Detect current location">
                                            <i class="bi bi-crosshair"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Customer details, Addons, and Payment options -->
                        <div class="step-content hidden" data-step="2">
                            <!-- Package Summary Bar -->
                            <div class="p-3.5 rounded-2xl bg-white shadow-2xs border border-slate-200 mb-4 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-orange-50 text-primary flex items-center justify-center shrink-0">
                                    <i class="bi bi-check-lg text-lg font-black"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <small class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Selected Experience</small>
                                    <div class="font-extrabold text-slate-900 text-sm truncate" id="summaryTourName">Loading...</div>
                                    <div class="text-xs text-slate-500" id="summaryTierName"></div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="font-black text-primary font-mono text-base" id="summaryTotal">AED 0</div>
                                    <a href="#" class="text-xs font-bold text-slate-400 hover:text-primary transition-colors" id="editStep1">Edit</a>
                                </div>
                            </div>

                            <!-- Dynamic Tour-Specific Addons Section -->
                            <div class="mb-5" id="addonsSection" style="display:none">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="text-xs font-black uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                                        <i class="bi bi-stars text-amber-500"></i>
                                        <span>Enhance Your Safari (Optional Add-ons)</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full bg-orange-50 text-primary text-[10px] font-bold">1-Click Add</span>
                                </div>
                                <div class="addon-horizontal-wrapper flex gap-2 overflow-x-auto pb-2 scrollbar-none" id="addonList"></div>
                            </div>

                            <!-- OTP verification state banners -->
                            <div class="p-3 rounded-2xl bg-sky-50 border border-sky-200 text-sky-800 text-xs hidden mb-4" id="otpNotice"></div>

                            <!-- Contact Info -->
                            <div class="mb-5 space-y-3">
                                <div class="text-xs font-black uppercase tracking-wider text-slate-800 mb-2">Contact Info</div>
                                <div>
                                    <label class="block text-[11px] font-extrabold text-slate-800 uppercase tracking-wider mb-1" for="bookingName">Full Name</label>
                                    <input type="text" class="w-full px-4 py-3 rounded-2xl bg-white border border-slate-200 text-sm font-semibold text-slate-900 shadow-2xs focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all placeholder:text-slate-400" id="bookingName" name="name" placeholder="John Doe" autocomplete="name" required data-form="booking" data-field="name">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-extrabold text-slate-800 uppercase tracking-wider mb-1" for="bookingEmail">Email Address</label>
                                    <input type="email" class="w-full px-4 py-3 rounded-2xl bg-white border border-slate-200 text-sm font-semibold text-slate-900 shadow-2xs focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all placeholder:text-slate-400" id="bookingEmail" name="email" placeholder="name@example.com" autocomplete="email" required data-form="booking" data-field="email">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-extrabold text-slate-800 uppercase tracking-wider mb-1" for="bookingPhone">Phone Number</label>
                                    <div class="welcome-phone-field rounded-2xl bg-white border border-slate-200 shadow-2xs focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                                        <input type="tel" class="w-full py-3 px-4 bg-transparent text-sm font-semibold text-slate-900 border-0 focus:outline-none placeholder:text-slate-400" id="bookingPhone" name="phone" placeholder="50 123 4567" autocomplete="tel" required data-form="booking" data-field="phone">
                                    </div>
                                </div>
                            </div>

                            <!-- OTP Verification Fields -->
                            <div class="mb-5 hidden" id="otpFieldsWrapper">
                                <div class="text-xs font-black uppercase tracking-wider text-slate-800 mb-2">Email Verification Code</div>
                                <div class="flex rounded-2xl shadow-2xs border border-slate-200 bg-white overflow-hidden">
                                    <input type="text" class="flex-1 px-4 py-3 bg-transparent text-center font-black font-mono tracking-widest text-lg text-slate-900 border-0 focus:outline-none" id="bookingOtpCode" placeholder="Enter 6-digit OTP">
                                    <button class="px-5 bg-primary hover:bg-primary-dark text-white font-bold text-xs transition-colors cursor-pointer" type="button" id="verifyOtpBtn">Verify</button>
                                </div>
                                <div class="flex justify-between mt-2 px-1 text-xs">
                                    <span class="text-slate-400" id="otpTimer"></span>
                                    <a href="#" class="font-bold text-primary hover:underline" id="resendOtpBtn">Resend Code</a>
                                </div>
                            </div>

                            <!-- Special Requests -->
                            <div class="mb-5">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-800 mb-2" for="bookingRequests">Special Requests / Dietary Notes</label>
                                <textarea class="w-full p-3.5 rounded-2xl bg-white border border-slate-200 text-xs sm:text-sm font-medium text-slate-900 shadow-2xs focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all placeholder:text-slate-400" id="bookingRequests" name="requests" rows="3" placeholder="Any dietary requirements, hotel room numbers, baby seat needs..." autocomplete="off" data-form="booking" data-field="requests"></textarea>
                            </div>

                            <!-- Luxury Voucher & Promo Code Section -->
                            <div class="p-4 rounded-2xl bg-white shadow-2xs border border-slate-200 mb-5" id="promoCodeCard">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5" for="bookingPromoCode">
                                        <i class="bi bi-ticket-perforated-fill text-primary"></i> Have a Promo Code or Voucher?
                                    </label>
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold hidden" id="promoAppliedBadge">
                                        <i class="bi bi-check2-circle"></i> Applied
                                    </span>
                                </div>
                                <div class="flex rounded-xl border border-slate-200 bg-white overflow-hidden focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all" id="promoInputGroup">
                                    <input type="text" class="flex-1 px-3.5 py-2.5 bg-transparent text-xs sm:text-sm font-semibold text-slate-800 border-0 focus:outline-none placeholder:text-slate-400 uppercase" id="bookingPromoCode" name="coupon_code" placeholder="Enter promo code (e.g. DUNESWELCOME)" autocomplete="off" spellcheck="false">
                                    <button class="px-4 bg-primary hover:bg-primary-dark text-white font-bold text-xs transition-colors cursor-pointer flex items-center gap-1" type="button" id="applyPromoBtn">
                                        <span>Apply</span>
                                        <i class="bi bi-arrow-right-short"></i>
                                    </button>
                                </div>
                                
                                <div class="hidden mt-2 items-center justify-between" id="promoSuccessBox">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 text-sm">
                                            <i class="bi bi-check-lg"></i>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 rounded-md bg-slate-900 text-amber-400 font-mono font-bold text-xs" id="promoCodeLabel">CODE</span>
                                                <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[10px]" id="promoDiscountBadge">Applied</span>
                                            </div>
                                            <div class="text-xs font-bold text-emerald-600 mt-0.5" id="promoSavingsText">Savings applied</div>
                                        </div>
                                    </div>
                                    <button type="button" class="text-xs text-red-500 hover:text-red-700 font-semibold cursor-pointer" id="removePromoBtn" aria-label="Remove promo code">
                                        <i class="bi bi-x-circle me-1"></i>Remove
                                    </button>
                                </div>
                                <div class="hidden mt-2 text-xs text-red-600 items-center gap-1.5" id="promoErrorBox">
                                    <i class="bi bi-exclamation-circle-fill shrink-0"></i>
                                    <span id="promoErrorMessage">Invalid promo code.</span>
                                </div>
                            </div>

                            <!-- Payment Options -->
                            <div class="mb-5" id="paymentOptions" data-ziina-active="{{ $ziinaActive ? '1' : '0' }}" data-advance-percent="{{ $advancePercent }}">
                                <div class="text-xs font-black uppercase tracking-wider text-slate-800 mb-2">Payment Options</div>
                                <div class="payment-options grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <div class="payment-option selected p-3 rounded-2xl border-2 border-primary bg-orange-50/40 cursor-pointer text-left transition-all" data-value="cash">
                                        <div class="payment-option-title font-bold text-slate-900 text-xs sm:text-sm">Cash</div>
                                        <div class="payment-option-sub text-slate-500 text-[11px]">Pay on pickup</div>
                                    </div>
                                    @if($ziinaActive)
                                    <div class="payment-option p-3 rounded-2xl border border-slate-200 bg-white hover:border-primary/50 cursor-pointer text-left transition-all" data-value="advance">
                                        <div class="payment-option-title font-bold text-slate-900 text-xs sm:text-sm">Advance</div>
                                        <div class="payment-option-sub text-slate-500 text-[11px]">Hold slot ({{ $advancePercent }}%)</div>
                                    </div>
                                    <div class="payment-option p-3 rounded-2xl border border-slate-200 bg-white hover:border-primary/50 cursor-pointer text-left transition-all" data-value="full">
                                        <div class="payment-option-title font-bold text-slate-900 text-xs sm:text-sm">Full</div>
                                        <div class="payment-option-sub text-slate-500 text-[11px]">Instant confirmation</div>
                                    </div>
                                    @endif
                                </div>
                                <div class="p-3 rounded-xl bg-red-50 text-red-700 text-xs border border-red-200 mt-3 hidden" id="bookingError"></div>
                            </div>

                            <!-- Legal Agreement -->
                            <div class="space-y-2 mb-2">
                                <label class="flex items-start gap-2.5 text-xs text-slate-600 cursor-pointer">
                                    <input class="mt-0.5 rounded border-slate-300 text-primary focus:ring-primary/20" type="checkbox" id="bookingAgreement" required>
                                    <span>
                                        I agree to the <a href="{{ route('terms') }}" target="_blank" rel="noopener noreferrer" class="text-primary font-bold hover:underline">Terms & Conditions</a> and <a href="{{ route('privacy') }}" target="_blank" rel="noopener noreferrer" class="text-primary font-bold hover:underline">Privacy Policy</a>.
                                    </span>
                                </label>
                                <label class="flex items-start gap-2.5 text-xs text-slate-500 cursor-pointer">
                                    <input class="mt-0.5 rounded border-slate-300 text-primary focus:ring-primary/20" type="checkbox" id="bookingNewsletter" name="subscribe_newsletter" value="1" checked>
                                    <span>Keep me updated with exclusive desert safari deals, seasonal discounts & travel guides.</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Sticky Footer with Live Total & Action Buttons -->
                    <div class="border-t border-slate-200/80 bg-white py-3.5 px-4 sm:px-6 sticky bottom-0 z-20 pb-safe shrink-0 shadow-lg">
                        <div class="flex items-center justify-between w-full">
                            <div class="text-left">
                                <small class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Total Amount</small>
                                <div class="font-black text-primary font-mono text-xl sm:text-2xl leading-none" id="bookingTotal">AED 0.00</div>
                            </div>
                            <div class="flex items-center gap-2 ml-auto" id="continueBtnWrapper">
                                <button type="button" 
                                        class="px-5 sm:px-7 py-3 rounded-full bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-extrabold text-xs sm:text-sm shadow-md hover:shadow-lg transition-all cursor-pointer inline-flex items-center gap-2" 
                                        id="nextStep">
                                    <span>Continue</span>
                                    <i class="bi bi-arrow-right"></i>
                                </button>
                                <button type="submit" 
                                        class="px-5 sm:px-7 py-3 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-xs sm:text-sm shadow-md hover:shadow-lg transition-all cursor-pointer hidden items-center gap-2" 
                                        id="submitBooking">
                                    <span>Confirm Booking</span>
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    window.appliedPromoCoupon = null;

    const promoInput = document.getElementById('bookingPromoCode');
    const applyBtn = document.getElementById('applyPromoBtn');
    const removeBtn = document.getElementById('removePromoBtn');
    const successBox = document.getElementById('promoSuccessBox');
    const inputGroup = document.getElementById('promoInputGroup');
    const errorBox = document.getElementById('promoErrorBox');
    const errorMsg = document.getElementById('promoErrorMessage');
    const codeLabel = document.getElementById('promoCodeLabel');
    const discountBadge = document.getElementById('promoDiscountBadge');
    const savingsLabel = document.getElementById('promoSavingsText');
    const appliedBadge = document.getElementById('promoAppliedBadge');

    const urlParams = new URLSearchParams(window.location.search);
    const urlPromo = urlParams.get('promo') || urlParams.get('coupon');
    if (urlPromo && promoInput) {
        promoInput.value = urlPromo.toUpperCase().trim();
    }

    window.validateCurrentPromo = function(isSilent = false) {
        if (!promoInput) return;
        const code = (promoInput.value || '').trim().toUpperCase();
        if (!code) {
            if (!isSilent && errorBox) {
                if (errorMsg) errorMsg.innerText = 'Please enter a promo or voucher code.';
                errorBox.classList.remove('hidden');
                errorBox.classList.add('flex');
                if (inputGroup) {
                    inputGroup.classList.add('border-red-400', 'shake-field');
                    setTimeout(() => inputGroup.classList.remove('shake-field'), 500);
                }
            }
            return;
        }

        let subtotal = 0;
        if (window.App && typeof window.App.calculateBaseTotal === 'function') {
            subtotal = window.App.calculateBaseTotal();
        } else if (window.DunesApp && typeof window.DunesApp.calculateBaseTotal === 'function') {
            subtotal = window.DunesApp.calculateBaseTotal();
        }

        const adults = parseInt(document.getElementById('bookingAdults')?.value || '1', 10);
        const children = parseInt(document.getElementById('bookingChildren')?.value || '0', 10);
        const totalGuests = Math.max(1, adults + children);

        if (subtotal <= 0) {
            const selectedTierCard = document.querySelector('.tier-card.selected');
            if (selectedTierCard && selectedTierCard.dataset.price) {
                const p = parseFloat(selectedTierCard.dataset.price) || 0;
                const pType = (selectedTierCard.dataset.priceType || 'per person').toLowerCase();
                if (['per buggy', 'per vehicle', 'per group', 'private'].includes(pType)) {
                    subtotal = p;
                } else {
                    subtotal = (p * adults) + (p * 0.70 * children);
                }
            }
        }

        if (subtotal <= 0) {
            const rawTxt = document.getElementById('bookingTotal')?.innerText?.replace(/[^0-9.]/g, '') || document.getElementById('summaryTotal')?.innerText?.replace(/[^0-9.]/g, '') || '0';
            subtotal = parseFloat(rawTxt) || 0;
        }

        const emailVal = document.getElementById('bookingEmail')?.value?.trim();
        const email = (emailVal && emailVal.includes('@')) ? emailVal : null;
        const dateVal = document.getElementById('bookingDate')?.value?.trim();
        const tourDate = dateVal || null;
        const tourVal = document.getElementById('bookingTour')?.value;
        const tourId = tourVal ? parseInt(tourVal, 10) : null;
        const tierVal = document.getElementById('selectedTier')?.value || (window.App ? window.App.selectedTier : null);
        const tierId = tierVal ? parseInt(tierVal, 10) : null;

        if (!isSilent) {
            if (applyBtn) {
                applyBtn.disabled = true;
                applyBtn.innerHTML = '<span class="inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin me-1"></span> Checking...';
            }
            if (promoInput) promoInput.disabled = true;
            if (errorBox) {
                errorBox.classList.add('hidden');
                errorBox.classList.remove('flex');
            }
            if (inputGroup) inputGroup.classList.remove('border-red-400', 'shake-field');
        }

        fetch('/api/v1/coupon/validate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.CSRF_TOKEN || ''
            },
            body: JSON.stringify({
                code: code,
                subtotal: subtotal,
                tour_id: tourId,
                tier_id: tierId,
                email: email,
                adults: adults,
                children: children,
                guests: totalGuests,
                date: tourDate
            })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (!isSilent) {
                if (applyBtn) {
                    applyBtn.disabled = false;
                    applyBtn.innerHTML = '<span>Apply</span> <i class="bi bi-arrow-right-short text-base"></i>';
                }
                if (promoInput) promoInput.disabled = false;
            }

            if (status === 200 && body.success && body.coupon) {
                window.appliedPromoCoupon = body.coupon;
                if (codeLabel) codeLabel.innerText = body.coupon.code;
                if (discountBadge) {
                    if (body.coupon.discount_type === 'percentage') {
                        discountBadge.innerText = `${Math.round(body.coupon.discount_value)}% OFF`;
                    } else if (body.coupon.discount_type === 'per_person') {
                        discountBadge.innerText = `AED ${body.coupon.discount_value}/guest`;
                    } else {
                        discountBadge.innerText = 'Flat Discount';
                    }
                }
                if (savingsLabel) {
                    savingsLabel.innerText = body.coupon.savings_text || `AED ${body.coupon.discount_amount} saved`;
                }
                
                if (inputGroup) {
                    inputGroup.classList.add('hidden');
                    inputGroup.classList.remove('border-red-400', 'shake-field');
                }
                if (successBox) {
                    successBox.classList.remove('hidden');
                    successBox.classList.add('flex');
                }
                if (appliedBadge) appliedBadge.classList.remove('hidden');
                if (errorBox) {
                    errorBox.classList.add('hidden');
                    errorBox.classList.remove('flex');
                }

                if (window.App && typeof window.App.updateTotal === 'function') {
                    window.App.updateTotal();
                } else if (window.DunesApp && typeof window.DunesApp.updateTotal === 'function') {
                    window.DunesApp.updateTotal();
                }
            } else {
                if (isSilent) {
                    const oldCode = window.appliedPromoCoupon ? window.appliedPromoCoupon.code : code;
                    window.removeCurrentPromo(true);
                    if (window.App && typeof window.App.toast === 'function') {
                        window.App.toast(`Promo code ${oldCode} was removed: ${body.message || 'Not eligible for selected tour/package'}`, 'warning');
                    }
                } else {
                    window.appliedPromoCoupon = null;
                    if (errorBox) {
                        if (errorMsg) errorMsg.innerText = body.message || 'Invalid promo code. Please check for typos and try again.';
                        errorBox.classList.remove('hidden');
                        errorBox.classList.add('flex');
                    }
                    if (inputGroup) {
                        inputGroup.classList.add('border-red-400', 'shake-field');
                        setTimeout(() => inputGroup.classList.remove('shake-field'), 500);
                    }
                }
            }
        })
        .catch(() => {
            if (!isSilent) {
                if (applyBtn) {
                    applyBtn.disabled = false;
                    applyBtn.innerHTML = '<span>Apply</span> <i class="bi bi-arrow-right-short text-base"></i>';
                }
                if (promoInput) promoInput.disabled = false;
                if (errorBox) {
                    if (errorMsg) errorMsg.innerText = 'Unable to validate promo code. Please check connection and try again.';
                    errorBox.classList.remove('hidden');
                    errorBox.classList.add('flex');
                }
            }
        });
    };

    window.removeCurrentPromo = function(isSilent = false) {
        window.appliedPromoCoupon = null;
        if (promoInput) promoInput.value = '';
        if (inputGroup) {
            inputGroup.classList.remove('hidden', 'border-red-400', 'shake-field');
        }
        if (successBox) {
            successBox.classList.add('hidden');
            successBox.classList.remove('flex');
        }
        if (appliedBadge) appliedBadge.classList.add('hidden');
        if (errorBox) {
            errorBox.classList.add('hidden');
            errorBox.classList.remove('flex');
        }

        if (window.App && typeof window.App.updateTotal === 'function') {
            window.App.updateTotal();
        } else if (window.DunesApp && typeof window.DunesApp.updateTotal === 'function') {
            window.DunesApp.updateTotal();
        }

        if (!isSilent && window.App && typeof window.App.toast === 'function') {
            window.App.toast('Promo code removed', 'success');
        }
    };

    window.revalidateAppliedPromo = function() {
        if (window.appliedPromoCoupon && promoInput && promoInput.value.trim()) {
            window.validateCurrentPromo(true);
        }
    };

    if (applyBtn) applyBtn.addEventListener('click', () => window.validateCurrentPromo(false));
    if (removeBtn) removeBtn.addEventListener('click', () => window.removeCurrentPromo(false));
    if (promoInput) {
        promoInput.addEventListener('input', function() {
            if (errorBox) {
                errorBox.classList.add('hidden');
                errorBox.classList.remove('flex');
            }
            if (inputGroup) inputGroup.classList.remove('border-red-400', 'shake-field');
        });
        promoInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                window.validateCurrentPromo(false);
            }
        });
    }

    const tourSelect = document.getElementById('bookingTour');
    const tierInput = document.getElementById('selectedTier');
    const dateInput = document.getElementById('bookingDate');
    const adultsInput = document.getElementById('bookingAdults');
    const childrenInput = document.getElementById('bookingChildren');

    if (tourSelect) tourSelect.addEventListener('change', window.revalidateAppliedPromo);
    if (tierInput) tierInput.addEventListener('change', window.revalidateAppliedPromo);
    if (dateInput) dateInput.addEventListener('change', window.revalidateAppliedPromo);
    if (adultsInput) adultsInput.addEventListener('change', window.revalidateAppliedPromo);
    if (childrenInput) childrenInput.addEventListener('change', window.revalidateAppliedPromo);

    document.querySelectorAll('[data-action="plus"], [data-action="minus"]').forEach(btn => {
        btn.addEventListener('click', () => {
            setTimeout(window.revalidateAppliedPromo, 100);
        });
    });
});
</script>
