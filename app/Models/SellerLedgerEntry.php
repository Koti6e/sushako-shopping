<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vendor_id', 'order_id', 'order_item_id', 'entry_type', 'gross_amount', 'commission_amount', 'net_amount', 'status', 'reason', 'created_by'])]
class SellerLedgerEntry extends Model
{
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
