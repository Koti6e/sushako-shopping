<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['provider', 'event_id', 'event', 'order_id', 'provider_order_id', 'provider_payment_id', 'payload_hash', 'status', 'failure_reason', 'metadata', 'processed_at'])]
class PaymentWebhookEvent extends Model
{
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
