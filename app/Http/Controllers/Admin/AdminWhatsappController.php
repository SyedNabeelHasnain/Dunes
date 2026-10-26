<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RequestLog;
use App\Models\Setting;
use App\Models\WhatsappInquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $status = $request->input('status');

        $query = DB::table('whatsapp_inquiries')
            ->leftJoin('request_logs', 'whatsapp_inquiries.request_log_id', '=', 'request_logs.id')
            ->select(
                'whatsapp_inquiries.*',
                DB::raw("COALESCE(NULLIF(whatsapp_inquiries.client_ip, ''), NULLIF(request_logs.client_ip, ''), 'Not Available') as client_ip"),
                DB::raw("COALESCE(NULLIF(whatsapp_inquiries.country, ''), NULLIF(request_logs.country, ''), 'Unknown') as country"),
                DB::raw("COALESCE(NULLIF(whatsapp_inquiries.city, ''), NULLIF(request_logs.city, ''), 'Unknown') as city"),
                DB::raw("COALESCE(NULLIF(whatsapp_inquiries.region, ''), NULLIF(request_logs.region, ''), '') as region"),
                DB::raw("COALESCE(NULLIF(whatsapp_inquiries.device_type, ''), NULLIF(request_logs.device_type, ''), 'Desktop') as device_type"),
                DB::raw("COALESCE(NULLIF(whatsapp_inquiries.browser_name, ''), NULLIF(request_logs.browser_name, ''), 'Unknown') as browser_name"),
                DB::raw("COALESCE(NULLIF(whatsapp_inquiries.os_name, ''), NULLIF(request_logs.os_name, ''), 'Unknown') as os_name"),
                DB::raw("COALESCE(NULLIF(whatsapp_inquiries.isp, ''), NULLIF(request_logs.isp, ''), 'Not Available') as isp"),
                DB::raw("COALESCE(NULLIF(whatsapp_inquiries.organization, ''), NULLIF(request_logs.organization, ''), 'Not Available') as organization"),
                DB::raw("COALESCE(NULLIF(whatsapp_inquiries.utm_source, ''), NULLIF(request_logs.utm_source, ''), 'Direct') as utm_source"),
                DB::raw("COALESCE(NULLIF(whatsapp_inquiries.utm_campaign, ''), NULLIF(request_logs.utm_campaign, ''), 'Not Available') as utm_campaign")
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
        if ($status === 'new' || $status === 'unread') {
            $query->where(function ($q) {
                $q->where('whatsapp_inquiries.is_read', false)
                    ->orWhereNull('whatsapp_inquiries.is_read')
                    ->orWhere('whatsapp_inquiries.status', 'new');
            });
        } elseif ($status === 'read') {
            $query->where('whatsapp_inquiries.is_read', true);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
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
        $unreadLeads = DB::table('whatsapp_inquiries')
            ->where(function ($q) {
                $q->where('is_read', false)->orWhereNull('is_read');
            })
            ->count();

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
            'unread' => $unreadLeads,
        ];

        // 2. 14-Day Acquisition Trend Chart Data
        $dailyCounts = WhatsappInquiry::where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        $trendData = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $label = now()->subDays($i)->format('M j');
            $trendData[] = [
                'date' => $label,
                'count' => (int) ($dailyCounts[$date] ?? 0),
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
            'search',
            'status'
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
            'whatsapp_greeting', 'whatsapp_prefill_message', 'whatsapp_form_enabled',
        ];

        $settings = $request->only($allowedKeys);

        if ($request->has('whatsapp_form_enabled')) {
            $settings['whatsapp_form_enabled'] = $request->input('whatsapp_form_enabled') == '1' ? '1' : '0';
        }

        if (!empty($settings['site_whatsapp']) && empty($settings['whatsapp_number'])) {
            $settings['whatsapp_number'] = $settings['site_whatsapp'];
        }

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value !== null ? trim((string) $value) : '']
            );
        }

        Cache::forget('site_settings_cache');
        Cache::forget('site_home_cache');
        app(\App\Services\SettingsService::class)->clearCache();

        return redirect()->route('admin.whatsapp.settings')->with('success', 'WhatsApp settings updated successfully.');
    }

    /**
     * Export WhatsApp leads to CSV.
     */
    public function exportCsv(Request $request)
    {
        $fileName = 'dunes-whatsapp-leads-'.date('Y-m-d-His').'.csv';
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

        if ($request->filled('from_date')) {
            $query->whereDate('whatsapp_inquiries.created_at', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('whatsapp_inquiries.created_at', '<=', $request->input('to_date'));
        }
        if ($request->filled('tour_name')) {
            $query->where('whatsapp_inquiries.tour_name', $request->input('tour_name'));
        }
        if ($request->filled('device_type')) {
            $query->where('request_logs.device_type', $request->input('device_type'));
        }
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('whatsapp_inquiries.name', 'like', "%{$search}%")
                    ->orWhere('whatsapp_inquiries.phone', 'like', "%{$search}%")
                    ->orWhere('whatsapp_inquiries.tour_name', 'like', "%{$search}%")
                    ->orWhere('whatsapp_inquiries.message_text', 'like', "%{$search}%");
            });
        }

        $leads = $query->orderBy('whatsapp_inquiries.created_at', 'desc')->get();

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'ID', 'Timestamp', 'Customer Name', 'Phone Number', 'Email', 'Tour / Activity',
            'Traffic Channel', 'Initial Referrer', 'Landing Page', 'Stay Duration (Sec)', 'Pages Viewed',
            'Google Ads GCLID', 'Meta FBCLID', 'Prospect Source', 'Source Page URL',
            'Prefill Message', 'Client IP', 'Location', 'ISP / Carrier', 'Device Type', 'OS / Browser',
        ];

        $sanitize = function (array $row): array {
            return array_map(function ($val) {
                return sanitize_csv_cell($val);
            }, $row);
        };

        $callback = function () use ($leads, $columns, $sanitize) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            foreach ($leads as $lead) {
                fputcsv($file, $sanitize([
                    $lead->id,
                    $lead->created_at,
                    $lead->name ?: 'Visitor',
                    $lead->phone ?: '',
                    $lead->email ?: '',
                    $lead->tour_name ?: 'General',
                    $lead->traffic_channel ?: ($lead->utm_source ?: 'Direct'),
                    $lead->initial_referrer ?: ($lead->referrer_url ?: 'Direct / None'),
                    $lead->landing_page ?: ($lead->page_url ?: ''),
                    $lead->session_duration_seconds ?? 0,
                    $lead->pages_viewed_count ?? 1,
                    $lead->gclid ?: '',
                    $lead->fbclid ?: '',
                    $lead->prospect_source ?: 'Direct Click',
                    $lead->page_url ?: '',
                    $lead->message_text ?: '',
                    $lead->client_ip ?: '',
                    ($lead->city ?: 'Unknown').', '.($lead->country ?: ''),
                    $lead->isp ?: ($lead->organization ?: ''),
                    $lead->device_type ?: 'Desktop',
                    ($lead->os_name ?: '').' / '.($lead->browser_name ?: ''),
                ]));
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Permanently delete a single WhatsApp lead and its telemetry footprints.
     */
    public function destroy(Request $request, int $id)
    {
        try {
            $lead = DB::table('whatsapp_inquiries')->where('id', $id)->first();
            if (! $lead) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'WhatsApp lead not found.'], 404);
                }

                return redirect()->route('admin.whatsapp.leads')->with('error', 'WhatsApp lead not found.');
            }

            $leadName = $lead->name ?: 'Lead';
            $this->purgeLeadData($id, $lead->request_log_id ? (int) $lead->request_log_id : null);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "WhatsApp lead ({$leadName}) and all associated records permanently purged.",
                ]);
            }

            return redirect()->route('admin.whatsapp.leads')
                ->with('success', "WhatsApp lead ({$leadName}) and all associated records permanently purged.");
        } catch (\Throwable $e) {
            Log::error("Failed to delete WhatsApp lead #{$id}: ".$e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete lead: '.$e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Failed to delete lead: '.$e->getMessage());
        }
    }

    /**
     * Execute complete cascade purge of a WhatsApp lead and its telemetry footprints.
     */
    private function purgeLeadData(int $id, ?int $requestLogId = null): void
    {
        DB::transaction(function () use ($id, $requestLogId) {
            // 1. Delete associated request_log by direct ID
            if ($requestLogId) {
                RequestLog::where('id', $requestLogId)->delete();
            }

            // 2. Delete any request_logs linked by entity_type and entity_id
            RequestLog::whereIn('entity_type', ['whatsapp', 'whatsapp_inquiry', 'whatsapp_lead'])
                ->where('entity_id', $id)
                ->delete();

            // 3. Delete WhatsApp inquiry record
            DB::table('whatsapp_inquiries')->where('id', $id)->delete();
        });
    }

    /**
     * Mark a WhatsApp lead as read (AJAX or direct).
     */
    public function markRead(Request $request, int $id)
    {
        $lead = WhatsappInquiry::find($id);
        if (! $lead) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Lead not found.'], 404);
            }

            return redirect()->back()->with('error', 'Lead not found.');
        }

        $lead->markAsRead();

        $unreadCount = WhatsappInquiry::where(function ($q) {
            $q->where('is_read', false)->orWhereNull('is_read');
        })->count();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'WhatsApp lead marked as read.',
                'is_read' => true,
                'status' => $lead->status,
                'unread_count' => $unreadCount,
            ]);
        }

        return redirect()->back()->with('success', 'WhatsApp lead marked as read.');
    }

    /**
     * Toggle read/unread status for a WhatsApp lead.
     */
    public function toggleRead(Request $request, int $id)
    {
        $lead = WhatsappInquiry::findOrFail($id);
        $newRead = ! (bool) $lead->is_read;
        $lead->is_read = $newRead;
        $lead->status = $newRead ? 'read' : 'new';
        $lead->read_at = $newRead ? now() : null;
        $lead->save();

        $unreadCount = WhatsappInquiry::where(function ($q) {
            $q->where('is_read', false)->orWhereNull('is_read');
        })->count();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $newRead ? 'Lead marked as read.' : 'Lead marked as unread.',
                'is_read' => $newRead,
                'status' => $lead->status,
                'unread_count' => $unreadCount,
            ]);
        }

        return redirect()->back()->with('success', 'Lead status updated.');
    }

    /**
     * Perform batch operations across multiple WhatsApp inquiries.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer',
            'action' => 'required|string|in:delete,mark_read,mark_unread',
        ]);

        $ids = $request->input('ids');
        $action = $request->input('action');

        try {
            if ($action === 'delete') {
                $count = 0;
                DB::transaction(function () use ($ids, &$count) {
                    $leads = DB::table('whatsapp_inquiries')->whereIn('id', $ids)->get();
                    foreach ($leads as $lead) {
                        $this->purgeLeadData($lead->id, $lead->request_log_id ? (int) $lead->request_log_id : null);
                        $count++;
                    }
                });

                return response()->json([
                    'success' => true,
                    'message' => "{$count} lead(s) and all associated analytics permanently purged.",
                ]);
            }

            if ($action === 'mark_read') {
                WhatsappInquiry::whereIn('id', $ids)->update([
                    'is_read' => true,
                    'status' => 'read',
                    'read_at' => now(),
                ]);

                $unreadCount = WhatsappInquiry::where(function ($q) {
                    $q->where('is_read', false)->orWhereNull('is_read');
                })->count();

                return response()->json([
                    'success' => true,
                    'message' => count($ids).' lead(s) marked as read.',
                    'unread_count' => $unreadCount,
                ]);
            }

            if ($action === 'mark_unread') {
                WhatsappInquiry::whereIn('id', $ids)->update([
                    'is_read' => false,
                    'status' => 'new',
                    'read_at' => null,
                ]);

                $unreadCount = WhatsappInquiry::where(function ($q) {
                    $q->where('is_read', false)->orWhereNull('is_read');
                })->count();

                return response()->json([
                    'success' => true,
                    'message' => count($ids).' lead(s) marked as unread.',
                    'unread_count' => $unreadCount,
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Invalid action.'], 400);
        } catch (\Throwable $e) {
            Log::error('Bulk action failed on WhatsApp inquiries: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process bulk action: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Re-lookup GeoIP & Telemetry for a WhatsApp Lead on-demand.
     */
    public function refreshTelemetry(Request $request, int $id)
    {
        $lead = WhatsappInquiry::findOrFail($id);
        $tracker = app(\App\Services\VisitorTrackerService::class);

        $ip = trim((string) $request->input('client_ip', $lead->client_ip));
        if (empty($ip) || in_array(strtolower($ip), ['not available', '0.0.0.0', '-', 'unknown'])) {
            // Check request log if linked
            if ($lead->request_log_id) {
                $reqLog = RequestLog::find($lead->request_log_id);
                if ($reqLog && ! empty($reqLog->client_ip) && ! in_array(strtolower($reqLog->client_ip), ['not available', '0.0.0.0', '127.0.0.1'])) {
                    $ip = $reqLog->client_ip;
                }
            }
        }

        $isPublic = (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);

        $updates = [];
        if ($isPublic && $ip !== $lead->client_ip) {
            $updates['client_ip'] = $ip;
        }

        if ($isPublic) {
            // Clear cache for fresh lookup
            Cache::forget("geoip_{$ip}");
            $geo = $tracker->ipLookup($ip);
            if ($geo) {
                if (! empty($geo['country'])) {
                    $updates['country'] = $geo['country'];
                }
                if (! empty($geo['city'])) {
                    $updates['city'] = $geo['city'];
                }
                if (! empty($geo['regionName']) || ! empty($geo['region'])) {
                    $updates['region'] = $geo['regionName'] ?? $geo['region'];
                }
                if (! empty($geo['isp'])) {
                    $updates['isp'] = $geo['isp'];
                }
                if (! empty($geo['org'])) {
                    $updates['organization'] = $geo['org'];
                }
                if (! empty($geo['timezone']) && empty($lead->client_timezone)) {
                    $updates['client_timezone'] = $geo['timezone'];
                }
            }
        }

        // If client passed manual overrides
        if ($request->filled('country')) {
            $updates['country'] = $request->input('country');
        }
        if ($request->filled('city')) {
            $updates['city'] = $request->input('city');
        }
        if ($request->filled('isp')) {
            $updates['isp'] = $request->input('isp');
        }

        if (! empty($updates)) {
            $lead->update($updates);
        }

        $loc = trim(($lead->city ?: 'Unknown').', '.($lead->country ?: 'Unknown'), ', ');

        return response()->json([
            'success' => true,
            'message' => 'Telemetry refreshed successfully.',
            'lead' => [
                'id' => $lead->id,
                'client_ip' => $lead->client_ip ?: 'Not Available',
                'country' => $lead->country ?: 'Unknown',
                'city' => $lead->city ?: 'Unknown',
                'region' => $lead->region ?: '',
                'isp' => $lead->isp ?: ($lead->organization ?: 'Not Available'),
                'location' => $loc,
            ],
        ]);
    }
}
