<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMetadata extends Model
{
    protected $table = 'seo_metadata';

    protected $fillable = [
        'entity_type',
        'entity_id',
        'focus_keyword',
        'secondary_keywords',
        'search_intent',
        'canonical_override',
        'robots_directive',
        'og_title',
        'og_description',
        'og_image',
        'twitter_card',
        'schema_type_override',
        'schema_json_override',
        'include_in_sitemap',
        'breadcrumb_label',
        'editorial_status',
        'reviewed_by',
        'reviewed_by_name',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'review_notes',
        'stale_after_days',
    ];

    protected $casts = [
        'secondary_keywords' => 'array',
        'include_in_sitemap' => 'boolean',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'stale_after_days' => 'integer',
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'entity_type', 'entity_id');
    }
}
