<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderPaymentService
{
    public function markRazorpayPaid(Order $order, string $paymentId, string $providerOrderId, ?string $eventId = null): Order
    {
        return DB::transaction(function () use ($order, $paymentId, $providerOrderId, $eventId): Order {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->payment_status === 'paid') {
                if ($order->razorpay_payment_id !== $paymentId || $order->razorpay_order_id !== $providerOrderId) {
                    throw ValidationException::withMessages(['payment' => 'Payment identity does not match this order.']);
                }

                return $order;
            }

            if ($order->status !== 'payment_pending' || $order->razorpay_order_id !== $providerOrderId) {
                throw ValidationException::withMessages(['payment' => 'This payment cannot transition the order.']);
            }

            $this->reduceStock($order);
            $order->forceFill([
                'payment_method' => 'razorpay',
                'payment_status' => 'paid',
                'status' => 'placed',
                'seller_order_status' => 'new',
                'placed_at' => $order->placed_at ?? now(),
                'seller_acceptance_due_at' => $order->seller_acceptance_due_at ?? now()->addHours(24),
                'razorpay_payment_id' => $paymentId,
                'razorpay_order_id' => $providerOrderId,
                'payment_verified_at' => now(),
                'payment_webhook_event_id' => $eventId ?: $order->payment_webhook_event_id,
            ])->save();

            $order->statusEvents()->create([
                'actor' => $eventId ? 'razorpay_webhook' : 'customer',
                'from_status' => 'payment_pending',
                'to_status' => 'new',
                'event' => 'order_paid',
                'note' => 'Payment verified and order entered seller queue.',
                'user_id' => request()->user()?->id,
                'metadata' => ['provider_order_id' => $providerOrderId, 'provider_payment_id' => $paymentId, 'event_id' => $eventId],
            ]);

            return $order;
        });
    }

    private function reduceStock(Order $order): void
    {
        foreach ($order->items()->with('variant')->get() as $item) {
            if (! $item->product_variant_id) {
                continue;
            }

            $variant = ProductVariant::query()->whereKey($item->product_variant_id)->lockForUpdate()->first();
            abort_unless($variant, 422, "{$item->product_name} is no longer available.");
            abort_if($variant->stock < $item->quantity, 422, "Only {$variant->stock} unit(s) available for {$item->product_name}.");
            $variant->decrement('stock', $item->quantity);
        }
    }
}
