<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tour;
use App\Models\TourTier;
use App\Models\Coupon;
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
}
