<?php

namespace App\Services\Localization;

use App\Models\Language;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class LocaleManager
{
    /**
     * Build the complete locale ecosystem for a given language code or Language model.
     */
    public function buildLocale(string|Language $locale): bool
    {
        $code = $locale instanceof Language ? $locale->code : (string) $locale;
        $code = strtolower(trim($code));
        $langDir = base_path('lang/'.$code);

        File::ensureDirectoryExists($langDir);

        // 1. UI translation dictionary: lang/{code}/ui.php
        $uiFilePath = $langDir.'/ui.php';
        if (! File::exists($uiFilePath)) {
            $baseUiPath = base_path('lang/en/ui.php');
            if (File::exists($baseUiPath)) {
                $presetPath = base_path('lang/ar/ui.php');
                if ($code === 'ar' && File::exists($presetPath)) {
                    // Already exists or preset available
                } else {
                    File::copy($baseUiPath, $uiFilePath);
                }
            } else {
                File::put($uiFilePath, "<?php\n\nreturn [\n    // UI translations for {$code}\n];\n");
            }
        }

        // 2. Messages file: lang/{code}/messages.php
        $messagesFilePath = $langDir.'/messages.php';
        if (! File::exists($messagesFilePath)) {
            $baseMessagesPath = base_path('lang/en/messages.php');
            if (File::exists($baseMessagesPath)) {
                File::copy($baseMessagesPath, $messagesFilePath);
            } else {
                File::put($messagesFilePath, "<?php\n\nreturn [\n    'success' => 'Operation completed successfully.',\n    'error' => 'An unexpected error occurred.',\n];\n");
            }
        }

        // 3. JSON dictionary: lang/{code}.json
        $jsonFilePath = base_path('lang/'.$code.'.json');
        if (! File::exists($jsonFilePath)) {
            $baseJsonPath = base_path('lang/en.json');
            if (File::exists($baseJsonPath)) {
                File::copy($baseJsonPath, $jsonFilePath);
            } else {
                File::put($jsonFilePath, json_encode(new \stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }

        Language::clearLanguageCache();

        Log::info("Locale ecosystem successfully built for [{$code}].");

        return true;
    }

    /**
     * Purge the complete locale ecosystem for a language.
     * English is permanently guarded from deletion.
     */
    public function removeLocale(string|Language $locale): bool
    {
        $code = $locale instanceof Language ? $locale->code : (string) $locale;
        $code = strtolower(trim($code));

        if ($code === 'en') {
            throw new \InvalidArgumentException('Default English (en) locale cannot be removed.');
        }

        $langDir = base_path('lang/'.$code);
        if (File::isDirectory($langDir)) {
            File::deleteDirectory($langDir);
        }

        $jsonFilePath = base_path('lang/'.$code.'.json');
        if (File::exists($jsonFilePath)) {
            File::delete($jsonFilePath);
        }

        Language::clearLanguageCache();

        Log::info("Locale ecosystem successfully purged for [{$code}].");

        return true;
    }

    /**
     * Check if a locale's files are fully built.
     */
    public function isLocaleBuilt(string|Language $locale): bool
    {
        $code = $locale instanceof Language ? $locale->code : (string) $locale;
        $code = strtolower(trim($code));
        $langDir = base_path('lang/'.$code);
        $uiFile = $langDir.'/ui.php';

        return File::isDirectory($langDir) && File::exists($uiFile);
    }
}
