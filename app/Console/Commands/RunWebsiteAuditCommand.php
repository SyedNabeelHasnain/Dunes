<?php

namespace App\Console\Commands;

use App\Services\Audit\WebsiteAuditService;
use Illuminate\Console\Command;

class RunWebsiteAuditCommand extends Command
{
    protected $signature = 'audit:run {--type=full : full or incremental} {--url= : Optional specific relative path to audit}';

    protected $description = 'Execute the centralized Dunes Discovery Website Optimization & Audit Engine.';

    public function handle(WebsiteAuditService $audit): int
    {
        $type = (string) $this->option('type');
        $targetUrl = $this->option('url');

        if ($targetUrl) {
            $result = $audit->auditSingleUrl((string) $targetUrl, true);
            $this->info("Single-URL Audit for {$result['url']}: Health Score {$result['health_score']}/100 ({$result['issues_count']} issues)");

            return self::SUCCESS;
        }

        $run = $audit->runAudit(in_array($type, ['full', 'incremental'], true) ? $type : 'full');
        $this->info("Audit Run #{$run->id} ({$run->scan_type}) completed: Score {$run->health_score}/100, Total Issues: {$run->total_issues_count} ({$run->duration_ms}ms)");

        return self::SUCCESS;
    }
}
