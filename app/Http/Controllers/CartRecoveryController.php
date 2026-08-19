<?php

namespace App\Http\Controllers;

use App\Models\CustomerCart;
use App\Services\AbandonedCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartRecoveryController extends Controller
{
    public function __construct(private readonly AbandonedCartService $abandoned) {}

    public function __invoke(Request $request, string $token): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), 403);

        $cart = CustomerCart::query()->where('recovery_token', $token)->firstOrFail();
        [$restored, $unavailable] = $this->abandoned->restore($cart, $request->user());

        if ($restored === []) {
            return redirect()->route('cart.empty')->with('status', 'This saved cart no longer has available products.');
        }

        $request->session()->put('cart', $restored);

        return redirect()->route('cart.empty')->with('status', $unavailable === []
            ? 'Your saved cart has been restored.'
            : 'Your saved cart was restored. Some unavailable items were skipped.');
    }
}
