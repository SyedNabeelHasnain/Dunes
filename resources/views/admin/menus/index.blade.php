@extends('layouts.admin')

@section('page_title', 'Navigation & Footer Menus Manager')

@section('content')
<div class="space-y-6" x-data="{ addModal: false, editModal: false, activeItem: null }">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Navigation & Footer Menus CMS</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage live navbar links, footer columns, order, and multilingual labels across all 5 languages.</p>
        </div>
        <button type="button" @click="addModal = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer">
            <i class="bi bi-plus-lg"></i> Add Menu Link
        </button>
    </div>

    <!-- Location Tabs -->
    <div class="flex items-center gap-2 p-1.5 bg-white border border-slate-200/80 rounded-2xl overflow-x-auto shadow-2xs">
        @foreach($validLocations as $locKey => $locName)
        <a href="{{ route('admin.menus.index', ['location' => $locKey]) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $location === $locKey ? 'bg-primary text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
            {{ $locName }}
        </a>
        @endforeach
    </div>

    <!-- Menu Items Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700">
                <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Order</th>
                        <th class="py-3 px-4">Label (English)</th>
                        <th class="py-3 px-4">Target URL / Route</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($items as $item)
                    @php
                        $labels = $item->getTranslations('label');
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4 font-mono text-xs text-slate-500">
                            #{{ $item->order }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900 text-xs">{{ $labels['en'] ?? $item->label }}</div>
                            @if(isset($labels['ar']))
                            <div class="text-[10px] text-slate-400 mt-0.5" dir="rtl">{{ $labels['ar'] }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-mono font-semibold bg-slate-100 text-slate-700">
                                {{ $item->url }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($item->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">Inactive</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right pe-4">
                            <div class="inline-flex items-center gap-2">
                                <button type="button" @click="activeItem = {
                                    id: {{ $item->id }},
                                    url: {{ json_encode($item->url) }},
                                    order: {{ (int)$item->order }},
                                    target: {{ json_encode($item->target ?? '_self') }},
                                    is_active: {{ $item->is_active ? 'true' : 'false' }},
                                    labels: {{ json_encode($labels) }}
                                }; editModal = true" class="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-bold transition" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('admin.menus.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Delete this menu link?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-rose-50 text-rose-500 text-xs transition" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                            No menu links found for this location. Click "Add Menu Link" to create one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Link Modal -->
    <div x-show="addModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs" style="display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto" @click.outside="addModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-black text-slate-900">Add New Menu Link</h3>
                <button type="button" @click="addModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>
            <form action="{{ route('admin.menus.store') }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="location" value="{{ $location }}">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Target URL *</label>
                        <input type="text" name="url" placeholder="/tours or https://..." class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Display Order</label>
                        <input type="number" name="order" value="{{ count($items) + 1 }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800">
                    </div>
                </div>

                <div class="space-y-2 bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Link Labels (Multilingual)</span>
                    @foreach($languages as $lang)
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-0.5">
                            {{ $lang->flag }} {{ $lang->name }} ({{ strtoupper($lang->code) }})
                        </label>
                        <input type="text" name="label[{{ $lang->code }}]" placeholder="Label in {{ $lang->name }}" class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs bg-white text-slate-800" dir="{{ $lang->direction }}" {{ $lang->code === 'en' ? 'required' : '' }}>
                    </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="addModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 transition">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition">Create Link</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Link Modal -->
    <div x-show="editModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs" style="display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto" @click.outside="editModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-black text-slate-900">Edit Menu Link</h3>
                <button type="button" @click="editModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>
            <form :action="'{{ url('/admin/menus') }}/' + (activeItem ? activeItem.id : '')" method="POST" class="space-y-3">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Target URL *</label>
                        <input type="text" name="url" :value="activeItem ? activeItem.url : ''" placeholder="/tours or https://..." class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Display Order</label>
                        <input type="number" name="order" :value="activeItem ? activeItem.order : 0" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Target Window</label>
                        <select name="target" :value="activeItem ? activeItem.target : '_self'" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 bg-white">
                            <option value="_self">Same Window (_self)</option>
                            <option value="_blank">New Tab (_blank)</option>
                        </select>
                    </div>
                    <div class="flex items-center pt-5">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" :checked="activeItem && activeItem.is_active" class="rounded border-slate-300 text-primary focus:ring-primary">
                            <span class="text-xs font-bold text-slate-700">Link is Active</span>
                        </label>
                    </div>
                </div>

                <div class="space-y-2 bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Link Labels (Multilingual)</span>
                    @foreach($languages as $lang)
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-0.5">
                            {{ $lang->flag }} {{ $lang->name }} ({{ strtoupper($lang->code) }})
                        </label>
                        <input type="text" name="label[{{ $lang->code }}]" :value="activeItem && activeItem.labels ? (activeItem.labels['{{ $lang->code }}'] || '') : ''" placeholder="Label in {{ $lang->name }}" class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs bg-white text-slate-800" dir="{{ $lang->direction }}" {{ $lang->code === 'en' ? 'required' : '' }}>
                    </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 transition">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
