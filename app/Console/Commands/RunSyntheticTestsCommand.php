<?php

namespace App\Console\Commands;

use App\Services\SyntheticTesting\SyntheticJourneyRunner;
use Illuminate\Console\Command;

class RunSyntheticTestsCommand extends Command
{
    protected $signature = 'qa:run-synthetic-tests
                            {--flows= : Comma-separated subset of flows (booking,whatsapp,contact)}
                            {--locale= : Target storefront locale (e.g. en, ar)}
                            {--device= : Device profile (desktop or mobile)}
                            {--mail-mode= : Mail handling mode (intercept or live_smtp)}
                            {--no-cleanup : Preserve synthetic database records for inspection}
                            {--scheduled : Run only when enabled and due according to CMS schedule}
                            {--prune : Prune historical synthetic test runs older than retention policy}';

    protected $description = 'Execute end-to-end visitor journey tests across Booking, WhatsApp Inquiry, and Contact Us workflows';

    public function handle(SyntheticJourneyRunner $runner): int
    {
        if ($this->option('prune')) {
            $pruned = $runner->pruneOldRuns();
            $this->info("Pruned {$pruned} expired synthetic test run(s).");

            return self::SUCCESS;
        }

        $isScheduled = (bool) $this->option('scheduled');
        if ($isScheduled) {
            $cfg = $runner->getSettings();
            if (empty($cfg['enabled'])) {
                $this->line('Scheduled synthetic QA is disabled in CMS settings.');

                return self::SUCCESS;
            }
            if (! $runner->isDueNow(now('Asia/Dubai'), $cfg)) {
                $this->line('Scheduled synthetic QA is not due at this minute.');

                return self::SUCCESS;
            }
        }

        $overrides = [];
        if ($flowsOpt = trim((string) $this->option('flows'))) {
            $overrides['flows'] = array_values(array_intersect(
                SyntheticJourneyRunner::VALID_FLOWS,
                array_filter(array_map('trim', explode(',', $flowsOpt)))
            ));
        }
        if ($localeOpt = trim((string) $this->option('locale'))) {
            $overrides['locale'] = $localeOpt;
        }
        if ($deviceOpt = trim((string) $this->option('device'))) {
            $overrides['device_profile'] = $deviceOpt;
        }
        if ($mailOpt = trim((string) $this->option('mail-mode'))) {
            $overrides['mail_mode'] = $mailOpt;
        }
        if ($this->option('no-cleanup')) {
            $overrides['auto_cleanup'] = false;
        }

        $run = $runner->execute($overrides, $isScheduled ? 'scheduled' : 'cli');

        $rows = [];
        foreach ($run->steps()->get() as $step) {
            $rows[] = [
                strtoupper($step->flow),
                $step->step_order,
                $step->title,
                $step->http_status ?? '—',
                strtoupper($step->status),
                $step->duration_ms.' ms',
                $step->status === 'failed' ? ($step->error_message ?? 'Failed') : ($step->assertion_summary ?? ''),
            ];
        }

        $this->table(['Flow', '#', 'Step', 'HTTP', 'Status', 'Duration', 'Details'], $rows);
        $this->line("Run #{$run->id} finished with status [".strtoupper($run->status)."]: {$run->summary}");

        return $run->status === 'passed' ? self::SUCCESS : self::FAILURE;
    }
}
