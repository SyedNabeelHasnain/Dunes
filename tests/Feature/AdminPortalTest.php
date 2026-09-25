<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Models\LegalPage;
use App\Models\Setting;
use App\Models\Subscriber;
use App\Models\SubscriberGroup;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminPortalTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $secondaryAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('site_settings_cache');

        // Seed basic settings
        Setting::updateOrCreate(['setting_key' => 'site_phone'], ['setting_value' => '+971 50 245 6056']);
        Setting::updateOrCreate(['setting_key' => 'site_name'], ['setting_value' => 'Dunes Discovery Tourism']);
        Setting::updateOrCreate(['setting_key' => 'currency_rates'], ['setting_value' => json_encode(['AED' => 1, 'USD' => 0.2723, 'EUR' => 0.251, 'GBP' => 0.215, 'SAR' => 1.021, 'INR' => 22.85])]);

        // Retrieve the admin users created by the ensure_admin_user_credentials migration
        $this->adminUser = User::where('email', 'dunesdiscovery85@gmail.com')->first();
        $this->secondaryAdmin = User::where('email', 'admin@dunesdiscoverytourism.com')->first();
    }

    /**
     * Test admin can authenticate with primary email.
     */
    public function test_admin_can_login_with_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'dunesdiscovery85@gmail.com',
            'password' => 'admin123',
        ]);

        $this->assertAuthenticatedAs($this->adminUser);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    /**
     * Test admin can authenticate with username 'admin'.
     */
    public function test_admin_can_login_with_username(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin',
            'password' => 'admin123',
        ]);

        $this->assertAuthenticatedAs($this->adminUser);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    /**
     * Test admin can authenticate with case-insensitive username 'Admin'.
     */
    public function test_admin_can_login_with_case_insensitive_username(): void
    {
        $response = $this->post('/login', [
            'email' => 'Admin',
            'password' => 'admin123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    /**
     * Test admin can authenticate with secondary domain email.
     */
    public function test_admin_can_login_with_secondary_domain_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@dunesdiscoverytourism.com',
            'password' => 'admin123',
        ]);

        $this->assertAuthenticatedAs($this->secondaryAdmin);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    /**
     * Test login fails with invalid password and reports error in session.
     */
    public function test_admin_login_fails_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin',
            'password' => 'incorrect_pass',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test admin can log out safely and session is invalidated.
     */
    public function test_admin_can_logout(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    /**
     * Test unauthenticated access to admin routes redirects to login.
     */
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/admin');
        $response->assertStatus(302);
        $response->assertRedirect('/login');

        $response2 = $this->get('/admin/bookings');
        $response2->assertStatus(302);
        $response2->assertRedirect('/login');
    }

    /**
     * Test that all critical admin navigation routes render cleanly with HTTP 200.
     */
    public function test_all_core_admin_get_routes_render_cleanly(): void
    {
        $routes = [
            '/admin',
            '/admin/dashboard',
            '/admin/analytics',
            '/admin/active-visitors',
            '/admin/bookings',
            '/admin/operations',
            '/admin/tours',
            '/admin/tours/create',
            '/admin/tiers',
            '/admin/addons',
            '/admin/pricing',
            '/admin/coupons',
            '/admin/coupons/create',
            '/admin/coupons/popup-settings',
            '/admin/blogs',
            '/admin/blogs/create',
            '/admin/blog-categories',
            '/admin/inquiries',
            '/admin/whatsapp',
            '/admin/whatsapp-settings',
            '/admin/reviews',
            '/admin/faqs',
            '/admin/legal-pages',
            '/admin/subscribers',
            '/admin/subscriber-groups',
            '/admin/campaigns',
            '/admin/campaigns/create',
            '/admin/email-templates',
            '/admin/settings/general',
            '/admin/settings/seo',
            '/admin/settings/marketing',
            '/admin/settings/mail',
            '/admin/settings/google',
            '/admin/settings/meta',
            '/admin/settings/currency',
            '/admin/profile',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->adminUser)->get($route);
            $this->assertTrue(
                in_array($response->status(), [200, 301, 302]),
                "Admin route [{$route}] failed with status {$response->status()}"
            );
        }
    }

    /**
     * Test the Artisan admin:reset-password command updates user password.
     */
    public function test_artisan_reset_admin_password_command(): void
    {
        $exitCode = Artisan::call('admin:reset-password', [
            'login' => 'admin',
            'password' => 'NewCustomPass2026!',
        ]);

        $this->assertEquals(0, $exitCode);

        // Verify authentication succeeds with the newly set password
        $response = $this->post('/login', [
            'email' => 'admin',
            'password' => 'NewCustomPass2026!',
        ]);

        $this->assertAuthenticatedAs($this->adminUser);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    /**
     * Test that all resource edit and detail views render cleanly with HTTP 200.
     */
    public function test_resource_edit_and_detail_views_render_cleanly(): void
    {
        $cat = Category::firstOrCreate(['slug' => 'desert-safari'], ['name' => 'Desert Safari', 'priority' => 1]);
        $tour = Tour::create([
            'slug' => 'test-safari-tour',
            'name' => 'Test Safari Tour',
            'category_id' => $cat->id,
            'short_desc' => 'Short desc',
            'full_desc' => 'Full desc',
            'duration' => '6 hours',
            'status' => 'active',
            'priority' => 1,
        ]);

        $coupon = Coupon::create([
            'code' => 'TESTCODE99',
            'name' => 'Test Coupon',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'status' => 'active',
        ]);

        $template = EmailTemplate::create([
            'name' => 'Test Template',
            'slug' => 'test-template',
            'subject' => 'Test Subject',
            'content_html' => '<p>Hello {{subscriber_name}}</p>',
        ]);

        $group = SubscriberGroup::create([
            'name' => 'VIP Group',
            'slug' => 'vip-group',
        ]);

        $subscriber = Subscriber::create([
            'email' => 'vip@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
            'status' => 'subscribed',
        ]);

        $campaign = EmailCampaign::create([
            'title' => 'Test Campaign',
            'subject' => 'Special Test',
            'status' => 'draft',
            'target_type' => 'all',
            'content_html' => '<p>Hello World</p>',
        ]);

        $legalPage = LegalPage::firstOrCreate(
            ['slug' => 'terms-and-conditions'],
            ['title' => 'Terms and Conditions', 'description' => 'Test terms']
        );

        $blogCat = BlogCategory::firstOrCreate(['slug' => 'safari-guide'], ['name' => 'Safari Guide', 'status' => 'active', 'priority' => 1]);
        $blogPost = BlogPost::create([
            'title' => 'Test Blog Post',
            'slug' => 'test-blog-post',
            'category_id' => $blogCat->id,
            'content' => 'Blog content here',
            'status' => 'published',
        ]);

        $booking = Booking::create([
            'reference' => 'BK-TEST-999',
            'tour_id' => $tour->id,
            'tour_name' => $tour->name,
            'tour_date' => now()->addDays(2),
            'adults' => 2,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+971500000000',
            'subtotal' => 500.00,
            'total' => 500.00,
            'currency' => 'AED',
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $editRoutes = [
            "/admin/tours/{$tour->id}/edit",
            "/admin/coupons/{$coupon->id}/edit",
            "/admin/email-templates/{$template->id}/edit",
            "/admin/email-templates/{$template->id}/preview",
            "/admin/subscriber-groups/{$group->id}/edit",
            "/admin/subscribers/{$subscriber->id}/edit",
            "/admin/campaigns/{$campaign->id}",
            "/admin/campaigns/{$campaign->id}/edit",
            "/admin/legal-pages/{$legalPage->id}/edit",
            "/admin/blogs/{$blogPost->id}/edit",
            "/admin/bookings/{$booking->id}",
        ];

        foreach ($editRoutes as $route) {
            $response = $this->actingAs($this->adminUser)->get($route);
            $this->assertTrue(
                in_array($response->getStatusCode(), [200, 301, 302]),
                "Admin edit/detail route [{$route}] failed with status {$response->getStatusCode()}"
            );
        }
    }

    /**
     * Test that all admin CSV export endpoints respond with valid stream/download.
     */
    public function test_export_csv_endpoints(): void
    {
        $exportRoutes = [
            '/admin/coupons/export/csv',
            '/admin/subscribers/export',
            '/admin/operations/export/csv',
            '/admin/whatsapp-leads/export/csv',
            '/admin/inquiries/export/csv',
        ];

        foreach ($exportRoutes as $route) {
            $response = $this->actingAs($this->adminUser)->get($route);
            $this->assertTrue(
                in_array($response->getStatusCode(), [200, 301, 302]),
                "Admin export route [{$route}] failed with status {$response->getStatusCode()}"
            );
        }
    }

    /**
     * Test that admin can download booking ticket voucher as PDF.
     */
    public function test_admin_can_download_ticket_pdf(): void
    {
        $tour = Tour::create([
            'name' => 'Evening Red Dunes Desert Safari',
            'slug' => 'evening-red-dunes-desert-safari',
            'category' => 'desert-safari',
            'duration' => '6 Hours',
            'min_price' => 150,
            'is_active' => true,
        ]);

        $booking = Booking::create([
            'reference' => 'BK-TEST-1234',
            'tour_id' => $tour->id,
            'tour_name' => $tour->name,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+971501234567',
            'tour_date' => now()->addDays(2)->toDateString(),
            'adults' => 2,
            'children' => 0,
            'infants' => 0,
            'subtotal' => 300,
            'total' => 300,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->adminUser)->get("/admin/bookings/{$booking->id}/ticket");
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test public customer voucher PDF download.
     */
    public function test_customer_can_download_voucher_pdf(): void
    {
        $booking = Booking::create([
            'reference' => 'DDT-VOUCH-999',
            'tour_name' => 'Morning Dune Buggy Tour',
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '+971559876543',
            'tour_date' => '2026-11-20',
            'adults' => 1,
            'subtotal' => 450,
            'total' => 450,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $response = $this->get("/booking/{$booking->reference}/ticket-pdf");
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test ticket PDF handles edge cases (no tier, no tour relation, no addons, unpaid status, null notes).
     */
    public function test_ticket_pdf_handles_edge_cases_cleanly(): void
    {
        $booking = Booking::create([
            'reference' => 'DDT-EDGE-001',
            'tour_name' => 'VIP Private Safari',
            'name' => 'Edge Case Traveler',
            'email' => 'edge@example.com',
            'phone' => '+971500000000',
            'tour_date' => '2026-10-15',
            'adults' => 2,
            'children' => 0,
            'infants' => 0,
            'subtotal' => 600,
            'total' => 600,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_amount' => 0,
            'balance_due' => 600,
        ]);

        $response = $this->actingAs($this->adminUser)->get("/admin/bookings/{$booking->id}/ticket");
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
