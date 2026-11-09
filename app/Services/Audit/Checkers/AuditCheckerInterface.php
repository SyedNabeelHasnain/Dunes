<?php

namespace App\Services\Audit\Checkers;

interface AuditCheckerInterface
{
    /**
     * Executes the audit check.
     *
     * @param array $context Optional parameters (e.g. ['single_url' => '...'])
     * @return array Array of issue arrays:
     *   [
     *     'category' => string,
     *     'check_key' => string,
     *     'title' => string,
     *     'description' => string,
     *     'severity' => 'critical'|'high'|'medium'|'low'|'info',
     *     'verification_type' => 'automated'|'partial_automated'|'manual_review',
     *     'affected_url' => ?string,
     *     'evidence' => string,
     *     'recommended_action' => string,
     *   ]
     */
    public function run(array $context = []): array;
}
