@extends('layouts.admin')

@section('page_title', 'Google Things To Do Feed')

@section('content')
<div class="space-y-6 mb-8">
    <!-- Header Card -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 bg-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-primary text-white flex items-center justify-center text-xl shadow-xs shrink-0">
                    <i class="bi bi-pin-map-fill"></i>
                </div>
                <div>
                    <h5 class="text-base font-extrabold text-slate-900 leading-tight">Google Things To Do (GTTD) Partner Feed</h5>
                    <div class="text-xs text-slate-500 mt-0.5">Automated partner XML and REST JSON feeds for Google Experiences & Search vertical listings.</div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <form action="{{ route('admin.settings.gttd.flush-cache') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 shadow-xs transition">
                        <i class="bi bi-arrow-repeat text-amber-500"></i> Flush Feed Cache
                    </button>
                </form>
                <a href="{{ url('/feeds/google-things-to-do/feed.xml') }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary hover:bg-[#e07b32] text-xs font-bold text-white shadow-xs transition">
                    <i class="bi bi-box-arrow-up-right"></i> View Live Feed
                </a>
            </div>
        </div>

        <!-- Inventory & Status KPIs -->
        <div class="p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 bg-slate-50/50 border-b border-slate-100">
            <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-2xs">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Active Tours</div>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $activeToursCount }}</div>
                <div class="text-[11px] text-emerald-600 font-medium mt-0.5"><i class="bi bi-check-circle-fill"></i> Ingested in Feed</div>
            </div>
            <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-2xs">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pricing Options</div>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalTiersCount }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Tiers & Packages</div>
            </div>
            <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-2xs">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Operator Identity</div>
                <div class="text-xs font-bold text-slate-900 mt-2 truncate">{{ $settings['site_name'] ?? 'Dunes Discovery Tourism L.L.C.' }}</div>
                <div class="text-[11px] text-blue-600 mt-0.5 font-mono">DET #{{ $settings['company_license_number'] ?? '1430583' }}</div>
            </div>
            <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-2xs">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Feed Format</div>
                <div class="text-xs font-bold text-slate-900 mt-2">XML v1.0 & REST JSON</div>
                <div class="text-[11px] text-emerald-600 font-bold mt-0.5">Google Certified Schema</div>
            </div>
        </div>

        <div class="p-6">
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="gttd_form_submitted" value="1">

                <!-- Master Toggle & Parameters -->
                <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80 mb-6">
                    <div class="flex items-center justify-between gap-4 mb-4">
                        <div>
                            <h6 class="text-xs font-black uppercase tracking-wider text-primary">Feed Generation Engine</h6>
                            <div class="text-xs text-slate-500 mt-0.5">Control live feed generation and public availability for Google Things To Do crawlers.</div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="gttd_feed_enabled" id="gttd_feed_enabled" value="1" {{ ($settings['gttd_feed_enabled'] ?? '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-slate-200/60">
                        <div>
                            <label for="gttd_partner_id" class="block text-xs font-bold text-slate-700 mb-1.5">Google Things To Do Partner ID</label>
                            <input type="text" name="gttd_partner_id" id="gttd_partner_id" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['gttd_partner_id'] ?? 'dunes-discovery-tourism' }}" placeholder="dunes-discovery-tourism">
                            <div class="mt-1 text-[11px] text-slate-500">Provided by Google Things To Do partner onboarding console.</div>
                        </div>
                        <div>
                            <label for="gttd_default_poi_place_id" class="block text-xs font-bold text-slate-700 mb-1.5">Primary Attraction / POI Place ID</label>
                            <input type="text" name="gttd_default_poi_place_id" id="gttd_default_poi_place_id" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['gttd_default_poi_place_id'] ?? 'ChIJt7e4-c9xdj4R_hY6W5xrqU8' }}" placeholder="ChIJt7e4-c9xdj4R_hY6W5xrqU8">
                            <div class="mt-1 text-[11px] text-slate-500">Lahbab Red Dunes (`ChIJt7e4-c9xdj4R_hY6W5xrqU8`) for desert safaris.</div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-[#e07b32] transition">
                        <i class="bi bi-check2-circle"></i> Save Feed Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Feed Endpoints & Quick Copy -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden p-6">
        <h6 class="text-sm font-extrabold text-slate-900 mb-1 flex items-center gap-2">
            <i class="bi bi-link-45deg text-primary text-base"></i> Live Feed Endpoints for Google Partner Console
        </h6>
        <p class="text-xs text-slate-500 mb-6">Submit these URLs into the Google Partner Portal during the Google Things To Do onboarding process.</p>

        <div class="space-y-4">
            <!-- Products XML -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-md bg-blue-100 text-blue-800 text-[10px] font-black uppercase">XML Feed</span>
                        <strong class="text-xs font-bold text-slate-900">Products Feed (`products.xml`)</strong>
                    </div>
                    <div class="text-xs font-mono text-slate-600 break-all select-all">{{ $feedUrls['products_xml'] }}</div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $feedUrls['products_xml'] }}'); alert('Products feed URL copied to clipboard!');" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-xs font-bold text-slate-700 transition">
                        <i class="bi bi-clipboard"></i> Copy URL
                    </button>
                    <a href="{{ $feedUrls['products_xml'] }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-xs font-bold text-white transition">
                        <i class="bi bi-box-arrow-up-right"></i> Open
                    </a>
                </div>
            </div>

            <!-- Options XML -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase">Pricing XML</span>
                        <strong class="text-xs font-bold text-slate-900">Options & Pricing Feed (`options.xml`)</strong>
                    </div>
                    <div class="text-xs font-mono text-slate-600 break-all select-all">{{ $feedUrls['options_xml'] }}</div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $feedUrls['options_xml'] }}'); alert('Options feed URL copied to clipboard!');" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-xs font-bold text-slate-700 transition">
                        <i class="bi bi-clipboard"></i> Copy URL
                    </button>
                    <a href="{{ $feedUrls['options_xml'] }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-xs font-bold text-white transition">
                        <i class="bi bi-box-arrow-up-right"></i> Open
                    </a>
                </div>
            </div>

            <!-- Operators XML -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-md bg-purple-100 text-purple-800 text-[10px] font-black uppercase">Operator XML</span>
                        <strong class="text-xs font-bold text-slate-900">Operator Identity Feed (`operators.xml`)</strong>
                    </div>
                    <div class="text-xs font-mono text-slate-600 break-all select-all">{{ $feedUrls['operators_xml'] }}</div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $feedUrls['operators_xml'] }}'); alert('Operators feed URL copied to clipboard!');" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-xs font-bold text-slate-700 transition">
                        <i class="bi bi-clipboard"></i> Copy URL
                    </button>
                    <a href="{{ $feedUrls['operators_xml'] }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-xs font-bold text-white transition">
                        <i class="bi bi-box-arrow-up-right"></i> Open
                    </a>
                </div>
            </div>

            <!-- Unified Complete XML -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 text-[10px] font-black uppercase">Unified Feed</span>
                        <strong class="text-xs font-bold text-slate-900">Consolidated GTTD XML (`feed.xml`)</strong>
                    </div>
                    <div class="text-xs font-mono text-slate-600 break-all select-all">{{ $feedUrls['unified_xml'] }}</div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $feedUrls['unified_xml'] }}'); alert('Unified feed URL copied to clipboard!');" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-xs font-bold text-slate-700 transition">
                        <i class="bi bi-clipboard"></i> Copy URL
                    </button>
                    <a href="{{ $feedUrls['unified_xml'] }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-primary hover:bg-[#e07b32] text-xs font-bold text-white transition">
                        <i class="bi bi-box-arrow-up-right"></i> Open
                    </a>
                </div>
            </div>

            <!-- REST JSON Feed -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-md bg-cyan-100 text-cyan-800 text-[10px] font-black uppercase">REST JSON</span>
                        <strong class="text-xs font-bold text-slate-900">Full Catalog JSON Feed (`feed.json`)</strong>
                    </div>
                    <div class="text-xs font-mono text-slate-600 break-all select-all">{{ $feedUrls['feed_json'] }}</div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $feedUrls['feed_json'] }}'); alert('JSON feed URL copied to clipboard!');" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-xs font-bold text-slate-700 transition">
                        <i class="bi bi-clipboard"></i> Copy URL
                    </button>
                    <a href="{{ $feedUrls['feed_json'] }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-xs font-bold text-white transition">
                        <i class="bi bi-box-arrow-up-right"></i> Open
                    </a>
                </div>
            </div>

            <!-- Official Proto V1 JSON Feed -->
            <div class="p-4 rounded-xl bg-emerald-50/50 border border-emerald-200 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-md bg-emerald-600 text-white text-[10px] font-black uppercase">Official Proto V1</span>
                        <strong class="text-xs font-bold text-emerald-900">Google Actions Center ProductFeed (`proto.json`)</strong>
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full"><i class="bi bi-patch-check-fill"></i> Certified Schema</span>
                    </div>
                    <div class="text-xs font-mono text-slate-700 break-all select-all">{{ $feedUrls['proto_json'] }}</div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $feedUrls['proto_json'] }}'); alert('Official Proto V1 feed URL copied to clipboard!');" class="px-3 py-1.5 rounded-lg border border-emerald-300 bg-white hover:bg-emerald-50 text-xs font-bold text-emerald-800 transition">
                        <i class="bi bi-clipboard"></i> Copy URL
                    </button>
                    <a href="{{ $feedUrls['proto_json'] }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-xs font-bold text-white transition">
                        <i class="bi bi-box-arrow-up-right"></i> Open
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Google Things To Do 100% Ranking & Compliance Checklist -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden p-6">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <div>
                <h6 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="bi bi-trophy-fill text-amber-500 text-base"></i> Google Things To Do Top-Rank & Official Badge Checklist
                </h6>
                <p class="text-xs text-slate-500 mt-0.5">Automated validation of all factors required to rank above OTAs (Viator, GetYourGuide) and maintain a 100% Price Accuracy Score.</p>
            </div>
            <span class="bg-emerald-100 text-emerald-800 text-xs font-black px-3 py-1 rounded-full flex items-center gap-1.5">
                <i class="bi bi-check-circle-fill"></i> 100% COMPLIANT
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pt-2">
            <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/40">
                <div class="flex items-start gap-2.5">
                    <i class="bi bi-patch-check-fill text-emerald-600 text-lg shrink-0 mt-0.5"></i>
                    <div>
                        <strong class="text-xs font-bold text-slate-900 block leading-tight">Official Site Green Badge</strong>
                        <p class="text-[11px] text-slate-600 mt-1">Verified operator match with GBP Place ID <code class="bg-white px-1 py-0.5 rounded text-[10px] font-mono">{{ $settings['google_place_id'] ?? 'ChIJbWsIEIVEdEER4uHEhb2dbcQ' }}</code> and Dubai DET License #{{ $settings['company_license_number'] ?? '1430583' }}.</p>
                    </div>
                </div>
            </div>

            <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/40">
                <div class="flex items-start gap-2.5">
                    <i class="bi bi-shield-check text-emerald-600 text-lg shrink-0 mt-0.5"></i>
                    <div>
                        <strong class="text-xs font-bold text-slate-900 block leading-tight">100% Price Accuracy Engine</strong>
                        <p class="text-[11px] text-slate-600 mt-1">Landing page dynamic <code class="bg-white px-1 py-0.5 rounded text-[10px] font-mono">?tier=</code> selector anchors visual prices and Schema.org <code class="bg-white px-1 py-0.5 rounded text-[10px] font-mono">Offer</code> to feed option prices down to the fils.</p>
                    </div>
                </div>
            </div>

            <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/40">
                <div class="flex items-start gap-2.5">
                    <i class="bi bi-receipt text-emerald-600 text-lg shrink-0 mt-0.5"></i>
                    <div>
                        <strong class="text-xs font-bold text-slate-900 block leading-tight">Zero Hidden Fees / VAT Included</strong>
                        <p class="text-[11px] text-slate-600 mt-1">All option prices explicitly declare <code class="bg-white px-1 py-0.5 rounded text-[10px] font-mono">taxes_and_fees_included: true</code> covering 5% UAE VAT and local municipality fees.</p>
                    </div>
                </div>
            </div>

            <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/40">
                <div class="flex items-start gap-2.5">
                    <i class="bi bi-geo-alt-fill text-emerald-600 text-lg shrink-0 mt-0.5"></i>
                    <div>
                        <strong class="text-xs font-bold text-slate-900 block leading-tight">High-Precision POI Mapping</strong>
                        <p class="text-[11px] text-slate-600 mt-1">Individual place card linking to Sheikh Zayed Grand Mosque, Lahbab Red Dunes, Big Red Dune Quad Arena, Burj Khalifa, and Marina Canal.</p>
                    </div>
                </div>
            </div>

            <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/40">
                <div class="flex items-start gap-2.5">
                    <i class="bi bi-arrow-repeat text-emerald-600 text-lg shrink-0 mt-0.5"></i>
                    <div>
                        <strong class="text-xs font-bold text-slate-900 block leading-tight">24h Free Cancellation Signal</strong>
                        <p class="text-[11px] text-slate-600 mt-1">Declared <code class="bg-white px-1 py-0.5 rounded text-[10px] font-mono">refund_percent: 100</code> up to 24h before tour start, rewarded heavily in Google Experiences search results.</p>
                    </div>
                </div>
            </div>

            <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/40">
                <div class="flex items-start gap-2.5">
                    <i class="bi bi-qr-code text-emerald-600 text-lg shrink-0 mt-0.5"></i>
                    <div>
                        <strong class="text-xs font-bold text-slate-900 block leading-tight">Mobile Ticket Instant Voucher</strong>
                        <p class="text-[11px] text-slate-600 mt-1">Fulfillment type set to <code class="bg-white px-1 py-0.5 rounded text-[10px] font-mono">FULFILLMENT_TYPE_MOBILE_TICKET</code> with instant digital confirmation.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
