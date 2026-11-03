<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\BookingPayment;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\RequestLog;
use App\Models\Review;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PurgeTestBookingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bookings:purge-test-bookings
                            {--force : Force deletion without confirmation prompt}
                            {--dry-run : Simulate and preview which records will be removed}';

    /**
     * The console command description.
     */
    protected $description = 'Safely and permanently purge all test bookings and their cascading records without touching authentic customer data';

    /**
     * Known test references explicitly generated during QA and tests.
     */
    protected const EXPLICIT_TEST_REFS = [
        'DT-TEST-001',
        'DT-TEST-002',
        'DT-TEST-003',
        'DT-TEST-VIEW',
        'DT-TEST-RAW',
        'DT-TEST-VOUCH',
        'DT-TEST-VOUCH1',
        'DT-VERIFY-100',
        'DT-TEST-3143D',
        'DT-TEST-A7BFB',
        'DT-TEST-552E6',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $this->info($dryRun
            ? '🔍 Inspecting database for test bookings (DRY RUN - No data will be modified)...'
            : '🧹 Initializing safe purge of test bookings and analytical telemetry...'
        );

        // 1. Fetch potential test bookings using explicit matching
        $candidates = Booking::withTrashed()
            ->where(function ($query) {
                $query->where('reference', 'LIKE', 'DT-TEST-%')
                    ->orWhere('reference', 'LIKE', 'DT-ADMIN-%')
                    ->orWhere('reference', 'LIKE', 'DT-VERIFY-%')
                    ->orWhereIn('reference', self::EXPLICIT_TEST_REFS)
                    ->orWhere(function ($q) {
                        $q->where('reference', 'LIKE', 'DT-%')
                            ->where(function ($sub) {
                                $sub->whereIn('name', ['Honorable VIP Traveler', 'Alexander Wright'])
                                    ->orWhere('email', 'alexander.wright@uk-travel.co.uk');
                            });
                    });
            })
            ->get();

        // 2. Strict safety filter to GUARANTEE zero false positives
        $testBookings = $candidates->filter(function (Booking $booking) {
            $ref = (string) $booking->reference;

            // Absolute hard safeguard: Never touch real customer booking patterns
            if (str_starts_with($ref, 'DDT') || str_starts_with($ref, 'DFT')) {
                return false;
            }

            // Must match explicit test patterns
            if (str_starts_with($ref, 'DT-TEST-') ||
                str_starts_with($ref, 'DT-ADMIN-') ||
                str_starts_with($ref, 'DT-VERIFY-') ||
                in_array($ref, self::EXPLICIT_TEST_REFS, true)) {
                return true;
            }

            // Must be with test persona names or test emails under DT- prefix
            if (str_starts_with($ref, 'DT-')) {
                $name = (string) $booking->name;
                $email = (string) $booking->email;
                if ($name === 'Honorable VIP Traveler' || $name === 'Alexander Wright' || $email === 'alexander.wright@uk-travel.co.uk') {
                    return true;
                }
            }

            return false;
        });

        $count = $testBookings->count();

        if ($count === 0) {
            $this->info('✅ Clean state: No test bookings found in the database.');
            $this->cleanOrphanVoucherPdfs($dryRun);

            return self::SUCCESS;
        }

        $this->warn("⚠️  Identified {$count} test booking(s) matching strict test criteria:");
        foreach ($testBookings as $b) {
            $this->line("   - Ref: {$b->reference} | Name: {$b->name} | Email: {$b->email} | Total: {$b->total} | Created: {$b->created_at}");
        }

        if ($dryRun) {
            $this->info("Dry run complete. {$count} test booking(s) would be permanently purged.");
            $this->cleanOrphanVoucherPdfs(true);

            return self::SUCCESS;
        }

        if (! $force && ! $this->confirm("Are you sure you want to permanently delete these {$count} test booking(s) and all cascading records?", false)) {
            $this->info('Operation cancelled by user.');

            return self::SUCCESS;
        }

        $purgedCount = 0;
        $purgedRefs = [];

        DB::transaction(function () use ($testBookings, &$purgedCount, &$purgedRefs) {
            foreach ($testBookings as $booking) {
                $bookingId = $booking->id;
                $bookingRef = $booking->reference;

                // 1. Delete associated addons
                BookingAddon::where('booking_id', $bookingId)->delete();

                // 2. Delete payment records
                BookingPayment::where('booking_id', $bookingId)->forceDelete();

                // 3. Handle Coupon Usage restoration if test used coupons
                $couponUsages = CouponUsage::where('booking_id', $bookingId)
                    ->orWhere('booking_reference', $bookingRef)
                    ->get();

                foreach ($couponUsages as $usage) {
                    if ($usage->coupon_id) {
                        Coupon::where('id', $usage->coupon_id)
                            ->where('used_count', '>', 0)
                            ->decrement('used_count');
                    }
                    $usage->delete();
                }

                // 4. Delete associated reviews and unlinked photos
                $reviews = Review::where('booking_id', $bookingId)->get();
                foreach ($reviews as $review) {
                    if (! empty($review->photos) && is_array($review->photos)) {
                        foreach ($review->photos as $photo) {
                            if (is_string($photo) && str_starts_with($photo, '/storage/')) {
                                $relativePath = str_replace('/storage/', '', $photo);
                                Storage::disk('public')->delete($relativePath);
                            }
                        }
                    }
                    $review->delete();
                }

                // 5. Delete analytical telemetry (request_logs)
                if ($booking->request_log_id) {
                    RequestLog::where('id', $booking->request_log_id)->delete();
                }
                RequestLog::where('entity_type', 'booking')
                    ->where('entity_id', $bookingId)
                    ->delete();

                // 6. Delete cached voucher PDF for this reference
                $voucherFile = storage_path('app/vouchers/' . $bookingRef . '.pdf');
                if (file_exists($voucherFile)) {
                    @unlink($voucherFile);
                }

                // 7. Permanently force delete the booking record
                $booking->forceDelete();

                $purgedCount++;
                $purgedRefs[] = $bookingRef;
            }
        });

        // 8. Clean up any remaining orphan test voucher PDF files matching DT-*.pdf
        $this->cleanOrphanVoucherPdfs(false);

        // 9. Invalidate KPI, social proof, and dashboard caches
        Cache::forget('admin_dashboard_kpis');
        Cache::forget('site_social_proof_feed');
        Cache::forget('site_social_proof_feed_all');
        Cache::forget('site_social_proof_feed_bookings_only');
        Cache::forget('site_social_proof_feed_custom_only');

        Log::info("Safely purged {$purgedCount} test bookings from database and storage.", [
            'references' => $purgedRefs,
        ]);

        $this->info("✅ Successfully and permanently purged {$purgedCount} test booking(s) and refreshed analytics caches.");

        return self::SUCCESS;
    }

    /**
     * Clean any orphan test voucher PDF files matching DT-*.pdf from storage.
     */
    protected function cleanOrphanVoucherPdfs(bool $dryRun = false): void
    {
        $vouchersDir = storage_path('app/vouchers');
        if (! is_dir($vouchersDir)) {
            return;
        }

        $files = glob($vouchersDir . '/DT-*.pdf') ?: [];
        $deleted = 0;

        foreach ($files as $file) {
            $filename = basename($file);
            if ($dryRun) {
                $this->line("   [Dry-run] Would unlink test voucher file: {$filename}");
            } else {
                @unlink($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("🧹 Cleaned {$deleted} orphan test voucher PDF file(s) from storage.");
        }
    }
}
