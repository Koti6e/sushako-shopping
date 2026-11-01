<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vendor_id', 'seller_domain_import_id', 'status', 'new_products', 'price_changes', 'image_changes', 'availability_changes', 'error_message', 'started_at', 'finished_at'])]
class CatalogueMonitoringRun extends Model
{
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function source(): BelongsTo { return $this->belongsTo(SellerDomainImport::class, 'seller_domain_import_id'); }

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'finished_at' => 'datetime'];
    }
}
