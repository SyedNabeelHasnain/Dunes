<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Contact;
use App\Models\RequestLog;
use App\Models\Tour;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Display the Admin CMS Dashboard.
     */
    public function index()
    {
        try {
            $revenue = Booking::whereIn('status', ['confirmed', 'completed'])
                ->whereIn('payment_status', ['paid', 'partial'])
                ->sum('payment_amount');

            $totalBookings = Booking::count();
            $confirmedBookings = Booking::whereIn('status', ['confirmed', 'completed'])->count();
            $pendingBookings = Booking::where('status', 'pending')->count();
            $avgOrderValue = $confirmedBookings > 0 ? round($revenue / $confirmedBookings, 2) : 0;

            // 30-day human visitors count for conversion rate calculation
            $visitorsCount = RequestLog::where('request_timestamp', '>=', now()->subDays(30))
                ->where('bot_indicator', 'Likely Human')
                ->distinct('session_id')
                ->count('session_id') ?: 1;

            $recentBookingsCount = Booking::where('created_at', '>=', now()->subDays(30))->count();
            $conversionRate = round(($recentBookingsCount / $visitorsCount) * 100, 2);

            $stats = [
                'revenue' => (float)$revenue,
                'total' => $totalBookings,
                'confirmed' => $confirmedBookings,
                'pending' => $pendingBookings,
                'aov' => $avgOrderValue,
                'conversion_rate' => $conversionRate,
            ];

            // Recent bookings (latest 10)
            $recentBookings = Booking::orderBy('created_at', 'desc')->limit(10)->get();

            // Top Tours (by booking counts)
            $topTours = Booking::whereIn('status', ['confirmed', 'completed'])
                ->select('tour_name', \DB::raw('COUNT(*) as count'), \DB::raw('SUM(total) as revenue'))
                ->groupBy('tour_name')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get();

            // Recent WhatsApp leads
            $whatsappLeads = \DB::table('whatsapp_inquiries')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            return view('admin.dashboard', compact('stats', 'recentBookings', 'topTours', 'whatsappLeads'));
        } catch (\Throwable $e) {
            \Log::error("Admin dashboard error: " . $e->getMessage());
            $stats = ['revenue' => 0, 'total' => 0, 'confirmed' => 0, 'pending' => 0];
            $recentBookings = collect();
            $topTours = collect();
            $whatsappLeads = collect();
            return view('admin.dashboard', compact('stats', 'recentBookings', 'topTours', 'whatsappLeads'));
        }
    }

    /**
     * Display Analytics & Traffic Intelligence Dashboard.
     */
    public function analytics(Request $request)
    {
        try {
            $days = (int)$request->input('days', 30);
            $startDate = now()->subDays($days)->startOfDay();

            // 1. Core Traffic Stats
            $totalPageviews = RequestLog::where('request_timestamp', '>=', $startDate)->count();
            $humanPageviews = RequestLog::where('request_timestamp', '>=', $startDate)
                ->where('bot_indicator', 'Likely Human')
                ->count();
            $uniqueVisitors = RequestLog::where('request_timestamp', '>=', $startDate)
                ->where('bot_indicator', 'Likely Human')
                ->distinct('session_id')
                ->count('session_id');
            $uniqueIPs = RequestLog::where('request_timestamp', '>=', $startDate)
                ->where('bot_indicator', 'Likely Human')
                ->distinct('client_ip')
                ->count('client_ip');

            // 2. Top Visited Pages
            $topPages = RequestLog::where('request_timestamp', '>=', $startDate)
                ->where('bot_indicator', 'Likely Human')
                ->select('request_uri', \DB::raw('COUNT(*) as views'), \DB::raw('COUNT(DISTINCT session_id) as visitors'))
                ->groupBy('request_uri')
                ->orderBy('views', 'desc')
                ->limit(10)
                ->get();

            // 3. Top Countries
            $topCountries = RequestLog::where('request_timestamp', '>=', $startDate)
                ->where('bot_indicator', 'Likely Human')
                ->where('country', '!=', 'Not Available')
                ->select('country', \DB::raw('COUNT(*) as count'))
                ->groupBy('country')
                ->orderBy('count', 'desc')
                ->limit(8)
                ->get();

            // 4. Traffic Sources Channel Grouping
            $trafficSources = RequestLog::where('request_timestamp', '>=', $startDate)
                ->where('bot_indicator', 'Likely Human')
                ->select(
                    \DB::raw("CASE 
                        WHEN utm_source IS NOT NULL AND utm_source != '' AND utm_source != 'Not Available' THEN utm_source
                        WHEN query_string LIKE '%gclid%' OR query_string LIKE '%gad_source%' THEN 'Google Ads'
                        WHEN referrer_url LIKE '%google%' THEN 'Google Organic'
                        WHEN referrer_url LIKE '%facebook%' OR referrer_url LIKE '%fb.com%' THEN 'Facebook'
                        WHEN referrer_url LIKE '%instagram%' THEN 'Instagram'
                        WHEN referrer_url LIKE '%wa.me%' OR referrer_url LIKE '%whatsapp%' THEN 'WhatsApp'
                        WHEN referrer_url LIKE '%bing%' THEN 'Bing Organic'
                        WHEN referrer_url LIKE '%tiktok%' THEN 'TikTok'
                        WHEN referrer_url IS NULL OR referrer_url = '' OR referrer_url = 'Not Available' OR referrer_url = 'direct' THEN 'Direct / Bookmark'
                        ELSE 'External Referral'
                    END as channel"),
                    \DB::raw('COUNT(*) as views'),
                    \DB::raw('COUNT(DISTINCT session_id) as visitors')
                )
                ->groupBy('channel')
                ->orderBy('views', 'desc')
                ->get();

            // 5. Top Referring Domains & URLs
            $topReferrers = RequestLog::where('request_timestamp', '>=', $startDate)
                ->where('bot_indicator', 'Likely Human')
                ->whereNotNull('referrer_url')
                ->where('referrer_url', '!=', 'Not Available')
                ->where('referrer_url', '!=', '')
                ->where('referrer_url', '!=', 'direct')
                ->select('referrer_url as referrer', \DB::raw('COUNT(*) as views'), \DB::raw('COUNT(DISTINCT session_id) as visitors'))
                ->groupBy('referrer_url')
                ->orderBy('views', 'desc')
                ->limit(15)
                ->get();

            // 6. Campaign & UTM Attribution
            $campaigns = RequestLog::where('request_timestamp', '>=', $startDate)
                ->where('bot_indicator', 'Likely Human')
                ->whereNotNull('utm_campaign')
                ->where('utm_campaign', '!=', 'Not Available')
                ->where('utm_campaign', '!=', '')
                ->select('utm_campaign', 'utm_source', 'utm_medium', \DB::raw('COUNT(*) as views'), \DB::raw('COUNT(DISTINCT session_id) as visitors'))
                ->groupBy('utm_campaign', 'utm_source', 'utm_medium')
                ->orderBy('views', 'desc')
                ->limit(10)
                ->get();

            // 7. Daily Traffic Trend (Time-Series for Chart.js)
            $dailyTrend = RequestLog::where('request_timestamp', '>=', $startDate)
                ->select(
                    \DB::raw('DATE(request_timestamp) as date'),
                    \DB::raw('COUNT(*) as total_views'),
                    \DB::raw("SUM(CASE WHEN bot_indicator = 'Likely Human' THEN 1 ELSE 0 END) as human_views"),
                    \DB::raw("COUNT(DISTINCT CASE WHEN bot_indicator = 'Likely Human' THEN session_id ELSE NULL END) as human_sessions")
                )
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            // 8. Device & Browser Breakdown
            $devices = RequestLog::where('request_timestamp', '>=', $startDate)
                ->where('bot_indicator', 'Likely Human')
                ->select('device_type', \DB::raw('COUNT(*) as count'))
                ->groupBy('device_type')
                ->orderBy('count', 'desc')
                ->get();

            $browsers = RequestLog::where('request_timestamp', '>=', $startDate)
                ->where('bot_indicator', 'Likely Human')
                ->select('browser_name', \DB::raw('COUNT(*) as count'))
                ->groupBy('browser_name')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get();

            // 9. Recent Request Logs (Paginated)
            $logs = RequestLog::orderBy('request_timestamp', 'desc')->paginate(25);

            return view('admin.analytics.index', compact(
                'days', 'totalPageviews', 'humanPageviews', 'uniqueVisitors', 'uniqueIPs',
                'topPages', 'topCountries', 'trafficSources', 'topReferrers', 'campaigns', 'dailyTrend',
                'devices', 'browsers', 'logs'
            ));
        } catch (\Throwable $e) {
            return response("ANALYTICS ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString(), 500)
                ->header('Content-Type', 'text/plain');
        }
    }

    /**
     * Polling endpoint for active visitors in the last 5 minutes.
     */
    public function activeVisitors(): JsonResponse
    {
        $window = now()->subMinutes(5)->toDateTimeString();

        $count = RequestLog::where('request_timestamp', '>=', $window)
            ->where('bot_indicator', 'Likely Human')
            ->distinct('session_id')
            ->count('session_id');

        $visitors = RequestLog::where('request_timestamp', '>=', $window)
            ->where('bot_indicator', 'Likely Human')
            ->select('session_id', 'client_ip', 'user_agent', 'country', 'city', 'request_uri', 'request_timestamp', 'device_type', 'browser_name', 'os_name')
            ->orderBy('request_timestamp', 'desc')
            ->get()
            ->unique('session_id')
            ->values();

        return response()->json([
            'count' => $count,
            'visitors' => $visitors
        ]);
    }

    /**
     * List Contact Inquiries.
     */
    public function inquiries()
    {
        $inquiries = Contact::orderBy('id', 'desc')->get();
        return view('admin.inquiries.index', compact('inquiries'));
    }

    /**
     * View Specific Contact Inquiry.
     */
    public function viewInquiry(int $id)
    {
        $inquiry = Contact::findOrFail($id);
        
        // Mark as read/viewed if pending
        if ($inquiry->status === 'new') {
            $inquiry->update(['status' => 'read']);
        }

        // Fetch associated request logs
        $log = null;
        if ($inquiry->request_log_id) {
            $log = RequestLog::find($inquiry->request_log_id);
        }

        return view('admin.inquiries.show', compact('inquiry', 'log'));
    }

    /**
     * Create a Quick Payment via Ziina.
     */
    public function createQuickPayment(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:1',
        ]);

        $name = trim($request->input('name'));
        $email = trim(strtolower($request->input('email')));
        $phone = trim($request->input('phone'));
        $description = trim($request->input('description'));
        $amount = (float)$request->input('amount');

        $ziina = app(\App\Services\ZiinaPaymentService::class);

        $successUrl = route('booking.thankyou', ['pi' => '{PAYMENT_INTENT_ID}']);
        $cancelUrl = route('booking.cancel', ['pi' => '{PAYMENT_INTENT_ID}']);
        $fullDescription = $description ?: 'Quick Payment for ' . $name;

        $intent = $ziina->createPaymentIntent($amount, 'AED', $successUrl, $cancelUrl, $fullDescription);

        if (isset($intent['error'])) {
            return response()->json(['success' => false, 'message' => $intent['error']], 400);
        }

        \App\Models\BookingPayment::create([
            'booking_id' => null,
            'payment_intent_id' => $intent['id'],
            'amount' => $amount,
            'currency' => 'AED',
            'status' => $intent['status'] ?? 'pending',
            'payment_url' => $intent['redirect_url'],
            'notes' => $description,
            'customer_name' => $name,
            'customer_email' => $email,
            'customer_phone' => $phone,
            'description' => $description
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Quick payment link created successfully!',
            'payment' => [
                'link' => $intent['redirect_url'],
                'amount' => $amount,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'status' => $intent['status'] ?? 'pending',
                'notes' => $description
            ]
        ]);
    }

    /**
     * Update status of an inquiry.
     */
    public function updateInquiryStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|string|in:new,read,replied'
        ]);

        $inquiry = Contact::findOrFail($id);
        $inquiry->update(['status' => $request->input('status')]);

        return redirect()->route('admin.inquiries.index')->with('success', 'Inquiry status updated successfully.');
    }

    /**
     * Delete an inquiry.
     */
    public function deleteInquiry(int $id)
    {
        $inquiry = Contact::findOrFail($id);
        $inquiry->delete();

        return redirect()->route('admin.inquiries.index')->with('success', 'Inquiry deleted successfully.');
    }
}
