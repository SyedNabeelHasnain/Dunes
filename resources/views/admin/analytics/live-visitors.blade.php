@extends('layouts.admin')

@section('page_title', 'Live Visitors & Real-Time Telemetry Hub')

@section('content')
<div class="space-y-6" x-data="liveVisitorsHubApp()">

    <!-- Executive Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Live Visitors &amp; Telemetry Hub</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live Streaming
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Real-time guest monitoring: observe what visitors are browsing live, detect checkout friction and 404 errors, and inspect complete session clickstreams.
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" 
                    @click="fetchVisitors(true)" 
                    :disabled="loading"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold shadow-2xs transition cursor-pointer">
                <i class="bi bi-arrow-repeat text-slate-500" :class="loading ? 'animate-spin text-primary' : ''"></i>
                <span>Refresh Stream</span>
            </button>
            <a href="{{ route('admin.analytics.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition">
                <i class="bi bi-graph-up text-amber-400"></i> Analytics Dashboard
            </a>
        </div>
    </div>

    <!-- 4 High-Impact Real-Time Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Active Right Now -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Active Live Visitors</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="bi bi-people-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900" x-text="visitors.length"></div>
            <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                <span>Window: <strong>Last 15 minutes</strong></span>
                <span class="text-emerald-700 font-bold inline-flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Active
                </span>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-emerald-500"></div>
        </div>

        <!-- Incomplete Checkout Drafts -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Incomplete Drafts</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="bi bi-cart-x-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-amber-600" x-text="visitors.filter(v => v.has_draft).length"></div>
            <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                <span>Recoverable Carts</span>
                <a href="{{ route('admin.bookings.drafts') }}" class="text-amber-700 hover:underline font-bold">Drafts Hub &rarr;</a>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-amber-500"></div>
        </div>

        <!-- 404 & Obstacle Alerts -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Friction &amp; Errors</span>
                <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i class="bi bi-exclamation-triangle-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-rose-600" x-text="visitors.filter(v => v.has_error).length"></div>
            <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                <span>404 or Broken Links</span>
                <span class="text-rose-700 font-bold">High Priority</span>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-rose-500"></div>
        </div>

        <!-- WhatsApp Inquiries -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">WhatsApp Leads</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="bi bi-whatsapp text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-emerald-700" x-text="visitors.filter(v => v.has_whatsapp).length"></div>
            <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                <span>Concierge Chat</span>
                <a href="{{ route('admin.whatsapp.index') }}" class="text-emerald-700 hover:underline font-bold">Leads &rarr;</a>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-emerald-500"></div>
        </div>
    </div>

    <!-- Interactive Live Table & Filter Strip -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
        
        <!-- Filter Tabs & Search -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 border-b border-slate-100 gap-3">
            <div class="flex items-center gap-1.5 flex-wrap">
                <button type="button" 
                        @click="activeFilter = 'all'"
                        :class="activeFilter === 'all' ? 'bg-slate-900 text-white font-black' : 'bg-slate-100 text-slate-600 hover:text-slate-900 font-bold'"
                        class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                    All Active (<span x-text="visitors.length"></span>)
                </button>
                <button type="button" 
                        @click="activeFilter = 'drafts'"
                        :class="activeFilter === 'drafts' ? 'bg-amber-600 text-white font-black' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 font-bold'"
                        class="px-3.5 py-1.5 rounded-xl text-xs transition inline-flex items-center gap-1.5 cursor-pointer">
                    <i class="bi bi-cart"></i> With Incomplete Drafts (<span x-text="visitors.filter(v => v.has_draft).length"></span>)
                </button>
                <button type="button" 
                        @click="activeFilter = 'errors'"
                        :class="activeFilter === 'errors' ? 'bg-rose-600 text-white font-black' : 'bg-rose-50 text-rose-800 hover:bg-rose-100 font-bold'"
                        class="px-3.5 py-1.5 rounded-xl text-xs transition inline-flex items-center gap-1.5 cursor-pointer">
                    <i class="bi bi-exclamation-triangle"></i> Encountered 404 (<span x-text="visitors.filter(v => v.has_error).length"></span>)
                </button>
                <button type="button" 
                        @click="activeFilter = 'whatsapp'"
                        :class="activeFilter === 'whatsapp' ? 'bg-emerald-600 text-white font-black' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 font-bold'"
                        class="px-3.5 py-1.5 rounded-xl text-xs transition inline-flex items-center gap-1.5 cursor-pointer">
                    <i class="bi bi-whatsapp"></i> WhatsApp Leads (<span x-text="visitors.filter(v => v.has_whatsapp).length"></span>)
                </button>
            </div>

            <!-- Search Input -->
            <div class="relative w-full md:w-64">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" 
                       x-model="searchQuery" 
                       placeholder="Filter by IP, City, or Page..." 
                       class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-hidden">
            </div>
        </div>

        <!-- Live Streaming Table -->
        <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left border-collapse text-xs text-slate-700">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-3.5">Visitor &amp; IP Address</th>
                        <th class="py-3 px-3.5">Location</th>
                        <th class="py-3 px-3.5">Device &amp; OS</th>
                        <th class="py-3 px-3.5">Current Screen &amp; Steps</th>
                        <th class="py-3 px-3.5">Acquisition Channel</th>
                        <th class="py-3 px-3.5">Friction / Lead State</th>
                        <th class="py-3 px-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <template x-for="(v, index) in filteredVisitors()" :key="index">
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <!-- Visitor & IP -->
                            <td class="py-3 px-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0" :class="v.last_active_human === 'Just now' ? 'animate-ping' : ''"></span>
                                    <div>
                                        <div class="flex items-center gap-1 font-mono font-bold text-slate-900">
                                            <span x-text="v.client_ip"></span>
                                        </div>
                                        <div class="text-[10px] text-slate-400" x-text="v.last_active_human"></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Location -->
                            <td class="py-3 px-3.5 whitespace-nowrap">
                                <span class="font-semibold text-slate-800" x-text="(v.city || 'Dubai') + ', ' + (v.country || 'UAE')"></span>
                            </td>

                            <!-- Device & OS -->
                            <td class="py-3 px-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-1.5 text-slate-700">
                                    <i class="bi" :class="v.device_type === 'Mobile' ? 'bi-phone' : 'bi-display'"></i>
                                    <span x-text="v.device_type + ' (' + v.browser_name + ')'"></span>
                                </div>
                            </td>

                            <!-- Current Screen & Steps -->
                            <td class="py-3 px-3.5 whitespace-nowrap">
                                <div>
                                    <span class="font-mono text-slate-800 font-bold block truncate max-w-xs" x-text="v.request_uri || '/'"></span>
                                    <span class="text-[10px] text-slate-400" x-text="v.pages_count + ' page views (' + Math.floor(v.duration_seconds / 60) + 'm ' + (v.duration_seconds % 60) + 's)'"></span>
                                </div>
                            </td>

                            <!-- Channel -->
                            <td class="py-3 px-3.5 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase"
                                      :class="{
                                          'bg-emerald-100 text-emerald-800': v.channel === 'Google Ads',
                                          'bg-blue-100 text-blue-800': v.channel === 'Meta Ads' || v.channel === 'Social',
                                          'bg-indigo-100 text-indigo-800': v.channel === 'Organic Search',
                                          'bg-slate-100 text-slate-800': v.channel === 'Direct'
                                      }"
                                      x-text="v.channel"></span>
                            </td>

                            <!-- Friction / Lead State -->
                            <td class="py-3 px-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <template x-if="v.has_draft">
                                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-black text-[10px] inline-flex items-center gap-1">
                                            <i class="bi bi-cart-x"></i> Draft (AED <span x-text="v.draft_total"></span>)
                                        </span>
                                    </template>
                                    <template x-if="v.has_error">
                                        <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 font-black text-[10px] inline-flex items-center gap-1">
                                            <i class="bi bi-exclamation-triangle-fill"></i> 404 Error
                                        </span>
                                    </template>
                                    <template x-if="v.has_whatsapp">
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-black text-[10px] inline-flex items-center gap-1">
                                            <i class="bi bi-whatsapp"></i> Chat Lead
                                        </span>
                                    </template>
                                    <template x-if="!v.has_draft && !v.has_error && !v.has_whatsapp">
                                        <span class="text-slate-400 text-[11px] italic">Smooth Browsing</span>
                                    </template>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="py-3 px-3.5 whitespace-nowrap text-right">
                                <button type="button" 
                                        @click="$dispatch('inspect-visitor', { sessionId: v.session_id, clientIp: v.client_ip })"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-2xs transition cursor-pointer">
                                    <i class="bi bi-activity"></i>
                                    <span>Inspect Journey</span>
                                </button>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredVisitors().length === 0">
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 text-xs">
                                <i class="bi bi-person-x text-2xl block mb-1"></i>
                                No active visitors matching the current filter criteria.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

    </div>

