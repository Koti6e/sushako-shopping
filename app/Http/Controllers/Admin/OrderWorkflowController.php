<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderWorkflowController extends Controller
{
    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['placed', 'packing', 'shipped', 'delivered'])],
            'shipping_provider' => ['nullable', Rule::in(['rapido', 'uber', 'india_post', 'dtdc', 'professional', 'others'])],
            'shipping_provider_other' => ['nullable', 'required_if:shipping_provider,others', 'string', 'max:120'],
            'tracking_number' => ['nullable', 'string', 'max:120'],
        ]);

        $timestamps = match ($data['status']) {
            'packing' => ['packed_at' => $order->packed_at ?? now()],
            'shipped' => [
                'packed_at' => $order->packed_at ?? now(),
                'shipped_at' => $order->shipped_at ?? now(),
            ],
            'delivered' => [
                'packed_at' => $order->packed_at ?? now(),
                'shipped_at' => $order->shipped_at ?? now(),
                'delivered_at' => $order->delivered_at ?? now(),
            ],
            default => [],
        };

        $order->forceFill(array_merge($data, $timestamps))->save();

        return back()->with('status', "Order {$order->order_number} updated.");
    }
}
