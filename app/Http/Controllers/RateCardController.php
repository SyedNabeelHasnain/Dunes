<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\Category;
use App\Models\Addon;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class RateCardController extends Controller
{
    /**
     * Display the dynamic, print-ready Tour Rate Card.
     */
    public function index(Request $request)
    {
        $settingsService = app(SettingsService::class);
        $phone = $settingsService->get('site_phone', '+971 50 245 6056');
        $waPhone = $settingsService->get('site_whatsapp', '971502456056');
        $email = $settingsService->getFromEmail();

        $currentYear = date('Y');
        $defaultTitle = "Official Rate Card & Pricing Guide {$currentYear} | Dunes Discovery Tourism";
        $defaultDesc = "View transparent, all-inclusive rates for all Dubai Desert Safari packages, VIP majlis upgrades, buggy rentals, and private transport options.";
        $defaultKeys = "desert safari prices dubai, safari rate card 2026, dubai buggy prices, vip safari rates";

        $pageTitle = $settingsService->get('seo_rate_card_title') ?: $defaultTitle;
        $pageDesc = $settingsService->get('seo_rate_card_description') ?: $defaultDesc;
        $pageKeys = $settingsService->get('seo_rate_card_keywords') ?: $defaultKeys;
        $ogImageSetting = $settingsService->get('seo_rate_card_og_image');
        $ogImage = $ogImageSetting ? asset(ltrim($ogImageSetting, '/')) : asset('images/desert-safari-poster.avif');
        $canonical = url('/rate-card');

        $tours = Tour::with(['tiers', 'addons', 'category', 'itineraries'])
            ->where('status', 'active')
            ->orderBy('priority', 'asc')
            ->get();

        $categories = Category::with(['tours' => function($q) {
            $q->where('status', 'active')->with(['tiers', 'addons'])->orderBy('priority', 'asc');
        }])->get();

        $globalAddons = Addon::where('status', 'active')
            ->where('default_price', '>', 0)
            ->orderBy('priority', 'asc')
            ->get();

        $autoPrint = $request->has('print') || $request->has('download');

        return view('pages.rate-card', compact(
            'tours',
            'categories',
            'globalAddons',
            'phone',
            'waPhone',
            'email',
            'autoPrint',
            'pageTitle',
            'pageDesc',
            'pageKeys',
            'canonical',
            'ogImage'
        ));
    }
}