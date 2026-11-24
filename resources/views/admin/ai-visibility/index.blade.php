@extends('layouts.admin')

@section('page_title', 'AI Search Visibility (GEO / AEO / LLMO) & Bot Monitor')

@section('content')
<div class="space-y-6 mb-8">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-xs font-bold text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h5 class="text-base font-extrabold text-slate-900">AI Search Bot Crawls &amp; Referral Traffic (Last 30 Days)</h5>
                <p class="text-xs text-slate-500 mt-0.5">Measured strictly from observed server request logs. External AI engine citation visibility cannot be guaranteed or fabricated.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ url('/llms.txt') }}" target="_blank" class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50">/llms.txt</a>
                <a href="{{ url('/llms-full.txt') }}" target="_blank" class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50">/llms-full.txt</a>
                <a href="{{ route('admin.settings.robots.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold">Manage Bot Rules</a>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
            @foreach($botHits as $bot => $hits)
                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3">
                    <div class="font-mono text-[11px] font-bold text-slate-700 truncate">{{ $bot }}</div>
                    <div class="text-xl font-black text-slate-900 mt-1">{{ number_format($hits) }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs space-y-3">
            <h6 class="text-sm font-extrabold text-slate-900">Record Manual AI Citation Benchmark</h6>
            <form method="POST" action="{{ route('admin.ai-visibility.observations.store') }}" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">AI Platform</label>
                    <select name="platform" class="w-full rounded-xl border border-slate-200 px-3 py-2">
                        <option value="chatgpt">ChatGPT Search</option>
                        <option value="perplexity">Perplexity</option>
                        <option value="google_ai_overview">Google AI Overview</option>
                        <option value="claude">Claude</option>
                        <option value="gemini">Google Gemini</option>
                        <option value="copilot">Microsoft Copilot</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Prompt Tested</label>
                    <input type="text" name="prompt_query" required placeholder="e.g. best licensed desert safari operator in dubai" class="w-full rounded-xl border border-slate-200 px-3 py-2">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Cited URL (optional)</label>
                    <input type="text" name="cited_url" placeholder="https://dunesdiscoverytourism.com/..." class="w-full rounded-xl border border-slate-200 px-3 py-2">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Sentiment</label>
                        <select name="sentiment" class="w-full rounded-xl border border-slate-200 px-3 py-2">
                            <option value="positive">Positive</option>
                            <option value="neutral">Neutral</option>
                            <option value="negative">Negative</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Position</label>
                        <input type="number" name="citation_position" min="1" max="20" class="w-full rounded-xl border border-slate-200 px-3 py-2">
                    </div>
                </div>
                <label class="inline-flex items-center gap-2 font-bold text-slate-700">
                    <input type="hidden" name="brand_mentioned" value="0">
                    <input type="checkbox" name="brand_mentioned" value="1" checked class="rounded border-slate-300 text-primary">
                    Dunes Discovery Brand Cited
                </label>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Verification Notes</label>
                    <textarea name="notes" rows="2" class="w-full rounded-xl border border-slate-200 px-3 py-2"></textarea>
                </div>
                <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-primary text-white font-bold hover:opacity-90">Save Observation</button>
            </form>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h6 class="text-sm font-extrabold text-slate-900">Verified AI Referral Traffic (from Request Logs)</h6>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-bold">
                            <tr><th class="px-4 py-3 text-left">Referrer URL</th><th class="px-4 py-3 text-right">Visits (30d)</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($aiReferrals as $ref)
                                <tr><td class="px-4 py-2.5 font-mono text-[11px]">{{ $ref->referrer_url }}</td><td class="px-4 py-2.5 text-right font-bold">{{ $ref->visits }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="px-6 py-6 text-center text-slate-500">No external AI platform referral hits recorded in the last 30 days.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h6 class="text-sm font-extrabold text-slate-900">Observed Manual Prompt Citation Log</h6>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($observations as $obs)
                        <div class="p-4 flex items-center justify-between gap-3 text-xs">
                            <div>
                                <span class="rounded bg-slate-100 px-2 py-0.5 font-bold uppercase text-slate-700 mr-2">{{ $obs->platform }}</span>
                                <span class="font-bold text-slate-900">{{ $obs->prompt_query }}</span>
                                <div class="text-slate-500 mt-0.5">{{ $obs->cited_url ?: 'Mention only' }} · {{ $obs->brand_mentioned ? 'Cited' : 'Absent' }} · {{ optional($obs->observed_at)->format('Y-m-d') }}</div>
                            </div>
                            <form method="POST" action="{{ route('admin.ai-visibility.observations.destroy', $obs->id) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-rose-600"><i class="bi bi-trash3"></i></button>
                            </form>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-500">No manual AI citation observations logged yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
