<?php

namespace App\Services;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignLog;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailMarketingService
{
    protected SettingsService $settings;

    public function __construct(SettingsService $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Dynamically apply Primary + Optional Secondary Failover SMTP configuration from database settings at runtime.
     */
    public function applySmtpConfig(bool $forceInTesting = false): void
    {
        if (app()->environment('testing') && ! $forceInTesting) {
            return;
        }

        $configuredHost = trim((string) $this->settings->get('smtp_host', ''));
        $fallbackEnabled = $this->settings->get('smtp_fallback_enabled', '0') === '1';
        $fallbackHost = trim((string) $this->settings->get('smtp_fallback_host', ''));

        if ($configuredHost === '' && ! ($fallbackEnabled && $fallbackHost !== '')) {
            return;
        }

        $driver = $this->settings->get('smtp_driver', config('mail.default', 'smtp'));
        $host = $configuredHost;
        $port = (int) $this->settings->get('smtp_port', config('mail.mailers.smtp.port', 465));
        $encryption = $this->settings->get('smtp_encryption', config('mail.mailers.smtp.encryption', 'ssl'));
        $username = $this->settings->get('smtp_username', config('mail.mailers.smtp.username'));
        $password = $this->settings->get('smtp_password', config('mail.mailers.smtp.password'));
        $fromAddress = $this->settings->get('smtp_from_address', config('mail.from.address'));
        $fromName = $this->settings->get('smtp_from_name', config('mail.from.name', $this->settings->get('site_name', 'Dunes Discovery Tourism')));

        if ($driver === 'smtp' && $host !== '') {
            Config::set('mail.mailers.smtp.host', $host);
            Config::set('mail.mailers.smtp.port', $port);
            Config::set('mail.mailers.smtp.encryption', $encryption === 'none' ? null : $encryption);
            Config::set('mail.mailers.smtp.scheme', $encryption === 'ssl' ? 'smtps' : null);

            if (! empty($username)) {
                Config::set('mail.mailers.smtp.username', $username);
            }
            if (! empty($password)) {
                Config::set('mail.mailers.smtp.password', $password);
            }
        }

        // Configure optional Secondary / Failover Transactional SMTP Relay (e.g., Brevo / Resend / Postmark / SES)
        $hasActiveFailover = false;
        if ($fallbackEnabled && $fallbackHost !== '') {
            $fbPort = (int) $this->settings->get('smtp_fallback_port', 587);
            $fbEnc = (string) $this->settings->get('smtp_fallback_encryption', 'tls');
            $fbUser = (string) $this->settings->get('smtp_fallback_username', '');
            $fbPass = (string) $this->settings->get('smtp_fallback_password', '');

            Config::set('mail.mailers.smtp_fallback.transport', 'smtp');
            Config::set('mail.mailers.smtp_fallback.host', $fallbackHost);
            Config::set('mail.mailers.smtp_fallback.port', $fbPort);
            Config::set('mail.mailers.smtp_fallback.encryption', $fbEnc === 'none' ? null : $fbEnc);
            Config::set('mail.mailers.smtp_fallback.scheme', $fbEnc === 'ssl' ? 'smtps' : null);
            Config::set('mail.mailers.smtp_fallback.username', $fbUser !== '' ? $fbUser : null);
            Config::set('mail.mailers.smtp_fallback.password', $fbPass !== '' ? $fbPass : null);
            $hasActiveFailover = true;
        }

        if ($hasActiveFailover && $driver === 'smtp') {
            $mailersChain = $host !== '' ? ['smtp', 'smtp_fallback'] : ['smtp_fallback'];
            Config::set('mail.mailers.failover.mailers', $mailersChain);
            Config::set('mail.mailers.failover.retry_after', 15);
            Config::set('mail.default', 'failover');
        } elseif (! empty($driver)) {
            Config::set('mail.default', $driver);
        }

        if (! empty($fromAddress)) {
            Config::set('mail.from.address', $fromAddress);
            Config::set('mail.from.name', $fromName);
        }

        try {
            Mail::purge('smtp');
            Mail::purge('smtp_fallback');
            Mail::purge('failover');
            if (! empty($driver) && ! in_array($driver, ['smtp', 'smtp_fallback', 'failover'], true)) {
                Mail::purge($driver);
            }
        } catch (\Throwable $e) {
        }
    }

    /**
     * Replace dynamic personalized tags in email subject or body.
     */
    public function parseTags(string $text, Subscriber $subscriber, ?EmailCampaignLog $log = null): string
    {
        $siteName = $this->settings->get('site_name', 'Dunes Discovery Tourism');
        $siteUrl = url('/');
        $sitePhone = $this->settings->get('site_phone', '+971 50 245 6056');
        $siteWhatsapp = preg_replace('/[^0-9]/', '', $this->settings->get('site_whatsapp', '971502456056'));
        $companyAddress = $this->settings->get('site_address', 'Dubai Desert Safari Terminal, Al Aweer & Lahbab, Dubai, UAE');
        $currentYear = date('Y');

        $firstName = ! empty($subscriber->first_name) ? $subscriber->first_name : (! empty($subscriber->full_name) ? explode(' ', $subscriber->full_name)[0] : 'Friend');
        $lastName = ! empty($subscriber->last_name) ? $subscriber->last_name : '';
        $fullName = ! empty($subscriber->full_name) ? $subscriber->full_name : $firstName;

        $unsubUrl = $subscriber->unsubscribe_url;

        $replacements = [
            '{{subscriber_name}}' => htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'),
            '{{first_name}}' => htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8'),
            '{{last_name}}' => htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8'),
            '{{email}}' => htmlspecialchars($subscriber->email, ENT_QUOTES, 'UTF-8'),
            '{{unsubscribe_url}}' => $unsubUrl,
            '{{site_name}}' => htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'),
            '{{site_url}}' => $siteUrl,
            '{{site_phone}}' => htmlspecialchars($sitePhone, ENT_QUOTES, 'UTF-8'),
            '{{site_whatsapp}}' => htmlspecialchars($siteWhatsapp, ENT_QUOTES, 'UTF-8'),
            '{{company_address}}' => htmlspecialchars($companyAddress, ENT_QUOTES, 'UTF-8'),
            '{{current_year}}' => $currentYear,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    /**
     * Render full personalized HTML email with tracking pixel and link rewrites.
     */
    public function renderHtml(string $rawHtml, Subscriber $subscriber, ?EmailCampaignLog $log = null): string
    {
        $html = $this->parseTags($rawHtml, $subscriber, $log);

        // If tracking log exists, rewrite non-unsubscribe links for click tracking
        if ($log && ! empty($log->tracking_token)) {
            $token = $log->tracking_token;
            $clickBase = url('/email/track/click/'.$token);

            $html = preg_replace_callback('/<a\s+([^>]*?)href=["\']([^"\']+)["\']([^>]*?)>/i', function ($matches) use ($clickBase, $subscriber) {
                $before = $matches[1];
                $url = $matches[2];
                $after = $matches[3];

                // Skip mailto, tel, javascript, hash anchors, and unsubscribe links
                if (
                    str_starts_with($url, 'mailto:') ||
                    str_starts_with($url, 'tel:') ||
                    str_starts_with($url, '#') ||
                    str_starts_with($url, 'javascript:') ||
                    str_contains($url, '/unsubscribe') ||
                    $url === $subscriber->unsubscribe_url
                ) {
                    return $matches[0];
                }

                $trackedUrl = $clickBase.'?url='.urlencode($url);

                return '<a '.$before.'href="'.$trackedUrl.'"'.$after.'>';
            }, $html);

            // Inject 1x1 GIF tracking pixel
            $openPixelUrl = url('/email/track/open/'.$token.'.gif');
            $trackingPixel = '<img src="'.$openPixelUrl.'" width="1" height="1" style="display:none!important;max-height:0;max-width:0;opacity:0;overflow:hidden;border:0;outline:none;" alt="" />';

            if (stripos($html, '</body>') !== false) {
                $html = str_ireplace('</body>', $trackingPixel.'</body>', $html);
            } else {
                $html .= $trackingPixel;
            }
        }

        // CAN-SPAM & GDPR Compliance: Guarantee unsubscribe link exists in every email
        if (! str_contains($html, $subscriber->unsubscribe_url)) {
            $siteName = htmlspecialchars($this->settings->get('site_name', 'Dunes Discovery Tourism'), ENT_QUOTES, 'UTF-8');
            $unsubFooter = '<div style="margin-top: 36px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 11px; color: #94a3b8; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; line-height: 1.5;">'
                .'You are receiving this communication because you are subscribed to updates from '.$siteName.'.<br>'
                .'<a href="'.$subscriber->unsubscribe_url.'" style="color: #64748b; text-decoration: underline; font-weight: 500;">Unsubscribe from future marketing emails</a>'
                .'</div>';

            if (stripos($html, '</body>') !== false) {
                $html = str_ireplace('</body>', $unsubFooter.'</body>', $html);
            } else {
                $html .= $unsubFooter;
            }
        }

        return $html;
    }

    /**
     * Dispatch an entire campaign in safe chunks with error logging and status tracking.
     */
    public function dispatchCampaign(EmailCampaign $campaign, int $chunkSize = 50, int $delaySeconds = 1): array
    {
        $this->applySmtpConfig();

        // 1. Atomically claim scheduled campaigns so concurrent runners cannot double-send
        if ($campaign->status === 'scheduled') {
            $claimed = EmailCampaign::where('id', $campaign->id)
                ->where('status', 'scheduled')
                ->update([
                    'status' => 'sending',
                    'sent_at' => now(),
                ]);

            if (! $claimed) {
                return ['success' => false, 'sent' => 0, 'failed' => 0, 'total' => 0];
            }
            $campaign->refresh();
        } else {
            $campaign->update([
                'status' => 'sending',
                'sent_at' => now(),
            ]);
        }

        // 2. Query target audience
        $query = Subscriber::query()->where('status', 'subscribed');
        if ($campaign->target_type === 'group' && $campaign->group_id) {
            $query->whereHas('groups', function ($q) use ($campaign) {
                $q->where('subscriber_groups.id', $campaign->group_id);
            });
        }

        $totalSubscribers = $query->count();
        $campaign->update(['total_recipients' => $totalSubscribers]);

        if ($totalSubscribers === 0) {
            $campaign->update(['status' => 'sent']);

            return ['success' => true, 'sent' => 0, 'failed' => 0, 'total' => 0];
        }

        $sentCount = 0;
        $failedCount = 0;

        $fromAddress = $campaign->from_email ?: $this->settings->get('smtp_from_address', config('mail.from.address'));
        $fromName = $campaign->from_name ?: $this->settings->get('smtp_from_name', $this->settings->get('site_name', 'Dunes Discovery Tourism'));
        $replyTo = $campaign->reply_to ?: $this->settings->get('smtp_reply_to', $fromAddress);

        $query->chunk($chunkSize, function ($subscribers) use ($campaign, $fromAddress, $fromName, $replyTo, $delaySeconds, &$sentCount, &$failedCount) {
            foreach ($subscribers as $sub) {
                // Check or create campaign log
                $log = EmailCampaignLog::firstOrCreate(
                    [
                        'campaign_id' => $campaign->id,
                        'subscriber_id' => $sub->id,
                    ],
                    [
                        'tracking_token' => Str::random(40).time().Str::random(8),
                        'status' => 'pending',
                    ]
                );

                try {
                    $parsedSubject = $this->parseTags($campaign->subject, $sub, $log);
                    $parsedHtml = $this->renderHtml($campaign->content_html, $sub, $log);
                    $parsedPlain = ! empty($campaign->content_plain) ? $this->parseTags($campaign->content_plain, $sub, $log) : strip_tags($parsedHtml);

                    Mail::html($parsedHtml, function ($message) use ($sub, $parsedSubject, $fromAddress, $fromName, $replyTo) {
                        $message->to($sub->email, $sub->full_name)
                            ->subject($parsedSubject)
                            ->from($fromAddress, $fromName);

                        if (! empty($replyTo)) {
                            $message->replyTo($replyTo);
                        }
                    });

                    $log->update([
                        'status' => 'delivered',
                        'sent_at' => now(),
                        'error_message' => null,
                    ]);

                    $sentCount++;
                } catch (\Throwable $e) {
                    $failedCount++;
                    Log::error("Campaign [{$campaign->id}] failed for subscriber [{$sub->email}]: ".$e->getMessage());

                    $isBounce = str_contains(strtolower($e->getMessage()), 'bounce') ||
                                str_contains(strtolower($e->getMessage()), 'mailbox unavailable') ||
                                str_contains(strtolower($e->getMessage()), 'recipient rejected');

                    $log->update([
                        'status' => $isBounce ? 'bounced' : 'failed',
                        'sent_at' => now(),
                        'error_message' => Str::limit($e->getMessage(), 1000),
                    ]);

                    if ($isBounce) {
                        $sub->update(['status' => 'bounced']);
                        $campaign->increment('bounced_count');
                    }
                }
            }

            // Optional delay between chunks to respect shared hosting SMTP throttling
            if ($delaySeconds > 0) {
                sleep($delaySeconds);
            }
        });

        // 3. Update Final Campaign Metrics
        $campaign->update([
            'status' => 'sent',
            'sent_count' => $sentCount,
            'delivered_count' => $sentCount,
        ]);

        return [
            'success' => true,
            'sent' => $sentCount,
            'failed' => $failedCount,
            'total' => $totalSubscribers,
        ];
    }

    /**
     * Send a single diagnostic test email to any address (supports primary, fallback, or auto failover).
     */
    public function sendTestEmail(string $recipientEmail, string $subject, string $htmlContent, string $target = 'auto'): array
    {
        $this->applySmtpConfig();

        $diagnostics = app(\App\Services\Mail\SmtpDiagnosticService::class);

        $fromAddress = $this->settings->get('smtp_from_address', config('mail.from.address'));
        $fromName = $this->settings->get('smtp_from_name', $this->settings->get('site_name', 'Dunes Discovery Tourism'));

        if ($target === 'fallback') {
            $fbFrom = trim((string) $this->settings->get('smtp_fallback_from_address', ''));
            $fbName = trim((string) $this->settings->get('smtp_fallback_from_name', ''));
            if ($fbFrom !== '') {
                $fromAddress = $fbFrom;
            }
            if ($fbName !== '') {
                $fromName = $fbName;
            }
        }

        // Dummy subscriber for preview tag parsing
        $dummySubscriber = new Subscriber([
            'email' => $recipientEmail,
            'first_name' => 'Sample',
            'last_name' => 'Recipient',
            'unsubscribe_token' => 'sample-test-token',
        ]);

        $renderedSubject = str_starts_with($subject, '[TEST]')
            ? $this->parseTags($subject, $dummySubscriber)
            : '[TEST] '.$this->parseTags($subject, $dummySubscriber);
        $renderedHtml = $this->renderHtml($htmlContent, $dummySubscriber);

        try {
            $mailer = match ($target) {
                'primary' => Mail::mailer('smtp'),
                'fallback' => Mail::mailer('smtp_fallback'),
                default => Mail::mailer(),
            };

            $mailer->html($renderedHtml, function ($message) use ($recipientEmail, $renderedSubject, $fromAddress, $fromName) {
                $message->to($recipientEmail)
                    ->subject($renderedSubject)
                    ->from($fromAddress, $fromName);
            });

            $diagnostics->recordSuccess("smtp_test:{$target}", $target);

            return [
                'success' => true,
                'mailer' => $target,
                'message' => "Test email dispatched successfully to {$recipientEmail} (mode: {$target})!",
            ];
        } catch (\Throwable $e) {
            Log::error("Test email dispatch failed ({$target}): ".$e->getMessage());
            $diagnosis = $diagnostics->recordFailure("smtp_test:{$target}", $e, $recipientEmail);

            return [
                'success' => false,
                'mailer' => $target,
                'message' => $diagnosis['title'].': '.$diagnosis['summary'],
                'diagnosis' => $diagnosis,
            ];
        }
    }
}
