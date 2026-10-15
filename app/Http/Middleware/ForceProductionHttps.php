<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class ForceProductionHttps
{
    /**
     * Force HTTPS URLs when this request is handled in production.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->force();

        if ($redirect = $this->redirectInsecureRequest($request)) {
            return $redirect;
        }

        return $next($request);
    }

    /**
     * Send a production request that arrived as HTTP on to HTTPS.
     *
     * Cloudflare normally answers port 80 itself. This redirect covers a
     * request that still reaches PHP, including one proxied without TLS.
     * Local hosts are left alone so health checks on 127.0.0.1 keep working.
     * A request that already has X-Forwarded-Proto: https is secure and stays.
     */
    private function redirectInsecureRequest(Request $request): ?RedirectResponse
    {
        if (! app()->isProduction() || $request->secure() || $this->keepsPlainHttp($request->getHost())) {
            return null;
        }

        $status = $request->isMethod('GET') || $request->isMethod('HEAD') ? 301 : 308;

        return redirect()->secure($request->getRequestUri(), $status);
    }

    private function keepsPlainHttp(string $host): bool
    {
        $host = strtolower($host);

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true) || str_ends_with($host, '.test');
    }

    /**
     * Serve production URLs over HTTPS even when APP_URL is http.
     *
     * The public site is HTTPS-only behind Cloudflare. APP_URL may still be
     * http://bagspar.se, and the sitemap and canonical tag read that value
     * directly. Local http hosts are left unchanged.
     */
    public function force(): void
    {
        if (! app()->isProduction()) {
            return;
        }

        URL::forceHttps();

        $secureUrl = $this->secureAppUrl(config('app.url'));

        if ($secureUrl === null) {
            return;
        }

        config(['app.url' => $secureUrl]);
        URL::forceRootUrl($secureUrl);
    }

    private function secureAppUrl(mixed $configuredUrl): ?string
    {
        if (! is_string($configuredUrl)) {
            return null;
        }

        $configuredUrl = rtrim(trim($configuredUrl), '/');

        if ($configuredUrl === '') {
            return null;
        }

        $secureUrl = preg_replace('#\Ahttps?://#i', 'https://', $configuredUrl);

        if (! is_string($secureUrl)) {
            return null;
        }

        $host = parse_url($secureUrl, PHP_URL_HOST);

        if (parse_url($secureUrl, PHP_URL_SCHEME) !== 'https' || ! is_string($host) || $host === '') {
            return null;
        }

        return $secureUrl;
    }
}
