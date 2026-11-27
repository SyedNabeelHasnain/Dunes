<?php

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Tour;
use App\Models\WikiArticle;
use Illuminate\Support\Str;

/**
 * Provides keyword mapping matrix, duplicate/cannibalization detection,
 * internal linking suggestions, and on-page readability/width analysis.
 */
class KeywordIntelligenceService
{
    /**
     * Build complete sitewide keyword inventory, cannibalization groups, and internal link opportunities.
     */
    public function buildReport(): array
    {
        $entries = [];

        $tours = Tour::where('status', 'active')->with('seoMeta')->get();
        foreach ($tours as $tour) {
            $focus = trim((string) ($tour->seoMeta?->focus_keyword ?: ''));
            $entries[] = [
                'entity_type' => Tour::class,
                'type_label' => 'Tour',
                'id' => $tour->id,
                'title' => (string) $tour->getTranslation('name', 'en', true),
                'url' => url('/'.$tour->slug),
                'path' => '/'.$tour->slug,
                'focus_keyword' => $focus,
                'secondary_keywords' => $tour->seoMeta?->secondary_keywords ?? [],
                'search_intent' => $tour->seoMeta?->search_intent ?? 'transactional',
                'editorial_status' => $tour->seoMeta?->editorial_status ?? 'published',
                'word_count' => str_word_count(strip_tags((string) $tour->getTranslation('full_desc', 'en', true))),
                'edit_route' => route('admin.tours.edit', $tour->id),
            ];
        }

        $blogs = BlogPost::where('status', 'published')->with('seoMeta')->get();
        foreach ($blogs as $post) {
            $focus = trim((string) ($post->seoMeta?->focus_keyword ?: $post->focus_keyword ?: ''));
            $entries[] = [
                'entity_type' => BlogPost::class,
                'type_label' => 'Blog Post',
                'id' => $post->id,
                'title' => (string) $post->getTranslation('title', 'en', true),
                'url' => url('/blog/'.$post->slug),
                'path' => '/blog/'.$post->slug,
                'focus_keyword' => $focus,
                'secondary_keywords' => $post->seoMeta?->secondary_keywords ?? [],
                'search_intent' => $post->seoMeta?->search_intent ?? 'informational',
                'editorial_status' => $post->seoMeta?->editorial_status ?? 'published',
                'word_count' => str_word_count(strip_tags((string) $post->getTranslation('content', 'en', true))),
                'edit_route' => route('admin.blogs.edit', $post->id),
            ];
        }

        $wikiArticles = WikiArticle::where('status', 'published')->with(['pillar', 'seoMeta'])->get();
        foreach ($wikiArticles as $article) {
            $focus = trim((string) ($article->seoMeta?->focus_keyword ?: ''));
            $pillarSlug = $article->pillar?->slug ?? 'guide';
            $entries[] = [
                'entity_type' => WikiArticle::class,
                'type_label' => 'Wiki Article',
                'id' => $article->id,
                'title' => (string) $article->getTranslation('title', 'en', true),
                'url' => url('/wiki/'.$pillarSlug.'/'.$article->slug),
                'path' => '/wiki/'.$pillarSlug.'/'.$article->slug,
                'focus_keyword' => $focus,
                'secondary_keywords' => $article->seoMeta?->secondary_keywords ?? [],
                'search_intent' => $article->seoMeta?->search_intent ?? 'informational',
                'editorial_status' => $article->seoMeta?->editorial_status ?? 'published',
                'word_count' => str_word_count(strip_tags((string) $article->getTranslation('content', 'en', true))),
                'edit_route' => route('admin.wiki.articles.edit', $article->id),
            ];
        }

        $pages = Page::where('status', 'published')->with('seoMeta')->get();
        foreach ($pages as $page) {
            $focus = trim((string) ($page->seoMeta?->focus_keyword ?: ''));
            $entries[] = [
                'entity_type' => Page::class,
                'type_label' => 'Page',
                'id' => $page->id,
                'title' => (string) ($page->getTranslation('title', 'en', true) ?: $page->name),
                'url' => url('/'.ltrim($page->slug, '/')),
                'path' => '/'.ltrim($page->slug, '/'),
                'focus_keyword' => $focus,
                'secondary_keywords' => $page->seoMeta?->secondary_keywords ?? [],
                'search_intent' => $page->seoMeta?->search_intent ?? 'navigational',
                'editorial_status' => $page->seoMeta?->editorial_status ?? 'published',
                'word_count' => 0,
                'edit_route' => route('admin.pages.edit', $page->id),
            ];
        }

        // Detect cannibalization: duplicate normalized focus keywords across multiple URLs
        $byKeyword = [];
        foreach ($entries as $entry) {
            $kw = strtolower(trim($entry['focus_keyword']));
            if ($kw === '') {
                continue;
            }
            $byKeyword[$kw][] = $entry;
        }

        $cannibalization = [];
        foreach ($byKeyword as $kw => $group) {
            if (count($group) > 1) {
                $cannibalization[] = [
                    'keyword' => $kw,
                    'count' => count($group),
                    'items' => $group,
                    'severity' => count($group) >= 3 ? 'high' : 'medium',
                    'recommendation' => "Differentiate search intent or consolidate pages competing for '{$kw}' via canonical/301 or unique long-tail focus terms.",
                ];
            }
        }

        $unmappedCount = collect($entries)->filter(fn ($e) => $e['focus_keyword'] === '')->count();
        $linkSuggestions = $this->suggestInternalLinks($tours, $blogs);

        return [
            'entries' => $entries,
            'cannibalization' => $cannibalization,
            'unmapped_count' => $unmappedCount,
            'mapped_count' => count($entries) - $unmappedCount,
            'link_suggestions' => $linkSuggestions,
        ];
    }

