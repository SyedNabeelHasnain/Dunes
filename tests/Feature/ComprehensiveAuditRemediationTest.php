<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Setting;
use App\Models\Tier;
use App\Models\Tour;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprehensiveAuditRemediationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        app(SettingsService::class)->clearCache();
    }

    public function test_normalize_google_ads_id_handles_various_formats(): void
    {
        $this->assertEquals('AW-7927294548', normalize_google_ads_id('7927294548'));
        $this->assertEquals('AW-7927294548', normalize_google_ads_id('792-729-4548'));
        $this->assertEquals('AW-7927294548', normalize_google_ads_id('aw-7927294548'));
        $this->assertEquals('AW-7927294548', normalize_google_ads_id('AW-7927294548'));
        $this->assertEquals('', normalize_google_ads_id(''));
        $this->assertEquals('', normalize_google_ads_id(null));
    }

    public function test_sanitize_csv_cell_neutralizes_spreadsheet_formulas(): void
    {
        $this->assertEquals("'=cmd|' /C calc'!A0", sanitize_csv_cell("=cmd|' /C calc'!A0"));
        $this->assertEquals("'+12345", sanitize_csv_cell("+12345"));
        $this->assertEquals("'-12345", sanitize_csv_cell("-12345"));
        $this->assertEquals("'@SUM(1+1)", sanitize_csv_cell("@SUM(1+1)"));
        $this->assertEquals("+971502456056", sanitize_csv_cell("+971502456056")); // E.164 phone preserved
        $this->assertEquals("Normal Text", sanitize_csv_cell("Normal Text"));
    }

    public function test_tier_model_recognizes_unit_price_types_and_resolves_capacity(): void
    {
        $this->assertTrue(Tier::isUnitPriceType('per buggy'));
        $this->assertTrue(Tier::isUnitPriceType('per vehicle'));
        $this->assertTrue(Tier::isUnitPriceType('per tent'));
        $this->assertTrue(Tier::isUnitPriceType('per package'));
        $this->assertTrue(Tier::isUnitPriceType('private'));
        $this->assertFalse(Tier::isUnitPriceType('per person'));

        $buggyTier = new Tier(['name' => 'Can-Am Maverick 2-Seater', 'price_type' => 'per buggy', 'guest_count' => 2]);
        $this->assertEquals(2, Tier::resolveUnitCapacity($buggyTier));

        $vehicleTier = new Tier(['name' => 'Private Land Cruiser 6-Seater', 'price_type' => 'per vehicle']);
        $this->assertEquals(6, Tier::resolveUnitCapacity($vehicleTier));

        $tentTier = new Tier(['name' => 'Overnight Bedouin Tent', 'price_type' => 'per tent']);
        $this->assertEquals(2, Tier::resolveUnitCapacity($tentTier));
    }

    public function test_dynamic_welcome_coupon_validates_without_db_row_creation(): void
    {
        $uniqueCode = 'FIRST25-TESTCODE';

        // Ensure database does not have this code
        $this->assertDatabaseMissing('coupons', ['code' => $uniqueCode]);

        $response = $this->postJson('/api/v1/coupon/validate', [
            'code' => $uniqueCode,
            'subtotal' => 200.00,
            'email' => 'newvisitor' . uniqid() . '@example.com',
            'adults' => 2,
            'children' => 0,
            'guests' => 2,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'coupon' => [
                'code' => $uniqueCode,
                'discount_type' => 'percentage',
                'discount_value' => 25,
            ],
        ]);

        // Must NOT create an orphaned database row
        $this->assertDatabaseMissing('coupons', ['code' => $uniqueCode]);
    }

    public function test_dynamic_welcome_coupon_rejects_returning_customers(): void
    {
        $returningEmail = 'returning.customer.' . uniqid() . '@example.com';
        $tour = Tour::first();

        // Create an existing completed booking for this email
        Booking::create([
            'reference' => 'DDT-RET-' . strtoupper(uniqid()),
            'tour_id' => $tour->id,
            'tour_name' => $tour->name,
            'tour_date' => now()->subDays(5)->format('Y-m-d'),
            'name' => 'Returning Customer',
            'email' => $returningEmail,
            'phone' => '+971501112233',
            'location' => 'Dubai Hotel',
            'adults' => 2,
            'children' => 0,
            'subtotal' => 300.00,
            'total' => 300.00,
            'currency' => 'AED',
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
        ]);

        $response = $this->postJson('/api/v1/coupon/validate', [
            'code' => 'FIRST25-RETURNING',
            'subtotal' => 200.00,
            'email' => $returningEmail,
            'adults' => 2,
            'children' => 0,
            'guests' => 2,
        ]);

        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('first-time customers', $response->json('message'));
    }

    public function test_recovery_checkout_recalculates_authoritative_pricing_and_handles_addons(): void
    {
        $tour = Tour::first();
        $tier = $tour->tiers()->first();

        $booking = Booking::create([
            'reference' => 'DDT-REC-' . strtoupper(uniqid()),
            'tour_id' => $tour->id,
            'tier_id' => $tier->id,
            'tour_name' => $tour->name,
            'tier_name' => $tier->name,
            'tour_date' => now()->addDays(3)->format('Y-m-d'),
            'adults' => 2,
            'children' => 0,
            'subtotal' => 10.00, // Stale/tampered price
            'total' => 10.00,
            'currency' => 'AED',
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'name' => 'Recovery Tester',
            'email' => 'tester@example.com',
            'phone' => '+971501112233',
            'location' => 'Dubai Hotel',
            'payment_method' => 'cash',
        ]);

        $response = $this->get('/checkout/' . $booking->reference);
        $response->assertOk();
        $response->assertViewIs('booking.recover');

        // Fresh database total should be recalculated authoritatively
        $booking->refresh();
        $expectedTotal = (float) $tier->pivot->price * 2;
        $this->assertEquals($expectedTotal, (float) $booking->total);
    }

    public function test_recovery_checkout_redirects_invalid_reference(): void
    {
        $response = $this->get('/checkout/INVALID-REFERENCE-999');
        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');
    }

    public function test_recovery_checkout_redirects_completed_booking(): void
    {
        $tour = Tour::first();
        $booking = Booking::create([
            'reference' => 'DDT-DONE-' . strtoupper(uniqid()),
            'tour_id' => $tour->id,
            'tour_name' => $tour->name,
            'tour_date' => now()->addDays(2)->format('Y-m-d'),
            'name' => 'Done Customer',
            'email' => 'done@example.com',
            'phone' => '+971501112233',
            'location' => 'Dubai Hotel',
            'adults' => 2,
            'children' => 0,
            'subtotal' => 300.00,
            'total' => 300.00,
            'currency' => 'AED',
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
        ]);

        $response = $this->get('/checkout/' . $booking->reference);
        $response->assertRedirect(localized_route('booking.voucher', ['reference' => $booking->reference]));
    }

    public function test_thankyou_page_protects_against_unverified_reference(): void
    {
        // Visiting /thankyou with an unverified reference renders fallback and suppresses conversion scripts
        $response = $this->get('/thankyou?ref=DDT-FAKE-REF-123');
        $response->assertOk();
        $response->assertSee('Booking Not Found');
        $response->assertDontSee("window.gtag('event', 'conversion'");
        $response->assertDontSee("fbq('track', 'Purchase'");
    }

    public function test_custom_safari_builder_enforces_even_adults_for_overnight(): void
    {
        $response = $this->postJson('/api/v1/booking/checkout', [
            'tour_id' => 3, // Overnight Safari Tour
            'adults' => 3,  // Odd number
            'children' => 0,
            'date' => now()->addDays(2)->format('Y-m-d'),
            'location' => 'Dubai Marina',
            'name' => 'Solo Tent Guest',
            'email' => 'overnight.solo@example.com',
            'phone' => '+971501234567',
            'payment_method' => 'cash',
            'is_custom_builder' => '1',
            'custom_quote_data' => json_encode([
                'base_tour_id' => 3,
                'base_name' => 'Overnight Stargazing Safari',
                'base_price' => 399,
                'transfer_name' => 'Shared 4x4',
                'transfer_price' => 0,
                'sports_name' => 'Scenic Only',
                'sports_price' => 0,
                'addons' => [],
                'adults' => 3,
                'children' => 0,
                'subtotal' => 1197,
                'total' => 1197,
            ]),
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('multiples of 2', $response->json('message'));
    }

    public function test_uploads_htaccess_exists_and_denies_php_execution(): void
    {
        $htaccessPath = public_path('uploads/.htaccess');
        $this->assertFileExists($htaccessPath);

        $content = file_get_contents($htaccessPath);
        $this->assertStringContainsString('php_flag engine off', $content);
        $this->assertStringContainsString('Deny from all', $content);
    }
}
