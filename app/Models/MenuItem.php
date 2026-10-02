<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = [
        'label',
    ];

    protected $fillable = [
        'location',
        'label',
        'url',
        'route_name',
        'target',
        'icon',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order', 'asc');
    }

    public function scopeByLocation($query, string $location)
    {
        return $query->where('location', $location)->where('is_active', true)->orderBy('order', 'asc');
    }

    public function getResolvedUrlAttribute(): string
    {
        if (!empty($this->route_name)) {
            try {
                return route($this->route_name);
            } catch (\Throwable $e) {
                // fallback to url attribute
            }
        }

        if (empty($this->url)) {
            return '/';
        }

        if (str_starts_with($this->url, 'http://') || str_starts_with($this->url, 'https://')) {
            return $this->url;
        }

        return url($this->url);
    }
}