</div>

<script>
function liveVisitorsHubApp() {
    return {
        visitors: @js($recentVisitors),
        activeFilter: 'all',
        searchQuery: '',
        loading: false,
        pollTimer: null,

        init() {
            this.pollTimer = setInterval(() => {
                if (!document.hidden) {
                    this.fetchVisitors(false);
                }
            }, 10000);
        },

        async fetchVisitors(showLoader = true) {
            if (showLoader) this.loading = true;
            try {
                const res = await fetch("{{ route('admin.active-visitors') }}", {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data && data.visitors) {
                    this.visitors = data.visitors;
                }
            } catch (e) {
                console.error("Live Hub fetch failed:", e);
            } finally {
                this.loading = false;
            }
        },

        filteredVisitors() {
            let list = this.visitors;
            if (this.activeFilter === 'drafts') {
                list = list.filter(v => v.has_draft);
            } else if (this.activeFilter === 'errors') {
                list = list.filter(v => v.has_error);
            } else if (this.activeFilter === 'whatsapp') {
                list = list.filter(v => v.has_whatsapp);
            }

            if (this.searchQuery && this.searchQuery.trim() !== '') {
                const q = this.searchQuery.toLowerCase();
                list = list.filter(v => 
                    (v.client_ip && v.client_ip.toLowerCase().includes(q)) ||
                    (v.city && v.city.toLowerCase().includes(q)) ||
                    (v.country && v.country.toLowerCase().includes(q)) ||
                    (v.request_uri && v.request_uri.toLowerCase().includes(q))
                );
            }

            return list;
        }
    };
}
</script>
@endsection
