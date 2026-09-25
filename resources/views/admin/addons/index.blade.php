@extends('layouts.admin')

@section('page_title', 'Tour Addons')

@section('content')
<div class="space-y-6" x-data>
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Tour Addons Catalog</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage global add-on upgrades, pricing, and adoption across all desert safaris and tours.</p>
        </div>
        <button type="button" @click="$dispatch('open-add-addon')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition-all cursor-pointer">
            <i class="bi bi-plus-lg"></i> Add New Addon
        </button>
    </div>

    <!-- 3 Summary KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Addons in Catalog</span>
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <i class="bi bi-puzzle-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ number_format(count($addons)) }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Active tour enhancements</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Add-ons Booked</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="bi bi-cart-check-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-emerald-600">{{ number_format($totalAddonsBooked) }} <span class="text-xs font-semibold text-slate-400">Units</span></div>
            <span class="text-[11px] text-slate-400 mt-1 block">Across all customer reservations</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Add-on Revenue Earned</span>
                <span class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                    <i class="bi bi-cash-stack text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-primary">AED {{ number_format($totalAddonsRevenue) }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Incremental upsell revenue</span>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700 datatable" id="addonsTable">
                <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Addon Identity</th>
                        <th class="py-3 px-4">Default Price</th>
                        <th class="py-3 px-4">Tour Adoption</th>
                        <th class="py-3 px-4">Times Booked</th>
                        <th class="py-3 px-4">Revenue Earned</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Priority</th>
                        <th class="py-3 px-4 text-right no-sort pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($addons as $addon)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 border border-primary/20">
                                    <i class="bi bi-{{ $addon->icon ?: 'plus-lg' }} text-base"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-900">{{ $addon->name }}</div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-xs">{{ $addon->description }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-900">AED {{ number_format($addon->default_price, 2) }}</td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                <i class="bi bi-link-45deg"></i> {{ $addon->tours_count ?? $addon->tours->count() }} Tours
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ number_format($addon->times_booked ?? 0) }} Sold ({{ $addon->attachment_rate ?? 0 }}%)
                            </span>
                        </td>
                        <td class="py-3 px-4 font-black text-primary">
                            AED {{ number_format($addon->total_revenue ?? 0) }}
                        </td>
                        <td class="py-3 px-4">
                            @if($addon->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 capitalize">Active</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200 capitalize">Inactive</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-500">
                            {{ $addon->priority }}
                        </td>
                        <td class="py-3 px-4 text-right pe-4">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <button type="button" class="w-8 h-8 rounded-xl border border-slate-200 hover:border-primary hover:text-primary flex items-center justify-center text-slate-600 bg-white transition shadow-2xs edit-addon-btn cursor-pointer" 
                                    title="Edit Addon"
                                    data-id="{{ $addon->id }}"
                                    data-name="{{ $addon->name }}"
                                    data-slug="{{ $addon->slug }}"
                                    data-description="{{ $addon->description }}"
                                    data-icon="{{ $addon->icon }}"
                                    data-price="{{ $addon->default_price }}"
                                    data-status="{{ $addon->status }}"
                                    data-priority="{{ $addon->priority }}"
                                    data-action="{{ route('admin.addons.update', $addon->id) }}">
                                    <i class="bi bi-pencil-fill text-xs"></i>
                                </button>
                                <form action="{{ route('admin.addons.destroy', $addon->id) }}" method="POST" class="inline delete-form" data-confirm="Are you sure you want to delete this addon? It will be detached from all linked tours.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 flex items-center justify-center bg-white transition shadow-2xs" 
                                        title="Delete Addon">
                                        <i class="bi bi-trash3-fill text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-10 text-slate-400">No addons found in catalog.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add New Addon (Alpine.js) -->
<div x-data="{ open: false }" @open-add-addon.window="open = true" x-show="open" x-cloak class="relative z-50">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-center justify-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4" @click.stop>
            <form action="{{ route('admin.addons.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                        <i class="bi bi-plus-circle text-primary"></i> Add New Tour Addon
                    </h3>
                    <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
                </div>

                <div>
                    <label for="addonName" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Addon Name *</label>
                    <input type="text" name="name" id="addonName" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" placeholder="e.g. Quad Biking (30 Mins)" required>
                </div>
                <div>
                    <label for="addonSlug" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Slug (Optional - auto-generated if empty)</label>
                    <input type="text" name="slug" id="addonSlug" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" placeholder="e.g. quad-biking-30-mins">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="addonPrice" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Default Price (AED) *</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xs font-bold text-slate-400">AED</span>
                            <input type="number" step="0.01" min="0" name="default_price" id="addonPrice" class="w-full rounded-xl border border-slate-200 pl-11 pr-3 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" placeholder="150.00" required>
                        </div>
                    </div>
                    <div>
                        <label for="addonIcon" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Icon Class</label>
                        <input type="text" name="icon" id="addonIcon" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" placeholder="e.g. plus-lg, bicycle" value="plus-lg">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="addonStatus" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Status</label>
                        <select name="status" id="addonStatus" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label for="addonPriority" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Priority Order</label>
                        <input type="number" name="priority" id="addonPriority" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" value="1" required>
                    </div>
                </div>
                <div>
                    <label for="addonDesc" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Description</label>
                    <textarea name="description" id="addonDesc" rows="3" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" placeholder="Short description of this add-on upgrade..."></textarea>
                </div>
                
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition">Save Addon</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Addon (Alpine.js) -->
<div x-data="{ open: false }" @open-edit-addon.window="open = true" x-show="open" x-cloak class="relative z-50">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-center justify-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4" @click.stop>
            <form id="editAddonForm" method="POST" class="space-y-4">
                @csrf
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                        <i class="bi bi-pencil-square text-primary"></i> Edit Tour Addon
                    </h3>
                    <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
                </div>

                <div>
                    <label for="editAddonName" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Addon Name *</label>
                    <input type="text" name="name" id="editAddonName" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" required>
                </div>
                <div>
                    <label for="editAddonSlug" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Slug *</label>
                    <input type="text" name="slug" id="editAddonSlug" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="editAddonPrice" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Default Price (AED) *</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xs font-bold text-slate-400">AED</span>
                            <input type="number" step="0.01" min="0" name="default_price" id="editAddonPrice" class="w-full rounded-xl border border-slate-200 pl-11 pr-3 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" required>
                        </div>
                    </div>
                    <div>
                        <label for="editAddonIcon" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Icon Class</label>
                        <input type="text" name="icon" id="editAddonIcon" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden focus:border-primary">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="editAddonStatus" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Status</label>
                        <select name="status" id="editAddonStatus" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label for="editAddonPriority" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Priority Order</label>
                        <input type="number" name="priority" id="editAddonPriority" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" required>
                    </div>
                </div>
                <div>
                    <label for="editAddonDesc" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Description</label>
                    <textarea name="description" id="editAddonDesc" rows="3" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden focus:border-primary"></textarea>
                </div>
                
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition">Update Addon</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.edit-addon-btn').on('click', function() {
        const btn = $(this);
        $('#editAddonName').val(btn.data('name'));
        $('#editAddonSlug').val(btn.data('slug'));
        $('#editAddonPrice').val(btn.data('price'));
        $('#editAddonIcon').val(btn.data('icon'));
        $('#editAddonStatus').val(btn.data('status'));
        $('#editAddonPriority').val(btn.data('priority'));
        $('#editAddonDesc').val(btn.data('description'));
        $('#editAddonForm').attr('action', btn.data('action'));
        
        window.dispatchEvent(new CustomEvent('open-edit-addon'));
    });
});
</script>
@endpush
@endsection
