<?php

namespace App\Services\Mail;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SmtpDiagnosticService
{
    public const CACHE_KEY = 'smtp_health_status_v1';

    public const SETTING_KEY = 'smtp_last_health_json';

    /**
     * Classify an SMTP / mail transport exception into a clear actionable diagnosis.
     */
    public function diagnose(\Throwable|string $error, ?string $recipient = null): array
    {
        $raw = $error instanceof \Throwable ? $error->getMessage() : (string) $error;
        $lower = strtolower($raw);

        // 1. Account outbound sending suspended (Hostinger / Titan / cPanel 554 5.7.1)
        if (
            str_contains($lower, 'outbound sending is disabled')
            || (str_contains($lower, '554') && (str_contains($lower, '5.7.1') || str_contains($lower, 'disabled') || str_contains($lower, 'suspended') || str_contains($lower, 'blocked')))
            || str_contains($lower, 'account has been suspended')
            || str_contains($lower, 'sending limit exceeded')
        ) {
            return [
                'category' => 'account_suspended',
                'source' => 'smtp_server',
                'severity' => 'critical',
                'smtp_code' => '554 5.7.1',
                'title' => 'Your SMTP Mail Account Outbound Sending Is Suspended',
                'summary' => 'This error is from your SMTP mail server (e.g., Hostinger) — NOT the customer\'s email address. Your mail provider temporarily disabled outbound sending on this mailbox (commonly triggered by contact-form spam reflection).',
                'recommended_actions' => [
                    'Verify the Anti-Spam Shield & Email OTP protection are active on your Contact form first.',
                    'Log into Hostinger hPanel -> Emails -> Manage (or contact Hostinger Support) to re-enable outbound sending for your mailbox.',
                    'Configure a Secondary Failover SMTP Relay (Brevo, Resend, Postmark, or Amazon SES) in Admin -> SMTP & Mailer Settings so transactional emails never stop again.',
                ],
                'raw_error' => Str::limit($raw, 400),
                'recipient' => $recipient,
            ];
        }

        // 2. Authentication error (535)
        if (
            str_contains($lower, '535')
            || str_contains($lower, 'authentication failed')
            || str_contains($lower, 'username and password not accepted')
            || str_contains($lower, 'failed to authenticate')
        ) {
            return [
                'category' => 'auth_failed',
                'source' => 'smtp_server',
                'severity' => 'critical',
                'smtp_code' => '535',
                'title' => 'SMTP Authentication Failed (Invalid Username or Password)',
                'summary' => 'Your SMTP server rejected the configured username or password — NOT an issue with the customer\'s email address.',
                'recommended_actions' => [
                    'Open Admin -> Settings -> Mail / SMTP and re-enter the mailbox password or app-specific password.',
                    'Ensure SMTP Username matches the exact From Email Address.',
                ],
                'raw_error' => Str::limit($raw, 400),
                'recipient' => $recipient,
            ];
        }

        // 3. Recipient mailbox does not exist / rejected (550 5.1.1)
        if (
            str_contains($lower, '5.1.1')
            || str_contains($lower, 'user unknown')
            || str_contains($lower, 'no such user')
            || str_contains($lower, 'mailbox unavailable')
            || str_contains($lower, 'mailbox not found')
            || str_contains($lower, 'recipient address rejected')
            || str_contains($lower, 'does not exist')
        ) {
            return [
                'category' => 'recipient_rejected',
                'source' => 'recipient_email',
                'severity' => 'warning',
                'smtp_code' => '550 5.1.1',
                'title' => 'Customer Email Address Rejected / Invalid Mailbox',
                'summary' => 'The recipient email address'.($recipient ? " ({$recipient})" : '').' was rejected by the destination mail server because the address does not exist or cannot receive mail.',
                'recommended_actions' => [
                    'Double-check the customer email address for typos before retrying.',
                    'Reach out to the guest via WhatsApp or phone to confirm their correct email.',
                ],
                'raw_error' => Str::limit($raw, 400),
                'recipient' => $recipient,
            ];
        }

        // 4. Temporary rate limit / greylisting (421 / 451 / 452)
        if (
            str_contains($lower, '421')
            || str_contains($lower, '451')
            || str_contains($lower, '452')
            || str_contains($lower, 'too many')
            || str_contains($lower, 'rate limit')
            || str_contains($lower, 'try again later')
        ) {
            return [
                'category' => 'rate_limited',
                'source' => 'smtp_server',
                'severity' => 'warning',
                'smtp_code' => '421 / 451',
                'title' => 'SMTP Server Rate-Limited or Temporarily Throttled',
                'summary' => 'Your mail provider temporarily throttled outbound email volume — NOT an issue with the customer\'s email.',
                'recommended_actions' => [
                    'Wait a few minutes and retry, or reduce newsletter batch size.',
                    'Enable a Secondary Failover SMTP Relay in Mail Settings for uninterrupted transactional delivery.',
                ],
                'raw_error' => Str::limit($raw, 400),
                'recipient' => $recipient,
            ];
        }

        // 5. Sender / Relay / SPF / DMARC policy rejection (553 / 550 5.7.1)
        if (
            str_contains($lower, '553')
            || str_contains($lower, 'sender address rejected')
            || str_contains($lower, 'relay access denied')
            || str_contains($lower, 'not owned by user')
            || str_contains($lower, '550 5.7.1')
        ) {
            return [
                'category' => 'sender_rejected',
                'source' => 'smtp_server',
                'severity' => 'critical',
                'smtp_code' => '553 / 550 5.7.1',
                'title' => 'Sender Address / Relay Policy Rejected by Mail Server',
                'summary' => 'The SMTP server refused the From Address or relay permission.',
                'recommended_actions' => [
                    'Ensure "From Email Address" matches the authenticated SMTP username.',
                    'Verify SPF, DKIM, and DMARC DNS records for your sending domain.',
                ],
                'raw_error' => Str::limit($raw, 400),
                'recipient' => $recipient,
            ];
        }

        // 6. Network / TLS / Socket timeout
        if (
            str_contains($lower, 'connection refused')
            || str_contains($lower, 'timed out')
            || str_contains($lower, 'stream_socket_client')
            || str_contains($lower, 'could not be established')
            || str_contains($lower, 'ssl')
            || str_contains($lower, 'getaddrinfo')
        ) {
            return [
                'category' => 'connection_failed',
                'source' => 'network',
                'severity' => 'critical',
                'smtp_code' => 'NETWORK',
                'title' => 'Could Not Connect to SMTP Server',
                'summary' => 'The application could not open a socket/TLS connection to the configured SMTP host & port.',
                'recommended_actions' => [
                    'Verify SMTP Host, Port (465 SSL or 587 TLS), and Encryption match your provider.',
                    'Check whether your hosting firewall blocks outbound port 465/587.',
                ],
                'raw_error' => Str::limit($raw, 400),
                'recipient' => $recipient,
            ];
        }

        return [
            'category' => 'unknown',
            'source' => 'smtp_server',
            'severity' => 'warning',
            'smtp_code' => 'SMTP',
            'title' => 'Email Delivery Error',
            'summary' => 'The mail server returned an unexpected error while transmitting the email.',
            'recommended_actions' => [
                'Run Live SMTP Tester in Admin -> Settings -> Mail / SMTP to inspect connectivity.',
                'Check laravel.log for full transport trace details.',
            ],
            'raw_error' => Str::limit($raw, 400),
            'recipient' => $recipient,
        ];
    }

