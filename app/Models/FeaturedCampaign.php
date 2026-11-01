<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['vendor_id', 'product_id', 'status', 'amount', 'currency', 'razorpay_order_id', 'razorpay_payment_id', 'payment_status', 'starts_at', 'ends_at', 'impressions', 'clicks'])]
class FeaturedCampaign extends Model
{
    public const PRICE = 99;
    public const DURATION_DAYS = 7;

    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function events(): HasMany { return $this->hasMany(FeaturedCampaignEvent::class); }
    public function payments(): HasMany { return $this->hasMany(FeaturedCampaignPayment::class); }

    public function scopeVisible($query)
    {
        return $query->where('status', 'active')->where('payment_status', 'paid')
            ->where('starts_at', '<=', now())->where('ends_at', '>', now())
            ->whereHas('product', fn ($products) => $products->where('is_published', true));
    }

    public function isVisible(): bool
    {
        $this->loadMissing('product');
        return $this->status === 'active' && $this->payment_status === 'paid' && $this->starts_at?->isPast() && $this->ends_at?->isFuture() && (bool) $this->product?->is_published;
    }

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }
}
