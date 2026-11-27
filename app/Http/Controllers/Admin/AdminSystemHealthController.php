<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\RumVitalsController;
use App\Models\RequestLog;
use App\Models\SyntheticTestRun;
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
        $hasVitalsTable = false;
        try {
            $hasVitalsTable = Schema::hasTable('web_vitals_metrics');
        } catch (\Throwable) {
        }

        foreach (RumVitalsController::BUDGETS as $metric => $bounds) {
            $values = [];
            if ($hasVitalsTable) {
                try {
                    $values = WebVitalsMetric::where('metric_name', $metric)
                        ->where('recorded_at', '>=', now()->subDays(14))
                        ->orderBy('metric_value')
                        ->pluck('metric_value')
                        ->all();
                } catch (\Throwable) {
                    $values = [];
                }
            }

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

        $poorPages = collect();
        if ($hasVitalsTable) {
            try {
                $poorPages = WebVitalsMetric::where('rating', 'poor')
                    ->where('recorded_at', '>=', now()->subDays(14))
                    ->select('page_path', 'metric_name', DB::raw('COUNT(*) as poor_samples'), DB::raw('AVG(metric_value) as avg_value'))
                    ->groupBy('page_path', 'metric_name')
                    ->orderByDesc('poor_samples')
                    ->limit(15)
                    ->get();
            } catch (\Throwable) {
                $poorPages = collect();
            }
        }

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

        $lastHeartbeat = null;
        $cronActive = false;
        try {
            $lastHeartbeat = Cache::get('scheduler_last_heartbeat');
            if ($lastHeartbeat) {
                $cronActive = \Carbon\Carbon::parse((string) $lastHeartbeat)->diffInMinutes(now()) <= 5;
            }
        } catch (\Throwable) {
            $cronActive = false;
        }

        $error404Count = 0;
        try {
            if (class_exists(RequestLog::class) && Schema::hasTable('request_logs')) {
                $error404Count = RequestLog::where('entity_type', 'error_404')
                    ->where('request_timestamp', '>=', now()->subDays(7))
                    ->count();
            }
        } catch (\Throwable) {
        }

        $logPath = storage_path('logs/laravel.log');
        $logSizeMb = 0;
        $recentLogErrors = [];
        try {
            if (is_file($logPath) && is_readable($logPath)) {
                $size = @filesize($logPath) ?: 0;
                $logSizeMb = round($size / 1048576, 2);
                $recentLogErrors = $this->readRecentLogErrors($logPath, $size);
            }
        } catch (\Throwable) {
        }

        $diskFreeGb = null;
        try {
            if (function_exists('disk_free_space')) {
                $free = @disk_free_space(base_path());
                if (is_numeric($free) && $free > 0) {
                    $diskFreeGb = round(((float) $free) / 1073741824, 2);
                }
            }
        } catch (\Throwable) {
        }

        $smtpHealth = [
            'status' => 'unknown',
            'context' => null,
            'checked_at' => null,
            'diagnosis' => null,
        ];
        $spamTelemetry = ['blocked_24h' => 0, 'blocked_7d' => 0, 'auto_ip_blocked_7d' => 0, 'quarantined_7d' => 0];
        $quarantinedInquiries = 0;

        try {
            $smtpHealth = app(\App\Services\Mail\SmtpDiagnosticService::class)->getHealthStatus();
            $spamTelemetry = app(\App\Services\Security\ContactSpamGuardService::class)->getTelemetrySummary();
            if (Schema::hasTable('contacts') && Schema::hasColumn('contacts', 'is_spam')) {
                $quarantinedInquiries = \App\Models\Contact::quarantined()->count();
            }
        } catch (\Throwable) {
        }

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
            'quarantined_inquiries' => $quarantinedInquiries,
        ];

        $latestSyntheticRun = null;
        try {
            if (Schema::hasTable('synthetic_test_runs')) {
                $latestSyntheticRun = SyntheticTestRun::latest('id')->first();
            }
        } catch (\Throwable) {
        }

        return view('admin.health.index', compact('vitalsSummary', 'poorPages', 'recentFailedJobs', 'recentLogErrors', 'system', 'smtpHealth', 'spamTelemetry', 'latestSyntheticRun'));
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

    /**
     * Read only the tail 64 KB of laravel.log in constant O(1) memory so multi-megabyte log files never OOM.
     *
     * @return list<string>
     */
    private function readRecentLogErrors(string $logPath, int $fileSize): array
    {
        if ($fileSize <= 0) {
            return [];
        }

        $handle = @fopen($logPath, 'rb');
        if (! $handle) {
            return [];
        }

        try {
            $maxBytes = 65536;
            $offset = max(0, $fileSize - $maxBytes);
            if ($offset > 0) {
                fseek($handle, $offset);
            }
            $chunk = stream_get_contents($handle) ?: '';
        } finally {
            fclose($handle);
        }

        if ($chunk === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $chunk) ?: [];
        $errors = [];
        foreach (array_reverse($lines) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (str_contains($line, '.ERROR:') || str_contains($line, '.CRITICAL:')) {
                $errors[] = function_exists('mb_strcut') ? mb_strcut($line, 0, 260, 'UTF-8') : substr($line, 0, 260);
                if (count($errors) >= 10) {
                    break;
                }
            }
        }

        return $errors;
    }
}
