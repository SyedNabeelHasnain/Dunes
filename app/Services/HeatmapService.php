<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\VisitorHeatmapClick;
use App\Models\VisitorHeatmapScroll;
use Illuminate\Support\Facades\DB;

class HeatmapService
{
    protected IpSecurityService $ipSecurity;

    public function __construct(IpSecurityService $ipSecurity)
    {
        $this->ipSecurity = $ipSecurity;
    }

    /**
     * Check if heatmaps are globally enabled.
     */
    public function isEnabled(): bool
    {
        try {
            $val = Setting::where('setting_key', 'heatmaps_enabled')->value('setting_value');
            return $val === null || $val === '1' || $val === 'true';
        } catch (\Throwable $e) {
            return true;
        }
    }

    /**
     * Record a click event for heatmaps.
     */
    public function recordClick(array $data, ?string $clientIp = null): bool
    {
        $ip = $clientIp ?? request()->ip();
        if ($this->ipSecurity->isIpBlocked($ip) || $this->ipSecurity->isIpIgnoredFromAnalytics($ip)) {
            return false;
        }

        $pagePath = $this->sanitizePath($data['page_path'] ?? '/');
        $xRatio = max(0, min(1, (float) ($data['x_ratio'] ?? 0)));
        $yRatio = max(0, min(1, (float) ($data['y_ratio'] ?? 0)));

        VisitorHeatmapClick::create([
            'session_id' => ! empty($data['session_id']) ? substr((string) $data['session_id'], 0, 64) : null,
            'page_path' => $pagePath,
            'device_type' => in_array($data['device_type'] ?? '', ['desktop', 'mobile', 'tablet']) ? $data['device_type'] : 'desktop',
            'x_ratio' => $xRatio,
            'y_ratio' => $yRatio,
            'click_x' => (int) ($data['click_x'] ?? 0),
            'click_y' => (int) ($data['click_y'] ?? 0),
            'viewport_width' => (int) ($data['viewport_width'] ?? 0),
            'viewport_height' => (int) ($data['viewport_height'] ?? 0),
            'target_tag' => ! empty($data['target_tag']) ? substr(trim($data['target_tag']), 0, 50) : null,
            'target_text' => ! empty($data['target_text']) ? substr(trim($data['target_text']), 0, 100) : null,
            'created_at' => now(),
        ]);

        return true;
    }

    /**
     * Record max scroll depth for heatmaps.
     */
    public function recordScroll(array $data, ?string $clientIp = null): bool
    {
        $ip = $clientIp ?? request()->ip();
        if ($this->ipSecurity->isIpBlocked($ip) || $this->ipSecurity->isIpIgnoredFromAnalytics($ip)) {
            return false;
        }

        $pagePath = $this->sanitizePath($data['page_path'] ?? '/');
        $maxPercent = max(0, min(100, (int) ($data['max_scroll_percent'] ?? 0)));

        VisitorHeatmapScroll::create([
            'session_id' => ! empty($data['session_id']) ? substr((string) $data['session_id'], 0, 64) : null,
            'page_path' => $pagePath,
            'device_type' => in_array($data['device_type'] ?? '', ['desktop', 'mobile', 'tablet']) ? $data['device_type'] : 'desktop',
            'max_scroll_percent' => $maxPercent,
            'viewport_height' => (int) ($data['viewport_height'] ?? 0),
            'page_height' => (int) ($data['page_height'] ?? 0),
            'created_at' => now(),
        ]);

        return true;
    }

