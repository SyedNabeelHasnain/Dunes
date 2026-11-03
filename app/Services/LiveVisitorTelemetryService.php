<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Contact;
use App\Models\RequestLog;
use App\Models\WhatsappInquiry;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LiveVisitorTelemetryService
{
    protected IpSecurityService $ipSecurity;

    public function __construct(IpSecurityService $ipSecurity)
    {
        $this->ipSecurity = $ipSecurity;
    }

    /**
     * Retrieve active visitors from the last N minutes enriched with friction and lead flags.
     */
    public function getActiveVisitors(int $minutes = 5): array
    {
        $window = now()->subMinutes($minutes)->toDateTimeString();
        $ignoredIps = $this->ipSecurity->getIgnoredIpsList();

        $baseQuery = RequestLog::where('request_timestamp', '>=', $window)
            ->where('bot_indicator', 'Likely Human');

        if (! empty($ignoredIps)) {
            $baseQuery->whereNotIn('client_ip', $ignoredIps);
        }

        $count = (clone $baseQuery)->distinct('session_id')->count('session_id');

        $rawVisitors = (clone $baseQuery)
            ->select(
                'session_id', 'client_ip', 'user_agent', 'country', 'city',
                'request_uri', 'request_timestamp', 'device_type', 'browser_name',
                'os_name', 'entity_type', 'utm_source', 'utm_medium', 'utm_campaign', 'referrer_url'
            )
            ->orderBy('request_timestamp', 'desc')
            ->get();

        // Group by session_id (or client_ip fallback)
        $grouped = $rawVisitors->groupBy(function ($v) {
            return ! empty($v->session_id) && $v->session_id !== 'Not Available'
                ? $v->session_id
                : $v->client_ip;
        });

        // Batch resolve existing drafts and inquiries in this time window
        $sessionIds = $rawVisitors->pluck('session_id')->filter()->unique()->toArray();
        $clientIps = $rawVisitors->pluck('client_ip')->filter()->unique()->toArray();

        $drafts = Booking::where('status', 'draft')
            ->where(function ($q) use ($sessionIds, $clientIps) {
                if (! empty($sessionIds)) {
                    $q->whereIn('draft_token', $sessionIds);
                }
                if (! empty($clientIps)) {
                    $q->orWhereIn('ip_address', $clientIps);
                }
            })
            ->latest()
            ->get()
            ->keyBy(function ($b) {
                return $b->draft_token ?: $b->ip_address;
            });

        $visitors = [];
        foreach ($grouped as $key => $group) {
            $latest = $group->first();
            $pagesCount = $group->count();
            $earliest = $group->last();
            $durationSec = max(0, Carbon::parse($latest->request_timestamp)->diffInSeconds(Carbon::parse($earliest->request_timestamp)));

            // Check for drafts
            $draft = $drafts->get($latest->session_id) ?: $drafts->get($latest->client_ip);

            // Check for errors (404, etc.)
            $hasError = $group->contains(function ($r) {
                return $r->entity_type === 'error_404' || str_contains($r->request_uri, '404') || str_contains($r->entity_type, 'error');
            });

            // Check for WhatsApp clicks
            $hasWhatsApp = $group->contains(function ($r) {
                return $r->entity_type === 'whatsapp' || $r->entity_type === 'whatsapp_click';
            });

            // Calculate human-friendly last active time
            $diffMinutes = Carbon::parse($latest->request_timestamp)->diffInMinutes(now());
            $diffSeconds = Carbon::parse($latest->request_timestamp)->diffInSeconds(now());
            $lastActiveHuman = $diffSeconds < 45 ? 'Just now' : ($diffMinutes < 1 ? "{$diffSeconds}s ago" : "{$diffMinutes}m ago");

            $visitors[] = [
                'session_id' => $latest->session_id ?: $latest->client_ip,
                'client_ip' => $latest->client_ip,
                'user_agent' => $latest->user_agent,
                'country' => $latest->country ?: 'United Arab Emirates',
                'city' => $latest->city ?: 'Dubai',
                'request_uri' => $latest->request_uri,
                'request_timestamp' => $latest->request_timestamp ? Carbon::parse($latest->request_timestamp)->toIso8601String() : now()->toIso8601String(),
                'last_active_human' => $lastActiveHuman,
                'device_type' => $latest->device_type ?: 'Desktop',
                'browser_name' => $latest->browser_name ?: 'Chrome',
                'os_name' => $latest->os_name ?: 'Windows',
                'pages_count' => $pagesCount,
                'duration_seconds' => $durationSec,
                'has_draft' => (bool) $draft,
                'draft_ref' => $draft?->reference,
                'draft_total' => $draft ? (float) $draft->total : 0.0,
                'draft_tour' => $draft?->tour_name,
                'has_error' => $hasError,
                'has_whatsapp' => $hasWhatsApp,
                'channel' => VisitorTrackerService::determineChannel($latest->utm_source, $latest->utm_medium, null, null, $latest->referrer_url),
            ];
        }

        return [
            'count' => $count,
            'visitors' => array_values($visitors),
        ];
    }

    /**
     * Compile a comprehensive, step-by-step visitor journey with friction diagnostics and lead details.
     */
    public function getVisitorJourney(string $sessionId, ?string $clientIp = null): array
    {
        $query = RequestLog::query();
        if (! empty($sessionId) && $sessionId !== 'Not Available' && $sessionId !== 'undefined') {
            $query->where('session_id', $sessionId);
            if (! empty($clientIp)) {
                $query->orWhere('client_ip', $clientIp);
            }
        } elseif (! empty($clientIp)) {
            $query->where('client_ip', $clientIp);
        } else {
            return ['success' => false, 'message' => 'Valid session identifier or IP address required.'];
        }

        $logs = $query->orderBy('request_timestamp', 'asc')->get();

        if ($logs->isEmpty()) {
            return ['success' => false, 'message' => 'No session telemetry recorded for this visitor.'];
        }

        $latestLog = $logs->last();
        $firstLog = $logs->first();
        $ip = $latestLog->client_ip;

        // 1. Visitor Profile
        $visitor = [
            'session_id' => $sessionId,
            'client_ip' => $ip,
            'country' => $latestLog->country ?: 'United Arab Emirates',
            'city' => $latestLog->city ?: 'Dubai',
            'region' => $latestLog->region ?: 'Dubai',
            'isp' => $latestLog->isp ?: 'Etisalat / DU',
            'asn' => $latestLog->asn ?: 'AS5384',
            'device_type' => $latestLog->device_type ?: 'Desktop',
            'browser_name' => $latestLog->browser_name ?: 'Chrome',
            'browser_version' => $latestLog->browser_version ?: '',
            'os_name' => $latestLog->os_name ?: 'Windows',
            'landing_page' => $firstLog->request_uri ?: '/',
            'current_page' => $latestLog->request_uri ?: '/',
            'referrer_url' => $firstLog->referrer_url ?: 'Direct',
            'channel' => VisitorTrackerService::determineChannel($firstLog->utm_source, $firstLog->utm_medium, null, null, $firstLog->referrer_url),
            'utm_source' => $firstLog->utm_source,
            'utm_medium' => $firstLog->utm_medium,
            'utm_campaign' => $firstLog->utm_campaign,
            'first_seen' => Carbon::parse($firstLog->request_timestamp)->format('h:i:s A (M d)'),
            'last_active' => Carbon::parse($latestLog->request_timestamp)->format('h:i:s A (M d)'),
            'is_active_now' => Carbon::parse($latestLog->request_timestamp)->diffInMinutes(now()) <= 3,
            'total_steps' => $logs->count(),
            'total_duration_sec' => max(0, Carbon::parse($latestLog->request_timestamp)->diffInSeconds(Carbon::parse($firstLog->request_timestamp))),
            'is_ignored_ip' => $this->ipSecurity->isIpIgnoredFromAnalytics($ip),
            'is_blocked_ip' => $this->ipSecurity->isIpBlocked($ip),
        ];

        // 2. Resolve Customer Lead (Draft checkout, Inquiry, or WhatsApp)
        $lead = null;
        $draft = Booking::where('status', 'draft')
            ->where(function ($q) use ($sessionId, $ip) {
                $q->where('draft_token', $sessionId)->orWhere('ip_address', $ip);
            })
            ->latest()
            ->first();

        if ($draft && (! empty($draft->email) || ! empty($draft->phone) || ! empty($draft->name))) {
            $lead = [
                'type' => 'draft_checkout',
                'reference' => $draft->reference,
                'name' => $draft->name ?: 'Guest Traveler',
                'email' => $draft->email,
                'phone' => $draft->phone,
                'tour_name' => $draft->tour_name,
                'tier_name' => $draft->tier_name,
                'tour_date' => $draft->tour_date ? Carbon::parse($draft->tour_date)->format('M d, Y') : 'Not Chosen',
                'guests' => "{$draft->adults} Adults" . ($draft->children ? ", {$draft->children} Children" : ''),
                'subtotal' => (float) $draft->subtotal,
                'total' => (float) $draft->total,
                'url' => route('admin.bookings.drafts', ['search' => $draft->reference]),
            ];
        } else {
            // Check WhatsApp inquiries
            $wa = WhatsappInquiry::where('ip_address', $ip)->latest()->first();
            if ($wa) {
                $lead = [
                    'type' => 'whatsapp_inquiry',
                    'reference' => 'WA-#' . $wa->id,
                    'name' => $wa->name ?: 'WhatsApp Guest',
                    'email' => $wa->email,
                    'phone' => $wa->phone,
                    'tour_name' => $wa->tour_name,
                    'tier_name' => 'Direct Message',
                    'tour_date' => $wa->tour_date ? Carbon::parse($wa->tour_date)->format('M d, Y') : null,
                    'guests' => $wa->guests,
                    'subtotal' => 0.0,
                    'total' => 0.0,
                    'url' => route('admin.whatsapp.index', ['search' => $wa->phone ?: $wa->name]),
                ];
            } else {
                // Check Contact Inquiries
                $contact = Contact::where('client_ip', $ip)->latest()->first();
                if ($contact) {
                    $lead = [
                        'type' => 'contact_inquiry',
                        'reference' => 'INQ-#' . $contact->id,
                        'name' => $contact->name,
                        'email' => $contact->email,
                        'phone' => $contact->phone,
                        'tour_name' => $contact->subject,
                        'tier_name' => 'Web Form',
                        'tour_date' => null,
                        'guests' => null,
                        'subtotal' => 0.0,
                        'total' => 0.0,
                        'url' => route('admin.inquiries.index', ['search' => $contact->email ?: $contact->name]),
                    ];
                }
            }
        }

        // 3. Friction & Obstacle Diagnostics
        $frictionAlerts = [];

        // Obstacle 1: Incomplete Draft Abandonment
        if ($draft && $draft->status === 'draft') {
            $frictionAlerts[] = [
                'type' => 'draft_abandoned',
                'severity' => 'amber',
                'icon' => 'bi-cart-x-fill',
                'title' => 'Incomplete Checkout Draft Abandoned',
                'message' => "Guest began booking for {$draft->tour_name} (AED " . number_format($draft->total, 2) . ") but stopped before payment completion.",
                'action_label' => 'Inspect Draft in CMS',
                'action_url' => route('admin.bookings.drafts', ['search' => $draft->reference]),
            ];
        }

        // Obstacle 2: 404 Page Not Found Encountered
        $errorLogs = $logs->filter(function ($l) {
            return $l->entity_type === 'error_404' || str_contains($l->request_uri, '404');
        });
        if ($errorLogs->isNotEmpty()) {
            foreach ($errorLogs as $err) {
                $frictionAlerts[] = [
                    'type' => '404_not_found',
                    'severity' => 'rose',
                    'icon' => 'bi-exclamation-triangle-fill',
                    'title' => 'Encountered 404 Broken Link / Missing Page',
                    'message' => "Visitor attempted to access '{$err->request_uri}' which returned a 404 Not Found error.",
                    'action_label' => 'Check Broken URL',
                    'action_url' => url($err->request_uri),
                ];
            }
        }

        // Obstacle 3: Promo Code Failure
        $promoFailures = $logs->filter(function ($l) {
            return $l->entity_type === 'promo_failure';
        });
        if ($promoFailures->isNotEmpty()) {
            foreach ($promoFailures as $pf) {
                parse_str($pf->query_string ?? '', $params);
                $code = $params['code'] ?? 'Unknown';
                $reason = $params['reason'] ?? 'Invalid code or minimum spend condition not met.';
                $frictionAlerts[] = [
                    'type' => 'promo_failure',
                    'severity' => 'orange',
                    'icon' => 'bi-tag-fill',
                    'title' => "Promo Code Rejection: '{$code}'",
                    'message' => "Visitor attempted discount coupon code '{$code}', but validation failed: {$reason}",
                    'action_label' => 'View Coupons in CMS',
                    'action_url' => route('admin.coupons.index'),
                ];
            }
        }

        // Obstacle 4: High Inactivity / Long Dwell
        if ($visitor['is_active_now'] && $visitor['total_duration_sec'] > 300 && $logs->count() <= 2) {
            $frictionAlerts[] = [
                'type' => 'high_dwell_idle',
                'severity' => 'indigo',
                'icon' => 'bi-hourglass-split',
                'title' => 'High Page Dwell / Inactive on Current Screen',
                'message' => "Visitor has been on '{$visitor['current_page']}' for over 5 minutes with minimal navigation. May need guidance.",
                'action_label' => 'Inspect Page',
                'action_url' => url($visitor['current_page']),
            ];
        }

        // Obstacle 5: WhatsApp Concierge Intent
        $waClicks = $logs->filter(function ($l) {
            return $l->entity_type === 'whatsapp' || $l->entity_type === 'whatsapp_click';
        });
        if ($waClicks->isNotEmpty()) {
            $frictionAlerts[] = [
                'type' => 'whatsapp_intent',
                'severity' => 'emerald',
                'icon' => 'bi-whatsapp',
                'title' => 'Requested 24/7 WhatsApp Concierge Help',
                'message' => "Guest clicked the WhatsApp chat concierge from '{$waClicks->first()->request_uri}'.",
                'action_label' => 'Open WhatsApp Leads Hub',
                'action_url' => route('admin.whatsapp.index'),
            ];
        }

        // 4. Chronological Clickstream / Timeline Steps
        $steps = [];
        $logCount = $logs->count();

        for ($i = 0; $i < $logCount; $i++) {
            $current = $logs[$i];
            $next = ($i < $logCount - 1) ? $logs[$i + 1] : null;

            $stepDwellSec = $next
                ? max(1, Carbon::parse($next->request_timestamp)->diffInSeconds(Carbon::parse($current->request_timestamp)))
                : max(1, now()->diffInSeconds(Carbon::parse($current->request_timestamp)));

            // Friendly page title resolution
            $pageTitle = $this->resolveFriendlyPageTitle($current->request_uri, $current->entity_type);

            $stepType = match ($current->entity_type) {
                'draft' => 'draft',
                'booking' => 'booking',
                'whatsapp', 'whatsapp_click' => 'whatsapp',
                'error_404' => 'error',
                'promo_failure' => 'warning',
                default => 'pageview',
            };

            $steps[] = [
                'step_number' => $i + 1,
                'type' => $stepType,
                'entity_type' => $current->entity_type,
                'title' => $pageTitle,
                'uri' => $current->request_uri,
                'url' => url($current->request_uri),
                'method' => $current->request_method ?: 'GET',
                'timestamp' => Carbon::parse($current->request_timestamp)->format('h:i:s A'),
                'relative_time' => Carbon::parse($current->request_timestamp)->diffForHumans(),
                'dwell_seconds' => $stepDwellSec,
                'dwell_formatted' => $this->formatDwellTime($stepDwellSec),
                'is_current' => ($i === $logCount - 1),
            ];
        }

        return [
            'success' => true,
            'visitor' => $visitor,
            'lead' => $lead,
            'friction_alerts' => $frictionAlerts,
            'journey_steps' => array_reverse($steps), // Show newest on top for live feed feel
        ];
    }

    /**
     * Resolve a human-friendly label for any requested URI.
     */
    protected function resolveFriendlyPageTitle(string $uri, ?string $entityType = null): string
    {
        $clean = trim($uri, '/');

        if ($entityType === 'error_404') {
            return "404 Not Found ({$uri})";
        }
        if ($entityType === 'promo_failure') {
            return 'Promo Code Validation Attempt';
        }
        if ($entityType === 'draft') {
            return 'Checkout Draft Auto-Save';
        }
        if ($entityType === 'whatsapp' || $entityType === 'whatsapp_click') {
            return 'Clicked 24/7 WhatsApp Concierge';
        }

        if (empty($clean)) {
            return 'Home Page (Dubai Desert Safari)';
        }

        if ($clean === 'tours') {
            return 'All Desert Safari Tours & Packages';
        }

        if (str_starts_with($clean, 'tours/')) {
            $slug = str_replace('tours/', '', $clean);
            return ucwords(str_replace('-', ' ', $slug));
        }

        if (str_starts_with($clean, 'booking/') || str_starts_with($clean, 'checkout/')) {
            return 'Checkout & Payment Screen';
        }

        if ($clean === 'contact') {
            return 'Contact Us & Location Inquiry';
        }

        if ($clean === 'gallery') {
            return 'Visual Photo & Video Gallery';
        }

        if ($clean === 'blog' || str_starts_with($clean, 'blog/')) {
            return 'Desert Safari Travel Guide';
        }

        return ucwords(str_replace(['-', '_', '/'], ' ', $clean));
    }

    /**
     * Human readable duration format.
     */
    protected function formatDwellTime(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds}s";
        }
        $mins = floor($seconds / 60);
        $secs = $seconds % 60;

        return "{$mins}m {$secs}s";
    }
}
