<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vendor_id', 'plan', 'amount', 'payment_reference', 'status', 'activated_at', 'expires_at', 'grace_starts_at', 'grace_ends_at'])]
class SellerPlanPayment extends Model
{
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'expires_at' => 'datetime',
            'grace_starts_at' => 'datetime',
            'grace_ends_at' => 'datetime',
        ];
    }
}
