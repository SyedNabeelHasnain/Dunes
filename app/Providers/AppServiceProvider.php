<?php

namespace App\Providers;

use App\Models\Language;
use App\Services\Routing\LocalizedUrlGenerator;
use App\Services\SettingsService;
use App\Validation\AppValidator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
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

        $this->app->singleton('url', function ($app) {
            $routes = $app['router']->getRoutes();
            $app->instance('routes', $routes);

            $url = new LocalizedUrlGenerator(
                $routes,
                $app->rebinding('request', function ($app, $request) {
                    $app['url']->setRequest($request);
                }),
                $app['config']['app.asset_url']
            );

            $url->setSessionResolver(function () use ($app) {
                return $app['session'] ?? null;
            });

            $url->setKeyResolver(function () use ($app) {
                $config = $app->make('config');

                return [$config->get('app.key'), ...($config->get('app.previous_keys') ?? [])];
            });

            $app->rebinding('routes', function ($app, $routes) {
                $app['url']->setRoutes($routes);
            });

            return $url;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Support AVIF in Laravel's built-in 'image' validator
        Validator::resolver(function ($translator, $data, $rules, $messages, $customAttributes) {
            return new AppValidator($translator, $data, $rules, $messages, $customAttributes);
        });

        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Ensure Dompdf font cache directory exists and is writable
        $fontDir = storage_path('fonts');
        if (! is_dir($fontDir)) {
            @mkdir($fontDir, 0775, true);
        }

        View::composer('*', function ($view) {
            try {
                $settings = app(SettingsService::class)->all();
                $view->with('settings', $settings);
            } catch (\Throwable $e) {
                $view->with('settings', collect());
            }

            try {
                $activeLanguages = Language::getActive();
                $view->with('activeLanguages', $activeLanguages);
            } catch (\Throwable $e) {
                $view->with('activeLanguages', Language::fallbackCollection());
            }
        });
    }
}
