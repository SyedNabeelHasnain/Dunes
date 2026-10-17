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
    $dubaiNow = now('Asia/Dubai');
    $canBookToday = $dubaiNow->format('H:i') < '13:30';
    $minDate = $canBookToday ? $dubaiNow->format('Y-m-d') : $dubaiNow->copy()->addDay()->format('Y-m-d');
    $ziinaActive = isset($settings['ziina_active']) ? ($settings['ziina_active'] === '1') : false;
    $advancePercent = (int)($settings['deposit_percentage'] ?? ($settings['ziina_advance_percent'] ?? 25));
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
             class="relative z-10 w-full sm:max-w-2xl lg:max-w-3xl rounded-t-3xl sm:rounded-3xl bg-white text-start align-middle shadow-2xl transition-all border border-slate-200 overflow-hidden flex flex-col max-h-[92vh] sm:max-h-[88vh]"
             @click.stop>

            <!-- Modal Header -->
            <div class="flex items-center justify-between py-3.5 px-4 sm:px-6 border-b border-slate-200 bg-white sticky top-0 z-20 shrink-0">
                <div class="flex items-center gap-3">
                    <button type="button" class="w-11 h-11 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-colors cursor-pointer hidden" id="headerBackBtn" aria-label="{{ __('ui.common.back') ?? 'Back' }}">
                        <i class="bi bi-chevron-left rtl:rotate-180 text-sm"></i>
                    </button>
                    <div>
                        <h5 class="text-base sm:text-lg font-black text-slate-900 leading-tight" id="bookingModalTitle">{{ __('ui.booking.title') }}</h5>
                        <div class="text-primary text-xs font-extrabold hidden" id="bookingModalSubtitle" data-step1="{{ __('ui.booking.step_1_of_2') }}" data-step2="{{ __('ui.booking.step_2_of_2') }}">{{ __('ui.booking.step_1_of_2') }}</div>
                    </div>
                </div>
                <button type="button" 
                        class="w-11 h-11 rounded-full bg-slate-100 text-slate-500 hover:text-slate-800 hover:bg-slate-200 flex items-center justify-center transition-colors cursor-pointer" 
                        @click="$store.modal.close()" 
                        aria-label="{{ __('ui.common.close') }}">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>

            <!-- Modal Body & Form -->
            <form id="bookingForm" autocomplete="off" class="flex-1 flex flex-col min-h-0 needs-validation m-0 p-0">
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

                <!-- Scrollable Body ONLY -->
                <div class="booking-scroll-area flex-1 overflow-y-auto bg-slate-50 min-h-0 p-4 sm:p-6 lg:p-7">

                    <!-- Step 1: Select Tour, Package and Date -->
                    <div class="step-content active" data-step="1">
                            <div class="mb-5" id="tourSelectWrapper">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-800 mb-2" for="bookingTour">{{ __('ui.booking.choose_tour') }}</label>
                                <div class="relative">
                                    <select class="w-full h-[52px] appearance-none rounded-2xl bg-white px-4 pe-11 font-bold text-slate-900 text-sm border-2 border-slate-200 shadow-2xs hover:border-slate-300 focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all cursor-pointer" id="bookingTour" name="tour_id" required autocomplete="off">
                                        <option value="">{{ __('ui.booking.select_tier') }}</option>
                                        @foreach($modalTours as $t)
                                            <option value="{{ $t->id }}" data-slug="{{ $t->slug }}">{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute end-4 top-1/2 -translate-y-1/2 text-slate-400 flex items-center justify-center">
                                        <i class="bi bi-chevron-down text-xs font-black"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-5">
                                <div class="text-xs font-black uppercase tracking-wider text-slate-800 mb-2">{{ __('ui.booking.select_package') }}</div>
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
                                    <div class="text-xs font-black uppercase tracking-wider text-slate-800">{{ __('ui.booking.when') }}</div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs cursor-pointer min-h-[36px]" id="calendarTrigger">
                                            <i class="bi bi-calendar3 text-primary"></i>
                                            <span>{{ __('ui.booking.select_date') }}</span>
                                        </button>
                                        <div class="date-nav flex items-center gap-1">
                                            <button type="button" class="w-11 h-11 rounded-full bg-white border border-slate-200 hover:bg-slate-50 flex items-center justify-center text-slate-700 shadow-2xs transition-colors cursor-pointer" id="datePrev" aria-label="{{ __('ui.common.previous_date') }}"><i class="bi bi-chevron-left rtl:rotate-180 text-xs"></i></button>
                                            <button type="button" class="w-11 h-11 rounded-full bg-white border border-slate-200 hover:bg-slate-50 flex items-center justify-center text-slate-700 shadow-2xs transition-colors cursor-pointer" id="dateNext" aria-label="{{ __('ui.common.next_date') }}"><i class="bi bi-chevron-right rtl:rotate-180 text-xs"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="date-cards-wrapper flex gap-2.5 overflow-x-auto pb-2 scrollbar-none" id="dateCardsWrapper"></div>
                                <input type="date" class="sr-only" name="date" id="bookingDate" required min="{{ $minDate }}" autocomplete="off">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 mb-3">
                                <div class="sm:col-span-4 lg:col-span-3">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-800 mb-2" for="bookingAdults">{{ __('ui.booking.guests') }}</label>
                                    <div class="flex items-center justify-between bg-white shadow-2xs rounded-2xl border-2 border-slate-200 p-1 h-[52px]">
                                        <button type="button" class="w-11 h-11 flex items-center justify-center text-primary hover:bg-orange-50 rounded-xl transition-colors cursor-pointer" data-action="minus" data-target="adults" aria-label="{{ __('ui.booking.decrease_guests') }}">
                                            <i class="bi bi-dash-circle-fill text-xl"></i>
                                        </button>
                                        <input type="number" class="w-10 text-center font-black text-slate-900 border-0 bg-transparent p-0 text-base focus:outline-none" name="adults" id="bookingAdults" value="1" min="1" max="50" step="1" readonly autocomplete="off">
                                        <button type="button" class="w-11 h-11 flex items-center justify-center text-primary hover:bg-orange-50 rounded-xl transition-colors cursor-pointer" data-action="plus" data-target="adults" aria-label="{{ __('ui.booking.increase_guests') }}">
                                            <i class="bi bi-plus-circle-fill text-xl"></i>
                                        </button>
                                    </div>
                                    <p id="overnightAdultsHelper" class="hidden text-[10px] font-bold text-amber-700 bg-amber-50 rounded-lg px-2 py-1 mt-1 border border-amber-200"><i class="bi bi-info-circle me-1"></i>Overnight safari requires at least 2 guests (1 private tent). Bookings step in pairs (2, 4, 6... guests).</p>
                                </div>
                                <div class="sm:col-span-8 lg:col-span-9 booking-location-wrapper relative">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-800 mb-2" for="bookingLocation">{{ __('ui.booking.pickup_location_label') }}</label>
                                    <div class="relative rounded-2xl bg-white shadow-2xs border-2 border-slate-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 flex items-center h-[52px] transition-all">
                                        <span class="ps-3.5 pe-2 text-primary shrink-0"><i class="bi bi-geo-alt-fill text-base"></i></span>
                                        <input type="text" class="flex-1 bg-transparent font-bold text-slate-900 text-base sm:text-sm border-0 focus:outline-none placeholder:text-slate-400 min-w-0" name="location" id="bookingLocation" required placeholder="{{ __('ui.booking.pickup_placeholder') }}" autocomplete="off">
                                        <button class="px-3.5 h-full min-w-[44px] text-slate-400 hover:text-primary hover:bg-slate-50 border-s border-slate-100 transition-colors cursor-pointer rounded-e-2xl shrink-0 flex items-center justify-center" type="button" id="detectLocation" aria-label="{{ __('ui.common.detect_location') }}" title="{{ __('ui.common.detect_location') }}">
                                            <i class="bi bi-crosshair"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Optional Children & Infants Row -->
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="bg-slate-50/80 p-2.5 rounded-2xl border border-slate-200/80">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-800" for="bookingChildren">Children <span class="text-[10px] text-slate-400 font-semibold lowercase">(3–10 yrs)</span></label>
                                        <span class="text-[9px] font-black text-emerald-700 bg-emerald-100/80 px-1.5 py-0.5 rounded-full">30% OFF</span>
                                    </div>
                                    <div class="flex items-center justify-between bg-white shadow-2xs rounded-xl border border-slate-200 p-0.5 h-[44px]">
                                        <button type="button" class="w-10 h-10 flex items-center justify-center text-primary hover:bg-orange-50 rounded-lg transition-colors cursor-pointer" data-action="minus" data-target="children" aria-label="Decrease children">
                                            <i class="bi bi-dash-circle-fill text-lg"></i>
                                        </button>
                                        <input type="number" class="w-8 text-center font-black text-slate-900 border-0 bg-transparent p-0 text-sm focus:outline-none" name="children" id="bookingChildren" value="0" min="0" max="30" readonly autocomplete="off">
                                        <button type="button" class="w-10 h-10 flex items-center justify-center text-primary hover:bg-orange-50 rounded-lg transition-colors cursor-pointer" data-action="plus" data-target="children" aria-label="Increase children">
                                            <i class="bi bi-plus-circle-fill text-lg"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="bg-slate-50/80 p-2.5 rounded-2xl border border-slate-200/80">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-800" for="bookingInfants">Infants <span class="text-[10px] text-slate-400 font-semibold lowercase">(&lt;3 yrs)</span></label>
                                        <span class="text-[9px] font-black text-primary bg-orange-100/80 px-1.5 py-0.5 rounded-full">FREE</span>
                                    </div>
                                    <div class="flex items-center justify-between bg-white shadow-2xs rounded-xl border border-slate-200 p-0.5 h-[44px]">
                                        <button type="button" class="w-10 h-10 flex items-center justify-center text-primary hover:bg-orange-50 rounded-lg transition-colors cursor-pointer" data-action="minus" data-target="infants" aria-label="Decrease infants">
                                            <i class="bi bi-dash-circle-fill text-lg"></i>
                                        </button>
                                        <input type="number" class="w-8 text-center font-black text-slate-900 border-0 bg-transparent p-0 text-sm focus:outline-none" name="infants" id="bookingInfants" value="0" min="0" max="15" readonly autocomplete="off">
                                        <button type="button" class="w-10 h-10 flex items-center justify-center text-primary hover:bg-orange-50 rounded-lg transition-colors cursor-pointer" data-action="plus" data-target="infants" aria-label="Increase infants">
                                            <i class="bi bi-plus-circle-fill text-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 px-1 text-[11px] text-slate-500 leading-snug flex items-center gap-1.5">
                                <i class="bi bi-shield-check text-emerald-600 shrink-0"></i>
                                <span>Infants (&lt;3 yrs) join free. Under UAE tourism safety rules, infants bypass rough dune bashing via direct-to-camp transfer or scenic gentle drive.</span>
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
                                <div class="text-end shrink-0">
                                    <div class="font-black text-primary font-mono text-base" id="summaryTotal">AED 0</div>
                                    <a href="#" class="text-xs font-bold text-slate-400 hover:text-primary transition-colors" id="editStep1">Edit</a>
                                </div>
                            </div>

                            <!-- Dynamic Tour-Specific Addons Section -->
                            <div class="mb-6" id="addonsSection" style="display:none">
                                <div class="flex items-center justify-between mb-2.5">
                                    <div class="text-xs font-black uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                                        <i class="bi bi-stars text-amber-500"></i>
                                        <span>{{ __('ui.booking.addons_title') }}</span>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full bg-orange-50 text-primary border border-orange-200/60 text-[10px] font-extrabold uppercase tracking-wide">1-Click Add</span>
                                </div>
                                <div class="addon-horizontal-wrapper flex gap-3 overflow-x-auto pb-3 pt-1 scrollbar-none" id="addonList"></div>
                            </div>

                            <!-- OTP verification state banners -->
                            <div class="p-3 rounded-2xl bg-sky-50 border border-sky-200 text-sky-800 text-xs hidden mb-4" id="otpNotice"></div>

                            <!-- Contact Info -->
                            <div class="mb-5 space-y-3.5">
                                <div class="text-xs font-black uppercase tracking-wider text-slate-800 mb-2">{{ __('ui.booking.step_contact') }}</div>
                                
                                <!-- Full Name -->
                                <div>
                                    <label class="block text-[11px] font-extrabold text-slate-800 uppercase tracking-wider mb-1" for="bookingName">{{ __('ui.booking.name') }} <span class="text-rose-500">*</span></label>
                                    <input type="text" class="w-full px-4 py-3 rounded-2xl bg-white border border-slate-200 text-base sm:text-sm font-semibold text-slate-900 shadow-2xs focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all placeholder:text-slate-400" id="bookingName" name="name" placeholder="{{ __('ui.booking.name_placeholder') }}" autocomplete="name" required data-form="booking" data-field="name">
                                </div>

                                <!-- Email Address + Interactive Verification -->
                                <div id="bookingEmailSection">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[11px] font-extrabold text-slate-800 uppercase tracking-wider" for="bookingEmail">{{ __('ui.booking.email') }} <span class="text-rose-500">*</span></label>
                                        <span id="emailStatusBadge" class="hidden items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold tracking-wide transition-all"></span>
                                    </div>
                                    <div class="relative flex items-center rounded-2xl bg-white border border-slate-200 shadow-2xs focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all" id="bookingEmailContainer">
                                        <input type="email" class="w-full pl-4 pr-28 py-3 rounded-2xl bg-transparent text-base sm:text-sm font-semibold text-slate-900 border-0 focus:outline-none placeholder:text-slate-400" id="bookingEmail" name="email" placeholder="{{ __('ui.booking.email_placeholder') }}" autocomplete="email" required data-form="booking" data-field="email">
                                        
                                        <!-- Inline Action / Spinner / Verified Indicator -->
                                        <div class="absolute right-1.5 inset-y-1.5 flex items-center" id="bookingEmailActionSlot">
                                            <span id="emailCheckSpinner" class="hidden px-3 text-primary items-center">
                                                <span class="inline-block w-4 h-4 border-2 border-primary border-t-transparent rounded-full animate-spin"></span>
                                            </span>
                                            <button type="button" id="sendEmailOtpBtn" class="hidden h-full px-3.5 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-extrabold text-[11px] uppercase tracking-wider shadow-xs transition-all cursor-pointer items-center gap-1.5 shrink-0">
                                                <i class="bi bi-shield-lock-fill text-xs"></i>
                                                <span>{{ __('ui.booking.verify') }}</span>
                                            </button>
                                            <span id="emailVerifiedIcon" class="hidden px-3 text-emerald-500 text-lg items-center" title="Email Verified">
                                                <i class="bi bi-patch-check-fill"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <p id="emailHelperText" class="text-[11px] font-medium text-slate-500 mt-1 hidden"></p>

                                    <!-- Interactive 6-Digit OTP Verification Card -->
                                    <div class="mt-3 hidden transition-all duration-300" id="otpFieldsWrapper">
                                        <div class="rounded-2xl bg-gradient-to-br from-orange-50/90 via-white to-amber-50/60 border-2 border-orange-200/90 p-3.5 sm:p-4 shadow-md">
                                            <div class="flex items-start justify-between gap-2 mb-3">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <div class="w-8 h-8 rounded-xl bg-orange-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                                                        <i class="bi bi-envelope-check-fill text-sm"></i>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <div class="text-xs font-black text-slate-900 uppercase tracking-wide leading-tight">{{ __('ui.booking.email_verification') }}</div>
                                                        <div class="text-[11px] text-slate-600 truncate mt-0.5">
                                                            6-digit code sent to <span id="otpTargetEmail" class="font-bold text-slate-900">your email</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200/80 text-[10px] font-extrabold shrink-0">Valid 5m</span>
                                            </div>

                                            <!-- Hidden master input for compatibility -->
                                            <input type="hidden" id="bookingOtpCode" value="">

                                            <!-- 6 Segmented Interactive Digit Boxes -->
                                            <div class="grid grid-cols-6 gap-1.5 sm:gap-2.5 my-2.5" id="otpDigitsContainer" dir="ltr">
                                                @for($d = 0; $d < 6; $d++)
                                                <input type="text"
                                                       inputmode="numeric"
                                                       pattern="[0-9]*"
                                                       maxlength="1"
                                                       data-otp-index="{{ $d }}"
                                                       autocomplete="{{ $d === 0 ? 'one-time-code' : 'off' }}"
                                                       aria-label="Digit {{ $d + 1 }}"
                                                       class="otp-digit-box w-full h-11 sm:h-12 text-center font-mono font-black text-base sm:text-lg text-slate-900 bg-white border-2 border-slate-200 rounded-xl shadow-2xs focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all">
                                                @endfor
                                            </div>

                                            <!-- Error Alert inside OTP Card -->
                                            <div id="otpErrorMsg" class="hidden items-center gap-1.5 text-xs font-bold text-rose-600 bg-rose-50 border border-rose-200 rounded-xl px-3 py-2 my-2">
                                                <i class="bi bi-exclamation-circle-fill shrink-0"></i>
                                                <span id="otpErrorText">Invalid verification code.</span>
                                            </div>

                                            <!-- Actions & Countdown Footer -->
                                            <div class="flex flex-wrap items-center justify-between gap-2 mt-3 pt-2.5 border-t border-orange-200/60">
                                                <div class="flex items-center gap-2 text-xs">
                                                    <span class="text-slate-500 font-semibold text-[11px]" id="otpTimer"></span>
                                                    <button type="button" class="font-extrabold text-primary hover:text-primary-dark hover:underline text-xs cursor-pointer disabled:opacity-50 disabled:pointer-events-none py-1" id="resendOtpBtn">
                                                        <i class="bi bi-arrow-repeat me-0.5"></i>{{ __('ui.booking.resend_code') }}
                                                    </button>
                                                </div>
                                                <button type="button" id="verifyOtpBtn" class="px-4 py-2 min-h-[44px] rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-sm transition-all cursor-pointer inline-flex items-center gap-1.5 ms-auto">
                                                    <i class="bi bi-check2-circle"></i>
                                                    <span>{{ __('ui.booking.verify') }} Code</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Phone / WhatsApp Number -->
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[11px] font-extrabold text-slate-800 uppercase tracking-wider" for="bookingPhone">{{ __('ui.booking.phone') }} <span class="text-rose-500">*</span></label>
                                        <span class="text-[10px] font-semibold text-emerald-600 inline-flex items-center gap-1"><i class="bi bi-whatsapp"></i> Instant Confirmation</span>
                                    </div>
                                    <div class="welcome-phone-field rounded-2xl bg-white border border-slate-200 shadow-2xs focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all" id="bookingPhoneContainer">
                                        <input type="tel" class="w-full py-3 px-4 bg-transparent text-base sm:text-sm font-semibold text-slate-900 border-0 focus:outline-none placeholder:text-slate-400" id="bookingPhone" name="phone" placeholder="{{ __('ui.booking.phone_placeholder') }}" autocomplete="tel" required data-form="booking" data-field="phone">
                                    </div>
                                    <p id="bookingPhoneError" class="text-[11px] font-semibold text-rose-600 mt-1 hidden items-center gap-1">
                                        <i class="bi bi-exclamation-circle-fill"></i>
                                        <span>Please enter a valid phone or WhatsApp number.</span>
                                    </p>
                                </div>
                            </div>

                            <!-- Special Requests -->
                            <div class="mb-5">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-800 mb-2" for="bookingRequests">{{ __('ui.booking.special_requests') }}</label>
                                <textarea class="w-full p-3.5 rounded-2xl bg-white border border-slate-200 text-base sm:text-sm font-medium text-slate-900 shadow-2xs focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all placeholder:text-slate-400" id="bookingRequests" name="requests" rows="3" placeholder="{{ __('ui.booking.special_placeholder') }}" autocomplete="off" data-form="booking" data-field="requests"></textarea>
                            </div>

                            <!-- Luxury Voucher & Promo Code Section -->
                            <div class="p-4 rounded-2xl bg-white shadow-2xs border border-slate-200 mb-5" id="promoCodeCard">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5" for="bookingPromoCode">
                                        <i class="bi bi-ticket-perforated-fill text-primary"></i> {{ __('ui.booking.promo_code') }}
                                    </label>
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold hidden" id="promoAppliedBadge">
                                        <i class="bi bi-check2-circle"></i> {{ __('ui.booking.promo_applied') }}
                                    </span>
                                </div>
                                <div class="flex rounded-xl border border-slate-200 bg-white overflow-hidden focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all" id="promoInputGroup">
                                    <input type="hidden" id="bookingPromoInput" value="">
                                    <input type="text" class="flex-1 px-3.5 py-2.5 bg-transparent text-base sm:text-sm font-semibold text-slate-800 border-0 focus:outline-none placeholder:text-slate-400 uppercase" id="bookingPromoCode" name="coupon_code" placeholder="{{ __('ui.booking.promo_placeholder') }}" autocomplete="off" spellcheck="false">
                                    <button class="px-4 min-h-[44px] bg-primary hover:bg-primary-dark text-white font-bold text-xs sm:text-sm transition-colors cursor-pointer flex items-center justify-center gap-1" type="button" id="applyPromoBtn">
                                        <span>{{ __('ui.booking.apply') }}</span>
                                        <i class="bi bi-arrow-right-short rtl:rotate-180"></i>
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
                                                <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[10px]" id="promoDiscountBadge">{{ __('ui.booking.promo_applied') }}</span>
                                            </div>
                                            <div class="text-xs font-bold text-emerald-600 mt-0.5" id="promoSavingsText">{{ __('ui.booking.savings_applied') }}</div>
                                        </div>
                                    </div>
                                    <button type="button" class="text-xs text-red-500 hover:text-red-700 font-semibold cursor-pointer py-2 px-2.5 min-h-[44px] inline-flex items-center" id="removePromoBtn" aria-label="{{ __('ui.common.remove') }}">
                                        <i class="bi bi-x-circle me-1"></i>{{ __('ui.common.remove') }}
                                    </button>
                                </div>
                                <div class="hidden mt-2 text-xs text-red-600 items-center gap-1.5" id="promoErrorBox">
                                    <i class="bi bi-exclamation-circle-fill shrink-0"></i>
                                    <span id="promoErrorMessage">{{ __('ui.booking.invalid_promo') }}</span>
                                </div>
                            </div>

                            <!-- Payment Options -->
                            <div class="mb-5" id="paymentOptions" data-ziina-active="{{ $ziinaActive ? '1' : '0' }}" data-advance-percent="{{ $advancePercent }}">
                                <div class="text-xs font-black uppercase tracking-wider text-slate-800 mb-2">{{ __('ui.booking.payment_options') }}</div>
                                <div class="payment-options grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <div class="payment-option selected p-3 rounded-2xl border-2 border-primary bg-orange-50/40 cursor-pointer text-start transition-all" data-value="cash">
                                        <div class="payment-option-title font-bold text-slate-900 text-xs sm:text-sm">{{ __('ui.booking.cash') }}</div>
                                        <div class="payment-option-sub text-slate-500 text-[11px]">{{ __('ui.booking.pay_on_pickup') }}</div>
                                    </div>
                                    @if($ziinaActive)
                                    <div class="payment-option p-3 rounded-2xl border border-slate-200 bg-white hover:border-primary/50 cursor-pointer text-start transition-all" data-value="advance">
                                        <div class="payment-option-title font-bold text-slate-900 text-xs sm:text-sm">{{ __('ui.booking.advance') }} ({{ $advancePercent }}%)</div>
                                        <div class="payment-option-sub text-slate-500 text-[11px]">{{ __('ui.booking.hold_slot', ['percent' => $advancePercent]) }}</div>
                                    </div>
                                    <div class="payment-option p-3 rounded-2xl border border-slate-200 bg-white hover:border-primary/50 cursor-pointer text-start transition-all" data-value="full">
                                        <div class="payment-option-title font-bold text-slate-900 text-xs sm:text-sm">{{ __('ui.booking.full_payment') }} (100%)</div>
                                        <div class="payment-option-sub text-slate-500 text-[11px]">{{ __('ui.booking.instant_confirmation') }}</div>
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
                                        {!! __('ui.booking.agree_terms', [
                                            'terms' => '<a href="' . localized_route('terms') . '" target="_blank" rel="noopener noreferrer" class="text-primary font-bold hover:underline">' . e(__('ui.nav.terms')) . '</a>',
                                            'privacy' => '<a href="' . localized_route('privacy') . '" target="_blank" rel="noopener noreferrer" class="text-primary font-bold hover:underline">' . e(__('ui.nav.privacy')) . '</a>',
                                        ]) !!}
                                    </span>
                                </label>
                                <label class="flex items-start gap-2.5 text-xs text-slate-500 cursor-pointer">
                                    <input class="mt-0.5 rounded border-slate-300 text-primary focus:ring-primary/20" type="checkbox" id="bookingNewsletter" name="subscribe_newsletter" value="1" checked>
                                    <span>{{ __('ui.booking.keep_updated') }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                <!-- Modal Static Fixed Footer with Live Total & Action Buttons -->
                <div class="border-t border-slate-200/90 bg-white py-4 px-5 sm:px-7 shrink-0 shadow-xs z-10">
                    <div class="flex items-center justify-between w-full gap-4 pb-safe" style="padding-bottom: max(0.5rem, env(safe-area-inset-bottom, 0.5rem));">
                        <div class="text-start">
                            <small class="text-[10px] sm:text-[11px] font-black uppercase tracking-wider text-slate-500 block mb-0.5">{{ __('ui.booking.total') }}</small>
                            <div class="font-black text-primary font-mono text-xl sm:text-2xl leading-none" id="bookingTotal" data-aed="0">AED 0.00</div>
                        </div>
                        <div class="flex items-center gap-2.5 ms-auto" id="continueBtnWrapper">
                            <button type="button" 
                                    class="px-6 sm:px-8 py-3 min-h-[44px] rounded-full bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-extrabold text-xs sm:text-sm shadow-md hover:shadow-lg transition-all cursor-pointer inline-flex items-center justify-center gap-2" 
                                    id="nextStep">
                                <span>{{ __('ui.common.continue') }}</span>
                                <i class="bi bi-arrow-right rtl:rotate-180"></i>
                            </button>
                            <button type="submit" 
                                    class="px-6 sm:px-8 py-3 min-h-[44px] rounded-full bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-xs sm:text-sm shadow-md hover:shadow-lg transition-all cursor-pointer hidden items-center justify-center gap-2" 
                                    id="submitBooking">
                                <span>{{ __('ui.common.confirm_booking') }}</span>
                                <i class="bi bi-check-lg text-base"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    window.appliedPromoCoupon = null;

    const promoInput = document.getElementById('bookingPromoCode');
    const hiddenPromoInput = document.getElementById('bookingPromoInput');
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
        const cleanUrlPromo = urlPromo.toUpperCase().trim();
        promoInput.value = cleanUrlPromo;
        if (hiddenPromoInput) hiddenPromoInput.value = cleanUrlPromo;
        window.pendingPromoCode = cleanUrlPromo;
    }

    window.validateCurrentPromo = function(isSilent = false) {
        if (!promoInput) return;
        const code = (promoInput.value || hiddenPromoInput?.value || window.pendingPromoCode || '').trim().toUpperCase();
        if (code) {
            promoInput.value = code;
            if (hiddenPromoInput) hiddenPromoInput.value = code;
        }
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
                const rawChildPrice = parseFloat(selectedTierCard.dataset.childPrice || 0);
                const childPrice = rawChildPrice > 0 ? rawChildPrice : (p * 0.70);
                if (['per buggy', 'per vehicle', 'per group', 'private'].includes(pType)) {
                    const capacity = parseInt(selectedTierCard.dataset.capacity || '2', 10) || 2;
                    const vehicles = Math.ceil(Math.max(1, adults + children) / capacity);
                    subtotal = p * vehicles;
                } else {
                    subtotal = (p * adults) + (childPrice * children);
                }
            }
        }

        if (subtotal <= 0) {
            const rawTxt = document.getElementById('bookingTotal')?.innerText?.replace(/[^0-9.]/g, '') || document.getElementById('summaryTotal')?.innerText?.replace(/[^0-9.]/g, '') || '0';
            subtotal = parseFloat(rawTxt) || 0;
        }

        if (subtotal <= 0) {
            window.pendingPromoCode = code;
            return;
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
                    applyBtn.innerHTML = '<span>Apply</span> <i class="bi bi-arrow-right-short rtl:rotate-180 text-base"></i>';
                }
                if (promoInput) promoInput.disabled = false;
            }

            if (status === 200 && body.success && body.coupon) {
                window.appliedPromoCoupon = body.coupon;
                window.pendingPromoCode = null;
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
                    applyBtn.innerHTML = '<span>Apply</span> <i class="bi bi-arrow-right-short rtl:rotate-180 text-base"></i>';
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
        window.pendingPromoCode = null;
        if (promoInput) promoInput.value = '';
        if (hiddenPromoInput) hiddenPromoInput.value = '';
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
