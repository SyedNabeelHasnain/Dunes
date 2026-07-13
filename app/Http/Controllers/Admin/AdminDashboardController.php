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
        $revenue = Booking::whereIn('status', ['confirmed', 'completed'])
            ->whereIn('payment_status', ['paid', 'partial'])
            ->sum('payment_amount');

        $totalBookings = Booking::count();
        $confirmedBookings = Booking::whereIn('status', ['confirmed', 'completed'])->count();
        $pendingBookings = Booking::where('status', 'pending')->count();

        $stats = [
            'revenue' => (float)$revenue,
            'total' => $totalBookings,
            'confirmed' => $confirmedBookings,
            'pending' => $pendingBookings,
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
        $inquiries = Contact::orderBy('id', 'desc')->paginate(20);
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
