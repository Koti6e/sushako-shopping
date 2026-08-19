<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['admin_user_id', 'export_type', 'filters', 'record_count'])]
class CustomerExportAudit extends Model
{
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    protected function casts(): array
    {
        return [
            'filters' => 'array',
        ];
    }
}
