<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAdmin
{
    /**
     * Require the admin token, or hide the route when it is not configured.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('bagspar.admin_token');

        if (! is_string($token) || $token === '') {
            abort(404);
        }

        $provided = $this->providedToken($request);

        if ($provided === null || ! hash_equals(hash('sha256', $token), hash('sha256', $provided))) {
            return response('Unauthorized.', 401, [
                'WWW-Authenticate' => 'Basic realm="Bågspår", charset="UTF-8"',
                'X-Robots-Tag' => 'noindex, nofollow',
                'Cache-Control' => 'no-store',
            ]);
        }

        $response = $next($request);
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }

    private function providedToken(Request $request): ?string
    {
        $bearer = $request->bearerToken();

        if (is_string($bearer) && $bearer !== '') {
            return $bearer;
        }

        $password = $request->getPassword();

        if (is_string($password) && $password !== '') {
            return $password;
        }

        return null;
    }
}
