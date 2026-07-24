<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['product_id', 'path', 'label', 'sort_order'])]
class ProductImage extends Model
{
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function url(): string
    {
        if (Str::startsWith($this->path, ['http://', 'https://', '/assets/', 'assets/', '/images/', 'images/'])) {
            return asset(ltrim($this->path, '/'));
        }

        return Storage::disk('public')->url($this->path);
    }
}
