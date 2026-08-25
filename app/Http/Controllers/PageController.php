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
use App\Mail\ContactNotification;
use App\Mail\ContactAcknowledgement;
use App\Mail\WhatsappLeadNotification;
use App\Services\SettingsService;

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
            try {
                $settings = app(SettingsService::class);
                $fromEmail = $settings->getFromEmail();
                $adminEmail = $settings->getAdminEmail();
                $ccEmails = $settings->getCcEmails();
                $bccEmails = $settings->getBccEmails();

                // Admin notification
                $adminMail = (new ContactNotification($name, $email, $phone, $subject, $messageText))->from($fromEmail, 'Dunes Discovery Tourism');
                if (!empty($ccEmails)) $adminMail->cc($ccEmails);
                if (!empty($bccEmails)) $adminMail->bcc($bccEmails);
                Mail::to($adminEmail)->send($adminMail);

                // User acknowledgement
                Mail::to($email)->send((new ContactAcknowledgement($name))->from($fromEmail, 'Dunes Discovery Tourism'));
            } catch (\Throwable $e) {
                Log::error("Failed to send contact emails: " . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Thank you! Your message has been sent successfully.',
                'verified' => $isVerified
            ]);

        } catch (\Throwable $e) {
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
            try {
                $settings = app(SettingsService::class);
                $adminEmail = $settings->get('site_email', 'info@dunesdiscoverytourism.com');
                Mail::to($adminEmail)->send(
                    (new WhatsappLeadNotification($name, $phone, $tourName, $pageUrl, $messageText))->from($adminEmail, 'Dunes Discovery Tourism')
                );
            } catch (\Throwable $e) {
                Log::error("Failed to send WhatsApp lead email: " . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'WhatsApp click logged successfully',
                'inquiry_id' => $inquiry->id
            ]);

        } catch (\Throwable $e) {
            Log::error("Failed to log WhatsApp click: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
