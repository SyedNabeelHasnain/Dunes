<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Setting;
use App\Models\Tier;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleThingsToDoFeedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic settings
        Setting::updateOrCreate(['setting_key' => 'site_name'], ['setting_value' => 'Dunes Discovery Tourism L.L.C.']);
        Setting::updateOrCreate(['setting_key' => 'company_license_number'], ['setting_value' => '1430583']);
        Setting::updateOrCreate(['setting_key' => 'google_place_id'], ['setting_value' => 'ChIJbWsIEIVEdEER4uHEhb2dbcQ']);
        Setting::updateOrCreate(['setting_key' => 'google_cid'], ['setting_value' => '14185012580795441634']);
        Setting::updateOrCreate(['setting_key' => 'site_phone'], ['setting_value' => '+971 50 245 6056']);
        Setting::updateOrCreate(['setting_key' => 'site_address'], ['setting_value' => 'Al Fahidi, Bur Dubai, Dubai, United Arab Emirates']);
        Setting::updateOrCreate(['setting_key' => 'site_postal_code'], ['setting_value' => '00000']);
        Setting::updateOrCreate(['setting_key' => 'gttd_feed_enabled'], ['setting_value' => '1']);
        Setting::updateOrCreate(['setting_key' => 'gttd_partner_id'], ['setting_value' => 'dunes-discovery-tourism']);
        Setting::updateOrCreate(['setting_key' => 'gttd_default_poi_place_id'], ['setting_value' => 'ChIJt7e4-c9xdj4R_hY6W5xrqU8']);

        // Create sample Category, Tour, and Tiers
        $category = Category::create([
            'name' => 'Desert Safari',
            'slug' => 'desert-safari',
            'status' => 'active',
        ]);

        $tour = Tour::create([
            'slug' => 'premium-red-dunes-safari',
            'name' => 'Premium Red Dunes Desert Safari with BBQ Dinner',
            'category_id' => $category->id,
            'short_desc' => 'Experience Dubai red dunes with 4x4 dune bashing, camel rides and live shows.',
            'full_desc' => 'Detailed safari description with full inclusions.',
            'duration' => '6 Hours',
            'rating' => 4.9,
            'review_count' => 2840,
            'status' => 'active',
            'is_bestseller' => true,
            'hero_image' => 'images/desert-safari-poster.avif',
        ]);

        $tier = Tier::create([
            'name' => 'Standard Package',
            'slug' => 'standard',
            'display_name' => 'Standard 4x4 Package',
            'status' => 'active',
        ]);

        $tour->tiers()->attach($tier->id, [
            'price' => 99.00,
            'old_price' => 150.00,
            'price_type' => 'per_person',
        ]);
    }

    /**
     * Test 1: Products XML feed is accessible and valid XML schema
     */
    public function test_products_xml_feed_is_accessible_and_valid_xml(): void
    {
        $response = $this->get('/feeds/google-things-to-do/products.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $content = $response->getContent();
        $this->assertStringContainsString('<feed xmlns="http://www.google.com/schemas/things-to-do/1.0">', $content);
        $this->assertStringContainsString('<products>', $content);
        $this->assertStringContainsString('<product_id>dunes-tour-', $content);
        $this->assertStringContainsString('<operator_id>ChIJbWsIEIVEdEER4uHEhb2dbcQ</operator_id>', $content);
        $this->assertStringContainsString('Premium Red Dunes Desert Safari with BBQ Dinner', $content);
        $this->assertStringContainsString('utm_source=google&amp;utm_medium=things_to_do&amp;utm_campaign=gttd_free', $content);
        $this->assertStringContainsString('<duration>PT6H</duration>', $content);
        $this->assertStringContainsString('<average_rating>4.9</average_rating>', $content);
        $this->assertStringContainsString('<place_id>ChIJt7e4-c9xdj4R_hY6W5xrqU8</place_id>', $content);

        // Assert strictly valid XML with SimpleXML
        $xmlObj = simplexml_load_string($content);
        $this->assertNotFalse($xmlObj, 'Failed to parse Products feed as valid XML');
    }

    /**
     * Test 2: Options XML feed is accessible and valid XML schema
     */
    public function test_options_xml_feed_is_accessible_and_valid_xml(): void
    {
        $response = $this->get('/feeds/google-things-to-do/options.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $content = $response->getContent();
        $this->assertStringContainsString('<feed xmlns="http://www.google.com/schemas/things-to-do/1.0">', $content);
        $this->assertStringContainsString('<options>', $content);
        $this->assertStringContainsString('<option_id>dunes-opt-', $content);
        $this->assertStringContainsString('<currency>AED</currency>', $content);
        $this->assertStringContainsString('<amount>99.00</amount>', $content);
        $this->assertStringContainsString('<price_micros>99000000</price_micros>', $content);
        $this->assertStringContainsString('<original_amount>150.00</original_amount>', $content);

        // Assert strictly valid XML
        $xmlObj = simplexml_load_string($content);
        $this->assertNotFalse($xmlObj, 'Failed to parse Options feed as valid XML');
    }

    /**
     * Test 3: Operators XML feed is accessible and valid XML schema
     */
    public function test_operators_xml_feed_is_accessible_and_valid_xml(): void
    {
        $response = $this->get('/feeds/google-things-to-do/operators.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $content = $response->getContent();
        $this->assertStringContainsString('<feed xmlns="http://www.google.com/schemas/things-to-do/1.0">', $content);
        $this->assertStringContainsString('<operators>', $content);
        $this->assertStringContainsString('<operator_id>ChIJbWsIEIVEdEER4uHEhb2dbcQ</operator_id>', $content);
        $this->assertStringContainsString('Dunes Discovery Tourism L.L.C.', $content);
        $this->assertStringContainsString('<phone>+971 50 245 6056</phone>', $content);
        $this->assertStringContainsString('<country_code>AE</country_code>', $content);

        // Assert strictly valid XML
        $xmlObj = simplexml_load_string($content);
        $this->assertNotFalse($xmlObj, 'Failed to parse Operators feed as valid XML');
    }

    /**
     * Test 4: Unified XML feed contains operators, products, and options together
     */
    public function test_unified_xml_feed_contains_operators_products_and_options(): void
    {
        $response = $this->get('/feeds/google-things-to-do/feed.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $content = $response->getContent();
        $this->assertStringContainsString('<operators>', $content);
        $this->assertStringContainsString('<products>', $content);
        $this->assertStringContainsString('<options>', $content);

        $xmlObj = simplexml_load_string($content);
        $this->assertNotFalse($xmlObj, 'Failed to parse Unified feed as valid XML');
    }

    /**
     * Test 5: REST JSON feed returns valid Google Things to Do JSON schema
     */
    public function test_feed_json_returns_valid_json_structure(): void
    {
        $response = $this->get('/feeds/google-things-to-do/feed.json');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'metadata' => [
                'feed_name',
                'feed_version',
                'generation_timestamp',
                'publisher',
                'license',
            ],
            'operator' => [
                'operator_id',
                'name',
                'google_business_profile_name',
                'phone',
                'url',
                'address' => [
                    'street_address',
                    'locality',
                    'region',
                    'country_code',
                    'postal_code',
                ],
                'license_number',
            ],
            'products' => [
                '*' => [
                    'product_id',
                    'operator_id',
                    'title',
                    'description',
                    'landing_page_url',
                    'inventory_type',
                    'duration',
                    'ratings' => [
                        'average_rating',
                        'rating_count',
                    ],
                    'location' => [
                        'poi_place_id',
                        'poi_name',
                        'latitude',
                        'longitude',
                        'locality',
                        'country_code',
                    ],
                    'features',
                ],
            ],
            'options' => [
                '*' => [
                    'option_id',
                    'product_id',
                    'title',
                    'currency',
                    'amount',
                    'price_micros',
                    'landing_page_url',
                ],
            ],
        ]);
    }

    /**
     * Test 6: Disabled GTTD feed returns 404
     */
    public function test_disabled_gttd_feed_returns_404(): void
    {
        Setting::updateOrCreate(['setting_key' => 'gttd_feed_enabled'], ['setting_value' => '0']);

        $response = $this->get('/feeds/google-things-to-do/products.xml');
        $response->assertStatus(404);

        $responseJson = $this->get('/feeds/google-things-to-do/feed.json');
        $responseJson->assertStatus(404);
    }

    /**
     * Test 7: Admin can view GTTD dashboard
     */
    public function test_admin_can_view_gttd_dashboard(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.settings.gttd'));

        $response->assertStatus(200);
        $response->assertSee('Google Things To Do', false);
        $response->assertSee('products.xml');
        $response->assertSee('options.xml');
        $response->assertSee('operators.xml');
        $response->assertSee('feed.json');
    }

    /**
     * Test 8: Admin can update GTTD settings and flush cache
     */
    public function test_admin_can_update_gttd_settings_and_flush_cache(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'gttd_form_submitted' => '1',
            'gttd_feed_enabled' => '1',
            'gttd_partner_id' => 'custom-partner-id',
            'gttd_default_poi_place_id' => 'ChIJ_CUSTOM_POI',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('settings', [
            'setting_key' => 'gttd_partner_id',
            'setting_value' => 'custom-partner-id',
        ]);

        $flushResponse = $this->actingAs($admin)->post(route('admin.settings.gttd.flush-cache'));
        $flushResponse->assertRedirect();
        $flushResponse->assertSessionHas('success');
    }
}
