<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Contact;
use App\Models\Setting;
use App\Models\Tour;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarketingAttributionAndDraftDeduplicationTest extends TestCase
{
    use RefreshDatabase;

    protected Tour $tour;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        app(SettingsService::class)->clearCache();

        Setting::updateOrCreate(['setting_key' => 'site_phone'], ['setting_value' => '+971 50 245 6056']);
        Setting::updateOrCreate(['setting_key' => 'currency_rates'], ['setting_value' => json_encode(['AED' => 1, 'USD' => 0.2723, 'EUR' => 0.251, 'GBP' => 0.215])]);

        $this->tour = Tour::create([
            'name' => 'VIP Evening Desert Safari Dubai',
            'slug' => 'vip-evening-desert-safari-dubai',
            'status' => 'active',
            'base_price' => 250.00,
            'duration' => '6 Hours',
        ]);
    }

    /**
     * Test 1: Rapid saveDraft calls with the same draft_token deduplicate into a single record.
     */
    public function test_rapid_save_draft_calls_with_same_token_are_deduplicated_to_single_record(): void
    {
        $token = (string) Str::uuid();

        // 1st request: Guest enters partial name
        $response1 = $this->withHeaders(['Accept' => 'application/json'])
            ->postJson(route('booking.draft'), [
                'draft_token' => $token,
                'tour_id' => $this->tour->id,
                'name' => 'Alexander',
                'tour_date' => now()->addDays(3)->format('Y-m-d'),
                'adults' => 2,
                'children' => 0,
                'total' => 500.00,
                'traffic_channel' => 'Google Ads',
                'gclid' => 'gclid_google_ads_test_123',
            ]);

        $response1->assertOk();
        $response1->assertJson(['success' => true]);
        $data1 = $response1->json();
        $this->assertNotEmpty($data1['draft_id']);
        $draftId = $data1['draft_id'];

        // 2nd request: Guest types email while debouncing
        $response2 = $this->withHeaders(['Accept' => 'application/json'])
            ->postJson(route('booking.draft'), [
                'draft_token' => $token,
                'draft_id' => $draftId,
                'tour_id' => $this->tour->id,
                'name' => 'Alexander Wright',
                'email' => 'alex.wright@uk-travel.co.uk',
                'tour_date' => now()->addDays(3)->format('Y-m-d'),
                'adults' => 2,
                'children' => 0,
                'total' => 500.00,
            ]);

        $response2->assertOk();
        $data2 = $response2->json();
        $this->assertEquals($draftId, $data2['draft_id'], 'Draft ID must remain identical on subsequent keystroke calls');

        // 3rd request: Guest adds international phone number
        $response3 = $this->withHeaders(['Accept' => 'application/json'])
            ->postJson(route('booking.draft'), [
                'draft_token' => $token,
                'draft_id' => $draftId,
                'tour_id' => $this->tour->id,
                'name' => 'Alexander Wright',
                'email' => 'alex.wright@uk-travel.co.uk',
                'phone' => '+447911123456',
                'tour_date' => now()->addDays(3)->format('Y-m-d'),
                'adults' => 2,
                'children' => 0,
                'total' => 500.00,
            ]);

        $response3->assertOk();
        $data3 = $response3->json();
        $this->assertEquals($draftId, $data3['draft_id']);

        // Assert exactly ONE draft record exists in database
        $this->assertEquals(1, Booking::where('status', 'draft')->count());
        $savedDraft = Booking::find($draftId);
        $this->assertEquals('Alexander Wright', $savedDraft->name);
        $this->assertEquals('alex.wright@uk-travel.co.uk', $savedDraft->email);
        $this->assertEquals('+447911123456', $savedDraft->phone);
        $this->assertEquals('Google Ads', $savedDraft->traffic_channel);
        $this->assertEquals('gclid_google_ads_test_123', $savedDraft->gclid);
        $this->assertEquals($token, $savedDraft->draft_token);
    }

    /**
     * Test 2: Heuristic merge logic never overwrites valid guest data with placeholder defaults.
     */
    public function test_heuristic_merge_never_overwrites_valid_guest_data_with_placeholders(): void
    {
        $token = (string) Str::uuid();

        // 1st request with full valid details
        $this->postJson(route('booking.draft'), [
            'draft_token' => $token,
            'tour_id' => $this->tour->id,
            'name' => 'Fatima Al Mansoori',
            'email' => 'fatima@dubaigov.ae',
            'phone' => '+971501234567',
            'tour_date' => now()->addDays(2)->format('Y-m-d'),
            'adults' => 4,
            'total' => 1000.00,
            'traffic_channel' => 'Organic Search',
        ]);

        $draft = Booking::where('draft_token', $token)->first();
        $this->assertNotNull($draft);
        $this->assertEquals('Fatima Al Mansoori', $draft->name);

        // 2nd request sent from another tab or back button with placeholder names
        $this->postJson(route('booking.draft'), [
            'draft_token' => $token,
            'draft_id' => $draft->id,
            'tour_id' => $this->tour->id,
            'name' => 'Prospective Guest',
            'email' => 'abandoned@guest.local',
            'phone' => '', // Empty phone
            'tour_date' => now()->addDays(2)->format('Y-m-d'),
            'adults' => 4,
            'total' => 1000.00,
        ]);

        $draft->refresh();
        // Valid name, email, and phone MUST be retained!
        $this->assertEquals('Fatima Al Mansoori', $draft->name, 'Valid guest name must never be overwritten with placeholder');
        $this->assertEquals('fatima@dubaigov.ae', $draft->email, 'Valid email must never be overwritten with abandoned@guest.local');
        $this->assertEquals('+971501234567', $draft->phone, 'Existing phone must not be wiped by empty string');
        $this->assertEquals('Organic Search', $draft->traffic_channel);
    }

    /**
     * Test 3: Contact submission captures full omnichannel marketing attribution.
     */
    public function test_contact_submission_captures_traffic_channel_and_attribution(): void
    {
        $response = $this->postJson(route('contact.submit'), [
            'name' => 'Elena Rostova',
            'email' => 'elena.rostova@yandex.ru',
            'phone' => '+79161234567',
            'subject' => 'VIP Private Safari Query',
            'message' => 'Do you have Russian speaking safari guides available this weekend?',
            'traffic_channel' => 'Yandex Organic',
            'utm_source' => 'yandex',
            'utm_medium' => 'organic',
            'utm_campaign' => 'ru_dubai_desert_safari',
            'landing_page' => 'https://dunesdiscoverytourism.com/ru/safari-dubai',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('contacts', [
            'email' => 'elena.rostova@yandex.ru',
            'traffic_channel' => 'Yandex Organic',
            'utm_source' => 'yandex',
            'utm_medium' => 'organic',
            'utm_campaign' => 'ru_dubai_desert_safari',
            'landing_page' => 'https://dunesdiscoverytourism.com/ru/safari-dubai',
        ]);
    }

    /**
     * Test 4: Artisan command bookings:consolidate-drafts consolidates duplicate historical draft records.
     */
    public function test_artisan_consolidate_drafts_merges_duplicate_historical_drafts(): void
    {
        $email = 'corporate.vip@adnoc.ae';

        // Draft A: Shell with placeholder name and empty phone
        $draftA = Booking::create([
            'reference' => 'DFT-' . strtoupper(Str::random(8)),
            'tour_id' => $this->tour->id,
            'tour_name' => $this->tour->name,
            'name' => 'Prospective Guest',
            'email' => $email,
            'phone' => '',
            'tour_date' => now()->addDays(5),
            'adults' => 2,
            'subtotal' => 500.00,
            'total' => 500.00,
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'created_at' => now()->subMinutes(50),
        ]);

        // Draft B: Master record with complete phone, name, and attribution
        $draftB = Booking::create([
            'reference' => 'DFT-' . strtoupper(Str::random(8)),
            'tour_id' => $this->tour->id,
            'tour_name' => $this->tour->name,
            'name' => 'Sheikh Mansoor',
            'email' => $email,
            'phone' => '+971509998877',
            'tour_date' => now()->addDays(5),
            'adults' => 2,
            'subtotal' => 500.00,
            'total' => 500.00,
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'traffic_channel' => 'Meta Ads',
            'fbclid' => 'fb_campaign_vip_999',
            'created_at' => now()->subMinutes(30),
        ]);

        // Prior to consolidation, 2 drafts exist
        $this->assertEquals(2, Booking::where('status', 'draft')->where('email', $email)->count());

        // Run the consolidation Artisan command
        $exitCode = Artisan::call('bookings:consolidate-drafts');
        $this->assertEquals(0, $exitCode);

        // After consolidation, exactly 1 master draft remains
        $activeDrafts = Booking::where('status', 'draft')->where('email', $email)->get();
        $this->assertCount(1, $activeDrafts);

        $survivor = $activeDrafts->first();
        $this->assertEquals('Sheikh Mansoor', $survivor->name);
        $this->assertEquals('+971509998877', $survivor->phone);
        $this->assertEquals('Meta Ads', $survivor->traffic_channel);
    }

    /**
     * Test 5: Artisan command attribution:backfill assigns channels to legacy records based on UTM/referrer.
     */
    public function test_artisan_backfill_attribution_assigns_channels_to_legacy_records(): void
    {
        // Legacy booking 1: has UTM google/cpc, but traffic_channel is null
        $bookingGoogle = Booking::create([
            'reference' => 'DT-LEGACY-001',
            'tour_id' => $this->tour->id,
            'tour_name' => $this->tour->name,
            'name' => 'Google Booker',
            'email' => 'google.user@example.com',
            'phone' => '+971501112233',
            'tour_date' => now()->addDays(1),
            'adults' => 1,
            'subtotal' => 250.00,
            'total' => 250.00,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'traffic_channel' => null,
        ]);

        // Legacy booking 2: has yandex referrer, no UTM
        $bookingYandex = Booking::create([
            'reference' => 'DT-LEGACY-002',
            'tour_id' => $this->tour->id,
            'tour_name' => $this->tour->name,
            'name' => 'Yandex Booker',
            'email' => 'yandex.user@example.com',
            'phone' => '+79165554433',
            'tour_date' => now()->addDays(2),
            'adults' => 2,
            'subtotal' => 500.00,
            'total' => 500.00,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'referrer' => 'https://yandex.ru/search/?text=dubai+desert+safari',
            'traffic_channel' => null,
        ]);

        // Legacy contact 1: has instagram social UTM
        $contactSocial = Contact::create([
            'name' => 'Instagram Organic Lead',
            'email' => 'social@example.com',
            'phone' => '+971503334455',
            'subject' => 'Tour pricing',
            'message' => 'Hello',
            'utm_source' => 'instagram',
            'utm_medium' => 'social',
            'traffic_channel' => null,
        ]);

        // Legacy contact 2: has meta paid fbclid
        $contactMetaPaid = Contact::create([
            'name' => 'Meta Ads Lead',
            'email' => 'meta.paid@example.com',
            'phone' => '+971508889900',
            'subject' => 'VIP Query',
            'message' => 'Booking inquiry from ad',
            'fbclid' => 'fb_campaign_live_12345',
            'traffic_channel' => null,
        ]);

        $exitCode = Artisan::call('attribution:backfill');
        $this->assertEquals(0, $exitCode);

        $bookingGoogle->refresh();
        $bookingYandex->refresh();
        $contactSocial->refresh();
        $contactMetaPaid->refresh();

        $this->assertEquals('Google Ads', $bookingGoogle->traffic_channel);
        $this->assertEquals('Organic Search', $bookingYandex->traffic_channel);
        $this->assertEquals('Social', $contactSocial->traffic_channel);
        $this->assertEquals('Meta Ads', $contactMetaPaid->traffic_channel);
    }

    /**
     * Test 6: Admin Reports BI Hub index, export CSV, and print endpoints return HTTP 200.
     */
    public function test_admin_reports_index_export_and_print_endpoints_return_200(): void
    {
        $admin = User::factory()->create();

        // Seed sample booking for report content
        Booking::create([
            'reference' => 'DT-BI-REPORT-001',
            'tour_id' => $this->tour->id,
            'tour_name' => $this->tour->name,
            'name' => 'BI Test Guest',
            'email' => 'bi.guest@dunesdiscoverytourism.com',
            'phone' => '+971501239876',
            'tour_date' => now()->addDays(4),
            'adults' => 2,
            'subtotal' => 600.00,
            'total' => 600.00,
            'payment_amount' => 600.00,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'traffic_channel' => 'Google Ads',
        ]);

        // 1. BI Hub Dashboard Index
        $responseIndex = $this->actingAs($admin)->get(route('admin.reports.index'));
        $responseIndex->assertOk();
        $responseIndex->assertSee('Enterprise Reporting &amp; BI Hub', false);
        $responseIndex->assertSee('Acquisition Channel Revenue');

        // 2. Drafts tab on BI Hub
        $responseDrafts = $this->actingAs($admin)->get(route('admin.reports.index', ['dataset' => 'drafts']));
        $responseDrafts->assertOk();

        // 3. CSV Export Stream
        $responseCsv = $this->actingAs($admin)->get(route('admin.reports.export.csv', ['dataset' => 'bookings']));
        $responseCsv->assertOk();
        $this->assertStringContainsString('text/csv', (string) $responseCsv->headers->get('content-type'));

        // 4. Print / PDF Layout
        $responsePrint = $this->actingAs($admin)->get(route('admin.reports.print', ['dataset' => 'bookings']));
        $responsePrint->assertOk();
        $responsePrint->assertSee('DUNES DISCOVERY TOURISM LLC');
        $responsePrint->assertSee('1430583');
    }

    /**
     * Test 7: Admin can trigger draft consolidation directly from Incomplete Drafts UI.
     */
    public function test_admin_can_trigger_draft_consolidation_via_post_action(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.bookings.drafts.consolidate'));
        $response->assertRedirect(route('admin.bookings.drafts'));
        $response->assertSessionHas('success');
    }
}
