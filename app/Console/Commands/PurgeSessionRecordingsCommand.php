<?php

namespace App\Console\Commands;

use App\Services\HeatmapService;
use App\Services\SessionRecordingService;
use Illuminate\Console\Command;

class PurgeSessionRecordingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'telemetry:purge-recordings
                            {--days= : Explicit retention cutoff days (defaults to setting)}
                            {--all : Purge all recordings (keeps starred by default)}
                            {--include-starred : Also purge starred recordings}
                            {--heatmaps-only : Only purge old heatmap logs}';

    /**
     * The console command description.
     */
    protected $description = 'Prune old session recordings and heatmaps according to retention policy to preserve disk space';

    public function handle(SessionRecordingService $recordingService, HeatmapService $heatmapService): int
    {
        $this->info('Starting telemetry and session recording retention purge...');

        $days = $this->option('days') ? (int) $this->option('days') : null;

        if (! $this->option('heatmaps-only')) {
            if ($this->option('all')) {
                $keepStarred = ! $this->option('include-starred');
                $res = $recordingService->purgeAllRecordings($keepStarred);
                $this->info("Purged {$res['deleted_count']} recording(s) (" . round($res['freed_bytes'] / 1024, 1) . ' KB freed).');
            } else {
                $res = $recordingService->purgeOldRecordings($days);
                $this->info("Purged {$res['deleted_count']} recording(s) older than {$res['retention_days']} days (" . round($res['freed_bytes'] / 1024, 1) . ' KB freed).');
            }
        }

        // Prune heatmaps
        $hmDays = $days ?? 30;
        $hmRes = $heatmapService->purgeOldHeatmaps($hmDays);
        $this->info("Purged {$hmRes['deleted_clicks']} heatmap clicks and {$hmRes['deleted_scrolls']} scrolls older than {$hmDays} days.");

        $this->info('Retention purge completed successfully.');

        return Command::SUCCESS;
    }
}