    /**
     * Suggest missing internal links from blog guides to high-value commercial tours.
     */
    public function suggestInternalLinks($tours, $blogs): array
    {
        $suggestions = [];
        foreach ($blogs as $post) {
            $html = strtolower((string) $post->getTranslation('content', 'en', true));
            if ($html === '') {
                continue;
            }
            $blogTitle = (string) $post->getTranslation('title', 'en', true);

            foreach ($tours as $tour) {
                $tourSlug = trim((string) $tour->slug);
                $tourName = (string) $tour->getTranslation('name', 'en', true);
                if ($tourSlug === '' || $tourName === '') {
                    continue;
                }
                if (str_contains($html, '/'.$tourSlug)) {
                    continue;
                }
                $anchorCandidate = trim((string) ($tour->seoMeta?->focus_keyword ?: $tourName));
                if ($anchorCandidate !== '' && str_contains($html, strtolower($anchorCandidate))) {
                    $suggestions[] = [
                        'source_type' => 'Blog Post',
                        'source_title' => $blogTitle,
                        'source_url' => '/blog/'.$post->slug,
                        'edit_url' => route('admin.blogs.edit', $post->id),
                        'anchor_text' => $anchorCandidate,
                        'target_title' => $tourName,
                        'target_url' => '/'.$tourSlug,
                    ];
                }
            }
        }

        return array_slice($suggestions, 0, 50);
    }

