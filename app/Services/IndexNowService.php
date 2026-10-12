<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Setting;
use App\Models\Tour;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndexNowService
{
    /**
     * Official IndexNow API submission endpoint.
     * IndexNow automatically disseminates updates across Bing, Yandex, Seznam, Naver, and other participating engines.
     */
    public const API_ENDPOINT = 'https://api.indexnow.org/indexnow';

    /**
     * Default fallback 32-character key if not yet saved in CMS.
     */
    public const DEFAULT_KEY = '9f2b87e2467d4ab8a9b31d871e4f9b88';

    protected SettingsService $settings;

    public function __construct(SettingsService $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Check if IndexNow automatic submission is enabled.
     */
    public function isEnabled(): bool
    {
        $val = $this->settings->get('indexnow_enabled', '1');

        return $val === '1' || $val === 'true';
    }

    /**
     * Get the configured IndexNow verification key.
     */
    public function getKey(): string
    {
        $key = trim((string) $this->settings->get('indexnow_key', ''));

        return ! empty($key) ? $key : self::DEFAULT_KEY;
    }

    /**
     * Get the host domain (e.g. dunesdiscoverytourism.com).
     */
    public function getHost(): string
    {
        $appUrl = config('app.url', 'https://dunesdiscoverytourism.com');
        $host = parse_url($appUrl, PHP_URL_HOST);
        if (empty($host) || $host === 'localhost' || $host === '127.0.0.1') {
            return 'dunesdiscoverytourism.com';
        }

        return $host;
    }

    /**
     * Get key location verification URL.
     */
    public function getKeyLocation(): string
    {
        return url('/'.$this->getKey().'.txt');
    }

    /**
     * Submit an array of URLs to the IndexNow protocol.
     */
    public function submit(array $urls): array
    {
        $urls = array_values(array_unique(array_filter($urls)));
        if (empty($urls)) {
            return [
                'success' => false,
                'status' => 400,
                'message' => 'No URLs provided for submission.',
                'count' => 0,
            ];
        }

        $host = $this->getHost();
        $key = $this->getKey();
        $keyLocation = $this->getKeyLocation();

        // Ensure URLs are absolute and belong to this host
        $cleanUrls = [];
        foreach ($urls as $url) {
            $parsedHost = parse_url($url, PHP_URL_HOST);
            if ($parsedHost === $host || $parsedHost === 'dunesdiscoverytourism.com' || $parsedHost === 'localhost' || empty($parsedHost)) {
                $cleanUrls[] = str_starts_with($url, 'http') ? $url : url($url);
            }
        }

        if (empty($cleanUrls)) {
            return [
                'success' => false,
                'status' => 422,
                'message' => "None of the provided URLs match host {$host}.",
                'count' => 0,
            ];
        }

        // Maximum 10,000 URLs per IndexNow batch
        $cleanUrls = array_slice($cleanUrls, 0, 10000);

        $payload = [
            'host' => $host,
            'key' => $key,
            'keyLocation' => $keyLocation,
            'urlList' => $cleanUrls,
        ];

        try {
            $response = Http::timeout(12)
                ->withHeaders([
                    'Content-Type' => 'application/json; charset=utf-8',
                    'User-Agent' => 'DunesDiscoveryTourism-IndexNow/1.0',
                ])
                ->post(self::API_ENDPOINT, $payload);

            $status = $response->status();
            // HTTP 200 (OK) or 202 (Accepted) signify successful submission
            $isSuccess = in_array($status, [200, 202], true);

            $result = [
                'success' => $isSuccess,
                'status' => $status,
                'message' => $isSuccess
                    ? 'Successfully submitted '.count($cleanUrls).' URLs to search engines via IndexNow.'
                    : "IndexNow responded with status code {$status}: ".substr($response->body(), 0, 200),
                'count' => count($cleanUrls),
                'submitted_at' => now()->toIso8601String(),
            ];

            // Persist last submission details in settings
            try {
                Setting::updateOrCreate(
                    ['setting_key' => 'indexnow_last_submission'],
                    ['setting_value' => json_encode($result, JSON_UNESCAPED_SLASHES)]
                );
                $this->settings->clearCache();
            } catch (\Throwable $e) {
                // Non-blocking
            }

            Log::info('IndexNow submitted '.count($cleanUrls)." URLs (HTTP {$status}).");

            return $result;
        } catch (\Throwable $e) {
            Log::warning('IndexNow submission failed: '.$e->getMessage());

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Network error connecting to IndexNow API: '.$e->getMessage(),
                'count' => count($cleanUrls),
                'submitted_at' => now()->toIso8601String(),
            ];
        }
    }

    /**
     * Submit all public sitemap URLs.
     */
    public function submitAll(): array
    {
        $urls = [
            url('/'),
            url('/about'),
            url('/contact'),
            url('/faq'),
            url('/tours'),
            url('/rate-card'),
            url('/blog'),
            url('/gallery'),
            url('/dune-buggy-rental-dubai'),
            url('/dubai-desert-sunset-times'),
            url('/press-kit'),
            url('/terms-condition'),
            url('/privacy-policy'),
            url('/cookie-policy'),
            url('/cancellation-refund-policy'),
            url('/payment-security-policy'),
            url('/safety-liability-waiver'),
            url('/ai-editorial-policy'),
            url('/responsible-tourism-policy'),
        ];

        try {
            $tourSlugs = Tour::where('status', 'active')->pluck('slug');
            foreach ($tourSlugs as $slug) {
                $urls[] = url('/'.$slug);
            }
        } catch (\Throwable $e) {
        }

        try {
            $blogSlugs = BlogPost::where('status', 'published')->pluck('slug');
            foreach ($blogSlugs as $slug) {
                $urls[] = url('/blog/'.$slug);
            }
        } catch (\Throwable $e) {
        }

        return $this->submit($urls);
    }
}
