<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\ContentItem;
use App\Models\Faq;
use App\Models\Itinerary;
use App\Models\Tier;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ComprehensiveLanguageCompletenessTest extends TestCase
{
    private array $locales = ['en', 'ar', 'ru', 'es', 'it'];

    /**
     * Flatten a multi-dimensional array into a single-level dot-notated array.
     */
    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? $key : "{$prefix}.{$key}";
            if (is_array($value)) {
                $result += $this->flattenArray($value, $newKey);
            } else {
                $result[$newKey] = $value;
            }
        }
        return $result;
    }

    /**
     * Test that all 5 UI dictionaries (en, ar, ru, es, it) have 100% key parity.
     */
    public function test_ui_dictionaries_have_exact_parity_across_all_supported_languages(): void
    {
        $enPath = base_path('lang/en/ui.php');
        $this->assertFileExists($enPath);
        $enDict = $this->flattenArray(require $enPath);
        $enKeys = array_keys($enDict);

        $this->assertGreaterThanOrEqual(700, count($enKeys), "English UI dictionary should have at least 700 keys.");

        foreach ($this->locales as $locale) {
            $locPath = base_path("lang/{$locale}/ui.php");
            $this->assertFileExists($locPath, "Dictionary file missing for locale: {$locale}");

            $locDict = $this->flattenArray(require $locPath);
            $locKeys = array_keys($locDict);

            $missingKeys = array_diff($enKeys, $locKeys);
            $extraKeys = array_diff($locKeys, $enKeys);

            $this->assertEmpty($missingKeys, "Locale '{$locale}' is missing keys: " . implode(', ', array_slice($missingKeys, 0, 10)));
            $this->assertEmpty($extraKeys, "Locale '{$locale}' has extraneous keys: " . implode(', ', array_slice($extraKeys, 0, 10)));
            $this->assertSame(count($enKeys), count($locKeys), "Key count mismatch for locale: {$locale}");
        }
    }

    /**
     * Test that Arabic and Russian translations actually contain native script characters.
     */
    public function test_arabic_and_russian_translations_contain_native_character_scripts(): void
    {
        $arDict = $this->flattenArray(require base_path('lang/ar/ui.php'));
        $ruDict = $this->flattenArray(require base_path('lang/ru/ui.php'));

        $arArabicCharCount = 0;
        foreach ($arDict as $val) {
            if (is_string($val) && preg_match('/\p{Arabic}/u', $val)) {
                $arArabicCharCount++;
            }
        }
        // At least 90% of strings should have Arabic characters (some are numbers/symbols)
        $this->assertGreaterThan(count($arDict) * 0.85, $arArabicCharCount, "Arabic dictionary lacks expected Arabic script volume.");

        $ruCyrillicCharCount = 0;
        foreach ($ruDict as $val) {
            if (is_string($val) && preg_match('/\p{Cyrillic}/u', $val)) {
                $ruCyrillicCharCount++;
            }
        }
        $this->assertGreaterThan(count($ruDict) * 0.85, $ruCyrillicCharCount, "Russian dictionary lacks expected Cyrillic script volume.");
    }

    /**
     * Test that seeded tours JSON has 100% multilingual coverage.
     */
    public function test_seeded_tours_have_100_percent_multilingual_coverage(): void
    {
        $path = database_path('seeders/data/tours.json');
        $this->assertFileExists($path);
        $tours = json_decode(File::get($path), true);
        $this->assertIsArray($tours);
        $this->assertCount(8, $tours);

        $translatableFields = ['name', 'short_desc', 'full_desc', 'meta_title', 'meta_desc', 'meta_keywords'];

        foreach ($tours as $tour) {
            foreach ($translatableFields as $field) {
                $this->assertArrayHasKey($field, $tour, "Tour '{$tour['slug']}' missing field {$field}");
                $this->assertIsArray($tour[$field], "Tour '{$tour['slug']}' field {$field} must be a localized array");
                foreach ($this->locales as $locale) {
                    $this->assertArrayHasKey($locale, $tour[$field], "Tour '{$tour['slug']}' field {$field} missing locale {$locale}");
                    $this->assertNotEmpty($tour[$field][$locale], "Tour '{$tour['slug']}' field {$field} empty for locale {$locale}");
                }
            }
        }
    }

    /**
     * Test that seeded tiers JSON has 100% multilingual coverage.
     */
    public function test_seeded_tiers_have_100_percent_multilingual_coverage(): void
    {
        $path = database_path('seeders/data/tiers.json');
        $this->assertFileExists($path);
        $tiers = json_decode(File::get($path), true);
        $this->assertIsArray($tiers);
        $this->assertGreaterThanOrEqual(12, count($tiers));

        foreach ($tiers as $tier) {
            foreach (['name', 'display_name', 'description'] as $field) {
                if (! isset($tier[$field])) {
                    continue;
                }
                $this->assertIsArray($tier[$field], "Tier '{$tier['slug']}' field {$field} must be localized array");
                foreach ($this->locales as $locale) {
                    $this->assertArrayHasKey($locale, $tier[$field], "Tier '{$tier['slug']}' field {$field} missing locale {$locale}");
                    $this->assertNotEmpty($tier[$field][$locale], "Tier '{$tier['slug']}' field {$field} empty for locale {$locale}");
                }
            }
        }
    }

    /**
     * Test that seeded addons JSON has 100% multilingual coverage.
     */
    public function test_seeded_addons_have_100_percent_multilingual_coverage(): void
    {
        $path = database_path('seeders/data/addons.json');
        $this->assertFileExists($path);
        $addons = json_decode(File::get($path), true);
        $this->assertIsArray($addons);
        $this->assertCount(28, $addons);

        foreach ($addons as $addon) {
            foreach (['name', 'description'] as $field) {
                $this->assertArrayHasKey($field, $addon, "Addon '{$addon['slug']}' missing field {$field}");
                $this->assertIsArray($addon[$field], "Addon '{$addon['slug']}' field {$field} must be localized array");
                foreach ($this->locales as $locale) {
                    $this->assertArrayHasKey($locale, $addon[$field], "Addon '{$addon['slug']}' field {$field} missing locale {$locale}");
                    $this->assertNotEmpty($addon[$field][$locale], "Addon '{$addon['slug']}' field {$field} empty for locale {$locale}");
                }
            }
        }
    }

    /**
     * Test that seeded content items, itineraries, and FAQs have 100% multilingual coverage.
     */
    public function test_seeded_content_items_itineraries_and_faqs_have_full_coverage(): void
    {
        // Content items
        $content = json_decode(File::get(database_path('seeders/data/content_items.json')), true);
        $this->assertCount(236, $content);
        foreach ($content as $item) {
            foreach (['title', 'description'] as $field) {
                $this->assertArrayHasKey($field, $item);
                $this->assertIsArray($item[$field]);
                foreach ($this->locales as $locale) {
                    $this->assertArrayHasKey($locale, $item[$field], "Content item #{$item['id']} field {$field} missing {$locale}");
                    $this->assertNotEmpty($item[$field][$locale], "Content item #{$item['id']} field {$field} empty for {$locale}");
                }
            }
        }

        // Itineraries
        $itineraries = json_decode(File::get(database_path('seeders/data/itinerary.json')), true);
        $this->assertCount(36, $itineraries);
        foreach ($itineraries as $it) {
            foreach (['title', 'description'] as $field) {
                $this->assertArrayHasKey($field, $it);
                $this->assertIsArray($it[$field]);
                foreach ($this->locales as $locale) {
                    $this->assertArrayHasKey($locale, $it[$field], "Itinerary #{$it['id']} field {$field} missing {$locale}");
                    $this->assertNotEmpty($it[$field][$locale], "Itinerary #{$it['id']} field {$field} empty for {$locale}");
                }
            }
        }

        // FAQs
        $faqs = json_decode(File::get(database_path('seeders/data/faqs.json')), true);
        $this->assertCount(44, $faqs);
        foreach ($faqs as $faq) {
            foreach (['question', 'answer'] as $field) {
                $this->assertArrayHasKey($field, $faq);
                $this->assertIsArray($faq[$field]);
                foreach ($this->locales as $locale) {
                    $this->assertArrayHasKey($locale, $faq[$field], "FAQ #{$faq['id']} field {$field} missing {$locale}");
                    $this->assertNotEmpty($faq[$field][$locale], "FAQ #{$faq['id']} field {$field} empty for {$locale}");
                }
            }
        }

        // Blog Categories
        $cats = json_decode(File::get(database_path('seeders/data/blog_categories.json')), true);
        $this->assertCount(4, $cats);
        foreach ($cats as $cat) {
            foreach (['name', 'description'] as $field) {
                $this->assertArrayHasKey($field, $cat);
                $this->assertIsArray($cat[$field]);
                foreach ($this->locales as $locale) {
                    $this->assertArrayHasKey($locale, $cat[$field], "Blog category '{$cat['slug']}' field {$field} missing {$locale}");
                    $this->assertNotEmpty($cat[$field][$locale], "Blog category '{$cat['slug']}' field {$field} empty for {$locale}");
                }
            }
        }
    }
}
