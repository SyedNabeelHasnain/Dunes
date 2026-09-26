<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Setting;
use App\Models\Tier;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LiveSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        Setting::updateOrCreate(['setting_key' => 'site_whatsapp'], ['setting_value' => '971502456056']);

        $category = Category::create([
            'name' => 'Desert Safari',
            'slug' => 'desert-safari',
            'icon' => 'bi-sun',
            'priority' => 1,
        ]);

        $cityCat = Category::create([
            'name' => 'City Tour',
            'slug' => 'city-tour',
            'icon' => 'bi-building',
            'priority' => 2,
        ]);

        $tour1 = Tour::create([
            'name' => 'Evening Desert Safari Dubai',
            'slug' => 'evening-desert-safari-dubai',
            'category_id' => $category->id,
            'duration' => '6 Hours',
            'short_desc' => 'Dune bashing, camel ride, BBQ buffet dinner, and 3 live stage shows in Lahbab red dunes.',
            'status' => 'active',
            'priority' => 1,
            'rating' => 4.9,
            'review_count' => 1247,
            'is_bestseller' => true,
            'is_featured' => true,
        ]);

        $tour2 = Tour::create([
            'name' => 'Dubai Modern City Tour',
            'slug' => 'dubai-modern-city-tour',
            'category_id' => $cityCat->id,
            'duration' => '8 Hours',
            'short_desc' => 'Explore Burj Khalifa, Dubai Marina, Palm Jumeirah, and historic Dubai Creek.',
            'status' => 'active',
            'priority' => 2,
            'rating' => 4.8,
            'review_count' => 450,
            'is_bestseller' => false,
            'is_featured' => true,
        ]);

        $tier = Tier::create([
            'name' => 'Standard Package',
            'slug' => 'standard',
            'priority' => 1,
        ]);

        $tour1->tiers()->attach($tier->id, ['price' => 99, 'old_price' => 150, 'price_type' => 'per_person']);
        $tour2->tiers()->attach($tier->id, ['price' => 180, 'old_price' => 220, 'price_type' => 'per_person']);
    }

    public function test_live_search_returns_empty_when_query_less_than_two_characters(): void
    {
        $response = $this->getJson('/search/live?q=s');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'count' => 0,
            'results' => [],
        ]);
    }

    public function test_live_search_returns_matching_tours_when_query_two_or_more_characters(): void
    {
        $response = $this->getJson('/search/live?q=safari');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $json = $response->json();
        $this->assertGreaterThanOrEqual(1, $json['count']);
        $this->assertEquals('Evening Desert Safari Dubai', $json['results'][0]['name']);
        $this->assertEquals('desert-safari', $json['results'][0]['category_slug']);
        $this->assertNotEmpty($json['results'][0]['url']);
        $this->assertNotEmpty($json['results'][0]['price_formatted']);
    }

    public function test_live_search_filters_by_category(): void
    {
        // Query matching both tours ('dubai'), filtered to city tour category
        $responseCity = $this->getJson('/search/live?q=dubai&category=city-tour');
        $responseCity->assertStatus(200);
        $jsonCity = $responseCity->json();
        $this->assertCount(1, $jsonCity['results']);
        $this->assertEquals('Dubai Modern City Tour', $jsonCity['results'][0]['name']);

        // Query matching both tours ('dubai'), filtered to desert safari category
        $responseSafari = $this->getJson('/search/live?q=dubai&category=desert-safari');
        $responseSafari->assertStatus(200);
        $jsonSafari = $responseSafari->json();
        $this->assertCount(1, $jsonSafari['results']);
        $this->assertEquals('Evening Desert Safari Dubai', $jsonSafari['results'][0]['name']);
    }

    public function test_api_v1_live_search_endpoint(): void
    {
        $response = $this->getJson('/api/v1/tours/search?q=safari');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertGreaterThanOrEqual(1, $response->json()['count']);
    }

    public function test_ajax_gateway_handles_live_search(): void
    {
        $response = $this->post('/ajax.php', [
            'action' => 'live_search',
            'q' => 'safari',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertGreaterThanOrEqual(1, $response->json()['count']);
    }

    public function test_standard_search_page_still_renders_full_serp_html(): void
    {
        $response = $this->get('/search?q=safari');

        $response->assertStatus(200);
        $response->assertSee('Evening Desert Safari Dubai');
        $response->assertSee('noindex, follow');
        $response->assertSee('Suggestions:');
        $response->assertSee('no-scrollbar');
        $response->assertSee('Update');
    }

    public function test_standard_search_route_with_ajax_header_returns_json(): void
    {
        $response = $this->get('/search?q=safari', [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertGreaterThanOrEqual(1, $response->json()['count']);
    }

    public function test_frontend_modal_renders_search_catalog_and_alpine_component(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $html = $response->getContent();

        $this->assertStringContainsString('id="globalSearchModal"', $html);
        $this->assertStringContainsString('id="globalSearchModalInput"', $html);
        $this->assertStringContainsString('globalSearchModal({', $html);
        $this->assertStringContainsString('x-cloak', $html);
        $this->assertStringContainsString('style="display: none;"', $html);
    }
}
