<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GoogleThingsToDoFeedController;
use App\Models\Coupon;
use App\Models\Setting;
use App\Models\Tour;
use App\Services\IndexNowService;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminSettingController extends Controller
{
    /**
     * Show General Site Identity, Contact Information & Licensing.
     */
    public function general()
    {
        $keys = [
            'site_name', 'site_phone', 'site_whatsapp', 'site_email', 'site_support_email',
            'site_address', 'company_license_number', 'google_maps_embed_url',
            'social_tripadvisor', 'social_google', 'social_facebook', 'social_instagram',
            'social_youtube', 'social_tiktok', 'footer_about', 'site_copyright',
            'site_latitude', 'site_longitude', 'site_postal_code',
            'google_place_id', 'google_cid', 'google_review_url',
            'tripadvisor_location_id', 'tripadvisor_api_key',
            'site_hero_poster', 'site_default_image', 'seo_default_og_image',
            'site_logo', 'site_logo_white', 'site_hero_video', 'about_story_image',
            'admin_email', 'admin_email_cc', 'admin_email_bcc',
            'email_verification_required',
            'ziina_active', 'ziina_access_token', 'ziina_webhook_secret', 'ziina_test_mode', 'ziina_advance_percent', 'deposit_percentage',
        ];

        $settings = Setting::whereIn('setting_key', $keys)
            ->get()
            ->pluck('setting_value', 'setting_key');

        return view('admin.settings.general', compact('settings'));
    }

    /**
     * Show Sitewide & Per-Page SEO / Meta Management.
     */
    public function seo()
    {
        $keys = [
            'seo_default_title', 'seo_default_description', 'seo_default_keywords', 'seo_default_og_image',
            'seo_home_title', 'seo_home_description', 'seo_home_keywords', 'seo_home_og_image',
            'seo_tours_title', 'seo_tours_description', 'seo_tours_keywords', 'seo_tours_og_image',
            'seo_blog_title', 'seo_blog_description', 'seo_blog_keywords', 'seo_blog_og_image',
            'seo_about_title', 'seo_about_description', 'seo_about_keywords', 'seo_about_og_image',
            'seo_contact_title', 'seo_contact_description', 'seo_contact_keywords', 'seo_contact_og_image',
            'seo_faq_title', 'seo_faq_description', 'seo_faq_keywords', 'seo_faq_og_image',
            'seo_rate_card_title', 'seo_rate_card_description', 'seo_rate_card_keywords', 'seo_rate_card_og_image',
            'seo_sunset_title', 'seo_sunset_description', 'seo_sunset_keywords', 'seo_sunset_og_image',
            'seo_press_title', 'seo_press_description', 'seo_press_keywords', 'seo_press_og_image',
            'indexnow_enabled', 'indexnow_key', 'indexnow_last_submission',
            'google_site_verification', 'bing_site_verification', 'yandex_site_verification', 'baidu_site_verification',
            'clarity_id', 'clarity_active',
            'seo_index_tracking_params', 'schema_use_verified_review_count',
            'schema_homepage_rating', 'schema_homepage_review_count',
            'twitter_handle', 'site_description',
            'gsc_property_url', 'gsc_oauth_access_token', 'bing_webmaster_api_key',
            'site_hero_poster', 'site_default_image',
        ];

        $settings = Setting::whereIn('setting_key', $keys)
            ->get()
            ->pluck('setting_value', 'setting_key');

        return view('admin.settings.seo', compact('settings'));
    }

    /**
     * Show Marketing, Top Promo Banner & Welcome Modal Controls.
     */
    public function marketing()
    {
        return redirect()->route('admin.coupons.popup-settings');
    }

    /**
     * Show Google integrations settings.
     */
    public function google()
    {
        $settings = Setting::where('setting_key', 'like', 'google_%')
            ->orWhere('setting_key', 'like', 'recaptcha_%')
            ->orWhere('setting_key', 'like', 'turnstile_%')
            ->orWhere('setting_key', 'like', 'antispam_%')
            ->orWhereIn('setting_key', [
                'contact_email_otp_required',
                'site_latitude', 'site_longitude', 'site_postal_code',
                'company_license_number', 'site_address', 'site_name', 'site_phone',
                'google_place_id', 'google_cid', 'google_review_url',
                'google_business_hours', 'google_service_area', 'google_primary_category',
            ])
            ->get()
            ->pluck('setting_value', 'setting_key');

        $spamTelemetry = app(\App\Services\Security\ContactSpamGuardService::class)->getTelemetrySummary();

        return view('admin.settings.google', compact('settings', 'spamTelemetry'));
    }

    /**
     * Show Google Things to Do (GTTD) partner feed management dashboard.
     */
    public function gttd()
    {
        $keys = [
            'gttd_feed_enabled', 'gttd_partner_id', 'gttd_default_poi_place_id',
            'google_place_id', 'google_cid', 'google_review_url',
            'site_latitude', 'site_longitude', 'company_license_number', 'site_name',
        ];

        $settings = Setting::whereIn('setting_key', $keys)
            ->get()
            ->pluck('setting_value', 'setting_key');

        $activeToursCount = Tour::where('status', 'active')->count();
        $totalTiersCount = DB::table('tour_tiers')->count();

        $feedUrls = [
            'products_xml' => url('/feeds/google-things-to-do/products.xml'),
            'options_xml' => url('/feeds/google-things-to-do/options.xml'),
            'operators_xml' => url('/feeds/google-things-to-do/operators.xml'),
            'unified_xml' => url('/feeds/google-things-to-do/feed.xml'),
            'feed_json' => url('/feeds/google-things-to-do/feed.json'),
            'proto_json' => url('/feeds/google-things-to-do/proto.json'),
        ];

        return view('admin.settings.gttd', compact('settings', 'activeToursCount', 'totalTiersCount', 'feedUrls'));
    }

    /**
     * Flush Google Things to Do feed caches.
     */
    public function flushGttdCache()
    {
        GoogleThingsToDoFeedController::flushCache();

        return back()->with('success', 'Google Things To Do feeds cache flushed successfully.');
    }

    /**
     * Show Meta/Facebook integrations settings.
     */
    public function meta()
    {
        $settings = Setting::where('setting_key', 'like', 'meta_%')
            ->get()
            ->pluck('setting_value', 'setting_key');

        return view('admin.settings.meta', compact('settings'));
    }

    /**
     * Show multi-currency and live exchange rate management.
     */
    public function currency()
    {
        $settings = app(SettingsService::class);
        $rates = [
            'usd' => $settings->get('currency_rate_usd', '0.2723'),
            'eur' => $settings->get('currency_rate_eur', '0.2510'),
            'gbp' => $settings->get('currency_rate_gbp', '0.2150'),
            'sar' => $settings->get('currency_rate_sar', '1.0210'),
            'inr' => $settings->get('currency_rate_inr', '22.85'),
            'synced_at' => $settings->get('currency_rates_last_synced', 'Never'),
        ];

        return view('admin.settings.currency', compact('rates'));
    }

    /**
     * Show Comprehensive Notification & Alert Management Center.
     */
    public function notifications()
    {
        $keys = [
            'notification_from_name',
            'notification_from_email',
            'notification_reply_to',
            'notification_booking_customer_enabled',
            'notification_booking_customer_attach_pdf',
            'notification_booking_admin_enabled',
            'notification_booking_admin_attach_pdf',
            'notification_booking_recipients',
            'notification_whatsapp_admin_enabled',
            'notification_whatsapp_recipients',
            'notification_contact_admin_enabled',
            'notification_contact_recipients',
            'notification_contact_customer_enabled',
            'notification_contact_customer_mode',
            'notification_review_admin_enabled',
            'notification_review_recipients',
            'notification_webhook_enabled',
            'notification_webhook_url',
            'notification_webhook_secret',
            'admin_email',
            'admin_email_cc',
            'admin_email_bcc',
            'site_email',
        ];

        $settings = Setting::whereIn('setting_key', $keys)
            ->get()
            ->pluck('setting_value', 'setting_key')
            ->toArray();

        return view('admin.settings.notifications', compact('settings'));
    }

    /**
     * Save Notification Management Center settings.
     */
    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'notification_from_name' => 'nullable|string|max:255',
            'notification_from_email' => 'nullable|email:filter|max:255',
            'notification_reply_to' => 'nullable|email:filter|max:255',
            'notification_booking_customer_enabled' => 'nullable|in:0,1',
            'notification_booking_customer_attach_pdf' => 'nullable|in:0,1',
            'notification_booking_admin_enabled' => 'nullable|in:0,1',
            'notification_booking_admin_attach_pdf' => 'nullable|in:0,1',
            'notification_booking_recipients' => 'nullable|string|max:1000',
            'notification_whatsapp_admin_enabled' => 'nullable|in:0,1',
            'notification_whatsapp_recipients' => 'nullable|string|max:1000',
            'notification_contact_admin_enabled' => 'nullable|in:0,1',
            'notification_contact_recipients' => 'nullable|string|max:1000',
            'notification_contact_customer_enabled' => 'nullable|in:0,1',
            'notification_contact_customer_mode' => 'nullable|string|in:verified_only,clean_all,disabled',
            'notification_review_admin_enabled' => 'nullable|in:0,1',
            'notification_review_recipients' => 'nullable|string|max:1000',
            'notification_webhook_enabled' => 'nullable|in:0,1',
            'notification_webhook_url' => 'nullable|url|max:500',
            'notification_webhook_secret' => 'nullable|string|max:255',
        ]);

        $booleanKeys = [
            'notification_booking_customer_enabled',
            'notification_booking_customer_attach_pdf',
            'notification_booking_admin_enabled',
            'notification_booking_admin_attach_pdf',
            'notification_whatsapp_admin_enabled',
            'notification_contact_admin_enabled',
            'notification_contact_customer_enabled',
            'notification_review_admin_enabled',
            'notification_webhook_enabled',
        ];

        foreach ($booleanKeys as $bKey) {
            Setting::updateOrCreate(
                ['setting_key' => $bKey],
                ['setting_value' => $request->has($bKey) ? '1' : '0']
            );
        }

        $textKeys = [
            'notification_from_name',
            'notification_from_email',
            'notification_reply_to',
            'notification_booking_recipients',
            'notification_whatsapp_recipients',
            'notification_contact_recipients',
            'notification_contact_customer_mode',
            'notification_review_recipients',
            'notification_webhook_url',
            'notification_webhook_secret',
        ];

        foreach ($textKeys as $tKey) {
            Setting::updateOrCreate(
                ['setting_key' => $tKey],
                ['setting_value' => trim((string) ($validated[$tKey] ?? ''))]
            );
        }

        Cache::forget('site_settings_cache');
        app(SettingsService::class)->clearCache();

        return redirect()->route('admin.settings.notifications')
            ->with('success', 'Notification preferences and multi-recipient routing updated successfully.');
    }

    /**
     * Dispatch live diagnostic notification to verify email delivery and PDF attachment.
     */
    public function sendTestNotification(Request $request)
    {
        $validated = $request->validate([
            'event' => 'required|string|in:booking_customer,booking_admin,whatsapp_lead,contact_inquiry,customer_review',
            'test_email' => 'required|email:filter|max:255',
        ]);

        $notificationService = app(\App\Services\NotificationManagementService::class);
        $result = $notificationService->sendTestNotification($validated['event'], $validated['test_email']);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Safely and permanently purge all test bookings and their cascading records.
     */
    public function purgeTestBookings(Request $request)
    {
        try {
            $exitCode = Artisan::call('bookings:purge-test-bookings', ['--force' => true]);
            $rawOutput = Artisan::output();

            // Strip ANSI codes if any
            $cleanOutput = preg_replace('/\x1b\[[0-9;]*m/', '', $rawOutput);
            $cleanOutput = trim($cleanOutput);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $cleanOutput ?: 'All test bookings and associated artifacts permanently purged.',
                ]);
            }

            return redirect()->route('admin.settings.notifications')
                ->with('success', $cleanOutput ?: 'All test bookings and associated artifacts permanently purged.');
        } catch (\Throwable $e) {
            Log::error('Failed to purge test bookings: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to purge test bookings: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('admin.settings.notifications')
                ->with('error', 'Failed to purge test bookings: ' . $e->getMessage());
        }
    }

    /**
     * Update bulk settings.
     */
    public function update(Request $request)
    {
        $allowedKeys = [
            // Site Identity & Licensing
            'site_name', 'site_description', 'twitter_handle', 'site_phone', 'site_whatsapp', 'site_email', 'site_support_email',
            'site_address', 'company_license_number', 'google_maps_embed_url',
            'social_tripadvisor', 'social_google', 'social_facebook', 'social_instagram',
            'social_youtube', 'social_tiktok', 'footer_about', 'site_copyright',
            'site_hero_poster', 'site_hero_video', 'site_default_image', 'site_logo', 'site_logo_white', 'site_favicon', 'about_story_image',

            // Global & Per-Page SEO
            'seo_default_title', 'seo_default_description', 'seo_default_keywords', 'seo_default_og_image',
            'seo_home_title', 'seo_home_description', 'seo_home_keywords', 'seo_home_og_image',
            'seo_tours_title', 'seo_tours_description', 'seo_tours_keywords', 'seo_tours_og_image',
            'seo_blog_title', 'seo_blog_description', 'seo_blog_keywords', 'seo_blog_og_image',
            'seo_about_title', 'seo_about_description', 'seo_about_keywords', 'seo_about_og_image',
            'seo_contact_title', 'seo_contact_description', 'seo_contact_keywords', 'seo_contact_og_image',
            'seo_faq_title', 'seo_faq_description', 'seo_faq_keywords', 'seo_faq_og_image',
            'seo_rate_card_title', 'seo_rate_card_description', 'seo_rate_card_keywords', 'seo_rate_card_og_image',
            'seo_sunset_title', 'seo_sunset_description', 'seo_sunset_keywords', 'seo_sunset_og_image',
            'seo_press_title', 'seo_press_description', 'seo_press_keywords', 'seo_press_og_image',
            'seo_index_tracking_params', 'schema_use_verified_review_count', 'schema_homepage_rating', 'schema_homepage_review_count',
            'gsc_property_url', 'gsc_oauth_access_token', 'bing_webmaster_api_key',
            'indexnow_enabled', 'indexnow_key',

            // Marketing & Promos
            'promo_top_banner_enabled', 'promo_top_banner_badge', 'promo_top_banner_text',
            'promo_top_banner_code', 'promo_top_banner_discount',
            'promo_welcome_modal_enabled', 'promo_welcome_modal_headline',
            'promo_welcome_modal_subheadline', 'promo_welcome_modal_discount',
            'promo_welcome_modal_timer_minutes', 'promo_welcome_modal_delay_seconds',

            // Google Integrations & Business Profile (Local SEO)
            'google_active', 'google_gtm_id', 'google_ga4_id',
            'google_ads_id', 'google_conversion_label', 'google_site_verification', 'bing_site_verification', 'yandex_site_verification', 'baidu_site_verification', 'clarity_id', 'clarity_active', 'google_maps_api_key', 'google_places_api_key',
            'recaptcha_site_key', 'recaptcha_secret_key',
            'turnstile_enabled', 'turnstile_site_key', 'turnstile_secret_key',
            'antispam_enabled', 'antispam_mode', 'antispam_recaptcha_enabled', 'antispam_recaptcha_min_score',
            'antispam_min_dwell_seconds', 'antispam_auto_block_ip_enabled', 'antispam_auto_block_threshold',
            'antispam_check_email_mx', 'antispam_custom_blocked_keywords', 'antispam_custom_blocked_domains',
            'contact_email_otp_required',
            'google_place_id', 'google_cid', 'google_review_url',
            'site_latitude', 'site_longitude', 'site_postal_code',
            'google_business_hours', 'google_service_area', 'google_primary_category',

            // TripAdvisor Integrations & Content API
            'tripadvisor_location_id', 'tripadvisor_api_key',

            // Google Things To Do (GTTD) Partner Feeds
            'gttd_feed_enabled', 'gttd_partner_id', 'gttd_default_poi_place_id',

            // Meta Integrations
            'meta_active', 'meta_pixel_id', 'meta_access_token', 'meta_capi_token', 'meta_capi_enabled', 'meta_send_ip_ua', 'meta_test_event_code',

            // Email & Payment Settings
            'admin_email', 'admin_email_cc', 'admin_email_bcc',
            'email_verification_required',
            'ziina_active', 'ziina_access_token', 'ziina_webhook_secret', 'ziina_test_mode', 'ziina_advance_percent', 'deposit_percentage',

            // Social Proof & Concierge Widget Controls
            'safari_matcher_enabled',
            'social_proof_enabled', 'social_proof_mobile_enabled', 'social_proof_source',
            'social_proof_initial_delay', 'social_proof_duration', 'social_proof_interval',

            // Multi-Currency Exchange Rates
            'currency_rate_usd', 'currency_rate_eur', 'currency_rate_gbp', 'currency_rate_sar', 'currency_rate_inr',
            'currency_rates_last_synced',

            // Notification & Communication Controls
            'notification_from_name', 'notification_from_email', 'notification_reply_to',
            'notification_booking_customer_enabled', 'notification_booking_customer_attach_pdf',
            'notification_booking_admin_enabled', 'notification_booking_admin_attach_pdf', 'notification_booking_recipients',
            'notification_whatsapp_admin_enabled', 'notification_whatsapp_recipients',
            'notification_contact_admin_enabled', 'notification_contact_recipients', 'notification_contact_customer_enabled',
            'notification_review_admin_enabled', 'notification_review_recipients',
            'notification_webhook_enabled', 'notification_webhook_url', 'notification_webhook_secret',
        ];

        $settings = $request->only($allowedKeys);

        if (isset($settings['ziina_advance_percent']) && !isset($settings['deposit_percentage'])) {
            $settings['deposit_percentage'] = $settings['ziina_advance_percent'];
        } elseif (isset($settings['deposit_percentage']) && !isset($settings['ziina_advance_percent'])) {
            $settings['ziina_advance_percent'] = $settings['deposit_percentage'];
        }

        // Handle uploaded image files & strict key validation
        $request->validate([
            'indexnow_key' => ['nullable', 'string', 'min:8', 'max:64', 'regex:/^(?!robots$|sitemap$|llms$|llms-full$|security$|index$)[a-zA-Z0-9_\-]+$/i'],
            'site_hero_poster_file' => 'nullable|file|mimes:jpeg,png,jpg,webp,avif|max:8192',
            'site_default_image_file' => 'nullable|file|mimes:jpeg,png,jpg,webp,avif|max:8192',
            'seo_default_og_image_file' => 'nullable|file|mimes:jpeg,png,jpg,webp,avif|max:8192',
            'seo_home_og_image_file' => 'nullable|file|mimes:jpeg,png,jpg,webp,avif|max:8192',
            'site_logo_file' => 'nullable|file|mimes:jpeg,png,jpg,webp,avif,svg|max:8192',
            'site_logo_white_file' => 'nullable|file|mimes:jpeg,png,jpg,webp,avif,svg|max:8192',
            'about_story_image_file' => 'nullable|file|mimes:jpeg,png,jpg,webp,avif|max:8192',
        ]);

        if (isset($settings['google_ads_id'])) {
            $settings['google_ads_id'] = normalize_google_ads_id($settings['google_ads_id']);
        }

        if ($request->hasFile('site_hero_poster_file')) {
            $file = $request->file('site_hero_poster_file');
            $ext = safe_uploaded_image_ext($file, 'avif');
            \App\Services\Security\SvgSanitizer::assertNoScriptPolyglot($file->getRealPath(), $ext);
            $filename = 'hero-poster-' . time() . '.' . $ext;
            $file->move(public_path('images'), $filename);
            $settings['site_hero_poster'] = '/images/' . $filename;
        }

        if ($request->hasFile('seo_default_og_image_file') || $request->hasFile('site_default_image_file')) {
            $file = $request->file('seo_default_og_image_file') ?: $request->file('site_default_image_file');
            $ext = safe_uploaded_image_ext($file, 'avif');
            \App\Services\Security\SvgSanitizer::assertNoScriptPolyglot($file->getRealPath(), $ext);
            $filename = 'og-default-' . time() . '.' . $ext;
            $file->move(public_path('images'), $filename);
            $settings['seo_default_og_image'] = '/images/' . $filename;
            $settings['site_default_image'] = '/images/' . $filename;
        }

        if ($request->hasFile('seo_home_og_image_file')) {
            $file = $request->file('seo_home_og_image_file');
            $ext = safe_uploaded_image_ext($file, 'avif');
            \App\Services\Security\SvgSanitizer::assertNoScriptPolyglot($file->getRealPath(), $ext);
            $filename = 'og-home-' . time() . '.' . $ext;
            $file->move(public_path('images'), $filename);
            $settings['seo_home_og_image'] = '/images/' . $filename;
        }

        if ($request->hasFile('site_logo_file')) {
            $file = $request->file('site_logo_file');
            $rawExt = strtolower((string) ($file->getClientOriginalExtension() ?: 'png'));
            $ext = $rawExt === 'svg' ? 'svg' : safe_uploaded_image_ext($file, 'png');
            if ($ext === 'svg') {
                \App\Services\Security\SvgSanitizer::sanitizeFile($file->getRealPath());
            } else {
                \App\Services\Security\SvgSanitizer::assertNoScriptPolyglot($file->getRealPath(), $ext);
            }
            $filename = 'logo-' . time() . '.' . $ext;
            $file->move(public_path('images'), $filename);
            $settings['site_logo'] = '/images/' . $filename;
        }

        if ($request->hasFile('site_logo_white_file')) {
            $file = $request->file('site_logo_white_file');
            $rawExt = strtolower((string) ($file->getClientOriginalExtension() ?: 'png'));
            $ext = $rawExt === 'svg' ? 'svg' : safe_uploaded_image_ext($file, 'png');
            if ($ext === 'svg') {
                \App\Services\Security\SvgSanitizer::sanitizeFile($file->getRealPath());
            } else {
                \App\Services\Security\SvgSanitizer::assertNoScriptPolyglot($file->getRealPath(), $ext);
            }
            $filename = 'logo-white-' . time() . '.' . $ext;
            $file->move(public_path('images'), $filename);
            $settings['site_logo_white'] = '/images/' . $filename;
        }

        if ($request->hasFile('about_story_image_file')) {
            $file = $request->file('about_story_image_file');
            $ext = safe_uploaded_image_ext($file, 'avif');
            \App\Services\Security\SvgSanitizer::assertNoScriptPolyglot($file->getRealPath(), $ext);
            $filename = 'about-story-' . time() . '.' . $ext;
            $file->move(public_path('images'), $filename);
            $settings['about_story_image'] = '/images/' . $filename;
        }

        // Handle boolean switches that are omitted when unchecked
        if ($request->has('google_gtm_id') || $request->has('google_ga4_id') || $request->has('google_ads_id') || $request->has('google_active')) {
            $settings['google_active'] = $request->boolean('google_active') ? '1' : '0';
        }

        if ($request->has('meta_pixel_id') || $request->has('meta_access_token') || $request->has('meta_capi_token') || $request->has('meta_form_submitted')) {
            $settings['meta_active'] = $request->boolean('meta_active') ? '1' : '0';
            if ($request->has('meta_form_submitted')) {
                $settings['meta_capi_enabled'] = $request->boolean('meta_capi_enabled') ? '1' : '0';
                if ($request->has('meta_send_ip_ua')) {
                    $settings['meta_send_ip_ua'] = $request->boolean('meta_send_ip_ua') ? '1' : '0';
                }
            }
        }

        if ($request->has('gttd_form_submitted')) {
            $settings['gttd_feed_enabled'] = $request->boolean('gttd_feed_enabled') ? '1' : '0';
        }

        if ($request->has('indexnow_form_submitted')) {
            $settings['indexnow_enabled'] = $request->boolean('indexnow_enabled') ? '1' : '0';
        }

        if ($request->has('seo_form_submitted')) {
            $settings['seo_index_tracking_params'] = $request->boolean('seo_index_tracking_params') ? '1' : '0';
            if ($request->has('schema_use_verified_review_count_present')) {
                $settings['schema_use_verified_review_count'] = $request->boolean('schema_use_verified_review_count') ? '1' : '0';
            }
        }

        $actor = $request->user();
        $settingKeys = array_keys($settings);
        $securitySensitivePrefixes = ['security_', 'ziina_', 'antispam_', 'recaptcha_', 'turnstile_', 'notification_webhook_', 'admin_email'];
        $securitySensitiveExact = ['meta_access_token', 'meta_capi_token', 'tripadvisor_api_key', 'email_verification_required'];
        $hasSecurityKeys = collect($settingKeys)->contains(function ($k) use ($securitySensitivePrefixes, $securitySensitiveExact) {
            if (in_array($k, $securitySensitiveExact, true)) {
                return true;
            }
            foreach ($securitySensitivePrefixes as $prefix) {
                if (str_starts_with($k, $prefix)) {
                    return true;
                }
            }

            return false;
        });
        $hasPrivacyKeys = collect($settingKeys)->contains(fn ($k) => str_starts_with($k, 'privacy_'));
        $hasSeoKeys = collect($settingKeys)->contains(fn ($k) => str_starts_with($k, 'seo_')
            || str_starts_with($k, 'indexnow_')
            || str_starts_with($k, 'robots_')
            || str_starts_with($k, 'schema_')
            || str_starts_with($k, 'gsc_')
            || str_starts_with($k, 'bing_'));

        if ($actor && method_exists($actor, 'hasPermission')) {
            if ($hasSecurityKeys && ! $actor->hasAnyPermission(['security.manage', '*'])) {
                abort(403, 'Insufficient permissions to modify security configuration.');
            }
            if ($hasPrivacyKeys && ! $actor->hasAnyPermission(['privacy.manage', '*'])) {
                abort(403, 'Insufficient permissions to modify privacy & consent configuration.');
            }
            if ($hasSeoKeys && ! $actor->hasAnyPermission(['seo.manage', '*'])) {
                abort(403, 'Insufficient permissions to modify SEO settings.');
            }
            if (! $hasSecurityKeys && ! $hasPrivacyKeys && ! $hasSeoKeys && ! $actor->hasAnyPermission(['seo.manage', 'security.manage', 'privacy.manage', '*'])) {
                abort(403, 'Insufficient permissions to update site settings.');
            }
        }

        $existingValues = Setting::whereIn('setting_key', $settingKeys)
            ->pluck('setting_value', 'setting_key')
            ->toArray();

        $sensitiveApprovalKeys = array_values(array_filter($settingKeys, function ($key) {
            return str_starts_with($key, 'seo_default_')
                || str_starts_with($key, 'robots_')
                || str_starts_with($key, 'security_')
                || str_starts_with($key, 'privacy_');
        }));

        if ($sensitiveApprovalKeys !== [] && $actor && method_exists($actor, 'hasPermission') && ! $actor->hasAnyPermission(['governance.approve', '*'])) {
            $section = $hasSecurityKeys ? 'security' : ($hasPrivacyKeys ? 'privacy' : 'seo_global');
            $proposedPayload = array_intersect_key($settings, array_flip($sensitiveApprovalKeys));
            $previousPayload = array_intersect_key($existingValues, array_flip($sensitiveApprovalKeys));

            $approval = app(\App\Services\Governance\ApprovalWorkflowService::class)->propose(
                section: $section,
                targetKey: implode(', ', array_slice($sensitiveApprovalKeys, 0, 4)),
                summary: 'Proposed sensitive settings update ('.count($sensitiveApprovalKeys).' keys)',
                payload: $proposedPayload,
                previousPayload: $previousPayload,
                requester: $actor,
            );

            // Apply remaining non-sensitive keys if any
            $settings = array_diff_key($settings, $proposedPayload);
            if ($settings === []) {
                return back()->with('success', "Sensitive configuration changes submitted for approval (#{$approval->id}).");
            }
        }

        $audit = app(\App\Services\Governance\ConfigAuditService::class);
        $secretKeys = [
            'ziina_access_token', 'ziina_webhook_secret', 'tripadvisor_api_key',
            'google_places_api_key', 'google_maps_api_key', 'recaptcha_secret_key',
            'turnstile_secret_key', 'gsc_oauth_access_token', 'bing_webmaster_api_key',
            'meta_access_token', 'meta_capi_token', 'notification_webhook_secret',
        ];
        foreach ($settings as $key => $value) {
            $normalizedNew = $value !== null ? trim((string) $value) : '';
            $oldVal = $existingValues[$key] ?? null;

            // Preserve stored secrets when password inputs are intentionally submitted blank
            if ($normalizedNew === '' && in_array($key, $secretKeys, true) && ! empty($oldVal) && ! $request->boolean('clear_'.$key)) {
                continue;
            }

            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $normalizedNew]
            );

            if ((string) $oldVal !== $normalizedNew) {
                $auditSection = match (true) {
                    str_starts_with($key, 'security_') => 'security',
                    str_starts_with($key, 'privacy_') => 'privacy',
                    str_starts_with($key, 'seo_'), str_starts_with($key, 'indexnow_'), str_starts_with($key, 'robots_') => 'seo',
                    str_starts_with($key, 'google_'), str_starts_with($key, 'meta_'), str_starts_with($key, 'clarity_') => 'tracking',
                    default => 'general',
                };
                $audit->log(
                    section: $auditSection,
                    action: 'updated',
                    settingKey: $key,
                    oldValue: $oldVal,
                    newValue: $normalizedNew,
                    entityType: Setting::class,
                    entityId: null,
                    summary: "Updated setting {$key}",
                    actor: $actor,
                );
            }
        }

        Cache::forget('site_settings_cache');
        Cache::forget('site_home_cache');
        app(SettingsService::class)->clearCache();
        app(IndexNowService::class)->ensureKeyFileExists();
        GoogleThingsToDoFeedController::flushCache();
        \App\Http\Controllers\SitemapController::flushAllSitemapCaches();

        return back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Clear application cache.
     */
    public function clearCache(Request $request)
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');

        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }

        Cache::forget('site_settings_cache');
        Cache::forget('site_tours_header_cache');
        Cache::forget('site_home_cache');
        Cache::forget('admin_dashboard_kpis');
        Cache::forget('admin_dashboard_top_tours');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'All system, route, configuration, and view caches purged successfully.',
            ]);
        }

        return back()->with('success', 'Application and view cache cleared successfully.');
    }

    /**
     * Run all pending database migrations securely from admin.
     */
    public function runMigrations(Request $request)
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Database migrations executed successfully: '.$output,
                ]);
            }

            return back()->with('success', 'Database migrations executed successfully: '.$output);
        } catch (\Throwable $e) {
            Log::error('Admin migration execution error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while running migrations. Details have been logged.',
                ], 500);
            }

            return back()->with('error', 'An error occurred while executing migrations.');
        }
    }

    /**
     * Trigger immediate live currency exchange rate synchronization.
     */
    public function syncExchangeRates(Request $request)
    {
        try {
            Artisan::call('currency:sync-rates');
            $settings = app(SettingsService::class);
            $settings->clearCache();
            $rates = [
                'usd' => $settings->get('currency_rate_usd', '0.2723'),
                'eur' => $settings->get('currency_rate_eur', '0.2510'),
                'gbp' => $settings->get('currency_rate_gbp', '0.2150'),
                'sar' => $settings->get('currency_rate_sar', '1.0210'),
                'inr' => $settings->get('currency_rate_inr', '22.85'),
                'synced_at' => $settings->get('currency_rates_last_synced', now()->toDateTimeString()),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Foreign exchange rates synchronized successfully with Open Exchange Rates API.',
                'rates' => $rates,
            ]);
        } catch (\Throwable $e) {
            Log::error('Currency sync error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to synchronize currency rates. Please verify connection.',
            ], 500);
        }
    }

    /**
     * Submit all public URLs to search engines via IndexNow protocol on demand.
     */
    public function pingIndexNow(IndexNowService $indexNow)
    {
        $result = $indexNow->submitAll();

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        if (!empty($result['is_verification_pending'])) {
            return back()->with('info', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}
