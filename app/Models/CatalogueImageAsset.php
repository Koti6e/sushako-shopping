<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['catalogue_extracted_product_id', 'sort_order', 'original_path', 'processed_path', 'original_hash', 'processed_hash', 'settings', 'status', 'approved_at'])]
class CatalogueImageAsset extends Model
{
    public function product(): BelongsTo { return $this->belongsTo(CatalogueExtractedProduct::class, 'catalogue_extracted_product_id'); }

    protected function casts(): array
    {
        return ['settings' => 'array', 'approved_at' => 'datetime'];
    }
}
