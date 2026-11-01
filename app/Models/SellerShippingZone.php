<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['seller_shipping_profile_id', 'name', 'coverage_type', 'coverage', 'charge_type', 'base_charge', 'per_kg_charge', 'free_above_value', 'priority', 'is_active'])]
class SellerShippingZone extends Model
{
    public function profile(): BelongsTo { return $this->belongsTo(SellerShippingProfile::class, 'seller_shipping_profile_id'); }

    protected function casts(): array
    {
        return ['coverage' => 'array', 'is_active' => 'boolean'];
    }
}
