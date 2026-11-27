<?php

namespace App\Services\SyntheticTesting;

use Carbon\Carbon;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Routing\Router;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Manager;
use Illuminate\View\Factory as ViewFactory;

/**
 * Isolated stateful HTTP visitor emulator.
 *
 * Executes full Laravel HttpKernel requests with persistent per-journey cookies
 * and in-memory session storage while snapshotting and restoring the parent
 * request, router, session store/drivers, auth guards, queued cookies, view shares,
 * URL defaults, locale, and mailer configuration in a guaranteed finally block.
 */
class SyntheticVisitorSession
{
    private static bool $simulating = false;

    private static bool $suppressExternalSideEffects = false;

    private static bool $botShieldProbe = false;

    private static ?int $registeredDispatcherId = null;

    /**
     * @var array<int, array<string, mixed>>|null
     */
    private static ?array $capturedEmails = null;

    /**
     * @var array<int, array<string, mixed>>|null
     */
    private static ?array $deliveredEmails = null;

    private ?HttpKernel $kernel = null;

    private ?SessionManager $sessionManager = null;

    /**
     * @var array<string, string>
     */
    private array $cookies = [];

    private string $baseUrl = 'http://localhost';

    private string $locale = 'en';

    private string $userAgent = '';

    private string $clientIp = '127.0.0.1';

    public static function isSimulating(): bool
    {
        return self::$simulating;
    }

    public static function shouldSuppressExternalSideEffects(): bool
    {
        return self::$simulating && self::$suppressExternalSideEffects;
    }

    public static function isBotShieldProbe(): bool
    {
        return self::$simulating && self::$botShieldProbe;
    }

    /**
     * Run a multi-step synthetic visitor journey inside an isolated kernel & session scope.
     *
     * @param  callable(self): mixed  $callback
     * @param  array{locale?: string, device_profile?: string, mail_mode?: string, base_url?: string, client_ip?: string}  $options
     */
    public function runIsolated(callable $callback, array $options = []): mixed
    {
        $this->ensureMailListener();

        $kernel = app(HttpKernel::class);
        $this->kernel = $kernel;

        $originalRequest = app()->bound('request') ? app('request') : null;
        $originalSessionDriver = config('session.driver');
        $originalLocale = App::getLocale();
        $originalCarbonLocale = Carbon::getLocale();
        $originalMailDefault = config('mail.default');

        /** @var Router $router */
        $router = app('router');
        $originalRouterCurrent = $this->snapshotProperty($router, Router::class, 'current');
        $originalRouterRequest = $this->snapshotProperty($router, Router::class, 'currentRequest');

        /** @var SessionManager $sessionManager */
        $sessionManager = app('session');
        $this->sessionManager = $sessionManager;
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

        $this->locale = (string) ($options['locale'] ?? 'en');
        $device = (string) ($options['device_profile'] ?? 'desktop');
        $mailMode = (string) ($options['mail_mode'] ?? 'live_smtp');
        $this->clientIp = (string) ($options['client_ip'] ?? '127.0.0.1');
        $this->baseUrl = rtrim((string) ($options['base_url'] ?? ($originalRequest
            ? $originalRequest->getSchemeAndHttpHost()
            : config('app.url', 'http://localhost'))), '/');

        if ($this->baseUrl === '') {
            $this->baseUrl = 'http://localhost';
        }

        $this->userAgent = $device === 'mobile'
            ? 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Mobile/15E148 Safari/604.1'
            : 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36';

        $this->cookies = [];

        $wasSimulating = self::$simulating;
        $wasSuppressing = self::$suppressExternalSideEffects;
        $wasProbe = self::$botShieldProbe;
        $previousCollector = self::$capturedEmails;
        $previousDelivered = self::$deliveredEmails;

        self::$simulating = true;
        self::$suppressExternalSideEffects = ($mailMode !== 'live_smtp');
        self::$botShieldProbe = false;
        self::$capturedEmails = [];
        self::$deliveredEmails = [];

        try {
            // Reset session & auth to an ephemeral array-backed driver for the simulated visitor
            app()->forgetInstance('session.store');
            app()->forgetInstance('auth.driver');
            config(['session.driver' => 'array']);
            $sessionManager->forgetDrivers();
            $authManager->forgetGuards();
            $cookieJar->flushQueuedCookies();

            if (self::$suppressExternalSideEffects) {
                config(['mail.default' => 'array']);
                try {
                    app('mail.manager')->purge();
                } catch (\Throwable) {
                }
            }

            return $callback($this);
        } finally {
            self::$simulating = $wasSimulating;
            self::$suppressExternalSideEffects = $wasSuppressing;
            self::$botShieldProbe = $wasProbe;
            self::$capturedEmails = $previousCollector;
            self::$deliveredEmails = $previousDelivered;
            $this->kernel = null;
            $this->sessionManager = null;

            if ($originalMailDefault !== null) {
                config(['mail.default' => $originalMailDefault]);
            }
            try {
                app('mail.manager')->purge();
            } catch (\Throwable) {
            }

            $this->restoreProperty($router, Router::class, 'current', $originalRouterCurrent);
            $this->restoreProperty($router, Router::class, 'currentRequest', $originalRouterRequest);

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
        }
    }

