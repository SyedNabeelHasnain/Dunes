<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'direction',
        'flag',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function ($language) {
            // Normalize locale code to lowercase (e.g. 'en', 'ar', 'ru')
            $language->code = strtolower(trim($language->code));
            $language->direction = strtolower(trim($language->direction ?? 'ltr'));

            // English must remain active and default
            if ($language->code === 'en') {
                $language->is_active = true;
                $language->is_default = true;
            }

            // If this language is set as default, unset any other default
            if ($language->is_default && $language->code !== 'en') {
                // English is the immutable root default
                $language->is_default = false;
            }
        });

        static::saved(function () {
            self::clearLanguageCache();
        });

        static::deleting(function ($language) {
            // Guard: English cannot be deleted under any circumstances
            if ($language->code === 'en' || $language->is_default) {
                throw new \InvalidArgumentException('The default language (English) is protected and cannot be deleted.');
            }
        });

        static::deleted(function () {
            self::clearLanguageCache();
        });
    }

    /**
     * Check if language layout direction is Right-to-Left (RTL).
     */
    public function isRtl(): bool
    {
        return $this->direction === 'rtl';
    }

    /**
     * Get the default system language (English).
     */
    public static function getDefault(): ?self
    {
        return Cache::rememberForever('language_default', function () {
            return self::where('code', 'en')->first()
                ?? self::where('is_default', true)->first();
        });
    }

    /**
     * Get all active languages ordered by sort_order.
     *
     * @return Collection<int, Language>
     */
    public static function getActive(): Collection
    {
        return Cache::rememberForever('languages_active', function () {
            return self::where('is_active', true)
                ->orderBy('is_default', 'desc')
                ->orderBy('sort_order', 'asc')
                ->orderBy('name', 'asc')
                ->get();
        });
    }

    /**
     * Get array of all active locale codes (e.g. ['en', 'ar']).
     *
     * @return array<int, string>
     */
    public static function getActiveCodes(): array
    {
        return self::getActive()->pluck('code')->toArray();
    }

    /**
     * Clear all cached language lookups.
     */
    public static function clearLanguageCache(): void
    {
        Cache::forget('language_default');
        Cache::forget('languages_active');
        Cache::forget('all_languages');
    }
}
