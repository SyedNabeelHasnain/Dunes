<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Contact;
use App\Models\Tour;
use App\Models\WhatsappInquiry;
use App\Services\VisitorTrackerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    /**
     * Display the Enterprise Reporting & BI Hub.
     */
    public function index(Request $request)
    {
        $dataset = $request->input('dataset', 'bookings');
        $datePreset = $request->input('date_preset', 'this_month');
        $channel = $request->input('traffic_channel');
        $status = $request->input('status');
        $paymentStatus = $request->input('payment_status');
        $tourId = $request->input('tour_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Resolve Date Range from Preset
        [$resolvedFrom, $resolvedTo] = $this->resolveDateRange($datePreset, $fromDate, $toDate);

        // Pre-built Executive BI KPI Summary
        $biKpis = $this->calculateExecutiveKpis($resolvedFrom, $resolvedTo);

        // Channel Attribution Breakdown for Charts & Cards
        $channelAttribution = $this->calculateChannelAttribution($resolvedFrom, $resolvedTo);

        // Tour Performance Matrix
        $tourPerformance = $this->calculateTourPerformance($resolvedFrom, $resolvedTo);

        // Build dynamic query for custom report builder
        $reportData = $this->buildReportQuery(
            $dataset,
            $resolvedFrom,
            $resolvedTo,
            $channel,
            $status,
            $paymentStatus,
            $tourId
        );

        $availableTours = Tour::orderBy('name')->get();
        $channelsList = ['Google Ads', 'Meta Ads', 'TikTok Ads', 'Bing Ads', 'Organic Search', 'Social', 'Email', 'Referral', 'Direct'];

        return view('admin.reports.index', compact(
            'dataset',
            'datePreset',
            'channel',
            'status',
            'paymentStatus',
            'tourId',
            'resolvedFrom',
            'resolvedTo',
            'biKpis',
            'channelAttribution',
            'tourPerformance',
            'reportData',
            'availableTours',
            'channelsList'
        ));
    }

    /**
     * Export custom or pre-built report as CSV.
     */
    public function exportCsv(Request $request)
    {
        $dataset = $request->input('dataset', 'bookings');
        $datePreset = $request->input('date_preset', 'all_time');
        $channel = $request->input('traffic_channel');
        $status = $request->input('status');
        $paymentStatus = $request->input('payment_status');
        $tourId = $request->input('tour_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        [$resolvedFrom, $resolvedTo] = $this->resolveDateRange($datePreset, $fromDate, $toDate);

        $report = $this->buildReportQuery(
            $dataset,
            $resolvedFrom,
            $resolvedTo,
            $channel,
            $status,
            $paymentStatus,
            $tourId
        );

        $fileName = "dunes-bi-report-{$dataset}-" . date('Y-m-d-His') . ".csv";

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = $report['columns'];
        $rows = $report['rows'];

        $callback = function () use ($columns, $rows) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for UTF-8
            fputcsv($file, array_values($columns));

            foreach ($rows as $row) {
                $line = [];
                foreach (array_keys($columns) as $colKey) {
                    $val = $row[$colKey] ?? '';
                    if (is_array($val)) {
                        $val = json_encode($val);
                    }
                    $line[] = sanitize_csv_cell((string) $val);
                }
                fputcsv($file, $line);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Print-friendly executive layout for PDF / printing.
     */
    public function printView(Request $request)
    {
        $dataset = $request->input('dataset', 'bookings');
        $datePreset = $request->input('date_preset', 'all_time');
        $channel = $request->input('traffic_channel');
        $status = $request->input('status');
        $paymentStatus = $request->input('payment_status');
        $tourId = $request->input('tour_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        [$resolvedFrom, $resolvedTo] = $this->resolveDateRange($datePreset, $fromDate, $toDate);

        $report = $this->buildReportQuery(
            $dataset,
            $resolvedFrom,
            $resolvedTo,
            $channel,
            $status,
            $paymentStatus,
            $tourId
        );

        return view('admin.reports.print', compact(
            'dataset',
            'datePreset',
            'resolvedFrom',
            'resolvedTo',
            'report',
            'channel',
            'status'
        ));
    }

    /**
     * Build report datasets dynamically based on user criteria.
     */
    protected function buildReportQuery(
        string $dataset,
        ?string $from,
        ?string $to,
        ?string $channel,
        ?string $status,
        ?string $paymentStatus,
        ?string $tourId
    ): array {
        switch ($dataset) {
            case 'drafts':
                return $this->buildDraftsReport($from, $to, $channel, $tourId);

            case 'whatsapp_leads':
                return $this->buildWhatsappReport($from, $to, $channel);

            case 'inquiries':
                return $this->buildInquiriesReport($from, $to, $channel, $status);

            case 'tours':
                return $this->buildToursReport($from, $to);

            case 'bookings':
            default:
                return $this->buildBookingsReport($from, $to, $channel, $status, $paymentStatus, $tourId);
        }
    }

    /**
     * Bookings report builder.
     */
    protected function buildBookingsReport(?string $from, ?string $to, ?string $channel, ?string $status, ?string $paymentStatus, ?string $tourId): array
    {
        $query = Booking::with(['tour', 'tier'])->where('status', '!=', 'draft');

        if ($from) $query->whereDate('tour_date', '>=', $from);
        if ($to) $query->whereDate('tour_date', '<=', $to);
        if ($channel) $query->where('traffic_channel', $channel);
        if ($status) $query->where('status', $status);
        if ($paymentStatus) $query->where('payment_status', $paymentStatus);
        if ($tourId) $query->where('tour_id', $tourId);

        $items = $query->orderBy('tour_date', 'desc')->get();

        $columns = [
            'reference' => 'Booking Ref',
            'created_at' => 'Booked Date',
            'tour_date' => 'Tour Date',
            'customer_name' => 'Customer Name',
            'phone' => 'Phone',
            'email' => 'Email',
            'tour_name' => 'Tour Package',
            'tier_name' => 'Tier',
            'guests' => 'Guests (A/C/I)',
            'pickup_location' => 'Pickup Location',
            'total_aed' => 'Total (AED)',
            'paid_aed' => 'Paid (AED)',
            'balance_aed' => 'Balance (AED)',
            'payment_status' => 'Payment Status',
            'status' => 'Status',
            'traffic_channel' => 'Acquisition Channel',
            'utm_campaign' => 'Campaign',
            'gclid' => 'Google GCLID',
        ];

        $totalRevenue = 0.0;
        $totalPaid = 0.0;
        $totalGuests = 0;

        $rows = [];
        foreach ($items as $b) {
            $totalRevenue += (float) $b->total;
            $totalPaid += (float) ($b->payment_amount ?: ($b->payment_status === 'paid' ? $b->total : 0));
            $totalGuests += (int) $b->adults + (int) $b->children;

            $rows[] = [
                'reference' => '#' . $b->reference,
                'created_at' => $b->created_at ? $b->created_at->format('Y-m-d H:i') : '',
                'tour_date' => $b->tour_date ? $b->tour_date->format('Y-m-d') : '',
                'customer_name' => $b->name,
                'phone' => $b->phone,
                'email' => $b->email,
                'tour_name' => $b->tour_name,
                'tier_name' => $b->tier_name ?: ($b->tier ? $b->tier->display_name : 'Standard'),
                'guests' => "{$b->adults} Ad / {$b->children} Ch / {$b->infants} Inf",
                'pickup_location' => $b->pickup_location ?: 'Dubai Hotel',
                'total_aed' => number_format((float) $b->total, 2, '.', ''),
                'paid_aed' => number_format((float) ($b->payment_amount ?: ($b->payment_status === 'paid' ? $b->total : 0)), 2, '.', ''),
                'balance_aed' => number_format((float) ($b->payment_status === 'paid' ? 0 : $b->balance_due), 2, '.', ''),
                'payment_status' => ucfirst($b->payment_status),
                'status' => ucfirst($b->status),
                'traffic_channel' => $b->traffic_channel ?: 'Direct',
                'utm_campaign' => $b->utm_campaign ?: '',
                'gclid' => $b->gclid ?: '',
            ];
        }

        $count = count($rows);
        $summary = [
            'count' => $count,
            'total_value' => $totalRevenue,
            'paid_value' => $totalPaid,
            'avg_order_value' => $count > 0 ? round($totalRevenue / $count, 2) : 0.0,
            'total_guests' => $totalGuests,
        ];

        return [
            'entity' => 'Bookings',
            'columns' => $columns,
            'rows' => $rows,
            'summary' => $summary,
        ];
    }

    /**
     * Drafts report builder.
     */
    protected function buildDraftsReport(?string $from, ?string $to, ?string $channel, ?string $tourId): array
    {
        $query = Booking::with(['tour', 'tier'])->where('status', 'draft');

        if ($from) $query->whereDate('created_at', '>=', $from);
        if ($to) $query->whereDate('created_at', '<=', $to);
        if ($channel) $query->where('traffic_channel', $channel);
        if ($tourId) $query->where('tour_id', $tourId);

        $items = $query->orderBy('created_at', 'desc')->get();

        $columns = [
            'reference' => 'Draft Ref',
            'created_at' => 'Dropped Time',
            'name' => 'Guest Name',
            'phone' => 'Phone',
            'email' => 'Email',
            'tour_name' => 'Tour Package',
            'tier_name' => 'Package Tier',
            'tour_date' => 'Target Date',
            'guests' => 'Guests',
            'total_aed' => 'Cart Value (AED)',
            'traffic_channel' => 'Acquisition Channel',
            'utm_campaign' => 'Campaign',
            'reachability' => 'Reachability',
        ];

        $totalVal = 0.0;
        $contactable = 0;
        $rows = [];

        foreach ($items as $d) {
            $hasPhone = ! empty($d->phone);
            $hasRealEmail = ! empty($d->email) && ! str_ends_with($d->email, '@guest.local');
            if ($hasPhone || $hasRealEmail) $contactable++;

            $totalVal += (float) $d->total;

            $reach = 'Incomplete';
            if ($hasPhone && $hasRealEmail) $reach = 'Phone + Email';
            elseif ($hasPhone) $reach = 'Phone Only';
            elseif ($hasRealEmail) $reach = 'Email Only';

            $rows[] = [
                'reference' => $d->reference,
                'created_at' => $d->created_at ? $d->created_at->format('Y-m-d H:i') : '',
                'name' => $d->name ?: 'Prospective Guest',
                'phone' => $d->phone ?: '',
                'email' => str_ends_with((string) $d->email, '@guest.local') ? '' : $d->email,
                'tour_name' => $d->tour_name,
                'tier_name' => $d->tier_name ?: 'Standard',
                'tour_date' => $d->tour_date ? $d->tour_date->format('Y-m-d') : '',
                'guests' => "{$d->adults} Ad / {$d->children} Ch",
                'total_aed' => number_format((float) $d->total, 2, '.', ''),
                'traffic_channel' => $d->traffic_channel ?: 'Direct',
                'utm_campaign' => $d->utm_campaign ?: '',
                'reachability' => $reach,
            ];
        }

        $count = count($rows);
        $summary = [
            'count' => $count,
            'total_value' => $totalVal,
            'contactable_count' => $contactable,
            'contactable_rate' => $count > 0 ? round(($contactable / $count) * 100, 1) : 0.0,
            'avg_order_value' => $count > 0 ? round($totalVal / $count, 2) : 0.0,
        ];

        return [
            'entity' => 'Abandoned Checkouts (Drafts)',
            'columns' => $columns,
            'rows' => $rows,
            'summary' => $summary,
        ];
    }

    /**
     * WhatsApp leads report builder.
     */
    protected function buildWhatsappReport(?string $from, ?string $to, ?string $channel): array
    {
        $query = WhatsappInquiry::query();

        if ($from) $query->whereDate('created_at', '>=', $from);
        if ($to) $query->whereDate('created_at', '<=', $to);
        if ($channel) $query->where('traffic_channel', $channel);

        $items = $query->orderBy('created_at', 'desc')->get();

        $columns = [
            'id' => 'Lead ID',
            'created_at' => 'Inquiry Time',
            'name' => 'Name',
            'phone' => 'Phone',
            'email' => 'Email',
            'tour_name' => 'Tour / Interest',
            'traffic_channel' => 'Channel',
            'country' => 'Location',
            'device_type' => 'Device',
            'message_snippet' => 'Message Snippet',
        ];

        $rows = [];
        foreach ($items as $w) {
            $rows[] = [
                'id' => '#' . $w->id,
                'created_at' => $w->created_at ? $w->created_at->format('Y-m-d H:i') : '',
                'name' => $w->name ?: 'WhatsApp Guest',
                'phone' => $w->phone,
                'email' => $w->email ?: '',
                'tour_name' => $w->tour_name ?: 'General',
                'traffic_channel' => $w->traffic_channel ?: 'Direct',
                'country' => implode(', ', array_filter([$w->city, $w->country])) ?: 'Unknown',
                'device_type' => ucfirst($w->device_type ?: 'Mobile'),
                'message_snippet' => Str::limit($w->message_text, 60),
            ];
        }

        return [
            'entity' => 'WhatsApp Leads',
            'columns' => $columns,
            'rows' => $rows,
            'summary' => [
                'count' => count($rows),
                'total_value' => 0.0,
            ],
        ];
    }

    /**
     * Contact inquiries report builder.
     */
    protected function buildInquiriesReport(?string $from, ?string $to, ?string $channel, ?string $status): array
    {
        $query = Contact::query();

        if ($from) $query->whereDate('created_at', '>=', $from);
        if ($to) $query->whereDate('created_at', '<=', $to);
        if ($channel) $query->where('traffic_channel', $channel);
        if ($status) $query->where('status', $status);

        $items = $query->orderBy('created_at', 'desc')->get();

        $columns = [
            'id' => 'ID',
            'created_at' => 'Submitted Time',
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'subject' => 'Subject',
            'status' => 'Status',
            'traffic_channel' => 'Channel',
            'utm_source' => 'UTM Source',
        ];

        $rows = [];
        foreach ($items as $c) {
            $rows[] = [
                'id' => '#' . $c->id,
                'created_at' => $c->created_at ? $c->created_at->format('Y-m-d H:i') : '',
                'name' => $c->name,
                'email' => $c->email,
                'phone' => $c->phone ?: '',
                'subject' => $c->subject ?: 'General Inquiry',
                'status' => ucfirst($c->status),
                'traffic_channel' => $c->traffic_channel ?: 'Direct',
                'utm_source' => $c->utm_source ?: '',
            ];
        }

        return [
            'entity' => 'Contact Inquiries',
            'columns' => $columns,
            'rows' => $rows,
            'summary' => [
                'count' => count($rows),
                'total_value' => 0.0,
            ],
        ];
    }

    /**
     * Tour performance matrix builder.
     */
    protected function buildToursReport(?string $from, ?string $to): array
    {
        $matrix = $this->calculateTourPerformance($from, $to);

        $columns = [
            'name' => 'Tour / Activity Package',
            'bookings_count' => 'Confirmed Bookings',
            'total_guests' => 'Total Guests',
            'total_revenue' => 'Total Revenue (AED)',
            'avg_order_value' => 'Average Order Value (AED)',
            'abandoned_drafts' => 'Dropped Drafts',
        ];

        $rows = [];
        $totalRev = 0.0;
        $totalBookings = 0;

        foreach ($matrix as $m) {
            $totalRev += (float) $m['revenue'];
            $totalBookings += (int) $m['bookings'];

            $rows[] = [
                'name' => $m['name'],
                'bookings_count' => $m['bookings'],
                'total_guests' => $m['guests'],
                'total_revenue' => number_format((float) $m['revenue'], 2, '.', ''),
                'avg_order_value' => number_format((float) $m['aov'], 2, '.', ''),
                'abandoned_drafts' => $m['drafts'],
            ];
        }

        return [
            'entity' => 'Tours & Activities Performance',
            'columns' => $columns,
            'rows' => $rows,
            'summary' => [
                'count' => count($rows),
                'total_value' => $totalRev,
                'total_bookings' => $totalBookings,
            ],
        ];
    }

    /**
     * Calculate executive KPIs for header cards.
     */
    protected function calculateExecutiveKpis(?string $from, ?string $to): array
    {
        $bookingQuery = Booking::where('status', '!=', 'draft');
        $draftQuery = Booking::where('status', 'draft');
        $waQuery = WhatsappInquiry::query();
        $contactQuery = Contact::query();

        if ($from) {
            $bookingQuery->whereDate('tour_date', '>=', $from);
            $draftQuery->whereDate('created_at', '>=', $from);
            $waQuery->whereDate('created_at', '>=', $from);
            $contactQuery->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $bookingQuery->whereDate('tour_date', '<=', $to);
            $draftQuery->whereDate('created_at', '<=', $to);
            $waQuery->whereDate('created_at', '<=', $to);
            $contactQuery->whereDate('created_at', '<=', $to);
        }

        $confirmedBookings = (clone $bookingQuery)->whereIn('status', ['confirmed', 'completed'])->count();
        $totalBookings = (clone $bookingQuery)->count();
        $revenue = (float) (clone $bookingQuery)->whereIn('status', ['confirmed', 'completed'])->sum('total');
        $collectedRevenue = (float) (clone $bookingQuery)->whereIn('status', ['confirmed', 'completed'])
            ->whereIn('payment_status', ['paid', 'partial'])
            ->sum(DB::raw("CASE WHEN payment_amount > 0 THEN payment_amount ELSE (CASE WHEN payment_status = 'paid' THEN total ELSE 0 END) END"));

        $draftsCount = (clone $draftQuery)->count();
        $recoverableDraftsVal = (float) (clone $draftQuery)->sum('total');

        $waCount = (clone $waQuery)->count();
        $contactCount = (clone $contactQuery)->count();

        $convertedDraftsCount = Booking::where('status', 'converted')->count();

        return [
            'revenue' => $revenue,
            'collected_revenue' => $collectedRevenue,
            'total_bookings' => $totalBookings,
            'confirmed_bookings' => $confirmedBookings,
            'avg_order_value' => $confirmedBookings > 0 ? round($revenue / $confirmedBookings, 2) : 0.0,
            'drafts_count' => $draftsCount,
            'recoverable_drafts_val' => $recoverableDraftsVal,
            'converted_drafts_count' => $convertedDraftsCount,
            'whatsapp_leads' => $waCount,
            'contact_inquiries' => $contactCount,
            'total_inquiries' => $waCount + $contactCount,
        ];
    }

    /**
     * Calculate acquisition and channel attribution matrix.
     */
    protected function calculateChannelAttribution(?string $from, ?string $to): array
    {
        $query = Booking::where('status', '!=', 'draft');
        if ($from) $query->whereDate('tour_date', '>=', $from);
        if ($to) $query->whereDate('tour_date', '<=', $to);

        $channelStats = (clone $query)
            ->selectRaw("
                COALESCE(NULLIF(traffic_channel, ''), 'Direct') as ch,
                COUNT(*) as bookings_count,
                SUM(CASE WHEN status IN ('confirmed', 'completed') THEN total ELSE 0 END) as revenue,
                SUM(adults + children) as total_guests
            ")
            ->groupBy('ch')
            ->orderBy('revenue', 'desc')
            ->get();

        $result = [];
        $totalRev = $channelStats->sum('revenue');

        foreach ($channelStats as $stat) {
            $ch = $stat->ch ?: 'Direct';
            $rev = (float) $stat->revenue;
            $count = (int) $stat->bookings_count;

            $result[$ch] = [
                'channel' => $ch,
                'bookings' => $count,
                'revenue' => $rev,
                'guests' => (int) $stat->total_guests,
                'share' => $totalRev > 0 ? round(($rev / $totalRev) * 100, 1) : 0.0,
                'aov' => $count > 0 ? round($rev / $count, 2) : 0.0,
            ];
        }

        return $result;
    }

    /**
     * Calculate tour performance metrics.
     */
    protected function calculateTourPerformance(?string $from, ?string $to): array
    {
        $tours = Tour::all();
        $result = [];

        foreach ($tours as $tour) {
            $bQuery = Booking::where('tour_id', $tour->id)->where('status', '!=', 'draft');
            $dQuery = Booking::where('tour_id', $tour->id)->where('status', 'draft');

            if ($from) {
                $bQuery->whereDate('tour_date', '>=', $from);
                $dQuery->whereDate('created_at', '>=', $from);
            }
            if ($to) {
                $bQuery->whereDate('tour_date', '<=', $to);
                $dQuery->whereDate('created_at', '<=', $to);
            }

            $bookingsCount = (clone $bQuery)->whereIn('status', ['confirmed', 'completed'])->count();
            $rev = (float) (clone $bQuery)->whereIn('status', ['confirmed', 'completed'])->sum('total');
            $guests = (int) (clone $bQuery)->whereIn('status', ['confirmed', 'completed'])->sum(DB::raw('adults + children'));
            $drafts = (clone $dQuery)->count();

            $result[] = [
                'id' => $tour->id,
                'name' => $tour->name,
                'bookings' => $bookingsCount,
                'revenue' => $rev,
                'guests' => $guests,
                'drafts' => $drafts,
                'aov' => $bookingsCount > 0 ? round($rev / $bookingsCount, 2) : 0.0,
            ];
        }

        usort($result, fn ($a, $b) => $b['revenue'] <=> $a['revenue']);

        return $result;
    }

    /**
     * Resolve date ranges from preset.
     */
    protected function resolveDateRange(string $preset, ?string $from, ?string $to): array
    {
        $today = Carbon::today();

        return match ($preset) {
            'today' => [$today->toDateString(), $today->toDateString()],
            'yesterday' => [$today->copy()->subDay()->toDateString(), $today->copy()->subDay()->toDateString()],
            'last_7_days' => [$today->copy()->subDays(6)->toDateString(), $today->toDateString()],
            'this_month' => [$today->copy()->startOfMonth()->toDateString(), $today->copy()->endOfMonth()->toDateString()],
            'last_month' => [$today->copy()->subMonth()->startOfMonth()->toDateString(), $today->copy()->subMonth()->endOfMonth()->toDateString()],
            'this_quarter' => [$today->copy()->startOfQuarter()->toDateString(), $today->copy()->endOfQuarter()->toDateString()],
            'year_to_date' => [$today->copy()->startOfYear()->toDateString(), $today->toDateString()],
            'all_time' => [null, null],
            'custom' => [$from ?: null, $to ?: null],
            default => [$today->copy()->startOfMonth()->toDateString(), $today->copy()->endOfMonth()->toDateString()],
        };
    }
}
