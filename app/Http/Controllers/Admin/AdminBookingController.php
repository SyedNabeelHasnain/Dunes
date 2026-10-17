<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AbandonedBookingRecoveryMail;
use App\Mail\BookingNotification;
use App\Mail\PaymentLinkMail;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\BookingPayment;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\RequestLog;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Tier;
use App\Models\Tour;
use App\Models\VerifiedEmail;
use App\Services\EmailMarketingService;
use App\Services\SettingsService;
use App\Services\ZiinaPaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AdminBookingController extends Controller
{
    /**
     * Display a listing of bookings.
     */
    public function index(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');
        $paymentStatus = $request->input('payment_status');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $query = Booking::with(['tour', 'tier', 'addons']);

        if ($status) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', 'draft');
        }

        if ($paymentStatus) {
            $query->where('payment_status', $paymentStatus);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('tour_name', 'like', "%{$search}%");
            });
        }
        if ($fromDate) {
            $query->whereDate('tour_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('tour_date', '<=', $toDate);
        }

        $revenue = (float) Booking::whereIn('status', ['confirmed', 'completed'])
            ->whereIn('payment_status', ['paid', 'partial'])
            ->sum(DB::raw("CASE WHEN payment_amount > 0 THEN payment_amount ELSE (CASE WHEN payment_status = 'paid' THEN total ELSE 0 END) END"));

        $paidCount = Booking::whereIn('status', ['confirmed', 'completed'])
            ->whereIn('payment_status', ['paid', 'partial'])
            ->count();
        $avgOrderValue = $paidCount > 0 ? round($revenue / $paidCount, 2) : 0;
        $addonsRevenue = (float) Booking::whereIn('status', ['confirmed', 'completed'])->sum('addons_total');

        $stats = [
            'total' => Booking::where('status', '!=', 'draft')->count(),
            'pending' => Booking::where('status', 'pending')->count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'completed' => Booking::where('status', 'completed')->count(),
            'drafts' => Booking::where('status', 'draft')->count(),
            'addons_revenue' => $addonsRevenue,
            'revenue' => $revenue,
            'aov' => $avgOrderValue,
        ];

        // 14-Day Booking & Revenue Acquisition Trend Data
        $dailyTrend = Booking::where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->where('status', '!=', 'draft')
            ->selectRaw("
                DATE(created_at) as d,
                COUNT(*) as c,
                SUM(CASE WHEN status IN ('confirmed', 'completed') AND payment_status IN ('paid', 'partial') THEN (CASE WHEN payment_amount > 0 THEN payment_amount ELSE (CASE WHEN payment_status = 'paid' THEN total ELSE 0 END) END) ELSE 0 END) as rev
            ")
            ->groupBy('d')
            ->get()
            ->keyBy('d');

        $trendData = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $label = now()->subDays($i)->format('M j');
            $row = $dailyTrend->get($date);
            $trendData[] = [
                'date' => $label,
                'count' => $row ? (int) $row->c : 0,
                'revenue' => $row ? (float) $row->rev : 0.0,
            ];
        }

        // Status Breakdown
        $statusDistribution = [
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'pending' => Booking::where('status', 'pending')->count(),
            'completed' => Booking::where('status', 'completed')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
            'draft' => Booking::where('status', 'draft')->count(),
        ];

        $bookings = $query->orderBy('created_at', 'desc')->get();

        return view('admin.bookings.index', compact('bookings', 'status', 'search', 'paymentStatus', 'fromDate', 'toDate', 'stats', 'trendData', 'statusDistribution'));
    }

    /**
     * Display a comprehensive listing and recovery hub for Incomplete Drafts & Abandoned Checkouts.
     */
    public function drafts(Request $request)
    {
        $search = $request->input('search');
        $tourId = $request->input('tour_id');
        $contactType = $request->input('contact_type');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $query = Booking::with(['tour', 'tier'])
            ->where('status', 'draft');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('tour_name', 'like', "%{$search}%");
            });
        }

        if ($tourId) {
            $query->where('tour_id', $tourId);
        }

        if ($contactType === 'has_phone') {
            $query->whereNotNull('phone')->where('phone', '!=', '');
        } elseif ($contactType === 'has_email') {
            $query->whereNotNull('email')->where('email', '!=', '')->where('email', 'not like', '%@guest.local');
        } elseif ($contactType === 'both') {
            $query->whereNotNull('phone')->where('phone', '!=', '')
                ->whereNotNull('email')->where('email', '!=', '')->where('email', 'not like', '%@guest.local');
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        // Analytics KPIs for Drafts
        $totalDrafts = Booking::where('status', 'draft')->count();
        $todayDrafts = Booking::where('status', 'draft')->whereDate('created_at', today())->count();
        $recoverableValue = (float) Booking::where('status', 'draft')->sum('total');

        $contactableCount = Booking::where('status', 'draft')
            ->where(function ($q) {
                $q->where(function ($sq) {
                    $sq->whereNotNull('phone')->where('phone', '!=', '');
                })->orWhere(function ($sq) {
                    $sq->whereNotNull('email')->where('email', '!=', '')->where('email', 'not like', '%@guest.local');
                });
            })
            ->count();
        $contactableRate = $totalDrafts > 0 ? round(($contactableCount / $totalDrafts) * 100, 1) : 0;

        $stats = [
            'total' => $totalDrafts,
            'today' => $todayDrafts,
            'recoverable_value' => $recoverableValue,
            'contactable_count' => $contactableCount,
            'contactable_rate' => $contactableRate,
        ];

        // 14-Day Abandonment Trend
        $dailyTrend = Booking::where('status', 'draft')
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c, SUM(total) as val')
            ->groupBy('d')
            ->get()
            ->keyBy('d');

        $trendData = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $label = now()->subDays($i)->format('M j');
            $row = $dailyTrend->get($date);
            $trendData[] = [
                'date' => $label,
                'count' => $row ? (int) $row->c : 0,
                'value' => $row ? (float) $row->val : 0.0,
            ];
        }

        $availableTours = Tour::select('id', 'name')->orderBy('name')->get();

        $drafts = $query->orderBy('created_at', 'desc')->paginate(25)->withQueryString();

        return view('admin.bookings.drafts', compact(
            'drafts',
            'stats',
            'trendData',
            'availableTours',
            'search',
            'tourId',
            'contactType',
            'fromDate',
            'toDate'
        ));
    }

    /**
     * Convert an abandoned checkout draft to a full booking.
     */
    public function convertDraft(Request $request, int $id)
    {
        $draft = Booking::where('id', $id)->where('status', 'draft')->firstOrFail();

        $targetStatus = $request->input('target_status', $request->input('status', 'pending'));
        if (! in_array($targetStatus, ['pending', 'confirmed'])) {
            $targetStatus = 'pending';
        }

        $newReference = str_starts_with($draft->reference, 'DFT')
            ? 'DDT'.date('ymd').strtoupper(Str::random(5))
            : $draft->reference;

        $updateData = [
            'reference' => $newReference,
            'status' => $targetStatus,
            'payment_status' => $request->input('payment_status', $draft->payment_status ?: 'unpaid'),
        ];
        if ($request->filled('notes')) {
            $updateData['special_requests'] = $request->input('notes');
        }

        $draft->update($updateData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Draft successfully converted to booking #{$draft->reference}!",
                'redirect_url' => route('admin.bookings.show', $draft->id),
            ]);
        }

        return redirect()->route('admin.bookings.show', $draft->id)
            ->with('success', "Draft converted to booking #{$draft->reference} successfully.");
    }

    /**
     * Preview abandoned draft recovery email for admin review before dispatch.
     */
    public function previewDraftRecovery(Request $request, int $id)
    {
        $draft = Booking::with(['tour', 'tier'])->where('id', $id)->where('status', 'draft')->firstOrFail();

        $defaultSubject = "Complete Your Dubai Desert Safari Reservation - Ref: #{$draft->reference}";
        $mailable = new AbandonedBookingRecoveryMail($draft);
        $renderedHtml = $mailable->render();

        return response()->json([
            'success' => true,
            'draft' => [
                'id' => $draft->id,
                'reference' => $draft->reference,
                'name' => $draft->name ?: 'Prospective Guest',
                'email' => $draft->email,
                'phone' => $draft->phone,
                'tour_name' => $draft->tour_name,
                'tier_name' => $draft->tier_name,
                'tour_date' => $draft->tour_date ? $draft->tour_date->format('M j, Y') : 'Upcoming',
                'total' => number_format((float) $draft->total, 2),
                'recovery_url' => $mailable->recoveryUrl,
            ],
            'subject' => $defaultSubject,
            'html' => $renderedHtml,
        ]);
    }

    /**
     * Send direct recovery email to the abandoned draft guest.
     */
    public function sendDraftRecovery(Request $request, int $id)
    {
        $draft = Booking::where('id', $id)->where('status', 'draft')->firstOrFail();

        $toEmail = trim($request->input('to_email', $draft->email));
        $subject = trim($request->input('subject', ''));
        $customMessage = trim($request->input('custom_message', ''));

        if (empty($toEmail) || str_ends_with($toEmail, '@guest.local') || ! filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide a valid customer email address to send the recovery email.',
                ], 422);
            }

            return redirect()->back()->with('error', 'No valid email found on this draft.');
        }

        try {
            if (! app()->environment('testing')) {
                try {
                    app(EmailMarketingService::class)->applySmtpConfig();
                } catch (\Throwable $e) {}
            }

            $mailable = new AbandonedBookingRecoveryMail(
                $draft,
                ! empty($subject) ? $subject : null,
                ! empty($customMessage) ? $customMessage : null
            );

            Mail::to($toEmail)->send($mailable);

            $existingRequests = (string) $draft->special_requests;
            $dispatchTag = '[RECOVERY_DISPATCHED: '.now()->format('Y-m-d H:i:s').' to '.$toEmail.']';
            if (! str_contains($existingRequests, '[RECOVERY_DISPATCHED')) {
                $draft->update([
                    'special_requests' => trim($existingRequests."\n".$dispatchTag),
                ]);
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Recovery email dispatched to {$toEmail} via SMTP successfully!",
                ]);
            }

            return redirect()->back()->with('success', "Recovery email sent to {$toEmail}.");
        } catch (\Throwable $e) {
            Log::error("Failed to send draft recovery email for draft #{$draft->id}: ".$e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send recovery email: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Error sending recovery email: '.$e->getMessage());
        }
    }

    /**
     * Delete an abandoned draft permanently.
     */
    public function destroyDraft(Request $request, int $id)
    {
        try {
            $draft = Booking::where('id', $id)->where('status', 'draft')->firstOrFail();
            $ref = $draft->reference;

            // Delete associated addons if any
            BookingAddon::where('booking_id', $draft->id)->delete();
            $draft->forceDelete();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Draft #{$ref} permanently removed.",
                ]);
            }

            return redirect()->route('admin.bookings.drafts')
                ->with('success', "Draft #{$ref} permanently removed.");
        } catch (\Throwable $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete draft: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to delete draft.');
        }
    }

    /**
     * Batch bulk actions for drafts.
     */
    public function bulkDraftsAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer',
            'action' => 'required|string|in:delete',
        ]);

        $ids = $request->input('ids');
        $action = $request->input('action');

        if ($action === 'delete') {
            BookingAddon::whereIn('booking_id', $ids)->delete();
            $deleted = Booking::whereIn('id', $ids)->where('status', 'draft')->forceDelete();

            return response()->json([
                'success' => true,
                'message' => "{$deleted} draft(s) permanently removed.",
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action.'], 400);
    }

    /**
     * Export abandoned checkout drafts to CSV.
     */
    public function exportDraftsCsv(Request $request)
    {
        $fileName = 'dunes-abandoned-drafts-'.date('Y-m-d-His').'.csv';

        $query = Booking::with(['tour', 'tier'])
            ->where('status', 'draft');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('tour_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tour_id')) {
            $query->where('tour_id', $request->input('tour_id'));
        }

        if ($request->filled('contact_type')) {
            $ct = $request->input('contact_type');
            if ($ct === 'has_phone') {
                $query->whereNotNull('phone')->where('phone', '!=', '');
            } elseif ($ct === 'has_email') {
                $query->whereNotNull('email')->where('email', '!=', '')->where('email', 'not like', '%@guest.local');
            } elseif ($ct === 'both') {
                $query->whereNotNull('phone')->where('phone', '!=', '')
                    ->whereNotNull('email')->where('email', '!=', '')->where('email', 'not like', '%@guest.local');
            }
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }

        $drafts = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'Draft Ref', 'Date Dropped', 'Guest Name', 'Phone', 'Email',
            'Tour Name', 'Package Tier', 'Target Tour Date', 'Adults', 'Children', 'Infants',
            'Cart Total AED', 'Pickup Location', 'IP Address',
        ];

        $callback = function () use ($drafts, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            foreach ($drafts as $d) {
                fputcsv($file, [
                    $d->reference,
                    $d->created_at?->format('Y-m-d H:i:s') ?? '',
                    $d->name,
                    $d->phone ?: '',
                    str_ends_with((string) $d->email, '@guest.local') ? '' : $d->email,
                    $d->tour_name,
                    $d->tier_name,
                    $d->tour_date ? $d->tour_date->format('Y-m-d') : '',
                    $d->adults,
                    $d->children,
                    $d->infants,
                    number_format((float) $d->total, 2, '.', ''),
                    $d->pickup_location ?: '',
                    $d->ip_address ?: '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show the form for creating a manual booking in Admin CMS.
     */
    public function create(Request $request)
    {
        $hasChildPrice = Schema::hasColumn('tour_tiers', 'child_price');
        $tours = Tour::with([
            'tiers' => fn ($q) => $hasChildPrice ? $q->withPivot('child_price') : $q,
            'addons',
        ])->orderBy('priority')->orderBy('id')->get();

        $allTiers = Tier::orderBy('id')->get();
        $allAddons = Addon::orderBy('id')->get();
        $coupons = Coupon::where('status', 'active')->orderBy('code')->get();

        $toursCatalog = $tours->map(function (Tour $tour) use ($allTiers, $allAddons) {
            $tierItems = $tour->tiers->isNotEmpty()
                ? $tour->tiers->map(fn ($tier) => [
                    'id' => (int) $tier->id,
                    'name' => (string) ($tier->display_name ?: $tier->name),
                    'code' => (string) ($tier->slug ?: $tier->code),
                    'price' => (float) ($tier->pivot->price ?? $tour->price),
                    'child_price' => (float) ($tier->pivot->child_price ?? 0),
                    'price_type' => (string) ($tier->pivot->price_type ?? $tier->type ?? 'per_person'),
                    'capacity' => max(1, (int) ($tier->capacity ?: 6)),
                ])->values()->all()
                : $allTiers->map(fn ($tier) => [
                    'id' => (int) $tier->id,
                    'name' => (string) ($tier->display_name ?: $tier->name),
                    'code' => (string) ($tier->slug ?: $tier->code),
                    'price' => (float) $tour->price,
                    'child_price' => 0.0,
                    'price_type' => (string) ($tier->type ?? 'per_person'),
                    'capacity' => max(1, (int) ($tier->capacity ?: 6)),
                ])->values()->all();

            $tourAddonMap = $tour->addons->keyBy('id');
            $addonItems = $allAddons->map(function ($addon) use ($tourAddonMap) {
                $attached = $tourAddonMap->get($addon->id);

                return [
                    'id' => (int) $addon->id,
                    'name' => (string) $addon->name,
                    'price' => $attached ? (float) ($attached->pivot->price ?? $addon->price) : (float) $addon->price,
                    'price_type' => (string) ($addon->price_type ?: 'per_person'),
                    'is_tour_specific' => $attached !== null,
                ];
            })->values()->all();

            return [
                'id' => (int) $tour->id,
                'name' => (string) $tour->name,
                'price' => (float) $tour->price,
                'tiers' => $tierItems,
                'addons' => $addonItems,
            ];
        })->values()->all();

        $couponsCatalog = $coupons->map(fn (Coupon $c) => [
            'id' => (int) $c->id,
            'code' => (string) $c->code,
            'name' => (string) $c->name,
            'discount_type' => (string) $c->discount_type,
            'discount_value' => (float) $c->discount_value,
            'max_discount' => $c->max_discount ? (float) $c->max_discount : null,
            'min_spend' => (float) ($c->min_spend ?? 0),
        ])->values()->all();

        $prefillTourId = (string) $request->query('tour_id', '');
        if ($prefillTourId === '' && $request->filled('tour_name')) {
            $needle = strtolower(trim((string) $request->query('tour_name')));
            $matchedTour = $tours->first(fn ($t) => strtolower($t->name) === $needle)
                ?: $tours->first(fn ($t) => str_contains(strtolower($t->name), $needle) || str_contains($needle, strtolower($t->name)));
            if ($matchedTour) {
                $prefillTourId = (string) $matchedTour->id;
            }
        }

        $prefill = [
            'name' => (string) $request->query('name', ''),
            'email' => (string) $request->query('email', ''),
            'phone' => (string) $request->query('phone', ''),
            'tour_id' => $prefillTourId,
            'tier_id' => (string) $request->query('tier_id', ''),
            'tour_date' => (string) $request->query('tour_date', now()->addDay()->format('Y-m-d')),
            'adults' => max(1, (int) $request->query('adults', 2)),
            'children' => max(0, (int) $request->query('children', 0)),
            'infants' => max(0, (int) $request->query('infants', 0)),
            'pickup_location' => (string) $request->query('pickup_location', ''),
            'special_requests' => (string) $request->query('special_requests', ''),
        ];

        return view('admin.bookings.create', compact(
            'tours',
            'allTiers',
            'allAddons',
            'coupons',
            'toursCatalog',
            'couponsCatalog',
            'prefill'
        ));
    }

    /**
     * Store a newly created manual booking in database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:150',
            'phone' => 'required|string|max:50',
            'tour_id' => 'required|exists:tours,id',
            'tier_id' => 'required|exists:tiers,id',
            'tour_date' => 'required|date',
            'adults' => 'required|integer|min:1|max:500',
            'children' => 'nullable|integer|min:0|max:500',
            'infants' => 'nullable|integer|min:0|max:500',
            'addons' => 'nullable|array',
            'addons.*' => 'integer|exists:addons,id',
            'coupon_code' => 'nullable|string|max:64',
            'discount_amount' => 'nullable|numeric|min:0',
            'custom_total' => 'nullable|numeric|min:0',
            'status' => 'required|string|in:pending,confirmed,completed,cancelled',
            'payment_method' => 'required|string|max:50',
            'payment_status' => 'required|string|in:unpaid,partial,paid',
            'payment_amount' => 'nullable|numeric|min:0',
            'balance_due' => 'nullable|numeric|min:0',
            'pickup_location' => 'nullable|string|max:500',
            'special_requests' => 'nullable|string|max:2000',
            'send_confirmation_email' => 'nullable|boolean',
            'generate_payment_link' => 'nullable|boolean',
        ]);

        $tour = Tour::findOrFail($request->input('tour_id'));
        $tier = Tier::findOrFail($request->input('tier_id'));

        $tourTier = DB::table('tour_tiers')
            ->where('tour_id', $tour->id)
            ->where('tier_id', $tier->id)
            ->first();

        $price = $tourTier ? (float) $tourTier->price : (float) $tour->price;
        $priceType = $tourTier ? $tourTier->price_type : ($tier->type ?? 'per_person');

        $adults = max(1, (int) $request->input('adults', 1));
        $children = max(0, (int) $request->input('children', 0));
        $infants = max(0, (int) $request->input('infants', 0));

        if ($priceType === 'per_vehicle') {
            $capacity = max(1, (int) ($tier->capacity ?: 6));
            $billableGuests = max(1, $adults + $children);
            $vehicles = (int) ceil($billableGuests / $capacity);
            $subtotal = $vehicles * $price;
        } else {
            $childPrice = ($tourTier && isset($tourTier->child_price) && (float) $tourTier->child_price > 0)
                ? (float) $tourTier->child_price
                : ($price * 0.70);
            $subtotal = ($adults * $price) + ($children * $childPrice);
        }

        $addonsTotal = 0.0;
        $addonRecords = [];
        $selectedAddonIds = array_unique(array_map('intval', $request->input('addons', [])));

        foreach ($selectedAddonIds as $addonId) {
            $addon = Addon::find($addonId);
            if (! $addon) {
                continue;
            }
            $tourAddon = DB::table('tour_addons')
                ->where('tour_id', $tour->id)
                ->where('addon_id', $addonId)
                ->first();

            $aPrice = $tourAddon ? (float) $tourAddon->price : (float) $addon->price;
            $qty = ($addon->price_type === 'per_group') ? 1 : max(1, $adults + $children);
            $addonsTotal += ($aPrice * $qty);
            $addonRecords[] = [
                'addon_id' => $addon->id,
                'addon_name' => $addon->name,
                'price' => $aPrice,
                'quantity' => $qty,
            ];
        }

        $subtotal = round($subtotal, 2);
        $addonsTotal = round($addonsTotal, 2);
        $originalTotal = round($subtotal + $addonsTotal, 2);

        $couponCodeInput = strtoupper(trim((string) $request->input('coupon_code', '')));
        $appliedCoupon = null;
        $discountAmount = 0.0;

        if ($couponCodeInput !== '') {
            $appliedCoupon = Coupon::findByCode($couponCodeInput);
            if ($appliedCoupon) {
                $discountAmount = $appliedCoupon->calculateDiscount($originalTotal, $adults + $children);
            }
        }

        if ($request->filled('discount_amount') && (float) $request->input('discount_amount') > 0) {
            $discountAmount = min($originalTotal, round((float) $request->input('discount_amount'), 2));
        }

        $calculatedTotal = max(0, round($originalTotal - $discountAmount, 2));

        if ($request->filled('custom_total') && $request->input('custom_total') !== '') {
            $total = max(0, round((float) $request->input('custom_total'), 2));
            if ($total < $originalTotal && $discountAmount == 0) {
                $discountAmount = round($originalTotal - $total, 2);
            }
        } else {
            $total = $calculatedTotal;
        }

        $paymentStatus = $request->input('payment_status', 'unpaid');
        if ($paymentStatus === 'paid') {
            $paymentAmount = ($request->filled('payment_amount') && (float) $request->input('payment_amount') > 0)
                ? round((float) $request->input('payment_amount'), 2)
                : $total;
            $balanceDue = 0.0;
        } elseif ($paymentStatus === 'partial') {
            if ($request->filled('payment_amount') && (float) $request->input('payment_amount') > 0) {
                $paymentAmount = min($total, max(0, round((float) $request->input('payment_amount'), 2)));
            } else {
                $paymentAmount = round($total * 0.25, 2);
            }
            $balanceDue = $request->filled('balance_due')
                ? max(0, round((float) $request->input('balance_due'), 2))
                : max(0, round($total - $paymentAmount, 2));
        } else {
            $paymentAmount = 0.0;
            $balanceDue = $request->filled('balance_due')
                ? max(0, round((float) $request->input('balance_due'), 2))
                : $total;
        }

        do {
            $ref = 'DDT'.date('ymd').strtoupper(Str::random(5));
        } while (Booking::withTrashed()->where('reference', $ref)->exists());

        $booking = DB::transaction(function () use (
            $ref,
            $tour,
            $tier,
            $appliedCoupon,
            $couponCodeInput,
            $request,
            $adults,
            $children,
            $infants,
            $subtotal,
            $addonsTotal,
            $originalTotal,
            $discountAmount,
            $total,
            $paymentAmount,
            $balanceDue,
            $paymentStatus,
            $addonRecords
        ) {
            $booking = Booking::create([
                'reference' => $ref,
                'tour_id' => $tour->id,
                'tier_id' => $tier->id,
                'coupon_id' => $appliedCoupon?->id,
                'coupon_code' => $appliedCoupon ? $appliedCoupon->code : ($couponCodeInput ?: null),
                'tour_name' => $tour->name,
                'tier_name' => $tier->display_name ?: $tier->name,
                'name' => trim((string) $request->input('name')),
                'email' => strtolower(trim((string) $request->input('email'))),
                'phone' => trim((string) $request->input('phone')),
                'tour_date' => $request->input('tour_date'),
                'adults' => $adults,
                'children' => $children,
                'infants' => $infants,
                'subtotal' => $subtotal,
                'addons_total' => $addonsTotal,
                'original_total' => $originalTotal,
                'discount_amount' => $discountAmount,
                'total' => $total,
                'payment_method' => $request->input('payment_method', 'cash'),
                'payment_amount' => $paymentAmount,
                'balance_due' => $balanceDue,
                'payment_status' => $paymentStatus,
                'status' => $request->input('status', 'confirmed'),
                'pickup_location' => $request->input('pickup_location'),
                'special_requests' => $request->input('special_requests'),
            ]);

            foreach ($addonRecords as $rec) {
                BookingAddon::create(array_merge($rec, ['booking_id' => $booking->id]));
            }

            if ($appliedCoupon && $appliedCoupon->id && $discountAmount > 0) {
                $appliedCoupon->increment('used_count');
                CouponUsage::create([
                    'coupon_id' => $appliedCoupon->id,
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->reference,
                    'customer_name' => $booking->name,
                    'customer_email' => $booking->email,
                    'customer_phone' => $booking->phone,
                    'discount_amount' => $discountAmount,
                    'order_subtotal' => $originalTotal,
                    'order_final_total' => $total,
                    'used_at' => now(),
                ]);
            }

            VerifiedEmail::markVerified($booking->email);

            return $booking;
        });

        Cache::forget('admin_dashboard_kpis');

        // Optional Ziina Payment Link Generation
        if ($request->boolean('generate_payment_link') && $booking->balance_due > 0) {
            try {
                $ziina = app(ZiinaPaymentService::class);
                $successUrl = route('booking.thankyou', ['pi' => '{PAYMENT_INTENT_ID}']);
                $cancelUrl = route('booking.cancel', ['pi' => '{PAYMENT_INTENT_ID}']);
                $intent = $ziina->createPaymentIntent(
                    (float) $booking->balance_due,
                    'AED',
                    $successUrl,
                    $cancelUrl,
                    'Booking '.$booking->reference
                );
                if (! empty($intent['redirect_url']) && ! empty($intent['id'])) {
                    BookingPayment::create([
                        'booking_id' => $booking->id,
                        'payment_intent_id' => $intent['id'],
                        'amount' => (float) $booking->balance_due,
                        'currency' => 'AED',
                        'status' => $intent['status'] ?? 'pending',
                        'payment_url' => $intent['redirect_url'],
                        'notes' => 'Generated on manual booking creation',
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Manual booking Ziina payment link skipped: '.$e->getMessage());
            }
        }

        // Optional Guest Confirmation Email
        if ($request->boolean('send_confirmation_email') && ! empty($booking->email)) {
            try {
                if (! app()->environment('testing')) {
                    try {
                        app(EmailMarketingService::class)->applySmtpConfig();
                    } catch (\Throwable $e) {
                    }
                }
                $fromEmail = app(SettingsService::class)->getFromEmail();
                $emailType = match (true) {
                    $booking->status === 'cancelled' => 'booking_cancelled',
                    in_array($booking->status, ['confirmed', 'completed'], true) => 'booking_confirmed',
                    $booking->payment_status === 'paid' => 'booking_full',
                    $booking->payment_status === 'partial' => 'booking_advance',
                    default => 'booking_cash',
                };
                Mail::to($booking->email)->send(
                    (new BookingNotification($emailType, $booking->load('addons')))->from($fromEmail, 'Dunes Discovery Tourism')
                );
            } catch (\Throwable $e) {
                Log::error('Failed to send manual booking confirmation email: '.$e->getMessage());
            }
        }

        return redirect()
            ->route('admin.bookings.show', $booking->id)
            ->with('success', "Booking #{$booking->reference} created successfully!");
    }

    /**
     * Export bookings to CSV.
     */
    public function exportCsv(Request $request)
    {
        $fileName = 'dunes-bookings-export-'.date('Y-m-d-His').'.csv';
        $query = Booking::with(['tour', 'tier'])->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', '!=', 'draft');
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('reference', 'like', "%{$s}%")
                    ->orWhere('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('tour_name', 'like', "%{$s}%");
            });
        }
        if ($request->filled('from_date')) {
            $query->whereDate('tour_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('tour_date', '<=', $request->to_date);
        }

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'Reference', 'Created At', 'Customer Name', 'Email', 'Phone',
            'Tour Name', 'Package Tier', 'Tour Date', 'Pickup Time', 'Adults',
            'Children', 'Infants', 'Pickup Location', 'Total (AED)', 'Paid (AED)',
            'Balance Due (AED)', 'Payment Method', 'Payment Status', 'Booking Status',
        ];

        $sanitize = function (array $row): array {
            return array_map(function ($val) {
                if ($val === null) {
                    return '';
                }
                $str = (string) $val;
                if (isset($str[0]) && in_array($str[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
                    return "'".$str;
                }

                return $str;
            }, $row);
        };

        $callback = function () use ($query, $columns, $sanitize) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            $query->chunk(100, function ($rows) use ($file, $sanitize) {
                foreach ($rows as $b) {
                    fputcsv($file, $sanitize([
                        $b->reference,
                        $b->created_at ? $b->created_at->format('Y-m-d H:i') : '',
                        $b->name,
                        $b->email,
                        $b->phone,
                        $b->tour_name,
                        $b->tier_name ?: ($b->tier ? $b->tier->display_name : 'Standard'),
                        $b->tour_date ? $b->tour_date->format('Y-m-d') : '',
                        $b->pickup_time ?: '',
                        $b->adults ?? 1,
                        $b->children ?? 0,
                        $b->infants ?? 0,
                        $b->pickup_location ?: '',
                        $b->total,
                        $b->payment_amount,
                        $b->balance_due,
                        $b->payment_method,
                        $b->payment_status,
                        $b->status,
                    ]));
                }
            });
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display details of a specific booking.
     */
    public function show(string $id)
    {
        $booking = Booking::with(['tour', 'tier', 'addons', 'payments', 'driver.defaultVehicle', 'vehicle'])->findOrFail($id);

        $log = null;
        if ($booking->request_log_id) {
            $log = RequestLog::find($booking->request_log_id);
        }

        $tours = Tour::orderBy('priority')->orderBy('id')->get();
        $tiers = Tier::orderBy('id')->get();
        $drivers = Driver::where('status', 'active')->orderBy('name')->get();
        $vehicles = Vehicle::where('status', 'active')->orderBy('model_name')->get();

        return view('admin.bookings.show', compact('booking', 'log', 'tours', 'tiers', 'drivers', 'vehicles'));
    }

    /**
     * Update status or details of a booking.
     */
    public function update(Request $request, string $id)
    {
        $booking = Booking::findOrFail($id);

        $request->validate([
            'status' => 'required|string|in:draft,pending,confirmed,completed,cancelled',
            'payment_status' => 'required|string|in:unpaid,partial,paid,failed,cancelled',
            'balance_due' => 'required|numeric|min:0',
            'name' => 'sometimes|required|string|max:120',
            'email' => 'sometimes|required|email|max:150',
            'phone' => 'sometimes|required|string|max:50',
            'tour_id' => 'nullable|exists:tours,id',
            'tier_id' => 'nullable|exists:tiers,id',
            'tour_date' => 'sometimes|required|date',
            'pickup_time' => 'nullable|string|max:50',
            'adults' => 'sometimes|required|integer|min:1|max:500',
            'children' => 'nullable|integer|min:0|max:500',
            'infants' => 'nullable|integer|min:0|max:500',
            'payment_method' => 'nullable|string|max:50',
            'total' => 'nullable|numeric|min:0',
            'payment_amount' => 'nullable|numeric|min:0',
            'pickup_location' => 'nullable|string|max:500',
            'special_requests' => 'nullable|string|max:2000',
            'driver_id' => 'nullable|exists:drivers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'pickup_time_slot' => 'nullable|string|max:100',
            'pickup_status' => 'nullable|in:scheduled,en_route,arrived,picked_up,completed,no_show',
            'driver_notes' => 'nullable|string|max:1000',
        ]);

        $total = $request->filled('total') ? round((float) $request->input('total'), 2) : (float) $booking->total;
        $paymentStatus = $request->input('payment_status');
        $balanceDue = round((float) $request->input('balance_due'), 2);
        $paymentAmount = $request->filled('payment_amount')
            ? round((float) $request->input('payment_amount'), 2)
            : (float) $booking->payment_amount;

        if ($paymentStatus === 'paid') {
            $balanceDue = 0.00;
            $paymentAmount = $total;
        } elseif ($paymentStatus === 'unpaid') {
            $balanceDue = $total;
            $paymentAmount = 0.00;
        } else {
            if (! $request->filled('payment_amount')) {
                $paymentAmount = max(0, round($total - $balanceDue, 2));
            }
        }

        $oldStatus = $booking->status;

        $updateData = [
            'status' => $request->input('status'),
            'payment_status' => $paymentStatus,
            'total' => $total,
            'payment_amount' => $paymentAmount,
            'balance_due' => $balanceDue,
        ];

        if ($request->has('name')) {
            $updateData['name'] = trim((string) $request->input('name'));
        }
        if ($request->has('email')) {
            $updateData['email'] = strtolower(trim((string) $request->input('email')));
        }
        if ($request->has('phone')) {
            $updateData['phone'] = trim((string) $request->input('phone'));
        }
        if ($request->filled('tour_id')) {
            $newTour = Tour::find($request->input('tour_id'));
            if ($newTour) {
                $updateData['tour_id'] = $newTour->id;
                $updateData['tour_name'] = $newTour->name;
            }
        }
        if ($request->filled('tier_id')) {
            $newTier = Tier::find($request->input('tier_id'));
            if ($newTier) {
                $updateData['tier_id'] = $newTier->id;
                $updateData['tier_name'] = $newTier->display_name ?: $newTier->name;
            }
        }
        if ($request->has('tour_date')) {
            $updateData['tour_date'] = $request->input('tour_date');
        }
        if ($request->has('adults')) {
            $updateData['adults'] = max(1, (int) $request->input('adults'));
        }
        if ($request->has('children')) {
            $updateData['children'] = max(0, (int) $request->input('children'));
        }
        if ($request->has('infants')) {
            $updateData['infants'] = max(0, (int) $request->input('infants'));
        }
        if ($request->filled('payment_method')) {
            $updateData['payment_method'] = $request->input('payment_method');
        }
        if ($request->has('pickup_location')) {
            $updateData['pickup_location'] = $request->input('pickup_location');
        }
        if ($request->has('special_requests')) {
            $updateData['special_requests'] = $request->input('special_requests');
        }

        if ($request->has('driver_id')) {
            $updateData['driver_id'] = $request->input('driver_id') ?: null;
            if ($request->filled('driver_id') && empty($booking->driver_assigned_at)) {
                $updateData['driver_assigned_at'] = now();
            }
        }
        if ($request->has('vehicle_id')) {
            $updateData['vehicle_id'] = $request->input('vehicle_id') ?: null;
        } elseif ($request->filled('driver_id')) {
            $assignedDriver = Driver::find($request->input('driver_id'));
            if ($assignedDriver && $assignedDriver->default_vehicle_id) {
                $updateData['vehicle_id'] = $assignedDriver->default_vehicle_id;
            }
        }
        if ($request->filled('pickup_time_slot')) {
            $updateData['pickup_time_slot'] = $request->input('pickup_time_slot');
        }
        if ($request->filled('pickup_status')) {
            $updateData['pickup_status'] = $request->input('pickup_status');
        }
        if ($request->has('driver_notes')) {
            $updateData['driver_notes'] = $request->input('driver_notes');
        }

        // Keep [DISPATCH: ...] synchronized if driver is assigned
        $effectiveDriverId = array_key_exists('driver_id', $updateData) ? $updateData['driver_id'] : $booking->driver_id;
        $effectiveVehicleId = array_key_exists('vehicle_id', $updateData) ? $updateData['vehicle_id'] : $booking->vehicle_id;
        $effectivePickupTime = array_key_exists('pickup_time_slot', $updateData) ? $updateData['pickup_time_slot'] : ($booking->pickup_time_slot ?: $booking->pickup_time);
        
        if ($effectiveDriverId) {
            $drv = Driver::find($effectiveDriverId);
            $veh = $effectiveVehicleId ? Vehicle::find($effectiveVehicleId) : null;
            $dispInfo = array_filter([
                $effectivePickupTime ? 'Pickup: ' . $effectivePickupTime : null,
                $drv ? 'Driver: ' . $drv->name : null,
                $drv ? 'Phone: ' . $drv->phone : null,
                $veh ? 'Plate: ' . $veh->short_plate : null,
            ]);
            $baseNotes = array_key_exists('special_requests', $updateData) ? ($updateData['special_requests'] ?? '') : ($booking->special_requests ?? '');
            $cleanNotes = preg_replace('/\[DISPATCH:.*?\]/s', '', $baseNotes);
            $updateData['special_requests'] = trim($cleanNotes . "\n[DISPATCH: " . implode(' | ', $dispInfo) . ']');
        }

        $booking->update($updateData);
        Cache::forget('admin_dashboard_kpis');

        // If status changed to confirmed or cancelled, trigger notifications
        if ($oldStatus !== $booking->status) {
            try {
                if (! app()->environment('testing')) {
                    try {
                        app(EmailMarketingService::class)->applySmtpConfig();
                    } catch (\Throwable $e) {
                    }
                }
                $settings = app(SettingsService::class);
                $fromEmail = $settings->getFromEmail();

                if ($booking->status === 'confirmed') {
                    Mail::to($booking->email)->send(
                        (new BookingNotification('booking_confirmed', $booking))->from($fromEmail, 'Dunes Discovery Tourism')
                    );
                } elseif ($booking->status === 'cancelled') {
                    Mail::to($booking->email)->send(
                        (new BookingNotification('booking_cancelled', $booking))->from($fromEmail, 'Dunes Discovery Tourism')
                    );
                }
            } catch (\Throwable $e) {
                \Log::error('Failed to send booking status change email: '.$e->getMessage());
            }
        }

        return redirect()->route('admin.bookings.show', $booking->id)->with('success', 'Booking updated successfully.');
    }

    /**
     * Permanently delete a booking and all associated relational & analytical data.
     */
    public function destroy(Request $request, string $id)
    {
        $booking = Booking::withTrashed()->findOrFail($id);
        $reference = $booking->reference;

        try {
            $this->purgeBookingData($booking);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Booking #{$reference} and all associated records permanently purged.",
                ]);
            }

            return redirect()->route('admin.bookings.index')
                ->with('success', "Booking #{$reference} and all associated records permanently purged.");
        } catch (\Throwable $e) {
            Log::error("Failed to permanently delete booking #{$reference}: ".$e->getMessage(), [
                'booking_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete booking: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to delete booking: '.$e->getMessage());
        }
    }

    /**
     * Execute complete cascade purge of all records and relations for a booking.
     */
    private function purgeBookingData(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {
            $bookingId = $booking->id;
            $bookingRef = $booking->reference;
            $logId = $booking->request_log_id;
            $couponId = $booking->coupon_id;

            // 1. Delete all booking addons
            BookingAddon::where('booking_id', $bookingId)->delete();

            // 2. Delete all booking payment history
            BookingPayment::where('booking_id', $bookingId)->forceDelete();

            // 3. Handle Coupon Usage & Quota Restoration
            $couponUsage = CouponUsage::where('booking_id', $bookingId)
                ->orWhere('booking_reference', $bookingRef)
                ->first();
            if ($couponUsage) {
                if ($couponUsage->coupon_id) {
                    Coupon::where('id', $couponUsage->coupon_id)
                        ->where('used_count', '>', 0)
                        ->decrement('used_count');
                }
                $couponUsage->delete();
            } elseif ($couponId) {
                Coupon::where('id', $couponId)
                    ->where('used_count', '>', 0)
                    ->decrement('used_count');
            }

            // 4. Delete Reviews directly linked to this booking & cleanup storage photos
            $reviews = Review::where('booking_id', $bookingId)->get();
            foreach ($reviews as $review) {
                if (! empty($review->photos) && is_array($review->photos)) {
                    foreach ($review->photos as $photo) {
                        if (is_string($photo) && str_starts_with($photo, '/storage/')) {
                            $relativePath = str_replace('/storage/', '', $photo);
                            Storage::disk('public')->delete($relativePath);
                        }
                    }
                }
                $review->delete();
            }

            // 5. Delete Analytical and Telemetry Data (request_logs)
            if ($logId) {
                RequestLog::where('id', $logId)->delete();
            }
            RequestLog::where('entity_type', 'booking')
                ->where('entity_id', $bookingId)
                ->delete();

            // 6. Forget draft session if this was the active session draft
            if (session('active_booking_draft_id') == $bookingId) {
                session()->forget('active_booking_draft_id');
            }

            // 7. Permanently Delete the Booking record itself from the database
            $booking->forceDelete();

            // 8. Flush Dashboard KPI Cache
            Cache::forget('admin_dashboard_kpis');
        });
    }

    /**
     * Create a payment link for a booking.
     */
    public function createPaymentLink(Request $request, string $id)
    {
        $booking = Booking::findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:255',
            'send_method' => 'required|string|in:none,whatsapp,email',
        ]);

        $amount = (float) $request->input('amount');
        $notes = trim($request->input('notes', ''));
        $sendMethod = $request->input('send_method');

        $ziina = app(ZiinaPaymentService::class);

        $successUrl = route('booking.thankyou', ['pi' => '{PAYMENT_INTENT_ID}']);
        $cancelUrl = route('booking.cancel', ['pi' => '{PAYMENT_INTENT_ID}']);

        $description = 'Booking '.$booking->reference;
        if (! empty($notes)) {
            $description .= ' '.$notes;
        }

        $intent = $ziina->createPaymentIntent($amount, 'AED', $successUrl, $cancelUrl, $description);

        if (isset($intent['error'])) {
            return response()->json(['success' => false, 'message' => $intent['error']], 400);
        }

        if (empty($intent['redirect_url']) || empty($intent['id'])) {
            return response()->json(['success' => false, 'message' => 'Payment link could not be generated.'], 400);
        }

        $payment = BookingPayment::create([
            'booking_id' => $booking->id,
            'payment_intent_id' => $intent['id'],
            'amount' => $amount,
            'currency' => 'AED',
            'status' => $intent['status'] ?? 'pending',
            'payment_url' => $intent['redirect_url'],
            'notes' => $notes,
        ]);

        $message = 'Payment link created successfully.';

        if ($sendMethod === 'whatsapp') {
            $defaultCountry = Setting::where('setting_key', 'whatsapp_default_country')->value('setting_value') ?? '971';
            $phone = preg_replace('/[^0-9]/', '', $booking->phone);
            if (substr($phone, 0, strlen($defaultCountry)) !== $defaultCountry) {
                $phone = $defaultCountry.ltrim($phone, '0');
            }
            $text = "Hello {$booking->name}, please use this link to complete your payment for booking #{$booking->reference}: {$intent['redirect_url']}";
            $whatsappUrl = 'https://wa.me/'.$phone.'?text='.urlencode($text);

            return response()->json([
                'success' => true,
                'message' => 'Link created. Redirecting to WhatsApp...',
                'redirect_url' => $whatsappUrl,
                'payment' => [
                    'created_at' => $payment->created_at->format('Y-m-d H:i'),
                    'amount' => number_format($amount),
                    'status' => $payment->status,
                    'link' => $payment->payment_url,
                    'notes' => $notes,
                ],
            ]);
        } elseif ($sendMethod === 'email') {
            try {
                if (! app()->environment('testing')) {
                    try {
                        app(EmailMarketingService::class)->applySmtpConfig();
                    } catch (\Throwable $e) {
                    }
                }
                $fromEmail = app(SettingsService::class)->getFromEmail();
                Mail::to($booking->email)->send(
                    (new PaymentLinkMail($booking, $amount, $payment->payment_url, $notes))->from($fromEmail, 'Dunes Discovery Tourism')
                );
                $message = 'Payment link created and email sent successfully.';
            } catch (\Throwable $e) {
                \Log::error('Failed to send payment link email: '.$e->getMessage());
                $message = 'Payment link created but email sending failed.';
            }
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'payment' => [
                'created_at' => $payment->created_at->format('Y-m-d H:i'),
                'amount' => number_format($amount),
                'status' => $payment->status,
                'link' => $payment->payment_url,
                'notes' => $notes,
            ],
        ]);
    }

    /**
     * Resend an existing payment link via email.
     */
    public function resendPaymentEmail(Request $request, string $id)
    {
        $booking = Booking::findOrFail($id);

        $request->validate([
            'link' => 'required|url',
            'amount' => 'required|numeric',
        ]);

        $link = $request->input('link');
        $amount = (float) $request->input('amount');

        try {
            if (! app()->environment('testing')) {
                try {
                    app(EmailMarketingService::class)->applySmtpConfig();
                } catch (\Throwable $e) {
                }
            }
            $fromEmail = app(SettingsService::class)->getFromEmail();
            Mail::to($booking->email)->send(
                (new PaymentLinkMail($booking, $amount, $link, ''))->from($fromEmail, 'Dunes Discovery Tourism')
            );

            return response()->json(['success' => true, 'message' => 'Email sent successfully.']);
        } catch (\Throwable $e) {
            \Log::error('Failed to resend payment email: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to send email.'], 500);
        }
    }

    /**
     * Resend the booking confirmation / voucher email to the customer.
     */
    public function resendConfirmationEmail(Request $request, string $id)
    {
        $booking = Booking::with('addons')->findOrFail($id);

        if (empty($booking->email)) {
            return response()->json(['success' => false, 'message' => 'No customer email address on file.'], 422);
        }

        try {
            try {
                app(EmailMarketingService::class)->applySmtpConfig();
            } catch (\Throwable $e) {}

            $fromEmail = app(SettingsService::class)->getFromEmail();
            $emailType = ($booking->status === 'cancelled') ? 'booking_cancelled' : 'booking_confirmed';

            Mail::to($booking->email)->send(
                (new BookingNotification($emailType, $booking))->from($fromEmail, 'Dunes Discovery Tourism')
            );

            return response()->json([
                'success' => true,
                'message' => 'Booking confirmation email resent successfully to '.$booking->email,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to resend confirmation email: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to send confirmation email: '.$e->getMessage()], 500);
        }
    }

    /**
     * Perform batch operations across multiple bookings.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:bookings,id',
            'action' => 'required|string|in:status_confirmed,status_completed,status_cancelled,status_pending,delete',
        ]);

        $ids = $request->input('ids');
        $action = $request->input('action');

        try {
            if ($action === 'delete') {
                $count = 0;
                DB::transaction(function () use ($ids, &$count) {
                    $bookings = Booking::withTrashed()->whereIn('id', $ids)->get();
                    foreach ($bookings as $booking) {
                        $this->purgeBookingData($booking);
                        $count++;
                    }
                });

                Cache::forget('admin_dashboard_kpis');

                return response()->json([
                    'success' => true,
                    'message' => "{$count} booking(s) and all associated records permanently purged.",
                ]);
            }

            $newStatus = str_replace('status_', '', $action);
            $count = Booking::whereIn('id', $ids)->update(['status' => $newStatus]);
            Cache::forget('admin_dashboard_kpis');

            return response()->json([
                'success' => true,
                'message' => "{$count} booking(s) updated to ".ucfirst($newStatus).' successfully.',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Bulk action failed on bookings: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process bulk action: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download booking voucher / e-ticket as PDF.
     */
    public function downloadTicket(int $id)
    {
        try {
            $booking = Booking::with(['addons', 'tier', 'tour'])->findOrFail($id);

            $verificationUrl = route('booking.voucher', $booking->reference);
            $qrSvg = null;
            $qrCodeUrl = null;

            try {
                $qrSvg = (string) QrCode::format('svg')->size(140)->margin(1)->generate($verificationUrl);
                $qrCodeUrl = 'data:image/svg+xml;base64,'.base64_encode($qrSvg);
            } catch (\Throwable $qrEx) {
                \Log::warning('QR generation failed for booking #'.$booking->reference.': '.$qrEx->getMessage());
            }

            $pdf = Pdf::loadView('booking.ticket-pdf', compact('booking', 'qrCodeUrl', 'qrSvg'))
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isRemoteEnabled' => false,
                    'isHtml5ParserEnabled' => true,
                    'dpi' => 120,
                    'defaultFont' => 'sans-serif',
                ]);

            $fileName = 'Dunes-Voucher-'.$booking->reference.'.pdf';

            return $pdf->download($fileName);
        } catch (\Throwable $e) {
            \Log::error('Admin Booking ticket PDF generation failed: '.$e->getMessage(), [
                'booking_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            $booking = Booking::find($id);
            if ($booking) {
                return redirect()->route('booking.voucher', $booking->reference)
                    ->with('info', 'Viewing digital boarding pass voucher. You can print directly or save as PDF.');
            }

            return redirect()->route('admin.bookings.index')
                ->with('error', 'Booking record not found or could not compile ticket.');
        }
    }

    /**
     * Real-time JSON endpoint for live Bookings KPI refresh without stale cache.
     */
    public function liveStats(Request $request): JsonResponse
    {
        $revenue = (float) Booking::whereIn('status', ['confirmed', 'completed'])
            ->whereIn('payment_status', ['paid', 'partial'])
            ->sum(DB::raw("CASE WHEN payment_amount > 0 THEN payment_amount ELSE (CASE WHEN payment_status = 'paid' THEN total ELSE 0 END) END"));

        $paidCount = Booking::whereIn('status', ['confirmed', 'completed'])
            ->whereIn('payment_status', ['paid', 'partial'])
            ->count();
        $avgOrderValue = $paidCount > 0 ? round($revenue / $paidCount, 2) : 0;
        $addonsRevenue = (float) Booking::whereIn('status', ['confirmed', 'completed'])->sum('addons_total');

        $stats = [
            'total' => Booking::where('status', '!=', 'draft')->count(),
            'pending' => Booking::where('status', 'pending')->count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'completed' => Booking::where('status', 'completed')->count(),
            'confirmed_and_completed' => Booking::whereIn('status', ['confirmed', 'completed'])->count(),
            'drafts' => Booking::where('status', 'draft')->count(),
            'addons_revenue' => $addonsRevenue,
            'revenue' => $revenue,
            'aov' => $avgOrderValue,
            'timestamp' => now()->toIso8601String(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}
