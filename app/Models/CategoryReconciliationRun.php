<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['initiated_by', 'status', 'total_count', 'unchanged_count', 'mapped_count', 'uncertain_count', 'missing_count', 'conflict_count', 'metadata', 'approved_at', 'approved_by'])]
class CategoryReconciliationRun extends Model
{
    public function items(): HasMany { return $this->hasMany(CategoryReconciliationItem::class, 'run_id'); }
    protected function casts(): array { return ['metadata' => 'array', 'approved_at' => 'datetime']; }
}
