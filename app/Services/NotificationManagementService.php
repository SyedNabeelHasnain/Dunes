<?php

namespace App\Services;

use App\Mail\BookingAdminNotification;
use App\Mail\BookingNotification;
use App\Mail\ContactAcknowledgement;
use App\Mail\ContactNotification;
use App\Mail\ReviewAdminNotification;
use App\Mail\WhatsappLeadNotification;
use App\Models\Booking;
use App\Models\Contact;
use App\Models\Review;
use App\Models\WhatsappInquiry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class NotificationManagementService
{
    protected SettingsService $settings;

    protected EmailMarketingService $emailMarketing;

    public function __construct(SettingsService $settings, EmailMarketingService $emailMarketing)
    {
        $this->settings = $settings;
        $this->emailMarketing = $emailMarketing;
    }

    /**
     * Apply runtime SMTP configurations from CMS settings.
     */
    protected function prepareMailEnvironment(): void
    {
        if (! app()->environment('testing')) {
            try {
                $this->emailMarketing->applySmtpConfig();
            } catch (\Throwable $e) {
                Log::warning('Failed applying dynamic SMTP settings: ' . $e->getMessage());
            }
        }
    }

    /**
     * Resolve sender identity from notification settings with site fallback.
     */
    public function getSenderIdentity(): array
    {
        $name = $this->settings->get('notification_from_name') 
            ?: ($this->settings->get('smtp_from_name') 
            ?: ($this->settings->get('site_name', 'Dunes Discovery Tourism')));

        $email = $this->settings->get('notification_from_email') 
            ?: ($this->settings->get('smtp_from_address') 
            ?: $this->settings->getFromEmail());

        $replyTo = $this->settings->get('notification_reply_to') 
            ?: ($this->settings->get('smtp_reply_to') ?: $email);

        return [
            'name' => $name,
            'email' => $email,
            'reply_to' => $replyTo,
        ];
    }

    /**
     * Parse multiple recipient emails for a given alert event.
     */
    public function resolveEventRecipients(string $settingKey, ?string $legacyFallback = null): array
    {
        $dedicated = $this->settings->get($settingKey, '');
        if (! empty($dedicated)) {
            $parsed = array_filter(array_map('trim', explode(',', $dedicated)));
            if (! empty($parsed)) {
                return array_values(array_unique($parsed));
            }
        }

        if ($legacyFallback && ! empty($legacyFallback)) {
            return [$legacyFallback];
        }

        return [$this->settings->getAdminEmail()];
    }

    /**
     * Generate official A4 PDF Ticket Voucher directly in-memory (0 disk files).
     */
    public function generateVoucherPdfBinary(Booking $booking): ?string
    {
        try {
            if (! $booking->relationLoaded('addons') || ! $booking->relationLoaded('tier') || ! $booking->relationLoaded('tour')) {
                $booking->loadMissing(['addons', 'tier', 'tour']);
            }

            $verificationUrl = route('booking.voucher', $booking->reference);
            $qrSvg = null;
            $qrCodeUrl = null;

            try {
                $qrSvg = (string) QrCode::format('svg')->size(140)->margin(1)->generate($verificationUrl);
                $qrCodeUrl = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
            } catch (\Throwable $qrEx) {
                Log::warning("QR generation skipped for in-memory voucher PDF #{$booking->reference}: " . $qrEx->getMessage());
            }

            $pdf = Pdf::loadView('booking.ticket-pdf', compact('booking', 'qrCodeUrl', 'qrSvg'))
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isRemoteEnabled' => false,
                    'isHtml5ParserEnabled' => true,
                    'dpi' => 120,
                    'defaultFont' => 'sans-serif',
                ]);

            return $pdf->output();
        } catch (\Throwable $e) {
            Log::error("In-memory Voucher PDF generation failed for booking #{$booking->reference}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * 1. Dispatch Successful Booking Confirmation Notifications (Customer & Admin with PDF Attachment).
     */
    public function notifyBookingConfirmed(Booking $booking, string $type = 'booking_confirmed'): array
    {
        $this->prepareMailEnvironment();
        $sender = $this->getSenderIdentity();
        $result = [
            'customer_sent' => false,
            'admin_sent' => false,
            'pdf_attached' => false,
        ];

        // 1. In-memory PDF Voucher Generation
        $pdfBinary = null;
        $pdfFileName = 'Dunes-Discovery-Ticket-Voucher-' . $booking->reference . '.pdf';

        $customerPdfEnabled = $this->settings->get('notification_booking_customer_attach_pdf', '1') === '1';
        $adminPdfEnabled = $this->settings->get('notification_booking_admin_attach_pdf', '1') === '1';

        if (($customerPdfEnabled || $adminPdfEnabled) && $type !== 'booking_cancelled') {
            $pdfBinary = $this->generateVoucherPdfBinary($booking);
            if ($pdfBinary) {
                $result['pdf_attached'] = true;
            }
        }

        // 2. Dispatch Customer Confirmation Email
        $customerEnabled = $this->settings->get('notification_booking_customer_enabled', '1') === '1';
        if ($customerEnabled && ! empty($booking->email) && ! str_ends_with($booking->email, '@guest.local')) {
            try {
                $customerMail = (new BookingNotification($type, $booking))
                    ->from($sender['email'], $sender['name'])
                    ->replyTo($sender['reply_to'], $sender['name']);

                if ($customerPdfEnabled && $pdfBinary) {
                    $customerMail->withPdfAttachment($pdfBinary, $pdfFileName);
                }

                Mail::to($booking->email)->send($customerMail);
                $result['customer_sent'] = true;
            } catch (\Throwable $e) {
                Log::error("Failed sending customer booking email for {$booking->reference}: " . $e->getMessage());
            }
        }

        // 3. Dispatch Admin Booking Alert
        $adminEnabled = $this->settings->get('notification_booking_admin_enabled', '1') === '1';
        if ($adminEnabled) {
            try {
                $recipients = $this->resolveEventRecipients('notification_booking_recipients');
                $primaryRecipient = array_shift($recipients) ?: $this->settings->getAdminEmail();

                $adminMail = (new BookingAdminNotification($booking))
                    ->from($sender['email'], $sender['name'])
                    ->replyTo($booking->email ?: $sender['reply_to'], $booking->name ?: 'Guest');

                if ($adminPdfEnabled && $pdfBinary) {
                    $adminMail->withPdfAttachment($pdfBinary, $pdfFileName);
                }

                $ccList = array_merge($recipients, $this->settings->getCcEmails());
                if (! empty($ccList)) {
                    $adminMail->cc(array_values(array_unique($ccList)));
                }

                $bccList = $this->settings->getBccEmails();
                if (! empty($bccList)) {
                    $adminMail->bcc(array_values(array_unique($bccList)));
                }

                Mail::to($primaryRecipient)->send($adminMail);
                $result['admin_sent'] = true;
            } catch (\Throwable $e) {
                Log::error("Failed sending admin booking alert for {$booking->reference}: " . $e->getMessage());
            }
        }

        // 4. Outgoing Webhook Trigger
        $this->dispatchWebhook('booking.created', [
            'reference' => $booking->reference,
            'tour_name' => $booking->tour_name,
            'guest_name' => $booking->name,
            'guest_phone' => $booking->phone,
            'guest_email' => $booking->email,
            'total' => (float) $booking->total,
            'status' => $booking->status,
            'payment_status' => $booking->payment_status,
            'traffic_channel' => $booking->traffic_channel ?: 'Direct',
            'created_at' => now()->toIso8601String(),
        ]);

        return $result;
    }

    /**
     * 2. Dispatch WhatsApp Lead Notification to Admin Team.
     */
    public function notifyWhatsappLead(WhatsappInquiry $inquiry): bool
    {
        $enabled = $this->settings->get('notification_whatsapp_admin_enabled', '1') === '1';
        if (! $enabled) {
            return false;
        }

        $this->prepareMailEnvironment();
        $sender = $this->getSenderIdentity();

        try {
            $recipients = $this->resolveEventRecipients('notification_whatsapp_recipients', $this->settings->get('site_email'));
            $primaryRecipient = array_shift($recipients) ?: $this->settings->getAdminEmail();

            $location = implode(', ', array_filter([$inquiry->city, $inquiry->country])) ?: 'Unknown Location';

            $mail = (new WhatsappLeadNotification(
                name: $inquiry->name ?: 'Prospective Guest',
                phone: $inquiry->phone ?: 'N/A',
                tourName: $inquiry->tour_name ?: 'General Inquiry',
                pageUrl: $inquiry->page_url ?: url('/'),
                messageText: $inquiry->message_text ?: 'Customer initiated WhatsApp conversation.',
                trafficChannel: $inquiry->traffic_channel ?: 'Direct',
                location: $location,
                deviceType: $inquiry->device_type ?: 'Mobile',
                refCode: $inquiry->ref_code ?? ('WA-' . $inquiry->id),
                inquiryId: (int) $inquiry->id
            ))
            ->from($sender['email'], $sender['name'])
            ->replyTo($sender['reply_to'], $sender['name']);

            $ccList = array_merge($recipients, $this->settings->getCcEmails());
            if (! empty($ccList)) {
                $mail->cc(array_values(array_unique($ccList)));
            }

            $bccList = $this->settings->getBccEmails();
            if (! empty($bccList)) {
                $mail->bcc(array_values(array_unique($bccList)));
            }

            Mail::to($primaryRecipient)->send($mail);

            // Outgoing Webhook Trigger
            $this->dispatchWebhook('whatsapp.lead', [
                'id' => $inquiry->id,
                'name' => $inquiry->name,
                'phone' => $inquiry->phone,
                'tour_name' => $inquiry->tour_name,
                'traffic_channel' => $inquiry->traffic_channel,
                'location' => $location,
                'message' => $inquiry->message_text,
                'created_at' => now()->toIso8601String(),
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error("Failed to send WhatsApp lead email for lead #{$inquiry->id}: " . $e->getMessage());

            return false;
        }
    }

    /**
     * 3. Dispatch Contact Form Inquiry Notifications (Admin Alert & Customer Acknowledgement).
     */
    public function notifyContactInquiry(Contact $contact): array
    {
        $this->prepareMailEnvironment();
        $sender = $this->getSenderIdentity();
        $result = [
            'admin_sent' => false,
            'customer_sent' => false,
        ];

        // 1. Admin Alert
        $adminEnabled = $this->settings->get('notification_contact_admin_enabled', '1') === '1';
        if ($adminEnabled) {
            try {
                $recipients = $this->resolveEventRecipients('notification_contact_recipients');
                $primaryRecipient = array_shift($recipients) ?: $this->settings->getAdminEmail();

                $adminMail = (new ContactNotification(
                    $contact->name,
                    $contact->email,
                    $contact->phone,
                    $contact->subject ?: 'Contact Inquiry',
                    $contact->message
                ))
                ->from($sender['email'], $sender['name'])
                ->replyTo($contact->email, $contact->name);

                $ccList = array_merge($recipients, $this->settings->getCcEmails());
                if (! empty($ccList)) {
                    $adminMail->cc(array_values(array_unique($ccList)));
                }

                $bccList = $this->settings->getBccEmails();
                if (! empty($bccList)) {
                    $adminMail->bcc(array_values(array_unique($bccList)));
                }

                Mail::to($primaryRecipient)->send($adminMail);
                $result['admin_sent'] = true;
            } catch (\Throwable $e) {
                Log::error("Failed sending admin contact notification for inquiry #{$contact->id}: " . $e->getMessage());
            }
        }

        // 2. Customer Auto-Acknowledgement
        $customerEnabled = $this->settings->get('notification_contact_customer_enabled', '1') === '1';
        if ($customerEnabled && ! empty($contact->email) && filter_var($contact->email, FILTER_VALIDATE_EMAIL)) {
            try {
                $ackMail = (new ContactAcknowledgement($contact->name))
                    ->from($sender['email'], $sender['name'])
                    ->replyTo($sender['reply_to'], $sender['name']);

                Mail::to($contact->email)->send($ackMail);
                $result['customer_sent'] = true;
            } catch (\Throwable $e) {
                Log::error("Failed sending customer contact acknowledgement to {$contact->email}: " . $e->getMessage());
            }
        }

        // 3. Outgoing Webhook Trigger
        $this->dispatchWebhook('contact.submitted', [
            'id' => $contact->id,
            'name' => $contact->name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'subject' => $contact->subject,
            'message' => $contact->message,
            'traffic_channel' => $contact->traffic_channel ?: 'Direct',
            'created_at' => now()->toIso8601String(),
        ]);

        return $result;
    }

    /**
     * 4. Dispatch Customer Review & Guest Feedback Alerts to Admin Quality Control Team.
     */
    public function notifyCustomerReview(Review $review, ?Booking $booking = null, string $type = 'review'): bool
    {
        $enabled = $this->settings->get('notification_review_admin_enabled', '1') === '1';
        if (! $enabled) {
            return false;
        }

        $this->prepareMailEnvironment();
        $sender = $this->getSenderIdentity();

        try {
            $recipients = $this->resolveEventRecipients('notification_review_recipients');
            $primaryRecipient = array_shift($recipients) ?: $this->settings->getAdminEmail();

            $mail = (new ReviewAdminNotification($review, $booking, $type))
                ->from($sender['email'], $sender['name'])
                ->replyTo($booking?->email ?: $sender['reply_to'], $review->reviewer_name);

            $ccList = array_merge($recipients, $this->settings->getCcEmails());
            if (! empty($ccList)) {
                $mail->cc(array_values(array_unique($ccList)));
            }

            $bccList = $this->settings->getBccEmails();
            if (! empty($bccList)) {
                $mail->bcc(array_values(array_unique($bccList)));
            }

            Mail::to($primaryRecipient)->send($mail);

            // Outgoing Webhook Trigger
            $this->dispatchWebhook('review.submitted', [
                'id' => $review->id,
                'reviewer_name' => $review->reviewer_name,
                'rating' => $review->rating,
                'title' => $review->review_title,
                'text' => $review->review_text,
                'status' => $review->status,
                'booking_reference' => $booking?->reference,
                'created_at' => now()->toIso8601String(),
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error("Failed to send review alert for review #{$review->id}: " . $e->getMessage());

            return false;
        }
    }

    /**
     * 5. Dispatch External Webhook notification (Slack, Discord, Zapier, Make, CRM).
     */
    public function dispatchWebhook(string $event, array $payload): void
    {
        $enabled = $this->settings->get('notification_webhook_enabled', '0') === '1';
        $url = trim((string) $this->settings->get('notification_webhook_url', ''));

        if (! $enabled || empty($url)) {
            return;
        }

        try {
            $secret = trim((string) $this->settings->get('notification_webhook_secret', ''));
            $body = [
                'event' => $event,
                'site' => 'Dunes Discovery Tourism LLC',
                'license' => '1430583',
                'timestamp' => now()->toIso8601String(),
                'data' => $payload,
            ];
            $jsonBody = json_encode($body);

            $headers = [
                'Content-Type' => 'application/json',
                'User-Agent' => 'DunesDiscoveryTourism-Webhook/1.0',
            ];

            if (! empty($secret)) {
                $headers['X-Dunes-Signature'] = hash_hmac('sha256', $jsonBody, $secret);
            }

            Http::timeout(5)
                ->withHeaders($headers)
                ->post($url, $body);
        } catch (\Throwable $e) {
            Log::warning("Failed to dispatch outgoing notification webhook ({$event}): " . $e->getMessage());
        }
    }

    /**
     * 6. Live Test Notification Dispatcher for Admin Verification.
     */
    public function sendTestNotification(string $event, string $recipientEmail): array
    {
        $this->prepareMailEnvironment();
        $sender = $this->getSenderIdentity();

        try {
            switch ($event) {
                case 'booking_customer':
                    $dummyBooking = new Booking([
                        'reference' => 'DT-TEST-' . strtoupper(substr(uniqid(), -5)),
                        'tour_name' => 'Premium Evening Desert Safari Dubai (VIP Test)',
                        'tier_name' => 'Private 4x4 Land Cruiser',
                        'tour_date' => now()->addDays(2)->format('Y-m-d'),
                        'name' => 'Honorable VIP Traveler',
                        'email' => $recipientEmail,
                        'phone' => '+971 50 123 4567',
                        'pickup_location' => 'Burj Al Arab Hotel Lobby',
                        'adults' => 2,
                        'children' => 1,
                        'infants' => 0,
                        'total' => 750.00,
                        'payment_status' => 'paid',
                        'status' => 'confirmed',
                    ]);
                    $dummyBooking->id = 999999;

                    $pdfBinary = $this->generateVoucherPdfBinary($dummyBooking);
                    $mail = (new BookingNotification('booking_confirmed', $dummyBooking))
                        ->from($sender['email'], $sender['name']);

                    if ($pdfBinary) {
                        $mail->withPdfAttachment($pdfBinary, 'Dunes-Discovery-Ticket-Voucher-' . $dummyBooking->reference . '.pdf');
                    }

                    Mail::to($recipientEmail)->send($mail);

                    return [
                        'success' => true,
                        'message' => "Test customer confirmation email with PDF voucher successfully dispatched to {$recipientEmail}!",
                        'pdf_attached' => ! empty($pdfBinary),
                    ];

                case 'booking_admin':
                    $dummyBooking = new Booking([
                        'reference' => 'DT-ADMIN-' . strtoupper(substr(uniqid(), -5)),
                        'tour_name' => 'Overnight Desert Safari & Bedouin Camp (Test)',
                        'tour_date' => now()->addDays(3)->format('Y-m-d'),
                        'name' => 'Alexander Wright',
                        'email' => 'alexander.wright@uk-travel.co.uk',
                        'phone' => '+44 7911 123456',
                        'pickup_location' => 'Atlantis The Royal, Palm Jumeirah',
                        'adults' => 4,
                        'children' => 0,
                        'total' => 1800.00,
                        'payment_status' => 'paid',
                        'status' => 'confirmed',
                    ]);
                    $dummyBooking->id = 999998;

                    $pdfBinary = $this->generateVoucherPdfBinary($dummyBooking);
                    $mail = (new BookingAdminNotification($dummyBooking))
                        ->from($sender['email'], $sender['name']);

                    if ($pdfBinary) {
                        $mail->withPdfAttachment($pdfBinary, 'Dunes-Discovery-Ticket-Voucher-' . $dummyBooking->reference . '.pdf');
                    }

                    Mail::to($recipientEmail)->send($mail);

                    return [
                        'success' => true,
                        'message' => "Test admin booking alert email with PDF voucher successfully dispatched to {$recipientEmail}!",
                        'pdf_attached' => ! empty($pdfBinary),
                    ];

                case 'whatsapp_lead':
                    $mail = (new WhatsappLeadNotification(
                        name: 'Elena Rostova (Test Lead)',
                        phone: '+7 916 123 4567',
                        tourName: 'Red Dunes Dune Buggy & VIP Safari',
                        pageUrl: url('/safari-dubai'),
                        messageText: 'Hello Dunes Discovery, checking availability for 4 passengers tomorrow.',
                        trafficChannel: 'Google Ads',
                        location: 'Dubai Marina, UAE',
                        deviceType: 'iPhone / Safari',
                        refCode: 'WA-TEST',
                        inquiryId: 777
                    ))->from($sender['email'], $sender['name']);

                    Mail::to($recipientEmail)->send($mail);

                    return [
                        'success' => true,
                        'message' => "Test WhatsApp Lead notification email successfully dispatched to {$recipientEmail}!",
                    ];

                case 'contact_inquiry':
                    $mail = (new ContactNotification(
                        'Marcus Aurelius (Corporate Group)',
                        'marcus@rome-holdings.com',
                        '+39 06 12345678',
                        'Corporate Team Building Desert Safari (50 Guests)',
                        'We would like a full buyout of the VIP desert camp on November 15th with private entertainment.'
                    ))->from($sender['email'], $sender['name']);

                    Mail::to($recipientEmail)->send($mail);

                    return [
                        'success' => true,
                        'message' => "Test Contact Inquiry notification email successfully dispatched to {$recipientEmail}!",
                    ];

                case 'customer_review':
                    $dummyReview = new Review([
                        'reviewer_name' => 'Sophie Laurent',
                        'rating' => 5.0,
                        'review_title' => 'The absolute highlight of our Dubai holiday!',
                        'review_text' => 'From the prompt hotel pickup in a pristine Land Cruiser to the thrilling dune bashing and delicious BBQ, everything was 5 stars!',
                        'status' => 'pending',
                    ]);
                    $dummyReview->id = 888;

                    $mail = (new ReviewAdminNotification($dummyReview, null))
                        ->from($sender['email'], $sender['name']);

                    Mail::to($recipientEmail)->send($mail);

                    return [
                        'success' => true,
                        'message' => "Test Customer Review notification email successfully dispatched to {$recipientEmail}!",
                    ];

                default:
                    return ['success' => false, 'message' => "Unrecognized test event type: {$event}"];
            }
        } catch (\Throwable $e) {
            Log::error("Test notification dispatch failed ({$event}): " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Notification dispatch failed: ' . $e->getMessage(),
            ];
        }
    }
}
