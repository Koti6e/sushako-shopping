<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['vendor_id', 'type', 'title', 'body', 'action_url', 'dedupe_key', 'read_at'])]
class SellerNotification extends Model
{
    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }
}
