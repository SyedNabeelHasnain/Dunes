<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyntheticTestStep extends Model
{
    protected $fillable = [
        'synthetic_test_run_id',
        'flow',
        'step_order',
        'step_key',
        'title',
        'http_method',
        'endpoint',
        'http_status',
        'status',
        'duration_ms',
        'assertion_summary',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'step_order' => 'integer',
        'http_status' => 'integer',
        'duration_ms' => 'integer',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(SyntheticTestRun::class, 'synthetic_test_run_id');
    }
}
