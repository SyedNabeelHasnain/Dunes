<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Coupon;
use App\Models\Tour;
use App\Models\User;
use App\Models\WhatsappInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSpotlightTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::where('email', 'dunesdiscovery85@gmail.com')->first() ?: User::factory()->create();
    }

    public function test_guest_cannot_access_spotlight_api(): void
    {
        $response = $this->get(route('admin.api.spotlight', ['q' => 'safari']));
        $response->assertRedirect(route('login'));
    }

    public function test_short_query_returns_empty_results(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson(route('admin.api.spotlight', ['q' => 'a']));
        $response->assertStatus(200);
        $response->assertJson([
            'total' => 0,
            'categories' => [],
        ]);
    }

    public function test_spotlight_searches_bookings_tours_coupons_and_inquiries(): void
    {
        // 1. Create a category and tour
        $cat = Category::create([
            'name' => 'Desert Safaris',
            'slug' => 'desert-safari',
            'status' => 'active',
        ]);

        $tour = Tour::create([
            'name' => 'Royal Platinum Dunes Safari',
            'slug' => 'royal-platinum-dunes-safari',
            'category_id' => $cat->id,
            'duration' => '6 Hours',
            'status' => 'active',
            'price' => 450,
        ]);

        // 2. Create a booking
        $booking = Booking::create([
            'reference' => 'DT-2026-9999',
            'name' => 'Alexander Hamilton',
            'email' => 'alex@example.com',
            'phone' => '+971501234567',
            'tour_name' => 'Royal Platinum Dunes Safari',
            'tour_date' => now()->addDays(5)->toDateString(),
            'subtotal' => 900,
            'total' => 900,
            'status' => 'confirmed',
        ]);

        // 3. Create a coupon
        $coupon = Coupon::create([
            'code' => 'PLATINUM50',
            'name' => 'Platinum Safari Special',
            'discount_type' => 'percentage',
            'discount_value' => 50,
            'status' => 'active',
        ]);

        // 4. Create an inquiry
        $contact = Contact::create([
            'name' => 'Johnathan VIP',
            'email' => 'johnathan@example.com',
            'phone' => '+971509998877',
            'subject' => 'VIP Corporate Safari Booking',
            'message' => 'Looking to book for 20 executives.',
            'status' => 'new',
        ]);

        // Query by tour name keyword
        $resTour = $this->actingAs($this->adminUser)->getJson(route('admin.api.spotlight', ['q' => 'Platinum']));
        $resTour->assertStatus(200);
        $resTour->assertJsonStructure([
            'query',
            'total',
            'categories',
        ]);
        $data = $resTour->json();
        $this->assertGreaterThan(0, $data['total']);

        // Query by booking reference
        $resBooking = $this->actingAs($this->adminUser)->getJson(route('admin.api.spotlight', ['q' => 'DT-2026-9999']));
        $resBooking->assertStatus(200);
        $bookingData = $resBooking->json();
        $this->assertGreaterThan(0, $bookingData['total']);
        $this->assertEquals('bookings', $bookingData['categories'][0]['key']);
        $this->assertStringContainsString('Alexander Hamilton', $bookingData['categories'][0]['items'][0]['title']);

        // Query by coupon code
        $resCoupon = $this->actingAs($this->adminUser)->getJson(route('admin.api.spotlight', ['q' => 'PLATINUM50']));
        $resCoupon->assertStatus(200);
        $couponData = $resCoupon->json();
        $this->assertGreaterThan(0, $couponData['total']);
    }

    public function test_spotlight_respects_category_filter(): void
    {
        $cat = Category::create([
            'name' => 'Morning Safaris',
            'slug' => 'morning-safaris',
            'status' => 'active',
        ]);

        Tour::create([
            'name' => 'Morning Dune Adventure',
            'slug' => 'morning-dune-adventure',
            'category_id' => $cat->id,
            'status' => 'active',
        ]);

        Booking::create([
            'reference' => 'DT-MORN-001',
            'name' => 'Morning Guest',
            'email' => 'morn@example.com',
            'phone' => '+971501112233',
            'tour_name' => 'Morning Dune Adventure',
            'tour_date' => now()->addDays(2)->toDateString(),
            'subtotal' => 350,
            'total' => 350,
            'status' => 'confirmed',
        ]);

        // Filter only tours
        $resToursOnly = $this->actingAs($this->adminUser)->getJson(route('admin.api.spotlight', [
            'q' => 'Morning',
            'category' => 'tours',
        ]));
        $resToursOnly->assertStatus(200);
        $toursData = $resToursOnly->json();
        $this->assertEquals(1, count($toursData['categories']));
        $this->assertEquals('tours', $toursData['categories'][0]['key']);
    }
}
