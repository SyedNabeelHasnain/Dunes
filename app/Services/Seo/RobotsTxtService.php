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

    /**
     * Parse robots.txt into User-agent stanzas: [['agents' => [...], 'rules' => [['directive' => 'allow|disallow', 'path' => '...']]]]
     */
    private function parseStanzas(string $content): array
    {
        $stanzas = [];
        $current = null;
        $seenRuleInCurrent = false;

        foreach (preg_split('/\R/', $content) ?: [] as $rawLine) {
            $line = trim((string) preg_replace('/#.*$/', '', $rawLine));
            if ($line === '') {
                if ($current !== null && $seenRuleInCurrent) {
                    $stanzas[] = $current;
                    $current = null;
                    $seenRuleInCurrent = false;
                }
                continue;
            }

            if (preg_match('/^User-agent:\s*(.+)$/i', $line, $m)) {
                $agent = trim($m[1]);
                if ($current === null || $seenRuleInCurrent) {
                    if ($current !== null) {
                        $stanzas[] = $current;
                    }
                    $current = ['agents' => [$agent], 'rules' => []];
                    $seenRuleInCurrent = false;
                } else {
                    $current['agents'][] = $agent;
                }
                continue;
            }

            if ($current !== null && preg_match('/^(Allow|Disallow):\s*(.*)$/i', $line, $m)) {
                $current['rules'][] = [
                    'directive' => strtolower($m[1]),
                    'path' => trim($m[2]),
                ];
                $seenRuleInCurrent = true;
            }
        }

        if ($current !== null) {
            $stanzas[] = $current;
        }

        return $stanzas;
    }

    public function getBotStates(?string $content = null): array
    {
        $content ??= $this->getCurrentContent();
        $stanzas = $this->parseStanzas($content);
        $states = [];

        foreach (self::BOT_GROUPS as $group) {
            foreach ($group['bots'] as $bot) {
                $status = 'allow';
                $botLower = strtolower($bot);
                foreach ($stanzas as $stanza) {
                    $agentsLower = array_map('strtolower', $stanza['agents']);
                    if (! in_array($botLower, $agentsLower, true)) {
                        continue;
                    }
                    foreach ($stanza['rules'] as $rule) {
                        if ($rule['directive'] === 'disallow' && $rule['path'] === '/') {
                            $status = 'disallow';
                        } elseif ($rule['directive'] === 'allow' && $rule['path'] === '/') {
                            $status = 'allow';
                        }
                    }
                }
                $states[$bot] = $status;
            }
        }

        return $states;
    }

    /**
     * Extract custom User-agent: * rules beyond the baseline protected directives.
     */
    public function extractCustomDisallows(?string $content = null): string
    {
        $content ??= $this->getCurrentContent();
        $stanzas = $this->parseStanzas($content);
        $defaults = [
            'allow:/',
            'disallow:/admin/',
            'disallow:/booking/draft',
            'disallow:/ajax.php',
        ];

        $custom = [];
        foreach ($stanzas as $stanza) {
            if (! in_array('*', $stanza['agents'], true)) {
                continue;
            }
            foreach ($stanza['rules'] as $rule) {
                $sig = $rule['directive'].':'.$rule['path'];
                if ($rule['path'] === '' || in_array(strtolower($sig), $defaults, true)) {
                    continue;
                }
                $label = ucfirst($rule['directive']).': '.$rule['path'];
                if (! in_array($label, $custom, true)) {
                    $custom[] = $label;
                }
            }
        }

        return implode("\n", $custom);
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
            if ($trimmed !== '' && ! in_array(strtolower($trimmed), ['disallow: /search', 'disallow: /thankyou', 'disallow: /payment-cancel', 'disallow: /unsubscribe/'], true)) {
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

        foreach ($this->parseStanzas($content) as $stanza) {
            if (! in_array('*', $stanza['agents'], true)) {
                continue;
            }
            $hasAllowRoot = false;
            $hasDisallowRoot = false;
            foreach ($stanza['rules'] as $rule) {
                if ($rule['directive'] === 'allow' && $rule['path'] === '/') {
                    $hasAllowRoot = true;
                }
                if ($rule['directive'] === 'disallow' && $rule['path'] === '/') {
                    $hasDisallowRoot = true;
                }
            }
            if ($hasDisallowRoot && ! $hasAllowRoot) {
                $errors[] = 'Wildcard rule "User-agent: * Disallow: /" blocks the entire website from search indexing.';
                break;
            }
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

        // Sync static public/robots.txt (and Hostinger ../public_html mirror if present) for web servers serving static files directly
        if (! App::runningUnitTests()) {
            @file_put_contents(public_path('robots.txt'), $content);
            $hostingerPublic = base_path('../public_html');
            if (is_dir($hostingerPublic) && is_writable($hostingerPublic)) {
                @file_put_contents($hostingerPublic.DIRECTORY_SEPARATOR.'robots.txt', $content);
            }
        }
    }

    private function defaultContent(): string
    {
        return $this->compileFromBotPolicies([]);
    }
}
