<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiCitationObservation extends Model
{
    protected $table = 'ai_citation_observations';

    protected $fillable = [
        'platform',
        'prompt_query',
        'cited_url',
        'brand_mentioned',
        'citation_position',
        'sentiment',
        'notes',
        'observed_at',
        'recorded_by',
    ];

    protected $casts = [
        'brand_mentioned' => 'boolean',
        'citation_position' => 'integer',
        'observed_at' => 'datetime',
    ];

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
