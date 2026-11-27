<?php

namespace App\Services\Audit\Checkers;

use App\Models\Setting;

class AiReadinessChecker implements AuditCheckerInterface
{
    public function run(array $context = []): array
    {
        $issues = [];

        // 1. Robots.txt AI Crawlers Inspection (reads authoritative CMS/static RobotsTxtService)
        $robotsTxt = trim(app(\App\Services\Seo\RobotsTxtService::class)->getCurrentContent());
        if ($robotsTxt === '') {
            $issues[] = [
                'category' => 'ai_readiness',
                'check_key' => 'ai_robots_txt_missing',
                'title' => 'Missing robots.txt Directives',
                'description' => 'robots.txt directives are absent. AI crawlers and search spiders have no explicit directive guidance.',
                'severity' => 'critical',
                'verification_type' => 'automated',
                'affected_url' => url('/robots.txt'),
                'evidence' => 'RobotsTxtService returned empty directives.',
                'recommended_action' => 'Configure robots.txt directives in CMS Technical SEO specifying crawler rules and sitemap locations.',
            ];
        } else {
            $requiredAiBots = ['GPTBot', 'ClaudeBot', 'PerplexityBot', 'Google-Extended'];
            $missingBots = [];

            foreach ($requiredAiBots as $bot) {
                if (!str_contains($robotsTxt, $bot)) {
                    $missingBots[] = $bot;
                }
            }

            if (!empty($missingBots)) {
                $issues[] = [
                    'category' => 'ai_readiness',
                    'check_key' => 'ai_missing_crawler_declarations',
                    'title' => 'Missing Explicit Directives for Major AI Crawlers',
                    'description' => 'robots.txt does not explicitly declare policies for: ' . implode(', ', $missingBots) . '.',
                    'severity' => 'medium',
                    'verification_type' => 'automated',
                    'affected_url' => url('/robots.txt'),
                    'evidence' => 'Missing user-agent rules in robots.txt: ' . implode(', ', $missingBots),
                    'recommended_action' => 'Add explicit User-agent rules allowing legitimate AI discovery engines while protecting private admin endpoints.',
                ];
            }

            if (!str_contains($robotsTxt, 'llms.txt')) {
                $issues[] = [
                    'category' => 'ai_readiness',
                    'check_key' => 'ai_robots_missing_llms_pointer',
                    'title' => 'robots.txt Lacks Pointer to /llms.txt',
                    'description' => 'Declaring the llms.txt discovery endpoint in robots.txt accelerates indexation by LLM data scrapers and answer engines.',
                    'severity' => 'low',
                    'verification_type' => 'automated',
                    'affected_url' => url('/robots.txt'),
                    'evidence' => 'No mention of /llms.txt in robots.txt comments or headers.',
                    'recommended_action' => 'Add a comment link "# Generative Engine Optimization: https://dunesdiscoverytourism.com/llms.txt" to robots.txt.',
                ];
            }
        }

        // 2. /llms.txt and /llms-full.txt Validation
        try {
            $llmsCtrl = app(\App\Http\Controllers\LlmsController::class);
            $respIndex = $llmsCtrl->index();
            $indexContent = (string) $respIndex->getContent();

            if (empty($indexContent) || !str_contains($indexContent, '# Dunes Discovery')) {
                $issues[] = [
                    'category' => 'ai_readiness',
                    'check_key' => 'ai_llms_index_invalid',
                    'title' => 'Machine-Readable /llms.txt Endpoint Failed Validation',
                    'description' => 'The /llms.txt endpoint did not return standard Markdown structure according to llmstxt.org.',
                    'severity' => 'high',
                    'verification_type' => 'automated',
                    'affected_url' => url('/llms.txt'),
                    'evidence' => 'Content length: ' . strlen($indexContent),
                    'recommended_action' => 'Ensure LlmsController::index() outputs markdown starting with # Dunes Discovery and listing active tour packages.',
                ];
            }
        } catch (\Throwable $e) {
            $issues[] = [
                'category' => 'ai_readiness',
                'check_key' => 'ai_llms_controller_error',
                'title' => 'Error Executing /llms.txt Controller',
                'description' => 'An exception occurred while generating the machine-readable manifest.',
                'severity' => 'high',
                'verification_type' => 'automated',
                'affected_url' => url('/llms.txt'),
                'evidence' => $e->getMessage(),
                'recommended_action' => 'Fix database or caching issues in LlmsController.',
            ];
        }

        // 3. Organization Entity & License Consistency
        $license = Setting::where('setting_key', 'company_license_number')->value('setting_value');
        if (empty($license)) {
            $issues[] = [
                'category' => 'ai_readiness',
                'check_key' => 'ai_missing_det_license_entity',
                'title' => 'DET Tourism License Not Configured in Entity Profile',
                'description' => 'LLM search engines (Perplexity, ChatGPT Search) rely on verifiable government credentials to cite travel authorities.',
                'severity' => 'medium',
                'verification_type' => 'automated',
                'affected_url' => url('/about'),
                'evidence' => 'company_license_number setting is empty.',
                'recommended_action' => 'Enter the official Dubai Department of Economy and Tourism (DET) license number in CMS General Settings.',
            ];
        }

        return $issues;
    }
}
