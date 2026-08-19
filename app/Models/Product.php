<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['vendor_id', 'category_id', 'seller_storefront_category_id', 'tax_slab_id', 'name', 'slug', 'collection', 'subcategory', 'brand', 'badge', 'short_description', 'full_description', 'fabric', 'fit', 'sleeve', 'pattern', 'occasion', 'country_of_origin', 'return_policy', 'mrp', 'selling_price', 'buying_price', 'rating', 'reviews', 'is_published', 'is_new', 'is_best_seller', 'local_delivery', 'fulfillment_scope', 'sort_order', 'seller_status', 'scheduled_go_live_at', 'published_mode', 'seller_rejection_reason', 'seller_product_views', 'continue_selling_when_out_of_stock', 'product_condition', 'refurbishment_type', 'refurbishment_grade', 'condition_description', 'cosmetic_condition', 'testing_details', 'warranty_period', 'package_contents', 'weight_grams', 'length_cm', 'width_cm', 'height_cm', 'low_stock_threshold'])]
class Product extends Model
{
    public const SELLER_STATUS_DRAFT = 'draft';

    public const SELLER_STATUS_PENDING_REVIEW = 'pending_review';

    public const SELLER_STATUS_APPROVED = 'approved';

    public const SELLER_STATUS_ACTIVE = 'approved';

    public const SELLER_STATUS_SCHEDULED = 'scheduled';

    public const SELLER_STATUS_INACTIVE = 'inactive';

    public const SELLER_STATUS_OUT_OF_STOCK = 'out_of_stock';

    public const SELLER_STATUS_POLICY_REVIEW = 'policy_review';

    public const SELLER_STATUS_REJECTED = 'rejected';

    public const SELLER_STATUS_ARCHIVED = 'archived';

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function sellerStorefrontCategory(): BelongsTo
    {
        return $this->belongsTo(SellerStorefrontCategory::class);
    }

    public function taxSlab(): BelongsTo
    {
        return $this->belongsTo(TaxSlab::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function primaryImage(): ?ProductImage
    {
        return $this->images->first();
    }

    public function totalStock(): int
    {
        return (int) $this->variants->sum('stock');
    }

    protected function casts(): array
    {
        return [
            'rating' => 'float',
            'is_published' => 'boolean',
            'is_new' => 'boolean',
            'is_best_seller' => 'boolean',
            'local_delivery' => 'boolean',
            'scheduled_go_live_at' => 'datetime',
        ];
    }
}
