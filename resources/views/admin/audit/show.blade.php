@extends('layouts.admin')

@section('page_title', 'Audit Run #' . $run->id . ' Details')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.audit.index') }}" class="text-xs font-bold text-slate-500 hover:text-primary transition flex items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Back to Audit Dashboard
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-700">Audit Run #{{ $run->id }}</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 mt-1">Audit Run Execution #{{ $run->id }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Executed on {{ $run->created_at->format('M d, Y h:i A') }} (Duration: {{ $run->duration_ms }} ms)</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.audit.export', ['id' => $run->id, 'format' => 'csv']) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs">
                <i class="bi bi-download"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Health Score & Metric Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Health Score</span>
            <div class="text-3xl font-black {{ $run->health_score >= 85 ? 'text-emerald-600' : ($run->health_score >= 70 ? 'text-amber-600' : 'text-rose-600') }} mt-1">
                {{ $run->health_score }} / 100
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Findings</span>
            <div class="text-3xl font-black text-slate-900 mt-1">{{ $run->total_issues_count }}</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Critical & High</span>
            <div class="text-3xl font-black text-rose-600 mt-1">{{ $run->critical_count + $run->high_count }}</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Scanned Entities</span>
            <div class="text-3xl font-black text-slate-900 mt-1">{{ $run->scanned_urls_count }}</div>
        </div>
    </div>

    <!-- Issues Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">All Issues Logged in Run #{{ $run->id }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-4">Severity</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Issue Title & Description</th>
                        <th class="py-3 px-4">Affected URL</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($issues as $issue)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-4 align-top whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black border uppercase {{ $issue->severity === 'critical' ? 'bg-rose-100 text-rose-800 border-rose-200' : ($issue->severity === 'high' ? 'bg-orange-100 text-orange-800 border-orange-200' : 'bg-amber-100 text-amber-800 border-amber-200') }}">
                                    {{ $issue->severity }}
                                </span>
                            </td>
                            <td class="py-3 px-4 align-top font-mono text-[11px] text-slate-500 whitespace-nowrap">{{ $issue->category }}</td>
                            <td class="py-3 px-4 align-top">
                                <div class="font-bold text-slate-900">{{ $issue->title }}</div>
                                <div class="text-[11px] text-slate-600 mt-1">{{ $issue->description }}</div>
                                @if($issue->recommended_action)
                                    <div class="mt-1.5 text-[11px] text-emerald-700 font-medium">
                                        <strong>Fix:</strong> {{ $issue->recommended_action }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4 align-top">
                                @if($issue->affected_url)
                                    <a href="{{ $issue->affected_url }}" target="_blank" class="text-primary hover:underline font-mono text-[11px] truncate block max-w-xs">
                                        {{ $issue->affected_url }}
                                    </a>
                                @else
                                    <span class="text-slate-400 font-mono text-[11px]">Global / Sitewide</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 align-top whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border capitalize {{ $issue->status === 'resolved' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-amber-100 text-amber-800 border-amber-200' }}">
                                    {{ str_replace('_', ' ', $issue->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">No issues found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($issues->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $issues->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
