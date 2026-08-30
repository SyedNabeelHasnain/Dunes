<?php

namespace App\Console\Commands;

use App\Mail\ReviewRequestMail;
use App\Models\Booking;
use App\Services\SettingsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendReviewRequestsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tours:send-review-requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send post-tour review collection emails to yesterday\'s completed/confirmed safari guests.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $settings = app(SettingsService::class);
        $fromEmail = $settings->getFromEmail();

        $yesterday = now()->subDay()->format('Y-m-d');
        $this->info("Scanning completed tour bookings for date: {$yesterday}");

        $bookings = Booking::whereDate('tour_date', $yesterday)
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        $count = 0;
        foreach ($bookings as $booking) {
            // Check if review already requested via special_requests tag
            if ($booking->special_requests && str_contains($booking->special_requests, '[REVIEW_REQUESTED]')) {
                continue;
            }

            try {
                Mail::to($booking->email)->send(
                    (new ReviewRequestMail($booking))->from($fromEmail, 'Dunes Discovery Tourism')
                );

                $notes = $booking->special_requests ?: '';
                $booking->update([
                    'special_requests' => trim($notes . "\n[REVIEW_REQUESTED: " . now()->toIso8601String() . "]")
                ]);

                $count++;
                $this->info("Dispatched review invitation to: {$booking->email} (Ref: #{$booking->reference})");
            } catch (\Throwable $e) {
                Log::error("Failed to send post-tour review email to {$booking->email}: " . $e->getMessage());
                $this->error("Failed to send review email to {$booking->email}: " . $e->getMessage());
            }
        }

        $this->info("Successfully dispatched {$count} post-tour review requests.");
        return Command::SUCCESS;
    }
}