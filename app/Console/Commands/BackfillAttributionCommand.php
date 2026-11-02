<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Contact;
use App\Models\RequestLog;
use App\Models\WhatsappInquiry;
use App\Services\VisitorTrackerService;
use Illuminate\Console\Command;

class BackfillAttributionCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'attribution:backfill {--dry-run : Analyze without saving}';

    /**
     * The console command description.
     */
    protected $description = 'Infer and backfill traffic channel and marketing attribution across Bookings, Contacts, and WhatsApp Inquiries';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $this->info($dryRun ? 'Starting Attribution Backfill (DRY RUN)...' : 'Starting Attribution Backfill...');

        // 1. Backfill Bookings
        $bookings = Booking::whereNull('traffic_channel')
            ->orWhere('traffic_channel', '')
            ->orWhere('traffic_channel', 'Direct')
            ->get();

        $bookingsUpdated = 0;
        foreach ($bookings as $b) {
            $source = $b->utm_source;
            $medium = $b->utm_medium;
            $gclid = $b->gclid;
            $fbclid = $b->fbclid;
            $referrer = $b->referrer ?: ($b->initial_referrer ?? null);

            // Cross-reference linked request_logs if present
            if ($b->request_log_id) {
                $log = RequestLog::find($b->request_log_id);
                if ($log) {
                    $source = $source ?: ($log->utm_source !== 'Not Available' ? $log->utm_source : null);
                    $medium = $medium ?: ($log->utm_medium !== 'Not Available' ? $log->utm_medium : null);
                    $referrer = $referrer ?: ($log->referrer_url !== 'Not Available' ? $log->referrer_url : null);
                    if (empty($b->landing_page) && $log->landing_page && $log->landing_page !== 'Not Available') {
                        $b->landing_page = $log->landing_page;
                    }
                }
            }

            $channel = VisitorTrackerService::determineChannel($source, $medium, $gclid, $fbclid, $referrer);

            if ($b->traffic_channel !== $channel) {
                $b->traffic_channel = $channel;
                if (! $dryRun) {
                    $b->save();
                }
                $bookingsUpdated++;
            }
        }
        $this->info("Bookings backfilled: {$bookingsUpdated} records updated.");

        // 2. Backfill Contacts
        $contacts = Contact::whereNull('traffic_channel')
            ->orWhere('traffic_channel', '')
            ->orWhere('traffic_channel', 'Direct')
            ->get();

        $contactsUpdated = 0;
        foreach ($contacts as $c) {
            $source = $c->utm_source;
            $medium = $c->utm_medium;
            $gclid = $c->gclid;
            $fbclid = $c->fbclid;
            $referrer = $c->initial_referrer;

            if ($c->request_log_id) {
                $log = RequestLog::find($c->request_log_id);
                if ($log) {
                    $source = $source ?: ($log->utm_source !== 'Not Available' ? $log->utm_source : null);
                    $medium = $medium ?: ($log->utm_medium !== 'Not Available' ? $log->utm_medium : null);
                    $referrer = $referrer ?: ($log->referrer_url !== 'Not Available' ? $log->referrer_url : null);
                    if (empty($c->landing_page) && $log->landing_page && $log->landing_page !== 'Not Available') {
                        $c->landing_page = $log->landing_page;
                    }
                    if (empty($c->utm_source) && $source) {
                        $c->utm_source = $source;
                    }
                    if (empty($c->utm_medium) && $medium) {
                        $c->utm_medium = $medium;
                    }
                    if (empty($c->utm_campaign) && $log->utm_campaign !== 'Not Available') {
                        $c->utm_campaign = $log->utm_campaign;
                    }
                }
            }

            $channel = VisitorTrackerService::determineChannel($source, $medium, $gclid, $fbclid, $referrer);

            if ($c->traffic_channel !== $channel || empty($c->traffic_channel)) {
                $c->traffic_channel = $channel;
                if (! $dryRun) {
                    $c->save();
                }
                $contactsUpdated++;
            }
        }
        $this->info("Contacts backfilled: {$contactsUpdated} records updated.");

        // 3. Backfill WhatsApp Inquiries
        $whatsappLeads = WhatsappInquiry::whereNull('traffic_channel')
            ->orWhere('traffic_channel', '')
            ->orWhere('traffic_channel', 'Direct')
            ->get();

        $waUpdated = 0;
        foreach ($whatsappLeads as $wa) {
            $source = $wa->utm_source;
            $medium = $wa->utm_medium;
            $gclid = $wa->gclid;
            $fbclid = $wa->fbclid;
            $referrer = $wa->initial_referrer ?: $wa->referrer_url;

            $channel = VisitorTrackerService::determineChannel($source, $medium, $gclid, $fbclid, $referrer);

            if ($wa->traffic_channel !== $channel) {
                $wa->traffic_channel = $channel;
                if (! $dryRun) {
                    $wa->save();
                }
                $waUpdated++;
            }
        }
        $this->info("WhatsApp leads backfilled: {$waUpdated} records updated.");

        return self::SUCCESS;
    }
}
