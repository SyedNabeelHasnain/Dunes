<?php

namespace App\Traits;

trait NormalizesLocalizedInputs
{
    /**
     * Extract string source suitable for slug generation.
     */
    protected function extractSlugSource(mixed $value, string $fallback = 'item'): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            return $value['en'] ?? reset($value) ?? $fallback;
        }

        return $fallback;
    }

    /**
     * Normalize translatable fields in array so they accept either array or string,
     * preserving untouched locales on an existing model when updating.
     */
    protected function normalizeLocalizedData(array $data, array $translatableFields, ?\Illuminate\Database\Eloquent\Model $existingModel = null): array
    {
        foreach ($translatableFields as $field) {
            $arField = $field.'_ar';
            if (! array_key_exists($field, $data)) {
                continue;
            }

            $existing = [];
            if ($existingModel && method_exists($existingModel, 'getTranslations')) {
                $existing = $existingModel->getTranslations($field);
            }

            $val = $data[$field];
            if (is_array($val)) {
                if (array_key_exists($arField, $data) && is_string($data[$arField]) && ! array_key_exists('ar', $val)) {
                    $val['ar'] = $data[$arField];
                }
                $merged = $existing;
                foreach ($val as $loc => $v) {
                    if ($v !== null && $v !== '') {
                        $merged[$loc] = $v;
                    } elseif ($loc !== 'en') {
                        unset($merged[$loc]);
                    }
                }
                $nonEmpty = array_filter($merged, fn ($v) => $v !== null && $v !== '');
                // If English key missing, assign from first available non-empty translation
                if (empty($merged['en']) && ! empty($nonEmpty)) {
                    $firstKey = array_key_first($nonEmpty);
                    $merged['en'] = $nonEmpty[$firstKey];
                }
                $data[$field] = $merged;
            } elseif (is_string($val)) {
                // Backward compatibility: single string updates English (+ optional _ar companion) while preserving other locales
                $normalized = $existing;
                $normalized['en'] = $val;
                if (array_key_exists($arField, $data) && is_string($data[$arField])) {
                    if ($data[$arField] !== '') {
                        $normalized['ar'] = $data[$arField];
                    } else {
                        unset($normalized['ar']);
                    }
                }
                $data[$field] = $normalized;
            }
        }

        return $data;
    }
}
