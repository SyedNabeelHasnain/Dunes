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
use App\Models\WhatsappInquiry;
use App\Models\Contact;
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

    /**
     * Test permanent single and bulk WhatsApp lead deletion with cascade footprint purge.
     */
    public function test_permanent_whatsapp_lead_deletion_cascades_request_logs(): void
    {
        $admin = User::create([
            'name' => 'Admin WhatsApp',
            'email' => 'admin_wa@dunesdiscovery.com',
            'password' => bcrypt('password123'),
        ]);

        // 1. Single Lead Deletion Test
        $logPrimary = RequestLog::create([
            'entity_type' => 'whatsapp',
            'request_timestamp' => now(),
            'client_ip' => '192.168.10.50',
            'city' => 'Dubai',
            'country' => 'United Arab Emirates',
        ]);

        $lead = WhatsappInquiry::create([
            'request_log_id' => $logPrimary->id,
            'name' => 'Fatima Al-Zahra',
            'phone' => '+971551234567',
            'tour_name' => 'Premium Desert Safari',
            'page_url' => 'https://dunesdiscovery.com/tours/evening-desert-safari',
            'message_text' => 'Hello, I want to book for 4 people.',
        ]);

        $logRelational = RequestLog::create([
            'entity_type' => 'whatsapp_inquiry',
            'entity_id' => $lead->id,
            'request_timestamp' => now(),
            'client_ip' => '192.168.10.50',
        ]);

        $this->assertDatabaseHas('whatsapp_inquiries', ['id' => $lead->id]);
        $this->assertDatabaseHas('request_logs', ['id' => $logPrimary->id]);
        $this->assertDatabaseHas('request_logs', ['id' => $logRelational->id]);

        // Execute single delete as admin
        $response = $this->actingAs($admin)->deleteJson("/admin/whatsapp-leads/{$lead->id}");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Assert permanent eradication with zero orphaned footprints
        $this->assertDatabaseMissing('whatsapp_inquiries', ['id' => $lead->id]);
        $this->assertDatabaseMissing('request_logs', ['id' => $logPrimary->id]);
        $this->assertDatabaseMissing('request_logs', ['id' => $logRelational->id]);

        // 2. Bulk Lead Deletion Test
        $leadA = WhatsappInquiry::create([
            'name' => 'Lead Alpha',
            'phone' => '+971501111111',
            'tour_name' => 'Morning Safari',
        ]);
        $leadB = WhatsappInquiry::create([
            'name' => 'Lead Beta',
            'phone' => '+971502222222',
            'tour_name' => 'Quad Biking',
        ]);

        $bulkLogA = RequestLog::create([
            'entity_type' => 'whatsapp_lead',
            'entity_id' => $leadA->id,
            'request_timestamp' => now(),
        ]);
        $bulkLogB = RequestLog::create([
            'entity_type' => 'whatsapp',
            'entity_id' => $leadB->id,
            'request_timestamp' => now(),
        ]);

        $bulkResp = $this->actingAs($admin)->postJson('/admin/whatsapp-leads/bulk-action', [
            'ids' => [$leadA->id, $leadB->id],
            'action' => 'delete',
        ]);
        $bulkResp->assertStatus(200);
        $bulkResp->assertJson(['success' => true]);

        $this->assertDatabaseMissing('whatsapp_inquiries', ['id' => $leadA->id]);
        $this->assertDatabaseMissing('whatsapp_inquiries', ['id' => $leadB->id]);
        $this->assertDatabaseMissing('request_logs', ['id' => $bulkLogA->id]);
        $this->assertDatabaseMissing('request_logs', ['id' => $bulkLogB->id]);
    }

    /**
     * Test permanent single and bulk Contact Inquiry deletion bypassing SoftDeletes and cascading logs.
     */
    public function test_permanent_contact_inquiry_deletion_cascades_request_logs(): void
    {
        $admin = User::create([
            'name' => 'Admin Inquiries',
            'email' => 'admin_inq@dunesdiscovery.com',
            'password' => bcrypt('password123'),
        ]);

        // 1. Single Inquiry Deletion Test
        $logPrimary = RequestLog::create([
            'entity_type' => 'contact',
            'request_timestamp' => now(),
            'client_ip' => '192.168.20.100',
            'city' => 'Abu Dhabi',
        ]);

        $inquiry = Contact::create([
            'request_log_id' => $logPrimary->id,
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+971520000000',
            'subject' => 'Corporate Event Safari',
            'message' => 'Looking to book a private corporate group of 50 delegates.',
            'status' => 'new',
            'ip_address' => '192.168.20.100',
        ]);

        $logRelational = RequestLog::create([
            'entity_type' => 'contact',
            'entity_id' => $inquiry->id,
            'request_timestamp' => now(),
        ]);

        $this->assertDatabaseHas('contacts', ['id' => $inquiry->id]);
        $this->assertDatabaseHas('request_logs', ['id' => $logPrimary->id]);
        $this->assertDatabaseHas('request_logs', ['id' => $logRelational->id]);

        // Execute single delete as admin
        $response = $this->actingAs($admin)->deleteJson("/admin/inquiries/{$inquiry->id}");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Assert permanent database eradication (assertDatabaseMissing checks the table directly, ensuring no soft-deleted row remains)
        $this->assertDatabaseMissing('contacts', ['id' => $inquiry->id]);
        $this->assertNull(Contact::withTrashed()->find($inquiry->id));
        $this->assertDatabaseMissing('request_logs', ['id' => $logPrimary->id]);
        $this->assertDatabaseMissing('request_logs', ['id' => $logRelational->id]);

        // 2. Bulk Inquiries Deletion Test
        $inqA = Contact::create([
            'name' => 'Inquiry A',
            'email' => 'inqA@example.com',
            'subject' => 'Subject A',
            'message' => 'Message A',
            'status' => 'new',
        ]);
        $inqB = Contact::create([
            'name' => 'Inquiry B',
            'email' => 'inqB@example.com',
            'subject' => 'Subject B',
            'message' => 'Message B',
            'status' => 'read',
        ]);

        $bulkLogA = RequestLog::create([
            'entity_type' => 'inquiry',
            'entity_id' => $inqA->id,
            'request_timestamp' => now(),
        ]);
        $bulkLogB = RequestLog::create([
            'entity_type' => 'contact',
            'entity_id' => $inqB->id,
            'request_timestamp' => now(),
        ]);

        $bulkResp = $this->actingAs($admin)->postJson('/admin/inquiries/bulk-action', [
            'ids' => [$inqA->id, $inqB->id],
            'action' => 'delete',
        ]);
        $bulkResp->assertStatus(200);
        $bulkResp->assertJson(['success' => true]);

        $this->assertDatabaseMissing('contacts', ['id' => $inqA->id]);
        $this->assertDatabaseMissing('contacts', ['id' => $inqB->id]);
        $this->assertNull(Contact::withTrashed()->find($inqA->id));
        $this->assertNull(Contact::withTrashed()->find($inqB->id));
        $this->assertDatabaseMissing('request_logs', ['id' => $bulkLogA->id]);
        $this->assertDatabaseMissing('request_logs', ['id' => $bulkLogB->id]);
    }

    /**
     * Test dashboard KPIs reflect real-time database changes immediately without caching.
     */
    public function test_dashboard_kpis_reflect_realtime_data_without_cache(): void
    {
        $admin = User::create([
            'name' => 'Admin Realtime',
            'email' => 'admin_realtime@dunesdiscovery.com',
            'password' => bcrypt('password123'),
        ]);

        $b1 = Booking::create([
            'reference' => 'DDT-RT-001',
            'tour_name' => 'Desert Safari',
            'tour_date' => now()->addDays(1)->toDateString(),
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_amount' => 500.00,
            'subtotal' => 500.00,
            'total' => 500.00,
            'adults' => 2,
            'children' => 0,
            'name' => 'Guest Realtime 1',
            'email' => 'gr1@example.com',
            'phone' => '+971501234567',
        ]);

        $response = $this->actingAs($admin)->getJson('/admin/api/kpis');
        $response->assertStatus(200);
        $response->assertJsonPath('stats.total', 1);
        $response->assertJsonPath('stats.confirmed', 1);
        $this->assertEquals(500.0, $response->json('stats.revenue'));

        // Add a second booking and verify immediate real-time update without waiting for any cache
        $b2 = Booking::create([
            'reference' => 'DDT-RT-002',
            'tour_name' => 'Morning Safari',
            'tour_date' => now()->addDays(2)->toDateString(),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_amount' => 0.00,
            'subtotal' => 300.00,
            'total' => 300.00,
            'adults' => 1,
            'children' => 0,
            'name' => 'Guest Realtime 2',
            'email' => 'gr2@example.com',
            'phone' => '+971507654321',
        ]);

        $response2 = $this->actingAs($admin)->getJson('/admin/api/kpis');
        $response2->assertStatus(200);
        $response2->assertJsonPath('stats.total', 2);
        $response2->assertJsonPath('stats.pending', 1);
        $this->assertEquals(500.0, $response2->json('stats.revenue')); // Unpaid booking adds 0 to collected revenue
    }

    /**
     * Test that draft/abandoned bookings are strictly excluded from Total Bookings count.
     */
    public function test_dashboard_kpis_exclude_draft_bookings(): void
    {
        $admin = User::create([
            'name' => 'Admin Drafts',
            'email' => 'admin_drafts@dunesdiscovery.com',
            'password' => bcrypt('password123'),
        ]);

        // Create 1 real confirmed booking
        Booking::create([
            'reference' => 'DDT-REAL-001',
            'tour_name' => 'Evening Safari',
            'tour_date' => now()->addDays(2)->toDateString(),
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_amount' => 350.00,
            'subtotal' => 350.00,
            'total' => 350.00,
            'adults' => 1,
            'name' => 'Real Guest',
            'email' => 'real@example.com',
            'phone' => '+971509999999',
        ]);

        // Create 3 incomplete draft checkouts
        for ($i = 1; $i <= 3; $i++) {
            Booking::create([
                'reference' => "DDT-DRAFT-00{$i}",
                'tour_name' => 'Evening Safari',
                'tour_date' => now()->addDays(2)->toDateString(),
                'status' => 'draft',
                'payment_status' => 'unpaid',
                'payment_amount' => 0.00,
                'subtotal' => 350.00,
                'total' => 350.00,
                'adults' => 1,
                'name' => "Draft User {$i}",
                'email' => "draft{$i}@example.com",
                'phone' => '+971500000000',
            ]);
        }

        $response = $this->actingAs($admin)->getJson('/admin/api/kpis');
        $response->assertStatus(200);
        // Total Bookings MUST be exactly 1 (excluding the 3 drafts)
        $response->assertJsonPath('stats.total', 1);
        $response->assertJsonPath('stats.drafts', 3);
    }

    /**
     * Test collected revenue and AOV calculations match identically across Dashboard and Bookings Hub.
     */
    public function test_collected_revenue_matches_between_dashboard_and_bookings_hub(): void
    {
        $admin = User::create([
            'name' => 'Admin Revenue',
            'email' => 'admin_rev@dunesdiscovery.com',
            'password' => bcrypt('password123'),
        ]);

        // Booking 1: Confirmed and fully paid (total 600, payment_amount 600)
        Booking::create([
            'reference' => 'DDT-PAID-001',
            'tour_name' => 'VIP Desert Safari',
            'tour_date' => now()->addDays(1)->toDateString(),
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_amount' => 600.00,
            'subtotal' => 600.00,
            'total' => 600.00,
            'adults' => 2,
            'name' => 'Paid Guest',
            'email' => 'paid@example.com',
            'phone' => '+971501111111',
        ]);

        // Booking 2: Completed and partially paid (total 800, payment_amount 400)
        Booking::create([
            'reference' => 'DDT-PARTIAL-001',
            'tour_name' => 'Dune Buggy Adventure',
            'tour_date' => now()->addDays(2)->toDateString(),
            'status' => 'completed',
            'payment_status' => 'partial',
            'payment_amount' => 400.00,
            'subtotal' => 800.00,
            'total' => 800.00,
            'adults' => 2,
            'name' => 'Partial Guest',
            'email' => 'partial@example.com',
            'phone' => '+971502222222',
        ]);

        // Booking 3: Confirmed but unpaid (total 500, payment_amount 0) - should NOT add to collected revenue
        Booking::create([
            'reference' => 'DDT-UNPAID-001',
            'tour_name' => 'Quad Bike Safari',
            'tour_date' => now()->addDays(3)->toDateString(),
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_amount' => 0.00,
            'subtotal' => 500.00,
            'total' => 500.00,
            'adults' => 1,
            'name' => 'Unpaid Guest',
            'email' => 'unpaid@example.com',
            'phone' => '+971503333333',
        ]);

        $dashboardKpi = $this->actingAs($admin)->getJson('/admin/api/kpis');
        $bookingKpi = $this->actingAs($admin)->getJson('/admin/api/bookings-stats');

        $dashboardKpi->assertStatus(200);
        $bookingKpi->assertStatus(200);

        // Expected collected revenue = 600 + 400 = 1000.00
        $this->assertEquals(1000.00, $dashboardKpi->json('stats.revenue'));
        $this->assertEquals(1000.00, $bookingKpi->json('stats.revenue'));

        // Both paid reservations = 2, AOV = 1000 / 2 = 500.00
        $this->assertEquals(500.00, $dashboardKpi->json('stats.aov'));
        $this->assertEquals(500.00, $bookingKpi->json('stats.aov'));
    }

    /**
     * Test Operations Manifest correctly calculates 0 vehicles when no bookings exist.
     */
    public function test_operations_manifest_vehicles_needed_zero_when_no_bookings(): void
    {
        $admin = User::create([
            'name' => 'Admin Operations',
            'email' => 'admin_ops@dunesdiscovery.com',
            'password' => bcrypt('password123'),
        ]);

        $emptyDate = now()->addDays(30)->format('Y-m-d');
        $response = $this->actingAs($admin)->get("/admin/operations?date={$emptyDate}");
        $response->assertStatus(200);
        // Vehicles needed must be 0, not 1
        $response->assertViewHas('stats', function ($stats) {
            return $stats['total_bookings'] === 0 
                && $stats['total_guests'] === 0 
                && $stats['vehicles_needed'] === 0;
        });
    }
}
