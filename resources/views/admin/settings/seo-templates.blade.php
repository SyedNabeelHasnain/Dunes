@extends('layouts.admin')

@section('page_title', 'Content-Type SEO Inheritance Templates')

@section('content')
<div class="space-y-6 mb-8">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-xs font-bold text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-xs font-bold text-rose-800">{{ $errors->first() }}</div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white shadow-xs p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h5 class="text-base font-extrabold text-slate-900">Hierarchical SEO &amp; Schema Templates</h5>
                <p class="text-xs text-slate-500 mt-0.5">
                    Inheritance Chain: <strong>Global Defaults</strong> -> <strong>Content-Type Template (this screen)</strong> -> <strong>Per-Item Override</strong>.
                    Tokens supported: <code>{title}</code>, <code>{site_name}</code>, <code>{year}</code>, <code>{excerpt}</code>, <code>{category}</code>, <code>{focus_keyword}</code>.
                </p>
            </div>
            <a href="{{ route('admin.settings.seo') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50">Global SEO Settings</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5">
        @foreach($templates as $tpl)
            <div class="rounded-2xl border border-slate-200 bg-white shadow-xs p-6 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
                    <div>
                        <span class="rounded-lg bg-primary/10 px-2.5 py-1 text-[11px] font-bold text-primary mr-2">{{ $tpl->content_type }}</span>
                        <span class="text-sm font-extrabold text-slate-900">{{ $tpl->label }}</span>
                    </div>
                    <form method="POST" action="{{ route('admin.settings.seo-templates.reset', $tpl->content_type) }}" onsubmit="return confirm('Reset template to factory defaults?');">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset to Default
                        </button>
                    </form>
                </div>

                <form method="POST" action="{{ route('admin.settings.seo-templates.update', $tpl->content_type) }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Title Pattern</label>
                            <input type="text" name="title_pattern" value="{{ $tpl->title_pattern }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Default Robots Directive</label>
                            <input type="text" name="robots_default" value="{{ $tpl->robots_default }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Description Pattern</label>
                            <textarea name="description_pattern" rows="2" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-mono">{{ $tpl->description_pattern }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Keywords Pattern</label>
                            <textarea name="keywords_pattern" rows="2" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-mono">{{ $tpl->keywords_pattern }}</textarea>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 items-end">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Default OG Type</label>
                            <input type="text" name="og_type_default" value="{{ $tpl->og_type_default }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Default Schema Type</label>
                            <input type="text" name="schema_type_default" value="{{ $tpl->schema_type_default }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Sitemap Priority</label>
                            <input type="number" step="0.05" min="0.1" max="1.0" name="sitemap_priority" value="{{ $tpl->sitemap_priority }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Changefreq</label>
                            <select name="sitemap_changefreq" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                                @foreach(['always','hourly','daily','weekly','monthly','yearly','never'] as $freq)
                                    <option value="{{ $freq }}" @selected($tpl->sitemap_changefreq === $freq)>{{ $freq }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center gap-4 pb-2">
                            <label class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700">
                                <input type="hidden" name="sitemap_enabled" value="0">
                                <input type="checkbox" name="sitemap_enabled" value="1" @checked($tpl->sitemap_enabled) class="rounded border-slate-300 text-primary">
                                Sitemap
                            </label>
                            <label class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" @checked($tpl->is_active) class="rounded border-slate-300 text-primary">
                                Active
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:opacity-90">Save Template</button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endsection
