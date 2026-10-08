<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SellerLedgerEntry;
use App\Models\SellerPlan;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class SellerCommissionService
{
    public const CALCULATION_VERSION = 'plan-v2';

    public function preview(Vendor $vendor, ?Product $product, int $sellingPrice, int $quantity = 1): array
    {
        $quantity = max(0, $quantity);
        $base = max(0, $sellingPrice) * $quantity;
        $plan = $this->planSnapshot($vendor);
        $planCode = $this->effectivePlan($vendor);
        $isUnitFee = in_array($planCode, [Vendor::PLAN_FREE, Vendor::PLAN_STARTER], true);
        $commission = $isUnitFee ? min($base, $quantity) : 0;

        return [
            'base' => $base,
            'type' => $isUnitFee ? 'flat_per_unit' : 'none',
            'rate' => $isUnitFee ? 1 : 0,
            'commission' => $commission,
            'platform_fee_per_unit' => $isUnitFee ? 1 : 0,
            'platform_fee_total' => $commission,
            'eligible_quantity' => $quantity,
            'seller_earning' => max(0, $base - $commission),
            'source' => $planCode === Vendor::PLAN_FREE ? 'free_plan_unit_commission' : $planCode.'_plan',
            'seller_plan' => $planCode,
            'seller_plan_version' => (int) ($plan['plan_version'] ?? 1),
            'commission_calculation_version' => self::CALCULATION_VERSION,
            'payment_gateway_fee' => 0,
        ];
    }

    public function capture(OrderItem $item, Vendor $vendor): void
    {
        DB::transaction(function () use ($item, $vendor): void {
            $item->refresh();
            $dedupeKey = 'commission:order-item:'.$item->id;
            $preview = $this->preview($vendor, $item->product, (int) $item->unit_price, (int) $item->quantity);

            if (! $item->commission_calculated_at) {
                $item->forceFill([
                    'vendor_id' => $vendor->id,
                    'seller_plan_at_order' => $preview['seller_plan'],
                    'seller_plan_version_snapshot' => $preview['seller_plan_version'],
                    'gross_line_amount' => $preview['base'],
                    'commission_base' => $preview['base'],
                    'commission_type' => $preview['type'],
                    'commission_rate' => $preview['rate'],
                    'commission_rate_snapshot' => $preview['rate'],
                    'commission_amount' => $preview['commission'],
                    'eligible_quantity' => $preview['eligible_quantity'],
                    'platform_fee_per_unit' => $preview['platform_fee_per_unit'],
                    'platform_fee_total' => $preview['platform_fee_total'],
                    'retained_quantity' => $preview['eligible_quantity'],
                    'seller_earning' => $preview['seller_earning'],
                    'commission_rule_source' => $preview['source'],
                    'commission_calculation_version' => self::CALCULATION_VERSION,
                    'commission_calculated_at' => now(),
                    'settlement_status' => 'upcoming',
                ])->save();
            }

            SellerLedgerEntry::query()->firstOrCreate(['dedupe_key' => $dedupeKey], [
                'vendor_id' => $vendor->id,
                'order_id' => $item->order_id,
                'order_item_id' => $item->id,
                'entry_type' => 'commission_charged',
                'gross_amount' => $item->gross_line_amount ?? $preview['base'],
                'commission_amount' => $item->commission_amount ?? $preview['commission'],
                'net_amount' => $item->seller_earning ?? $preview['seller_earning'],
                'status' => 'posted',
                'reason' => 'Order item commission captured at order time.',
            ]);
        });
    }

    public function zeroCommission(Vendor $vendor): bool
    {
        return $this->effectivePlan($vendor) === Vendor::PLAN_PREMIUM;
    }

    public function effectivePlan(Vendor $vendor): string
    {
        $plan = Vendor::canonicalPlan($vendor->active_plan_code ?: $vendor->current_plan ?: $vendor->selected_plan);

        if ($plan !== Vendor::PLAN_FREE && $vendor->plan_expires_at?->isPast() && ! $vendor->grace_ends_at?->isFuture()) {
            return Vendor::PLAN_FREE;
        }

        return $plan;
    }

    public function effectiveRule(Vendor $vendor, ?Product $product = null): array
    {
        $preview = $this->preview($vendor, $product, 100, 1);

        return [
            'type' => $preview['type'],
            'rate' => (float) $preview['rate'],
            'source' => $preview['source'],
        ];
    }

    private function planSnapshot(Vendor $vendor): array
    {
        $code = Vendor::canonicalPlan($vendor->active_plan_code ?: $vendor->current_plan ?: $vendor->selected_plan);
        $plan = SellerPlan::query()->where('business_code', $code)->where('status', SellerPlan::STATUS_ACTIVE)->first();

        return [
            'business_code' => $code,
            'plan_version' => $plan?->plan_version ?: 1,
        ];
    }
}
