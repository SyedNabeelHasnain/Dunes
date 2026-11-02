<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\LocationLandingService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RegionalSearchEngineVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        app(SettingsService::class)->clearCache();
    }

    public function test_admin_can_update_bing_yandex_and_baidu_verification_in_cms(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'bing_site_verification' => 'BING_TEST_TOKEN_1234567890ABCDEF',
            'yandex_site_verification' => 'yandex_test_token_abcdef123456',
            'baidu_site_verification' => 'baidu_test_token_9876543210',
        ]);

        $response->assertRedirect();
        app(SettingsService::class)->clearCache();

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'bing_site_verification',
            'setting_value' => 'BING_TEST_TOKEN_1234567890ABCDEF',
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'yandex_site_verification',
            'setting_value' => 'yandex_test_token_abcdef123456',
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'baidu_site_verification',
            'setting_value' => 'baidu_test_token_9876543210',
        ]);
    }

    public function test_frontend_renders_yandex_bing_baidu_verification_meta_tags(): void
    {
        Setting::updateOrCreate(
            ['setting_key' => 'bing_site_verification'],
            ['setting_value' => 'BING_LIVE_ABC123']
        );
        Setting::updateOrCreate(
            ['setting_key' => 'yandex_site_verification'],
            ['setting_value' => 'yandex_live_xyz789']
        );
        Setting::updateOrCreate(
            ['setting_key' => 'baidu_site_verification'],
            ['setting_value' => 'baidu_live_token_456']
        );
        app(SettingsService::class)->clearCache();

        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('<meta name="msvalidate.01" content="BING_LIVE_ABC123">', false);
        $response->assertSee('<meta name="yandex-verification" content="yandex_live_xyz789">', false);
        $response->assertSee('<meta name="baidu-site-verification" content="baidu_live_token_456">', false);
    }

    public function test_international_schema_contact_point_area_served_contains_inbound_markets(): void
    {
        $response = $this->get('/');
        $response->assertOk();

        // Contact point areaServed covers major international inbound tourist origin countries
        $response->assertSee('"areaServed": ["AE", "SA", "GB", "DE", "FR", "IT", "ES", "RU", "US"]', false);

        // Languages include both codes and full names for international crawlers
        $response->assertSee('"availableLanguage": ["en", "ar", "ru", "es", "it", "English", "Arabic", "Russian", "Spanish", "Italian"]', false);

        // Physical business location remains anchored to Dubai, UAE
        $response->assertSee('"name": "Dubai"', false);
        $response->assertSee('"https://www.wikidata.org/wiki/Q612"', false);
        $response->assertSee('"https://www.wikidata.org/wiki/Q878"', false);
    }

    public function test_all_15_location_landing_pages_contain_wikidata_uris(): void
    {
        $locations = LocationLandingService::getLocations();
        $this->assertCount(15, $locations);

        foreach ($locations as $key => $loc) {
            $this->assertArrayHasKey('wikidata_uri', $loc, "Location {$key} missing wikidata_uri");
            $this->assertStringStartsWith('https://www.wikidata.org/wiki/', $loc['wikidata_uri'], "Location {$key} wikidata_uri invalid");
        }
    }

    public function test_location_landing_page_renders_wikidata_same_as_and_service_schema(): void
    {
        $response = $this->get('/desert-safari-from-dubai-marina');
        $response->assertOk();

        // Place sameAs linking to Wikidata Q1262846 (Dubai Marina)
        $response->assertSee('"sameAs": "https://www.wikidata.org/wiki/Q1262846"', false);

        // Dedicated Schema Service entity for pickup transfer
        $response->assertSee('"@type": "Service"', false);
        $response->assertSee('"serviceType": "Tourist Transfer & Desert Safari Tour Pickup"', false);
        $response->assertSee('Doorstep 4x4 Desert Safari Hotel Pickup from Dubai Marina & JBR', false);
    }

    public function test_multilingual_hreflang_tags_are_reciprocal_and_consistent(): void
    {
        $responseEn = $this->get('/');
        $responseEn->assertOk();
        $responseEn->assertSee('hreflang="x-default"', false);
        $responseEn->assertSee('hreflang="en"', false);
        $responseEn->assertSee('hreflang="ar"', false);
        $responseEn->assertSee('hreflang="ru"', false);
        $responseEn->assertSee('hreflang="es"', false);
        $responseEn->assertSee('hreflang="it"', false);

        $responseAr = $this->get('/ar');
        $responseAr->assertOk();
        $responseAr->assertSee('hreflang="x-default"', false);
        $responseAr->assertSee('hreflang="en"', false);
        $responseAr->assertSee('hreflang="ar"', false);
        $responseAr->assertSee('hreflang="ru"', false);
        $responseAr->assertSee('hreflang="es"', false);
        $responseAr->assertSee('hreflang="it"', false);
    }

    public function test_admin_can_update_clarity_id_and_status_in_cms(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'clarity_id' => 'custom_clarity_999',
            'clarity_active' => '1',
        ]);

        $response->assertRedirect();
        app(SettingsService::class)->clearCache();

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'clarity_id',
            'setting_value' => 'custom_clarity_999',
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'clarity_active',
            'setting_value' => '1',
        ]);
    }

    public function test_frontend_renders_microsoft_clarity_script_dynamically(): void
    {
        Setting::updateOrCreate(
            ['setting_key' => 'clarity_id'],
            ['setting_value' => 'z79y89wha9']
        );
        Setting::updateOrCreate(
            ['setting_key' => 'clarity_active'],
            ['setting_value' => '1']
        );
        app(SettingsService::class)->clearCache();

        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('https://www.clarity.ms/tag/"+i+"?ref=bwt', false);
        $response->assertSee('"clarity", "script", "z79y89wha9"', false);
    }

    public function test_frontend_omits_microsoft_clarity_script_when_disabled(): void
    {
        Setting::updateOrCreate(
            ['setting_key' => 'clarity_id'],
            ['setting_value' => 'z79y89wha9']
        );
        Setting::updateOrCreate(
            ['setting_key' => 'clarity_active'],
            ['setting_value' => '0']
        );
        app(SettingsService::class)->clearCache();

        $response = $this->get('/');
        $response->assertOk();
        $response->assertDontSee('https://www.clarity.ms/tag/"+i+"?ref=bwt', false);
    }

    public function test_indexnow_command_defaults_to_streaming_delta_mode(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://api.indexnow.org/indexnow' => \Illuminate\Support\Facades\Http::response(['message' => 'OK'], 200),
        ]);

        \App\Models\Tour::query()->update(['updated_at' => now()->subDays(2)]);
        \App\Models\BlogPost::query()->update(['updated_at' => now()->subDays(2)]);

        // Default run should succeed in streaming mode without dumping all URLs
        $this->artisan('seo:indexnow')
            ->expectsOutputToContain('Preserved streaming mode')
            ->assertExitCode(0);
    }

    public function test_indexnow_service_submit_recent_handles_clean_zero_updates(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://api.indexnow.org/indexnow' => \Illuminate\Support\Facades\Http::response(['message' => 'OK'], 200),
        ]);

        \App\Models\Tour::query()->update(['updated_at' => now()->subDays(2)]);
        \App\Models\BlogPost::query()->update(['updated_at' => now()->subDays(2)]);

        $service = app(\App\Services\IndexNowService::class);
        $result = $service->submitRecent(24);

        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['count']);
        $this->assertStringContainsString('Preserved streaming mode', $result['message']);
    }
}
