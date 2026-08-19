<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'description', 'desktop_image_path', 'mobile_image_path', 'cta_label', 'cta_url', 'display_order', 'is_active', 'starts_at', 'ends_at'])]
class StorefrontPromotionalBanner extends Model
{
    public function scopeVisible($query)
    {
        return $query
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderBy('display_order')
            ->orderBy('id');
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
