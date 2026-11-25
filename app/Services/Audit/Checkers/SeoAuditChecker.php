<?php

namespace App\Services\Audit\Checkers;

use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Setting;
use App\Models\Tour;
use App\Models\WikiArticle;
use App\Services\Seo\KeywordIntelligenceService;

class SeoAuditChecker implements AuditCheckerInterface
{
    public function run(array $context = []): array
    {
        $issues = [];

        // 1. Check Global Fallbacks
        $settings = Setting::whereIn('setting_key', [
            'seo_default_title',
            'seo_default_description',
            'seo_default_og_image',
            'google_site_verification',
            'bing_site_verification',
        ])->pluck('setting_value', 'setting_key');

        if (empty($settings['seo_default_title'])) {
            $issues[] = [
                'category' => 'seo',
                'check_key' => 'seo_missing_global_title',
                'title' => 'Missing Global Fallback Meta Title',
                'description' => 'A sitewide fallback meta title is not configured. When individual pages lack custom meta, search engines will construct arbitrary titles.',
                'severity' => 'critical',
                'verification_type' => 'automated',
                'affected_url' => url('/'),
                'evidence' => 'Setting key "seo_default_title" is empty in settings repository.',
                'recommended_action' => 'Go to CMS Settings > SEO & Metadata and configure a compelling Global Default Meta Title (e.g. "Dunes Discovery Tourism | Premium Dubai Desert Safari Tours").',
            ];
        }

        if (empty($settings['seo_default_description'])) {
            $issues[] = [
                'category' => 'seo',
                'check_key' => 'seo_missing_global_description',
                'title' => 'Missing Global Fallback Meta Description',
                'description' => 'A sitewide fallback meta description is missing. Search engine snippets may show raw page text or navigation links.',
                'severity' => 'high',
                'verification_type' => 'automated',
                'affected_url' => url('/'),
                'evidence' => 'Setting key "seo_default_description" is empty.',
                'recommended_action' => 'Configure a 120-160 character description in CMS Settings > SEO & Metadata summarizing Dunes Discovery offerings.',
            ];
        }

        // 2. Check Tour Pages (Meta title length, descriptions, cannibalization)
        $tours = Tour::where('status', 'active')->get();
        $seenTitles = [];
        $seenDescriptions = [];

        foreach ($tours as $tour) {
            $url = url('/' . $tour->slug);
            $metaTitle = trim((string) ($tour->meta_title ?: $tour->name));
            $metaDesc = trim((string) ($tour->meta_desc ?: $tour->short_desc));

            // Title Length
            $tLen = mb_strlen($metaTitle);
            if ($tLen < 25) {
                $issues[] = [
                    'category' => 'seo',
                    'check_key' => 'seo_tour_title_too_short',
                    'title' => "Tour Meta Title Is Too Short ({$tLen} chars)",
                    'description' => "The meta title for tour '{$tour->name}' is shorter than recommended (minimum 30 characters). Short titles waste search snippet real estate.",
                    'severity' => 'medium',
                    'verification_type' => 'automated',
                    'affected_url' => $url,
                    'evidence' => "Title: '{$metaTitle}' (Length: {$tLen})",
                    'recommended_action' => 'Expand the title to 45-60 characters including high-intent commercial keywords and brand suffix.',
                ];
            } elseif ($tLen > 70) {
                $issues[] = [
                    'category' => 'seo',
                    'check_key' => 'seo_tour_title_too_long',
                    'title' => "Tour Meta Title Exceeds Recommended Length ({$tLen} chars)",
                    'description' => "The meta title for '{$tour->name}' is likely to be truncated in Google search results (recommended <= 60 characters).",
                    'severity' => 'low',
                    'verification_type' => 'automated',
                    'affected_url' => $url,
                    'evidence' => "Title: '{$metaTitle}' (Length: {$tLen})",
                    'recommended_action' => 'Shorten the title to under 60 characters to avoid snippet truncation.',
                ];
            }

            // Description Length
            $dLen = mb_strlen($metaDesc);
            if ($dLen === 0) {
                $issues[] = [
                    'category' => 'seo',
                    'check_key' => 'seo_tour_missing_meta_desc',
                    'title' => "Missing Meta Description for Tour '{$tour->name}'",
                    'description' => 'Tour has no meta description configured.',
                    'severity' => 'high',
                    'verification_type' => 'automated',
                    'affected_url' => $url,
                    'evidence' => 'meta_desc and short_desc are both empty.',
                    'recommended_action' => 'Add an enticing 120-160 character meta description highlighting inclusions, vehicles, and booking perks.',
                ];
            } elseif ($dLen < 60) {
                $issues[] = [
                    'category' => 'seo',
                    'check_key' => 'seo_tour_meta_desc_too_short',
                    'title' => "Tour Meta Description Too Short ({$dLen} chars)",
                    'description' => "Meta description for tour '{$tour->name}' is under 60 characters.",
                    'severity' => 'low',
                    'verification_type' => 'automated',
                    'affected_url' => $url,
                    'evidence' => "Description: '{$metaDesc}' (Length: {$dLen})",
                    'recommended_action' => 'Expand the description to 120-160 characters for maximum search snippet engagement.',
                ];
            }

            // Keyword Cannibalization / Duplicate Title Detection
            $normalizedTitle = strtolower($metaTitle);
            if (isset($seenTitles[$normalizedTitle])) {
                $issues[] = [
                    'category' => 'seo',
                    'check_key' => 'seo_duplicate_title_cannibalization',
                    'title' => 'Potential Title Cannibalization Detected',
                    'description' => "Two tours share identical meta titles, causing internal ranking competition.",
                    'severity' => 'high',
                    'verification_type' => 'automated',
                    'affected_url' => $url,
                    'evidence' => "Duplicate title '{$metaTitle}' matches existing tour URL: " . $seenTitles[$normalizedTitle],
                    'recommended_action' => 'Differentiate the titles by specifying package tiers, vehicle horsepower, duration, or target audience.',
                ];
            } else {
                $seenTitles[$normalizedTitle] = $url;
            }

            $normalizedDesc = strtolower(trim($metaDesc));
            if (mb_strlen($normalizedDesc) >= 25) {
                if (isset($seenDescriptions[$normalizedDesc])) {
                    $issues[] = [
                        'category' => 'seo',
                        'check_key' => 'seo_duplicate_meta_description',
                        'title' => "Duplicate Meta Description on '{$tour->name}'",
                        'description' => 'Multiple indexable URLs share an identical meta description, reducing snippet uniqueness in SERPs.',
                        'severity' => 'medium',
                        'verification_type' => 'automated',
                        'affected_url' => $url,
                        'evidence' => 'Matches description already used on: '.$seenDescriptions[$normalizedDesc],
                        'recommended_action' => 'Write a distinct, value-specific 120–155 character meta description for this URL.',
                    ];
                } else {
                    $seenDescriptions[$normalizedDesc] = $url;
                }
            }
        }

        // 3. Check Blog Posts
        $posts = BlogPost::where('status', 'published')->get();
        foreach ($posts as $post) {
            $url = url('/blog/' . $post->slug);
            if (empty($post->meta_title) && empty($post->title)) {
                $issues[] = [
                    'category' => 'seo',
                    'check_key' => 'seo_blog_missing_title',
                    'title' => 'Blog Post Missing Meta Title',
                    'description' => "Published blog post #{$post->id} lacks a title.",
                    'severity' => 'critical',
                    'verification_type' => 'automated',
                    'affected_url' => $url,
                    'evidence' => "Post slug: '{$post->slug}'",
                    'recommended_action' => 'Set an informative title for this post.',
                ];
            }
            $postDesc = trim((string) ($post->meta_desc ?: $post->excerpt));
            if ($postDesc === '') {
                $issues[] = [
                    'category' => 'seo',
                    'check_key' => 'seo_blog_missing_meta_desc',
                    'title' => "Blog Post '{$post->title}' Missing Meta Description",
                    'description' => 'Published blog post has no meta description or excerpt.',
                    'severity' => 'medium',
                    'verification_type' => 'automated',
                    'affected_url' => $url,
                    'evidence' => 'meta_desc and excerpt are both empty.',
                    'recommended_action' => 'Add an engaging 120-155 character excerpt summarizing the article findings.',
                ];
            } elseif (mb_strlen($postDesc) >= 25) {
                $normPostDesc = strtolower($postDesc);
                if (isset($seenDescriptions[$normPostDesc])) {
                    $issues[] = [
                        'category' => 'seo',
                        'check_key' => 'seo_duplicate_meta_description',
                        'title' => "Duplicate Meta Description on Blog '{$post->title}'",
                        'description' => 'This blog post shares an identical meta description with another published URL.',
                        'severity' => 'medium',
                        'verification_type' => 'automated',
                        'affected_url' => $url,
                        'evidence' => 'Matches description already used on: '.$seenDescriptions[$normPostDesc],
                        'recommended_action' => 'Differentiate the meta description for this article.',
                    ];
                } else {
                    $seenDescriptions[$normPostDesc] = $url;
                }
            }
        }

        // 4. Wiki Article Metadata Coverage
        try {
            $wikiArticles = WikiArticle::where('status', 'published')->with('pillar:id,slug')->get();
            foreach ($wikiArticles as $article) {
                $wUrl = url('/wiki/'.($article->pillar?->slug ?? 'guide').'/'.$article->slug);
                if (empty($article->meta_description) && empty($article->excerpt)) {
                    $issues[] = [
                        'category' => 'seo',
                        'check_key' => 'seo_wiki_missing_meta_desc',
                        'title' => "Wiki Article '{$article->title}' Missing Meta Description",
                        'description' => 'Published wiki guide lacks both meta description and excerpt.',
                        'severity' => 'medium',
                        'verification_type' => 'automated',
                        'affected_url' => $wUrl,
                        'evidence' => "WikiArticle #{$article->id} has empty meta_description and excerpt.",
                        'recommended_action' => 'Add a concise 120–155 character summary in the Wiki Editor.',
                    ];
                }
            }
        } catch (\Throwable) {
        }

        // 5. Focus Keyword Cannibalization Check
        try {
            $kwReport = app(KeywordIntelligenceService::class)->buildReport();
            foreach (array_slice($kwReport['cannibalization'] ?? [], 0, 8) as $group) {
                $urls = collect($group['items'] ?? [])->pluck('path')->implode(', ');
                $issues[] = [
                    'category' => 'seo',
                    'check_key' => 'seo_keyword_cannibalization',
                    'title' => "Keyword Cannibalization on '{$group['keyword']}' ({$group['count']} URLs)",
                    'description' => 'Multiple indexable pages target the exact same focus keyword.',
                    'severity' => ($group['severity'] ?? 'medium') === 'high' ? 'high' : 'medium',
                    'verification_type' => 'automated',
                    'affected_url' => route('admin.settings.keywords.index'),
                    'evidence' => "Competing URLs: {$urls}",
                    'recommended_action' => (string) ($group['recommendation'] ?? 'Differentiate focus keywords or consolidate overlapping pages.'),
                ];
            }
        } catch (\Throwable) {
        }

        return $issues;
    }
}
