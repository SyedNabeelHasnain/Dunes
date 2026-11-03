@extends('layouts.admin')

@section('page_title', 'Heatmaps & Scroll Depth Visualizer')

@push('styles')
<style>
    .heatmap-frame-container {
        position: relative;
        width: 100%;
        min-height: 850px;
        background: #0f172a;
        border-radius: 1.25rem;
        overflow: hidden;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }
    .heatmap-overlay-canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 10;
    }
    .scroll-depth-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 10;
        display: flex;
        flex-direction: column;
    }
    .scroll-bracket-line {
        position: absolute;
        left: 0;
        right: 0;
        border-top: 2px dashed rgba(255, 255, 255, 0.7);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 1rem;
        font-family: ui-monospace, SFMono-Regular, monospace;
        font-size: 11px;
        font-weight: 800;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.8);
    }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="heatmapsHubApp()">

    <!-- Header & Action Pills -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Heatmap &amp; Scroll Analytics</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-900 border border-orange-300 inline-flex items-center gap-1.5">
                    <i class="bi bi-fire text-orange-600"></i>
                    Self-Hosted (Zero-Cost Canvas)
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Aggregating Clicks
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Visual click intensity density and vertical scroll drop-off gradients: see exactly where guests focus, tap, and abandon pages.
            </p>
        </div>

        <!-- Mode Toggle & Navigation -->
        <div class="flex items-center gap-2 flex-wrap">
            <div class="inline-flex p-1 rounded-2xl bg-slate-100 border border-slate-200">
                <button type="button" 
                        @click="setMode('clicks')" 
                        :class="mode === 'clicks' ? 'bg-white text-slate-900 shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                        class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5">
                    <i class="bi bi-cursor-fill text-amber-500"></i>
                    <span>Click Heatmap</span>
                </button>
                <button type="button" 
                        @click="setMode('scroll')" 
                        :class="mode === 'scroll' ? 'bg-white text-slate-900 shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                        class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5">
                    <i class="bi bi-water text-cyan-500"></i>
                    <span>Scroll Depth Map</span>
                </button>
            </div>

            <a href="{{ route('admin.analytics.recordings.index') }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold shadow-2xs transition">
                <i class="bi bi-camera-reels-fill text-amber-500"></i>
                <span>Session Replays</span>
            </a>

            <a href="{{ route('admin.analytics.live-visitors') }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Live Hub</span>
            </a>
        </div>
    </div>

    <!-- Controls & Filters Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-center">
            
            <!-- Page Selector -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Target Page</label>
                <div class="relative">
                    <i class="bi bi-link-45deg absolute left-3 top-2.5 text-slate-400 text-base"></i>
                    <select x-model="selectedPage" 
                            @change="reloadHeatmapData()" 
                            class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary font-mono font-semibold text-slate-800">
                        @foreach($pages as $p)
                            <option value="{{ $p['page_path'] }}" {{ ($selectedPage === $p['page_path']) ? 'selected' : '' }}>
                                {{ $p['page_path'] }} ({{ $p['count'] ?? 0 }} clicks)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Device Selector -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Device Form Factor</label>
                <select x-model="selectedDevice" 
                        @change="reloadHeatmapData()" 
                        class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="all">All Devices Combined</option>
                    <option value="desktop">Desktop Only</option>
                    <option value="mobile">Mobile Devices</option>
                    <option value="tablet">Tablets</option>
                </select>
            </div>

            <!-- Time Window -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Time Horizon</label>
                <select x-model="selectedDays" 
                        @change="reloadHeatmapData()" 
                        class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="7">Last 7 Days</option>
                    <option value="30">Last 30 Days</option>
                    <option value="90">Last 90 Days</option>
                </select>
            </div>

            <!-- Heat Radius Control (Click map mode only) -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Hotspot Radius</label>
                    <span class="text-[11px] font-mono font-bold text-primary" x-text="heatmapRadius + 'px'"></span>
                </div>
                <input type="range" 
                       min="15" 
                       max="60" 
                       step="5" 
                       x-model="heatmapRadius" 
                       @input="redrawHeatmap()" 
                       class="w-full accent-primary h-2 bg-slate-200 rounded-lg cursor-pointer">
            </div>
        </div>
    </div>

    <!-- Main Workspace Grid: Canvas Frame + Insights Sidebar -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        <!-- Left: Interactive Page Preview Frame with Heatmap Overlay -->
        <div class="lg:col-span-3 space-y-3">
            
            <!-- Frame Header Bar -->
            <div class="bg-slate-900 text-slate-300 rounded-t-2xl px-4 py-2.5 flex items-center justify-between text-xs border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span>
                    <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
                    <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                    <span class="font-mono text-slate-400 ms-2 text-[11px]" x-text="window.location.origin + selectedPage"></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded bg-slate-800 text-[11px] text-slate-300" x-text="mode === 'clicks' ? `${currentClicks.length} Clicks Loaded` : 'Scroll Drop-off Gradient'"></span>
                    <button type="button" @click="reloadHeatmapData()" class="text-slate-400 hover:text-white transition" title="Refresh Map">
                        <i class="bi bi-arrow-repeat" :class="loading ? 'animate-spin text-primary' : ''"></i>
                    </button>
                </div>
            </div>

            <!-- Heatmap Frame Container -->
            <div class="heatmap-frame-container rounded-b-2xl" id="heatmap-viewport">
                
                <!-- Underlay Page Iframe -->
                <iframe :src="selectedPage" 
                        id="heatmap-iframe" 
                        class="w-full h-full border-0 rounded-b-2xl bg-white" 
                        style="min-height: 850px; opacity: 0.92;"
                        loading="lazy"></iframe>

                <!-- Mode 1: Click Heatmap Overlay Container -->
                <div x-show="mode === 'clicks'" 
                     id="heatmap-canvas-container" 
                     class="heatmap-overlay-canvas"></div>

                <!-- Mode 2: Scroll Depth Visualizer Overlay -->
                <div x-show="mode === 'scroll'" class="scroll-depth-overlay">
                    
                    <!-- 25% Scroll Line -->
                    <div class="scroll-bracket-line bg-emerald-500/10 text-emerald-300" style="top: 25%; height: 25%;">
                        <span class="bg-slate-900/90 px-2 py-1 rounded text-emerald-400 border border-emerald-500/40">
                            25% Depth &bull; Top Section
                        </span>
                        <span class="bg-emerald-500 text-white px-2.5 py-1 rounded-full font-black text-xs shadow-md" x-text="(scrollMetrics?.reach_25 || 100) + '% Reached'"></span>
                    </div>

                    <!-- 50% Scroll Line (Mid-page Fold) -->
                    <div class="scroll-bracket-line bg-amber-500/10 text-amber-300" style="top: 50%; height: 25%;">
                        <span class="bg-slate-900/90 px-2 py-1 rounded text-amber-400 border border-amber-500/40">
                            50% Depth &bull; Core Catalog Fold
                        </span>
                        <span class="bg-amber-500 text-white px-2.5 py-1 rounded-full font-black text-xs shadow-md" x-text="(scrollMetrics?.reach_50 || 0) + '% Reached'"></span>
                    </div>

                    <!-- 75% Scroll Line -->
                    <div class="scroll-bracket-line bg-orange-500/10 text-orange-300" style="top: 75%; height: 25%;">
                        <span class="bg-slate-900/90 px-2 py-1 rounded text-orange-400 border border-orange-500/40">
                            75% Depth &bull; Deep Content &amp; Reviews
                        </span>
                        <span class="bg-orange-500 text-white px-2.5 py-1 rounded-full font-black text-xs shadow-md" x-text="(scrollMetrics?.reach_75 || 0) + '% Reached'"></span>
                    </div>

                    <!-- 100% Scroll Line (Footer) -->
                    <div class="scroll-bracket-line bg-rose-500/15 text-rose-300" style="top: 98%;">
                        <span class="bg-slate-900/90 px-2 py-1 rounded text-rose-400 border border-rose-500/40">
                            100% Bottom &bull; Footer &amp; FAQs
                        </span>
                        <span class="bg-rose-500 text-white px-2.5 py-1 rounded-full font-black text-xs shadow-md" x-text="(scrollMetrics?.reach_100 || 0) + '% Reached'"></span>
                    </div>
                </div>

                <!-- Empty State Message when zero data -->
                <div x-show="!loading && currentClicks.length === 0 && mode === 'clicks'" 
                     class="absolute inset-0 flex flex-col items-center justify-center bg-slate-950/70 backdrop-blur-xs text-center p-6 z-20">
                    <div class="w-14 h-14 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center mx-auto mb-3 text-2xl">
                        <i class="bi bi-hand-index-thumb"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">No Clicks Recorded on this Page Yet</h3>
                    <p class="text-xs text-slate-400 max-w-sm mt-1">
                        Browse the public website from a non-ignored IP to generate fresh click coordinates, or select another target page above.
                    </p>
                </div>
            </div>
        </div>

        <!-- Right: Analytics Summary Sidebar -->
        <div class="space-y-4">
            
            <!-- Quick KPI Cards -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Page Interaction Stats</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/10 text-primary" x-text="selectedDays + ' Days'"></span>
                </div>

                <!-- Total Clicks -->
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Recorded Clicks</div>
                    <div class="text-3xl font-black text-slate-900 mt-1" x-text="totalClicksCount"></div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Normalized $(x,y)$ coordinates</div>
                </div>

                <!-- Avg Scroll Depth -->
                <div class="pt-3 border-t border-slate-100">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Average Scroll Reach</div>
                    <div class="text-3xl font-black text-slate-900 mt-1 flex items-baseline gap-1">
                        <span x-text="scrollMetrics?.avg_scroll_percent || 0"></span>
                        <span class="text-base font-bold text-slate-400">%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 mt-2 overflow-hidden">
                        <div class="bg-cyan-500 h-2 rounded-full transition-all duration-500" :style="`width: ${scrollMetrics?.avg_scroll_percent || 0}%`"></div>
                    </div>
                </div>
            </div>

            <!-- Top Clicked Elements on this Page -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Top Clicked Elements</span>
                    <i class="bi bi-pie-chart-fill text-amber-500 text-sm"></i>
                </div>

                <div class="space-y-2.5">
                    <template x-for="(el, idx) in topElements" :key="idx">
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 hover:border-slate-200 transition">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-900 mb-1">
                                <span class="truncate max-w-[140px]" x-text="el.target_text || 'Unnamed Element'"></span>
                                <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 text-[10px] font-extrabold" x-text="el.click_count + ' clicks'"></span>
                            </div>
                            <div class="text-[10px] text-slate-400 flex items-center justify-between">
                                <span class="font-mono text-slate-500" x-text="'<' + (el.target_tag || 'ELEMENT') + '>'"></span>
                                <span x-text="totalClicksCount > 0 ? Math.round((el.click_count / totalClicksCount) * 100) + '% of clicks' : '0%'"></span>
                            </div>
                        </div>
                    </template>
                    <template x-if="topElements.length === 0">
                        <div class="text-center py-6 text-xs text-slate-400">
                            No element click clusters identified on this page yet.
                        </div>
                    </template>
                </div>
            </div>

            <!-- Scroll Depth Reach Funnel Breakdown -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Scroll Drop-off Funnel</span>
                    <i class="bi bi-funnel-fill text-cyan-500 text-sm"></i>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <div class="flex justify-between font-bold text-slate-700 mb-1">
                            <span>Top 25% (Header &amp; Hero)</span>
                            <span class="font-mono" x-text="(scrollMetrics?.reach_25 || 100) + '%'"></span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-emerald-500 h-1.5 rounded-full" :style="`width: ${scrollMetrics?.reach_25 || 100}%`"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between font-bold text-slate-700 mb-1">
                            <span>Mid 50% (Tour Cards &amp; Pricing)</span>
                            <span class="font-mono" x-text="(scrollMetrics?.reach_50 || 0) + '%'"></span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-amber-500 h-1.5 rounded-full" :style="`width: ${scrollMetrics?.reach_50 || 0}%`"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between font-bold text-slate-700 mb-1">
                            <span>Deep 75% (Reviews &amp; Itinerary)</span>
                            <span class="font-mono" x-text="(scrollMetrics?.reach_75 || 0) + '%'"></span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-orange-500 h-1.5 rounded-full" :style="`width: ${scrollMetrics?.reach_75 || 0}%`"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between font-bold text-slate-700 mb-1">
                            <span>Footer 100% (Complete Page)</span>
                            <span class="font-mono" x-text="(scrollMetrics?.reach_100 || 0) + '%'"></span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-rose-500 h-1.5 rounded-full" :style="`width: ${scrollMetrics?.reach_100 || 0}%`"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/heatmap/heatmap.min.js') }}"></script>
