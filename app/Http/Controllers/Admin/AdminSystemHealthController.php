<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\RumVitalsController;
use App\Models\RequestLog;
use App\Models\WebVitalsMetric;
use App\Services\Governance\ConfigAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminSystemHealthController extends Controller
{
    public function index(): View
    {
        $vitalsSummary = [];
        foreach (RumVitalsController::BUDGETS as $metric => $bounds) {
            $values = WebVitalsMetric::where('metric_name', $metric)
                ->where('recorded_at', '>=', now()->subDays(14))
                ->orderBy('metric_value')
                ->pluck('metric_value')
                ->all();

            $count = count($values);
            $p75 = null;
            if ($count > 0) {
                $idx = (int) floor(0.75 * ($count - 1));
                $p75 = round((float) $values[$idx], $metric === 'CLS' ? 3 : 1);
            }

            $status = 'no_data';
            if ($p75 !== null) {
                $status = $p75 <= $bounds['good'] ? 'good' : ($p75 <= $bounds['poor'] ? 'needs-improvement' : 'poor');
            }

            $vitalsSummary[$metric] = [
                'p75' => $p75,
                'samples' => $count,
                'good_budget' => $bounds['good'],
                'poor_threshold' => $bounds['poor'],
                'status' => $status,
            ];
        }

        $poorPages = WebVitalsMetric::where('rating', 'poor')
            ->where('recorded_at', '>=', now()->subDays(14))
            ->select('page_path', 'metric_name', DB::raw('COUNT(*) as poor_samples'), DB::raw('AVG(metric_value) as avg_value'))
            ->groupBy('page_path', 'metric_name')
            ->orderByDesc('poor_samples')
            ->limit(15)
            ->get();

        $pendingJobs = 0;
        $failedJobsCount = 0;
        $recentFailedJobs = collect();

        try {
            if (Schema::hasTable('jobs')) {
                $pendingJobs = DB::table('jobs')->count();
            }
            if (Schema::hasTable('failed_jobs')) {
                $failedJobsCount = DB::table('failed_jobs')->count();
                $recentFailedJobs = DB::table('failed_jobs')->orderByDesc('failed_at')->limit(10)->get();
            }
        } catch (\Throwable) {
        }

        $lastHeartbeat = Cache::get('scheduler_last_heartbeat');
        $cronActive = $lastHeartbeat && \Carbon\Carbon::parse($lastHeartbeat)->diffInMinutes(now()) <= 5;

        $error404Count = 0;
        try {
            if (class_exists(RequestLog::class) && Schema::hasTable('request_logs')) {
                $error404Count = RequestLog::where('action_type', 'error_404')
                    ->where('request_timestamp', '>=', now()->subDays(7))
                    ->count();
            }
        } catch (\Throwable) {
        }

        $logPath = storage_path('logs/laravel.log');
        $logSizeMb = file_exists($logPath) ? round(filesize($logPath) / 1048576, 2) : 0;
        $recentLogErrors = [];
        if (file_exists($logPath) && is_readable($logPath)) {
            $lines = @file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            $tail = array_slice($lines, -120);
            foreach (array_reverse($tail) as $line) {
                if (str_contains($line, '.ERROR:') || str_contains($line, '.CRITICAL:')) {
                    $recentLogErrors[] = substr($line, 0, 260);
                    if (count($recentLogErrors) >= 10) {
                        break;
                    }
                }
            }
        }

        $diskFreeGb = @disk_free_space(base_path()) ? round(@disk_free_space(base_path()) / 1073741824, 2) : null;

        $system = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'env' => app()->environment(),
            'debug_enabled' => (bool) config('app.debug'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'session_driver' => config('session.driver'),
            'session_encrypted' => (bool) config('session.encrypt'),
            'pending_jobs' => $pendingJobs,
            'failed_jobs' => $failedJobsCount,
            'cron_active' => $cronActive,
            'last_heartbeat' => $lastHeartbeat,
            'errors_404_7d' => $error404Count,
            'log_size_mb' => $logSizeMb,
            'disk_free_gb' => $diskFreeGb,
        ];

        return view('admin.health.index', compact('vitalsSummary', 'poorPages', 'recentFailedJobs', 'recentLogErrors', 'system'));
    }

    public function retryFailedJobs(ConfigAuditService $audit): RedirectResponse
    {
        try {
            Artisan::call('queue:retry', ['id' => ['all']]);
            $audit->log(
                section: 'infrastructure',
                action: 'updated',
                settingKey: 'failed_jobs_retry',
                summary: 'Retried all failed queue jobs from System Health Dashboard',
            );

            return redirect()->route('admin.health.index')->with('success', 'Queued all failed jobs for retry.');
        } catch (\Throwable $e) {
            return redirect()->route('admin.health.index')->withErrors(['queue' => $e->getMessage()]);
        }
    }
}
