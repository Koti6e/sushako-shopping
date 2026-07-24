<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['free_shipping_enabled', 'free_shipping_threshold', 'shipping_policy_text', 'weight_based_shipping_enabled', 'courier_integration_enabled', 'zone_based_shipping_enabled'])]
class ShippingSetting extends Model
{
    protected function casts(): array
    {
        return [
            'free_shipping_enabled' => 'boolean',
            'weight_based_shipping_enabled' => 'boolean',
            'courier_integration_enabled' => 'boolean',
            'zone_based_shipping_enabled' => 'boolean',
        ];
    }
}
