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
        $settings = $request->except(['_token']);

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value !== null ? trim($value) : '']
            );
        }

        // Return back to referring page or specific route
        return back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Clear application cache.
     */
    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');

        return back()->with('success', 'Application and view cache cleared successfully.');
    }
}