    public function get(string $path, array $headers = []): array
    {
        return $this->request('GET', $path, [], $headers, false);
    }

    public function post(string $path, array $data = [], array $headers = []): array
    {
        return $this->request('POST', $path, $data, $headers, false);
    }

    public function postJson(string $path, array $data = [], array $headers = []): array
    {
        return $this->request('POST', $path, $data, $headers, true);
    }

    /**
     * Dispatch a simulated request through HttpKernel while carrying cookies & session state.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $headers
     * @return array{status: int, body: string, json: array<string, mixed>|null, headers: array<string, string>, location: string, duration_ms: int}
     */
    public function request(string $method, string $path, array $data = [], array $headers = [], bool $asJson = false): array
    {
        if (! $this->kernel) {
            throw new \RuntimeException('SyntheticVisitorSession::request() called outside runIsolated().');
        }

        $method = strtoupper($method);
        $uri = str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
            ? $path
            : $this->baseUrl.'/'.ltrim($path, '/');

        $server = [
            'REMOTE_ADDR' => $this->clientIp,
            'HTTP_USER_AGENT' => $this->userAgent,
            'HTTP_ACCEPT_LANGUAGE' => $this->locale === 'ar' ? 'ar,en-US;q=0.9,en;q=0.8' : 'en-US,en;q=0.9',
            'HTTP_REFERER' => $this->baseUrl.'/',
        ];

        $content = null;
        $parameters = $data;

        if ($asJson) {
            $server['CONTENT_TYPE'] = 'application/json';
            $server['HTTP_ACCEPT'] = 'application/json';
            $server['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
            $content = (string) json_encode($data, JSON_UNESCAPED_UNICODE);
            $parameters = [];
        } else {
            $server['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml,application/json;q=0.9,*/*;q=0.8';
        }

        $csrf = $this->csrfToken();
        if ($csrf !== '' && ! isset($headers['X-CSRF-TOKEN'])) {
            $server['HTTP_X_CSRF_TOKEN'] = $csrf;
        }

        foreach ($headers as $key => $value) {
            $serverKey = 'HTTP_'.strtoupper(str_replace('-', '_', (string) $key));
            $server[$serverKey] = (string) $value;
        }

        $subReq = Request::create($uri, $method, $parameters, $this->cookies, [], $server, $content);

        // Flush any queued cookies from previous controller responses before executing sub-request
        app('cookie')->flushQueuedCookies();

        $started = microtime(true);
        $subRes = $this->kernel->handle($subReq);
        $durationMs = (int) round((microtime(true) - $started) * 1000);

        foreach ($subRes->headers->getCookies() as $cookie) {
            if ($cookie->isCleared()) {
                unset($this->cookies[$cookie->getName()]);
            } else {
                $this->cookies[$cookie->getName()] = (string) $cookie->getValue();
            }
        }

        $flatHeaders = [];
        foreach ($subRes->headers->all() as $hName => $hVals) {
            $flatHeaders[strtolower((string) $hName)] = is_array($hVals) ? (string) ($hVals[0] ?? '') : (string) $hVals;
        }

        $body = '';
        if ($subRes instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
            $filePath = $subRes->getFile()->getPathname();
            if (is_file($filePath) && is_readable($filePath)) {
                $body = (string) file_get_contents($filePath);
            }
        } else {
            $body = (string) $subRes->getContent();
        }
        $decodedJson = null;
        $trimmed = trim($body);
        if ($trimmed !== '' && (str_starts_with($trimmed, '{') || str_starts_with($trimmed, '['))) {
            $parsed = json_decode($trimmed, true);
            if (is_array($parsed)) {
                $decodedJson = $parsed;
            }
        }

        return [
            'status' => $subRes->getStatusCode(),
            'body' => $body,
            'json' => $decodedJson,
            'headers' => $flatHeaders,
            'location' => (string) $subRes->headers->get('Location', ''),
            'duration_ms' => max(1, $durationMs),
        ];
    }

    public function csrfToken(): string
    {
        try {
            $store = app()->resolved('session.store')
                ? app('session.store')
                : $this->sessionManager?->driver();

            return $store ? (string) ($store->token() ?? '') : '';
        } catch (\Throwable) {
            return '';
        }
    }

    public function sessionGet(string $key, mixed $default = null): mixed
    {
        try {
            $store = app()->resolved('session.store')
                ? app('session.store')
                : $this->sessionManager?->driver();

            return $store ? $store->get($key, $default) : $default;
        } catch (\Throwable) {
            return $default;
        }
    }

    /**
     * Execute a step under Bot-Shield Probe mode (disables min-dwell bypass so anti-spam layers enforce normally).
     */
    public function withBotShieldProbe(callable $fn): mixed
    {
        $prev = self::$botShieldProbe;
        self::$botShieldProbe = true;
        try {
            return $fn($this);
        } finally {
            self::$botShieldProbe = $prev;
        }
    }

    /**
     * Execute a step with external SMTP and webhooks temporarily suppressed
     * (useful for secondary gateway parity probes so only 1 real alert fires).
     */
    public function withExternalSideEffectsSuppressed(callable $fn): mixed
    {
        $prevSuppress = self::$suppressExternalSideEffects;
        $prevMailer = config('mail.default');
        self::$suppressExternalSideEffects = true;
        config(['mail.default' => 'array']);
        try {
            app('mail.manager')->purge();
        } catch (\Throwable) {
        }

        try {
            return $fn($this);
        } finally {
            self::$suppressExternalSideEffects = $prevSuppress;
            if ($prevMailer !== null) {
                config(['mail.default' => $prevMailer]);
            }
            try {
                app('mail.manager')->purge();
            } catch (\Throwable) {
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getCapturedEmails(): array
    {
        return self::$capturedEmails ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getDeliveredEmails(): array
    {
        return self::$deliveredEmails ?? [];
    }

    public function clearCapturedEmails(): void
    {
        self::$capturedEmails = [];
        self::$deliveredEmails = [];
    }

    public function findLatestOtpCodeForEmail(string $email): ?string
    {
        $target = strtolower(trim($email));
        $emails = array_reverse($this->getCapturedEmails());
        foreach ($emails as $mail) {
            $recipients = array_map('strtolower', (array) ($mail['to'] ?? []));
            if (! in_array($target, $recipients, true)) {
                continue;
            }
            $haystack = strip_tags((string) ($mail['text_body'] ?? '')."\n".(string) ($mail['html_body'] ?? ''));
            if (preg_match('/\b(\d{6})\b/', $haystack, $m)) {
                return $m[1];
            }
        }

        $sessionOtp = $this->sessionGet('otp_code');

        return is_string($sessionOtp) && preg_match('/^\d{6}$/', $sessionOtp) ? $sessionOtp : null;
    }

    private function ensureMailListener(): void
    {
        $dispatcher = app('events');
        $dispatcherId = spl_object_id($dispatcher);
        if (self::$registeredDispatcherId === $dispatcherId) {
            return;
        }

        self::$registeredDispatcherId = $dispatcherId;

        Event::listen(MessageSending::class, function (MessageSending $event): void {
            if (! self::$simulating || self::$capturedEmails === null) {
                return;
            }

            try {
                $msg = $event->message;
                $to = [];
                foreach ($msg->getTo() as $addr) {
                    $to[] = $addr->getAddress();
                }

                $attachmentNames = [];
                foreach ($msg->getAttachments() as $part) {
                    $attachmentNames[] = method_exists($part, 'getFilename') ? ($part->getFilename() ?: 'attachment') : 'attachment';
                }

                self::$capturedEmails[] = [
                    'to' => $to,
                    'subject' => (string) $msg->getSubject(),
                    'text_body' => (string) ($msg->getTextBody() ?? ''),
                    'html_body' => (string) ($msg->getHtmlBody() ?? ''),
                    'attachments' => $attachmentNames,
                ];
            } catch (\Throwable) {
            }
        });

        Event::listen(MessageSent::class, function (MessageSent $event): void {
            if (! self::$simulating || self::$deliveredEmails === null) {
                return;
            }

            try {
                $msg = $event->message;
                $to = [];
                foreach ($msg->getTo() as $addr) {
                    $to[] = $addr->getAddress();
                }

                $attachmentNames = [];
                foreach ($msg->getAttachments() as $part) {
                    $attachmentNames[] = method_exists($part, 'getFilename') ? ($part->getFilename() ?: 'attachment') : 'attachment';
                }

                self::$deliveredEmails[] = [
                    'to' => $to,
                    'subject' => (string) $msg->getSubject(),
                    'text_body' => (string) ($msg->getTextBody() ?? ''),
                    'html_body' => (string) ($msg->getHtmlBody() ?? ''),
                    'attachments' => $attachmentNames,
                ];
            } catch (\Throwable) {
            }
        });
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
