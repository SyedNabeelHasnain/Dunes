<?php

namespace App\Http\Controllers;

use App\Mail\ContactAcknowledgement;
use App\Mail\ContactNotification;
use App\Mail\WhatsappLeadNotification;
use App\Models\Booking;
use App\Models\Contact;
use App\Models\Faq;
use App\Models\FaqAssignment;
use App\Models\Review;
use App\Models\VerifiedEmail;
use App\Models\WhatsappInquiry;
use App\Services\SettingsService;
use App\Services\VisitorTrackerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
    public function about(Request $request)
    {
        $settings = app(SettingsService::class);
        $currentLocale = app()->getLocale();
        $currentYear = date('Y');

        if ($currentLocale === 'en' || empty($currentLocale)) {
            $defaultTitle = "About Dunes Discovery Tourism ({$currentYear}) | Leading Dubai Desert Safari Operator";
            $defaultDesc = "Learn about Dunes Discovery Tourism LLC, Dubai's premier DTCM-licensed desert safari & adventure operator since 2018. Over 25+ luxury 4x4 Land Cruisers, 5-star live BBQ camps, and 10,000+ happy travelers.";
            $defaultKeys = 'about dunes discovery tourism, dubai desert safari operator, licensed tourism company dubai, luxury desert safaris';

            $pageTitle = $settings->get('seo_about_title') ?: $defaultTitle;
            $pageDesc = $settings->get('seo_about_description') ?: $defaultDesc;
            $pageKeys = $settings->get('seo_about_keywords') ?: $defaultKeys;
        } else {
            $pageTitle = $settings->get("seo_about_title_{$currentLocale}") ?: __('ui.seo.about_title', ['year' => $currentYear]);
            $pageDesc = $settings->get("seo_about_description_{$currentLocale}") ?: __('ui.seo.about_description');
            $pageKeys = $settings->get("seo_about_keywords_{$currentLocale}") ?: __('ui.seo.about_keywords');
        }

        $ogImageSetting = $settings->get('seo_about_og_image');
        $ogImage = resolve_og_image($ogImageSetting, 'images/dubai-desert-safar-camel-ride-dune-discovery-tourism.avif');
        $canonical = localized_route('about');
        $pageRobots = ! empty($request->query()) ? 'noindex, follow' : 'index, follow';

        return view('about', compact('pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage', 'pageRobots'));
    }

    /**
     * Show Contact Us page.
     */
    public function contact(Request $request)
    {
        session(['form_load.contact' => microtime(true)]);

        $settings = app(SettingsService::class);
        $currentLocale = app()->getLocale();
        $currentYear = date('Y');

        if ($currentLocale === 'en' || empty($currentLocale)) {
            $defaultTitle = "Contact Dunes Discovery Tourism ({$currentYear}) | 24/7 Dubai Support & Booking";
            $defaultDesc = 'Get in touch with Dunes Discovery Tourism Dubai. 24/7 WhatsApp assistance (+971 50 245 6056), instant bookings, custom group tours, and corporate desert safaris.';
            $defaultKeys = 'contact dunes discovery, dubai desert safari contact, book desert safari whatsapp, tourism office dubai';

            $pageTitle = $settings->get('seo_contact_title') ?: $defaultTitle;
            $pageDesc = $settings->get('seo_contact_description') ?: $defaultDesc;
            $pageKeys = $settings->get('seo_contact_keywords') ?: $defaultKeys;
        } else {
            $pageTitle = $settings->get("seo_contact_title_{$currentLocale}") ?: __('ui.seo.contact_title', ['year' => $currentYear]);
            $pageDesc = $settings->get("seo_contact_description_{$currentLocale}") ?: __('ui.seo.contact_description');
            $pageKeys = $settings->get("seo_contact_keywords_{$currentLocale}") ?: __('ui.seo.contact_keywords');
        }

        $ogImageSetting = $settings->get('seo_contact_og_image');
        $ogImage = resolve_og_image($ogImageSetting, 'images/dubai-desert-safari-camp-dune-discovery-tourism.avif');
        $canonical = localized_route('contact');
        $pageRobots = ! empty($request->query()) ? 'noindex, follow' : 'index, follow';

        return view('contact', compact('pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage', 'pageRobots'));
    }

    /**
     * Show FAQ page.
     */
    public function faq(Request $request)
    {
        $generalFaqIds = FaqAssignment::where('entity_type', 'general')
            ->pluck('faq_id');

        $faqs = Faq::whereIn('id', $generalFaqIds)
            ->where('status', 'active')
            ->orderBy('priority', 'asc')
            ->get();

        $settings = app(SettingsService::class);
        $currentLocale = app()->getLocale();
        $currentYear = date('Y');

        if ($currentLocale === 'en' || empty($currentLocale)) {
            $defaultTitle = "Dubai Desert Safari FAQs ({$currentYear}) | Complete Traveler Guide | Dunes Discovery";
            $defaultDesc = 'Find instant answers to all questions about Dubai desert safaris, what to wear, dune bashing safety, child booster seats, 100% Halal live BBQ dining, and free 24h cancellations.';
            $defaultKeys = 'dubai desert safari faq, desert safari questions, what to wear desert safari dubai, halal bbq desert safari';

            $pageTitle = $settings->get('seo_faq_title') ?: $defaultTitle;
            $pageDesc = $settings->get('seo_faq_description') ?: $defaultDesc;
            $pageKeys = $settings->get('seo_faq_keywords') ?: $defaultKeys;
        } else {
            $pageTitle = $settings->get("seo_faq_title_{$currentLocale}") ?: __('ui.seo.faq_title', ['year' => $currentYear]);
            $pageDesc = $settings->get("seo_faq_description_{$currentLocale}") ?: __('ui.seo.faq_description');
            $pageKeys = $settings->get("seo_faq_keywords_{$currentLocale}") ?: __('ui.seo.faq_keywords');
        }

        $ogImageSetting = $settings->get('seo_faq_og_image');
        $ogImage = resolve_og_image($ogImageSetting, 'images/dubai-desert-safari-dune-bashing-dune-discovery-tourism.avif');
        $canonical = localized_route('faq');
        $pageRobots = ! empty($request->query()) ? 'noindex, follow' : 'index, follow';

        return view('faq', compact('faqs', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage', 'pageRobots'));
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
        $isVerified = VerifiedEmail::isVerified($email);

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
                if (! empty($ccEmails)) {
                    $adminMail->cc($ccEmails);
                }
                if (! empty($bccEmails)) {
                    $adminMail->bcc($bccEmails);
                }
                Mail::to($adminEmail)->send($adminMail);

                // User acknowledgement
                Mail::to($email)->send((new ContactAcknowledgement($name))->from($fromEmail, 'Dunes Discovery Tourism'));
            } catch (\Throwable $e) {
                Log::error('Failed to send contact emails: '.$e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Thank you! Your message has been sent successfully.',
                'verified' => $isVerified,
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to process contact submission: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending your message. Please try again later.',
            ], 500);
        }
    }

    /**
     * Log WhatsApp Inquiry Click.
     */
    public function logWhatsapp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['nullable', 'string', 'max:50', 'regex:/^([\+]?[0-9\s\-\(\)\.]{5,25}|N\/A)?$/i'],
            'name' => 'nullable|string|max:100',
            'email' => 'nullable|string|max:150',
            'tour_name' => 'nullable|string|max:150',
            'page_url' => 'nullable|string|max:255',
            'message_text' => 'nullable|string|max:1000',
            'prospect_name' => 'nullable|string|max:100',
            'prospect_phone' => 'nullable|string|max:50',
            'prospect_email' => 'nullable|string|max:150',
            'client_ip' => 'nullable|string|max:50',
            'client_country' => 'nullable|string|max:100',
            'client_city' => 'nullable|string|max:100',
            'client_region' => 'nullable|string|max:100',
            'client_isp' => 'nullable|string|max:150',
            'screen_res' => 'nullable|string|max:50',
            'client_timezone' => 'nullable|string|max:100',
            'client_language' => 'nullable|string|max:50',
            'ref_code' => 'nullable|string|max:30',
            'traffic_channel' => 'nullable|string|max:100',
            'initial_referrer' => 'nullable|string|max:2000',
            'landing_page' => 'nullable|string|max:2000',
            'session_duration_seconds' => 'nullable|integer|min:0',
            'pages_viewed_count' => 'nullable|integer|min:1',
            'journey_timeline' => 'nullable',
            'gclid' => 'nullable|string|max:150',
            'fbclid' => 'nullable|string|max:150',
        ]);

        $name = strip_tags(trim($request->input('name', 'Anonymous'))) ?: 'Anonymous';
        $phone = strip_tags(trim($request->input('phone', 'N/A'))) ?: 'N/A';
        $email = strip_tags(trim($request->input('email', '')));
        $tourName = strip_tags(trim($request->input('tour_name', 'General Inquiry'))) ?: 'General Inquiry';
        $pageUrl = strip_tags(trim($request->input('page_url', '')));
        $messageText = strip_tags(trim($request->input('message_text', '')));

        // Collect comprehensive request context & telemetry
        $ctx = $this->tracker->collectRequestContext('whatsapp', $request->all());

        // First-party prospect resolution
        $prospectSource = 'Direct Click';
        if ($name === 'Anonymous' || $name === 'N/A' || empty($name)) {
            if ($request->filled('prospect_name')) {
                $name = strip_tags(trim($request->input('prospect_name')));
                if ($phone === 'N/A' || empty($phone)) {
                    $phone = strip_tags(trim($request->input('prospect_phone', 'N/A'))) ?: 'N/A';
                }
                if (empty($email)) {
                    $email = strip_tags(trim($request->input('prospect_email', '')));
                }
                $prospectSource = 'Stored Browser Profile';
            } else {
                $clientIp = $ctx['client_ip'] ?? request()->ip();
                $sessionId = session()->getId();

                if ($clientIp && ! in_array($clientIp, ['0.0.0.0', '127.0.0.1', 'not available'])) {
                    $priorBooking = \App\Models\Booking::where('ip_address', $clientIp)
                        ->whereNotNull('name')
                        ->where('name', '!=', '')
                        ->latest()
                        ->first();

                    if ($priorBooking) {
                        $name = $priorBooking->name;
                        if ($phone === 'N/A' || empty($phone)) {
                            $phone = $priorBooking->phone ?: 'N/A';
                        }
                        if (empty($email)) {
                            $email = $priorBooking->email ?: '';
                        }
                        $prospectSource = 'Prior Booking History';
                    } else {
                        $priorContact = \App\Models\Contact::where('client_ip', $clientIp)
                            ->whereNotNull('name')
                            ->where('name', '!=', '')
                            ->latest()
                            ->first();

                        if ($priorContact) {
                            $name = $priorContact->name;
                            if ($phone === 'N/A' || empty($phone)) {
                                $phone = $priorContact->phone ?: 'N/A';
                            }
                            if (empty($email)) {
                                $email = $priorContact->email ?: '';
                            }
                            $prospectSource = 'Prior Contact Message';
                        }
                    }
                }
            }
        }

        $settings = app(SettingsService::class);
        $whatsappNum = preg_replace('/[^0-9]/', '', $settings->get('site_whatsapp', '971502456056')) ?: '971502456056';

        // Inquiry correlation tracking tag
        $refCode = strip_tags(trim($request->input('ref_code', '')));
        if (empty($refCode)) {
            $refCode = 'WA-'.strtoupper(substr(uniqid(), -4));
        }

        if (empty($messageText)) {
            $messageText = ($tourName && $tourName !== 'General Inquiry')
                ? "Hi Dunes Discovery Tourism, I would like to inquire about {$tourName}."
                : 'Hi Dunes Discovery Tourism, I would like to inquire about Dubai desert safaris.';
        }

        if (! str_contains($messageText, '[Ref:')) {
            $messageText = rtrim($messageText)." [Ref: #{$refCode}]";
        }

        $redirectUrl = "https://wa.me/{$whatsappNum}?text=".urlencode($messageText);

        // Acquisition & Traffic Channel resolution
        $trafficChannel = strip_tags(trim($request->input('traffic_channel', '')));
        $initialReferrer = strip_tags(trim($request->input('initial_referrer', '')));
        $landingPage = strip_tags(trim($request->input('landing_page', '')));
        $sessionDuration = (int) $request->input('session_duration_seconds', 0);
        $pagesCount = (int) $request->input('pages_viewed_count', 1);
        $gclid = strip_tags(trim($request->input('gclid', ''))) ?: ($ctx['gclid'] ?? session('google_ads_gclid'));
        $fbclid = strip_tags(trim($request->input('fbclid', '')));

        // If initial_referrer is empty, fall back to request context referrer
        if (empty($initialReferrer) && ! empty($ctx['referrer_url']) && $ctx['referrer_url'] !== 'Not Available') {
            $initialReferrer = $ctx['referrer_url'];
        }

        // If landing page is empty, fallback to pageUrl or current request
        if (empty($landingPage)) {
            $landingPage = $pageUrl ?: url()->previous();
        }

        // Channel Classification Fallback if client didn't provide one
        if (empty($trafficChannel)) {
            if (! empty($gclid) || strtolower($ctx['utm_medium'] ?? '') === 'cpc' || str_contains(strtolower($ctx['utm_source'] ?? ''), 'google ads')) {
                $trafficChannel = 'Google Ads';
            } elseif (! empty($fbclid) || str_contains(strtolower($initialReferrer), 'facebook.com') || str_contains(strtolower($initialReferrer), 'fb.com')) {
                $trafficChannel = 'Facebook';
            } elseif (str_contains(strtolower($initialReferrer), 'instagram.com')) {
                $trafficChannel = 'Instagram';
            } elseif (str_contains(strtolower($initialReferrer), 'tiktok.com')) {
                $trafficChannel = 'TikTok';
            } elseif (str_contains(strtolower($initialReferrer), 'youtube.com') || str_contains(strtolower($initialReferrer), 'youtu.be')) {
                $trafficChannel = 'YouTube';
            } elseif (str_contains(strtolower($initialReferrer), 'twitter.com') || str_contains(strtolower($initialReferrer), 'x.com') || str_contains(strtolower($initialReferrer), 't.co')) {
                $trafficChannel = 'X (Twitter)';
            } elseif (str_contains(strtolower($initialReferrer), 'linkedin.com')) {
                $trafficChannel = 'LinkedIn';
            } elseif (str_contains(strtolower($initialReferrer), 'google.')) {
                $trafficChannel = 'Google Organic';
            } elseif (str_contains(strtolower($initialReferrer), 'bing.')) {
                $trafficChannel = 'Bing Organic';
            } elseif (! empty($initialReferrer) && $initialReferrer !== 'Direct' && $initialReferrer !== 'Not Available') {
                $refHost = parse_url($initialReferrer, PHP_URL_HOST);
                $trafficChannel = $refHost ? 'Referral: '.preg_replace('/^www\./', '', $refHost) : 'Referral';
            } else {
                $trafficChannel = 'Direct';
            }
        }

        // Parse and validate journey timeline
        $journeyTimeline = $request->input('journey_timeline');
        if (is_string($journeyTimeline)) {
            $decoded = json_decode($journeyTimeline, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $journeyTimeline = $decoded;
            }
        }

        if (! is_array($journeyTimeline) || empty($journeyTimeline)) {
            $journeyTimeline = [
                [
                    'step' => 1,
                    'url' => $pageUrl ?: '/',
                    'title' => ($tourName && $tourName !== 'General Inquiry') ? $tourName : 'Dubai Desert Safari Experience',
                    'duration_seconds' => max(5, $sessionDuration),
                    'sections' => ['Inquiry Triggered'],
                    'timestamp' => now()->toIso8601String(),
                ],
            ];
        }

        try {
            $finalClientIp = $ctx['client_ip'] ?? request()->ip();
            if (empty($finalClientIp) || in_array(strtolower($finalClientIp), ['not available', '0.0.0.0', '127.0.0.1'])) {
                $rawClientEcho = trim((string) $request->input('client_ip', ''));
                if (filter_var($rawClientEcho, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    $finalClientIp = $rawClientEcho;
                }
            }

            $finalCountry = (! empty($ctx['country']) && $ctx['country'] !== 'Not Available' && $ctx['country'] !== 'Unknown')
                ? $ctx['country']
                : ($request->input('client_country') ?: null);

            $finalCity = (! empty($ctx['city']) && $ctx['city'] !== 'Not Available' && $ctx['city'] !== 'Unknown')
                ? $ctx['city']
                : ($request->input('client_city') ?: null);

            $finalRegion = (! empty($ctx['region']) && $ctx['region'] !== 'Not Available')
                ? $ctx['region']
                : ($request->input('client_region') ?: null);

            $finalIsp = (! empty($ctx['isp']) && $ctx['isp'] !== 'Not Available')
                ? $ctx['isp']
                : ($request->input('client_isp') ?: ($ctx['organization'] ?? null));

            // Save WhatsApp lead with direct telemetry columns (self-contained & resilient)
            $inquiry = WhatsappInquiry::create([
                'name' => $name,
                'phone' => $phone,
                'email' => $email ?: null,
                'tour_name' => $tourName,
                'page_url' => $pageUrl,
                'message_text' => $messageText,
                'client_ip' => $finalClientIp ?: 'Not Available',
                'country' => $finalCountry,
                'city' => $finalCity,
                'region' => $finalRegion,
                'device_type' => $ctx['device_type'] ?? null,
                'browser_name' => $ctx['browser_name'] ?? null,
                'os_name' => $ctx['os_name'] ?? null,
                'isp' => $finalIsp,
                'organization' => $finalIsp,
                'utm_source' => $ctx['utm_source'] ?? null,
                'utm_medium' => $ctx['utm_medium'] ?? null,
                'utm_campaign' => $ctx['utm_campaign'] ?? null,
                'referrer_url' => $ctx['referrer_url'] ?? null,
                'screen_res' => $request->input('screen_res'),
                'client_timezone' => $request->input('client_timezone'),
                'client_language' => $request->input('client_language'),
                'prospect_source' => $prospectSource,
                'traffic_channel' => $trafficChannel,
                'initial_referrer' => $initialReferrer ?: null,
                'landing_page' => $landingPage ?: null,
                'session_duration_seconds' => max(0, $sessionDuration),
                'pages_viewed_count' => max(1, $pagesCount, count($journeyTimeline)),
                'journey_timeline' => $journeyTimeline,
                'gclid' => $gclid ?: null,
                'fbclid' => $fbclid ?: null,
            ]);

            // Log detailed context in request_logs table
            $logId = $this->tracker->logRequest('whatsapp', $inquiry->id, 'WhatsApp Click', $ctx);
            if ($logId) {
                $inquiry->update(['request_log_id' => $logId]);
            }

            // Send Admin Email Notification
            try {
                $adminEmail = $settings->get('site_email', 'info@dunesdiscoverytourism.com');
                Mail::to($adminEmail)->send(
                    (new WhatsappLeadNotification($name, $phone, $tourName, $pageUrl, $messageText))->from($adminEmail, 'Dunes Discovery Tourism')
                );
            } catch (\Throwable $e) {
                Log::error('Failed to send WhatsApp lead email: '.$e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'WhatsApp click logged successfully',
                'inquiry_id' => $inquiry->id,
                'ref_code' => $refCode,
                'redirect_url' => $redirectUrl,
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to log WhatsApp click: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'An error occurred while logging WhatsApp inquiry.'], 500);
        }
    }

    /**
     * Display Public Customer Review & Photo UGC Submission Portal.
     */
    public function reviewRate(Request $request, ...$params)
    {
        $ref = (string) end($params);
        $booking = Booking::where('reference', $ref)->with('tour')->first();
        if (! $booking) {
            $booking = new Booking([
                'tour_name' => 'Dubai Desert Safari Experience',
            ]);
            $booking->id = null;
            $booking->reference = strtoupper($ref);
            $booking->name = 'Valued Guest';
        }
        $score = (int) $request->input('score', 5);
        if ($score < 1 || $score > 5) {
            $score = 5;
        }

        $currentLocale = app()->getLocale();
        $canonical = localized_route('review.rate', ['ref' => $ref]);

        if ($score <= 3) {
            $pageTitle = 'Guest Feedback | Dunes Discovery Tourism';
            $pageDesc = 'Share your private feedback to help us improve your desert safari adventure.';
            $pageRobots = 'noindex, nofollow';

            return view('review-feedback', compact('booking', 'score', 'pageTitle', 'pageDesc', 'pageRobots', 'canonical'));
        }

        $settings = app(SettingsService::class);
        $googleReviewUrl = $settings->get('google_review_url', 'https://search.google.com/local/writereview?placeid=ChIJbWsIEIVEdEER4uHEhb2dbcQ');

        // Optional direct redirect if explicitly requested
        if ($request->has('google_direct') && $score >= 4) {
            return redirect()->away($googleReviewUrl);
        }

        $localizedReviewMeta = [
            'ar' => [
                'title' => 'قيّم مغامرة السفاري الخاصة بك | Dunes Discovery Tourism',
                'desc' => 'شارك تقييمك الموثق، وقيّم سائق السفاري الصحراوي، وارفع صور رحلتك في صحراء دبي.',
            ],
            'ru' => [
                'title' => 'Оставьте отзыв о вашем сафари в Дубае | Dunes Discovery Tourism',
                'desc' => 'Поделитесь проверенным отзывом, оцените вашего капитана сафари и загрузите фотографии из пустыни Дубая.',
            ],
            'es' => [
                'title' => 'Valora tu Aventura de Safari en Dubái | Dunes Discovery Tourism',
                'desc' => 'Comparte tu reseña verificada, califica a tu capitán de safari y sube las fotos de tu tour en el desierto.',
            ],
            'it' => [
                'title' => 'Valuta la Tua Avventura Safari a Dubai | Dunes Discovery Tourism',
                'desc' => 'Condividi la tua recensione verificata, valuta il capitano del safari e carica le foto del tuo tour nel deserto.',
            ],
        ];

        $pageTitle = $localizedReviewMeta[$currentLocale]['title'] ?? 'Review Your Safari Adventure | Dunes Discovery Tourism';
        $pageDesc = $localizedReviewMeta[$currentLocale]['desc'] ?? 'Share your verified guest review, rate your desert safari captain, and upload your tour photos.';
        $pageRobots = ($ref === 'guest' && empty($request->query())) ? 'index, follow' : 'noindex, nofollow';

        return view('pages.submit-review', compact('booking', 'score', 'googleReviewUrl', 'pageTitle', 'pageDesc', 'pageRobots', 'canonical'));
    }

    /**
     * Process Public Customer Review & Photo UGC Submission.
     */
    public function submitReview(Request $request, ...$params)
    {
        $ref = (string) end($params);
        $booking = Booking::where('reference', $ref)->first();
        $isGuestMode = ! $booking;

        $rules = [
            'rating' => 'required|numeric|min:1|max:5',
            'review_title' => 'nullable|string|max:255',
            'review_text' => 'required|string|min:10|max:3000',
            'photos' => 'nullable|array|max:4',
            'photos.*' => 'file|mimes:jpeg,png,jpg,webp,avif|max:8192',
        ];
        if ($isGuestMode) {
            $rules['guest_name'] = 'required|string|min:2|max:100';
        }
        $request->validate($rules);

        $reviewerName = $booking ? $booking->name : trim($request->input('guest_name', 'Guest Traveler'));
        $bookingId = $booking ? $booking->id : null;

        $rating = (float) $request->input('rating');
        $storedPhotos = [];

        if ($request->hasFile('photos')) {
            $uploadDir = public_path('uploads/reviews');
            if (! file_exists($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }

            foreach ($request->file('photos') as $photoFile) {
                if ($photoFile->isValid()) {
                    $ext = strtolower($photoFile->getClientOriginalExtension() ?: ($photoFile->guessExtension() ?: 'avif'));
                    $filename = 'rev_'.uniqid().'_'.time().'.'.$ext;
                    $photoFile->move($uploadDir, $filename);
                    $storedPhotos[] = 'uploads/reviews/'.$filename;
                }
            }
        }

        $sourceReviewId = $booking ? $booking->reference : ('GUEST-'.strtoupper($ref).'-'.substr(md5($reviewerName.($storedPhotos[0] ?? time())), 0, 8));

        $review = Review::updateOrCreate(
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

        Cache::forget('site_home_reviews_feed');
        Cache::forget('site_social_proof_feed');
        Cache::forget('site_home_cache');

        $settings = app(SettingsService::class);
        $googleReviewUrl = $settings->get('google_review_url', 'https://search.google.com/local/writereview?placeid=ChIJbWsIEIVEdEER4uHEhb2dbcQ');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you! Your review and photos have been received.',
                'rating' => $rating,
                'google_review_url' => ($rating >= 4) ? $googleReviewUrl : null,
            ]);
        }

        return redirect()->to(localized_route('review.rate', ['ref' => $ref]))
            ->with('review_submitted', true)
            ->with('submitted_rating', $rating)
            ->with('google_review_url', $googleReviewUrl);
    }

    /**
     * Submit private customer feedback for service recovery.
     */
    public function submitFeedback(Request $request, ...$params)
    {
        $ref = (string) end($params);
        $booking = Booking::where('reference', $ref)->first();
        $feedback = trim($request->input('feedback', ''));

        if ($booking && ! empty($feedback)) {
            $notes = $booking->special_requests ?: '';
            $booking->update([
                'special_requests' => trim($notes."\n[GUEST FEEDBACK: ".$feedback.']'),
            ]);
        }

        return redirect()->to(localized_route('home'))->with('success', 'Thank you for your valuable feedback. Our management team will review your comments.');
    }

    /**
     * Show Official Press Kit & Partner Media Resources page.
     */
    public function pressKit(Request $request)
    {
        $settings = app(SettingsService::class);
        $currentLocale = app()->getLocale();
        $currentYear = date('Y');

        $canonical = localized_route('press.kit');
        $pageRobots = ! empty($request->query()) ? 'noindex, follow' : 'index, follow';

        $localizedPressMeta = [
            'ar' => [
                'title' => "الملف الصحفي والموارد الإعلامية الرسمية ({$currentYear}) | Dunes Discovery Tourism دبي",
                'desc' => 'حمّل الشعارات الرسمية عالية الدقة، بيانات ترخيص دائرة الاقتصاد والسياحة بدبي DTCM #1430583، وشارات الشركاء المعتمدة لشركة Dunes Discovery Tourism.',
            ],
            'ru' => [
                'title' => "Официальный пресс-кит и медиаресурсы ({$currentYear}) | Dunes Discovery Tourism Дубай",
                'desc' => 'Скачайте официальные логотипы в высоком разрешении, данные лицензии DTCM #1430583, информационный бюллетень и партнерские бейджи Dunes Discovery Tourism.',
            ],
            'es' => [
                'title' => "Kit de Prensa Oficial y Recursos Multimedia ({$currentYear}) | Dunes Discovery Tourism Dubái",
                'desc' => 'Descarga activos de marca oficiales en alta resolución, credenciales de licencia DTCM #1430583, hoja informativa e insignias para socios de Dunes Discovery Tourism.',
            ],
            'it' => [
                'title' => "Press Kit Ufficiale e Risorse Media ({$currentYear}) | Dunes Discovery Tourism Dubai",
                'desc' => 'Scarica risorse del marchio ad alta risoluzione, credenziali di licenza DTCM #1430583, scheda informativa e badge partner per Dunes Discovery Tourism.',
            ],
        ];

        $pageTitle = $localizedPressMeta[$currentLocale]['title'] ?? ($settings->get('seo_press_title') ?: "Official Press Kit & Media Resources ({$currentYear}) | Dunes Discovery Tourism Dubai");
        $pageDesc = $localizedPressMeta[$currentLocale]['desc'] ?? ($settings->get('seo_press_description') ?: 'Download official high-resolution brand assets, DTCM licensing credentials, fact sheet, and embeddable partner badges for Dunes Discovery Tourism.');
        $pageKeys = $settings->get('seo_press_keywords') ?: 'dunes discovery press kit, dubai desert safari media kit, dtcm license verification, dunes discovery logo, desert safari partner badges';

        $ogImageSetting = $settings->get('seo_press_og_image');
        $ogImage = $ogImageSetting ? asset(ltrim($ogImageSetting, '/')) : asset('images/dubai-desert-safari-tour-dune-discovery-tourism.avif');

        // Official Fact Sheet Stats
        $facts = [
            'company_name' => 'Dunes Discovery Tourism L.L.C.',
            'license_number' => $settings->get('company_license_number', '1430583'),
            'license_authority' => 'Department of Economy and Tourism (DET / DTCM), Dubai',
            'established_year' => '2018',
            'fleet_size' => '25+ Specially Prepared Toyota Land Cruisers & Dune Buggies',
            'primary_terrain' => 'Lahbab Red Sand Desert, Dubai, UAE (25.04° N, 55.67° E)',
            'rating_google' => '5.0 / 5.0 (Google Maps Verified)',
            'rating_tripadvisor' => '4.9 / 5.0 (TripAdvisor Traveler Choice)',
            'total_reviews' => '329+ Verified Direct & Global Platform Reviews',
            'contact_press' => $settings->getFromEmail(),
            'phone' => $settings->get('site_phone', '+971 50 245 6056'),
        ];

        // Embeddable Partner Badges
        $badges = [
            [
                'id' => 'partner',
                'name' => 'Verified Desert Safari Partner',
                'description' => 'Dark luxury theme with gold desert compass emblem for partner portals, hotel concierges, and affiliate directories.',
                'image' => asset('badges/verified-safari-partner.svg'),
                'embed_code' => '<a href="'.url('/').'" target="_blank" rel="noopener" title="Dunes Discovery Tourism - Verified Desert Safari Partner"><img src="'.asset('badges/verified-safari-partner.svg').'" alt="Dunes Discovery Tourism - Verified Desert Safari Partner" width="280" height="80" style="display:inline-block;max-width:100%;height:auto;border-radius:14px;" /></a>',
            ],
            [
                'id' => 'top-rated',
                'name' => '5.0 Star Top Rated Operator',
                'description' => 'Clean light card displaying our 5.0 rating across 329+ verified reviews on Google and TripAdvisor.',
                'image' => asset('badges/top-rated-operator.svg'),
                'embed_code' => '<a href="'.url('/').'" target="_blank" rel="noopener" title="Dunes Discovery Tourism - 5.0 Star Top Rated Operator"><img src="'.asset('badges/top-rated-operator.svg').'" alt="Dunes Discovery Tourism - 5.0 Star Top Rated Operator" width="280" height="80" style="display:inline-block;max-width:100%;height:auto;border-radius:14px;" /></a>',
            ],
            [
                'id' => 'licensed',
                'name' => 'DTCM Government Licensed Badge',
                'description' => 'Emerald verified seal showcasing our official Dubai Department of Economy and Tourism license #1430583.',
                'image' => asset('badges/det-licensed-badge.svg'),
                'embed_code' => '<a href="'.url('/').'" target="_blank" rel="noopener" title="Dunes Discovery Tourism - DTCM Licensed Operator Dubai"><img src="'.asset('badges/det-licensed-badge.svg').'" alt="Dunes Discovery Tourism - DTCM Licensed Operator Dubai" width="280" height="80" style="display:inline-block;max-width:100%;height:auto;border-radius:14px;" /></a>',
            ],
        ];

        // Editorial Media Downloads (Verified Existing Assets in public/images/)
        $editorialImages = [
            [
                'title' => 'Lahbab Red Dunes 4x4 Dune Bashing Action',
                'category' => 'Desert Safaris',
                'filename' => 'evening-desert-safari-dubai-dune-discovery-tourism.avif',
                'credit' => 'Photo: Dunes Discovery Tourism LLC',
            ],
            [
                'title' => 'Golden Hour Sunset & Camel Caravan in Lahbab',
                'category' => 'Sunset & Landscape',
                'filename' => 'dubai-desert-safar-camel-ride-dune-discovery-tourism.avif',
                'credit' => 'Photo: Dunes Discovery Tourism LLC',
            ],
            [
                'title' => 'VIP 5-Star Bedouin Desert Oasis Camp & BBQ',
                'category' => 'Camp Experience',
                'filename' => 'dubai-desert-safari-bbq-dune-discovery-tourism.avif',
                'credit' => 'Photo: Dunes Discovery Tourism LLC',
            ],
            [
                'title' => 'High-Performance Can-Am Maverick Dune Buggy',
                'category' => 'Motorsports',
                'filename' => 'dubai-desert-safari-buggy-dune-discovery-tourism.avif',
                'credit' => 'Photo: Dunes Discovery Tourism LLC',
            ],
        ];

        return view('pages.press-kit', compact(
            'pageTitle',
            'pageDesc',
            'pageKeys',
            'canonical',
            'ogImage',
            'pageRobots',
            'facts',
            'badges',
            'editorialImages',
            'currentYear'
        ));
    }
}
