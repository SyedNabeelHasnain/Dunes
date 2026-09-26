<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Tour;
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
        if (!$admin) {
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
}
