<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['invoice_prefix', 'next_invoice_number', 'invoice_footer', 'terms_conditions', 'authorized_signatory_name', 'authorized_signatory_designation'])]
class InvoiceSetting extends Model
{
}
