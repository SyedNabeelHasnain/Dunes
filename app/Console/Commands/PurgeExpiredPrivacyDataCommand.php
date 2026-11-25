<?php

namespace App\Console\Commands;

use App\Models\PrivacyConsent;
use App\Models\Setting;
use App\Models\WebVitalsMetric;
use App\Services\Governance\ConfigAuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class PurgeExpiredPrivacyDataCommand extends Command
{
    protected $signature = 'privacy:purge-expired {--dry-run : Report how many expired records would be pruned without deleting}';

    protected $description = 'Enforce CMS privacy_log_retention_days policy by pruning expired consent logs and aged RUM telemetry.';

    public function handle(ConfigAuditService $audit): int
    {
        $configuredDays = (int) Setting::getVal('privacy_log_retention_days', '365');
        $retentionDays = max(30, min(1825, $configuredDays ?: 365));
        $consentCutoff = now()->subDays($retentionDays);
        $vitalsCutoff = now()->subDays(90);
        $dryRun = (bool) $this->option('dry-run');

        $consentsDeleted = 0;
        $vitalsDeleted = 0;

        if (Schema::hasTable('privacy_consents')) {
            $query = PrivacyConsent::where('consented_at', '<', $consentCutoff);
            $consentsDeleted = $dryRun ? $query->count() : $query->delete();
        }

        if (Schema::hasTable('web_vitals_metrics')) {
            $query = WebVitalsMetric::where('recorded_at', '<', $vitalsCutoff);
            $vitalsDeleted = $dryRun ? $query->count() : $query->delete();
        }

        if (! $dryRun && ($consentsDeleted > 0 || $vitalsDeleted > 0)) {
            $audit->log(
                section: 'privacy',
                action: 'retention_purge',
                settingKey: 'privacy_log_retention_days',
                oldValue: ['retention_days' => $retentionDays],
                newValue: ['consents_deleted' => $consentsDeleted, 'vitals_deleted' => $vitalsDeleted],
                summary: "Purged {$consentsDeleted} expired consent logs (>{$retentionDays}d) and {$vitalsDeleted} aged RUM samples (>90d)",
            );
        }

        $prefix = $dryRun ? '[DRY RUN] Would purge' : 'Purged';
        $this->info("{$prefix} {$consentsDeleted} consent records older than {$retentionDays} days and {$vitalsDeleted} RUM vitals older than 90 days.");

        return self::SUCCESS;
    }
}
