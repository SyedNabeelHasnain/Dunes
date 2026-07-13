<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminWhatsappController extends Controller
{
    /**
     * Display a listing of WhatsApp click leads.
     */
    public function index()
    {
        $leads = DB::table('whatsapp_inquiries')
            ->leftJoin('request_logs', 'whatsapp_inquiries.request_log_id', '=', 'request_logs.id')
            ->select('whatsapp_inquiries.*', 'request_logs.country', 'request_logs.city', 'request_logs.device_type', 'request_logs.browser_name', 'request_logs.os_name')
            ->orderBy('whatsapp_inquiries.created_at', 'desc')
            ->paginate(20);

        return view('admin.whatsapp.index', compact('leads'));
    }

    /**
     * Show WhatsApp click settings page.
     */
    public function settings()
    {
        $settings = Setting::where('setting_key', 'like', 'whatsapp_%')
            ->orWhere('setting_key', 'site_whatsapp')
            ->get()
            ->pluck('setting_value', 'setting_key');

        return view('admin.whatsapp.settings', compact('settings'));
    }

    /**
     * Update WhatsApp settings.
     */
    public function updateSettings(Request $request)
    {
        $settings = $request->except(['_token']);

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value !== null ? trim($value) : '']
            );
        }

        return redirect()->route('admin.whatsapp.settings')->with('success', 'WhatsApp settings updated successfully.');
    }
}
