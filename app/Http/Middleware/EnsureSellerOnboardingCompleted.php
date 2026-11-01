<?php

namespace App\Http\Middleware;

use App\Models\Vendor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerOnboardingCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $vendor = $request->attributes->get('vendor');

        abort_unless($vendor instanceof Vendor, 403);

        if ($vendor->onboarding_status !== 'complete') {
            return redirect()->route('seller.onboarding');
        }

        return $next($request);
    }
}
