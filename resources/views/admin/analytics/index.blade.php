@extends('layouts.admin')

@section('page_title', 'Analytics & Traffic Intelligence')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi bi-activity text-primary"></i> Traffic & Acquisition Intelligence
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Real-time visitor telemetry, traffic channels, referring websites, and UTM campaign attribution.</p>
        </div>
        
        <form method="GET" action="{{ route('admin.analytics.index') }}" class="flex items-center gap-2">
            <label for="days" class="text-xs font-bold text-slate-700 whitespace-nowrap">Timeframe:</label>
            <select name="days" id="days" class="rounded-xl border border-slate-200 px-3.5 py-1.5 text-xs font-bold text-slate-800 outline-hidden bg-white shadow-2xs focus:border-primary" onchange="this.form.submit()">
                <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 Days</option>
                <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 Days</option>
                <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 Days</option>
            </select>
        </form>
    </div>

    <!-- 4 Key Stat Cards -->
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Pageviews</span>
                <span class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
                    <i class="bi bi-eye-fill text-lg"></i>
                </span>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900">{{ number_format($totalPageviews) }}</div>
                <div class="text-xs text-slate-400 mt-1">Total HTTP requests</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Human Pageviews</span>
                <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-200">
                    <i class="bi bi-person-check-fill text-lg"></i>
                </span>
            </div>
            <div>
                <div class="text-2xl font-black text-emerald-600">{{ number_format($humanPageviews) }}</div>
                <div class="text-xs text-slate-400 mt-1">Excludes bots & automated crawlers</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Unique Sessions</span>
                <span class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-200">
                    <i class="bi bi-people-fill text-lg"></i>
                </span>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900">{{ number_format($uniqueVisitors) }}</div>
                <div class="text-xs text-slate-400 mt-1">Distinct user sessions</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Unique Human IPs</span>
                <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center border border-amber-200">
                    <i class="bi bi-globe text-lg"></i>
                </span>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900">{{ number_format($uniqueIPs) }}</div>
                <div class="text-xs text-slate-400 mt-1">Distinct IP addresses</div>
            </div>
        </div>
    </div>

    <!-- Visual Analytics Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-graph-up text-primary"></i> Daily Traffic Trend
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Pageviews & Human Visits progression over the last {{ $days }} days</p>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">Daily Telemetry</span>
            </div>
            <div class="h-72 relative">
                <canvas id="trafficTrendChart"></canvas>
            </div>
        </div>
        <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-pie-chart-fill text-amber-500"></i> Traffic Sources
                    </h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">Channels</span>
                </div>
                <div class="h-56 relative">
                    <canvas id="trafficSourcesChart"></canvas>
                </div>
            </div>
            <div class="text-slate-400 text-xs text-center mt-3 pt-3 border-t border-slate-100">
                Attribution grouped by Google Ads, Organic Search, Social, WhatsApp & Direct.
            </div>
        </div>
    </div>

    <!-- Traffic Acquisition & Referrers Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Traffic Sources Breakdown -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between">
            <div class="bg-slate-50/80 border-b border-slate-200/80 py-3.5 px-5 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-compass text-primary"></i> Acquisition Channels
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Origin channels of visiting traffic</p>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/20">
                    {{ $trafficSources->count() }} Channels
                </span>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse text-sm text-slate-700">
                    <thead class="bg-slate-50/50 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Channel / Source</th>
                            <th class="py-3 px-4 text-center">Pageviews</th>
                            <th class="py-3 px-4 text-center">Unique Visitors</th>
                            <th class="py-3 px-4 text-right pe-4">Traffic Share</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @php $totalChannelViews = $trafficSources->sum('views') ?: 1; @endphp
                        @forelse($trafficSources as $src)
                        @php
                            $percent = round(($src->views / $totalChannelViews) * 100, 1);
                            $iconClass = match($src->channel) {
                                'Google Ads' => 'bi-google text-rose-500',
                                'Google Organic' => 'bi-search text-emerald-600',
                                'Facebook' => 'bi-facebook text-blue-600',
                                'Instagram' => 'bi-instagram text-rose-500',
                                'WhatsApp' => 'bi-whatsapp text-emerald-500',
                                'Bing Organic' => 'bi-browser-edge text-sky-500',
                                'TikTok' => 'bi-tiktok text-slate-900',
                                'Direct / Bookmark' => 'bi-bookmark-star-fill text-amber-500',
                                default => 'bi-box-arrow-up-right text-slate-400'
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <i class="bi {{ $iconClass }} text-base"></i>
                                    <span class="font-bold text-slate-900 text-xs">{{ $src->channel }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-900 text-xs">{{ number_format($src->views) }}</td>
                            <td class="py-3 px-4 text-center text-slate-500 text-xs">{{ number_format($src->visitors) }}</td>
                            <td class="py-3 px-4 text-right pe-4">
                                <div class="flex items-center justify-end gap-2">
                                    <div class="w-16 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-primary h-1.5 rounded-full" style="width: {{ $percent }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-600 w-10 text-right">{{ $percent }}%</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-6 text-slate-400 text-xs">No traffic channel telemetry available.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Referring Domains -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between">
            <div class="bg-slate-50/80 border-b border-slate-200/80 py-3.5 px-5 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-link-45deg text-emerald-600"></i> Referring Websites
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">External sites sending traffic to Dunes</p>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                    {{ $topReferrers->count() }} Referrers
                </span>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse text-sm text-slate-700">
                    <thead class="bg-slate-50/50 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Referrer URL</th>
                            <th class="py-3 px-4 text-center">Views</th>
                            <th class="py-3 px-4 text-right pe-4">Unique Visitors</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($topReferrers as $ref)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4">
                                <a href="{{ $ref->referrer }}" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline font-mono font-bold text-xs truncate inline-block max-w-xs" title="{{ $ref->referrer }}">
                                    {{ $ref->referrer }} <i class="bi bi-box-arrow-up-right text-[10px] ml-1"></i>
                                </a>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/20">
                                    {{ number_format($ref->views) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right pe-4 text-xs font-bold text-slate-600">
                                {{ number_format($ref->visitors) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-6 text-slate-400 text-xs">No external referring URLs recorded for this timeframe.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- UTM Campaigns Table (If any) -->
    @if($campaigns->count() > 0)
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="bg-slate-50/80 border-b border-slate-200/80 py-3.5 px-5 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-megaphone-fill text-rose-500"></i> Active UTM Marketing Campaigns
            </h2>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                {{ $campaigns->count() }} Campaigns
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700">
                <thead class="bg-slate-50/50 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Campaign Name</th>
                        <th class="py-3 px-4">Source</th>
                        <th class="py-3 px-4">Medium</th>
                        <th class="py-3 px-4 text-center">Pageviews</th>
                        <th class="py-3 px-4 text-right pe-4">Unique Visitors</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($campaigns as $camp)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4 font-bold text-slate-900 text-xs">{{ $camp->utm_campaign }}</td>
                        <td class="py-3 px-4"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">{{ $camp->utm_source }}</span></td>
                        <td class="py-3 px-4"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200">{{ $camp->utm_medium }}</span></td>
                        <td class="py-3 px-4 text-center font-black text-primary text-xs">{{ number_format($camp->views) }}</td>
                        <td class="py-3 px-4 text-right pe-4 font-bold text-slate-600 text-xs">{{ number_format($camp->visitors) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Top Pages & Geographies / Devices -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Top Pages Table (Col 7) -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="bg-slate-50/80 border-b border-slate-200/80 py-3.5 px-5 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="bi bi-file-earmark-text text-primary"></i> Top Most Visited Pages
                </h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">Human Traffic</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm text-slate-700">
                    <thead class="bg-slate-50/50 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Page URI</th>
                            <th class="py-3 px-4 text-center">Views</th>
                            <th class="py-3 px-4 text-right pe-4">Unique Visitors</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($topPages as $page)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900 font-mono text-xs break-all">{{ $page->request_uri }}</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/20">
                                    {{ number_format($page->views) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right pe-4 font-bold text-slate-600 text-xs">
                                {{ number_format($page->visitors) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-6 text-slate-400 text-xs">No pageview data recorded for this timeframe.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Countries & Devices (Col 5) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Top Countries -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="bg-slate-50/80 border-b border-slate-200/80 py-3.5 px-5">
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-globe-americas text-sky-600"></i> Top Visitor Geographies
                    </h2>
                </div>
                <div class="p-5 space-y-3">
                    @forelse($topCountries as $c)
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 last:border-0 last:pb-0">
                        <span class="font-bold text-slate-800 text-xs flex items-center gap-2">
                            <i class="bi bi-geo-alt-fill text-rose-500"></i> {{ $c->country }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            {{ number_format($c->count) }} views
                        </span>
                    </div>
                    @empty
                    <p class="text-slate-400 text-xs text-center py-2">No location telemetry available.</p>
                    @endforelse
                </div>
            </div>

            <!-- Device Breakdown -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="bg-slate-50/80 border-b border-slate-200/80 py-3.5 px-5">
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-display text-primary"></i> Device Breakdown
                    </h2>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-3 gap-3 text-center">
                        @foreach($devices as $dev)
                        <div class="p-3 bg-slate-50/70 rounded-xl border border-slate-200/80">
                            <i class="bi {{ $dev->device_type === 'Mobile' ? 'bi-phone text-emerald-500' : ($dev->device_type === 'Tablet' ? 'bi-tablet text-amber-500' : 'bi-laptop text-primary') }} text-2xl block mb-1"></i>
                            <strong class="block text-slate-800 text-xs">{{ $dev->device_type ?: 'Desktop' }}</strong>
                            <span class="text-slate-400 text-[11px]">{{ number_format($dev->count) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Request Logs Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="bg-slate-50/80 border-b border-slate-200/80 py-3.5 px-5 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="bi bi-activity text-rose-500"></i> Live Request Logs Telemetry
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Real-time HTTP requests captured by Dunes VisitorTracker engine.</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700">
                <thead class="bg-slate-50/50 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Timestamp</th>
                        <th class="py-3 px-4">IP & Location</th>
                        <th class="py-3 px-4">Request URI</th>
                        <th class="py-3 px-4">Device / OS / Browser</th>
                        <th class="py-3 px-4 text-right pe-4">Indicator</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4 whitespace-nowrap">
                            <div class="font-bold text-slate-900 text-xs">{{ \Carbon\Carbon::parse($log->request_timestamp)->format('M d, Y') }}</div>
                            <div class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($log->request_timestamp)->format('H:i:s') }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-mono font-bold text-slate-800 text-xs">{{ $log->client_ip }}</div>
                            <div class="text-[11px] text-slate-400">{{ ($log->city ?: 'Unknown') . ', ' . ($log->country ?: '') }}</div>
                        </td>
                        <td class="py-3 px-4 font-mono text-xs text-primary break-all">
                            {{ $log->request_uri }}
                        </td>
                        <td class="py-3 px-4 text-xs text-slate-600">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 mr-1">{{ $log->device_type ?: 'Desktop' }}</span>
                            {{ $log->os_name }} / {{ $log->browser_name }}
                        </td>
                        <td class="py-3 px-4 text-right pe-4">
                            @if($log->bot_indicator === 'Likely Human')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Human</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">Bot/System</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-6 text-slate-400 text-xs">No request logs found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 bg-white">
            {{ $logs->appends(['days' => $days])->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // 1. Daily Traffic Trend Chart
    const trendCtx = document.getElementById('trafficTrendChart');
    if (trendCtx) {
        const trendData = @json($dailyTrend);
        const labels = trendData.map(d => d.date);
        const totalViews = trendData.map(d => d.total_views);
        const humanViews = trendData.map(d => d.human_views);
        const humanSessions = trendData.map(d => d.human_sessions);

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Human Pageviews',
                        data: humanViews,
                        borderColor: '#F58F43',
                        backgroundColor: 'rgba(245, 143, 67, 0.15)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2.5,
                        pointRadius: 3
                    },
                    {
                        label: 'Unique Sessions',
                        data: humanSessions,
                        borderColor: '#10b981',
                        backgroundColor: 'transparent',
                        borderDash: [5, 5],
                        borderWidth: 2,
                        tension: 0.35,
                        pointRadius: 2
                    },
                    {
                        label: 'Total Requests',
                        data: totalViews,
                        borderColor: '#94a3b8',
                        backgroundColor: 'transparent',
                        borderWidth: 1.5,
                        tension: 0.35,
                        pointRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11, weight: 'bold' } } },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { grid: { color: '#f1f5f9' }, beginAtZero: true }
                }
            }
        });
    }

    // 2. Traffic Sources Donut Chart
    const sourcesCtx = document.getElementById('trafficSourcesChart');
    if (sourcesCtx) {
        const sourcesData = @json($trafficSources);
        const sourceLabels = sourcesData.map(s => s.channel);
        const sourceViews = sourcesData.map(s => s.views);

        const colors = ['#F58F43', '#10b981', '#3b82f6', '#ec4899', '#14b8a6', '#f59e0b', '#64748b', '#06b6d4'];

        new Chart(sourcesCtx, {
            type: 'doughnut',
            data: {
                labels: sourceLabels,
                datasets: [{
                    data: sourceViews,
                    backgroundColor: colors.slice(0, sourceLabels.length),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
                },
                cutout: '65%'
            }
        });
    }
});
</script>
@endpush
@endsection
