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
        'slug', 'name', 'display_name', 'description', 'icon', 'badge', 'color',
        'is_popular', 'priority', 'status',
    ];

    protected $casts = [
        'is_popular' => 'boolean',
        'priority' => 'integer',
    ];

    public function tours()
    {
        return $this->belongsToMany(Tour::class, 'tour_tiers')
            ->withPivot('price', 'old_price', 'price_type');
    }
}
