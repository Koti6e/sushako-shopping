<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentSetting;

class CodEligibilityService
{
    public function evaluate(Order $order): array
    {
        if (! (bool) PaymentSetting::query()->where('provider', 'cod')->value('enabled')) {
            return ['status' => 'ineligible', 'reason' => 'Cash on Delivery is currently unavailable.'];
        }

        $items = $order->items()->with('product.vendor')->get();
        if ($items->isEmpty()) {
            return ['status' => 'eligible', 'reason' => 'Legacy order retained its existing Cash on Delivery eligibility.'];
        }

        foreach ($items as $item) {
            $product = $item->product;
            $vendor = $product?->vendor;

            if (! $product || ! $vendor) {
                return ['status' => 'ineligible', 'reason' => 'A product seller could not be verified.'];
            }

            if ($vendor->cod_enabled === false) {
                return ['status' => 'ineligible', 'reason' => 'Cash on Delivery is disabled by this seller.'];
            }

            if ($product->cod_enabled === false) {
                return ['status' => 'ineligible', 'reason' => 'Cash on Delivery is unavailable for one or more products.'];
            }
        }

        return ['status' => 'eligible', 'reason' => 'Cash on Delivery is available for this order.'];
    }

    public function snapshot(Order $order): array
    {
        $decision = $this->evaluate($order);
        $order->forceFill([
            'cod_eligibility_status' => $decision['status'],
            'cod_eligibility_reason' => $decision['reason'],
        ])->save();

        return $decision;
    }
}
