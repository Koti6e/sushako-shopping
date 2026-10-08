<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['seller_import_job_id', 'source_url', 'source_hash', 'payload', 'category_suggestion', 'duplicate_product_id', 'status', 'review_notes'])]
class SellerImportCandidate extends Model
{
    public function job(): BelongsTo { return $this->belongsTo(SellerImportJob::class, 'seller_import_job_id'); }

    public function duplicateProduct(): BelongsTo { return $this->belongsTo(Product::class, 'duplicate_product_id'); }

    protected function casts(): array
    {
        return ['payload' => 'array', 'category_suggestion' => 'array'];
    }
}
