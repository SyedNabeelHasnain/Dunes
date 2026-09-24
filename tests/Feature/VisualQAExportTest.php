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

    public function test_modals_have_proper_stacking_context_and_contrast(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $html = $response->getContent();

        // 1. Booking modal stacking context (Backdrop z-0, dialog relative z-10)
        $this->assertStringContainsString('class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity z-0"', $html);
        $this->assertStringContainsString('class="relative z-10 min-h-full flex items-end sm:items-center justify-center p-0 sm:p-4 text-center"', $html);
        $this->assertStringContainsString('class="relative z-10 w-full sm:max-w-2xl lg:max-w-3xl rounded-t-3xl sm:rounded-3xl bg-white text-left align-middle shadow-2xl transition-all border border-slate-200 overflow-hidden flex flex-col max-h-[92vh] sm:max-h-[88vh]"', $html);

        // 2. High contrast section labels in booking modal
        $this->assertStringContainsString('text-slate-800 mb-2" for="bookingTour">Choose Tour</label>', $html);
        $this->assertStringContainsString('text-slate-800 mb-2">Select Package</div>', $html);
        $this->assertStringContainsString('text-slate-800">When</div>', $html);
        $this->assertStringContainsString('text-slate-800 mb-2" for="bookingAdults">Guests</label>', $html);
        $this->assertStringContainsString('text-slate-800 mb-2" for="bookingLocation">Pickup Location</label>', $html);

        // 3. Other modals have elevated stacking context
        $this->assertStringContainsString('class="relative z-10 w-full max-w-4xl transform overflow-hidden rounded-3xl bg-white text-left align-middle shadow-2xl transition-all border border-slate-200"', $html);
        $this->assertStringContainsString('class="relative z-10 w-full max-w-2xl transform overflow-hidden rounded-3xl bg-white text-left align-middle shadow-2xl transition-all border border-slate-200"', $html);
        $this->assertStringContainsString('class="relative z-10 w-full max-w-2xl transform overflow-hidden rounded-3xl bg-slate-950 p-5 sm:p-7 text-left align-middle shadow-2xl transition-all border border-orange-500/40 text-white flex flex-col min-h-[500px]"', $html);
        $this->assertStringContainsString('class="relative z-10 w-full max-w-5xl transform overflow-hidden rounded-3xl bg-slate-950 text-left align-middle shadow-2xl transition-all border border-orange-500/40 text-white flex flex-col max-h-[92vh]"', $html);
    }

    public function test_booking_modal_has_isolated_body_scroll_and_anchored_footer(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $html = $response->getContent();

        // 1. Form wraps the whole body and footer as a flex column
        $this->assertStringContainsString('<form id="bookingForm" autocomplete="off" class="flex-1 flex flex-col min-h-0 needs-validation m-0 p-0">', $html);

        // 2. Scrollable area is isolated to the body ONLY
        $this->assertStringContainsString('<div class="booking-scroll-area flex-1 overflow-y-auto bg-slate-50 min-h-0 p-4 sm:p-6 lg:p-7">', $html);

        // 3. Footer is anchored statically outside the scrollable body with generous padding
        $this->assertStringContainsString('<div class="border-t border-slate-200/90 bg-white py-4 px-5 sm:px-7 shrink-0 shadow-xs z-10">', $html);
        $this->assertStringContainsString('<span>Confirm Booking</span>', $html);

        // 4. Addons section has clean spacing and 1-click pill
        $this->assertStringContainsString('id="addonsSection"', $html);
        $this->assertStringContainsString('1-Click Add</span>', $html);
        $this->assertStringContainsString('class="addon-horizontal-wrapper flex gap-3 overflow-x-auto pb-3 pt-1 scrollbar-none" id="addonList"', $html);

        // 5. Pickup Location wrapper and input autocomplete readiness (unclipped dropdown)
        $this->assertStringContainsString('booking-location-wrapper relative', $html);
        $this->assertStringContainsString('name="location" id="bookingLocation"', $html);

        // 6. Ensure Footer is nested inside the form and inside the modal card (no stray closing div)
        $formStartPos = strpos($html, '<form id="bookingForm"');
        $footerPos = strpos($html, '<div class="border-t border-slate-200/90 bg-white py-4 px-5 sm:px-7 shrink-0 shadow-xs z-10">');
        $formEndPos = strpos($html, '</form>', $footerPos);
        $this->assertNotFalse($formStartPos);
        $this->assertNotFalse($footerPos);
        $this->assertNotFalse($formEndPos);
        $this->assertTrue($footerPos > $formStartPos && $footerPos < $formEndPos, 'Footer must be nested inside <form id="bookingForm">');
    }
}
