<?php

namespace App\Console\Commands;

use App\Services\ReviewMediaGalleryService;
use Illuminate\Console\Command;

class VerifyGalleryMediaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gallery:verify-media {--force : Re-verify all links even if previously cached}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan all customer review media links, verify accessibility, and purge broken media from the gallery.';

    /**
     * Execute the console command.
     */
    public function handle(ReviewMediaGalleryService $galleryService): int
    {
        $this->info('Scanning customer review media gallery for health and accessibility...');

        $force = (bool) $this->option('force');
        $rawItems = $galleryService->extractAllReviewMedia();
        $totalCount = count($rawItems);

        $this->info("Found {$totalCount} customer review media items. Verifying links...");

        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        $healthyCount = 0;
        $brokenCount = 0;

        foreach ($rawItems as $item) {
            $isHealthy = $galleryService->isUrlHealthy($item['url'], $force);
            if ($isHealthy) {
                $healthyCount++;
            } else {
                $brokenCount++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Pre-warm the cache with verified items
        $galleryService->clearCache();
        $verified = $galleryService->getGalleryItems(['force_verify' => false]);
        $stats = $galleryService->getCategoryStats($verified);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Media Scanned', $totalCount],
                ['Verified Healthy (Served to Frontend)', count($verified)],
                ['Broken / Inaccessible (Excluded)', $brokenCount],
                ['Red Dunes & Safari Moments', $stats['red_dunes'] ?? 0],
                ['Buggy & Quad Bike Action', $stats['buggy_quad'] ?? 0],
                ['Desert Camp & BBQ Entertainment', $stats['camp_bbq'] ?? 0],
                ['Sunset & Camel Treks', $stats['sunset_camels'] ?? 0],
                ['Video Clips', $stats['videos'] ?? 0],
            ]
        );

        $this->info('Gallery cache successfully primed and verified.');
        return Command::SUCCESS;
    }
}
