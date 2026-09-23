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
     * Dynamically apply SMTP configuration from database settings at runtime.
     */
    public function applySmtpConfig(): void
    {
        $driver = $this->settings->get('smtp_driver', config('mail.default', 'smtp'));
        $host = $this->settings->get('smtp_host', config('mail.mailers.smtp.host'));
        $port = (int) $this->settings->get('smtp_port', config('mail.mailers.smtp.port', 465));
        $encryption = $this->settings->get('smtp_encryption', config('mail.mailers.smtp.encryption', 'ssl'));
        $username = $this->settings->get('smtp_username', config('mail.mailers.smtp.username'));
        $password = $this->settings->get('smtp_password', config('mail.mailers.smtp.password'));
        $fromAddress = $this->settings->get('smtp_from_address', config('mail.from.address'));
        $fromName = $this->settings->get('smtp_from_name', config('mail.from.name', $this->settings->get('site_name', 'Dunes Discovery Tourism')));

        if (! empty($driver)) {
            Config::set('mail.default', $driver);
        }

        if ($driver === 'smtp' && ! empty($host)) {
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

        if (! empty($fromAddress)) {
            Config::set('mail.from.address', $fromAddress);
            Config::set('mail.from.name', $fromName);
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

        // 1. Mark campaign as sending
        $campaign->update([
            'status' => 'sending',
            'sent_at' => now(),
        ]);

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
     * Send a single diagnostic test email to any address.
     */
    public function sendTestEmail(string $recipientEmail, string $subject, string $htmlContent): array
    {
        $this->applySmtpConfig();

        $fromAddress = $this->settings->get('smtp_from_address', config('mail.from.address'));
        $fromName = $this->settings->get('smtp_from_name', $this->settings->get('site_name', 'Dunes Discovery Tourism'));

        // Dummy subscriber for preview tag parsing
        $dummySubscriber = new Subscriber([
            'email' => $recipientEmail,
            'first_name' => 'Sample',
            'last_name' => 'Recipient',
            'unsubscribe_token' => 'sample-test-token',
        ]);

        $renderedSubject = '[TEST] '.$this->parseTags($subject, $dummySubscriber);
        $renderedHtml = $this->renderHtml($htmlContent, $dummySubscriber);

        try {
            Mail::html($renderedHtml, function ($message) use ($recipientEmail, $renderedSubject, $fromAddress, $fromName) {
                $message->to($recipientEmail)
                    ->subject($renderedSubject)
                    ->from($fromAddress, $fromName);
            });

            return [
                'success' => true,
                'message' => "Test email dispatched successfully to {$recipientEmail}!",
            ];
        } catch (\Throwable $e) {
            Log::error('Test email dispatch failed: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Dispatch failed: '.$e->getMessage(),
            ];
        }
    }
}
