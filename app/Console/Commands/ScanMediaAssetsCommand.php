<?php

namespace App\Console\Commands;

use App\Services\MediaManagerService;
use Illuminate\Console\Command;

class ScanMediaAssetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'media:scan-assets {--refresh-usages : Only refresh usage counts without directory re-indexing}';

    /**
     * The console command description.
     */
    protected $description = 'Scan public images and videos directories and synchronize media library assets';

    /**
     * Execute the console command.
     */
    public function handle(MediaManagerService $service): int
    {
        $this->info('Starting Dunes Discovery media asset synchronization...');

        if ($this->option('refresh-usages')) {
            $this->comment('Refreshing cross-referenced usages for all media assets...');
            $count = $service->refreshAllUsages();
            $this->info("Successfully refreshed usages for {$count} media assets.");

            return self::SUCCESS;
        }

        $stats = $service->scanAndSyncExistingMedia();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Files Scanned', $stats['scanned']],
                ['New Assets Indexed', $stats['indexed']],
                ['Existing Assets Updated', $stats['updated']],
                ['Files Skipped (Non-Media)', $stats['skipped']],
                ['Assets In-Use (Tours/Blogs/Reviews)', $stats['in_use']],
            ]
        );

        $this->info('Media library synchronization completed successfully.');

        return self::SUCCESS;
    }
}
