<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AdminSettingController extends Controller
{
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
            // Google Integrations
            'google_active', 'google_gtm_id', 'google_ga4_id', 'google_analytics_id', 'google_tag_manager_id',
            'google_ads_id', 'google_conversion_label', 'google_site_verification', 'google_maps_api_key',
            'recaptcha_site_key', 'recaptcha_secret_key',

            // Meta Integrations
            'meta_active', 'meta_pixel_id', 'meta_access_token', 'meta_capi_enabled', 'meta_test_event_code',

            // Email & General Settings
            'site_email', 'admin_email', 'admin_email_cc', 'admin_email_bcc', 'site_phone',
            'ziina_active', 'ziina_access_token', 'ziina_test_mode', 'ziina_advance_percent',
            'cache_version',

            // Multi-Currency Exchange Rates
            'currency_rate_usd', 'currency_rate_eur', 'currency_rate_gbp', 'currency_rate_sar', 'currency_rate_inr',
            'currency_rates_last_synced',
        ];

        $settings = $request->only($allowedKeys);

        // Handle boolean switch checkboxes that are omitted by browsers when unchecked
        if ($request->has('google_gtm_id') || $request->has('google_ga4_id') || $request->has('google_ads_id')) {
            $settings['google_active'] = $request->has('google_active') ? '1' : '0';
        }

        if ($request->has('meta_pixel_id') || $request->has('meta_access_token')) {
            $settings['meta_active'] = $request->has('meta_active') ? '1' : '0';
        }

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value !== null ? trim((string)$value) : '']
            );
        }

        \Illuminate\Support\Facades\Cache::forget('site_settings_cache');
        \Illuminate\Support\Facades\Cache::forget('site_home_cache');

        // Return back to referring page or specific route
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

        \Illuminate\Support\Facades\Cache::forget('site_settings_cache');
        \Illuminate\Support\Facades\Cache::forget('site_tours_header_cache');
        \Illuminate\Support\Facades\Cache::forget('site_home_cache');
        \Illuminate\Support\Facades\Cache::forget('admin_dashboard_kpis');
        \Illuminate\Support\Facades\Cache::forget('admin_dashboard_top_tours');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'All system, route, configuration, and view caches purged successfully.'
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
                    'message' => 'Database migrations executed successfully: ' . $output
                ]);
            }

            return back()->with('success', 'Database migrations executed successfully: ' . $output);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Admin migration execution error: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Migration error: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Migration error: ' . $e->getMessage());
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
                'rates' => $rates
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to synchronize currency rates: ' . $e->getMessage()
            ], 500);
        }
    }
}

