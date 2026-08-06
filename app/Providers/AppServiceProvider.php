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
