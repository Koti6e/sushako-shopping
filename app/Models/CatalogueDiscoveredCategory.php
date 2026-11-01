<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['catalogue_discovery_session_id', 'parent_id', 'name', 'canonical_url', 'product_count', 'status'])]
class CatalogueDiscoveredCategory extends Model
{
    public function session(): BelongsTo { return $this->belongsTo(CatalogueDiscoverySession::class, 'catalogue_discovery_session_id'); }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
}
