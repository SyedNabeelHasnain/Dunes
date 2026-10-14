<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WikiArticle extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = [
        'title',
        'subtitle',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $fillable = [
        'pillar_id',
        'slug',
        'title',
        'subtitle',
        'excerpt',
        'content',
        'hero_image',
        'table_of_contents',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'reviewed_by_name',
        'reviewed_by_title',
        'last_reviewed_at',
        'reading_time_minutes',
        'priority',
        'view_count',
        'status',
        'is_featured',
    ];

    protected $casts = [
        'table_of_contents' => 'array',
        'last_reviewed_at' => 'datetime',
        'reading_time_minutes' => 'integer',
        'priority' => 'integer',
        'view_count' => 'integer',
        'is_featured' => 'boolean',
    ];

    public function pillar(): BelongsTo
    {
        return $this->belongsTo(WikiPillar::class, 'pillar_id');
    }

    public function tours(): BelongsToMany
    {
        return $this->belongsToMany(Tour::class, 'wiki_article_tours')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order', 'asc');
    }

    public function glossaryTerms(): HasMany
    {
        return $this->hasMany(WikiGlossaryTerm::class, 'wiki_article_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getHeroImageUrlAttribute(): string
    {
        if (! empty($this->hero_image)) {
            return resolve_tour_image($this->hero_image);
        }

        if ($this->pillar && ! empty($this->pillar->hero_image)) {
            return resolve_tour_image($this->pillar->hero_image);
        }

        return asset('images/desert-safari-poster.avif');
    }

    /**
     * Parse headings from content to generate a clean Table of Contents if not explicitly configured.
     */
    public function getParsedTocAttribute(): array
    {
        if (! empty($this->table_of_contents) && is_array($this->table_of_contents)) {
            return $this->table_of_contents;
        }

        $content = (string) $this->content;
        $toc = [];

        // Match ## Heading 2 or ### Heading 3 in markdown, or <h2> / <h3> in HTML
        if (preg_match_all('/(?:<h([2-3])[^>]*>(.*?)<\/h\1>|^(#{2,3})\s+(.*?)$)/m', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $level = ! empty($match[1]) ? (int) $match[1] : strlen($match[3]);
                $title = strip_tags(! empty($match[2]) ? $match[2] : $match[4]);
                $anchor = \Illuminate\Support\Str::slug($title);
                if (! empty($title)) {
                    $toc[] = [
                        'level' => $level,
                        'title' => trim($title),
                        'anchor' => $anchor,
                    ];
                }
            }
        }

        return $toc;
    }
}
