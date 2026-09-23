<!-- Build Your Own Safari Customizer Modal (Tailwind v4 + Alpine.js) -->
<div id="customSafariModal"
     x-data="customSafariModal({
        waPhone: '{{ preg_replace('/[^0-9]/','',(string)($settings['site_whatsapp'] ?? '971502456056')) }}'
     })"
     x-show="$store.modal.active === 'custom-safari'"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     role="dialog"
     aria-modal="true"
     @keydown.escape.window="$store.modal.close()">

    <!-- Backdrop -->
    <div x-show="$store.modal.active === 'custom-safari'"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"
         @click="$store.modal.close()"></div>

    <!-- Modal Dialog Panel -->
    <div class="min-h-full flex items-center justify-center p-2 sm:p-4 text-center">
        <div x-show="$store.modal.active === 'custom-safari'"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="w-full max-w-5xl transform overflow-hidden rounded-3xl bg-slate-950 text-left align-middle shadow-2xl transition-all border border-orange-500/40 text-white flex flex-col max-h-[92vh]"
             @click.stop>
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 sm:p-5 border-b border-slate-800 bg-slate-900/90 backdrop-blur-md shrink-0">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-primary/20 text-primary border border-primary/40 uppercase tracking-wider">
                            <i class="bi bi-sliders"></i> Interactive Customizer
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                            DET #1430583
                        </span>
                    </div>
                    <h5 class="text-base sm:text-lg font-black text-white" id="customSafariModalLabel">
                        Build Your Own Dubai Desert Safari
                    </h5>
                </div>
                <button type="button" 
                        class="w-8 h-8 rounded-full bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center transition-colors cursor-pointer" 
                        @click="$store.modal.close()" 
                        aria-label="Close">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="p-4 sm:p-6 overflow-y-auto flex-1">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    
                    <!-- Left: Customizer Options (7 cols) -->
                    <div class="lg:col-span-7 space-y-6">
                        
                        <!-- Step 1: Base -->
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-300 mb-2.5">
                                1. Select Base Experience
                            </label>
                            <div class="grid grid-cols-2 gap-2.5">
                                <!-- Base 1 -->
                                <div @click="base = { name: 'Standard Evening Red Dunes', price: 150, tourId: 1 }" 
                                     class="p-3 rounded-2xl cursor-pointer transition-all border text-left"
                                     :class="base.name === 'Standard Evening Red Dunes' ? 'bg-orange-500/15 border-primary shadow-sm' : 'bg-slate-900/70 border-slate-800 hover:border-slate-700'">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <i class="bi bi-sunset text-lg text-amber-400"></i>
                                        <span class="px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 font-black text-[9px]">Popular</span>
                                    </div>
                                    <div class="font-bold text-white text-xs mb-0.5">Standard Evening</div>
                                    <div class="text-slate-400 text-[10px] leading-tight">Dune bashing & BBQ show</div>
                                    <div class="font-extrabold text-amber-400 text-xs mt-2" data-aed="150">AED 150/guest</div>
                                </div>

                                <!-- Base 2 -->
                                <div @click="base = { name: 'VIP Luxury Evening Safari', price: 250, tourId: 2 }" 
                                     class="p-3 rounded-2xl cursor-pointer transition-all border text-left"
                                     :class="base.name === 'VIP Luxury Evening Safari' ? 'bg-orange-500/15 border-primary shadow-sm' : 'bg-slate-900/70 border-slate-800 hover:border-slate-700'">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <i class="bi bi-award text-lg text-amber-400"></i>
                                        <span class="px-2 py-0.5 rounded-full bg-slate-800 text-amber-400 border border-amber-400/40 font-black text-[9px]">VIP</span>
                                    </div>
                                    <div class="font-bold text-white text-xs mb-0.5">VIP Luxury Safari</div>
                                    <div class="text-slate-400 text-[10px] leading-tight">VIP table & waiter service</div>
                                    <div class="font-extrabold text-amber-400 text-xs mt-2" data-aed="250">AED 250/guest</div>
                                </div>

                                <!-- Base 3 -->
                                <div @click="base = { name: 'Morning Desert Safari', price: 120, tourId: 4 }" 
                                     class="p-3 rounded-2xl cursor-pointer transition-all border text-left"
                                     :class="base.name === 'Morning Desert Safari' ? 'bg-orange-500/15 border-primary shadow-sm' : 'bg-slate-900/70 border-slate-800 hover:border-slate-700'">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <i class="bi bi-sunrise text-lg text-sky-400"></i>
                                        <span class="px-2 py-0.5 rounded-full bg-sky-500/20 text-sky-300 font-black text-[9px]">Cooler</span>
                                    </div>
                                    <div class="font-bold text-white text-xs mb-0.5">Morning Safari</div>
                                    <div class="text-slate-400 text-[10px] leading-tight">Cool air & sunrise photos</div>
                                    <div class="font-extrabold text-amber-400 text-xs mt-2" data-aed="120">AED 120/guest</div>
                                </div>

                                <!-- Base 4 -->
                                <div @click="base = { name: 'Overnight Stargazing Safari', price: 350, tourId: 5 }" 
                                     class="p-3 rounded-2xl cursor-pointer transition-all border text-left"
                                     :class="base.name === 'Overnight Stargazing Safari' ? 'bg-orange-500/15 border-primary shadow-sm' : 'bg-slate-900/70 border-slate-800 hover:border-slate-700'">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <i class="bi bi-moon-stars text-lg text-emerald-400"></i>
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-black text-[9px]">Glamping</span>
                                    </div>
                                    <div class="font-bold text-white text-xs mb-0.5">Overnight Safari</div>
                                    <div class="text-slate-400 text-[10px] leading-tight">Bedouin tent & breakfast</div>
                                    <div class="font-extrabold text-amber-400 text-xs mt-2" data-aed="350">AED 350/guest</div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Transfer -->
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-300 mb-2.5">
                                2. Transfer Option
                            </label>
                            <div class="grid grid-cols-3 gap-2">
                                <div @click="transfer = { name: 'Shared 4x4 Land Cruiser', price: 0, type: 'flat' }"
                                     class="p-2.5 rounded-xl cursor-pointer transition-all border text-left"
                                     :class="transfer.name === 'Shared 4x4 Land Cruiser' ? 'bg-orange-500/15 border-primary' : 'bg-slate-900/70 border-slate-800 hover:border-slate-700'">
                                    <div class="font-bold text-white text-xs">Shared 4x4</div>
                                    <div class="text-emerald-400 font-bold text-[10px] mt-1">FREE</div>
                                </div>
                                <div @click="transfer = { name: 'Private 7-Seater 4x4', price: 350, type: 'flat' }"
                                     class="p-2.5 rounded-xl cursor-pointer transition-all border text-left"
                                     :class="transfer.name === 'Private 7-Seater 4x4' ? 'bg-orange-500/15 border-primary' : 'bg-slate-900/70 border-slate-800 hover:border-slate-700'">
                                    <div class="font-bold text-white text-xs">Private 4x4</div>
                                    <div class="text-amber-400 font-bold text-[10px] mt-1" data-aed="350">+AED 350 flat</div>
                                </div>
                                <div @click="transfer = { name: 'VIP Range Rover', price: 750, type: 'flat' }"
                                     class="p-2.5 rounded-xl cursor-pointer transition-all border text-left"
                                     :class="transfer.name === 'VIP Range Rover' ? 'bg-orange-500/15 border-primary' : 'bg-slate-900/70 border-slate-800 hover:border-slate-700'">
                                    <div class="font-bold text-white text-xs">VIP SUV</div>
                                    <div class="text-amber-400 font-bold text-[10px] mt-1" data-aed="750">+AED 750 flat</div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Sports -->
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-300 mb-2.5">
                                3. Desert Motorsports
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <div @click="sports = { name: 'Scenic Only', price: 0, type: 'per_person' }"
                                     class="p-2 rounded-xl cursor-pointer transition-all border text-left"
                                     :class="sports.name === 'Scenic Only' ? 'bg-orange-500/15 border-primary' : 'bg-slate-900/70 border-slate-800 hover:border-slate-700'">
                                    <div class="font-bold text-white text-xs">None</div>
                                    <div class="text-emerald-400 font-bold text-[9px] mt-1">INCLUDED</div>
                                </div>
                                <div @click="sports = { name: '250cc Quad Biking', price: 120, type: 'per_person' }"
                                     class="p-2 rounded-xl cursor-pointer transition-all border text-left"
                                     :class="sports.name === '250cc Quad Biking' ? 'bg-orange-500/15 border-primary' : 'bg-slate-900/70 border-slate-800 hover:border-slate-700'">
                                    <div class="font-bold text-white text-xs">250cc Quad</div>
                                    <div class="text-amber-400 font-bold text-[9px] mt-1" data-aed="120">+AED 120</div>
                                </div>
                                <div @click="sports = { name: '400cc Quad Biking', price: 220, type: 'per_person' }"
                                     class="p-2 rounded-xl cursor-pointer transition-all border text-left"
                                     :class="sports.name === '400cc Quad Biking' ? 'bg-orange-500/15 border-primary' : 'bg-slate-900/70 border-slate-800 hover:border-slate-700'">
                                    <div class="font-bold text-white text-xs">400cc Quad</div>
                                    <div class="text-amber-400 font-bold text-[9px] mt-1" data-aed="220">+AED 220</div>
                                </div>
                                <div @click="sports = { name: '1000cc Can-Am Buggy', price: 550, type: 'flat' }"
                                     class="p-2 rounded-xl cursor-pointer transition-all border text-left"
                                     :class="sports.name === '1000cc Can-Am Buggy' ? 'bg-orange-500/15 border-primary' : 'bg-slate-900/70 border-slate-800 hover:border-slate-700'">
                                    <div class="font-bold text-white text-xs">1000cc Buggy</div>
                                    <div class="text-amber-400 font-bold text-[9px] mt-1" data-aed="550">+AED 550</div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Addons -->
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-300 mb-2.5">
                                4. Optional Camp Luxuries
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <div @click="toggleAddon({ key: 'vip_table', name: 'VIP Table Service', price: 60, type: 'per_person' })"
                                     class="p-2.5 rounded-xl cursor-pointer transition-all border flex items-center justify-between"
                                     :class="hasAddon('vip_table') ? 'bg-orange-500/15 border-primary' : 'bg-slate-900/70 border-slate-800 hover:border-slate-700'">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" :checked="hasAddon('vip_table')" class="rounded border-slate-700 text-primary focus:ring-0 pointer-events-none">
                                        <span class="text-white text-xs font-semibold">VIP Table</span>
                                    </div>
                                    <span class="text-amber-400 text-[10px] font-bold" data-aed="60">+AED 60</span>
                                </div>
                                <div @click="toggleAddon({ key: 'shisha', name: 'Private Table Shisha', price: 50, type: 'flat' })"
                                     class="p-2.5 rounded-xl cursor-pointer transition-all border flex items-center justify-between"
                                     :class="hasAddon('shisha') ? 'bg-orange-500/15 border-primary' : 'bg-slate-900/70 border-slate-800 hover:border-slate-700'">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" :checked="hasAddon('shisha')" class="rounded border-slate-700 text-primary focus:ring-0 pointer-events-none">
                                        <span class="text-white text-xs font-semibold">Table Shisha</span>
                                    </div>
                                    <span class="text-amber-400 text-[10px] font-bold" data-aed="50">+AED 50</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right: Summary & Instant Checkout (5 cols) -->
                    <div class="lg:col-span-5">
                        <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/90 border border-slate-800 flex flex-col h-full justify-between shadow-xl">
                            
                            <!-- Guest Counter -->
                            <div>
                                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-950/60 border border-slate-800 mb-4">
                                    <div>
                                        <div class="font-bold text-white text-xs">Guests (Adults)</div>
                                        <div class="text-slate-400 text-[10px]">Age 11+ years</div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button type="button" 
                                                class="w-7 h-7 rounded-full border border-slate-700 hover:border-slate-500 bg-slate-800 text-white flex items-center justify-center text-xs font-bold transition-colors cursor-pointer"
                                                @click="if (adults > 1) adults--">-</button>
                                        <span class="font-black text-white text-sm w-4 text-center" x-text="adults">2</span>
                                        <button type="button" 
                                                class="w-7 h-7 rounded-full border border-amber-400/50 hover:bg-amber-400 hover:text-slate-950 bg-slate-800 text-amber-400 flex items-center justify-center text-xs font-bold transition-colors cursor-pointer"
                                                @click="if (adults < 30) adults++">+</button>
                                    </div>
                                </div>

                                <!-- Live Specs Breakdown -->
                                <div class="space-y-2 mb-4 text-xs">
                                    <div class="flex justify-between text-slate-400">
                                        <span>Base:</span>
                                        <strong class="text-white font-semibold text-right" x-text="base.name">Standard Evening</strong>
                                    </div>
                                    <div class="flex justify-between text-slate-400">
                                        <span>Transfer:</span>
                                        <strong class="text-white font-semibold text-right" x-text="transfer.name">Shared 4x4</strong>
                                    </div>
                                    <div class="flex justify-between text-slate-400">
                                        <span>Sports:</span>
                                        <strong class="text-white font-semibold text-right" x-text="sports.name">Scenic Only</strong>
                                    </div>
                                    <div class="flex justify-between text-slate-400">
                                        <span>Addons:</span>
                                        <strong class="text-white font-semibold text-right" x-text="summaryAddons">None</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Display & CTAs -->
                            <div class="pt-4 border-t border-slate-800 text-center">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider mb-0.5">Live Estimated Total</span>
                                <div class="text-3xl font-black text-amber-400 font-mono my-1" x-text="'AED ' + total">AED 300</div>
                                <div class="text-emerald-400 font-semibold text-[10px] mb-4 flex items-center justify-center gap-1">
                                    <i class="bi bi-shield-check"></i> Best Price Guarantee • Free Cancel 24h
                                </div>

                                <div class="space-y-2">
                                    <button type="button" 
                                            class="w-full py-3 rounded-full bg-gradient-to-r from-[#b45309] to-[#c45e14] hover:from-[#9a4408] hover:to-[#b45309] text-white font-extrabold text-xs sm:text-sm shadow-md transition-all cursor-pointer flex items-center justify-center gap-2" 
                                            @click="book()">
                                        <i class="bi bi-calendar-check-fill"></i>
                                        <span>Book Custom Safari</span>
                                    </button>
                                    <a :href="waUrl" 
                                       target="_blank" 
                                       rel="noopener" 
                                       class="w-full py-2.5 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-xs transition-colors flex items-center justify-center gap-2">
                                        <i class="bi bi-whatsapp"></i>
                                        <span>WhatsApp Inquire</span>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
window.openSafariCustomizer = function() {
    if (window.Alpine && Alpine.store('modal')) {
        Alpine.store('modal').open('custom-safari');
    }
};
</script>
