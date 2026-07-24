<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['category_id', 'tax_slab_id', 'name', 'slug', 'collection', 'subcategory', 'brand', 'badge', 'short_description', 'full_description', 'fabric', 'fit', 'sleeve', 'pattern', 'occasion', 'country_of_origin', 'return_policy', 'mrp', 'selling_price', 'rating', 'reviews', 'is_published', 'is_new', 'is_best_seller', 'local_delivery', 'fulfillment_scope', 'sort_order'])]
class Product extends Model
{
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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
        ];
    }
}
