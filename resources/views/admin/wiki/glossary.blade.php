@extends('layouts.admin')

@section('page_title', 'Manage Glossary Terms')

@section('content')
<div class="space-y-6 max-w-5xl">
    <div class="flex items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.wiki.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition">&larr; Back to Wiki Articles</a>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Manage Glossary Dictionary</h1>
            <p class="text-xs text-slate-500 mt-0.5">Define entity terms for instant tooltips and the public A-Z terminology index.</p>
        </div>
        <div>
            <a href="{{ localized_route('wiki.glossary') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold shadow-2xs transition">
                <i class="bi bi-box-arrow-up-right text-slate-400"></i> View Public Glossary
            </a>
        </div>
    </div>

    <!-- Add New Term Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2 mb-4">Add New Term</h2>
        <form method="POST" action="{{ route('admin.wiki.glossary.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label for="term" class="block text-xs font-bold text-slate-700 mb-1">Term Display Name *</label>
                <input type="text" name="term" id="term" required placeholder="e.g. Lahbab Red Dunes" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden">
            </div>
            <div>
                <label for="term_key" class="block text-xs font-bold text-slate-700 mb-1">Term Key / Code (Optional)</label>
                <input type="text" name="term_key" id="term_key" placeholder="e.g. lahbab-red-dunes" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden font-mono">
            </div>
            <div>
                <label for="wiki_article_id" class="block text-xs font-bold text-slate-700 mb-1">Linked Deep-Dive Guide (Optional)</label>
                <select name="wiki_article_id" id="wiki_article_id" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden">
                    <option value="">None / Standalone Definition</option>
                    @foreach($articles as $a)
                        <option value="{{ $a->id }}">{{ $a->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-3">
                <label for="definition" class="block text-xs font-bold text-slate-700 mb-1">Definition *</label>
                <textarea name="definition" id="definition" rows="2" required placeholder="Concise 1-2 sentence encyclopedic definition..." class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden"></textarea>
            </div>
            <div class="md:col-span-3 flex justify-end">
                <button type="submit" class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold transition shadow-xs">
                    Save Term
                </button>
            </div>
        </form>
    </div>

    <!-- Glossary Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2 mb-4">Existing Terms ({{ $terms->total() }})</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700">
                <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Term</th>
                        <th class="py-3 px-4">Definition</th>
                        <th class="py-3 px-4">Linked Guide</th>
                        <th class="py-3 px-4 text-right pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($terms as $term)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4 font-bold text-xs text-slate-900 whitespace-nowrap">
                            {{ $term->term }}
                            <div class="text-[10px] text-slate-400 font-mono font-normal">{{ $term->term_key }}</div>
                        </td>
                        <td class="py-3 px-4 text-xs text-slate-600 max-w-md">
                            {{ $term->definition }}
                        </td>
                        <td class="py-3 px-4 text-xs whitespace-nowrap">
                            @if($term->article)
                                <a href="{{ localized_route('wiki.article', ['pillarSlug' => $term->article->pillar->slug, 'articleSlug' => $term->article->slug]) }}" target="_blank" class="text-primary hover:underline font-medium">
                                    {{ Str::limit($term->article->title, 35) }}
                                </a>
                            @else
                                <span class="text-slate-400 italic">None</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right pe-4 whitespace-nowrap">
                            <form method="POST" action="{{ route('admin.wiki.glossary.delete', $term->id) }}" onsubmit="return confirm('Delete this glossary term?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition" title="Delete Term">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-xs text-slate-400">
                            No glossary terms found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($terms->hasPages())
        <div class="pt-4 border-t border-slate-100">
            {{ $terms->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
