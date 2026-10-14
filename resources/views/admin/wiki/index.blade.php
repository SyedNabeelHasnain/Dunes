@extends('layouts.admin')

@section('page_title', 'Knowledge Portal & Wiki')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Dubai Desert Knowledge Portal</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    Wiki & Topical Authority
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Manage the 6 foundational knowledge pillars, cluster guides, glossary terms, and marshal verification.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ localized_route('wiki.index') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold shadow-2xs transition">
                <i class="bi bi-box-arrow-up-right text-slate-400"></i> View Live Wiki
            </a>
            <a href="{{ route('admin.wiki.pillars') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold shadow-2xs transition">
                <i class="bi bi-compass text-primary"></i> Manage Pillars ({{ $stats['total_pillars'] }})
            </a>
            <a href="{{ route('admin.wiki.glossary') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold shadow-2xs transition">
                <i class="bi bi-alphabet text-sky-600"></i> Glossary ({{ $stats['total_glossary'] }})
            </a>
            <a href="{{ route('admin.wiki.articles.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition">
                <i class="bi bi-plus-lg"></i> New Article
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Articles</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total_articles'] }}</div>
            <div class="text-[11px] text-emerald-600 font-semibold mt-0.5">{{ $stats['published_articles'] }} Published</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Knowledge Pillars</div>
            <div class="text-2xl font-black text-primary mt-1">{{ $stats['total_pillars'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Foundational Domains</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Glossary Terms</div>
            <div class="text-2xl font-black text-sky-600 mt-1">{{ $stats['total_glossary'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">A-Z Dictionary</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Reader Views</div>
            <div class="text-2xl font-black text-amber-500 mt-1">{{ number_format($stats['total_views']) }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Organic Engagement</div>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4">
        <form method="GET" action="{{ route('admin.wiki.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search articles by title or keyword..." class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-primary">
            </div>
            <div class="w-full sm:w-48">
                <select name="pillar_id" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs text-slate-700 focus:outline-none focus:border-primary" onchange="this.form.submit()">
                    <option value="">All Pillars</option>
                    @foreach($pillars as $p)
                        <option value="{{ $p->id }}" {{ request('pillar_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-36">
                <select name="status" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs text-slate-700 focus:outline-none focus:border-primary" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">Filter</button>
            @if(request()->hasAny(['search', 'pillar_id', 'status']))
                <a href="{{ route('admin.wiki.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 text-slate-600 text-xs font-bold hover:bg-slate-200 transition">Clear</a>
            @endif
        </form>
    </div>

    <!-- Articles Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700">
                <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Article</th>
                        <th class="py-3 px-4">Pillar Domain</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4">Verified By</th>
                        <th class="py-3 px-4 text-center">Views</th>
                        <th class="py-3 px-4 text-right pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($articles as $a)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ resolve_tour_image($a->hero_image) }}" alt="" class="w-12 h-12 rounded-xl object-cover border border-slate-200 shrink-0">
                                <div>
                                    <div class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                        {{ $a->title }}
                                        @if($a->is_featured)
                                            <span class="px-1.5 py-0.5 rounded-sm bg-amber-50 border border-amber-200 text-amber-700 text-[9px] font-bold">Featured</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-400 font-mono">/wiki/{{ $a->pillar->slug }}/{{ $a->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                <i class="bi {{ $a->pillar->icon ?: 'bi-compass' }}"></i>
                                {{ $a->pillar->name }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($a->status === 'published')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Published</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">Draft</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="text-xs font-semibold text-slate-800">{{ $a->reviewed_by_name }}</div>
                            <div class="text-[10px] text-slate-400">{{ $a->last_reviewed_at ? $a->last_reviewed_at->format('M d, Y') : 'Unverified' }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono text-xs text-slate-600">
                            {{ number_format($a->view_count) }}
                        </td>
                        <td class="py-3.5 px-4 text-right pe-4">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ localized_route('wiki.article', ['pillarSlug' => $a->pillar->slug, 'articleSlug' => $a->slug]) }}" target="_blank" class="p-1.5 text-slate-400 hover:text-primary transition" title="View Public Page">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                                <a href="{{ route('admin.wiki.articles.edit', $a->id) }}" class="p-1.5 text-slate-400 hover:text-amber-600 transition" title="Edit Article">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.wiki.articles.delete', $a->id) }}" onsubmit="return confirm('Are you sure you want to delete this knowledge article?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition" title="Delete Article">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-xs text-slate-400">
                            No knowledge articles found. Click "New Article" to create your first cluster guide.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($articles->hasPages())
        <div class="pt-4 border-t border-slate-100">
            {{ $articles->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
