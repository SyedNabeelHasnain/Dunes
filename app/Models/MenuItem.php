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
        $rawUrl = trim((string) ($this->url ?? ''));

        // Normalize legacy or broken menu item paths to canonical targets
        $legacyMap = [
            '/luxury-vip-desert-safari-dubai' => '/evening-desert-safari-dubai',
            '/vip-desert-safari-dubai' => '/evening-desert-safari-dubai',
            '/cancellation-policy' => '/cancellation-refund-policy',
            '/refund-policy' => '/cancellation-refund-policy',
            '/terms' => '/terms-condition',
            '/terms-and-conditions' => '/terms-condition',
            '/privacy' => '/privacy-policy',
            '/cookies' => '/cookie-policy',
            '/custom-safari' => '/build-your-own-safari',
            '/pricing-guide' => '/rate-card',
        ];
        if (isset($legacyMap[$rawUrl])) {
            $rawUrl = $legacyMap[$rawUrl];
        }

        if (! empty($this->route_name)) {
            try {
                if ($this->route_name === 'tours.show' && ! empty($rawUrl)) {
                    $slug = ltrim($rawUrl, '/');

                    return localized_route('tours.show', $slug);
                }

                return localized_route($this->route_name);
            } catch (\Throwable $e) {
                // fallback to url attribute
            }
        }

        if (empty($rawUrl) || $rawUrl === '/') {
            return localized_route('home');
        }

        if (str_starts_with($rawUrl, 'http://') || str_starts_with($rawUrl, 'https://')) {
            return $rawUrl;
        }

        $locale = app()->getLocale();
        $cleanPath = '/'.ltrim($rawUrl, '/');
        if ($locale && $locale !== 'en') {
            return url('/'.$locale.$cleanPath);
        }

        return url($cleanPath);
    }
}
