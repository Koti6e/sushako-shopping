<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['catalogue_extracted_product_id', 'fields', 'generated_by', 'status', 'approved_at'])]
class CatalogueEnrichmentSuggestion extends Model
{
    public function product(): BelongsTo { return $this->belongsTo(CatalogueExtractedProduct::class, 'catalogue_extracted_product_id'); }

    protected function casts(): array
    {
        return ['fields' => 'array', 'approved_at' => 'datetime'];
    }
}
