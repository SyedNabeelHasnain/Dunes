<?php

namespace App\Console\Commands;

use App\Services\IndexNowService;
use Illuminate\Console\Command;

class SubmitIndexNowCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:indexnow {--all : Submit all catalog URLs} {--url=* : Specific URLs to submit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Instantly notify search engines (Bing, Yandex, Seznam, Naver) of updated content via IndexNow protocol';

    /**
     * Execute the console command.
     */
    public function handle(IndexNowService $indexNow): int
    {
        $this->info('Starting IndexNow submission...');

        if (!$indexNow->isEnabled()) {
            $this->warn('IndexNow is currently disabled in site settings.');
            if (!$this->confirm('Do you wish to proceed anyway?', false)) {
                return Command::SUCCESS;
            }
        }

        $specificUrls = $this->option('url');

        if (!empty($specificUrls)) {
            $result = $indexNow->submit($specificUrls);
        } else {
            $result = $indexNow->submitAll();
        }

        if ($result['success']) {
            $this->info("✓ {$result['message']}");
            $this->line("Submitted {$result['count']} URLs successfully with status {$result['status']}.");
            return Command::SUCCESS;
        }

        $this->error("✗ {$result['message']}");
        return Command::FAILURE;
    }
}
