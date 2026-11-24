<?php

namespace App\Services\Http;

use Carbon\Carbon;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Manager;
use Illuminate\View\Factory as ViewFactory;

/**
 * Executes internal read-only GET probes through the HTTP kernel inside an
 * isolated container scope so active admin sessions, auth guards, CSRF tokens,
 * cookie queues, locales, URL defaults, and analytics remain intact.
 */
class InternalHttpProbeService
{
    private static bool $probing = false;

    public static function isProbing(): bool
    {
        return self::$probing;
    }

    /**
     * Probe multiple internal paths sequentially inside isolated scopes.
     *
     * @param  array<int, string>  $paths
     * @param  array<string, string>  $extraHeaders
     * @return array<string, array{status: int, html: string}>
     */
    public function probePaths(array $paths, array $extraHeaders = []): array
    {
        $results = [];

        foreach ($paths as $path) {
            $norm = $path === '' ? '/' : '/'.ltrim($path, '/');
            if (str_starts_with($norm, '/admin')) {
                continue;
            }

            $probed = $this->probePath($norm, $extraHeaders);
            $results[$norm] = [
                'status' => $probed['status'],
                'html' => $probed['html'],
            ];
        }

        return $results;
    }

    /**
     * Probe a single internal path without mutating parent session, auth, or request globals.
     *
     * @param  array<string, string>  $extraHeaders
     * @return array{status: int, html: string, content_type: string, location: string}
     */
    public function probePath(string $path, array $extraHeaders = []): array
    {
        $norm = $path === '' ? '/' : '/'.ltrim($path, '/');
        $kernel = app(HttpKernel::class);

        $originalRequest = app()->bound('request') ? app('request') : null;
        $originalSessionDriver = config('session.driver');
        $originalLocale = App::getLocale();
        $originalCarbonLocale = Carbon::getLocale();

        /** @var SessionManager $sessionManager */
        $sessionManager = app('session');
        $originalSessionStore = app()->resolved('session.store') ? app('session.store') : null;
        $originalSessionDrivers = $this->snapshotProperty($sessionManager, Manager::class, 'drivers');

        /** @var AuthManager $authManager */
        $authManager = app('auth');
        $originalAuthDriver = app()->resolved('auth.driver') ? app('auth.driver') : null;
        $originalAuthGuards = $this->snapshotProperty($authManager, AuthManager::class, 'guards');

        /** @var CookieJar $cookieJar */
        $cookieJar = app('cookie');
        $originalQueuedCookies = $cookieJar->getQueuedCookies();

        /** @var ViewFactory $viewFactory */
        $viewFactory = app('view');
        $originalViewShared = $this->snapshotProperty($viewFactory, ViewFactory::class, 'shared');

        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = app('url');
        $originalRouteGenerator = $this->snapshotProperty($urlGenerator, UrlGenerator::class, 'routeGenerator');
        $originalDefaultParams = is_object($originalRouteGenerator) ? $originalRouteGenerator->defaultParameters : [];

        $wasProbing = self::$probing;
        self::$probing = true;
        $subReq = null;
        $subRes = null;

        try {
            // Switch to an ephemeral in-memory session driver so StartSession never resets or
            // overwrites the admin's active database session ID, auth state, or CSRF token.
            app()->forgetInstance('session.store');
            app()->forgetInstance('auth.driver');
            config(['session.driver' => 'array']);
            $sessionManager->forgetDrivers();
            $authManager->forgetGuards();
            $cookieJar->flushQueuedCookies();

            $baseUrl = $originalRequest
                ? rtrim($originalRequest->getSchemeAndHttpHost(), '/')
                : rtrim((string) config('app.url', 'http://localhost'), '/');

            $subReq = Request::create($baseUrl.$norm, 'GET');
            $subReq->headers->set('User-Agent', 'DunesAuditBot/1.0');
            foreach ($extraHeaders as $header => $value) {
                $subReq->headers->set($header, $value);
            }

            $subRes = $kernel->handle($subReq);
            $status = $subRes->getStatusCode();
            $contentType = (string) $subRes->headers->get('Content-Type', '');
            $location = (string) $subRes->headers->get('Location', '');
            $html = (str_contains($contentType, 'html') || $status === 200)
                ? (string) $subRes->getContent()
                : '';

            return [
                'status' => $status,
                'html' => $html,
                'content_type' => $contentType,
                'location' => $location,
            ];
        } catch (\Throwable) {
            return [
                'status' => 500,
                'html' => '',
                'content_type' => '',
                'location' => '',
            ];
        } finally {
            self::$probing = $wasProbing;

            $cookieJar->flushQueuedCookies();
            foreach ($originalQueuedCookies as $cookie) {
                $cookieJar->queue($cookie);
            }

            $sessionManager->forgetDrivers();
            $this->restoreProperty($sessionManager, Manager::class, 'drivers', $originalSessionDrivers);
            config(['session.driver' => $originalSessionDriver]);

            if ($originalSessionStore !== null) {
                app()->instance('session.store', $originalSessionStore);
            } else {
                app()->forgetInstance('session.store');
            }

            $authManager->forgetGuards();
            $this->restoreProperty($authManager, AuthManager::class, 'guards', $originalAuthGuards);
            if ($originalAuthDriver !== null) {
                app()->instance('auth.driver', $originalAuthDriver);
            } else {
                app()->forgetInstance('auth.driver');
            }

            $this->restoreProperty($viewFactory, ViewFactory::class, 'shared', $originalViewShared);

            if ($originalRouteGenerator !== null) {
                $originalRouteGenerator->defaultParameters = $originalDefaultParams;
            }
            $this->restoreProperty($urlGenerator, UrlGenerator::class, 'routeGenerator', $originalRouteGenerator);

            App::setLocale($originalLocale);
            Carbon::setLocale($originalCarbonLocale);

            if ($originalRequest !== null) {
                app()->instance('request', $originalRequest);
                URL::setRequest($originalRequest);
            }

            unset($subRes, $subReq);
        }
    }

    private function snapshotProperty(object $target, string $scopeClass, string $property): mixed
    {
        $reader = \Closure::bind(fn (): mixed => $this->{$property}, $target, $scopeClass);

        return $reader();
    }

    private function restoreProperty(object $target, string $scopeClass, string $property, mixed $value): void
    {
        $writer = \Closure::bind(function (mixed $val) use ($property): void {
            $this->{$property} = $val;
        }, $target, $scopeClass);

        $writer($value);
    }
}
