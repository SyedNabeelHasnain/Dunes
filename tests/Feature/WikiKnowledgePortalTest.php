<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\User;
use App\Models\WikiArticle;
use App\Models\WikiGlossaryTerm;
use App\Models\WikiPillar;
use Database\Seeders\WikiKnowledgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class WikiKnowledgePortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->seed(WikiKnowledgeSeeder::class);
    }

    /**
     * Test Public Wiki Hub / Directory renders correctly with all pillars and cornerstone guides.
     */
    public function test_wiki_hub_renders_successfully(): void
    {
        $response = $this->get('/wiki');

        $response->assertStatus(200);
        $response->assertViewIs('wiki.index');
        $response->assertSee('Dubai Desert & UAE Tourism', false);
        $response->assertSee('Verified Destination Knowledge Base');
        $response->assertSee('Desert Geography &amp; Terrains', false);
        $response->assertSee('Desert Adventures &amp; Vehicles', false);
        $response->assertSee('CollectionPage');
        $response->assertSee('BreadcrumbList');
    }

    /**
     * Test Pillar landing page renders cornerstone briefing and cluster guide listings.
     */
    public function test_wiki_pillar_page_renders_successfully(): void
    {
        $pillar = WikiPillar::where('slug', 'geography')->firstOrFail();

        $response = $this->get('/wiki/' . $pillar->slug);

        $response->assertStatus(200);
        $response->assertViewIs('wiki.pillar');
        $response->assertSee($pillar->name);
        $response->assertSee('CollectionPage');
        $response->assertSee('Verified Guides');
    }

    /**
     * Test Article detail view displays TOC, Marshal verification badge, contextual tour card, and TechArticle JSON-LD.
     */
    public function test_wiki_article_page_renders_with_schema_and_tour_widget(): void
    {
        $article = WikiArticle::with('pillar')->firstOrFail();
        $pillar = $article->pillar;

        $response = $this->get('/wiki/' . $pillar->slug . '/' . $article->slug);

        $response->assertStatus(200);
        $response->assertViewIs('wiki.article');
        $response->assertSee($article->title);
        $response->assertSee('TechArticle');
        $response->assertSee('DET Certified Fact-Check');
        $response->assertSee('Ask our Safari Marshals');
    }

    /**
     * Test A-Z Desert Safari Glossary renders dictionary terms and alphabet anchors.
     */
    public function test_wiki_glossary_renders_successfully(): void
    {
        $response = $this->get('/wiki/glossary');

        $response->assertStatus(200);
        $response->assertViewIs('wiki.glossary');
        $response->assertSee('A-Z Glossary');
        $response->assertSee('Terminology Index');
        $response->assertSee('DefinedTermSet');
        $response->assertSee('Dune Bashing');
        $response->assertSee('Slipface');
    }

    /**
     * Test Wiki Live Search API returns JSON with matching results.
     */
    public function test_wiki_live_search_endpoint_returns_json(): void
    {
        $response = $this->getJson('/wiki/search?q=dune');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'articles' => [
                '*' => [
                    'title',
                    'pillar',
                    'excerpt',
                    'url',
                    'reading_time',
                ],
            ],
            'terms',
        ]);
    }

    /**
     * Test Multi-language localized Wiki route (/ar/wiki).
     */
    public function test_localized_wiki_route_renders_successfully(): void
    {
        $response = $this->get('/ar/wiki');

        $response->assertStatus(200);
        $response->assertViewIs('wiki.index');
    }

    /**
     * Test Dedicated XML Sitemap for Wiki (/sitemap-wiki.xml).
     */
    public function test_wiki_sitemap_renders_valid_xml(): void
    {
        $response = $this->get('/sitemap-wiki.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=utf-8');
        $content = $response->getContent();

        $this->assertStringContainsString('<urlset', $content);
        $this->assertStringContainsString('/wiki', $content);
        $this->assertStringContainsString('/wiki/glossary', $content);
        $this->assertStringContainsString('/wiki/geography', $content);
    }

    /**
     * Test LLM markdown files include Wiki / Knowledge Portal references.
     */
    public function test_llms_endpoints_include_knowledge_portal(): void
    {
        Cache::forget('site_llms_txt');
        Cache::forget('site_llms_full_txt');

        $indexResponse = $this->get('/llms.txt');
        $indexResponse->assertStatus(200);
        $this->assertStringContainsString('Knowledge Portal & Desert Safari Wiki', $indexResponse->getContent());
        $this->assertStringContainsString('/wiki', $indexResponse->getContent());

        $fullResponse = $this->get('/llms-full.txt');
        $fullResponse->assertStatus(200);
        $this->assertStringContainsString('SECTION 8: KNOWLEDGE PORTAL', $fullResponse->getContent());
    }

    /**
     * Test Admin Wiki routes require authentication.
     */
    public function test_admin_wiki_requires_auth(): void
    {
        $response = $this->get('/admin/wiki');
        $response->assertRedirect('/login');
    }

    /**
     * Test Admin can list articles, create a new article, update, and delete.
     */
    public function test_admin_can_manage_wiki_articles(): void
    {
        $admin = User::factory()->create();
        $pillar = WikiPillar::firstOrFail();

        // 1. View Articles Index
        $indexRes = $this->actingAs($admin)->get('/admin/wiki');
        $indexRes->assertStatus(200);
        $indexRes->assertViewIs('admin.wiki.index');

        // 2. View Create Form
        $createRes = $this->actingAs($admin)->get('/admin/wiki/articles/create');
        $createRes->assertStatus(200);
        $createRes->assertViewIs('admin.wiki.create');

        // 3. Store new article
        $storeData = [
            'pillar_id' => $pillar->id,
            'title' => 'Test Dune Recovery Protocol',
            'slug' => 'test-dune-recovery-protocol',
            'excerpt' => 'Comprehensive technical instructions on kinetic rope recovery.',
            'content' => "## Introduction\nTesting recovery techniques.\n\n## Equipment\nKinetic ropes and soft shackles.",
            'reviewed_by_name' => 'Marshal Rashid',
            'reviewed_by_title' => 'Lead Marshal Tariq',
            'status' => 'published',
            'reading_time_minutes' => 8,
        ];

        $storeRes = $this->actingAs($admin)->post('/admin/wiki/articles', $storeData);
        $storeRes->assertRedirect(route('admin.wiki.index'));

        $this->assertDatabaseHas('wiki_articles', [
            'slug' => 'test-dune-recovery-protocol',
        ]);

        $article = WikiArticle::where('slug', 'test-dune-recovery-protocol')->firstOrFail();
        $this->assertEquals('Test Dune Recovery Protocol', $article->title);

        // 4. Update Article
        $updateData = array_merge($storeData, [
            'title' => 'Updated Dune Recovery Protocol',
            'status' => 'draft',
        ]);

        $updateRes = $this->actingAs($admin)->put('/admin/wiki/articles/' . $article->id, $updateData);
        $updateRes->assertRedirect(route('admin.wiki.index'));

        $this->assertEquals('Updated Dune Recovery Protocol', $article->fresh()->title);
        $this->assertEquals('draft', $article->fresh()->status);

        // 5. Delete Article
        $deleteRes = $this->actingAs($admin)->delete('/admin/wiki/articles/' . $article->id);
        $deleteRes->assertRedirect(route('admin.wiki.index'));

        $this->assertDatabaseMissing('wiki_articles', [
            'id' => $article->id,
        ]);
    }

    /**
     * Test Admin can manage pillars and glossary.
     */
    public function test_admin_can_manage_pillars_and_glossary(): void
    {
        $admin = User::factory()->create();
        $pillar = WikiPillar::firstOrFail();

        // 1. View Pillars Index
        $pillarRes = $this->actingAs($admin)->get('/admin/wiki/pillars');
        $pillarRes->assertStatus(200);
        $pillarRes->assertViewIs('admin.wiki.pillars');

        // 2. Update Pillar
        $updatePillarRes = $this->actingAs($admin)->put('/admin/wiki/pillars/' . $pillar->id, [
            'name' => 'Updated Geography Title',
            'slug' => $pillar->slug,
            'headline' => 'Desert Sand Formations',
            'description' => 'Updated description for geography pillar.',
            'icon' => 'bi-compass',
            'priority' => 1,
            'status' => 'active',
        ]);
        $updatePillarRes->assertRedirect(route('admin.wiki.pillars'));
        $this->assertEquals('Updated Geography Title', $pillar->fresh()->name);

        // 3. View Glossary Index
        $glossaryRes = $this->actingAs($admin)->get('/admin/wiki/glossary');
        $glossaryRes->assertStatus(200);
        $glossaryRes->assertViewIs('admin.wiki.glossary');

        // 4. Store new glossary term
        $storeGlossaryRes = $this->actingAs($admin)->post('/admin/wiki/glossary', [
            'term' => 'Wadi',
            'term_key' => 'wadi-expedition',
            'definition' => 'A valley, ravine, or channel that is dry except in the rainy season.',
        ]);
        $storeGlossaryRes->assertRedirect(route('admin.wiki.glossary'));
        $this->assertDatabaseHas('wiki_glossary_terms', [
            'term_key' => 'wadi-expedition',
        ]);
    }
}
