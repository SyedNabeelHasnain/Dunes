@extends('layouts.admin')

@section('page_title', 'Keyword Strategy, Cannibalization & Internal Linking')

@section('content')
<div class="space-y-6 mb-8">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-xs p-6">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
            <div>
                <h5 class="text-base font-extrabold text-slate-900">Keyword Mapping &amp; Cannibalization Intelligence</h5>
                <p class="text-xs text-slate-500 mt-0.5">Verify unique focus keywords per landing URL, detect competing target topics, and discover internal linking opportunities.</p>
            </div>
            <div class="flex gap-3 text-xs">
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-3.5 py-2 font-bold text-emerald-800">{{ $mapped_count }} Mapped URLs</div>
                <div class="rounded-xl bg-amber-50 border border-amber-200 px-3.5 py-2 font-bold text-amber-800">{{ $unmapped_count }} Unmapped URLs</div>
                <div class="rounded-xl {{ count($cannibalization) > 0 ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-slate-50 border-slate-200 text-slate-700' }} border px-3.5 py-2 font-bold">{{ count($cannibalization) }} Cannibalization Alerts</div>
            </div>
        </div>

        @if(!empty($cannibalization))
            <div class="space-y-3 mb-6">
                <h6 class="text-xs font-extrabold uppercase tracking-wider text-rose-700">Detected Keyword Cannibalization Conflicts</h6>
                @foreach($cannibalization as $conflict)
                    <div class="rounded-xl border border-rose-200 bg-rose-50/60 p-4 text-xs">
                        <div class="font-extrabold text-rose-900">Keyword: "{{ $conflict['keyword'] }}" ({{ $conflict['count'] }} competing URLs)</div>
                        <p class="text-rose-800 mt-0.5">{{ $conflict['recommendation'] }}</p>
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach($conflict['items'] as $item)
                                <a href="{{ $item['edit_route'] }}" class="rounded-lg bg-white border border-rose-200 px-2.5 py-1 font-mono text-[11px] text-rose-900 hover:bg-rose-100">
                                    [{{ $item['type_label'] }}] {{ $item['path'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Internal Link Opportunities -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h6 class="text-sm font-extrabold text-slate-900">Automated Internal Linking Suggestions</h6>
            <p class="text-xs text-slate-500">Editorial articles mentioning target tour keywords without an active internal link.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold">
                    <tr>
                        <th class="px-4 py-3 text-left">Source Article</th>
                        <th class="px-4 py-3 text-left">Matched Anchor Text</th>
                        <th class="px-4 py-3 text-left">Suggested Target Tour</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($link_suggestions as $sug)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $sug['source_title'] }} <span class="text-slate-400 font-mono text-[11px]">({{ $sug['source_url'] }})</span></td>
                            <td class="px-4 py-3 font-mono text-primary">{{ $sug['anchor_text'] }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-700">{{ $sug['target_title'] }} <span class="text-slate-400 font-mono text-[11px]">({{ $sug['target_url'] }})</span></td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ $sug['edit_url'] }}" class="px-3 py-1 rounded-lg bg-slate-900 text-white text-[11px] font-bold">Edit Article</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-6 text-center text-slate-500">No missing internal anchor suggestions detected right now.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Full Keyword Inventory Matrix -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h6 class="text-sm font-extrabold text-slate-900">Complete URL Keyword &amp; Intent Map</h6>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold">
                    <tr>
                        <th class="px-4 py-3 text-left">Type / Path</th>
                        <th class="px-4 py-3 text-left">Title</th>
                        <th class="px-4 py-3 text-left">Focus Keyword</th>
                        <th class="px-4 py-3 text-left">Intent / Words</th>
                        <th class="px-4 py-3 text-right">Edit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($entries as $entry)
                        <tr>
                            <td class="px-4 py-2.5">
                                <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-700">{{ $entry['type_label'] }}</span>
                                <div class="font-mono text-[11px] text-slate-600 mt-0.5">{{ $entry['path'] }}</div>
                            </td>
                            <td class="px-4 py-2.5 font-semibold text-slate-900 max-w-xs truncate">{{ $entry['title'] }}</td>
                            <td class="px-4 py-2.5">
                                @if($entry['focus_keyword'] !== '')
                                    <span class="rounded-lg bg-primary/10 px-2.5 py-0.5 font-bold text-primary">{{ $entry['focus_keyword'] }}</span>
                                @else
                                    <span class="text-amber-600 font-semibold">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-slate-500">{{ ucfirst($entry['search_intent'] ?? 'n/a') }} · {{ $entry['word_count'] }} words</td>
                            <td class="px-4 py-2.5 text-right">
                                <a href="{{ $entry['edit_route'] }}" class="text-primary font-bold hover:underline">Configure</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
