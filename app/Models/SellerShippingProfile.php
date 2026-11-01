<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['vendor_id', 'default_method', 'free_shipping_threshold', 'free_local_pickup', 'is_active'])]
class SellerShippingProfile extends Model
{
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function zones(): HasMany { return $this->hasMany(SellerShippingZone::class); }

    protected function casts(): array
    {
        return ['free_local_pickup' => 'boolean', 'is_active' => 'boolean'];
    }
}
