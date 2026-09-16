<?php

namespace App\Http\Controllers;

use App\Models\LegalPage;
use Illuminate\Http\Request;

class LegalController extends Controller
{
    /**
     * Helper to load a legal page with its eager-loaded sections and items.
     */
    private function getPageWithRelations(string $slug): LegalPage
    {
        return LegalPage::where('slug', $slug)
            ->with(['sections' => function ($query) {
                $query->orderBy('priority', 'asc')->with(['items' => function ($q) {
                    $q->orderBy('priority', 'asc');
                }]);
            }])->firstOrFail();
    }

    /**
     * Display the Terms and Conditions page.
     */
    public function terms()
    {
        $page = $this->getPageWithRelations('terms-condition');
        $currentYear = date('Y');
        $pageTitle = "Terms & Conditions ({$currentYear}) | Dunes Discovery Tourism LLC Dubai";
        $pageDesc = "Read the official Terms and Conditions of Dunes Discovery Tourism LLC. Covers tour bookings, cancellations, 100% refund policy, and passenger safety guidelines.";
        $pageKeys = "dunes discovery terms, desert safari terms and conditions dubai, booking cancellation policy, uae tour operator contract";
        $canonical = route('terms');
        $ogImage = asset('images/desert-safari-poster.avif');

        return view('legal.show', compact('page', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage'));
    }

    /**
     * Display the Privacy Policy page.
     */
    public function privacy()
    {
        $page = $this->getPageWithRelations('privacy-policy');
        $currentYear = date('Y');
        $pageTitle = "Privacy Policy & Data Protection ({$currentYear}) | Dunes Discovery Tourism LLC Dubai";
        $pageDesc = "Read the Privacy Policy of Dunes Discovery Tourism LLC. Learn how we handle customer data, payment security, and UAE PDPL and GDPR compliance.";
        $pageKeys = "dunes discovery privacy policy, data protection dubai, uae pdpl compliance, secure booking dubai";
        $canonical = route('privacy');
        $ogImage = asset('images/desert-safari-poster.avif');

        return view('legal.show', compact('page', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage'));
    }

    /**
     * Display the Cookie & Tracking Technologies Policy page.
     */
    public function cookies()
    {
        $page = $this->getPageWithRelations('cookie-policy');
        $currentYear = date('Y');
        $pageTitle = "Cookie & Tracking Technologies Policy ({$currentYear}) | Dunes Discovery Tourism LLC Dubai";
        $pageDesc = "Learn about cookies, local storage, Google Consent Mode v2, and analytics tracking deployed on Dunes Discovery Tourism in compliance with international ePrivacy standards.";
        $pageKeys = "cookie policy dubai, google consent mode v2, website cookies desert safari, analytics tracking policy";
        $canonical = route('cookies');
        $ogImage = asset('images/desert-safari-poster.avif');

        return view('legal.show', compact('page', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage'));
    }

    /**
     * Display the Cancellation, Refund & Rescheduling Policy page.
     */
    public function cancellation()
    {
        $page = $this->getPageWithRelations('cancellation-refund-policy');
        $currentYear = date('Y');
        $pageTitle = "Cancellation, Refund & Rescheduling Policy (100% Refund Guarantee) | Dunes Discovery Tourism";
        $pageDesc = "Explore our 24-hour free cancellation policy, 100% refund guarantee, and flexible rescheduling for all Dubai desert safaris and city excursions.";
        $pageKeys = "desert safari refund policy, cancel desert safari dubai, 100 refund guarantee, free cancellation tour dubai, rescheduling dubai tour";
        $canonical = route('cancellation');
        $ogImage = asset('images/desert-safari-poster.avif');

        return view('legal.show', compact('page', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage'));
    }

    /**
     * Display the Payment, Security & Anti-Fraud Policy page.
     */
    public function paymentSecurity()
    {
        $page = $this->getPageWithRelations('payment-security-policy');
        $currentYear = date('Y');
        $pageTitle = "Payment Security & Anti-Fraud Policy | Dunes Discovery Tourism LLC Dubai";
        $pageDesc = "Learn about our PCI-DSS Level 1 payment security, 256-bit SSL encryption, 3D Secure 2.0 verification, and fraud prevention through Ziina Payment Gateway.";
        $pageKeys = "payment security dubai, ziina payment gateway, secure booking desert safari, 3d secure apple pay, anti fraud travel policy";
        $canonical = route('payment.security');
        $ogImage = asset('images/desert-safari-poster.avif');

        return view('legal.show', compact('page', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage'));
    }

    /**
     * Display the Desert Safari & Off-Road Adventure Liability Waiver page.
     */
    public function safetyWaiver()
    {
        $page = $this->getPageWithRelations('safety-liability-waiver');
        $currentYear = date('Y');
        $pageTitle = "Desert Safari & Off-Road Adventure Liability Waiver | Dunes Discovery Tourism";
        $pageDesc = "Review our comprehensive off-road safety standards, RTA-certified safari drivers, vehicle roll-cages, and health guidelines for desert dune adventures.";
        $pageKeys = "desert safari safety waiver, dubai dune bashing rules, rta safari regulations, off road liability waiver, quad biking safety rules";
        $canonical = route('safety.waiver');
        $ogImage = asset('images/desert-safari-poster.avif');

        return view('legal.show', compact('page', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage'));
    }

    /**
     * Display the AI Search Engine, Editorial & Intellectual Property Policy page.
     */
    public function aiEditorial()
    {
        $page = $this->getPageWithRelations('ai-editorial-policy');
        $currentYear = date('Y');
        $pageTitle = "AI Search Engine, Editorial & Intellectual Property Policy | Dunes Discovery Tourism";
        $pageDesc = "Discover our Generative Engine Optimization (GEO) standards, machine-readable llms.txt protocols, expert guide verification, and copyright rules.";
        $pageKeys = "ai editorial policy, llms.txt tourism dubai, generative engine optimization, content transparency, copyright dubai travel";
        $canonical = route('ai.editorial');
        $ogImage = asset('images/desert-safari-poster.avif');

        return view('legal.show', compact('page', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage'));
    }

    /**
     * Display the Responsible Tourism & Environmental Sustainability Policy page.
     */
    public function responsibleTourism()
    {
        $page = $this->getPageWithRelations('responsible-tourism-policy');
        $currentYear = date('Y');
        $pageTitle = "Responsible Tourism & Environmental Sustainability Policy | Dunes Discovery Tourism";
        $pageDesc = "Our charter for Arabian desert ecosystem protection, Leave No Trace wilderness protocols, ethical camel welfare, and Emirati Bedouin heritage preservation.";
        $pageKeys = "responsible tourism dubai, desert conservation ddcr, sustainable safari dubai, eco friendly desert tour, animal welfare charter";
        $canonical = route('responsible.tourism');
        $ogImage = asset('images/desert-safari-poster.avif');

        return view('legal.show', compact('page', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage'));
    }

    /**
     * Generic fallback display for any legal page by slug.
     */
    public function show(string $slug)
    {
        $page = $this->getPageWithRelations($slug);
        $currentYear = date('Y');
        $pageTitle = "{$page->title} ({$currentYear}) | Dunes Discovery Tourism LLC Dubai";
        $pageDesc = $page->description ?: "Read official policy guidelines from Dunes Discovery Tourism L.L.C in Dubai, United Arab Emirates.";
        $pageKeys = "dunes discovery policy, {$page->slug}, dubai tour policies";
        $canonical = url('/' . $page->slug);
        $ogImage = asset('images/desert-safari-poster.avif');

        return view('legal.show', compact('page', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage'));
    }
}
