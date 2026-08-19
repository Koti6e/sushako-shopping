<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SettlementCalculationService
{
    public const ELIGIBLE_ORDER_STATUSES = ['delivered', 'completed'];

    public function eligibleItemsQuery(Vendor $vendor, ?string $start = null, ?string $end = null): Builder
    {
        return OrderItem::query()
            ->with(['order.user', 'product'])
            ->where('vendor_id', $vendor->id)
            ->whereDoesntHave('settlement')
            ->whereHas('order', function (Builder $query) use ($start, $end): void {
                $query->whereIn('status', self::ELIGIBLE_ORDER_STATUSES)
                    ->where(function (Builder $payment): void {
                        $payment->where('payment_method', 'cod')
                            ->orWhere(function (Builder $online): void {
                                $online->where('payment_method', '!=', 'cod')->where('payment_status', 'paid');
                            });
                    })
                    ->when($start, fn (Builder $dateQuery) => $dateQuery->whereDate('created_at', '>=', $start))
                    ->when($end, fn (Builder $dateQuery) => $dateQuery->whereDate('created_at', '<=', $end));
            })
            ->whereNotIn('settlement_status', ['settled', 'cancelled', 'refunded', 'returned']);
    }

    public function summarize(Vendor $vendor, ?string $start = null, ?string $end = null): array
    {
        $items = $this->eligibleItemsQuery($vendor, $start, $end)->get();

        return $this->snapshot($items);
    }

    public function snapshot(Collection $items, float $adjustments = 0): array
    {
        $gross = (float) $items->sum('gross_line_amount');
        $platformFee = (float) $items->sum('platform_fee_total');
        $refunds = (float) $items->sum('refund_amount');
        $returns = (float) $items->sum('return_amount');
        $quantity = (int) $items->sum(fn (OrderItem $item) => (int) ($item->retained_quantity ?? $item->eligible_quantity ?? $item->quantity));

        return [
            'gross_amount' => $gross,
            'platform_fee_amount' => $platformFee,
            'refund_amount' => $refunds,
            'return_amount' => $returns,
            'adjustment_amount' => $adjustments,
            'final_settlement_amount' => max(0, $gross - $platformFee - $refunds - $returns + $adjustments),
            'eligible_quantity' => $quantity,
            'platform_fee_per_unit' => $items->max('platform_fee_per_unit') ?: 0,
            'items_count' => $items->count(),
        ];
    }
}
