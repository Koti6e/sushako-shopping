<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['vendor_id', 'category_id', 'product_id', 'plan', 'type', 'rate', 'starts_at', 'ends_at', 'is_active'])]
class CommissionRule extends Model
{
    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
