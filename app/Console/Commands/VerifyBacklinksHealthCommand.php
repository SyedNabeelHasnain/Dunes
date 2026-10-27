<?php

namespace App\Console\Commands;

use App\Models\Backlink;
use Illuminate\Console\Command;

class VerifyBacklinksHealthCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'seo:verify-backlinks 
                            {--id= : Verify specific backlink ID} 
                            {--domain= : Filter verification by referring domain} 
                            {--limit=50 : Maximum number of backlinks to crawl per execution} 
                            {--force : Force verification even if checked recently}';

    /**
     * The console command description.
     */
    protected $description = 'Crawl and verify the live health, HTTP status, anchor text, and link equity of registered backlinks';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $id = $this->option('id');
        $domain = $this->option('domain');
        $force = $this->option('force');

        $query = Backlink::query();

        if ($id) {
            $query->where('id', $id);
        }

        if ($domain) {
            $query->where('referring_domain', 'like', "%{$domain}%");
        }

        if (! $force && ! $id) {
            // Verify links that haven't been checked in the last 24 hours, or are broken/pending
            $query->where(function ($q) {
                $q->whereNull('last_checked_at')
                    ->orWhere('last_checked_at', '<', now()->subHours(24))
                    ->orWhere('status', '!=', 'active');
            });
        }

        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $backlinks = $query->get();

        if ($backlinks->isEmpty()) {
            $this->info('No backlinks require verification at this time.');
            return Command::SUCCESS;
        }

        $this->info("Starting verification for {$backlinks->count()} backlink(s)...");

        $rows = [];
        $activeCount = 0;
        $brokenCount = 0;

        $progressBar = $this->output->createProgressBar($backlinks->count());
        $progressBar->start();

        foreach ($backlinks as $backlink) {
            $result = $backlink->verifyHealth();

            if ($result['status'] === 'active') {
                $activeCount++;
            } else {
                $brokenCount++;
            }

            $rows[] = [
                $backlink->id,
                $backlink->referring_domain,
                strtoupper($backlink->status),
                $backlink->link_type,
                $backlink->anchor_text ?: 'N/A',
                $backlink->http_status_code,
                $result['message'],
            ];

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->table(
            ['ID', 'Domain', 'Status', 'Type', 'Anchor Text', 'HTTP Code', 'Diagnostic Message'],
            $rows
        );

        $this->info("Verification Complete: {$activeCount} Active / Healthy, {$brokenCount} Broken or Dropped.");

        return Command::SUCCESS;
    }
}
