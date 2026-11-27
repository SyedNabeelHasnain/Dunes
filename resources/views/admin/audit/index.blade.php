@extends('layouts.admin')

@section('page_title', 'Website Optimization, SEO & Quality Audit Engine')

@section('content')
<div class="space-y-6" x-data="{
    remediateModal: false,
    showMethodology: false,
    editIssue: { id: null, title: '', status: 'open', assigned_to: '', remediation_notes: '' },
    openRemediate(issue) {
        this.editIssue = {
            id: issue.id,
            title: issue.title,
            status: issue.status,
            assigned_to: issue.assigned_to || '',
            remediation_notes: issue.remediation_notes || ''
        };
        this.remediateModal = true;
    }
}">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-xs font-bold text-emerald-800">{{ session('success') }}</div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-linear-to-br from-primary to-amber-600 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                <i class="bi bi-shield-check"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Website Optimization & Quality Audit Engine</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        11 Disciplines · Live DOM Verified
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Evidence-based auditing across SEO, Technical SEO, AI Readiness (GEO), Performance &amp; RUM, WCAG 2.2 AA Accessibility, OWASP Security, Schema.org, Content E-E-A-T, Privacy, UX, and Infrastructure.
                </p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="showMethodology = !showMethodology" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition">
                <i class="bi bi-calculator"></i> Methodology
            </button>
            @if($latestRun)
            <a href="{{ route('admin.audit.export', ['id' => $latestRun->id, 'format' => 'csv']) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs">
                <i class="bi bi-download"></i> Export CSV
            </a>
            @endif
            <form action="{{ route('admin.audit.run') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="scan_type" value="incremental">
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition">
                    <i class="bi bi-lightning-charge"></i> Incremental Scan
                </button>
            </form>
            <form action="{{ route('admin.audit.run') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="scan_type" value="full">
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold transition shadow-sm">
                    <i class="bi bi-arrow-repeat"></i> Run Full Audit Now
                </button>
            </form>
        </div>
    </div>

    <!-- Single-URL Quick Audit Bar & Methodology Drawer -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        <form action="{{ route('admin.audit.run') }}" method="POST" class="lg:col-span-7 bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-wrap items-center gap-2">
            @csrf
            <input type="hidden" name="scan_type" value="single_url">
            <span class="text-xs font-extrabold text-slate-700 px-1">Scan Specific Path:</span>
            <input type="text" name="target_url" placeholder="/evening-desert-safari-dubai" required class="flex-1 min-w-[200px] rounded-xl border border-slate-200 px-3 py-2 text-xs font-mono">
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:opacity-90">Audit URL</button>
        </form>

        <div class="lg:col-span-5 bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between text-xs">
            <div>
                <span class="font-bold text-slate-700">Check Classification:</span>
                <span class="ml-2 text-emerald-700 font-bold">{{ $verificationCounts['automated'] ?? 0 }} Automated</span> ·
                <span class="text-amber-700 font-bold">{{ $verificationCounts['partial_automated'] ?? 0 }} Partial</span> ·
                <span class="text-sky-700 font-bold">{{ $verificationCounts['manual_review'] ?? 0 }} Manual QA</span>
            </div>
        </div>
    </div>

    <!-- Transparent Methodology Panel -->
    <div x-show="showMethodology" x-cloak class="rounded-2xl border border-slate-200 bg-slate-900 text-slate-100 p-6 space-y-4 text-xs">
        <div class="flex items-center justify-between">
            <h6 class="text-sm font-extrabold text-white">Transparent Scoring Formula &amp; Category Weights</h6>
            <button type="button" @click="showMethodology = false" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="font-mono text-amber-300 bg-slate-950/70 p-3 rounded-xl border border-slate-800">
            {{ $methodology['formula'] }}
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-2">
            @foreach($methodology['weights'] as $w)
                <div class="rounded-lg bg-slate-800/80 p-2.5 border border-slate-700">
                    <div class="font-bold text-white">{{ $w['weight_pct'] }}%</div>
                    <div class="text-[11px] text-slate-300">{{ $w['label'] }}</div>
                </div>
            @endforeach
        </div>
        <div class="text-[11px] text-slate-400">
            Honest boundaries: External third-party search engine rankings, domain authority, live AI answer engine inclusion percentages, and off-server TLS/DNS settings remain dependent on external infrastructure/provider verification.
        </div>
    </div>

    <!-- Run-to-Run Diff Banner -->
    @if(!empty($comparison))
        <div class="rounded-2xl border border-sky-200 bg-sky-50/70 px-5 py-3.5 flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex flex-wrap items-center gap-3">
                <span class="font-extrabold text-sky-900">Run Comparison vs #{{ $comparison['previous_run_id'] }}:</span>
                <span class="px-2.5 py-0.5 rounded-full font-black {{ $comparison['score_delta'] >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                    {{ $comparison['score_delta'] >= 0 ? '+' : '' }}{{ $comparison['score_delta'] }} pts
                </span>
                <span class="text-emerald-700 font-bold">{{ $comparison['resolved_count'] }} resolved</span> ·
                <span class="text-amber-700 font-bold">{{ $comparison['new_count'] }} new</span>
            </div>
            <a href="{{ route('admin.audit.show', $comparison['previous_run_id']) }}" class="font-bold text-sky-800 hover:underline">Inspect Baseline Run #{{ $comparison['previous_run_id'] }}</a>
        </div>
    @endif

    @if(!$latestRun)
    <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <div class="w-20 h-20 rounded-full border-4 border-slate-100 flex flex-col items-center justify-center shrink-0">
                <span class="text-xl font-black text-slate-400 leading-none">--</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase mt-0.5">/ 100</span>
            </div>
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Overall Health</span>
                <h3 class="text-base font-extrabold text-slate-900 mt-0.5">No Audit Runs Recorded Yet</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-md">Click <strong>Incremental Scan</strong> or <strong>Run Full Audit Now</strong> above to execute your first live evidence-based site health audit.</p>
            </div>
        </div>
    </div>
    @else
    <!-- Health Score & Primary Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-5">
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
                <p class="text-[11px] text-slate-500 mt-1">Weighted across {{ count($latestRun->category_scores ?? []) }} disciplines.</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Severity Breakdown</span>
            <div class="grid grid-cols-2 gap-2 mt-2">
                <div class="flex items-center gap-2 p-2 rounded-xl bg-rose-50 border border-rose-100">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                    <div><div class="text-xs font-black text-rose-700">{{ $latestRun->critical_count }}</div><div class="text-[10px] text-rose-600 font-semibold">Critical</div></div>
                </div>
                <div class="flex items-center gap-2 p-2 rounded-xl bg-orange-50 border border-orange-100">
                    <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                    <div><div class="text-xs font-black text-orange-700">{{ $latestRun->high_count }}</div><div class="text-[10px] text-orange-600 font-semibold">High</div></div>
                </div>
                <div class="flex items-center gap-2 p-2 rounded-xl bg-amber-50 border border-amber-100">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <div><div class="text-xs font-black text-amber-700">{{ $latestRun->medium_count }}</div><div class="text-[10px] text-amber-600 font-semibold">Medium</div></div>
                </div>
                <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                    <div><div class="text-xs font-black text-slate-700">{{ $latestRun->low_count + $latestRun->info_count }}</div><div class="text-[10px] text-slate-500 font-semibold">Low & Info</div></div>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Audit Telemetry</span>
            <div class="space-y-2 mt-2 text-xs">
                <div class="flex items-center justify-between"><span class="text-slate-500">Scan Duration:</span><span class="font-mono font-bold text-slate-800">{{ $latestRun->duration_ms }} ms</span></div>
                <div class="flex items-center justify-between"><span class="text-slate-500">Scanned Entities:</span><span class="font-mono font-bold text-slate-800">{{ $latestRun->scanned_urls_count }} URLs/Models</span></div>
                <div class="flex items-center justify-between"><span class="text-slate-500">Mode:</span><span class="font-mono font-bold uppercase text-[10px] px-2 py-0.5 bg-slate-100 rounded-md">{{ $latestRun->scan_type }}</span></div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Last Execution</span>
            <div>
                <div class="text-xs font-black text-slate-900 mt-1">{{ optional($latestRun->created_at)->format('M d, Y h:i A') ?? 'N/A' }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">{{ optional($latestRun->created_at)->diffForHumans() ?? 'Just now' }}</div>
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Run ID:</span>
                <span class="font-bold text-slate-700">#{{ $latestRun->id }}</span>
            </div>
        </div>
    </div>

    <!-- Category Performance Breakdown Grid -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
            <i class="bi bi-grid-fill text-primary"></i> 11-Discipline Category Scores
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach(($latestRun->category_scores ?? []) as $catKey => $catData)
                @php
                    $score = $catData['score'] ?? 100;
                    $badgeClass = $score >= 85 ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : ($score >= 70 ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-rose-700 bg-rose-50 border-rose-200');
                    $barColor = $score >= 85 ? 'bg-emerald-500' : ($score >= 70 ? 'bg-amber-500' : 'bg-rose-500');
                @endphp
                <a href="{{ route('admin.audit.index', ['category' => $catKey]) }}" class="block p-4 rounded-xl border border-slate-200 hover:border-primary/50 hover:shadow-xs transition bg-slate-50/50">
                    <div class="flex items-center justify-between mb-2 gap-2">
                        <span class="text-xs font-bold text-slate-800 truncate">{{ $catData['label'] ?? $catKey }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-black border {{ $badgeClass }} shrink-0">{{ $score }}/100</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden mb-2">
                        <div class="{{ $barColor }} h-1.5 rounded-full" style="width: {{ $score }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500">
                        <span>Weight: {{ $catData['weight'] ?? 0 }}%</span>
                        <span>Issues: {{ $catData['counts']['total'] ?? 0 }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Active Findings & Remediation Workflow Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 leading-tight">Optimization Findings &amp; Remediation Workflow</h2>
                <p class="text-xs text-slate-500 mt-0.5">Filter by discipline, verification level, assignee, or status.</p>
            </div>
            <form action="{{ route('admin.audit.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <select name="severity" class="rounded-xl border border-slate-200 text-xs px-3 py-1.5 bg-white text-slate-700 font-medium">
                    <option value="">All Severities</option>
                    @foreach(['critical', 'high', 'medium', 'low', 'info'] as $sev)
                        <option value="{{ $sev }}" @selected(request('severity') === $sev)>{{ ucfirst($sev) }}</option>
                    @endforeach
                </select>
                <select name="verification_type" class="rounded-xl border border-slate-200 text-xs px-3 py-1.5 bg-white text-slate-700 font-medium">
                    <option value="">All Check Types</option>
                    <option value="automated" @selected(request('verification_type') === 'automated')>Automated</option>
                    <option value="partial_automated" @selected(request('verification_type') === 'partial_automated')>Partial Automated</option>
                    <option value="manual_review" @selected(request('verification_type') === 'manual_review')>Manual QA</option>
                </select>
                <select name="status" class="rounded-xl border border-slate-200 text-xs px-3 py-1.5 bg-white text-slate-700 font-medium">
                    <option value="">All Statuses</option>
                    @foreach(['open', 'in_progress', 'resolved', 'ignored'] as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucwords(str_replace('_', ' ', $st)) }}</option>
                    @endforeach
                </select>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title or URL..." class="rounded-xl border border-slate-200 text-xs px-3 py-1.5 bg-white">
                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-slate-800 text-white text-xs font-bold">Filter</button>
                @if(request()->hasAny(['category', 'severity', 'status', 'verification_type', 'search']))
                    <a href="{{ route('admin.audit.index') }}" class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-medium">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-4">Severity</th>
                        <th class="py-3 px-4">Category / Check Type</th>
                        <th class="py-3 px-4">Finding &amp; Action</th>
                        <th class="py-3 px-4">URL &amp; Evidence</th>
                        <th class="py-3 px-4">Status &amp; Owner</th>
                        <th class="py-3 px-4 text-end">Workflow</th>
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
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black border uppercase {{ $sevBadge }}">{{ $issue->severity }}</span>
                            </td>
                            <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                <span class="font-mono text-[11px] text-slate-600">{{ $issue->category }}</span>
                                <div class="text-[10px] text-slate-400 capitalize">{{ str_replace('_', ' ', $issue->verification_type) }}</div>
                            </td>
                            <td class="py-3.5 px-4 align-top max-w-md">
                                <div class="font-bold text-slate-900">{{ $issue->title }}</div>
                                <div class="text-[11px] text-slate-600 mt-1">{{ $issue->description }}</div>
                                @if($issue->recommended_action)
                                    <div class="mt-2 p-2 rounded-lg bg-emerald-50 border border-emerald-100 text-[11px] text-emerald-800">
                                        <strong>Remediation:</strong> {{ $issue->recommended_action }}
                                    </div>
                                @endif
                                @if($issue->remediation_notes)
                                    <div class="mt-1.5 text-[11px] text-slate-600 italic">Note: {{ $issue->remediation_notes }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 align-top max-w-xs">
                                @if($issue->affected_url)
                                    <a href="{{ $issue->affected_url }}" target="_blank" class="text-primary hover:underline font-mono text-[11px] truncate block">{{ $issue->affected_url }}</a>
                                @else
                                    <span class="text-slate-400 font-mono text-[11px]">Sitewide</span>
                                @endif
                                @if($issue->evidence)
                                    <div class="mt-1 font-mono text-[10px] text-slate-500 bg-slate-100 p-1.5 rounded-md break-all">{{ Str::limit($issue->evidence, 120) }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border capitalize {{ $statusBadge }}">{{ str_replace('_', ' ', $issue->status) }}</span>
                                <div class="text-[10px] text-slate-500 mt-1">{{ $issue->assignee?->name ?? 'Unassigned' }}</div>
                            </td>
                            <td class="py-3.5 px-4 align-top text-end whitespace-nowrap">
                                @php $issuePayload = $issue->only(['id', 'title', 'status', 'assigned_to', 'remediation_notes']); @endphp
                                <button type="button" data-issue="{{ json_encode($issuePayload) }}" @click="openRemediate(JSON.parse($el.dataset.issue))" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700">
                                    Assign / Update
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-12 text-center text-slate-400 font-bold">No issues matching selected filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($issues->hasPages())
            <div class="p-4 border-t border-slate-100">{{ $issues->links() }}</div>
        @endif
    </div>

    <!-- Recent Audit Runs History -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider mb-4">Historical Audit Runs &amp; Run Comparison</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-slate-500 font-bold uppercase text-[10px]">
                        <th class="py-2.5 px-3">Run</th>
                        <th class="py-2.5 px-3">Date &amp; Mode</th>
                        <th class="py-2.5 px-3">Score</th>
                        <th class="py-2.5 px-3">Findings</th>
                        <th class="py-2.5 px-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($recentRuns as $pastRun)
                        <tr>
                            <td class="py-2.5 px-3 font-mono font-bold">#{{ $pastRun->id }}</td>
                            <td class="py-2.5 px-3">{{ optional($pastRun->created_at)->format('M d, Y h:i A') ?? 'N/A' }} · <span class="uppercase text-[10px] font-bold text-slate-500">{{ $pastRun->scan_type }}</span></td>
                            <td class="py-2.5 px-3 font-black {{ $pastRun->health_score >= 85 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $pastRun->health_score }}/100</td>
                            <td class="py-2.5 px-3">{{ $pastRun->total_issues_count }} ({{ $pastRun->critical_count }} Crit, {{ $pastRun->high_count }} High)</td>
                            <td class="py-2.5 px-3 text-end space-x-2">
                                @if($pastRun->id !== $latestRun->id)
                                    <a href="{{ route('admin.audit.index', ['compare_with' => $pastRun->id]) }}" class="text-sky-700 hover:underline font-bold">Compare vs Latest</a>
                                @endif
                                <a href="{{ route('admin.audit.show', $pastRun->id) }}" class="text-primary hover:underline font-bold">Inspect</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Assign / Remediation Modal -->
    <div x-show="remediateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div @click.outside="remediateModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-slate-900">Remediate &amp; Assign Audit Issue</h3>
                <button type="button" @click="remediateModal = false" class="text-slate-400 hover:text-slate-700"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="text-xs font-bold text-slate-700 bg-slate-50 rounded-xl p-3 border border-slate-200" x-text="editIssue.title"></div>
            <form :action="`{{ url('/admin/audit/issues') }}/${editIssue.id}`" method="POST" class="space-y-3 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Status</label>
                    <select name="status" x-model="editIssue.status" class="w-full rounded-xl border border-slate-200 px-3 py-2 font-semibold">
                        <option value="open">Open</option>
                        <option value="in_progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="ignored">Ignored</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Assign To Staff Owner</label>
                    <select name="assigned_to" x-model="editIssue.assigned_to" class="w-full rounded-xl border border-slate-200 px-3 py-2">
                        <option value="">Unassigned</option>
                        @foreach($staffUsers as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->role }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Remediation Notes</label>
                    <textarea name="remediation_notes" x-model="editIssue.remediation_notes" rows="3" placeholder="Document root-cause fix or verification details..." class="w-full rounded-xl border border-slate-200 px-3 py-2"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="remediateModal = false" class="px-4 py-2 rounded-xl border border-slate-200 font-bold text-slate-700">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-primary text-white font-bold">Save Remediation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