    /**
     * Analyze on-page SEO & readability metrics for editor live scorecard.
     */
    public function analyzeOnPage(string $title, string $description, string $bodyHtml, ?string $focusKeyword = null): array
    {
        $plainBody = trim(preg_replace('/\s+/', ' ', strip_tags($bodyHtml)) ?? '');
        $wordCount = $plainBody === '' ? 0 : str_word_count($plainBody);
        $titleLen = mb_strlen(trim($title));
        $descLen = mb_strlen(trim($description));
        $estPixelWidth = (int) round($titleLen * 9.2);

        $checks = [];
        $score = 100;

        if ($titleLen < 30 || $titleLen > 65) {
            $score -= 12;
            $checks[] = ['status' => 'warn', 'label' => "Title is {$titleLen} chars (~{$estPixelWidth}px); ideal is 30–60 chars (<= 580px)."];
        } else {
            $checks[] = ['status' => 'pass', 'label' => "Title length optimal ({$titleLen} chars, ~{$estPixelWidth}px)."];
        }

        if ($descLen < 70 || $descLen > 165) {
            $score -= 12;
            $checks[] = ['status' => 'warn', 'label' => "Meta description is {$descLen} chars; ideal is 110–160 chars."];
        } else {
            $checks[] = ['status' => 'pass', 'label' => "Meta description length optimal ({$descLen} chars)."];
        }

        if ($wordCount < 300) {
            $score -= 15;
            $checks[] = ['status' => 'warn', 'label' => "Body copy has {$wordCount} words; >= 300 words recommended for rich topic authority."];
        } else {
            $checks[] = ['status' => 'pass', 'label' => "Strong topical depth ({$wordCount} words)."];
        }

        if ($focusKeyword !== null && trim($focusKeyword) !== '') {
            $kw = strtolower(trim($focusKeyword));
            if (str_contains(strtolower($title), $kw)) {
                $checks[] = ['status' => 'pass', 'label' => "Focus keyword '{$focusKeyword}' appears in SEO title."];
            } else {
                $score -= 10;
                $checks[] = ['status' => 'warn', 'label' => "Include focus keyword '{$focusKeyword}' in title."];
            }

            if (str_contains(strtolower($plainBody), $kw)) {
                $checks[] = ['status' => 'pass', 'label' => "Focus keyword '{$focusKeyword}' used in body copy."];
            } else {
                $score -= 10;
                $checks[] = ['status' => 'warn', 'label' => "Focus keyword '{$focusKeyword}' missing from body text."];
            }
        } else {
            $score -= 8;
            $checks[] = ['status' => 'info', 'label' => 'Assign a unique Focus Keyword to prevent cannibalization.'];
        }

        // Editor-level WCAG & Semantic Accessibility Checks on body HTML
        $a11yChecks = [];
        if ($bodyHtml !== '') {
            preg_match_all('/<img\b[^>]*>/i', $bodyHtml, $imgMatches);
            $missingAlt = 0;
            foreach ($imgMatches[0] ?? [] as $imgTag) {
                if (! preg_match('/\balt\s*=\s*["\'][^"\']+["\']/i', $imgTag)) {
                    $missingAlt++;
                }
            }
            if ($missingAlt > 0) {
                $score -= 6;
                $a11yChecks[] = ['status' => 'warn', 'label' => "{$missingAlt} embedded image(s) missing descriptive alt text."];
            } else {
                $a11yChecks[] = ['status' => 'pass', 'label' => 'All embedded body images include alt attributes (or none embedded).'];
            }

            if (preg_match_all('/<h([1-6])\b/i', $bodyHtml, $hMatches)) {
                $levels = array_map('intval', $hMatches[1]);
                $skipped = false;
                $prev = 1;
                foreach ($levels as $lvl) {
                    if ($lvl === 1) {
                        $a11yChecks[] = ['status' => 'warn', 'label' => 'Avoid <h1> inside body HTML (page already renders one primary <h1>); use <h2>–<h4>.'];
                        $score -= 4;
                        $skipped = true;
                        break;
                    }
                    if (($lvl - $prev) > 1) {
                        $a11yChecks[] = ['status' => 'warn', 'label' => "Skipped heading level in body (<h{$prev}> to <h{$lvl}>)."];
                        $score -= 4;
                        $skipped = true;
                        break;
                    }
                    $prev = $lvl;
                }
                if (! $skipped) {
                    $a11yChecks[] = ['status' => 'pass', 'label' => 'Body heading hierarchy is sequential (H2 -> H3 -> H4).'];
                }
            }

            preg_match_all('/<a\b[^>]*>(.*?)<\/a>/is', $bodyHtml, $anchorMatches);
            $genericAnchors = 0;
            foreach ($anchorMatches[1] ?? [] as $aText) {
                $cleanA = strtolower(trim(strip_tags($aText)));
                if (in_array($cleanA, ['click here', 'read more', 'learn more', 'here', 'link', 'more'], true)) {
                    $genericAnchors++;
                }
            }
            if ($genericAnchors > 0) {
                $score -= 4;
                $a11yChecks[] = ['status' => 'warn', 'label' => "{$genericAnchors} generic link anchor(s) ('click here' / 'read more'). Use descriptive anchor text."];
            } else {
                $a11yChecks[] = ['status' => 'pass', 'label' => 'No non-descriptive generic anchor text detected.'];
            }
        }

        return [
            'score' => max(0, $score),
            'word_count' => $wordCount,
            'read_minutes' => max(1, (int) ceil($wordCount / 220)),
            'title_chars' => $titleLen,
            'title_pixels' => $estPixelWidth,
            'description_chars' => $descLen,
            'checks' => $checks,
            'accessibility_checks' => $a11yChecks,
        ];
    }

    /**
     * Suggest contextual internal links for a single item's body copy directly inside the editor panel.
     */
    public function suggestLinksForItem(string $bodyHtml, ?string $excludeSlug = null): array
    {
        $lower = strtolower($bodyHtml);
        if (trim($lower) === '') {
            return [];
        }

        $suggestions = [];
        try {
            $tours = Tour::where('status', 'active')->with('seoMeta')->orderBy('priority')->limit(30)->get();
            foreach ($tours as $tour) {
                if ($excludeSlug && $tour->slug === $excludeSlug) {
                    continue;
                }
                if (str_contains($lower, '/'.$tour->slug)) {
                    continue;
                }
                $name = (string) $tour->getTranslation('name', 'en', true);
                $kw = trim((string) ($tour->seoMeta?->focus_keyword ?: $name));
                if ($kw !== '' && (str_contains($lower, strtolower($kw)) || str_contains($lower, strtolower($name)))) {
                    $suggestions[] = [
                        'anchor' => $kw,
                        'target_title' => $name,
                        'target_url' => '/'.$tour->slug,
                    ];
                }
                if (count($suggestions) >= 6) {
                    break;
                }
            }
        } catch (\Throwable) {
        }

        return $suggestions;
    }
}
