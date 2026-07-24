<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerHasMobile
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->phone && ! $request->routeIs('profile.complete', 'profile.complete.update', 'logout')) {
            return redirect()->route('profile.complete');
        }

        return $next($request);
    }
}
