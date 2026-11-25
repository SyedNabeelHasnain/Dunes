<?php

namespace App\Services\Seo;

use App\Models\RequestLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Analyzes crawler activity, bot categories, top crawled URLs, 404/parameter crawl waste,
 * and optional web-server access log tails using observed request data only (zero fabricated metrics).
 */
class CrawlerLogAnalyzerService
{
    public const CRAWLER_CATEGORIES = [
        'search_engines' => [
            'label' => 'Search Engine Crawlers',
            'tokens' => ['Googlebot', 'Bingbot', 'Applebot', 'YandexBot', 'DuckDuckBot', 'Baiduspider'],
        ],
        'ai_search' => [
            'label' => 'AI Search & Citation Bots',
            'tokens' => ['GPTBot', 'ChatGPT-User', 'OAI-SearchBot', 'ClaudeBot', 'PerplexityBot', 'Google-Extended'],
        ],
        'scrapers_seo' => [
            'label' => 'AI Scrapers & SEO Tools',
            'tokens' => ['CCBot', 'anthropic-ai', 'Bytespider', 'Amazonbot', 'AhrefsBot', 'SemrushBot', 'Diffbot'],
        ],
    ];

    /**
     * Build observed crawler activity & crawl-budget efficiency report for the last N days.
     */
    public function analyze(int $days = 30): array
    {
        $days = max(1, min(90, $days));
        $since = now()->subDays($days);

        $report = [
            'days' => $days,
            'has_data' => false,
            'total_bot_hits' => 0,
            'search_engine_hits' => 0,
            'ai_bot_hits' => 0,
            'scraper_hits' => 0,
            'wasted_404_bot_hits' => 0,
            'wasted_query_bot_hits' => 0,
            'crawl_waste_pct' => 0.0,
            'by_category' => [],
            'top_crawled_urls' => [],
            'top_404_bot_urls' => [],
            'anomalies' => [],
            'server_log_status' => $this->inspectConfiguredServerLog(),
        ];

        try {
            if (! class_exists(RequestLog::class) || ! Schema::hasTable('request_logs')) {
                return $report;
            }

            $allTokens = [];
            foreach (self::CRAWLER_CATEGORIES as $catKey => $catMeta) {
                $catTotal = 0;
                $bots = [];
                foreach ($catMeta['tokens'] as $token) {
                    $allTokens[] = $token;
                    $hits = RequestLog::where('user_agent', 'like', "%{$token}%")
                        ->where('request_timestamp', '>=', $since)
                        ->count();
                    $bots[$token] = $hits;
                    $catTotal += $hits;
                }
                $report['by_category'][$catKey] = [
                    'label' => $catMeta['label'],
                    'total' => $catTotal,
                    'bots' => $bots,
                ];
            }

            $report['search_engine_hits'] = $report['by_category']['search_engines']['total'] ?? 0;
            $report['ai_bot_hits'] = $report['by_category']['ai_search']['total'] ?? 0;
            $report['scraper_hits'] = $report['by_category']['scrapers_seo']['total'] ?? 0;

            $botScope = function ($query) use ($allTokens) {
                $query->where('bot_indicator', '!=', 'Likely Human')
                    ->orWhere(function ($q) use ($allTokens) {
                        foreach ($allTokens as $t) {
                            $q->orWhere('user_agent', 'like', "%{$t}%");
                        }
                    });
            };

            $totalBotHits = RequestLog::where('request_timestamp', '>=', $since)
                ->where($botScope)
                ->count();

            $wasted404 = RequestLog::where('request_timestamp', '>=', $since)
                ->where('entity_type', 'error_404')
                ->where($botScope)
                ->count();

            $wastedQuery = RequestLog::where('request_timestamp', '>=', $since)
                ->whereNotNull('query_string')
                ->where('query_string', '!=', '')
                ->where($botScope)
                ->count();

            $topCrawled = RequestLog::where('request_timestamp', '>=', $since)
                ->whereNotNull('request_uri')
                ->where($botScope)
                ->select('request_uri', DB::raw('COUNT(*) as hits'), DB::raw('MAX(request_timestamp) as last_hit'))
                ->groupBy('request_uri')
                ->orderByDesc('hits')
                ->limit(15)
                ->get()
                ->map(fn ($r) => [
                    'uri' => (string) $r->request_uri,
                    'hits' => (int) $r->hits,
                    'last_hit' => (string) $r->last_hit,
                ])
                ->all();

            $top404Bot = RequestLog::where('request_timestamp', '>=', $since)
                ->where('entity_type', 'error_404')
                ->whereNotNull('request_uri')
                ->where($botScope)
                ->select('request_uri', DB::raw('COUNT(*) as hits'), DB::raw('MAX(request_timestamp) as last_hit'))
                ->groupBy('request_uri')
                ->orderByDesc('hits')
                ->limit(10)
                ->get()
                ->map(fn ($r) => [
                    'uri' => (string) $r->request_uri,
                    'hits' => (int) $r->hits,
                    'last_hit' => (string) $r->last_hit,
                ])
                ->all();

            $wasteNumerator = $wasted404 + $wastedQuery;
            $wastePct = $totalBotHits > 0 ? round(min(100.0, ($wasteNumerator / $totalBotHits) * 100), 1) : 0.0;

            $anomalies = [];
            if ($totalBotHits >= 20 && $wasted404 > 0 && ($wasted404 / $totalBotHits) >= 0.15) {
                $anomalies[] = [
                    'severity' => 'high',
                    'message' => "High 404 crawler error rate: {$wasted404} of {$totalBotHits} bot requests hit missing URLs. Map top 404 paths in Redirect Manager.",
                ];
            }
            if ($totalBotHits >= 20 && $wastedQuery > 0 && ($wastedQuery / $totalBotHits) >= 0.25) {
                $anomalies[] = [
                    'severity' => 'medium',
                    'message' => "Elevated parameterized URL crawl volume ({$wastedQuery} bot hits with query strings). Verify canonical URLs and noindex rules.",
                ];
            }
            if ($report['scraper_hits'] > ($report['search_engine_hits'] * 3) && $report['scraper_hits'] >= 50) {
                $anomalies[] = [
                    'severity' => 'medium',
                    'message' => "Scraper bot volume ({$report['scraper_hits']}) significantly exceeds search engine crawls ({$report['search_engine_hits']}). Review robots.txt AI Scraper directives.",
                ];
            }

            $report['has_data'] = $totalBotHits > 0;
            $report['total_bot_hits'] = $totalBotHits;
            $report['wasted_404_bot_hits'] = $wasted404;
            $report['wasted_query_bot_hits'] = $wastedQuery;
            $report['crawl_waste_pct'] = $wastePct;
            $report['top_crawled_urls'] = $topCrawled;
            $report['top_404_bot_urls'] = $top404Bot;
            $report['anomalies'] = $anomalies;
        } catch (\Throwable) {
            // Fail safely with empty observed metrics
        }

        return $report;
    }

