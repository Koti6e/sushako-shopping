<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderFulfilmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
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
            'fulfilment' => $order->vendorFulfilments()->where('vendor_id', $vendor->id)->first(),
            'vendor' => $vendor,
        ]);
    }

    public function update(Request $request, Order $order, OrderFulfilmentService $fulfilments): RedirectResponse|JsonResponse
    {
        $vendor = $request->attributes->get('vendor');
        abort_unless($order->items()->where('vendor_id', $vendor->id)->exists(), 404);
        $data = $request->validate([
            'seller_order_status' => ['required', Rule::in(['accepted', 'processing', 'contact_customer', 'shipping_confirmed', 'packed', 'shipped', 'delivered', 'delivery_failed', 'cancelled'])],
            'tracking_number' => ['nullable', 'string', 'max:120'],
            'tracking_url' => ['nullable', 'url', 'max:500'],
            'shipping_method' => ['nullable', 'string', 'max:120'],
            'shipping_charge' => ['nullable', 'integer', 'min:0'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:today'],
            'fulfilment_notes' => ['nullable', 'string', 'max:2000'],
            'reason' => ['required_if:seller_order_status,cancelled', 'nullable', 'string', 'max:240'],
        ]);

        try {
            $fulfilments->transition($order, $vendor, [
                'status' => $data['seller_order_status'],
                ...$data,
                // Preserve compatibility with the legacy seller order form.
                'shipping_method' => $data['shipping_method'] ?? $request->input('shipping_provider'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage(), 'errors' => $exception->errors()], 422);
            }

            return response()->json(['message' => $exception->getMessage(), 'errors' => $exception->errors()], 422);
        }

        return back()->with('status', 'Order updated.');
    }
}
