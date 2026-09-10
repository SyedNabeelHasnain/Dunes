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
    }
}
