@extends('layouts.admin')

@section('page_title', 'Session Recordings & Replay Hub')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/rrweb/rrweb-player.min.css') }}">
<style>
    .rr-player-cinema {
        background: #090d16;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
    }
    .rrweb-player {
        margin: 0 auto;
        box-shadow: none !important;
        border: none !important;
    }
    .rrweb-player .rr-controller {
        background: rgba(15, 23, 42, 0.95) !important;
        backdrop-filter: blur(8px);
        border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
        color: #f1f5f9 !important;
    }
    .rrweb-player .rr-timeline__time {
        color: #94a3b8 !important;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
        font-size: 11px !important;
    }
    .rrweb-player .rr-controller__btn {
        color: #cbd5e1 !important;
    }
    .rrweb-player .rr-controller__btn:hover {
        color: #f59e0b !important;
    }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="recordingsHubApp()">

    <!-- Header & Quick Nav -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Session Recordings &amp; Replay</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300 inline-flex items-center gap-1.5">
                    <i class="bi bi-camera-video-fill text-amber-600"></i>
                    Self-Hosted (Zero-Cost rrweb)
                </span>
                @if(($stats['is_enabled'] ?? true))
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Recording Active (Humans Only)
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                        Recording Paused
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Visual screen replay of genuine human guest sessions: inspect mouse movements, clicks, scrolls, form interactions, and checkout friction without third-party subscriptions.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" 
                    @click="openSettingsModal = true"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold shadow-2xs transition cursor-pointer">
                <i class="bi bi-gear-fill text-slate-500"></i>
                <span>Retention &amp; Policy</span>
            </button>

            <a href="{{ route('admin.analytics.heatmaps.index') }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold shadow-2xs transition cursor-pointer">
                <i class="bi bi-fire text-orange-500"></i>
                <span>Heatmaps &amp; Scroll</span>
            </a>

            <a href="{{ route('admin.analytics.live-visitors') }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Live Hub</span>
            </a>
        </div>
    </div>

    <!-- 4 High-Impact Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Sessions -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Recorded Sessions</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="bi bi-camera-reels-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ number_format($stats['total_recordings'] ?? 0) }}</div>
            <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                <span>Starred / Pinned: <strong>{{ $stats['starred_recordings'] ?? 0 }}</strong></span>
                <span class="text-emerald-700 font-bold">100% Human</span>
            </div>
        </div>

        <!-- Total Watch Time -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Playback Time</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i class="bi bi-clock-history text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $stats['formatted_duration'] ?? '0m' }}</div>
            <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                <span>Avg Session: <strong>{{ ($stats['total_recordings'] ?? 0) > 0 ? round(($stats['total_duration_sec'] ?? 0) / $stats['total_recordings']) . 's' : '0s' }}</strong></span>
                <span class="text-indigo-600 font-medium">Replayable</span>
            </div>
        </div>

        <!-- Inquiries / Leads Converted -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Inquiry &amp; Leads</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="bi bi-check2-circle text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ number_format($stats['inquiries_recordings'] ?? 0) }}</div>
            <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                <span>Drafts &amp; Contacts</span>
                <span class="text-emerald-600 font-bold">High Intent</span>
            </div>
        </div>

        <!-- Storage & Retention -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Storage Footprint</span>
                <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                    <i class="bi bi-hdd-network-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $stats['formatted_bytes'] ?? '0 KB' }}</div>
            <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                <span>Auto-Purge: <strong>{{ $stats['retention_days'] ?? 14 }} days</strong></span>
                <span class="text-slate-600 font-medium">Gzip Compressed</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4">
        <form method="GET" action="{{ route('admin.analytics.recordings.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Search Sessions</label>
                <div class="relative">
                    <i class="bi bi-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Session ID, IP, City, or URL..." 
                           class="w-full pl-8 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
            </div>

            <!-- Device -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Device Form Factor</label>
                <select name="device" class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="all">All Devices</option>
                    <option value="desktop" {{ request('device') === 'desktop' ? 'selected' : '' }}>Desktop</option>
                    <option value="mobile" {{ request('device') === 'mobile' ? 'selected' : '' }}>Mobile</option>
                    <option value="tablet" {{ request('device') === 'tablet' ? 'selected' : '' }}>Tablet</option>
                </select>
            </div>

            <!-- Country -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Country</label>
                <select name="country" class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="all">All Countries</option>
                    @foreach($countries as $c)
                        <option value="{{ $c }}" {{ request('country') === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Lead / Inquiries -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Inquiry Status</label>
                <select name="inquiry" class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">All Visitors</option>
                    <option value="yes" {{ request('inquiry') === 'yes' ? 'selected' : '' }}>With Leads / Inquiries</option>
                    <option value="no" {{ request('inquiry') === 'no' ? 'selected' : '' }}>Browsing Only</option>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-primary hover:bg-primary-dark text-white text-xs font-bold rounded-xl transition cursor-pointer">
                    <i class="bi bi-funnel-fill me-1"></i> Filter
                </button>
                @if(request()->anyFilled(['search', 'device', 'country', 'inquiry', 'starred', 'days']))
                    <a href="{{ route('admin.analytics.recordings.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition" title="Clear Filters">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>

        <!-- Quick filter pills -->
        <div class="flex items-center gap-2 mt-3 pt-3 border-t border-slate-100 text-xs overflow-x-auto">
            <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider shrink-0">Quick Views:</span>
            <a href="{{ route('admin.analytics.recordings.index', ['starred' => '1']) }}" 
               class="px-2.5 py-1 rounded-lg text-xs font-semibold transition shrink-0 {{ request('starred') === '1' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                <i class="bi bi-star-fill text-amber-500 me-1"></i> Starred Sessions Only
            </a>
            <a href="{{ route('admin.analytics.recordings.index', ['inquiry' => 'yes']) }}" 
               class="px-2.5 py-1 rounded-lg text-xs font-semibold transition shrink-0 {{ request('inquiry') === 'yes' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                <i class="bi bi-check-circle-fill text-emerald-500 me-1"></i> Sessions with Inquiries
            </a>
            <a href="{{ route('admin.analytics.recordings.index', ['days' => '1']) }}" 
               class="px-2.5 py-1 rounded-lg text-xs font-semibold transition shrink-0 {{ request('days') === '1' ? 'bg-primary/10 text-primary border border-primary/30' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                <i class="bi bi-calendar-event me-1"></i> Today
            </a>
            <a href="{{ route('admin.analytics.recordings.index', ['days' => '7']) }}" 
               class="px-2.5 py-1 rounded-lg text-xs font-semibold transition shrink-0 {{ request('days') === '7' ? 'bg-primary/10 text-primary border border-primary/30' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                <i class="bi bi-calendar-week me-1"></i> Last 7 Days
            </a>
        </div>
    </div>

    <!-- Recordings Data Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        @if($recordings->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <i class="bi bi-camera-video-off text-2xl"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">No session recordings match criteria</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                    Recordings will automatically appear as genuine human visitors explore the site. Headless bots and ignored admin testing IPs are automatically filtered out.
                </p>
                <div class="mt-4 flex items-center justify-center gap-2">
                    <a href="{{ url('/') }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-dark transition">
                        Open Public Site <i class="bi bi-box-arrow-up-right ms-1"></i>
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider text-[10px] font-bold">
                        <tr>
                            <th class="py-3 px-3 w-10 text-center">Pin</th>
                            <th class="py-3 px-4">Visitor &amp; Location</th>
                            <th class="py-3 px-4">Device &amp; System</th>
                            <th class="py-3 px-4">Journey / Pages</th>
                            <th class="py-3 px-4">Duration &amp; Events</th>
                            <th class="py-3 px-4">Lead Status</th>
                            <th class="py-3 px-4 text-end">Replay Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($recordings as $rec)
                            <tr class="hover:bg-slate-50/80 transition" id="rec-row-{{ $rec->id }}">
                                
                                <!-- Star Pin Toggle -->
                                <td class="py-3.5 px-3 text-center">
                                    <button type="button" 
                                            @click="toggleStar({{ $rec->id }})" 
                                            class="text-base transition hover:scale-110 cursor-pointer"
                                            :class="isStarred({{ $rec->id }}, {{ $rec->is_starred ? 'true' : 'false' }}) ? 'text-amber-500' : 'text-slate-300 hover:text-amber-400'"
                                            title="Star to permanently prevent auto-purge">
                                        <i class="bi" :class="isStarred({{ $rec->id }}, {{ $rec->is_starred ? 'true' : 'false' }}) ? 'bi-star-fill' : 'bi-star'"></i>
                                    </button>
                                </td>

                                <!-- Visitor & Location -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-[11px] shrink-0">
                                            {{ strtoupper(substr($rec->country ?? 'UN', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                                <span>{{ $rec->city ?: 'Unknown City' }}</span>
                                                <span class="text-slate-400 font-normal">({{ $rec->country ?: 'Global' }})</span>
                                            </div>
                                            <div class="text-[11px] text-slate-400 font-mono flex items-center gap-2 mt-0.5">
                                                <span>{{ $rec->client_ip }}</span>
                                                <span>&bull;</span>
                                                <span>{{ $rec->last_event_at?->diffForHumans() ?? $rec->created_at?->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Device & System -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2">
                                        <i class="bi {{ $rec->device_icon }} text-slate-500 text-sm"></i>
                                        <div>
                                            <div class="font-bold capitalize text-slate-800">{{ $rec->device_type }}</div>
                                            <div class="text-[11px] text-slate-400">
                                                {{ $rec->browser_name ?: 'Web Browser' }} {{ $rec->os_name ? '(' . $rec->os_name . ')' : '' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Journey / Pages -->
                                <td class="py-3.5 px-4 max-w-xs">
                                    <div class="truncate font-semibold text-slate-900" title="{{ $rec->landing_page }}">
                                        {{ $rec->clean_landing_page }}
                                    </div>
                                    @if($rec->exit_page && $rec->clean_exit_page !== $rec->clean_landing_page)
                                        <div class="truncate text-[11px] text-slate-400 flex items-center gap-1 mt-0.5" title="{{ $rec->exit_page }}">
                                            <i class="bi bi-arrow-right text-[10px]"></i>
                                            <span>{{ $rec->clean_exit_page }}</span>
                                        </div>
                                    @endif
                                </td>

                                <!-- Duration & Activity -->
                                <td class="py-3.5 px-4">
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-800">
                                        <i class="bi bi-stopwatch text-slate-500"></i>
                                        <span>{{ $rec->formatted_duration }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-2">
                                        <span>{{ number_format($rec->events_count) }} events</span>
                                        <span>&bull;</span>
                                        <span>{{ $rec->formatted_file_size }}</span>
                                    </div>
                                </td>

                                <!-- Lead Status -->
                                <td class="py-3.5 px-4">
                                    @if($rec->has_inquiry)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <i class="bi bi-check-circle-fill text-emerald-600"></i>
                                            Lead Submitted
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium text-slate-400 bg-slate-50">
                                            Browsing
                                        </span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 text-end">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button" 
                                                @click="playSession({{ $rec->id }})" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-2xs transition cursor-pointer">
                                            <i class="bi bi-play-circle-fill"></i>
                                            <span>Play Replay</span>
                                        </button>

                                        <button type="button" 
                                                @click="confirmDelete({{ $rec->id }})"
                                                class="p-1.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" 
                                                title="Delete Recording">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($recordings->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $recordings->links() }}
                </div>
            @endif
        @endif
    </div>

    <!-- ========================================== -->
    <!-- INTERACTIVE RRWEB REPLAYER MODAL           -->
    <!-- ========================================== -->
    <div x-show="playerModalOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-6"
         style="display: none;">
        
        <div class="bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl w-full max-w-6xl max-h-[94vh] flex flex-col overflow-hidden text-slate-100"
             @click.outside="closePlayerModal()">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between gap-4 bg-slate-900/90 shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-2xl bg-primary/20 text-primary-light flex items-center justify-center text-lg font-black shrink-0">
                        <i class="bi bi-play-fill text-2xl text-amber-400"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm sm:text-base font-black text-white tracking-tight truncate">
                                Session Replay: <span class="text-amber-400 font-mono text-xs" x-text="currentSession?.session_id || 'Loading...'"></span>
                            </h3>
                            <template x-if="currentSession?.is_starred">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    <i class="bi bi-star-fill text-amber-400 me-0.5"></i> Starred
                                </span>
                            </template>
                        </div>
                        <div class="text-xs text-slate-400 flex items-center gap-2 flex-wrap mt-0.5">
                            <span x-text="currentSession?.city ? `${currentSession.city}, ${currentSession.country}` : 'Global'"></span>
                            <span>&bull;</span>
                            <span class="font-mono" x-text="currentSession?.client_ip"></span>
                            <span>&bull;</span>
                            <span class="capitalize" x-text="currentSession?.device_type"></span>
                            <span>&bull;</span>
                            <span x-text="currentSession?.formatted_duration"></span>
                        </div>
                    </div>
                </div>

                <!-- Header Actions -->
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" 
                            @click="toggleStarCurrent()"
                            class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-amber-400 transition"
                            :title="currentSession?.is_starred ? 'Unstar Session' : 'Star Session'">
                        <i class="bi text-base" :class="currentSession?.is_starred ? 'bi-star-fill text-amber-400' : 'bi-star'"></i>
                    </button>
                    <button type="button" 
                            @click="quickIgnoreCurrentIp()"
                            class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-rose-400 transition text-xs font-semibold hidden sm:inline-flex items-center gap-1.5"
                            title="Exclude this IP from analytics & recordings">
                        <i class="bi bi-shield-slash"></i>
                        <span>Exclude IP</span>
                    </button>
                    <button type="button" 
                            @click="closePlayerModal()"
                            class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition cursor-pointer">
                        <i class="bi bi-x-lg text-base"></i>
                    </button>
                </div>
            </div>

            <!-- Replayer Canvas Cinema Body -->
            <div class="flex-1 p-4 sm:p-6 overflow-hidden flex flex-col items-center justify-center bg-slate-950 min-h-[460px] relative">
                
                <!-- Loading State -->
                <div x-show="loadingEvents" class="text-center py-12">
                    <div class="w-12 h-12 rounded-full border-4 border-amber-400/20 border-t-amber-400 animate-spin mx-auto mb-3"></div>
                    <div class="text-sm font-bold text-slate-300">Decompressing Session Stream...</div>
                    <div class="text-xs text-slate-500 mt-1">Loading DOM mutations, mouse paths, and scroll events</div>
                </div>

                <!-- Error State -->
                <div x-show="!loadingEvents && playerError" class="text-center py-12 max-w-md">
                    <div class="w-12 h-12 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center mx-auto mb-3">
                        <i class="bi bi-exclamation-triangle-fill text-xl"></i>
                    </div>
                    <div class="text-sm font-bold text-white" x-text="playerError"></div>
                    <div class="text-xs text-slate-400 mt-1">This recording has no DOM events or the file was deleted.</div>
                </div>

                <!-- rrweb-player Mount Container -->
                <div x-show="!loadingEvents && !playerError" 
                     id="rrweb-player-target" 
                     class="w-full flex items-center justify-center overflow-hidden rr-player-cinema">
                </div>
            </div>

            <!-- Footer Details Bar -->
            <div class="px-6 py-3 border-t border-slate-800 bg-slate-900/90 text-xs text-slate-400 flex items-center justify-between flex-wrap gap-2 shrink-0">
                <div class="flex items-center gap-3">
                    <span>Events: <strong class="text-white" x-text="currentSession?.events_count || 0"></strong></span>
                    <span>&bull;</span>
                    <span>Landing: <strong class="text-white" x-text="currentSession?.landing_page || '/'"></strong></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[11px] text-slate-500">Inputs &amp; Cards Masked for Traveler Privacy</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- SETTINGS & RETENTION CONFIG MODAL          -->
    <!-- ========================================== -->
    <div x-show="openSettingsModal" 
         x-transition 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">
        
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden"
             @click.outside="openSettingsModal = false">
            
            <form method="POST" action="{{ route('admin.analytics.recordings.settings') }}">
                @csrf
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <i class="bi bi-sliders text-base"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Recording Retention &amp; Policy</h3>
                    </div>
                    <button type="button" @click="openSettingsModal = false" class="text-slate-400 hover:text-slate-600">
                        <i class="bi bi-x-lg text-base"></i>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <!-- Enable Recording Toggle -->
                    <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                        <div>
                            <div class="text-xs font-bold text-slate-900">Enable Session Recordings</div>
                            <div class="text-[11px] text-slate-500">Record genuine human visitor sessions (bots always excluded)</div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="session_recordings_enabled" value="1" class="sr-only peer" {{ ($stats['is_enabled'] ?? true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                    <!-- Retention Duration Slider -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-bold text-slate-800">Auto-Purge Retention Duration</label>
                            <span class="text-xs font-black text-primary font-mono" x-text="retentionDays + ' Days'"></span>
                        </div>
                        <input type="range" 
                               name="session_recordings_retention_days" 
                               min="3" 
                               max="90" 
                               step="1"
                               x-model="retentionDays"
                               class="w-full accent-primary h-2 bg-slate-200 rounded-lg cursor-pointer">
                        <div class="flex justify-between text-[10px] text-slate-400 mt-1 font-semibold">
                            <span>3 Days (Light)</span>
                            <span>14 Days (Recommended)</span>
                            <span>30 Days</span>
                            <span>90 Days</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-2">
                            Recordings older than this threshold will be automatically removed daily via scheduled cron. <strong>Starred recordings are permanently protected</strong> from auto-purge.
                        </p>
                    </div>

                    <!-- Bulk Purge Actions Box -->
                    <div class="pt-4 border-t border-slate-100 space-y-2">
                        <div class="text-xs font-bold text-slate-800 uppercase tracking-wider text-[10px]">Manual Storage Cleanup</div>
                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click="purgeExpiredNow()"
                                    class="flex-1 py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                                <i class="bi bi-trash3 me-1"></i> Purge Expired ({{ $stats['retention_days'] ?? 14 }}d+)
                            </button>
                            <button type="button" 
                                    @click="purgeAllNow()"
                                    class="py-2 px-3 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition">
                                <i class="bi bi-radioactive me-1"></i> Purge All
                            </button>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="openSettingsModal = false" class="py-2 px-4 rounded-xl border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-100 transition">
                        Cancel
                    </button>
                    <button type="submit" class="py-2 px-5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition">
                        Save Preferences
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden Purge Form for POST dispatch -->
    <form id="purge-form" method="POST" action="{{ route('admin.analytics.recordings.purge') }}" style="display: none;">
        @csrf
        <input type="hidden" name="mode" id="purge-mode" value="expired">
    </form>

</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/rrweb/rrweb-player.min.js') }}"></script>
<script>
function recordingsHubApp() {
    return {
        playerModalOpen: false,
        openSettingsModal: false,
        retentionDays: {{ $stats['retention_days'] ?? 14 }},
        loadingEvents: false,
        playerError: null,
        currentSession: null,
        playerInstance: null,
        starredMap: {},

        isStarred(id, initialVal) {
            if (this.starredMap[id] !== undefined) {
                return this.starredMap[id];
            }
            return initialVal;
        },

        toggleStar(id) {
            const self = this;
            fetch(`/admin/analytics/recordings/${id}/toggle-star`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    self.starredMap[id] = data.is_starred;
                    if (self.currentSession && self.currentSession.id === id) {
                        self.currentSession.is_starred = data.is_starred;
                    }
                }
            })
            .catch(err => console.error('Failed to toggle star:', err));
        },

        toggleStarCurrent() {
            if (this.currentSession && this.currentSession.id) {
                this.toggleStar(this.currentSession.id);
            }
        },

        quickIgnoreCurrentIp() {
            if (!this.currentSession || !this.currentSession.client_ip) return;
            const ip = this.currentSession.client_ip;
            if (!confirm(`Exclude IP ${ip} from all visitor telemetry and recordings?`)) return;

            fetch('/admin/api/visitors/quick-ignore', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ip: ip, reason: 'Excluded via Session Replay' })
            })
            .then(res => res.json())
            .then(data => {
                alert(`IP ${ip} has been added to Ignore Rules.`);
            })
            .catch(err => alert('Failed to exclude IP: ' + err.message));
        },

        playSession(id) {
            this.playerModalOpen = true;
            this.loadingEvents = true;
            this.playerError = null;
            this.currentSession = null;

            const target = document.getElementById('rrweb-player-target');
            if (target) {
                target.innerHTML = '';
            }

            fetch(`/admin/analytics/recordings/${id}/events`)
                .then(res => res.json())
                .then(data => {
                    this.loadingEvents = false;
                    if (!data.success || !data.events || data.events.length < 2) {
                        this.playerError = 'No DOM mutations or screen activity captured in this session.';
                        return;
                    }

                    this.currentSession = data.recording;
                    this.$nextTick(() => {
                        this.mountPlayer(data.events);
                    });
                })
                .catch(err => {
                    this.loadingEvents = false;
                    this.playerError = 'Failed to load recording events: ' + err.message;
                });
        },

        mountPlayer(events) {
            const target = document.getElementById('rrweb-player-target');
            if (!target) return;
            target.innerHTML = '';

            try {
                // Responsive dimensions for cinema player
                const containerW = Math.min(1060, window.innerWidth - 64);
                const containerH = Math.min(600, Math.round(containerW * 0.58));

                this.playerInstance = new rrwebPlayer({
                    target: target,
                    props: {
                        events: events,
                        width: containerW,
                        height: containerH,
                        autoPlay: true,
                        showController: true,
                        speedOption: [1, 2, 4, 8],
                        skipInactive: true,
                    }
                });
            } catch (err) {
                this.playerError = 'Player initialization error: ' + err.message;
            }
        },

        closePlayerModal() {
            this.playerModalOpen = false;
            const target = document.getElementById('rrweb-player-target');
            if (target) {
                target.innerHTML = '';
            }
            this.playerInstance = null;
        },

        confirmDelete(id) {
            if (!confirm('Are you sure you want to delete this session recording? This will free up storage immediately.')) {
                return;
            }

            fetch(`/admin/analytics/recordings/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const row = document.getElementById(`rec-row-${id}`);
                    if (row) row.remove();
                }
            })
            .catch(err => alert('Failed to delete recording: ' + err.message));
        },

        purgeExpiredNow() {
            if (!confirm(`Purge all recordings older than ${this.retentionDays} days? Starred recordings will NOT be deleted.`)) {
                return;
            }
            document.getElementById('purge-mode').value = 'expired';
            document.getElementById('purge-form').submit();
        },

        purgeAllNow() {
            if (!confirm('CAUTION: Are you sure you want to purge ALL session recordings? Starred recordings will remain safe.')) {
                return;
            }
            document.getElementById('purge-mode').value = 'all';
            document.getElementById('purge-form').submit();
        }
    };
}
</script>
@endpush
