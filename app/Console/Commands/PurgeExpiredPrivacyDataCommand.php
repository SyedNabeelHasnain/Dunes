<?php

namespace App\Console\Commands;

use App\Models\EmailOtp;
use App\Models\PrivacyConsent;
use App\Models\Setting;
use App\Models\SpamEvent;
use App\Models\WebVitalsMetric;
use App\Services\Governance\ConfigAuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class PurgeExpiredPrivacyDataCommand extends Command
{
    protected $signature = 'privacy:purge-expired {--dry-run : Report how many expired records would be pruned without deleting}';

    protected $description = 'Enforce CMS privacy_log_retention_days policy by pruning expired consent logs, RUM telemetry, expired OTPs, and aged spam logs.';

    public function handle(ConfigAuditService $audit): int
    {
        $configuredDays = (int) Setting::getVal('privacy_log_retention_days', '365');
        $retentionDays = max(30, min(1825, $configuredDays ?: 365));
        $consentCutoff = now()->subDays($retentionDays);
        $vitalsCutoff = now()->subDays(90);
        $otpCutoff = now()->subHours(24);
        $spamCutoff = now()->subDays(90);
        $dryRun = (bool) $this->option('dry-run');

        $consentsDeleted = 0;
        $vitalsDeleted = 0;
        $otpsDeleted = 0;
        $spamDeleted = 0;

        if (Schema::hasTable('privacy_consents')) {
            if ($dryRun) {
                $consentsDeleted = PrivacyConsent::where('consented_at', '<', $consentCutoff)->count();
            } else {
                do {
                    $batch = PrivacyConsent::where('consented_at', '<', $consentCutoff)->limit(2000)->delete();
                    $consentsDeleted += $batch;
                } while ($batch > 0);
            }
        }

        if (Schema::hasTable('web_vitals_metrics')) {
            if ($dryRun) {
                $vitalsDeleted = WebVitalsMetric::where('recorded_at', '<', $vitalsCutoff)->count();
            } else {
                do {
                    $batch = WebVitalsMetric::where('recorded_at', '<', $vitalsCutoff)->limit(2000)->delete();
                    $vitalsDeleted += $batch;
                } while ($batch > 0);
            }
        }

        if (Schema::hasTable('email_otps')) {
            if ($dryRun) {
                $otpsDeleted = EmailOtp::where('expires_at', '<', $otpCutoff)->count();
            } else {
                do {
                    $batch = EmailOtp::where('expires_at', '<', $otpCutoff)->limit(2000)->delete();
                    $otpsDeleted += $batch;
                } while ($batch > 0);
            }
        }

        if (Schema::hasTable('spam_events')) {
            if ($dryRun) {
                $spamDeleted = SpamEvent::where('created_at', '<', $spamCutoff)->count();
            } else {
                do {
                    $batch = SpamEvent::where('created_at', '<', $spamCutoff)->limit(2000)->delete();
                    $spamDeleted += $batch;
                } while ($batch > 0);
            }
        }

        if (! $dryRun && ($consentsDeleted > 0 || $vitalsDeleted > 0 || $otpsDeleted > 0 || $spamDeleted > 0)) {
            $audit->log(
                section: 'privacy',
                action: 'retention_purge',
                settingKey: 'privacy_log_retention_days',
                oldValue: ['retention_days' => $retentionDays],
                newValue: [
                    'consents_deleted' => $consentsDeleted,
                    'vitals_deleted' => $vitalsDeleted,
                    'otps_deleted' => $otpsDeleted,
                    'spam_events_deleted' => $spamDeleted,
                ],
                summary: "Purged {$consentsDeleted} expired consent logs (>{$retentionDays}d), {$vitalsDeleted} aged RUM samples (>90d), {$otpsDeleted} expired OTPs (>24h), and {$spamDeleted} aged spam events (>90d)",
            );
        }

        $prefix = $dryRun ? '[DRY RUN] Would purge' : 'Purged';
        $this->info("{$prefix} {$consentsDeleted} consent records older than {$retentionDays} days, {$vitalsDeleted} RUM vitals older than 90 days, {$otpsDeleted} expired OTPs, and {$spamDeleted} spam events.");

        return self::SUCCESS;
    }
}
