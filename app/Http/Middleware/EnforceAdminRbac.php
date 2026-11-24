<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Central RBAC enforcement across all CMS /admin/* routes.
 * Default users with 'super_admin' pass immediately; narrower roles
 * ('seo_manager', 'editor', 'developer', 'viewer') are restricted
 * to their least-privilege domain boundaries.
 */
class EnforceAdminRbac
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (isset($user->is_active) && ! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Account deactivated.'], 403);
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Your staff account has been deactivated.',
            ]);
        }

        if (is_object($user) && (! method_exists($user, 'isSuperAdmin') || $user->isSuperAdmin())) {
            return $next($request);
        }

        $path = trim($request->path(), '/');
        $isMutation = ! $request->isMethodSafe();

        // Viewer role: strict read-only and blocked from sensitive config/RBAC/firewall modules.
        if ($user->normalizedRole() === 'viewer' && $isMutation && $path !== 'admin/logout') {
            return $this->deny($request, 'Read-only Viewer accounts cannot modify CMS data or settings.');
        }

        $rules = [
            'admin/governance/users*' => ['governance.rbac'],
            'admin/governance/approvals/*' => ['governance.approve'],
            'admin/governance/logs/*' => ['governance.audit_logs'],
            'admin/security*' => ['security.manage'],
            'admin/settings/privacy*' => ['privacy.manage'],
            'admin/settings/mail*' => ['security.manage'],
            'admin/settings/currency*' => ['seo.manage', 'security.manage'],
            'admin/settings/general*' => ['security.manage', 'seo.manage'],
            'admin/settings/google*' => ['seo.manage', 'security.manage'],
            'admin/settings/meta*' => ['seo.manage', 'security.manage'],
            'admin/settings/seo-templates*' => ['seo.templates'],
            'admin/settings/robots*' => ['robots.request_approval', 'robots.publish'],
            'admin/settings/keywords*' => ['keywords.manage', 'seo.manage'],
            'admin/settings/redirects*' => ['redirects.manage'],
            'admin/redirects*' => ['redirects.manage'],
            'admin/audit/page*' => ['audit.page_view', 'audit.run'],
            'admin/audit/run*' => ['audit.run'],
            'admin/audit/issues*' => ['audit.manage'],
            'admin/audit/export*' => ['audit.manage', 'audit.view'],
            'admin/audit*' => ['audit.view', 'audit.run'],
            'admin/health*' => ['system_health.view', 'performance.manage'],
            'admin/ai-visibility*' => ['ai_visibility.manage', 'seo.view'],
        ];

        foreach ($rules as $pattern => $requiredPermissions) {
            if ($request->is($pattern) && ! $user->hasAnyPermission($requiredPermissions)) {
                return $this->deny($request, 'Your role does not grant access to this CMS capability.');
            }
        }

        if ($request->is('admin/settings/update') || ($path === 'admin/settings' && $isMutation)) {
            if (! $user->hasAnyPermission(['seo.manage', 'security.manage', 'privacy.manage'])) {
                return $this->deny($request, 'Only authorized administrators or SEO/security leads may update platform settings.');
            }
        }

        return $next($request);
    }

    private function deny(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        abort(403, $message);
    }
}
