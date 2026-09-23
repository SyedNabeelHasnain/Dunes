@extends('layouts.admin')

@section('page_title', 'Pricing Manager')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Pricing Matrix Manager</h1>
            <p class="text-xs text-slate-500 mt-0.5">Bulk update rates across all active tours and package tiers in one unified matrix.</p>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-black text-slate-900">Bulk Pricing Matrix</h2>
            <span class="text-xs text-slate-400">All prices in AED</span>
        </div>
        
        <div class="p-6">
            <form action="{{ route('admin.pricing.update') }}" method="POST">
                @csrf

                <div class="overflow-x-auto rounded-xl border border-slate-200 mb-6">
                    <table class="w-full text-center text-sm border-collapse no-datatable" style="min-width: 900px;">
                        <thead class="bg-slate-50/80 text-xs font-bold text-slate-600 uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="text-left py-3 px-4 w-64 border-r border-slate-200">Tour / Activity Name</th>
                                @foreach($tiers as $tier)
                                    <th class="py-3 px-3 border-r border-slate-200 last:border-r-0">
                                        <div class="font-bold text-slate-900 text-xs">{{ $tier->display_name }}</div>
                                        <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-mono text-slate-400 bg-white border border-slate-200">{{ $tier->slug }}</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach($tours as $tour)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="text-left py-3 px-4 border-r border-slate-200">
                                    <div class="font-bold text-xs text-slate-900">{{ $tour->name }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">{{ $tour->category ? $tour->category->name : '' }}</div>
                                </td>
                                @foreach($tiers as $tier)
                                    @php
                                        $pivot = $tour->tiers->firstWhere('id', $tier->id)?->pivot;
                                    @endphp
                                    <td class="p-2 border-r border-slate-200 last:border-r-0 {{ $pivot ? 'bg-emerald-50/20' : 'bg-slate-50/30' }}">
                                        <div class="space-y-1.5 max-w-[160px] mx-auto">
                                            <!-- Active Price -->
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none text-[10px] font-bold text-slate-400">AED</span>
                                                <input type="number" name="pricing[{{ $tour->id }}][{{ $tier->id }}][price]" step="0.01" class="w-full rounded-lg border border-slate-200 bg-white pl-9 pr-2 py-1 text-xs font-bold text-slate-900 text-center focus:border-primary outline-hidden" value="{{ $pivot ? $pivot->price : '' }}" placeholder="Price">
                                            </div>
                                            
                                            <!-- Old/Strike Price -->
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none text-[10px] text-slate-400">Old</span>
                                                <input type="number" name="pricing[{{ $tour->id }}][{{ $tier->id }}][old_price]" step="0.01" class="w-full rounded-lg border border-slate-200 bg-white pl-9 pr-2 py-1 text-xs text-slate-500 text-center focus:border-primary outline-hidden" value="{{ $pivot ? $pivot->old_price : '' }}" placeholder="Old">
                                            </div>

                                            <!-- Price Type -->
                                            <select name="pricing[{{ $tour->id }}][{{ $tier->id }}][price_type]" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1 text-[11px] text-slate-700 focus:border-primary outline-hidden">
                                                <option value="per person" {{ $pivot && $pivot->price_type === 'per person' ? 'selected' : '' }}>Per Person</option>
                                                <option value="per group" {{ $pivot && $pivot->price_type === 'per group' ? 'selected' : '' }}>Per Group</option>
                                                <option value="per car" {{ $pivot && $pivot->price_type === 'per car' ? 'selected' : '' }}>Per Car</option>
                                            </select>
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Info Alert -->
                <div class="rounded-xl bg-sky-50 border border-sky-100 p-4 flex items-start gap-3 mb-6">
                    <i class="bi bi-info-circle-fill text-sky-600 text-base shrink-0 mt-0.5"></i>
                    <div class="text-xs text-sky-900 leading-relaxed">
                        <strong>Note:</strong> Pricing columns with empty price values will not be updated or attached. To deactivate a tier package for a tour, please manage that directly from the individual tour's edit view.
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition-all">
                        <i class="bi bi-check-lg"></i> Update Inventory Pricing Matrix
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
