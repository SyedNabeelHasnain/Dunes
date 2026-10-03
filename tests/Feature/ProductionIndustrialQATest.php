<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Tier;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductionIndustrialQATest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_arabic_views_render_logical_css_classes(): void
    {
        $tour = Tour::where('status', 'active')->first();
        $this->assertNotNull($tour);

        // Fetch Arabic tour page
        $response = $this->get('/ar/' . $tour->slug);
        $response->assertStatus(200);

        // Assert logical border-s-4 is present on quick facts and sections
        $content = $response->getContent();
        $this->assertStringContainsString('border-s-4 border-primary', $content);

        // Fetch homepage in Arabic
        $homeResponse = $this->get('/ar');
        $homeResponse->assertStatus(200);
        $homeContent = $homeResponse->getContent();

        // Assert Why Choose Us cards use border-s-4
        $this->assertStringContainsString('border-s-4 border-primary hover:shadow-md', $homeContent);

        // Assert directional icons rotate in RTL
        $this->assertStringContainsString('rtl:rotate-180', $homeContent);
    }

    public function test_booking_submission_records_infants_and_capacity_properly(): void
    {
        $tour = Tour::where('status', 'active')->first();
        $tier = $tour->tiers()->first();

        $payload = [
            'tour_id' => $tour->id,
            'tier_id' => $tier->id,
            'date' => now()->addDays(2)->format('Y-m-d'),
            'adults' => 2,
            'children' => 1,
            'infants' => 1,
            'location' => 'Atlantis The Palm, Dubai',
            'name' => 'QA Industrial Test Guest',
            'email' => 'industrial-qa@example.com',
            'phone' => '+971501234567',
            'payment_method' => 'cash',
        ];

        session(['email_verified_' . md5('industrial-qa@example.com') => true]);

        $response = $this->postJson('/api/v1/booking/checkout', $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('bookings', [
            'email' => 'industrial-qa@example.com',
            'adults' => 2,
            'children' => 1,
            'infants' => 1,
        ]);
    }

    public function test_coupon_discount_calculation_safely_caps_at_subtotal(): void
    {
        $coupon = Coupon::create([
            'name' => 'Super 150 Promo',
            'code' => 'SUPER150',
            'discount_type' => 'percentage',
            'discount_value' => 150.00, // 150% discount edge case
            'status' => 'active',
            'is_active' => true,
        ]);

        $subtotal = 200.00;
        $discount = $coupon->calculateDiscount($subtotal, 2);

        // Discount must never exceed subtotal (cap = 200)
        $this->assertLessThanOrEqual($subtotal, $discount);
        $this->assertEquals(200.00, $discount);
    }

    public function test_multilingual_search_catalog_caches_and_invalidates_properly(): void
    {
        Cache::forget('site_search_modal_catalog_v4_en');
        Cache::forget('site_search_modal_catalog_v4_ar');

        // Access English and Arabic pages to trigger cache build
        $this->get('/')->assertStatus(200);
        $this->get('/ar')->assertStatus(200);

        // Both cache keys should now be cached
        $this->assertTrue(Cache::has('site_search_modal_catalog_v4_en'));
        $this->assertTrue(Cache::has('site_search_modal_catalog_v4_ar'));

        // Admin updates a tour - should invalidate all localized catalogs
        $admin = User::first();
        $this->actingAs($admin);

        $tour = Tour::first();
        $updateResponse = $this->put(route('admin.tours.update', $tour->id), [
            'name' => ['en' => 'Updated Tour QA Name', 'ar' => 'اسم الجولة المحدثة'],
            'category_id' => $tour->category_id,
            'duration' => '4 Hours',
            'priority' => 1,
            'status' => 'active',
            'short_desc' => ['en' => 'Updated short desc', 'ar' => 'وصف قصير محدث'],
            'full_desc' => ['en' => 'Updated full description here', 'ar' => 'وصف كامل هنا'],
        ]);
        $updateResponse->assertSessionHasNoErrors();
        $updateResponse->assertRedirect();

        $this->assertFalse(Cache::has('site_search_modal_catalog_v4_en'));
        $this->assertFalse(Cache::has('site_search_modal_catalog_v4_ar'));
    }

    public function test_blog_post_slug_auto_deduplication(): void
    {
        $admin = User::first();
        $this->actingAs($admin);

        $category = DB::table('blog_categories')->first();
        if (!$category) {
            $catId = DB::table('blog_categories')->insertGetId([
                'name' => 'Safari Tips',
                'slug' => 'safari-tips',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $catId = $category->id;
        }

        // Post 1
        $res1 = $this->post(route('admin.blogs.store'), [
            'title' => ['en' => 'Ultimate Desert Safari Guide', 'ar' => 'دليل رحلات السفاري'],
            'excerpt' => ['en' => 'Excerpt 1', 'ar' => 'مقتطف 1'],
            'content' => ['en' => 'Content 1', 'ar' => 'محتوى 1'],
            'category_id' => $catId,
            'status' => 'published',
        ]);
        $res1->assertRedirect();

        // Post 2 with same title
        $res2 = $this->post(route('admin.blogs.store'), [
            'title' => ['en' => 'Ultimate Desert Safari Guide', 'ar' => 'دليل رحلات السفاري'],
            'excerpt' => ['en' => 'Excerpt 2', 'ar' => 'مقتطف 2'],
            'content' => ['en' => 'Content 2', 'ar' => 'محتوى 2'],
            'category_id' => $catId,
            'status' => 'published',
        ]);
        $res2->assertRedirect();

        $post1 = BlogPost::where('slug', 'ultimate-desert-safari-guide')->first();
        $post2 = BlogPost::where('slug', 'ultimate-desert-safari-guide-1')->first();

        $this->assertNotNull($post1);
        $this->assertNotNull($post2);
        $this->assertNotEquals($post1->id, $post2->id);
    }

    public function test_quick_payment_returns_customer_contact_info(): void
    {
        $admin = User::first();
        $this->actingAs($admin);

        $this->mock(\App\Services\ZiinaPaymentService::class, function ($mock) {
            $mock->shouldReceive('createPaymentIntent')->andReturn([
                'id' => 'pi_test_123',
                'status' => 'pending',
                'redirect_url' => 'https://pay.ziina.com/pi_test_123',
            ]);
        });

        $response = $this->postJson(route('admin.quick-payment'), [
            'amount' => 500,
            'name' => 'John Doe',
            'phone' => '+971509998877',
            'email' => 'john@example.com',
            'description' => 'VIP Quad Dune Setup',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'payment' => [
                'amount' => 500,
                'name' => 'John Doe',
                'phone' => '+971509998877',
            ],
        ]);
    }
}
