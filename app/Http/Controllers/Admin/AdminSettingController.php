<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
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
            ->get()
            ->pluck('setting_value', 'setting_key');

        return view('admin.settings.google', compact('settings'));
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
     * Update bulk settings.
     */
    public function update(Request $request)
    {
        $allowedKeys = [
            // Site Identity & Licensing
            'site_name', 'site_phone', 'site_whatsapp', 'site_email', 'site_support_email',
            'site_address', 'company_license_number', 'google_maps_embed_url',
            'social_tripadvisor', 'social_google', 'social_facebook', 'social_instagram',
            'social_youtube', 'social_tiktok', 'footer_about', 'site_copyright',

            // Global & Per-Page SEO
            'seo_default_title', 'seo_default_description', 'seo_default_keywords', 'seo_default_og_image',
            'seo_home_title', 'seo_home_description', 'seo_home_keywords', 'seo_home_og_image',
            'seo_tours_title', 'seo_tours_description', 'seo_tours_keywords', 'seo_tours_og_image',
            'seo_blog_title', 'seo_blog_description', 'seo_blog_keywords', 'seo_blog_og_image',
            'seo_about_title', 'seo_about_description', 'seo_about_keywords', 'seo_about_og_image',
            'seo_contact_title', 'seo_contact_description', 'seo_contact_keywords', 'seo_contact_og_image',
            'seo_faq_title', 'seo_faq_description', 'seo_faq_keywords', 'seo_faq_og_image',
            'seo_rate_card_title', 'seo_rate_card_description', 'seo_rate_card_keywords', 'seo_rate_card_og_image',

            // Marketing & Promos
            'promo_top_banner_enabled', 'promo_top_banner_badge', 'promo_top_banner_text',
            'promo_top_banner_code', 'promo_top_banner_discount',
            'promo_welcome_modal_enabled', 'promo_welcome_modal_headline',
            'promo_welcome_modal_subheadline', 'promo_welcome_modal_discount',
            'promo_welcome_modal_timer_minutes', 'promo_welcome_modal_delay_seconds',

            // Google Integrations
            'google_active', 'google_gtm_id', 'google_ga4_id', 'google_analytics_id', 'google_tag_manager_id',
            'google_ads_id', 'google_conversion_label', 'google_site_verification', 'google_maps_api_key',
            'recaptcha_site_key', 'recaptcha_secret_key',

            // Meta Integrations
            'meta_active', 'meta_pixel_id', 'meta_access_token', 'meta_capi_enabled', 'meta_test_event_code',

            // Email & Payment Settings
            'admin_email', 'admin_email_cc', 'admin_email_bcc',
            'ziina_active', 'ziina_access_token', 'ziina_webhook_secret', 'ziina_test_mode', 'ziina_advance_percent',
            'cache_version',

            // Multi-Currency Exchange Rates
            'currency_rate_usd', 'currency_rate_eur', 'currency_rate_gbp', 'currency_rate_sar', 'currency_rate_inr',
            'currency_rates_last_synced',
        ];

        $settings = $request->only($allowedKeys);

        // Handle boolean switches that are omitted when unchecked
        if ($request->has('google_gtm_id') || $request->has('google_ga4_id') || $request->has('google_ads_id')) {
            $settings['google_active'] = $request->has('google_active') ? '1' : '0';
        }

        if ($request->has('meta_pixel_id') || $request->has('meta_access_token')) {
            $settings['meta_active'] = $request->has('meta_active') ? '1' : '0';
        }

        if ($request->has('promo_banner_form_submitted')) {
            $settings['promo_top_banner_enabled'] = $request->has('promo_top_banner_enabled') ? '1' : '0';
        }

        if ($request->has('promo_modal_form_submitted')) {
            $settings['promo_welcome_modal_enabled'] = $request->has('promo_welcome_modal_enabled') ? '1' : '0';
        }

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value !== null ? trim((string) $value) : '']
            );
        }

        Cache::forget('site_settings_cache');
        Cache::forget('site_home_cache');

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
}
