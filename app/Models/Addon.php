<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = [
        'name',
        'description',
    ];

    protected $fillable = ['slug', 'name', 'description', 'icon', 'default_price', 'status', 'priority'];

    protected $casts = [
        'default_price' => 'float',
        'priority' => 'integer',
    ];

    public function getPriceAttribute()
    {
        return $this->attributes['default_price'] ?? 0;
    }

    public function tours()
    {
        return $this->belongsToMany(Tour::class, 'tour_addons')
            ->withPivot('price');
    }
}
