@extends('layouts.admin')

@section('page_title', 'WhatsApp Leads & Analytics')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition flex items-center gap-1">
        <i class="bi bi-house-door-fill text-xs"></i> <span>Dashboard</span>
    </a>
    <i class="bi bi-chevron-right text-[10px] text-slate-400"></i>
    <span class="text-slate-800 font-semibold">WhatsApp Leads</span>
@endsection

@section('content')
<div class="space-y-6" x-data>
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi bi-whatsapp text-emerald-500"></i> WhatsApp Leads Hub & Analytics
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Track real-time chat click leads, customer phone numbers, tour preferences, and visitor telemetry.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.whatsapp.export', request()->query()) }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-emerald-200 hover:bg-emerald-50 text-emerald-700 font-bold text-xs shadow-2xs transition">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
            </a>
            <a href="{{ route('admin.whatsapp.settings') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-2xs transition">
                <i class="bi bi-gear-fill text-primary"></i> Settings
            </a>
        </div>
    </div>

    <!-- 4 Key Performance Metric Cards -->
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total WhatsApp Leads</span>
                <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-200">
                    <i class="bi bi-whatsapp text-lg"></i>
                </span>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="text-xs text-slate-400 mt-1">All-time chat click inquiries</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Today's Leads</span>
                <span class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
                    <i class="bi bi-calendar2-day-fill text-lg"></i>
                </span>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900">{{ number_format($stats['today'] ?? 0) }}</div>
                <div class="text-xs font-bold text-emerald-600 mt-1 flex items-center gap-1">
                    <i class="bi bi-lightning-fill"></i> Active today
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">This Month</span>
                <span class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-200">
                    <i class="bi bi-graph-up text-lg"></i>
                </span>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900">{{ number_format($stats['this_month'] ?? 0) }}</div>
                <div class="text-xs text-slate-400 mt-1">Current month acquisition</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Mobile Traffic %</span>
                <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center border border-amber-200">
                    <i class="bi bi-phone-fill text-lg"></i>
                </span>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900">{{ $stats['mobile_pct'] ?? 0 }}%</div>
                <div class="text-xs text-slate-400 mt-1">Mobile vs desktop users</div>
            </div>
        </div>
    </div>

    <!-- 2 Interactive Analytics Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-graph-up-arrow text-emerald-500"></i> 14-Day WhatsApp Leads Trend
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Daily volume of customer WhatsApp inquiries</p>
                </div>
            </div>
            <div class="h-56 relative">
                <canvas id="leadsTrendChart"></canvas>
            </div>
        </div>
        <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-pie-chart-fill text-primary"></i> Tour Interest
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Top inquired experiences</p>
                </div>
            </div>
            <div class="h-56 relative">
                <canvas id="tourBreakdownChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Multi-Parameter Filter Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
        <form method="GET" action="{{ route('admin.whatsapp.leads') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="lg:col-span-3">
                <label for="leadSearch" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Search Leads</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" id="leadSearch" class="w-full rounded-xl border border-slate-200 pl-10 pr-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden focus:border-primary transition" placeholder="Name, Phone, Tour..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="lg:col-span-2">
                <label for="statusFilter" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Status</label>
                <select name="status" id="statusFilter" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-xs text-slate-800 outline-hidden bg-white focus:border-primary">
                    <option value="">All Leads</option>
                    <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>Action Needed (New)</option>
                    <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Read</option>
                </select>
            </div>
            <div class="lg:col-span-2">
                <label for="tourFilter" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Tour Package</label>
                <select name="tour_name" id="tourFilter" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-xs text-slate-800 outline-hidden bg-white focus:border-primary">
                    <option value="">All Tours</option>
                    @foreach($availableTours as $tName)
                        <option value="{{ $tName }}" {{ request('tour_name') === $tName ? 'selected' : '' }}>{{ $tName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-2">
                <label for="deviceFilter" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Device Type</label>
                <select name="device_type" id="deviceFilter" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-xs text-slate-800 outline-hidden bg-white focus:border-primary">
                    <option value="">All Devices</option>
                    <option value="mobile" {{ request('device_type') === 'mobile' ? 'selected' : '' }}>Mobile</option>
                    <option value="desktop" {{ request('device_type') === 'desktop' ? 'selected' : '' }}>Desktop</option>
                    <option value="tablet" {{ request('device_type') === 'tablet' ? 'selected' : '' }}>Tablet</option>
                </select>
            </div>
            <div class="lg:col-span-1">
                <label for="fromDate" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">From Date</label>
                <input type="date" name="from_date" id="fromDate" class="w-full rounded-xl border border-slate-200 px-2.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" value="{{ request('from_date') }}">
            </div>
            <div class="lg:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition cursor-pointer">
                    <i class="bi bi-funnel-fill"></i> Filter
                </button>
                <a href="{{ route('admin.whatsapp.leads') }}" class="inline-flex items-center justify-center px-3.5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold transition" title="Reset Filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- WhatsApp Leads Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700 datatable" id="whatsappLeadsTable">
                <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 no-sort no-export no-colvis w-10">
                            <input type="checkbox" class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4 whatsapp-select-all" title="Select All">
                        </th>
                        <th class="py-3 px-4">Date & Time</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Acquisition & Stay</th>
                        <th class="py-3 px-4">Interest Context</th>
                        <th class="py-3 px-4">Message Snippet</th>
                        <th class="py-3 px-4">Location</th>
                        <th class="py-3 px-4">Device</th>
                        <th class="py-3 px-4 text-right no-sort pe-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($leads as $lead)
                    @php
                        $isUnread = empty($lead->is_read) || $lead->status === 'new';
                        $channel = $lead->traffic_channel ?: ($lead->utm_source ?: 'Direct');
                        $durationSec = (int) ($lead->session_duration_seconds ?? 0);
                        $pagesViewed = (int) ($lead->pages_viewed_count ?? 1);
                        $mins = floor($durationSec / 60);
                        $secs = $durationSec % 60;
                        $stayStr = $durationSec > 0 ? ($mins > 0 ? "{$mins}m {$secs}s" : "{$secs}s") : '<15s';
                    @endphp
                    <tr id="whatsapp-row-{{ $lead->id }}" class="transition-colors {{ $isUnread ? 'is-unread-lead bg-emerald-50/50 hover:bg-emerald-100/50 border-l-4 border-l-emerald-500 font-semibold' : 'hover:bg-slate-50/60' }} whatsapp-row" data-id="{{ $lead->id }}">
                        <td class="py-3 px-4 no-export">
                            <input type="checkbox" class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4 whatsapp-row-select" value="{{ $lead->id }}">
                        </td>
                        <td class="py-3 px-4" data-order="{{ \Carbon\Carbon::parse($lead->created_at)->timestamp }}">
                            <div class="text-xs font-bold text-slate-900">
                                {{ \Carbon\Carbon::parse($lead->created_at)->format('M j, Y') }}
                            </div>
                            <div class="text-[11px] text-slate-400">
                                {{ \Carbon\Carbon::parse($lead->created_at)->format('g:ia') }}
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                @if($isUnread)
                                    <span class="unread-badge inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300 uppercase tracking-wide">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> New
                                    </span>
                                @endif
                                @if(!empty($lead->prospect_source) && $lead->prospect_source !== 'Direct Click')
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9px] font-extrabold bg-blue-100 text-blue-800 border border-blue-200 uppercase tracking-wide" title="{{ $lead->prospect_source }}">
                                        <i class="bi bi-person-check-fill"></i> {{ Str::limit($lead->prospect_source, 18) }}
                                    </span>
                                @endif
                                <span class="font-bold text-slate-900 text-xs">{{ $lead->name ?: 'Visitor' }}</span>
                            </div>
                            <div class="text-emerald-600 font-mono font-bold text-xs mt-0.5">
                                {{ $lead->phone }}
                            </div>
                            @if(!empty($lead->email))
                                <div class="text-[11px] text-slate-400 truncate max-w-[160px]" title="{{ $lead->email }}">
                                    {{ $lead->email }}
                                </div>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex flex-col gap-1 items-start">
                                @if(str_contains(strtolower($channel), 'google ads'))
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300">
                                        <i class="bi bi-google"></i> {{ $channel }}
                                    </span>
                                @elseif(str_contains(strtolower($channel), 'google'))
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200">
                                        <i class="bi bi-google"></i> Google
                                    </span>
                                @elseif(str_contains(strtolower($channel), 'instagram'))
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-pink-50 text-pink-800 border border-pink-200">
                                        <i class="bi bi-instagram"></i> Instagram
                                    </span>
                                @elseif(str_contains(strtolower($channel), 'facebook'))
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i class="bi bi-facebook"></i> Facebook
                                    </span>
                                @elseif(str_contains(strtolower($channel), 'tiktok'))
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">
                                        <i class="bi bi-tiktok"></i> TikTok
                                    </span>
                                @elseif(str_contains(strtolower($channel), 'youtube'))
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i class="bi bi-youtube"></i> YouTube
                                    </span>
                                @elseif(str_contains(strtolower($channel), 'referral'))
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200" title="{{ $lead->initial_referrer }}">
                                        <i class="bi bi-box-arrow-up-right"></i> {{ Str::limit($channel, 18) }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                        Direct Visit
                                    </span>
                                @endif
                                <div class="text-[11px] font-mono text-slate-500 flex items-center gap-1" title="Active dwell time & pages viewed">
                                    <i class="bi bi-stopwatch text-slate-400"></i> {{ $stayStr }} <span class="text-slate-300">•</span> {{ $pagesViewed }} {{ Str::plural('page', $pagesViewed) }}
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/20">
                                {{ $lead->tour_name ?: 'General Inquiry' }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="text-slate-500 text-xs truncate max-w-xs" title="{{ $lead->message_text }}">
                                {{ Str::limit($lead->message_text, 65) }}
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="text-xs font-semibold text-slate-700 flex items-center gap-1">
                                <i class="bi bi-geo-alt-fill text-rose-500"></i> {{ $lead->city ?: 'Unknown' }}, {{ $lead->country ?: '' }}
                            </div>
                            @if(!empty($lead->isp) && $lead->isp !== 'Not Available')
                                <div class="text-[10px] text-slate-400 truncate max-w-[140px]" title="{{ $lead->isp }}">
                                    {{ Str::limit($lead->isp, 20) }}
                                </div>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 capitalize">
                                {{ $lead->device_type ?: 'Desktop' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right pe-4">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.bookings.create', ['name' => $lead->name ?: '', 'phone' => $lead->phone ?: '', 'tour_name' => $lead->tour_name ?: '', 'special_requests' => $lead->message_text ?: '']) }}"
                                   class="w-8 h-8 rounded-xl border border-emerald-200 text-emerald-600 hover:bg-emerald-50 flex items-center justify-center bg-white transition shadow-2xs"
                                   title="Convert WhatsApp Lead to Booking">
                                    <i class="bi bi-calendar-plus text-xs"></i>
                                </a>
                                <button type="button" 
                                        class="w-8 h-8 rounded-xl border border-slate-200 hover:border-emerald-500 text-slate-500 hover:text-emerald-600 flex items-center justify-center bg-white transition shadow-2xs btn-toggle-lead-read cursor-pointer" 
                                        title="{{ $isUnread ? 'Mark as Read' : 'Mark as Unread' }}"
                                        data-id="{{ $lead->id }}">
                                    <i class="bi {{ $isUnread ? 'bi-envelope' : 'bi-envelope-open' }} text-xs"></i>
                                </button>
                                <button class="w-8 h-8 rounded-xl border border-slate-200 hover:border-primary hover:text-primary flex items-center justify-center text-slate-600 bg-white transition shadow-2xs view-lead-btn cursor-pointer" 
                                        title="View Full Lead Details"
                                        data-id="{{ $lead->id }}"
                                        data-name="{{ $lead->name }}"
                                        data-phone="{{ $lead->phone }}"
                                        data-email="{{ $lead->email ?? '' }}"
                                        data-context="{{ $lead->tour_name ?: 'General Inquiry' }}"
                                        data-url="{{ $lead->page_url }}"
                                        data-msg="{{ $lead->message_text }}"
                                        data-ip="{{ $lead->client_ip ?? $lead->ip_address ?? 'Not Available' }}"
                                        data-location="{{ ($lead->city ?? 'Unknown') . ', ' . ($lead->country ?? '') }}"
                                        data-device="{{ ucfirst($lead->device_type ?? '-') }} ({{ $lead->os_name ?? '-' }} / {{ $lead->browser_name ?? '-' }})"
                                        data-isp="{{ $lead->isp ?: ($lead->organization ?: 'Not Available') }}"
                                        data-prospect-source="{{ $lead->prospect_source ?: 'Direct Click' }}"
                                        data-attribution="{{ ($lead->utm_source ?: 'Direct') . ($lead->utm_campaign && $lead->utm_campaign !== 'Not Available' ? ' (' . $lead->utm_campaign . ')' : '') }}"
                                        data-screen="{{ ($lead->screen_res ?: '-') . ' • ' . ($lead->client_timezone ?: '-') }}"
                                        data-traffic-channel="{{ $lead->traffic_channel ?: ($lead->utm_source ?: 'Direct') }}"
                                        data-initial-referrer="{{ $lead->initial_referrer ?: ($lead->referrer_url ?: 'Direct / None') }}"
                                        data-landing-page="{{ $lead->landing_page ?: ($lead->page_url ?: '-') }}"
                                        data-duration="{{ (int) ($lead->session_duration_seconds ?? 0) }}"
                                        data-pages-count="{{ (int) ($lead->pages_viewed_count ?? 1) }}"
                                        data-journey="{{ is_string($lead->journey_timeline) ? $lead->journey_timeline : json_encode($lead->journey_timeline ?? []) }}"
                                        data-gclid="{{ $lead->gclid ?? '' }}"
                                        data-fbclid="{{ $lead->fbclid ?? '' }}">
                                    <i class="bi bi-search text-xs"></i>
                                </button>
                                <button type="button" class="w-8 h-8 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 flex items-center justify-center bg-white transition shadow-2xs btn-delete-lead cursor-pointer" 
                                        title="Permanently Delete WhatsApp Lead & All Footprints"
                                        onclick="promptPermanentDeleteLead({{ $lead->id }}, '{{ addslashes($lead->name ?: 'Visitor') }}', '{{ addslashes($lead->phone ?: '') }}')">
                                    <i class="bi bi-trash3 text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-12 text-slate-400 text-xs">
                            <i class="bi bi-whatsapp text-4xl block mb-2 opacity-50"></i>
                            No WhatsApp leads found matching your criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Floating Batch Bulk Action Toolbar -->
    <div id="whatsappBulkBar" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-40 bg-slate-900/90 backdrop-blur-md text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-4 transition-all duration-300 border border-slate-700">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-500 text-white" id="whatsappSelectedCount">0</span>
            <span class="text-xs font-semibold text-white">leads selected</span>
        </div>
        <div class="w-px h-5 bg-slate-700"></div>
        <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition cursor-pointer btn-whatsapp-bulk-read" data-action="mark_read">
            <i class="bi bi-eye text-emerald-400"></i> Mark Read
        </button>
        <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition cursor-pointer btn-whatsapp-bulk-read" data-action="mark_unread">
            <i class="bi bi-bell text-amber-400"></i> Mark Unread
        </button>
        <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition cursor-pointer" id="whatsappBulkDelete">
            <i class="bi bi-trash3"></i> Delete Selected
        </button>
        <button type="button" class="text-slate-400 hover:text-white transition cursor-pointer text-xs" id="whatsappBulkClear" title="Deselect all">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
</div>

<!-- Modal: View Details (Alpine.js) -->
<div x-data="{ open: false }" @open-lead-modal.window="open = true" @close-lead-modal.window="open = false" x-show="open" x-cloak class="relative z-50">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-16 flex items-center justify-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-2xl w-full p-6 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-200">
                        <i class="bi bi-whatsapp text-base"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">WhatsApp Lead Telemetry & Details</h3>
                        <div class="text-[11px] text-slate-400">Captured click-to-chat inquiry footprint</div>
                    </div>
                </div>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Client Details -->
                <div class="p-4 bg-slate-50/70 rounded-2xl border border-slate-200/80 space-y-3">
                    <h4 class="text-xs font-black uppercase text-emerald-600 tracking-wider flex items-center gap-1.5">
                        <i class="bi bi-person-fill"></i> Customer Identity & Prospect Note
                    </h4>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Customer Name</div>
                        <div class="text-sm font-black text-slate-900" id="modalCustomerName">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Phone Number</div>
                        <div class="text-xs font-mono font-bold text-emerald-600" id="modalCustomerPhone">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Email Address</div>
                        <div class="text-xs font-mono text-slate-700" id="modalCustomerEmail">Not Available</div>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Interest Context</div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-primary text-white mt-0.5" id="modalTourName">-</span>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Prospect Identity Source</div>
                        <div class="text-xs font-semibold text-slate-700" id="modalProspectSource">Direct Click</div>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Source Page</div>
                        <a href="#" target="_blank" rel="noopener" class="text-xs text-primary hover:underline truncate block" id="modalPageUrl">-</a>
                    </div>
                </div>

                <!-- Telemetry -->
                <div class="p-4 bg-slate-50/70 rounded-2xl border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-black uppercase text-primary tracking-wider flex items-center gap-1.5">
                            <i class="bi bi-geo-alt-fill"></i> Visitor Telemetry & Network
                        </h4>
                        <button type="button" id="btnRefreshLeadTelemetry" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold text-slate-600 bg-white hover:text-primary hover:border-primary border border-slate-200 rounded-lg shadow-2xs transition cursor-pointer" title="Re-query GeoIP & Refresh Network Telemetry">
                            <i class="bi bi-arrow-clockwise text-xs" id="refreshTelemetryIcon"></i> <span>Refresh</span>
                        </button>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Client IP Address</div>
                        <div class="text-xs font-mono font-bold text-slate-800" id="modalClientIp">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Estimated Location</div>
                        <div class="text-xs font-bold text-slate-800" id="modalLocation">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Network Carrier / ISP</div>
                        <div class="text-xs font-semibold text-slate-700" id="modalIsp">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Device & Browser Environment</div>
                        <div class="text-xs font-medium text-slate-700" id="modalDevice">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Screen & Timezone</div>
                        <div class="text-xs font-mono text-slate-600" id="modalScreen">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Marketing Attribution</div>
                        <div class="text-xs font-semibold text-emerald-700" id="modalAttribution">Direct</div>
                    </div>
                </div>

                <!-- Buyer Journey, Multi-Page Navigation & Active Dwell Time -->
                <div class="sm:col-span-2 p-4 bg-slate-50/70 rounded-2xl border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <h4 class="text-xs font-black uppercase text-amber-600 tracking-wider flex items-center gap-1.5">
                            <i class="bi bi-signpost-split-fill"></i> Buyer Journey, Multi-Page Navigation & Dwell Time
                        </h4>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-amber-100 text-amber-900 border border-amber-300" id="modalSessionDuration">
                                <i class="bi bi-stopwatch"></i> Total Stay: <15s
                            </span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-200 text-slate-700" id="modalPagesCount">
                                1 Page Visited
                            </span>
                        </div>
                    </div>

                    <!-- Acquisition Summary Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <div class="p-2.5 bg-white rounded-xl border border-slate-200/80">
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Acquisition Channel</div>
                            <div class="text-xs font-bold text-slate-900 mt-0.5" id="modalTrafficChannel">Direct</div>
                        </div>
                        <div class="p-2.5 bg-white rounded-xl border border-slate-200/80">
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Original Referrer</div>
                            <div class="text-xs font-semibold text-slate-700 truncate mt-0.5" id="modalInitialReferrer">Direct / None</div>
                        </div>
                        <div class="p-2.5 bg-white rounded-xl border border-slate-200/80">
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Initial Landing Page</div>
                            <div class="text-xs font-semibold text-primary truncate mt-0.5" id="modalLandingPage">-</div>
                        </div>
                    </div>

                    <!-- Paid Ad Click Identifiers (GCLID / FBCLID) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" id="modalClickIdsWrapper">
                        <div class="p-2 bg-amber-50/70 border border-amber-200 rounded-xl" id="modalGclidRow" style="display:none;">
                            <div class="text-[10px] font-extrabold text-amber-800 uppercase tracking-wider flex items-center gap-1">
                                <i class="bi bi-google"></i> Google Ads Click ID (GCLID)
                            </div>
                            <div class="text-[11px] font-mono text-slate-700 truncate mt-0.5" id="modalGclidVal">-</div>
                        </div>
                        <div class="p-2 bg-blue-50/70 border border-blue-200 rounded-xl" id="modalFbclidRow" style="display:none;">
                            <div class="text-[10px] font-extrabold text-blue-800 uppercase tracking-wider flex items-center gap-1">
                                <i class="bi bi-facebook"></i> Meta / Facebook Click ID (FBCLID)
                            </div>
                            <div class="text-[11px] font-mono text-slate-700 truncate mt-0.5" id="modalFbclidVal">-</div>
                        </div>
                    </div>

                    <!-- Step-by-Step Browsing Path Timeline -->
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                            Step-by-Step Pages Browsed & High-Value Sections Explored:
                        </div>
                        <div class="space-y-2 max-h-56 overflow-y-auto pr-1" id="modalJourneyTimeline">
                            <!-- Rendered dynamically via JS -->
                        </div>
                    </div>
                </div>

                <!-- Message text -->
                <div class="sm:col-span-2 p-4 bg-slate-50/70 rounded-2xl border border-slate-200/80 space-y-1.5">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Prefilled Customer Message</div>
                    <p class="text-xs text-slate-800 p-3 bg-white border border-slate-200 rounded-xl leading-relaxed whitespace-pre-wrap" id="modalMessageText">-</p>
                </div>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                <button type="button" class="inline-flex items-center gap-1 px-3.5 py-2 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold transition cursor-pointer" id="modalDeleteLeadBtn">
                    <i class="bi bi-trash3"></i> Delete Lead
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold cursor-pointer">Close</button>
                    <a href="#" id="modalDirectChatBtn" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition">
                        <i class="bi bi-whatsapp"></i> Open Chat on WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // 14-Day Acquisition Trend Chart
    const trendCtx = document.getElementById('leadsTrendChart');
    if (trendCtx) {
        const trendData = @json($trendData);
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.date),
                datasets: [{
                    label: 'WhatsApp Leads',
                    data: trendData.map(d => d.count),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#10b981',
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

    // Tour Breakdown Doughnut Chart
    const tourCtx = document.getElementById('tourBreakdownChart');
    if (tourCtx) {
        const tourData = @json($tourBreakdown);
        new Chart(tourCtx, {
            type: 'doughnut',
            data: {
                labels: tourData.map(t => t.tour_label.length > 20 ? t.tour_label.substring(0, 20) + '...' : t.tour_label),
                datasets: [{
                    data: tourData.map(t => t.count),
                    backgroundColor: ['#F58F43', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899'],
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

    // View Details Modal Handler (Delegated for pagination, sorting & dynamic search)
    $(document).on('click', '.view-lead-btn', function() {
        const btn = $(this);
        const name = String(btn.attr('data-name') || btn.data('name') || 'Visitor').trim();
        const rawPhone = btn.attr('data-phone') !== undefined ? btn.attr('data-phone') : btn.data('phone');
        const phone = (rawPhone !== undefined && rawPhone !== null && String(rawPhone).trim() !== '') ? String(rawPhone).trim() : '';
        const context = String(btn.attr('data-context') || btn.data('context') || 'General Inquiry').trim();
        const url = String(btn.attr('data-url') || btn.data('url') || '#').trim();
        const msg = String(btn.attr('data-msg') || btn.data('msg') || 'No message text provided.').trim();
        const ip = String(btn.attr('data-ip') || btn.data('ip') || '-').trim();
        const loc = String(btn.attr('data-location') || btn.data('location') || 'Unknown Location').trim();
        const device = String(btn.attr('data-device') || btn.data('device') || '-').trim();
        const email = String(btn.attr('data-email') || btn.data('email') || '').trim();
        const isp = String(btn.attr('data-isp') || btn.data('isp') || 'Not Available').trim();
        const prospectSource = String(btn.attr('data-prospect-source') || btn.data('prospect-source') || 'Direct Click').trim();
        const attribution = String(btn.attr('data-attribution') || btn.data('attribution') || 'Direct').trim();
        const screen = String(btn.attr('data-screen') || btn.data('screen') || '-').trim();
        const trafficChannel = String(btn.attr('data-traffic-channel') || btn.data('traffic-channel') || 'Direct').trim();
        const initialReferrer = String(btn.attr('data-initial-referrer') || btn.data('initial-referrer') || 'Direct / None').trim();
        const landingPage = String(btn.attr('data-landing-page') || btn.data('landing-page') || '-').trim();
        const durationSec = parseInt(btn.attr('data-duration') || btn.data('duration') || 0, 10);
        const pagesCount = parseInt(btn.attr('data-pages-count') || btn.data('pages-count') || 1, 10);
        const rawJourney = btn.attr('data-journey') || btn.data('journey');
        const gclid = String(btn.attr('data-gclid') || btn.data('gclid') || '').trim();
        const fbclid = String(btn.attr('data-fbclid') || btn.data('fbclid') || '').trim();

        $('#modalCustomerName').text(name);
        $('#modalCustomerPhone').text(phone || 'No Phone');
        $('#modalCustomerEmail').text(email || 'Not Available');
        $('#modalTourName').text(context);
        $('#modalProspectSource').text(prospectSource);
        $('#modalPageUrl').attr('href', url).text(url);
        $('#modalClientIp').text(ip);
        $('#modalLocation').text(loc);
        $('#modalIsp').text(isp);
        $('#modalDevice').text(device);
        $('#modalScreen').text(screen);
        $('#modalAttribution').text(attribution);
        $('#modalMessageText').text(msg);

        // Journey and Attribution summary
        const mins = Math.floor(durationSec / 60);
        const secs = durationSec % 60;
        const stayStr = durationSec > 0 ? (mins > 0 ? `${mins}m ${secs}s` : `${secs}s`) : '<15s';
        $('#modalSessionDuration').html(`<i class="bi bi-stopwatch"></i> Total Stay: ${stayStr}`);
        $('#modalPagesCount').text(`${pagesCount} ${pagesCount === 1 ? 'Page' : 'Pages'} Visited`);

        $('#modalTrafficChannel').text(trafficChannel);
        
        if (initialReferrer.startsWith('http')) {
            $('#modalInitialReferrer').html(`<a href="${initialReferrer}" target="_blank" rel="noopener" class="text-primary hover:underline">${initialReferrer}</a>`);
        } else {
            $('#modalInitialReferrer').text(initialReferrer);
        }

        if (landingPage.startsWith('http') || landingPage.startsWith('/')) {
            $('#modalLandingPage').html(`<a href="${landingPage}" target="_blank" rel="noopener" class="text-primary hover:underline">${landingPage}</a>`);
        } else {
            $('#modalLandingPage').text(landingPage);
        }

        // Paid Click Identifiers
        if (gclid) {
            $('#modalGclidRow').show();
            $('#modalGclidVal').text(gclid);
        } else {
            $('#modalGclidRow').hide();
        }

        if (fbclid) {
            $('#modalFbclidRow').show();
            $('#modalFbclidVal').text(fbclid);
        } else {
            $('#modalFbclidRow').hide();
        }

        // Parse and render timeline steps
        let timeline = [];
        try {
            timeline = typeof rawJourney === 'object' ? rawJourney : JSON.parse(rawJourney);
        } catch (e) {
            timeline = [];
        }

        const timelineContainer = $('#modalJourneyTimeline');
        timelineContainer.empty();

        if (Array.isArray(timeline) && timeline.length > 0) {
            timeline.forEach((step, idx) => {
                const stepNum = idx + 1;
                const pageTitle = step.title || step.url || `Page ${stepNum}`;
                const pagePath = step.url || '/';
                const stepSecs = parseInt(step.duration_seconds || 0, 10);
                const sMins = Math.floor(stepSecs / 60);
                const sSecs = stepSecs % 60;
                const stepStay = stepSecs > 0 ? (sMins > 0 ? `${sMins}m ${sSecs}s` : `${sSecs}s`) : 'Brief glance';
                const sections = Array.isArray(step.sections) ? step.sections : [];

                let sectionsBadges = '';
                if (sections.length > 0) {
                    sectionsBadges = `
                        <div class="flex items-center gap-1.5 flex-wrap mt-1">
                            <span class="text-[9px] uppercase font-bold text-slate-400">Viewed Sections:</span>
                            ${sections.map(s => `<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-900 border border-amber-200/80"><i class="bi bi-eye-fill me-1 text-[9px] text-amber-600"></i>${s}</span>`).join('')}
                        </div>
                    `;
                }

                timelineContainer.append(`
                    <div class="flex items-start gap-3 p-2.5 rounded-xl bg-white border border-slate-200/70 shadow-2xs">
                        <div class="w-6 h-6 rounded-full bg-slate-900 text-white flex items-center justify-center text-xs font-black shrink-0 mt-0.5">
                            ${stepNum}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <span class="text-xs font-bold text-slate-900 truncate">${pageTitle}</span>
                                <span class="text-[10px] font-mono font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full"><i class="bi bi-clock"></i> ${stepStay}</span>
                            </div>
                            <div class="text-[11px] text-primary truncate font-mono mt-0.5">${pagePath}</div>
                            ${sectionsBadges}
                        </div>
                    </div>
                `);
            });
        } else {
            timelineContainer.html('<div class="text-xs text-slate-400 italic p-2 bg-white rounded-xl border border-slate-200/70">Single-page direct inquiry (Direct landing)</div>');
        }

        if (phone && phone !== 'No Phone') {
            const cleanPhone = String(phone).replace(/[^0-9]/g, '');
            if (cleanPhone) {
                const chatMsg = encodeURIComponent('Hi ' + name + '! Thanks for reaching out to Dunes Discovery Tourism regarding ' + context + '. How can we assist you?');
                $('#modalDirectChatBtn').attr('href', `https://wa.me/${cleanPhone}?text=${chatMsg}`).show();
            } else {
                $('#modalDirectChatBtn').hide();
            }
        } else {
            $('#modalDirectChatBtn').hide();
        }

        const id = btn.attr('data-id') || btn.data('id');
        $('#modalDeleteLeadBtn').off('click').on('click', function() {
            window.dispatchEvent(new CustomEvent('close-lead-modal'));
            promptPermanentDeleteLead(id, name, phone);
        });

        // Refresh / Re-lookup Visitor Telemetry on-demand
        $('#btnRefreshLeadTelemetry').off('click').on('click', function() {
            const btnRefresh = $(this);
            const icon = $('#refreshTelemetryIcon');
            btnRefresh.prop('disabled', true).addClass('opacity-60 cursor-not-allowed');
            icon.addClass('spin');

            $.ajax({
                url: `/admin/whatsapp-leads/${id}/refresh-telemetry`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    client_ip: (ip && ip !== 'Not Available' && ip !== '-') ? ip : ''
                },
                headers: { 'Accept': 'application/json' },
                success: function(res) {
                    if (res.success && res.lead) {
                        $('#modalClientIp').text(res.lead.client_ip);
                        $('#modalLocation').text(res.lead.location);
                        $('#modalIsp').text(res.lead.isp);

                        // Update trigger button data attributes
                        btn.attr('data-ip', res.lead.client_ip);
                        btn.attr('data-location', res.lead.location);
                        btn.attr('data-isp', res.lead.isp);

                        // Update table row cells
                        const tableRow = $(`#whatsapp-row-${id}`);
                        if (tableRow.length) {
                            tableRow.find('td:nth-child(5) .font-semibold').html(`<i class="bi bi-geo-alt-fill text-rose-500"></i> ${res.lead.location}`);
                            if (res.lead.isp && res.lead.isp !== 'Not Available') {
                                tableRow.find('td:nth-child(5) .text-\\[10px\\]').text(res.lead.isp);
                            }
                        }
                    }
                },
                complete: function() {
                    btnRefresh.prop('disabled', false).removeClass('opacity-60 cursor-not-allowed');
                    icon.removeClass('spin');
                }
            });
        });

        // Auto mark as read on view details
        const row = $(`#whatsapp-row-${id}`);
        if (row.hasClass('is-unread-lead')) {
            $.ajax({
                url: `/admin/whatsapp-leads/${id}/mark-read`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                headers: { 'Accept': 'application/json' },
                success: function(res) {
                    if (res.success) {
                        row.removeClass('is-unread-lead bg-emerald-50/50 hover:bg-emerald-100/50 border-l-4 border-l-emerald-500 font-semibold');
                        row.find('.unread-badge').remove();
                        row.find('.btn-toggle-lead-read').attr('title', 'Mark as Unread');
                        row.find('.btn-toggle-lead-read i').removeClass('bi-envelope').addClass('bi-envelope-open');
                        if (res.unread_count !== undefined) {
                            $('.whatsapp-unread-count').text(res.unread_count);
                            if (res.unread_count === 0) {
                                $('.whatsapp-unread-count').hide();
                            } else {
                                $('.whatsapp-unread-count').show();
                            }
                        }
                    }
                }
            });
        }

        window.dispatchEvent(new CustomEvent('open-lead-modal'));
    });

    // Toggle Lead Read / Unread Status
    $(document).on('click', '.btn-toggle-lead-read', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const btn = $(this);
        const id = btn.data('id');
        btn.prop('disabled', true);
        $.ajax({
            url: `/admin/whatsapp-leads/${id}/toggle-read`,
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            headers: { 'Accept': 'application/json' },
            success: function(res) {
                if (res.success) {
                    const row = $(`#whatsapp-row-${id}`);
                    if (res.is_read) {
                        row.removeClass('is-unread-lead bg-emerald-50/50 hover:bg-emerald-100/50 border-l-4 border-l-emerald-500 font-semibold');
                        row.find('.unread-badge').remove();
                        btn.attr('title', 'Mark as Unread');
                        btn.find('i').removeClass('bi-envelope').addClass('bi-envelope-open');
                    } else {
                        row.addClass('is-unread-lead bg-emerald-50/50 hover:bg-emerald-100/50 border-l-4 border-l-emerald-500 font-semibold');
                        if (!row.find('.unread-badge').length) {
                            row.find('td:eq(2) .flex').prepend('<span class="unread-badge inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300 uppercase tracking-wide"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> New</span>');
                        }
                        btn.attr('title', 'Mark as Read');
                        btn.find('i').removeClass('bi-envelope-open').addClass('bi-envelope');
                    }
                    if (res.unread_count !== undefined) {
                        $('.whatsapp-unread-count').text(res.unread_count);
                        if (res.unread_count === 0) {
                            $('.whatsapp-unread-count').hide();
                        } else {
                            $('.whatsapp-unread-count').show();
                        }
                    }
                }
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });

    // Bulk Mark Read / Unread
    $(document).on('click', '.btn-whatsapp-bulk-read', function() {
        const action = $(this).data('action');
        const selectedIds = $('.whatsapp-row-select:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) return;

        $.ajax({
            url: '{{ route("admin.whatsapp.bulk") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                ids: selectedIds,
                action: action
            },
            headers: { 'Accept': 'application/json' },
            success: function(res) {
                if (res.success) {
                    selectedIds.forEach(id => {
                        const row = $(`#whatsapp-row-${id}`);
                        const toggleBtn = row.find('.btn-toggle-lead-read');
                        if (action === 'mark_read') {
                            row.removeClass('is-unread-lead bg-emerald-50/50 hover:bg-emerald-100/50 border-l-4 border-l-emerald-500 font-semibold');
                            row.find('.unread-badge').remove();
                            toggleBtn.attr('title', 'Mark as Unread');
                            toggleBtn.find('i').removeClass('bi-envelope').addClass('bi-envelope-open');
                        } else {
                            row.addClass('is-unread-lead bg-emerald-50/50 hover:bg-emerald-100/50 border-l-4 border-l-emerald-500 font-semibold');
                            if (!row.find('.unread-badge').length) {
                                row.find('td:eq(2) .flex').prepend('<span class="unread-badge inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300 uppercase tracking-wide"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> New</span>');
                            }
                            toggleBtn.attr('title', 'Mark as Read');
                            toggleBtn.find('i').removeClass('bi-envelope-open').addClass('bi-envelope');
                        }
                    });
                    if (res.unread_count !== undefined) {
                        $('.whatsapp-unread-count').text(res.unread_count);
                        if (res.unread_count === 0) {
                            $('.whatsapp-unread-count').hide();
                        } else {
                            $('.whatsapp-unread-count').show();
                        }
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: 'Updated!', text: res.message, timer: 1500, showConfirmButton: false });
                    }
                    $waBulkBar.addClass('hidden');
                    $waSelectAll.prop('checked', false);
                    $('.whatsapp-row-select').prop('checked', false);
                }
            }
        });
    });

    // WhatsApp Batch Bulk Selection & Processing
    const $waBulkBar = $('#whatsappBulkBar');
    const $waSelectAll = $('.whatsapp-select-all');
    const $waCountBadge = $('#whatsappSelectedCount');

    function updateWaBulkBar() {
        const checkedBoxes = $('.whatsapp-row-select:checked');
        const count = checkedBoxes.length;
        $waCountBadge.text(count);
        if (count > 0) {
            $waBulkBar.removeClass('hidden');
        } else {
            $waBulkBar.addClass('hidden');
        }
    }

    $waSelectAll.on('change', function() {
        const isChecked = $(this).prop('checked');
        $('.whatsapp-row-select').prop('checked', isChecked);
        updateWaBulkBar();
    });

    $(document).on('change', '.whatsapp-row-select', function() {
        const total = $('.whatsapp-row-select').length;
        const checked = $('.whatsapp-row-select:checked').length;
        $waSelectAll.prop('checked', total > 0 && total === checked);
        updateWaBulkBar();
    });

    $('#whatsappBulkClear').on('click', function() {
        $('.whatsapp-row-select, .whatsapp-select-all').prop('checked', false);
        updateWaBulkBar();
    });

    $('#whatsappBulkDelete').on('click', function(e) {
        e.preventDefault();
        const selectedIds = $('.whatsapp-row-select:checked').map(function() { return $(this).val(); }).get();

        if (selectedIds.length === 0) return;

        // Step 1: Bulk Caution Dialog
        Swal.fire({
            title: 'CAUTION: Permanent Bulk Deletion',
            html: `
                <div class="text-left text-xs text-slate-600 leading-relaxed">
                    <div class="p-3 mb-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 font-semibold">
                        <i class="bi bi-exclamation-triangle-fill mr-1"></i>
                        <strong>IRREVERSIBLE BULK ACTION:</strong> You are about to permanently purge <strong class="text-slate-900">${selectedIds.length}</strong> selected WhatsApp lead inquiry record(s) from the database.
                    </div>
                    <p class="mb-2 text-slate-800">This will permanently delete all associated customer messages, client IP telemetry, and request log analytics. No orphaned footprints will remain.</p>
                    <p class="mb-0 text-slate-500">Are you sure you want to proceed to the final confirmation?</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Proceed to Final Confirmation <i class="bi bi-arrow-right ms-1"></i>',
            cancelButtonText: 'Cancel (Keep Leads)',
            focusCancel: true
        }).then((step1Result) => {
            if (!step1Result.isConfirmed) return;

            // Step 2: Final Safeguard Double Confirmation
            Swal.fire({
                title: 'Confirm Bulk Deletion',
                html: `
                    <div class="text-left text-xs">
                        <p class="text-slate-800 mb-2">To confirm permanent deletion of <strong>${selectedIds.length}</strong> WhatsApp leads and all analytics footprints, type <strong>DELETE</strong> in capital letters below:</p>
                    </div>
                `,
                input: 'text',
                inputPlaceholder: 'DELETE',
                inputAttributes: {
                    autocapitalize: 'characters',
                    style: 'text-align: center; font-family: monospace; font-weight: bold; font-size: 1.1rem; letter-spacing: 2px;'
                },
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#b91c1c',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="bi bi-trash3-fill mr-1"></i> PURGE ALL SELECTED',
                cancelButtonText: 'Abort',
                focusCancel: true,
                showLoaderOnConfirm: true,
                preConfirm: (inputValue) => {
                    if (!inputValue || inputValue.trim() !== 'DELETE') {
                        Swal.showValidationMessage('Verification failed! You must type "DELETE" exactly.');
                        return false;
                    }
                    return $.ajax({
                        url: '{{ route("admin.whatsapp.bulk") }}',
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
                        text: step2Result.value.message || 'Selected WhatsApp leads permanently deleted.',
                        timer: 1600,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                }
            });
        });
    });
});

/**
 * 2-Step Cautionary Double Confirmation for Permanent Single WhatsApp Lead Deletion
 */
window.promptPermanentDeleteLead = function(id, name, phone) {
    const phoneStr = (phone !== undefined && phone !== null && phone !== false) ? String(phone).trim() : '';
    const nameStr = (name !== undefined && name !== null && name !== false) ? String(name).trim() : 'Visitor';
    const verifyTarget = (phoneStr && phoneStr !== 'No Phone') ? phoneStr : nameStr;

    // Step 1: Caution Dialog
    Swal.fire({
        title: 'CAUTION: Permanent Lead Deletion',
        html: `
            <div class="text-left text-xs text-slate-600 leading-relaxed">
                <div class="p-3 mb-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 font-semibold">
                    <i class="bi bi-exclamation-triangle-fill mr-1"></i>
                    <strong>IRREVERSIBLE ACTION:</strong> You are about to permanently eradicate WhatsApp lead inquiry <strong class="text-slate-900">#${id} (${name})</strong> from the database.
                </div>
                <div class="p-3 mb-3 bg-slate-50 border border-slate-200 rounded-xl">
                    <div class="font-bold text-slate-700 mb-1 text-[11px] uppercase">The following records will be permanently purged:</div>
                    <ul class="list-disc pl-4 text-slate-500 text-xs space-y-1">
                        <li>WhatsApp Lead Record & Contact Information (${phone || 'No Phone'})</li>
                        <li>Prefilled inquiry message & campaign source URL</li>
                        <li>Visitor Analytics, Telemetry & Request Logs (${id})</li>
                        <li>Client IP & Geolocation Audit Trail</li>
                    </ul>
                </div>
                <p class="mb-0 text-slate-500">Are you sure you want to proceed to the final verification?</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Proceed to Final Confirmation <i class="bi bi-arrow-right ms-1"></i>',
        cancelButtonText: 'Cancel (Keep Lead)',
        focusCancel: true
    }).then((step1Result) => {
        if (!step1Result.isConfirmed) return;

        // Step 2: Final Safeguard Double Confirmation
        Swal.fire({
            title: 'Double Confirmation Required',
            html: `
                <div class="text-left text-xs">
                    <p class="text-slate-800 mb-2">To prevent accidental deletion, please type the verification value below to authorize permanent destruction:</p>
                    <div class="text-center my-3">
                        <span class="inline-block px-3 py-1.5 rounded-lg bg-rose-50 text-rose-600 border border-rose-200 font-mono text-sm font-bold">
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
            confirmButtonColor: '#b91c1c',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-trash3-fill mr-1"></i> PERMANENTLY PURGE EVERYTHING',
            cancelButtonText: 'Abort',
            focusCancel: true,
            showLoaderOnConfirm: true,
            preConfirm: (inputValue) => {
                if (!inputValue || inputValue.trim() !== verifyTarget) {
                    Swal.showValidationMessage(`Verification mismatch! You must type exactly "${verifyTarget}" to authorize deletion.`);
                    return false;
                }
                return $.ajax({
                    url: `/admin/whatsapp-leads/${id}`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    headers: { 'Accept': 'application/json' }
                }).then(response => {
                    if (!response.success) {
                        throw new Error(response.message || 'Failed to delete WhatsApp lead.');
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
                    text: step2Result.value.message || 'WhatsApp lead and all analytics footprints permanently deleted.',
                    timer: 1600,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            }
        });
    });
};
</script>
@endpush
@endsection
