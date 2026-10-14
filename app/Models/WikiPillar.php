<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WikiPillar extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = [
        'name',
        'headline',
        'description',
    ];

    protected $fillable = [
        'slug',
        'name',
        'headline',
        'description',
        'icon',
        'hero_image',
        'priority',
        'status',
    ];

    protected $casts = [
        'priority' => 'integer',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(WikiArticle::class, 'pillar_id')
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'asc');
    }

    public function activeArticles(): HasMany
    {
        return $this->hasMany(WikiArticle::class, 'pillar_id')
            ->where('status', 'published')
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'asc');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getHeroImageUrlAttribute(): string
    {
        if (! empty($this->hero_image)) {
            return resolve_tour_image($this->hero_image);
        }

        return asset('images/desert-safari-poster.avif');
    }
}
