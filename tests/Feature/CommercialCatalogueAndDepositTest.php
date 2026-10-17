<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Tour;
use App\Models\User;
use App\Services\SettingsService;
use App\Services\ZiinaPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CommercialCatalogueAndDepositTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        app(SettingsService::class)->clearCache();
    }

    public function test_ziina_payment_service_defaults_to_25_percent_deposit(): void
    {
        Setting::whereIn('setting_key', ['deposit_percentage', 'ziina_advance_percent'])->delete();
        app(SettingsService::class)->clearCache();

        $service = app(ZiinaPaymentService::class);
        $this->assertEquals(25, $service->getAdvancePercent());
    }

    public function test_ziina_payment_service_reads_custom_deposit_percentage(): void
    {
        Setting::updateOrCreate(
            ['setting_key' => 'deposit_percentage'],
            ['setting_value' => '30']
        );
        app(SettingsService::class)->clearCache();

        $service = app(ZiinaPaymentService::class);
        $this->assertEquals(30, $service->getAdvancePercent());
    }

    public function test_admin_can_update_deposit_percentage_in_general_settings(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'deposit_percentage' => '35',
            'site_name' => 'Dunes Discovery Tourism',
            'site_email' => 'contact@dunesdiscoverytourism.com',
            'site_phone' => '+971 50 245 6056',
            'site_whatsapp' => '971502456056',
            'booking_cutoff_hours' => 2,
        ]);

        $response->assertRedirect();
        app(SettingsService::class)->clearCache();

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'deposit_percentage',
            'setting_value' => '35',
        ]);
        $this->assertDatabaseHas('settings', [
            'setting_key' => 'ziina_advance_percent',
            'setting_value' => '35',
        ]);
    }

    public function test_tour_1_classic_tier_has_price_79_and_old_price_99(): void
    {
        $tourTier = DB::table('tour_tiers')->where('id', 1)->first();
        $this->assertNotNull($tourTier);
        $this->assertEquals(79, (int) $tourTier->price);
        $this->assertEquals(99, (int) $tourTier->old_price);
    }

    public function test_overnight_safari_rejects_solo_or_odd_guest_count(): void
    {
        $tour = Tour::find(3);
        $this->assertNotNull($tour);
        $tier = DB::table('tour_tiers')->where('tour_id', 3)->first();
        $this->assertNotNull($tier);

        $responseSolo = $this->postJson('/api/v1/booking/checkout', [
            'tour_id' => 3,
            'tier_id' => $tier->tier_id,
            'date' => now()->addDays(2)->format('Y-m-d'),
            'adults' => 1,
            'children' => 0,
            'location' => 'Marina Mall, Dubai',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+971501234567',
            'payment_method' => 'cash',
        ]);

        $responseSolo->assertStatus(422);
        $responseSolo->assertJsonFragment([
            'success' => false,
        ]);

        $responseOdd = $this->postJson('/api/v1/booking/checkout', [
            'tour_id' => 3,
            'tier_id' => $tier->tier_id,
            'date' => now()->addDays(2)->format('Y-m-d'),
            'adults' => 3,
            'children' => 0,
            'location' => 'Marina Mall, Dubai',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+971501234567',
            'payment_method' => 'cash',
        ]);

        $responseOdd->assertStatus(422);
    }

    public function test_overnight_safari_accepts_even_guest_count_and_calculates_tent_pricing(): void
    {
        $tour = Tour::find(3);
        $this->assertNotNull($tour);
        $tier = DB::table('tour_tiers')->where('tour_id', 3)->first();
        $this->assertNotNull($tier);

        \App\Models\VerifiedEmail::create([
            'email' => 'jane@example.com',
            'verified_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/booking/checkout', [
            'tour_id' => 3,
            'tier_id' => $tier->tier_id,
            'date' => now()->addDays(2)->format('Y-m-d'),
            'adults' => 2,
            'children' => 0,
            'location' => 'Downtown Dubai',
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '+971507654321',
            'payment_method' => 'cash',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('bookings', [
            'tour_id' => 3,
            'adults' => 2,
            'total' => (float) $tier->price,
        ]);
    }

    public function test_overnight_safari_calculates_exact_package_pricing_for_higher_tiers(): void
    {
        $tour = Tour::find(3);
        $this->assertNotNull($tour);
        $tier6 = DB::table('tour_tiers')->where('tour_id', 3)->where('tier_id', 7)->first();
        $this->assertNotNull($tier6);

        \App\Models\VerifiedEmail::create([
            'email' => 'premium6@example.com',
            'verified_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/booking/checkout', [
            'tour_id' => 3,
            'tier_id' => 7,
            'date' => now()->addDays(3)->format('Y-m-d'),
            'adults' => 6,
            'children' => 0,
            'location' => 'Palm Jumeirah',
            'name' => 'Sara Connor',
            'email' => 'premium6@example.com',
            'phone' => '+971509998888',
            'payment_method' => 'cash',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('bookings', [
            'tour_id' => 3,
            'adults' => 6,
            'total' => 2394.00,
        ]);
    }

    public function test_admin_can_update_tier_guest_count_via_cms(): void
    {
        $admin = User::factory()->create();
        $tier = \App\Models\Tier::find(5);
        $this->assertNotNull($tier);

        $response = $this->actingAs($admin)->post(route('admin.tiers.update', $tier->id), [
            'name' => $tier->name,
            'display_name' => $tier->display_name,
            'slug' => $tier->slug,
            'guest_count' => 2,
            'status' => 'active',
            'priority' => $tier->priority,
        ]);

        $response->assertRedirect(route('admin.tiers.index'));
        $this->assertDatabaseHas('tiers', [
            'id' => 5,
            'guest_count' => 2,
        ]);
    }

    public function test_booking_modal_renders_overnight_rules_and_deposit_markup(): void
    {
        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertDontSee('id="overnightAdultsHelper"', false);
        $response->assertSee('data-advance-percent="25"', false);
        $response->assertSee('(25%)', false);
    }
}
