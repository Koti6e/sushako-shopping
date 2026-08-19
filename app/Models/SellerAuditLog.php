<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['vendor_id', 'actor_user_id', 'action', 'reason', 'metadata'])]
class SellerAuditLog extends Model
{
    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
