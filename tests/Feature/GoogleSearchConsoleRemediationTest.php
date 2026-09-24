<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Booking;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleSearchConsoleRemediationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Homepage Canonical Trailing Slash & Single AggregateRating
     */
    public function test_homepage_canonical_has_trailing_slash_and_single_aggregate_rating(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();

        // 1. Canonical must end with '/'
        preg_match('/<link rel="canonical" href="([^"]+)">/', $content, $m);
        $this->assertNotEmpty($m, 'Canonical link tag must be present on homepage');
        $canonical = $m[1];
        $this->assertStringEndsWith('/', $canonical, 'Homepage canonical URL must terminate with a trailing slash');

        // 2. AggregateRating must occur EXACTLY once on the homepage
        $aggregateRatingCount = substr_count($content, '"@type": "AggregateRating"');
        $this->assertEquals(1, $aggregateRatingCount, 'Homepage must have strictly ONE AggregateRating in JSON-LD');

        // 3. Review count must be 2847
        $this->assertStringContainsString('"reviewCount": "2847"', $content);

        // 4. VideoObject must be present and linked to organization
        $this->assertStringContainsString('"VideoObject"', $content);
        $this->assertStringContainsString('#organization', $content);

        // 5. Exactly 1 TravelAgency definition
        $travelAgencyCount = substr_count($content, '"TravelAgency"');
        $this->assertEquals(1, $travelAgencyCount, 'Homepage must have strictly ONE TravelAgency entity in JSON-LD');
    }

    /**
     * Test 2: Subpage (/about) Canonical Has NO Trailing Slash & Zero AggregateRating
     */
    public function test_subpages_have_no_trailing_slash_and_no_aggregate_rating(): void
    {
        $response = $this->get('/about');
        $response->assertStatus(200);

        $content = $response->getContent();

        // 1. Canonical must NOT end with '/'
        preg_match('/<link rel="canonical" href="([^"]+)">/', $content, $m);
        $this->assertNotEmpty($m, 'Canonical link tag must be present on about page');
        $canonical = $m[1];
        $this->assertFalse(str_ends_with($canonical, '/'), 'Subpage canonical must NOT terminate with a trailing slash');

        // 2. AggregateRating must be 0 on about page
        $aggregateRatingCount = substr_count($content, '"@type": "AggregateRating"');
        $this->assertEquals(0, $aggregateRatingCount, 'About page must not contain AggregateRating');
    }

    /**
     * Test 3: Tour Detail Page Has Strictly ONE AggregateRating on #trip and No Duplicate Entities
     */
    public function test_tour_detail_page_has_strictly_one_aggregate_rating_on_product_trip(): void
    {
        $tour = Tour::create([
            'name' => 'Morning Desert Safari Dubai',
            'slug' => 'morning-desert-safari-dubai',
            'short_desc' => 'Sunrise dune bashing adventure in Dubai.',
            'hero_image' => 'desert-safari-dubai-morning-desert-safari.avif',
            'status' => 'active',
            'duration' => '4 Hours',
            'rating' => 4.9,
            'review_count' => 120,
        ]);

        $response = $this->get('/'.$tour->slug);
        $response->assertStatus(200);

        $content = $response->getContent();

        // 1. Strictly ONE AggregateRating on the entire page
        $aggregateRatingCount = substr_count($content, '"@type": "AggregateRating"');
        $this->assertEquals(1, $aggregateRatingCount, 'Tour detail page must have strictly ONE AggregateRating');

        // 2. Tour Product / TouristTrip schema is present
        $this->assertStringContainsString('"TouristTrip"', $content);

        // 3. Brand references layout #organization
        $this->assertStringContainsString('"brand":', $content);
        $this->assertStringContainsString('#organization', $content);

        // 4. Exactly 1 WebPage definition across the page
        $webPageCount = substr_count($content, '"@type": "WebPage"');
        $this->assertEquals(1, $webPageCount, 'Tour detail page must have strictly ONE WebPage entity');
    }

    /**
     * Test 4: Pricing Guide Duplicate Route 301 Redirects to Rate Card
     */
    public function test_pricing_guide_route_301_redirects_to_rate_card(): void
    {
        $response = $this->get('/pricing-guide');
        $response->assertStatus(301);
        $response->assertRedirect('/rate-card');
    }

    /**
     * Test 5: Sitemap pages() Homepage Entry Has Trailing Slash
     */
    public function test_sitemap_pages_homepage_entry_has_trailing_slash(): void
    {
        $response = $this->get('/sitemap-pages.xml');
        $response->assertStatus(200);

        $content = $response->getContent();

        preg_match_all('/<loc>(.*?)<\/loc>/', $content, $matches);
        $this->assertNotEmpty($matches[1], 'Sitemap must contain URLs');

        $firstLoc = $matches[1][0];
        $this->assertStringEndsWith('/', $firstLoc, 'Sitemap homepage entry must terminate with a trailing slash');

        $secondLoc = $matches[1][1];
        $this->assertFalse(str_ends_with($secondLoc, '/'), 'Sitemap subpage entry must NOT terminate with a trailing slash');
    }

    /**
     * Test 6: Blog Post Server Renders og:type article and Canonical
     */
    public function test_blog_post_server_renders_og_type_article_and_canonical(): void
    {
        $category = BlogCategory::create([
            'name' => 'Safari Guides',
            'slug' => 'safari-guides',
            'status' => 'active',
        ]);

        $post = BlogPost::create([
            'category_id' => $category->id,
            'title' => 'Ultimate Dubai Safari Guide',
            'slug' => 'ultimate-dubai-safari-guide',
            'excerpt' => 'Everything you need to know before booking your desert safari in Dubai.',
            'content' => 'Complete guide to quad biking, dune bashing, and BBQ dinner in Dubai.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get('/blog/'.$post->slug);
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('<meta property="og:type" content="article">', $content);
        $this->assertStringNotContainsString("setAttribute('content', 'article')", $content, 'Client-side og:type modification script must be removed');
    }

    /**
     * Test 7: Internal Search Returns noindex, follow Directives
     */
    public function test_internal_search_has_noindex_follow_robots_directive(): void
    {
        $response = $this->get('/search?q=safari');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $content);
    }

    /**
     * Test 8: Sensitive and Transactional Routes Have noindex, nofollow Directives
     */
    public function test_sensitive_and_transactional_routes_have_noindex_nofollow_directive(): void
    {
        // 1. Thank you page
        $responseThankYou = $this->get('/thankyou');
        $responseThankYou->assertStatus(200);
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $responseThankYou->getContent());

        // 2. Payment Cancel page
        $responseCancel = $this->get('/payment-cancel');
        $responseCancel->assertStatus(200);
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $responseCancel->getContent());

        // 3. Voucher page
        $booking = Booking::create([
            'reference' => 'TEST-ROBOTS-101',
            'tour_name' => 'Evening Desert Safari',
            'tour_date' => now()->toDateString(),
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+971501234567',
            'adults' => 2,
            'children' => 0,
            'infants' => 0,
            'pickup_location' => 'Dubai Marina',
            'subtotal' => 300,
            'total' => 300,
        ]);
        $responseVoucher = $this->get('/booking/'.$booking->reference.'/voucher');
        $responseVoucher->assertStatus(200);
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $responseVoucher->getContent());

        // 4. Unsubscribe page (404/invalid token scenario)
        $responseUnsub = $this->get('/unsubscribe/invalid-or-sample-token');
        $responseUnsub->assertStatus(404);
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $responseUnsub->getContent());
    }

    /**
     * Test 9: Tour Without Reviews Omits AggregateRating Schema
     */
    public function test_tour_without_reviews_omits_aggregate_rating(): void
    {
        $tour = Tour::create([
            'name' => 'New Quad Safari Without Reviews',
            'slug' => 'new-quad-safari-no-reviews',
            'short_desc' => 'Adventure safari with no reviews yet.',
            'hero_image' => 'quad-safari.avif',
            'status' => 'active',
            'duration' => '3 Hours',
            'rating' => 5.0,
            'review_count' => 0,
        ]);

        $response = $this->get('/'.$tour->slug);
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringNotContainsString('"@type": "AggregateRating"', $content, 'Tour with 0 reviews must not emit fabricated AggregateRating');
    }

    /**
     * Test 10: Sitemap pages() Includes lastmod Tag For Every Entry
     */
    public function test_sitemap_pages_includes_lastmod_timestamps(): void
    {
        $response = $this->get('/sitemap-pages.xml');
        $response->assertStatus(200);

        $content = $response->getContent();

        preg_match_all('/<url>/', $content, $urls);
        preg_match_all('/<lastmod>(.*?)<\/lastmod>/', $content, $lastmods);

        $this->assertNotEmpty($urls[0]);
        $this->assertEquals(count($urls[0]), count($lastmods[0]), 'Every URL entry in sitemap-pages must have a lastmod timestamp');
    }

    /**
     * Test 11: Core Public Pages Have Strictly ONE <h1> Heading
     */
    public function test_all_core_public_pages_have_strictly_one_h1_heading(): void
    {
        $routes = [
            '/',
            '/about',
            '/contact',
            '/faq',
            '/tours',
            '/rate-card',
            '/blog',
            '/payment-cancel',
            '/thankyou',
            '/review/guest',
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertStatus(200);

            $content = $response->getContent();
            $h1Count = substr_count(strtolower($content), '<h1');
            $this->assertEquals(1, $h1Count, "Route [{$route}] must have strictly ONE <h1> heading");
        }
    }

    /**
     * Test 12: Rate Card Page Has Valid BreadcrumbList Schema
     */
    public function test_rate_card_page_has_valid_breadcrumb_list_schema(): void
    {
        $response = $this->get('/rate-card');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('"@type": "BreadcrumbList"', $content);
        $this->assertStringContainsString('"name": "Rate Card"', $content);
    }

    /**
     * Test 13: BreadcrumbList Home Entries Match Homepage Canonical Trailing Slash
     */
    public function test_breadcrumb_list_home_entries_match_canonical_trailing_slash(): void
    {
        $routes = ['/about', '/contact', '/faq', '/rate-card', '/tours', '/blog'];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertStatus(200);

            $content = $response->getContent();
            $this->assertStringContainsString('"name"', $content);
            $this->assertStringContainsString('"Home"', $content);
            preg_match('/"name"\s*:\s*"Home"\s*,\s*"item"\s*:\s*"([^"]+)"/i', $content, $matches);
            $this->assertNotEmpty($matches, "Home breadcrumb item must be present on [{$route}]");
            $this->assertStringEndsWith('/', $matches[1], "Breadcrumb home entry on [{$route}] must end with trailing slash");
        }
    }

    /**
     * Test 14: 404 Error Page Renders HTTP 404 and Has Noindex Directive
     */
    public function test_404_error_page_renders_status_and_noindex_directive(): void
    {
        $response = $this->get('/non-existent-page-url-xyz');
        $response->assertStatus(404);

        $content = $response->getContent();
        $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $content);
        $this->assertStringContainsString('<h1', $content);
    }

    /**
     * Test 15: Multi-Engine Site Verification Tags (Google & Bing)
     */
    public function test_multi_engine_site_verification_tags(): void
    {
        putenv('GOOGLE_SITE_VERIFICATION=google_test_token_123');
        putenv('BING_SITE_VERIFICATION=bing_test_token_456');

        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('<meta name="google-site-verification" content="google_test_token_123">', $content);
        $this->assertStringContainsString('<meta name="msvalidate.01" content="bing_test_token_456">', $content);

        putenv('GOOGLE_SITE_VERIFICATION');
        putenv('BING_SITE_VERIFICATION');
    }

    /**
     * Test 16: Semantic Buttons for Interactive Modal Triggers
     */
    public function test_interactive_booking_triggers_use_semantic_buttons(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();
        // Desktop & Mobile booking triggers should not be <a href="#" data-action="open-booking">
        $this->assertStringNotContainsString('<a class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full font-extrabold text-xs text-white bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 shadow-sm hover:shadow-md transition-all cursor-pointer" href="#" data-action="open-booking">', $content);
        $this->assertStringContainsString('data-action="open-booking"', $content);
    }

    /**
     * Test 17: TravelAgency Schema Has Map Linked to Coordinates
     */
    public function test_travel_agency_schema_has_map_linked_to_coordinates(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('"hasMap": "https://maps.google.com/?q=25.2048,55.2708"', $content);
    }

    /**
     * Test 18: Blog Post FAQ Schema Produces Valid JSON
     */
    public function test_blog_post_faq_schema_handles_quotes_and_produces_valid_json(): void
    {
        $category = BlogCategory::create([
            'name' => 'Safari Tips',
            'slug' => 'safari-tips',
        ]);

        $post = BlogPost::create([
            'title' => 'Guide to "Dune Bashing" in Dubai',
            'slug' => 'guide-to-dune-bashing',
            'excerpt' => 'Complete guide with quotes "and" formatting.',
            'content' => 'Full article body with plenty of details about desert safari safety.',
            'category_id' => $category->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $post->faqs()->create([
            'question' => 'Is "dune bashing" safe for kids?',
            'answer' => 'Yes, our "experienced" drivers use 5-point harnesses and roll cages.',
            'priority' => 1,
        ]);

        $response = $this->get('/blog/'.$post->slug);
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('"@type": "FAQPage"', $content);
        $this->assertStringContainsString('Is \"dune bashing\" safe for kids?', $content);
    }
}

