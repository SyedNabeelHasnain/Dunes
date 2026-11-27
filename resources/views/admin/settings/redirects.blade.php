@extends('layouts.admin')

@section('page_title', 'Technical SEO: Dynamic Redirect Manager & 404 Monitor')

@section('content')
<div class="space-y-6" x-data="{
    testUrl: '',
    testResult: null,
    testing: false,
    createCode: 301,
    createSource: '',
    createTarget: '',
    editModal: false,
    editRule: { id: null, source_path: '', target_path: '', status_code: 301, notes: '', is_active: true },
    openEdit(rule) {
        this.editRule = {
            id: rule.id,
            source_path: rule.source_path,
            target_path: rule.target_path || '',
            status_code: Number(rule.status_code),
            notes: rule.notes || '',
            is_active: Boolean(rule.is_active)
        };
        this.editModal = true;
    },
    prefill404(uri, code = 301) {
        this.createSource = uri;
        this.createCode = code;
        if (code === 410) {
            this.createTarget = '';
        }
        window.scrollTo({ top: 180, behavior: 'smooth' });
    },
    async runTest() {
        if (!this.testUrl) return;
        this.testing = true;
        this.testResult = null;
        try {
            const res = await fetch(`{{ route('admin.api.redirects.test') }}?path=${encodeURIComponent(this.testUrl)}`);
            this.testResult = await res.json();
        } catch(e) {
            this.testResult = { success: false, message: 'Simulation error occurred.' };
        } finally {
            this.testing = false;
        }
    }
}">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-xs font-bold text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-xs font-bold text-rose-800">{{ session('error') }}</div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-linear-to-br from-primary to-orange-600 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                <i class="bi bi-signpost-split"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Dynamic Redirect Manager & 404 Inbox</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                        Technical SEO
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Manage 301, 302, 307, 308 redirects & 410 Gone responses, verify destination health, detect chains/loops, and convert top 404 errors.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.settings.robots.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs">
                <i class="bi bi-robot"></i> robots.txt
            </a>
            <a href="{{ route('admin.settings.seo') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs">
                <i class="bi bi-search"></i> SEO Settings
            </a>
            <a href="{{ route('admin.audit.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition shadow-sm">
                <i class="bi bi-shield-check"></i> Audit Engine
            </a>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Rules</span>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">{{ $stats['active'] }} Active</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Redirect Hits</span>
            <div class="text-2xl font-black text-primary mt-1">{{ number_format($stats['total_hits']) }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Preserved Requests</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Permanent (301/308)</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['permanent_count'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Canonical Transfers</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Temporary (302/307)</span>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $stats['temporary_count'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Campaign / Seasonal</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Gone (410)</span>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ $stats['gone_count'] ?? 0 }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Decommissioned URLs</div>
        </div>
    </div>

    <!-- Live Redirect Test Simulator & Add New Rule Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-7 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center gap-2 mb-4">
                <i class="bi bi-plus-circle-fill text-primary text-base"></i>
                <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Create New Redirect or 410 Rule</h2>
            </div>
            <form action="{{ route('admin.settings.redirects.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="source_path" class="block text-xs font-bold text-slate-700 mb-1">
                            Source Path <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="source_path" id="source_path" x-model="createSource" required
                            class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="/old-desert-safari">
                    </div>
                    <div>
                        <label for="target_path" class="block text-xs font-bold text-slate-700 mb-1">
                            Destination Target <span class="text-rose-500" x-show="Number(createCode) !== 410">*</span>
                        </label>
                        <input type="text" name="target_path" id="target_path" x-model="createTarget" :required="Number(createCode) !== 410" :disabled="Number(createCode) === 410"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary disabled:bg-slate-100"
                            placeholder="/evening-desert-safari-dubai">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="status_code" class="block text-xs font-bold text-slate-700 mb-1">HTTP Status</label>
                        <select name="status_code" id="status_code" x-model.number="createCode" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 font-semibold focus:border-primary">
                            <option value="301">301 - Moved Permanently</option>
                            <option value="302">302 - Found / Temporary</option>
                            <option value="307">307 - Temporary (Preserve Method)</option>
                            <option value="308">308 - Permanent (Preserve Method)</option>
                            <option value="410">410 - Gone (Permanently Removed)</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="notes" class="block text-xs font-bold text-slate-700 mb-1">Audit Notes / Reason</label>
                        <input type="text" name="notes" id="notes"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary"
                            placeholder="e.g. Discontinued seasonal route / preserves backlinks">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded-md border-slate-300 text-primary focus:ring-primary">
                        <span class="text-xs font-semibold text-slate-700">Activate Rule Immediately</span>
                    </label>
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold transition shadow-xs">
                        <i class="bi bi-check-circle"></i> Save Rule
                    </button>
                </div>
            </form>
        </div>

        <!-- Live Redirect Test Simulator -->
        <div class="lg:col-span-5 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <i class="bi bi-play-circle-fill text-sky-600 text-base"></i>
                    <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Live URL Simulator</h2>
                </div>
                <p class="text-xs text-slate-500 mb-4">
                    Test any incoming path to verify HTTP status, detect loops, and preview multi-hop resolution.
                </p>
                <div class="space-y-3">
                    <div class="flex gap-2">
                        <input type="text" x-model="testUrl" @keydown.enter.prevent="runTest()" placeholder="e.g. /morning-desert-safari"
                            class="flex-1 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 outline-hidden focus:border-sky-500 font-mono">
                        <button type="button" @click="runTest()" :disabled="testing || !testUrl"
                            class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold transition disabled:opacity-50 flex items-center gap-1.5 shrink-0">
                            <span x-show="!testing"><i class="bi bi-arrow-right-circle"></i> Test</span>
                            <span x-show="testing"><i class="bi bi-hourglass-split animate-spin"></i> Checking</span>
                        </button>
                    </div>

                    <template x-if="testResult">
                        <div class="p-3.5 rounded-xl text-xs border"
                            :class="testResult.matched ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-slate-50 border-slate-200 text-slate-700'">
                            <div class="font-bold flex items-center gap-1.5 mb-1">
                                <i :class="testResult.matched ? 'bi bi-check-circle-fill text-emerald-600' : 'bi bi-info-circle-fill text-slate-500'"></i>
                                <span x-text="testResult.matched ? `Rule Matched (HTTP ${testResult.status_code})` : 'Direct Route Resolution'"></span>
                            </div>
                            <div class="font-mono text-[11px] break-all" x-text="testResult.message"></div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 text-[11px] text-slate-400 flex items-center justify-between">
                <span>In-memory cached mapping</span>
                <span class="text-emerald-600 font-semibold flex items-center gap-1"><i class="bi bi-shield-check"></i> Zero DB latency</span>
            </div>
        </div>
    </div>

    <!-- Unresolved Top 404 Errors Inbox -->
    @if(isset($top404s) && $top404s->isNotEmpty())
    <div class="bg-white rounded-2xl border border-amber-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-amber-100 bg-amber-50/40 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-extrabold text-slate-900">Top Unresolved 404 URLs (Recovery Inbox)</h2>
                <p class="text-xs text-slate-500 mt-0.5">Frequently hit missing paths logged by visitors/crawlers that do not yet have a redirect or 410 rule.</p>
            </div>
            <span class="rounded-full bg-amber-100 text-amber-800 px-3 py-1 text-xs font-bold">{{ $top404s->count() }} flagged</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-slate-500 font-bold uppercase text-[10px]">
                        <th class="py-2.5 px-4">Requested 404 Path</th>
                        <th class="py-2.5 px-4">Hits</th>
                        <th class="py-2.5 px-4">Last Seen</th>
                        <th class="py-2.5 px-4 text-end">Quick Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($top404s as $err404)
                        <tr>
                            <td class="py-2.5 px-4 font-mono text-[11px] text-slate-800">{{ $err404->request_uri }}</td>
                            <td class="py-2.5 px-4 font-bold text-rose-700">{{ number_format($err404->hits) }}</td>
                            <td class="py-2.5 px-4 text-slate-500">{{ \Carbon\Carbon::parse($err404->last_seen_at)->diffForHumans() }}</td>
                            <td class="py-2.5 px-4 text-end space-x-1">
                                <button type="button" @click="prefill404(@js(parse_url($err404->request_uri, PHP_URL_PATH) ?: $err404->request_uri), 301)" class="px-2.5 py-1 rounded-lg bg-primary/10 text-primary font-bold text-[11px] hover:bg-primary/20">Create 301</button>
                                <button type="button" @click="prefill404(@js(parse_url($err404->request_uri, PHP_URL_PATH) ?: $err404->request_uri), 410)" class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-bold text-[11px] hover:bg-rose-100">Mark 410 Gone</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Redirect Rules Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 leading-tight">Configured Redirect &amp; Gone Rules</h2>
                <p class="text-xs text-slate-500 mt-0.5">Verify destinations, edit rules inline, and track crawl/referral hits.</p>
            </div>

            <form action="{{ route('admin.settings.redirects.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <select name="status_code" class="rounded-xl border border-slate-200 text-xs px-3 py-1.5 bg-white text-slate-700 font-medium focus:border-primary">
                    <option value="">All HTTP Codes</option>
                    <option value="301" @selected(request('status_code') === '301')>301 Permanent</option>
                    <option value="302" @selected(request('status_code') === '302')>302 Temporary</option>
                    <option value="307" @selected(request('status_code') === '307')>307 Temporary</option>
                    <option value="308" @selected(request('status_code') === '308')>308 Permanent</option>
                    <option value="410" @selected(request('status_code') === '410')>410 Gone</option>
                </select>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search path or note..." class="rounded-xl border border-slate-200 text-xs px-3 py-1.5 bg-white text-slate-700 placeholder-slate-400 focus:border-primary">
                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-slate-800 text-white text-xs font-bold hover:bg-slate-900 transition">Filter</button>
                @if(request()->hasAny(['search', 'status_code', 'status']))
                    <a href="{{ route('admin.settings.redirects.index') }}" class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-medium hover:bg-slate-50 transition">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Source URL</th>
                        <th class="py-3 px-4">Destination &amp; Verification</th>
                        <th class="py-3 px-4">Hits / Creator</th>
                        <th class="py-3 px-4">Notes</th>
                        <th class="py-3 px-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($rules as $rule)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black border uppercase {{ $rule->status_code == 410 ? 'bg-rose-100 text-rose-800 border-rose-200' : (in_array($rule->status_code, [301, 308]) ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-amber-100 text-amber-800 border-amber-200') }}">
                                    HTTP {{ $rule->status_code }}
                                </span>
                                @if(!$rule->is_active)
                                    <span class="block text-[10px] text-slate-400 font-bold uppercase mt-1">Disabled</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 align-top font-mono text-[11px] text-slate-800 font-bold max-w-xs break-all">
                                {{ $rule->source_path }}
                            </td>
                            <td class="py-3.5 px-4 align-top font-mono text-[11px] max-w-xs break-all">
                                @if($rule->status_code === 410)
                                    <span class="text-rose-700 font-bold">HTTP 410 Gone (No Target)</span>
                                @else
                                    <a href="{{ str_starts_with((string) $rule->target_path, 'http') ? $rule->target_path : url((string) $rule->target_path) }}" target="_blank" class="text-primary hover:underline inline-flex items-center gap-1">
                                        {{ $rule->target_path }} <i class="bi bi-box-arrow-up-right text-[10px]"></i>
                                    </a>
                                @endif
                                @if($rule->last_verified_at)
                                    <div class="mt-1 font-sans text-[10px] {{ $rule->destination_ok ? 'text-emerald-700' : 'text-rose-700' }}">
                                        Verified HTTP {{ $rule->last_verified_status }} · {{ $rule->last_verified_at->diffForHumans() }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                <div class="font-bold text-slate-900 text-xs">{{ number_format($rule->hit_count) }} hits</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $rule->last_hit_at ? $rule->last_hit_at->diffForHumans() : 'Never hit' }} · By {{ $creators[$rule->created_by] ?? 'System' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 align-top text-[11px] text-slate-500 max-w-xs">
                                {{ $rule->notes ?: '—' }}
                            </td>
                            <td class="py-3.5 px-4 align-top text-end whitespace-nowrap space-x-1">
                                <form action="{{ route('admin.settings.redirects.verify', $rule->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1.5 text-slate-500 hover:text-emerald-600 rounded-lg hover:bg-emerald-50 transition" title="Verify Destination">
                                        <i class="bi bi-patch-check"></i>
                                    </button>
                                </form>
                                <button type="button" @click='openEdit(@json($rule))' class="p-1.5 text-slate-500 hover:text-primary rounded-lg hover:bg-amber-50 transition" title="Edit Rule">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="{{ route('admin.settings.redirects.destroy', $rule->id) }}" method="POST" onsubmit="return confirm('Remove this redirect rule?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Delete Redirect">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400">
                                <i class="bi bi-signpost-2 text-3xl text-slate-300 block mb-2"></i>
                                <span class="font-bold text-slate-700 text-xs">No dynamic redirect rules configured yet.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rules->hasPages())
            <div class="p-4 border-t border-slate-100">{{ $rules->links() }}</div>
        @endif
    </div>

    <!-- Edit Modal -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div @click.outside="editModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-extrabold text-slate-900">Edit Redirect Rule</h3>
                <button type="button" @click="editModal = false" class="text-slate-400 hover:text-slate-700"><i class="bi bi-x-lg"></i></button>
            </div>
            <form :action="`{{ url('/admin/settings/redirects') }}/${editRule.id}`" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Source Path</label>
                    <input type="text" name="source_path" x-model="editRule.source_path" required class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Destination Target</label>
                    <input type="text" name="target_path" x-model="editRule.target_path" :required="Number(editRule.status_code) !== 410" :disabled="Number(editRule.status_code) === 410" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-mono disabled:bg-slate-100">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">HTTP Code</label>
                        <select name="status_code" x-model.number="editRule.status_code" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold">
                            <option value="301">301 Permanent</option>
                            <option value="302">302 Temporary</option>
                            <option value="307">307 Temporary</option>
                            <option value="308">308 Permanent</option>
                            <option value="410">410 Gone</option>
                        </select>
                    </div>
                    <div class="flex items-end pb-2">
                        <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-700">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" x-model="editRule.is_active" class="rounded border-slate-300 text-primary">
                            Active Rule
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Notes</label>
                    <input type="text" name="notes" x-model="editRule.notes" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