<script>
function heatmapsHubApp() {
    return {
        mode: 'clicks', // 'clicks' or 'scroll'
        selectedPage: '{{ $selectedPage }}',
        selectedDevice: '{{ $selectedDevice }}',
        selectedDays: '{{ $days }}',
        heatmapRadius: 35,
        loading: false,
        totalClicksCount: {{ $heatmapData['total_clicks'] ?? 0 }},
        currentClicks: @json($heatmapData['clicks'] ?? []),
        topElements: @json($heatmapData['top_elements'] ?? []),
        scrollMetrics: @json($heatmapData['scroll'] ?? []),
        heatmapInstance: null,

        init() {
            this.$nextTick(() => {
                this.initHeatmapCanvas();
                this.redrawHeatmap();
            });
        },

        setMode(newMode) {
            this.mode = newMode;
            if (this.mode === 'clicks') {
                this.$nextTick(() => {
                    this.redrawHeatmap();
                });
            }
        },

        initHeatmapCanvas() {
            const container = document.getElementById('heatmap-canvas-container');
            if (!container || typeof h337 === 'undefined') return;

            container.innerHTML = '';
            this.heatmapInstance = h337.create({
                container: container,
                radius: parseInt(this.heatmapRadius),
                maxOpacity: 0.82,
                minOpacity: 0.15,
                blur: 0.85
            });
        },

        redrawHeatmap() {
            if (!this.heatmapInstance) {
                this.initHeatmapCanvas();
            }
            if (!this.heatmapInstance) return;

            const container = document.getElementById('heatmap-canvas-container');
            if (!container) return;

            const w = container.offsetWidth || 1000;
            const h = container.offsetHeight || 850;

            const points = [];
            this.currentClicks.forEach(c => {
                const px = Math.round(c.x_ratio * w);
                const py = Math.round(c.y_ratio * h);
                if (px >= 0 && px <= w && py >= 0 && py <= h) {
                    points.push({
                        x: px,
                        y: py,
                        value: 1
                    });
                }
            });

            this.heatmapInstance.setData({
                max: Math.max(3, Math.ceil(points.length / 15)),
                data: points
            });
        },

        reloadHeatmapData() {
            this.loading = true;
            const params = new URLSearchParams({
                page: this.selectedPage,
                device: this.selectedDevice,
                days: this.selectedDays
            });

            fetch(`/admin/analytics/heatmaps/data?${params.toString()}`)
                .then(res => res.json())
                .then(res => {
                    this.loading = false;
                    if (res.success && res.data) {
                        this.totalClicksCount = res.data.total_clicks || 0;
                        this.currentClicks = res.data.clicks || [];
                        this.topElements = res.data.top_elements || [];
                        this.scrollMetrics = res.data.scroll || {};
                        this.$nextTick(() => {
                            this.redrawHeatmap();
                        });
                    }
                })
                .catch(err => {
                    this.loading = false;
                    console.error('Heatmap load error:', err);
                });
        }
    };
}
</script>
@endpush
