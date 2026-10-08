<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['vendor_id', 'seller_domain_import_id', 'idempotency_key', 'status', 'pages_found', 'products_found', 'products_ready', 'products_needing_review', 'error_message', 'started_at', 'finished_at'])]
class SellerImportJob extends Model
{
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }

    public function domainImport(): BelongsTo { return $this->belongsTo(SellerDomainImport::class); }

    public function candidates(): HasMany { return $this->hasMany(SellerImportCandidate::class); }

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'finished_at' => 'datetime'];
    }
}
