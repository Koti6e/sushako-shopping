<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'vendor_id', 'status', 'shipping_method', 'shipping_charge', 'expected_delivery_date', 'tracking_number', 'tracking_url', 'customer_contacted_at', 'shipping_confirmed_at', 'fulfilment_notes', 'policy_version'])]
class OrderVendorFulfilment extends Model
{
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }

    protected function casts(): array
    {
        return ['shipping_charge' => 'integer', 'expected_delivery_date' => 'date', 'customer_contacted_at' => 'datetime', 'shipping_confirmed_at' => 'datetime'];
    }
}
