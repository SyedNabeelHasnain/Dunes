<?php

namespace App\Console\Commands;

use App\Services\TripAdvisorReviewSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncTripAdvisorReviewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reviews:sync-tripadvisor {--force : Bypass the 60-second rate-limit cooldown}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize latest customer reviews and ratings from TripAdvisor Content API.';

    /**
     * Execute the console command.
     */
    public function handle(TripAdvisorReviewSyncService $syncService): int
    {
        $this->info('Starting automated TripAdvisor Reviews synchronization...');

        $force = (bool) $this->option('force');
        $result = $syncService->sync($force);

        if (! ($result['success'] ?? false)) {
            $msg = $result['message'] ?? 'Failed to synchronize TripAdvisor reviews.';
            $this->error($msg);
            Log::warning("Artisan command [reviews:sync-tripadvisor] ended with failure: {$msg}");

            return Command::FAILURE;
        }

        $this->info($result['message'] ?? 'TripAdvisor reviews synchronized successfully.');

        $this->table(
            ['Metric', 'Value'],
            [
                ['New Reviews Added', $result['added'] ?? 0],
                ['Existing Reviews Updated', $result['updated'] ?? 0],
                ['Reviews Skipped', $result['skipped'] ?? 0],
                ['Total TripAdvisor Reviews on File', $result['total'] ?? 0],
                ['TripAdvisor Rating', ($result['rating'] ?? 'N/A') . ' ★'],
                ['Total Reviews Count', $result['reviews_count'] ?? 'N/A'],
                ['Last Synced Timestamp', $result['last_synced_at'] ?? 'N/A'],
            ]
        );

        return Command::SUCCESS;
    }
}
