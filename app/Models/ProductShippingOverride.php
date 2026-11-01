<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'vendor_id', 'mode', 'charge', 'method', 'is_active'])]
class ProductShippingOverride extends Model
{
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }

    protected function casts(): array { return ['is_active' => 'boolean']; }
}
