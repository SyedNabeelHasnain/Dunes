<?php

namespace App\Services\Audit\Checkers;

use App\Models\RedirectRule;
use App\Models\RequestLog;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class UxChecker implements AuditCheckerInterface
{
    public function run(array $context = []): array
    {
        $issues = [];

        // 1. High-Frequency Unresolved 404 Dead Ends
        try {
            if (class_exists(RequestLog::class) && Schema::hasTable('request_logs')) {
                $activeSources = RedirectRule::where('is_active', true)->pluck('source_path')
                    ->map(fn ($p) => RedirectRule::normalizePath((string) $p))
                    ->flip();

                $topMissing = RequestLog::where('entity_type', 'error_404')
                    ->where('request_timestamp', '>=', now()->subDays(14))
                    ->selectRaw('request_uri, COUNT(*) as hits')
                    ->groupBy('request_uri')
                    ->havingRaw('COUNT(*) >= 3')
                    ->orderByDesc('hits')
                    ->limit(5)
                    ->get();

                foreach ($topMissing as $row) {
                    $norm = RedirectRule::normalizePath((string) parse_url((string) $row->request_uri, PHP_URL_PATH));
                    if (! isset($activeSources[$norm]) && $norm !== '/') {
                        $issues[] = [
                            'category' => 'ux',
                            'check_key' => 'ux_unresolved_404_spike',
                            'title' => "Frequent Unresolved 404 Dead End: {$norm} ({$row->hits} hits)",
                            'description' => 'Visitors and crawlers are repeatedly landing on a non-existent path without a redirect or 410 recovery rule.',
                            'severity' => 'medium',
                            'verification_type' => 'automated',
                            'affected_url' => url($norm),
                            'evidence' => "404 hits logged in last 14 days: {$row->hits}",
                            'recommended_action' => 'Open Redirect Manager > Top 404 Errors Inbox and create a 301 redirect or mark 410 Gone.',
                        ];
                    }
                }
            }
        } catch (\Throwable) {
        }

        // 2. Probed Live Pages: Viewport Meta, Broken Status, Broken Internal Links & Primary Booking CTA Check
        $activeTourSlugs = [];
        $knownCatchAllSlugs = [];
        try {
            $activeTourSlugs = Tour::where('status', 'active')->pluck('slug')->flip()->all();
            $knownCatchAllSlugs = $activeTourSlugs;
            foreach (\App\Models\BlogPost::where('status', 'published')->pluck('slug') as $bs) {
                $knownCatchAllSlugs[(string) $bs] = true;
            }
            foreach (\App\Services\LocationLandingService::getLocations() as $loc) {
                if (! empty($loc['slug'])) {
                    $knownCatchAllSlugs[(string) $loc['slug']] = true;
                }
            }
            foreach (RedirectRule::where('is_active', true)->pluck('source_path') as $rp) {
                $knownCatchAllSlugs[ltrim((string) $rp, '/')] = true;
            }
        } catch (\Throwable) {
        }

        $checkedLinks = [];
        $probedPages = $context['probed_pages'] ?? [];
        foreach ($probedPages as $path => $probe) {
            $status = (int) ($probe['status'] ?? 200);
            $html = (string) ($probe['html'] ?? '');

            if ($status >= 400) {
                $issues[] = [
                    'category' => 'ux',
                    'check_key' => 'ux_public_route_http_error',
                    'title' => "Public Route Returned HTTP {$status}: {$path}",
                    'description' => 'An active public route failed to render HTTP 2xx OK during live HTTP kernel verification.',
                    'severity' => 'critical',
                    'verification_type' => 'automated',
                    'affected_url' => url($path),
                    'evidence' => "HTTP status {$status}",
                    'recommended_action' => 'Inspect controller route binding, view compilation, or redirect rule immediately.',
                ];

                continue;
            }

            if ($html !== '' && ! preg_match('/<meta[^>]+name=["\']viewport["\']/i', $html)) {
                $issues[] = [
                    'category' => 'ux',
                    'check_key' => 'ux_missing_viewport_meta',
                    'title' => "Missing Mobile Responsive Viewport Tag on {$path}",
                    'description' => 'Mobile devices cannot scale layout properly without <meta name="viewport">.',
                    'severity' => 'high',
                    'verification_type' => 'automated',
                    'affected_url' => url($path),
                    'evidence' => 'No meta viewport tag detected in rendered HTML.',
                    'recommended_action' => 'Ensure layout <head> outputs <meta name="viewport" content="width=device-width, initial-scale=1">.',
                ];
            }

            // Primary Booking CTA verification on tour detail pages
            $trimmedSlug = ltrim((string) $path, '/');
            if ($html !== '' && isset($activeTourSlugs[$trimmedSlug])) {
                $hasBookingCta = preg_match('/(book\s+now|reserve|check\s+availability|instant\s+booking|wa\.me|whatsapp|<form[^>]+booking)/i', $html) === 1;
                if (! $hasBookingCta) {
                    $issues[] = [
                        'category' => 'ux',
                        'check_key' => 'ux_missing_primary_booking_cta',
                        'title' => "Missing Primary Booking CTA on Tour Page {$path}",
                        'description' => 'Commercial tour page HTML does not expose a recognizable booking form or reservation call-to-action.',
                        'severity' => 'high',
                        'verification_type' => 'automated',
                        'affected_url' => url($path),
                        'evidence' => 'No booking form or primary reservation CTA detected in rendered HTML.',
                        'recommended_action' => 'Restore the primary booking widget and sticky mobile booking bar on tours/show.blade.php.',
                    ];
                }
            }

            // Scan internal <a href="/..."> links against route table for broken links
            if ($html !== '' && preg_match_all('/<a\b[^>]*\bhref=["\']([^"\']+)["\']/i', $html, $hrefMatches)) {
                $brokenOnPage = [];
                foreach (array_unique($hrefMatches[1] ?? []) as $href) {
                    $href = html_entity_decode(trim($href), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:') || str_starts_with($href, 'javascript:')) {
                        continue;
                    }
                    $linkPath = null;
                    if (str_starts_with($href, '/') && ! str_starts_with($href, '//')) {
                        $linkPath = (string) parse_url($href, PHP_URL_PATH);
                    } elseif (str_starts_with($href, rtrim(url('/'), '/'))) {
                        $linkPath = (string) parse_url($href, PHP_URL_PATH);
                    }
                    if (! $linkPath || $linkPath === '/' || preg_match('#^/(images|videos|assets|storage|build|admin|api|booking|checkout|driver|email|locale|currency|sitemap|feed\.xml|robots\.txt|llms)#i', $linkPath)) {
                        continue;
                    }
                    if (! array_key_exists($linkPath, $checkedLinks)) {
                        try {
                            $matched = Route::getRoutes()->match(Request::create($linkPath, 'GET'));
                            $routeName = (string) ($matched->getName() ?? '');
                            if (in_array($routeName, ['tours.show', 'locale.tours.show'], true)) {
                                $slugParam = (string) ($matched->parameter('slug') ?: ltrim($linkPath, '/'));
                                $checkedLinks[$linkPath] = isset($knownCatchAllSlugs[$slugParam]);
                            } else {
                                $checkedLinks[$linkPath] = true;
                            }
                        } catch (\Throwable) {
                            $checkedLinks[$linkPath] = false;
                        }
                    }
                    if ($checkedLinks[$linkPath] === false) {
                        $brokenOnPage[] = $linkPath;
                    }
                }

                if ($brokenOnPage !== []) {
                    $sample = implode(', ', array_slice($brokenOnPage, 0, 5));
                    $issues[] = [
                        'category' => 'ux',
                        'check_key' => 'ux_broken_internal_link',
                        'title' => count($brokenOnPage)." Unresolvable Internal Link(s) Found on {$path}",
                        'description' => 'One or more internal anchor hrefs do not match any registered public application route.',
                        'severity' => 'high',
                        'verification_type' => 'automated',
                        'affected_url' => url($path),
                        'evidence' => "Unmatched internal path(s): {$sample}",
                        'recommended_action' => 'Fix the link href in the CMS/Blade view or configure a 301 redirect in Redirect Manager.',
                    ];
                }
            }
        }

        return $issues;
    }
}
