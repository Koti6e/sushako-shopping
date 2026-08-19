<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'order_id', 'customer_cart_id', 'admin_user_id', 'channel', 'category', 'template_key', 'status', 'message_content', 'opened_at', 'marked_sent_at'])]
class CustomerCommunication extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(CustomerCart::class, 'customer_cart_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'marked_sent_at' => 'datetime',
        ];
    }
}
