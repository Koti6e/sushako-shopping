<?php

namespace App\Http\Middleware;

use App\Models\Vendor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerAccountActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $vendor = $request->attributes->get('vendor');

        abort_unless($vendor instanceof Vendor, 403);

        if ($request->user()?->status !== 'active' || ! in_array($vendor->status, [Vendor::STATUS_ACTIVE, 'pending'], true)) {
            return redirect()->route('seller.status');
        }

        return $next($request);
    }
}
