<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['catalogue_import_batch_id', 'catalogue_product_match_id', 'action', 'status', 'approved_fields', 'error_message', 'decided_at'])]
class CatalogueImportItem extends Model
{
    public function batch(): BelongsTo { return $this->belongsTo(CatalogueImportBatch::class, 'catalogue_import_batch_id'); }
    public function match(): BelongsTo { return $this->belongsTo(CatalogueProductMatch::class, 'catalogue_product_match_id'); }

    protected function casts(): array
    {
        return ['approved_fields' => 'array', 'decided_at' => 'datetime'];
    }
}
