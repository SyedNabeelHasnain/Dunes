<?php

namespace App\Console\Commands;

use App\Services\GoogleReviewSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncGoogleReviewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reviews:sync-google {--force : Bypass the 60-second rate-limit cooldown}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize latest customer reviews and ratings from Google Places API.';

    /**
     * Execute the console command.
     */
    public function handle(GoogleReviewSyncService $syncService): int
    {
        $this->info('Starting automated Google Reviews synchronization...');

        $force = (bool) $this->option('force');
        $result = $syncService->sync($force);

        if (! ($result['success'] ?? false)) {
            $msg = $result['message'] ?? 'Failed to synchronize Google reviews.';
            $this->error($msg);
            Log::warning("Artisan command [reviews:sync-google] ended with failure: {$msg}");

            return Command::FAILURE;
        }

        $this->info($result['message'] ?? 'Google reviews synchronized successfully.');

        $this->table(
            ['Metric', 'Value'],
            [
                ['New Reviews Added', $result['added'] ?? 0],
                ['Existing Reviews Updated', $result['updated'] ?? 0],
                ['Reviews Skipped', $result['skipped'] ?? 0],
                ['Total Google Reviews on File', $result['total'] ?? 0],
                ['Google Place Rating', ($result['place_rating'] ?? 'N/A') . ' ★'],
                ['Total User Ratings Count', $result['user_ratings_total'] ?? 'N/A'],
                ['Last Synced Timestamp', $result['last_synced_at'] ?? 'N/A'],
            ]
        );

        return Command::SUCCESS;
    }
}
