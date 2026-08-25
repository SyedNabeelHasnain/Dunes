<?php

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
        $middleware->validateCsrfTokens(except: [
            'ajax.php',
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\TrackVisitor::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->is('ajax.php') || $request->ajax(),
        );

        $exceptions->render(function (\Illuminate\Contracts\Encryption\DecryptException $e, Request $request) {
            if ($request->is('api/*') || $request->is('ajax.php') || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired. Please refresh the page.'
                ], 200);
            }

            return redirect($request->fullUrl())
                ->withCookie(cookie()->forget('laravel_session'))
                ->withCookie(cookie()->forget('laravel-session'))
                ->withCookie(cookie()->forget('XSRF-TOKEN'))
                ->withCookie(cookie()->forget('remember_web'));
        });
    })->create();
