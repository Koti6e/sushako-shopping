<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['source_category_id', 'target_category_id', 'source_name', 'target_name', 'status', 'reason', 'reviewed_by'])]
class CategoryMigrationMapping extends Model
{
}
