<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Coupon;
use App\Models\EmailOtp;
use App\Models\IpRule;
use App\Models\RequestLog;
use App\Models\Setting;
use App\Models\SpamEvent;
use App\Models\SyntheticTestRun;
use App\Models\Tier;
use App\Models\Tour;
use App\Models\User;
use App\Models\VerifiedEmail;
use App\Models\WhatsappInquiry;
use App\Services\SettingsService;
use App\Services\SyntheticTesting\SyntheticJourneyRunner;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SyntheticJourneySimulatorTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Tour $tour;

    private Tier $tier;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->admin = User::factory()->create([
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $category = Category::firstOrCreate(
            ['slug' => 'desert-safari'],
            ['name' => ['en' => 'Desert Safari'], 'priority' => 1]
        );

        $this->tour = Tour::create([
            'slug' => 'evening-desert-safari-dubai',
            'name' => ['en' => 'Evening Desert Safari Dubai'],
            'category_id' => $category->id,
            'short_desc' => ['en' => 'Sunset dunes, camel ride and live BBQ camp.'],
            'status' => 'active',
            'priority' => 1,
            'hero_image' => 'images/evening-desert-safari-dubai-hero.avif',
            'thumb_image' => 'images/evening-desert-safari-dubai-hero.avif',
        ]);

        $this->tier = Tier::create([
            'slug' => 'classic',
            'name' => ['en' => 'Classic Safari'],
            'display_name' => ['en' => 'Classic'],
            'status' => 'active',
            'priority' => 1,
        ]);

        DB::table('tour_tiers')->insert([
            'tour_id' => $this->tour->id,
            'tier_id' => $this->tier->id,
            'price' => 150.00,
            'old_price' => 200.00,
            'price_type' => 'per person',
        ]);

        Coupon::create([
            'code' => 'QA10OFF',
            'name' => 'QA Test Discount',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'status' => 'active',
            'min_spend' => 50,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addMonth(),
        ]);

        Setting::updateOrCreate(['setting_key' => 'email_verification_required'], ['setting_value' => '1']);
        Setting::updateOrCreate(['setting_key' => 'contact_email_otp_required'], ['setting_value' => '1']);
        Setting::updateOrCreate(['setting_key' => 'antispam_enabled'], ['setting_value' => '1']);
        app(SettingsService::class)->clearCache();
    }

    public function test_manual_full_three_flow_simulation_passes_keeps_admin_session_and_auto_cleans(): void
    {
        $baselineBookings = Booking::count();
        $baselineWhatsapp = WhatsappInquiry::count();
        $baselineContacts = Contact::count();
        $baselineSpam = SpamEvent::count();
        $baselineLogs = RequestLog::count();
        $baselineVerified = VerifiedEmail::count();
        $baselineOtps = EmailOtp::count();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.qa-simulator.run'), [
                'flows' => ['booking', 'whatsapp', 'contact'],
                'locale' => 'en',
                'device_profile' => 'desktop',
                'tour_id' => $this->tour->id,
                'mail_mode' => 'intercept',
                'include_bot_shield_check' => '1',
                'include_coupon_check' => '1',
                'auto_cleanup' => '1',
            ]);

        $run = SyntheticTestRun::with('steps')->latest('id')->firstOrFail();
        $response->assertRedirect(route('admin.qa-simulator.show', $run->id));

        $this->assertSame('passed', $run->status, $run->summary ?? '');
        $this->assertSame(3, $run->total_flows);
        $this->assertSame(3, $run->passed_flows);
        $this->assertSame(0, $run->failed_flows);
        $this->assertTrue($run->cleanup_completed);
        $this->assertGreaterThanOrEqual(15, $run->total_steps);
        $this->assertSame(0, $run->failed_steps);

        // Verify intercepted emails were recorded on Booking, WhatsApp, and Contact steps
        $checkoutStep = $run->steps->firstWhere('step_key', 'booking_checkout_submit');
        $this->assertNotNull($checkoutStep);
        $this->assertNotEmpty($checkoutStep->metadata['emails_rendered'] ?? []);

        $contactDbStep = $run->steps->firstWhere('step_key', 'contact_db_and_mail_verify');
        $this->assertNotNull($contactDbStep);
        $this->assertNotEmpty($contactDbStep->metadata['emails_rendered'] ?? []);

        // Zero synthetic database pollution after auto_cleanup=true
        $this->assertSame($baselineBookings, Booking::count());
        $this->assertSame($baselineWhatsapp, WhatsappInquiry::count());
        $this->assertSame($baselineContacts, Contact::count());
        $this->assertSame($baselineSpam, SpamEvent::count());
        $this->assertSame($baselineLogs, RequestLog::count());
        $this->assertSame($baselineVerified, VerifiedEmail::count());
        $this->assertSame($baselineOtps, EmailOtp::count());

        // Bot-shield probe must NEVER auto-ban localhost/server IP in ip_rules
        $this->assertSame(0, IpRule::where('mode', 'block')->count());

        // Active admin session & guard must remain completely intact!
        $this->assertAuthenticatedAs($this->admin);
        $this->get(route('admin.qa-simulator.show', $run->id))->assertStatus(200);
        $this->get(route('admin.health.index'))->assertStatus(200);
    }

    public function test_manual_cleanup_deletes_only_synthetic_artifacts_and_preserves_real_customer_data(): void
    {
        $realBooking = Booking::create([
            'reference' => 'DDT261127REAL1',
            'tour_id' => $this->tour->id,
            'tier_id' => $this->tier->id,
            'tour_name' => 'Evening Desert Safari Dubai',
            'tier_name' => 'Classic',
            'tour_date' => now()->addDays(4)->toDateString(),
            'adults' => 2,
            'children' => 0,
            'name' => 'Real Customer',
            'email' => 'real.customer@example.com',
            'phone' => '+971501112233',
            'subtotal' => 300,
            'addons_total' => 0,
            'total' => 300,
            'currency' => 'AED',
            'status' => 'confirmed',
            'payment_method' => 'cash',
            'payment_status' => 'unpaid',
            'balance_due' => 300,
        ]);

        $realWhatsapp = WhatsappInquiry::create([
            'ref_code' => 'WA-REAL1',
            'name' => 'Real WhatsApp Lead',
            'phone' => '+971504445566',
            'tour_name' => 'Evening Desert Safari Dubai',
        ]);

        $realContact = Contact::create([
            'name' => 'Real Contact Guest',
            'email' => 'real.contact@example.com',
            'subject' => 'Group Quote',
            'message' => 'Real visitor question about pickup.',
            'status' => 'new',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.qa-simulator.run'), [
                'flows' => ['booking', 'whatsapp', 'contact'],
                'auto_cleanup' => '0',
                'include_bot_shield_check' => '1',
            ]);

        $run = SyntheticTestRun::latest('id')->firstOrFail();
        $this->assertSame('passed', $run->status);
        $this->assertFalse($run->cleanup_completed);
        $this->assertTrue($run->hasUncleanedArtifacts());
        $this->assertGreaterThan(1, Booking::count());
        $this->assertGreaterThan(1, WhatsappInquiry::count());
        $this->assertGreaterThan(1, Contact::count());

        // Trigger deterministic manual cleanup
        $this->actingAs($this->admin)
            ->post(route('admin.qa-simulator.cleanup', $run->id))
            ->assertRedirect();

        $run->refresh();
        $this->assertTrue($run->cleanup_completed);
        $this->assertFalse($run->hasUncleanedArtifacts());

        // Only pre-existing real records remain
        $this->assertSame(1, Booking::count());
        $this->assertSame($realBooking->id, Booking::first()->id);
        $this->assertSame(1, WhatsappInquiry::count());
        $this->assertSame($realWhatsapp->id, WhatsappInquiry::first()->id);
        $this->assertSame(1, Contact::count());
        $this->assertSame($realContact->id, Contact::first()->id);
    }

    public function test_individual_flows_and_arabic_locale_mobile_run_succeed(): void
    {
        $runner = app(SyntheticJourneyRunner::class);

        $waRun = $runner->execute([
            'flows' => ['whatsapp'],
            'locale' => 'ar',
            'device_profile' => 'mobile',
            'mail_mode' => 'intercept',
            'auto_cleanup' => true,
        ], 'manual', $this->admin->id);

        $this->assertSame('passed', $waRun->status, $waRun->summary ?? '');
        $this->assertSame(['whatsapp'], $waRun->flows_requested);
        $this->assertSame('ar', $waRun->locale);
        $this->assertSame('mobile', $waRun->device_profile);

        $contactRun = $runner->execute([
            'flows' => ['contact'],
            'locale' => 'en',
            'device_profile' => 'desktop',
            'mail_mode' => 'intercept',
            'include_bot_shield_check' => true,
            'auto_cleanup' => true,
        ], 'manual', $this->admin->id);

        $this->assertSame('passed', $contactRun->status, $contactRun->summary ?? '');
    }

    public function test_schedule_configuration_validation_due_evaluation_cli_and_pruning(): void
    {
        $this->actingAs($this->admin);

        // Invalid custom cron must fail validation
        $this->post(route('admin.qa-simulator.settings.update'), [
            'enabled' => '1',
            'frequency' => 'custom_cron',
            'time' => '06:30',
            'weekday' => 1,
            'custom_cron' => 'not-a-valid-cron',
            'flows' => ['booking', 'whatsapp', 'contact'],
            'locale' => 'en',
            'device_profile' => 'desktop',
            'mail_mode' => 'intercept',
            'retention_days' => 30,
        ])->assertSessionHasErrors('custom_cron');

        // Valid weekly schedule saves cleanly
        $this->post(route('admin.qa-simulator.settings.update'), [
            'enabled' => '1',
            'frequency' => 'weekly',
            'time' => '06:30',
            'weekday' => 2, // Tuesday
            'flows' => ['booking', 'contact'],
            'locale' => 'en',
            'device_profile' => 'mobile',
            'mail_mode' => 'intercept',
            'include_bot_shield_check' => '1',
            'include_coupon_check' => '1',
            'auto_cleanup' => '1',
            'alert_on_failure' => '1',
            'retention_days' => 14,
        ])->assertRedirect(route('admin.qa-simulator.index'));

        $runner = app(SyntheticJourneyRunner::class);
        $cfg = $runner->getSettings();
        $this->assertTrue($cfg['enabled']);
        $this->assertSame('weekly', $cfg['frequency']);
        $this->assertSame('30 6 * * 2', $runner->buildCronExpression($cfg));

        // Exact matching Tuesday 06:30 Asia/Dubai minute is due once (slot dedup prevents double trigger)
        $tuesdayDubai = Carbon::create(2026, 12, 1, 6, 30, 0, 'Asia/Dubai'); // Dec 1, 2026 is Tuesday
        $wednesdayDubai = Carbon::create(2026, 12, 2, 6, 30, 0, 'Asia/Dubai');
        $this->assertFalse($runner->isDueNow($wednesdayDubai, $cfg));
        $this->assertTrue($runner->isDueNow($tuesdayDubai, $cfg));
        $this->assertFalse($runner->isDueNow($tuesdayDubai, $cfg)); // slot deduped within same minute

        // CLI command execution
        $this->artisan('qa:run-synthetic-tests --flows=whatsapp')->assertExitCode(0);
        $this->assertDatabaseHas('synthetic_test_runs', ['trigger_type' => 'cli', 'status' => 'passed']);

        // Retention pruning deletes runs older than retention_days
        $oldRun = SyntheticTestRun::create([
            'trigger_type' => 'scheduled',
            'status' => 'passed',
            'flows_requested' => ['whatsapp'],
            'locale' => 'en',
            'device_profile' => 'desktop',
            'mail_mode' => 'intercept',
        ]);
        DB::table('synthetic_test_runs')->where('id', $oldRun->id)->update(['created_at' => now()->subDays(20)]);

        $this->artisan('qa:run-synthetic-tests --prune')->assertExitCode(0);
        $this->assertDatabaseMissing('synthetic_test_runs', ['id' => $oldRun->id]);
    }

    public function test_rbac_and_json_export_enforcement(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer', 'is_active' => true]);

        // Viewer can inspect index, but cannot run tests or update schedule settings
        $this->actingAs($viewer)->get(route('admin.qa-simulator.index'))->assertStatus(200);
        $this->actingAs($viewer)->post(route('admin.qa-simulator.run'), ['flows' => ['whatsapp']])->assertStatus(403);
        $this->actingAs($viewer)->post(route('admin.qa-simulator.settings.update'), [
            'frequency' => 'daily',
            'time' => '06:00',
            'weekday' => 1,
            'flows' => ['whatsapp'],
            'locale' => 'en',
            'device_profile' => 'desktop',
            'mail_mode' => 'intercept',
            'retention_days' => 30,
        ])->assertStatus(403);

        // Super admin can export run report as JSON
        $run = app(SyntheticJourneyRunner::class)->execute(['flows' => ['whatsapp']], 'manual', $this->admin->id);
        $export = $this->actingAs($this->admin)->get(route('admin.qa-simulator.export', $run->id));
        $export->assertStatus(200);
        $this->assertStringContainsString('application/json', (string) $export->headers->get('Content-Type'));
        $decoded = json_decode((string) $export->getContent(), true);
        $this->assertSame($run->id, $decoded['id'] ?? null);
    }

    public function test_schema_tables_created(): void
    {
        $this->assertTrue(Schema::hasTable('synthetic_test_runs'));
        $this->assertTrue(Schema::hasTable('synthetic_test_steps'));
    }
}
