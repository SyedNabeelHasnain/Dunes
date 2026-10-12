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
     * Normalize translatable fields in array so they accept either array or string.
     */
    protected function normalizeLocalizedData(array $data, array $translatableFields): array
    {
        foreach ($translatableFields as $field) {
            $arField = $field.'_ar';
            if (! array_key_exists($field, $data)) {
                continue;
            }

            $val = $data[$field];
            if (is_array($val)) {
                if (! empty($data[$arField]) && is_string($data[$arField]) && empty($val['ar'])) {
                    $val['ar'] = $data[$arField];
                }
                $nonEmpty = array_filter($val, fn ($v) => $v !== null && $v !== '');
                // If English key missing, assign from first available non-empty translation
                if (empty($val['en']) && ! empty($nonEmpty)) {
                    $firstKey = array_key_first($nonEmpty);
                    $val['en'] = $nonEmpty[$firstKey];
                }
                $data[$field] = $val;
            } elseif (is_string($val)) {
                // Backward compatibility: single string maps to English default (+ optional _ar companion)
                $normalized = ['en' => $val];
                if (! empty($data[$arField]) && is_string($data[$arField])) {
                    $normalized['ar'] = $data[$arField];
                }
                $data[$field] = $normalized;
            }
        }

        return $data;
    }
}
