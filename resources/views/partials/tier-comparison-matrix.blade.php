@if(isset($tour) && $tour->tiers && $tour->tiers->count() > 1)
<div class="rounded-3xl bg-white p-5 sm:p-8 mb-8 shadow-sm border border-slate-200/80" id="packageMatrixSection">
    <div class="flex justify-between items-center mb-6 flex-wrap gap-3">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-orange-50 text-primary border border-orange-200/60 uppercase tracking-wider mb-2">
                <i class="bi bi-layers"></i> Side-by-Side Comparison
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mb-1">Package Tier Feature Breakdown</h2>
            <p class="text-slate-500 text-xs sm:text-sm">Compare what is included in each package tier to pick the ideal safari experience for your party.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-50 border border-slate-200 text-xs font-bold text-slate-700">
                <i class="bi bi-shield-check text-emerald-500"></i> Instant Confirmation
            </span>
        </div>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200">
        <table class="w-full text-center text-sm border-collapse min-w-[620px]">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left py-4 px-4 text-slate-500 font-bold uppercase text-xs w-[28%] min-w-[180px]">Feature / Inclusion</th>
                    @foreach($tour->tiers->sortBy('priority') as $tier)
                    <th class="py-4 px-3 min-w-[140px] {{ $tier->is_popular ? 'bg-orange-50/60 border-x border-orange-200' : '' }}">
                        @if($tier->is_popular)
                        <span class="inline-block px-2 py-0.5 rounded-full bg-primary text-white text-[9px] font-black uppercase mb-1">MOST POPULAR</span>
                        @endif
                        <div class="font-extrabold text-slate-900 text-sm">{{ $tier->name }}</div>
                        <div class="text-lg font-black text-primary font-mono mt-0.5" data-aed="{{ $tier->pivot?->price ?? 0 }}">AED {{ number_format($tier->pivot?->price ?? 0) }}</div>
                        <small class="text-slate-500 block text-[10px] font-normal">per person</small>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-xs sm:text-sm">
                <tr>
                    <td class="text-left py-3.5 px-4 font-bold text-slate-800 flex items-center gap-2">
                        <i class="bi bi-compass text-primary"></i> Dune Bashing & Sandboarding
                    </td>
                    @foreach($tour->tiers->sortBy('priority') as $tier)
                    <td class="py-3.5 px-3 {{ $tier->is_popular ? 'bg-orange-50/30 border-x border-orange-200/60' : '' }}">
                        @if(stripos($tier->name, 'vip') !== false)
                            <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold text-xs">35-45 Min (Red Dunes)</span>
                        @elseif(stripos($tier->name, 'premium') !== false)
                            <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold text-xs">25-30 Min (High Dunes)</span>
                        @else
                            <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold text-xs">15-20 Min</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                <tr>
                    <td class="text-left py-3.5 px-4 font-bold text-slate-800 flex items-center gap-2">
                        <i class="bi bi-house-door text-primary"></i> Camp Seating & Service
                    </td>
                    @foreach($tour->tiers->sortBy('priority') as $tier)
                    <td class="py-3.5 px-3 {{ $tier->is_popular ? 'bg-orange-50/30 border-x border-orange-200/60' : '' }}">
                        @if(stripos($tier->name, 'vip') !== false)
                            <span class="font-bold text-primary flex items-center justify-center gap-1"><i class="bi bi-star-fill text-amber-400"></i> AC VIP Majlis & Waiter</span>
                        @elseif(stripos($tier->name, 'premium') !== false)
                            <span class="text-slate-800 font-semibold">Reserved Table Seating</span>
                        @else
                            <span class="text-slate-500">Standard Carpet Seating</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                <tr>
                    <td class="text-left py-3.5 px-4 font-bold text-slate-800 flex items-center gap-2">
                        <i class="bi bi-fire text-primary"></i> Live Desert Shows
                    </td>
                    @foreach($tour->tiers->sortBy('priority') as $tier)
                    <td class="py-3.5 px-3 text-emerald-600 {{ $tier->is_popular ? 'bg-orange-50/30 border-x border-orange-200/60' : '' }}">
                        <i class="bi bi-check-circle-fill text-base"></i>
                        <small class="block text-slate-500 text-[10px]">Tanoura, Fire & Belly</small>
                    </td>
                    @endforeach
                </tr>
                <tr>
                    <td class="text-left py-3.5 px-4 font-bold text-slate-800 flex items-center gap-2">
                        <i class="bi bi-egg-fried text-primary"></i> BBQ Buffet Dinner
                    </td>
                    @foreach($tour->tiers->sortBy('priority') as $tier)
                    <td class="py-3.5 px-3 {{ $tier->is_popular ? 'bg-orange-50/30 border-x border-orange-200/60' : '' }}">
                        @if(stripos($tier->name, 'vip') !== false)
                            <span class="font-bold text-slate-900">Served at Table + VIP Buffet</span>
                        @else
                            <span class="text-slate-700">Deluxe Open Buffet (Veg & Non-Veg)</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                <tr>
                    <td class="text-left py-3.5 px-4 font-bold text-slate-800 flex items-center gap-2">
                        <i class="bi bi-truck text-primary"></i> Transfer Vehicle
                    </td>
                    @foreach($tour->tiers->sortBy('priority') as $tier)
                    <td class="py-3.5 px-3 {{ $tier->is_popular ? 'bg-orange-50/30 border-x border-orange-200/60' : '' }}">
                        @if(stripos($tier->name, 'private') !== false || stripos($tier->name, 'vip') !== false)
                            <span class="px-2 py-0.5 rounded-full bg-orange-50 text-primary font-bold text-xs">Private 4x4 / Doorstep</span>
                        @else
                            <span class="text-slate-500">Shared 4x4 Land Cruiser</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                <tr>
                    <td class="text-left py-3.5 px-4 font-bold text-slate-800 flex items-center gap-2">
                        <i class="bi bi-speedometer2 text-primary"></i> Quad Biking / Buggy
                    </td>
                    @foreach($tour->tiers->sortBy('priority') as $tier)
                    <td class="py-3.5 px-3 {{ $tier->is_popular ? 'bg-orange-50/30 border-x border-orange-200/60' : '' }}">
                        @if(stripos($tier->name, 'quad') !== false || stripos($tier->name, 'buggy') !== false)
                            <span class="px-2.5 py-1 rounded-full bg-emerald-500 text-white font-bold text-xs">INCLUDED</span>
                        @else
                            <span class="text-slate-500 text-xs">Available as Add-on</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                <tr>
                    <td class="text-left py-3.5 px-4 font-bold text-slate-800 flex items-center gap-2">
                        <i class="bi bi-heart text-primary"></i> Camel Ride & Henna
                    </td>
                    @foreach($tour->tiers->sortBy('priority') as $tier)
                    <td class="py-3.5 px-3 text-emerald-600 {{ $tier->is_popular ? 'bg-orange-50/30 border-x border-orange-200/60' : '' }}">
                        <i class="bi bi-check-circle-fill text-base"></i>
                    </td>
                    @endforeach
                </tr>
            </tbody>
            <tfoot class="bg-slate-50 border-t border-slate-200">
                <tr>
                    <td class="text-left py-3.5 px-4 font-bold text-slate-500 text-xs">Select Package:</td>
                    @foreach($tour->tiers->sortBy('priority') as $tier)
                    <td class="py-3.5 px-3 {{ $tier->is_popular ? 'bg-orange-50/60 border-x border-orange-200' : '' }}">
                        <button type="button" 
                                class="w-full py-2.5 px-3 rounded-full text-xs font-bold transition-all shadow-xs cursor-pointer {{ $tier->is_popular ? 'bg-gradient-to-r from-[#b45309] to-[#c45e14] hover:from-[#9a4408] hover:to-[#b45309] text-white shadow-sm' : 'border border-primary text-primary hover:bg-primary hover:text-white' }}" 
                                data-action="open-booking" 
                                data-tour="{{ $tour->id }}" 
                                data-tier="{{ $tier->id }}">
                            Select {{ $tier->name }}
                        </button>
                    </td>
                    @endforeach
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif