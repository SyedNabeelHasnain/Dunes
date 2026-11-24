<?php

namespace App\Services\Audit\Checkers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InfrastructureChecker implements AuditCheckerInterface
{
    public function run(array $context = []): array
    {
        $issues = [];

        // 1. Failed Queue Jobs Detection
        try {
            if (Schema::hasTable('failed_jobs')) {
                $failedCount = DB::table('failed_jobs')->count();
                if ($failedCount > 0) {
                    $issues[] = [
                        'category' => 'infrastructure',
                        'check_key' => 'infra_failed_queue_jobs',
                        'title' => "{$failedCount} Failed Background Queue Job(s) Detected",
                        'description' => 'Failed jobs can break asynchronous booking confirmation emails, review syncs, or Meta CAPI dispatches.',
                        'severity' => 'high',
                        'verification_type' => 'automated',
                        'affected_url' => route('admin.health.index'),
                        'evidence' => "failed_jobs table count: {$failedCount}",
                        'recommended_action' => 'Inspect System Health > Failed Jobs and trigger Queue Retry after resolving root cause.',
                    ];
                }
            }
        } catch (\Throwable) {
        }

        // 2. Scheduler Cron Heartbeat Verification
        if (app()->environment('production')) {
            $heartbeat = Cache::get('scheduler_last_heartbeat');
            if (! $heartbeat || \Carbon\Carbon::parse($heartbeat)->diffInMinutes(now()) > 15) {
                $issues[] = [
                    'category' => 'infrastructure',
                    'check_key' => 'infra_stale_cron_heartbeat',
                    'title' => 'Cron Scheduler Heartbeat Missing or Stale in Production',
                    'description' => 'Scheduled maintenance, sitemap refreshes, exchange rate updates, and recurring audits depend on cron execution.',
                    'severity' => 'medium',
                    'verification_type' => 'automated',
                    'affected_url' => route('admin.health.index'),
                    'evidence' => 'Last heartbeat: '.($heartbeat ?: 'Never recorded'),
                    'recommended_action' => 'Verify Hostinger cron invokes artisan schedule:run cleanly every minute.',
                ];
            }
        }

        // 3. Log File Bloat Check
        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath) && filesize($logPath) > 50 * 1024 * 1024) {
            $mb = round(filesize($logPath) / 1048576, 1);
            $issues[] = [
                'category' => 'infrastructure',
                'check_key' => 'infra_large_log_file',
                'title' => "Application Log Exceeds 50 MB ({$mb} MB)",
                'description' => 'Unrotated single log files degrade disk I/O and may obscure recent production errors.',
                'severity' => 'low',
                'verification_type' => 'automated',
                'affected_url' => route('admin.health.index'),
                'evidence' => "storage/logs/laravel.log size: {$mb} MB",
                'recommended_action' => 'Configure daily log rotation (LOG_CHANNEL=daily) and prune archived log files.',
            ];
        }

        // 4. Writable Storage Check
        if (! is_writable(storage_path('framework'))) {
            $issues[] = [
                'category' => 'infrastructure',
                'check_key' => 'infra_storage_unwritable',
                'title' => 'Framework Storage Directory Is Not Writable',
                'description' => 'Session, cache, and compiled view generation will fail if storage/framework lacks write permissions.',
                'severity' => 'critical',
                'verification_type' => 'automated',
                'affected_url' => url('/'),
                'evidence' => storage_path('framework').' failed is_writable().',
                'recommended_action' => 'Ensure PHP user owns storage/ and bootstrap/cache/ with 0775 permissions.',
            ];
        }

        return $issues;
    }
}
