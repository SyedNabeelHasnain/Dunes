<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tier extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'name', 'display_name', 'description', 'icon', 'badge', 'color',
        'is_popular', 'priority', 'status'
    ];

    protected $casts = [
        'is_popular' => 'boolean',
        'priority' => 'integer'
    ];

    public function tours()
    {
        return $this->belongsToMany(Tour::class, 'tour_tiers')
            ->withPivot('price', 'old_price', 'price_type');
    }
}
