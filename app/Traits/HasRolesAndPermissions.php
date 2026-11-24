<?php

namespace App\Traits;

trait HasRolesAndPermissions
{
    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_SEO_MANAGER = 'seo_manager';
    public const ROLE_EDITOR = 'editor';
    public const ROLE_DEVELOPER = 'developer';
    public const ROLE_VIEWER = 'viewer';

    public static function roleDefinitions(): array
    {
        return [
            'super_admin' => [
                'label' => 'Super Administrator',
                'description' => 'Unrestricted platform governance, security, payments, RBAC, and approval authority.',
                'permissions' => ['*'],
            ],
            'seo_manager' => [
                'label' => 'SEO & Growth Manager',
                'description' => 'Manages SEO templates, item SEO overrides, keywords, sitemaps, redirects, AI visibility, and audits; sensitive global changes require approval.',
                'permissions' => [
                    'seo.view',
                    'seo.manage',
                    'seo.templates',
                    'seo.item_edit',
                    'keywords.manage',
                    'schema.manage',
                    'redirects.manage',
                    'robots.request_approval',
                    'content.edit',
                    'content.submit_review',
                    'content.publish',
                    'media.manage',
                    'ai_visibility.manage',
                    'analytics.view',
                    'audit.view',
                    'audit.page_view',
                    'audit.run',
                    'audit.manage',
                ],
            ],
            'editor' => [
                'label' => 'Content & Editorial Author',
                'description' => 'Authors and edits tours, blogs, pages, and wiki articles, edits item SEO metadata, submits work for editorial review, and runs single-page audits.',
                'permissions' => [
                    'seo.view',
                    'seo.item_edit',
                    'content.edit',
                    'content.submit_review',
                    'media.manage',
                    'audit.view',
                    'audit.page_view',
                    'analytics.view',
                ],
            ],
            'developer' => [
                'label' => 'Technical / DevOps Engineer',
                'description' => 'Manages security headers, firewall, CWV performance telemetry, system health, redirects, schema validation, and sitewide audits.',
                'permissions' => [
                    'seo.view',
                    'security.manage',
                    'performance.manage',
                    'system_health.view',
                    'schema.manage',
                    'redirects.manage',
                    'robots.request_approval',
                    'audit.view',
                    'audit.page_view',
                    'audit.run',
                    'audit.manage',
                    'analytics.view',
                ],
            ],
            'viewer' => [
                'label' => 'Auditor / Read-Only Viewer',
                'description' => 'Read-only access to dashboards, SEO matrix, analytics, CWV telemetry, audit reports, and audit logs.',
                'permissions' => [
                    'seo.view',
                    'audit.view',
                    'audit.page_view',
                    'analytics.view',
                    'system_health.view',
                ],
            ],
        ];
    }

    public static function availablePermissions(): array
    {
        return [
            'governance.rbac' => 'Manage CMS staff roles & permissions',
            'governance.approve' => 'Approve or reject pending high-impact CMS changes',
            'governance.audit_logs' => 'View immutable configuration audit logs',
            'seo.view' => 'View SEO settings, templates, and keyword maps',
            'seo.manage' => 'Edit global & section SEO settings',
            'seo.templates' => 'Configure content-type SEO inheritance templates',
            'seo.item_edit' => 'Edit per-item SEO, canonical, and OpenGraph fields',
            'keywords.manage' => 'Manage focus keywords & cannibalization resolution',
            'schema.manage' => 'Configure JSON-LD structured data templates',
            'redirects.manage' => 'Create, edit, test, and delete dynamic redirect rules',
            'robots.request_approval' => 'Propose robots.txt updates for approval',
            'robots.publish' => 'Directly publish robots.txt directives',
            'content.edit' => 'Create & edit tours, blog posts, pages, and wiki articles',
            'content.submit_review' => 'Submit content revisions for editorial review',
            'content.publish' => 'Approve & publish editorial content',
            'media.manage' => 'Upload & optimize media library assets',
            'privacy.manage' => 'Manage cookie consent categories, policies & logs',
            'security.manage' => 'Manage firewall, headers & application security',
            'performance.manage' => 'View & manage Core Web Vitals RUM budgets',
            'ai_visibility.manage' => 'Manage /llms.txt, AI bot rules & citation observations',
            'audit.view' => 'View audit dashboard & history',
            'audit.page_view' => 'Run and inspect page-level audits inside editors',
            'audit.run' => 'Trigger sitewide & incremental audits',
            'audit.manage' => 'Assign, resolve, and export audit issues',
            'analytics.view' => 'View traffic, conversions, heatmaps & visitor telemetry',
            'system_health.view' => 'Inspect queue jobs, failed jobs, cron & system logs',
        ];
    }

    public function normalizedRole(): string
    {
        $role = strtolower(trim((string) ($this->role ?? 'super_admin')));

        return array_key_exists($role, static::roleDefinitions()) ? $role : 'super_admin';
    }

    public function roleLabel(): string
    {
        $defs = static::roleDefinitions();

        return $defs[$this->normalizedRole()]['label'] ?? ucfirst($this->normalizedRole());
    }

    public function isSuperAdmin(): bool
    {
        return $this->normalizedRole() === 'super_admin' && ($this->is_active ?? true);
    }

    /**
     * Effective permissions resolved from role defaults + explicit per-user overrides.
     *
     * @return list<string>
     */
    public function effectivePermissions(): array
    {
        if (isset($this->is_active) && ! $this->is_active) {
            return [];
        }

        $defs = static::roleDefinitions();
        $base = $defs[$this->normalizedRole()]['permissions'] ?? [];
        $custom = is_array($this->permissions) ? $this->permissions : [];

        return array_values(array_unique(array_merge($base, $custom)));
    }

    public function hasPermission(string $permission): bool
    {
        if (isset($this->is_active) && ! $this->is_active) {
            return false;
        }

        $perms = $this->effectivePermissions();

        return in_array('*', $perms, true) || in_array($permission, $perms, true);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission((string) $permission)) {
                return true;
            }
        }

        return false;
    }
}
