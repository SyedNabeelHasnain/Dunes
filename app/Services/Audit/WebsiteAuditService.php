<?php

namespace App\Services\Audit;

use App\Models\AuditIssue;
use App\Models\AuditRun;
use App\Services\Audit\Checkers\AccessibilityChecker;
use App\Services\Audit\Checkers\AiReadinessChecker;
use App\Services\Audit\Checkers\ContentQualityChecker;
use App\Services\Audit\Checkers\PerformanceChecker;
use App\Services\Audit\Checkers\PrivacyChecker;
use App\Services\Audit\Checkers\SecurityChecker;
use App\Services\Audit\Checkers\SeoAuditChecker;
use App\Services\Audit\Checkers\StructuredDataChecker;
use App\Services\Audit\Checkers\TechnicalSeoChecker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebsiteAuditService
{
    /**
     * Category weights contributing to Overall Health Score (total = 1.00 / 100%).
     */
    protected const CATEGORY_WEIGHTS = [
        'technical_seo' => 0.15,
        'seo' => 0.15,
        'performance' => 0.15,
        'security' => 0.15,
        'accessibility' => 0.10,
        'ai_readiness' => 0.10,
        'structured_data' => 0.10,
        'content_quality' => 0.05,
        'privacy' => 0.05,
    ];

    /**
     * Severity deduction points for category score calculation.
     */
    protected const SEVERITY_PENALTIES = [
        'critical' => 20,
        'high' => 10,
        'medium' => 5,
        'low' => 2,
        'info' => 0,
    ];

    /**
     * Checker registration map.
     */
    protected array $checkers = [
        SeoAuditChecker::class,
        AiReadinessChecker::class,
        TechnicalSeoChecker::class,
        PerformanceChecker::class,
        AccessibilityChecker::class,
        SecurityChecker::class,
        StructuredDataChecker::class,
        ContentQualityChecker::class,
        PrivacyChecker::class,
    ];

    /**
     * Executes a complete or incremental website audit run.
     */
    public function runAudit(string $scanType = 'full', ?int $triggeredBy = null, array $context = []): AuditRun
    {
        $startTime = microtime(true);

        $run = AuditRun::create([
            'scan_type' => $scanType,
            'status' => 'running',
            'health_score' => 100,
            'triggered_by' => $triggeredBy,
        ]);

        $allIssues = [];
        $uniqueUrls = [];

        foreach ($this->checkers as $checkerClass) {
            try {
                /** @var \App\Services\Audit\Checkers\AuditCheckerInterface $checker */
                $checker = app($checkerClass);
                $checkerIssues = $checker->run($context);

                foreach ($checkerIssues as $issue) {
                    $allIssues[] = $issue;
                    if (!empty($issue['affected_url'])) {
                        $uniqueUrls[$issue['affected_url']] = true;
                    }
                }
            } catch (\Throwable $e) {
                Log::error("WebsiteAuditService checker failed [{$checkerClass}]: " . $e->getMessage(), [
                    'exception' => $e,
                ]);
            }
        }

        // Tally category scores and severities
        $categoryCounts = [];
        $severityCounts = [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0,
            'info' => 0,
        ];

        foreach (array_keys(self::CATEGORY_WEIGHTS) as $cat) {
            $categoryCounts[$cat] = [
                'critical' => 0,
                'high' => 0,
                'medium' => 0,
                'low' => 0,
                'info' => 0,
                'total' => 0,
            ];
        }

        foreach ($allIssues as $issue) {
            $cat = $issue['category'] ?? 'seo';
            $sev = $issue['severity'] ?? 'medium';

            if (!isset($categoryCounts[$cat])) {
                $categoryCounts[$cat] = ['critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0, 'info' => 0, 'total' => 0];
            }

            $categoryCounts[$cat][$sev] = ($categoryCounts[$cat][$sev] ?? 0) + 1;
            $categoryCounts[$cat]['total']++;
            $severityCounts[$sev] = ($severityCounts[$sev] ?? 0) + 1;
        }

        // Calculate Category Scores
        $categoryScores = [];
        $weightedHealth = 0.0;

        foreach (self::CATEGORY_WEIGHTS as $cat => $weight) {
            $crit = $categoryCounts[$cat]['critical'] ?? 0;
            $high = $categoryCounts[$cat]['high'] ?? 0;
            $med = $categoryCounts[$cat]['medium'] ?? 0;
            $low = $categoryCounts[$cat]['low'] ?? 0;

            $deduction = ($crit * self::SEVERITY_PENALTIES['critical'])
                       + ($high * self::SEVERITY_PENALTIES['high'])
                       + ($med * self::SEVERITY_PENALTIES['medium'])
                       + ($low * self::SEVERITY_PENALTIES['low']);

            $score = max(0, 100 - $deduction);
            $categoryScores[$cat] = [
                'score' => $score,
                'label' => $this->getCategoryLabel($cat),
                'counts' => $categoryCounts[$cat],
                'weight' => $weight * 100,
            ];

            $weightedHealth += ($score * $weight);
        }

        $healthScore = (int) round($weightedHealth);
        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        // Batch insert audit issues inside transaction
        DB::transaction(function () use ($run, $allIssues) {
            $now = now();
            $records = [];
            foreach ($allIssues as $issue) {
                $records[] = [
                    'audit_run_id' => $run->id,
                    'category' => $issue['category'],
                    'check_key' => $issue['check_key'],
                    'title' => $issue['title'],
                    'description' => $issue['description'] ?? null,
                    'severity' => $issue['severity'],
                    'status' => 'open',
                    'verification_type' => $issue['verification_type'] ?? 'automated',
                    'affected_url' => $issue['affected_url'] ?? null,
                    'evidence' => is_array($issue['evidence'] ?? null) ? json_encode($issue['evidence']) : ($issue['evidence'] ?? null),
                    'recommended_action' => $issue['recommended_action'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($records)) {
                // Insert in chunks of 100
                foreach (array_chunk($records, 100) as $chunk) {
                    AuditIssue::insert($chunk);
                }
            }
        });

        // Update AuditRun details
        $run->update([
            'status' => 'completed',
            'health_score' => $healthScore,
            'category_scores' => $categoryScores,
            'total_issues_count' => count($allIssues),
            'critical_count' => $severityCounts['critical'],
            'high_count' => $severityCounts['high'],
            'medium_count' => $severityCounts['medium'],
            'low_count' => $severityCounts['low'],
            'info_count' => $severityCounts['info'],
            'scanned_urls_count' => count($uniqueUrls),
            'duration_ms' => $durationMs,
        ]);

        return $run->fresh('issues');
    }

    /**
     * Retrieve the most recent completed audit run.
     */
    public function getLatestRun(): ?AuditRun
    {
        return AuditRun::where('status', 'completed')
            ->latest('id')
            ->first();
    }

    /**
     * Formats human-friendly category label.
     */
    public function getCategoryLabel(string $category): string
    {
        return match ($category) {
            'seo' => 'On-Page SEO & Metadata',
            'technical_seo' => 'Technical SEO & Crawlability',
            'ai_readiness' => 'AI Search & GEO Discoverability',
            'performance' => 'Performance & Core Web Vitals',
            'accessibility' => 'Accessibility (WCAG 2.2 AA)',
            'security' => 'Security & OWASP Headers',
            'structured_data' => 'Schema.org JSON-LD Graphs',
            'content_quality' => 'Content Quality & E-E-A-T',
            'privacy' => 'Privacy & Cookie Consent',
            default => ucwords(str_replace('_', ' ', $category)),
        };
    }
}
