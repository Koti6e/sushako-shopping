<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vendor_id', 'seller_plan_id', 'plan', 'plan_name_snapshot', 'amount', 'currency', 'provider', 'internal_order_reference', 'provider_order_id', 'provider_payment_id', 'provider_signature', 'status', 'payment_purpose', 'failure_reason', 'verified_at', 'metadata', 'plan_price_snapshot', 'commission_type_snapshot', 'commission_value_snapshot', 'product_limit_snapshot', 'billing_period_snapshot'])]
class SellerOnboardingPayment extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function sellerPlan(): BelongsTo
    {
        return $this->belongsTo(SellerPlan::class);
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'plan_price_snapshot' => 'decimal:2',
            'commission_value_snapshot' => 'decimal:2',
            'metadata' => 'array',
            'verified_at' => 'datetime',
        ];
    }
}
