<?php

namespace App\Services\Seo;

use App\Services\Security\SafeUrlValidator;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Log;

/**
 * Provider-independent integration layer for Google Search Console & Bing Webmaster Tools.
 * Never fabricates impressions, clicks, CTR, or rankings; fails safely when credentials
 * are unconfigured, expired, or remote APIs are unreachable.
 */
class SearchConsoleIntegrationService
{
    public function __construct(
        protected SettingsService $settingsService,
    ) {}

    /**
     * Return current integration readiness status without exposing secrets.
     */
    public function getStatusSummary(): array
    {
        $settings = $this->settingsService->all();

        $gVerify = trim((string) ($settings['google_site_verification'] ?? ''));
        $gscProperty = trim((string) ($settings['gsc_property_url'] ?? ''));
        $gscToken = trim((string) (($settings['gsc_oauth_access_token'] ?? '') ?: config('services.google.search_console_token', '')));

        $bVerify = trim((string) ($settings['bing_site_verification'] ?? ''));
        $bingApiKey = trim((string) (($settings['bing_webmaster_api_key'] ?? '') ?: config('services.bing.webmaster_api_key', '')));

        return [
            'google_search_console' => [
                'verified_meta_configured' => $gVerify !== '',
                'property_url' => $gscProperty ?: url('/'),
                'api_configured' => $gscToken !== '',
                'status' => $gscToken !== '' ? 'configured' : 'awaiting_credentials',
                'note' => $gscToken !== ''
                    ? 'OAuth access token present; live Search Analytics API calls available.'
                    : 'Site verification tag is configurable; live clicks/impressions/CTR API sync awaits OAuth credentials.',
            ],
            'bing_webmaster_tools' => [
                'verified_meta_configured' => $bVerify !== '',
                'api_configured' => $bingApiKey !== '',
                'status' => $bingApiKey !== '' ? 'configured' : 'awaiting_credentials',
                'note' => $bingApiKey !== ''
                    ? 'Bing Webmaster API key configured; crawl stats & URL submission API available.'
                    : 'IndexNow instant submission & msvalidate.01 verification active; Bing Traffic/Crawl API awaits API key.',
            ],
        ];
    }

    /**
     * Fetch live Google Search Console Search Analytics summary when OAuth credentials exist.
     */
    public function fetchGoogleSearchAnalytics(int $days = 28): array
    {
        $settings = $this->settingsService->all();
        $token = trim((string) (($settings['gsc_oauth_access_token'] ?? '') ?: config('services.google.search_console_token', '')));
        $property = trim((string) (($settings['gsc_property_url'] ?? '') ?: url('/')));

        if ($token === '') {
            return [
                'success' => false,
                'status' => 'awaiting_credentials',
                'message' => 'Google Search Console OAuth credentials are not configured yet.',
                'rows' => [],
            ];
        }

        $endpoint = 'https://www.googleapis.com/webmasters/v3/sites/'.rawurlencode($property).'/searchAnalytics/query';

        try {
            $response = SafeUrlValidator::httpClient(6, 3)
                ->withToken($token)
                ->acceptJson()
                ->post($endpoint, [
                    'startDate' => now()->subDays($days)->toDateString(),
                    'endDate' => now()->subDay()->toDateString(),
                    'dimensions' => ['page'],
                    'rowLimit' => 25,
                ]);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'status' => $response->status() === 401 ? 'credentials_expired' : 'upstream_error',
                    'http_status' => $response->status(),
                    'message' => 'Google Search Console API returned HTTP '.$response->status().'. Refresh OAuth credentials or check property permissions.',
                    'rows' => [],
                ];
            }

            $json = $response->json();

            return [
                'success' => true,
                'status' => 'connected',
                'source' => 'google_search_console_api',
                'rows' => is_array($json['rows'] ?? null) ? $json['rows'] : [],
            ];
        } catch (\Throwable $e) {
            Log::warning('SearchConsoleIntegrationService Google API failure: '.$e->getMessage());

            return [
                'success' => false,
                'status' => 'connection_error',
                'message' => 'Unable to reach Google Search Console API safely: '.$e->getMessage(),
                'rows' => [],
            ];
        }
    }

    /**
     * Fetch Bing Webmaster Tools crawl stats when an API key is configured.
     */
    public function fetchBingCrawlStats(): array
    {
        $settings = $this->settingsService->all();
        $apiKey = trim((string) (($settings['bing_webmaster_api_key'] ?? '') ?: config('services.bing.webmaster_api_key', '')));
        $rawSiteUrl = trim((string) (($settings['bing_site_url'] ?? '') ?: ($settings['gsc_property_url'] ?? '') ?: url('/')));
        if (str_starts_with(strtolower($rawSiteUrl), 'sc-domain:')) {
            $domain = ltrim(substr($rawSiteUrl, strlen('sc-domain:')), '/');
            $rawSiteUrl = 'https://'.$domain;
        }
        $siteUrl = rtrim($rawSiteUrl, '/');

        if ($apiKey === '') {
            return [
                'success' => false,
                'status' => 'awaiting_credentials',
                'message' => 'Bing Webmaster Tools API key is not configured yet.',
                'stats' => [],
            ];
        }

        $endpoint = 'https://ssl.bing.com/webmaster/api.svc/json/GetCrawlStats?'.http_build_query([
            'apikey' => $apiKey,
            'siteUrl' => $siteUrl,
        ]);

        try {
            $response = SafeUrlValidator::httpClient(6, 3)->acceptJson()->get($endpoint);
            if (! $response->successful()) {
                return [
                    'success' => false,
                    'status' => $response->status() === 401 || $response->status() === 403 ? 'credentials_expired' : 'upstream_error',
                    'http_status' => $response->status(),
                    'message' => 'Bing Webmaster API returned HTTP '.$response->status().'.',
                    'stats' => [],
                ];
            }

            $json = $response->json();

            return [
                'success' => true,
                'status' => 'connected',
                'source' => 'bing_webmaster_api',
                'stats' => is_array($json['d'] ?? null) ? $json['d'] : [],
            ];
        } catch (\Throwable $e) {
            $safeMessage = (string) preg_replace('/(apikey=)[^&\s]+/i', '$1[REDACTED]', $e->getMessage());
            Log::warning('SearchConsoleIntegrationService Bing API failure: '.$safeMessage);

            return [
                'success' => false,
                'status' => 'connection_error',
                'message' => 'Unable to reach Bing Webmaster API safely: '.$safeMessage,
                'stats' => [],
            ];
        }
    }
}
