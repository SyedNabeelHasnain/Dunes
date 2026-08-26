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
        $phone = $settingsService->get('site_phone', '+971 56 466 8467');
        $waPhone = $settingsService->get('site_whatsapp', '+971 56 466 8467');
        $email = $settingsService->getFromEmail();

        $tours = Tour::with(['tiers', 'addons', 'category', 'itineraries'])
            ->where('status', 'active')
            ->orderBy('priority', 'asc')
            ->get();

        $categories = Category::with(['tours' => function($q) {
            $q->where('status', 'active')->with(['tiers', 'addons'])->orderBy('priority', 'asc');
        }])->get();

        $globalAddons = Addon::where('status', 'active')->get();

        $autoPrint = $request->has('print') || $request->has('download');

        return view('pages.rate-card', compact(
            'tours',
            'categories',
            'globalAddons',
            'phone',
            'waPhone',
            'email',
            'autoPrint'
        ));
    }
}