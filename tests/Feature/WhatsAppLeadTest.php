<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Models\WhatsappInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WhatsAppLeadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Mail::fake();

        Setting::updateOrCreate(['setting_key' => 'site_whatsapp'], ['setting_value' => '971502456056']);
        Setting::updateOrCreate(['setting_key' => 'whatsapp_form_enabled'], ['setting_value' => '1']);
    }

    public function test_whatsapp_lead_can_be_logged_via_ajax_gateway(): void
    {
        $response = $this->post('/ajax.php', [
            'action' => 'logWhatsApp',
            'name' => 'John Doe',
            'phone' => '+971501234567',
            'tour_name' => 'Premium Evening Desert Safari Dubai',
            'page_url' => 'http://localhost/evening-desert-safari-dubai',
            'message_text' => 'Hi Dunes Discovery Tourism, I would like to inquire about Premium Evening Desert Safari Dubai.',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $json = $response->json();
        $this->assertNotEmpty($json['redirect_url']);
        $this->assertStringContainsString('https://wa.me/971502456056', $json['redirect_url']);
        $this->assertStringContainsString(urlencode('Premium Evening Desert Safari Dubai'), $json['redirect_url']);

        $this->assertDatabaseHas('whatsapp_inquiries', [
            'name' => 'John Doe',
            'phone' => '+971501234567',
            'tour_name' => 'Premium Evening Desert Safari Dubai',
        ]);
    }

    public function test_whatsapp_lead_can_be_logged_when_bypassed_or_empty_phone(): void
    {
        $response = $this->post('/ajax.php', [
            'action' => 'logWhatsApp',
            'name' => 'N/A',
            'phone' => 'N/A',
            'tour_name' => 'General Inquiry',
            'page_url' => 'http://localhost/',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $json = $response->json();
        $this->assertNotEmpty($json['redirect_url']);
        $this->assertStringContainsString('https://wa.me/971502456056', $json['redirect_url']);

        $this->assertDatabaseHas('whatsapp_inquiries', [
            'name' => 'N/A',
            'phone' => 'N/A',
            'tour_name' => 'General Inquiry',
        ]);
    }

    public function test_frontend_renders_whatsapp_globals_and_modal(): void
    {
        Setting::updateOrCreate(['setting_key' => 'whatsapp_form_enabled'], ['setting_value' => '1']);
        Cache::forget('site_settings_cache');

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('window.WHATSAPP_FORM_ENABLED = "1"', false);
        $response->assertSee('window.WHATSAPP_NUMBER = "971502456056"', false);
        $response->assertSee('id="whatsappModal"', false);
        $response->assertSee('id="whatsappForm"', false);
        $response->assertSee('id="startChatBtn"', false);
        $response->assertSee('id="waName"', false);
        $response->assertSee('id="waPhone"', false);
    }

    public function test_admin_can_toggle_whatsapp_form_enabled_setting(): void
    {
        $admin = User::first();
        if (! $admin) {
            $admin = User::factory()->create([
                'email' => 'admin@dunesdiscoverytourism.com',
                'role' => 'admin',
            ]);
        }

        $this->actingAs($admin);

        // Disable form
        $response = $this->post(route('admin.whatsapp.settings.update'), [
            'site_whatsapp' => '971502456056',
            'whatsapp_default_country' => '971',
            'whatsapp_form_enabled' => '0',
        ]);

        $response->assertRedirect(route('admin.whatsapp.settings'));

        $this->assertEquals('0', Setting::where('setting_key', 'whatsapp_form_enabled')->value('setting_value'));

        // Enable form
        $response2 = $this->post(route('admin.whatsapp.settings.update'), [
            'site_whatsapp' => '971502456056',
            'whatsapp_default_country' => '971',
            'whatsapp_form_enabled' => '1',
        ]);

        $response2->assertRedirect(route('admin.whatsapp.settings'));

        $this->assertEquals('1', Setting::where('setting_key', 'whatsapp_form_enabled')->value('setting_value'));
    }

    public function test_admin_whatsapp_index_renders_lead_with_numeric_phone_and_details_modal_markup(): void
    {
        $admin = User::first() ?: User::factory()->create([
            'email' => 'admin@dunesdiscoverytourism.com',
            'role' => 'admin',
        ]);

        $lead = WhatsappInquiry::create([
            'name' => 'Sara Al Nuaimi',
            'phone' => '971501234567',
            'tour_name' => 'VIP Evening Red Dunes Safari',
            'page_url' => 'http://localhost/vip-evening-safari',
            'message_text' => 'Interested in VIP booking for 4 guests',
            'device_type' => 'mobile',
            'city' => 'Dubai',
            'country' => 'AE',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.whatsapp.leads'));
        $response->assertStatus(200);
        $response->assertSee('Sara Al Nuaimi');
        $response->assertSee('data-phone="971501234567"', false);
        // Verify defensive String coercion is present in the page scripts
        $response->assertSee('const cleanPhone = String(phone).replace(/[^0-9]/g, \'\');', false);
    }

    public function test_whatsapp_bypassed_lead_persists_telemetry_and_ref_code(): void
    {
        $response = $this->post('/ajax.php', [
            'action' => 'logWhatsApp',
            'name' => 'N/A',
            'phone' => 'N/A',
            'tour_name' => 'Overnight Desert Safari Dubai',
            'page_url' => 'http://localhost/overnight-desert-safari-dubai',
            'message_text' => 'Hello, I would like to inquire about Overnight Desert Safari Dubai.',
            'ref_code' => 'WA-9821',
            'screen_res' => '390x844',
            'client_timezone' => 'Asia/Dubai',
            'client_language' => 'en-US',
            'utm_source' => 'Google Ads',
            'utm_campaign' => 'Desert Safari Campaign',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'ref_code' => 'WA-9821',
        ]);

        $json = $response->json();
        $this->assertStringContainsString('WA-9821', $json['redirect_url']);

        $this->assertDatabaseHas('whatsapp_inquiries', [
            'tour_name' => 'Overnight Desert Safari Dubai',
            'screen_res' => '390x844',
            'client_timezone' => 'Asia/Dubai',
            'utm_source' => 'Google Ads',
            'utm_campaign' => 'Desert Safari Campaign',
        ]);
    }

    public function test_whatsapp_lead_resolves_first_party_prospect_profile(): void
    {
        $response = $this->post('/ajax.php', [
            'action' => 'logWhatsApp',
            'name' => 'N/A',
            'phone' => 'N/A',
            'prospect_name' => 'Michael Scott',
            'prospect_phone' => '+971551234567',
            'prospect_email' => 'michael@dundermifflin.com',
            'tour_name' => 'Dune Buggy Tour Dubai',
            'page_url' => 'http://localhost/dune-buggy-safari-dubai',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('whatsapp_inquiries', [
            'name' => 'Michael Scott',
            'phone' => '+971551234567',
            'email' => 'michael@dundermifflin.com',
            'prospect_source' => 'Stored Browser Profile',
        ]);
    }

    public function test_whatsapp_lead_resolves_prior_booking_prospect(): void
    {
        // Seed a prior booking with the same IP
        \App\Models\Booking::create([
            'reference' => 'DDT-PR-12345',
            'name' => 'Fatima Al Mansoori',
            'email' => 'fatima@example.com',
            'phone' => '+971509998888',
            'tour_name' => 'Morning Desert Safari Dubai',
            'tour_date' => now()->addDays(2)->format('Y-m-d'),
            'adults' => 2,
            'children' => 0,
            'subtotal' => 500,
            'total' => 500,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_method' => 'card',
            'ip_address' => '198.51.100.99',
        ]);

        $response = $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.99'])->post('/ajax.php', [
            'action' => 'logWhatsApp',
            'name' => 'N/A',
            'phone' => 'N/A',
            'tour_name' => 'Morning Desert Safari Dubai',
            'page_url' => 'http://localhost/morning-safari',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('whatsapp_inquiries', [
            'name' => 'Fatima Al Mansoori',
            'phone' => '+971509998888',
            'email' => 'fatima@example.com',
            'prospect_source' => 'Prior Booking History',
        ]);
    }

    public function test_whatsapp_lead_persists_marketing_journey_and_channel_attribution(): void
    {
        $timeline = [
            [
                'step' => 1,
                'url' => '/tours/evening-desert-safari',
                'title' => 'Evening Desert Safari Dubai',
                'duration_seconds' => 95,
                'sections' => ['Itinerary Timeline', 'Pricing Tiers & Packages'],
                'timestamp' => now()->subMinutes(3)->toIso8601String(),
            ],
            [
                'step' => 2,
                'url' => '/build-your-own-safari',
                'title' => 'Build Your Own Desert Safari',
                'duration_seconds' => 120,
                'sections' => ['Custom Safari Builder'],
                'timestamp' => now()->subMinutes(1)->toIso8601String(),
            ],
            [
                'step' => 3,
                'url' => '/tours/vip-desert-safari',
                'title' => 'VIP Red Dunes Desert Safari',
                'duration_seconds' => 45,
                'sections' => ['VIP Camp Experience'],
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        $response = $this->post('/ajax.php', [
            'action' => 'logWhatsApp',
            'name' => 'Rashid Al Maktoum',
            'phone' => '+971501112233',
            'email' => 'rashid@example.ae',
            'tour_name' => 'VIP Red Dunes Desert Safari',
            'page_url' => 'http://localhost/tours/vip-desert-safari',
            'traffic_channel' => 'Google Ads (Desert Safari Campaign)',
            'initial_referrer' => 'https://www.google.com/',
            'landing_page' => '/tours/evening-desert-safari',
            'session_duration_seconds' => 260,
            'pages_viewed_count' => 3,
            'journey_timeline' => json_encode($timeline),
            'gclid' => 'EAIaIQobChMI1234567890',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('whatsapp_inquiries', [
            'name' => 'Rashid Al Maktoum',
            'phone' => '+971501112233',
            'email' => 'rashid@example.ae',
            'traffic_channel' => 'Google Ads (Desert Safari Campaign)',
            'initial_referrer' => 'https://www.google.com/',
            'landing_page' => '/tours/evening-desert-safari',
            'session_duration_seconds' => 260,
            'pages_viewed_count' => 3,
            'gclid' => 'EAIaIQobChMI1234567890',
        ]);

        $inquiry = WhatsappInquiry::where('phone', '+971501112233')->first();
        $this->assertNotNull($inquiry);
        $this->assertIsArray($inquiry->journey_timeline);
        $this->assertCount(3, $inquiry->journey_timeline);
        $this->assertEquals('Evening Desert Safari Dubai', $inquiry->journey_timeline[0]['title']);
        $this->assertEquals(['VIP Camp Experience'], $inquiry->journey_timeline[2]['sections']);
    }

    public function test_admin_whatsapp_index_renders_journey_timeline_and_channel_badge(): void
    {
        $admin = User::first() ?: User::factory()->create([
            'email' => 'admin@dunesdiscoverytourism.com',
            'role' => 'admin',
        ]);

        $lead = WhatsappInquiry::create([
            'name' => 'Elena Rostova',
            'phone' => '971529876543',
            'tour_name' => 'Quad Biking Desert Safari',
            'page_url' => 'http://localhost/quad-biking-safari',
            'traffic_channel' => 'Instagram',
            'initial_referrer' => 'https://l.instagram.com/',
            'landing_page' => '/tours/quad-biking',
            'session_duration_seconds' => 195,
            'pages_viewed_count' => 2,
            'device_type' => 'mobile',
            'city' => 'Dubai',
            'country' => 'AE',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.whatsapp.leads'));
        $response->assertStatus(200);
        $response->assertSee('Elena Rostova');
        $response->assertSee('Instagram');
        $response->assertSee('data-traffic-channel="Instagram"', false);
        $response->assertSee('data-initial-referrer="https://l.instagram.com/"', false);
        $response->assertSee('data-duration="195"', false);
        $response->assertSee('Buyer Journey, Multi-Page Navigation & Dwell Time', false);
    }

    public function test_admin_whatsapp_csv_export_includes_journey_columns(): void
    {
        $admin = User::first() ?: User::factory()->create([
            'email' => 'admin@dunesdiscoverytourism.com',
            'role' => 'admin',
        ]);

        WhatsappInquiry::create([
            'name' => 'David Miller',
            'phone' => '971508887777',
            'tour_name' => 'Dune Buggy Safari',
            'page_url' => 'http://localhost/buggy-safari',
            'traffic_channel' => 'Google Ads',
            'initial_referrer' => 'https://www.google.com/',
            'landing_page' => '/tours/buggy-safari',
            'session_duration_seconds' => 180,
            'pages_viewed_count' => 2,
            'gclid' => 'GCLID-TEST-XYZ',
            'device_type' => 'desktop',
            'city' => 'Abu Dhabi',
            'country' => 'AE',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.whatsapp.export'));
        $response->assertStatus(200);

        // Streamed CSV content check
        $content = $response->streamedContent();
        $this->assertStringContainsString('Traffic Channel', $content);
        $this->assertStringContainsString('Initial Referrer', $content);
        $this->assertStringContainsString('Landing Page', $content);
        $this->assertStringContainsString('Stay Duration (Sec)', $content);
        $this->assertStringContainsString('Google Ads GCLID', $content);
        $this->assertStringContainsString('David Miller', $content);
        $this->assertStringContainsString('Google Ads', $content);
        $this->assertStringContainsString('GCLID-TEST-XYZ', $content);
    }
}
