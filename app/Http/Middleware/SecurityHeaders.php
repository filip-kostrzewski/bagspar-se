<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Route names whose URL carries a secret. Those pages send no referrer at all.
     *
     * @var list<string>
     */
    private const SECRET_URL_ROUTES = [
        'client-status.*',
        'quote.*',
        'invitations.accept',
        'invitations.accept.store',
        'auth.password.reset',
        'verification.verify',
    ];

    /**
     * Add the headers every response should carry.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $headers = $response->headers;

        if (! $headers->has('X-Content-Type-Options')) {
            $headers->set('X-Content-Type-Options', 'nosniff');
        }

        if (! $headers->has('X-Frame-Options')) {
            $headers->set('X-Frame-Options', 'DENY');
        }

        if (! $headers->has('Referrer-Policy')) {
            $headers->set('Referrer-Policy', $request->routeIs(...self::SECRET_URL_ROUTES)
                ? 'no-referrer'
                : 'strict-origin-when-cross-origin');
        }

        return $response;
    }
}
