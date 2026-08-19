<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['seller_settlement_id', 'order_item_id', 'gross_amount', 'commission_amount', 'seller_earning', 'eligible_quantity', 'platform_fee_per_unit', 'platform_fee_total', 'refund_amount', 'return_amount', 'adjustment_amount', 'final_amount'])]
class SellerSettlementItem extends Model
{
    public function settlement(): BelongsTo
    {
        return $this->belongsTo(SellerSettlement::class, 'seller_settlement_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
