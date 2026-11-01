<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['vendor_id', 'domain', 'verification_token', 'verification_method', 'verified_at', 'status', 'page_limit', 'crawl_depth', 'settings'])]
class SellerDomainImport extends Model
{
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }

    public function jobs(): HasMany { return $this->hasMany(SellerImportJob::class); }

    public function intelligenceProfile(): HasOne { return $this->hasOne(CatalogueIntelligenceProfile::class); }

    public function monitoring(): HasOne { return $this->hasOne(CatalogueMonitoringSetting::class); }

    protected function casts(): array
    {
        return ['verified_at' => 'datetime', 'settings' => 'array'];
    }
}
