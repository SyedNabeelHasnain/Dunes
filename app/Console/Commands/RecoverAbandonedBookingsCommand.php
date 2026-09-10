<?php

namespace App\Console\Commands;

use App\Mail\AbandonedBookingRecoveryMail;
use App\Models\Booking;
use App\Services\SettingsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class RecoverAbandonedBookingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:recover-abandoned';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recover abandoned checkouts by sending a personalized completion email and WhatsApp concierge invite.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $settings = app(SettingsService::class);
        $fromEmail = $settings->getFromEmail();

        // Target pending & unpaid bookings created between 1 hour and 24 hours ago
        $startTime = now()->subHours(24);
        $endTime = now()->subHour();

        $this->info("Scanning abandoned bookings created between {$startTime->toDateTimeString()} and {$endTime->toDateTimeString()}...");

        $bookings = Booking::whereIn('status', ['pending', 'draft'])
            ->where(function ($query) {
                $query->where('payment_status', 'unpaid')
                    ->orWhereNull('payment_status');
            })
            ->where(function ($query) {
                $query->where('payment_method', '!=', 'cash')
                    ->orWhereNull('payment_method');
            })
            ->whereBetween('created_at', [$startTime, $endTime])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->where('email', 'not like', '%@guest.local')
            ->get();

        $count = 0;
        foreach ($bookings as $booking) {
            if (!filter_var($booking->email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            // Prevent duplicate recovery emails (check cache and tag)
            $cacheKey = 'abandoned_recovery_sent_' . $booking->id;
            if (\Illuminate\Support\Facades\Cache::has($cacheKey) || 
                ($booking->special_requests && str_contains($booking->special_requests, '[RECOVERY_DISPATCHED]'))) {
                continue;
            }

            // Exclude if customer has already completed another booking subsequently
            $hasCompletedOther = Booking::where('email', $booking->email)
                ->whereIn('status', ['confirmed', 'completed'])
                ->where('created_at', '>=', $booking->created_at)
                ->exists();

            if ($hasCompletedOther) {
                continue;
            }

            try {
                Mail::to($booking->email)->send(
                    (new AbandonedBookingRecoveryMail($booking))->from($fromEmail, 'Dunes Discovery Tourism')
                );

                \Illuminate\Support\Facades\Cache::put($cacheKey, now()->toIso8601String(), 86400 * 7);

                $count++;
                $this->info("Dispatched recovery invitation to: {$booking->email} (Ref: #{$booking->reference})");
            } catch (\Throwable $e) {
                Log::error("Failed to send abandoned checkout email to {$booking->email}: " . $e->getMessage());
                $this->error("Failed to send recovery email to {$booking->email}: " . $e->getMessage());
            }
        }

        $this->info("Successfully dispatched {$count} abandoned booking recovery emails.");
        return Command::SUCCESS;
    }
}