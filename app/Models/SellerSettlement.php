<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['vendor_id', 'settlement_number', 'period_start', 'period_end', 'gross_product_value', 'total_commission', 'refund_deductions', 'other_adjustments', 'net_payable', 'expected_settlement_date', 'paid_at', 'payment_reference', 'status', 'gross_amount', 'platform_fee_amount', 'refund_amount', 'return_amount', 'adjustment_amount', 'final_settlement_amount', 'eligible_quantity', 'platform_fee_per_unit', 'settlement_period_start', 'settlement_period_end', 'payment_mode', 'hold_reason', 'failure_reason', 'admin_note', 'approved_at', 'approved_by', 'payment_marked_by'])]
class SellerSettlement extends Model
{
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SellerSettlementItem::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(SettlementAdjustment::class);
    }

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'expected_settlement_date' => 'date',
            'paid_at' => 'date',
            'settlement_period_start' => 'date',
            'settlement_period_end' => 'date',
            'approved_at' => 'datetime',
        ];
    }
}
