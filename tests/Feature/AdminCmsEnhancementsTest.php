<?php

namespace Tests\Feature;

use App\Mail\AbandonedBookingRecoveryMail;
use App\Models\Booking;
use App\Models\Contact;
use App\Models\User;
use App\Models\WhatsappInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminCmsEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Retrieve the admin user created by ensure_admin_user_credentials migration
        $this->adminUser = User::where('email', 'dunesdiscovery85@gmail.com')->first();
        if (! $this->adminUser) {
            $this->adminUser = User::factory()->create([
                'email' => 'admin@dunesdiscoverytourism.com',
                'role' => 'admin',
            ]);
        }
    }

    /**
     * Test admin can view Incomplete Drafts Hub with statistics and draft listings.
     */
    public function test_admin_can_view_drafts_hub(): void
    {
        // Create draft booking
        $draft = Booking::create([
            'reference' => 'DFT-TEST-001',
            'name' => 'Sara Al Mansoori',
            'email' => 'sara@example.com',
            'phone' => '+971501234567',
            'tour_name' => 'Premium Evening Desert Safari',
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'subtotal' => 450.00,
            'total' => 450.00,
            'tour_date' => now()->addDays(3)->toDateString(),
            'adults' => 2,
            'children' => 0,
            'infants' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->get('/admin/bookings/drafts');

        $response->assertStatus(200);
        $response->assertSee('Abandoned Checkouts Hub');
        $response->assertSee('Sara Al Mansoori');
        $response->assertSee('DFT-TEST-001');
        $response->assertSee('Convert to Active Booking');
    }

    /**
     * Test admin can convert a draft booking to pending/confirmed.
     */
    public function test_admin_can_convert_draft_booking(): void
    {
        $draft = Booking::create([
            'reference' => 'DFT-CONV-001',
            'name' => 'Ahmed Khan',
            'email' => 'ahmed@example.com',
            'phone' => '+971509876543',
            'tour_name' => 'VIP Desert Safari Dubai',
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'subtotal' => 750.00,
            'total' => 750.00,
            'tour_date' => now()->addDays(5)->toDateString(),
            'adults' => 3,
            'children' => 0,
            'infants' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->post("/admin/bookings/drafts/{$draft->id}/convert", [
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'notes' => 'Customer called to confirm via phone',
        ]);

        $response->assertRedirect(route('admin.bookings.show', $draft->id));
        $this->assertEquals('confirmed', $draft->fresh()->status);
        $this->assertEquals('paid', $draft->fresh()->payment_status);
    }

    /**
     * Test admin can trigger recovery email for draft checkout.
     */
    public function test_admin_can_send_recovery_email(): void
    {
        Mail::fake();

        $draft = Booking::create([
            'reference' => 'DFT-MAIL-001',
            'name' => 'Elena Rostova',
            'email' => 'elena@example.com',
            'phone' => '+971504445555',
            'tour_name' => 'Quad Biking Safari',
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'subtotal' => 350.00,
            'total' => 350.00,
            'tour_date' => now()->addDays(2)->toDateString(),
            'adults' => 1,
            'children' => 0,
            'infants' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->postJson("/admin/bookings/drafts/{$draft->id}/send-recovery", [
            'to_email' => 'elena.custom@example.com',
            'subject' => 'Special VIP Safari Offer For You',
            'custom_message' => 'We have reserved your dune buggy with complimentary refreshments.',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        Mail::assertSent(AbandonedBookingRecoveryMail::class, function ($mail) {
            return $mail->hasTo('elena.custom@example.com') &&
                   $mail->envelope()->subject === 'Special VIP Safari Offer For You';
        });
    }

    /**
     * Test admin can preview recovery email with rendered HTML for draft checkout.
     */
    public function test_admin_can_preview_draft_recovery_email(): void
    {
        $draft = Booking::create([
            'reference' => 'DFT-PREV-001',
            'name' => 'Elena Rostova',
            'email' => 'elena@example.com',
            'phone' => '+971504445555',
            'tour_name' => 'Quad Biking Safari',
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'subtotal' => 350.00,
            'total' => 350.00,
            'tour_date' => now()->addDays(2)->toDateString(),
            'adults' => 1,
            'children' => 0,
            'infants' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->getJson("/admin/bookings/drafts/{$draft->id}/preview-recovery");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'draft' => [
                'reference' => 'DFT-PREV-001',
                'name' => 'Elena Rostova',
                'email' => 'elena@example.com',
            ],
        ]);
        $this->assertNotEmpty($response->json('html'));
        $this->assertStringContainsString('DFT-PREV-001', $response->json('html'));
    }

    /**
     * Test admin can export drafts CSV.
     */
    public function test_admin_can_export_drafts_csv(): void
    {
        Booking::create([
            'reference' => 'DFT-CSV-001',
            'name' => 'David Miller',
            'email' => 'david@example.com',
            'phone' => '+447911123456',
            'tour_name' => 'Morning Desert Safari',
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'subtotal' => 280.00,
            'total' => 280.00,
            'tour_date' => now()->addDays(4)->toDateString(),
            'adults' => 2,
            'children' => 0,
            'infants' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->get('/admin/bookings/drafts/export');

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
    }

    /**
     * Test admin can mark inquiry as read via AJAX.
     */
    public function test_admin_can_mark_inquiry_read(): void
    {
        $contact = Contact::create([
            'name' => 'Rashid Ali',
            'email' => 'rashid@example.com',
            'phone' => '+971500001122',
            'subject' => 'Private Dune Buggy Inquiry',
            'message' => 'Interested in booking private 4-seater buggy for family.',
            'status' => 'new',
        ]);

        $response = $this->actingAs($this->adminUser)->postJson("/admin/inquiries/{$contact->id}/mark-read");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertEquals('read', $contact->fresh()->status);
    }

    /**
     * Test admin can mark WhatsApp lead read and toggle status.
     */
    public function test_admin_can_mark_and_toggle_whatsapp_lead_read(): void
    {
        $lead = WhatsappInquiry::create([
            'name' => 'Maria Gonzalez',
            'phone' => '+34600123456',
            'tour_name' => 'Evening Desert Safari',
            'status' => 'new',
            'is_read' => false,
        ]);

        // 1. Mark as read
        $response1 = $this->actingAs($this->adminUser)->postJson("/admin/whatsapp-leads/{$lead->id}/mark-read");
        $response1->assertStatus(200);
        $response1->assertJson(['success' => true, 'is_read' => true]);
        $this->assertTrue((bool) $lead->fresh()->is_read);
        $this->assertEquals('read', $lead->fresh()->status);

        // 2. Toggle to unread
        $response2 = $this->actingAs($this->adminUser)->postJson("/admin/whatsapp-leads/{$lead->id}/toggle-read");
        $response2->assertStatus(200);
        $response2->assertJson(['success' => true, 'is_read' => false]);
        $this->assertFalse((bool) $lead->fresh()->is_read);

        // 3. Toggle back to read
        $response3 = $this->actingAs($this->adminUser)->postJson("/admin/whatsapp-leads/{$lead->id}/toggle-read");
        $response3->assertStatus(200);
        $response3->assertJson(['success' => true, 'is_read' => true]);
        $this->assertTrue((bool) $lead->fresh()->is_read);
    }

    /**
     * Test dynamic breadcrumbs bar and grouped sidebar are rendered.
     */
    public function test_dynamic_breadcrumbs_and_grouped_sidebar_rendered(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/bookings');

        $response->assertStatus(200);

        // Verify Dynamic Breadcrumbs
        $response->assertSee('Breadcrumbs');
        $response->assertSee('Back');
        $response->assertSee('Dashboard');
        $response->assertSee('Bookings');

        // Verify Grouped Sidebar Tree items
        $response->assertSee('Incomplete Drafts Hub');
        $response->assertSee('Operations & Fleet', false);
        $response->assertSee('Tour Inventory');
        $response->assertSee('Leads & Messages', false);
        $response->assertSee('Marketing & Promos', false);
        $response->assertSee('Content & Knowledge', false);
        $response->assertSee('Portal Settings');
    }
}
