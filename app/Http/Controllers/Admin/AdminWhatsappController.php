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
            ->select('whatsapp_inquiries.*', 'request_logs.client_ip', 'request_logs.country', 'request_logs.city', 'request_logs.device_type', 'request_logs.browser_name', 'request_logs.os_name')
            ->orderBy('whatsapp_inquiries.created_at', 'desc')
            ->get();

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
        $allowedKeys = [
            'site_whatsapp', 'whatsapp_number', 'whatsapp_default_country',
            'whatsapp_greeting', 'whatsapp_prefill_message',
        ];

        $settings = $request->only($allowedKeys);

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value !== null ? trim($value) : '']
            );
        }

        return redirect()->route('admin.whatsapp.settings')->with('success', 'WhatsApp settings updated successfully.');
    }

    /**
     * Export WhatsApp leads to CSV.
     */
    public function exportCsv()
    {
        $fileName = 'dunes-whatsapp-leads-' . date('Y-m-d-His') . '.csv';
        $leads = DB::table('whatsapp_inquiries')
            ->leftJoin('request_logs', 'whatsapp_inquiries.request_log_id', '=', 'request_logs.id')
            ->select(
                'whatsapp_inquiries.*',
                'request_logs.client_ip',
                'request_logs.country',
                'request_logs.city',
                'request_logs.device_type',
                'request_logs.browser_name',
                'request_logs.os_name'
            )
            ->orderBy('whatsapp_inquiries.created_at', 'desc')
            ->get();

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = [
            'ID', 'Timestamp', 'Customer Name', 'Phone Number', 'Tour / Activity',
            'Source Page URL', 'Prefill Message', 'Client IP', 'Location', 'Device Type', 'OS / Browser'
        ];

        $callback = function() use ($leads, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            foreach ($leads as $lead) {
                fputcsv($file, [
                    $lead->id,
                    $lead->created_at,
                    $lead->name ?: 'Visitor',
                    $lead->phone ?: '',
                    $lead->tour_name ?: 'General',
                    $lead->page_url ?: '',
                    $lead->message ?: '',
                    $lead->client_ip ?: '',
                    ($lead->city ?: 'Unknown') . ', ' . ($lead->country ?: ''),
                    $lead->device_type ?: 'Desktop',
                    ($lead->os_name ?: '') . ' / ' . ($lead->browser_name ?: '')
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
