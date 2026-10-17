<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkshopAccess
{
    /**
     * Routes a locked workshop may still reach: the billing page, where the
     * owner orders a subscription, and the exports, so no data is held hostage.
     *
     * @var list<string>
     */
    private const OPEN_ROUTES = [
        'app.billing.edit',
        'app.billing.store',
        'app.export',
        'app.customers.export.json',
        'app.customers.export.csv',
    ];

    /**
     * Send a workshop whose trial or paid period has ended to the billing page.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        $workshop = $user->workshop;

        if ($workshop === null || $workshop->hasAccess()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if (is_string($routeName) && in_array($routeName, self::OPEN_ROUTES, true)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(402, 'Abonnemang krävs.');
        }

        return redirect()->route('app.billing.edit');
    }
}
