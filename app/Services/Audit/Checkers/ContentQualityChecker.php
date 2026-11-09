<?php

namespace App\Services\Audit\Checkers;

use App\Models\BlogPost;
use App\Models\Tour;
use App\Models\WikiArticle;

class ContentQualityChecker implements AuditCheckerInterface
{
    public function run(array $context = []): array
    {
        $issues = [];

        // 1. Thin Blog Posts (< 250 words)
        $posts = BlogPost::where('status', 'published')->get();
        foreach ($posts as $post) {
            $text = strip_tags((string) $post->content);
            $wordCount = str_word_count($text);

            if ($wordCount < 250) {
                $issues[] = [
                    'category' => 'content_quality',
                    'check_key' => 'content_thin_blog_post',
                    'title' => "Thin Content Detected in Blog Post '{$post->title}' ({$wordCount} words)",
                    'description' => "Google Helpful Content System and E-E-A-T guidelines deprecate thin, uninformative articles. Comprehensive travel guides typically require at least 500-1200 words.",
                    'severity' => 'medium',
                    'verification_type' => 'automated',
                    'affected_url' => url('/blog/' . $post->slug),
                    'evidence' => "Word count: {$wordCount} words (minimum recommended: 250)",
                    'recommended_action' => 'Expand the article with first-hand travel insights, practical itinerary tips, FAQs, and high-quality photography.',
                ];
            }

            // Missing Author Bio (E-E-A-T)
            if (empty($post->author_name) || empty($post->author_bio)) {
                $issues[] = [
                    'category' => 'content_quality',
                    'check_key' => 'content_missing_author_bio',
                    'title' => "Missing Author Bio & Expertise on '{$post->title}'",
                    'description' => 'E-E-A-T (Experience, Expertise, Authoritativeness, Trustworthiness) relies on verifiable author bylines to evaluate content reliability.',
                    'severity' => 'low',
                    'verification_type' => 'automated',
                    'affected_url' => url('/blog/' . $post->slug),
                    'evidence' => "Author: '" . ($post->author_name ?: 'None') . "', Bio length: " . strlen((string)$post->author_bio),
                    'recommended_action' => 'Assign a verified author profile with a concise biography describing their desert guiding or tourism credentials.',
                ];
            }
        }

        // 2. Active Tours Missing Detailed Inclusions or Descriptions
        $thinTours = Tour::where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('full_desc')
                    ->orWhere('full_desc', '');
            })
            ->get();

        foreach ($thinTours as $tour) {
            $issues[] = [
                'category' => 'content_quality',
                'check_key' => 'content_tour_missing_full_description',
                'title' => "Tour '{$tour->name}' Missing Comprehensive Itinerary Description",
                'description' => 'Tour has no full description. Guests cannot review detailed itinerary milestones, inclusions, pickup windows, or camp amenities.',
                'severity' => 'high',
                'verification_type' => 'automated',
                'affected_url' => url('/' . $tour->slug),
                'evidence' => "Tour #{$tour->id} has empty full_desc.",
                'recommended_action' => 'Add a comprehensive description in CMS Tour Editor detailing the full desert experience.',
            ];
        }

        return $issues;
    }
}
