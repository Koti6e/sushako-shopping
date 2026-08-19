<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerCart;
use App\Services\AbandonedCartService;
use App\Services\CustomerMetricsService;
use App\Services\WhatsAppIntentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AbandonedCartController extends Controller
{
    public function __construct(
        private readonly CustomerMetricsService $metrics,
        private readonly AbandonedCartService $abandoned,
        private readonly WhatsAppIntentService $whatsApp,
    ) {}

    public function index(): View
    {
        $this->abandoned->markAbandonedCarts();

        return view('admin.customers.abandoned-carts.index', [
            'summaryCards' => $this->metrics->abandonedSummaries(),
            'carts' => $this->metrics->abandonedCarts(),
        ]);
    }

    public function show(CustomerCart $cart): View
    {
        return view('admin.customers.abandoned-carts.show', [
            'cart' => $cart->load(['user.orders', 'items.product.images', 'items.variant', 'communications.admin']),
            'recoveryUrl' => $cart->recovery_token ? $this->whatsApp->cartRecoveryUrl($cart) : null,
        ]);
    }

    public function dismiss(CustomerCart $cart): RedirectResponse
    {
        $cart->forceFill([
            'status' => CustomerCart::STATUS_DISMISSED,
            'dismissed_at' => now(),
        ])->save();

        return redirect()->route('admin.customers.abandoned-carts.index')->with('status', 'Abandoned cart dismissed.');
    }

    public function markRecovered(CustomerCart $cart): RedirectResponse
    {
        $cart->forceFill([
            'status' => CustomerCart::STATUS_RECOVERED,
            'recovered_at' => now(),
        ])->save();

        return back()->with('status', 'Cart marked recovered.');
    }
}
