<?php

namespace App\Http\Controllers;

use App\Models\LegalPage;
use Illuminate\Http\Request;

class LegalController extends Controller
{
    /**
     * Display the Terms and Conditions page.
     */
    public function terms()
    {
        $page = LegalPage::where('slug', 'terms-condition')
            ->with(['sections' => function ($query) {
                $query->orderBy('priority', 'asc')->with(['items' => function ($q) {
                    $q->orderBy('priority', 'asc');
                }]);
            }])->firstOrFail();

        $currentYear = date('Y');
        $pageTitle = "Terms & Conditions ({$currentYear}) | Dunes Discovery Tourism LLC Dubai";
        $pageDesc = "Read the official Terms and Conditions of Dunes Discovery Tourism LLC. Covers tour bookings, cancellations, 100% refund policy, and passenger safety guidelines.";
        $pageKeys = "dunes discovery terms, desert safari terms and conditions dubai, booking cancellation policy";
        $canonical = route('terms');
        $ogImage = asset('images/desert-safari-poster.avif');

        return view('legal.show', compact('page', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage'));
    }

    /**
     * Display the Privacy Policy page.
     */
    public function privacy()
    {
        $page = LegalPage::where('slug', 'privacy-policy')
            ->with(['sections' => function ($query) {
                $query->orderBy('priority', 'asc')->with(['items' => function ($q) {
                    $q->orderBy('priority', 'asc');
                }]);
            }])->firstOrFail();

        $currentYear = date('Y');
        $pageTitle = "Privacy Policy ({$currentYear}) | Dunes Discovery Tourism LLC Dubai";
        $pageDesc = "Read the Privacy Policy of Dunes Discovery Tourism LLC. Learn how we handle customer data, payment security, and UAE data protection compliance.";
        $pageKeys = "dunes discovery privacy policy, data protection, secure booking dubai";
        $canonical = route('privacy');
        $ogImage = asset('images/desert-safari-poster.avif');

        return view('legal.show', compact('page', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage'));
    }
}
