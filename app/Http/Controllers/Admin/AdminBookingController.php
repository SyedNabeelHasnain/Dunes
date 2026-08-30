<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\RequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingNotification;
use App\Mail\PaymentLinkMail;
use App\Services\SettingsService;

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
            $query->where(function($q) use ($search) {
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

        $revenue = (float)Booking::whereIn('status', ['confirmed', 'completed'])->sum('payment_amount');
        $confirmedCount = Booking::whereIn('status', ['confirmed', 'completed'])->count();
        $avgOrderValue = $confirmedCount > 0 ? round($revenue / $confirmedCount, 2) : 0;
        $addonsRevenue = (float)Booking::whereIn('status', ['confirmed', 'completed'])->sum('addons_total');

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
        $trendData = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $label = now()->subDays($i)->format('M j');
            $count = Booking::whereDate('created_at', $date)->where('status', '!=', 'draft')->count();
            $dayRev = (float)Booking::whereDate('created_at', $date)->whereIn('status', ['confirmed', 'completed'])->sum('payment_amount');
            $trendData[] = [
                'date' => $label,
                'count' => $count,
                'revenue' => $dayRev,
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
     * Export bookings to CSV.
     */
    public function exportCsv(Request $request)
    {
        $fileName = 'dunes-bookings-export-' . date('Y-m-d-His') . '.csv';
        $query = Booking::with(['tour', 'tier'])->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('reference', 'like', "%{$s}%")
                  ->orWhere('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = [
            'Reference', 'Created At', 'Customer Name', 'Email', 'Phone',
            'Tour Name', 'Package Tier', 'Tour Date', 'Pickup Time', 'Adults',
            'Children', 'Infants', 'Pickup Location', 'Total (AED)', 'Paid (AED)',
            'Balance Due (AED)', 'Payment Method', 'Payment Status', 'Booking Status'
        ];

        $callback = function() use ($query, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            $query->chunk(100, function($rows) use ($file) {
                foreach ($rows as $b) {
                    fputcsv($file, [
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
                        $b->status
                    ]);
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
        $booking = Booking::with(['tour', 'tier', 'addons', 'payments'])->findOrFail($id);
        
        $log = null;
        if ($booking->request_log_id) {
            $log = RequestLog::find($booking->request_log_id);
        }

        return view('admin.bookings.show', compact('booking', 'log'));
    }

    /**
     * Update status or details of a booking.
     */
    public function update(Request $request, string $id)
    {
        $booking = Booking::findOrFail($id);

        $request->validate([
            'status' => 'required|string|in:pending,confirmed,completed,cancelled',
            'payment_status' => 'required|string|in:unpaid,partial,paid,failed,cancelled',
            'balance_due' => 'required|numeric|min:0',
        ]);

        $oldStatus = $booking->status;
        $booking->update($request->only(['status', 'payment_status', 'balance_due', 'special_requests', 'pickup_location']));

        // If status changed to confirmed or cancelled, trigger notifications
        if ($oldStatus !== $booking->status) {
            try {
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
                \Log::error("Failed to send booking status change email: " . $e->getMessage());
            }
        }

        return redirect()->route('admin.bookings.show', $booking->id)->with('success', 'Booking updated successfully.');
    }

    /**
     * Delete a booking.
     */
    public function destroy(string $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();
        return redirect()->route('admin.bookings.index')->with('success', 'Booking deleted successfully.');
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

        $amount = (float)$request->input('amount');
        $notes = trim($request->input('notes', ''));
        $sendMethod = $request->input('send_method');

        $ziina = app(\App\Services\ZiinaPaymentService::class);

        $successUrl = route('booking.thankyou', ['pi' => '{PAYMENT_INTENT_ID}']);
        $cancelUrl = route('booking.cancel', ['pi' => '{PAYMENT_INTENT_ID}']);
        
        $description = 'Booking #' . $booking->reference;
        if ($booking->tour_name) {
            $description .= ' - ' . $booking->tour_name;
        }
        if ($notes) {
            $description .= ' (' . $notes . ')';
        }

        $intent = $ziina->createPaymentIntent($amount, 'AED', $successUrl, $cancelUrl, $description);

        if (isset($intent['error'])) {
            return response()->json(['success' => false, 'message' => $intent['error']], 400);
        }

        $payment = \App\Models\BookingPayment::create([
            'booking_id' => $booking->id,
            'payment_intent_id' => $intent['id'],
            'amount' => $amount,
            'currency' => 'AED',
            'status' => $intent['status'] ?? 'pending',
            'payment_url' => $intent['redirect_url'],
            'notes' => $notes
        ]);

        $message = 'Payment link created successfully.';

        if ($sendMethod === 'whatsapp') {
            $defaultCountry = \App\Models\Setting::where('setting_key', 'whatsapp_default_country')->value('setting_value') ?? '971';
            $phone = preg_replace('/[^0-9]/', '', $booking->phone);
            if (substr($phone, 0, strlen($defaultCountry)) !== $defaultCountry) {
                $phone = $defaultCountry . ltrim($phone, '0');
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
                    'notes' => $notes
                ]
            ]);
        } elseif ($sendMethod === 'email') {
            try {
                $fromEmail = app(SettingsService::class)->getFromEmail();
                Mail::to($booking->email)->send(
                    (new PaymentLinkMail($booking, $amount, $payment->payment_url, $notes))->from($fromEmail, 'Dunes Discovery Tourism')
                );
                $message = 'Payment link created and email sent successfully.';
            } catch (\Throwable $e) {
                \Log::error("Failed to send payment link email: " . $e->getMessage());
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
                'notes' => $notes
            ]
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
        $amount = (float)$request->input('amount');

        try {
            $fromEmail = app(SettingsService::class)->getFromEmail();
            Mail::to($booking->email)->send(
                (new PaymentLinkMail($booking, $amount, $link, ''))->from($fromEmail, 'Dunes Discovery Tourism')
            );
            return response()->json(['success' => true, 'message' => 'Email sent successfully.']);
        } catch (\Throwable $e) {
            \Log::error("Failed to resend payment email: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send email.'], 500);
        }
    }
}
