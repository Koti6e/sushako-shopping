<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['order_number', 'invoice_number', 'invoice_sequence', 'invoiced_at', 'user_id', 'customer_name', 'customer_phone', 'customer_email', 'address_line_1', 'address_line_2', 'city', 'pincode', 'landmark', 'delivery_location_url', 'delivery_latitude', 'delivery_longitude', 'location_confirmed', 'location_captured_at', 'location_updated_by', 'location_capture_method', 'subtotal', 'shipping_amount', 'shipping_status', 'tax_amount', 'cgst_amount', 'sgst_amount', 'igst_amount', 'discount_amount', 'total_amount', 'status', 'seller_order_status', 'seller_acceptance_due_at', 'seller_accepted_at', 'seller_overdue_at', 'customer_overdue_choice', 'customer_overdue_choice_at', 'payment_method', 'payment_status', 'shipping_provider', 'shipping_provider_other', 'tracking_number', 'tracking_url', 'razorpay_order_id', 'razorpay_payment_id', 'placed_at', 'packed_at', 'shipped_at', 'delivered_at', 'cancelled_at', 'cancellation_reason'])]
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

    public function shippingLabels(): HasMany
    {
        return $this->hasMany(ShippingLabel::class);
    }

    public function statusEvents(): HasMany
    {
        return $this->hasMany(OrderStatusEvent::class);
    }

    public function latestShippingLabel(): HasOne
    {
        return $this->hasOne(ShippingLabel::class)->latest();
    }

    protected function casts(): array
    {
        return [
            'placed_at' => 'datetime',
            'invoiced_at' => 'datetime',
            'packed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'seller_acceptance_due_at' => 'datetime',
            'seller_accepted_at' => 'datetime',
            'seller_overdue_at' => 'datetime',
            'customer_overdue_choice_at' => 'datetime',
            'location_confirmed' => 'boolean',
            'location_captured_at' => 'datetime',
            'delivery_latitude' => 'decimal:7',
            'delivery_longitude' => 'decimal:7',
        ];
    }
}
