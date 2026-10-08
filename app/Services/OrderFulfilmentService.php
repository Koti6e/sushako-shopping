<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderVendorFulfilment;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderFulfilmentService
{
    public const POLICY_VERSION = 'seller-fulfilment-v1';

    private const TRANSITIONS = [
        'new' => ['accepted', 'cancelled'],
        'accepted' => ['processing', 'packed', 'cancelled'],
        'processing' => ['contact_customer', 'packed', 'cancelled'],
        'contact_customer' => ['shipping_confirmed', 'cancelled'],
        'shipping_confirmed' => ['shipped', 'cancelled'],
        'packed' => ['shipping_confirmed', 'shipped', 'cancelled'],
        'shipped' => ['delivered', 'delivery_failed'],
        'delivery_failed' => ['shipped', 'cancelled'],
        'delivered' => [],
        'cancelled' => [],
    ];

    public function transition(Order $order, Vendor $vendor, array $data): OrderVendorFulfilment
    {
        return DB::transaction(function () use ($order, $vendor, $data): OrderVendorFulfilment {
            abort_unless($order->items()->where('vendor_id', $vendor->id)->exists(), 404);
            $fulfilment = OrderVendorFulfilment::query()->where('order_id', $order->id)->where('vendor_id', $vendor->id)->lockForUpdate()->first();
            if (! $fulfilment) {
                $fulfilment = OrderVendorFulfilment::query()->create(['order_id' => $order->id, 'vendor_id' => $vendor->id, 'status' => $order->seller_order_status ?: 'new', 'policy_version' => self::POLICY_VERSION]);
            }

            $from = $fulfilment->status ?: 'new';
            $to = $data['status'];
            if (! in_array($to, self::TRANSITIONS[$from] ?? [], true)) {
                throw ValidationException::withMessages(['status' => "Cannot move fulfilment from {$from} to {$to}."]);
            }
            if ($to === 'shipped' && blank($data['shipping_method'] ?? $fulfilment->shipping_method)) {
                throw ValidationException::withMessages(['shipping_method' => 'Add the seller shipping method before marking the order shipped.']);
            }

            $fulfilment->forceFill([
                'status' => $to,
                'shipping_method' => $data['shipping_method'] ?? $fulfilment->shipping_method,
                'shipping_charge' => $data['shipping_charge'] ?? $fulfilment->shipping_charge ?? 0,
                'expected_delivery_date' => $data['expected_delivery_date'] ?? $fulfilment->expected_delivery_date,
                'tracking_number' => $data['tracking_number'] ?? $fulfilment->tracking_number,
                'tracking_url' => $data['tracking_url'] ?? $fulfilment->tracking_url,
                'customer_contacted_at' => $to === 'contact_customer' ? ($fulfilment->customer_contacted_at ?? now()) : $fulfilment->customer_contacted_at,
                'shipping_confirmed_at' => $to === 'shipping_confirmed' ? ($fulfilment->shipping_confirmed_at ?? now()) : $fulfilment->shipping_confirmed_at,
                'fulfilment_notes' => $data['fulfilment_notes'] ?? $fulfilment->fulfilment_notes,
                'policy_version' => self::POLICY_VERSION,
            ])->save();

            $order->forceFill([
                'fulfilment_mode' => 'seller_fulfilled', 'fulfilment_policy_version' => self::POLICY_VERSION,
                'seller_order_status' => $to, 'shipping_provider' => $data['shipping_method'] ?? $order->shipping_provider,
                'tracking_number' => $data['tracking_number'] ?? $order->tracking_number, 'tracking_url' => $data['tracking_url'] ?? $order->tracking_url,
                'seller_accepted_at' => $to === 'accepted' ? ($order->seller_accepted_at ?? now()) : $order->seller_accepted_at,
                'packed_at' => in_array($to, ['packed', 'shipping_confirmed', 'shipped', 'delivered'], true) ? ($order->packed_at ?? now()) : $order->packed_at,
                'shipped_at' => in_array($to, ['shipped', 'delivered'], true) ? ($order->shipped_at ?? now()) : $order->shipped_at,
                'delivered_at' => $to === 'delivered' ? ($order->delivered_at ?? now()) : $order->delivered_at,
                'cancelled_at' => $to === 'cancelled' ? ($order->cancelled_at ?? now()) : $order->cancelled_at,
                'cancellation_reason' => $to === 'cancelled' ? ($data['reason'] ?? null) : $order->cancellation_reason,
                'status' => match ($to) { 'packed', 'shipping_confirmed' => 'packing', 'shipped' => 'shipped', 'delivered' => 'delivered', 'cancelled' => 'cancelled', default => $order->status },
            ])->save();

            $order->statusEvents()->create(['actor' => 'seller', 'user_id' => auth()->id(), 'from_status' => $from, 'to_status' => $to, 'event' => 'seller_fulfilment_updated', 'note' => $data['reason'] ?? $data['fulfilment_notes'] ?? null, 'metadata' => ['vendor_id' => $vendor->id, 'policy_version' => self::POLICY_VERSION]]);
            return $fulfilment->fresh();
        });
    }
}
