<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Language;
use App\Models\Setting;
use App\Models\Tour;
use App\Models\WikiArticle;
use App\Models\WikiPillar;
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
     * Get the configured IndexNow verification key (strictly validated against path traversal/reserved names).
     */
    public function getKey(): string
    {
        $key = trim((string) $this->settings->get('indexnow_key', ''));
        $reserved = ['robots', 'sitemap', 'llms', 'llms-full', 'security', 'index'];

        if (
            $key === ''
            || ! preg_match('/^[a-zA-Z0-9_\-]{8,64}$/', $key)
            || in_array(strtolower($key), $reserved, true)
        ) {
            return self::DEFAULT_KEY;
        }

        return $key;
    }

    /**
     * Ensure the static IndexNow verification key file exists physically in public/.
     * Enables direct web server serving without PHP execution overhead.
     */
    public function ensureKeyFileExists(): void
    {
        $key = $this->getKey();
        if (empty($key) || ! preg_match('/^[a-zA-Z0-9_\-]{8,64}$/', $key)) {
            return;
        }

        $filePath = public_path($key . '.txt');

        try {
            if (!file_exists($filePath) || trim((string) @file_get_contents($filePath)) !== $key) {
                @file_put_contents($filePath, $key);
            }
        } catch (\Throwable $e) {
            Log::warning('IndexNow: Could not write physical verification key file: ' . $e->getMessage());
        }
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
     * Must be an absolute HTTPS URL hosted on the verified domain.
     */
    public function getKeyLocation(): string
    {
        return 'https://' . $this->getHost() . '/' . $this->getKey() . '.txt';
    }

    /**
     * Submit an array of URLs to the IndexNow protocol.
     */
    public function submit(array $urls): array
    {
        $this->ensureKeyFileExists();

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

        // Ensure URLs are absolute HTTPS URLs belonging to this host
        $cleanUrls = [];
        foreach ($urls as $url) {
            $parsedHost = parse_url($url, PHP_URL_HOST);
            $parsedPath = parse_url($url, PHP_URL_PATH) ?? '/';
            $parsedQuery = parse_url($url, PHP_URL_QUERY);

            // Re-normalize URL to canonical https://{host}{path}
            if ($parsedHost === $host || $parsedHost === 'dunesdiscoverytourism.com' || $parsedHost === 'localhost' || $parsedHost === '127.0.0.1' || empty($parsedHost)) {
                $fullUrl = 'https://' . $host . ($parsedPath === '/' && empty($parsedQuery) ? '/' : $parsedPath);
                if (!empty($parsedQuery)) {
                    $fullUrl .= '?' . $parsedQuery;
                }
                $cleanUrls[] = $fullUrl;
            }
        }

        $cleanUrls = array_values(array_unique(array_filter($cleanUrls)));

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
            $body = $response->body();
            // HTTP 200 (OK) or 202 (Accepted) signify successful submission
            $isSuccess = in_array($status, [200, 202], true);

            $isVerificationPending = false;
            if ($isSuccess) {
                $message = 'Successfully submitted ' . count($cleanUrls) . ' URLs to search engines (Bing, Yandex, etc.) via IndexNow.';
            } elseif ($status === 403 || str_contains($body, 'SiteVerificationNotCompleted')) {
                $isVerificationPending = true;
                $message = "Site verification is in progress with IndexNow / Bing. Your verification key is published at {$keyLocation}. Search engines typically take 10–30 minutes to complete initial validation. Once validated, all future submissions succeed automatically.";
            } elseif ($status === 429) {
                $message = 'IndexNow submission rate limit reached. Please wait a few moments before trying again.';
            } else {
                $decoded = json_decode($body, true);
                $detailMsg = $decoded['message'] ?? substr($body, 0, 200);
                $message = "IndexNow responded with status code {$status}: {$detailMsg}";
            }

            $result = [
                'success' => $isSuccess,
                'is_verification_pending' => $isVerificationPending,
                'status' => $status,
                'message' => $message,
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

            Log::info('IndexNow submitted ' . count($cleanUrls) . " URLs (HTTP {$status}).");

            return $result;
        } catch (\Throwable $e) {
            Log::warning('IndexNow submission failed: ' . $e->getMessage());

            return [
                'success' => false,
                'is_verification_pending' => false,
                'status' => 500,
                'message' => 'Network error connecting to IndexNow API: ' . $e->getMessage(),
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
        $this->ensureKeyFileExists();

        $paths = [
            '/',
            '/about',
            '/contact',
            '/faq',
            '/tours',
            '/rate-card',
            '/blog',
            '/gallery',
            '/dune-buggy-rental-dubai',
            '/dubai-desert-sunset-times',
            '/press-kit',
            '/build-your-own-safari',
            '/terms-condition',
            '/privacy-policy',
            '/cookie-policy',
            '/cancellation-refund-policy',
            '/payment-security-policy',
            '/safety-liability-waiver',
            '/ai-editorial-policy',
            '/responsible-tourism-policy',
        ];

        foreach (LocationLandingService::getLocations() as $loc) {
            $paths[] = '/' . $loc['slug'];
        }

        try {
            $tourSlugs = Tour::where('status', 'active')->pluck('slug');
            foreach ($tourSlugs as $slug) {
                $paths[] = '/' . $slug;
            }
        } catch (\Throwable $e) {
        }

        try {
            $blogSlugs = BlogPost::where('status', 'published')->pluck('slug');
            foreach ($blogSlugs as $slug) {
                $paths[] = '/blog/' . $slug;
            }
        } catch (\Throwable $e) {
        }

        try {
            $paths[] = '/wiki';
            $paths[] = '/wiki/glossary';
            $activePillars = WikiPillar::where('status', 'active')->with(['articles' => fn ($q) => $q->where('status', 'published')])->get();
            foreach ($activePillars as $pillar) {
                $paths[] = '/wiki/' . $pillar->slug;
                foreach ($pillar->articles as $article) {
                    $paths[] = '/wiki/' . $pillar->slug . '/' . $article->slug;
                }
            }
        } catch (\Throwable $e) {
        }

        try {
            $languages = Language::getActive();
        } catch (\Throwable $e) {
            $languages = Language::fallbackCollection();
        }

        $host = $this->getHost();
        $scheme = 'https://';
        $urls = [];
        foreach ($paths as $path) {
            $cleanPath = $path === '/' ? '' : $path;
            $urls[] = $scheme . $host . $cleanPath;
            foreach ($languages as $lang) {
                if (! $lang->is_default) {
                    $urls[] = $path === '/'
                        ? $scheme . $host . '/' . $lang->code
                        : $scheme . $host . '/' . $lang->code . $path;
                }
            }
        }

        return $this->submit($urls);
    }
}
