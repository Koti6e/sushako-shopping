<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vendor_id', 'catalogue_extracted_product_id', 'product_id', 'outcome', 'confidence', 'evidence', 'changes', 'decision', 'review_notes'])]
class CatalogueProductMatch extends Model
{
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function extractedProduct(): BelongsTo { return $this->belongsTo(CatalogueExtractedProduct::class, 'catalogue_extracted_product_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }

    protected function casts(): array
    {
        return ['confidence' => 'decimal:4', 'evidence' => 'array', 'changes' => 'array'];
    }
}
