<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Setting;
use App\Models\Tier;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SeoKeywordsAndRedirectsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        Setting::updateOrCreate(['setting_key' => 'site_whatsapp'], ['setting_value' => '971502456056']);

        $desertCat = Category::create([
            'name' => 'Desert Safari',
            'slug' => 'desert-safari',
            'icon' => 'bi-sun',
            'priority' => 1,
        ]);

        $waterCat = Category::create([
            'name' => 'Cruises & Water Activities',
            'slug' => 'water-activity',
            'icon' => 'bi-water',
            'priority' => 2,
        ]);

        $cityCat = Category::create([
            'name' => 'City Tours',
            'slug' => 'city-tour',
            'icon' => 'bi-building',
            'priority' => 3,
        ]);

        $t1 = Tour::create([
            'name' => 'Evening Desert Safari Dubai',
            'slug' => 'evening-desert-safari-dubai',
            'category_id' => $desertCat->id,
            'duration' => '6 Hours',
            'short_desc' => 'Red dune bashing, BBQ dinner, and shows.',
            'status' => 'active',
            'priority' => 1,
            'rating' => 4.9,
            'review_count' => 1200,
            'is_bestseller' => true,
            'is_featured' => true,
        ]);

        $t2 = Tour::create([
            'name' => 'Dune Buggy Rental Dubai',
            'slug' => 'dune-buggy-rental-dubai',
            'category_id' => $desertCat->id,
            'duration' => '3 Hours',
            'short_desc' => '1000cc Can-Am Maverick X3 dune buggies.',
            'status' => 'active',
            'priority' => 2,
            'rating' => 4.9,
            'review_count' => 640,
            'is_bestseller' => true,
            'is_featured' => true,
        ]);

        $t3 = Tour::create([
            'name' => 'Desert Safari Quad Biking Dubai',
            'slug' => 'desert-safari-quad-biking-dubai',
            'category_id' => $desertCat->id,
            'duration' => '4 Hours',
            'short_desc' => 'Yamaha 400cc quad bikes in red dunes.',
            'status' => 'active',
            'priority' => 3,
            'rating' => 4.8,
            'review_count' => 520,
            'is_bestseller' => false,
            'is_featured' => true,
        ]);

        $t4 = Tour::create([
            'name' => 'Dhow and Catamaran Cruise Dubai',
            'slug' => 'dhow-cruise-catamaran-cruise-dinner-dubai',
            'category_id' => $waterCat->id,
            'duration' => '2 Hours',
            'short_desc' => 'Dubai Marina luxury cruise dinner.',
            'status' => 'active',
            'priority' => 4,
            'rating' => 4.7,
            'review_count' => 560,
            'is_bestseller' => true,
            'is_featured' => true,
        ]);

        $t5 = Tour::create([
            'name' => 'Abu Dhabi City Tour from Dubai',
            'slug' => 'abu-dhabi-city-tour-from-dubai',
            'category_id' => $cityCat->id,
            'duration' => '10 Hours',
            'short_desc' => 'Sheikh Zayed Grand Mosque tour.',
            'status' => 'active',
            'priority' => 5,
            'rating' => 4.9,
            'review_count' => 740,
            'is_bestseller' => true,
            'is_featured' => true,
        ]);

        $t6 = Tour::create([
            'name' => 'Dubai City Tour',
            'slug' => 'dubai-city-tour',
            'category_id' => $cityCat->id,
            'duration' => '8 Hours',
            'short_desc' => 'Iconic landmarks from Creek to Burj Khalifa.',
            'status' => 'active',
            'priority' => 6,
            'rating' => 4.7,
            'review_count' => 890,
            'is_bestseller' => true,
            'is_featured' => true,
        ]);

        $tier = Tier::create([
            'name' => 'Standard Package',
            'slug' => 'standard',
            'priority' => 1,
        ]);

        foreach ([$t1, $t2, $t3, $t4, $t5, $t6] as $tour) {
            $tour->tiers()->attach($tier->id, ['price' => 99, 'old_price' => 150, 'price_type' => 'per_person']);
        }
    }

    public function test_commercial_search_keyword_redirects_in_root(): void
    {
        $redirects = [
            '/faqs' => '/faq',
            '/admin/login' => '/login',
            '/pricing' => '/rate-card',
            '/packages' => '/tours',
            '/tour' => '/tours',
            '/desert-safari' => '/evening-desert-safari-dubai',
            '/desert-safari-dubai' => '/evening-desert-safari-dubai',
            '/dubai-desert-safari' => '/evening-desert-safari-dubai',
            '/safari-dubai' => '/evening-desert-safari-dubai',
            '/desert-safaris-dubai' => '/tours',
            '/dubai-desert-safaris' => '/tours',
            '/quad-biking-dubai' => '/desert-safari-quad-biking-dubai',
            '/quad-bike-dubai' => '/desert-safari-quad-biking-dubai',
            '/quad-biking' => '/desert-safari-quad-biking-dubai',
            '/dune-buggy-dubai' => '/dune-buggy-rental-dubai',
            '/dune-buggy-rental' => '/dune-buggy-rental-dubai',
            '/dune-buggies-dubai' => '/dune-buggy-rental-dubai',
            '/dhow-cruise-dubai' => '/dhow-cruise-catamaran-cruise-dinner-dubai',
            '/dhow-cruise' => '/dhow-cruise-catamaran-cruise-dinner-dubai',
            '/abu-dhabi-tour' => '/abu-dhabi-city-tour-from-dubai',
            '/city-tour-dubai' => '/dubai-city-tour',
            '/dubai-city-tours' => '/dubai-city-tour',
        ];

        foreach ($redirects as $source => $expectedTarget) {
            $response = $this->get($source);
            $response->assertStatus(301);
            $response->assertRedirect($expectedTarget);
        }
    }

    public function test_commercial_search_keyword_redirects_in_localized_locales(): void
    {
        $locales = ['ar', 'ru', 'es', 'it'];

        foreach ($locales as $loc) {
            $respFaq = $this->get("/{$loc}/faqs");
            $respFaq->assertStatus(301);
            $respFaq->assertRedirect("/{$loc}/faq");

            $respSafari = $this->get("/{$loc}/desert-safari-dubai");
            $respSafari->assertStatus(301);
            $respSafari->assertRedirect("/{$loc}/evening-desert-safari-dubai");

            $respQuad = $this->get("/{$loc}/quad-biking-dubai");
            $respQuad->assertStatus(301);
            $respQuad->assertRedirect("/{$loc}/desert-safari-quad-biking-dubai");

            $respBuggy = $this->get("/{$loc}/dune-buggy-dubai");
            $respBuggy->assertStatus(301);
            $respBuggy->assertRedirect("/{$loc}/dune-buggy-rental-dubai");
        }
    }

    public function test_live_search_endpoint_returns_json_and_handles_keywords_resiliently(): void
    {
        $response = $this->getJson('/search/live?q=buggy');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $json = $response->json();
        $this->assertNotEmpty($json['results']);
        $this->assertEquals('dune-buggy-rental-dubai', $json['results'][0]['slug']);

        // Test with empty query and all=1
        $responseAll = $this->getJson('/search/live?all=1');
        $responseAll->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $responseAll->json()['count']);

        // Test with single letter
        $responseSingle = $this->getJson('/search/live?q=a');
        $responseSingle->assertStatus(200);
        $this->assertEquals(0, $responseSingle->json()['count']);
    }

    public function test_security_headers_are_applied_to_web_responses(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy');
        $response->assertHeader('Strict-Transport-Security');
    }
}
