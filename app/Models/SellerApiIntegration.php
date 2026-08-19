<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['vendor_id', 'provider', 'api_key', 'api_secret', 'webhook_url', 'status', 'last_successful_sync_at', 'last_error'])]
class SellerApiIntegration extends Model
{
    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'api_secret' => 'encrypted',
            'last_successful_sync_at' => 'datetime',
        ];
    }
}
