<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['order_number', 'invoice_number', 'invoice_sequence', 'invoiced_at', 'user_id', 'customer_name', 'customer_phone', 'customer_email', 'address_line_1', 'address_line_2', 'city', 'pincode', 'landmark', 'delivery_location_url', 'subtotal', 'shipping_amount', 'shipping_status', 'tax_amount', 'cgst_amount', 'sgst_amount', 'igst_amount', 'discount_amount', 'total_amount', 'status', 'payment_method', 'payment_status', 'shipping_provider', 'shipping_provider_other', 'tracking_number', 'razorpay_order_id', 'razorpay_payment_id', 'placed_at', 'packed_at', 'shipped_at', 'delivered_at'])]
class Order extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    protected function casts(): array
    {
        return [
            'placed_at' => 'datetime',
            'invoiced_at' => 'datetime',
            'packed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }
}
