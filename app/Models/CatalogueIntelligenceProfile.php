<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['seller_domain_import_id', 'platform', 'status', 'is_accessible', 'currency', 'category_count', 'product_url_count', 'capabilities', 'strategy', 'limitations', 'analysed_at', 'failed_at', 'error_message', 'profile_version'])]
class CatalogueIntelligenceProfile extends Model
{
    public function source(): BelongsTo { return $this->belongsTo(SellerDomainImport::class, 'seller_domain_import_id'); }

    protected function casts(): array
    {
        return ['is_accessible' => 'boolean', 'capabilities' => 'array', 'strategy' => 'array', 'limitations' => 'array', 'analysed_at' => 'datetime', 'failed_at' => 'datetime'];
    }
}
