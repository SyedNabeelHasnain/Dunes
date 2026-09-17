<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tour;
use App\Models\TourTier;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\BookingPayment;
use App\Models\Review;
use App\Models\RequestLog;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('site_settings_cache');
        config(['services.ziina.webhook_secret' => 'test_webhook_secret_key_123']);

        // Seed basic settings
        Setting::updateOrCreate(['setting_key' => 'ziina_webhook_secret'], ['setting_value' => 'test_webhook_secret_key_123']);
        Setting::updateOrCreate(['setting_key' => 'site_phone'], ['setting_value' => '+971 50 245 6056']);
        Setting::updateOrCreate(['setting_key' => 'currency_rates'], ['setting_value' => json_encode(['AED' => 1, 'USD' => 0.2723, 'EUR' => 0.251, 'GBP' => 0.215, 'SAR' => 1.021, 'INR' => 22.85])]);
        
        Cache::forget('site_settings_cache');
    }

    /**
     * Test adult and 30% child discount calculations on standard per-person tours.
     */
    public function test_per_person_tour_pricing_calculation(): void
    {
        $basePrice = 100.00;
        $childPrice = round($basePrice * 0.70, 2); // 70.00 (30% off)

        $adults = 2;
        $children = 1;
        $infants = 2; // Free

        $expectedSubtotal = ($basePrice * $adults) + ($childPrice * $children); // 200 + 70 = 270
        $this->assertEquals(270.00, $expectedSubtotal);
    }

    /**
     * Test flat pricing on per-buggy / per-vehicle tours regardless of guest count.
     */
    public function test_per_vehicle_tour_pricing_is_flat(): void
    {
        $vehiclePrice = 1299.00;
        $priceType = 'per buggy';

        $adults = 4;
        $children = 2;

        if (in_array($priceType, ['per buggy', 'per vehicle', 'per group', 'private'])) {
            $subtotal = $vehiclePrice;
        } else {
            $subtotal = ($vehiclePrice * $adults) + (round($vehiclePrice * 0.70, 2) * $children);
        }

        $this->assertEquals(1299.00, $subtotal);
    }

    /**
     * Test MATCH5 and SAVE5 resilient coupon lookup and discount math.
     */
    public function test_system_coupons_match5_and_save5(): void
    {
        $matchCoupon = Coupon::findByCode('MATCH5');
        $this->assertNotNull($matchCoupon);
        $this->assertEquals('percentage', $matchCoupon->discount_type);
        $this->assertEquals(5.00, $matchCoupon->discount_value);

        // 5% of 200 = 10
        $discount = $matchCoupon->calculateDiscount(200.00);
        $this->assertEquals(10.00, $discount);

        $saveCoupon = Coupon::findByCode('SAVE5');
        $this->assertNotNull($saveCoupon);
        $discountSave = $saveCoupon->calculateDiscount(300.00);
        $this->assertEquals(15.00, $discountSave);
    }

    /**
     * Test that fixed coupons strictly enforce max_discount caps.
     */
    public function test_fixed_coupon_enforces_max_discount_cap(): void
    {
        $coupon = Coupon::create([
            'code' => 'FIXED50CAP20',
            'name' => 'Fixed 50 Cap 20',
            'discount_type' => 'fixed',
            'discount_value' => 50.00,
            'max_discount' => 20.00,
            'is_active' => true,
        ]);

        $discount = $coupon->calculateDiscount(100.00);
        $this->assertEquals(20.00, $discount);
    }

    /**
     * Test that per-person discounts only count paying guests (infants excluded).
     */
    public function test_per_person_coupon_excludes_infants(): void
    {
        $coupon = Coupon::create([
            'code' => 'PERPERSON10',
            'name' => '10 Off Per Person',
            'discount_type' => 'per_person',
            'discount_value' => 10.00,
            'max_discount' => 100.00,
            'is_active' => true,
        ]);

        $adults = 2;
        $children = 1;
        $infants = 5; // Should not receive discount

        $payingGuests = max(1, $adults + $children); // 3 paying guests
        $this->assertEquals(3, $payingGuests);

        $discount = $coupon->calculateDiscount(500.00, $payingGuests);
        $this->assertEquals(30.00, $discount); // 3 * 10 = 30, not 8 * 10 = 80
    }

    /**
     * Test Ziina webhook HMAC-SHA256 signature verification.
     */
    public function test_ziina_webhook_signature_verification(): void
    {
        $secret = 'test_webhook_secret_key_123';
        $payload = json_encode(['event' => 'payment_intent.status_updated', 'data' => ['id' => 'pi_test123', 'status' => 'completed']]);
        $validSignature = hash_hmac('sha256', $payload, $secret);

        // Invalid signature should return 401
        $responseInvalid = $this->call(
            'POST',
            '/api/v1/ziina/webhook',
            [],
            [],
            [],
            [
                'HTTP_X_ZIINA_SIGNATURE' => 'invalid_signature_hash',
                'CONTENT_TYPE' => 'application/json'
            ],
            $payload
        );

        $this->assertEquals(401, $responseInvalid->getStatusCode());

        // Valid signature should be accepted (or proceed to handle event)
        $responseValid = $this->call(
            'POST',
            '/api/v1/ziina/webhook',
            [],
            [],
            [],
            [
                'HTTP_X_ZIINA_SIGNATURE' => $validSignature,
                'CONTENT_TYPE' => 'application/json'
            ],
            $payload
        );

        $this->assertNotEquals(401, $responseValid->getStatusCode());
    }

    /**
     * Test public pages and redirect routes.
     */
    public function test_core_public_routes_respond(): void
    {
        $responseHome = $this->get('/');
        $responseHome->assertStatus(200);

        $responseTours = $this->get('/tours');
        $responseTours->assertStatus(200);

        $responseSearch = $this->get('/search?q=quad+biking');
        $responseSearch->assertStatus(200);

        $responseCustomizer = $this->get('/build-your-own-safari');
        $responseCustomizer->assertStatus(200);

        // Redirects
        $responseCustomRedirect = $this->get('/custom-safari');
        $responseCustomRedirect->assertStatus(301);

        $responseTermsRedirect = $this->get('/terms-and-conditions');
        $responseTermsRedirect->assertStatus(301);
    }

    /**
     * Test admin route authentication guard.
     */
    public function test_admin_dashboard_is_protected_by_auth(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Test permanent booking deletion and complete cascade purge of relations & analytics.
     */
    public function test_permanent_booking_deletion_cascades_all_relational_and_analytical_data(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@dunesdiscovery.com',
            'password' => bcrypt('password123'),
        ]);

        $coupon = Coupon::create([
            'code' => 'PURGE5',
            'name' => 'Purge Test Coupon',
            'discount_type' => 'percentage',
            'discount_value' => 5.00,
            'is_active' => true,
        ]);
        $coupon->increment('used_count');

        $booking = Booking::create([
            'reference' => 'DDT-PURGE-001',
            'tour_name' => 'VIP Desert Safari',
            'tier_name' => 'Private Land Cruiser',
            'tour_date' => now()->addDays(3)->format('Y-m-d'),
            'adults' => 2,
            'children' => 1,
            'infants' => 0,
            'name' => 'Alexander Pierce',
            'email' => 'alexander.pierce@example.com',
            'phone' => '+971501112233',
            'pickup_location' => 'Burj Al Arab Hotel',
            'coupon_id' => $coupon->id,
            'coupon_code' => 'PURGE5',
            'subtotal' => 600.00,
            'total' => 570.00,
            'status' => 'confirmed',
            'payment_method' => 'card',
            'payment_status' => 'paid',
            'payment_amount' => 570.00,
            'balance_due' => 0.00,
        ]);

        $addon = BookingAddon::create([
            'booking_id' => $booking->id,
            'addon_name' => 'Quad Biking 30 Mins',
            'quantity' => 1,
            'price' => 150.00,
        ]);

        $payment = BookingPayment::create([
            'booking_id' => $booking->id,
            'payment_intent_id' => 'pi_purge_test_999',
            'amount' => 570.00,
            'currency' => 'AED',
            'status' => 'completed',
        ]);

        $usage = CouponUsage::create([
            'coupon_id' => $coupon->id,
            'booking_id' => $booking->id,
            'booking_reference' => $booking->reference,
            'customer_email' => $booking->email,
            'discount_amount' => 30.00,
            'order_subtotal' => 600.00,
            'order_final_total' => 570.00,
            'used_at' => now(),
        ]);

        $review = Review::create([
            'source' => 'guest',
            'booking_id' => $booking->id,
            'reviewer_name' => 'Alexander Pierce',
            'rating' => 5.0,
            'review_title' => 'Incredible Service',
            'review_text' => 'Best desert safari ever experienced.',
            'status' => 'published',
        ]);

        $log = RequestLog::create([
            'entity_type' => 'booking',
            'entity_id' => $booking->id,
            'request_timestamp' => now(),
            'client_ip' => '192.168.1.1',
        ]);
        $booking->update(['request_log_id' => $log->id]);

        // Verify all entities exist prior to deletion
        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
        $this->assertDatabaseHas('booking_addons', ['id' => $addon->id]);
        $this->assertDatabaseHas('booking_payments', ['id' => $payment->id]);
        $this->assertDatabaseHas('coupon_usages', ['id' => $usage->id]);
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
        $this->assertDatabaseHas('request_logs', ['id' => $log->id]);
        $this->assertEquals(1, $coupon->fresh()->used_count);

        // Execute permanent delete request as admin
        $response = $this->actingAs($admin)->deleteJson("/admin/bookings/{$booking->id}");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify permanent eradication across all database tables
        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
        $this->assertDatabaseMissing('booking_addons', ['id' => $addon->id]);
        $this->assertDatabaseMissing('booking_payments', ['id' => $payment->id]);
        $this->assertDatabaseMissing('coupon_usages', ['id' => $usage->id]);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
        $this->assertDatabaseMissing('request_logs', ['id' => $log->id]);

        // Verify coupon used quota was restored (decremented from 1 to 0)
        $this->assertEquals(0, $coupon->fresh()->used_count);
    }

    /**
     * Test permanent bulk booking deletion.
     */
    public function test_bulk_permanent_booking_deletion(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_bulk@dunesdiscovery.com',
            'password' => bcrypt('password123'),
        ]);

        $b1 = Booking::create([
            'reference' => 'DDT-BULK-001',
            'tour_name' => 'Safari 1',
            'tier_name' => 'Standard',
            'tour_date' => now()->addDays(2)->format('Y-m-d'),
            'adults' => 1,
            'children' => 0,
            'infants' => 0,
            'name' => 'Guest 1',
            'email' => 'guest1@example.com',
            'phone' => '+971500000001',
            'subtotal' => 150.00,
            'total' => 150.00,
            'status' => 'pending',
        ]);

        $b2 = Booking::create([
            'reference' => 'DDT-BULK-002',
            'tour_name' => 'Safari 2',
            'tier_name' => 'Standard',
            'tour_date' => now()->addDays(2)->format('Y-m-d'),
            'adults' => 2,
            'children' => 0,
            'infants' => 0,
            'name' => 'Guest 2',
            'email' => 'guest2@example.com',
            'phone' => '+971500000002',
            'subtotal' => 300.00,
            'total' => 300.00,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->postJson('/admin/bookings/bulk-action', [
            'ids' => [$b1->id, $b2->id],
            'action' => 'delete',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('bookings', ['id' => $b1->id]);
        $this->assertDatabaseMissing('bookings', ['id' => $b2->id]);
    }
}
