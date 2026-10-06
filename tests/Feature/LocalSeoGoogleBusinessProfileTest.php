<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalSeoGoogleBusinessProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic settings
        Setting::updateOrCreate(['setting_key' => 'site_name'], ['setting_value' => 'Dunes Discovery Tourism LLC']);
        Setting::updateOrCreate(['setting_key' => 'company_license_number'], ['setting_value' => '1430583']);
        Setting::updateOrCreate(['setting_key' => 'google_place_id'], ['setting_value' => 'ChIJbWsIEIVEdEER4uHEhb2dbcQ']);
        Setting::updateOrCreate(['setting_key' => 'google_cid'], ['setting_value' => '14185012580795441634']);
        Setting::updateOrCreate(['setting_key' => 'google_review_url'], ['setting_value' => 'https://search.google.com/local/writereview?placeid=ChIJbWsIEIVEdEER4uHEhb2dbcQ']);
        Setting::updateOrCreate(['setting_key' => 'site_latitude'], ['setting_value' => '25.2048']);
        Setting::updateOrCreate(['setting_key' => 'site_longitude'], ['setting_value' => '55.2708']);
        Setting::updateOrCreate(['setting_key' => 'site_postal_code'], ['setting_value' => '00000']);
        Setting::updateOrCreate(['setting_key' => 'google_business_hours'], ['setting_value' => 'Monday - Sunday: 24 Hours (00:00 - 23:59)']);
        Setting::updateOrCreate(['setting_key' => 'google_service_area'], ['setting_value' => 'Dubai, Sharjah, Ajman, Lahbab Desert, Al Marmoom, United Arab Emirates']);
        Setting::updateOrCreate(['setting_key' => 'google_primary_category'], ['setting_value' => 'Tour Operator']);
    }

    /**
     * Test 1: Admin can view Google Business Profile & Local SEO management screen
     */
    public function test_admin_can_view_google_and_local_seo_settings_page(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.settings.google'));

        $response->assertStatus(200);
        $response->assertSee('Google Integration Suite', false);
        $response->assertSee('Google Business Profile & Local SEO Entity', false);
        $response->assertSee('ChIJbWsIEIVEdEER4uHEhb2dbcQ');
        $response->assertSee('14154142641213989346');
        $response->assertSee('Test Review Modal');
        $response->assertSee('Open Listing on Google Maps');
    }

    /**
     * Test 2: Admin can update Google Business Profile & Local SEO coordinates and Place ID
     */
    public function test_admin_can_update_google_business_profile_settings(): void
    {
        $admin = User::factory()->create();

        $postData = [
            'google_active' => '1',
            'google_place_id' => 'ChIJ_NEW_PLACE_ID_123',
            'google_cid' => '999888777666555444',
            'google_review_url' => 'https://search.google.com/local/writereview?placeid=ChIJ_NEW_PLACE_ID_123',
            'site_latitude' => '25.2048493',
            'site_longitude' => '55.2707828',
            'site_postal_code' => '12345',
            'google_primary_category' => 'Desert Safari Operator',
            'google_service_area' => 'Dubai and Northern Emirates',
            'google_business_hours' => '24/7 Desert Safari Concierge',
        ];

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), $postData);

        $response->assertRedirect();
        $this->assertDatabaseHas('settings', [
            'setting_key' => 'google_place_id',
            'setting_value' => 'ChIJ_NEW_PLACE_ID_123',
        ]);
        $this->assertDatabaseHas('settings', [
            'setting_key' => 'google_cid',
            'setting_value' => '999888777666555444',
        ]);
        $this->assertDatabaseHas('settings', [
            'setting_key' => 'site_latitude',
            'setting_value' => '25.2048493',
        ]);
        $this->assertDatabaseHas('settings', [
            'setting_key' => 'site_longitude',
            'setting_value' => '55.2707828',
        ]);
    }

    /**
     * Test 3: Frontend head renders full Geo Meta tags (region, placename, position, ICBM)
     */
    public function test_frontend_head_renders_complete_geo_meta_tags(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('<meta name="geo.region" content="AE-DU">', $content);
        $this->assertStringContainsString('<meta name="geo.placename" content="Dubai">', $content);
        $this->assertStringContainsString('<meta name="geo.position" content="25.2048;55.2708">', $content);
        $this->assertStringContainsString('<meta name="ICBM" content="25.2048, 55.2708">', $content);
    }

    /**
     * Test 4: Frontend Schema.org TravelAgency JSON-LD entity graph includes Place ID, CID, coordinates and hasMap
     */
    public function test_frontend_travel_agency_schema_includes_google_business_profile_entity(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('"propertyID": "DET Tourism License"', $content);
        $this->assertStringContainsString('"value": "1430583"', $content);
        $this->assertStringContainsString('"propertyID": "Google Place ID"', $content);
        $this->assertStringContainsString('"value": "ChIJbWsIEIVEdEER4uHEhb2dbcQ"', $content);
        $this->assertStringContainsString('"propertyID": "Google CID"', $content);
        $this->assertStringContainsString('"value": "14154142641213989346"', $content);
        $this->assertStringContainsString('"latitude": 25.2048', $content);
        $this->assertStringContainsString('"longitude": 55.2708', $content);
        $this->assertStringContainsString('"hasMap": "https://maps.google.com/?q=25.2048,55.2708"', $content);
        $this->assertStringContainsString('https://search.google.com/local/writereview?placeid=ChIJbWsIEIVEdEER4uHEhb2dbcQ', $content);
        $this->assertStringContainsString('https://maps.google.com/?cid=14154142641213989346', $content);
    }

    /**
     * Test 5: Contact page renders verified Google reviews card, directions link and Place ID schema
     */
    public function test_contact_page_renders_local_seo_google_entity_card_and_schema(): void
    {
        $response = $this->get(route('contact'));

        $response->assertStatus(200);
        $content = $response->getContent();

        // UI elements
        $response->assertSee('Dunes Discovery Tourism LLC');
        $response->assertSee('Write a Review');
        $response->assertSee('Get Directions');
        $this->assertStringContainsString('https://search.google.com/local/writereview?placeid=ChIJbWsIEIVEdEER4uHEhb2dbcQ', $content);
        $this->assertStringContainsString('https://maps.google.com/?q=25.2048,55.2708', $content);

        // Schema assertions
        $this->assertStringContainsString('"@type": "ContactPage"', $content);
        $this->assertStringContainsString('"hasMap": "https://maps.google.com/?q=25.2048,55.2708"', $content);
        $this->assertStringContainsString('"propertyID": "Google Place ID"', $content);
        $this->assertStringContainsString('"value": "ChIJbWsIEIVEdEER4uHEhb2dbcQ"', $content);
    }
}
