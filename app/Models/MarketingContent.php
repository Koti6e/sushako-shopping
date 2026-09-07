<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'type',
    'placement',
    'title',
    'slug',
    'subtitle',
    'description',
    'cta_label',
    'destination_type',
    'destination_url',
    'category_id',
    'product_id',
    'product_collection_id',
    'image_path',
    'mobile_image_path',
    'display_order',
    'is_active',
    'starts_at',
    'ends_at',
])]
class MarketingContent extends Model
{
    public const TYPE_HERO = 'hero_banner';
    public const TYPE_PROMOTION = 'promotional_banner';
    public const TYPE_CATEGORY = 'category_banner';
    public const TYPE_COLLECTION = 'product_collection';
    public const TYPE_CAMPAIGN = 'campaign';
    public const TYPE_ANNOUNCEMENT = 'announcement';

    public const DESTINATION_NONE = 'none';
    public const DESTINATION_CATEGORY = 'category';
    public const DESTINATION_PRODUCT = 'product';
    public const DESTINATION_COLLECTION = 'collection';
    public const DESTINATION_URL = 'url';

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productCollection(): BelongsTo
    {
        return $this->belongsTo(ProductCollection::class);
    }

    public function scopeVisible($query)
    {
        return $query
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderBy('display_order')
            ->orderBy('id');
    }

    public function destination(): ?string
    {
        return match ($this->destination_type) {
            self::DESTINATION_CATEGORY => $this->category ? route('category.show', $this->category->slug) : null,
            self::DESTINATION_PRODUCT => $this->product ? route('products.show', $this->product->slug) : null,
            self::DESTINATION_COLLECTION => $this->productCollection ? route('collections.show', $this->productCollection->slug) : null,
            self::DESTINATION_URL => $this->destination_url,
            default => null,
        };
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
