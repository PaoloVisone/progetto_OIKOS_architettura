<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Adatta se usi ruoli diversi
        if (! $user || ! in_array($user->role ?? 'user', ['admin', 'editor'])) {
            abort(403, 'Forbidden');
        }

        if (property_exists($user, 'is_active') && ! $user->is_active) {
            abort(403, 'Account disabilitato');
        }

        return $next($request);
    }
}
