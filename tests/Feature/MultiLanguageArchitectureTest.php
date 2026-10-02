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
        $translated = $driver->translate('Welcome to <span class="highlight">Dubai</span>', 'ar', 'en', true);

        $this->assertEquals('مرحبا بكم في <span class="highlight">دبي</span>', $translated);

        Http::assertSent(function ($request) {
            $data = $request->data();
            return isset($data['tag_handling']) && $data['tag_handling'] === 'html'
                && ! isset($data['preserve_formatting']);
        });
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

    /**
     * Test bulletproof fallback resiliency when languages table is missing or unmigrated.
     */
    public function test_fallback_resiliency_when_languages_table_is_missing(): void
    {
        \Illuminate\Support\Facades\Schema::dropIfExists('languages');
        Language::clearLanguageCache();

        $active = Language::getActive();
        $this->assertNotEmpty($active);
        $this->assertEquals('en', $active->first()->code);

        $default = Language::getDefault();
        $this->assertNotNull($default);
        $this->assertEquals('en', $default->code);

        $codes = Language::getActiveCodes();
        $this->assertEquals(['en'], $codes);

        $switchUrl = switch_locale_url('ar');
        $this->assertStringContainsString('/ar', $switchUrl);
    }

    /**
     * Test admin can update translation settings.
     */
    public function test_admin_can_update_translation_settings(): void
    {
        $admin = User::first() ?? User::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/settings/translations', [
            'translation_active_service' => 'deepl',
            'deepl_auth_key' => 'test-key:fx',
            'deepl_endpoint_type' => 'free',
            'google_translate_api_key' => '',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/settings/translations');
        $this->assertEquals('deepl', Setting::where('setting_key', 'translation_active_service')->value('setting_value'));
    }

    /**
     * Test all 5 global languages are seeded and configured with directions and emoji flags.
     */
    public function test_five_global_languages_seeded_and_active(): void
    {
        $this->artisan('migrate');

        $expected = [
            'en' => ['dir' => 'ltr', 'default' => true],
            'ar' => ['dir' => 'rtl', 'default' => false],
            'ru' => ['dir' => 'ltr', 'default' => false],
            'es' => ['dir' => 'ltr', 'default' => false],
            'it' => ['dir' => 'ltr', 'default' => false],
        ];

        foreach ($expected as $code => $data) {
            $lang = Language::where('code', $code)->first();
            $this->assertNotNull($lang, "Language [{$code}] must exist in database.");
            $this->assertTrue($lang->is_active, "Language [{$code}] must be active.");
            $this->assertEquals($data['dir'], $lang->direction, "Language [{$code}] direction mismatch.");
            $this->assertEquals($data['default'], $lang->is_default, "Language [{$code}] is_default mismatch.");
            $this->assertNotEmpty($lang->flag_emoji, "Language [{$code}] must have valid flag emoji.");
        }
    }

    /**
     * Test static UI translation dictionaries exist for all 5 languages with matching navigation keys.
     */
    public function test_static_ui_dictionaries_exist_for_all_five_languages(): void
    {
        $locales = ['en', 'ar', 'ru', 'es', 'it'];

        foreach ($locales as $locale) {
            $uiPath = base_path("lang/{$locale}/ui.php");
            $messagesPath = base_path("lang/{$locale}/messages.php");
            $jsonPath = base_path("lang/{$locale}.json");

            $this->assertFileExists($uiPath, "UI dictionary missing for [{$locale}]");
            $this->assertFileExists($messagesPath, "Messages dictionary missing for [{$locale}]");
            $this->assertFileExists($jsonPath, "JSON dictionary missing for [{$locale}]");

            $ui = include $uiPath;
            $this->assertIsArray($ui);
            $this->assertArrayHasKey('nav', $ui);
            $this->assertArrayHasKey('common', $ui);
            $this->assertArrayHasKey('booking', $ui);
            $this->assertArrayHasKey('tour', $ui);
            $this->assertArrayHasKey('blog', $ui);
            $this->assertArrayHasKey('footer', $ui);
            $this->assertArrayHasKey('home', $ui['nav']);
            $this->assertArrayHasKey('book_now', $ui['nav']);
        }
    }

    /**
     * Test multilingual frontend routes for all 5 languages render with correct dir and lang attributes.
     */
    public function test_multilingual_routes_render_with_expected_direction_and_language(): void
    {
        $this->artisan('migrate');

        // English root
        $resEn = $this->get('/');
        $resEn->assertStatus(200);
        $resEn->assertSee('lang="en"', false);
        $resEn->assertSee('dir="ltr"', false);

        // Arabic RTL
        $resAr = $this->get('/ar');
        $resAr->assertStatus(200);
        $resAr->assertSee('lang="ar"', false);
        $resAr->assertSee('dir="rtl"', false);
        $resAr->assertSee('Cairo', false);

        // Russian LTR
        $resRu = $this->get('/ru');
        $resRu->assertStatus(200);
        $resRu->assertSee('lang="ru"', false);
        $resRu->assertSee('dir="ltr"', false);

        // Spanish LTR
        $resEs = $this->get('/es');
        $resEs->assertStatus(200);
        $resEs->assertSee('lang="es"', false);
        $resEs->assertSee('dir="ltr"', false);

        // Italian LTR
        $resIt = $this->get('/it');
        $resIt->assertStatus(200);
        $resIt->assertSee('lang="it"', false);
        $resIt->assertSee('dir="ltr"', false);

        // Verify localized dynamic tour detail page
        $tour = Tour::first() ?? Tour::create([
            'slug' => 'evening-desert-safari-dubai',
            'name' => 'Evening Desert Safari Dubai',
            'status' => 'active',
            'priority' => 1,
        ]);
        $this->get('/ar/'.$tour->slug)->assertStatus(200)->assertSee('dir="rtl"', false);
        $this->get('/ru/'.$tour->slug)->assertStatus(200)->assertSee('dir="ltr"', false);
        $this->get('/es/'.$tour->slug)->assertStatus(200)->assertSee('dir="ltr"', false);
        $this->get('/it/'.$tour->slug)->assertStatus(200)->assertSee('dir="ltr"', false);
    }

    /**
     * Test TranslateCatalogCommand runs cleanly with dry-run or when API key is missing.
     */
    public function test_translate_catalog_command_runs_successfully(): void
    {
        $this->artisan('migrate');

        // When DeepL key is not set, it exits gracefully with info
        Setting::where('setting_key', 'deepl_auth_key')->delete();
        $this->artisan('app:translate-catalog')
            ->assertExitCode(0);

        // Mock DeepL API
        Http::fake([
            'https://api-free.deepl.com/v2/translate' => Http::response([
                'translations' => [
                    ['text' => 'Translated Text Arabic'],
                ],
            ], 200),
            'https://api.deepl.com/v2/translate' => Http::response([
                'translations' => [
                    ['text' => 'Translated Text Pro'],
                ],
            ], 200),
        ]);

        Setting::updateOrCreate(['setting_key' => 'deepl_auth_key'], ['setting_value' => 'mock-deepl-key:fx']);

        $this->artisan('app:translate-catalog', ['--dry-run' => true])
            ->assertExitCode(0);
    }

    /**
     * Test /llms.txt and /llms-full.txt include all 5 language portals.
     */
    public function test_llms_txt_and_full_txt_include_all_five_language_portals(): void
    {
        $resIndex = $this->get('/llms.txt');
        $resIndex->assertStatus(200);
        $resIndex->assertSee('English Portal');
        $resIndex->assertSee('Arabic Portal');
        $resIndex->assertSee('Russian Portal');
        $resIndex->assertSee('Spanish Portal');
        $resIndex->assertSee('Italian Portal');

        $resFull = $this->get('/llms-full.txt');
        $resFull->assertStatus(200);
        $resFull->assertSee('SECTION 7: GLOBAL MULTILINGUAL PORTALS');
        $resFull->assertSee('Russian (`ru`)');
        $resFull->assertSee('Spanish (`es`)');
        $resFull->assertSee('Italian (`it`)');
    }

    /**
     * Test admin can trigger catalog translation endpoint.
     */
    public function test_admin_can_trigger_catalog_translation_endpoint(): void
    {
        $admin = User::first() ?? User::factory()->create();

        $response = $this->actingAs($admin)->postJson(route('admin.settings.translations.translate-catalog'), [
            'force' => false,
            'locale' => 'ar',
            'model' => 'Tour',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'message', 'output']);
        $this->assertTrue($response->json('success'));
    }

    /**
     * Test navigation preserves locale across pages.
     */
    public function test_navigation_preserves_locale_across_pages(): void
    {
        $this->artisan('migrate');

        // 1. Visit Arabic homepage
        $responseAr = $this->get('/ar');
        $responseAr->assertStatus(200);
        $responseAr->assertSee('/ar/about', false);
        $responseAr->assertSee('/ar/tours', false);
        $responseAr->assertSee('/ar/contact', false);
        $responseAr->assertSee('/ar/faq', false);

        // 2. Visit Arabic about page directly via the link
        $responseAbout = $this->get('/ar/about');
        $responseAbout->assertStatus(200);
        $responseAbout->assertSee('lang="ar"', false);
        $responseAbout->assertSee('dir="rtl"', false);
        $responseAbout->assertSee('/ar/tours', false);
        $responseAbout->assertSee('/ar/contact', false);

        // 3. Visit Russian homepage
        $responseRu = $this->get('/ru');
        $responseRu->assertStatus(200);
        $responseRu->assertSee('/ru/about', false);
        $responseRu->assertSee('/ru/tours', false);

        // 4. Default English homepage should have no prefix
        $responseEn = $this->get('/');
        $responseEn->assertStatus(200);
        $responseEn->assertSee('href="http://localhost/about"', false);
    }
}
