<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_prefix' => ['required', 'string', 'max:20'],
            'next_invoice_number' => ['required', 'integer', 'min:1'],
            'invoice_footer' => ['nullable', 'string', 'max:1000'],
            'terms_conditions' => ['nullable', 'string', 'max:2000'],
            'authorized_signatory_name' => ['nullable', 'string', 'max:120'],
            'authorized_signatory_designation' => ['nullable', 'string', 'max:120'],
        ];
    }
}
