<?php

namespace App\Http\Middleware;

use App\Models\Vendor;
use App\Services\SellerAccountService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerPlanActivated
{
    public function __construct(private readonly SellerAccountService $sellerAccounts) {}

    public function handle(Request $request, Closure $next): Response
    {
        $vendor = $request->attributes->get('vendor');

        abort_unless($vendor instanceof Vendor, 403);

        if (! $this->sellerAccounts->hasActivePlan($vendor)) {
            return redirect()->route('seller.onboarding');
        }

        return $next($request);
    }
}
