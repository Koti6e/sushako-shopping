<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['from_path', 'to_path', 'status_code', 'is_active'])]
class SlugRedirect extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
