<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['vendor_id', 'product_variant_id', 'quantity_delta', 'stock_after', 'reason', 'adjusted_by'])]
class SellerInventoryAdjustment extends Model {}
