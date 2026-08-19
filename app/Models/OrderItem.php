<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['vendor_id', 'product_id', 'product_variant_id', 'product_name', 'product_slug', 'colour', 'size', 'quantity', 'unit_price', 'line_total', 'gst_rate', 'tax_amount', 'cgst_amount', 'sgst_amount', 'igst_amount', 'image', 'seller_plan_at_order', 'gross_line_amount', 'commission_base', 'commission_type', 'commission_rate', 'commission_amount', 'seller_earning', 'commission_rule_source', 'commission_calculated_at', 'settlement_status', 'eligible_quantity', 'platform_fee_per_unit', 'platform_fee_total', 'refund_amount', 'return_amount', 'adjustment_amount', 'retained_quantity', 'settled_quantity'])]
class OrderItem extends Model
{
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function settlement(): HasOne
    {
        return $this->hasOne(SellerSettlementItem::class);
    }

    protected function casts(): array
    {
        return [
            'commission_calculated_at' => 'datetime',
        ];
    }
}
