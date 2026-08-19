<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['customer_cart_id', 'product_id', 'product_variant_id', 'cart_key', 'product_name', 'product_slug', 'colour', 'size', 'quantity', 'unit_price', 'line_total', 'image'])]
class CustomerCartItem extends Model
{
    public function cart(): BelongsTo
    {
        return $this->belongsTo(CustomerCart::class, 'customer_cart_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
