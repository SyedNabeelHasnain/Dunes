<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tier extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = [
        'name',
        'display_name',
        'description',
    ];

    protected $fillable = [
        'slug', 'name', 'display_name', 'description', 'guest_count', 'price_type', 'icon', 'badge', 'color',
        'is_popular', 'priority', 'status',
    ];

    protected $casts = [
        'is_popular' => 'boolean',
        'priority' => 'integer',
        'guest_count' => 'integer',
    ];

    public const UNIT_PRICE_TYPES = [
        'per buggy',
        'per vehicle',
        'per_vehicle',
        'per car',
        'per group',
        'per_group',
        'private',
        'per tent',
        'per package',
    ];

    public function tours()
    {
        return $this->belongsToMany(Tour::class, 'tour_tiers')
            ->withPivot('price', 'old_price', 'price_type', 'guest_count');
    }

    public static function normalizePriceType(?string $priceType, ?Tour $tour = null): string
    {
        $normalized = strtolower(trim((string) ($priceType ?: 'per person')));

        if ($tour) {
            $slug = strtolower((string) $tour->slug);
            if ((int) $tour->id === 3 || str_contains($slug, 'overnight')) {
                return 'per tent';
            }
            if ((int) $tour->id === 8 || str_contains($slug, 'buggy')) {
                return 'per buggy';
            }
        }

        return $normalized;
    }

    public static function isUnitPriceType(?string $priceType, ?Tour $tour = null): bool
    {
        $normalized = static::normalizePriceType($priceType, $tour);

        return in_array($normalized, self::UNIT_PRICE_TYPES, true);
    }

    public static function resolveUnitCapacity(?object $pricing = null, ?self $tier = null, ?string $priceType = null): int
    {
        if ($pricing instanceof self && $tier === null) {
            $tier = $pricing;
            $pricing = null;
        }

        $capacity = (int) ($pricing->guest_count ?? ($tier?->pivot?->guest_count ?? ($tier?->guest_count ?? 0)));
        if ($capacity > 0) {
            return $capacity;
        }

        $priceType = $priceType ?: ($pricing->price_type ?? ($tier?->pivot?->price_type ?? ($tier?->price_type ?? null)));

        $tierName = (string) ($tier?->name ?? ($pricing->name ?? ''));
        if ($tierName !== '') {
            if (preg_match('/(7|seven)/i', $tierName)) {
                return 7;
            }
            if (preg_match('/(6|six)/i', $tierName)) {
                return 6;
            }
            if (preg_match('/(4|four)/i', $tierName)) {
                return 4;
            }
            if (preg_match('/(2|two)/i', $tierName)) {
                return 2;
            }
            if (preg_match('/(1|single|solo)/i', $tierName)) {
                return 1;
            }
        }

        $norm = strtolower(trim((string) $priceType));
        if (in_array($norm, ['per vehicle', 'per_vehicle', 'per car', 'private'], true)) {
            return 6;
        }

        if ($norm === 'per tent' || stripos($tierName, 'tent') !== false) {
            return 2;
        }

        return 1;
    }
}
