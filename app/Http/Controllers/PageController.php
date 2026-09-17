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
        $settings = app(SettingsService::class);
        $currentYear = date('Y');
        $defaultTitle = "About Dunes Discovery Tourism ({$currentYear}) | Leading Dubai Desert Safari Operator";
        $defaultDesc = "Learn about Dunes Discovery Tourism LLC, Dubai's premier DTCM-licensed desert safari & adventure operator since 2018. Over 25+ luxury 4x4 Land Cruisers, 5-star live BBQ camps, and 10,000+ happy travelers.";
        $defaultKeys = "about dunes discovery tourism, dubai desert safari operator, licensed tourism company dubai, luxury desert safaris";

        $pageTitle = $settings->get('seo_about_title') ?: $defaultTitle;
        $pageDesc = $settings->get('seo_about_description') ?: $defaultDesc;
        $pageKeys = $settings->get('seo_about_keywords') ?: $defaultKeys;
        $ogImageSetting = $settings->get('seo_about_og_image');
        $ogImage = $ogImageSetting ? asset(ltrim($ogImageSetting, '/')) : asset('images/dubai-desert-safari-tour-dune-discovery-tourism.avif');
        $canonical = route('about');

        return view('about', compact('pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage'));
    }

    /**
     * Show Contact Us page.
     */
    public function contact()
    {
        session(['form_load.contact' => microtime(true)]);

        $settings = app(SettingsService::class);
        $currentYear = date('Y');
        $defaultTitle = "Contact Dunes Discovery Tourism ({$currentYear}) | 24/7 Dubai Support & Booking";
        $defaultDesc = "Get in touch with Dunes Discovery Tourism Dubai. 24/7 WhatsApp assistance (+971 50 245 6056), instant bookings, custom group tours, and corporate desert safaris.";
        $defaultKeys = "contact dunes discovery, dubai desert safari contact, book desert safari whatsapp, tourism office dubai";

        $pageTitle = $settings->get('seo_contact_title') ?: $defaultTitle;
        $pageDesc = $settings->get('seo_contact_description') ?: $defaultDesc;
        $pageKeys = $settings->get('seo_contact_keywords') ?: $defaultKeys;
        $ogImageSetting = $settings->get('seo_contact_og_image');
        $ogImage = $ogImageSetting ? asset(ltrim($ogImageSetting, '/')) : asset('images/desert-safari-poster.avif');
        $canonical = route('contact');

        return view('contact', compact('pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage'));
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

        $settings = app(SettingsService::class);
        $currentYear = date('Y');
        $defaultTitle = "Dubai Desert Safari FAQs ({$currentYear}) | Complete Traveler Guide | Dunes Discovery";
        $defaultDesc = "Find instant answers to all questions about Dubai desert safaris, what to wear, dune bashing safety, child booster seats, 100% Halal live BBQ dining, and free 24h cancellations.";
        $defaultKeys = "dubai desert safari faq, desert safari questions, what to wear desert safari dubai, halal bbq desert safari";

        $pageTitle = $settings->get('seo_faq_title') ?: $defaultTitle;
        $pageDesc = $settings->get('seo_faq_description') ?: $defaultDesc;
        $pageKeys = $settings->get('seo_faq_keywords') ?: $defaultKeys;
        $ogImageSetting = $settings->get('seo_faq_og_image');
        $ogImage = $ogImageSetting ? asset(ltrim($ogImageSetting, '/')) : asset('images/desert-safari-poster.avif');
        $canonical = route('faq');

        return view('faq', compact('faqs', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage'));
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
            return response()->json(['success' => false, 'message' => 'An error occurred while logging WhatsApp inquiry.'], 500);
        }
    }

    /**
     * Display Public Customer Review & Photo UGC Submission Portal.
     */
    public function reviewRate(Request $request, string $ref)
    {
        $booking = \App\Models\Booking::where('reference', $ref)->with('tour')->first();
        if (!$booking) {
            $booking = new \App\Models\Booking([
                'tour_name' => 'Dubai Desert Safari Experience',
            ]);
            $booking->id = null;
            $booking->reference = strtoupper($ref);
            $booking->name = 'Valued Guest';
        }
        $score = (int)$request->input('score', 5);
        if ($score < 1 || $score > 5) {
            $score = 5;
        }

        $settings = app(SettingsService::class);
        $googleReviewUrl = $settings->get('google_review_url', 'https://maps.google.com/?cid=123456789');

        // Optional direct redirect if explicitly requested
        if ($request->has('google_direct') && $score >= 4) {
            return redirect()->away($googleReviewUrl);
        }

        $pageTitle = "Review Your Safari Adventure | Dunes Discovery Tourism";
        $pageDesc = "Share your verified guest review, rate your desert safari captain, and upload your tour photos.";

        return view('pages.submit-review', compact('booking', 'score', 'googleReviewUrl', 'pageTitle', 'pageDesc'));
    }

    /**
     * Process Public Customer Review & Photo UGC Submission.
     */
    public function submitReview(Request $request, string $ref)
    {
        $booking = \App\Models\Booking::where('reference', $ref)->first();
        $isGuestMode = !$booking;

        $rules = [
            'rating' => 'required|numeric|min:1|max:5',
            'review_title' => 'nullable|string|max:255',
            'review_text' => 'required|string|min:10|max:3000',
            'photos' => 'nullable|array|max:4',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp,avif|max:5120',
        ];
        if ($isGuestMode) {
            $rules['guest_name'] = 'required|string|min:2|max:100';
        }
        $request->validate($rules);

        $reviewerName = $booking ? $booking->name : trim($request->input('guest_name', 'Guest Traveler'));
        $bookingId = $booking ? $booking->id : null;

        $rating = (float)$request->input('rating');
        $storedPhotos = [];

        if ($request->hasFile('photos')) {
            $uploadDir = public_path('uploads/reviews');
            if (!file_exists($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }

            foreach ($request->file('photos') as $photoFile) {
                if ($photoFile->isValid()) {
                    $ext = $photoFile->getClientOriginalExtension() ?: 'jpg';
                    $filename = 'rev_' . uniqid() . '_' . time() . '.' . $ext;
                    $photoFile->move($uploadDir, $filename);
                    $storedPhotos[] = 'uploads/reviews/' . $filename;
                }
            }
        }

        $sourceReviewId = $booking ? $booking->reference : ('GUEST-' . strtoupper($ref) . '-' . substr(md5($reviewerName . ($storedPhotos[0] ?? time())), 0, 8));

        $review = \App\Models\Review::updateOrCreate(
            [
                'source' => 'direct_ugc',
                'source_review_id' => $sourceReviewId,
            ],
            [
                'booking_id' => $bookingId,
                'reviewer_name' => $reviewerName,
                'rating' => $rating,
                'review_title' => $request->input('review_title') ?: 'Unforgettable Desert Safari Experience',
                'review_text' => $request->input('review_text'),
                'photos' => $storedPhotos,
                'status' => ($rating >= 4) ? 'approved' : 'pending',
                'is_featured' => ($rating >= 4.5 && count($storedPhotos) > 0),
                'published_date' => now()->toDateString(),
                'imported_at' => now(),
            ]
        );

        $settings = app(SettingsService::class);
        $googleReviewUrl = $settings->get('google_review_url', 'https://maps.google.com/?cid=123456789');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you! Your review and photos have been received.',
                'rating' => $rating,
                'google_review_url' => ($rating >= 4) ? $googleReviewUrl : null,
            ]);
        }

        return redirect()->route('review.rate', ['ref' => $ref])
            ->with('review_submitted', true)
            ->with('submitted_rating', $rating)
            ->with('google_review_url', $googleReviewUrl);
    }

    /**
     * Submit private customer feedback for service recovery.
     */
    public function submitFeedback(Request $request, string $ref)
    {
        $booking = \App\Models\Booking::where('reference', $ref)->firstOrFail();
        $feedback = trim($request->input('feedback', ''));

        if (!empty($feedback)) {
            $notes = $booking->special_requests ?: '';
            $booking->update([
                'special_requests' => trim($notes . "\n[GUEST FEEDBACK: " . $feedback . "]")
            ]);
        }

        return redirect()->route('home')->with('success', 'Thank you for your valuable feedback. Our management team will review your comments.');
    }
}
