@extends('layouts.admin')

@section('page_title', 'Technical SEO: Dynamic Redirect Manager')

@section('content')
<div class="space-y-6" x-data="{
    testUrl: '',
    testResult: null,
    testing: false,
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
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-linear-to-br from-primary to-orange-600 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                <i class="bi bi-signpost-split"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Dynamic Redirect Manager & Loop Shield</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                        Technical SEO
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Manage 301, 302, 307, and 308 HTTP redirects. Real-time loop detection, multi-hop chain flattening, and inbound backlink PageRank preservation.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.settings.seo') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs">
                <i class="bi bi-search"></i> SEO Settings
            </a>
            <a href="{{ route('admin.audit.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition shadow-sm">
                <i class="bi bi-shield-check"></i> Audit Engine
            </a>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Rules</span>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">{{ $stats['active'] }} Active in Routing Table</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Redirect Hits</span>
            <div class="text-2xl font-black text-primary mt-1">{{ number_format($stats['total_hits']) }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">PageRank & Visitor Transferred</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Permanent (301/308)</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['permanent_count'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Search Engine Canonical Transfers</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Temporary (302/307)</span>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $stats['temporary_count'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Seasonal & Maintenance Reroutes</div>
        </div>
    </div>

    <!-- Live Redirect Test Simulator & Add New Rule Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Add New Rule Form (8 Cols) -->
        <div class="lg:col-span-7 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center gap-2 mb-4">
                <i class="bi bi-plus-circle-fill text-primary text-base"></i>
                <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Create New Redirect Rule</h2>
            </div>
            <form action="{{ route('admin.settings.redirects.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="source_path" class="block text-xs font-bold text-slate-700 mb-1">
                            Source Path <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="source_path" id="source_path" required
                            class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="/old-desert-safari">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Incoming legacy or broken URL path.</span>
                    </div>
                    <div>
                        <label for="target_path" class="block text-xs font-bold text-slate-700 mb-1">
                            Destination URL / Path <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="target_path" id="target_path" required
                            class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="/evening-desert-safari-dubai">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Canonical target route or external URL.</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="status_code" class="block text-xs font-bold text-slate-700 mb-1">HTTP Status</label>
                        <select name="status_code" id="status_code" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 font-semibold focus:border-primary">
                            <option value="301" selected>301 - Moved Permanently (SEO Standard)</option>
                            <option value="302">302 - Found / Temporary</option>
                            <option value="307">307 - Temporary (Preserve Method)</option>
                            <option value="308">308 - Permanent (Preserve Method)</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="notes" class="block text-xs font-bold text-slate-700 mb-1">Audit Notes / Reason</label>
                        <input type="text" name="notes" id="notes"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary"
                            placeholder="e.g. Migrated from old brochure link; preserves PR">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded-md border-slate-300 text-primary focus:ring-primary">
                        <span class="text-xs font-semibold text-slate-700">Activate Rule Immediately</span>
                    </label>
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold transition shadow-xs">
                        <i class="bi bi-check-circle"></i> Save Redirect Rule
                    </button>
                </div>
            </form>
        </div>

        <!-- Live Redirect Test Simulator (5 Cols) -->
        <div class="lg:col-span-5 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <i class="bi bi-play-circle-fill text-sky-600 text-base"></i>
                    <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Live URL Simulator</h2>
                </div>
                <p class="text-xs text-slate-500 mb-4">
                    Test any incoming URL to verify resolution, detect infinite loops, and preview chain hops before going live.
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

                    <!-- Simulation Result Box -->
                    <template x-if="testResult">
                        <div class="p-3.5 rounded-xl text-xs border"
                            :class="testResult.matched ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-slate-50 border-slate-200 text-slate-700'">
                            <div class="font-bold flex items-center gap-1.5 mb-1">
                                <i :class="testResult.matched ? 'bi bi-check-circle-fill text-emerald-600' : 'bi bi-info-circle-fill text-slate-500'"></i>
                                <span x-text="testResult.matched ? `Redirect Matched (HTTP ${testResult.status_code})` : 'Direct Route Resolution'"></span>
                            </div>
                            <div class="font-mono text-[11px] break-all" x-text="testResult.message"></div>
                            <template x-if="testResult.matched && testResult.hops > 1">
                                <div class="mt-2 text-[10px] text-amber-700 font-bold bg-amber-100 p-1.5 rounded-md">
                                    <i class="bi bi-exclamation-triangle-fill"></i> Multi-hop chain detected (<span x-text="testResult.hops"></span> hops). System will auto-resolve directly to final destination.
                                </div>
                            </template>
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

    <!-- Redirect Rules Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 leading-tight">Configured Redirect Rules</h2>
                <p class="text-xs text-slate-500 mt-0.5">Listing all database-driven redirects. Dynamic redirects execute seamlessly alongside existing legacy web routes.</p>
            </div>

            <form action="{{ route('admin.settings.redirects.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <select name="status_code" class="rounded-xl border border-slate-200 text-xs px-3 py-1.5 bg-white text-slate-700 font-medium focus:border-primary">
                    <option value="">All HTTP Codes</option>
                    <option value="301" {{ request('status_code') === '301' ? 'selected' : '' }}>301 Permanent</option>
                    <option value="302" {{ request('status_code') === '302' ? 'selected' : '' }}>302 Temporary</option>
                    <option value="307" {{ request('status_code') === '307' ? 'selected' : '' }}>307 Temporary</option>
                    <option value="308" {{ request('status_code') === '308' ? 'selected' : '' }}>308 Permanent</option>
                </select>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search path or note..." class="rounded-xl border border-slate-200 text-xs px-3 py-1.5 bg-white text-slate-700 placeholder-slate-400 focus:border-primary">
                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-slate-800 text-white text-xs font-bold hover:bg-slate-900 transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status_code', 'status']))
                    <a href="{{ route('admin.settings.redirects.index') }}" class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-medium hover:bg-slate-50 transition">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Source URL</th>
                        <th class="py-3 px-4">Destination Target</th>
                        <th class="py-3 px-4">Hits / Last Hit</th>
                        <th class="py-3 px-4">Notes</th>
                        <th class="py-3 px-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($rules as $rule)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black border uppercase {{ $rule->status_code == 301 ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-amber-100 text-amber-800 border-amber-200' }}">
                                    HTTP {{ $rule->status_code }}
                                </span>
                                @if(!$rule->is_active)
                                    <span class="block text-[10px] text-slate-400 font-bold uppercase mt-1">Disabled</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 align-top font-mono text-[11px] text-slate-800 font-bold max-w-xs break-all">
                                {{ $rule->source_path }}
                            </td>
                            <td class="py-3.5 px-4 align-top font-mono text-[11px] text-primary max-w-xs break-all">
                                <a href="{{ str_starts_with($rule->target_path, 'http') ? $rule->target_path : url($rule->target_path) }}" target="_blank" class="hover:underline flex items-center gap-1">
                                    {{ $rule->target_path }} <i class="bi bi-box-arrow-up-right text-[10px]"></i>
                                </a>
                            </td>
                            <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                <div class="font-bold text-slate-900 text-xs">{{ number_format($rule->hit_count) }} hits</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $rule->last_hit_at ? $rule->last_hit_at->diffForHumans() : 'Never accessed' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 align-top text-[11px] text-slate-500 max-w-xs">
                                {{ $rule->notes ?: '—' }}
                            </td>
                            <td class="py-3.5 px-4 align-top text-end whitespace-nowrap">
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
                                <p class="text-[11px] text-slate-400 mt-0.5">Add legacy paths above to preserve incoming search backlinks.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rules->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $rules->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
