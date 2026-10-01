<?php

namespace App\Traits;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $activeCodes = Language::getActiveCodes();

        foreach ($translatableFields as $field) {
            if (! isset($data[$field])) {
                continue;
            }

            $val = $data[$field];
            if (is_array($val)) {
                // If English key missing, assign from first available
                if (empty($val['en']) && ! empty($val)) {
                    $firstKey = array_key_first($val);
                    $val['en'] = $val[$firstKey];
                }
                $data[$field] = $val;
            } elseif (is_string($val) && ! empty($val)) {
                // Backward compatibility: single string maps to English default
                $data[$field] = ['en' => $val];
            }
        }

        return $data;
    }
}
