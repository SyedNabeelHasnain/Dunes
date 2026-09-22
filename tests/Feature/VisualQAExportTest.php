<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisualQAExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_phone_inputs_render_standardized_markup(): void
    {
        // 1. Homepage Verification
        $response = $this->get('/');
        $response->assertStatus(200);
        $homeContent = $response->getContent();

        // Verify Welcome Offer Phone Markup
        $this->assertStringContainsString('welcome-phone-field', $homeContent);
        $this->assertStringNotContainsString('<input type="tel" class="form-control border-0 shadow-none py-3 fw-bold ps-2" id="welcomePhone"', $homeContent);
        $this->assertStringContainsString('id="welcomePhone" name="phone" placeholder="50 123 4567"', $homeContent);
        $this->assertStringContainsString('Phone / WhatsApp Number', $homeContent);

        // Verify Booking Modal Phone Markup
        $this->assertStringContainsString('id="bookingPhone" name="phone"', $homeContent);
        $this->assertStringContainsString('placeholder="50 123 4567"', $homeContent);

        // 2. Contact Page Verification
        $contactResponse = $this->get('/contact');
        $contactResponse->assertStatus(200);
        $contactContent = $contactResponse->getContent();

        // Verify Contact Phone Markup
        $this->assertStringContainsString('id="phone" name="phone"', $contactContent);
        $this->assertStringContainsString('placeholder="50 123 4567"', $contactContent);
    }
}
