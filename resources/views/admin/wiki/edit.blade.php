@extends('layouts.admin')

@section('page_title', 'Edit Knowledge Article')

@section('content')
<div class="space-y-6 max-w-5xl">
    <div class="flex items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.wiki.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition">&larr; Back to Wiki Articles</a>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Edit Knowledge Article</h1>
        </div>
        <div>
            <a href="{{ localized_route('wiki.article', ['pillarSlug' => $article->pillar->slug, 'articleSlug' => $article->slug]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold shadow-2xs transition">
                <i class="bi bi-box-arrow-up-right text-slate-400"></i> View Live Guide
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.wiki.articles.update', $article->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-5">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">Article Core Details</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="pillar_id" class="block text-xs font-bold text-slate-700 mb-1">Knowledge Pillar Domain *</label>
                    <select name="pillar_id" id="pillar_id" required class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary outline-hidden">
                        @foreach($pillars as $p)
                            <option value="{{ $p->id }}" {{ (old('pillar_id', $article->pillar_id) == $p->id) ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-xs font-bold text-slate-700 mb-1">Publication Status *</label>
                    <select name="status" id="status" required class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary outline-hidden">
                        <option value="published" {{ old('status', $article->status) === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ old('status', $article->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="title" class="block text-xs font-bold text-slate-700 mb-1">Article Headline / Title *</label>
                <input type="text" name="title" id="title" required value="{{ old('title', $article->title) }}" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary outline-hidden font-bold">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="slug" class="block text-xs font-bold text-slate-700 mb-1">Custom URL Slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $article->slug) }}" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden font-mono">
                </div>
                <div>
                    <label for="reading_time_minutes" class="block text-xs font-bold text-slate-700 mb-1">Reading Time (Minutes)</label>
                    <input type="number" name="reading_time_minutes" id="reading_time_minutes" value="{{ old('reading_time_minutes', $article->reading_time_minutes) }}" min="1" max="60" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden">
                </div>
            </div>

            <div>
                <label for="subtitle" class="block text-xs font-bold text-slate-700 mb-1">Subtitle / Key Takeaway</label>
                <input type="text" name="subtitle" id="subtitle" value="{{ old('subtitle', $article->subtitle) }}" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden">
            </div>

            <div>
                <label for="excerpt" class="block text-xs font-bold text-slate-700 mb-1">Executive Summary / Excerpt *</label>
                <textarea name="excerpt" id="excerpt" rows="2" required class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden">{{ old('excerpt', $article->excerpt) }}</textarea>
            </div>

            <div>
                <label for="content" class="block text-xs font-bold text-slate-700 mb-1">Article Body (Markdown Supported) *</label>
                <div class="text-[11px] text-slate-400 mb-1.5">Use ## for major section headings, ### for sub-sections. Headings are automatically parsed into the sticky Table of Contents.</div>
                <textarea name="content" id="content" rows="18" required class="w-full rounded-xl border border-slate-200 bg-white p-3.5 text-xs text-slate-800 focus:border-primary outline-hidden font-mono leading-relaxed">{{ old('content', $article->content) }}</textarea>
            </div>
        </div>

        <!-- Marshal Verification & Conversion Widget -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-5">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">Marshal E-E-A-T Verification & Tour Conversion</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="reviewed_by_name" class="block text-xs font-bold text-slate-700 mb-1">Marshal / Lead Reviewer Name</label>
                    <input type="text" name="reviewed_by_name" id="reviewed_by_name" value="{{ old('reviewed_by_name', $article->reviewed_by_name) }}" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden">
                </div>
                <div>
                    <label for="reviewed_by_title" class="block text-xs font-bold text-slate-700 mb-1">Reviewer License & Title</label>
                    <input type="text" name="reviewed_by_title" id="reviewed_by_title" value="{{ old('reviewed_by_title', $article->reviewed_by_title) }}" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden">
                </div>
            </div>

            <div class="p-3.5 bg-emerald-50/70 border border-emerald-200 rounded-xl flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold text-emerald-950">Verify & Refresh Regulatory Date</div>
                    <div class="text-[11px] text-emerald-700">Check this box to update the last verified timestamp to right now ({{ now()->format('M d, Y') }}).</div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="verify_now" value="1" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                </label>
            </div>

            @php
                $linkedTourIds = $article->tours->pluck('id')->toArray();
            @endphp
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Contextual Tour Booking Recommendation</label>
                <div class="text-[11px] text-slate-400 mb-2">Select which tour experience should be embedded inside this article as a high-converting booking card.</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto p-2 border border-slate-200 rounded-xl bg-slate-50/50">
                    @foreach($tours as $t)
                    <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer p-1.5 rounded-lg hover:bg-white">
                        <input type="checkbox" name="tours[]" value="{{ $t->id }}" {{ in_array($t->id, $linkedTourIds) ? 'checked' : '' }} class="rounded text-primary">
                        <span class="truncate font-medium">{{ $t->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-6 pt-2">
                <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $article->is_featured) ? 'checked' : '' }} class="rounded text-primary">
                    <span>Feature on Knowledge Hub Homepage</span>
                </label>
            </div>
        </div>

        <!-- SEO Meta -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">Search Engine Optimization (SEO & Social Cards)</h2>

            <div>
                <label for="meta_title" class="block text-xs font-bold text-slate-700 mb-1">Custom Meta Title</label>
                <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $article->meta_title) }}" placeholder="Max 60 characters. Default uses article title." class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden">
            </div>

            <div>
                <label for="meta_description" class="block text-xs font-bold text-slate-700 mb-1">Custom Meta Description</label>
                <textarea name="meta_description" id="meta_description" rows="2" placeholder="Max 160 characters. Default uses excerpt." class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden">{{ old('meta_description', $article->meta_description) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
            <a href="{{ route('admin.wiki.index') }}" class="px-5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-sm transition">Update Knowledge Article</button>
        </div>
    </form>
</div>
@endsection
