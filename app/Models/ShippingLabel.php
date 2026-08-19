<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_id',
    'label_number',
    'version',
    'status',
    'fulfillment_type',
    'brand_mode',
    'print_format',
    'courier_code',
    'courier_name',
    'warehouse_name',
    'warehouse_address',
    'seller_name',
    'seller_logo_path',
    'seller_address',
    'seller_gst',
    'seller_contact',
    'seller_return_address',
    'package_count',
    'package_index',
    'weight_grams',
    'generated_by_id',
    'generated_at',
    'regenerated_by_id',
    'regenerated_at',
    'previous_label_id',
    'printed_at',
    'printed_by_id',
    'print_count',
    'last_printed_printer',
    'internal_lookup_code',
    'location_qr_url',
    'metadata',
])]
class ShippingLabel extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_GENERATED = 'generated';

    public const STATUS_PRINTED = 'printed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_REGENERATED = 'regenerated';

    public const STATUS_ARCHIVED = 'archived';

    public const BRAND_SUSHAKO = 'sushako';

    public const BRAND_SELLER = 'seller';

    public const BRAND_COURIER_NEUTRAL = 'courier_neutral';

    public const FULFILLMENT_SUSHAKO = 'fulfilled_by_sushako';

    public const FULFILLMENT_SELLER_DIRECT = 'seller_direct';

    public const FULFILLMENT_WAREHOUSE = 'warehouse';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by_id');
    }

    public function printedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'printed_by_id');
    }

    public function previousLabel(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous_label_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ShippingLabelEvent::class);
    }

    public function isPrinted(): bool
    {
        return filled($this->printed_at) || $this->status === self::STATUS_PRINTED;
    }

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'regenerated_at' => 'datetime',
            'printed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