    /**
     * Safely inspect an optional server access log path (tail 64 KB only) if configured in env.
     */
    protected function inspectConfiguredServerLog(): array
    {
        $path = trim((string) config('services.seo.server_access_log_path', ''));
        if ($path === '') {
            return [
                'available' => false,
                'source' => 'application_request_logs',
                'note' => 'Analyzing Laravel request_logs table. Set SEO_SERVER_ACCESS_LOG_PATH in .env if raw LiteSpeed/Nginx access logs are readable on this host.',
            ];
        }

        if (! is_file($path) || ! is_readable($path)) {
            return [
                'available' => false,
                'source' => 'application_request_logs',
                'note' => 'Configured server access log path is not readable by the PHP worker; using Laravel request_logs.',
            ];
        }

        $size = (int) (@filesize($path) ?: 0);
        $statusCounts = ['2xx' => 0, '3xx' => 0, '4xx' => 0, '5xx' => 0];
        $handle = @fopen($path, 'rb');
        if ($handle) {
            try {
                if ($size > 65536) {
                    fseek($handle, $size - 65536);
                }
                $tail = (string) stream_get_contents($handle);
                foreach (preg_split('/\r\n|\r|\n/', $tail) ?: [] as $line) {
                    if (preg_match('/"\s([2345]\d{2})\s/', $line, $m)) {
                        $bucket = substr($m[1], 0, 1).'xx';
                        if (isset($statusCounts[$bucket])) {
                            $statusCounts[$bucket]++;
                        }
                    }
                }
            } finally {
                fclose($handle);
            }
        }

        return [
            'available' => true,
            'source' => 'server_access_log_tail',
            'size_mb' => round($size / 1048576, 2),
            'status_counts_tail' => $statusCounts,
            'note' => 'Parsed tail 64 KB of server access log.',
        ];
    }
}
