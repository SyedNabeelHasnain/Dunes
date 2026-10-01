<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Itinerary extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = [
        'title',
        'description',
    ];

    protected $fillable = ['tour_id', 'time', 'title', 'description', 'icon', 'duration', 'priority'];

    protected $casts = [
        'priority' => 'integer',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }
}