    /**
     * Record an SMTP failure snapshot so Admin Mail Settings & Health Monitor show live status.
     */
    public function recordFailure(string $context, \Throwable|string $error, ?string $recipient = null): array
    {
        $diagnosis = $this->diagnose($error, $recipient);
        $snapshot = [
            'status' => $diagnosis['source'] === 'recipient_email' ? 'warning' : 'degraded',
            'context' => $context,
            'checked_at' => now()->toIso8601String(),
            'diagnosis' => $diagnosis,
        ];

        $this->persistSnapshot($snapshot);

        return $diagnosis;
    }

    /**
     * Record a healthy SMTP dispatch snapshot.
     */
    public function recordSuccess(string $context, string $mailerUsed = 'primary'): void
    {
        $snapshot = [
            'status' => 'healthy',
            'context' => $context,
            'mailer_used' => $mailerUsed,
            'checked_at' => now()->toIso8601String(),
            'diagnosis' => null,
        ];

        $this->persistSnapshot($snapshot);
    }

    /**
     * Retrieve the latest known SMTP health snapshot.
     */
    public function getHealthStatus(): array
    {
        $cached = Cache::get(self::CACHE_KEY);
        if (is_array($cached)) {
            return $cached;
        }

        try {
            if (Schema::hasTable('settings')) {
                $raw = Setting::where('setting_key', self::SETTING_KEY)->value('setting_value');
                if (is_string($raw) && $raw !== '') {
                    $decoded = json_decode($raw, true);
                    if (is_array($decoded)) {
                        Cache::put(self::CACHE_KEY, $decoded, now()->addDays(7));

                        return $decoded;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::debug('SmtpDiagnosticService::getHealthStatus lookup failed: '.$e->getMessage());
        }

        return [
            'status' => 'unknown',
            'context' => null,
            'checked_at' => null,
            'diagnosis' => null,
        ];
    }

    protected function persistSnapshot(array $snapshot): void
    {
        try {
            $previous = Cache::get(self::CACHE_KEY);
            Cache::put(self::CACHE_KEY, $snapshot, now()->addDays(7));

            $statusChanged = ! is_array($previous) || (($previous['status'] ?? null) !== ($snapshot['status'] ?? null));
            $throttleKey = 'smtp_health_db_persisted_lock';

            if (($statusChanged || ! Cache::has($throttleKey)) && Schema::hasTable('settings')) {
                Setting::updateOrCreate(
                    ['setting_key' => self::SETTING_KEY],
                    ['setting_value' => (string) json_encode($snapshot)]
                );
                Cache::put($throttleKey, true, now()->addMinutes(5));
                Cache::forget('site_settings_cache');
            }
        } catch (\Throwable $e) {
            Log::debug('SmtpDiagnosticService::persistSnapshot failed: '.$e->getMessage());
        }
    }
}
