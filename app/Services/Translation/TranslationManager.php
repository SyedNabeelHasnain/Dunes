<?php

namespace App\Services\Translation;

use App\Models\Setting;
use App\Services\Translation\Contracts\TranslationDriverInterface;
use App\Services\Translation\Drivers\DeepLTranslationDriver;
use App\Services\Translation\Drivers\GoogleTranslationDriver;
use App\Services\Translation\Drivers\NullTranslationDriver;
use Illuminate\Support\Facades\Cache;

class TranslationManager
{
    /**
     * Cache key for settings
     */
    protected const CACHE_KEY = 'translation_settings_cache';

    /**
     * Get settings map
     */
    protected function getSettings(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            return Setting::whereIn('setting_key', [
                'translation_active_service',
                'deepl_auth_key',
                'deepl_endpoint_type',
                'google_translate_api_key',
            ])->pluck('setting_value', 'setting_key')->toArray();
        });
    }

    /**
     * Clear cached settings.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Update translation settings and flush cache.
     */
    public function updateSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $dbKey = match ($key) {
                'active_service' => 'translation_active_service',
                'deepl_api_key', 'deepl_auth_key' => 'deepl_auth_key',
                'deepl_endpoint_type' => 'deepl_endpoint_type',
                'google_api_key', 'google_translate_api_key' => 'google_translate_api_key',
                default => $key,
            };

            Setting::updateOrCreate(
                ['setting_key' => $dbKey],
                ['setting_value' => (string) $value]
            );
        }

        $this->clearCache();
    }

    /**
     * Get the currently active translation provider name ('deepl', 'google', or 'none').
     */
    public function getActiveServiceName(): string
    {
        $settings = $this->getSettings();
        return strtolower(trim($settings['translation_active_service'] ?? 'none'));
    }

    /**
     * Check if an active, configured driver is ready.
     */
    public function isConfigured(): bool
    {
        $service = $this->getActiveServiceName();
        $settings = $this->getSettings();

        if ($service === 'deepl') {
            return ! empty($settings['deepl_auth_key']);
        }

        if ($service === 'google') {
            return ! empty($settings['google_translate_api_key']);
        }

        return false;
    }

    /**
     * Resolve a translation driver by name or active default.
     */
    public function driver(?string $name = null): TranslationDriverInterface
    {
        $service = $name ? strtolower(trim($name)) : $this->getActiveServiceName();
        $settings = $this->getSettings();

        return match ($service) {
            'deepl' => new DeepLTranslationDriver(
                $settings['deepl_auth_key'] ?? '',
                $settings['deepl_endpoint_type'] ?? 'free'
            ),
            'google' => new GoogleTranslationDriver(
                $settings['google_translate_api_key'] ?? ''
            ),
            default => new NullTranslationDriver(),
        };
    }

    /**
     * Translate text via the currently active driver.
     *
     * @param string|array<string, string> $text
     * @param string $targetLocale
     * @param string $sourceLocale
     * @param bool $isHtml
     * @return string|array<string, string>
     */
    public function translate(string|array $text, string $targetLocale, string $sourceLocale = 'en', bool $isHtml = false): string|array
    {
        return $this->driver()->translate($text, $targetLocale, $sourceLocale, $isHtml);
    }
}
