<?php

namespace App\Console\Commands;

use App\Models\EmailCampaign;
use App\Services\EmailMarketingService;
use App\Services\SettingsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendScheduledCampaignsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:send-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find and broadcast email marketing campaigns scheduled for dispatch.';

    /**
     * Execute the console command.
     */
    public function handle(EmailMarketingService $emailService, SettingsService $settings): int
    {
        $now = now();
        $this->info("Scanning for scheduled email campaigns at {$now->toDateTimeString()}...");

        $campaigns = EmailCampaign::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $now)
            ->get();

        if ($campaigns->isEmpty()) {
            $this->info('No pending scheduled campaigns to dispatch.');

            return Command::SUCCESS;
        }

        $batchSize = (int) $settings->get('newsletter_batch_size', '50');
        $batchDelay = (int) $settings->get('newsletter_batch_delay', '1');

        $dispatched = 0;
        foreach ($campaigns as $campaign) {
            $this->info("Dispatching scheduled campaign #{$campaign->id} ('{$campaign->title}')...");

            try {
                $res = $emailService->dispatchCampaign($campaign, $batchSize, $batchDelay);
                $dispatched++;
                $this->info("Campaign #{$campaign->id} dispatched successfully to {$res['sent']} recipient(s).");
            } catch (\Throwable $e) {
                Log::error("Failed to dispatch scheduled campaign #{$campaign->id}: ".$e->getMessage());
                $this->error("Error dispatching campaign #{$campaign->id}: ".$e->getMessage());
            }
        }

        $this->info("Finished processing. Total scheduled campaigns dispatched: {$dispatched}.");

        return Command::SUCCESS;
    }
}
