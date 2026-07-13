<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'name', 'description', 'icon', 'default_price', 'status', 'priority'];

    protected $casts = [
        'default_price' => 'float',
        'priority' => 'integer'
    ];

    public function tours()
    {
        return $this->belongsToMany(Tour::class, 'tour_addons')
            ->withPivot('price');
    }
}
