@extends('layouts.admin')

@section('page_title', 'IP Firewall & Analytics Access Control')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi bi-shield-lock-fill text-primary"></i> IP Firewall & Analytics Filters
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Govern IP exclusions from visitor analytics & telemetry, manage real-time firewall blocklists, and process guest unblock appeal reviews.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <button type="button" onclick="openAddRuleModal()" class="btn-desert-animated inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold text-white shadow-xs">
                <i class="bi bi-plus-circle-fill"></i> Add IP Rule
            </button>
        </div>
    </div>

    <!-- Current Administrator IP Station Banner -->
    <div class="rounded-2xl border {{ $currentAdminRule && $currentAdminRule->mode === 'ignore_analytics' && $currentAdminRule->status === 'active' ? 'border-emerald-200 bg-emerald-50/70' : 'border-amber-200 bg-amber-50/70' }} p-5 shadow-xs transition">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl {{ $currentAdminRule && $currentAdminRule->mode === 'ignore_analytics' && $currentAdminRule->status === 'active' ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white' }} flex items-center justify-center shrink-0 shadow-xs">
                    <i class="bi {{ $currentAdminRule && $currentAdminRule->mode === 'ignore_analytics' && $currentAdminRule->status === 'active' ? 'bi-check-shield-fill' : 'bi-exclamation-diamond-fill' }} text-lg"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Your Current Connected IP:</span>
                        <code class="px-2 py-0.5 rounded-md font-mono font-bold text-xs bg-white text-slate-900 border border-slate-200 shadow-2xs">{{ $currentAdminIp }}</code>
                        @if($currentAdminRule && $currentAdminRule->mode === 'ignore_analytics' && $currentAdminRule->status === 'active')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                <i class="bi bi-check-circle-fill me-1 text-emerald-600"></i> Excluded from Analytics
                            </span>
                        @elseif($currentAdminRule && $currentAdminRule->mode === 'block' && $currentAdminRule->status === 'active')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                <i class="bi bi-shield-x me-1 text-rose-600"></i> Marked as Blocked
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                <i class="bi bi-broadcast me-1 text-amber-600"></i> Currently Tracked
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-600 mt-1">
                        @if($currentAdminRule && $currentAdminRule->mode === 'ignore_analytics' && $currentAdminRule->status === 'active')
                            Your browsing and testing activities from this device are <strong>completely ignored</strong> from all analytics, pageview charts, visitor telemetry, and abandoned draft checkouts.
                        @else
                            Your visits and tests are currently being recorded in analytics and request logs. Use the 1-click button to exclude your IP and keep traffic metrics 100% accurate.
                        @endif
                    </p>
                </div>
            </div>

            <div class="shrink-0 flex items-center gap-2">
                @if(!($currentAdminRule && $currentAdminRule->mode === 'ignore_analytics' && $currentAdminRule->status === 'active'))
                    <form method="POST" action="{{ route('admin.security.ip-rules.quick-ignore') }}">
                        @csrf
                        <input type="hidden" name="purge_logs" value="1">
                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                            <i class="bi bi-eye-slash-fill text-amber-400"></i> 1-Click: Ignore My Current IP & Clean Past Logs
                        </button>
                    </form>
                @else
                    <button type="button" onclick="purgeLogsForIp('{{ $currentAdminIp }}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition cursor-pointer">
                        <i class="bi bi-trash3 text-rose-500"></i> Purge Past Logs for My IP
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- 5 Key Stat Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Rules</span>
                <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                    <i class="bi bi-shield-shaded"></i>
                </span>
            </div>
            <div class="text-xl font-black text-slate-900">{{ number_format($stats['total_rules']) }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Configured IP rules</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Active Ignored</span>
                <span class="w-8 h-8 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center border border-cyan-100">
                    <i class="bi bi-eye-slash-fill"></i>
                </span>
            </div>
            <div class="text-xl font-black text-cyan-700">{{ number_format($stats['active_ignored']) }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Excluded from analytics</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Active Blocks</span>
                <span class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-100">
                    <i class="bi bi-shield-x"></i>
                </span>
            </div>
            <div class="text-xl font-black text-rose-600">{{ number_format($stats['active_blocks']) }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Denied site access</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pending Appeals</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100">
                    <i class="bi bi-person-exclamation"></i>
                </span>
            </div>
            <div class="text-xl font-black text-amber-600">{{ number_format($stats['pending_appeals']) }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Awaiting admin review</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs col-span-2 md:col-span-1">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Firewall Hits</span>
                <span class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
                    <i class="bi bi-slash-circle-fill"></i>
                </span>
            </div>
            <div class="text-xl font-black text-slate-900">{{ number_format($stats['total_blocked_hits']) }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Interceptions blocked</div>
        </div>
    </div>

    <!-- Main Tabs -->
    <div x-data="{ currentTab: '{{ request()->has('appeals_page') || $appealStatus !== 'all' ? 'appeals' : 'rules' }}' }" class="space-y-4">
        <!-- Tab Pill Navigation -->
        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
            <div class="flex items-center gap-2">
                <button type="button" @click="currentTab = 'rules'" :class="currentTab === 'rules' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer">
                    <i class="bi bi-sliders"></i> IP Access Rules
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20 text-white font-mono">{{ $rules->total() }}</span>
                </button>
                <button type="button" @click="currentTab = 'appeals'" :class="currentTab === 'appeals' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer">
                    <i class="bi bi-envelope-paper-fill"></i> Unblock Review Requests
                    @if($stats['pending_appeals'] > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-amber-500 text-white font-bold animate-pulse">{{ $stats['pending_appeals'] }}</span>
                    @else
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-200 text-slate-700 font-mono">{{ $unblockRequests->total() }}</span>
                    @endif
                </button>
            </div>

            <!-- Quick Link to Analytics -->
            <a href="{{ route('admin.analytics.index') }}" class="text-xs text-primary font-bold hover:underline inline-flex items-center gap-1">
                <i class="bi bi-graph-up-arrow"></i> Back to Analytics &rarr;
            </a>
        </div>

        <!-- ==================== TAB 1: IP RULES ==================== -->
        <div x-show="currentTab === 'rules'" class="space-y-4">
            <!-- Filter & Search Toolbar -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('admin.security.ip-rules', ['tab' => 'all']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $tab === 'all' ? 'bg-primary text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">All Rules</a>
                    <a href="{{ route('admin.security.ip-rules', ['tab' => 'ignored']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $tab === 'ignored' ? 'bg-cyan-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Ignored from Analytics ({{ $stats['active_ignored'] }})</a>
                    <a href="{{ route('admin.security.ip-rules', ['tab' => 'blocked']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $tab === 'blocked' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Firewall Blocked ({{ $stats['active_blocks'] }})</a>
                    <a href="{{ route('admin.security.ip-rules', ['tab' => 'inactive']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $tab === 'inactive' ? 'bg-slate-700 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Inactive</a>
                </div>

                <form method="GET" action="{{ route('admin.security.ip-rules') }}" class="flex items-center gap-2">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search IP or label..." class="w-56 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary">
                        @if(request('search'))
                            <a href="{{ route('admin.security.ip-rules', ['tab' => $tab]) }}" class="absolute right-2 top-1.5 text-slate-400 hover:text-slate-600 text-xs">&times;</a>
                        @endif
                    </div>
                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition cursor-pointer">Filter</button>
                </form>
            </div>

            <!-- Rules Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-3 px-4">IP Address / CIDR</th>
                                <th class="py-3 px-4">Mode</th>
                                <th class="py-3 px-4">Label & Identity</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-center">Hits</th>
                                <th class="py-3 px-4">Last Activity</th>
                                <th class="py-3 px-4 text-right pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($rules as $rule)
                            <tr class="hover:bg-slate-50/60 transition-colors {{ $rule->ip_address === $currentAdminIp ? 'bg-amber-50/30' : '' }}">
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-slate-900 text-xs">{{ $rule->ip_address }}</span>
                                        @if($rule->ip_mask)
                                            <span class="px-1.5 py-0.2 rounded font-mono text-[10px] font-bold bg-slate-200 text-slate-700">/{{ $rule->ip_mask }}</span>
                                        @endif
                                        @if($rule->ip_address === $currentAdminIp)
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200" title="Your Current IP">You</span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Added {{ $rule->created_at->format('M d, Y') }}</div>
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($rule->mode === 'block')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <i class="bi bi-shield-x"></i> Block Access
                                        </span>
                                    @elseif($rule->mode === 'ignore_analytics')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200">
                                            <i class="bi bi-eye-slash-fill"></i> Ignore Analytics
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            <i class="bi bi-activity"></i> Monitor
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800 text-xs">{{ $rule->label ?: 'Unlabeled IP' }}</div>
                                    @if($rule->reason)
                                        <div class="text-[11px] text-slate-500 line-clamp-1 mt-0.5" title="{{ $rule->reason }}">{{ $rule->reason }}</div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <form method="POST" action="{{ route('admin.security.ip-rules.toggle-status', $rule->id) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold transition cursor-pointer {{ $rule->status === 'active' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}" title="Click to toggle status">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $rule->status === 'active' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                            {{ ucfirst($rule->status) }}
                                        </button>
                                    </form>
                                </td>

                                <td class="py-3.5 px-4 text-center font-mono text-xs font-bold {{ $rule->blocked_attempts_count > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                    {{ number_format($rule->blocked_attempts_count) }}
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap text-slate-500 text-[11px]">
                                    {{ $rule->last_activity_at ? $rule->last_activity_at->diffForHumans() : 'Never' }}
                                </td>

                                <td class="py-3.5 px-4 text-right pe-4 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Toggle Mode (Block vs Ignore) -->
                                        <form method="POST" action="{{ route('admin.security.ip-rules.toggle-mode', $rule->id) }}">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-medium transition cursor-pointer" title="{{ $rule->mode === 'block' ? 'Switch to Ignore Analytics' : 'Switch to Block Access' }}">
                                                <i class="bi {{ $rule->mode === 'block' ? 'bi-eye-slash text-cyan-600' : 'bi-shield-x text-rose-600' }}"></i>
                                            </button>
                                        </form>

                                        <!-- Purge Past Logs -->
                                        <button type="button" onclick="purgeLogsForIp('{{ $rule->ip_address }}')" class="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-medium transition cursor-pointer" title="Purge historical request logs for this IP">
                                            <i class="bi bi-clock-history text-slate-500"></i>
                                        </button>

                                        <!-- Delete Rule -->
                                        <form method="POST" action="{{ route('admin.security.ip-rules.destroy', $rule->id) }}" onsubmit="return confirm('Permanently delete security rule for IP {{ $rule->ip_address }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-rose-50 text-rose-600 text-xs font-medium transition cursor-pointer" title="Delete Rule">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-10 text-slate-400 text-xs">
                                    <i class="bi bi-shield-check text-2xl text-slate-300 block mb-2"></i>
                                    No IP security rules found matching the selected filter.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($rules->hasPages())
                <div class="p-4 border-t border-slate-100 bg-white">
                    {{ $rules->appends(request()->query())->links() }}
                </div>
                @endif
            </div>
        </div>

        <!-- ==================== TAB 2: UNBLOCK APPEAL REQUESTS ==================== -->
        <div x-show="currentTab === 'appeals'" class="space-y-4">
            <!-- Filter Toolbar -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex items-center gap-2">
                <span class="text-xs font-bold text-slate-600 mr-2">Filter Appeals:</span>
                <a href="{{ route('admin.security.ip-rules', ['appeal_status' => 'all']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $appealStatus === 'all' ? 'bg-primary text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">All Requests</a>
                <a href="{{ route('admin.security.ip-rules', ['appeal_status' => 'pending']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $appealStatus === 'pending' ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Pending ({{ $stats['pending_appeals'] }})</a>
                <a href="{{ route('admin.security.ip-rules', ['appeal_status' => 'approved_unblocked']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $appealStatus === 'approved_unblocked' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Approved</a>
                <a href="{{ route('admin.security.ip-rules', ['appeal_status' => 'rejected_kept_blocked']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $appealStatus === 'rejected_kept_blocked' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Rejected</a>
            </div>

            <!-- Appeals Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-3 px-4">Visitor Identity</th>
                                <th class="py-3 px-4">Blocked IP</th>
                                <th class="py-3 px-4">Appeal Explanation & Details</th>
                                <th class="py-3 px-4">Submitted</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right pe-4">Resolution Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($unblockRequests as $appeal)
                            <tr class="hover:bg-slate-50/60 transition-colors {{ $appeal->status === 'pending' ? 'bg-amber-50/20' : '' }}">
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-900 text-xs">{{ $appeal->name }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono">{{ $appeal->email }}</div>
                                    @if($appeal->phone)
                                        <div class="text-[11px] text-emerald-600 font-mono mt-0.5"><i class="bi bi-telephone-fill text-[9px] me-1"></i>{{ $appeal->phone }}</div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap font-mono text-xs font-bold text-slate-800">
                                    {{ $appeal->ip_address }}
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="text-xs text-slate-700 bg-slate-50 p-2.5 rounded-xl border border-slate-200 leading-relaxed max-w-md">
                                        {{ $appeal->reason }}
                                    </div>
                                    @if($appeal->admin_notes)
                                        <div class="mt-1 text-[11px] text-slate-500 italic">
                                            <strong>Admin Note:</strong> {{ $appeal->admin_notes }}
                                        </div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap text-[11px] text-slate-500">
                                    <div>{{ $appeal->created_at->format('M d, Y') }}</div>
                                    <div class="text-slate-400">{{ $appeal->created_at->format('H:i') }} ({{ $appeal->created_at->diffForHumans() }})</div>
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($appeal->status === 'pending')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 animate-pulse">
                                            <i class="bi bi-clock-fill"></i> Pending Review
                                        </span>
                                    @elseif($appeal->status === 'approved_unblocked')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <i class="bi bi-check-circle-fill"></i> Approved & Unblocked
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            <i class="bi bi-x-circle-fill"></i> Kept Blocked
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-right pe-4 whitespace-nowrap">
                                    @if($appeal->status === 'pending')
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- Approve & Unblock -->
                                            <form method="POST" action="{{ route('admin.security.unblock-requests.approve', $appeal->id) }}">
                                                @csrf
                                                <input type="hidden" name="target_action" value="switch_to_ignored">
                                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-xs transition cursor-pointer" title="Unblock IP and switch rule to Ignore Analytics mode">
                                                    <i class="bi bi-check-lg"></i> Accept & Unblock
                                                </button>
                                            </form>

                                            <!-- Reject Appeal -->
                                            <form method="POST" action="{{ route('admin.security.unblock-requests.reject', $appeal->id) }}" onsubmit="return confirm('Keep IP {{ $appeal->ip_address }} blocked and reject appeal?')">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-xs transition cursor-pointer" title="Keep IP blocked">
                                                    <i class="bi bi-x-lg"></i> Reject
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <form method="POST" action="{{ route('admin.security.unblock-requests.destroy', $appeal->id) }}" onsubmit="return confirm('Delete appeal record?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 text-xs transition cursor-pointer" title="Delete Appeal Record">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-slate-400 text-xs">
                                    <i class="bi bi-inbox text-2xl text-slate-300 block mb-2"></i>
                                    No visitor unblock requests found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($unblockRequests->hasPages())
                <div class="p-4 border-t border-slate-100 bg-white">
                    {{ $unblockRequests->appends(request()->query())->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Rule Modal -->
<div id="addRuleModal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 max-w-lg w-full shadow-2xl relative">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-5">
            <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <i class="bi bi-shield-plus text-primary"></i> Create Security IP Rule
            </h3>
            <button type="button" onclick="closeAddRuleModal()" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.security.ip-rules.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">IP Address <span class="text-rose-500">*</span></label>
                <input type="text" name="ip_address" required class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-900 font-mono placeholder-slate-400 outline-hidden focus:border-primary focus:ring-1 focus:ring-primary" placeholder="e.g. 192.168.1.100 or 2001:db8::1">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">CIDR Subnet Mask (Optional)</label>
                <input type="number" name="ip_mask" min="1" max="128" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-900 font-mono placeholder-slate-400 outline-hidden focus:border-primary focus:ring-1 focus:ring-primary" placeholder="e.g. 24 for /24 range, leave empty for single IP">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">Rule Mode <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="rounded-xl border border-slate-200 p-3 flex items-start gap-2.5 cursor-pointer hover:border-cyan-400 transition">
                        <input type="radio" name="mode" value="ignore_analytics" checked class="mt-0.5 text-cyan-600 focus:ring-cyan-500">
                        <div>
                            <strong class="text-xs font-bold text-slate-900 block">Ignore Analytics</strong>
                            <span class="text-[10px] text-slate-500 leading-tight block mt-0.5">Allows normal site visits but excludes all pageviews & drafts from analytics.</span>
                        </div>
                    </label>

                    <label class="rounded-xl border border-slate-200 p-3 flex items-start gap-2.5 cursor-pointer hover:border-rose-400 transition">
                        <input type="radio" name="mode" value="block" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                        <div>
                            <strong class="text-xs font-bold text-slate-900 block">Block Access</strong>
                            <span class="text-[10px] text-slate-500 leading-tight block mt-0.5">Denies site access with branded 403 Forbidden page and unblock appeal form.</span>
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Friendly Label / Identity</label>
                <input type="text" name="label" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 outline-hidden focus:border-primary focus:ring-1 focus:ring-primary" placeholder="e.g. Admin Office Fiber, Crawler Bot">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Reason / Notes</label>
                <textarea name="reason" rows="2" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-900 placeholder-slate-400 outline-hidden focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Explanation for administrative records..."></textarea>
            </div>

            <div class="flex items-center justify-between pt-2">
                <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                    <input type="checkbox" name="purge_existing_logs" value="1" checked class="rounded border-slate-300 text-primary focus:ring-primary">
                    <span>Purge past request logs for this IP immediately</span>
                </label>

                <input type="hidden" name="status" value="active">
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeAddRuleModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-100 transition cursor-pointer">Cancel</button>
                <button type="submit" class="btn-desert-animated px-5 py-2 rounded-xl text-xs font-bold text-white shadow-xs cursor-pointer">Save IP Rule</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openAddRuleModal() {
    document.getElementById('addRuleModal').classList.remove('hidden');
}
function closeAddRuleModal() {
    document.getElementById('addRuleModal').classList.add('hidden');
}

async function purgeLogsForIp(ip) {
    if (!confirm(`Are you sure you want to permanently purge all historical telemetry and request logs for IP ${ip}?`)) {
        return;
    }

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch('{{ route("admin.security.ip-rules.purge-logs") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ip_address: ip })
        });

        const data = await res.json();
        alert(data.message);
        window.location.reload();
    } catch (err) {
        alert('Failed purging logs for IP.');
    }
}
</script>
@endpush
@endsection
