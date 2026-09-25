@extends('layouts.admin')

@section('page_title', 'Pricing Tiers')

@section('content')
<div class="space-y-6" x-data>
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Pricing Tiers Hierarchy</h1>
            <p class="text-xs text-slate-500 mt-0.5">Configure package tiers (Standard, VIP, Glamping, Premium) and their global feature descriptions.</p>
        </div>
        <button type="button" @click="$dispatch('open-add-tier')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition-all cursor-pointer">
            <i class="bi bi-plus-lg"></i> Add New Tier
        </button>
    </div>

    <!-- Tiers Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700 datatable" id="tiersTable">
                <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Tier Identity</th>
                        <th class="py-3 px-4">Internal Name</th>
                        <th class="py-3 px-4">Slug</th>
                        <th class="py-3 px-4">Tour Adoption</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Priority</th>
                        <th class="py-3 px-4 text-right no-sort pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($tiers as $tier)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 border border-primary/20">
                                    <i class="bi bi-{{ $tier->icon ?: 'star-fill' }} text-base"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                        {{ $tier->display_name }}
                                        @if($tier->is_popular)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                                                <i class="bi bi-fire text-amber-500"></i> Popular
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-xs">{{ $tier->description }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-xs font-medium text-slate-800">{{ $tier->name }}</td>
                        <td class="py-3 px-4"><code class="text-xs font-mono text-primary bg-primary/5 px-2 py-0.5 rounded-md border border-primary/15">{{ $tier->slug }}</code></td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/20">
                                <i class="bi bi-link-45deg"></i> {{ $tier->tours_count ?? $tier->tours->count() }} Tours
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            @if($tier->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 capitalize">Active</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200 capitalize">Inactive</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-500">
                            {{ $tier->priority }}
                        </td>
                        <td class="py-3 px-4 text-right pe-4">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <button type="button" class="w-8 h-8 rounded-xl border border-slate-200 hover:border-primary hover:text-primary flex items-center justify-center text-slate-600 bg-white transition shadow-2xs edit-tier-btn cursor-pointer" 
                                    title="Edit Tier"
                                    data-id="{{ $tier->id }}"
                                    data-name="{{ $tier->name }}"
                                    data-display-name="{{ $tier->display_name }}"
                                    data-slug="{{ $tier->slug }}"
                                    data-description="{{ $tier->description }}"
                                    data-icon="{{ $tier->icon }}"
                                    data-popular="{{ $tier->is_popular ? '1' : '0' }}"
                                    data-status="{{ $tier->status }}"
                                    data-priority="{{ $tier->priority }}"
                                    data-action="{{ route('admin.tiers.update', $tier->id) }}">
                                    <i class="bi bi-pencil-fill text-xs"></i>
                                </button>
                                <form action="{{ route('admin.tiers.destroy', $tier->id) }}" method="POST" class="inline delete-form" data-confirm="Are you sure you want to delete this pricing tier? It will be detached from all linked tours.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 flex items-center justify-center bg-white transition shadow-2xs" 
                                        title="Delete Tier">
                                        <i class="bi bi-trash3-fill text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-slate-400">No pricing tiers defined.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add New Tier (Alpine.js) -->
<div x-data="{ open: false }" @open-add-tier.window="open = true" x-show="open" x-cloak class="relative z-50">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-center justify-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4" @click.stop>
            <form action="{{ route('admin.tiers.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                        <i class="bi bi-plus-circle text-primary"></i> Add New Pricing Tier
                    </h3>
                    <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="tierDisplayName" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Display Name *</label>
                        <input type="text" name="display_name" id="tierDisplayName" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" placeholder="e.g. VIP Royal Dining" required>
                    </div>
                    <div>
                        <label for="tierName" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Internal Name *</label>
                        <input type="text" name="name" id="tierName" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" placeholder="e.g. VIP Royal" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="tierSlug" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Slug (Optional)</label>
                        <input type="text" name="slug" id="tierSlug" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" placeholder="e.g. vip-royal">
                    </div>
                    <div>
                        <label for="tierIcon" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Icon Class</label>
                        <input type="text" name="icon" id="tierIcon" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" placeholder="e.g. star-fill, trophy" value="star-fill">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="tierStatus" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Status</label>
                        <select name="status" id="tierStatus" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label for="tierPriority" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Priority Order</label>
                        <input type="number" name="priority" id="tierPriority" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" value="1" required>
                    </div>
                </div>

                <label class="flex items-center gap-3 cursor-pointer p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                    <input class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4" type="checkbox" name="is_popular" value="1" id="addTierPopular">
                    <span class="text-xs font-bold text-slate-800">Mark as "Popular / Best Value" Badge</span>
                </label>

                <div>
                    <label for="tierDesc" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Description</label>
                    <textarea name="description" id="tierDesc" rows="3" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" placeholder="Key inclusions or features of this tier..."></textarea>
                </div>
                
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition">Save Tier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Tier (Alpine.js) -->
<div x-data="{ open: false }" @open-edit-tier.window="open = true" x-show="open" x-cloak class="relative z-50">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-center justify-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4" @click.stop>
            <form id="editTierForm" method="POST" class="space-y-4">
                @csrf
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                        <i class="bi bi-pencil-square text-primary"></i> Edit Pricing Tier
                    </h3>
                    <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="editTierDisplayName" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Display Name *</label>
                        <input type="text" name="display_name" id="editTierDisplayName" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" required>
                    </div>
                    <div>
                        <label for="editTierName" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Internal Name *</label>
                        <input type="text" name="name" id="editTierName" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="editTierSlug" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Slug *</label>
                        <input type="text" name="slug" id="editTierSlug" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" required>
                    </div>
                    <div>
                        <label for="editTierIcon" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Icon Class</label>
                        <input type="text" name="icon" id="editTierIcon" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="editTierStatus" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Status</label>
                        <select name="status" id="editTierStatus" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label for="editTierPriority" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Priority Order</label>
                        <input type="number" name="priority" id="editTierPriority" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" required>
                    </div>
                </div>

                <label class="flex items-center gap-3 cursor-pointer p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                    <input class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4" type="checkbox" name="is_popular" value="1" id="editTierPopular">
                    <span class="text-xs font-bold text-slate-800">Mark as "Popular / Best Value" Badge</span>
                </label>

                <div>
                    <label for="editTierDesc" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Description</label>
                    <textarea name="description" id="editTierDesc" rows="3" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden focus:border-primary"></textarea>
                </div>
                
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition">Update Tier</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.edit-tier-btn').on('click', function() {
        const btn = $(this);
        $('#editTierName').val(btn.data('name'));
        $('#editTierDisplayName').val(btn.data('display-name'));
        $('#editTierSlug').val(btn.data('slug'));
        $('#editTierIcon').val(btn.data('icon'));
        $('#editTierStatus').val(btn.data('status'));
        $('#editTierPriority').val(btn.data('priority'));
        $('#editTierDesc').val(btn.data('description'));
        $('#editTierPopular').prop('checked', btn.data('popular') == '1');
        $('#editTierForm').attr('action', btn.data('action'));
        
        window.dispatchEvent(new CustomEvent('open-edit-tier'));
    });
});
</script>
@endpush
@endsection
