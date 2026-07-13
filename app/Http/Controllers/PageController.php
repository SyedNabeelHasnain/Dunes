<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Faq;
use App\Models\FaqAssignment;
use App\Models\WhatsappInquiry;
use App\Services\VisitorTrackerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PageController extends Controller
{
    protected $tracker;

    public function __construct(VisitorTrackerService $tracker)
    {
        $this->tracker = $tracker;
    }

    /**
     * Show About Us page.
     */
    public function about()
    {
        return view('about');
    }

    /**
     * Show Contact Us page.
     */
    public function contact()
    {
        session(['form_load.contact' => microtime(true)]);
        return view('contact');
    }

    /**
     * Show FAQ page.
     */
    public function faq()
    {
        $generalFaqIds = FaqAssignment::where('entity_type', 'general')
            ->pluck('faq_id');
            
        $faqs = Faq::whereIn('id', $generalFaqIds)
            ->where('status', 'active')
            ->orderBy('priority', 'asc')
            ->get();

        return view('faq', compact('faqs'));
    }

    /**
     * Handle Contact form AJAX submission.
     */
    public function submitContact(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        $name = trim($request->input('name'));
        $email = trim(strtolower($request->input('email')));
        $phone = trim($request->input('phone', ''));
        $subject = trim($request->input('subject', 'Inquiry from Contact Form'));
        $messageText = trim($request->input('message'));

        // Check if email is verified
        $sessionVerified = session()->has('email_verified_' . md5($email));
        $isVerified = $sessionVerified || 
            \App\Models\VerifiedEmail::where('email', $email)->exists() ||
            \App\Models\Booking::where('email', $email)->where('is_verified', true)->exists() ||
            Contact::where('email', $email)->where('is_verified', true)->exists();

        // Collect request context
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
        
        $ctx = $this->tracker->collectRequestContext('contact', $gpsPost);

        try {
            // Save contact record
            $contact = Contact::create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'subject' => $subject,
                'message' => $messageText,
                'status' => 'new',
                'is_verified' => $isVerified,
                'ip_address' => $ctx['client_ip'],
            ]);

            // Log detailed context in request_logs table
            $logId = $this->tracker->logRequest('contact', $contact->id, 'contact', $ctx);
            if ($logId) {
                $contact->update(['request_log_id' => $logId]);
            }

            // Send Email Notifications
            $siteEmailSetting = \App\Models\Setting::where('setting_key', 'site_email')->first();
            $fromEmail = $siteEmailSetting ? $siteEmailSetting->setting_value : 'info@dunesdiscoverytourism.com';

            $adminEmailSetting = \App\Models\Setting::where('setting_key', 'admin_email')->first();
            $adminEmail = $adminEmailSetting ? $adminEmailSetting->setting_value : 'admin@dunesdiscoverytourism.com';

            $ccSetting = \App\Models\Setting::where('setting_key', 'admin_email_cc')->first();
            $ccEmails = $ccSetting && !empty($ccSetting->setting_value) ? array_filter(array_map('trim', explode(',', $ccSetting->setting_value))) : [];

            $bccSetting = \App\Models\Setting::where('setting_key', 'admin_email_bcc')->first();
            $bccEmails = $bccSetting && !empty($bccSetting->setting_value) ? array_filter(array_map('trim', explode(',', $bccSetting->setting_value))) : [];

            // 1. To Admin
            Mail::send([], [], function ($message) use ($name, $email, $phone, $subject, $messageText, $adminEmail, $fromEmail, $ccEmails, $bccEmails) {
                $message->to($adminEmail)
                    ->from($fromEmail, 'Dunes Discovery Tourism')
                    ->subject("New Contact Message: {$subject}")
                    ->html("
                        <h2>New Contact Inquiry Received</h2>
                        <p><strong>Name:</strong> {$name}</p>
                        <p><strong>Email:</strong> {$email}</p>
                        <p><strong>Phone:</strong> {$phone}</p>
                        <p><strong>Subject:</strong> {$subject}</p>
                        <p><strong>Message:</strong></p>
                        <p style='background: #f9f9f9; padding: 15px; border-left: 4px solid #d2a13b;'>{$messageText}</p>
                    ");
                
                if (!empty($ccEmails)) {
                    $message->cc($ccEmails);
                }
                if (!empty($bccEmails)) {
                    $message->bcc($bccEmails);
                }
            });

            // 2. To User (Acknowledgement)
            Mail::send([], [], function ($message) use ($email, $name, $fromEmail) {
                $message->to($email)
                    ->from($fromEmail, 'Dunes Discovery Tourism')
                    ->subject("We received your message - Dunes Discovery Tourism")
                    ->html("
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee;'>
                            <h3>Dear {$name},</h3>
                            <p>Thank you for contacting Dunes Discovery Tourism. We have received your inquiry and our support team will get back to you shortly.</p>
                            <p>Best Regards,<br><strong>Dunes Discovery Tourism Team</strong></p>
                        </div>
                    ");
            });

            return response()->json([
                'success' => true,
                'message' => 'Thank you! Your message has been sent successfully.',
                'verified' => $isVerified
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to process contact submission: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending your message. Please try again later.'
            ], 500);
        }
    }

    /**
     * Log WhatsApp Inquiry Click.
     */
    public function logWhatsapp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'name' => 'nullable|string',
            'tour_name' => 'nullable|string',
            'page_url' => 'nullable|string',
            'message_text' => 'nullable|string',
        ]);

        $name = trim($request->input('name', 'Anonymous'));
        $phone = trim($request->input('phone'));
        $tourName = trim($request->input('tour_name', 'General Inquiry'));
        $pageUrl = trim($request->input('page_url', ''));
        $messageText = trim($request->input('message_text', ''));

        // Collect request context
        $ctx = $this->tracker->collectRequestContext('whatsapp');

        try {
            // Save WhatsApp lead
            $inquiry = WhatsappInquiry::create([
                'name' => $name,
                'phone' => $phone,
                'tour_name' => $tourName,
                'page_url' => $pageUrl,
                'message_text' => $messageText,
            ]);

            // Log detailed context in request_logs table
            $logId = $this->tracker->logRequest('whatsapp', $inquiry->id, 'WhatsApp Click', $ctx);
            if ($logId) {
                $inquiry->update(['request_log_id' => $logId]);
            }

            // Send Admin Email Notification
            $siteEmailSetting = \App\Models\Setting::where('setting_key', 'site_email')->first();
            $adminEmail = $siteEmailSetting ? $siteEmailSetting->setting_value : 'info@dunesdiscoverytourism.com';

            Mail::send([], [], function ($message) use ($name, $phone, $tourName, $pageUrl, $messageText, $adminEmail) {
                $message->to($adminEmail)
                    ->from($adminEmail, 'Dunes Discovery Tourism')
                    ->subject("New WhatsApp Lead: {$name} - {$tourName}")
                    ->html("
                        <h2>New WhatsApp Click Lead</h2>
                        <p><strong>Name:</strong> {$name}</p>
                        <p><strong>Phone:</strong> {$phone}</p>
                        <p><strong>Tour / Topic:</strong> {$tourName}</p>
                        <p><strong>Page URL:</strong> <a href='{$pageUrl}'>{$pageUrl}</a></p>
                        <p><strong>Prefilled Message:</strong></p>
                        <p style='background: #f9f9f9; padding: 15px; border-left: 4px solid #25d366;'>{$messageText}</p>
                    ");
            });

            return response()->json([
                'success' => true,
                'message' => 'WhatsApp click logged successfully',
                'inquiry_id' => $inquiry->id
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to log WhatsApp click: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
