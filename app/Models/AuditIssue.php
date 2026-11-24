<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditIssue extends Model
{
    use HasFactory;

    protected $table = 'audit_issues';

    protected $fillable = [
        'audit_run_id',
        'category',
        'check_key',
        'title',
        'description',
        'severity',
        'status',
        'verification_type',
        'affected_url',
        'evidence',
        'recommended_action',
        'assigned_to',
        'remediated_by',
        'entity_type',
        'entity_id',
        'remediation_notes',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function auditRun(): BelongsTo
    {
        return $this->belongsTo(AuditRun::class, 'audit_run_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
