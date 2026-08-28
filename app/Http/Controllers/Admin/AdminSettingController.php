<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
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
     * Update bulk settings.
     */
    public function update(Request $request)
    {
        $allowedKeys = [
            'google_analytics_id', 'google_tag_manager_id', 'google_ads_id',
            'google_maps_api_key', 'recaptcha_site_key', 'recaptcha_secret_key',
            'meta_pixel_id', 'meta_access_token', 'meta_active', 'meta_capi_enabled',
            'site_email', 'admin_email', 'admin_email_cc', 'admin_email_bcc',
            'ziina_active', 'ziina_access_token', 'ziina_test_mode', 'ziina_advance_percent',
            'cache_version',
        ];

        $settings = $request->only($allowedKeys);

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value !== null ? trim($value) : '']
            );
        }

        \Illuminate\Support\Facades\Cache::forget('site_settings_cache');

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

        \Illuminate\Support\Facades\Cache::forget('site_settings_cache');
        \Illuminate\Support\Facades\Cache::forget('site_tours_header_cache');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'All system, route, configuration, and view caches purged successfully.'
            ]);
        }

        return back()->with('success', 'Application and view cache cleared successfully.');
    }
}
