<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'admin_user_id', 'from_status', 'to_status', 'reason'])]
class CustomerStatusLog extends Model {}
