<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (file_exists(base_path('../public_html'))) {
            $this->app->usePublicPath(base_path('../public_html'));
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ensure primary admin email in live DB matches dunesdiscovery85@gmail.com
        try {
            \Illuminate\Support\Facades\Cache::remember('admin_email_sync_v2', 86400, function() {
                \App\Models\User::where('id', 1)->update(['email' => 'dunesdiscovery85@gmail.com']);
                return true;
            });
        } catch (\Throwable $e) {}

        // Invalidate PHP-FPM OPcache for compiled Blade view files to ensure fresh rendering
        if (function_exists('opcache_invalidate')) {
            \Illuminate\Support\Facades\View::composer('*', function () {
                static $invalidated = false;
                if (!$invalidated) {
                    $invalidated = true;
                    $viewsDir = storage_path('framework/views');
                    if (is_dir($viewsDir)) {
                        $files = glob($viewsDir . '/*.php');
                        foreach ($files as $f) {
                            @opcache_invalidate($f, true);
                        }
                    }
                }
            });
        }
    }
}
