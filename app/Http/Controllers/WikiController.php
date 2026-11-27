<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\WikiArticle;
use App\Models\WikiGlossaryTerm;
use App\Models\WikiPillar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WikiController extends Controller
{
    /**
     * Display the Knowledge Portal directory & hub.
     */
    public function index(Request $request)
    {
        $currentLocale = app()->getLocale();
        $currentYear = date('Y');

        $pillars = WikiPillar::where('status', 'active')
            ->orderBy('priority', 'asc')
            ->withCount(['activeArticles'])
            ->get();

        $featuredArticles = WikiArticle::published()
            ->where('is_featured', true)
            ->with(['pillar', 'tours'])
            ->orderBy('priority', 'asc')
            ->take(6)
            ->get();

        $recentArticles = WikiArticle::published()
            ->with(['pillar', 'tours'])
            ->orderBy('id', 'desc')
            ->take(8)
            ->get();

        $glossaryHighlights = WikiGlossaryTerm::with('article')
            ->take(8)
            ->get();

        $pageTitle = ($currentLocale === 'ar')
            ? "موسوعة وبوابة المعرفة لسفاري دبي الصحراوي ({$currentYear}) | ديونز ديسكفري"
            : "Dubai Desert Safari & UAE Tourism Knowledge Portal ({$currentYear}) | Dunes Discovery";

        $pageDesc = ($currentLocale === 'ar')
            ? 'الموسوعة الشاملة لسفاري دبي الصحراوي: جيولوجيا الرمال الحمراء، وفيزياء التقطيع في الكثبان، وقواعد أمان الحوامل، والتراث البدوي المعتمد من دائرة السياحة.'
            : 'Comprehensive Dubai desert knowledge portal: red dunes geology, dune bashing mechanics, safety manuals, Bedouin heritage, and wildlife guides by licensed marshals.';

        $pageKeys = 'dubai desert safari wiki, dubai desert knowledge portal, lahbab red dunes geology, dune bashing physics, desert safari safety pregnant, bedouin heritage dubai';
        $canonical = localized_route('wiki.index');
        $ogImage = resolve_og_image(null, 'images/desert-safari-poster.avif');
        $pageRobots = ! empty($request->query()) ? 'noindex, follow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

        return view('wiki.index', compact(
            'pillars',
            'featuredArticles',
            'recentArticles',
            'glossaryHighlights',
            'pageTitle',
            'pageDesc',
            'pageKeys',
            'canonical',
            'ogImage',
            'pageRobots'
        ));
    }

    /**
     * Display a specific Knowledge Pillar cornerstone hub.
     */
    public function pillar(Request $request, ...$params)
    {
        $pillarSlug = (string) end($params);
        $pillar = WikiPillar::where('slug', $pillarSlug)->active()->firstOrFail();

        $articles = WikiArticle::where('pillar_id', $pillar->id)
            ->published()
            ->with(['tours', 'pillar'])
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $otherPillars = WikiPillar::where('id', '!=', $pillar->id)
            ->active()
            ->orderBy('priority', 'asc')
            ->withCount('activeArticles')
            ->get();

        $pageTitle = $pillar->name . ' | Dubai Desert Knowledge Base';
        $pageDesc = $pillar->headline ?: $pillar->description;
        $pageKeys = $pillar->slug . ', dubai desert guide, ' . $pillar->name;
        $canonical = localized_route('wiki.pillar', ['pillarSlug' => $pillar->slug]);
        $ogImage = resolve_og_image($pillar->hero_image, 'images/desert-safari-poster.avif');
        $pageRobots = ! empty($request->query()) ? 'noindex, follow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

        return view('wiki.pillar', compact(
            'pillar',
            'articles',
            'otherPillars',
            'pageTitle',
            'pageDesc',
            'pageKeys',
            'canonical',
            'ogImage',
            'pageRobots'
        ));
    }

    /**
     * Display a single in-depth Cluster Knowledge Article.
     */
    public function article(Request $request, ...$params)
    {
        $articleSlug = (string) end($params);
        $pillarSlug = (string) (count($params) >= 2 ? $params[count($params) - 2] : reset($params));
        $pillar = WikiPillar::where('slug', $pillarSlug)->active()->firstOrFail();

        $article = WikiArticle::where('pillar_id', $pillar->id)
            ->where('slug', $articleSlug)
            ->published()
            ->with(['pillar', 'tours.tiers', 'glossaryTerms'])
            ->firstOrFail();

        // Increment view count quietly (throttled per visitor to prevent write amplification on GET)
        try {
            $viewKey = 'wiki_view_'.$article->id.'_'.md5((string) $request->ip());
            if (Cache::add($viewKey, true, 1800)) {
                $article->increment('view_count');
            }
        } catch (\Throwable $e) {
            Log::debug('WikiController view_count increment failed: '.$e->getMessage());
        }

        $relatedArticles = WikiArticle::where('pillar_id', $pillar->id)
            ->where('id', '!=', $article->id)
            ->published()
            ->take(4)
            ->get();

        $pageTitle = $article->meta_title ?: ($article->title . ' | Dubai Desert Knowledge Base');
        $pageDesc = $article->meta_description ?: $article->excerpt;
        $pageKeys = $article->meta_keywords ?: 'dubai desert safari, ' . $article->title;
        $canonical = localized_route('wiki.article', [
            'pillarSlug' => $pillar->slug,
            'articleSlug' => $article->slug,
        ]);
        $ogImage = resolve_og_image($article->hero_image ?: $pillar->hero_image, 'images/desert-safari-poster.avif');
        $pageRobots = ! empty($request->query()) ? 'noindex, follow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

        // Contextual tour for booking conversion
        $contextualTour = $article->tours->first();
        if (! $contextualTour) {
            $contextualTour = Tour::where('status', 'active')
                ->where('is_bestseller', true)
                ->with('tiers')
                ->first();
        }

        return view('wiki.article', compact(
            'pillar',
            'article',
            'relatedArticles',
            'contextualTour',
            'pageTitle',
            'pageDesc',
            'pageKeys',
            'canonical',
            'ogImage',
            'pageRobots'
        ));
    }

    /**
     * A-Z Glossary of all Dubai Desert & UAE Tourism terminology.
     */
    public function glossary(Request $request)
    {
        $terms = WikiGlossaryTerm::with('article.pillar')
            ->get()
            ->sortBy(function ($item) {
                $clean = trim(strip_tags((string) $item->term));

                return mb_strtoupper(mb_substr($clean, 0, 1, 'UTF-8'), 'UTF-8');
            });

        // Group by first Unicode letter
        $groupedTerms = $terms->groupBy(function ($item) {
            $clean = trim(strip_tags((string) $item->term));
            $firstChar = mb_strtoupper(mb_substr($clean, 0, 1, 'UTF-8'), 'UTF-8');

            return preg_match('/^\p{L}$/u', $firstChar) ? $firstChar : '#';
        })->sortKeys();

        $pageTitle = 'Dubai Desert Safari Glossary & Terminology Index | Dunes Discovery';
        $pageDesc = 'Comprehensive A-Z dictionary of UAE desert safari, off-roading, dune mechanics, and Bedouin cultural terms with verified definitions.';
        $pageKeys = 'desert safari glossary, dubai desert terms, lahbab definition, dune bashing meaning, gahwa meaning';
        $canonical = localized_route('wiki.glossary');
        $ogImage = resolve_og_image(null, 'images/desert-safari-poster.avif');
        $pageRobots = ! empty($request->query()) ? 'noindex, follow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

        return view('wiki.glossary', compact(
            'groupedTerms',
            'pageTitle',
            'pageDesc',
            'pageKeys',
            'canonical',
            'ogImage',
            'pageRobots'
        ));
    }

    /**
     * Live search endpoint for wiki topics & glossary definitions.
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->input('q'));
        if (strlen($q) < 2) {
            return response()->json(['articles' => [], 'terms' => []]);
        }

        $escaped = addcslashes($q, '%_!');
        $likePattern = "%{$escaped}%";

        $articles = WikiArticle::published()
            ->whereHas('pillar', fn ($p) => $p->where('status', 'active'))
            ->where(function ($query) use ($likePattern) {
                $query->whereRaw("title LIKE ? ESCAPE '!'", [$likePattern])
                    ->orWhereRaw("excerpt LIKE ? ESCAPE '!'", [$likePattern])
                    ->orWhereRaw("slug LIKE ? ESCAPE '!'", [$likePattern]);
            })
            ->with('pillar')
            ->take(5)
            ->get()
            ->filter(fn ($a) => $a->pillar !== null)
            ->values()
            ->map(function ($a) {
                return [
                    'title' => $a->title,
                    'excerpt' => Str::limit($a->excerpt, 80),
                    'url' => localized_route('wiki.article', ['pillarSlug' => $a->pillar->slug, 'articleSlug' => $a->slug]),
                    'pillar' => $a->pillar->name,
                    'reading_time' => $a->reading_time_minutes,
                ];
            });

        $terms = WikiGlossaryTerm::where(function ($query) use ($likePattern) {
                $query->whereRaw("term LIKE ? ESCAPE '!'", [$likePattern])
                    ->orWhereRaw("definition LIKE ? ESCAPE '!'", [$likePattern]);
            })
            ->with('article.pillar')
            ->take(5)
            ->get()
            ->map(function ($t) {
                $article = $t->article;
                $hasPublicArticle = $article
                    && $article->status === 'published'
                    && $article->pillar
                    && $article->pillar->status === 'active';

                return [
                    'term' => $t->term,
                    'definition' => Str::limit($t->definition, 100),
                    'url' => $hasPublicArticle
                        ? localized_route('wiki.article', ['pillarSlug' => $article->pillar->slug, 'articleSlug' => $article->slug])
                        : localized_route('wiki.glossary'),
                ];
            });

        return response()->json([
            'articles' => $articles,
            'terms' => $terms,
        ]);
    }
}
