<?php

namespace App\Services\Audit\Checkers;

use App\Models\BlogPost;
use App\Models\SeoMetadata;
use App\Models\Tour;
use App\Models\WikiArticle;
use App\Models\WikiPillar;
use Illuminate\Support\Facades\Log;

class ContentQualityChecker implements AuditCheckerInterface
{
    public function run(array $context = []): array
    {
        $issues = [];

        // 1. Thin Blog Posts (< 250 words), Duplicate Body Fingerprints & Body Heading Hierarchy
        $bodyFingerprints = [];
        BlogPost::where('status', 'published')->chunkById(100, function ($posts) use (&$issues, &$bodyFingerprints) {
            foreach ($posts as $post) {
                $rawHtml = (string) $post->content;
                $text = trim((string) preg_replace('/\s+/u', ' ', strip_tags($rawHtml)));
                $wordCount = $text === '' ? 0 : count(preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: []);
                $postUrl = url('/blog/' . $post->slug);

                if ($wordCount < 250) {
                    $issues[] = [
                        'category' => 'content_quality',
                        'check_key' => 'content_thin_blog_post',
                        'title' => "Thin Content Detected in Blog Post '{$post->title}' ({$wordCount} words)",
                        'description' => "Google Helpful Content System and E-E-A-T guidelines deprecate thin, uninformative articles. Comprehensive travel guides typically require at least 500-1200 words.",
                        'severity' => 'medium',
                        'verification_type' => 'automated',
                        'affected_url' => $postUrl,
                        'evidence' => "Word count: {$wordCount} words (minimum recommended: 250)",
                        'recommended_action' => 'Expand the article with first-hand travel insights, practical itinerary tips, FAQs, and high-quality photography.',
                    ];
                }

                if ($wordCount >= 120) {
                    $fp = hash('sha256', mb_strtolower(mb_substr($text, 0, 400, 'UTF-8'), 'UTF-8'));
                    if (isset($bodyFingerprints[$fp])) {
                        $issues[] = [
                            'category' => 'content_quality',
                            'check_key' => 'content_near_duplicate_body',
                            'title' => "Near-Duplicate Article Body Detected on '{$post->title}'",
                            'description' => 'Substantial opening body text duplicates another published URL, risking duplicate content filtering.',
                            'severity' => 'medium',
                            'verification_type' => 'automated',
                            'affected_url' => $postUrl,
                            'evidence' => 'Matches opening body fingerprint of: '.$bodyFingerprints[$fp],
                            'recommended_action' => 'Rewrite or consolidate overlapping content and apply canonical or 301 redirect if redundant.',
                        ];
                    } else {
                        $bodyFingerprints[$fp] = $postUrl;
                    }
                }

                if (preg_match('/<h1\b/i', $rawHtml)) {
                    $issues[] = [
                        'category' => 'content_quality',
                        'check_key' => 'content_embedded_h1_in_body',
                        'title' => "Embedded <h1> Inside Blog Body on '{$post->title}'",
                        'description' => 'Blog templates already render the primary page <h1>; embedding another <h1> inside article HTML breaks document outline semantics.',
                        'severity' => 'low',
                        'verification_type' => 'automated',
                        'affected_url' => $postUrl,
                        'evidence' => 'Detected <h1> tag inside BlogPost::content.',
                        'recommended_action' => 'Demote body headings to <h2> and <h3> inside the Blog Editor.',
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
                        'affected_url' => $postUrl,
                        'evidence' => "Author: '" . ($post->author_name ?: 'None') . "', Bio length: " . mb_strlen((string) $post->author_bio, 'UTF-8'),
                        'recommended_action' => 'Assign a verified author profile with a concise biography describing their desert guiding or tourism credentials.',
                    ];
                }
            }
        });

        // 2. Wiki Knowledge Articles: Depth & Reviewer Verification
        try {
            WikiArticle::where('status', 'published')->with('pillar:id,slug')->chunkById(100, function ($wikiArticles) use (&$issues) {
                foreach ($wikiArticles as $article) {
                    $wUrl = url('/wiki/'.($article->pillar?->slug ?? 'guide').'/'.$article->slug);
                    $wText = trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) $article->content)));
                    $wWords = $wText === '' ? 0 : count(preg_split('/\s+/u', $wText, -1, PREG_SPLIT_NO_EMPTY) ?: []);
                    if ($wWords < 200) {
                        $issues[] = [
                            'category' => 'content_quality',
                            'check_key' => 'content_thin_wiki_article',
                            'title' => "Thin Knowledge Wiki Article '{$article->title}' ({$wWords} words)",
                            'description' => 'Reference wiki guides should deliver deep, authoritative answers (>= 200 words).',
                            'severity' => 'medium',
                            'verification_type' => 'automated',
                            'affected_url' => $wUrl,
                            'evidence' => "Word count: {$wWords}",
                            'recommended_action' => 'Expand with verified operational specifications, safety facts, and cross-links.',
                        ];
                    }
                    if (empty($article->reviewed_by_name)) {
                        $issues[] = [
                            'category' => 'content_quality',
                            'check_key' => 'content_wiki_missing_reviewer',
                            'title' => "Wiki Article '{$article->title}' Missing Subject-Matter Reviewer",
                            'description' => 'Assigning a named reviewer strengthens E-E-A-T and AI citation authority.',
                            'severity' => 'low',
                            'verification_type' => 'automated',
                            'affected_url' => $wUrl,
                            'evidence' => 'reviewed_by_name is empty.',
                            'recommended_action' => 'Set reviewer credentials in Wiki Article Editor.',
                        ];
                    }
                }
            });
        } catch (\Throwable $e) {
            Log::warning('ContentQualityChecker wiki inspection failed: '.$e->getMessage());
        }

        // 3. Stale Content Governance Check (honoring SeoMetadata.stale_after_days)
        try {
            $staleConfigs = SeoMetadata::whereNotNull('stale_after_days')
                ->where('stale_after_days', '>', 0)
                ->with('entity')
                ->limit(50)
                ->get();

            foreach ($staleConfigs as $meta) {
                $entity = $meta->entity;
                if (! $entity) {
                    continue;
                }
                $lastTouch = $meta->reviewed_at ?? $entity->updated_at;
                if ($lastTouch && $lastTouch->lt(now()->subDays((int) $meta->stale_after_days))) {
                    $daysOld = (int) $lastTouch->diffInDays(now());
                    $label = (string) ($entity->title ?? $entity->name ?? (class_basename($entity).' #'.$entity->getKey()));
                    $affectedUrl = match (true) {
                        $entity instanceof BlogPost => url('/blog/'.ltrim((string) $entity->slug, '/')),
                        $entity instanceof WikiArticle => url('/wiki/'.($entity->pillar?->slug ?? 'guide').'/'.ltrim((string) $entity->slug, '/')),
                        $entity instanceof WikiPillar => url('/wiki/'.ltrim((string) $entity->slug, '/')),
                        isset($entity->slug) => url('/'.ltrim((string) $entity->slug, '/')),
                        default => url('/'),
                    };
                    $issues[] = [
                        'category' => 'content_quality',
                        'check_key' => 'content_stale_review_overdue',
                        'title' => "Stale Content Review Overdue on '{$label}' ({$daysOld}d old)",
                        'description' => "Configured freshness threshold ({$meta->stale_after_days} days) has been exceeded without editorial verification.",
                        'severity' => 'low',
                        'verification_type' => 'automated',
                        'affected_url' => $affectedUrl,
                        'evidence' => "Last verified/updated: {$lastTouch->toDateString()} ({$daysOld} days ago; threshold: {$meta->stale_after_days}d).",
                        'recommended_action' => 'Review seasonal pricing, schedules, and facts, then save & approve in the SEO & Editorial panel.',
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('ContentQualityChecker stale review check failed: '.$e->getMessage());
        }

        // 4. Active Tours Missing Detailed Inclusions or Descriptions
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
