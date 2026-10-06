<?php

use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TrackVisitor;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            TrackVisitor::class,
            SecurityHeaders::class,
            \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->is('ajax.php') || $request->ajax(),
        );

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            if ($e->getStatusCode() === 419) {
                if ($request->is('api/*') || $request->is('ajax.php') || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Security token expired. Please refresh the page.',
                    ], 419);
                }

                return redirect()->route('login')
                    ->with('status', 'Your session expired for security reasons. Please enter your credentials to sign in.')
                    ->withInput($request->except('password', '_token'));
            }

            if ($e->getStatusCode() === 429) {
                if ($request->is('api/*') || $request->is('ajax.php') || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many requests. Please wait a moment and try again.',
                    ], 429);
                }

                return redirect()->route('login')
                    ->with('status', 'Too many requests were detected from your network. Please wait a moment and try again.')
                    ->withInput($request->except('password', '_token'));
            }
        });

        $exceptions->render(function (DecryptException $e, Request $request) {
            if ($request->is('api/*') || $request->is('ajax.php') || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired. Please refresh the page.',
                ], 200);
            }

            return redirect($request->fullUrl())
                ->withCookie(cookie()->forget('laravel_session'))
                ->withCookie(cookie()->forget('laravel-session'))
                ->withCookie(cookie()->forget('XSRF-TOKEN'))
                ->withCookie(cookie()->forget('remember_web'));
        });
    })->create();
