<?php

namespace App\Traits;

use Illuminate\Support\Facades\App;

trait HasTranslations
{
    /**
     * Check if an attribute is translatable.
     */
    public function isTranslatableAttribute(string $key): bool
    {
        return property_exists($this, 'translatable') && is_array($this->translatable) && in_array($key, $this->translatable, true);
    }

    /**
     * Get translatable attribute names.
     */
    public function getTranslatableAttributes(): array
    {
        return property_exists($this, 'translatable') && is_array($this->translatable) ? $this->translatable : [];
    }

    /**
     * Get all translations array for a specific attribute: ['en' => '...', 'ar' => '...'].
     */
    public function getTranslations(string $key): array
    {
        $value = $this->attributes[$key] ?? null;

        if (empty($value)) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) {
                $decoded = json_decode($trimmed, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }

            // Legacy non-JSON string: treat as English default translation
            return ['en' => $value];
        }

        return [];
    }

    /**
     * Get translation for a specific locale, or current app locale if not specified.
     */
    public function getTranslation(string $key, ?string $locale = null, bool $useFallback = true): ?string
    {
        $translations = $this->getTranslations($key);

        if (empty($translations)) {
            return null;
        }

        $targetLocale = $locale ?: (App::getLocale() ?: config('app.fallback_locale', 'en'));

        if (isset($translations[$targetLocale]) && $translations[$targetLocale] !== '' && $translations[$targetLocale] !== null) {
            return $translations[$targetLocale];
        }

        if ($useFallback) {
            $fallbackLocale = config('app.fallback_locale', 'en');
            if ($targetLocale !== $fallbackLocale && isset($translations[$fallbackLocale]) && $translations[$fallbackLocale] !== '' && $translations[$fallbackLocale] !== null) {
                return $translations[$fallbackLocale];
            }

            // Return first available non-empty translation
            foreach ($translations as $val) {
                if ($val !== '' && $val !== null) {
                    return $val;
                }
            }
        }

        return null;
    }

    /**
     * Check if model has translation for a given attribute and locale.
     */
    public function hasTranslation(string $key, ?string $locale = null): bool
    {
        $translations = $this->getTranslations($key);
        $targetLocale = $locale ?: (App::getLocale() ?: 'en');

        return ! empty($translations[$targetLocale]);
    }

    /**
     * Set a single translation for a specific locale.
     */
    public function setTranslation(string $key, string $locale, ?string $value): self
    {
        if (! $this->isTranslatableAttribute($key)) {
            $this->attributes[$key] = $value;

            return $this;
        }

        $translations = $this->getTranslations($key);
        if ($value === null || $value === '') {
            unset($translations[$locale]);
        } else {
            $translations[$locale] = $value;
        }

        $this->attributes[$key] = json_encode($translations, JSON_UNESCAPED_UNICODE);

        return $this;
    }

    /**
     * Set all translations for an attribute: ['en' => '...', 'ar' => '...'].
     */
    public function setTranslations(string $key, array $translations): self
    {
        if (! $this->isTranslatableAttribute($key)) {
            $this->attributes[$key] = json_encode($translations, JSON_UNESCAPED_UNICODE);

            return $this;
        }

        // Clean empty entries
        $cleaned = [];
        foreach ($translations as $locale => $val) {
            if ($val !== null && $val !== '') {
                $cleaned[$locale] = is_string($val) ? trim($val) : $val;
            }
        }

        $this->attributes[$key] = json_encode($cleaned, JSON_UNESCAPED_UNICODE);

        return $this;
    }

    /**
     * Override getAttribute to automatically return the localized string for translatable fields.
     */
    public function getAttribute($key)
    {
        if ($this->isTranslatableAttribute($key)) {
            return $this->getTranslation($key);
        }

        return parent::getAttribute($key);
    }

    /**
     * Override setAttribute to intelligently handle arrays and localized strings.
     */
    public function setAttribute($key, $value)
    {
        if ($this->isTranslatableAttribute($key)) {
            if (is_array($value)) {
                return $this->setTranslations($key, $value);
            }

            if (is_string($value)) {
                $trimmed = trim($value);
                if (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) {
                    $decoded = json_decode($trimmed, true);
                    if (is_array($decoded)) {
                        $this->attributes[$key] = $trimmed;

                        return $this;
                    }
                }

                // If setting a raw string on a translatable attribute:
                $locale = App::getLocale() ?: config('app.fallback_locale', 'en');

                return $this->setTranslation($key, $locale, $value);
            }

            if ($value === null) {
                $this->attributes[$key] = null;

                return $this;
            }
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Get raw translations dictionary across all translatable attributes.
     * Useful for CMS forms to prefill inputs across language tabs.
     */
    public function getAllTranslations(): array
    {
        $result = [];
        foreach ($this->getTranslatableAttributes() as $attribute) {
            $result[$attribute] = $this->getTranslations($attribute);
        }

        return $result;
    }
}
