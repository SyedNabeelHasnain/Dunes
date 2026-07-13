<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\LegalPage;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Api\EmailVerificationController;
use Illuminate\Http\Request;

class AjaxGatewayController extends Controller
{
    protected $apiController;
    protected $bookingController;
    protected $pageController;
    protected $emailVerificationController;

    public function __construct(
        ApiController $apiController,
        BookingController $bookingController,
        PageController $pageController,
        EmailVerificationController $emailVerificationController
    ) {
        $this->apiController = $apiController;
        $this->bookingController = $bookingController;
        $this->pageController = $pageController;
        $this->emailVerificationController = $emailVerificationController;
    }

    public function handle(Request $request)
    {
        $action = $request->input('action');

        switch ($action) {
            case 'getTiers':
                $tourId = (int)$request->input('tour_id');
                $tour = Tour::find($tourId);
                if (!$tour) {
                    return response()->json(['error' => 'Invalid tour'], 400);
                }

                $tiers = $tour->tiers()
                    ->where('status', 'active')
                    ->get()
                    ->map(function ($tier) {
                        return [
                            'id' => $tier->id,
                            'name' => $tier->name,
                            'description' => $tier->description,
                            'is_popular' => $tier->is_popular ? 1 : 0,
                            'price' => $tier->pivot->price,
                            'old_price' => $tier->pivot->old_price,
                            'price_type' => $tier->pivot->price_type,
                        ];
                    });

                $addons = $tour->addons()
                    ->where('status', 'active')
                    ->get()
                    ->map(function ($addon) {
                        return [
                            'id' => $addon->id,
                            'name' => $addon->name,
                            'description' => $addon->description,
                            'price' => $addon->pivot->price,
                        ];
                    });

                return response()->json([
                    'tiers' => $tiers,
                    'addons' => $addons
                ]);

            case 'get_legal_content':
                $type = $request->input('type');
                if (!in_array($type, ['terms-condition', 'privacy-policy'])) {
                    return response()->json(['success' => false, 'message' => 'Invalid content type']);
                }

                $page = LegalPage::where('slug', $type)->first();
                if (!$page) {
                    return response()->json(['success' => false, 'message' => 'Content not found']);
                }

                $sections = $page->sections()->orderBy('priority', 'asc')->with(['items' => function($q) {
                    $q->orderBy('priority', 'asc');
                }])->get();

                $html = view('partials.legal-modal-content', compact('page', 'sections'))->render();
                return response()->json(['success' => true, 'html' => $html]);

            case 'geoip':
                return $this->apiController->geoip();

            case 'booking':
                return $this->bookingController->checkout($request);

            case 'contact':
                return $this->pageController->submitContact($request);

            case 'logWhatsApp':
                return $this->pageController->logWhatsapp($request);

            case 'check_email_status':
                return $this->emailVerificationController->status($request);

            case 'send_otp':
                return $this->emailVerificationController->sendOtp($request);

            case 'verify_otp':
                return $this->emailVerificationController->verifyOtp($request);

            default:
                return response()->json(['error' => 'Invalid action'], 400);
        }
    }
}
