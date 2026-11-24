<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces Role-Based Access Control (RBAC) across CMS endpoints
 * and blocks deactivated user accounts.
 */
class RequirePermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
        }

        if (isset($user->is_active) && ! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'This staff account has been deactivated.'], 403);
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Your staff account has been deactivated by an administrator.',
            ]);
        }

        if ($permissions !== [] && is_object($user) && method_exists($user, 'hasAnyPermission')) {
            if (! $user->hasAnyPermission($permissions)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Insufficient permissions for this operation.',
                        'required_permissions' => $permissions,
                    ], 403);
                }

                abort(403, 'You do not have permission to access this optimization or governance section.');
            }
        }

        return $next($request);
    }
}
