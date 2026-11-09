<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403, 'Unauthorized access.');
        }

        // Administrators hold every ability. Any other account is only allowed
        // into the panel when at least one permission has been granted to it;
        // the individual routes then decide which pages it can reach.
        if (! $user->isAdmin() && $user->getAllPermissions()->isEmpty()) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
