@php
    $settingsService = app(\App\Services\SettingsService::class);
    $conciergePromoActive = ($settingsService->get('concierge_promo_active', '0') === '1');
    $conciergePromoDiscount = $settingsService->get('concierge_promo_discount', '5');
    $conciergePromoCode = $settingsService->get('concierge_promo_code', 'MATCH5');
@endphp
<section class="py-12 sm:py-16 bg-slate-50 relative overflow-hidden" 
         id="safariMatcherSection"
         x-data="safariMatcherQuiz({
            conciergePromoActive: {{ $conciergePromoActive ? 'true' : 'false' }},
            conciergePromoCode: '{{ $conciergePromoCode }}'
         })">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="max-w-2xl mx-auto text-center mb-8">
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-black bg-orange-50 text-primary border border-orange-200/60 uppercase tracking-wider mb-2">
                <i class="bi bi-compass"></i> Tour Recommender
            </span>
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-900 tracking-tight mb-2">
                Find Your Perfect Dubai Experience in 3 Clicks
            </h2>
            <p class="text-slate-500 text-xs sm:text-sm leading-relaxed">
                Not sure which tour to pick? Answer 3 quick questions and our recommendation engine will find the exact experience tailored to your trip.
            </p>
        </div>

        <!-- Quiz Container Card -->
        <div class="max-w-3xl mx-auto">
            <div class="rounded-3xl bg-white shadow-xl border border-slate-200/80 p-5 sm:p-8">
                
                <!-- Progress Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 mb-6" x-show="step <= 3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-full bg-primary text-white flex items-center justify-center font-black text-xs" x-text="step">1</span>
                        <span class="font-bold text-slate-800 text-xs sm:text-sm" x-text="step === 1 ? 'Choose Experience Type' : (step === 2 ? 'Choose Time of Day' : 'Choose Group Style & Pace')">Choose Experience Type</span>
                    </div>
                    <span class="text-slate-500 font-mono text-xs font-bold" x-text="'Step ' + step + ' of 3'">Step 1 of 3</span>
                </div>

                <!-- Step 1: Category -->
                <div x-show="step === 1">
                    <h4 class="font-black text-slate-900 text-base sm:text-lg mb-4 text-center">What type of Dubai experience are you looking for?</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div @click="selectChoice(1, 'desert')" class="p-4 rounded-2xl border border-slate-200 hover:border-primary hover:bg-orange-50/50 cursor-pointer transition-all text-center group">
                            <i class="bi bi-compass text-3xl text-amber-500 mb-2 block group-hover:scale-110 transition-transform"></i>
                            <h6 class="font-bold text-slate-900 text-sm mb-1">Desert Safari & Red Dunes</h6>
                            <p class="text-slate-500 text-xs leading-relaxed">4x4 Dune Bashing, Camel Rides, Live Camp Shows & BBQ Dinner</p>
                        </div>
                        <div @click="selectChoice(1, 'city')" class="p-4 rounded-2xl border border-slate-200 hover:border-primary hover:bg-orange-50/50 cursor-pointer transition-all text-center group">
                            <i class="bi bi-buildings text-3xl text-amber-500 mb-2 block group-hover:scale-110 transition-transform"></i>
                            <h6 class="font-bold text-slate-900 text-sm mb-1">City Sightseeing & Landmarks</h6>
                            <p class="text-slate-500 text-xs leading-relaxed">Dubai & Abu Dhabi iconic tours, Burj Khalifa & Grand Mosque</p>
                        </div>
                        <div @click="selectChoice(1, 'water')" class="p-4 rounded-2xl border border-slate-200 hover:border-primary hover:bg-orange-50/50 cursor-pointer transition-all text-center group">
                            <i class="bi bi-water text-3xl text-amber-500 mb-2 block group-hover:scale-110 transition-transform"></i>
                            <h6 class="font-bold text-slate-900 text-sm mb-1">Marina Dhow & Luxury Cruise</h6>
                            <p class="text-slate-500 text-xs leading-relaxed">Dubai Marina dinner cruise with skyline views & entertainment</p>
                        </div>
                        <div @click="selectChoice(1, 'quad_buggy')" class="p-4 rounded-2xl border border-slate-200 hover:border-primary hover:bg-orange-50/50 cursor-pointer transition-all text-center group">
                            <i class="bi bi-speedometer2 text-3xl text-amber-500 mb-2 block group-hover:scale-110 transition-transform"></i>
                            <h6 class="font-bold text-slate-900 text-sm mb-1">Quad Bike & Buggy Rentals</h6>
                            <p class="text-slate-500 text-xs leading-relaxed">Self-drive 1000cc Buggy & 400cc ATV adrenaline in open red dunes</p>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Timing -->
                <div x-show="step === 2">
                    <h4 class="font-black text-slate-900 text-base sm:text-lg mb-4 text-center">What time of day do you prefer?</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                        <div @click="selectChoice(2, 'morning')" class="p-4 rounded-2xl border border-slate-200 hover:border-primary hover:bg-orange-50/50 cursor-pointer transition-all text-center group">
                            <i class="bi bi-sunrise text-3xl text-amber-500 mb-2 block group-hover:scale-110 transition-transform"></i>
                            <h6 class="font-bold text-slate-900 text-sm mb-1">Morning Experience</h6>
                            <p class="text-slate-500 text-xs leading-relaxed">8:00 AM - 12:00 PM â€¢ Crisp breeze & cool weather</p>
                        </div>
                        <div @click="selectChoice(2, 'evening')" class="p-4 rounded-2xl border border-slate-200 hover:border-primary hover:bg-orange-50/50 cursor-pointer transition-all text-center group">
                            <i class="bi bi-sunset text-3xl text-amber-500 mb-2 block group-hover:scale-110 transition-transform"></i>
                            <h6 class="font-bold text-slate-900 text-sm mb-1">Evening & Sunset</h6>
                            <p class="text-slate-500 text-xs leading-relaxed">3:00 PM - 9:30 PM â€¢ Sunset, 5-Star Buffet & Shows</p>
                        </div>
                        <div @click="selectChoice(2, 'overnight')" class="p-4 rounded-2xl border border-slate-200 hover:border-primary hover:bg-orange-50/50 cursor-pointer transition-all text-center group">
                            <i class="bi bi-moon-stars text-3xl text-amber-500 mb-2 block group-hover:scale-110 transition-transform"></i>
                            <h6 class="font-bold text-slate-900 text-sm mb-1">Overnight Stay</h6>
                            <p class="text-slate-500 text-xs leading-relaxed">Camp under stars with campfire & morning breakfast</p>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Style -->
                <div x-show="step === 3">
                    <h4 class="font-black text-slate-900 text-base sm:text-lg mb-4 text-center">What is your group style & pace?</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                        <div @click="selectChoice(3, 'family')" class="p-4 rounded-2xl border border-slate-200 hover:border-primary hover:bg-orange-50/50 cursor-pointer transition-all text-center group">
                            <i class="bi bi-people text-3xl text-amber-500 mb-2 block group-hover:scale-110 transition-transform"></i>
                            <h6 class="font-bold text-slate-900 text-sm mb-1">Family & Friends</h6>
                            <p class="text-slate-500 text-xs leading-relaxed">Relaxed sightseeing, photo stops & family-friendly fun</p>
                        </div>
                        <div @click="selectChoice(3, 'thrill')" class="p-4 rounded-2xl border border-slate-200 hover:border-primary hover:bg-orange-50/50 cursor-pointer transition-all text-center group">
                            <i class="bi bi-speedometer2 text-3xl text-amber-500 mb-2 block group-hover:scale-110 transition-transform"></i>
                            <h6 class="font-bold text-slate-900 text-sm mb-1">Thrill & Adrenaline</h6>
                            <p class="text-slate-500 text-xs leading-relaxed">Dune bashing, quad biking, sandboarding & high excitement</p>
                        </div>
                        <div @click="selectChoice(3, 'luxury')" class="p-4 rounded-2xl border border-slate-200 hover:border-primary hover:bg-orange-50/50 cursor-pointer transition-all text-center group">
                            <i class="bi bi-award text-3xl text-amber-400 mb-2 block group-hover:scale-110 transition-transform"></i>
                            <h6 class="font-bold text-slate-900 text-sm mb-1">VIP Luxury & Romance</h6>
                            <p class="text-slate-500 text-xs leading-relaxed">Private 4x4, reserved VIP dining & premium comfort</p>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Result -->
                <div x-show="step === 4">
                    <div class="text-center mb-6">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 mb-2">
                            <i class="bi bi-patch-check-fill text-emerald-500"></i> Top Recommendation Based on Your Choices
                        </span>
                        <h3 class="text-xl sm:text-2xl font-black text-slate-900 mb-1" x-text="matchedTour.title">Evening Desert Safari Dubai</h3>
                        <div class="flex items-center justify-center gap-3 text-xs text-slate-500 mb-2">
                            <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 font-semibold" x-text="matchedTour.category">Desert Safari</span>
                            <span class="flex items-center gap-1"><i class="bi bi-clock"></i><span x-text="matchedTour.duration"></span></span>
                            <span>â€¢</span>
                            <span class="flex items-center gap-1"><i class="bi bi-star-fill text-amber-400"></i><span x-text="matchedTour.rating"></span></span>
                        </div>
                        <p class="text-slate-500 text-xs sm:text-sm max-w-lg mx-auto" x-text="matchedTour.desc"></p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 border border-slate-200 p-5 mb-5">
                        @if($conciergePromoActive)
                        <div class="p-3 rounded-xl mb-4 bg-orange-50 border border-orange-200 flex items-center justify-between flex-wrap gap-2 text-xs">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-gift-fill text-primary text-base"></i>
                                <span class="font-bold text-slate-800">{{ $conciergePromoDiscount }}% Matcher Promo Unlocked: <span class="font-mono font-black text-primary">{{ $conciergePromoCode }}</span></span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full bg-slate-900 text-amber-400 font-bold text-[10px]">Auto-Applies at Checkout</span>
                        </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-center">
                            <div class="sm:col-span-7 space-y-2 text-xs text-slate-600">
                                <div class="flex items-center gap-2 text-slate-900 font-bold">
                                    <i class="bi bi-shield-check text-emerald-500 text-base"></i>
                                    <span>Official DTCM-Licensed Operator & 100% Guaranteed Spot</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="bi bi-check2 text-emerald-500 font-bold"></i>
                                    <span>Door-to-door hotel pick & drop included</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="bi bi-check2 text-emerald-500 font-bold"></i>
                                    <span>24-Hour free cancellation available</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="bi bi-check2 text-emerald-500 font-bold"></i>
                                    <span>No hidden charges â€¢ Instant WhatsApp support</span>
                                </div>
                            </div>
                            <div class="sm:col-span-5 text-center sm:text-right border-t sm:border-t-0 sm:border-l border-slate-200 pt-4 sm:pt-0 sm:pl-4">
                                <div class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Starting From</div>
                                <div class="text-3xl font-black text-primary font-mono my-1" x-text="matchedTour.price">AED 150</div>
                                <button type="button" 
                                        class="w-full py-3 rounded-full bg-gradient-to-r from-[#b45309] to-[#c45e14] hover:from-[#9a4408] hover:to-[#b45309] text-white font-extrabold text-xs sm:text-sm shadow-md transition-all cursor-pointer flex items-center justify-center gap-2" 
                                        @click="bookMatched()">
                                    <i class="bi bi-calendar-check-fill"></i>
                                    <span>{{ $conciergePromoActive ? "Book with {$conciergePromoDiscount}% OFF" : "Book Recommended Tour" }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="button" 
                                class="text-xs text-slate-500 hover:text-slate-700 transition-colors inline-flex items-center gap-1.5 cursor-pointer" 
                                @click="resetQuiz()">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            <span>Retake Matcher Quiz</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>