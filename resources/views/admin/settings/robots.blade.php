@extends('layouts.admin')

@section('page_title', 'Technical SEO: robots.txt & AI Crawler Manager')

@section('content')
<div class="space-y-6 mb-8" x-data="{ tab: 'builder' }">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-xs font-bold text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-xs font-bold text-rose-800">{{ $errors->first() }}</div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white shadow-xs p-6">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
            <div>
                <h5 class="text-base font-extrabold text-slate-900">CMS robots.txt &amp; AI Crawler Governance</h5>
                <p class="text-xs text-slate-500 mt-0.5">Configure search engine access, AI Search/Citation bots, and bulk AI scrapers with automated validation before publishing.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ url('/robots.txt') }}" target="_blank" class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50">
                    <i class="bi bi-box-arrow-up-right"></i> View Live /robots.txt
                </a>
                <button type="button" @click="tab = 'builder'" :class="tab === 'builder' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-700'" class="px-3.5 py-2 rounded-xl text-xs font-bold">Visual Bot Controls</button>
                <button type="button" @click="tab = 'raw'" :class="tab === 'raw' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-700'" class="px-3.5 py-2 rounded-xl text-xs font-bold">Raw Editor &amp; Preview</button>
            </div>
        </div>

        @if(!empty($validationIssues))
            <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-900">
                <div class="font-bold mb-1">Active Validation Warnings:</div>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach($validationIssues as $issue)
                        <li>{{ $issue }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form x-show="tab === 'builder'" method="POST" action="{{ route('admin.settings.robots.update') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="mode" value="builder">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($botGroups as $groupKey => $group)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 space-y-3">
                        <h6 class="text-xs font-extrabold uppercase tracking-wider text-slate-800">{{ $group['label'] }}</h6>
                        @foreach($group['bots'] as $bot)
                            <div class="flex items-center justify-between gap-2 bg-white rounded-xl border border-slate-200 px-3 py-2">
                                <span class="font-mono text-xs font-bold text-slate-800">{{ $bot }}</span>
                                <select name="bots[{{ $bot }}]" class="rounded-lg border border-slate-200 px-2 py-1 text-xs font-bold">
                                    <option value="allow" @selected(($botStates[$bot] ?? 'allow') === 'allow')>Allow /</option>
                                    <option value="disallow" @selected(($botStates[$bot] ?? 'allow') === 'disallow')>Disallow /</option>
                                </select>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Additional Custom Path Disallows (one per line)</label>
                <textarea name="custom_disallows" rows="3" placeholder="/private-staging/" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-mono"></textarea>
            </div>

            <div class="flex items-center justify-between pt-2">
                <p class="text-[11px] text-slate-500">
                    @if(!auth()->user()?->hasAnyPermission(['robots.publish', 'governance.approve', '*']))
                        Note: Changes submitted by your role will be queued for Administrator Approval before taking effect.
                    @else
                        Changes validate against GSC noindex-readability safety rules and publish directly with audit trail logging.
                    @endif
                </p>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary text-white text-xs font-bold hover:opacity-90">Save &amp; Validate robots.txt</button>
            </div>
        </form>

        <form x-show="tab === 'raw'" x-cloak method="POST" action="{{ route('admin.settings.robots.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="mode" value="raw">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Direct robots.txt Directives Preview &amp; Editor</label>
                <textarea name="robots_txt_content" rows="18" class="w-full rounded-xl border border-slate-200 bg-slate-950 text-emerald-300 font-mono text-xs p-4 leading-relaxed">{{ old('robots_txt_content', $content) }}</textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:opacity-90">Validate &amp; Apply Raw Directives</button>
            </div>
        </form>
    </div>
</div>
@endsection
