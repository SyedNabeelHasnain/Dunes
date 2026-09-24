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

    public function test_modals_have_x_cloak_and_synchronous_hidden_state(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $html = $response->getContent();

        // 1. Synchronous x-cloak style in head
        $this->assertStringContainsString('<style>[x-cloak] { display: none !important; }</style>', $html);

        // 2. Modals have x-cloak and inline display:none to prevent layout flash on page load
        $this->assertMatchesRegularExpression('/id="bookingModal"[^>]*x-cloak[^>]*style="display:\s*none;"/', $html);
        $this->assertMatchesRegularExpression('/id="welcomeOfferModal"[^>]*x-cloak[^>]*style="display:\s*none;"/', $html);
        $this->assertMatchesRegularExpression('/id="globalSearchModal"[^>]*x-cloak[^>]*style="display:\s*none;"/', $html);
        $this->assertMatchesRegularExpression('/id="safariMatcherModal"[^>]*x-cloak[^>]*style="display:\s*none;"/', $html);
        $this->assertMatchesRegularExpression('/id="customSafariModal"[^>]*x-cloak[^>]*style="display:\s*none;"/', $html);
    }

    public function test_pre_footer_cta_has_high_contrast_dark_styling(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $html = $response->getContent();

        // CTA section has dark background and top border
        $this->assertStringContainsString('class="cta-section py-16 sm:py-24 relative bg-slate-950 text-white overflow-hidden border-t border-slate-800"', $html);
        $this->assertStringContainsString('text-slate-300', $html);
    }

    public function test_hero_section_has_header_clearance_and_high_contrast_rating_badge(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $html = $response->getContent();

        // Hero has top padding to clear fixed navbar and avoid crowding
        $this->assertStringContainsString('pt-28 sm:pt-36 pb-16 sm:pb-20', $html);

        // Rating badge uses high-contrast dark pill instead of faint glass
        $this->assertStringContainsString('bg-slate-900/85 border border-white/20 text-white', $html);
    }

    public function test_footer_payment_icons_have_high_contrast_white_badges(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $html = $response->getContent();

        // Payment icons wrapped in clean white card badges
        $this->assertStringContainsString('bg-white rounded px-2 py-0.5 inline-flex items-center justify-center shadow-2xs h-6', $html);
        $this->assertStringContainsString('visa-card.svg', $html);
        $this->assertStringContainsString('mastercard.svg', $html);
        $this->assertStringContainsString('applepay.svg', $html);
        $this->assertStringContainsString('googlepay.svg', $html);
    }
}
