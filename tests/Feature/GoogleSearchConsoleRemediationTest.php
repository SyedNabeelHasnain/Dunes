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
}
