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
                $records = $modelClass::all();
            } catch (\Throwable $e) {
                $this->error("Failed to query {$modelName}: ".$e->getMessage());
                continue;
            }

            $recordCount = $records->count();
            $translatedInModel = 0;

            foreach ($records as $record) {
                $count = $this->translateRecord($record, $targetLocales, $htmlFields, $driver, $force, $dryRun);
                $translatedInModel += $count;
            }

            $totalFieldsTranslated += $translatedInModel;
            $summary[] = [
                'model' => $modelName,
                'records' => $recordCount,
                'fields_translated' => $translatedInModel,
            ];

            $this->info("Completed {$modelName}: {$translatedInModel} field translations across {$recordCount} records.");
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
     * Translate an individual model record into the given target locales.
     *
     * @param Model $record
     * @param array<string> $targetLocales
     * @param array<string> $htmlFields
     * @param DeepLTranslationDriver $driver
     * @param bool $force
     * @param bool $dryRun
     * @return int Number of translated fields saved
     */
    protected function translateRecord(
        Model $record,
        array $targetLocales,
        array $htmlFields,
        DeepLTranslationDriver $driver,
        bool $force,
        bool $dryRun
    ): int {
        if (! property_exists($record, 'translatable') || ! is_array($record->translatable)) {
            return 0;
        }

        $translatableAttributes = $record->translatable;
        $totalCount = 0;
        $recordDirty = false;

        foreach ($targetLocales as $locale) {
            $plainBatch = [];
            $htmlBatch = [];

            foreach ($translatableAttributes as $attribute) {
                // Determine English base source
                $enValue = $this->getEnglishSource($record, $attribute);
                if (empty($enValue) || ! is_string($enValue) || trim($enValue) === '') {
                    continue;
                }

                // Check if target translation already exists
                $existing = $record->getTranslation($attribute, $locale, false);
                if (! empty($existing) && ! $force) {
                    continue;
                }

                // Sort into plain vs HTML batch
                if (in_array($attribute, $htmlFields, true)) {
                    $htmlBatch[$attribute] = $enValue;
                } else {
                    $plainBatch[$attribute] = $enValue;
                }
            }

            // Perform batch translations via DeepL
            if (! empty($plainBatch)) {
                try {
                    $translated = $driver->translate($plainBatch, $locale, 'en', false);
                    foreach ($translated as $attr => $translatedVal) {
                        if (! empty($translatedVal) && is_string($translatedVal)) {
                            if (! $dryRun) {
                                $record->setTranslation($attr, $locale, $translatedVal);
                            }
                            $totalCount++;
                            $recordDirty = true;
                        }
                    }
                    if (! app()->runningUnitTests()) {
                        usleep(100000); // 100ms rate-limit pause in production
                    }
                } catch (\Throwable $e) {
                    $this->warn("DeepL plain translation error for [{$locale}] on record #{$record->getKey()}: ".$e->getMessage());
                }
            }

            if (! empty($htmlBatch)) {
                try {
                    $translated = $driver->translate($htmlBatch, $locale, 'en', true);
                    foreach ($translated as $attr => $translatedVal) {
                        if (! empty($translatedVal) && is_string($translatedVal)) {
                            if (! $dryRun) {
                                $record->setTranslation($attr, $locale, $translatedVal);
                            }
                            $totalCount++;
                            $recordDirty = true;
                        }
                    }
                    if (! app()->runningUnitTests()) {
                        usleep(100000); // 100ms rate-limit pause in production
                    }
                } catch (\Throwable $e) {
                    $this->warn("DeepL HTML translation error for [{$locale}] on record #{$record->getKey()}: ".$e->getMessage());
                }
            }
        }

        if ($recordDirty && ! $dryRun) {
            $record->saveQuietly();
        }

        return $totalCount;
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
