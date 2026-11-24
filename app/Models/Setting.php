<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['setting_key', 'setting_value', 'description'];

    public static function getVal(string $key, ?string $default = null): ?string
    {
        $val = static::where('setting_key', $key)->value('setting_value');

        return $val !== null ? (string) $val : $default;
    }

    public static function setVal(string $key, ?string $value, ?string $description = null): static
    {
        $attributes = ['setting_value' => $value !== null ? (string) $value : ''];
        if ($description !== null) {
            $attributes['description'] = $description;
        }

        $record = static::updateOrCreate(
            ['setting_key' => $key],
            $attributes
        );

        try {
            \Illuminate\Support\Facades\Cache::forget('site_settings_cache');
            app(\App\Services\SettingsService::class)->clearCache();
        } catch (\Throwable) {
            // Non-blocking in isolated CLI/unit contexts
        }

        return $record;
    }
}
