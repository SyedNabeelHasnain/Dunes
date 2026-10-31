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

    public function test_moz_top_backlink_pages_redirect_301_to_canonical_urls(): void
    {
        // 1. Historical /tour/{slug} backlinks identified in Moz audit
        $respTourOvernight = $this->get('/tour/overnight-desert-safari-dubai');
        $respTourOvernight->assertStatus(301);
        $respTourOvernight->assertRedirect('/overnight-desert-safari-dubai');

        $respTourCruise = $this->get('/tour/dhow-cruise-catamaran-cruise-dinner-dubai');
        $respTourCruise->assertStatus(301);
        $respTourCruise->assertRedirect('/dhow-cruise-catamaran-cruise-dinner-dubai');

        // 2. Short/unqualified legacy slugs identified in Moz audit
        $respMorning = $this->get('/morning-desert-safari');
        $respMorning->assertStatus(301);
        $respMorning->assertRedirect('/morning-desert-safari-dubai');

        $respOvernight = $this->get('/overnight-desert-safari');
        $respOvernight->assertStatus(301);
        $respOvernight->assertRedirect('/overnight-desert-safari-dubai');

        $respEvening = $this->get('/evening-desert-safari');
        $respEvening->assertStatus(301);
        $respEvening->assertRedirect('/evening-desert-safari-dubai');

        // 3. Wix legacy import slug identified in Moz audit
        $respCopyTour = $this->get('/copy-of-dubai-city-tour-1');
        $respCopyTour->assertStatus(301);
        $respCopyTour->assertRedirect('/dubai-city-tour');

        // 4. Legacy /tours/{slug} prefix mapping
        $respToursMorning = $this->get('/tours/morning-desert-safari');
        $respToursMorning->assertStatus(301);
        $respToursMorning->assertRedirect('/morning-desert-safari-dubai');

        // 5. Query string preservation across 301 redirects
        $respQuery = $this->get('/tour/overnight-desert-safari-dubai?tier=vip&utm_source=moz');
        $respQuery->assertStatus(301);
        $respQuery->assertRedirect('/overnight-desert-safari-dubai?tier=vip&utm_source=moz');
    }

    public function test_localized_moz_backlinks_redirect_301(): void
    {
        $locales = ['ar', 'ru', 'es', 'it'];

        foreach ($locales as $loc) {
            $respTour = $this->get("/{$loc}/tour/overnight-desert-safari-dubai");
            $respTour->assertStatus(301);
            $respTour->assertRedirect("/{$loc}/overnight-desert-safari-dubai");

            $respMorning = $this->get("/{$loc}/morning-desert-safari");
            $respMorning->assertStatus(301);
            $respMorning->assertRedirect("/{$loc}/morning-desert-safari-dubai");

            $respCopy = $this->get("/{$loc}/copy-of-dubai-city-tour-1");
            $respCopy->assertStatus(301);
            $respCopy->assertRedirect("/{$loc}/dubai-city-tour");
        }
    }
}
