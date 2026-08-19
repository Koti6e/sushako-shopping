<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'price', 'billing_period', 'product_limit', 'unlimited_products', 'order_limit', 'commission_type', 'commission_value', 'is_paid', 'grace_period_days', 'status', 'features', 'supporting_text'])]
class SellerPlan extends Model
{
    public const STATUS_ACTIVE = 'active';

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'commission_value' => 'decimal:2',
            'unlimited_products' => 'boolean',
            'is_paid' => 'boolean',
            'features' => 'array',
        ];
    }
}
