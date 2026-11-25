@php
    $seoResolver = app(\App\Services\Seo\SeoResolverService::class);
    $keywordSvc = app(\App\Services\Seo\KeywordIntelligenceService::class);

    $contentType = match ($entityType ?? '') {
        'tour' => 'tour',
        'blog_post' => 'blog_post',
        'wiki_article' => 'wiki_article',
        default => 'page',
    };

    $entity->loadMissing('seoMeta', 'revisions');
    $resolvedSeo = $seoResolver->resolve($contentType, $entity);
    $seoMeta = $entity->seoMeta;

    $rawBody = match ($entityType ?? '') {
        'tour' => (string) ($entity->getTranslation('full_desc', 'en', true) ?: $entity->getTranslation('short_desc', 'en', true)),
        'blog_post', 'wiki_article' => (string) $entity->getTranslation('content', 'en', true),
        default => (string) $entity->getTranslation('subtitle', 'en', true),
    };
    $scorecard = $keywordSvc->analyzeOnPage(
        $resolvedSeo['title'],
        $resolvedSeo['description'],
        $rawBody,
        $resolvedSeo['focus_keyword']
    );
    $itemLinkSuggestions = $keywordSvc->suggestLinksForItem($rawBody, (string) ($entity->slug ?? ''));

    $publicTargetUrl = match ($entityType ?? '') {
        'blog_post' => url('/blog/'.$entity->slug),
        'wiki_article' => url('/wiki/'.($entity->pillar?->slug ?? 'guide').'/'.$entity->slug),
        default => ($entityType === 'page' && $entity->slug === 'home') ? url('/') : url('/'.ltrim((string) $entity->slug, '/')),
    };
@endphp

