<?php

use App\Http\Middleware\AuthenticateAdmin;
use App\Http\Middleware\EnsureVerifiedEmailInProduction;
use App\Http\Middleware\EnsureWorkshopAccess;
use App\Http\Middleware\ForceProductionHttps;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Exceptions\InvalidSignatureException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Cloudflare terminates TLS and forwards the visitor's scheme and IP.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_AWS_ELB,
        );
        $middleware->append(ForceProductionHttps::class);
        $middleware->append(SecurityHeaders::class);
        $middleware->alias([
            'admin' => AuthenticateAdmin::class,
            'verified.production' => EnsureVerifiedEmailInProduction::class,
            'workshop.access' => EnsureWorkshopAccess::class,
        ]);
        $middleware->redirectGuestsTo(fn (Request $request): string => route('login'));
        $middleware->redirectUsersTo(fn (Request $request): string => route('app.dashboard'));
        // The demo page is cached for offline use, so a later visit may not
        // have a fresh session token. The endpoint stores example steps and notes only.
        $middleware->preventRequestForgery(except: [
            'demo/synk',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (InvalidSignatureException $exception, Request $request): ?Response {
            if ($request->routeIs('verification.verify') && ! $request->expectsJson()) {
                return response()->view('auth.verification-invalid', status: 403);
            }

            return null;
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
