@extends('layouts.admin')

@section('page_title', 'Core Web Vitals RUM & System Health Monitor')

@section('content')
<div class="space-y-6 mb-8">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-xs font-bold text-emerald-800">{{ session('success') }}</div>
    @endif

    <!-- Core Web Vitals Real-User Monitoring (75th Percentile) -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h5 class="text-base font-extrabold text-slate-900">Real-User Core Web Vitals (p75 · Last 14 Days)</h5>
                <p class="text-xs text-slate-500 mt-0.5">Live browser telemetry compared directly against Google Search Central p75 thresholds.</p>
            </div>
            <span class="rounded-full bg-slate-100 text-slate-700 px-3 py-1 text-xs font-bold">Beacon: /api/v1/rum/vitals</span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            @foreach($vitalsSummary as $metric => $data)
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black text-slate-800">{{ $metric }}</span>
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $data['status'] === 'good' ? 'bg-emerald-100 text-emerald-800' : ($data['status'] === 'poor' ? 'bg-rose-100 text-rose-800' : 'bg-slate-200 text-slate-700') }}">
                            {{ str_replace('_', ' ', $data['status']) }}
                        </span>
                    </div>
                    <div class="text-2xl font-black text-slate-900 mt-2">
                        {{ $data['p75'] !== null ? ($metric === 'CLS' ? $data['p75'] : $data['p75'].'ms') : '—' }}
                    </div>
                    <div class="text-[11px] text-slate-500 mt-1">
                        Budget <= {{ $metric === 'CLS' ? $data['good_budget'] : $data['good_budget'].'ms' }} · {{ $data['samples'] }} samples
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Poor Performing Routes & System Health Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <h6 class="text-sm font-extrabold text-slate-900">Runtime, Queue, Cron &amp; Storage Health</h6>
                @if($system['failed_jobs'] > 0)
                    <form method="POST" action="{{ route('admin.health.retry') }}">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-amber-600 text-white text-xs font-bold">Retry Failed Jobs</button>
                    </form>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                    <div class="text-slate-500">Runtime</div>
                    <div class="font-bold text-slate-900">PHP {{ $system['php_version'] }} · Laravel {{ $system['laravel_version'] }}</div>
                </div>
                <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                    <div class="text-slate-500">Cron Scheduler</div>
                    <div class="font-bold {{ $system['cron_active'] ? 'text-emerald-700' : 'text-amber-700' }}">{{ $system['cron_active'] ? 'Active' : 'Stale / Pending' }}</div>
                </div>
                <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                    <div class="text-slate-500">Queue Jobs / Failed</div>
                    <div class="font-bold text-slate-900">{{ $system['pending_jobs'] }} pending · {{ $system['failed_jobs'] }} failed</div>
                </div>
                <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                    <div class="text-slate-500">404 Errors (7d) &amp; Log Size</div>
                    <div class="font-bold text-slate-900">{{ $system['errors_404_7d'] }} 404s · {{ $system['log_size_mb'] }} MB log</div>
                </div>
                <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                    <div class="text-slate-500">Drivers (Cache / Queue / Session)</div>
                    <div class="font-bold text-slate-900">{{ $system['cache_driver'] }} / {{ $system['queue_driver'] }} / {{ $system['session_driver'] }}</div>
                </div>
                <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                    <div class="text-slate-500">Disk Free</div>
                    <div class="font-bold text-slate-900">{{ $system['disk_free_gb'] !== null ? $system['disk_free_gb'].' GB' : 'N/A' }}</div>
                </div>
            </div>

            @if(!empty($recentLogErrors))
                <div class="space-y-1.5 pt-2">
                    <div class="text-xs font-bold text-rose-700">Recent Application Log Errors</div>
                    @foreach($recentLogErrors as $errLine)
                        <div class="rounded-lg bg-rose-50 border border-rose-200 p-2 font-mono text-[10px] text-rose-900 break-all">{{ $errLine }}</div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h6 class="text-sm font-extrabold text-slate-900">Pages Exceeding Core Web Vitals Budgets</h6>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-bold">
                        <tr>
                            <th class="px-4 py-3 text-left">Path</th>
                            <th class="px-4 py-3 text-left">Metric</th>
                            <th class="px-4 py-3 text-left">Avg Poor Value</th>
                            <th class="px-4 py-3 text-right">Poor Samples</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($poorPages as $pg)
                            <tr>
                                <td class="px-4 py-2.5 font-mono text-[11px]">{{ $pg->page_path }}</td>
                                <td class="px-4 py-2.5 font-bold">{{ $pg->metric_name }}</td>
                                <td class="px-4 py-2.5 text-rose-700 font-bold">{{ round($pg->avg_value, 2) }}</td>
                                <td class="px-4 py-2.5 text-right">{{ $pg->poor_samples }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center text-slate-500">No poor Core Web Vitals budget violations logged yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
