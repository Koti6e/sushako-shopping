<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['company_name', 'legal_business_name', 'logo_path', 'gstin', 'pan', 'cin', 'address_line_1', 'address_line_2', 'city', 'state', 'pincode', 'country', 'support_email', 'support_phone', 'website', 'currency', 'timezone', 'business_hours'])]
class CompanySetting extends Model
{
}
