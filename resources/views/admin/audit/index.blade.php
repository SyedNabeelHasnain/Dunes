@extends('layouts.admin')

@section('page_title', 'Website Optimization, SEO & Quality Audit Engine')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-linear-to-br from-primary to-amber-600 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                <i class="bi bi-shield-check"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Website Optimization & Quality Audit Engine</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        Enterprise Standard
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Continuous multi-discipline evaluation across On-Page SEO, AI Readiness (GEO), Technical Crawlability, Performance, Accessibility (WCAG 2.2 AA), Security (OWASP), and Schema Graphs.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.audit.export', ['id' => $latestRun->id, 'format' => 'csv']) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs">
                <i class="bi bi-download"></i> Export CSV
            </a>
            <form action="{{ route('admin.audit.run') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold transition shadow-sm">
                    <i class="bi bi-arrow-repeat"></i> Run Full Audit Now
                </button>
            </form>
        </div>
    </div>

    <!-- Health Score & Primary Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Overall Health Score Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-5 md:col-span-1">
            <div class="relative w-24 h-24 shrink-0 flex items-center justify-center">
                <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 36 36">
                    <path class="text-slate-100" stroke-width="3.5" stroke="currentColor" fill="none"
                        d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    <path class="{{ $latestRun->health_score >= 85 ? 'text-emerald-500' : ($latestRun->health_score >= 70 ? 'text-amber-500' : 'text-rose-500') }}"
                        stroke-dasharray="{{ $latestRun->health_score }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                        d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-2xl font-black text-slate-900 leading-none">{{ $latestRun->health_score }}</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase mt-0.5">/ 100</span>
                </div>
            </div>
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Overall Health</span>
                <h3 class="text-base font-extrabold text-slate-900 leading-tight mt-0.5">
                    @if($latestRun->health_score >= 85)
                        <span class="text-emerald-600">Excellent Posture</span>
                    @elseif($latestRun->health_score >= 70)
                        <span class="text-amber-600">Good (Needs Polish)</span>
                    @else
                        <span class="text-rose-600">Action Required</span>
                    @endif
                </h3>
                <p class="text-[11px] text-slate-500 mt-1">Weighted composite across 9 verification disciplines.</p>
            </div>
        </div>

        <!-- Issue Breakdown Counters -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Severity Breakdown</span>
            <div class="grid grid-cols-2 gap-2 mt-2">
                <div class="flex items-center gap-2 p-2 rounded-xl bg-rose-50 border border-rose-100">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                    <div>
                        <div class="text-xs font-black text-rose-700">{{ $latestRun->critical_count }}</div>
                        <div class="text-[10px] text-rose-600 font-semibold">Critical</div>
                    </div>
                </div>
                <div class="flex items-center gap-2 p-2 rounded-xl bg-orange-50 border border-orange-100">
                    <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                    <div>
                        <div class="text-xs font-black text-orange-700">{{ $latestRun->high_count }}</div>
                        <div class="text-[10px] text-orange-600 font-semibold">High</div>
                    </div>
                </div>
                <div class="flex items-center gap-2 p-2 rounded-xl bg-amber-50 border border-amber-100">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <div>
                        <div class="text-xs font-black text-amber-700">{{ $latestRun->medium_count }}</div>
                        <div class="text-[10px] text-amber-600 font-semibold">Medium</div>
                    </div>
                </div>
                <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                    <div>
                        <div class="text-xs font-black text-slate-700">{{ $latestRun->low_count + $latestRun->info_count }}</div>
                        <div class="text-[10px] text-slate-500 font-semibold">Low & Info</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scan Telemetry Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Audit Telemetry</span>
            <div class="space-y-2 mt-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500">Scan Duration:</span>
                    <span class="font-mono font-bold text-slate-800">{{ $latestRun->duration_ms }} ms</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500">Scanned Entities:</span>
                    <span class="font-mono font-bold text-slate-800">{{ $latestRun->scanned_urls_count }} URLs/Models</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500">Execution Mode:</span>
                    <span class="font-mono font-bold text-slate-800 uppercase text-[10px] px-2 py-0.5 bg-slate-100 rounded-md">{{ $latestRun->scan_type }}</span>
                </div>
            </div>
        </div>

        <!-- Last Execution Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Last Execution</span>
            <div>
                <div class="text-xs font-black text-slate-900 mt-1">{{ $latestRun->created_at->format('M d, Y h:i A') }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">{{ $latestRun->created_at->diffForHumans() }}</div>
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Triggered By:</span>
                <span class="font-bold text-slate-700">{{ $latestRun->triggered_by ? 'Administrator' : 'System Engine' }}</span>
            </div>
        </div>
    </div>

    <!-- Category Performance Breakdown Grid -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
            <i class="bi bi-grid-fill text-primary"></i> Multi-Discipline Category Scores
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @php
                $categoryScores = $latestRun->category_scores ?? [];
            @endphp
            @foreach($categoryScores as $catKey => $catData)
                @php
                    $score = $catData['score'] ?? 100;
                    $badgeClass = $score >= 85 ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : ($score >= 70 ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-rose-700 bg-rose-50 border-rose-200');
                    $barColor = $score >= 85 ? 'bg-emerald-500' : ($score >= 70 ? 'bg-amber-500' : 'bg-rose-500');
                @endphp
                <a href="{{ route('admin.audit.index', ['category' => $catKey]) }}" class="block p-4 rounded-xl border border-slate-200 hover:border-primary/50 hover:shadow-xs transition bg-slate-50/50">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-800">{{ $catData['label'] ?? $catKey }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-black border {{ $badgeClass }}">
                            {{ $score }}/100
                        </span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden mb-2">
                        <div class="{{ $barColor }} h-1.5 rounded-full" style="width: {{ $score }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500">
                        <span>Weight: {{ $catData['weight'] }}%</span>
                        <span>Issues: {{ $catData['counts']['total'] ?? 0 }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Active Findings & Actionable Remediation Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 leading-tight">Optimization Findings & Recommended Actions</h2>
                <p class="text-xs text-slate-500 mt-0.5">Filter, inspect evidence, assign owners, and track resolution across all website elements.</p>
            </div>
            <!-- Filter Bar -->
            <form action="{{ route('admin.audit.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <select name="severity" class="rounded-xl border border-slate-200 text-xs px-3 py-1.5 bg-white text-slate-700 font-medium focus:border-primary">
                    <option value="">All Severities</option>
                    <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
                    <option value="high" {{ request('severity') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('severity') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ request('severity') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="info" {{ request('severity') === 'info' ? 'selected' : '' }}>Informational</option>
                </select>
                <select name="status" class="rounded-xl border border-slate-200 text-xs px-3 py-1.5 bg-white text-slate-700 font-medium focus:border-primary">
                    <option value="">All Statuses</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="ignored" {{ request('status') === 'ignored' ? 'selected' : '' }}>Ignored</option>
                </select>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title or URL..." class="rounded-xl border border-slate-200 text-xs px-3 py-1.5 bg-white text-slate-700 placeholder-slate-400 focus:border-primary">
                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-slate-800 text-white text-xs font-bold hover:bg-slate-900 transition">
                    Filter
                </button>
                @if(request()->hasAny(['category', 'severity', 'status', 'search']))
                    <a href="{{ route('admin.audit.index') }}" class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-medium hover:bg-slate-50 transition">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-4">Severity</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Finding & Recommended Action</th>
                        <th class="py-3 px-4">Affected URL / Evidence</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($issues as $issue)
                        @php
                            $sevBadge = match($issue->severity) {
                                'critical' => 'bg-rose-100 text-rose-800 border-rose-200',
                                'high' => 'bg-orange-100 text-orange-800 border-orange-200',
                                'medium' => 'bg-amber-100 text-amber-800 border-amber-200',
                                'low' => 'bg-slate-100 text-slate-700 border-slate-200',
                                default => 'bg-sky-100 text-sky-800 border-sky-200',
                            };
                            $statusBadge = match($issue->status) {
                                'resolved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'in_progress' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'ignored' => 'bg-slate-100 text-slate-500 border-slate-200',
                                default => 'bg-amber-100 text-amber-800 border-amber-200',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black border uppercase {{ $sevBadge }}">
                                    {{ $issue->severity }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                <span class="font-mono text-[11px] text-slate-500">{{ $issue->category }}</span>
                                <div class="text-[10px] text-slate-400 capitalize">{{ str_replace('_', ' ', $issue->verification_type) }}</div>
                            </td>
                            <td class="py-3.5 px-4 align-top max-w-md">
                                <div class="font-bold text-slate-900 text-xs">{{ $issue->title }}</div>
                                <div class="text-[11px] text-slate-600 mt-1 leading-relaxed">{{ $issue->description }}</div>
                                @if($issue->recommended_action)
                                    <div class="mt-2 p-2 rounded-lg bg-emerald-50 border border-emerald-100 text-[11px] text-emerald-800 flex items-start gap-1.5">
                                        <i class="bi bi-lightbulb-fill text-emerald-600 mt-0.5 shrink-0"></i>
                                        <span><strong>Remediation:</strong> {{ $issue->recommended_action }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 align-top max-w-xs">
                                @if($issue->affected_url)
                                    <a href="{{ $issue->affected_url }}" target="_blank" class="text-primary hover:underline font-mono text-[11px] truncate block" title="{{ $issue->affected_url }}">
                                        {{ $issue->affected_url }} <i class="bi bi-box-arrow-up-right text-[10px]"></i>
                                    </a>
                                @else
                                    <span class="text-slate-400 font-mono text-[11px]">Sitewide / Global</span>
                                @endif
                                @if($issue->evidence)
                                    <div class="mt-1 font-mono text-[10px] text-slate-500 bg-slate-100 p-1.5 rounded-md break-all">
                                        {{ Str::limit($issue->evidence, 120) }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border capitalize {{ $statusBadge }}">
                                    {{ str_replace('_', ' ', $issue->status) }}
                                </span>
                                @if($issue->resolved_at)
                                    <div class="text-[10px] text-slate-400 mt-1">{{ $issue->resolved_at->format('M d, Y') }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 align-top text-end whitespace-nowrap">
                                <!-- Status Update Form / Button -->
                                <form action="{{ route('admin.audit.issues.update', $issue->id) }}" method="POST" class="inline-flex items-center gap-1.5">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" onchange="this.form.submit()" class="rounded-lg border border-slate-200 text-[11px] py-1 px-2 bg-white text-slate-700 font-medium">
                                        <option value="open" {{ $issue->status === 'open' ? 'selected' : '' }}>Open</option>
                                        <option value="in_progress" {{ $issue->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="resolved" {{ $issue->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                        <option value="ignored" {{ $issue->status === 'ignored' ? 'selected' : '' }}>Ignored</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i class="bi bi-check2-circle text-4xl text-emerald-500 block mb-2"></i>
                                <span class="font-bold text-slate-700 text-sm">No issues matching the selected filters.</span>
                                <p class="text-xs text-slate-400 mt-1">Your website configuration is in clean health according to the current audit criteria.</p>
                            </td>
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

    <!-- Recent Audit Runs History -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
            <i class="bi bi-clock-history text-primary"></i> Historical Audit Executions
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                        <th class="py-2.5 px-3">Run ID</th>
                        <th class="py-2.5 px-3">Execution Date</th>
                        <th class="py-2.5 px-3">Health Score</th>
                        <th class="py-2.5 px-3">Total Issues</th>
                        <th class="py-2.5 px-3">Critical / High</th>
                        <th class="py-2.5 px-3">Duration</th>
                        <th class="py-2.5 px-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($recentRuns as $pastRun)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-800">#{{ $pastRun->id }}</td>
                            <td class="py-2.5 px-3">{{ $pastRun->created_at->format('M d, Y h:i A') }} ({{ $pastRun->created_at->diffForHumans() }})</td>
                            <td class="py-2.5 px-3 font-black {{ $pastRun->health_score >= 85 ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ $pastRun->health_score }} / 100
                            </td>
                            <td class="py-2.5 px-3 font-semibold">{{ $pastRun->total_issues_count }} issues</td>
                            <td class="py-2.5 px-3">
                                <span class="text-rose-600 font-bold">{{ $pastRun->critical_count }} Crit</span>, 
                                <span class="text-orange-600 font-bold">{{ $pastRun->high_count }} High</span>
                            </td>
                            <td class="py-2.5 px-3 font-mono text-slate-500">{{ $pastRun->duration_ms }} ms</td>
                            <td class="py-2.5 px-3 text-end">
                                <a href="{{ route('admin.audit.show', $pastRun->id) }}" class="text-primary hover:underline font-bold text-[11px]">
                                    Inspect Run <i class="bi bi-arrow-right"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
