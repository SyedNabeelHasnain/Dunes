<?php

namespace Tests\Feature;

use App\Mail\BookingAdminNotification;
use App\Mail\BookingNotification;
use App\Mail\ContactAcknowledgement;
use App\Mail\ContactNotification;
use App\Mail\ReviewAdminNotification;
use App\Mail\WhatsappLeadNotification;
use App\Models\Booking;
use App\Models\Contact;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Tour;
use App\Models\User;
use App\Models\WhatsappInquiry;
use App\Services\NotificationManagementService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Tour $tour;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        app(SettingsService::class)->clearCache();

        $this->adminUser = User::first() ?: User::factory()->create();

        Setting::updateOrCreate(['setting_key' => 'site_name'], ['setting_value' => 'Dunes Discovery Tourism LLC']);
        Setting::updateOrCreate(['setting_key' => 'admin_email'], ['setting_value' => 'admin@dunesdiscoverytourism.com']);
        Setting::updateOrCreate(['setting_key' => 'site_email'], ['setting_value' => 'info@dunesdiscoverytourism.com']);

        $this->tour = Tour::create([
            'name' => 'Premium Evening Desert Safari Dubai',
            'slug' => 'premium-evening-desert-safari-dubai',
            'status' => 'active',
            'base_price' => 350.00,
            'duration' => '6 Hours',
        ]);
    }

    /**
     * Test 1: Booking confirmation dispatches both customer and admin notifications with PDF voucher attachment.
     */
    public function test_booking_confirmation_sends_customer_and_admin_notifications_with_pdf_attachments(): void
    {
        Mail::fake();

        $booking = Booking::create([
            'tour_id' => $this->tour->id,
            'reference' => 'DT-TEST-001',
            'tour_name' => $this->tour->name,
            'tour_date' => now()->addDays(2)->format('Y-m-d'),
            'name' => 'Sophia Martinez',
            'email' => 'sophia.martinez@example.com',
            'phone' => '+971 50 111 2233',
            'pickup_location' => 'Armani Hotel Dubai, Downtown',
            'adults' => 2,
            'children' => 0,
            'infants' => 0,
            'subtotal' => 700.00,
            'total' => 700.00,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_method' => 'card',
            'traffic_channel' => 'Google Ads',
        ]);

        $service = app(NotificationManagementService::class);
        $result = $service->notifyBookingConfirmed($booking);

        $this->assertTrue($result['customer_sent']);
        $this->assertTrue($result['admin_sent']);
        $this->assertTrue($result['pdf_attached']);

        Mail::assertQueued(BookingNotification::class, function ($mail) use ($booking) {
            $hasAttachment = count($mail->attachments()) > 0;
            return $mail->hasTo($booking->email) && $hasAttachment;
        });

        Mail::assertQueued(BookingAdminNotification::class, function ($mail) {
            $hasAttachment = count($mail->attachments()) > 0;
            return $mail->hasTo('admin@dunesdiscoverytourism.com') && $hasAttachment;
        });
    }

    /**
     * Test 2: Customer booking email can be toggled inactive in settings while admin alert still fires.
     */
    public function test_customer_booking_notification_can_be_disabled_via_settings(): void
    {
        Mail::fake();

        Setting::updateOrCreate(['setting_key' => 'notification_booking_customer_enabled'], ['setting_value' => '0']);
        app(SettingsService::class)->clearCache();

        $booking = Booking::create([
            'tour_id' => $this->tour->id,
            'reference' => 'DT-TEST-002',
            'tour_name' => $this->tour->name,
            'tour_date' => now()->addDays(3)->format('Y-m-d'),
            'name' => 'James Sterling',
            'email' => 'james.sterling@uk-travel.com',
            'phone' => '+44 7911 223344',
            'pickup_location' => 'Burj Al Arab Lobby',
            'adults' => 2,
            'total' => 700.00,
            'subtotal' => 700.00,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $service = app(NotificationManagementService::class);
        $result = $service->notifyBookingConfirmed($booking);

        $this->assertFalse($result['customer_sent']);
        $this->assertTrue($result['admin_sent']);

        Mail::assertNotQueued(BookingNotification::class);
        Mail::assertQueued(BookingAdminNotification::class);
    }

    /**
     * Test 3: PDF Voucher attachment can be toggled inactive for customer.
     */
    public function test_customer_voucher_pdf_attachment_can_be_disabled_via_settings(): void
    {
        Mail::fake();

        Setting::updateOrCreate(['setting_key' => 'notification_booking_customer_attach_pdf'], ['setting_value' => '0']);
        Setting::updateOrCreate(['setting_key' => 'notification_booking_admin_attach_pdf'], ['setting_value' => '0']);
        app(SettingsService::class)->clearCache();

        $booking = Booking::create([
            'tour_id' => $this->tour->id,
            'reference' => 'DT-TEST-003',
            'tour_name' => $this->tour->name,
            'tour_date' => now()->addDays(4)->format('Y-m-d'),
            'name' => 'Liam O\'Connor',
            'email' => 'liam.oconnor@dubai-visitor.ie',
            'phone' => '+353 87 123 4567',
            'pickup_location' => 'Atlantis The Palm',
            'adults' => 1,
            'total' => 350.00,
            'subtotal' => 350.00,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $service = app(NotificationManagementService::class);
        $result = $service->notifyBookingConfirmed($booking);

        $this->assertTrue($result['customer_sent']);
        $this->assertFalse($result['pdf_attached']);

        Mail::assertQueued(BookingNotification::class, function ($mail) {
            return count($mail->attachments()) === 0;
        });
    }

    /**
     * Test 4: WhatsApp Lead alert routes to dedicated multi-recipients with telemetry & 1-click CTA.
     */
    public function test_whatsapp_lead_notification_sends_to_multi_recipients_with_channel_and_cta(): void
    {
        Mail::fake();

        Setting::updateOrCreate(
            ['setting_key' => 'notification_whatsapp_recipients'],
            ['setting_value' => 'sales@dunesdiscoverytourism.com, manager@dunesdiscoverytourism.com']
        );
        app(SettingsService::class)->clearCache();

        $inquiry = WhatsappInquiry::create([
            'phone' => '+971 50 999 8877',
            'name' => 'Elena Petrova',
            'tour_name' => 'Sunrise Desert Safari & Camel Trekking',
            'page_url' => url('/safari-dubai'),
            'traffic_channel' => 'Google Ads',
            'city' => 'Dubai',
            'country' => 'United Arab Emirates',
            'device_type' => 'iPhone',
            'ref_code' => 'WA-TEST-99',
            'status' => 'pending',
        ]);

        $service = app(NotificationManagementService::class);
        $sent = $service->notifyWhatsappLead($inquiry);

        $this->assertTrue($sent);

        Mail::assertSent(WhatsappLeadNotification::class, function ($mail) {
            return $mail->hasTo('sales@dunesdiscoverytourism.com') &&
                   $mail->hasCc('manager@dunesdiscoverytourism.com') &&
                   $mail->trafficChannel === 'Google Ads' &&
                   $mail->directChatUrl === 'https://wa.me/971509998877';
        });
    }

    /**
     * Test 5: Contact inquiry sends admin notification and polite customer acknowledgement.
     */
    public function test_contact_inquiry_sends_admin_alert_and_customer_acknowledgement(): void
    {
        Mail::fake();

        $contact = Contact::create([
            'name' => 'Marcus Aurelius',
            'email' => 'marcus@rome-safaris.it',
            'phone' => '+39 06 99887766',
            'subject' => 'Corporate Buyout Desert Safari (60 Guests)',
            'message' => 'Please provide private desert camp rates for November 20th.',
            'status' => 'new',
            'traffic_channel' => 'Organic Search',
        ]);

        $service = app(NotificationManagementService::class);
        $result = $service->notifyContactInquiry($contact);

        $this->assertTrue($result['admin_sent']);
        $this->assertTrue($result['customer_sent']);

        Mail::assertSent(ContactNotification::class, function ($mail) use ($contact) {
            return $mail->hasTo('admin@dunesdiscoverytourism.com') &&
                   $mail->name === $contact->name;
        });

        Mail::assertSent(ContactAcknowledgement::class, function ($mail) use ($contact) {
            return $mail->hasTo($contact->email) &&
                   $mail->name === $contact->name;
        });
    }

    /**
     * Test 6: Customer UGC review sends quality alert to admin.
     */
    public function test_customer_ugc_review_sends_quality_alert_to_admin(): void
    {
        Mail::fake();

        $review = Review::create([
            'source' => 'direct_ugc',
            'reviewer_name' => 'Charlotte Dubois',
            'rating' => 5.0,
            'review_title' => 'Spectacular Dune Bashing and Stargazing',
            'review_text' => 'Driver Tariq was phenomenal! The food was authentic and the camp was immaculate.',
            'status' => 'approved',
            'published_date' => now()->toDateString(),
        ]);

        $service = app(NotificationManagementService::class);
        $sent = $service->notifyCustomerReview($review);

        $this->assertTrue($sent);

        Mail::assertSent(ReviewAdminNotification::class, function ($mail) use ($review) {
            return $mail->hasTo('admin@dunesdiscoverytourism.com') &&
                   $mail->review->reviewer_name === $review->reviewer_name;
        });
    }

    /**
     * Test 7: Admin can update all notification preferences via CMS panel.
     */
    public function test_admin_can_update_notification_settings_via_cms(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.settings.notifications.update'), [
                'notification_from_name' => 'Dunes Operations Command',
                'notification_from_email' => 'ops@dunesdiscoverytourism.com',
                'notification_reply_to' => 'helpdesk@dunesdiscoverytourism.com',
                'notification_booking_customer_enabled' => '1',
                'notification_booking_customer_attach_pdf' => '1',
                'notification_booking_admin_enabled' => '1',
                'notification_booking_admin_attach_pdf' => '1',
                'notification_booking_recipients' => 'res1@dunes.com, res2@dunes.com',
                'notification_whatsapp_admin_enabled' => '1',
                'notification_whatsapp_recipients' => 'sales@dunes.com',
                'notification_contact_admin_enabled' => '1',
                'notification_contact_recipients' => 'inquiries@dunes.com',
                'notification_contact_customer_enabled' => '1',
                'notification_review_admin_enabled' => '1',
                'notification_review_recipients' => 'quality@dunes.com',
                'notification_webhook_enabled' => '1',
                'notification_webhook_url' => 'https://hooks.slack.com/services/T00/B00/XXXX',
                'notification_webhook_secret' => 'whsec_secret_12345',
            ]);

        $response->assertRedirect(route('admin.settings.notifications'));
        $response->assertSessionHas('success');

        $this->assertEquals('Dunes Operations Command', Setting::where('setting_key', 'notification_from_name')->value('setting_value'));
        $this->assertEquals('ops@dunesdiscoverytourism.com', Setting::where('setting_key', 'notification_from_email')->value('setting_value'));
        $this->assertEquals('res1@dunes.com, res2@dunes.com', Setting::where('setting_key', 'notification_booking_recipients')->value('setting_value'));
        $this->assertEquals('1', Setting::where('setting_key', 'notification_webhook_enabled')->value('setting_value'));
        $this->assertEquals('whsec_secret_12345', Setting::where('setting_key', 'notification_webhook_secret')->value('setting_value'));
    }

    /**
     * Test 8: Admin can trigger live diagnostic test notifications via AJAX.
     */
    public function test_admin_can_dispatch_live_diagnostic_test_notifications(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.settings.notifications.test'), [
                'event' => 'booking_customer',
                'test_email' => 'qa-tester@dunesdiscoverytourism.com',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'pdf_attached' => true,
        ]);

        Mail::assertQueued(BookingNotification::class, function ($mail) {
            return $mail->hasTo('qa-tester@dunesdiscoverytourism.com') &&
                   count($mail->attachments()) > 0;
        });
    }
}
