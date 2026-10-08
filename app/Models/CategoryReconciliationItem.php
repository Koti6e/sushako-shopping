<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['run_id', 'product_id', 'current_category_id', 'proposed_category_id', 'status', 'confidence', 'reason', 'old_slug', 'new_slug', 'reviewed_by', 'reviewed_at'])]
class CategoryReconciliationItem extends Model
{
    protected function casts(): array { return ['confidence' => 'decimal:2', 'reviewed_at' => 'datetime']; }
}
