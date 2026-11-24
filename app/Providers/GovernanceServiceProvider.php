<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Governance\ConfigAuditService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class GovernanceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::before(function ($user, string $ability) {
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return true;
            }

            return null;
        });

        foreach (array_keys(User::availablePermissions()) as $permission) {
            Gate::define($permission, function ($user) use ($permission): bool {
                return method_exists($user, 'hasPermission') && $user->hasPermission($permission);
            });
        }

        Event::listen(Login::class, function (Login $event): void {
            $user = $event->user;
            if (! $user || ! is_object($user)) {
                return;
            }

            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'last_login_at')) {
                    $user->forceFill(['last_login_at' => now()])->saveQuietly();
                }

                app(ConfigAuditService::class)->log(
                    section: 'auth',
                    action: 'login',
                    settingKey: 'cms_session',
                    oldValue: null,
                    newValue: ['email' => $user->email ?? null],
                    entityType: $user::class,
                    entityId: $user->getAuthIdentifier(),
                    summary: 'CMS user signed in',
                    actor: $user instanceof \App\Models\User ? $user : null,
                );
            } catch (\Throwable) {
                // Non-blocking audit telemetry on login
            }
        });
    }
}
