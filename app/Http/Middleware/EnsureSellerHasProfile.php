<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\SellerAccountService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerHasProfile
{
    public function __construct(private readonly SellerAccountService $sellerAccounts) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user && $user->role === User::ROLE_SELLER, 403);

        $request->attributes->set('vendor', $this->sellerAccounts->ensureVendor($user));

        return $next($request);
    }
}
