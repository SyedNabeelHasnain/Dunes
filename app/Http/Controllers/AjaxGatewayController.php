<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Models\Booking;
use App\Models\LegalPage;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

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
                return $this->getTiers($request);

            case 'get_legal_content':
                $type = $request->input('type');
                if (! $type) {
                    return response()->json(['success' => false, 'message' => 'Missing content type']);
                }

                $page = LegalPage::where('slug', $type)->first();
                if (! $page) {
                    return response()->json(['success' => false, 'message' => 'Content not found']);
                }

                $sections = $page->sections()->orderBy('priority', 'asc')->with(['items' => function ($q) {
                    $q->orderBy('priority', 'asc');
                }])->get();

                $html = view('partials.legal-modal-content', compact('page', 'sections'))->render();

                return response()->json(['success' => true, 'html' => $html]);

            case 'geoip':
            case 'get_geoip':
                return $this->apiController->geoip();

            case 'booking':
                return $this->bookingController->checkout($request);

            case 'contact':
                return $this->submitContact($request);

            case 'logWhatsApp':
            case 'log_whatsapp':
                return $this->logWhatsapp($request);

            case 'check_email_status':
                return $this->emailVerificationController->status($request);

            case 'send_otp':
                return $this->emailVerificationController->sendOtp($request);

            case 'verify_otp':
                return $this->emailVerificationController->verifyOtp($request);

            case 'get_social_proof':
            case 'social_proof':
                return $this->getSocialProof();

            case 'subscribe_newsletter':
            case 'subscribe':
                return app(SubscriberController::class)->subscribe($request);

            case 'live_search':
            case 'search':
                return app(TourController::class)->liveSearch($request);

            default:
                return response()->json(['error' => 'Invalid action'], 400);
        }
    }

    public function getTiers(Request $request)
    {
        $tourId = (int) $request->input('tour_id');
        $tour = Tour::find($tourId);
        if (! $tour) {
            return response()->json(['error' => 'Invalid tour'], 400);
        }

        $tiersQuery = $tour->tiers()->where('status', 'active');
        $withPivot = ['price', 'old_price', 'price_type'];
        if (Schema::hasColumn('tour_tiers', 'child_price')) {
            $withPivot[] = 'child_price';
        }
        if (Schema::hasColumn('tour_tiers', 'guest_count')) {
            $withPivot[] = 'guest_count';
        }
        $tiersQuery->withPivot($withPivot);

        $tiers = $tiersQuery
            ->get()
            ->map(function ($tier) {
                $guestCount = (int) ($tier->pivot->guest_count ?: ($tier->guest_count ?: 1));
                if ($guestCount <= 0) {
                    if (preg_match('/(6|six)/i', $tier->name ?? '')) $guestCount = 6;
                    elseif (preg_match('/(4|four)/i', $tier->name ?? '')) $guestCount = 4;
                    elseif (preg_match('/(2|two)/i', $tier->name ?? '')) $guestCount = 2;
                    else $guestCount = 1;
                }
                return [
                    'id' => $tier->id,
                    'name' => $tier->name,
                    'description' => $tier->description,
                    'is_popular' => $tier->is_popular ? 1 : 0,
                    'price' => $tier->pivot->price,
                    'child_price' => $tier->pivot->child_price ?? 0,
                    'old_price' => $tier->pivot->old_price,
                    'price_type' => $tier->pivot->price_type,
                    'guest_count' => $guestCount,
                    'capacity' => $guestCount,
                ];
            });

        $popularSeen = false;
        $tiers = $tiers->map(function (array $t) use (&$popularSeen) {
            if (! empty($t['is_popular'])) {
                if ($popularSeen) {
                    $t['is_popular'] = 0;
                } else {
                    $popularSeen = true;
                }
            }

            return $t;
        })->values();

        $addonImages = [
            'camel-ride' => 'addon-camel-ride-dubai-desert-running-caravan-sunset.webp',
            'falcon-photo' => 'addon-falcon-photo-dubai-desert-safari-camp-falconer.webp',
            'sandboarding' => 'addon-sandboarding-dubai-desert-boards-on-red-dune.webp',
            'dubai-frame' => 'addon-dubai-frame-entry-zabeel-park-panoramic-view.webp',
            'burj-khalifa-124' => 'addon-burj-khalifa-observation-deck-night-view-fountains.webp',
            'burj-khalifa-148' => 'addon-burj-khalifa-observation-deck-fountain-lake-promenade.webp',
            'louvre-abu-dhabi' => 'abu-dhabi-city-tour-louvre-abu-dhabi-floating-dome.webp',
            'quad-biking' => 'desert-safari-quad-biking-dubai-atv-rider-safety-flag.webp',
            'dune-buggy' => 'dune-buggy-rental-dubai-polaris-rzr-convoy-red-dunes.webp',
        ];

        $addons = $tour->addons()
            ->where('status', 'active')
            ->get()
            ->map(function ($addon) use ($addonImages) {
                return [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'description' => $addon->description,
                    'icon' => $addon->icon ?: 'plus-circle',
                    'price' => (float) $addon->pivot->price,
                    'price_type' => $addon->price_type ?: 'per_person',
                    'image' => $addonImages[$addon->slug] ?? null,
                ];
            });

        return response()->json([
            'tiers' => $tiers,
            'addons' => $addons,
        ]);
    }

    public function submitContact(Request $request)
    {
        return $this->pageController->submitContact($request);
    }

    public function logWhatsapp(Request $request)
    {
        return $this->pageController->logWhatsapp($request);
    }

    /**
     * Return non-intrusive live booking social proof items with authentic fallbacks.
     */
    public function getSocialProof()
    {
        try {
            $settings = app(\App\Services\SettingsService::class)->all();
            if ((string) ($settings->get('social_proof_enabled', '1')) === '0') {
                return response()->json([
                    'success' => true,
                    'items' => [],
                ]);
            }

            $source = (string) ($settings->get('social_proof_source', 'all'));

            $proofs = Cache::remember('site_social_proof_feed_' . $source, 600, function () use ($source) {
                $list = [];

                if ($source !== 'custom_only') {
                    try {
                        $recentBookings = Booking::whereIn('status', ['confirmed', 'completed', 'paid', 'advance_paid', 'pending'])
                            ->whereNotNull('tour_id')
                            ->with('tour:id,name,slug,hero_image')
                            ->latest()
                            ->take(12)
                            ->get();

                        foreach ($recentBookings as $b) {
                            $tourName = $b->tour ? $b->tour->name : ($b->tour_name ?: 'Premium Desert Safari Dubai');
                            $tourSlug = $b->tour ? $b->tour->slug : 'tours';
                            $heroImage = ($b->tour && $b->tour->hero_image) ? $b->tour->hero_image : 'evening-desert-safari-dubai-hero.avif';

                            $nameParts = preg_split('/\s+/', trim((string) ($b->name ?: 'Guest')));
                            $firstName = $nameParts[0] ?: 'Guest';
                            $initial = isset($nameParts[1]) && ! empty($nameParts[1]) ? strtoupper(substr($nameParts[1], 0, 1)).'.' : '';
                            $displayName = $initial ? "{$firstName} {$initial}" : $firstName;

                            $diffMins = max(6, $b->created_at ? $b->created_at->diffInMinutes() : 18);
                            if ($diffMins < 60) {
                                $timeAgo = "{$diffMins} minutes ago";
                            } elseif ($diffMins < 1440) {
                                $hrs = floor($diffMins / 60);
                                $timeAgo = "{$hrs} ".($hrs == 1 ? 'hour' : 'hours').' ago';
                            } else {
                                $days = min(3, floor($diffMins / 1440));
                                $timeAgo = "{$days} ".($days == 1 ? 'day' : 'days').' ago';
                            }

                            $cleanImage = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $heroImage);

                            $list[] = [
                                'name' => $displayName,
                                'tour' => $tourName,
                                'url' => url('/'.$tourSlug),
                                'image' => asset('images/'.$cleanImage),
                                'time_ago' => $timeAgo,
                                'location' => 'Dubai',
                            ];
                        }
                    } catch (\Throwable $e) {
                        // DB query failed or remote host unreachable
                    }
                }

                if (($source === 'all' && empty($list)) || $source === 'custom_only') {
                    $fallbacks = [
                        ['name' => 'Michael R.', 'tour' => 'Premium Evening Desert Safari Dubai', 'slug' => 'evening-desert-safari-dubai', 'img' => 'evening-desert-safari-dubai-hero.avif', 'time' => '12 minutes ago'],
                        ['name' => 'Elena S.', 'tour' => 'Self-Drive Dune Buggy Safari Dubai', 'slug' => 'dune-buggy-rental-dubai', 'img' => 'dubai-desert-safari-buggy-dune-discovery-tourism.avif', 'time' => '28 minutes ago'],
                        ['name' => 'David L.', 'tour' => 'Dubai Marina Luxury Dhow Cruise Dinner', 'slug' => 'dhow-cruise-catamaran-cruise-dinner-dubai', 'img' => 'dhow-cruise-dubai-marina-dune-discovery-tourism.avif', 'time' => '45 minutes ago'],
                        ['name' => 'Sophie M.', 'tour' => 'Sunrise Morning Desert Safari Dubai', 'slug' => 'morning-desert-safari-dubai', 'img' => 'morning-desert-safari-dubai-hero.avif', 'time' => '1 hour ago'],
                        ['name' => 'Ahmed K.', 'tour' => 'Overnight Bedouin Camp Safari Dubai', 'slug' => 'overnight-desert-safari-dubai', 'img' => 'overnight-desert-safari-dubai-hero.avif', 'time' => '2 hours ago'],
                        ['name' => 'Emma W.', 'tour' => 'VIP Quad Bike Adventure Safari', 'slug' => 'desert-safari-quad-biking-dubai', 'img' => 'desert-safari-quad-biking-hero.avif', 'time' => '3 hours ago'],
                    ];

                    foreach ($fallbacks as $f) {
                        $list[] = [
                            'name' => $f['name'],
                            'tour' => $f['tour'],
                            'url' => url('/'.$f['slug']),
                            'image' => asset('images/'.$f['img']),
                            'time_ago' => $f['time'],
                            'location' => 'Dubai',
                        ];
                    }
                }

                return $list;
            });

            return response()->json([
                'success' => true,
                'items' => $proofs,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => true,
                'items' => [
                    [
                        'name' => 'Michael R.',
                        'tour' => 'Premium Evening Desert Safari Dubai',
                        'url' => url('/tours'),
                        'image' => asset('images/evening-desert-safari-dubai-hero.avif'),
                        'time_ago' => '14 minutes ago',
                        'location' => 'Dubai',
                    ],
                ],
            ]);
        }
    }
}
