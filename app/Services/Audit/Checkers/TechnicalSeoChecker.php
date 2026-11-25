<?php

namespace App\Services\Audit\Checkers;

use App\Http\Controllers\SitemapController;
use App\Models\BlogPost;
use App\Models\Language;
use App\Models\RedirectRule;
use App\Models\Tour;
use App\Models\WikiArticle;
use App\Services\RedirectManagerService;
use App\Services\Seo\CrawlerLogAnalyzerService;
use Illuminate\Support\Facades\File;

class TechnicalSeoChecker implements AuditCheckerInterface
{
    public function run(array $context = []): array
    {
        $issues = [];

        // 1. XML Sitemap Index & Sub-Sitemaps Verification
        try {
            $sitemapCtrl = app(SitemapController::class);
            $respIndex = $sitemapCtrl->index();
            $xml = (string) $respIndex->getContent();

            if (!str_contains($xml, '<sitemapindex') || !str_contains($xml, 'sitemap-tours.xml')) {
                $issues[] = [
                    'category' => 'technical_seo',
                    'check_key' => 'tech_seo_sitemap_index_malformed',
                    'title' => 'Google XML Sitemap Index Malformed',
                    'description' => 'The root sitemap_index.xml output does not contain the standard sitemapindex namespace or missing tour sub-sitemaps.',
                    'severity' => 'critical',
                    'verification_type' => 'automated',
                    'affected_url' => url('/sitemap_index.xml'),
                    'evidence' => 'XML output did not contain valid sitemapindex or sitemap-tours.xml reference.',
                    'recommended_action' => 'Inspect SitemapController::index() to ensure proper XML formatting.',
                ];
            }
        } catch (\Throwable $e) {
            $issues[] = [
                'category' => 'technical_seo',
                'check_key' => 'tech_seo_sitemap_error',
                'title' => 'Error Generating XML Sitemap',
                'description' => 'Sitemap generation encountered an unexpected exception: ' . $e->getMessage(),
                'severity' => 'critical',
                'verification_type' => 'automated',
                'affected_url' => url('/sitemap_index.xml'),
                'evidence' => $e->getMessage(),
                'recommended_action' => 'Resolve database query or cache exception in SitemapController.',
            ];
        }

        // 2. Redirect Rules Health (Loops, Chains & 404 targets)
        $redirectService = app(RedirectManagerService::class);
        $rules = RedirectRule::where('is_active', true)->get();

        foreach ($rules as $rule) {
            $check = $redirectService->detectLoopOrChain($rule->source_path, $rule->target_path, $rule->id);
            if ($check['is_loop']) {
                $issues[] = [
                    'category' => 'technical_seo',
                    'check_key' => 'tech_seo_redirect_loop',
                    'title' => "Redirect Loop Detected on '{$rule->source_path}'",
                    'description' => 'A redirect rule creates a continuous redirect loop, causing browser ERR_TOO_MANY_REDIRECTS and wasting crawler budget.',
                    'severity' => 'critical',
                    'verification_type' => 'automated',
                    'affected_url' => url($rule->source_path),
                    'evidence' => $check['message'],
                    'recommended_action' => 'Edit or deactivate redirect rule #' . $rule->id . ' to break the loop.',
                ];
            } elseif ($check['is_chain']) {
                $issues[] = [
                    'category' => 'technical_seo',
                    'check_key' => 'tech_seo_redirect_chain',
                    'title' => "Multi-Hop Redirect Chain Detected on '{$rule->source_path}'",
                    'description' => 'A redirect hops through multiple intermediate URLs before reaching the final destination, causing latency and PageRank dilution.',
                    'severity' => 'medium',
                    'verification_type' => 'automated',
                    'affected_url' => url($rule->source_path),
                    'evidence' => $check['message'],
                    'recommended_action' => "Update rule destination directly to '{$check['final_target']}' to bypass intermediate hops.",
                ];
            }
        }

        // 3. Custom 404 Recovery Template
        $hasCustom404 = File::exists(resource_path('views/errors/404.blade.php'));
        if (!$hasCustom404) {
            $issues[] = [
                'category' => 'technical_seo',
                'check_key' => 'tech_seo_missing_custom_404',
                'title' => 'Missing Custom 404 Error Recovery Page',
                'description' => 'A bespoke 404 error template with search bar and popular tour links is missing. Users landing on broken links may bounce immediately.',
                'severity' => 'medium',
                'verification_type' => 'automated',
                'affected_url' => url('/non-existent-page-test-404'),
                'evidence' => 'resources/views/errors/404.blade.php not found.',
                'recommended_action' => 'Create a user-friendly 404 Blade template with search bar, catalog links, and WhatsApp emergency contact.',
            ];
        }

        // 4. Tour Slug Cleanliness & Canonical Consistency
        $malformedTours = Tour::where('status', 'active')
            ->where(function ($q) {
                $q->where('slug', 'like', '% %')
                    ->orWhere('slug', 'like', '%/%')
                    ->orWhere('slug', 'like', '%?%');
            })
            ->get();

        foreach ($malformedTours as $t) {
            $issues[] = [
                'category' => 'technical_seo',
                'check_key' => 'tech_seo_malformed_slug',
                'title' => "Malformed URL Slug on Tour '{$t->name}'",
                'description' => 'Tour slug contains illegal characters (spaces, slashes, or query symbols) violating canonical URL standards.',
                'severity' => 'high',
                'verification_type' => 'automated',
                'affected_url' => url('/' . $t->slug),
                'evidence' => "Slug: '{$t->slug}'",
                'recommended_action' => 'Update the slug to use lowercase alphanumeric characters separated by hyphens.',
            ];
        }

        // 5. Multilingual ISO Code Standards
        $languages = Language::all();
        foreach ($languages as $lang) {
            if (strlen($lang->code) !== 2) {
                $issues[] = [
                    'category' => 'technical_seo',
                    'check_key' => 'tech_seo_invalid_lang_iso_code',
                    'title' => "Invalid ISO 639-1 Language Code '{$lang->code}'",
                    'description' => "Language '{$lang->name}' does not use a standard 2-letter ISO code for hreflang declarations.",
                    'severity' => 'medium',
                    'verification_type' => 'automated',
                    'affected_url' => url('/' . $lang->code),
                    'evidence' => "Code: '{$lang->code}'",
                    'recommended_action' => 'Use standard 2-letter codes (e.g. en, ar, ru, es, it).',
                ];
            }
        }

        // 6. Probed Live HTML Canonical & Hreflang Reciprocity Verification
        foreach (($context['probed_pages'] ?? []) as $path => $probe) {
            if ((int) ($probe['status'] ?? 500) !== 200) {
                continue;
            }
            $html = (string) ($probe['html'] ?? '');
            if ($html === '') {
                continue;
            }
            $isNoindex = preg_match('/<meta[^>]+name=["\']robots["\'][^>]+content=["\'][^"\']*noindex/i', $html) === 1;
            if (! preg_match('/<link[^>]+rel=["\']canonical["\'][^>]+href=["\']([^"\']+)["\']/i', $html)) {
                $issues[] = [
                    'category' => 'technical_seo',
                    'check_key' => 'tech_seo_missing_canonical',
                    'title' => "Missing Canonical Tag on {$path}",
                    'description' => 'Rendered HTML lacks a self-referential <link rel="canonical"> declaration.',
                    'severity' => 'high',
                    'verification_type' => 'automated',
                    'affected_url' => url($path),
                    'evidence' => "No canonical link element found on {$path}.",
                    'recommended_action' => 'Ensure layout <head> outputs an absolute canonical URL.',
                ];
            }
            if (! $isNoindex && ! preg_match('/<link[^>]+rel=["\']alternate["\'][^>]+hreflang=["\']x-default["\']/i', $html)) {
                $issues[] = [
                    'category' => 'technical_seo',
                    'check_key' => 'tech_seo_missing_hreflang_xdefault',
                    'title' => "Missing hreflang=\"x-default\" on {$path}",
                    'description' => 'Indexable multilingual page is missing an x-default hreflang alternate tag.',
                    'severity' => 'medium',
                    'verification_type' => 'automated',
                    'affected_url' => url($path),
                    'evidence' => "No hreflang='x-default' alternate found on {$path}.",
                    'recommended_action' => 'Verify international hreflang loop in layouts/app.blade.php.',
                ];
            }
        }

        // 7. Contextual Orphan Page Detection (Published blogs/wiki with 0 inbound contextual links)
        try {
            $publishedBlogs = BlogPost::where('status', 'published')->get(['id', 'slug', 'title', 'content']);
            $publishedWikis = WikiArticle::where('status', 'published')->with('pillar:id,slug')->get(['id', 'pillar_id', 'slug', 'title', 'content']);
            $tourBodies = Tour::where('status', 'active')->pluck('full_desc')->implode("\n");
            $allCorpus = strtolower(
                $tourBodies."\n"
                .$publishedBlogs->pluck('content')->implode("\n")."\n"
                .$publishedWikis->pluck('content')->implode("\n")
            );

            $orphanCount = 0;
            foreach ($publishedBlogs as $b) {
                if ($orphanCount >= 5) {
                    break;
                }
                if (! str_contains($allCorpus, '/blog/'.strtolower((string) $b->slug))) {
                    $orphanCount++;
                    $issues[] = [
                        'category' => 'technical_seo',
                        'check_key' => 'tech_seo_orphan_internal_linking',
                        'title' => "No Contextual Inbound Links to Blog '{$b->title}'",
                        'description' => 'While listed on the blog archive, no other tour, blog post, or wiki article links contextually to this URL.',
                        'severity' => 'low',
                        'verification_type' => 'partial_automated',
                        'affected_url' => url('/blog/'.$b->slug),
                        'evidence' => "Zero occurrences of '/blog/{$b->slug}' across published body copy.",
                        'recommended_action' => 'Add 1–2 contextual internal links from related tours, wiki pillars, or blog guides.',
                    ];
                }
            }
        } catch (\Throwable) {
        }

        // 8. Observed Crawl Waste Anomaly Check (from Request Logs)
        try {
            $crawl = app(CrawlerLogAnalyzerService::class)->analyze(14);
            if (($crawl['total_bot_hits'] ?? 0) >= 20 && ($crawl['crawl_waste_pct'] ?? 0) >= 25.0) {
                $issues[] = [
                    'category' => 'technical_seo',
                    'check_key' => 'tech_seo_elevated_crawl_waste',
                    'title' => "Elevated Crawler Waste Detected ({$crawl['crawl_waste_pct']}% of Bot Hits)",
                    'description' => 'Over 25% of observed bot hits in the last 14 days targeted 404 URLs or parameterized query URLs.',
                    'severity' => 'medium',
                    'verification_type' => 'automated',
                    'affected_url' => route('admin.ai-visibility.index'),
                    'evidence' => "Bot 404 hits: {$crawl['wasted_404_bot_hits']}, Query-param bot hits: {$crawl['wasted_query_bot_hits']} of {$crawl['total_bot_hits']} total bot hits.",
                    'recommended_action' => 'Review Crawler Log Analyzer and map top 404 paths in Redirect Manager.',
                ];
            }
        } catch (\Throwable) {
        }

        return $issues;
    }
}
