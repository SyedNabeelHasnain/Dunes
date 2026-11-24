@extends('layouts.admin')

@section('page_title', 'Governance, Approvals & Audit Trail')

@section('content')
<div class="space-y-6 mb-8">
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3.5 text-xs font-bold text-emerald-800 flex items-center gap-2">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3.5 text-xs font-bold text-rose-800">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Optimization Center Overview KPIs -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-xs p-6">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
            <div>
                <h5 class="text-base font-extrabold text-slate-900">Website Optimization & Governance Hub</h5>
                <p class="text-xs text-slate-500 mt-0.5">Centralized change approvals, immutable configuration audit log, and platform health snapshot.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if (auth()->user()?->hasPermission('governance.rbac'))
                    <a href="{{ route('admin.governance.users.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 transition inline-flex items-center gap-2">
                        <i class="bi bi-people"></i> Staff & RBAC ({{ $stats['staff_count'] }})
                    </a>
                @endif
                @if (auth()->user()?->hasPermission('governance.audit_logs'))
                    <a href="{{ route('admin.governance.logs.export') }}" class="px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:opacity-90 transition inline-flex items-center gap-2">
                        <i class="bi bi-download"></i> Export Audit CSV
                    </a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5">
                <div class="text-[11px] font-bold text-slate-500">Audit Score</div>
                <div class="text-xl font-black text-slate-900 mt-1">{{ $stats['latest_health_score'] !== null ? $stats['latest_health_score'].'/100' : 'N/A' }}</div>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-3.5">
                <div class="text-[11px] font-bold text-amber-700">Pending Approvals</div>
                <div class="text-xl font-black text-amber-900 mt-1">{{ $stats['pending_approvals'] }}</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5">
                <div class="text-[11px] font-bold text-slate-500">SEO Templates</div>
                <div class="text-xl font-black text-slate-900 mt-1">{{ $stats['active_templates'] }}</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5">
                <div class="text-[11px] font-bold text-slate-500">Active Redirects</div>
                <div class="text-xl font-black text-slate-900 mt-1">{{ $stats['active_redirects'] }}</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5">
                <div class="text-[11px] font-bold text-slate-500">404 Hits (7d)</div>
                <div class="text-xl font-black text-slate-900 mt-1">{{ $stats['recent_404s'] }}</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5">
                <div class="text-[11px] font-bold text-slate-500">Consents (7d)</div>
                <div class="text-xl font-black text-slate-900 mt-1">{{ $stats['consents_7d'] }}</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5">
                <div class="text-[11px] font-bold text-slate-500">CWV Samples (7d)</div>
                <div class="text-xl font-black text-slate-900 mt-1">{{ $stats['vitals_7d'] }}</div>
            </div>
        </div>
    </div>

    <!-- Pending Approvals Queue -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h6 class="text-sm font-extrabold text-slate-900">Pending Approval Queue</h6>
                <p class="text-xs text-slate-500">High-impact SEO, robots.txt, security, privacy, and redirect updates staged for reviewer sign-off.</p>
            </div>
            <span class="rounded-full bg-amber-100 text-amber-800 px-3 py-1 text-xs font-bold">{{ $pendingApprovals->count() }} pending</span>
        </div>

        @if ($pendingApprovals->isEmpty())
            <div class="p-6 text-xs text-slate-500">No pending change proposals require approval right now.</div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach ($pendingApprovals as $approval)
                    <div class="p-6 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <span class="inline-block rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-700 uppercase mr-2">{{ $approval->section }}</span>
                                <span class="text-sm font-bold text-slate-900">{{ $approval->summary }}</span>
                                <div class="text-xs text-slate-500 mt-1">
                                    Requested by {{ $approval->requester?->name ?? 'System' }} ({{ $approval->requester?->email ?? 'N/A' }}) · {{ $approval->created_at?->diffForHumans() }} · Target: <code>{{ $approval->target_key }}</code>
                                </div>
                            </div>
                            @if (auth()->user()?->hasPermission('governance.approve'))
                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('admin.governance.approvals.approve', $approval->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition">
                                            Approve & Publish
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.governance.approvals.reject', $approval->id) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="review_notes" value="Rejected from governance queue">
                                        <button type="submit" class="px-3.5 py-2 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 text-xs font-bold hover:bg-rose-100 transition">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                            <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                                <div class="font-bold text-slate-600 mb-1">Current State</div>
                                <pre class="text-[11px] text-slate-700 whitespace-pre-wrap break-all">{{ json_encode($approval->previous_payload ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            </div>
                            <div class="rounded-xl bg-amber-50/50 border border-amber-200 p-3">
                                <div class="font-bold text-amber-800 mb-1">Proposed Change</div>
                                <pre class="text-[11px] text-slate-800 whitespace-pre-wrap break-all">{{ json_encode($approval->payload ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Immutable Configuration Audit Log -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h6 class="text-sm font-extrabold text-slate-900">Immutable Configuration Audit Log</h6>
                <p class="text-xs text-slate-500">Tracks configuration updates, approvals, role modifications, and content state changes with secret redaction.</p>
            </div>
            <form method="GET" action="{{ route('admin.governance.index') }}" class="flex flex-wrap items-center gap-2">
                <select name="section" class="rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-700">
                    <option value="">All Sections</option>
                    @foreach ($sections as $sec)
                        <option value="{{ $sec }}" @selected(request('section') === $sec)>{{ $sec }}</option>
                    @endforeach
                </select>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search key, user, summary..." class="rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-700">
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold">
                    <tr>
                        <th class="px-4 py-3 text-left">When</th>
                        <th class="px-4 py-3 text-left">Actor</th>
                        <th class="px-4 py-3 text-left">Section / Action</th>
                        <th class="px-4 py-3 text-left">Key & Summary</th>
                        <th class="px-4 py-3 text-left">Diff</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($auditLogs as $log)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-800">{{ $log->user_name ?? 'System' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $log->user_role }} · {{ $log->ip_address }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-block rounded-lg bg-slate-100 px-2 py-0.5 font-bold text-slate-700">{{ $log->section }}</span>
                                <span class="inline-block rounded-lg bg-primary/10 px-2 py-0.5 font-bold text-primary ml-1">{{ $log->action }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-mono text-[11px] text-slate-800">{{ $log->setting_key }}</div>
                                <div class="text-slate-600 mt-0.5">{{ $log->summary }}</div>
                            </td>
                            <td class="px-4 py-3 max-w-sm">
                                @if ($log->old_value !== null || $log->new_value !== null)
                                    <div class="text-[11px] text-rose-700 truncate">- {{ \Illuminate\Support\Str::limit((string) $log->old_value, 90) }}</div>
                                    <div class="text-[11px] text-emerald-700 truncate">+ {{ \Illuminate\Support\Str::limit((string) $log->new_value, 90) }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">No configuration audit log entries recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($auditLogs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $auditLogs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
