<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['vendor_id', 'seller_settlement_id', 'admin_user_id', 'action', 'reason', 'metadata'])]
class FinancialAuditLog extends Model
{
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
