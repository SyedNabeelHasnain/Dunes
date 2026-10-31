<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Tier;
use App\Models\Tour;
use App\Models\WikiArticle;
use App\Models\WikiPillar;
use App\Services\IndexNowService;
use Database\Seeders\WikiKnowledgeSeeder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class SearchAndAiEngineOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * Test that all 40 blog posts in the JSON seeder have non-empty ai_summary fields.
     */
    public function test_all_blog_posts_in_seeder_have_non_empty_ai_summaries(): void
    {
        $jsonPath = database_path('seeders/data/blog_posts.json');
        $this->assertFileExists($jsonPath);

        $posts = json_decode(file_get_contents($jsonPath), true);
        $this->assertIsArray($posts);
        $this->assertCount(40, $posts);

        foreach ($posts as $post) {
            $this->assertArrayHasKey('ai_summary', $post, "Post ID {$post['id']} ({$post['slug']}) is missing the ai_summary key.");
            $this->assertNotEmpty(trim($post['ai_summary']), "Post ID {$post['id']} ({$post['slug']}) has an empty ai_summary.");
            $this->assertGreaterThanOrEqual(50, strlen($post['ai_summary']), "Post ID {$post['id']} ai_summary should be informative and inverted-pyramid structured.");
        }

        // Specifically verify ID 13 and 14
        $post13 = collect($posts)->firstWhere('id', 13);
        $this->assertNotNull($post13);
        $this->assertStringContainsString('Desert Safari (from AED 79)', $post13['ai_summary']);
        $this->assertStringContainsString('Dune Buggy Rental (from AED 599)', $post13['ai_summary']);

        $post14 = collect($posts)->firstWhere('id', 14);
        $this->assertNotNull($post14);
        $this->assertStringContainsString('Dune bashing is strictly prohibited for pregnant travelers', $post14['ai_summary']);
        $this->assertStringContainsString('DET safety laws', $post14['ai_summary']);
    }

    /**
     * Test Syndication Feed XML contains custom dunes namespace and timing / price fields.
     */
    public function test_syndication_feed_contains_dunes_namespace_and_structured_tour_fields(): void
    {
        $tour = Tour::create([
            'name' => 'Evening Desert Safari Dubai',
            'slug' => 'evening-desert-safari-dubai',
            'short_desc' => 'The definitive Dubai desert adventure with BBQ dinner and dune bashing.',
            'full_desc' => 'Full tour description.',
            'duration' => '6 Hours',
            'pickup_time' => '2:30 PM - 3:00 PM',
            'dropoff_time' => '9:00 PM - 9:30 PM',
            'status' => 'active',
            'priority' => 1,
        ]);

        $tier = Tier::create([
            'name' => 'Standard Camp',
            'slug' => 'standard-camp',
            'status' => 'active',
            'priority' => 1,
        ]);
        $tour->tiers()->attach($tier->id, ['price' => 79.00]);

        $response = $this->get('/feeds/tours.xml');

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('xmlns:dunes="https://dunesdiscoverytourism.com/ns/tours"', $content);
        $this->assertStringContainsString('<dunes:pickup_time>2:30 PM - 3:00 PM</dunes:pickup_time>', $content);
        $this->assertStringContainsString('<dunes:dropoff_time>9:00 PM - 9:30 PM</dunes:dropoff_time>', $content);
        $this->assertStringContainsString('<dunes:starting_price currency="AED">79.00</dunes:starting_price>', $content);
        $this->assertStringContainsString('<dunes:duration>6 Hours</dunes:duration>', $content);
        $this->assertStringContainsString('Pickup: 2:30 PM - 3:00 PM', $content);
        $this->assertStringContainsString('Drop-off: 9:00 PM - 9:30 PM', $content);
    }

    /**
     * Test /tours catalogue page contains semantic HTML comparison table.
     */
    public function test_tours_index_page_contains_semantic_comparison_table(): void
    {
        $category = Category::create([
            'name' => 'Desert Safari',
            'slug' => 'desert-safari',
            'priority' => 1,
        ]);

        $tour = Tour::create([
            'name' => 'Morning Desert Safari Dubai',
            'slug' => 'morning-desert-safari-dubai',
            'category_id' => $category->id,
            'short_desc' => 'Sunrise adventures with quad biking.',
            'full_desc' => 'Morning safari description.',
            'duration' => '4 Hours',
            'pickup_time' => '7:00 AM - 7:30 AM',
            'dropoff_time' => '10:30 AM - 11:00 AM',
            'status' => 'active',
            'priority' => 1,
            'is_featured' => true,
        ]);

        $tier = Tier::create([
            'name' => 'Standard Morning',
            'slug' => 'standard-morning',
            'status' => 'active',
            'priority' => 1,
        ]);
        $tour->tiers()->attach($tier->id, ['price' => 120.00]);

        $response = $this->get('/tours');

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('tour-comparison-table-wrapper', $content);
        $this->assertStringContainsString('<table', $content);
        $this->assertStringContainsString('Tour Experience', $content);
        $this->assertStringContainsString('Duration', $content);
        $this->assertStringContainsString('Pickup / Drop-off', $content);
        $this->assertStringContainsString('Starting Price (AED)', $content);
        $this->assertStringContainsString('Morning Desert Safari Dubai', $content);
        $this->assertStringContainsString('AED 120', $content);
    }

    /**
     * Test Tour detail page renders .page-summary and AggregateOffer JSON-LD schema.
     */
    public function test_tour_detail_page_has_page_summary_and_aggregate_offer_schema(): void
    {
        $category = Category::create([
            'name' => 'Desert Safari',
            'slug' => 'desert-safari-vip',
            'priority' => 1,
        ]);

        $tour = Tour::create([
            'name' => 'VIP Evening Desert Safari Dubai',
            'slug' => 'vip-evening-desert-safari-dubai',
            'category_id' => $category->id,
            'short_desc' => 'Luxury VIP 4x4 evening desert safari.',
            'full_desc' => 'Indulge in a premium desert safari with VIP table service.',
            'duration' => '6 Hours',
            'pickup_time' => '2:30 PM - 3:00 PM',
            'dropoff_time' => '9:00 PM - 9:30 PM',
            'status' => 'active',
            'priority' => 1,
        ]);

        $tier1 = Tier::create(['name' => 'Standard Buffet', 'slug' => 'std-buffet', 'status' => 'active', 'priority' => 1]);
        $tier2 = Tier::create(['name' => 'VIP Table Service', 'slug' => 'vip-service', 'status' => 'active', 'priority' => 2]);

        $tour->tiers()->attach([
            $tier1->id => ['price' => 149.00],
            $tier2->id => ['price' => 249.00],
        ]);

        $response = $this->get('/' . $tour->slug);

        $response->assertStatus(200);
        $content = $response->getContent();

        // Check CSS class for direct-answer extraction and speakable
        $this->assertStringContainsString('page-summary', $content);

        // Check AggregateOffer schema
        $this->assertStringContainsString('"@type": "AggregateOffer"', $content);
        $this->assertStringContainsString('"lowPrice": "149.00"', $content);
        $this->assertStringContainsString('"highPrice": "249.00"', $content);
        $this->assertStringContainsString('"offerCount": "2"', $content);

        // Check SpeakableSpecification
        $this->assertStringContainsString('"@type": "SpeakableSpecification"', $content);
        $this->assertStringContainsString('.page-summary', $content);
    }

    /**
     * Test Blog post detail page renders .page-summary on the AI key takeaways.
     */
    public function test_blog_post_detail_page_has_page_summary_and_speakable_schema(): void
    {
        $post = BlogPost::create([
            'title' => 'Top 10 Dune Buggy Rental Tips for Dubai Desert Beginners',
            'slug' => 'top-10-dune-buggy-rental-tips-dubai-beginners',
            'excerpt' => 'Essential dune buggy rental advice for first-time drivers in Dubai.',
            'ai_summary' => 'Beginner dune buggy rentals in Dubai require closed-toe footwear, UV sunglasses, and adherence to guide tracks. No driver license is legally required as riding takes place in private off-road desert zones.',
            'content' => '<p>Here are the complete top 10 tips for driving buggies in Dubai.</p>',
            'status' => 'published',
            'published_at' => now(),
            'read_time' => 5,
        ]);

        $response = $this->get('/blog/' . $post->slug);

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('page-summary', $content);
        $this->assertStringContainsString('Beginner dune buggy rentals in Dubai require closed-toe footwear', $content);
        $this->assertStringContainsString('"@type": "SpeakableSpecification"', $content);
        $this->assertStringContainsString('.page-summary', $content);
    }

    /**
     * Test Wiki article has Executive Takeaways & Field Specs with .page-summary.
     */
    public function test_wiki_article_page_has_executive_takeaways_and_page_summary(): void
    {
        $this->seed(WikiKnowledgeSeeder::class);

        $article = WikiArticle::with('pillar')->firstOrFail();

        $response = $this->get('/wiki/' . $article->pillar->slug . '/' . $article->slug);

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('Executive Takeaways & Field Specs', $content);
        $this->assertStringContainsString('page-summary', $content);
    }

    /**
     * Test Global footer contains outbound link to Dubai Department of Economy and Tourism (DET).
     */
    public function test_footer_contains_hyperlink_to_official_dubai_det_portal(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('https://www.dubaitourism.gov.ae', $content);
        $this->assertStringContainsString('target="_blank"', $content);
        $this->assertStringContainsString('rel="noopener noreferrer"', $content);
    }

    /**
     * Test Tour and BlogPost Observers trigger IndexNow submission.
     */
    public function test_tour_and_blog_observers_push_to_indexnow_service(): void
    {
        $mockIndexNow = Mockery::mock(IndexNowService::class);
        $mockIndexNow->shouldReceive('isEnabled')->andReturn(true);
        $mockIndexNow->shouldReceive('submit')
            ->atLeast()->once()
            ->andReturn(['success' => true]);

        $this->app->instance(IndexNowService::class, $mockIndexNow);

        // Creating / Updating a Tour should trigger TourObserver -> IndexNowService::submit
        $tour = Tour::create([
            'name' => 'Observer Test Tour',
            'slug' => 'observer-test-tour',
            'short_desc' => 'Observer test tour summary.',
            'full_desc' => 'Observer test description.',
            'duration' => '4 Hours',
            'status' => 'active',
            'priority' => 1,
        ]);

        $this->assertNotNull($tour->id);

        // Creating / Updating a BlogPost should trigger BlogPostObserver -> IndexNowService::submit
        $post = BlogPost::create([
            'title' => 'Observer Test Blog Post',
            'slug' => 'observer-test-blog-post',
            'excerpt' => 'Observer test blog summary.',
            'ai_summary' => 'Observer test AI summary high density direct answer content.',
            'content' => '<p>Observer test content.</p>',
            'status' => 'published',
            'published_at' => now(),
            'read_time' => 3,
        ]);

        $this->assertNotNull($post->id);
    }

    /**
     * Test scheduled daily IndexNow sweep is registered in console schedule.
     */
    public function test_scheduled_indexnow_sweep_is_registered_in_console_schedule(): void
    {
        $schedule = app()->make(Schedule::class);
        $events = collect($schedule->events());

        $hasIndexNowCommand = $events->contains(function ($event) {
            return str_contains($event->command ?? '', 'seo:indexnow');
        });

        $this->assertTrue($hasIndexNowCommand, "Schedule does not contain daily 'seo:indexnow' command.");
    }
}
