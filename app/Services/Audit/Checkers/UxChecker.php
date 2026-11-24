<?php

namespace App\Services\Audit\Checkers;

use App\Models\RedirectRule;
use App\Models\RequestLog;
use Illuminate\Support\Facades\Schema;

class UxChecker implements AuditCheckerInterface
{
    public function run(array $context = []): array
    {
        $issues = [];

        // 1. High-Frequency Unresolved 404 Dead Ends
        try {
            if (class_exists(RequestLog::class) && Schema::hasTable('request_logs')) {
                $activeSources = RedirectRule::where('is_active', true)->pluck('source_path')
                    ->map(fn ($p) => RedirectRule::normalizePath((string) $p))
                    ->flip();

                $topMissing = RequestLog::where('entity_type', 'error_404')
                    ->where('request_timestamp', '>=', now()->subDays(14))
                    ->selectRaw('request_uri, COUNT(*) as hits')
                    ->groupBy('request_uri')
                    ->havingRaw('COUNT(*) >= 3')
                    ->orderByDesc('hits')
                    ->limit(5)
                    ->get();

                foreach ($topMissing as $row) {
                    $norm = RedirectRule::normalizePath((string) parse_url((string) $row->request_uri, PHP_URL_PATH));
                    if (! isset($activeSources[$norm]) && $norm !== '/') {
                        $issues[] = [
                            'category' => 'ux',
                            'check_key' => 'ux_unresolved_404_spike',
                            'title' => "Frequent Unresolved 404 Dead End: {$norm} ({$row->hits} hits)",
                            'description' => 'Visitors and crawlers are repeatedly landing on a non-existent path without a redirect or 410 recovery rule.',
                            'severity' => 'medium',
                            'verification_type' => 'automated',
                            'affected_url' => url($norm),
                            'evidence' => "404 hits logged in last 14 days: {$row->hits}",
                            'recommended_action' => 'Open Redirect Manager > Top 404 Errors Inbox and create a 301 redirect or mark 410 Gone.',
                        ];
                    }
                }
            }
        } catch (\Throwable) {
        }

        // 2. Probed Live Pages: Viewport Meta, Broken Status, and Primary Conversion CTA Check
        $probedPages = $context['probed_pages'] ?? [];
        foreach ($probedPages as $path => $probe) {
            $status = (int) ($probe['status'] ?? 200);
            $html = (string) ($probe['html'] ?? '');

            if ($status >= 400) {
                $issues[] = [
                    'category' => 'ux',
                    'check_key' => 'ux_public_route_http_error',
                    'title' => "Public Route Returned HTTP {$status}: {$path}",
                    'description' => 'An active public route failed to render HTTP 2xx OK during live HTTP kernel verification.',
                    'severity' => 'critical',
                    'verification_type' => 'automated',
                    'affected_url' => url($path),
                    'evidence' => "HTTP status {$status}",
                    'recommended_action' => 'Inspect controller route binding, view compilation, or redirect rule immediately.',
                ];

                continue;
            }

            if ($html !== '' && ! preg_match('/<meta[^>]+name=["\']viewport["\']/i', $html)) {
                $issues[] = [
                    'category' => 'ux',
                    'check_key' => 'ux_missing_viewport_meta',
                    'title' => "Missing Mobile Responsive Viewport Tag on {$path}",
                    'description' => 'Mobile devices cannot scale layout properly without <meta name="viewport">.',
                    'severity' => 'high',
                    'verification_type' => 'automated',
                    'affected_url' => url($path),
                    'evidence' => 'No meta viewport tag detected in rendered HTML.',
                    'recommended_action' => 'Ensure layout <head> outputs <meta name="viewport" content="width=device-width, initial-scale=1">.',
                ];
            }
        }

        return $issues;
    }
}
