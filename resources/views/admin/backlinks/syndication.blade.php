@extends('layouts.admin')

@section('page_title', 'Real-Time Content Syndication & Embed Hub')

@section('content')
<div x-data="syndicationHub()" class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.backlinks.index') }}" class="text-xs font-bold text-slate-400 hover:text-primary transition flex items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Back to Backlink Tracker
                </a>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Content Syndication &amp; Embed Widget Hub</h1>
            <p class="text-xs text-slate-500 mt-0.5">Equip bloggers, affiliate travel portals, and directory partners with automated embed widgets, RSS feeds, and REST APIs that sync real-time CMS tour data.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ $feedUrl }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold shadow-2xs transition-all">
                <i class="bi bi-rss-fill text-amber-500 text-sm"></i>
                <span>Open RSS 2.0 Feed</span>
            </a>
            <a href="{{ $apiUrl }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition-all">
                <i class="bi bi-code-slash"></i>
                <span>Test JSON API</span>
            </a>
        </div>
    </div>

    <!-- Alert / Copy Feedback Toast -->
    <template x-if="copied">
        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-bold flex items-center justify-between">
            <span class="flex items-center gap-2">
                <i class="bi bi-check-circle-fill text-emerald-600"></i> Code snippet copied to clipboard!
            </span>
            <button @click="copied = false" class="text-slate-400 hover:text-slate-600"><i class="bi bi-x-lg"></i></button>
        </div>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Column: Embed Generator -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Widget Builder Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">
                            <i class="bi bi-window-fullscreen"></i>
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Embeddable Safari Card Generator</h2>
                            <p class="text-[11px] text-slate-400">Generates lightweight, self-updating HTML embeds for external travel blogs & reviews.</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <!-- Tour Selection -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Choose Tour Package</label>
                        <select x-model="selectedTour" class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 bg-white font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/20">
                            @foreach($tours as $t)
                                <option value="{{ $t->slug }}">{{ $t->name }} (AED {{ number_format($t->starting_price) }} – {{ $t->duration }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Partner Referral Code -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Partner Tracking Tag (Optional)</label>
                        <input type="text" x-model="partnerSlug" placeholder="e.g. lonelyplanet_uae or wanderlust_blog" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20">
                        <span class="text-[10px] text-slate-400 mt-1 block">Traffic from this widget will be attributed to this tag with direct conversion tracking.</span>
                    </div>

                    <!-- Generated HTML Snippet -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Embed Code (Paste anywhere on external site)</label>
                            <button type="button" @click="copyEmbedCode()" class="text-xs font-bold text-primary hover:text-primary-dark flex items-center gap-1">
                                <i class="bi bi-clipboard"></i> Copy Code
                            </button>
                        </div>
                        <div class="relative">
                            <pre class="bg-slate-900 text-slate-100 p-3.5 rounded-xl text-[11px] font-mono overflow-x-auto select-all leading-relaxed whitespace-pre-wrap" x-text="embedSnippet"></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- REST API Documentation Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-slate-100">
                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                        <i class="bi bi-hdd-network"></i>
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Public Syndication REST API (v1)</h2>
                        <p class="text-[11px] text-slate-400">Headless JSON feed for programmatic directory listings, travel apps, and OTA partners.</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 flex items-center justify-between">
                        <div class="flex items-center gap-2 font-mono text-xs">
                            <span class="px-2 py-0.5 rounded bg-emerald-600 text-white font-bold text-[10px]">GET</span>
                            <span class="text-slate-800">{{ $apiUrl }}</span>
                        </div>
                        <a href="{{ $apiUrl }}" target="_blank" class="text-xs font-bold text-primary hover:underline">Test Run</a>
                    </div>

                    <div class="text-xs text-slate-600 space-y-2">
                        <p class="font-bold text-slate-800">Supported Query Parameters:</p>
                        <ul class="list-disc list-inside space-y-1 text-slate-600 text-[11px]">
                            <li><code class="text-primary font-bold">?tour=evening-desert-safari</code> — Returns a specific tour only.</li>
                            <li><code class="text-primary font-bold">?limit=10</code> — Limit number of returned tours.</li>
                        </ul>
                    </div>

                    <div class="p-3 bg-amber-50/70 border border-amber-200 rounded-xl text-[11px] text-amber-900 flex items-start gap-2">
                        <i class="bi bi-info-circle-fill text-amber-600 mt-0.5 shrink-0"></i>
                        <span><strong>Zero-Maintenance Guarantee:</strong> Whenever you modify tour pricing, duration, or official pickup/drop-off timings in the CMS, this endpoint updates automatically with zero delay.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Widget Preview & Directories Strategy -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Live Preview -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                <div class="flex items-center justify-between mb-3 pb-3 border-b border-slate-100">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Live Widget Preview</span>
                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                        Real-Time Renderer
                    </span>
                </div>

                <!-- Preview Container -->
                <div class="bg-slate-100/70 p-4 rounded-xl flex items-center justify-center">
                    <div class="dunes-safari-widget" :data-tour="selectedTour" :data-partner="partnerSlug" style="width: 100%; max-width: 360px;"></div>
                </div>
            </div>

            <!-- White-Hat Free Directory Citations Checklist -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                <div class="flex items-center gap-2 mb-3 pb-2 border-b border-slate-100">
                    <i class="bi bi-award-fill text-amber-500"></i>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Top Free High-DA Directory Checklist</h3>
                </div>
                <div class="space-y-2 text-xs text-slate-600">
                    <p class="text-[11px] text-slate-500 mb-2">High-Domain Authority directories and travel ecosystems for clean, white-hat citations:</p>
                    
                    <div class="p-2.5 rounded-xl border border-slate-100 hover:bg-slate-50 transition flex items-center justify-between">
                        <div>
                            <div class="font-bold text-slate-800">TripAdvisor Operator Listing</div>
                            <div class="text-[10px] text-slate-400">DA 93 • Direct verified booking link</div>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">Tier 1</span>
                    </div>

                    <div class="p-2.5 rounded-xl border border-slate-100 hover:bg-slate-50 transition flex items-center justify-between">
                        <div>
                            <div class="font-bold text-slate-800">Google Business Profile (DET Verified)</div>
                            <div class="text-[10px] text-slate-400">DA 100 • Local 3-Pack & Maps Search</div>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">Tier 1</span>
                    </div>

                    <div class="p-2.5 rounded-xl border border-slate-100 hover:bg-slate-50 transition flex items-center justify-between">
                        <div>
                            <div class="font-bold text-slate-800">OpenStreetMap & Wikivoyage Dubai</div>
                            <div class="text-[10px] text-slate-400">DA 90 • Open-source geographic citations</div>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[10px]">High Trust</span>
                    </div>

                    <div class="p-2.5 rounded-xl border border-slate-100 hover:bg-slate-50 transition flex items-center justify-between">
                        <div>
                            <div class="font-bold text-slate-800">UAE Yellow Pages & Kompass Middle East</div>
                            <div class="text-[10px] text-slate-400">DA 65 • Commercial trade registry citations</div>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold text-[10px]">Local NAP</span>
                    </div>

                    <div class="p-2.5 rounded-xl border border-slate-100 hover:bg-slate-50 transition flex items-center justify-between">
                        <div>
                            <div class="font-bold text-slate-800">Bing Places & Apple Maps Connect</div>
                            <div class="text-[10px] text-slate-400">DA 96 • Native Voice & iOS Siri search discovery</div>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[10px]">Mobile SEO</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load Safari Widget JS for Live Interactive Preview -->
<script src="{{ $widgetScriptUrl }}"></script>

<script>
function syndicationHub() {
    return {
        selectedTour: '{{ $tours->first()?->slug ?? 'evening-desert-safari-dubai' }}',
        partnerSlug: 'partner_embed',
        copied: false,
        get embedSnippet() {
            const partnerAttr = this.partnerSlug ? ` data-partner="${this.partnerSlug}"` : '';
            return `<!-- Dunes Discovery Tourism Safari Card Widget -->\n<div class="dunes-safari-widget" data-tour="${this.selectedTour}"${partnerAttr}></div>\n<script src="{{ $widgetScriptUrl }}" async><\/script>`;
        },
        copyEmbedCode() {
            navigator.clipboard.writeText(this.embedSnippet).then(() => {
                this.copied = true;
                setTimeout(() => this.copied = false, 3000);
            });
        }
    };
}
</script>
@endsection