    /**
     * Retrieve aggregated heatmap data (clicks & scroll depth) for a page.
     */
    public function getHeatmapData(string $pagePath, string $deviceType = 'all', int $days = 30): array
    {
        $path = $this->sanitizePath($pagePath);
        $cutoff = now()->subDays(max(1, $days));

        // Click query
        $clickQuery = VisitorHeatmapClick::where('page_path', $path)
            ->where('created_at', '>=', $cutoff);

        if ($deviceType !== 'all') {
            $clickQuery->where('device_type', $deviceType);
        }

        $totalClicks = (clone $clickQuery)->count();

        // Sample up to 1000 clicks for responsive canvas rendering
        $clicks = (clone $clickQuery)
            ->select('x_ratio', 'y_ratio', 'click_x', 'click_y', 'viewport_width', 'viewport_height')
            ->orderBy('id', 'desc')
            ->limit(1000)
            ->get();

        // Top clicked elements
        $topElements = (clone $clickQuery)
            ->whereNotNull('target_tag')
            ->select('target_tag', 'target_text', DB::raw('count(*) as click_count'))
            ->groupBy('target_tag', 'target_text')
            ->orderBy('click_count', 'desc')
            ->limit(5)
            ->get();

        // Scroll query
        $scrollQuery = VisitorHeatmapScroll::where('page_path', $path)
            ->where('created_at', '>=', $cutoff);

        if ($deviceType !== 'all') {
            $scrollQuery->where('device_type', $deviceType);
        }

        $totalScrollRecords = (clone $scrollQuery)->count();
        $avgScrollPercent = $totalScrollRecords > 0 ? (int) round((clone $scrollQuery)->avg('max_scroll_percent')) : 0;

        // Reach brackets
        $reach25 = $totalScrollRecords > 0 ? round(((clone $scrollQuery)->where('max_scroll_percent', '>=', 25)->count() / $totalScrollRecords) * 100) : 0;
        $reach50 = $totalScrollRecords > 0 ? round(((clone $scrollQuery)->where('max_scroll_percent', '>=', 50)->count() / $totalScrollRecords) * 100) : 0;
        $reach75 = $totalScrollRecords > 0 ? round(((clone $scrollQuery)->where('max_scroll_percent', '>=', 75)->count() / $totalScrollRecords) * 100) : 0;
        $reach100 = $totalScrollRecords > 0 ? round(((clone $scrollQuery)->where('max_scroll_percent', '>=', 95)->count() / $totalScrollRecords) * 100) : 0;

        return [
            'page_path' => $path,
            'device_type' => $deviceType,
            'days' => $days,
            'total_clicks' => $totalClicks,
            'clicks' => $clicks,
            'top_elements' => $topElements,
            'scroll' => [
                'total_records' => $totalScrollRecords,
                'avg_scroll_percent' => $avgScrollPercent,
                'reach_25' => $reach25,
                'reach_50' => $reach50,
                'reach_75' => $reach75,
                'reach_100' => $reach100,
            ],
        ];
    }

    /**
     * Get list of top pages with heatmap data.
     */
    public function getAvailablePages(): array
    {
        $clickPages = VisitorHeatmapClick::select('page_path', DB::raw('count(*) as count'))
            ->groupBy('page_path')
            ->orderBy('count', 'desc')
            ->limit(30)
            ->get();

        if ($clickPages->isEmpty()) {
            return [
                ['page_path' => '/', 'count' => 0],
                ['page_path' => '/tours', 'count' => 0],
                ['page_path' => '/contact', 'count' => 0],
                ['page_path' => '/about-us', 'count' => 0],
            ];
        }

        return $clickPages->toArray();
    }

    /**
     * Purge heatmaps older than specified days.
     */
    public function purgeOldHeatmaps(?int $days = null): array
    {
        $retentionDays = $days ?? 30;
        $cutoff = now()->subDays($retentionDays);

        $deletedClicks = VisitorHeatmapClick::where('created_at', '<', $cutoff)->delete();
        $deletedScrolls = VisitorHeatmapScroll::where('created_at', '<', $cutoff)->delete();

        return [
            'deleted_clicks' => $deletedClicks,
            'deleted_scrolls' => $deletedScrolls,
            'retention_days' => $retentionDays,
        ];
    }

    /**
     * Standardize page path without query parameters or trailing slashes.
     */
    protected function sanitizePath(string $raw): string
    {
        $urlParts = parse_url(trim($raw));
        $path = $urlParts['path'] ?? '/';
        $path = '/' . ltrim($path, '/');
        if (strlen($path) > 1) {
            $path = rtrim($path, '/');
        }

        return substr($path, 0, 255);
    }
}
