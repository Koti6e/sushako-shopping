<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'tagline', 'headline', 'description', 'image', 'accent', 'tax_slab_id', 'is_active', 'sort_order'])]
class Category extends Model
{
    public function taxSlab(): BelongsTo
    {
        return $this->belongsTo(TaxSlab::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
