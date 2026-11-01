<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vendor_id', 'seller_domain_import_id', 'frequency', 'new_products', 'price_changes', 'image_changes', 'availability_changes', 'auto_publish', 'last_run_at'])]
class CatalogueMonitoringSetting extends Model
{
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function source(): BelongsTo { return $this->belongsTo(SellerDomainImport::class, 'seller_domain_import_id'); }

    protected function casts(): array
    {
        return ['new_products' => 'boolean', 'price_changes' => 'boolean', 'image_changes' => 'boolean', 'availability_changes' => 'boolean', 'auto_publish' => 'boolean', 'last_run_at' => 'datetime'];
    }
}
