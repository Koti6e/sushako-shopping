<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $vendor = $request->attributes->get('vendor');

        return view('seller.orders.index', [
            'orders' => Order::query()
                ->with('items')
                ->whereHas('items', fn ($query) => $query->where('vendor_id', $vendor->id))
                ->when($request->query('status'), fn ($query, string $status) => $query->where('seller_order_status', $status))
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'vendor' => $vendor,
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $vendor = $request->attributes->get('vendor');
        abort_unless($order->items()->where('vendor_id', $vendor->id)->exists(), 404);

        return view('seller.orders.show', [
            'order' => $order->load(['items' => fn ($query) => $query->where('vendor_id', $vendor->id)]),
            'vendor' => $vendor,
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');
        abort_unless($order->items()->where('vendor_id', $vendor->id)->exists(), 404);
        $data = $request->validate([
            'seller_order_status' => ['required', Rule::in(['accepted', 'packed', 'shipped', 'delivered', 'cancelled'])],
            'tracking_number' => ['nullable', 'string', 'max:120'],
            'tracking_url' => ['nullable', 'url', 'max:500'],
            'shipping_provider' => ['nullable', 'string', 'max:120'],
            'reason' => ['required_if:seller_order_status,cancelled', 'nullable', 'string', 'max:240'],
        ]);

        $from = $order->seller_order_status ?: 'new';
        $to = $data['seller_order_status'];
        $allowed = [
            'new' => ['accepted', 'cancelled'],
            'accepted' => ['packed', 'cancelled'],
            'packed' => ['shipped', 'cancelled'],
            'shipped' => ['delivered'],
            'delivered' => [],
            'cancelled' => [],
        ];
        abort_unless(in_array($to, $allowed[$from] ?? [], true), 422, 'Invalid order status transition.');

        $order->forceFill([
            'seller_order_status' => $to,
            'status' => match ($to) {
                'packed' => 'packing',
                'shipped' => 'shipped',
                'delivered' => 'delivered',
                'cancelled' => 'cancelled',
                default => $order->status,
            },
            'tracking_number' => $data['tracking_number'] ?? $order->tracking_number,
            'tracking_url' => $data['tracking_url'] ?? $order->tracking_url,
            'shipping_provider' => $data['shipping_provider'] ?? $order->shipping_provider,
            'seller_accepted_at' => $to === 'accepted' ? now() : $order->seller_accepted_at,
            'packed_at' => $to === 'packed' ? now() : $order->packed_at,
            'shipped_at' => $to === 'shipped' ? now() : $order->shipped_at,
            'delivered_at' => $to === 'delivered' ? now() : $order->delivered_at,
            'cancelled_at' => $to === 'cancelled' ? now() : $order->cancelled_at,
            'cancellation_reason' => $to === 'cancelled' ? $data['reason'] : $order->cancellation_reason,
        ])->save();

        $order->statusEvents()->create([
            'actor' => 'seller',
            'user_id' => $request->user()->id,
            'from_status' => $from,
            'to_status' => $to,
            'event' => 'seller_status_updated',
            'note' => $data['reason'] ?? null,
            'metadata' => [
                'vendor_id' => $vendor->id,
                'tracking_number' => $data['tracking_number'] ?? null,
                'shipping_provider' => $data['shipping_provider'] ?? null,
            ],
        ]);

        return back()->with('status', 'Order updated.');
    }
}
