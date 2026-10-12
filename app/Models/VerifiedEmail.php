<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifiedEmail extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['email', 'verified_at'];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    /**
     * Check if an email address is already verified in session or database,
     * and self-heal by persisting to verified_emails + session if found.
     */
    public static function isVerified(string $rawEmail): bool
    {
        $email = strtolower(trim($rawEmail));
        if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $sessionKey = 'email_verified_'.md5($email);

        if (session()->has($sessionKey)) {
            try {
                if (! static::where('email', $email)->exists()) {
                    static::updateOrCreate(['email' => $email], ['verified_at' => now()]);
                }
            } catch (\Throwable $e) {
            }

            return true;
        }

        try {
            // 1. Primary lookup in verified_emails table
            if (static::where('email', $email)->exists()) {
                session([$sessionKey => true]);

                return true;
            }

            // 2. Fallback lookup across historical bookings, verified contacts, or registered users
            $previouslyVerified = Booking::whereRaw('LOWER(email) = ?', [$email])
                ->whereNotIn('status', ['draft', 'cancelled'])
                ->exists()
                || Contact::whereRaw('LOWER(email) = ?', [$email])
                    ->where('is_verified', true)
                    ->exists()
                || User::whereRaw('LOWER(email) = ?', [$email])->exists();

            if ($previouslyVerified) {
                static::markVerified($email);

                return true;
            }
        } catch (\Throwable $e) {
        }

        return false;
    }

    /**
     * Permanently mark an email address as verified in the database and current session.
     */
    public static function markVerified(string $rawEmail): void
    {
        $email = strtolower(trim($rawEmail));
        if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        session(['email_verified_'.md5($email) => true]);

        try {
            static::updateOrCreate(
                ['email' => $email],
                ['verified_at' => now()]
            );
        } catch (\Throwable $e) {
        }
    }
}
