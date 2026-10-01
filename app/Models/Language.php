<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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
     * Fallback collection with default English language in memory.
     *
     * @return Collection<int, Language>
     */
    public static function fallbackCollection(): Collection
    {
        $english = new self([
            'id' => 1,
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'direction' => 'ltr',
            'flag' => 'gb',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        return new Collection([$english]);
    }

    /**
     * Fallback default English language instance.
     */
    public static function fallbackDefault(): self
    {
        return new self([
            'id' => 1,
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'direction' => 'ltr',
            'flag' => 'gb',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    /**
     * Get the default system language (English).
     */
    public static function getDefault(): ?self
    {
        try {
            $active = self::getActive();

            return $active->firstWhere('is_default', true)
                ?? $active->firstWhere('code', 'en')
                ?? $active->first()
                ?? self::fallbackDefault();
        } catch (\Throwable $e) {
            return self::fallbackDefault();
        }
    }

    /**
     * Get all active languages ordered by sort_order.
     *
     * @return Collection<int, Language>
     */
    public static function getActive(): Collection
    {
        try {
            // Self-healing: if languages table does not exist yet, trigger migration safely
            if (! Schema::hasTable('languages')) {
                try {
                    Artisan::call('migrate', ['--force' => true]);
                } catch (\Throwable $migrationError) {
                    Log::warning('Self-healing migration failed: '.$migrationError->getMessage());
                }

                if (! Schema::hasTable('languages')) {
                    return self::fallbackCollection();
                }
            }

            $rows = Cache::rememberForever('languages_active_data', function () {
                return self::where('is_active', true)
                    ->orderBy('is_default', 'desc')
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('name', 'asc')
                    ->get()
                    ->toArray();
            });

            if (empty($rows) || ! is_array($rows)) {
                return self::fallbackCollection();
            }

            $models = array_map(function ($row) {
                $lang = new self();
                $lang->forceFill($row);
                $lang->exists = true;

                return $lang;
            }, $rows);

            return new Collection($models);
        } catch (\Throwable $e) {
            Log::warning('Language::getActive() query failed: '.$e->getMessage());

            return self::fallbackCollection();
        }
    }

    /**
     * Get array of all active locale codes (e.g. ['en', 'ar']).
     *
     * @return array<int, string>
     */
    public static function getActiveCodes(): array
    {
        try {
            $codes = self::getActive()->pluck('code')->all();

            return ! empty($codes) ? $codes : ['en'];
        } catch (\Throwable $e) {
            return ['en'];
        }
    }

    /**
     * Clear all cached language lookups.
     */
    public static function clearLanguageCache(): void
    {
        try {
            Cache::forget('language_default');
            Cache::forget('languages_active');
            Cache::forget('languages_active_data');
            Cache::forget('all_languages');
        } catch (\Throwable $e) {
            // Silently ignore cache clearing issues during bootstrap/migration
        }
    }
}
