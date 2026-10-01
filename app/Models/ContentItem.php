<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentItem extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = [
        'title',
        'description',
    ];

    protected $fillable = ['type', 'icon', 'title', 'description', 'priority'];

    protected $casts = [
        'priority' => 'integer',
    ];

    public function tours()
    {
        return $this->belongsToMany(Tour::class, 'tour_content', 'content_id', 'tour_id')
            ->withPivot('tier_id');
    }
}
