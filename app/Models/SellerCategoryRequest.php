<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vendor_id', 'requested_category_name', 'description', 'suggested_parent_category', 'suggested_parent_id', 'example_products', 'status', 'admin_comment', 'resolved_category_id', 'reviewed_by', 'reviewed_at'])]
class SellerCategoryRequest extends Model
{
    public const STATUS_PENDING = 'pending_approval';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function suggestedParent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'suggested_parent_id');
    }

    public function resolvedCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'resolved_category_id');
    }

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }
}
