@extends('layouts.admin')

@section('page_title', 'Manage Knowledge Pillars')

@section('content')
<div class="space-y-6 max-w-5xl">
    <div class="flex items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.wiki.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition">&larr; Back to Wiki Articles</a>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Manage Knowledge Pillars</h1>
            <p class="text-xs text-slate-500 mt-0.5">The 6 core pillar domains forming our topical authority encyclopedia.</p>
        </div>
    </div>

    <!-- Add New Pillar Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2 mb-4">Add Knowledge Pillar</h2>
        <form method="POST" action="{{ route('admin.wiki.pillars.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 mb-1">Pillar Name *</label>
                <input type="text" name="name" id="name" required placeholder="e.g. Desert Geography & Terrains" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden">
            </div>
            <div>
                <label for="slug" class="block text-xs font-bold text-slate-700 mb-1">Slug (Optional)</label>
                <input type="text" name="slug" id="slug" placeholder="e.g. geography" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden font-mono">
            </div>
            <div>
                <label for="icon" class="block text-xs font-bold text-slate-700 mb-1">Icon Class</label>
                <input type="text" name="icon" id="icon" value="bi-compass" placeholder="e.g. bi-compass, bi-geo-alt" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden">
            </div>
            <div class="md:col-span-2">
                <label for="headline" class="block text-xs font-bold text-slate-700 mb-1">Cornerstone Headline</label>
                <input type="text" name="headline" id="headline" placeholder="Explore the geological formations and high crimson dunes..." class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden">
            </div>
            <div>
                <label for="status" class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                <select name="status" id="status" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="md:col-span-3 flex justify-end">
                <button type="submit" class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold transition shadow-xs">
                    Create Pillar
                </button>
            </div>
        </form>
    </div>

    <!-- Existing Pillars List -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2 mb-4">Active Pillars</h3>
        <div class="space-y-4">
            @foreach($pillars as $p)
            <div class="p-4 rounded-xl border border-slate-200 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-lg shrink-0">
                        <i class="bi {{ $p->icon ?: 'bi-compass' }}"></i>
                    </div>
                    <div>
                        <div class="font-bold text-xs sm:text-sm text-slate-900 flex items-center gap-2">
                            {{ $p->name }}
                            <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded-full font-mono text-slate-500">/wiki/{{ $p->slug }}</span>
                        </div>
                        <div class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">{{ $p->headline }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-end">
                    <span class="text-[11px] font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-full">
                        {{ $p->articles_count }} {{ Str::plural('Guide', $p->articles_count) }}
                    </span>
                    <a href="{{ localized_route('wiki.pillar', $p->slug) }}" target="_blank" class="p-1.5 text-slate-400 hover:text-primary transition" title="View Public Pillar">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                    @if($p->articles_count === 0)
                    <form method="POST" action="{{ route('admin.wiki.pillars.delete', $p->id) }}" onsubmit="return confirm('Delete this pillar?')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition" title="Delete Pillar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
