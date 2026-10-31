@php
    $settingsService = app(\App\Services\SettingsService::class);
    $conciergePromoActive = ($settingsService->get('concierge_promo_active', '0') === '1');
    $conciergePromoDiscount = $settingsService->get('concierge_promo_discount', '5');
    $conciergePromoCode = $settingsService->get('concierge_promo_code', 'MATCH5');
@endphp

<!-- Safari Match Concierge Recommendation & Special Offer Modal (Tailwind v4 + Alpine.js) -->
<div id="safariMatcherModal"
     x-data="safariMatcherModal({
        conciergePromoActive: {{ $conciergePromoActive ? 'true' : 'false' }},
        conciergePromoCode: '{{ $conciergePromoCode }}',
        waPhone: '{{ preg_replace('/[^0-9]/','',(string)($settings['site_whatsapp'] ?? '971502456056')) }}'
     })"
     x-show="$store.modal.active === 'safari-matcher'"
     x-cloak
     style="display: none;"
     class="fixed inset-0 z-50 overflow-y-auto"
     role="dialog"
     aria-modal="true"
     aria-labelledby="safariMatcherModalLabel"
     @keydown.escape.window="$store.modal.close()">

    <!-- Backdrop -->
    <div x-show="$store.modal.active === 'safari-matcher'"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity z-0"
         @click="$store.modal.close()"></div>

    <!-- Modal Dialog Panel -->
    <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-4 text-center">
        <div x-show="$store.modal.active === 'safari-matcher'"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="relative z-10 w-full max-w-2xl transform overflow-hidden rounded-3xl bg-slate-950 p-5 sm:p-7 text-start align-middle shadow-2xl transition-all border border-orange-500/40 text-white flex flex-col min-h-[500px]"
             @click.stop>
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-primary/20 text-primary border border-primary/40 mb-1">
                        <i class="bi bi-compass-fill"></i> {{ __('ui.safari_quiz.badge') }}
                    </span>
                    <h4 id="safariMatcherModalLabel" class="text-lg sm:text-xl font-black text-white">
                        {{ __('ui.safari_quiz.title_prefix') }} <span class="bg-gradient-to-r from-orange-500 to-amber-400 bg-clip-text text-transparent">{{ __('ui.safari_quiz.title_highlight') }}</span>
                    </h4>
                </div>
                <button type="button" 
                        class="w-11 h-11 rounded-full bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 flex items-center justify-center transition-colors cursor-pointer" 
                        @click="$store.modal.close()" 
                        aria-label="{{ __('ui.common.close') }}">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>

            <!-- Stepper Progress Bar -->
            <div class="py-3" x-show="step <= 3">
                <div class="flex items-center justify-between text-slate-300 text-xs font-mono mb-1.5">
                    <span x-text="step === 1 ? '{{ addslashes(__('ui.safari_quiz.step1_indicator')) }}' : (step === 2 ? '{{ addslashes(__('ui.safari_quiz.step2_indicator')) }}' : '{{ addslashes(__('ui.safari_quiz.step3_indicator')) }}')"></span>
                    <span x-text="step === 1 ? '33%' : (step === 2 ? '66%' : '90%')"></span>
                </div>
                <div class="w-full h-1.5 bg-slate-800/80 border border-slate-700/50 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-orange-500 to-amber-400 transition-all duration-300 rounded-full"
                         :style="'width: ' + (step === 1 ? '33%' : (step === 2 ? '66%' : '90%'))"></div>
                </div>
            </div>

            <!-- Modal Content Area -->
            <div class="flex-1 py-4 overflow-y-auto max-h-[75vh]">
                
                <!-- Step 1: Group Style -->
                <div x-show="step === 1">
                    <h5 class="font-extrabold text-base sm:text-lg text-white mb-1">{{ __('ui.safari_quiz.step1_title') }}</h5>
                    <p class="text-slate-300 text-xs mb-4">{{ __('ui.safari_quiz.step1_subtitle') }}</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div @click="selectOption('group', 'family')" 
                             class="p-3.5 rounded-2xl cursor-pointer transition-all border text-start bg-slate-900/80 border-slate-700/80 hover:border-primary hover:bg-orange-500/10"
                             :class="answers.group === 'family' ? 'border-primary bg-orange-500/15' : ''">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-people-fill text-2xl text-amber-400 shrink-0"></i>
                                <div>
                                    <div class="font-bold text-white text-sm mb-1">{{ __('ui.safari_quiz.group_family_title') }}</div>
                                    <div class="text-slate-300 text-xs leading-relaxed">{{ __('ui.safari_quiz.group_family_desc') }}</div>
                                </div>
                            </div>
                        </div>

                        <div @click="selectOption('group', 'adventure')" 
                             class="p-3.5 rounded-2xl cursor-pointer transition-all border text-start bg-slate-900/80 border-slate-700/80 hover:border-primary hover:bg-orange-500/10"
                             :class="answers.group === 'adventure' ? 'border-primary bg-orange-500/15' : ''">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-lightning-charge-fill text-2xl text-amber-400 shrink-0"></i>
                                <div>
                                    <div class="font-bold text-white text-sm mb-1">{{ __('ui.safari_quiz.group_adventure_title') }}</div>
                                    <div class="text-slate-300 text-xs leading-relaxed">{{ __('ui.safari_quiz.group_adventure_desc') }}</div>
                                </div>
                            </div>
                        </div>

                        <div @click="selectOption('group', 'luxury')" 
                             class="p-3.5 rounded-2xl cursor-pointer transition-all border text-start bg-slate-900/80 border-slate-700/80 hover:border-primary hover:bg-orange-500/10"
                             :class="answers.group === 'luxury' ? 'border-primary bg-orange-500/15' : ''">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-award-fill text-2xl text-amber-400 shrink-0"></i>
                                <div>
                                    <div class="font-bold text-white text-sm mb-1">{{ __('ui.safari_quiz.group_luxury_title') }}</div>
                                    <div class="text-slate-300 text-xs leading-relaxed">{{ __('ui.safari_quiz.group_luxury_desc') }}</div>
                                </div>
                            </div>
                        </div>

                        <div @click="selectOption('group', 'budget')" 
                             class="p-3.5 rounded-2xl cursor-pointer transition-all border text-start bg-slate-900/80 border-slate-700/80 hover:border-primary hover:bg-orange-500/10"
                             :class="answers.group === 'budget' ? 'border-primary bg-orange-500/15' : ''">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-tag-fill text-2xl text-amber-400 shrink-0"></i>
                                <div>
                                    <div class="font-bold text-white text-sm mb-1">{{ __('ui.safari_quiz.priority_budget_title') }}</div>
                                    <div class="text-slate-300 text-xs leading-relaxed">{{ __('ui.safari_quiz.priority_budget_desc') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Time & Vibe -->
                <div x-show="step === 2">
                    <h5 class="font-extrabold text-base sm:text-lg text-white mb-1">{{ __('ui.safari_quiz.step2_title') }}</h5>
                    <p class="text-slate-300 text-xs mb-4">{{ __('ui.safari_quiz.step2_subtitle') }}</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div @click="selectOption('vibe', 'evening')" 
                             class="p-3.5 rounded-2xl cursor-pointer transition-all border text-start bg-slate-900/80 border-slate-700/80 hover:border-primary hover:bg-orange-500/10"
                             :class="answers.vibe === 'evening' ? 'border-primary bg-orange-500/15' : ''">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-sunset-fill text-2xl text-amber-400 shrink-0"></i>
                                <div>
                                    <div class="font-bold text-white text-sm mb-1">{{ __('ui.safari_quiz.time_evening_title') }}</div>
                                    <div class="text-slate-300 text-xs leading-relaxed">{{ __('ui.safari_quiz.time_evening_desc') }}</div>
                                </div>
                            </div>
                        </div>

                        <div @click="selectOption('vibe', 'morning')" 
                             class="p-3.5 rounded-2xl cursor-pointer transition-all border text-start bg-slate-900/80 border-slate-700/80 hover:border-primary hover:bg-orange-500/10"
                             :class="answers.vibe === 'morning' ? 'border-primary bg-orange-500/15' : ''">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-sunrise-fill text-2xl text-amber-400 shrink-0"></i>
                                <div>
                                    <div class="font-bold text-white text-sm mb-1">{{ __('ui.safari_quiz.time_morning_title') }}</div>
                                    <div class="text-slate-300 text-xs leading-relaxed">{{ __('ui.safari_quiz.time_morning_desc') }}</div>
                                </div>
                            </div>
                        </div>

                        <div @click="selectOption('vibe', 'overnight')" 
                             class="p-3.5 rounded-2xl cursor-pointer transition-all border text-start bg-slate-900/80 border-slate-700/80 hover:border-primary hover:bg-orange-500/10"
                             :class="answers.vibe === 'overnight' ? 'border-primary bg-orange-500/15' : ''">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-moon-stars-fill text-2xl text-amber-400 shrink-0"></i>
                                <div>
                                    <div class="font-bold text-white text-sm mb-1">{{ __('ui.safari_quiz.time_overnight_title') }}</div>
                                    <div class="text-slate-300 text-xs leading-relaxed">{{ __('ui.safari_quiz.time_overnight_desc') }}</div>
                                </div>
                            </div>
                        </div>

                        <div @click="selectOption('vibe', 'cruise')" 
                             class="p-3.5 rounded-2xl cursor-pointer transition-all border text-start bg-slate-900/80 border-slate-700/80 hover:border-primary hover:bg-orange-500/10"
                             :class="answers.vibe === 'cruise' ? 'border-primary bg-orange-500/15' : ''">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-water text-2xl text-amber-400 shrink-0"></i>
                                <div>
                                    <div class="font-bold text-white text-sm mb-1">{{ __('ui.safari_quiz.time_buggy_title') }}</div>
                                    <div class="text-slate-300 text-xs leading-relaxed">{{ __('ui.safari_quiz.time_buggy_desc') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Must-Haves -->
                <div x-show="step === 3">
                    <h5 class="font-extrabold text-base sm:text-lg text-white mb-1">{{ __('ui.safari_quiz.step3_title') }}</h5>
                    <p class="text-slate-300 text-xs mb-4">{{ __('ui.safari_quiz.step3_subtitle') }}</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div @click="selectOption('perk', 'quad_buggy')" 
                             class="p-3.5 rounded-2xl cursor-pointer transition-all border text-start bg-slate-900/80 border-slate-700/80 hover:border-primary hover:bg-orange-500/10"
                             :class="answers.perk === 'quad_buggy' ? 'border-primary bg-orange-500/15' : ''">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-speedometer2 text-2xl text-amber-400 shrink-0"></i>
                                <div>
                                    <div class="font-bold text-white text-sm mb-1">{{ __('ui.safari_quiz.time_buggy_title') }}</div>
                                    <div class="text-slate-300 text-xs leading-relaxed">{{ __('ui.safari_quiz.priority_extreme_desc') }}</div>
                                </div>
                            </div>
                        </div>

                        <div @click="selectOption('perk', 'vip_service')" 
                             class="p-3.5 rounded-2xl cursor-pointer transition-all border text-start bg-slate-900/80 border-slate-700/80 hover:border-primary hover:bg-orange-500/10"
                             :class="answers.perk === 'vip_service' ? 'border-primary bg-orange-500/15' : ''">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-cup-hot-fill text-2xl text-amber-400 shrink-0"></i>
                                <div>
                                    <div class="font-bold text-white text-sm mb-1">{{ __('ui.safari_quiz.priority_vip_title') }}</div>
                                    <div class="text-slate-300 text-xs leading-relaxed">{{ __('ui.safari_quiz.priority_vip_desc') }}</div>
                                </div>
                            </div>
                        </div>

                        <div @click="selectOption('perk', 'private_car')" 
                             class="p-3.5 rounded-2xl cursor-pointer transition-all border text-start bg-slate-900/80 border-slate-700/80 hover:border-primary hover:bg-orange-500/10"
                             :class="answers.perk === 'private_car' ? 'border-primary bg-orange-500/15' : ''">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-car-front-fill text-2xl text-amber-400 shrink-0"></i>
                                <div>
                                    <div class="font-bold text-white text-sm mb-1">{{ __('ui.safari_quiz.priority_pickup_title') }}</div>
                                    <div class="text-slate-300 text-xs leading-relaxed">{{ __('ui.safari_quiz.priority_pickup_desc') }}</div>
                                </div>
                            </div>
                        </div>

                        <div @click="selectOption('perk', 'all_inclusive')" 
                             class="p-3.5 rounded-2xl cursor-pointer transition-all border text-start bg-slate-900/80 border-slate-700/80 hover:border-primary hover:bg-orange-500/10"
                             :class="answers.perk === 'all_inclusive' ? 'border-primary bg-orange-500/15' : ''">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-star-fill text-2xl text-amber-400 shrink-0"></i>
                                <div>
                                    <div class="font-bold text-white text-sm mb-1">{{ __('ui.safari_quiz.priority_budget_title') }}</div>
                                    <div class="text-slate-300 text-xs leading-relaxed">{{ __('ui.safari_quiz.package_includes') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div x-show="loading" class="text-center py-12">
                    <div class="inline-block w-12 h-12 border-4 border-amber-400 border-t-transparent rounded-full animate-spin mb-4"></div>
                    <h5 class="text-lg font-bold text-white mb-2">{{ __('ui.safari_quiz.calculating_title') }}</h5>
                    <p class="text-slate-300 text-xs max-w-sm mx-auto">{{ __('ui.safari_quiz.calculating_desc') }}</p>
                </div>

                <!-- Result Screen -->
                <div x-show="step === 4 && !loading && result">
                    <div class="text-center mb-4">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold mb-2">
                            <i class="bi bi-patch-check-fill"></i> {{ __('ui.safari_quiz.recommendation_badge') }}
                        </span>
                        <h4 class="text-xl font-black text-white">{{ __('ui.safari_quiz.recommendation_badge') }}</h4>
                        <p class="text-slate-300 text-xs">{{ __('ui.safari_quiz.match_confidence') }}</p>
                    </div>

                    <!-- Tour Card -->
                    <div class="p-4 rounded-2xl bg-slate-900 border border-orange-500/30 mb-4">
                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            <div class="w-full sm:w-32 h-24 rounded-xl overflow-hidden shrink-0 relative bg-slate-800">
                                <img :src="(result && result.thumb) ? result.thumb : '{{ asset('images/desert-safari-poster.avif') }}'" :alt="result ? result.name : 'Dunes Discovery Tour'" class="w-full h-full object-cover">
                                <span class="absolute top-1 start-1 px-1.5 py-0.5 rounded-full bg-amber-400 text-slate-950 font-black text-[9px]">{{ __('ui.safari_quiz.recommendation_badge') }}</span>
                            </div>
                            <div class="flex-1 min-w-0 text-start">
                                <div class="flex items-start justify-between gap-2">
                                    <h5 class="font-extrabold text-white text-base truncate" x-text="result ? result.name : ''">Evening Desert Safari</h5>
                                    <div class="text-end shrink-0">
                                        <div class="text-lg font-black text-amber-400 font-mono" x-text="'{{ __('ui.common.aed') }} ' + ((result && result.min_price) ? result.min_price : 120)">{{ __('ui.common.aed') }} 150</div>
                                        <small class="text-slate-400 text-[10px]">{{ __('ui.rate_card.per_person') }}</small>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 text-slate-300 text-xs my-1">
                                    <span class="flex items-center gap-1"><i class="bi bi-star-fill text-amber-400"></i>4.9/5 (1,200+ Reviews)</span>
                                    <span>•</span>
                                    <span class="flex items-center gap-1"><i class="bi bi-clock"></i><span x-text="(result && result.duration) ? result.duration : '6-7 Hours'"></span></span>
                                </div>
                                <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-700/80 mt-2">
                                    <span class="text-[10px] uppercase font-bold text-slate-300 tracking-wider block mb-1">{{ __('ui.safari_quiz.match_confidence') }}:</span>
                                    <ul class="space-y-1 text-xs text-slate-300">
                                        <template x-for="reason in reasons" :key="reason">
                                             <li class="flex items-start gap-1.5">
                                                 <i class="bi bi-check2 text-emerald-400 shrink-0 mt-0.5"></i>
                                                 <span x-text="reason"></span>
                                             </li>
                                         </template>
                                     </ul>
                                 </div>
                             </div>
                         </div>
                     </div>

                     @if($conciergePromoActive)
                     <!-- Gamified Reward Box: Concierge Discount Certificate -->
                     <div class="p-3.5 rounded-2xl mb-4 bg-gradient-to-r from-orange-500/20 to-amber-500/10 border-2 border-dashed border-orange-500/50 flex items-center justify-between flex-wrap gap-2 text-start">
                         <div class="flex items-center gap-3">
                             <div class="w-9 h-9 rounded-full bg-amber-400 text-slate-950 flex items-center justify-center shrink-0 text-lg">
                                 <i class="bi bi-gift-fill"></i>
                             </div>
                             <div>
                                 <div class="font-bold text-white text-xs">Congratulations! {{ $conciergePromoDiscount }}% Matcher Promo Unlocked</div>
                                 <div class="text-slate-300 text-[11px]">Automatically applied when you proceed to booking.</div>
                             </div>
                         </div>
                         <span class="font-mono font-bold text-sm px-3 py-1 rounded-xl bg-slate-900 text-amber-400 border border-amber-400/40">{{ $conciergePromoCode }}</span>
                     </div>
                     @endif

                     <!-- CTAs -->
                     <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                         <button type="button" 
                                 class="sm:col-span-7 w-full py-3.5 rounded-full bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-extrabold text-sm shadow-md transition-all cursor-pointer flex items-center justify-center gap-2" 
                                 @click="book()">
                             <span>{{ $conciergePromoActive ? __('ui.common.book_now') . " ({$conciergePromoDiscount}% OFF)" : __('ui.common.book_now') }}</span>
                             <i class="bi bi-arrow-right rtl:rotate-180"></i>
                         </button>
                         <a :href="waUrl" 
                            target="_blank" 
                            rel="noopener" 
                            class="btn-whatsapp sm:col-span-5 w-full py-3.5 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-sm shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer"
                           data-action="whatsapp"
                           :data-tour-name="result ? result.name : ''"
                           @click.prevent="window.openWhatsApp ? window.openWhatsApp(result ? result.name : '', waUrl) : null">
                             <i class="bi bi-whatsapp"></i>
                             <span>{{ __('ui.common.whatsapp_inquire') }}</span>
                         </a>
                     </div>

                     <div class="flex items-center justify-between mt-4 px-1 text-xs">
                         <button type="button" 
                                 class="text-slate-300 hover:text-white transition-colors cursor-pointer flex items-center gap-1" 
                                 @click="resetQuiz()">
                             <i class="bi bi-arrow-counterclockwise"></i>
                             <span>{{ __('ui.safari_quiz.retake_btn') }}</span>
                         </button>
                         <a href="{{ localized_route('tours.index') }}" class="text-amber-400 hover:text-amber-300 transition-colors flex items-center gap-1">
                             <i class="bi bi-shuffle"></i>
                             <span>{{ __('ui.blog_journal.browse_all_tours') }}</span>
                         </a>
                     </div>
                 </div>

             </div>

             <!-- Modal Footer Controls -->
             <div class="pt-3 border-t border-slate-800 flex items-center justify-between text-xs text-slate-300" x-show="step <= 3">
                 <button type="button" 
                         class="min-h-[44px] px-4 py-2 rounded-full border border-slate-700 hover:border-slate-500 text-slate-300 transition-colors cursor-pointer flex items-center justify-center gap-1" 
                         x-show="step > 1" 
                         @click="step--">
                     <i class="bi bi-chevron-left rtl:rotate-180"></i> {{ __('ui.safari_quiz.back_btn') }}
                 </button>
                 <div class="ms-auto flex items-center gap-1.5 text-[11px]">
                     <i class="bi bi-shield-lock-fill text-amber-400"></i>
                     <span>DET Licensed #1430583 • No Credit Card Required</span>
                 </div>
             </div>

         </div>
    </div>
</div>

<script>
window.openSafariMatcher = function() {
    if (window.Alpine && Alpine.store('modal')) {
        Alpine.store('modal').open('safari-matcher');
    }
};
</script>
