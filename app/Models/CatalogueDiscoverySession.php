<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['vendor_id', 'seller_domain_import_id', 'status', 'pages_scanned', 'urls_discovered', 'unique_urls', 'duplicate_urls', 'categories_discovered', 'failed_records', 'eligible_products', 'selected_products', 'imported_products', 'options', 'progress', 'error_message', 'started_at', 'finished_at'])]
class CatalogueDiscoverySession extends Model
{
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function source(): BelongsTo { return $this->belongsTo(SellerDomainImport::class, 'seller_domain_import_id'); }
    public function categories(): HasMany { return $this->hasMany(CatalogueDiscoveredCategory::class); }
    public function urls(): HasMany { return $this->hasMany(CatalogueDiscoveredUrl::class); }
    public function products(): HasMany { return $this->hasMany(CatalogueExtractedProduct::class); }

    protected function casts(): array
    {
        return ['options' => 'array', 'progress' => 'array', 'started_at' => 'datetime', 'finished_at' => 'datetime'];
    }
}
