<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['catalogue_discovery_session_id', 'catalogue_discovered_category_id', 'canonical_url', 'source_hash', 'identifier', 'status', 'error_message'])]
class CatalogueDiscoveredUrl extends Model
{
    public function session(): BelongsTo { return $this->belongsTo(CatalogueDiscoverySession::class, 'catalogue_discovery_session_id'); }
    public function category(): BelongsTo { return $this->belongsTo(CatalogueDiscoveredCategory::class, 'catalogue_discovered_category_id'); }
}
