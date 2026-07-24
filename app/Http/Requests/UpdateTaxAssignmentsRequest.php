<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaxAssignmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categories' => ['nullable', 'array'],
            'categories.*.tax_slab_id' => ['nullable', 'exists:tax_slabs,id'],
            'products' => ['nullable', 'array'],
            'products.*.tax_slab_id' => ['nullable', 'exists:tax_slabs,id'],
        ];
    }
}
