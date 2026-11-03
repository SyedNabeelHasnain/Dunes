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
             class="relative w-full max-w-5xl bg-white rounded-3xl shadow-2xl border border-slate-200 text-start overflow-hidden flex flex-col max-h-[94vh]"
             @click.stop>

            <!-- 1. Modal Top Bar -->
            <div class="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800 shrink-0">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-primary/20 text-primary flex items-center justify-center text-lg shadow-2xs">
                        <i class="bi bi-display"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm sm:text-base font-black text-white tracking-tight">Live Visitor Journey &amp; Screen Inspector</h3>
                            <template x-if="data && data.visitor && data.visitor.is_active_now">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 inline-flex items-center gap-1.5 shadow-2xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    LIVE NOW
                                </span>
                            </template>
                        </div>
                        <p class="text-[11px] text-slate-400">Real-time screen viewing, live dwell timer, step-by-step clickstream, and friction diagnostics.</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Live Auto-Refresh Toggle -->
                    <button type="button" 
                            @click="autoRefresh = !autoRefresh; if (autoRefresh) startPoller(); else stopPoller();" 
                            :class="autoRefresh ? 'bg-emerald-600/30 text-emerald-300 border-emerald-500/40' : 'bg-slate-800 text-slate-400 border-slate-700'"
                            class="px-2.5 py-1 rounded-lg text-xs font-bold border transition inline-flex items-center gap-1.5 cursor-pointer"
                            :title="autoRefresh ? 'Live Auto-Polling Active (every 4s)' : 'Click to enable live auto-polling'">
                        <i class="bi bi-broadcast text-[11px]" :class="autoRefresh ? 'animate-pulse text-emerald-400' : ''"></i>
                        <span class="hidden sm:inline" x-text="autoRefresh ? 'Live Sync On (4s)' : 'Live Sync Off'"></span>
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
            <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-6 bg-slate-50/60">

                <!-- Loading State Skeleton -->
                <template x-if="loading && !data">
                    <div class="space-y-4 py-8 animate-pulse">
                        <div class="h-28 bg-slate-200 rounded-3xl"></div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="h-20 bg-slate-200 rounded-2xl"></div>
                            <div class="h-20 bg-slate-200 rounded-2xl"></div>
                            <div class="h-20 bg-slate-200 rounded-2xl"></div>
                            <div class="h-20 bg-slate-200 rounded-2xl"></div>
                        </div>
                        <div class="h-44 bg-slate-200 rounded-3xl"></div>
                        <div class="h-32 bg-slate-200 rounded-2xl"></div>
                    </div>
                </template>

                <!-- Error Fallback State -->
                <template x-if="error && !data">
                    <div class="p-6 rounded-3xl bg-white border border-rose-200 shadow-xs text-center space-y-3">
                        <span class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 inline-flex items-center justify-center text-xl">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </span>
                        <h4 class="font-extrabold text-slate-900 text-sm">Failed to Load Live Visitor Journey</h4>
                        <p class="text-xs text-slate-500 max-w-md mx-auto" x-text="error"></p>
                        <div class="pt-2">
                            <button type="button" @click="refreshData(true)" class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition cursor-pointer">
                                <i class="bi bi-arrow-repeat mr-1"></i> Retry Live Sync
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Active Data Content -->
                <template x-if="data && data.success">
                    <div class="space-y-6">

                        <!-- ═════════════════════════════════════════════════════════════════ -->
                        <!-- A. CURRENT LIVE SCREEN & ACTIVE VIEWPORT DISPLAY (Screen View)   -->
                        <!-- ═════════════════════════════════════════════════════════════════ -->
                        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                            <!-- Mock Browser Viewport Header -->
                            <div class="px-4 py-3 bg-slate-900 border-b border-slate-800 flex flex-wrap items-center justify-between gap-3">
                                <!-- Traffic Lights & SSL Bar -->
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span>
                                        <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
                                        <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                                    </div>
                                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-800 border border-slate-700/80 text-xs text-slate-300 min-w-0 max-w-full">
                                        <i class="bi bi-lock-fill text-emerald-400 text-[10px] shrink-0"></i>
                                        <span class="text-slate-400 shrink-0">https://dunesdiscoverytourism.com</span>
                                        <span class="text-white font-mono font-bold truncate" x-text="data.current_screen ? data.current_screen.uri : data.visitor.current_page"></span>
                                        <button type="button" 
                                                @click="copyToClipboard(data.current_screen ? data.current_screen.full_url : data.visitor.current_page, 'URL copied!')" 
                                                class="text-slate-400 hover:text-white ml-1 shrink-0 cursor-pointer" 
                                                title="Copy Full Page URL">
                                            <i class="bi bi-copy text-[10px]"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Screen Controls & Live Dwell -->
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="px-2.5 py-1 rounded-lg bg-emerald-950/80 border border-emerald-500/40 font-mono text-emerald-400 text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                        <span x-text="'On Screen: ' + formatLiveDwell(liveDwellSeconds)"></span>
                                    </span>

                                    <!-- 1-Click Open Live Screen Button -->
                                    <a :href="data.current_screen ? data.current_screen.full_url : ('/' + (data.visitor.current_page || '').replace(/^\//, ''))" 
                                       target="_blank" 
                                       class="px-3.5 py-1 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition inline-flex items-center gap-1.5">
                                        <span>Open Live Screen</span>
                                        <i class="bi bi-box-arrow-up-right text-[10px]"></i>
                                    </a>
                                </div>
                            </div>

                            <!-- Real-Time User Action Pulse Banner -->
                            <div class="px-5 py-2.5 bg-linear-to-r from-emerald-50 via-teal-50 to-blue-50 border-b border-emerald-100 flex items-center justify-between gap-3 text-xs">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                                    <span class="font-black text-emerald-900 shrink-0 uppercase tracking-wider text-[10px]">Real-Time User Action:</span>
                                    <span class="font-bold text-slate-800 truncate" x-text="data.current_screen ? data.current_screen.live_action : 'Actively browsing website'"></span>
                                </div>
                                <button type="button" 
                                        @click="showIframePreview = !showIframePreview" 
                                        class="px-2.5 py-0.5 rounded-lg bg-white border border-emerald-200 text-emerald-800 hover:bg-emerald-100/60 font-bold text-[11px] shadow-2xs transition inline-flex items-center gap-1 shrink-0 cursor-pointer">
                                    <i class="bi" :class="showIframePreview ? 'bi-chevron-up' : 'bi-display'"></i>
                                    <span x-text="showIframePreview ? 'Collapse Viewport' : 'Preview Screen Viewport'"></span>
                                </button>
                            </div>

                            <!-- Screen Card Content (Tour, Booking, or General Screen) -->
                            <div class="p-4 sm:p-5">
                                <!-- Case 1: Active Tour Screen -->
                                <template x-if="data.current_screen && data.current_screen.tour">
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                                        <div class="flex items-center gap-4 min-w-0">
                                            <template x-if="data.current_screen.tour.thumb_image || data.current_screen.tour.hero_image">
                                                <img :src="data.current_screen.tour.thumb_image || data.current_screen.tour.hero_image" 
                                                     :alt="data.current_screen.tour.name" 
                                                     class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border border-slate-200 shadow-xs shrink-0">
                                            </template>
                                            <div class="min-w-0 space-y-1">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-primary/10 text-primary border border-primary/20">Active Tour Page</span>
                                                    <span class="text-xs text-amber-500 font-bold inline-flex items-center gap-1">
                                                        <i class="bi bi-star-fill text-[11px]"></i>
                                                        <span x-text="data.current_screen.tour.rating"></span>
                                                        <span class="text-slate-400 font-normal" x-text="'(' + data.current_screen.tour.review_count + ' reviews)'"></span>
                                                    </span>
                                                </div>
                                                <h4 class="font-black text-slate-900 text-sm sm:text-base tracking-tight truncate" x-text="data.current_screen.tour.name"></h4>
                                                <div class="flex items-center gap-3 text-xs text-slate-500 flex-wrap">
                                                    <span class="inline-flex items-center gap-1">
                                                        <i class="bi bi-clock text-slate-400"></i>
                                                        <span x-text="data.current_screen.tour.duration"></span>
                                                    </span>
                                                    <span>&bull;</span>
                                                    <span class="inline-flex items-center gap-1 font-bold text-slate-900">
                                                        <span>From AED</span>
                                                        <span class="text-primary font-black" x-text="data.current_screen.tour.starting_price"></span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto justify-end">
                                            <a :href="data.current_screen.tour.edit_url" target="_blank" class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 font-bold text-xs shadow-2xs transition inline-flex items-center gap-1">
                                                <i class="bi bi-pencil-square text-slate-400"></i> Edit Tour in CMS
                                            </a>
                                            <a :href="data.current_screen.tour.live_url" target="_blank" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition inline-flex items-center gap-1">
                                                <i class="bi bi-box-arrow-up-right text-amber-400"></i> View Tour Live
                                            </a>
                                        </div>
                                    </div>
                                </template>

                                <!-- Case 2: Checkout / Booking Funnel Screen -->
                                <template x-if="data.current_screen && data.current_screen.booking">
                                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                        <div class="flex items-center gap-3.5 min-w-0">
                                            <span class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-xl shadow-xs shrink-0">
                                                <i class="bi bi-cart-check-fill"></i>
                                            </span>
                                            <div class="min-w-0 space-y-0.5">
                                                <div class="flex items-center gap-2">
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-200 text-amber-900">In Checkout Funnel</span>
                                                    <span class="font-mono font-bold text-xs text-slate-600" x-text="'Ref: #' + data.current_screen.booking.reference"></span>
                                                </div>
                                                <h4 class="font-black text-slate-900 text-sm truncate" x-text="data.current_screen.booking.tour_name"></h4>
                                                <div class="flex items-center gap-3 text-xs text-slate-600 flex-wrap">
                                                    <span x-text="'Date: ' + data.current_screen.booking.tour_date"></span>
                                                    <span>&bull;</span>
                                                    <span x-text="data.current_screen.booking.adults + ' Adults' + (data.current_screen.booking.children ? ', ' + data.current_screen.booking.children + ' Children' : '')"></span>
                                                    <span>&bull;</span>
                                                    <span class="font-black text-emerald-700">AED <span x-text="Number(data.current_screen.booking.total).toFixed(2)"></span></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="shrink-0 w-full sm:w-auto text-right">
                                            <a :href="'/admin/bookings/drafts?search=' + encodeURIComponent(data.current_screen.booking.reference)" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition inline-flex items-center gap-1.5">
                                                <i class="bi bi-box-arrow-up-right"></i> Inspect Draft Cart
                                            </a>
                                        </div>
                                    </div>
                                </template>

                                <!-- Case 3: Other Catalog, Homepage, or Content Screen -->
                                <template x-if="data.current_screen && !data.current_screen.tour && !data.current_screen.booking">
                                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <span class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-primary flex items-center justify-center text-lg shadow-2xs shrink-0">
                                                <i class="bi bi-compass"></i>
                                            </span>
                                            <div>
                                                <h4 class="font-black text-slate-900 text-sm" x-text="data.current_screen.title"></h4>
                                                <p class="text-xs text-slate-500 font-mono mt-0.5" x-text="data.current_screen.uri"></p>
                                            </div>
                                        </div>
                                        <a :href="data.current_screen.full_url" target="_blank" class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 font-bold text-xs shadow-2xs transition inline-flex items-center gap-1 shrink-0">
                                            <i class="bi bi-box-arrow-up-right text-slate-400"></i> Open Page
                                        </a>
                                    </div>
                                </template>

                                <!-- Expandable Sandboxed Live Iframe Preview -->
                                <div x-show="showIframePreview" x-transition class="mt-4 pt-4 border-t border-slate-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-bold text-slate-600 flex items-center gap-1.5">
                                            <i class="bi bi-display text-primary"></i> Interactive Live Viewport Preview (Direct Screen Render)
                                        </span>
                                        <span class="text-[10px] text-slate-400">Sandboxed Preview</span>
                                    </div>
                                    <iframe :src="data.current_screen ? data.current_screen.full_url : '/'" 
                                            class="w-full h-80 sm:h-96 rounded-2xl border border-slate-300 bg-white shadow-inner" 
                                            sandbox="allow-scripts allow-same-origin"></iframe>
                                </div>
                            </div>
                        </div>

                        <!-- ═════════════════════════════════════════════════════════════════ -->
                        <!-- B. REAL-TIME USER ACTIVITY STREAM (Clickstream Timeline Feed)    -->
                        <!-- ═════════════════════════════════════════════════════════════════ -->
                        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-4 sm:p-5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3.5 border-b border-slate-100 gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                                        <i class="bi bi-clock-history"></i>
                                    </span>
                                    <div>
                                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 flex items-center gap-1.5">
                                            <span>Real-Time User Activity Stream</span>
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        </h4>
                                        <p class="text-[11px] text-slate-400">Chronological sequence of every screen, cart interaction, and friction event.</p>
                                    </div>
                                </div>

                                <!-- Activity Filter Pills -->
                                <div class="flex items-center gap-1 flex-wrap">
                                    <button type="button" 
                                            @click="activeTab = 'all'"
                                            :class="activeTab === 'all' ? 'bg-slate-900 text-white font-black' : 'bg-slate-100 text-slate-600 hover:text-slate-900 font-bold'"
                                            class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer">
                                        All (<span x-text="data.journey_steps.length"></span>)
                                    </button>
                                    <button type="button" 
                                            @click="activeTab = 'screens'"
                                            :class="activeTab === 'screens' ? 'bg-primary text-white font-black' : 'bg-slate-100 text-slate-600 hover:text-slate-900 font-bold'"
                                            class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer">
                                        Screens
                                    </button>
                                    <button type="button" 
                                            @click="activeTab = 'leads'"
                                            :class="activeTab === 'leads' ? 'bg-amber-600 text-white font-black' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 font-bold'"
                                            class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer">
                                        Carts &amp; Leads
                                    </button>
                                    <button type="button" 
                                            @click="activeTab = 'friction'"
                                            :class="activeTab === 'friction' ? 'bg-rose-600 text-white font-black' : 'bg-rose-50 text-rose-800 hover:bg-rose-100 font-bold'"
                                            class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer">
                                        Friction (404)
                                    </button>
                                </div>
                            </div>

                            <!-- Clickstream Activity List -->
                            <div class="mt-3 divide-y divide-slate-100 rounded-2xl border border-slate-100 overflow-hidden">
                                <template x-for="(step, sIndex) in filteredSteps()" :key="sIndex">
                                    <div class="p-3.5 hover:bg-slate-50 transition flex items-center justify-between gap-3 text-xs"
                                         :class="step.is_current ? 'bg-primary/5 border-l-4 border-l-primary' : ''">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <!-- Step Number Badge -->
                                            <span class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-[11px] shrink-0"
                                                  :class="{
                                                      'bg-primary text-white': step.is_current,
                                                      'bg-rose-100 text-rose-800': step.type === 'error',
                                                      'bg-amber-100 text-amber-800': step.type === 'draft',
                                                      'bg-emerald-100 text-emerald-800': step.type === 'whatsapp',
                                                      'bg-slate-100 text-slate-700': step.type === 'pageview'
                                                  }"
                                                  x-text="'#' + step.step_number"></span>

                                            <!-- Step Description -->
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="font-extrabold text-slate-900 truncate" x-text="step.title"></span>
                                                    <template x-if="step.is_current">
                                                        <span class="px-2 py-0.5 rounded-full bg-emerald-500 text-white text-[9px] font-black uppercase inline-flex items-center gap-1 shadow-2xs">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
                                                            Current Screen
                                                        </span>
                                                    </template>
                                                    <template x-if="step.type === 'error'">
                                                        <span class="px-1.5 py-0.5 rounded bg-rose-600 text-white text-[9px] font-black uppercase">404 Error</span>
                                                    </template>
                                                    <template x-if="step.type === 'draft'">
                                                        <span class="px-1.5 py-0.5 rounded bg-amber-500 text-white text-[9px] font-black uppercase">Draft Cart</span>
                                                    </template>
                                                    <template x-if="step.type === 'whatsapp'">
                                                        <span class="px-1.5 py-0.5 rounded bg-emerald-600 text-white text-[9px] font-black uppercase">WhatsApp Concierge</span>
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
                                            <!-- Dwell Time on Step -->
                                            <span class="px-2 py-1 rounded-lg bg-slate-100 font-mono text-[11px] font-bold text-slate-600" 
                                                  title="Time spent on this page" 
                                                  x-text="step.is_current ? formatLiveDwell(liveDwellSeconds) : step.dwell_formatted"></span>
                                            
                                            <!-- View Live Page in New Tab -->
                                            <a :href="step.url" target="_blank" class="p-1 rounded-lg text-slate-400 hover:text-primary transition cursor-pointer" title="Open this page on live website">
                                                <i class="bi bi-box-arrow-up-right text-xs"></i>
                                            </a>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- ═════════════════════════════════════════════════════════════════ -->
                        <!-- C. VISITOR METADATA & ACQUISITION (Identity, Location, Device)   -->
                        <!-- ═════════════════════════════════════════════════════════════════ -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <!-- IP & Geolocation -->
                            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-2xs">
                                <div class="flex items-center justify-between text-slate-400 mb-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider">Visitor Identity</span>
                                    <i class="bi bi-geo-alt-fill text-primary text-xs"></i>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono font-black text-slate-900 text-xs truncate" x-text="data.visitor.client_ip"></span>
                                    <button type="button" @click="copyToClipboard(data.visitor.client_ip, 'IP copied!')" class="text-slate-400 hover:text-primary cursor-pointer" title="Copy IP">
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
                                    <span class="text-[10px] font-bold uppercase tracking-wider">Total Time on Site</span>
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

                        <!-- ═════════════════════════════════════════════════════════════════ -->
                        <!-- D. IDENTIFIED CUSTOMER LEAD CARD (If Draft or Inquiry exists)     -->
                        <!-- ═════════════════════════════════════════════════════════════════ -->
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

                        <!-- ═════════════════════════════════════════════════════════════════ -->
                        <!-- E. FRICTION & OBSTACLE DIAGNOSTICS (Drop-off & Error Resolution) -->
                        <!-- ═════════════════════════════════════════════════════════════════ -->
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

                    </div>
                </template>
            </div>

            <!-- 3. Modal Bottom Administrative Action Bar -->
            <div class="px-5 py-3.5 bg-white border-t border-slate-200 flex flex-wrap items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <template x-if="data && data.visitor">
                        <div class="flex items-center gap-2 flex-wrap">
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
        error: null,
        autoRefresh: true,
        data: null,
        pollTimer: null,
        dwellTicker: null,
        liveDwellSeconds: 0,
        showIframePreview: false,
        activeTab: 'all',

        openModal(sessionId, clientIp) {
            this.sessionId = sessionId;
            this.clientIp = clientIp;
            this.isOpen = true;
            this.data = null;
            this.error = null;
            this.showIframePreview = false;
            this.activeTab = 'all';
            this.liveDwellSeconds = 0;
            this.refreshData(true);
            this.startPoller();
            this.startTicker();
        },

        closeModal() {
            this.isOpen = false;
            this.stopPoller();
            this.stopTicker();
        },

        startPoller() {
            this.stopPoller();
            this.pollTimer = setInterval(() => {
                if (this.isOpen && this.autoRefresh && !this.loading) {
                    this.refreshData(false);
                }
            }, 4000);
        },

        stopPoller() {
            if (this.pollTimer) {
                clearInterval(this.pollTimer);
                this.pollTimer = null;
            }
        },

        startTicker() {
            this.stopTicker();
            this.dwellTicker = setInterval(() => {
                if (this.isOpen && this.data && this.data.visitor && this.data.visitor.is_active_now) {
                    this.liveDwellSeconds++;
                }
            }, 1000);
        },

        stopTicker() {
            if (this.dwellTicker) {
                clearInterval(this.dwellTicker);
                this.dwellTicker = null;
            }
        },

        async refreshData(showLoader = true) {
            if (showLoader && !this.data) this.loading = true;
            try {
                const params = new URLSearchParams();
                if (this.sessionId) params.append('session_id', this.sessionId);
                if (this.clientIp) params.append('client_ip', this.clientIp);

                const url = `/admin/api/visitor-journey?${params.toString()}`;
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}: Failed to reach journey inspector endpoint.`);
                }

                const json = await res.json();
                if (json && json.success) {
                    this.data = json;
                    this.error = null;
                    if (json.current_screen && typeof json.current_screen.dwell_seconds === 'number') {
                        this.liveDwellSeconds = json.current_screen.dwell_seconds;
                    }
                } else {
                    this.error = json?.message || 'Unable to retrieve visitor journey.';
                }
            } catch (err) {
                console.error("Live journey inspector error:", err);
                if (!this.data) {
                    this.error = err.message || 'Error communicating with live telemetry service.';
                }
            } finally {
                this.loading = false;
            }
        },

        formatLiveDwell(totalSec) {
            const s = Math.max(0, parseInt(totalSec || 0));
            if (s < 60) return `${s}s`;
            const mins = Math.floor(s / 60);
            const rem = s % 60;
            return `${mins}m ${rem}s`;
        },

        filteredSteps() {
            if (!this.data || !this.data.journey_steps) return [];
            if (this.activeTab === 'all') return this.data.journey_steps;
            if (this.activeTab === 'screens') return this.data.journey_steps.filter(s => s.type === 'pageview');
            if (this.activeTab === 'leads') return this.data.journey_steps.filter(s => s.type === 'draft' || s.type === 'whatsapp' || s.type === 'booking');
            if (this.activeTab === 'friction') return this.data.journey_steps.filter(s => s.type === 'error' || s.type === 'warning');
            return this.data.journey_steps;
        },

        copyToClipboard(text, msg = 'Copied to clipboard!') {
            if (!text) return;
            navigator.clipboard.writeText(text);
            if (window.Swal) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: msg,
                    showConfirmButton: false,
                    timer: 2000
                });
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
