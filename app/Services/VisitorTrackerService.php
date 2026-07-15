<?php

namespace App\Services;

use App\Models\RequestLog;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VisitorTrackerService
{
    /**
     * Resolve detailed client IP.
     */
    public function resolveClientIp(): array
    {
        $ip = request()->ip() ?: '0.0.0.0';
        $version = 'Not Available';
        if ($ip !== '0.0.0.0') {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $version = 'IPv4';
            } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $version = 'IPv6';
            }
        }

        $saltSetting = Setting::where('setting_key', 'tracking_ip_salt')->first();
        $salt = $saltSetting ? $saltSetting->setting_value : 'dunes-discovery-tracking-salt';
        $hash = ($ip !== '0.0.0.0') ? hash('sha256', $salt . $ip) : 'Not Available';

        return [
            'client_ip' => $ip,
            'ip_version' => $version,
            'ip_hash' => $hash,
        ];
    }

    /**
     * Lookup IP details from IP-API (with caching).
     */
    public function ipLookup(string $ip): ?array
    {
        if (in_array($ip, ['127.0.0.1', '::1', '0.0.0.0', 'localhost'])) {
            return null;
        }

        // Cache the lookup results for 24 hours to prevent API rate limiting
        return Cache::remember("geoip_{$ip}", 86400, function () use ($ip) {
            try {
                $response = Http::timeout(2)
                    ->get("http://ip-api.com/json/{$ip}?fields=status,message,country,regionName,city,lat,lon,timezone,isp,org,as,hosting,proxy");

                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['status'] ?? '') === 'success') {
                        return $data;
                    }
                }
            } catch (\Exception $e) {
                Log::warning("IP-API Lookup failed for IP: {$ip}, trying fallback: " . $e->getMessage());
            }

            // Fallback to FreeGeoIP
            try {
                $response = Http::timeout(2)->get("https://reallyfreegeoip.org/json/{$ip}");
                if ($response->successful()) {
                    $data = $response->json();
                    return [
                        'country' => $data['country_name'] ?? null,
                        'regionName' => $data['region_name'] ?? null,
                        'city' => $data['city'] ?? null,
                        'lat' => $data['latitude'] ?? null,
                        'lon' => $data['longitude'] ?? null,
                        'timezone' => $data['time_zone'] ?? null,
                        'isp' => null,
                        'org' => null,
                        'as' => null,
                    ];
                }
            } catch (\Exception $e) {
                Log::error("GeoIP Fallback failed for IP: {$ip}: " . $e->getMessage());
            }

            return null;
        });
    }

    /**
     * Parse User Agent into readable components.
     */
    public function parseUserAgent(?string $ua = null): array
    {
        $ua = $ua ?: (request()->userAgent() ?: '');
        
        $device = 'Desktop';
        if (preg_match('/Mobile|Android|iPhone|iPad/i', $ua)) {
            $device = preg_match('/iPad|Tablet/i', $ua) ? 'Tablet' : 'Mobile';
        }

        $browser = 'Unknown';
        $browserVersion = 'Not Available';
        if (preg_match('/Edg\/([\d\.]+)/i', $ua, $m)) {
            $browser = 'Edge';
            $browserVersion = $m[1];
        } elseif (preg_match('/Chrome\/([\d\.]+)/i', $ua, $m)) {
            $browser = 'Chrome';
            $browserVersion = $m[1];
        } elseif (preg_match('/Firefox\/([\d\.]+)/i', $ua, $m)) {
            $browser = 'Firefox';
            $browserVersion = $m[1];
        } elseif (preg_match('/Version\/([\d\.]+).*Safari/i', $ua, $m)) {
            $browser = 'Safari';
            $browserVersion = $m[1];
        }

        $platform = 'Unknown';
        $osVersion = 'Not Available';
        if (preg_match('/Windows NT ([\d\.]+)/i', $ua, $m)) {
            $platform = 'Windows';
            $osVersion = $m[1];
        } elseif (preg_match('/Mac OS X ([\d_]+)/i', $ua, $m)) {
            $platform = 'macOS';
            $osVersion = str_replace('_', '.', $m[1]);
        } elseif (preg_match('/Android ([\d\.]+)/i', $ua, $m)) {
            $platform = 'Android';
            $osVersion = $m[1];
        } elseif (preg_match('/iPhone OS ([\d_]+)/i', $ua, $m) || preg_match('/iPad; CPU OS ([\d_]+)/i', $ua, $m)) {
            $platform = 'iOS';
            $osVersion = str_replace('_', '.', $m[1]);
        } elseif (preg_match('/Linux/i', $ua)) {
            $platform = 'Linux';
        }

        return [
            'device_type' => $device,
            'browser_name' => $browser,
            'browser_version' => $browserVersion,
            'os_name' => $platform,
            'os_version' => $osVersion,
            'user_agent' => $ua,
        ];
    }

    /**
     * Check if user agent represents a search bot.
     */
    public function classifyBot(array $uaData): string
    {
        $ua = strtolower($uaData['user_agent'] ?? '');
        if (empty($ua)) {
            return 'Unknown';
        }

        $botPatterns = ['bot', 'crawler', 'spider', 'slurp', 'curl', 'wget', 'httpclient', 'libwww'];
        foreach ($botPatterns as $pat) {
            if (str_contains($ua, $pat)) {
                return 'Likely Bot';
            }
        }

        return 'Likely Human';
    }

    /**
     * Build the request context mapping.
     */
    public function collectRequestContext(string $formName = 'navigation', array $submitData = []): array
    {
        $requestTs = now()->toDateTimeString();
        $method = request()->method() ?: 'GET';
        $uri = request()->path() ?: '/';
        $query = request()->getQueryString() ?: '';
        $host = request()->getHost() ?: 'localhost';
        $proto = request()->server('SERVER_PROTOCOL') ?: 'HTTP/1.1';
        $https = request()->secure() ? 'Yes' : 'No';

        $acceptLang = request()->header('Accept-Language') ?: 'Not Available';
        $acceptEnc = request()->header('Accept-Encoding') ?: 'Not Available';
        $referrer = request()->header('referer') ?: 'Not Available';

        // Geolocation IP details
        $ipInfo = $this->resolveClientIp();
        $ipMeta = $this->ipLookup($ipInfo['client_ip']);

        $gpsLat = $submitData['gps_lat'] ?? 'Not Available';
        $gpsLng = $submitData['gps_lng'] ?? 'Not Available';
        $gpsAccuracy = $submitData['gps_accuracy'] ?? 'Not Available';
        $gpsTimestamp = $submitData['gps_timestamp'] ?? 'Not Available';
        $gpsSource = (!empty($submitData['gps_consent']) && $submitData['gps_consent'] === 'Yes') ? 'GPS (User Consented)' : 'Not Available';

        $loc = [
            'country' => $ipMeta['country'] ?? 'Not Available',
            'region' => $ipMeta['regionName'] ?? ($ipMeta['region'] ?? 'Not Available'),
            'city' => $ipMeta['city'] ?? 'Not Available',
            'latitude' => $gpsLat !== 'Not Available' ? (string)$gpsLat : ($ipMeta['lat'] ? (string)$ipMeta['lat'] : 'Not Available'),
            'longitude' => $gpsLng !== 'Not Available' ? (string)$gpsLng : ($ipMeta['lon'] ? (string)$ipMeta['lon'] : 'Not Available'),
            'timezone' => $ipMeta['timezone'] ?? 'Not Available',
            'isp' => $ipMeta['isp'] ?? 'Not Available',
            'organization' => $ipMeta['org'] ?? 'Not Available',
            'asn' => $ipMeta['as'] ?? 'Not Available',
            'hosting_flag' => isset($ipMeta['hosting']) ? ($ipMeta['hosting'] ? 'Yes' : 'No') : 'Not Available',
            'proxy_flag' => isset($ipMeta['proxy']) ? ($ipMeta['proxy'] ? 'Yes' : 'No') : 'Not Available',
            'gps_consent_flag' => $submitData['gps_consent'] ?? 'Not Available',
            'gps_latitude' => $gpsLat,
            'gps_longitude' => $gpsLng,
            'gps_accuracy' => $gpsAccuracy,
            'gps_timestamp' => $gpsTimestamp,
            'gps_source' => $gpsSource,
            'gps_altitude' => $submitData['gps_altitude'] ?? 'Not Available',
            'gps_heading' => $submitData['gps_heading'] ?? 'Not Available',
            'gps_speed' => $submitData['gps_speed'] ?? 'Not Available',
        ];

        if ($loc['latitude'] !== 'Not Available' && $loc['longitude'] !== 'Not Available') {
            $loc['google_maps_link'] = 'https://www.google.com/maps?q=' . $loc['latitude'] . ',' . $loc['longitude'];
        } else {
            $loc['google_maps_link'] = 'Not Available';
        }

        $uaData = $this->parseUserAgent();
        $bot = $this->classifyBot($uaData);

        // UTM tracking parameters
        $utmSource = $submitData['utm_source'] ?? request()->input('utm_source');
        $utmMedium = $submitData['utm_medium'] ?? request()->input('utm_medium');
        $utmCampaign = $submitData['utm_campaign'] ?? request()->input('utm_campaign');
        $utmTerm = $submitData['utm_term'] ?? request()->input('utm_term');
        $utmContent = $submitData['utm_content'] ?? request()->input('utm_content');

        // GCLID Google Ads tracking
        $gclid = request()->input('gclid') ?? session('google_ads_gclid');
        if (!empty($gclid)) {
            session(['google_ads_gclid' => $gclid]);
            if (empty($utmSource)) $utmSource = 'Google Ads';
            if (empty($utmMedium)) $utmMedium = 'CPC';
            
            $campaignId = request()->input('gad_campaignid');
            if (!$campaignId && is_numeric($utmCampaign)) {
                $campaignId = $utmCampaign;
            }
            if ((string)$campaignId === '23467597613') {
                $utmCampaign = 'Desert Safari Dubai - 15 Jan 2026';
            } elseif ($campaignId && empty($utmCampaign)) {
                $utmCampaign = 'Campaign ' . $campaignId;
            }
        }

        // Referrer parsing fallbacks
        if (empty($utmSource) && $referrer !== 'Not Available' && !empty($referrer)) {
            $refHost = parse_url($referrer, PHP_URL_HOST);
            $currentHost = request()->getHost();
            $cleanRef = preg_replace('/^www\./', '', $refHost ?: '');
            $cleanCurr = preg_replace('/^www\./', '', $currentHost ?: '');

            if ($refHost && !str_contains($cleanRef, $cleanCurr)) {
                if (str_contains($refHost, 'syndicatedsearch.goog')) {
                    $utmSource = 'Google Ads';
                    $utmMedium = 'CPC';
                } elseif (str_contains($refHost, 'google.')) {
                    $utmSource = 'Google Organic';
                    $utmMedium = 'Organic';
                } elseif (str_contains($refHost, 'facebook.com') || str_contains($refHost, 'fb.com')) {
                    $utmSource = 'Facebook';
                    $utmMedium = 'Social';
                } elseif (str_contains($refHost, 'instagram.com')) {
                    $utmSource = 'Instagram';
                    $utmMedium = 'Social';
                } elseif (str_contains($refHost, 'bing.com')) {
                    $utmSource = 'Bing Organic';
                    $utmMedium = 'Organic';
                } else {
                    $utmSource = $refHost;
                    $utmMedium = 'Referral';
                }
            }
        }

        if (empty($utmSource)) {
            $utmSource = 'Direct';
            $utmMedium = 'None';
        }

        // Session tracking
        if (!session()->has('tracking_session_start')) {
            session([
                'tracking_session_start' => microtime(true),
                'tracking_pages' => 0,
                'tracking_landing' => request()->getRequestUri() ?: '/'
            ]);
        }

        $pagesCount = (int)session('tracking_pages', 0) + 1;
        session(['tracking_pages' => $pagesCount]);

        $now = microtime(true);
        $durationSec = (int)max(0, $now - (float)session('tracking_session_start'));

        $formLoadTs = null;
        $formDuration = 0;
        if (session()->has("form_load.{$formName}")) {
            $load = (float)session("form_load.{$formName}");
            $formDuration = (int)max(0, $now - $load);
            $formLoadTs = date('Y-m-d H:i:s', (int)$load);
        }

        $isRepeat = session()->has('tracking_seen') ? 'Yes' : 'No';
        session(['tracking_seen' => true]);

        return array_merge([
            'form_name' => $formName,
            'request_timestamp' => $requestTs,
            'request_method' => $method,
            'request_uri' => $uri,
            'query_string' => $query,
            'host' => $host,
            'server_protocol' => $proto,
            'https_flag' => $https,
            'client_ip' => $ipInfo['client_ip'],
            'ip_version' => $ipInfo['ip_version'],
            'ip_hash' => $ipInfo['ip_hash'],
            'bot_indicator' => $bot,
            'accept_language' => $acceptLang,
            'accept_encoding' => $acceptEnc,
            'referrer_url' => $referrer,
            'utm_source' => $utmSource,
            'utm_medium' => $utmMedium,
            'utm_campaign' => $utmCampaign ?: 'Not Available',
            'utm_term' => $utmTerm ?: 'Not Available',
            'utm_content' => $utmContent ?: 'Not Available',
            'landing_page' => session('tracking_landing', '/'),
            'session_id' => session()->getId() ?: 'Not Available',
            'session_start_time' => date('Y-m-d H:i:s', (int)session('tracking_session_start')),
            'session_end_time' => date('Y-m-d H:i:s', (int)$now),
            'session_duration_seconds' => $durationSec,
            'pages_viewed_count' => $pagesCount,
            'form_load_timestamp' => $formLoadTs,
            'form_submit_timestamp' => date('Y-m-d H:i:s', (int)$now),
            'form_completion_seconds' => $formDuration,
            'repeat_visit_flag' => $isRepeat,
        ], $loc, $uaData);
    }

    /**
     * Check if a request log should be skipped (e.g. search engines, loopbacks).
     */
    public function shouldSkip(array $ctx): bool
    {
        $ip = strtolower($ctx['client_ip'] ?? '');
        $ua = strtolower($ctx['user_agent'] ?? '');

        if (empty($ip) || in_array($ip, ['not available', '0.0.0.0', '127.0.0.1', '::1', 'localhost'])) {
            return true;
        }

        // Whitelisted test client IP in prod to prevent log floods
        if ($ip === '175.107.244.213') {
            return true;
        }

        if (($ctx['bot_indicator'] ?? '') === 'Likely Bot') {
            return true;
        }

        $bots = ['googlebot', 'adsbot-google', 'bingbot', 'duckduckbot', 'baiduspider', 'yandexbot', 'ahrefsbot', 'semrushbot', 'mj12bot', 'petalbot', 'applebot', 'gptbot', 'claudebot', 'perplexitybot', 'facebookexternalhit', 'twitterbot', 'slackbot', 'telegrambot', 'curl', 'wget', 'python-requests', 'httpclient', 'libwww'];
        foreach ($bots as $b) {
            if (str_contains($ua, $b)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create request log and return its ID.
     */
    public function logRequest(string $entityType, ?int $entityId, string $formName, array $ctx): ?int
    {
        if ($this->shouldSkip($ctx)) {
            return null;
        }

        try {
            $log = RequestLog::create(array_merge([
                'entity_type' => $entityType,
                'entity_id' => $entityId,
            ], $ctx));

            return $log->id;
        } catch (\Exception $e) {
            Log::error("Failed to write request log context: " . $e->getMessage(), [
                'ctx' => $ctx
            ]);
            return null;
        }
    }
}
