<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['vendor_id', 'domain', 'verification_token', 'verification_method', 'verified_at', 'status', 'page_limit', 'crawl_depth'])]
class SellerDomainImport extends Model
{
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }

    public function jobs(): HasMany { return $this->hasMany(SellerImportJob::class); }

    protected function casts(): array
    {
        return ['verified_at' => 'datetime'];
    }
}