<div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden mt-8" x-data="{
    panelTab: 'seo',
    serpTitle: @js($resolvedSeo['title']),
    serpDesc: @js($resolvedSeo['description']),
    serpUrl: @js($resolvedSeo['canonical_url']),
    ogTitle: @js($seoMeta?->og_title ?: $resolvedSeo['og_title']),
    ogDesc: @js($seoMeta?->og_description ?: $resolvedSeo['og_description']),
    schemaJson: @js($seoMeta?->schema_json_override ?? ''),
    schemaStatus: null,
    pageAuditLoading: false,
    pageAuditResult: null,
    validateJsonLd() {
        if (!this.schemaJson.trim()) { this.schemaStatus = 'Empty (using automatic Graph)'; return; }
        try { JSON.parse(this.schemaJson); this.schemaStatus = 'Valid JSON-LD syntax'; }
        catch(e) { this.schemaStatus = 'Syntax error: ' + e.message; }
    },
    async runPageAudit() {
        this.pageAuditLoading = true;
        try {
            const res = await fetch(`{{ route('admin.audit.page') }}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ url: @js(parse_url($publicTargetUrl, PHP_URL_PATH) ?: '/') })
            });
            this.pageAuditResult = await res.json();
        } catch(e) {
            this.pageAuditResult = { error: 'Unable to run live audit check.' };
        } finally {
            this.pageAuditLoading = false;
        }
    }
}">
    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h5 class="text-sm font-extrabold text-slate-900">SEO Inheritance, Schema Preview, E-E-A-T Workflow &amp; Revision History</h5>
            <div class="flex flex-wrap gap-2 mt-1 text-[11px]">
                <span class="rounded-full bg-white border border-slate-200 px-2.5 py-0.5 font-bold text-slate-700">Title Origin: {{ $resolvedSeo['origins']['title'] ?? 'auto' }}</span>
                <span class="rounded-full bg-white border border-slate-200 px-2.5 py-0.5 font-bold text-slate-700">Robots Origin: {{ $resolvedSeo['origins']['robots'] ?? 'auto' }}</span>
                <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 font-bold text-emerald-800">On-Page Score: {{ $scorecard['score'] }}/100 ({{ $scorecard['word_count'] }} words)</span>
            </div>
        </div>
        <div class="flex flex-wrap gap-1.5">
            <button type="button" @click="panelTab = 'seo'" :class="panelTab === 'seo' ? 'bg-primary text-white' : 'bg-white text-slate-700 border border-slate-200'" class="px-3 py-1.5 rounded-xl text-xs font-bold">SEO &amp; Editorial</button>
            <button type="button" @click="panelTab = 'preview'" :class="panelTab === 'preview' ? 'bg-primary text-white' : 'bg-white text-slate-700 border border-slate-200'" class="px-3 py-1.5 rounded-xl text-xs font-bold">SERP &amp; Social Preview</button>
            <button type="button" @click="panelTab = 'revisions'" :class="panelTab === 'revisions' ? 'bg-primary text-white' : 'bg-white text-slate-700 border border-slate-200'" class="px-3 py-1.5 rounded-xl text-xs font-bold">Revisions ({{ $entity->revisions->count() }})</button>
            <button type="button" @click="panelTab = 'audit'; if(!pageAuditResult) runPageAudit()" :class="panelTab === 'audit' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 border border-slate-200'" class="px-3 py-1.5 rounded-xl text-xs font-bold">Live Page Audit</button>
        </div>
    </div>

    <div class="p-6">
        <!-- Tab 1: Normalized SEO Overrides & Editorial Review Workflow -->
        <div x-show="panelTab === 'seo'" class="space-y-5">
            <form method="POST" action="{{ route('admin.editorial.seo-meta.update', ['type' => $entityType, 'id' => $entity->id]) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Focus Keyword</label>
                        <input type="text" name="focus_keyword" value="{{ old('focus_keyword', $seoMeta?->focus_keyword ?? ($entity->focus_keyword ?? '')) }}" placeholder="e.g. evening desert safari dubai" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Secondary Keywords (comma separated)</label>
                        <input type="text" name="secondary_keywords" value="{{ old('secondary_keywords', is_array($seoMeta?->secondary_keywords) ? implode(', ', $seoMeta->secondary_keywords) : '') }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Search Intent</label>
                        <select name="search_intent" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                            <option value="">Auto / Inherit</option>
                            @foreach(['transactional', 'commercial', 'informational', 'navigational'] as $intent)
                                <option value="{{ $intent }}" @selected(($seoMeta?->search_intent ?? '') === $intent)>{{ ucfirst($intent) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Robots Override</label>
                        <input type="text" name="robots_directive" value="{{ old('robots_directive', $seoMeta?->robots_directive ?? '') }}" placeholder="Blank = inherit ({{ $resolvedSeo['template']?->robots_default ?? 'index, follow' }})" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Canonical Override URL</label>
                        <input type="text" name="canonical_override" value="{{ old('canonical_override', $seoMeta?->canonical_override ?? '') }}" placeholder="Blank = self-referential" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">XML Sitemap Inclusion</label>
                        <select name="include_in_sitemap" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                            <option value="inherit" @selected($seoMeta?->include_in_sitemap === null)>Inherit Template Default</option>
                            <option value="1" @selected($seoMeta?->include_in_sitemap === true)>Always Include</option>
                            <option value="0" @selected($seoMeta?->include_in_sitemap === false)>Exclude from Sitemaps</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">OpenGraph Title Override</label>
                        <input type="text" name="og_title" x-model="ogTitle" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Editorial Review State &amp; E-E-A-T</label>
                        <select name="editorial_status" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold">
                            @foreach(['published' => 'Published', 'approved' => 'Approved (Ready)', 'in_review' => 'In Editorial Review', 'needs_revision' => 'Needs Revision', 'draft' => 'Draft'] as $stKey => $stLabel)
                                <option value="{{ $stKey }}" @selected(($seoMeta?->editorial_status ?? 'published') === $stKey)>{{ $stLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">OpenGraph Description Override</label>
                    <textarea name="og_description" rows="2" x-model="ogDesc" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs"></textarea>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">Custom JSON-LD Schema Override (optional)</label>
                        <button type="button" @click="validateJsonLd()" class="text-[11px] font-bold text-primary hover:underline">Validate JSON-LD Syntax</button>
                    </div>
                    <textarea name="schema_json_override" rows="3" x-model="schemaJson" placeholder='{"&#64;context":"https://schema.org","&#64;type":"..."}' class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-mono"></textarea>
                    <div x-show="schemaStatus" class="text-[11px] font-bold mt-1 text-slate-600" x-text="schemaStatus"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Revision Change Summary Note</label>
                        <input type="text" name="change_summary" placeholder="e.g. Updated target keyword and seasonal FAQ schema" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    </div>
                    <div class="flex items-end justify-end gap-2">
                        <button type="submit" formaction="{{ route('admin.editorial.seo-meta.reset', ['type' => $entityType, 'id' => $entity->id]) }}" class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50">
                            Reset Overrides to Default
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:opacity-90">
                            Save SEO Metadata &amp; Record Revision
                        </button>
                    </div>
                </div>
            </form>

            <!-- Live On-Page Scorecard -->
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 space-y-3">
                <div class="text-xs font-extrabold text-slate-800">Readability, Accessibility &amp; On-Page Checks ({{ $scorecard['title_chars'] }} title chars / ~{{ $scorecard['title_pixels'] }}px · {{ $scorecard['description_chars'] }} desc chars · {{ $scorecard['read_minutes'] }} min read)</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                    @foreach($scorecard['checks'] as $chk)
                        <div class="flex items-center gap-2 {{ $chk['status'] === 'pass' ? 'text-emerald-700' : ($chk['status'] === 'warn' ? 'text-amber-700' : 'text-slate-600') }}">
                            <i class="bi {{ $chk['status'] === 'pass' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' }}"></i>
                            <span>{{ $chk['label'] }}</span>
                        </div>
                    @endforeach
                    @foreach(($scorecard['accessibility_checks'] ?? []) as $achk)
                        <div class="flex items-center gap-2 {{ $achk['status'] === 'pass' ? 'text-emerald-700' : 'text-amber-700' }}">
                            <i class="bi {{ $achk['status'] === 'pass' ? 'bi-universal-access-circle' : 'bi-exclamation-triangle-fill' }}"></i>
                            <span>{{ $achk['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                @if(!empty($itemLinkSuggestions))
                    <div class="pt-3 border-t border-slate-200">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Contextual Internal Linking Opportunities</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($itemLinkSuggestions as $ls)
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-white border border-slate-200 px-2.5 py-1 text-[11px] text-slate-700">
                                    Link <strong>{{ $ls['anchor'] }}</strong> &rarr; <code class="text-primary">{{ $ls['target_url'] }}</code>
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Tab 2: SERP & Social Card Previews -->
        <div x-show="panelTab === 'preview'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="rounded-2xl border border-slate-200 p-5 space-y-1.5 bg-white">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Google Desktop SERP Preview</div>
                <div class="text-xs text-emerald-700 font-mono truncate" x-text="serpUrl"></div>
                <div class="text-base font-semibold text-blue-700 hover:underline leading-snug" x-text="serpTitle"></div>
                <div class="text-xs text-slate-600 leading-relaxed" x-text="serpDesc"></div>
            </div>

            <div class="rounded-2xl border border-slate-200 overflow-hidden bg-slate-50">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 px-4 py-2 bg-white border-b border-slate-200">OpenGraph / Social Share Card</div>
                <img src="{{ $resolvedSeo['og_image'] }}" alt="OG Preview" class="w-full h-36 object-cover bg-slate-200">
                <div class="p-4 space-y-1">
                    <div class="text-[10px] uppercase text-slate-400 font-bold">{{ parse_url($resolvedSeo['canonical_url'], PHP_URL_HOST) ?? 'dunesdiscoverytourism.com' }}</div>
                    <div class="text-sm font-bold text-slate-900" x-text="ogTitle"></div>
                    <div class="text-xs text-slate-600 line-clamp-2" x-text="ogDesc"></div>
                </div>
            </div>
        </div>

        <!-- Tab 3: Revision History & Rollback -->
        <div x-show="panelTab === 'revisions'" x-cloak class="space-y-3">
            @forelse($entity->revisions as $rev)
                <div class="rounded-xl border border-slate-200 p-3.5 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div>
                        <span class="rounded bg-slate-900 text-white px-2 py-0.5 font-mono font-bold mr-2">v{{ $rev->version_number }}</span>
                        <span class="font-bold text-slate-800">{{ $rev->change_summary ?: 'Snapshot recorded' }}</span>
                        <div class="text-[11px] text-slate-500 mt-0.5">
                            By {{ $rev->created_by_name ?? 'System' }} · Status: {{ $rev->editorial_status ?? 'published' }} · {{ optional($rev->created_at)->diffForHumans() }}
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.editorial.revisions.restore', $rev->id) }}" onsubmit="return confirm('Restore content and SEO metadata to v{{ $rev->version_number }}?');">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50">
                            <i class="bi bi-arrow-counterclockwise"></i> Rollback to v{{ $rev->version_number }}
                        </button>
                    </form>
                </div>
            @empty
                <div class="text-xs text-slate-500 py-4">No revisions recorded yet. Save SEO Metadata above to start tracking version history.</div>
            @endforelse
        </div>

        <!-- Tab 4: On-Demand Live Page Audit Drawer -->
        <div x-show="panelTab === 'audit'" x-cloak class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="text-xs text-slate-600">Scanning live rendered HTML &amp; metadata for <code class="font-mono">{{ $publicTargetUrl }}</code></div>
                <button type="button" @click="runPageAudit()" :disabled="pageAuditLoading" class="px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold">
                    <span x-show="!pageAuditLoading">Re-run Live Scan</span>
                    <span x-show="pageAuditLoading">Scanning...</span>
                </button>
            </div>
            <template x-if="pageAuditResult && !pageAuditResult.error">
                <div class="space-y-3 text-xs">
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 flex items-center justify-between">
                        <div>
                            <div class="font-extrabold text-slate-900">Single-Page Health Score: <span x-text="pageAuditResult.health_score + '/100'"></span></div>
                            <div class="text-slate-500" x-text="`HTTP ${pageAuditResult.http_status} · ${pageAuditResult.issues?.length || 0} findings`"></div>
                        </div>
                    </div>
                    <template x-for="(iss, idx) in (pageAuditResult.issues || [])" :key="idx">
                        <div class="rounded-xl border border-slate-200 p-3 space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="rounded px-2 py-0.5 text-[10px] font-black uppercase bg-amber-100 text-amber-800" x-text="iss.severity"></span>
                                <span class="font-bold text-slate-900" x-text="iss.title"></span>
                            </div>
                            <div class="text-slate-600" x-text="iss.evidence"></div>
                            <div class="text-primary font-semibold" x-text="iss.recommended_action"></div>
                        </div>
                    </template>
                </div>
            </template>
            <template x-if="pageAuditResult && pageAuditResult.error">
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs text-rose-800" x-text="pageAuditResult.error"></div>
            </template>
        </div>
    </div>
</div>
