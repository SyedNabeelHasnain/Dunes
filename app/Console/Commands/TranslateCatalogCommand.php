<?php

namespace App\Console\Commands;

use App\Models\Addon;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogPostFaq;
use App\Models\BlogTag;
use App\Models\Category;
use App\Models\ContentItem;
use App\Models\EmailTemplate;
use App\Models\Faq;
use App\Models\Itinerary;
use App\Models\Language;
use App\Models\LegalItem;
use App\Models\LegalPage;
use App\Models\LegalSection;
use App\Models\Setting;
use App\Models\Tier;
use App\Models\Tour;
use App\Services\Translation\Drivers\DeepLTranslationDriver;
use App\Services\Translation\TranslationManager;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TranslateCatalogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:translate-catalog 
                            {--force : Force re-translation of existing translations}
                            {--locale= : Translate a specific locale only (e.g. ar, ru, es, it)}
                            {--model= : Translate a specific model only (e.g. Tour, BlogPost)}
                            {--dry-run : Simulate translation without saving to database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate entire commercial catalog, blogs, FAQs, legal policies, and email templates into all active languages via DeepL';

    /**
     * Execute the console command.
     */
    public function handle(TranslationManager $translationManager): int
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $this->info('===========================================================');
        $this->info('  Dunes Discovery Tourism - Global Multilingual Catalog Translator');
        $this->info('===========================================================');

        // 1. Resolve Driver & API Key
        $authKey = Setting::where('setting_key', 'deepl_auth_key')->value('setting_value')
            ?: (env('DEEPL_AUTH_KEY') ?: '');

        $endpointType = Setting::where('setting_key', 'deepl_endpoint_type')->value('setting_value')
            ?: (env('DEEPL_ENDPOINT_TYPE') ?: 'free');

        if (empty($authKey)) {
            $this->warn('DeepL Auth Key is not set in Settings or .env. Skipping automatic catalog translation.');
            Log::info('TranslateCatalogCommand: DeepL Auth Key not set; skipping execution.');
            return self::SUCCESS;
        }

        $driver = new DeepLTranslationDriver($authKey, $endpointType);
        $this->info("Translation Driver: {$driver->getName()}");

        // 2. Resolve Target Locales
        $specificLocale = $this->option('locale');
        if ($specificLocale) {
            $targetLocales = [strtolower(trim($specificLocale))];
        } else {
            try {
                $targetLocales = Language::where('is_default', false)
                    ->where('is_active', true)
                    ->pluck('code')
                    ->all();
            } catch (\Throwable $e) {
                $targetLocales = [];
            }

            if (empty($targetLocales)) {
                $targetLocales = ['ar', 'ru', 'es', 'it'];
            }
        }

        $this->info('Target Locales: '.implode(', ', array_map('strtoupper', $targetLocales)));

        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');
        if ($force) {
            $this->warn('Notice: --force enabled. Overwriting existing target translations.');
        }
        if ($dryRun) {
            $this->warn('Notice: --dry-run enabled. No changes will be committed to the database.');
        }

        // 3. Define Models & Translation Configurations
        $modelConfigs = [
            'Tour' => [
                'class' => Tour::class,
                'html_fields' => ['full_desc'],
            ],
            'Category' => [
                'class' => Category::class,
                'html_fields' => ['description'],
            ],
            'Tier' => [
                'class' => Tier::class,
                'html_fields' => [],
            ],
            'Addon' => [
                'class' => Addon::class,
                'html_fields' => [],
            ],
            'ContentItem' => [
                'class' => ContentItem::class,
                'html_fields' => ['description'],
            ],
            'Itinerary' => [
                'class' => Itinerary::class,
                'html_fields' => ['description'],
            ],
            'BlogPost' => [
                'class' => BlogPost::class,
                'html_fields' => ['content'],
            ],
            'BlogCategory' => [
                'class' => BlogCategory::class,
                'html_fields' => ['description'],
            ],
            'BlogTag' => [
                'class' => BlogTag::class,
                'html_fields' => [],
            ],
            'BlogPostFaq' => [
                'class' => BlogPostFaq::class,
                'html_fields' => [],
            ],
            'Faq' => [
                'class' => Faq::class,
                'html_fields' => [],
            ],
            'LegalPage' => [
                'class' => LegalPage::class,
                'html_fields' => ['description'],
            ],
            'LegalSection' => [
                'class' => LegalSection::class,
                'html_fields' => [],
            ],
            'LegalItem' => [
                'class' => LegalItem::class,
                'html_fields' => ['content'],
            ],
            'EmailTemplate' => [
                'class' => EmailTemplate::class,
                'html_fields' => ['content_html'],
            ],
        ];

        $specificModel = $this->option('model');
        if ($specificModel) {
            $matchedKey = collect(array_keys($modelConfigs))->first(
                fn ($k) => strtolower($k) === strtolower(trim($specificModel))
            );
            if (! $matchedKey) {
                $this->error("Unknown model '{$specificModel}'. Available models: ".implode(', ', array_keys($modelConfigs)));
                return self::FAILURE;
            }
            $modelConfigs = [$matchedKey => $modelConfigs[$matchedKey]];
        }

        $summary = [];
        $totalFieldsTranslated = 0;

        foreach ($modelConfigs as $modelName => $config) {
            $modelClass = $config['class'];
            $htmlFields = $config['html_fields'];

            $this->line("\nTranslating {$modelName} records...");

            try {
                $recordCount = $modelClass::count();
            } catch (\Throwable $e) {
                $this->error("Failed to query {$modelName}: ".$e->getMessage());
                continue;
            }

            $count = $this->translateModelBatch($modelName, $modelClass, $htmlFields, $targetLocales, $driver, $force, $dryRun);
            $totalFieldsTranslated += $count;
            $summary[] = [
                'model' => $modelName,
                'records' => $recordCount,
                'fields_translated' => $count,
            ];

            $this->info("Completed {$modelName}: {$count} field translations across {$recordCount} records.");
        }

        // 4. Clear Frontend & Sitemap Caches
        try {
            Cache::forget('site_tours_header_cache');
            Cache::forget('sitemap_tours_xml');
            Cache::forget('sitemap_blogs_xml');
            Cache::forget('sitemap_images_xml');
            Cache::forget('site_llms_txt');
            Cache::forget('site_llms_full_txt');
        } catch (\Throwable $e) {
            // Silently continue
        }

        $this->table(['Entity Model', 'Total Records', 'Fields Translated'], $summary);
        $this->info("Successfully completed global catalog translation! Total fields translated: {$totalFieldsTranslated}");

        return self::SUCCESS;
    }

    /**
     * Translate records for a given model in high-efficiency batches.
     *
     * @param string $modelName
     * @param string $modelClass
     * @param array<string> $htmlFields
     * @param array<string> $targetLocales
     * @param DeepLTranslationDriver $driver
     * @param bool $force
     * @param bool $dryRun
     * @return int Number of translated fields saved
     */
    protected function translateModelBatch(
        string $modelName,
        string $modelClass,
        array $htmlFields,
        array $targetLocales,
        DeepLTranslationDriver $driver,
        bool $force,
        bool $dryRun
    ): int {
        try {
            $records = $modelClass::all();
        } catch (\Throwable $e) {
            $this->error("Failed to query {$modelName}: ".$e->getMessage());
            return 0;
        }

        $totalFields = 0;
        $recordsById = $records->keyBy(fn ($r) => (string) $r->getKey());

        foreach ($targetLocales as $locale) {
            // 1. Collect all plain-text attributes needing translation
            $plainItems = [];
            foreach ($records as $record) {
                if (! property_exists($record, 'translatable') || ! is_array($record->translatable)) {
                    continue;
                }

                foreach ($record->translatable as $attribute) {
                    if (in_array($attribute, $htmlFields, true)) {
                        continue;
                    }

                    $existing = $record->getTranslation($attribute, $locale, false);
                    if (! empty($existing) && ! $force) {
                        continue;
                    }

                    $enValue = $this->getEnglishSource($record, $attribute);
                    if (! empty($enValue) && is_string($enValue) && trim($enValue) !== '') {
                        $key = $record->getKey().'___'.$attribute;
                        $plainItems[$key] = $enValue;
                    }
                }
            }

            // 2. Batch-translate plain items in chunks of 30
            if (! empty($plainItems)) {
                $chunks = array_chunk($plainItems, 30, true);
                foreach ($chunks as $chunk) {
                    try {
                        $translated = $driver->translate($chunk, $locale, 'en', false);
                        foreach ($translated as $compoundKey => $val) {
                            if (empty($val) || ! is_string($val)) {
                                continue;
                            }
                            $parts = explode('___', $compoundKey, 2);
                            if (count($parts) !== 2) {
                                continue;
                            }
                            [$recId, $attr] = $parts;
                            if (isset($recordsById[$recId])) {
                                if (! $dryRun) {
                                    $recordsById[$recId]->setTranslation($attr, $locale, $val);
                                }
                                $totalFields++;
                            }
                        }
                        if (! app()->runningUnitTests()) {
                            usleep(100000); // 100ms pause between batches
                        }
                    } catch (\Throwable $e) {
                        $this->warn("DeepL plain batch error for [{$locale}] in {$modelName}: ".$e->getMessage());
                    }
                }
            }

            // 3. Translate HTML fields individually to prevent payload/timeout issues
            foreach ($records as $record) {
                if (! property_exists($record, 'translatable') || ! is_array($record->translatable)) {
                    continue;
                }

                foreach ($htmlFields as $htmlAttr) {
                    if (! in_array($htmlAttr, $record->translatable, true)) {
                        continue;
                    }

                    $existing = $record->getTranslation($htmlAttr, $locale, false);
                    if (! empty($existing) && ! $force) {
                        continue;
                    }

                    $enValue = $this->getEnglishSource($record, $htmlAttr);
                    if (empty($enValue) || ! is_string($enValue) || trim($enValue) === '') {
                        continue;
                    }

                    try {
                        $transHtml = $driver->translate($enValue, $locale, 'en', true);
                        if (! empty($transHtml) && is_string($transHtml)) {
                            if (! $dryRun) {
                                $record->setTranslation($htmlAttr, $locale, $transHtml);
                            }
                            $totalFields++;
                        }
                        if (! app()->runningUnitTests()) {
                            usleep(100000);
                        }
                    } catch (\Throwable $e) {
                        $this->warn("DeepL HTML translation error for [{$locale}] on {$modelName} #{$record->getKey()}: ".$e->getMessage());
                    }
                }
            }
        }

        // 4. Save any updated records
        if (! $dryRun) {
            foreach ($records as $record) {
                if ($record->isDirty()) {
                    $record->saveQuietly();
                }
            }
        }

        return $totalFields;
    }

    /**
     * Safely extract the English source string for an attribute.
     */
    protected function getEnglishSource(Model $record, string $attribute): ?string
    {
        $translations = $record->getTranslations($attribute);
        if (! empty($translations['en'])) {
            return (string) $translations['en'];
        }

        $rawVal = $record->getRawOriginal($attribute);
        if (empty($rawVal)) {
            return null;
        }

        if (is_string($rawVal)) {
            $trimmed = trim($rawVal);
            if (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) {
                $decoded = json_decode($trimmed, true);
                if (is_array($decoded)) {
                    return $decoded['en'] ?? null;
                }
            }

            return $trimmed;
        }

        return null;
    }
}
