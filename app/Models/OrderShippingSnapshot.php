<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'vendor_id', 'zone_name', 'shipping_method', 'shipping_charge', 'pincode', 'delivery_address', 'rule_details'])]
class OrderShippingSnapshot extends Model
{
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }

    protected function casts(): array { return ['delivery_address' => 'array', 'rule_details' => 'array']; }
}
