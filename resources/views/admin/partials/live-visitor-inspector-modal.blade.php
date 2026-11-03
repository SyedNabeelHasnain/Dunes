<!-- ── Real-Time Live Visitor Journey & Friction Inspector Modal ────────────────────────────── -->
<div x-data="liveVisitorInspectorModal()" 
     @inspect-visitor.window="openModal($event.detail.sessionId, $event.detail.clientIp)"
     x-show="isOpen" 
     x-cloak 
     class="fixed inset-0 z-50 overflow-y-auto" 
     role="dialog" 
     aria-modal="true" 
     style="display: none;">

    <!-- Dark Backdrop -->
    <div x-show="isOpen"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs transition-opacity"
         @click="closeModal()"></div>

    <!-- Modal Dialog Panel -->
    <div class="relative min-h-screen flex items-center justify-center p-3 sm:p-4 text-center">
        <div x-show="isOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
             class="relative w-full max-w-4xl bg-white rounded-3xl shadow-2xl border border-slate-200 text-start overflow-hidden flex flex-col max-h-[92vh]"
             @click.stop>

            <!-- 1. Modal Top Bar -->
            <div class="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800 shrink-0">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-primary/20 text-primary flex items-center justify-center text-lg">
                        <i class="bi bi-activity"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm sm:text-base font-black text-white tracking-tight">Live Visitor Journey &amp; Friction Inspector</h3>
                            <template x-if="data && data.visitor && data.visitor.is_active_now">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 inline-flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    LIVE NOW
                                </span>
                            </template>
                        </div>
                        <p class="text-[11px] text-slate-400">Step-by-step clickstream, dwell metrics, customer lead match, and obstacle diagnostics.</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Live Auto-Refresh Toggle -->
                    <button type="button" 
                            @click="autoRefresh = !autoRefresh" 
                            :class="autoRefresh ? 'bg-emerald-600/30 text-emerald-300 border-emerald-500/40' : 'bg-slate-800 text-slate-400 border-slate-700'"
                            class="px-2.5 py-1 rounded-lg text-xs font-bold border transition inline-flex items-center gap-1.5 cursor-pointer"
                            :title="autoRefresh ? 'Live Auto-Polling Active (every 5s)' : 'Click to enable live auto-polling'">
                        <i class="bi bi-broadcast text-[11px]" :class="autoRefresh ? 'animate-pulse text-emerald-400' : ''"></i>
                        <span class="hidden sm:inline" x-text="autoRefresh ? 'Live Sync On' : 'Live Sync Off'"></span>
                    </button>

                    <button type="button" 
                            @click="refreshData(true)" 
                            :disabled="loading"
                            class="p-1.5 rounded-lg bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition cursor-pointer" 
                            title="Refresh Session Telemetry">
                        <i class="bi bi-arrow-clockwise text-xs" :class="loading ? 'animate-spin' : ''"></i>
                    </button>

                    <button type="button" 
                            @click="closeModal()" 
                            class="p-1.5 rounded-lg bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition cursor-pointer">
                        <i class="bi bi-x-lg text-xs"></i>
                    </button>
                </div>
            </div>

            <!-- 2. Scrollable Body -->
            <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-5 bg-slate-50/50">

                <!-- Loading State -->
                <template x-if="loading && !data">
                    <div class="py-16 text-center">
                        <div class="inline-block animate-spin text-primary text-3xl mb-3">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                        <p class="text-xs font-bold text-slate-600">Reconstructing visitor clickstream and friction diagnostics...</p>
                    </div>
                </template>

                <template x-if="data && data.success">
                    <div class="space-y-5">

                        <!-- A. Hero Metric Cards (IP, Location, Device, Attribution) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <!-- IP & Geolocation -->
                            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-2xs">
                                <div class="flex items-center justify-between text-slate-400 mb-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider">Visitor Identity</span>
                                    <i class="bi bi-geo-alt-fill text-primary text-xs"></i>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono font-black text-slate-900 text-xs truncate" x-text="data.visitor.client_ip"></span>
                                    <button type="button" @click="navigator.clipboard.writeText(data.visitor.client_ip); Swal.fire({toast:true, position:'top-end', icon:'success', title:'IP copied to clipboard', timer:1500, showConfirmButton:false});" class="text-slate-400 hover:text-primary" title="Copy IP">
                                        <i class="bi bi-copy text-[10px]"></i>
                                    </button>
                                </div>
                                <div class="text-[11px] text-slate-500 truncate mt-0.5" x-text="(data.visitor.city || 'Dubai') + ', ' + (data.visitor.country || 'UAE')"></div>
                                <div class="text-[10px] text-slate-400 truncate mt-0.5" x-text="data.visitor.isp || 'Telecom Provider'"></div>
                            </div>

                            <!-- Tech Stack & Viewport -->
                            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-2xs">
                                <div class="flex items-center justify-between text-slate-400 mb-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider">Device &amp; Platform</span>
                                    <i class="bi bi-laptop text-indigo-500 text-xs"></i>
                                </div>
                                <div class="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                                    <i class="bi" :class="data.visitor.device_type === 'Mobile' ? 'bi-phone' : 'bi-display'"></i>
                                    <span x-text="data.visitor.device_type + ' (' + data.visitor.os_name + ')'"></span>
                                </div>
                                <div class="text-[11px] text-slate-500 truncate mt-0.5" x-text="data.visitor.browser_name + (data.visitor.browser_version ? ' ' + data.visitor.browser_version : '')"></div>
                                <div class="text-[10px] text-slate-400 mt-0.5" x-text="'Total Steps: ' + data.visitor.total_steps + ' views'"></div>
                            </div>

                            <!-- Acquisition & Attribution -->
                            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-2xs">
                                <div class="flex items-center justify-between text-slate-400 mb-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider">Acquisition Source</span>
                                    <i class="bi bi-signpost-split-fill text-amber-500 text-xs"></i>
                                </div>
                                <div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase"
                                          :class="{
                                              'bg-emerald-100 text-emerald-800': data.visitor.channel === 'Google Ads',
                                              'bg-blue-100 text-blue-800': data.visitor.channel === 'Meta Ads' || data.visitor.channel === 'Social',
                                              'bg-indigo-100 text-indigo-800': data.visitor.channel === 'Organic Search',
                                              'bg-slate-100 text-slate-800': data.visitor.channel === 'Direct'
                                          }"
                                          x-text="data.visitor.channel"></span>
                                </div>
                                <div class="text-[11px] text-slate-500 truncate mt-1" x-text="data.visitor.utm_campaign ? 'Campaign: ' + data.visitor.utm_campaign : 'Referrer: ' + (data.visitor.referrer_url || 'Direct')"></div>
                                <div class="text-[10px] text-slate-400 truncate mt-0.5" x-text="'Entry: ' + data.visitor.landing_page"></div>
                            </div>

                            <!-- Session Timing & Dwell -->
                            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-2xs">
                                <div class="flex items-center justify-between text-slate-400 mb-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider">Time on Site</span>
                                    <i class="bi bi-stopwatch-fill text-rose-500 text-xs"></i>
                                </div>
                                <div class="font-mono font-black text-slate-900 text-xs" x-text="Math.floor(data.visitor.total_duration_sec / 60) + 'm ' + (data.visitor.total_duration_sec % 60) + 's'"></div>
                                <div class="text-[11px] text-slate-500 truncate mt-0.5" x-text="'First seen: ' + data.visitor.first_seen"></div>
                                <div class="text-[10px] text-emerald-600 font-bold mt-0.5 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span x-text="'Last active: ' + data.visitor.last_active"></span>
                                </div>
                            </div>
                        </div>

                        <!-- B. Identified Customer Lead Card (If draft or inquiry submitted) -->
                        <template x-if="data.lead">
                            <div class="p-4 rounded-2xl bg-linear-to-r from-amber-50 to-orange-50 border border-amber-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-base shadow-xs shrink-0">
                                        <i class="bi bi-person-check-fill"></i>
                                    </span>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-extrabold text-slate-900 text-sm" x-text="data.lead.name || 'Identified Tourist'"></span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-200 text-amber-900 uppercase" x-text="data.lead.type.replace('_', ' ')"></span>
                                            <span class="font-mono font-bold text-xs text-slate-500" x-text="'#' + data.lead.reference"></span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-600 mt-1">
                                            <template x-if="data.lead.email">
                                                <span class="inline-flex items-center gap-1"><i class="bi bi-envelope text-slate-400"></i> <span x-text="data.lead.email"></span></span>
                                            </template>
                                            <template x-if="data.lead.phone">
                                                <span class="inline-flex items-center gap-1"><i class="bi bi-telephone text-slate-400"></i> <span x-text="data.lead.phone"></span></span>
                                            </template>
                                            <template x-if="data.lead.tour_name">
                                                <span class="inline-flex items-center gap-1"><i class="bi bi-compass text-amber-600"></i> <span class="font-bold text-slate-800" x-text="data.lead.tour_name"></span></span>
                                            </template>
                                            <template x-if="data.lead.total > 0">
                                                <span class="inline-flex items-center gap-1 font-black text-emerald-700">AED <span x-text="Number(data.lead.total).toFixed(2)"></span></span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <template x-if="data.lead.phone">
                                        <a :href="'https://wa.me/' + data.lead.phone.replace(/[^0-9]/g, '')" target="_blank" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition inline-flex items-center gap-1.5 shadow-2xs">
                                            <i class="bi bi-whatsapp"></i> Chat WhatsApp
                                        </a>
                                    </template>
                                    <a :href="data.lead.url" target="_blank" class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold transition inline-flex items-center gap-1.5 shadow-2xs">
                                        <i class="bi bi-box-arrow-up-right text-slate-400"></i> Open Record
                                    </a>
                                </div>
                            </div>
                        </template>

                        <!-- C. Friction & Problem Diagnostic Box (Crucial Improvement Intelligence) -->
                        <div>
                            <div class="flex items-center justify-between mb-2.5">
                                <h4 class="text-xs font-black uppercase tracking-wider text-slate-600 flex items-center gap-2">
                                    <i class="bi bi-lightning-charge-fill text-amber-500"></i>
                                    <span>Obstacle &amp; Friction Diagnostics</span>
                                </h4>
                                <template x-if="data.friction_alerts && data.friction_alerts.length > 0">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-800" x-text="data.friction_alerts.length + ' Friction Alerts Detected'"></span>
                                </template>
                            </div>

                            <template x-if="!data.friction_alerts || data.friction_alerts.length === 0">
                                <div class="p-3.5 rounded-2xl bg-white border border-slate-200 text-xs text-slate-500 flex items-center gap-2.5">
                                    <i class="bi bi-check-circle-fill text-emerald-500 text-base"></i>
                                    <span>Smooth Guest Experience: No errors, checkout drop-offs, or promo code rejections detected in this session.</span>
                                </div>
                            </template>

                            <template x-if="data.friction_alerts && data.friction_alerts.length > 0">
                                <div class="space-y-2.5">
                                    <template x-for="(alert, aIndex) in data.friction_alerts" :key="aIndex">
                                        <div class="p-3.5 rounded-2xl border flex items-start justify-between gap-3"
                                             :class="{
                                                 'bg-rose-50/80 border-rose-200 text-rose-900': alert.severity === 'rose',
                                                 'bg-amber-50/80 border-amber-200 text-amber-900': alert.severity === 'amber',
                                                 'bg-orange-50/80 border-orange-200 text-orange-900': alert.severity === 'orange',
                                                 'bg-indigo-50/80 border-indigo-200 text-indigo-900': alert.severity === 'indigo',
                                                 'bg-emerald-50/80 border-emerald-200 text-emerald-900': alert.severity === 'emerald'
                                             }">
                                            <div class="flex items-start gap-2.5">
                                                <i class="bi text-base mt-0.5" :class="alert.icon"></i>
                                                <div>
                                                    <div class="font-extrabold text-xs" x-text="alert.title"></div>
                                                    <div class="text-xs opacity-90 mt-0.5" x-text="alert.message"></div>
                                                </div>
                                            </div>
                                            <template x-if="alert.action_url">
                                                <a :href="alert.action_url" target="_blank" class="px-2.5 py-1 rounded-lg bg-white/90 hover:bg-white text-slate-800 font-bold text-[11px] border border-slate-200 shadow-2xs transition shrink-0 inline-flex items-center gap-1">
                                                    <span x-text="alert.action_label || 'Inspect'"></span>
                                                    <i class="bi bi-arrow-right text-[10px]"></i>
                                                </a>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- D. Chronological Clickstream / Timeline -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-xs font-black uppercase tracking-wider text-slate-600 flex items-center gap-2">
                                    <i class="bi bi-clock-history text-primary"></i>
                                    <span>Chronological Clickstream Journey (Newest First)</span>
                                </h4>
                                <span class="text-[11px] text-slate-400 font-medium" x-text="data.journey_steps.length + ' Recorded Steps'"></span>
                            </div>

                            <div class="bg-white rounded-2xl border border-slate-200 divide-y divide-slate-100 overflow-hidden shadow-2xs">
                                <template x-for="(step, sIndex) in data.journey_steps" :key="sIndex">
                                    <div class="p-3.5 hover:bg-slate-50/70 transition flex items-center justify-between gap-3 text-xs"
                                         :class="step.is_current ? 'bg-primary/5 border-l-4 border-l-primary' : ''">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <!-- Step Badge -->
                                            <span class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-[11px] shrink-0"
                                                  :class="{
                                                      'bg-primary text-white': step.is_current,
                                                      'bg-rose-100 text-rose-800': step.type === 'error',
                                                      'bg-amber-100 text-amber-800': step.type === 'draft',
                                                      'bg-emerald-100 text-emerald-800': step.type === 'whatsapp',
                                                      'bg-slate-100 text-slate-700': step.type === 'pageview'
                                                  }"
                                                  x-text="'#' + step.step_number"></span>

                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="font-extrabold text-slate-900 truncate" x-text="step.title"></span>
                                                    <template x-if="step.is_current">
                                                        <span class="px-1.5 py-0.2 rounded bg-primary text-white text-[9px] font-black uppercase">Current</span>
                                                    </template>
                                                    <template x-if="step.type === 'error'">
                                                        <span class="px-1.5 py-0.2 rounded bg-rose-600 text-white text-[9px] font-black uppercase">Error 404</span>
                                                    </template>
                                                    <template x-if="step.type === 'draft'">
                                                        <span class="px-1.5 py-0.2 rounded bg-amber-500 text-white text-[9px] font-black uppercase">Draft Cart</span>
                                                    </template>
                                                </div>
                                                <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-400">
                                                    <span class="font-mono text-slate-500 truncate max-w-xs sm:max-w-md" x-text="step.uri"></span>
                                                    <span>&bull;</span>
                                                    <span x-text="step.timestamp"></span>
                                                    <span>&bull;</span>
                                                    <span x-text="step.relative_time"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2.5 shrink-0">
                                            <!-- Dwell Time Badge -->
                                            <span class="px-2 py-1 rounded-lg bg-slate-100 font-mono text-[11px] font-bold text-slate-600" title="Time spent on this page" x-text="step.dwell_formatted"></span>
                                            
                                            <!-- View Live Page in New Tab -->
                                            <a :href="step.url" target="_blank" class="p-1 rounded-lg text-slate-400 hover:text-primary transition" title="Open this page on live website">
                                                <i class="bi bi-box-arrow-up-right text-xs"></i>
                                            </a>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>
                </template>
            </div>

            <!-- 3. Modal Bottom Administrative Action Bar -->
            <div class="px-5 py-3.5 bg-white border-t border-slate-200 flex flex-wrap items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <template x-if="data && data.visitor">
                        <div class="flex items-center gap-2">
                            <!-- 1-Click Exclude IP from Analytics -->
                            <button type="button" 
                                    @click="quickIgnoreIp(data.visitor.client_ip)" 
                                    :disabled="data.visitor.is_ignored_ip"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition border cursor-pointer"
                                    :class="data.visitor.is_ignored_ip ? 'bg-slate-100 text-slate-400 border-slate-200' : 'bg-white hover:bg-slate-50 text-slate-700 border-slate-300 shadow-2xs'">
                                <i class="bi bi-eye-slash-fill text-slate-500"></i>
                                <span x-text="data.visitor.is_ignored_ip ? 'Already Excluded from Analytics' : 'Exclude IP from Analytics'"></span>
                            </button>

                            <!-- 1-Click Block IP Firewall -->
                            <button type="button" 
                                    @click="quickBlockIp(data.visitor.client_ip)" 
                                    :disabled="data.visitor.is_blocked_ip"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition border cursor-pointer"
                                    :class="data.visitor.is_blocked_ip ? 'bg-rose-100 text-rose-800 border-rose-200' : 'bg-rose-50 hover:bg-rose-100 text-rose-700 border-rose-200 shadow-2xs'">
                                <i class="bi bi-shield-x"></i>
                                <span x-text="data.visitor.is_blocked_ip ? 'Already Blocked by Firewall' : 'Block IP Firewall'"></span>
                            </button>
                        </div>
                    </template>
                </div>

                <div class="flex items-center gap-2">
                    <template x-if="data && data.visitor && data.visitor.current_page">
                        <a :href="url(data.visitor.current_page)" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition">
                            <i class="bi bi-globe2"></i> View Current Page
                        </a>
                    </template>
                    <button type="button" @click="closeModal()" class="px-4 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer">
                        Close
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function liveVisitorInspectorModal() {
    return {
        isOpen: false,
        sessionId: null,
        clientIp: null,
        loading: false,
        autoRefresh: true,
        data: null,
        pollTimer: null,

        openModal(sessionId, clientIp) {
            this.sessionId = sessionId;
            this.clientIp = clientIp;
            this.isOpen = true;
            this.data = null;
            this.refreshData();
            this.startPoller();
        },

        closeModal() {
            this.isOpen = false;
            this.stopPoller();
        },

        startPoller() {
            this.stopPoller();
            this.pollTimer = setInterval(() => {
                if (this.isOpen && this.autoRefresh && !this.loading) {
                    this.refreshData(false);
                }
            }, 5000);
        },

        stopPoller() {
            if (this.pollTimer) {
                clearInterval(this.pollTimer);
                this.pollTimer = null;
            }
        },

        async refreshData(showLoader = true) {
            if (showLoader) this.loading = true;
            try {
                const url = `/admin/api/visitor-journey/${encodeURIComponent(this.sessionId || '')}?client_ip=${encodeURIComponent(this.clientIp || '')}`;
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await res.json();
                if (json.success) {
                    this.data = json;
                }
            } catch (err) {
                console.error("Live journey inspector error:", err);
            } finally {
                this.loading = false;
            }
        },

        async quickIgnoreIp(ip) {
            const confirmed = await Swal.fire({
                title: 'Exclude IP from Analytics?',
                text: `Browsing activities from ${ip} will be completely ignored and past logs purged.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#F58F43',
                confirmButtonText: 'Yes, Exclude IP'
            });
            if (!confirmed.isConfirmed) return;

            try {
                const res = await fetch('/admin/api/visitors/quick-ignore', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ ip: ip })
                });
                const result = await res.json();
                if (result.success) {
                    Swal.fire({ icon: 'success', title: 'Excluded', text: result.message, timer: 3000 });
                    if (this.data && this.data.visitor) this.data.visitor.is_ignored_ip = true;
                }
            } catch (e) {
                Swal.fire('Error', 'Failed to exclude IP.', 'error');
            }
        },

        async quickBlockIp(ip) {
            const confirmed = await Swal.fire({
                title: 'Block IP Address via Firewall?',
                text: `IP ${ip} will receive an immediate 403 Forbidden page and be denied access to all public pages.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#E11D48',
                confirmButtonText: 'Yes, Block IP'
            });
            if (!confirmed.isConfirmed) return;

            try {
                const res = await fetch('/admin/api/visitors/quick-block', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ ip: ip })
                });
                const result = await res.json();
                if (result.success) {
                    Swal.fire({ icon: 'success', title: 'Blocked', text: result.message, timer: 3000 });
                    if (this.data && this.data.visitor) this.data.visitor.is_blocked_ip = true;
                }
            } catch (e) {
                Swal.fire('Error', 'Failed to block IP.', 'error');
            }
        },

        url(path) {
            if (!path) return '/';
            return path.startsWith('http') ? path : '/' + path.replace(/^\//, '');
        }
    };
}
</script>
