<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Setting;
use App\Models\Tour;
use App\Models\User;
use App\Services\Localization\LocaleManager;
use App\Services\Translation\Drivers\DeepLTranslationDriver;
use App\Services\Translation\Drivers\GoogleTranslationDriver;
use App\Services\Translation\TranslationManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MultiLanguageArchitectureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed default languages if not present
        if (! Language::where('code', 'en')->exists()) {
            Language::create([
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'direction' => 'ltr',
                'flag' => '🇬🇧',
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 1,
            ]);
        }

        if (! Language::where('code', 'ar')->exists()) {
            Language::create([
                'code' => 'ar',
                'name' => 'Arabic',
                'native_name' => 'العربية',
                'direction' => 'rtl',
                'flag' => '🇦🇪',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 2,
            ]);
        }
    }

    /**
     * Test English immutability at model and controller level.
     */
    public function test_english_language_cannot_be_deleted_or_deactivated(): void
    {
        $english = Language::where('code', 'en')->firstOrFail();

        // Attempting to delete English directly triggers exception
        $this->expectException(\InvalidArgumentException::class);
        $english->delete();

        // Assert English is still present
        $this->assertDatabaseHas('languages', ['code' => 'en', 'is_default' => true]);
    }

    /**
     * Test admin cannot delete English via controller.
     */
    public function test_admin_cannot_delete_english_via_http(): void
    {
        $admin = User::factory()->create();
        $english = Language::where('code', 'en')->firstOrFail();

        $response = $this->actingAs($admin)
            ->delete(route('admin.settings.languages.destroy', $english->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('languages', ['code' => 'en']);
    }

    /**
     * Test complete locale lifecycle creation and removal.
     */
    public function test_locale_manager_builds_and_purges_complete_locale_files(): void
    {
        $manager = app(LocaleManager::class);
        $testLang = new Language([
            'code' => 'fr',
            'name' => 'French',
            'native_name' => 'Français',
            'direction' => 'ltr',
            'is_default' => false,
            'is_active' => true,
        ]);

        // Build locale
        $manager->buildLocale($testLang);

        $baseDir = lang_path('fr');
        $this->assertTrue(File::exists($baseDir . '/ui.php'));
        $this->assertTrue(File::exists($baseDir . '/messages.php'));
        $this->assertTrue(File::exists(lang_path('fr.json')));

        // Purge locale
        $manager->removeLocale($testLang);

        $this->assertFalse(File::exists($baseDir));
        $this->assertFalse(File::exists(lang_path('fr.json')));
    }

    /**
     * Test HasTranslations trait on models.
     */
    public function test_translatable_attributes_transparent_fallback_and_mutation(): void
    {
        $tour = new Tour();
        $tour->name = [
            'en' => 'Evening Desert Safari',
            'ar' => 'سفاري صحراوي مسائي',
        ];

        $this->assertEquals('Evening Desert Safari', $tour->getTranslation('name', 'en'));
        $this->assertEquals('سفاري صحراوي مسائي', $tour->getTranslation('name', 'ar'));

        // Non-existent translation falls back to default English
        $this->assertEquals('Evening Desert Safari', $tour->getTranslation('name', 'es'));
    }

    /**
     * Test DeepL translation driver preserves HTML tags.
     */
    public function test_deepl_translation_driver_calls_api_and_preserves_html(): void
    {
        Http::fake([
            'https://api-free.deepl.com/v2/translate' => Http::response([
                'translations' => [
                    ['text' => 'مرحبا بكم في <span class="highlight">دبي</span>'],
                ],
            ], 200),
        ]);

        $driver = new DeepLTranslationDriver('dummy-key', 'free');
        $translated = $driver->translate('Welcome to <span class="highlight">Dubai</span>', 'ar', 'en');

        $this->assertEquals('مرحبا بكم في <span class="highlight">دبي</span>', $translated);
    }

    /**
     * Test Google Cloud Translation driver.
     */
    public function test_google_translation_driver_calls_api(): void
    {
        Http::fake([
            'https://translation.googleapis.com/language/translate/v2*' => Http::response([
                'data' => [
                    'translations' => [
                        ['translatedText' => 'مرحبا بكم في دبي'],
                    ],
                ],
            ], 200),
        ]);

        $driver = new GoogleTranslationDriver('dummy-google-key');
        $translated = $driver->translate('Welcome to Dubai', 'ar', 'en');

        $this->assertEquals('مرحبا بكم في دبي', $translated);
    }

    /**
     * Test translation manager enforces single active service rule.
     */
    public function test_translation_manager_single_active_service_rule(): void
    {
        $manager = app(TranslationManager::class);

        // Configure DeepL as active
        $manager->updateSettings([
            'active_service' => 'deepl',
            'deepl_api_key' => 'key123',
            'deepl_endpoint_type' => 'free',
        ]);

        $this->assertEquals('deepl', $manager->getActiveServiceName());
        $this->assertInstanceOf(DeepLTranslationDriver::class, $manager->driver());

        // Switch active service to Google
        $manager->updateSettings([
            'active_service' => 'google',
            'google_api_key' => 'key456',
        ]);

        $this->assertEquals('google', $manager->getActiveServiceName());
        $this->assertInstanceOf(GoogleTranslationDriver::class, $manager->driver());
    }

    /**
     * Test /en and /en/* are 301-redirected to clean root URLs.
     */
    public function test_en_urls_301_redirect_to_root(): void
    {
        $response = $this->get('/en');
        $response->assertStatus(301);
        $response->assertRedirect('/');

        $response2 = $this->get('/en/tours');
        $response2->assertStatus(301);
        $response2->assertRedirect('/tours');
    }

    /**
     * Test root / renders in English (LTR) and /ar renders in Arabic (RTL).
     */
    public function test_locale_routes_render_with_proper_direction_and_language(): void
    {
        // Root English
        $responseEn = $this->get('/');
        $responseEn->assertStatus(200);
        $responseEn->assertSee('lang="en"', false);
        $responseEn->assertSee('dir="ltr"', false);

        // Arabic /ar
        $responseAr = $this->get('/ar');
        $responseAr->assertStatus(200);
        $responseAr->assertSee('lang="ar"', false);
        $responseAr->assertSee('dir="rtl"', false);
        $responseAr->assertSee('Cairo', false);
    }

    /**
     * Test reciprocal alternate hreflang tags on frontend pages.
     */
    public function test_hreflang_tags_rendered_in_head(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('hreflang="x-default"', false);
        $response->assertSee('hreflang="en"', false);
        $response->assertSee('hreflang="ar"', false);
    }

    /**
     * Test XML Sitemaps include xmlns:xhtml and alternate links for all languages.
     */
    public function test_sitemap_pages_includes_xhtml_alternates(): void
    {
        $response = $this->get('/sitemap-pages.xml');
        $response->assertStatus(200);
        $response->assertSee('xmlns:xhtml="http://www.w3.org/1999/xhtml"', false);
        $response->assertSee('xhtml:link rel="alternate" hreflang="ar"', false);
        $response->assertSee('xhtml:link rel="alternate" hreflang="en"', false);
    }
}
