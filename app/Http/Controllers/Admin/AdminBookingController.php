<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\RequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminBookingController extends Controller
{
    /**
     * Display a listing of bookings.
     */
    public function index(Request $request)
    {
        $status = $request->input('status');
        $query = Booking::with('tour');

        if ($status) {
            $query->where('status', $status);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        return view('admin.bookings.index', compact('bookings', 'status'));
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
            $brandColor = '#F58F43';
            $siteEmailSetting = \App\Models\Setting::where('setting_key', 'site_email')->first();
            $fromEmail = $siteEmailSetting ? $siteEmailSetting->setting_value : 'info@dunesdiscoverytourism.com';

            if ($booking->status === 'confirmed') {
                // Send booking confirmation email
                $subject = "Booking Confirmed - Ref: {$booking->reference}";
                $bodyContent = "
                    <h1 style='color:{$brandColor};'>Booking Confirmed!</h1>
                    <p>Dear {$booking->name},</p>
                    <p>Your booking for <strong>{$booking->tour_name}</strong> is officially confirmed!</p>
                    <div style='background-color:#f9f9f9;padding:15px;border-radius:5px;margin:20px 0;'>
                        <p><strong>Reference:</strong> {$booking->reference}</p>
                        <p><strong>Date:</strong> " . ($booking->tour_date ? $booking->tour_date->format('Y-m-d') : '') . "</p>
                        <p><strong>Pickup:</strong> {$booking->pickup_location}</p>
                        <p><strong>Total:</strong> AED {$booking->total}</p>
                        <p><strong>Balance Due:</strong> AED {$booking->balance_due}</p>
                    </div>";

                Mail::send([], [], function ($message) use ($booking, $subject, $bodyContent, $fromEmail, $brandColor) {
                    $message->to($booking->email)
                        ->from($fromEmail, 'Dunes Discovery Tourism')
                        ->subject($subject)
                        ->html("
                            <div style='text-align:center;margin:0 auto;padding:20px;'><img src='https://dunesdiscoverytourism.com/images/logo.png' alt='Dunes Logo' style='max-width:180px;'/></div>
                            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;'>
                                <div style='background-color:{$brandColor};padding:20px;text-align:center;'><h2 style='color:white;margin:0;'>Dunes Discovery</h2></div>
                                <div style='padding:20px;background-color:#ffffff;'>{$bodyContent}</div>
                            </div>");
                });
            } elseif ($booking->status === 'cancelled') {
                // Send cancellation email
                $subject = "Booking Cancelled - Ref: {$booking->reference}";
                $bodyContent = "
                    <h1>Booking Cancelled</h1>
                    <p>Dear {$booking->name},</p>
                    <p>Your booking for <strong>{$booking->tour_name}</strong> (Ref: {$booking->reference}) has been cancelled as requested.</p>";

                Mail::send([], [], function ($message) use ($booking, $subject, $bodyContent, $fromEmail, $brandColor) {
                    $message->to($booking->email)
                        ->from($fromEmail, 'Dunes Discovery Tourism')
                        ->subject($subject)
                        ->html("
                            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;'>
                                <div style='background-color:{$brandColor};padding:20px;text-align:center;'><h2 style='color:white;margin:0;'>Dunes Discovery</h2></div>
                                <div style='padding:20px;background-color:#ffffff;'>{$bodyContent}</div>
                            </div>");
                });
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
                $brandColor = '#F58F43';
                $siteEmailSetting = \App\Models\Setting::where('setting_key', 'site_email')->first();
                $fromEmail = $siteEmailSetting ? $siteEmailSetting->setting_value : 'info@dunesdiscoverytourism.com';

                $subject = "Payment Link for Booking #{$booking->reference}";
                $bodyContent = "
                    <h1 style='color:{$brandColor};'>Payment Link Generated</h1>
                    <p>Dear {$booking->name},</p>
                    <p>A payment link has been generated to complete your payment for <strong>{$booking->tour_name}</strong>.</p>
                    <div style='background-color:#f9f9f9;padding:15px;border-radius:5px;margin:20px 0;text-align:center;'>
                        <p><strong>Amount:</strong> AED " . number_format($amount) . "</p>
                        " . ($notes ? "<p><strong>Notes:</strong> {$notes}</p>" : "") . "
                        <p style='margin-top: 15px;'>
                            <a href='{$payment->payment_url}' style='background-color:{$brandColor};color:white;padding:10px 20px;text-decoration:none;font-weight:bold;border-radius:5px;display:inline-block;'>Pay Now</a>
                        </p>
                    </div>";

                Mail::send([], [], function ($msg) use ($booking, $subject, $bodyContent, $fromEmail, $brandColor) {
                    $msg->to($booking->email)
                        ->from($fromEmail, 'Dunes Discovery Tourism')
                        ->subject($subject)
                        ->html("
                            <div style='text-align:center;margin:0 auto;padding:20px;'><img src='https://dunesdiscoverytourism.com/images/logo.png' alt='Dunes Logo' style='max-width:180px;'/></div>
                            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;'>
                                <div style='background-color:{$brandColor};padding:20px;text-align:center;'><h2 style='color:white;margin:0;'>Dunes Discovery</h2></div>
                                <div style='padding:20px;background-color:#ffffff;'>{$bodyContent}</div>
                            </div>");
                });
                $message = 'Payment link created and email sent successfully.';
            } catch (\Exception $e) {
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
            $brandColor = '#F58F43';
            $siteEmailSetting = \App\Models\Setting::where('setting_key', 'site_email')->first();
            $fromEmail = $siteEmailSetting ? $siteEmailSetting->setting_value : 'info@dunesdiscoverytourism.com';

            $subject = "Complete Payment for Booking #{$booking->reference}";
            $bodyContent = "
                <h1 style='color:{$brandColor};'>Complete Your Payment</h1>
                <p>Dear {$booking->name},</p>
                <p>Please use the link below to complete your payment of <strong>AED " . number_format($amount) . "</strong> for your booking <strong>{$booking->tour_name}</strong>.</p>
                <div style='background-color:#f9f9f9;padding:15px;border-radius:5px;margin:20px 0;text-align:center;'>
                    <p style='margin-top: 15px;'>
                        <a href='{$link}' style='background-color:{$brandColor};color:white;padding:10px 20px;text-decoration:none;font-weight:bold;border-radius:5px;display:inline-block;'>Pay Now</a>
                    </p>
                </div>";

            Mail::send([], [], function ($msg) use ($booking, $subject, $bodyContent, $fromEmail, $brandColor) {
                $msg->to($booking->email)
                    ->from($fromEmail, 'Dunes Discovery Tourism')
                    ->subject($subject)
                    ->html("
                        <div style='text-align:center;margin:0 auto;padding:20px;'><img src='https://dunesdiscoverytourism.com/images/logo.png' alt='Dunes Logo' style='max-width:180px;'/></div>
                        <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;'>
                            <div style='background-color:{$brandColor};padding:20px;text-align:center;'><h2 style='color:white;margin:0;'>Dunes Discovery</h2></div>
                            <div style='padding:20px;background-color:#ffffff;'>{$bodyContent}</div>
                        </div>");
            });

            return response()->json(['success' => true, 'message' => 'Email sent successfully.']);
        } catch (\Exception $e) {
            \Log::error("Failed to resend payment email: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send email.'], 500);
        }
    }
}
