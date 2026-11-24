<?php

namespace App\Services\Seo;

use App\Models\Setting;
use Illuminate\Support\Facades\App;

/**
 * Manages robots.txt directives, AI crawler category controls, validation,
 * and public file synchronization.
 */
class RobotsTxtService
{
    public const BOT_GROUPS = [
        'search_engines' => [
            'label' => 'Search Engines (Google, Bing, Apple)',
            'bots' => ['Googlebot', 'Bingbot', 'Applebot'],
        ],
        'ai_search' => [
            'label' => 'AI Search & Citation Crawlers',
            'bots' => ['GPTBot', 'ChatGPT-User', 'OAI-SearchBot', 'ClaudeBot', 'PerplexityBot', 'Google-Extended'],
        ],
        'ai_scrapers' => [
            'label' => 'AI Training & Bulk Scrapers',
            'bots' => ['CCBot', 'anthropic-ai', 'Bytespider', 'Amazonbot', 'Diffbot'],
        ],
    ];

    public function getCurrentContent(): string
    {
        $stored = Setting::getVal('robots_txt_content');
        if (is_string($stored) && trim($stored) !== '') {
            return $stored;
        }

        $publicPath = public_path('robots.txt');
        if (file_exists($publicPath)) {
            return (string) file_get_contents($publicPath);
        }

        return $this->defaultContent();
    }

    public function getBotStates(?string $content = null): array
    {
        $content ??= $this->getCurrentContent();
        $states = [];

        foreach (self::BOT_GROUPS as $groupKey => $group) {
            foreach ($group['bots'] as $bot) {
                $pattern = '/User-agent:\s*'.preg_quote($bot, '/').'\R+\s*(Allow|Disallow):\s*(\S+)/i';
                $status = 'allow';
                if (preg_match($pattern, $content, $m)) {
                    $directive = strtolower($m[1]);
                    $path = trim($m[2]);
                    $status = ($directive === 'disallow' && $path === '/') ? 'disallow' : 'allow';
                }
                $states[$bot] = $status;
            }
        }

        return $states;
    }

    /**
     * Build clean robots.txt directives from selected bot policy map + custom lines.
     */
    public function compileFromBotPolicies(array $botPolicies, string $customDisallows = ''): string
    {
        $baseUrl = rtrim((string) config('app.url', 'https://dunesdiscoverytourism.com'), '/');
        if (! str_starts_with($baseUrl, 'http')) {
            $baseUrl = 'https://dunesdiscoverytourism.com';
        }

        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin/',
            'Disallow: /booking/draft',
            'Disallow: /ajax.php',
        ];

        foreach (preg_split('/\R/', $customDisallows) ?: [] as $line) {
            $trimmed = trim($line);
            if ($trimmed !== '' && ! in_array($trimmed, ['Disallow: /search', 'Disallow: /thankyou', 'Disallow: /payment-cancel', 'Disallow: /unsubscribe/'], true)) {
                $lines[] = str_starts_with(strtolower($trimmed), 'disallow:') || str_starts_with(strtolower($trimmed), 'allow:')
                    ? $trimmed
                    : 'Disallow: /'.ltrim($trimmed, '/');
            }
        }

        $lines[] = '';

        foreach (self::BOT_GROUPS as $group) {
            foreach ($group['bots'] as $bot) {
                $mode = ($botPolicies[$bot] ?? 'allow') === 'disallow' ? 'Disallow: /' : 'Allow: /';
                $lines[] = "User-agent: {$bot}";
                $lines[] = $mode;
                $lines[] = '';
            }
        }

        $lines[] = "Sitemap: {$baseUrl}/sitemap_index.xml";
        $lines[] = "Sitemap: {$baseUrl}/sitemap.xml";
        $lines[] = '';
        $lines[] = '# Generative Engine Optimization (GEO) & LLM Documentation';
        $lines[] = '# https://llmstxt.org/';
        $lines[] = "# {$baseUrl}/llms.txt";
        $lines[] = "# {$baseUrl}/llms-full.txt";

        return implode("\n", $lines);
    }

    /**
     * Validate robots.txt text for dangerous rules before saving.
     *
     * @return list<string> Validation errors (empty when valid)
     */
    public function validateContent(string $content): array
    {
        $errors = [];

        if (trim($content) === '') {
            $errors[] = 'robots.txt content cannot be empty.';

            return $errors;
        }

        if (preg_match('/User-agent:\s*\*\R+(?:Allow:[^\r\n]*\R+)*Disallow:\s*\/\s*$/im', $content)) {
            $errors[] = 'Wildcard rule "User-agent: * Disallow: /" blocks the entire website from search indexing.';
        }

        foreach (['/search', '/thankyou', '/payment-cancel', '/unsubscribe/'] as $noindexPage) {
            if (preg_match('/^Disallow:\s*'.preg_quote($noindexPage, '/').'\s*$/im', $content)) {
                $errors[] = "Do not Disallow '{$noindexPage}' so crawlers can read its meta noindex directive cleanly.";
            }
        }

        if (! preg_match('/^Disallow:\s*\/admin\/?/im', $content)) {
            $errors[] = 'robots.txt must disallow /admin/ backend paths.';
        }

        if (! str_contains(strtolower($content), 'sitemap:')) {
            $errors[] = 'Include at least one Sitemap: directive pointing to sitemap_index.xml.';
        }

        return $errors;
    }

    public function persistContent(string $content): void
    {
        Setting::setVal('robots_txt_content', $content);

        // Sync static public/robots.txt for web servers serving static files directly (skip during PHPUnit to avoid dirtying repo)
        if (! App::runningUnitTests()) {
            @file_put_contents(public_path('robots.txt'), $content);
        }
    }

    private function defaultContent(): string
    {
        return $this->compileFromBotPolicies([]);
    }
}
