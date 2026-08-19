<?php

namespace App\Services;

use App\Models\CommissionRule;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SellerLedgerEntry;
use App\Models\Vendor;

class SellerCommissionService
{
    public function preview(Vendor $vendor, ?Product $product, int $sellingPrice, int $quantity = 1): array
    {
        $plan = $this->effectivePlan($vendor);

        if ($plan === Vendor::PLAN_FREE) {
            $base = $sellingPrice * $quantity;
            $commission = min($base, $quantity);

            return [
                'base' => $base,
                'type' => 'flat_per_unit',
                'rate' => 1,
                'commission' => $commission,
                'platform_fee_per_unit' => 1,
                'platform_fee_total' => $commission,
                'eligible_quantity' => $quantity,
                'seller_earning' => max(0, $base - $commission),
                'source' => 'free_plan_unit_commission',
            ];
        }

        $rule = $this->effectiveRule($vendor, $product);
        $base = $sellingPrice * $quantity;
        $commission = $this->commissionAmount($base, $rule['type'], $rule['rate']);

        if ($this->zeroCommission($vendor)) {
            $commission = 0;
            $rule = ['type' => 'percentage', 'rate' => 0, 'source' => $vendor->current_plan.'_zero_commission'];
        }

        return [
            'base' => $base,
            'type' => $rule['type'],
            'rate' => $rule['rate'],
            'commission' => $commission,
            'platform_fee_per_unit' => 0,
            'platform_fee_total' => 0,
            'eligible_quantity' => $quantity,
            'seller_earning' => max(0, $base - $commission),
            'source' => $rule['source'],
        ];
    }

    public function capture(OrderItem $item, Vendor $vendor): void
    {
        $preview = $this->preview($vendor, $item->product, (int) $item->unit_price, (int) $item->quantity);

        $item->forceFill([
            'vendor_id' => $vendor->id,
            'seller_plan_at_order' => $this->effectivePlan($vendor),
            'gross_line_amount' => $preview['base'],
            'commission_base' => $preview['base'],
            'commission_type' => $preview['type'],
            'commission_rate' => $preview['rate'],
            'commission_amount' => $preview['commission'],
            'eligible_quantity' => $preview['eligible_quantity'],
            'platform_fee_per_unit' => $preview['platform_fee_per_unit'],
            'platform_fee_total' => $preview['platform_fee_total'],
            'retained_quantity' => $preview['eligible_quantity'],
            'seller_earning' => $preview['seller_earning'],
            'commission_rule_source' => $preview['source'],
            'commission_calculated_at' => now(),
            'settlement_status' => 'upcoming',
        ])->save();

        SellerLedgerEntry::query()->create([
            'vendor_id' => $vendor->id,
            'order_id' => $item->order_id,
            'order_item_id' => $item->id,
            'entry_type' => 'commission_charged',
            'gross_amount' => $preview['base'],
            'commission_amount' => $preview['commission'],
            'net_amount' => $preview['seller_earning'],
            'status' => 'posted',
            'reason' => 'Order item commission captured at order time.',
        ]);
    }

    public function zeroCommission(Vendor $vendor): bool
    {
        if ($this->effectivePlan($vendor) === Vendor::PLAN_FREE) {
            return false;
        }

        if ($vendor->current_plan === Vendor::PLAN_GROWTH && $vendor->plan_expires_at?->isFuture()) {
            return true;
        }

        if ($vendor->current_plan === Vendor::PLAN_ENTERPRISE) {
            return $vendor->plan_expires_at?->isFuture() || $vendor->grace_ends_at?->isFuture();
        }

        return false;
    }

    public function effectivePlan(Vendor $vendor): string
    {
        if (in_array($vendor->current_plan, [Vendor::PLAN_GROWTH, Vendor::PLAN_ENTERPRISE], true) && $vendor->plan_expires_at?->isPast() && ! $vendor->grace_ends_at?->isFuture()) {
            return Vendor::PLAN_FREE;
        }

        return $vendor->current_plan ?: Vendor::PLAN_FREE;
    }

    public function effectiveRule(Vendor $vendor, ?Product $product = null): array
    {
        $query = CommissionRule::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()));

        foreach ([
            'product' => fn () => $product ? (clone $query)->where('product_id', $product->id)->first() : null,
            'seller' => fn () => (clone $query)->where('vendor_id', $vendor->id)->whereNull('product_id')->first(),
            'category' => fn () => $product ? (clone $query)->where('category_id', $product->category_id)->whereNull('product_id')->whereNull('vendor_id')->first() : null,
            'plan' => fn () => (clone $query)->where('plan', $vendor->current_plan)->whereNull('product_id')->whereNull('vendor_id')->whereNull('category_id')->first(),
            'default' => fn () => (clone $query)->whereNull('plan')->whereNull('product_id')->whereNull('vendor_id')->whereNull('category_id')->first(),
        ] as $source => $resolver) {
            $rule = $resolver();
            if ($rule) {
                return ['type' => $rule->type, 'rate' => (float) $rule->rate, 'source' => $source];
            }
        }

        return ['type' => 'percentage', 'rate' => 10.0, 'source' => 'default_platform'];
    }

    private function commissionAmount(int $base, string $type, float $rate): int
    {
        if ($type === 'flat') {
            return min($base, (int) round($rate));
        }

        return (int) round($base * $rate / 100);
    }
}
