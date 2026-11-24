<?php

namespace App\Services\Audit;

use App\Models\AuditIssue;
use App\Models\AuditRun;
use App\Models\BlogPost;
use App\Models\Tour;
use App\Services\Audit\Checkers\AccessibilityChecker;
use App\Services\Audit\Checkers\AiReadinessChecker;
use App\Services\Audit\Checkers\ContentQualityChecker;
use App\Services\Audit\Checkers\InfrastructureChecker;
use App\Services\Audit\Checkers\PerformanceChecker;
use App\Services\Audit\Checkers\PrivacyChecker;
use App\Services\Audit\Checkers\SecurityChecker;
use App\Services\Audit\Checkers\SeoAuditChecker;
use App\Services\Audit\Checkers\StructuredDataChecker;
use App\Services\Audit\Checkers\TechnicalSeoChecker;
use App\Services\Audit\Checkers\UxChecker;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebsiteAuditService
{
    /**
     * Normalized 11-discipline Category Weights (Total = 1.00 / 100%).
     */
    protected const CATEGORY_WEIGHTS = [
        'technical_seo' => 0.14,
        'seo' => 0.14,
        'performance' => 0.14,
        'security' => 0.14,
        'accessibility' => 0.10,
        'structured_data' => 0.08,
        'ai_readiness' => 0.08,
        'privacy' => 0.06,
        'content_quality' => 0.05,
        'ux' => 0.04,
        'infrastructure' => 0.03,
    ];

    protected const SEVERITY_PENALTIES = [
        'critical' => 20,
        'high' => 10,
        'medium' => 5,
        'low' => 2,
        'info' => 0,
    ];

    /**
     * Execute a full or incremental multi-discipline audit, persist issues & diffs.
     */
    public function runAudit(string $scanType = 'full', ?int $triggeredBy = null, array $context = []): AuditRun
    {
        $startTime = microtime(true);
        $previousRun = $this->getLatestRun();

        $probePaths = $this->determineProbePaths($scanType, $context['target_url'] ?? null);
        $probedPages = $context['probed_pages'] ?? $this->probeInternalUrls($probePaths);

        $checkerContext = array_merge($context, [
            'scan_type' => $scanType,
            'probed_pages' => $probedPages,
        ]);

        $checkers = [
            TechnicalSeoChecker::class,
            SeoAuditChecker::class,
            PerformanceChecker::class,
            SecurityChecker::class,
            AccessibilityChecker::class,
            AiReadinessChecker::class,
            StructuredDataChecker::class,
            ContentQualityChecker::class,
            PrivacyChecker::class,
            UxChecker::class,
            InfrastructureChecker::class,
        ];

        $allIssues = [];
        foreach ($checkers as $checkerClass) {
            try {
                $checker = app($checkerClass);
                $results = $checker->run($checkerContext);
                foreach ($results as $issueData) {
                    $allIssues[] = $issueData;
                }
            } catch (\Throwable $e) {
                $allIssues[] = [
                    'category' => 'infrastructure',
                    'check_key' => 'audit_checker_exception',
                    'title' => 'Checker Execution Error: '.class_basename($checkerClass),
                    'description' => 'An internal exception occurred while running this module checker.',
                    'severity' => 'low',
                    'verification_type' => 'automated',
                    'affected_url' => url('/admin/audit'),
                    'evidence' => $e->getMessage(),
                    'recommended_action' => 'Inspect application logs for full stack trace.',
                ];
            }
        }

        // Deduplicate identical (check_key, affected_url) findings within same scan
        $deduped = [];
        foreach ($allIssues as $iss) {
            $sig = ($iss['check_key'] ?? 'check').'|'.($iss['affected_url'] ?? 'global').'|'.($iss['title'] ?? '');
            $deduped[$sig] = $iss;
        }
        $allIssues = array_values($deduped);

        $severityCounts = ['critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0, 'info' => 0];
        $categoryCounts = [];
        foreach (array_keys(self::CATEGORY_WEIGHTS) as $cat) {
            $categoryCounts[$cat] = ['critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0, 'info' => 0, 'total' => 0];
        }

        foreach ($allIssues as $issue) {
            $sev = strtolower((string) ($issue['severity'] ?? 'info'));
            $cat = strtolower((string) ($issue['category'] ?? 'seo'));

            if (! isset($severityCounts[$sev])) {
                $sev = 'info';
            }
            if (! isset($categoryCounts[$cat])) {
                $categoryCounts[$cat] = ['critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0, 'info' => 0, 'total' => 0];
            }

            $severityCounts[$sev]++;
            $categoryCounts[$cat][$sev]++;
            $categoryCounts[$cat]['total']++;
        }

        $categoryScores = [];
        $compositeScore = 0.0;

        foreach (self::CATEGORY_WEIGHTS as $cat => $weight) {
            $crit = $categoryCounts[$cat]['critical'] ?? 0;
            $high = $categoryCounts[$cat]['high'] ?? 0;
            $med = $categoryCounts[$cat]['medium'] ?? 0;
            $low = $categoryCounts[$cat]['low'] ?? 0;

            $penalty = ($crit * self::SEVERITY_PENALTIES['critical'])
                + ($high * self::SEVERITY_PENALTIES['high'])
                + ($med * self::SEVERITY_PENALTIES['medium'])
                + ($low * self::SEVERITY_PENALTIES['low']);

            $score = max(0, 100 - $penalty);
            $categoryScores[$cat] = [
                'label' => $this->getCategoryLabel($cat),
                'score' => $score,
                'weight' => round($weight * 100),
                'counts' => $categoryCounts[$cat],
            ];

            $compositeScore += $score * $weight;
        }

        $overallScore = (int) round(max(0, min(100, $compositeScore)));
        $durationMs = (int) max(1, round((microtime(true) - $startTime) * 1000));
        $scannedEntities = count($probedPages) + Tour::where('status', 'active')->count() + BlogPost::where('status', 'published')->count() + 15;

        return DB::transaction(function () use (
            $scanType,
            $overallScore,
            $categoryScores,
            $allIssues,
            $severityCounts,
            $scannedEntities,
            $durationMs,
            $triggeredBy,
            $previousRun,
            $context
        ) {
            $run = AuditRun::create([
                'scan_type' => $scanType,
                'status' => 'completed',
                'health_score' => $overallScore,
                'category_scores' => $categoryScores,
                'total_issues_count' => count($allIssues),
                'critical_count' => $severityCounts['critical'],
                'high_count' => $severityCounts['high'],
                'medium_count' => $severityCounts['medium'],
                'low_count' => $severityCounts['low'],
                'info_count' => $severityCounts['info'],
                'scanned_urls_count' => $scannedEntities,
                'duration_ms' => $durationMs,
                'triggered_by' => $triggeredBy,
                'parent_run_id' => $previousRun?->id,
                'target_url' => $context['target_url'] ?? null,
            ]);

            if ($allIssues !== []) {
                $now = now();
                $rows = [];
                foreach ($allIssues as $iss) {
                    $rows[] = [
                        'audit_run_id' => $run->id,
                        'category' => $iss['category'] ?? 'seo',
                        'check_key' => $iss['check_key'] ?? 'generic_check',
                        'title' => mb_substr((string) ($iss['title'] ?? 'Audit Notice'), 0, 250),
                        'description' => $iss['description'] ?? null,
                        'severity' => $iss['severity'] ?? 'medium',
                        'status' => 'open',
                        'verification_type' => $iss['verification_type'] ?? 'automated',
                        'affected_url' => $iss['affected_url'] ?? null,
                        'evidence' => $iss['evidence'] ?? null,
                        'recommended_action' => $iss['recommended_action'] ?? null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                foreach (array_chunk($rows, 100) as $chunk) {
                    AuditIssue::insert($chunk);
                }
            }

            return $run;
        });
    }

    /**
     * Evaluate a single URL live via the HTTP kernel (used by Editor Live Audit Drawer and CLI).
     */
    public function auditSingleUrl(string $path, bool $persistRun = false, ?int $triggeredBy = null): array
    {
        $cleanPath = '/'.ltrim((string) parse_url($path, PHP_URL_PATH), '/');
        if ($cleanPath === '') {
            $cleanPath = '/';
        }

        $probed = $this->probeInternalUrls([$cleanPath]);
        $probe = $probed[$cleanPath] ?? ['status' => 500, 'html' => ''];

        $issues = [];
        foreach ([
            UxChecker::class,
            AccessibilityChecker::class,
            PerformanceChecker::class,
            StructuredDataChecker::class,
            PrivacyChecker::class,
        ] as $checkerClass) {
            try {
                foreach (app($checkerClass)->run(['probed_pages' => [$cleanPath => $probe], 'single_url' => $cleanPath]) as $iss) {
                    $target = (string) ($iss['affected_url'] ?? '');
                    if ($target === '' || str_ends_with(rtrim($target, '/'), rtrim($cleanPath, '/')) || $cleanPath === '/') {
                        $issues[] = $iss;
                    }
                }
            } catch (\Throwable) {
            }
        }

        // Additional live single-page DOM metadata check
        $html = (string) ($probe['html'] ?? '');
        if ($html !== '') {
            if (! preg_match('/<title>(.*?)<\/title>/is', $html, $tm) || trim($tm[1]) === '') {
                $issues[] = [
                    'category' => 'seo',
                    'check_key' => 'page_missing_title',
                    'title' => 'Rendered Page Missing <title> Tag',
                    'severity' => 'critical',
                    'evidence' => 'Empty or missing <title> element.',
                    'recommended_action' => 'Configure SEO title on item or Content Type Template.',
                ];
            }
            if (! preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']*)["\']/i', $html, $dm) || mb_strlen(trim($dm[1])) < 40) {
                $issues[] = [
                    'category' => 'seo',
                    'check_key' => 'page_short_description',
                    'title' => 'Meta Description Missing or Too Short (< 40 chars)',
                    'severity' => 'medium',
                    'evidence' => 'Extracted length: '.mb_strlen(trim($dm[1] ?? '')).' chars.',
                    'recommended_action' => 'Write a compelling 120–155 character meta description.',
                ];
            }
            if (! preg_match('/<link[^>]+rel=["\']canonical["\']/i', $html)) {
                $issues[] = [
                    'category' => 'technical_seo',
                    'check_key' => 'page_missing_canonical',
                    'title' => 'Rendered Page Missing <link rel="canonical">',
                    'severity' => 'high',
                    'evidence' => 'No canonical tag found in rendered HTML head.',
                    'recommended_action' => 'Verify canonical tag output in layout header.',
                ];
            }
        }

        $penalty = 0;
        foreach ($issues as $iss) {
            $penalty += self::SEVERITY_PENALTIES[strtolower((string) ($iss['severity'] ?? 'info'))] ?? 0;
        }
        $pageScore = max(0, 100 - $penalty);

        $runId = null;
        if ($persistRun) {
            $run = $this->runAudit('single_url', $triggeredBy, ['target_url' => $cleanPath, 'probed_pages' => [$cleanPath => $probe]]);
            $runId = $run->id;
        }

        return [
            'url' => url($cleanPath),
            'path' => $cleanPath,
            'http_status' => (int) ($probe['status'] ?? 200),
            'health_score' => $pageScore,
            'issues_count' => count($issues),
            'issues' => $issues,
            'run_id' => $runId,
        ];
    }

    /**
     * Compare current audit run with a baseline previous run to report improvements and regressions.
     */
    public function compareRuns(AuditRun $current, ?AuditRun $previous): ?array
    {
        if (! $previous || $previous->id === $current->id) {
            return null;
        }

        $current->loadMissing('issues');
        $previous->loadMissing('issues');

        $sig = fn (AuditIssue $i): string => $i->check_key.'|'.($i->affected_url ?? 'global');
        $currMap = $current->issues->keyBy($sig);
        $prevMap = $previous->issues->keyBy($sig);

        $newKeys = array_diff(array_keys($currMap->all()), array_keys($prevMap->all()));
        $resolvedKeys = array_diff(array_keys($prevMap->all()), array_keys($currMap->all()));

        return [
            'previous_run_id' => $previous->id,
            'previous_score' => $previous->health_score,
            'score_delta' => $current->health_score - $previous->health_score,
            'new_count' => count($newKeys),
            'resolved_count' => count($resolvedKeys),
            'new_issues' => collect($newKeys)->map(fn ($k) => $currMap->get($k))->filter()->take(8)->values(),
            'resolved_issues' => collect($resolvedKeys)->map(fn ($k) => $prevMap->get($k))->filter()->take(8)->values(),
        ];
    }

    public function getLatestRun(): ?AuditRun
    {
        return AuditRun::where('status', 'completed')
            ->latest('id')
            ->first();
    }

    public function getCategoryLabel(string $category): string
    {
        return match ($category) {
            'technical_seo' => 'Technical SEO & Crawlability',
            'seo' => 'On-Page SEO & Metadata',
            'performance' => 'Performance & Core Web Vitals',
            'security' => 'Security & OWASP Top 10:2025',
            'accessibility' => 'Accessibility (WCAG 2.2 AA)',
            'ai_readiness' => 'AI Readiness & GEO / LLMO',
            'structured_data' => 'Structured Data (Schema.org)',
            'content_quality' => 'Content Quality & E-E-A-T',
            'privacy' => 'Privacy & Consent Compliance',
            'ux' => 'UX & Error Recovery',
            'infrastructure' => 'Infrastructure & Queue Health',
            default => ucwords(str_replace('_', ' ', $category)),
        };
    }

    public function getScoringMethodology(): array
    {
        $weights = [];
        foreach (self::CATEGORY_WEIGHTS as $key => $weight) {
            $weights[] = [
                'key' => $key,
                'label' => $this->getCategoryLabel($key),
                'weight_pct' => (int) round($weight * 100),
            ];
        }

        return [
            'formula' => 'Category Score = max(0, 100 - (20 * Critical) - (10 * High) - (5 * Medium) - (2 * Low))',
            'penalties' => self::SEVERITY_PENALTIES,
            'weights' => $weights,
            'classification_guide' => [
                'automated' => '100% deterministic verification directly against live DOM, HTTP headers, schemas, or database state.',
                'partial_automated' => 'Programmatically detected signals requiring contextual human editorial validation.',
                'manual_review' => 'Architectural or UX/screen-reader guidelines where full sign-off requires human QA.',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private function determineProbePaths(string $scanType, ?string $targetUrl = null): array
    {
        if ($scanType === 'single_url' && $targetUrl) {
            return ['/'.ltrim((string) parse_url($targetUrl, PHP_URL_PATH), '/') ?: '/'];
        }

        $paths = ['/', '/tours', '/about', '/blog', '/wiki'];

        if ($scanType === 'full') {
            $tourSlugs = Tour::where('status', 'active')->orderBy('priority')->limit(2)->pluck('slug');
            foreach ($tourSlugs as $slug) {
                $paths[] = '/'.ltrim((string) $slug, '/');
            }
            $blogSlugs = BlogPost::where('status', 'published')->latest('id')->limit(2)->pluck('slug');
            foreach ($blogSlugs as $slug) {
                $paths[] = '/blog/'.ltrim((string) $slug, '/');
            }
        }

        return array_values(array_unique($paths));
    }

    /**
     * Execute clean internal GET sub-requests without disturbing the outer HTTP request or auth session.
     *
     * @param  list<string>  $paths
     * @return array<string, array{status:int, html:string}>
     */
    private function probeInternalUrls(array $paths): array
    {
        $results = [];
        $kernel = app(HttpKernel::class);
        $originalRequest = app()->bound('request') ? request() : null;

        foreach ($paths as $path) {
            $norm = $path === '' ? '/' : '/'.ltrim($path, '/');
            if (str_starts_with($norm, '/admin')) {
                continue;
            }
            try {
                $subReq = Request::create($norm, 'GET');
                $subReq->headers->set('User-Agent', 'DunesAuditBot/1.0');
                $subRes = $kernel->handle($subReq);
                $results[$norm] = [
                    'status' => $subRes->getStatusCode(),
                    'html' => str_contains((string) $subRes->headers->get('Content-Type'), 'html') || $subRes->getStatusCode() === 200
                        ? (string) $subRes->getContent()
                        : '',
                ];
            } catch (\Throwable) {
                $results[$norm] = ['status' => 500, 'html' => ''];
            }
        }

        if ($originalRequest) {
            app()->instance('request', $originalRequest);
        }

        return $results;
    }
}
