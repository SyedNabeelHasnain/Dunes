<?php

namespace App\Services\Governance;

use App\Models\ChangeApproval;
use App\Models\RedirectRule;
use App\Models\SeoTemplate;
use App\Models\Setting;
use App\Models\User;
use App\Services\RedirectManagerService;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

/**
 * Handles proposal, peer review, rejection, and transactional execution of
 * high-impact CMS changes (robots.txt, redirects, global SEO, headers, privacy).
 */
class ApprovalWorkflowService
{
    public function __construct(
        protected ConfigAuditService $audit,
    ) {}

    public function propose(
        string $section,
        string $targetKey,
        string $summary,
        array $payload,
        ?array $previousPayload = null,
        ?User $requester = null,
    ): ChangeApproval {
        $approval = ChangeApproval::create([
            'section' => $section,
            'target_key' => $targetKey,
            'summary' => $summary,
            'payload' => $payload,
            'previous_payload' => $previousPayload,
            'status' => 'pending_review',
            'requested_by' => $requester?->id,
        ]);

        $this->audit->log(
            section: $section,
            action: 'proposed',
            settingKey: $targetKey,
            oldValue: $previousPayload,
            newValue: $payload,
            entityType: ChangeApproval::class,
            entityId: $approval->id,
            summary: "Approval requested (#{$approval->id}): {$summary}",
            actor: $requester,
        );

        return $approval;
    }

    public function approveAndApply(ChangeApproval $approval, User $reviewer, ?string $notes = null): ChangeApproval
    {
        if (! $reviewer->hasAnyPermission(['governance.approve', '*'])) {
            throw new InvalidArgumentException('Reviewer lacks approval permission.');
        }

        if ($approval->status !== 'pending_review') {
            throw new InvalidArgumentException("Approval #{$approval->id} is not pending review.");
        }

        $payload = is_array($approval->payload) ? $approval->payload : [];

        match ($approval->section) {
            'robots_txt' => $this->applyRobotsTxt($payload),
            'seo_global', 'privacy', 'security', 'tracking' => $this->applySettingsBatch($payload),
            'seo_template' => $this->applySeoTemplate($payload),
            'redirect_rule' => $this->applyRedirectRule($payload),
            default => null,
        };

        $approval->update([
            'status' => 'applied',
            'reviewed_by' => $reviewer->id,
            'review_notes' => $notes,
            'reviewed_at' => now(),
            'applied_at' => now(),
        ]);

        $this->audit->log(
            section: $approval->section,
            action: 'approved',
            settingKey: $approval->target_key,
            oldValue: $approval->previous_payload,
            newValue: $payload,
            entityType: ChangeApproval::class,
            entityId: $approval->id,
            summary: "Approved & applied (#{$approval->id}): {$approval->summary}",
            actor: $reviewer,
        );

        return $approval->fresh();
    }

    public function reject(ChangeApproval $approval, User $reviewer, ?string $notes = null): ChangeApproval
    {
        if (! $reviewer->hasAnyPermission(['governance.approve', '*'])) {
            throw new InvalidArgumentException('Reviewer lacks approval permission.');
        }

        $approval->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'review_notes' => $notes,
            'reviewed_at' => now(),
        ]);

        $this->audit->log(
            section: $approval->section,
            action: 'rejected',
            settingKey: $approval->target_key,
            oldValue: $approval->previous_payload,
            newValue: $approval->payload,
            entityType: ChangeApproval::class,
            entityId: $approval->id,
            summary: "Rejected change (#{$approval->id}): {$approval->summary}".($notes ? " — {$notes}" : ''),
            actor: $reviewer,
        );

        return $approval->fresh();
    }

    private function applyRobotsTxt(array $payload): void
    {
        if (array_key_exists('robots_txt_content', $payload)) {
            Setting::setVal('robots_txt_content', (string) $payload['robots_txt_content']);
        }
        if (array_key_exists('robots_ai_policy', $payload)) {
            Setting::setVal('robots_ai_policy', json_encode($payload['robots_ai_policy']));
        }
        Cache::forget('site_settings_cache');
        app(SettingsService::class)->clearCache();
    }

    private function applySettingsBatch(array $payload): void
    {
        foreach ($payload as $key => $val) {
            if ($key === '_group') {
                continue;
            }
            Setting::setVal((string) $key, is_array($val) ? json_encode($val) : (string) $val);
        }
        app(SettingsService::class)->clearCache();
    }

    private function applySeoTemplate(array $payload): void
    {
        $contentType = (string) ($payload['content_type'] ?? '');
        if ($contentType === '') {
            return;
        }

        SeoTemplate::updateOrCreate(
            ['content_type' => $contentType],
            collect($payload)->except('content_type')->all()
        );
    }

    private function applyRedirectRule(array $payload): void
    {
        $source = RedirectRule::normalizePath((string) ($payload['source_path'] ?? ''));
        $statusCode = (int) ($payload['status_code'] ?? 301);
        $target = $statusCode === 410 ? '' : trim((string) ($payload['target_path'] ?? ''));

        RedirectRule::updateOrCreate(
            ['source_path' => $source, 'is_active' => true],
            [
                'target_path' => $target,
                'status_code' => $statusCode,
                'notes' => $payload['notes'] ?? null,
                'source_type' => $payload['source_type'] ?? 'approved',
                'created_by' => $payload['created_by'] ?? null,
            ]
        );

        app(RedirectManagerService::class)->flushCache();
    }
}
