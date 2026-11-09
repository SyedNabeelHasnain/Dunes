<?php

namespace App\Services\Audit\Checkers;

use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Setting;
use App\Models\Tour;

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
            if (empty($post->meta_desc) && empty($post->excerpt)) {
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
            }
        }

        return $issues;
    }
}
