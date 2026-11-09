<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditRun extends Model
{
    use HasFactory;

    protected $table = 'audit_runs';

    protected $fillable = [
        'scan_type',
        'status',
        'health_score',
        'category_scores',
        'total_issues_count',
        'critical_count',
        'high_count',
        'medium_count',
        'low_count',
        'info_count',
        'scanned_urls_count',
        'duration_ms',
        'triggered_by',
    ];

    protected $casts = [
        'category_scores' => 'array',
        'health_score' => 'integer',
        'total_issues_count' => 'integer',
        'critical_count' => 'integer',
        'high_count' => 'integer',
        'medium_count' => 'integer',
        'low_count' => 'integer',
        'info_count' => 'integer',
        'scanned_urls_count' => 'integer',
        'duration_ms' => 'integer',
    ];

    public function issues(): HasMany
    {
        return $this->hasMany(AuditIssue::class, 'audit_run_id');
    }
}
