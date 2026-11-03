<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\BookingPayment;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\RequestLog;
use App\Models\Review;
use App\Models\Tour;
use App\Models\User;
use App\Services\NotificationManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PurgeTestBookingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Tour $tour;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::first() ?: User::factory()->create([
            'email' => 'admin@dunesdiscoverytourism.com',
        ]);

        $this->tour = Tour::create([
            'name' => 'VIP Desert Safari Dubai',
            'slug' => 'vip-desert-safari-dubai',
            'description' => 'Test tour description',
            'price' => 750.00,
            'status' => 'active',
        ]);
    }

    /**
     * Test 1: Command safely purges all test bookings and cascading records while preserving authentic customer bookings.
     */
    public function test_command_purges_test_bookings_and_cascades_without_affecting_real_customers(): void
    {
        // 1. Create Authentic Customer Booking (MUST NOT BE DELETED)
        $realBooking = Booking::create([
            'reference' => 'DDT261103RDRYV',
            'tour_id' => $this->tour->id,
            'tour_name' => 'Authentic Live Tour',
            'tour_date' => '2026-11-10',
            'name' => 'Real Tourist Client',
            'email' => 'client@international-traveler.org',
            'phone' => '+44 7123 456789',
            'adults' => 2,
            'children' => 0,
            'infants' => 0,
            'subtotal' => 1500.00,
            'total' => 1500.00,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $realAddon = BookingAddon::create([
            'booking_id' => $realBooking->id,
            'addon_name' => 'Quad Bike 30 Mins',
            'price' => 200.00,
            'quantity' => 1,
        ]);

        $realPayment = BookingPayment::create([
            'booking_id' => $realBooking->id,
            'amount' => 1500.00,
            'status' => 'completed',
            'method' => 'stripe',
            'transaction_id' => 'tx_real_12345',
        ]);

        // 2. Create Test Bookings
        $coupon = Coupon::create([
            'name' => 'VIP Promo',
            'code' => 'TESTCOUPON10',
            'discount_type' => 'fixed',
            'discount_value' => 50.00,
            'status' => 'active',
        ]);
        $coupon->used_count = 1;
        $coupon->save();

        $testBooking1 = Booking::create([
            'reference' => 'DT-TEST-3143D',
            'tour_id' => $this->tour->id,
            'tour_name' => 'Test Safari Tour',
            'tour_date' => '2026-11-05',
            'name' => 'Honorable VIP Traveler',
            'email' => 'reservations@dunesdiscoverytourism.com',
            'phone' => '+971 50 123 4567',
            'adults' => 2,
            'children' => 1,
            'infants' => 0,
            'subtotal' => 750.00,
            'total' => 750.00,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $testAddon1 = BookingAddon::create([
            'booking_id' => $testBooking1->id,
            'addon_name' => 'Test Camel Ride',
            'price' => 50.00,
            'quantity' => 1,
        ]);

        $testPayment1 = BookingPayment::create([
            'booking_id' => $testBooking1->id,
            'amount' => 750.00,
            'status' => 'completed',
            'method' => 'card',
            'transaction_id' => 'tx_test_99999',
        ]);

        $testCouponUsage = CouponUsage::create([
            'coupon_id' => $coupon->id,
            'customer_email' => $testBooking1->email,
            'booking_id' => $testBooking1->id,
            'booking_reference' => $testBooking1->reference,
            'discount_amount' => 50.00,
            'used_at' => now(),
        ]);

        $testBooking2 = Booking::create([
            'reference' => 'DT-ADMIN-1AF43',
            'tour_id' => $this->tour->id,
            'tour_name' => 'Overnight Test Safari',
            'tour_date' => '2026-11-06',
            'name' => 'Alexander Wright',
            'email' => 'alexander.wright@uk-travel.co.uk',
            'phone' => '+44 7911 123456',
            'adults' => 4,
            'children' => 0,
            'infants' => 0,
            'subtotal' => 1800.00,
            'total' => 1800.00,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        // Mock test voucher files on disk
        $vouchersDir = storage_path('app/vouchers');
        if (! is_dir($vouchersDir)) {
            mkdir($vouchersDir, 0775, true);
        }
        file_put_contents($vouchersDir . '/DT-TEST-3143D.pdf', 'MOCK_TEST_PDF');
        file_put_contents($vouchersDir . '/DT-ADMIN-1AF43.pdf', 'MOCK_TEST_PDF');
        file_put_contents($vouchersDir . '/DDT261103RDRYV.pdf', 'MOCK_REAL_PDF');

        // Execute purge command with --force
        $exitCode = Artisan::call('bookings:purge-test-bookings', ['--force' => true]);
        $this->assertEquals(0, $exitCode);

        // Verify test bookings are completely deleted
        $this->assertNull(Booking::withTrashed()->where('reference', 'DT-TEST-3143D')->first());
        $this->assertNull(Booking::withTrashed()->where('reference', 'DT-ADMIN-1AF43')->first());
        $this->assertDatabaseMissing('booking_addons', ['booking_id' => $testBooking1->id]);
        $this->assertDatabaseMissing('booking_payments', ['booking_id' => $testBooking1->id]);
        $this->assertDatabaseMissing('coupon_usages', ['booking_id' => $testBooking1->id]);
        $this->assertEquals(0, $coupon->fresh()->used_count);

        // Verify test voucher files were deleted
        $this->assertFileDoesNotExist($vouchersDir . '/DT-TEST-3143D.pdf');
        $this->assertFileDoesNotExist($vouchersDir . '/DT-ADMIN-1AF43.pdf');

        // STRICT VERIFICATION: Real customer booking and associated data are 100% INTACT
        $this->assertNotNull(Booking::where('reference', 'DDT261103RDRYV')->first());
        $this->assertDatabaseHas('booking_addons', ['id' => $realAddon->id]);
        $this->assertDatabaseHas('booking_payments', ['id' => $realPayment->id]);
        $this->assertFileExists($vouchersDir . '/DDT261103RDRYV.pdf');

        // Clean up mock real pdf
        @unlink($vouchersDir . '/DDT261103RDRYV.pdf');
    }

    /**
     * Test 2: Admin can trigger the purge from the CMS settings endpoint.
     */
    public function test_admin_can_trigger_purge_via_cms_endpoint(): void
    {
        Booking::create([
            'reference' => 'DT-TEST-A7BFB',
            'tour_id' => $this->tour->id,
            'tour_name' => 'CMS Trigger Test Tour',
            'tour_date' => '2026-11-08',
            'name' => 'Honorable VIP Traveler',
            'email' => 'admin@dunesdiscoverytourism.com',
            'phone' => '+971 50 123 4567',
            'adults' => 2,
            'children' => 0,
            'infants' => 0,
            'subtotal' => 500.00,
            'total' => 500.00,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.settings.notifications.purge-test-bookings'));

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertNull(Booking::withTrashed()->where('reference', 'DT-TEST-A7BFB')->first());
    }

    /**
     * Test 3: Live diagnostic notification dispatcher does not persist test booking rows to the database.
     */
    public function test_live_diagnostic_dispatcher_does_not_persist_records_to_database(): void
    {
        Mail::fake();

        $initialBookingCount = Booking::count();

        $service = app(NotificationManagementService::class);
        $result = $service->sendTestNotification('booking_customer', 'test-qa@example.com');

        $this->assertTrue($result['success']);
        $this->assertTrue($result['pdf_attached']);

        // Verify that database booking count did NOT increase
        $this->assertEquals($initialBookingCount, Booking::count());
    }

    /**
     * Test 4: Dynamic voucher preview for test references resolves without creating database rows.
     */
    public function test_voucher_preview_resolves_dynamically_without_database_persistence(): void
    {
        $initialCount = Booking::count();

        $response = $this->get('/booking/DT-TEST-DYNAMIC-99/voucher');
        $response->assertOk();
        $response->assertSee('DT-TEST-DYNAMIC-99');
        $response->assertSee('Honorable VIP Traveler');

        // Verify that no booking row was persisted
        $this->assertEquals($initialCount, Booking::count());
    }
}
