@extends('layouts.admin')

@section('page_title', 'Inquiries & Messages')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition flex items-center gap-1">
        <i class="bi bi-house-door-fill text-xs"></i> <span>Dashboard</span>
    </a>
    <i class="bi bi-chevron-right text-[10px] text-slate-400"></i>
    <span class="text-slate-800 font-semibold">Contact Inquiries</span>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Customer Inquiries Hub</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage customer inquiries from website contact forms, review quarantined bot spam, and reply quickly.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" id="btnScanPurgeSpam" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition-all cursor-pointer">
                <i class="bi bi-shield-fill-exclamation text-sm"></i>
                <span>Scan &amp; Clean Spam</span>
            </button>
            <a href="{{ route('admin.settings.google') }}#antiSpamShieldSection" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition-all">
                <i class="bi bi-shield-lock-fill text-amber-400 text-sm"></i>
                <span>Anti-Spam Settings</span>
            </a>
            <a href="{{ route('admin.inquiries.export', request()->query()) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold shadow-2xs transition-all">
                <i class="bi bi-file-earmark-arrow-down text-emerald-600 text-sm"></i>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Inbox vs Spam Quarantine Tabs -->
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-2">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.inquiries.index', ['view' => 'inbox']) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-extrabold transition-all {{ ($viewMode ?? 'inbox') === 'inbox' ? 'bg-primary text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="bi bi-inbox-fill"></i>
                <span>Clean Inbox</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($viewMode ?? 'inbox') === 'inbox' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' }}">{{ number_format($stats['total'] ?? 0) }}</span>
            </a>
            <a href="{{ route('admin.inquiries.index', ['view' => 'quarantine']) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-extrabold transition-all {{ ($viewMode ?? 'inbox') === 'quarantine' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:bg-rose-50 hover:text-rose-700' }}">
                <i class="bi bi-shield-exclamation"></i>
                <span>Spam Quarantine</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($viewMode ?? 'inbox') === 'quarantine' ? 'bg-white/20 text-white' : (($stats['quarantined'] ?? 0) > 0 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600') }}">{{ number_format($stats['quarantined'] ?? 0) }}</span>
            </a>
        </div>
        <div class="flex items-center gap-3 px-2 text-[11px] font-semibold text-slate-500">
            <span class="inline-flex items-center gap-1 text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full">
                <i class="bi bi-shield-check"></i> {{ number_format($spamTelemetry['blocked_24h'] ?? 0) }} bots blocked (24h)
            </span>
            <span class="hidden md:inline-flex items-center gap-1 text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full">
                <i class="bi bi-ban"></i> {{ number_format($spamTelemetry['auto_ip_blocked_7d'] ?? 0) }} IPs auto-blocked (7d)
            </span>
        </div>
    </div>

    <!-- 5 Key Performance Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Clean Inquiries</span>
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <i class="bi bi-envelope-paper-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ number_format($stats['total'] ?? 0) }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Verified human inquiries</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Action Needed</span>
                <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i class="bi bi-exclamation-circle-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-rose-600">{{ number_format($stats['new'] ?? 0) }}</div>
            <span class="text-[11px] font-bold text-rose-600 mt-1 flex items-center gap-1">
                <i class="bi bi-bell-fill"></i> Unread inquiries
            </span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">In Review</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="bi bi-eye-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ number_format($stats['read'] ?? 0) }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Viewed / in progress</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Resolution Rate</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="bi bi-check2-circle text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-emerald-600">{{ $stats['response_rate'] ?? 0 }}%</div>
            <span class="text-[11px] text-slate-400 mt-1 block">{{ $stats['replied'] ?? 0 }} replied to customers</span>
        </div>

        <div class="bg-white rounded-2xl border border-rose-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-rose-500">Spam Shield (7d)</span>
                <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i class="bi bi-shield-fill-check text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-rose-600">{{ number_format($spamTelemetry['blocked_7d'] ?? 0) }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">{{ number_format($stats['quarantined'] ?? 0) }} in quarantine</span>
        </div>
    </div>

    <!-- 2 Interactive Analytics Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-xs font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <i class="bi bi-graph-up text-primary"></i> 14-Day Inquiries Acquisition Trend
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Daily volume of incoming contact requests</p>
                </div>
            </div>
            <div class="h-56 relative w-full">
                <canvas id="inquiriesTrendChart"></canvas>
            </div>
        </div>

        <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-xs font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <i class="bi bi-pie-chart-fill text-amber-500"></i> Status Breakdown
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Resolution pipeline</p>
                </div>
            </div>
            <div class="h-56 relative w-full">
                <canvas id="statusBreakdownChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Multi-Parameter Filter Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4">
        <form method="GET" action="{{ route('admin.inquiries.index') }}">
            <input type="hidden" name="view" value="{{ $viewMode ?? 'inbox' }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                <div class="lg:col-span-4 sm:col-span-2">
                    <label for="inquirySearch" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Search Messages</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="bi bi-search text-xs"></i>
                        </div>
                        <input type="text" name="search" id="inquirySearch" class="w-full rounded-xl border border-slate-200 pl-9 pr-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" placeholder="Name, Email, Subject, Message..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <label for="channelFilter" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Channel</label>
                    <select name="traffic_channel" id="channelFilter" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden bg-white">
                        <option value="">All Channels</option>
                        <option value="Google Ads" {{ request('traffic_channel') === 'Google Ads' ? 'selected' : '' }}>Google Ads</option>
                        <option value="Meta Ads" {{ request('traffic_channel') === 'Meta Ads' ? 'selected' : '' }}>Meta Ads</option>
                        <option value="TikTok Ads" {{ request('traffic_channel') === 'TikTok Ads' ? 'selected' : '' }}>TikTok Ads</option>
                        <option value="Bing Ads" {{ request('traffic_channel') === 'Bing Ads' ? 'selected' : '' }}>Bing Ads</option>
                        <option value="Organic Search" {{ request('traffic_channel') === 'Organic Search' ? 'selected' : '' }}>Organic Search</option>
                        <option value="Social" {{ request('traffic_channel') === 'Social' ? 'selected' : '' }}>Social</option>
                        <option value="Referral" {{ request('traffic_channel') === 'Referral' ? 'selected' : '' }}>Referral</option>
                        <option value="Direct" {{ request('traffic_channel') === 'Direct' ? 'selected' : '' }}>Direct</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label for="statusFilter" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Status</label>
                    <select name="status" id="statusFilter" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden bg-white">
                        <option value="">All Statuses</option>
                        <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>Action Needed (New)</option>
                        <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>In Review (Read)</option>
                        <option value="replied" {{ request('status') === 'replied' ? 'selected' : '' }}>Resolved (Replied)</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label for="fromDate" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">From Date</label>
                    <input type="date" name="from_date" id="fromDate" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ request('from_date') }}">
                </div>

                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition-all" title="Apply Filter">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                    <a href="{{ route('admin.inquiries.index') }}" class="inline-flex items-center justify-center p-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 text-xs font-bold shadow-2xs transition-all" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Inquiries Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700 datatable" id="inquiriesTable">
                <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 no-sort no-export no-colvis w-10">
                            <input type="checkbox" class="rounded border-slate-300 text-primary focus:ring-primary inquiries-select-all" title="Select All">
                        </th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Subject</th>
                        <th class="py-3 px-4">Channel</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right no-sort pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($inquiries as $c)
                    <tr id="inquiry-row-{{ $c->id }}" class="transition-colors {{ $c->status === 'new' ? 'is-unread-inquiry bg-amber-50/70 hover:bg-amber-100/70 border-l-4 border-l-amber-500 font-semibold' : 'hover:bg-slate-50/60' }} inquiry-row" data-id="{{ $c->id }}" data-status="{{ $c->status }}">
                        <td class="py-3 px-4 no-export">
                            <input type="checkbox" class="rounded border-slate-300 text-primary focus:ring-primary inquiries-row-select" value="{{ $c->id }}">
                        </td>
                        <td class="py-3 px-4" data-order="{{ $c->created_at ? $c->created_at->timestamp : 0 }}">
                            <div class="flex items-center gap-1.5">
                                @if($c->status === 'new')
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse shrink-0 unread-dot" title="Action Needed / Unread"></span>
                                @endif
                                <span class="text-xs font-bold text-slate-900">{{ $c->created_at ? $c->created_at->format('M j, Y') : '' }}</span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">
                                {{ $c->created_at ? $c->created_at->format('g:ia') : '' }}
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-xs font-bold text-slate-900">{{ Str::limit($c->name, 75) }}</span>
                                @if($c->is_verified)
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold" title="Email OTP Verified">
                                        <i class="bi bi-patch-check-fill"></i> Verified
                                    </span>
                                @endif
                                @if((int)($c->spam_score ?? 0) > 0)
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full {{ (int)$c->spam_score >= 50 ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200' }} border text-[10px] font-bold" title="{{ is_array($c->spam_reasons) ? implode(', ', $c->spam_reasons) : '' }}">
                                        Risk {{ (int)$c->spam_score }}
                                    </span>
                                @endif
                            </div>
                            <a href="mailto:{{ $c->email }}" class="text-[11px] text-slate-400 hover:text-primary transition">{{ $c->email }}</a>
                        </td>
                        <td class="py-3 px-4">
                            <div class="text-xs font-semibold text-slate-800 line-clamp-1">{{ $c->subject }}</div>
                            <div class="text-[11px] text-slate-400 line-clamp-1">{{ Str::limit($c->message, 60) }}</div>
                            @if(($viewMode ?? 'inbox') === 'quarantine' && is_array($c->spam_reasons) && count($c->spam_reasons) > 0)
                                <div class="text-[10px] text-rose-600 font-semibold mt-0.5 line-clamp-1">
                                    <i class="bi bi-shield-exclamation me-0.5"></i>{{ implode(', ', array_slice($c->spam_reasons, 0, 3)) }}
                                </div>
                            @endif
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap" data-order="{{ $c->traffic_channel ?: 'Direct' }}">
                            @include('partials.admin.channel-badge', ['channel' => $c->traffic_channel ?: ($c->utm_source ?: 'Direct')])
                        </td>
                        <td class="py-3 px-4 text-center status-col" data-order="{{ $c->status }}">
                            @php
                                $badgeClass = match($c->status) {
                                    'new' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'read' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'replied' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'spam' => 'bg-rose-100 text-rose-800 border-rose-300',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                };
                            @endphp
                            <span class="status-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border capitalize {{ $badgeClass }}">
                                {{ $c->status === 'new' ? 'Action Needed' : ($c->status === 'read' ? 'In Review' : ($c->status === 'spam' ? 'Spam Quarantine' : 'Replied')) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right pe-4">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.inquiries.show', $c->id) }}" class="w-8 h-8 rounded-xl border border-slate-200 hover:border-primary hover:text-primary flex items-center justify-center text-slate-600 bg-white transition shadow-2xs btn-view-inquiry" title="View Detail">
                                    <i class="bi bi-eye-fill text-xs"></i>
                                </a>

                                @if(($viewMode ?? 'inbox') === 'quarantine' || $c->is_spam || $c->status === 'spam')
                                    <form action="{{ route('admin.inquiries.restore-spam', $c->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="w-8 h-8 rounded-xl border border-emerald-200 text-emerald-600 hover:bg-emerald-50 flex items-center justify-center bg-white transition shadow-2xs cursor-pointer" title="Not Spam — Restore to Clean Inbox">
                                            <i class="bi bi-arrow-counterclockwise text-xs"></i>
                                        </button>
                                    </form>
                                @else
                                    <button type="button"
                                            class="w-8 h-8 rounded-xl border border-slate-200 hover:border-amber-400 text-slate-500 hover:text-amber-600 flex items-center justify-center bg-white transition shadow-2xs btn-toggle-inquiry-read cursor-pointer"
                                            title="{{ $c->status === 'new' ? 'Mark as Read' : 'Mark as Action Needed' }}"
                                            data-id="{{ $c->id }}"
                                            data-status="{{ $c->status }}">
                                        <i class="bi {{ $c->status === 'new' ? 'bi-envelope' : 'bi-envelope-open' }} text-xs"></i>
                                    </button>

                                    <form action="{{ route('admin.inquiries.status', $c->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="replied">
                                        <button type="submit" class="w-8 h-8 rounded-xl border border-emerald-200 text-emerald-600 hover:bg-emerald-50 flex items-center justify-center bg-white transition shadow-2xs disabled:opacity-40 disabled:cursor-not-allowed" title="Mark Replied" {{ $c->status === 'replied' ? 'disabled' : '' }}>
                                            <i class="bi bi-check-lg text-xs"></i>
                                        </button>
                                    </form>

                                    <button type="button"
                                            class="w-8 h-8 rounded-xl border border-amber-200 text-amber-700 hover:bg-amber-50 flex items-center justify-center bg-white transition shadow-2xs btn-mark-spam-inquiry cursor-pointer"
                                            title="Mark as Spam &amp; Block IP"
                                            data-id="{{ $c->id }}"
                                            data-ip="{{ $c->ip_address ?: 'Unknown' }}">
                                        <i class="bi bi-shield-fill-x text-xs"></i>
                                    </button>
                                @endif

                                <button type="button"
                                        class="w-8 h-8 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 flex items-center justify-center bg-white transition shadow-2xs btn-delete-inquiry"
                                        title="Permanently Delete Inquiry"
                                        data-id="{{ $c->id }}"
                                        data-name="{{ $c->name }}"
                                        data-email="{{ $c->email }}"
                                        data-subject="{{ $c->subject }}">
                                    <i class="bi bi-trash3-fill text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-slate-400">
                            <i class="bi bi-envelope-x text-3xl block mb-2 opacity-50"></i>
                            No contact inquiries match your filter criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Floating Batch Bulk Action Toolbar -->
<div id="inquiriesBulkBar" class="bulk-action-bar">
    <div class="flex items-center gap-2">
        <span class="px-2 py-0.5 bg-primary text-white rounded-full text-xs font-bold"><span id="inquiriesSelectedCount">0</span></span>
        <span class="font-semibold text-xs text-white">selected</span>
    </div>
    <div class="w-px h-5 bg-slate-700"></div>
    <div class="flex items-center gap-1.5 flex-wrap">
        <button type="button" class="px-3 py-1 rounded-full bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition inquiries-bulk-action" data-action="status_read">
            <i class="bi bi-eye text-amber-400 me-1"></i> Mark Read
        </button>
        <button type="button" class="px-3 py-1 rounded-full bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition inquiries-bulk-action" data-action="status_replied">
            <i class="bi bi-check-circle text-emerald-400 me-1"></i> Mark Replied
        </button>
        <button type="button" class="px-3 py-1 rounded-full bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition inquiries-bulk-action" data-action="status_new">
            <i class="bi bi-bell text-rose-400 me-1"></i> Mark New
        </button>
        @if(($viewMode ?? 'inbox') === 'quarantine')
        <button type="button" class="px-3 py-1 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition inquiries-bulk-action" data-action="restore_spam">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Restore Clean
        </button>
        @else
        <button type="button" class="px-3 py-1 rounded-full bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-xs transition inquiries-bulk-action" data-action="mark_spam_and_block">
            <i class="bi bi-shield-fill-x me-1"></i> Spam &amp; Block IP
        </button>
        @endif
        <button type="button" class="px-3 py-1 rounded-full bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition inquiries-bulk-action" data-action="delete">
            <i class="bi bi-trash3 me-1"></i> Delete
        </button>
    </div>
    <button type="button" class="text-slate-400 hover:text-white ms-1 cursor-pointer" id="inquiriesBulkClear" title="Deselect all">
        <i class="bi bi-x-lg"></i>
    </button>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // 14-Day Inquiries Acquisition Trend Chart
    const trendCtx = document.getElementById('inquiriesTrendChart');
    if (trendCtx) {
        const trendData = @json($trendData);
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.date),
                datasets: [{
                    label: 'Inquiries',
                    data: trendData.map(d => d.count),
                    borderColor: '#F69044',
                    backgroundColor: 'rgba(246, 144, 68, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#F69044',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { color: '#f1f5f9' } },
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { stepSize: 1 } }
                }
            }
        });
    }

    // Status Breakdown Doughnut Chart
    const statusCtx = document.getElementById('statusBreakdownChart');
    if (statusCtx) {
        const statusData = @json($statusBreakdown);
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Action Needed (New)', 'In Review (Read)', 'Resolved (Replied)'],
                datasets: [{
                    data: [statusData.new || 0, statusData.read || 0, statusData.replied || 0],
                    backgroundColor: ['#ef4444', '#f59e0b', '#10b981'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } }
                },
                cutout: '65%'
            }
        });
    }

    // Inquiries Batch Bulk Selection & Processing
    const $inqBulkBar = $('#inquiriesBulkBar');
    const $inqSelectAll = $('.inquiries-select-all');
    const $inqCountBadge = $('#inquiriesSelectedCount');

    function updateInqBulkBar() {
        const checkedBoxes = $('.inquiries-row-select:checked');
        const count = checkedBoxes.length;
        $inqCountBadge.text(count);
        if (count > 0) {
            $inqBulkBar.addClass('show');
        } else {
            $inqBulkBar.removeClass('show');
        }
    }

    $inqSelectAll.on('change', function() {
        $('.inquiries-row-select').prop('checked', $(this).is(':checked'));
        updateInqBulkBar();
    });

    $(document).on('change', '.inquiries-row-select', function() {
        const total = $('.inquiries-row-select').length;
        const checked = $('.inquiries-row-select:checked').length;
        $inqSelectAll.prop('checked', total > 0 && total === checked);
        updateInqBulkBar();
    });

    $('#inquiriesBulkClear').on('click', function() {
        $('.inquiries-row-select, .inquiries-select-all').prop('checked', false);
        updateInqBulkBar();
    });

    // Single inquiry delete button trigger
    $(document).on('click', '.btn-delete-inquiry', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const name = $(this).data('name') || 'Customer';
        const email = $(this).data('email') || '';
        const subject = $(this).data('subject') || '';
        promptPermanentDeleteInquiry(id, name, email, subject);
    });

    $('.inquiries-bulk-action').on('click', function(e) {
        e.preventDefault();
        const action = $(this).data('action');
        const selectedIds = $('.inquiries-row-select:checked').map(function() { return $(this).val(); }).get();

        if (selectedIds.length === 0) return;

        const isDelete = action === 'delete';

        if (isDelete) {
            // Step 1: Caution Dialog for Bulk Inquiries Purge
            Swal.fire({
                title: 'CAUTION: Permanent Bulk Inquiries Purge',
                html: `
                    <div class="text-left text-xs text-slate-600">
                        <div class="p-3 mb-3 border border-rose-200 bg-rose-50 text-rose-700 font-semibold rounded-xl">
                            <i class="bi bi-exclamation-triangle-fill mr-1"></i>
                            <strong>IRREVERSIBLE ACTION:</strong> You are about to permanently eradicate <strong class="text-slate-900">${selectedIds.length} contact inquiries</strong> from the database.
                        </div>
                        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 mb-3">
                            <div class="font-bold text-slate-800 mb-1 text-[11px] uppercase">The following records will be permanently erased:</div>
                            <ul class="list-disc pl-4 text-slate-500 space-y-0.5 text-[11px]">
                                <li>All ${selectedIds.length} customer messages, inquiries & sender details</li>
                                <li>All associated visitor telemetry, IP tracking & request logs</li>
                                <li>Complete attribution, UTM tags & analytics footprints</li>
                            </ul>
                        </div>
                        <p class="mb-0 text-slate-500">Are you sure you want to proceed to final verification?</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Proceed to Verification <i class="bi bi-arrow-right ms-1"></i>',
                cancelButtonText: 'Cancel (Keep Inquiries)',
                focusCancel: true
            }).then((step1) => {
                if (!step1.isConfirmed) return;

                // Step 2: Final Safeguard Bulk Purge Double Confirmation
                Swal.fire({
                    title: 'Double Confirmation: Bulk Purge',
                    html: `
                        <div class="text-left text-xs">
                            <p class="text-slate-800 mb-2">To permanently purge all <strong>${selectedIds.length}</strong> selected inquiry record(s) and their analytics footprints, type <span class="inline-block px-2 py-0.5 bg-rose-600 text-white font-mono rounded">DELETE</span> below:</p>
                        </div>
                    `,
                    input: 'text',
                    inputPlaceholder: 'Type DELETE to confirm',
                    inputAttributes: {
                        autocapitalize: 'characters',
                        style: 'text-align: center; font-family: monospace; font-weight: bold; font-size: 1.1rem; letter-spacing: 2px;'
                    },
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#991b1b',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> PURGE ALL SELECTED RECORDS',
                    cancelButtonText: 'Abort',
                    focusCancel: true,
                    showLoaderOnConfirm: true,
                    preConfirm: (inputValue) => {
                        if (inputValue !== 'DELETE') {
                            Swal.showValidationMessage('Verification mismatch! You must type exactly "DELETE" in capital letters to authorize bulk deletion.');
                            return false;
                        }
                        return $.ajax({
                            url: '{{ route("admin.inquiries.bulk") }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                ids: selectedIds,
                                action: 'delete'
                            },
                            headers: { 'Accept': 'application/json' }
                        }).then(response => {
                            if (!response.success) {
                                throw new Error(response.message || 'Failed to process bulk action.');
                            }
                            return response;
                        }).catch(error => {
                            const msg = error.responseJSON ? error.responseJSON.message : error.message;
                            Swal.showValidationMessage(`Bulk Purge Failed: ${msg}`);
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((step2Result) => {
                    if (step2Result.isConfirmed && step2Result.value) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Purged!',
                            text: step2Result.value.message || 'Selected inquiries permanently deleted.',
                            timer: 1600,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                });
            });
            return;
        }

        const actionLabel = 'mark as ' + action.replace('status_', '');

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to ${actionLabel} for ${selectedIds.length} selected inquiry message(s).`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#F69044',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Update',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoader();
                $.ajax({
                    url: '{{ route("admin.inquiries.bulk") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: selectedIds,
                        action: action
                    },
                    success: function(res) {
                        hideLoader();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: res.message || 'Inquiries updated successfully.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        hideLoader();
                        const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error processing bulk action.';
                        Swal.fire('Failed', msg, 'error');
                    }
                });
            }
        });
    });
});

