<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['provider', 'label', 'enabled', 'environment', 'key_placeholder', 'webhook_url_placeholder', 'is_future_provider'])]
class PaymentSetting extends Model
{
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'is_future_provider' => 'boolean',
        ];
    }
}
