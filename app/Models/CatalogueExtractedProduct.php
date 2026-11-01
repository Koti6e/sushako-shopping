<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['catalogue_discovery_session_id', 'catalogue_discovered_url_id', 'canonical_url', 'identity_hash', 'name', 'sku', 'gtin', 'mpn', 'brand', 'price', 'currency', 'availability', 'payload', 'images', 'variants', 'extracted_at', 'status', 'error_message'])]
class CatalogueExtractedProduct extends Model
{
    public function session(): BelongsTo { return $this->belongsTo(CatalogueDiscoverySession::class, 'catalogue_discovery_session_id'); }
    public function sourceUrl(): BelongsTo { return $this->belongsTo(CatalogueDiscoveredUrl::class, 'catalogue_discovered_url_id'); }
    public function match(): HasOne { return $this->hasOne(CatalogueProductMatch::class); }

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'payload' => 'array', 'images' => 'array', 'variants' => 'array', 'extracted_at' => 'datetime'];
    }
}