/**
 * 2-Step Cautionary Double Confirmation for Permanent Single Contact Inquiry Deletion
 */
window.promptPermanentDeleteInquiry = function(id, name, email, subject) {
    const verifyTarget = (email && email.trim()) ? email.trim() : (name ? name.trim() : 'CONFIRM');

    // Step 1: Caution Dialog
    Swal.fire({
        title: 'CAUTION: Permanent Inquiry Deletion',
        html: `
            <div class="text-left text-xs text-slate-600">
                <div class="p-3 mb-3 border border-rose-200 bg-rose-50 text-rose-700 font-semibold rounded-xl">
                    <i class="bi bi-exclamation-triangle-fill mr-1"></i>
                    <strong>IRREVERSIBLE ACTION:</strong> You are about to permanently eradicate contact inquiry <strong class="text-slate-900">#${id} (${name})</strong> from the database.
                </div>
                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 mb-3">
                    <div class="font-bold text-slate-800 mb-1 text-[11px] uppercase">The following records will be permanently purged:</div>
                    <ul class="list-disc pl-4 text-slate-500 space-y-0.5 text-[11px]">
                        <li>Contact Inquiry Record (${email || 'No Email'})</li>
                        <li>Customer message content & subject ("${subject || 'Inquiry'}")</li>
                        <li>Visitor Analytics, Telemetry & Request Logs (${id})</li>
                        <li>Client IP & Geolocation Audit Trail</li>
                    </ul>
                </div>
                <p class="mb-0 text-slate-500">Are you sure you want to proceed to the final verification?</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Proceed to Final Confirmation <i class="bi bi-arrow-right ms-1"></i>',
        cancelButtonText: 'Cancel (Keep Inquiry)',
        focusCancel: true
    }).then((step1Result) => {
        if (!step1Result.isConfirmed) return;

        // Step 2: Final Safeguard Double Confirmation
        Swal.fire({
            title: 'Double Confirmation Required',
            html: `
                <div class="text-left text-xs">
                    <p class="text-slate-800 mb-2">To prevent accidental deletion, please type the customer email below to authorize permanent destruction:</p>
                    <div class="text-center my-3">
                        <span class="inline-block px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 font-mono font-bold text-sm">
                            ${verifyTarget}
                        </span>
                    </div>
                </div>
            `,
            input: 'text',
            inputPlaceholder: `Type ${verifyTarget} to confirm`,
            inputAttributes: {
                autocapitalize: 'off',
                autocorrect: 'off',
                autocomplete: 'off',
                style: 'text-align: center; font-family: monospace; font-weight: bold; font-size: 1.1rem; letter-spacing: 1px;'
            },
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#991b1b',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> PERMANENTLY PURGE EVERYTHING',
            cancelButtonText: 'Abort',
            focusCancel: true,
            showLoaderOnConfirm: true,
            preConfirm: (inputValue) => {
                if (!inputValue || inputValue.trim().toLowerCase() !== verifyTarget.toLowerCase()) {
                    Swal.showValidationMessage(`Verification mismatch! You must type exactly "${verifyTarget}" to authorize deletion.`);
                    return false;
                }
                return $.ajax({
                    url: `/admin/inquiries/${id}`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    headers: { 'Accept': 'application/json' }
                }).then(response => {
                    if (!response.success) {
                        throw new Error(response.message || 'Failed to delete inquiry.');
                    }
                    return response;
                }).catch(error => {
                    const msg = error.responseJSON ? error.responseJSON.message : error.message;
                    Swal.showValidationMessage(`Purge Failed: ${msg}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((step2Result) => {
            if (step2Result.isConfirmed && step2Result.value) {
                Swal.fire({
                    icon: 'success',
                    title: 'Purged!',
                    text: step2Result.value.message || 'Inquiry and all analytics footprints permanently deleted.',
                    timer: 1600,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            }
        });
    });
};

// Toggle Inquiry Read/Unread Status (properly scoped outside promptPermanentDeleteInquiry)
$(document).on('click', '.btn-toggle-inquiry-read', function(e) {
    e.preventDefault();
    e.stopPropagation();
    const btn = $(this);
    const id = btn.data('id');
    const currentStatus = btn.attr('data-status') || 'new';
    const targetStatus = currentStatus === 'new' ? 'read' : 'new';

    btn.prop('disabled', true);
    $.ajax({
        url: `/admin/inquiries/${id}/status`,
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            status: targetStatus
        },
        headers: { 'Accept': 'application/json' },
        success: function(res) {
            if (res.success) {
                const row = $(`#inquiry-row-${id}`);
                btn.attr('data-status', targetStatus);
                if (targetStatus === 'read') {
                    row.removeClass('is-unread-inquiry bg-amber-50/70 hover:bg-amber-100/70 border-l-4 border-l-amber-500 font-semibold');
                    row.find('.unread-dot').remove();
                    btn.attr('title', 'Mark as Action Needed');
                    btn.find('i').removeClass('bi-envelope').addClass('bi-envelope-open');
                    row.find('.status-col').attr('data-order', 'read');
                    row.find('.status-badge').removeClass('bg-rose-50 text-rose-700 border-rose-200').addClass('bg-amber-50 text-amber-700 border-amber-200').text('In Review');
                } else {
                    row.addClass('is-unread-inquiry bg-amber-50/70 hover:bg-amber-100/70 border-l-4 border-l-amber-500 font-semibold');
                    if (!row.find('.unread-dot').length) {
                        row.find('td:eq(1) .flex').prepend('<span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse shrink-0 unread-dot" title="Action Needed / Unread"></span>');
                    }
                    btn.attr('title', 'Mark as Read');
                    btn.find('i').removeClass('bi-envelope-open').addClass('bi-envelope');
                    row.find('.status-col').attr('data-order', 'new');
                    row.find('.status-badge').removeClass('bg-amber-50 text-amber-700 border-amber-200').addClass('bg-rose-50 text-rose-700 border-rose-200').text('Action Needed');
                }
                if (res.unread_count !== undefined) {
                    $('.inquiry-unread-count').text(res.unread_count);
                    if (res.unread_count === 0) {
                        $('.inquiry-unread-count').hide();
                    } else {
                        $('.inquiry-unread-count').show();
                    }
                }
            }
        },
        complete: function() {
            btn.prop('disabled', false);
        }
    });
});

// 1-Click Mark Single Inquiry as Spam & Block IP
$(document).on('click', '.btn-mark-spam-inquiry', function(e) {
    e.preventDefault();
    const id = $(this).data('id');
    const ip = $(this).data('ip') || 'Unknown';
    Swal.fire({
        title: 'Mark as Spam & Block IP?',
        html: `<div class="text-xs text-slate-600 text-left">Move Inquiry <strong>#${id}</strong> to Spam Quarantine and add sender IP <code>${ip}</code> to the IP Firewall blocklist?</div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d97706',
        confirmButtonText: '<i class="bi bi-shield-fill-x me-1"></i> Quarantine & Block IP',
        cancelButtonText: 'Cancel',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return $.ajax({
                url: `/admin/inquiries/${id}/mark-spam`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', block_ip: 1 },
                headers: { 'Accept': 'application/json' }
            });
        }
    }).then((res) => {
        if (res.isConfirmed && res.value?.success) {
            Swal.fire({ icon: 'success', title: 'Quarantined!', text: res.value.message, timer: 1600, showConfirmButton: false })
                .then(() => window.location.reload());
        }
    });
});

// 1-Click Scan & Purge Existing Spam (Nov 25 Bot Flood Cleanup Tool)
$('#btnScanPurgeSpam').on('click', function(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Scanning Existing Inquiries...',
        text: 'Running Anti-Spam Intelligence over all stored contact records...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        url: '{{ route("admin.inquiries.scan-purge-spam") }}',
        method: 'POST',
        data: { _token: '{{ csrf_token() }}', mode: 'preview', include_quarantined: 1 },
        headers: { 'Accept': 'application/json' },
        success: function(preview) {
            if (!preview.spam_count) {
                Swal.fire({
                    icon: 'success',
                    title: 'Inbox Is 100% Clean!',
                    text: `Scanned ${preview.scanned_total} inquiry record(s) — zero spam or phishing records detected.`
                });
                return;
            }

            const sampleHtml = (preview.samples || []).map(s => `
                <div class="p-2 rounded-lg bg-rose-50/70 border border-rose-200/80 text-[11px] text-left">
                    <div class="flex justify-between font-bold text-slate-900">
                        <span>#${s.id} · ${s.email}</span>
                        <span class="text-rose-700">Score ${s.score}</span>
                    </div>
                    <div class="text-slate-600 truncate">${s.name}</div>
                    <div class="text-[10px] text-rose-600">IP: ${s.ip_address} · ${(s.reasons || []).join(', ')}</div>
                </div>
            `).join('');

            Swal.fire({
                title: `Found ${preview.spam_count} Spam Record(s)`,
                width: 640,
                html: `
                    <div class="text-left text-xs text-slate-600 space-y-3">
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900">
                            Scanned <strong>${preview.scanned_total}</strong> record(s). Detected <strong>${preview.spam_count}</strong> bot/phishing spam row(s) across <strong>${preview.unique_ips_count}</strong> unique IP address(es). Genuine customer inquiries are untouched.
                        </div>
                        <div class="max-h-56 overflow-y-auto space-y-1.5">${sampleHtml}</div>
                        <label class="flex items-center gap-2 pt-1 font-bold text-slate-800 cursor-pointer">
                            <input type="checkbox" id="scanBlockIpsCheck" checked class="rounded text-rose-600">
                            <span>Also auto-block all ${preview.unique_ips_count} offending bot IP(s) in IP Firewall</span>
                        </label>
                    </div>
                `,
                icon: 'warning',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                denyButtonColor: '#d97706',
                confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Permanently Purge All Spam',
                denyButtonText: '<i class="bi bi-shield-exclamation me-1"></i> Move to Quarantine Only',
                cancelButtonText: 'Cancel'
            }).then((choice) => {
                if (!choice.isConfirmed && !choice.isDenied) return;
                const targetMode = choice.isConfirmed ? 'purge' : 'quarantine';
                const blockIps = document.getElementById('scanBlockIpsCheck')?.checked ? 1 : 0;

                Swal.fire({ title: 'Cleaning Spam...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: '{{ route("admin.inquiries.scan-purge-spam") }}',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', mode: targetMode, block_ips: blockIps, include_quarantined: 1 },
                    headers: { 'Accept': 'application/json' },
                    success: function(res) {
                        Swal.fire({ icon: 'success', title: 'Cleanup Complete!', text: res.message })
                            .then(() => window.location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Cleanup failed.', 'error');
                    }
                });
            });
        },
        error: function(xhr) {
            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to scan inquiries.', 'error');
        }
    });
});
</script>
@endpush
@endsection
