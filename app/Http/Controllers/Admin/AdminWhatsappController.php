<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminWhatsappController extends Controller
{
    /**
     * Display a listing of WhatsApp click leads with analytics & filters.
     */
    public function index(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $tourName = $request->input('tour_name');
        $deviceType = $request->input('device_type');
        $search = $request->input('search');

        $query = DB::table('whatsapp_inquiries')
            ->leftJoin('request_logs', 'whatsapp_inquiries.request_log_id', '=', 'request_logs.id')
            ->select(
                'whatsapp_inquiries.*',
                'request_logs.client_ip',
                'request_logs.country',
                'request_logs.city',
                'request_logs.device_type',
                'request_logs.browser_name',
                'request_logs.os_name'
            );

        if ($fromDate) {
            $query->whereDate('whatsapp_inquiries.created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('whatsapp_inquiries.created_at', '<=', $toDate);
        }
        if ($tourName) {
            $query->where('whatsapp_inquiries.tour_name', $tourName);
        }
        if ($deviceType) {
            $query->where('request_logs.device_type', $deviceType);
        }
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('whatsapp_inquiries.name', 'like', "%{$search}%")
                  ->orWhere('whatsapp_inquiries.phone', 'like', "%{$search}%")
                  ->orWhere('whatsapp_inquiries.tour_name', 'like', "%{$search}%")
                  ->orWhere('whatsapp_inquiries.message_text', 'like', "%{$search}%");
            });
        }

        $leads = $query->orderBy('whatsapp_inquiries.created_at', 'desc')->get();

        // 1. Overall Key Metric Statistics
        $totalLeads = DB::table('whatsapp_inquiries')->count();
        $todayLeads = DB::table('whatsapp_inquiries')->whereDate('created_at', today())->count();
        $monthLeads = DB::table('whatsapp_inquiries')->where('created_at', '>=', now()->startOfMonth())->count();
        
        $mobileCount = DB::table('whatsapp_inquiries')
            ->leftJoin('request_logs', 'whatsapp_inquiries.request_log_id', '=', 'request_logs.id')
            ->where('request_logs.device_type', 'mobile')
            ->count();
        $mobilePct = $totalLeads > 0 ? round(($mobileCount / $totalLeads) * 100) : 0;

        $stats = [
            'total' => $totalLeads,
            'today' => $todayLeads,
            'this_month' => $monthLeads,
            'mobile_pct' => $mobilePct,
        ];

        // 2. 14-Day Acquisition Trend Chart Data
        $trendData = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $label = now()->subDays($i)->format('M j');
            $count = DB::table('whatsapp_inquiries')->whereDate('created_at', $date)->count();
            $trendData[] = [
                'date' => $label,
                'count' => $count
            ];
        }

        // 3. Tour Interest Breakdown Chart Data
        $tourBreakdown = DB::table('whatsapp_inquiries')
            ->select(DB::raw("COALESCE(NULLIF(tour_name, ''), 'General Inquiry') as tour_label"), DB::raw('count(*) as count'))
            ->groupBy('tour_label')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        // 4. Device Breakdown Chart Data
        $deviceBreakdown = DB::table('whatsapp_inquiries')
            ->leftJoin('request_logs', 'whatsapp_inquiries.request_log_id', '=', 'request_logs.id')
            ->select(DB::raw("COALESCE(NULLIF(request_logs.device_type, ''), 'Desktop') as device_label"), DB::raw('count(*) as count'))
            ->groupBy('device_label')
            ->get();

        // 5. Available distinct tours for filter dropdown
        $availableTours = DB::table('whatsapp_inquiries')
            ->whereNotNull('tour_name')
            ->where('tour_name', '!=', '')
            ->distinct()
            ->pluck('tour_name');

        return view('admin.whatsapp.index', compact(
            'leads',
            'stats',
            'trendData',
            'tourBreakdown',
            'deviceBreakdown',
            'availableTours',
            'fromDate',
            'toDate',
            'tourName',
            'deviceType',
            'search'
        ));
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
