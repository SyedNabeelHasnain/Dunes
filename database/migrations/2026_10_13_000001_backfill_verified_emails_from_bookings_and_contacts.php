<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Backfill all previously verified or booked customer emails into verified_emails
     * so returning guests are never asked to verify their email address again.
     */
    public function up(): void
    {
        if (! Schema::hasTable('verified_emails')) {
            return;
        }

        $emails = collect();

        if (Schema::hasTable('bookings')) {
            $bookingEmails = DB::table('bookings')
                ->whereNotNull('email')
                ->whereNotIn('status', ['draft', 'cancelled'])
                ->pluck('email');
            $emails = $emails->merge($bookingEmails);
        }

        if (Schema::hasTable('contacts')) {
            $contactEmails = DB::table('contacts')
                ->whereNotNull('email')
                ->where('is_verified', true)
                ->pluck('email');
            $emails = $emails->merge($contactEmails);
        }

        if (Schema::hasTable('users')) {
            $userEmails = DB::table('users')
                ->whereNotNull('email')
                ->pluck('email');
            $emails = $emails->merge($userEmails);
        }

        $uniqueValidEmails = $emails
            ->map(fn ($e) => strtolower(trim((string) $e)))
            ->filter(fn ($e) => ! empty($e) && filter_var($e, FILTER_VALIDATE_EMAIL) && $e !== 'abandoned@guest.local')
            ->unique()
            ->values();

        $now = now();
        foreach ($uniqueValidEmails as $email) {
            try {
                DB::table('verified_emails')->updateOrInsert(
                    ['email' => $email],
                    ['verified_at' => $now]
                );
            } catch (\Throwable $e) {
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left empty to preserve verified customer emails
    }
};
