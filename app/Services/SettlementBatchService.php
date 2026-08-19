<?php

namespace App\Services;

use App\Models\FinancialAuditLog;
use App\Models\SellerSettlement;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class SettlementBatchService
{
    public function __construct(private readonly SettlementCalculationService $calculator) {}

    public function create(Vendor $vendor, ?string $start, ?string $end, int $adminUserId): SellerSettlement
    {
        return DB::transaction(function () use ($vendor, $start, $end, $adminUserId): SellerSettlement {
            $items = $this->calculator->eligibleItemsQuery($vendor, $start, $end)->lockForUpdate()->get();
            abort_if($items->isEmpty(), 422, 'No eligible delivered order items are available for settlement.');

            $snapshot = $this->calculator->snapshot($items);
            $settlement = SellerSettlement::query()->create([
                'vendor_id' => $vendor->id,
                'settlement_number' => 'SET-'.now()->format('YmdHis').'-'.$vendor->id,
                'period_start' => $start,
                'period_end' => $end,
                'settlement_period_start' => $start,
                'settlement_period_end' => $end,
                'gross_product_value' => $snapshot['gross_amount'],
                'total_commission' => $snapshot['platform_fee_amount'],
                'refund_deductions' => $snapshot['refund_amount'] + $snapshot['return_amount'],
                'other_adjustments' => $snapshot['adjustment_amount'],
                'net_payable' => $snapshot['final_settlement_amount'],
                'expected_settlement_date' => now()->addWeekdays(3)->toDateString(),
                'status' => 'pending_approval',
            ] + $snapshot);

            foreach ($items as $item) {
                $settlement->items()->create([
                    'order_item_id' => $item->id,
                    'gross_amount' => $item->gross_line_amount,
                    'commission_amount' => $item->platform_fee_total ?: $item->commission_amount,
                    'seller_earning' => $item->seller_earning,
                    'eligible_quantity' => $item->retained_quantity ?? $item->eligible_quantity ?? $item->quantity,
                    'platform_fee_per_unit' => $item->platform_fee_per_unit,
                    'platform_fee_total' => $item->platform_fee_total,
                    'refund_amount' => $item->refund_amount,
                    'return_amount' => $item->return_amount,
                    'adjustment_amount' => $item->adjustment_amount,
                    'final_amount' => max(0, (float) $item->gross_line_amount - (float) $item->platform_fee_total - (float) $item->refund_amount - (float) $item->return_amount + (float) $item->adjustment_amount),
                ]);

                $item->forceFill([
                    'settlement_status' => 'pending_approval',
                    'settled_quantity' => $item->retained_quantity ?? $item->eligible_quantity ?? $item->quantity,
                ])->save();
            }

            FinancialAuditLog::query()->create([
                'vendor_id' => $vendor->id,
                'seller_settlement_id' => $settlement->id,
                'admin_user_id' => $adminUserId,
                'action' => 'settlement_generated',
                'metadata' => $snapshot,
            ]);

            return $settlement;
        });
    }

    public function transition(SellerSettlement $settlement, string $status, int $adminUserId, ?string $reason = null, array $extra = []): SellerSettlement
    {
        abort_if($settlement->status === 'paid' && $status !== 'paid', 422, 'Paid settlements are immutable. Apply corrections in a future adjustment.');

        return DB::transaction(function () use ($settlement, $status, $adminUserId, $reason, $extra): SellerSettlement {
            $payload = ['status' => $status] + $extra;

            if ($status === 'approved') {
                $payload += ['approved_at' => now(), 'approved_by' => $adminUserId];
            }

            $settlement->forceFill($payload)->save();
            $settlement->items()->with('orderItem')->get()->each(function ($item) use ($status): void {
                $item->orderItem?->forceFill(['settlement_status' => $status === 'paid' ? 'settled' : $status])->save();
            });

            FinancialAuditLog::query()->create([
                'vendor_id' => $settlement->vendor_id,
                'seller_settlement_id' => $settlement->id,
                'admin_user_id' => $adminUserId,
                'action' => 'settlement_'.$status,
                'reason' => $reason,
                'metadata' => $extra,
            ]);

            return $settlement->fresh();
        });
    }
}
