<?php

namespace App\Http\Controllers;

use App\Mail\BookingAdminNotification;
use App\Mail\BookingNotification;
use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\BookingPayment;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Subscriber;
use App\Models\SubscriberGroup;
use App\Models\Tier;
use App\Models\Tour;
use App\Models\VerifiedEmail;
use App\Services\MetaCapiService;
use App\Services\SettingsService;
use App\Services\VisitorTrackerService;
use App\Services\ZiinaPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    protected $ziina;

    protected $tracker;

    protected $metaCapi;

    public function __construct(ZiinaPaymentService $ziina, VisitorTrackerService $tracker, MetaCapiService $metaCapi)
    {
        $this->ziina = $ziina;
        $this->tracker = $tracker;
        $this->metaCapi = $metaCapi;
    }

    /**
     * Submit Booking Checkout.
     */
    public function checkout(Request $request): JsonResponse
    {
        $minValidDate = min(now('Asia/Dubai')->toDateString(), now('UTC')->toDateString());
        $isCustomBuilder = $request->boolean('is_custom_builder') || $request->input('is_custom_builder') == '1';
        try {
            $request->validate([
                'tour_id' => 'required|integer',
                'tier_id' => $isCustomBuilder ? 'nullable|integer' : 'required|integer',
                'date' => 'required|date|after_or_equal:'.$minValidDate,
                'adults' => 'required|integer|min:1',
                'children' => 'nullable|integer|min:0',
                'infants' => 'nullable|integer|min:0',
                'location' => 'required|string|max:500',
                'requests' => 'nullable|string',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:50',
                'payment_method' => 'required|string|in:cash,advance,full',
                'addons' => 'nullable|array',
                'addons.*' => 'integer',
            ]);
        } catch (ValidationException $ve) {
            $firstError = collect($ve->errors())->flatten()->first();

            return response()->json([
                'success' => false,
                'message' => $firstError ?: 'Please complete all required fields.',
                'errors' => $ve->errors(),
            ], 422);
        }

        $tourId = (int) $request->input('tour_id');
        $tierId = $request->filled('tier_id') ? (int) $request->input('tier_id') : 0;
        $date = $request->input('date');
        $adults = (int) $request->input('adults');
        $children = (int) $request->input('children', 0);
        $infants = max(0, (int) $request->input('infants', 0));
        $location = trim($request->input('location'));
        $requests = trim($request->input('requests', ''));
        $name = trim($request->input('name'));
        $email = trim(strtolower($request->input('email')));
        $phone = trim($request->input('phone'));
        $paymentMethod = $request->input('payment_method');

        $tour = Tour::find($tourId);
        $tier = $tierId > 0 ? Tier::find($tierId) : null;
        if (! $tier && $isCustomBuilder && $tour) {
            $tier = $tour->tiers()->first() ?: Tier::first();
            $tierId = $tier ? (int) $tier->id : 0;
        }

        if (! $tour || ! $tier) {
            return response()->json(['success' => false, 'message' => 'Invalid tour or package selection'], 400);
        }

        $customQuoteData = null;
        if ($isCustomBuilder) {
            $rawQuote = $request->input('custom_quote_data');
            if (is_string($rawQuote)) {
                $customQuoteData = json_decode($rawQuote, true);
            } elseif (is_array($rawQuote)) {
                $customQuoteData = $rawQuote;
            }
        }

        if ($isCustomBuilder && is_array($customQuoteData)) {
            $baseName = (string) ($customQuoteData['base_name'] ?? '');
            $isOvernightCustom = ($tourId === 3 || stripos($baseName, 'Overnight') !== false);
            if ($isOvernightCustom && ($adults < 2 || $adults % 2 !== 0)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Overnight safari requires at least 2 adult guests (1 private tent) and bookings must be in multiples of 2 (2, 4, 6... guests).',
                ], 422);
            }

            // Strictly server-authoritative base safari pricing (never trust client-supplied base_price)
            $basePrice = 79.0;
            if (stripos($baseName, 'VIP') !== false) {
                $basePrice = 130.0;
            } elseif (stripos($baseName, 'Morning') !== false || $tourId === 2) {
                $basePrice = 140.0;
            } elseif ($isOvernightCustom) {
                $basePrice = 399.0;
            }

            $childMultiplier = 0.70;
            $baseSubtotal = ($basePrice * $adults) + round($basePrice * $childMultiplier * $children, 2);

            // Strictly server-authoritative transfer vehicle pricing
            $transferPrice = 0.0;
            $transferName = (string) ($customQuoteData['transfer_name'] ?? '');
            if (stripos($transferName, 'Range Rover') !== false || stripos($transferName, 'VIP Luxury') !== false) {
                $transferPrice = 750.0;
            } elseif (stripos($transferName, 'Private') !== false || stripos($transferName, '7-Seater') !== false) {
                $transferPrice = 350.0;
            }

            // Strictly server-authoritative motorsports pricing
            $sportsPrice = 0.0;
            $sportsName = (string) ($customQuoteData['sports_name'] ?? '');
            if (stripos($sportsName, '30-Min') !== false || stripos($sportsName, '250cc') !== false) {
                $sportsPrice = 120.0 * $adults;
            } elseif (stripos($sportsName, '60-Min') !== false || stripos($sportsName, '400cc') !== false) {
                $sportsPrice = 220.0 * $adults;
            } elseif (stripos($sportsName, 'Buggy') !== false || stripos($sportsName, '1000cc') !== false) {
                $sportsPrice = 550.0 * max(1, (int) ceil($adults / 2));
            }

            // Strictly server-authoritative customizer add-ons pricing
            $customAddonCatalog = [
                'vip_table' => ['price' => 60.0, 'type' => 'per_person'],
                'shisha' => ['price' => 50.0, 'type' => 'flat'],
                'falcon' => ['price' => 40.0, 'type' => 'per_person'],
                'cake' => ['price' => 80.0, 'type' => 'flat'],
            ];

            $customAddonsTotal = 0.0;
            $totalGuests = max(1, $adults + $children);
            if (! empty($customQuoteData['addons']) && is_array($customQuoteData['addons'])) {
                $seenAddonKeys = [];
                foreach ($customQuoteData['addons'] as $ca) {
                    if (! is_array($ca)) {
                        continue;
                    }
                    $caId = strtolower(trim((string) ($ca['id'] ?? '')));
                    $caName = trim((string) ($ca['name'] ?? ''));
                    $matchedSpec = null;

                    if ($caId !== '' && isset($customAddonCatalog[$caId])) {
                        $matchedSpec = $customAddonCatalog[$caId];
                        $dedupKey = $caId;
                    } elseif (stripos($caName, 'VIP Majlis') !== false || stripos($caName, 'Butler') !== false) {
                        $matchedSpec = $customAddonCatalog['vip_table'];
                        $dedupKey = 'vip_table';
                    } elseif (stripos($caName, 'Shisha') !== false) {
                        $matchedSpec = $customAddonCatalog['shisha'];
                        $dedupKey = 'shisha';
                    } elseif (stripos($caName, 'Falcon') !== false) {
                        $matchedSpec = $customAddonCatalog['falcon'];
                        $dedupKey = 'falcon';
                    } elseif (stripos($caName, 'Cake') !== false || stripos($caName, 'Celebration') !== false) {
                        $matchedSpec = $customAddonCatalog['cake'];
                        $dedupKey = 'cake';
                    } else {
                        continue;
                    }

                    if (isset($seenAddonKeys[$dedupKey])) {
                        continue;
                    }
                    $seenAddonKeys[$dedupKey] = true;

                    $customAddonsTotal += ($matchedSpec['type'] === 'flat')
                        ? $matchedSpec['price']
                        : ($matchedSpec['price'] * $totalGuests);
                }
            }

            $subtotal = round($baseSubtotal + $transferPrice + $sportsPrice + $customAddonsTotal, 2);
            $addonsTotal = 0;
            $selectedAddons = [];

            if (! empty($customQuoteData['spec_text']) && ! str_contains($requests, '[CUSTOM SAFARI BUILDER SPEC]')) {
                $requests = trim(strip_tags((string) $customQuoteData['spec_text'])."\n\n".$requests);
            }
        } else {
            if ($tourId === 3 || str_contains($tour->slug, 'overnight')) {
                if ($adults < 2 || $adults % 2 !== 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Overnight safari requires at least 2 adult guests (1 private tent) and bookings must be in multiples of 2 (2, 4, 6... guests).',
                    ], 422);
                }
            }

            $pricing = DB::table('tour_tiers')
                ->where('tour_id', $tourId)
                ->where('tier_id', $tierId)
                ->first();

            if (! $pricing) {
                return response()->json(['success' => false, 'message' => 'Pricing configuration not found for selected package'], 400);
            }

            $price = (float) $pricing->price;
            $priceType = Tier::normalizePriceType($pricing->price_type ?? 'per person', $tour);

            $childPrice = (isset($pricing->child_price) && (float) $pricing->child_price > 0)
                ? (float) $pricing->child_price
                : round($price * 0.70, 2);

            if (Tier::isUnitPriceType($priceType, $tour)) {
                $capacity = Tier::resolveUnitCapacity($pricing, $tier, $priceType);
                $vehicles = (int) ceil(($adults + $children) / max(1, $capacity));
                $subtotal = $vehicles * $price;
            } else {
                $subtotal = ($price * $adults) + ($childPrice * $children);
            }

            // Addons total (respecting per_person vs per_group price_type)
            $addonsTotal = 0;
            $selectedAddons = [];
            $addonsInput = array_values(array_unique(array_map('intval', (array) $request->input('addons', []))));
            $payingGuests = max(1, $adults + $children);

            if (! empty($addonsInput)) {
                $addonsData = DB::table('addons')
                    ->join('tour_addons', 'addons.id', '=', 'tour_addons.addon_id')
                    ->where('tour_addons.tour_id', $tourId)
                    ->where('addons.status', 'active')
                    ->whereIn('addons.id', $addonsInput)
                    ->select('addons.id', 'addons.name', 'addons.price_type', 'tour_addons.price')
                    ->get();

                foreach ($addonsData as $addon) {
                    $addonPriceType = strtolower(trim((string) ($addon->price_type ?? 'per_person')));
                    $addonQty = in_array($addonPriceType, ['per_group', 'flat', 'per_vehicle'], true) ? 1 : $payingGuests;
                    $unitPrice = (float) $addon->price;

                    $selectedAddons[] = [
                        'id' => $addon->id,
                        'name' => $addon->name,
                        'quantity' => $addonQty,
                        'price' => $unitPrice,
                    ];
                    $addonsTotal += round($unitPrice * $addonQty, 2);
                }
            }
        }

        $rawSubtotal = $subtotal + $addonsTotal;
        $originalTotal = $rawSubtotal;
        $discountAmount = 0.00;
        $appliedCoupon = null;

        // Process promo code if provided
        $couponCodeInput = strtoupper(trim($request->input('coupon_code', '')));
        if (! empty($couponCodeInput)) {
            $coupon = Coupon::findByCode($couponCodeInput);
            if ($coupon) {
                $payingGuests = max(1, (int) $adults + (int) $children);
                $check = $coupon->validateEligibility($rawSubtotal, $tourId, $tierId, $email, $payingGuests, $date);
                if ($check['valid']) {
                    $appliedCoupon = $coupon;
                    $discountAmount = (float) $check['discount'];
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => $check['message'] ?: 'Coupon is invalid or cannot be applied to this booking.',
                        'errors' => ['coupon_code' => [$check['message']]],
                    ], 422);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid promo coupon code.',
                    'errors' => ['coupon_code' => ['Invalid promo coupon code.']],
                ], 422);
            }
        }

        $total = max(0, round($originalTotal - $discountAmount, 2));

        // Force cash if Ziina is not active
        if (! $this->ziina->isActive()) {
            $paymentMethod = 'cash';
        }

        $payNow = 0;
        $balanceDue = $total;

        if ($paymentMethod === 'advance') {
            $advancePercent = $this->ziina->getAdvancePercent();
            $payNow = round(($total * $advancePercent) / 100, 2);
            $balanceDue = round($total - $payNow, 2);
        } elseif ($paymentMethod === 'full') {
            $payNow = round($total, 2);
            $balanceDue = 0;
        }

        // Email Verification check (session or previously verified email in DB)
        $sessionVerified = VerifiedEmail::isVerified($email);
        $isVerified = $sessionVerified ? 1 : 0;
        $requireVerification = app(SettingsService::class)->get('email_verification_required', '1') === '1';
        if ($requireVerification && ! $sessionVerified) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email address before completing your booking.',
                'require_verification' => true,
            ], 422);
        }

        if ($paymentMethod !== 'cash') {
            if ($payNow < 0.50) {
                return response()->json(['success' => false, 'message' => 'Minimum online payment amount is AED 0.50'], 400);
            }
        }

        // Generate Unique Booking Reference DDT + ymd + 5 random chars
        $ref = 'DDT'.date('ymd').strtoupper(Str::random(5));

        // Request context logging
        $gpsPost = [
            'gps_consent' => $request->input('gps_consent'),
            'gps_lat' => $request->input('gps_lat'),
            'gps_lng' => $request->input('gps_lng'),
            'gps_accuracy' => $request->input('gps_accuracy'),
            'gps_timestamp' => $request->input('gps_timestamp'),
            'gps_source' => $request->input('gps_source'),
            'gps_altitude' => $request->input('gps_altitude'),
            'gps_heading' => $request->input('gps_heading'),
            'gps_speed' => $request->input('gps_speed'),
        ];
        $ctx = $this->tracker->collectRequestContext('booking', $gpsPost);

        try {
            // Save Booking atomically inside transaction
            $booking = DB::transaction(function () use (
                $ref, $tourId, $tierId, $tour, $tier, $date, $adults, $children, $infants,
                $name, $email, $phone, $location, $requests, $appliedCoupon, $discountAmount,
                $originalTotal, $subtotal, $addonsTotal, $total, $paymentMethod, $ctx,
                $isVerified, $selectedAddons, $isCustomBuilder, $customQuoteData
            ) {
                $lockedCoupon = null;
                if ($appliedCoupon && $discountAmount > 0) {
                    if (! $appliedCoupon->exists) {
                        $appliedCoupon = Coupon::firstOrCreate(
                            ['code' => $appliedCoupon->code],
                            $appliedCoupon->getAttributes()
                        );
                    }
                    $lockedCoupon = Coupon::where('id', $appliedCoupon->id)->lockForUpdate()->first();
                    if (! $lockedCoupon || ($lockedCoupon->usage_limit !== null && $lockedCoupon->used_count >= $lockedCoupon->usage_limit)) {
                        throw ValidationException::withMessages([
                            'coupon_code' => ['This promo code has reached its maximum redemption limit.'],
                        ]);
                    }
                    if ($lockedCoupon->usage_limit_per_user > 0) {
                        $usedByUser = CouponUsage::where('coupon_id', $lockedCoupon->id)
                            ->where('customer_email', $email)
                            ->count();
                        if ($usedByUser >= $lockedCoupon->usage_limit_per_user) {
                            throw ValidationException::withMessages([
                                'coupon_code' => ["You have already reached the maximum usage limit ({$lockedCoupon->usage_limit_per_user}) for this promo code."],
                            ]);
                        }
                    }
                }

                $b = Booking::create([
                    'reference' => $ref,
                    'tour_id' => $tourId,
                    'tier_id' => $tierId,
                    'tour_name' => $tour->name,
                    'tier_name' => ($isCustomBuilder && $customQuoteData)
                        ? ('Custom Safari Configurator: '.($customQuoteData['base_name'] ?? 'Tailored Package'))
                        : ($tier->display_name ?: $tier->name),
                    'tour_date' => $date,
                    'adults' => $adults,
                    'children' => $children,
                    'infants' => $infants,
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'pickup_location' => $location,
                    'special_requests' => $requests,
                    'coupon_id' => $lockedCoupon ? $lockedCoupon->id : null,
                    'coupon_code' => $lockedCoupon ? $lockedCoupon->code : null,
                    'discount_type' => $lockedCoupon ? $lockedCoupon->discount_type : null,
                    'discount_rate' => $lockedCoupon ? (float) $lockedCoupon->discount_value : 0.00,
                    'discount_amount' => $discountAmount,
                    'original_total' => $originalTotal,
                    'subtotal' => $subtotal,
                    'addons_total' => $addonsTotal,
                    'total' => $total,
                    'currency' => 'AED',
                    'status' => 'pending',
                    'payment_method' => $paymentMethod,
                    'payment_status' => 'unpaid',
                    'payment_amount' => 0.00,
                    'balance_due' => $total,
                    'ip_address' => $ctx['client_ip'],
                    'ip_location' => implode(', ', array_filter([$ctx['city'] ?? '', $ctx['region'] ?? '', $ctx['country'] ?? ''])),
                    'gps_lat' => $ctx['gps_latitude'],
                    'gps_lng' => $ctx['gps_longitude'],
                    'gps_address' => $ctx['gps_source'] ?? 'Not Available',
                    'device_type' => $ctx['device_type'],
                    'browser' => $ctx['browser_name'],
                    'platform' => $ctx['os_name'],
                    'user_agent' => $ctx['user_agent'],
                    'referrer' => $ctx['referrer_url'],
                    'utm_source' => $ctx['utm_source'],
                    'utm_medium' => $ctx['utm_medium'],
                    'utm_campaign' => $ctx['utm_campaign'],
                    'utm_term' => $ctx['utm_term'],
                    'utm_content' => $ctx['utm_content'],
                    'is_verified' => $isVerified,
                ]);

                // Record Coupon Usage immediately for cash bookings (instant confirmation)
                if ($lockedCoupon && $discountAmount > 0 && $paymentMethod === 'cash') {
                    $this->recordCouponUsage($b);
                }

                // Save Booking Addons
                foreach ($selectedAddons as $sa) {
                    BookingAddon::create([
                        'booking_id' => $b->id,
                        'addon_id' => $sa['id'],
                        'addon_name' => $sa['name'],
                        'quantity' => $sa['quantity'] ?? 1,
                        'price' => $sa['price'],
                    ]);
                }

                return $b;
            });

            // Permanently record the customer's email as verified in the database for future bookings
            VerifiedEmail::markVerified($email);

            // Log detailed context in request_logs table
            $logId = $this->tracker->logRequest('booking', $booking->id, 'booking', $ctx);
            if ($logId) {
                $booking->update(['request_log_id' => $logId]);
            }

            // Auto-subscribe guest to newsletter if consent given
            if ($request->boolean('subscribe_newsletter') || $request->input('subscribe_newsletter') == '1') {
                try {
                    $parts = preg_split('/\s+/', trim((string) $name), 2);
                    $subFirstName = $parts[0] ?? '';
                    $subLastName = $parts[1] ?? '';

                    $subscriber = Subscriber::firstOrNew(['email' => strtolower(trim((string) $email))]);
                    if (! $subscriber->exists) {
                        $subscriber->first_name = $subFirstName;
                        $subscriber->last_name = $subLastName;
                        $subscriber->phone = $phone;
                        $subscriber->source = 'checkout';
                        $subscriber->status = 'subscribed';
                        $subscriber->save();
                    } else {
                        if (empty($subscriber->first_name)) {
                            $subscriber->first_name = $subFirstName;
                        }
                        if (empty($subscriber->last_name)) {
                            $subscriber->last_name = $subLastName;
                        }
                        if (empty($subscriber->phone)) {
                            $subscriber->phone = $phone;
                        }
                        $subscriber->save();
                    }

                    // Attach to Booked Guests group
                    $bookedGroup = SubscriberGroup::where('slug', 'booked-guests')->first();
                    if ($bookedGroup && ! $subscriber->groups()->where('subscriber_groups.id', $bookedGroup->id)->exists()) {
                        $subscriber->groups()->attach($bookedGroup->id);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Newsletter auto-subscription on checkout failed: '.$e->getMessage());
                }
            }

            // Cash checkout is complete instantly
            if ($paymentMethod === 'cash') {
                $this->sendEmailNotification('booking_cash', $booking);

                // Trigger instant purchase conversion API (since cash completes without payment step)
                $custom = [
                    'value' => (float) $booking->total,
                    'currency' => 'AED',
                    'content_ids' => ['TOUR-'.$booking->tour_id],
                    'content_type' => 'product',
                    'contents' => [['id' => 'TOUR-'.$booking->tour_id, 'quantity' => 1]],
                    'coupon' => $booking->coupon_code,
                    'discount_amount' => (float) $booking->discount_amount,
                ];
                $this->metaCapi->dispatchEvent('Purchase', [
                    'event_id' => 'BOOK-'.$booking->reference,
                    'email' => $booking->email,
                    'phone' => $booking->phone,
                    'custom_data' => $custom,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Thank you! Your booking has been received.',
                    'reference' => $booking->reference,
                    'redirect_url' => localized_route('booking.thankyou', ['ref' => $booking->reference]),
                ]);
            }

            // Generate Ziina Payment Link
            $successUrl = localized_route('booking.thankyou', ['ref' => $booking->reference]);
            $cancelUrl = localized_route('booking.cancel', ['ref' => $booking->reference]);
            $messageStr = 'Booking '.$booking->reference;

            session([
                'active_booking_draft_id' => $booking->id,
                'active_booking_reference' => $booking->reference,
                'last_booking_reference' => $booking->reference,
            ]);

            $intent = $this->ziina->createPaymentIntent($payNow, 'AED', $successUrl, $cancelUrl, $messageStr);

            if (isset($intent['error'])) {
                $booking->update(['payment_status' => 'failed']);

                return response()->json(['success' => false, 'message' => $intent['error']], 400);
            }

            if (empty($intent['redirect_url'])) {
                $booking->update(['payment_status' => 'failed']);

                return response()->json(['success' => false, 'message' => 'Payment link could not be generated.'], 400);
            }

            $booking->update([
                'ziina_payment_intent_id' => $intent['id'] ?? null,
                'ziina_status' => $intent['status'] ?? null,
                'ziina_redirect_url' => $intent['redirect_url'] ?? null,
            ]);

            BookingPayment::create([
                'booking_id' => $booking->id,
                'payment_intent_id' => $intent['id'],
                'amount' => $payNow,
                'currency' => 'AED',
                'status' => $intent['status'] ?? 'pending',
                'payment_url' => $intent['redirect_url'],
            ]);

            // Dispatch InitiateCheckout event to Conversions API
            $custom = [
                'value' => (float) $booking->total,
                'currency' => 'AED',
                'content_ids' => ['TOUR-'.$booking->tour_id],
                'content_type' => 'product',
                'contents' => [['id' => 'TOUR-'.$booking->tour_id, 'quantity' => 1]],
            ];
            $this->metaCapi->dispatchEvent('InitiateCheckout', [
                'event_id' => 'INIT-'.$booking->reference,
                'email' => $booking->email,
                'phone' => $booking->phone,
                'custom_data' => $custom,
            ]);

            return response()->json([
                'success' => true,
                'redirect_url' => $intent['redirect_url'],
            ]);

        } catch (ValidationException $ve) {
            $firstError = collect($ve->errors())->flatten()->first();

            return response()->json([
                'success' => false,
                'message' => $firstError ?: 'Validation error occurred.',
                'errors' => $ve->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Failed to process checkout: '.$e->getMessage()."\n".$e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Unable to complete booking at this time. Please try again or contact us directly on WhatsApp.',
            ], 500);
        }
    }

    /**
     * Thank You redirect landing page.
     */
    public function thankyou(Request $request)
    {
        $rawPi = trim((string) $request->input('pi', ''));
        $pi = $rawPi;
        $ref = trim((string) $request->input('ref', ''));
        $hadPlaceholderPi = ($rawPi === '{PAYMENT_INTENT_ID}' || $rawPi === '%7BPAYMENT_INTENT_ID%7D');

        // Sanitize literal template placeholder if passed by gateway
        if ($hadPlaceholderPi) {
            $pi = '';
        }

        // Session fallbacks for booking reference
        if (empty($ref)) {
            $ref = (string) session('active_booking_reference', '');
            if (empty($ref)) {
                $ref = (string) session('last_booking_reference', '');
            }
        }

        if (empty($ref) && session()->has('active_booking_draft_id')) {
            $draftBooking = Booking::find(session('active_booking_draft_id'));
            if ($draftBooking) {
                $ref = $draftBooking->reference;
            }
        }

        // Strictly bounded IP fallback ONLY when returning from gateway with literal placeholder ?pi={PAYMENT_INTENT_ID}
        if (empty($ref) && empty($pi) && $hadPlaceholderPi) {
            $recentBooking = Booking::whereNotNull('ziina_payment_intent_id')
                ->where('ip_address', $request->ip())
                ->where('status', 'pending')
                ->where('payment_status', 'unpaid')
                ->where('created_at', '>=', now()->subHours(3))
                ->latest()
                ->first();

            if ($recentBooking) {
                $ref = $recentBooking->reference;
                $pi = (string) $recentBooking->ziina_payment_intent_id;
            }
        }

        $booking = null;
        $paymentRecord = null;
        $paymentStatus = 'pending';
        $method = 'cash';

        // 1. Locate Booking by reference
        if (! empty($ref)) {
            $booking = Booking::with(['addons'])->where('reference', $ref)->first();
            if ($booking) {
                $method = $booking->payment_method;

                // Guard against cross-booking payment intent replay (?ref=VICTIM_REF&pi=OTHER_COMPLETED_PI)
                if (! empty($pi)) {
                    $belongsToBooking = ($pi === (string) $booking->ziina_payment_intent_id)
                        || BookingPayment::where('booking_id', $booking->id)->where('payment_intent_id', $pi)->exists();
                    if (! $belongsToBooking) {
                        $pi = '';
                    }
                }

                if (empty($pi)) {
                    $latestPendingPayment = BookingPayment::where('booking_id', $booking->id)
                        ->where('status', '!=', 'completed')
                        ->latest()
                        ->first();
                    if ($latestPendingPayment && ! empty($latestPendingPayment->payment_intent_id)) {
                        $pi = $latestPendingPayment->payment_intent_id;
                        $paymentRecord = $latestPendingPayment;
                    } elseif (! empty($booking->ziina_payment_intent_id)) {
                        $pi = $booking->ziina_payment_intent_id;
                    }
                }
            }
        }

        // 2. Locate Booking or Payment Record by Payment Intent or Reference
        if (! $booking && (! empty($pi) || ! empty($ref))) {
            $paymentRecord = null;
            if (! empty($pi)) {
                $paymentRecord = BookingPayment::where('payment_intent_id', $pi)->first();
            }
            if (! $paymentRecord && ! empty($ref)) {
                $paymentRecord = BookingPayment::where('notes', 'like', "{$ref}%")
                    ->orWhere('payment_intent_id', $ref)
                    ->first();
            }

            if ($paymentRecord) {
                if ($paymentRecord->booking_id) {
                    $booking = Booking::with(['addons'])->find($paymentRecord->booking_id);
                }
                if (empty($pi) && ! empty($paymentRecord->payment_intent_id)) {
                    $pi = $paymentRecord->payment_intent_id;
                }
            }

            if (! $booking && ! empty($pi)) {
                $booking = Booking::with(['addons'])->where('ziina_payment_intent_id', $pi)->first();
            }

            if ($booking) {
                $method = $booking->payment_method;
            }
        }

        // If booking was found, locate matching payment record
        if ($booking && ! $paymentRecord && ! empty($pi)) {
            $paymentRecord = BookingPayment::where('booking_id', $booking->id)
                ->where('payment_intent_id', $pi)
                ->first();
        }
        if ($booking && ! $paymentRecord) {
            $paymentRecord = BookingPayment::where('booking_id', $booking->id)
                ->latest()
                ->first();
        }

        // 3. Process & verify payment with Ziina if payment intent exists
        $intentId = ! empty($pi) ? $pi : ($booking?->ziina_payment_intent_id);

        if (! empty($intentId)) {
            $intent = $this->ziina->fetchPaymentIntent($intentId);
            $intentStatus = $intent['status'] ?? '';

            if ($intentStatus === 'completed') {
                $shouldNotify = false;
                $newStatus = 'paid';

                if ($booking) {
                    $method = $booking->payment_method;

                    DB::transaction(function () use (
                        $booking, $paymentRecord, $intent, $intentStatus, $method, &$shouldNotify, &$newStatus, $intentId
                    ) {
                        $lockedBooking = Booking::where('id', $booking->id)->lockForUpdate()->first();
                        $lockedPayment = $paymentRecord
                            ? BookingPayment::where('id', $paymentRecord->id)->lockForUpdate()->first()
                            : BookingPayment::where('booking_id', $lockedBooking->id)->where('payment_intent_id', $intentId)->lockForUpdate()->first();

                        $wasCompleted = ($lockedPayment && $lockedPayment->status === 'completed')
                            || (! $lockedPayment && $lockedBooking && $lockedBooking->status === 'confirmed' && $lockedBooking->payment_status === 'paid');

                        if ($lockedPayment && $lockedPayment->status !== 'completed') {
                            $lockedPayment->update(['status' => 'completed']);
                        }

                        $totalPaid = BookingPayment::where('booking_id', $lockedBooking->id)
                            ->where('status', 'completed')
                            ->sum('amount');

                        if ($totalPaid <= 0 && isset($intent['amount'])) {
                            $totalPaid = (float) ($intent['amount'] / 100);
                        }

                        $remBalance = max(0, (float) $lockedBooking->total - (float) $totalPaid);
                        $newStatus = ($remBalance <= 0) ? 'paid' : (($method === 'advance' || $totalPaid > 0) ? 'partial' : 'paid');

                        if ($lockedBooking->payment_status !== $newStatus || $lockedBooking->balance_due != $remBalance || $lockedBooking->status !== 'confirmed') {
                            $lockedBooking->update([
                                'payment_status' => $newStatus,
                                'ziina_status' => $intentStatus,
                                'status' => 'confirmed',
                                'balance_due' => $remBalance,
                                'payment_amount' => $totalPaid,
                            ]);
                        }

                        // Send booking confirmation email and record coupon on first completion only
                        if (! $wasCompleted) {
                            $this->recordCouponUsage($lockedBooking);
                            $shouldNotify = true;
                        }
                    });

                    // Reload booking model with fresh attributes after transaction
                    $booking->refresh();

                    if ($shouldNotify) {
                        if ($newStatus === 'partial') {
                            $this->sendEmailNotification('booking_advance', $booking);
                        } else {
                            $this->sendEmailNotification('booking_full', $booking);
                        }

                        // Dispatch Purchase Event to Meta Conversions API
                        $custom = [
                            'value' => (float) $booking->total,
                            'currency' => 'AED',
                            'content_ids' => ['TOUR-'.$booking->tour_id],
                            'content_type' => 'product',
                            'contents' => [['id' => 'TOUR-'.$booking->tour_id, 'quantity' => 1]],
                        ];
                        $this->metaCapi->dispatchEvent('Purchase', [
                            'event_id' => 'BOOK-'.$booking->reference,
                            'email' => $booking->email,
                            'phone' => $booking->phone,
                            'custom_data' => $custom,
                        ]);
                    }

                    $paymentStatus = 'completed';
                } elseif ($paymentRecord) {
                    $paymentRecord->update(['status' => 'completed']);
                    $paymentStatus = 'completed';
                    $booking = (object) [
                        'id' => null,
                        'reference' => ! empty($ref) ? $ref : ('PAY-'.strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $intentId), -8))),
                        'tour_id' => 0,
                        'tour_name' => 'Direct / Quick Payment',
                        'tier_name' => 'Confirmed Payment',
                        'name' => $paymentRecord->customer_name ?? 'Valued Guest',
                        'email' => $paymentRecord->customer_email ?? null,
                        'phone' => $paymentRecord->customer_phone ?? null,
                        'status' => 'confirmed',
                        'adults' => 1,
                        'children' => 0,
                        'infants' => 0,
                        'tour_date' => null,
                        'total' => (float) $paymentRecord->amount,
                        'payment_amount' => (float) $paymentRecord->amount,
                        'balance_due' => 0.00,
                        'subtotal' => (float) $paymentRecord->amount,
                        'addons_total' => 0.00,
                        'coupon_code' => null,
                        'discount_amount' => 0.00,
                        'original_total' => (float) $paymentRecord->amount,
                        'payment_method' => 'card',
                        'payment_status' => 'completed',
                    ];
                    $method = 'card';
                }
            } elseif ($intentStatus === 'failed') {
                $alreadyCompleted = ($paymentRecord && $paymentRecord->status === 'completed') ||
                                    ($booking && in_array($booking->payment_status, ['paid', 'partial']) && $booking->status === 'confirmed');
                if (! $alreadyCompleted && $booking) {
                    $booking->update([
                        'payment_status' => 'failed',
                        'ziina_status' => $intentStatus,
                    ]);

                    if ($paymentRecord) {
                        $paymentRecord->update(['status' => $intentStatus]);
                    }
                }
                $paymentStatus = $alreadyCompleted ? 'completed' : 'failed';
            } else {
                $alreadyCompleted = ($paymentRecord && $paymentRecord->status === 'completed') ||
                                    ($booking && in_array($booking->payment_status, ['paid', 'partial']) && $booking->status === 'confirmed');
                if ($alreadyCompleted) {
                    $paymentStatus = 'completed';
                } else {
                    if (! empty($intentStatus) && $booking) {
                        $booking->update([
                            'ziina_status' => $intentStatus,
                        ]);

                        if ($paymentRecord) {
                            $paymentRecord->update(['status' => $intentStatus]);
                        }
                    }
                    $paymentStatus = 'pending';
                }
            }
        } elseif ($booking) {
            $method = $booking->payment_method ?: 'cash';
            if ($method === 'cash' || in_array($booking->payment_status, ['paid', 'partial'])) {
                $paymentStatus = 'completed';
            }
        }

        // Clean redirect if landing on placeholder URL or missing reference in query string
        if ($booking && is_object($booking) && isset($booking->reference) && (! $request->filled('ref') || $request->input('pi') === '{PAYMENT_INTENT_ID}' || $request->input('pi') === '%7BPAYMENT_INTENT_ID%7D')) {
            return redirect()->to(localized_route('booking.thankyou', ['ref' => $booking->reference]));
        }

        $pageRobots = 'noindex, nofollow';

        return view('thankyou', compact('booking', 'paymentStatus', 'method', 'pageRobots'));
    }

    /**
     * Payment Cancel landing page.
     */
    public function paymentCancel(Request $request)
    {
        $pi = trim((string) $request->input('pi', ''));
        $ref = trim((string) $request->input('ref', ''));

        if ($pi === '{PAYMENT_INTENT_ID}' || $pi === '%7BPAYMENT_INTENT_ID%7D') {
            $pi = '';
        }

        if (empty($ref)) {
            $ref = (string) session('active_booking_reference', '');
        }

        $booking = null;
        if (! empty($ref)) {
            $booking = Booking::where('reference', $ref)->first();
            if ($booking && ! empty($pi)) {
                $belongsToBooking = ($pi === (string) $booking->ziina_payment_intent_id)
                    || BookingPayment::where('booking_id', $booking->id)->where('payment_intent_id', $pi)->exists();
                if (! $belongsToBooking) {
                    $pi = '';
                }
            }
        }
        if (! $booking && ! empty($pi)) {
            $booking = Booking::where('ziina_payment_intent_id', $pi)->first();
        }

        if ($booking && in_array($booking->status, ['pending', 'draft']) && ! in_array($booking->payment_status, ['paid', 'partial', 'completed'])) {
            $isSessionOwner = (string) session('active_booking_reference', '') === (string) $booking->reference
                || (int) session('active_booking_draft_id', 0) === (int) $booking->id;

            $intentId = $pi ?: $booking->ziina_payment_intent_id;
            $allowCancel = false;

            if (! empty($intentId)) {
                $ziinaData = $this->ziina->fetchPaymentIntent($intentId);
                $gatewayStatus = $ziinaData['status'] ?? null;

                if (in_array($gatewayStatus, ['cancelled', 'failed', 'expired'], true)) {
                    $allowCancel = true;
                } elseif ($gatewayStatus !== 'completed' && ($isSessionOwner || ! empty($pi))) {
                    $allowCancel = true;
                }
            } elseif ($isSessionOwner) {
                $allowCancel = true;
            }

            if ($allowCancel) {
                DB::transaction(function () use ($booking, $intentId) {
                    $lockedBooking = Booking::where('id', $booking->id)->lockForUpdate()->first();
                    if (! $lockedBooking || in_array($lockedBooking->status, ['confirmed', 'completed']) || in_array($lockedBooking->payment_status, ['paid', 'partial', 'completed'])) {
                        return;
                    }

                    // Release any coupon reservation if present
                    if ($lockedBooking->coupon_id) {
                        try {
                            $usage = CouponUsage::where('booking_id', $lockedBooking->id)->lockForUpdate()->first();
                            if ($usage) {
                                $coupon = Coupon::where('id', $lockedBooking->coupon_id)->lockForUpdate()->first();
                                if ($coupon && $coupon->used_count > 0) {
                                    $coupon->decrement('used_count');
                                }
                                $usage->delete();
                            }
                        } catch (\Throwable $e) {
                            Log::error('Failed to release coupon on cancellation: '.$e->getMessage());
                        }
                    }

                    $lockedBooking->update([
                        'status' => 'cancelled',
                        'payment_status' => 'cancelled',
                        'ziina_status' => 'cancelled',
                    ]);

                    if (! empty($intentId)) {
                        BookingPayment::where('booking_id', $lockedBooking->id)
                            ->where('payment_intent_id', $intentId)
                            ->where('status', '!=', 'completed')
                            ->update(['status' => 'cancelled']);
                    }
                });
                $booking->refresh();
            }
        }

        if ($booking && (! $request->filled('ref') || $request->input('pi') === '{PAYMENT_INTENT_ID}' || $request->input('pi') === '%7BPAYMENT_INTENT_ID%7D')) {
            return redirect()->to(localized_route('booking.cancel', ['ref' => $booking->reference]));
        }

        $pageRobots = 'noindex, nofollow';

        return view('payment-cancel', compact('booking', 'pageRobots'));
    }

    /**
     * Record coupon usage upon confirmed payment.
     */
    protected function recordCouponUsage(Booking $booking): void
    {
        if (! $booking->coupon_id || (float) $booking->discount_amount <= 0) {
            return;
        }

        try {
            $alreadyRecorded = CouponUsage::where('booking_id', $booking->id)->exists();
            if (! $alreadyRecorded) {
                CouponUsage::create([
                    'coupon_id' => $booking->coupon_id,
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->reference,
                    'customer_name' => $booking->name,
                    'customer_email' => strtolower($booking->email),
                    'customer_phone' => $booking->phone,
                    'discount_amount' => (float) $booking->discount_amount,
                    'order_subtotal' => (float) $booking->original_total,
                    'order_final_total' => (float) $booking->total,
                    'used_at' => now(),
                ]);

                $coupon = Coupon::find($booking->coupon_id);
                if ($coupon) {
                    $coupon->increment('used_count');
                }
            }
        } catch (\Throwable $e) {
            Log::error("Failed to record coupon usage for {$booking->reference}: ".$e->getMessage());
        }
    }

    /**
     * Helper to render HTML and send notifications.
     */
    protected function sendEmailNotification(string $type, Booking $booking): void
    {
        try {
            if (! app()->environment('testing')) {
                app(\App\Services\EmailMarketingService::class)->applySmtpConfig();
            }
            $settings = app(SettingsService::class);
            $fromEmail = $settings->getFromEmail();
            $adminEmail = $settings->getAdminEmail();
            $ccEmails = $settings->getCcEmails();
            $bccEmails = $settings->getBccEmails();

            // Send to customer
            try {
                Mail::to($booking->email)
                    ->send((new BookingNotification($type, $booking))->from($fromEmail, 'Dunes Discovery Tourism'));
            } catch (\Throwable $e) {
                Log::error("Failed to send customer booking email for {$booking->reference}: ".$e->getMessage());
            }

            // Send to admin
            try {
                $adminMail = (new BookingAdminNotification($booking))->from($fromEmail, 'Dunes Discovery Tourism');
                if (! empty($ccEmails)) {
                    $adminMail->cc($ccEmails);
                }
                if (! empty($bccEmails)) {
                    $adminMail->bcc($bccEmails);
                }
                Mail::to($adminEmail)->send($adminMail);
            } catch (\Throwable $e) {
                Log::error("Failed to send admin booking email for {$booking->reference}: ".$e->getMessage());
            }
        } catch (\Throwable $e) {
            Log::error("Failed to prepare booking email for {$booking->reference}: ".$e->getMessage());
        }
    }

    /**
     * Auto-save dropped-off checkout draft for lead recovery.
     */
    public function saveDraft(Request $request)
    {
        $name = trim($request->input('name', ''));
        $email = strtolower(trim($request->input('email', '')));
        $phone = trim($request->input('phone', ''));
        $tourId = $request->input('tour_id');
        $tierId = $request->input('tier_id');
        $date = $request->input('date');

        // Only save if at least phone or email or name is provided
        if (empty($name) && empty($email) && empty($phone)) {
            return response()->json(['success' => false, 'message' => 'Insufficient lead data.']);
        }

        $tour = $tourId ? Tour::find($tourId) : null;
        $tier = $tierId ? Tier::find($tierId) : null;

        $adults = max(1, (int) $request->input('adults', 1));
        $children = max(0, (int) $request->input('children', 0));
        $infants = max(0, (int) $request->input('infants', 0));
        $clientSubtotal = max(0, (float) $request->input('subtotal', 0));
        $clientTotal = max(0, (float) $request->input('total', $clientSubtotal));

        $resolvedPricing = $this->resolveAuthoritativeDraftPricing($tour, $tier, $adults, $children, $clientSubtotal, 0.0, null, $email, $date);
        $subtotal = $resolvedPricing['authoritative'] ? $resolvedPricing['subtotal'] : $clientSubtotal;
        $total = $resolvedPricing['authoritative'] ? $resolvedPricing['total'] : $clientTotal;

        // Check if there is an existing draft for this session or email in the last 4 hours
        $sessionDraftId = session('active_booking_draft_id');
        $draftId = $request->input('draft_id');
        $draft = null;

        // Strictly ensure caller owns the draft they are attempting to update
        if ($draftId && $sessionDraftId && (int) $draftId === (int) $sessionDraftId) {
            $draft = Booking::where('id', $draftId)->where('status', 'draft')->first();
        }

        if (! $draft && ! empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $draft = Booking::where('email', $email)
                ->where('status', 'draft')
                ->where('created_at', '>=', now()->subHours(4))
                ->where(function ($q) use ($request) {
                    $q->where('ip_address', $request->ip())
                        ->orWhereNull('ip_address');
                })
                ->first();
        }

        if (! $draft) {
            $ref = 'DFT'.date('ymd').strtoupper(Str::random(5));
            $draft = new Booking;
            $draft->reference = $ref;
            $draft->status = 'draft';
        }

        $draft->tour_id = $tour ? $tour->id : null;
        $draft->tier_id = $tier ? $tier->id : null;
        $draft->tour_name = $tour ? $tour->name : 'Desert Safari Experience';
        $draft->tier_name = $tier ? ($tier->display_name ?: $tier->name) : 'Standard Package';
        $draft->tour_date = $date ? date('Y-m-d', strtotime($date)) : now()->addDay()->format('Y-m-d');
        $draft->adults = $adults;
        $draft->children = $children;
        $draft->infants = $infants;
        $draft->name = $name ?: 'Prospective Guest';
        $draft->email = $email ?: 'abandoned@guest.local';
        $draft->phone = $phone;
        $draft->pickup_location = $request->input('pickup_location', 'Dubai Hotel');
        $draft->subtotal = $subtotal;
        $draft->total = $total;
        $draft->currency = 'AED';
        $draft->payment_method = $request->input('payment_method', 'cash');
        $draft->payment_status = 'unpaid';
        $draft->ip_address = $request->ip();
        $draft->save();

        session(['active_booking_draft_id' => $draft->id]);

        return response()->json([
            'success' => true,
            'draft_id' => $draft->id,
            'reference' => $draft->reference,
        ]);
    }

    /**
     * Dedicated Abandoned Cart Checkout Recovery Page.
     */
    public function recoverCheckout(Request $request, string $reference)
    {
        $booking = Booking::with(['tour.tiers', 'tier', 'addons', 'coupon'])->where('reference', $reference)->first();

        if (! $booking) {
            return redirect()->route('home')->with('error', 'Booking reservation not found.');
        }

        if (in_array($booking->status, ['confirmed', 'completed']) || in_array($booking->payment_status, ['paid', 'completed'])) {
            return redirect()->to(localized_route('booking.voucher', ['reference' => $booking->reference]))
                ->with('info', "Your reservation #{$booking->reference} is already confirmed!");
        }

        $tour = $booking->tour ?: Tour::find($booking->tour_id);
        $tier = $booking->tier ?: Tier::find($booking->tier_id);

        $addonsTotal = $booking->addons && $booking->addons->count()
            ? round((float) $booking->addons->sum(fn ($a) => (float) $a->price * max(1, (int) ($a->quantity ?? 1))), 2)
            : max(0, (float) $booking->addons_total);

        $fallbackSubtotal = max(0, (float) $booking->subtotal ?: ((float) $booking->total ?: 0));
        $resolved = $this->resolveAuthoritativeDraftPricing(
            $tour,
            $tier,
            max(1, (int) $booking->adults),
            max(0, (int) $booking->children),
            $fallbackSubtotal,
            $addonsTotal,
            $booking->coupon,
            $booking->email,
            $booking->tour_date ? (string) $booking->tour_date : null
        );

        if ($resolved['authoritative']) {
            $subtotal = $resolved['subtotal'];
            $discount = $resolved['discount'];
            $total = $resolved['total'];
            if (abs((float) $booking->total - $total) >= 0.01 || abs((float) $booking->subtotal - $subtotal) >= 0.01) {
                $booking->update([
                    'subtotal' => $subtotal,
                    'addons_total' => $addonsTotal,
                    'discount_amount' => $discount,
                    'original_total' => round($subtotal + $addonsTotal, 2),
                    'total' => $total,
                    'balance_due' => max(0, round($total - (float) $booking->payment_amount, 2)),
                ]);
            }
        } else {
            $subtotal = $fallbackSubtotal;
            $discount = max(0, (float) $booking->discount_amount);
            $total = max(0, (float) $booking->total);
        }

        $ziinaActive = $this->ziina->isActive();
        $advancePercent = $this->ziina->getAdvancePercent();
        $advanceAmount = round(($total * $advancePercent) / 100, 2);
        $fullAmount = round($total, 2);

        $pageRobots = 'noindex, nofollow';

        return view('booking.recover', compact(
            'booking',
            'tour',
            'tier',
            'subtotal',
            'addonsTotal',
            'discount',
            'total',
            'ziinaActive',
            'advancePercent',
            'advanceAmount',
            'fullAmount',
            'pageRobots'
        ));
    }

    /**
     * Process checkout submission from the dedicated recovery page.
     */
    public function processRecoverCheckout(Request $request, string $reference)
    {
        $booking = Booking::with(['tour', 'tier', 'addons', 'coupon'])->where('reference', $reference)->first();

        if (! $booking) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Reservation record not found.'], 404);
            }

            return redirect()->route('home')->with('error', 'Reservation record not found.');
        }

        if (in_array($booking->status, ['confirmed', 'completed']) || in_array($booking->payment_status, ['paid', 'completed'])) {
            $redirectUrl = localized_route('booking.voucher', ['reference' => $booking->reference]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'redirect_url' => $redirectUrl]);
            }

            return redirect()->to($redirectUrl);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'location' => 'required|string|max:500',
            'requests' => 'nullable|string',
            'payment_method' => 'required|string|in:cash,advance,full',
        ]);

        $paymentMethod = $request->input('payment_method');
        if (! $this->ziina->isActive()) {
            $paymentMethod = 'cash';
        }
        $name = trim($request->input('name'));
        $email = strtolower(trim($request->input('email')));
        $phone = trim($request->input('phone'));
        $location = trim($request->input('location'));
        $requests = trim($request->input('requests', ''));

        $tour = $booking->tour ?: Tour::find($booking->tour_id);
        $tier = $booking->tier ?: Tier::find($booking->tier_id);
        $addonsTotal = $booking->addons && $booking->addons->count()
            ? round((float) $booking->addons->sum(fn ($a) => (float) $a->price * max(1, (int) ($a->quantity ?? 1))), 2)
            : max(0, (float) $booking->addons_total);

        $fallbackSubtotal = max(0, (float) $booking->subtotal ?: ((float) $booking->total ?: 0));
        $resolved = $this->resolveAuthoritativeDraftPricing(
            $tour,
            $tier,
            max(1, (int) $booking->adults),
            max(0, (int) $booking->children),
            $fallbackSubtotal,
            $addonsTotal,
            $booking->coupon,
            $email,
            $booking->tour_date ? (string) $booking->tour_date : null
        );

        $existingNotes = (string) $booking->special_requests;
        $recoveryTag = '[RECOVERED_CHECKOUT: '.now()->format('Y-m-d H:i:s').' ('.$paymentMethod.')]';
        $updatedNotes = $requests;
        if (! empty($existingNotes) && ! str_contains($existingNotes, '[RECOVERED_CHECKOUT')) {
            $updatedNotes = trim($updatedNotes."\n".$existingNotes);
        }
        $updatedNotes = trim($updatedNotes."\n".$recoveryTag);

        $updatePayload = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'pickup_location' => $location,
            'special_requests' => $updatedNotes,
            'payment_method' => $paymentMethod,
        ];

        if ($resolved['authoritative']) {
            $updatePayload['subtotal'] = $resolved['subtotal'];
            $updatePayload['addons_total'] = $addonsTotal;
            $updatePayload['discount_amount'] = $resolved['discount'];
            $updatePayload['original_total'] = round($resolved['subtotal'] + $addonsTotal, 2);
            $updatePayload['total'] = $resolved['total'];
        }

        $booking->update($updatePayload);

        $total = max(0, (float) $booking->total);

        if ($paymentMethod === 'cash') {
            $booking->update([
                'status' => 'confirmed',
                'payment_status' => 'unpaid',
                'payment_amount' => 0.00,
                'balance_due' => $total,
            ]);

            $this->recordCouponUsage($booking);
            $this->sendEmailNotification('booking_cash', $booking);

            $custom = [
                'value' => (float) $booking->total,
                'currency' => 'AED',
                'content_ids' => ['TOUR-'.$booking->tour_id],
                'content_type' => 'product',
                'contents' => [['id' => 'TOUR-'.$booking->tour_id, 'quantity' => 1]],
                'coupon' => $booking->coupon_code,
                'discount_amount' => (float) $booking->discount_amount,
            ];
            $this->metaCapi->dispatchEvent('Purchase', [
                'event_id' => 'BOOK-'.$booking->reference,
                'email' => $booking->email,
                'phone' => $booking->phone,
                'custom_data' => $custom,
            ]);

            $redirectUrl = localized_route('booking.thankyou', ['ref' => $booking->reference]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thank you! Your reservation has been confirmed.',
                    'redirect_url' => $redirectUrl,
                ]);
            }

            return redirect()->to($redirectUrl);
        }

        $advancePercent = $this->ziina->getAdvancePercent();
        $payNow = ($paymentMethod === 'advance') ? round(($total * $advancePercent) / 100, 2) : round($total, 2);

        if ($payNow < 0.50) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Minimum online payment amount is AED 0.50'], 400);
            }

            return redirect()->back()->with('error', 'Minimum online payment amount is AED 0.50');
        }

        $successUrl = localized_route('booking.thankyou', ['ref' => $booking->reference]);
        $cancelUrl = localized_route('booking.cancel', ['ref' => $booking->reference]);
        $messageStr = 'Booking '.$booking->reference;

        session([
            'active_booking_draft_id' => $booking->id,
            'active_booking_reference' => $booking->reference,
            'last_booking_reference' => $booking->reference,
        ]);

        $intent = $this->ziina->createPaymentIntent($payNow, 'AED', $successUrl, $cancelUrl, $messageStr);

        if (isset($intent['error'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Online payment could not be initiated at this moment: '.($intent['error']),
                ], 400);
            }

            return redirect()->back()->with('error', 'Online payment could not be initiated: '.($intent['error']));
        }

        $booking->update([
            'ziina_payment_intent_id' => $intent['id'] ?? null,
            'ziina_status' => $intent['status'] ?? null,
            'ziina_redirect_url' => $intent['redirect_url'] ?? null,
        ]);

        BookingPayment::create([
            'booking_id' => $booking->id,
            'payment_intent_id' => $intent['id'],
            'amount' => $payNow,
            'currency' => 'AED',
            'status' => $intent['status'] ?? 'pending',
            'payment_url' => $intent['redirect_url'],
        ]);

        $custom = [
            'value' => (float) $booking->total,
            'currency' => 'AED',
            'content_ids' => ['TOUR-'.$booking->tour_id],
            'content_type' => 'product',
            'contents' => [['id' => 'TOUR-'.$booking->tour_id, 'quantity' => 1]],
        ];
        $this->metaCapi->dispatchEvent('InitiateCheckout', [
            'event_id' => 'INIT-'.$booking->reference,
            'email' => $booking->email,
            'phone' => $booking->phone,
            'custom_data' => $custom,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect_url' => $intent['redirect_url'],
            ]);
        }

        return redirect()->away($intent['redirect_url']);
    }

    /**
     * Recompute authoritative subtotal, discount, and total for draft/recovered bookings whenever a valid tour + tier exists.
     */
    protected function resolveAuthoritativeDraftPricing(
        ?Tour $tour,
        ?Tier $tier,
        int $adults,
        int $children,
        float $fallbackSubtotal,
        float $addonsTotal = 0.0,
        ?Coupon $coupon = null,
        ?string $email = null,
        ?string $tourDate = null
    ): array {
        if ($tour && $tier) {
            $pricing = DB::table('tour_tiers')
                ->where('tour_id', $tour->id)
                ->where('tier_id', $tier->id)
                ->first();

            if ($pricing && (float) $pricing->price > 0) {
                $price = (float) $pricing->price;
                $priceType = Tier::normalizePriceType($pricing->price_type ?? 'per person', $tour);

                if (Tier::isUnitPriceType($priceType, $tour)) {
                    $capacity = Tier::resolveUnitCapacity($pricing, $tier, $priceType);
                    $vehicles = (int) ceil(($adults + $children) / max(1, $capacity));
                    $subtotal = round($vehicles * $price, 2);
                } else {
                    $childPrice = (isset($pricing->child_price) && (float) $pricing->child_price > 0)
                        ? (float) $pricing->child_price
                        : round($price * 0.70, 2);
                    $subtotal = round(($price * $adults) + ($childPrice * $children), 2);
                }

                $rawTotal = round($subtotal + $addonsTotal, 2);
                $discount = 0.0;
                if ($coupon) {
                    $check = $coupon->validateEligibility($rawTotal, (int) $tour->id, (int) $tier->id, $email, max(1, $adults + $children), $tourDate);
                    if ($check['valid']) {
                        $discount = (float) $check['discount'];
                    }
                }

                return [
                    'authoritative' => true,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total' => max(0, round($rawTotal - $discount, 2)),
                ];
            }
        }

        return [
            'authoritative' => false,
            'subtotal' => max(0, $fallbackSubtotal),
            'discount' => 0.0,
            'total' => max(0, round($fallbackSubtotal + $addonsTotal, 2)),
        ];
    }
}
