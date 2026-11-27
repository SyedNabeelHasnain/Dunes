@extends('layouts.admin')

@section('page_title', 'Edit Knowledge Article')

@section('content')
@php
    $activeLangs = isset($languages) && $languages->count() ? $languages : \App\Models\Language::getActive();
    $defaultLangCode = $activeLangs->firstWhere('is_default', true)?->code ?? 'en';
@endphp
<div class="space-y-6 max-w-5xl" x-data="{ activeLang: '{{ $defaultLangCode }}' }">
    <div class="flex items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.wiki.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition">&larr; Back to Wiki Articles</a>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Edit Knowledge Article</h1>
        </div>
        <div>
            @if($article->pillar)
            <a href="{{ localized_route('wiki.article', ['pillarSlug' => $article->pillar->slug, 'articleSlug' => $article->slug]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold shadow-2xs transition">
                <i class="bi bi-box-arrow-up-right text-slate-400"></i> View Live Guide
            </a>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route('admin.wiki.articles.update', $article->id) }}" class="space-y-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="tours_submitted" value="1">

        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Article Core Details</h2>
                @if($activeLangs->count() > 1)
                <div class="inline-flex flex-wrap items-center gap-1 bg-slate-100 p-1 rounded-xl">
                    @foreach($activeLangs as $lang)
                        <button type="button" @click="activeLang = '{{ $lang->code }}'" :class="activeLang === '{{ $lang->code }}' ? 'bg-white text-primary shadow-2xs' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition cursor-pointer">
                            {{ strtoupper($lang->code) }}
                        </button>
                    @endforeach
                </div>
                @endif
            </div>

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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="slug" class="block text-xs font-bold text-slate-700 mb-1">Custom URL Slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $article->slug) }}" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden font-mono">
                </div>
                <div>
                    <label for="reading_time_minutes" class="block text-xs font-bold text-slate-700 mb-1">Reading Time (Minutes)</label>
                    <input type="number" name="reading_time_minutes" id="reading_time_minutes" value="{{ old('reading_time_minutes', $article->reading_time_minutes) }}" min="1" max="60" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden">
                </div>
                <div>
                    <label for="hero_image" class="block text-xs font-bold text-slate-700 mb-1">Hero Image Path / URL</label>
                    <input type="text" name="hero_image" id="hero_image" value="{{ old('hero_image', $article->hero_image) }}" placeholder="/images/..." class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden">
                </div>
            </div>

            @foreach($activeLangs as $lang)
            @php $lc = $lang->code; $isRtl = $lang->isRtl(); @endphp
            <div x-show="activeLang === '{{ $lc }}'" @if($lc !== $defaultLangCode) x-cloak @endif class="space-y-4" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
                <div>
                    <label for="title_{{ $lc }}" class="block text-xs font-bold text-slate-700 mb-1">Article Headline / Title ({{ strtoupper($lc) }}) {{ $lc === 'en' ? '*' : '' }}</label>
                    <input type="text" name="title[{{ $lc }}]" id="title_{{ $lc }}" {{ $lc === 'en' ? 'required' : '' }} value="{{ old('title.'.$lc, $article->getTranslation('title', $lc, false)) }}" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary outline-hidden font-bold">
                </div>

                <div>
                    <label for="subtitle_{{ $lc }}" class="block text-xs font-bold text-slate-700 mb-1">Subtitle / Key Takeaway ({{ strtoupper($lc) }})</label>
                    <input type="text" name="subtitle[{{ $lc }}]" id="subtitle_{{ $lc }}" value="{{ old('subtitle.'.$lc, $article->getTranslation('subtitle', $lc, false)) }}" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden">
                </div>

                <div>
                    <label for="excerpt_{{ $lc }}" class="block text-xs font-bold text-slate-700 mb-1">Executive Summary / Excerpt ({{ strtoupper($lc) }}) {{ $lc === 'en' ? '*' : '' }}</label>
                    <textarea name="excerpt[{{ $lc }}]" id="excerpt_{{ $lc }}" rows="2" {{ $lc === 'en' ? 'required' : '' }} class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden">{{ old('excerpt.'.$lc, $article->getTranslation('excerpt', $lc, false)) }}</textarea>
                </div>

                <div>
                    <label for="content_{{ $lc }}" class="block text-xs font-bold text-slate-700 mb-1">Article Body (Markdown Supported — {{ strtoupper($lc) }}) {{ $lc === 'en' ? '*' : '' }}</label>
                    <div class="text-[11px] text-slate-400 mb-1.5" dir="ltr">Use ## for major section headings, ### for sub-sections. Headings are automatically parsed into the sticky Table of Contents.</div>
                    <textarea name="content[{{ $lc }}]" id="content_{{ $lc }}" rows="18" {{ $lc === 'en' ? 'required' : '' }} class="w-full rounded-xl border border-slate-200 bg-white p-3.5 text-xs text-slate-800 focus:border-primary outline-hidden font-mono leading-relaxed">{{ old('content.'.$lc, $article->getTranslation('content', $lc, false)) }}</textarea>
                </div>
            </div>
            @endforeach
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
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">Search Engine Optimization (SEO &amp; Social Cards)</h2>

            @foreach($activeLangs as $lang)
            @php $lc = $lang->code; $isRtl = $lang->isRtl(); @endphp
            <div x-show="activeLang === '{{ $lc }}'" @if($lc !== $defaultLangCode) x-cloak @endif class="space-y-4" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
                <div>
                    <label for="meta_title_{{ $lc }}" class="block text-xs font-bold text-slate-700 mb-1">Custom Meta Title ({{ strtoupper($lc) }})</label>
                    <input type="text" name="meta_title[{{ $lc }}]" id="meta_title_{{ $lc }}" value="{{ old('meta_title.'.$lc, $article->getTranslation('meta_title', $lc, false)) }}" placeholder="Max 60 characters. Default uses article title." class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden">
                </div>

                <div>
                    <label for="meta_description_{{ $lc }}" class="block text-xs font-bold text-slate-700 mb-1">Custom Meta Description ({{ strtoupper($lc) }})</label>
                    <textarea name="meta_description[{{ $lc }}]" id="meta_description_{{ $lc }}" rows="2" placeholder="Max 160 characters. Default uses excerpt." class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden">{{ old('meta_description.'.$lc, $article->getTranslation('meta_description', $lc, false)) }}</textarea>
                </div>
            </div>
            @endforeach
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
            <a href="{{ route('admin.wiki.index') }}" class="px-5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-sm transition">Update Knowledge Article</button>
        </div>
    </form>
</div>

@include('admin.partials.seo-editorial-panel', ['entity' => $article, 'entityType' => 'wiki_article'])
@endsection
