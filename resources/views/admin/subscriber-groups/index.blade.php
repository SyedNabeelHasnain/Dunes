@extends('layouts.admin')

@section('page_title', 'Subscriber Groups & Segments')

@section('content')
<div class="space-y-6" x-data>
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi bi-diagram-3-fill text-primary"></i> Subscriber Groups & Audience Segments
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Organize your audience into targeted lists for precision email marketing and promotions.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.subscribers.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-2xs transition">
                <i class="bi bi-people"></i> View All Subscribers
            </a>
            <button type="button" @click="$dispatch('open-create-group')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition cursor-pointer">
                <i class="bi bi-plus-lg"></i> Create Group
            </button>
        </div>
    </div>

    <!-- 4 Metric Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 border border-primary/20">
                <i class="bi bi-collection text-xl"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Groups</div>
                <div class="text-2xl font-black text-slate-900">{{ $groups->count() }}</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0 border border-sky-200">
                <i class="bi bi-shield-lock text-xl"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">System Segments</div>
                <div class="text-2xl font-black text-sky-600">{{ $groups->where('is_system', true)->count() }}</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-200">
                <i class="bi bi-sliders text-xl"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Custom Segments</div>
                <div class="text-2xl font-black text-emerald-600">{{ $groups->where('is_system', false)->count() }}</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center shrink-0 border border-amber-200">
                <i class="bi bi-person-check text-xl"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Active Tagged</div>
                <div class="text-2xl font-black text-amber-500">{{ number_format($groups->sum('active_subscribers_count')) }}</div>
            </div>
        </div>
    </div>

    <!-- Groups Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700">
                <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Group Name & Slug</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Description</th>
                        <th class="py-3 px-4 text-center">Active / Total Subscribers</th>
                        <th class="py-3 px-4 text-center">Created</th>
                        <th class="py-3 px-4 text-right pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($groups as $group)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $group->is_system ? 'bg-primary/10 text-primary border border-primary/20' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                    <i class="bi {{ $group->is_system ? 'bi-shield-check' : 'bi-tags' }} text-base"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">{{ $group->name }}</div>
                                    <div class="mt-0.5">
                                        <code class="text-[11px] font-mono text-primary bg-primary/5 px-2 py-0.5 rounded-md border border-primary/15">{{ $group->slug }}</code>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            @if($group->is_system)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                    <i class="bi bi-shield-lock text-[11px]"></i> System Auto-Segment
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    <i class="bi bi-person text-[11px]"></i> Custom Group
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <div class="text-xs text-slate-500 truncate max-w-xs" title="{{ $group->description }}">
                                {{ $group->description ?: 'No description provided.' }}
                            </div>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('admin.subscribers.index', ['group_id' => $group->id]) }}" class="inline-flex items-center gap-1.5 hover:opacity-80 transition">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ number_format($group->active_subscribers_count) }} Active
                                </span>
                                <span class="text-xs text-slate-400">/ {{ number_format($group->subscribers_count) }} Total</span>
                            </a>
                        </td>
                        <td class="py-3 px-4 text-center text-xs text-slate-500">
                            {{ $group->created_at ? $group->created_at->format('M d, Y') : 'System' }}
                        </td>
                        <td class="py-3 px-4 text-right pe-4">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.subscribers.index', ['group_id' => $group->id]) }}" class="w-8 h-8 rounded-xl border border-slate-200 hover:border-primary hover:text-primary flex items-center justify-center text-slate-600 bg-white transition shadow-2xs" title="View Subscribers in this Group">
                                    <i class="bi bi-people text-xs"></i>
                                </a>
                                <button type="button" class="w-8 h-8 rounded-xl border border-slate-200 hover:border-primary hover:text-primary flex items-center justify-center text-slate-600 bg-white transition shadow-2xs edit-group-btn cursor-pointer" 
                                    data-id="{{ $group->id }}"
                                    data-name="{{ $group->name }}"
                                    data-description="{{ $group->description }}"
                                    data-is-system="{{ $group->is_system ? '1' : '0' }}"
                                    title="Edit Group">
                                    <i class="bi bi-pencil text-xs"></i>
                                </button>
                                @if(!$group->is_system)
                                    <form action="{{ route('admin.subscriber-groups.destroy', $group->id) }}" method="POST" class="inline delete-group-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="w-8 h-8 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 flex items-center justify-center bg-white transition shadow-2xs btn-delete-group cursor-pointer" data-name="{{ $group->name }}" title="Delete Group">
                                            <i class="bi bi-trash text-xs"></i>
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="w-8 h-8 rounded-xl border border-slate-200 text-slate-300 flex items-center justify-center bg-slate-50 cursor-not-allowed" disabled title="System groups cannot be deleted">
                                        <i class="bi bi-lock text-xs"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-12 text-slate-400 text-xs">
                            <i class="bi bi-diagram-3 text-4xl block mb-2 opacity-50"></i>
                            <h3 class="font-bold text-slate-700 mb-1">No Subscriber Groups Found</h3>
                            <span class="text-slate-400">Create your first audience group to segment campaigns.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Create Group (Alpine.js) -->
<div x-data="{ open: false }" @open-create-group.window="open = true" x-show="open" x-cloak class="relative z-50">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-center justify-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
                        <i class="bi bi-diagram-3-fill text-base"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">Create Audience Group</h3>
                        <div class="text-[11px] text-slate-400">Targeted email segment for marketing dispatches</div>
                    </div>
                </div>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
            </div>
            <form action="{{ route('admin.subscriber-groups.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Group Name *</label>
                    <input type="text" name="name" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" placeholder="e.g. VIP Desert Campers, Summer 2026 Leads" required>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Description</label>
                    <textarea name="description" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" rows="3" placeholder="Brief note about audience criteria or campaign purpose..."></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer">Create Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Group (Alpine.js) -->
<div x-data="{ open: false }" @open-edit-group.window="open = true" x-show="open" x-cloak class="relative z-50">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-center justify-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
                        <i class="bi bi-pencil-fill text-base"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">Edit Audience Group</h3>
                        <div class="text-[11px] text-slate-400">Update group details</div>
                    </div>
                </div>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
            </div>
            <form id="editGroupForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Group Name *</label>
                    <input type="text" name="name" id="editGroupName" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" required>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Description</label>
                    <textarea name="description" id="editGroupDescription" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" rows="3"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer">Update Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editForm = document.getElementById('editGroupForm');
    const editName = document.getElementById('editGroupName');
    const editDesc = document.getElementById('editGroupDescription');

    document.querySelectorAll('.edit-group-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const desc = this.dataset.description;

            editForm.action = `/admin/subscriber-groups/${id}`;
            editName.value = name;
            editDesc.value = desc || '';
            window.dispatchEvent(new CustomEvent('open-edit-group'));
        });
    });

    document.querySelectorAll('.btn-delete-group').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const groupName = this.dataset.name;
            const form = this.closest('form');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete Audience Group?',
                    text: `Are you sure you want to delete "${groupName}"? Subscribers in this group will not be deleted, only unlinked from this group.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm(`Are you sure you want to delete "${groupName}"?`)) {
                    form.submit();
                }
            }
        });
    });
});
</script>
@endpush
@endsection
