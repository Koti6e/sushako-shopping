<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'session_id', 'customer_type', 'status', 'original_value', 'recovery_token', 'recovery_token_expires_at', 'last_activity_at', 'abandoned_at', 'reminder_opened_at', 'reminder_marked_sent_at', 'recovered_at', 'recovered_order_id', 'dismissed_at', 'expired_at', 'coupon_used'])]
class CustomerCart extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_ABANDONED = 'abandoned';

    public const STATUS_WHATSAPP_OPENED = 'whatsapp_opened';

    public const STATUS_REMINDER_MARKED_SENT = 'reminder_marked_sent';

    public const STATUS_RECOVERED = 'recovered';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_DISMISSED = 'dismissed';

    public const STATUS_CONVERTED_WITHOUT_REMINDER = 'converted_without_reminder';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CustomerCartItem::class);
    }

    public function recoveredOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'recovered_order_id');
    }

    public function communications(): HasMany
    {
        return $this->hasMany(CustomerCommunication::class);
    }

    protected function casts(): array
    {
        return [
            'last_activity_at' => 'datetime',
            'abandoned_at' => 'datetime',
            'reminder_opened_at' => 'datetime',
            'reminder_marked_sent_at' => 'datetime',
            'recovered_at' => 'datetime',
            'dismissed_at' => 'datetime',
            'expired_at' => 'datetime',
            'recovery_token_expires_at' => 'datetime',
        ];
    }
}
