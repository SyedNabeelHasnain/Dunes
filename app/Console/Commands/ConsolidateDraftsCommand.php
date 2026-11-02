<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConsolidateDraftsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bookings:consolidate-drafts {--dry-run : Run analysis without deleting records}';

    /**
     * The console command description.
     */
    protected $description = 'Consolidate duplicate abandoned checkout drafts and retain maximum visitor information';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $this->info($dryRun ? 'Starting Draft Consolidation Analysis (DRY RUN)...' : 'Starting Draft Consolidation & Deduplication...');

        $drafts = Booking::where('status', 'draft')
            ->orderBy('created_at', 'asc')
            ->get();

        if ($drafts->isEmpty()) {
            $this->info('No draft records found in database.');
            return self::SUCCESS;
        }

        $this->info("Found {$drafts->count()} total draft records to inspect.");

        $processedIds = [];
        $duplicatesToDelete = [];
        $mergedCount = 0;

        foreach ($drafts as $draft) {
            if (in_array($draft->id, $processedIds, true) || in_array($draft->id, $duplicatesToDelete, true)) {
                continue;
            }

            // Find candidates that belong to the same session / visitor
            $candidates = Booking::where('status', 'draft')
                ->where('id', '!=', $draft->id)
                ->whereNotIn('id', $processedIds)
                ->whereNotIn('id', $duplicatesToDelete)
                ->where(function ($q) use ($draft) {
                    $hasCondition = false;

                    // Match by draft_token if present
                    if (! empty($draft->draft_token)) {
                        $q->where('draft_token', $draft->draft_token);
                        $hasCondition = true;
                    }

                    // Match by valid email
                    if (! empty($draft->email) && ! str_contains($draft->email, '@guest.local')) {
                        if ($hasCondition) {
                            $q->orWhere('email', $draft->email);
                        } else {
                            $q->where('email', $draft->email);
                            $hasCondition = true;
                        }
                    }

                    // Match by valid phone
                    if (! empty($draft->phone) && strlen(preg_replace('/[^0-9]/', '', $draft->phone)) >= 7) {
                        if ($hasCondition) {
                            $q->orWhere('phone', $draft->phone);
                        } else {
                            $q->where('phone', $draft->phone);
                            $hasCondition = true;
                        }
                    }

                    // Match by IP + tour within 4 hours
                    if (! empty($draft->ip_address) && $draft->tour_id) {
                        $ipCond = function ($sq) use ($draft) {
                            $sq->where('ip_address', $draft->ip_address)
                                ->where('tour_id', $draft->tour_id)
                                ->whereBetween('created_at', [
                                    $draft->created_at->copy()->subHours(4),
                                    $draft->created_at->copy()->addHours(4),
                                ]);
                        };
                        if ($hasCondition) {
                            $q->orWhere($ipCond);
                        } else {
                            $ipCond($q);
                            $hasCondition = true;
                        }
                    }

                    if (! $hasCondition) {
                        $q->whereRaw('0 = 1');
                    }
                })
                ->get();

            if ($candidates->isEmpty()) {
                $processedIds[] = $draft->id;
                continue;
            }

            // Group includes master draft candidate and matches
            $group = collect([$draft])->merge($candidates);

            // Determine the master record (one with maximum non-placeholder data)
            $master = $group->sortByDesc(function ($b) {
                $score = 0;
                if (! empty($b->name) && $b->name !== 'Prospective Guest') $score += 20;
                if (! empty($b->email) && ! str_contains($b->email, '@guest.local')) $score += 25;
                if (! empty($b->phone)) $score += 25;
                if (! empty($b->pickup_location) && $b->pickup_location !== 'Dubai Hotel') $score += 10;
                if ((float) $b->total > 0) $score += 15;
                if (! empty($b->traffic_channel) && $b->traffic_channel !== 'Direct') $score += 10;
                if (! empty($b->draft_token)) $score += 10;
                return $score;
            })->first();

            // Merge details from group into master
            $changed = false;
            foreach ($group as $item) {
                if ($item->id === $master->id) {
                    continue;
                }

                // Merge Name
                if ((empty($master->name) || $master->name === 'Prospective Guest') && (! empty($item->name) && $item->name !== 'Prospective Guest')) {
                    $master->name = $item->name;
                    $changed = true;
                }

                // Merge Email
                if ((empty($master->email) || str_contains($master->email, '@guest.local')) && (! empty($item->email) && ! str_contains($item->email, '@guest.local'))) {
                    $master->email = $item->email;
                    $changed = true;
                }

                // Merge Phone
                if (empty($master->phone) && ! empty($item->phone)) {
                    $master->phone = $item->phone;
                    $changed = true;
                }

                // Merge Pickup Location
                if ((empty($master->pickup_location) || $master->pickup_location === 'Dubai Hotel') && (! empty($item->pickup_location) && $item->pickup_location !== 'Dubai Hotel')) {
                    $master->pickup_location = $item->pickup_location;
                    $changed = true;
                }

                // Merge Attribution
                if (empty($master->traffic_channel) && ! empty($item->traffic_channel)) {
                    $master->traffic_channel = $item->traffic_channel;
                    $changed = true;
                }
                if (empty($master->gclid) && ! empty($item->gclid)) {
                    $master->gclid = $item->gclid;
                    $changed = true;
                }
                if (empty($master->fbclid) && ! empty($item->fbclid)) {
                    $master->fbclid = $item->fbclid;
                    $changed = true;
                }
                if (empty($master->landing_page) && ! empty($item->landing_page)) {
                    $master->landing_page = $item->landing_page;
                    $changed = true;
                }
                if (empty($master->draft_token) && ! empty($item->draft_token)) {
                    $master->draft_token = $item->draft_token;
                    $changed = true;
                }

                // Total & Subtotal
                if ((float) $master->total <= 0 && (float) $item->total > 0) {
                    $master->total = $item->total;
                    $master->subtotal = $item->subtotal;
                    $changed = true;
                }

                $duplicatesToDelete[] = $item->id;
            }

            if (! $dryRun && $changed) {
                $master->save();
            }

            $mergedCount++;
            $processedIds[] = $master->id;
        }

        // Remove confirmed duplicates
        $duplicateIds = array_unique($duplicatesToDelete);
        $removedCount = count($duplicateIds);

        if (! $dryRun && $removedCount > 0) {
            Booking::whereIn('id', $duplicateIds)->delete();
        }

        $this->info("Consolidation summary: {$mergedCount} groups consolidated, {$removedCount} duplicate draft shells removed.");

        return self::SUCCESS;
    }
}
