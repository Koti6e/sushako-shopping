<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['vendor_id', 'catalogue_discovery_session_id', 'status', 'selected_count', 'imported_count', 'failed_count'])]
class CatalogueImportBatch extends Model
{
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function session(): BelongsTo { return $this->belongsTo(CatalogueDiscoverySession::class, 'catalogue_discovery_session_id'); }
    public function items(): HasMany { return $this->hasMany(CatalogueImportItem::class); }
}
