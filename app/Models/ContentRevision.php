<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentRevision extends Model
{
    public $timestamps = false;

    protected $table = 'content_revisions';

    protected $fillable = [
        'entity_type',
        'entity_id',
        'version_number',
        'title',
        'snapshot',
        'change_summary',
        'editorial_status',
        'created_by',
        'created_by_name',
        'created_at',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'snapshot' => 'array',
        'created_at' => 'datetime',
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'entity_type', 'entity_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
