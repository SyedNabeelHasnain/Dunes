<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        // 1. Seed Default System Subscriber Groups
        $groups = [
            [
                'name' => 'General Newsletter',
                'slug' => 'general-newsletter',
                'description' => 'Default subscriber audience opted-in via website footer, popups, and blog forms.',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Booked Guests',
                'slug' => 'booked-guests',
                'description' => 'Travelers who have completed a booking reservation with marketing consent.',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'VIP Travelers',
                'slug' => 'vip-travelers',
                'description' => 'High-value guests and private charter patrons eligible for bespoke premium alerts.',
                'is_system' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Inquiries & Leads',
                'slug' => 'inquiries-leads',
                'description' => 'Prospective customers who contacted support or WhatsApp concierge.',
                'is_system' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($groups as $g) {
            DB::table('subscriber_groups')->updateOrInsert(
                ['slug' => $g['slug']],
                $g
            );
        }

        // 2. Base Responsive Email Boilerplate Structure
        $makeHtml = function (string $headerBadge, string $headline, string $contentBody, ?string $ctaText = null, ?string $ctaUrl = null) {
            $ctaBlock = '';
            if ($ctaText && $ctaUrl) {
                $ctaBlock = '
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 32px 0;">
                    <tr>
                        <td align="center">
                            <a href="'.$ctaUrl.'" target="_blank" style="display: inline-block; padding: 16px 36px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; font-size: 15px; font-weight: 700; color: #ffffff; text-decoration: none; background: linear-gradient(135deg, #F58F43, #D97706); border-radius: 50px; box-shadow: 0 4px 14px rgba(245, 143, 67, 0.35); text-transform: uppercase; letter-spacing: 0.5px;">'.$ctaText.'</a>
                        </td>
                    </tr>
                </table>';
            }

            return '<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{subject}}</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; background-color: #0B1528; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; color: #1e293b; }
        @media screen and (max-width: 600px) {
            .email-container { width: 100% !important; margin: auto !important; }
            .content-padding { padding: 24px 20px !important; }
            .h1-title { font-size: 24px !important; line-height: 1.25 !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #0B1528;">
    <div style="display: none; font-size: 1px; line-height: 1px; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all;">
        {{preview_text}}
    </div>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0B1528;">
        <tr>
            <td align="center" style="padding: 40px 10px;">
                <!-- Main Card -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="email-container" style="max-width: 600px; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
                    <!-- Header Banner -->
                    <tr>
                        <td align="center" style="background: linear-gradient(135deg, #0B1528 0%, #172a4c 100%); padding: 36px 20px; border-bottom: 3px solid #F58F43;">
                            <a href="{{site_url}}" target="_blank" style="text-decoration: none;">
                                <h1 style="margin: 0; font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: 1px; text-transform: uppercase;">
                                    {{site_name}}
                                </h1>
                            </a>
                            <div style="display: inline-block; margin-top: 14px; padding: 4px 14px; background: rgba(245, 143, 67, 0.18); border: 1px solid rgba(245, 143, 67, 0.4); border-radius: 50px; font-size: 11px; font-weight: 700; color: #F58F43; text-transform: uppercase; letter-spacing: 1px;">
                                '.$headerBadge.'
                            </div>
                        </td>
                    </tr>
                    <!-- Body Content -->
                    <tr>
                        <td class="content-padding" style="padding: 40px 36px;">
                            <h2 class="h1-title" style="margin: 0 0 16px 0; font-size: 26px; font-weight: 800; color: #0F172A; line-height: 1.3;">
                                '.$headline.'
                            </h2>
                            <div style="font-size: 15px; line-height: 1.65; color: #475569;">
                                '.$contentBody.'
                            </div>
                            '.$ctaBlock.'
                            <div style="margin-top: 32px; padding-top: 20px; border-top: 1px solid #f1f5f9; font-size: 13px; color: #64748b;">
                                <p style="margin: 0 0 6px 0;">Warm regards,<br><strong style="color: #0f172a;">The Concierge Team</strong><br>{{site_name}}</p>
                                <p style="margin: 0;">WhatsApp 24/7: <a href="https://wa.me/{{site_whatsapp}}" style="color: #F58F43; text-decoration: none; font-weight: 600;">+{{site_whatsapp}}</a></p>
                            </div>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #080f1d; padding: 28px 30px; text-align: center; font-size: 12px; color: #94a3b8; line-height: 1.6;">
                            <p style="margin: 0 0 10px 0; color: #cbd5e1; font-weight: 600;">{{site_name}}</p>
                            <p style="margin: 0 0 12px 0;">{{company_address}}</p>
                            <p style="margin: 0 0 16px 0; color: #64748b;">You received this email because you subscribed to updates at <a href="{{site_url}}" style="color: #F58F43; text-decoration: none;">{{site_url}}</a>.</p>
                            <p style="margin: 0;">
                                <a href="{{unsubscribe_url}}" style="color: #94a3b8; text-decoration: underline;">Unsubscribe from this list</a> &bull;
                                <a href="{{site_url}}/privacy-policy" style="color: #94a3b8; text-decoration: underline;">Privacy Policy</a> &bull;
                                <a href="{{site_url}}/contact" style="color: #94a3b8; text-decoration: underline;">Contact Support</a>
                            </p>
                            <p style="margin: 12px 0 0 0; color: #475569; font-size: 11px;">&copy; {{current_year}} {{site_name}}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
        };

        // 3. Seed Default Starter Templates
        $templates = [
            [
                'name' => 'Welcome & VIP Voucher Alert',
                'slug' => 'welcome-vip-voucher',
                'subject' => 'Welcome to {{site_name}}! Here is your exclusive 25% discount',
                'preview_text' => 'Unlock your welcome bonus for premium Dubai desert safaris, dune buggy tours, and luxury dhow cruises.',
                'content_html' => $makeHtml(
                    'Exclusive Member Welcome',
                    'Welcome aboard, {{first_name}}!',
                    '<p>Thank you for subscribing to <strong>{{site_name}}</strong>! As our special welcome gift, enjoy an exclusive <strong>25% OFF</strong> on your upcoming desert adventure or city experience.</p>
                    <div style="margin: 24px 0; padding: 20px; background: #FFF7ED; border: 2px dashed #F58F43; border-radius: 12px; text-align: center;">
                        <span style="display: block; font-size: 12px; font-weight: 700; color: #9A3412; text-transform: uppercase; letter-spacing: 1px;">Your Private Promo Voucher</span>
                        <span style="display: inline-block; margin-top: 8px; font-size: 28px; font-weight: 900; letter-spacing: 3px; color: #C2410C;">FIRST25</span>
                        <span style="display: block; margin-top: 6px; font-size: 12px; color: #78350F;">Applies instantly at online checkout</span>
                    </div>
                    <p>Whether you dream of thrilling dune bashing across the Lahbab Red Dunes, renting high-powered 1000cc buggies, or an authentic 5-star live BBQ dinner under the Arabian stars, our licensed safari captains are ready to host you.</p>',
                    'Book Now & Redeem 25% Off',
                    '{{site_url}}?promo=FIRST25'
                ),
                'content_plain' => "Welcome to {{site_name}}, {{first_name}}!\n\nUse promo code FIRST25 to save 25% on your booking: {{site_url}}?promo=FIRST25\n\nUnsubscribe: {{unsubscribe_url}}",
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Monthly Travel & Safari Digest',
                'slug' => 'monthly-travel-digest',
                'subject' => '✨ Dubai Travel Insider: Top Desert Experiences This Season',
                'preview_text' => 'Discover sunset timing secrets, VIP camp upgrades, and seasonal desert highlights.',
                'content_html' => $makeHtml(
                    'Monthly Travel Digest',
                    'Hello {{first_name}}, discover what’s new in Dubai',
                    '<p>We’ve rounded up this month’s top highlights, seasonal desert recommendations, and insider travel tips for your UAE itinerary.</p>
                    <ul style="padding-left: 20px; margin: 20px 0;">
                        <li style="margin-bottom: 10px;"><strong>Red Dune Sunset Safari:</strong> The golden hour is breathtaking right now. Capture spectacular dune ridge photos followed by stargazing.</li>
                        <li style="margin-bottom: 10px;"><strong>VIP Air-Conditioned Majlis:</strong> Upgrade your camp experience with dedicated table service, premium live BBQ, and priority seating.</li>
                        <li style="margin-bottom: 10px;"><strong>Self-Drive 1000cc Buggies:</strong> Guided high-adrenaline trail runs across open desert terrain.</li>
                    </ul>
                    <p>Have specific dates in mind? Contact our concierge team anytime for personalized itinerary planning.</p>',
                    'Explore All Active Experiences',
                    '{{site_url}}/tours'
                ),
                'content_plain' => "Hello {{first_name}},\n\nCheck out the latest seasonal safari updates at {{site_url}}/tours\n\nUnsubscribe: {{unsubscribe_url}}",
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Flash Sale & Limited-Time Alert',
                'slug' => 'flash-sale-alert',
                'subject' => '⚡ 48-Hour Flash Sale: Special Rates on Selected Dubai Tours',
                'preview_text' => 'Limited spots available. Book your safari or city tour with instant confirmation.',
                'content_html' => $makeHtml(
                    'Limited-Time Flash Deal',
                    'Exclusive 48-Hour Special Offers',
                    '<p>Hi {{first_name}}, for the next 48 hours only, selected evening safari departures and private quad biking slots have been opened with special promotional pricing.</p>
                    <p>Spots are strictly limited per departure to ensure premium guest comfort and personalized captain attention.</p>
                    <div style="margin: 20px 0; padding: 16px; background: #F8FAFC; border-left: 4px solid #F58F43; border-radius: 8px;">
                        <p style="margin: 0; font-weight: 700; color: #0F172A;">All bookings include:</p>
                        <p style="margin: 4px 0 0 0; font-size: 14px; color: #475569;">✓ Luxury 4x4 Hotel Pick & Drop &bull; ✓ 100% Free Cancellation Guarantee &bull; ✓ Instant Digital Ticket</p>
                    </div>',
                    'Claim Flash Sale Rates',
                    '{{site_url}}'
                ),
                'content_plain' => "Hi {{first_name}},\n\n48-Hour Flash Sale is live now at {{site_url}}\n\nUnsubscribe: {{unsubscribe_url}}",
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Operational & Service Advisory',
                'slug' => 'service-advisory-notice',
                'subject' => '📢 Important Travel Advisory & Operational Update',
                'preview_text' => 'Please review this important announcement regarding upcoming tour departures and desert conditions.',
                'content_html' => $makeHtml(
                    'Important Service Notice',
                    'Tour Advisory for {{subscriber_name}}',
                    '<p>Dear {{first_name}},</p>
                    <p>This is an important operational notice regarding upcoming safari schedules, pickup guidelines, or seasonal weather advisories.</p>
                    <p>Our operations team monitors desert conditions 24 hours a day to guarantee the highest standards of safety, guest comfort, and entertainment.</p>
                    <p>If you have an active booking reference, our dispatch captains will contact you directly on WhatsApp 30–45 minutes prior to pickup to confirm your exact vehicle arrival.</p>',
                    'Contact Concierge Hotline',
                    '{{site_url}}/contact'
                ),
                'content_plain' => "Dear {{first_name}},\n\nPlease review this important travel advisory at {{site_url}}/contact\n\nUnsubscribe: {{unsubscribe_url}}",
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($templates as $t) {
            DB::table('email_templates')->updateOrInsert(
                ['slug' => $t['slug']],
                $t
            );
        }

        // 4. Seed Dynamic SMTP & Newsletter Settings
        if (! Schema::hasColumn('settings', 'description')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->text('description')->nullable();
            });
        }

        $settings = [
            ['setting_key' => 'smtp_driver', 'setting_value' => 'smtp', 'description' => 'Mail Transport Driver (smtp, sendmail, log)'],
            ['setting_key' => 'smtp_host', 'setting_value' => 'smtp.hostinger.com', 'description' => 'SMTP Host Server'],
            ['setting_key' => 'smtp_port', 'setting_value' => '465', 'description' => 'SMTP Server Port (465 for SSL, 587 for TLS)'],
            ['setting_key' => 'smtp_encryption', 'setting_value' => 'ssl', 'description' => 'SMTP Encryption Protocol (ssl, tls)'],
            ['setting_key' => 'smtp_username', 'setting_value' => '', 'description' => 'SMTP Authentication Username'],
            ['setting_key' => 'smtp_password', 'setting_value' => '', 'description' => 'SMTP Authentication Password'],
            ['setting_key' => 'smtp_from_address', 'setting_value' => 'info@dunesdiscoverytourism.com', 'description' => 'Default Mail From Email Address'],
            ['setting_key' => 'smtp_from_name', 'setting_value' => 'Dunes Discovery Tourism', 'description' => 'Default Mail From Sender Name'],
            ['setting_key' => 'smtp_reply_to', 'setting_value' => 'info@dunesdiscoverytourism.com', 'description' => 'Default Mail Reply-To Email Address'],
            ['setting_key' => 'newsletter_enabled', 'setting_value' => '1', 'description' => 'Global Newsletter & Email Marketing Active Toggle (1/0)'],
            ['setting_key' => 'newsletter_batch_size', 'setting_value' => '50', 'description' => 'Number of emails sent per campaign dispatch batch'],
            ['setting_key' => 'newsletter_batch_delay', 'setting_value' => '1', 'description' => 'Seconds delay between campaign email batches to respect host rate limits'],
        ];

        foreach ($settings as $s) {
            DB::table('settings')->updateOrInsert(
                ['setting_key' => $s['setting_key']],
                $s
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('subscriber_groups')->whereIn('slug', ['general-newsletter', 'booked-guests', 'vip-travelers', 'inquiries-leads'])->delete();
        DB::table('email_templates')->whereIn('slug', ['welcome-vip-voucher', 'monthly-travel-digest', 'flash-sale-alert', 'service-advisory-notice'])->delete();
        DB::table('settings')->whereIn('setting_key', [
            'smtp_driver', 'smtp_host', 'smtp_port', 'smtp_encryption',
            'smtp_username', 'smtp_password', 'smtp_from_address', 'smtp_from_name',
            'smtp_reply_to', 'newsletter_enabled', 'newsletter_batch_size', 'newsletter_batch_delay',
        ])->delete();
    }
};
