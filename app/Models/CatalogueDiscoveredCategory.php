<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['catalogue_discovery_session_id', 'parent_id', 'name', 'canonical_url', 'product_count', 'selected', 'excluded', 'status', 'processed_at'])]
class CatalogueDiscoveredCategory extends Model
{
    public function session(): BelongsTo { return $this->belongsTo(CatalogueDiscoverySession::class, 'catalogue_discovery_session_id'); }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }

    protected function casts(): array
    {
        return ['selected' => 'boolean', 'excluded' => 'boolean', 'processed_at' => 'datetime'];
    }
}
