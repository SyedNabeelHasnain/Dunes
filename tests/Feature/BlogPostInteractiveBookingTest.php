<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Tier;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BlogPostInteractiveBookingTest extends TestCase
{
    use RefreshDatabase;

    protected Category $safariCat;
    protected Tour $eveningTour;
    protected Tour $buggyTour;
    protected Tour $quadTour;
    protected BlogCategory $blogCat;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        Setting::updateOrCreate(['setting_key' => 'site_whatsapp'], ['setting_value' => '971502456056']);

        $this->safariCat = Category::create([
            'name' => 'Desert Safari',
            'slug' => 'desert-safari',
            'icon' => 'bi-sun',
            'priority' => 1,
        ]);

        $tier1 = Tier::create([
            'name' => 'Standard Sharing',
            'slug' => 'standard-sharing',
            'priority' => 1,
        ]);

        $tier2 = Tier::create([
            'name' => 'VIP Private',
            'slug' => 'vip-private',
            'priority' => 2,
        ]);

        $this->eveningTour = Tour::create([
            'name' => 'Evening Desert Safari Dubai',
            'slug' => 'evening-desert-safari-dubai',
            'category_id' => $this->safariCat->id,
            'duration' => '6 Hours',
            'short_desc' => 'Dune bashing, camel ride, BBQ buffet dinner, and 3 live stage shows in Lahbab red dunes.',
            'status' => 'active',
            'priority' => 1,
            'rating' => 4.9,
            'review_count' => 1247,
            'is_bestseller' => true,
            'is_featured' => true,
        ]);
        $this->eveningTour->tiers()->attach($tier1->id, ['price' => 99, 'old_price' => 150, 'price_type' => 'per_person']);

        $this->buggyTour = Tour::create([
            'name' => 'Dune Buggy Rental Dubai',
            'slug' => 'dune-buggy-rental-dubai',
            'category_id' => $this->safariCat->id,
            'duration' => '3 Hours',
            'short_desc' => 'Self-drive 1000cc Can-Am Maverick buggies in high red dunes.',
            'status' => 'active',
            'priority' => 2,
            'rating' => 4.9,
            'review_count' => 642,
            'is_bestseller' => true,
            'is_featured' => true,
        ]);
        $this->buggyTour->tiers()->attach($tier1->id, ['price' => 599, 'old_price' => 799, 'price_type' => 'per_vehicle']);

        $this->quadTour = Tour::create([
            'name' => 'Desert Safari with Quad Biking',
            'slug' => 'desert-safari-quad-biking-dubai',
            'category_id' => $this->safariCat->id,
            'duration' => '7 Hours',
            'short_desc' => 'ATV quad bike adventure plus complete evening safari.',
            'status' => 'active',
            'priority' => 3,
            'rating' => 4.8,
            'review_count' => 634,
            'is_bestseller' => true,
            'is_featured' => true,
        ]);
        $this->quadTour->tiers()->attach($tier1->id, ['price' => 199, 'old_price' => 299, 'price_type' => 'per_person']);

        $this->blogCat = BlogCategory::create([
            'name' => 'Desert Adventures',
            'slug' => 'desert-adventures',
            'status' => 'active',
            'priority' => 1,
        ]);
    }

    public function test_blog_post_matches_tour_by_explicit_content_link(): void
    {
        $post = BlogPost::create([
            'title' => 'Top 10 High-Speed Adventures in the UAE Sands',
            'slug' => 'high-speed-adventures-uae',
            'category_id' => $this->blogCat->id,
            'content' => '<p>Intro text</p><blockquote class="border-start border-4 border-primary bg-light p-3 my-4"><p><strong>Special VIP Recommendation:</strong> Check our <a href="/dune-buggy-rental-dubai">Dune Buggy Rental</a></p></blockquote><p>Outro text</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('blog.show', $post->slug));

        $response->assertStatus(200);
        $response->assertSee('Dune Buggy Rental Dubai');
        $response->assertSee('AED 599');
        $response->assertSee('$store.modal.open(\'booking\', { tourId: ' . $this->buggyTour->id . ' })', false);
    }

    public function test_blog_post_matches_tour_by_title_and_slug_keywords(): void
    {
        $post = BlogPost::create([
            'title' => 'Quad Biking in Dubai: The Complete 2026 ATV Rider Guide',
            'slug' => 'quad-biking-dubai-atv-guide',
            'category_id' => $this->blogCat->id,
            'content' => '<p>Introductory paragraph about ATVs.</p><h2>Comparison Table</h2><table><tr><td>Col</td></tr></table><p>Concluding remarks.</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('blog.show', $post->slug));

        $response->assertStatus(200);
        $response->assertSee('Desert Safari with Quad Biking');
        $response->assertSee('AED 199');
        $response->assertSee('$store.modal.open(\'booking\', { tourId: ' . $this->quadTour->id . ' })', false);
    }

    public function test_blog_post_renders_in_article_booking_card_with_whatsapp_and_booking_ctas(): void
    {
        $post = BlogPost::create([
            'title' => 'The Ultimate Evening Desert Safari Guide 2026',
            'slug' => 'ultimate-evening-desert-safari-guide-2026',
            'category_id' => $this->blogCat->id,
            'content' => '<p>Planning a desert safari in Dubai...</p><blockquote class="border-start border-4 border-primary bg-light p-3 my-4"><p><strong>Special VIP Recommendation:</strong> Secure your date early with our official <a href="/evening-desert-safari-dubai">Evening Desert Safari Dubai</a></p></blockquote><p>Detailed guide content continues here.</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('blog.show', $post->slug));

        $response->assertStatus(200);
        // Verify in-article card container
        $response->assertSee('Recommended Experience');
        $response->assertSee('DET Licensed Operator');
        // Verify Dual CTAs
        $response->assertSee('Book Online');
        $response->assertSee('WhatsApp');
        // Verify WhatsApp inquiry prefilled link
        $response->assertSee('https://wa.me/971502456056?text=', false);
        // Verify Currency Switcher support
        $response->assertSee('data-aed="99"', false);
    }

    public function test_blog_post_renders_sidebar_and_mobile_sticky_cards(): void
    {
        $post = BlogPost::create([
            'title' => 'Dune Buggy Riding Rules in Dubai Red Dunes',
            'slug' => 'dune-buggy-riding-rules-dubai',
            'category_id' => $this->blogCat->id,
            'content' => '<p>Paragraph 1</p><p>Paragraph 2</p><p>Paragraph 3</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('blog.show', $post->slug));

        $response->assertStatus(200);
        // Verify contextual sidebar widget
        $response->assertSee('Contextual Tour Booking Sidebar Widget', false);
        // Verify mobile sticky booking bar
        $response->assertSee('Mobile Sticky Booking Bar', false);
        $response->assertSee('role="region"', false);
        $response->assertSee('aria-label="Tour booking quick bar"', false);
    }
}
