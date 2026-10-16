<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Setting;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GoogleAdsConversionStrictnessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('site_settings_cache');

        Setting::updateOrCreate(['setting_key' => 'google_ads_id'], ['setting_value' => 'AW-17859624049']);
        Setting::updateOrCreate(['setting_key' => 'google_conversion_label'], ['setting_value' => 'eR3SCLimtvobEPH4kMRC']);
        Cache::forget('site_settings_cache');
    }

    public function test_thankyou_without_booking_does_not_fire_google_ads_conversion(): void
    {
        $response = $this->get('/thankyou');
        $response->assertStatus(200);

        $content = $response->getContent();
        // Google Ads conversion action string must NOT be present in conversion dispatch
        $this->assertStringNotContainsString("gtag('event', 'conversion'", $content);
        $this->assertStringNotContainsString('AW-17859624049/eR3SCLimtvobEPH4kMRC', $content);
    }

    public function test_thankyou_with_cancelled_or_failed_booking_does_not_fire_conversion(): void
    {
        $tour = Tour::create([
            'slug' => 'evening-desert-safari',
            'name' => 'Evening Desert Safari',
            'status' => 'active',
            'min_price' => 150,
        ]);

        $booking = Booking::create([
            'reference' => 'DDT-TEST-FAILED',
            'tour_id' => $tour->id,
            'tour_name' => $tour->name,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+971501234567',
            'subtotal' => 300.00,
            'total' => 300.00,
            'adults' => 2,
            'tour_date' => now()->format('Y-m-d'),
            'status' => 'cancelled',
            'payment_status' => 'failed',
            'payment_method' => 'card',
        ]);

        $response = $this->get('/thankyou?ref=' . $booking->reference);
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringNotContainsString("gtag('event', 'conversion'", $content);
    }

    public function test_thankyou_with_verified_booking_fires_deduplicated_conversion(): void
    {
        $tour = Tour::create([
            'slug' => 'evening-desert-safari',
            'name' => 'Evening Desert Safari',
            'status' => 'active',
            'min_price' => 150,
        ]);

        $booking = Booking::create([
            'reference' => 'DDT-TEST-CONFIRMED',
            'tour_id' => $tour->id,
            'tour_name' => $tour->name,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+971501234567',
            'subtotal' => 450.00,
            'total' => 450.00,
            'adults' => 2,
            'tour_date' => now()->format('Y-m-d'),
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
        ]);

        $response = $this->get('/thankyou?ref=' . $booking->reference);
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString("gtag('event', 'conversion'", $content);
        $this->assertStringContainsString('AW-17859624049/eR3SCLimtvobEPH4kMRC', $content);
        $this->assertStringContainsString("'value': 450", $content);
        $this->assertStringContainsString("'currency': 'AED'", $content);
        $this->assertStringContainsString("var txnId = 'DDT-TEST-CONFIRMED';", $content);
        $this->assertStringContainsString("'transaction_id': txnId", $content);
        // Verify sessionStorage deduplication mechanism is present
        $this->assertStringContainsString("dunes_gads_conv_' + txnId", $content);
        // Ensure no premature lead form event is fired on booking confirmation
        $this->assertStringNotContainsString("gtag('event', 'conversion_event_submit_lead_form'", $content);
    }

    public function test_gtagsendevent_in_layout_does_not_inject_conversion_events(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();
        // Layout helper must not contain automatic gtag conversion trigger
        $this->assertStringNotContainsString("gtag('event', 'conversion', eventData)", $content);
        $this->assertStringNotContainsString("gtag('event', 'conversion_event_submit_lead_form', eventData)", $content);
    }
}
