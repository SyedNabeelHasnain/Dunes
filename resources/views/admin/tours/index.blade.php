@extends('layouts.admin')

@section('page_title', 'Tours Inventory')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Tours & Experiences Inventory</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage desert safari packages, activity durations, best-seller highlights, and live visibility.</p>
        </div>
        <a href="{{ route('admin.tours.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition-all">
            <i class="bi bi-plus-lg"></i> Add New Tour
        </a>
    </div>

    <!-- Tours Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700 datatable" id="toursTable">
                <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Tour Name & Slug</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Duration</th>
                        <th class="py-3 px-4 text-center">Status (Click to toggle)</th>
                        <th class="py-3 px-4 text-right no-sort pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($tours as $t)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4">
                            <div class="text-xs font-bold text-slate-900">{{ $t->name }}</div>
                            <div class="text-[11px] text-slate-400 font-mono mt-0.5">/{{ $t->slug }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200 uppercase">
                                {{ $t->category ? str_replace('-', ' ', $t->category->name) : 'General' }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="text-xs font-medium text-slate-700 flex items-center gap-1.5">
                                <i class="bi bi-clock text-slate-400"></i> {{ $t->duration ?: 'Flexible' }}
                            </div>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="inline-flex items-center justify-center gap-2">
                                @if($t->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 cursor-pointer hover:scale-105 transition-transform ajax-toggle-status" data-url="{{ route('admin.tours.toggle-status', $t->id) }}" title="Click to toggle status">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200 cursor-pointer hover:scale-105 transition-transform ajax-toggle-status" data-url="{{ route('admin.tours.toggle-status', $t->id) }}" title="Click to toggle status">Hidden</span>
                                @endif
                                
                                @if($t->is_bestseller)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-200" title="Featured Best Seller">
                                        <i class="bi bi-fire text-amber-500"></i> Best
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3 px-4 text-right pe-4">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.tours.edit', $t->id) }}" class="w-8 h-8 rounded-xl border border-slate-200 hover:border-primary hover:text-primary flex items-center justify-center text-slate-600 bg-white transition shadow-2xs" title="Edit Tour Details">
                                    <i class="bi bi-pencil-fill text-xs"></i>
                                </a>
                                <a href="{{ route('tours.show', $t->slug) }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-xl border border-sky-200 text-sky-600 hover:bg-sky-50 flex items-center justify-center bg-white transition shadow-2xs" title="Live Preview">
                                    <i class="bi bi-box-arrow-up-right text-xs"></i>
                                </a>
                                <form action="{{ route('admin.tours.destroy', $t->id) }}" method="POST" class="inline delete-form" data-confirm="Are you sure you want to delete this tour and all its associations?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 flex items-center justify-center bg-white transition shadow-2xs" title="Delete Tour">
                                        <i class="bi bi-trash3-fill text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-10 text-slate-400">No tours found in inventory.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
