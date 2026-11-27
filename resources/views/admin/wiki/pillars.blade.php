@extends('layouts.admin')

@section('page_title', 'Manage Knowledge Pillars')

@section('content')
<div class="space-y-6 max-w-5xl" x-data="{
    editOpen: false,
    editing: { id: null, name: '', slug: '', headline: '', description: '', icon: 'bi-compass', priority: 0, status: 'active' },
    openEdit(p) {
        this.editing = { ...p };
        this.editOpen = true;
    }
}">
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
                    @php
                        $pillarPayload = [
                            'id' => $p->id,
                            'name' => $p->getTranslation('name', 'en', true) ?: $p->name,
                            'slug' => $p->slug,
                            'headline' => $p->getTranslation('headline', 'en', false) ?? '',
                            'description' => $p->getTranslation('description', 'en', false) ?? '',
                            'icon' => $p->icon ?: 'bi-compass',
                            'priority' => (int) $p->priority,
                            'status' => $p->status ?: 'active',
                        ];
                    @endphp
                    <button type="button" @click='openEdit(@json($pillarPayload))' class="p-1.5 text-slate-400 hover:text-amber-600 transition cursor-pointer" title="Edit Pillar">
                        <i class="bi bi-pencil-square"></i>
                    </button>
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

    <!-- Edit Pillar Modal -->
    <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div @click.outside="editOpen = false" class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-black text-slate-900">Edit Knowledge Pillar</h3>
                <button type="button" @click="editOpen = false" class="text-slate-400 hover:text-slate-700 cursor-pointer"><i class="bi bi-x-lg"></i></button>
            </div>
            <form :action="`{{ url('/admin/wiki/pillars') }}/${editing.id}`" method="POST" class="space-y-3">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pillar Name *</label>
                        <input type="text" name="name" x-model="editing.name" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Slug</label>
                        <input type="text" name="slug" x-model="editing.slug" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-mono">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Cornerstone Headline</label>
                    <input type="text" name="headline" x-model="editing.headline" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Description</label>
                    <textarea name="description" x-model="editing.description" rows="2" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs"></textarea>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Icon Class</label>
                        <input type="text" name="icon" x-model="editing.icon" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Priority</label>
                        <input type="number" name="priority" x-model="editing.priority" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                        <select name="status" x-model="editing.status" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="editOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 cursor-pointer">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold cursor-pointer">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
