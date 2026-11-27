<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Tour;
use App\Models\WikiArticle;
use App\Models\WikiGlossaryTerm;
use App\Models\WikiPillar;
use App\Traits\NormalizesLocalizedInputs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdminWikiController extends Controller
{
    use NormalizesLocalizedInputs;

    protected function uniqueSlug(string $modelClass, string $column, string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base) ?: 'item-'.time();
        $candidate = $slug;
        $counter = 2;

        while ($modelClass::where($column, $candidate)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $candidate = $slug.'-'.$counter++;
        }

        return $candidate;
    }

    protected function clearWikiCaches(): void
    {
        Cache::forget('sitemap_wiki_xml');
        Cache::forget('sitemap_images_xml');
        Cache::forget('site_wiki_hub_pillars');
        Cache::forget('site_wiki_glossary_all');
        \App\Http\Controllers\SitemapController::flushAllSitemapCaches();
    }

    /**
     * Display all articles, pillars, and glossary counts.
     */
    public function index(Request $request)
    {
        $pillarId = $request->input('pillar_id');
        $status = $request->input('status');
        $search = $request->input('search');

        $query = WikiArticle::with('pillar')->orderBy('priority', 'asc')->orderBy('id', 'desc');

        if ($pillarId) {
            $query->where('pillar_id', $pillarId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $articles = $query->paginate(15)->withQueryString();
        $pillars = WikiPillar::orderBy('priority', 'asc')->get();

        $stats = [
            'total_articles' => WikiArticle::count(),
            'published_articles' => WikiArticle::where('status', 'published')->count(),
            'total_views' => WikiArticle::sum('view_count'),
            'total_pillars' => WikiPillar::count(),
            'total_glossary' => WikiGlossaryTerm::count(),
        ];

        return view('admin.wiki.index', compact('articles', 'pillars', 'stats'));
    }

    /**
     * Manage Pillars.
     */
    public function pillars()
    {
        $pillars = WikiPillar::withCount('articles')->orderBy('priority', 'asc')->get();
        return view('admin.wiki.pillars', compact('pillars'));
    }

    public function storePillar(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'nullable|string|max:100|unique:wiki_pillars,slug',
            'headline' => 'nullable',
            'description' => 'nullable',
            'icon' => 'nullable|string|max:50',
            'priority' => 'nullable|integer',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $this->normalizeLocalizedData(
            $request->except(['hero_image']),
            ['name', 'headline', 'description']
        );

        $slugSource = $request->filled('slug') ? (string) $request->input('slug') : $this->extractSlugSource($request->name, 'pillar');
        $data['slug'] = $this->uniqueSlug(WikiPillar::class, 'slug', $slugSource);
        $data['priority'] = (int) $request->input('priority', 0);
        $data['status'] = $request->input('status', 'active');
        $data['icon'] = $request->input('icon', 'bi-compass');

        WikiPillar::create($data);
        $this->clearWikiCaches();

        return redirect()->route('admin.wiki.pillars')->with('success', 'Knowledge Pillar created successfully.');
    }

    public function updatePillar(Request $request, $id)
    {
        $pillar = WikiPillar::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'slug' => 'nullable|string|max:100|unique:wiki_pillars,slug,' . $pillar->id,
            'headline' => 'nullable',
            'description' => 'nullable',
            'icon' => 'nullable|string|max:50',
            'priority' => 'nullable|integer',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $this->normalizeLocalizedData(
            $request->except(['hero_image']),
            ['name', 'headline', 'description'],
            $pillar
        );

        if ($request->filled('slug')) {
            $data['slug'] = $this->uniqueSlug(WikiPillar::class, 'slug', (string) $request->input('slug'), $pillar->id);
        }
        $data['priority'] = (int) $request->input('priority', $pillar->priority);
        $data['status'] = $request->input('status', 'active');
        $data['icon'] = $request->input('icon', $pillar->icon ?: 'bi-compass');

        $pillar->update($data);
        $this->clearWikiCaches();

        return redirect()->route('admin.wiki.pillars')->with('success', 'Knowledge Pillar updated successfully.');
    }

    public function deletePillar($id)
    {
        $pillar = WikiPillar::withCount('articles')->findOrFail($id);
        if ($pillar->articles_count > 0) {
            return back()->with('error', 'Cannot delete pillar containing active articles. Please reassign or delete articles first.');
        }

        $pillar->delete();
        $this->clearWikiCaches();
        return redirect()->route('admin.wiki.pillars')->with('success', 'Pillar deleted successfully.');
    }

    /**
     * Show create article form.
     */
    public function createArticle()
    {
        $pillars = WikiPillar::where('status', 'active')->orderBy('priority', 'asc')->get();
        $tours = Tour::where('status', 'active')->orderBy('priority', 'asc')->get();
        $languages = Language::getActive();

        return view('admin.wiki.create', compact('pillars', 'tours', 'languages'));
    }

    /**
     * Store new knowledge article.
     */
    public function storeArticle(Request $request)
    {
        $request->validate([
            'pillar_id' => 'required|exists:wiki_pillars,id',
            'title' => 'required',
            'slug' => 'nullable|string|max:120|unique:wiki_articles,slug',
            'excerpt' => 'required',
            'content' => 'required',
            'status' => 'required|in:draft,published',
            'reading_time_minutes' => 'nullable|integer',
            'priority' => 'nullable|integer',
            'reviewed_by_name' => 'nullable|string|max:100',
            'reviewed_by_title' => 'nullable|string|max:150',
            'hero_image' => 'nullable|string|max:500',
            'tours' => 'nullable|array',
            'tours.*' => 'integer|exists:tours,id',
        ]);

        $data = $this->normalizeLocalizedData(
            $request->except(['tours', 'tours_submitted', 'hero_image']),
            ['title', 'subtitle', 'excerpt', 'content', 'meta_title', 'meta_description', 'meta_keywords']
        );

        if ($request->filled('hero_image')) {
            $data['hero_image'] = trim((string) $request->input('hero_image'));
        }

        $slugSource = $request->filled('slug') ? (string) $request->input('slug') : $this->extractSlugSource($request->title, 'article');
        $data['slug'] = $this->uniqueSlug(WikiArticle::class, 'slug', $slugSource);
        $data['pillar_id'] = (int) $request->input('pillar_id');
        $data['status'] = $request->input('status', 'published');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['priority'] = (int) $request->input('priority', 0);
        $data['reading_time_minutes'] = (int) $request->input('reading_time_minutes', 5);
        $data['reviewed_by_name'] = $request->input('reviewed_by_name', 'Malik Awan');
        $data['reviewed_by_title'] = $request->input('reviewed_by_title', 'Lead Safari Marshal & Head of Operations (DET #1430583)');
        $data['last_reviewed_at'] = now();

        $article = WikiArticle::create($data);

        if ($request->has('tours')) {
            $article->tours()->sync($request->input('tours', []));
        }

        $this->clearWikiCaches();

        return redirect()->route('admin.wiki.index')->with('success', 'Knowledge Article published successfully.');
    }

    /**
     * Show edit article form.
     */
    public function editArticle($id)
    {
        $article = WikiArticle::with(['tours', 'pillar'])->findOrFail($id);
        $pillars = WikiPillar::where('status', 'active')->orderBy('priority', 'asc')->get();
        $tours = Tour::where('status', 'active')->orderBy('priority', 'asc')->get();
        $languages = Language::getActive();

        return view('admin.wiki.edit', compact('article', 'pillars', 'tours', 'languages'));
    }

    /**
     * Update knowledge article.
     */
    public function updateArticle(Request $request, $id)
    {
        $article = WikiArticle::findOrFail($id);

        $request->validate([
            'pillar_id' => 'required|exists:wiki_pillars,id',
            'title' => 'required',
            'slug' => 'nullable|string|max:120|unique:wiki_articles,slug,' . $article->id,
            'excerpt' => 'required',
            'content' => 'required',
            'status' => 'required|in:draft,published',
            'reading_time_minutes' => 'nullable|integer',
            'priority' => 'nullable|integer',
            'reviewed_by_name' => 'nullable|string|max:100',
            'reviewed_by_title' => 'nullable|string|max:150',
            'hero_image' => 'nullable|string|max:500',
            'tours' => 'nullable|array',
            'tours.*' => 'integer|exists:tours,id',
        ]);

        $data = $this->normalizeLocalizedData(
            $request->except(['tours', 'tours_submitted', 'hero_image']),
            ['title', 'subtitle', 'excerpt', 'content', 'meta_title', 'meta_description', 'meta_keywords'],
            $article
        );

        if ($request->has('hero_image')) {
            $data['hero_image'] = trim((string) $request->input('hero_image')) ?: null;
        }

        if ($request->filled('slug')) {
            $data['slug'] = $this->uniqueSlug(WikiArticle::class, 'slug', (string) $request->input('slug'), $article->id);
        }
        $data['pillar_id'] = (int) $request->input('pillar_id');
        $data['status'] = $request->input('status', 'published');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['priority'] = (int) $request->input('priority', 0);
        $data['reading_time_minutes'] = (int) $request->input('reading_time_minutes', 5);
        $data['reviewed_by_name'] = $request->input('reviewed_by_name', $article->reviewed_by_name);
        $data['reviewed_by_title'] = $request->input('reviewed_by_title', $article->reviewed_by_title);
        
        if ($request->boolean('verify_now')) {
            $data['last_reviewed_at'] = now();
        }

        $article->update($data);

        try {
            if (method_exists($article, 'recordRevision')) {
                $article->refresh();
                $article->recordRevision('Updated wiki article via CMS editor');
            }
        } catch (\Throwable) {
        }

        if ($request->has('tours') || $request->has('tours_submitted')) {
            $article->tours()->sync($request->input('tours', []));
        }

        $this->clearWikiCaches();

        return redirect()->route('admin.wiki.index')->with('success', 'Knowledge Article updated successfully.');
    }

    /**
     * Delete article.
     */
    public function deleteArticle($id)
    {
        $article = WikiArticle::findOrFail($id);
        $article->delete();
        $this->clearWikiCaches();

        return redirect()->route('admin.wiki.index')->with('success', 'Article deleted successfully.');
    }

    /**
     * Glossary management.
     */
    public function glossary()
    {
        $terms = WikiGlossaryTerm::with('article.pillar')->orderBy('term_key', 'asc')->paginate(25);
        $articles = WikiArticle::published()->orderBy('title', 'asc')->get();

        return view('admin.wiki.glossary', compact('terms', 'articles'));
    }

    public function storeGlossaryTerm(Request $request)
    {
        $request->validate([
            'term' => 'required',
            'term_key' => 'nullable|string|max:80|unique:wiki_glossary_terms,term_key',
            'definition' => 'required',
            'wiki_article_id' => 'nullable|exists:wiki_articles,id',
        ]);

        $data = $this->normalizeLocalizedData(
            $request->all(),
            ['term', 'definition']
        );

        $termKeySource = $request->filled('term_key') ? (string) $request->input('term_key') : $this->extractSlugSource($request->term, 'term');
        $data['term_key'] = $this->uniqueSlug(WikiGlossaryTerm::class, 'term_key', $termKeySource);
        $data['wiki_article_id'] = $request->filled('wiki_article_id') ? (int) $request->input('wiki_article_id') : null;

        WikiGlossaryTerm::create($data);
        $this->clearWikiCaches();

        return redirect()->route('admin.wiki.glossary')->with('success', 'Glossary Term created successfully.');
    }

    public function updateGlossaryTerm(Request $request, $id)
    {
        $term = WikiGlossaryTerm::findOrFail($id);

        $request->validate([
            'term' => 'required',
            'term_key' => 'nullable|string|max:80|unique:wiki_glossary_terms,term_key,' . $term->id,
            'definition' => 'required',
            'wiki_article_id' => 'nullable|exists:wiki_articles,id',
        ]);

        $data = $this->normalizeLocalizedData(
            $request->all(),
            ['term', 'definition'],
            $term
        );

        if ($request->filled('term_key')) {
            $data['term_key'] = $this->uniqueSlug(WikiGlossaryTerm::class, 'term_key', (string) $request->input('term_key'), $term->id);
        }
        $data['wiki_article_id'] = $request->filled('wiki_article_id') ? (int) $request->input('wiki_article_id') : null;

        $term->update($data);
        $this->clearWikiCaches();

        return redirect()->route('admin.wiki.glossary')->with('success', 'Glossary Term updated successfully.');
    }

    public function deleteGlossaryTerm($id)
    {
        $term = WikiGlossaryTerm::findOrFail($id);
        $term->delete();
        $this->clearWikiCaches();

        return redirect()->route('admin.wiki.glossary')->with('success', 'Glossary Term deleted successfully.');
    }
}
