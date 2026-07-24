<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'providers' => ['required', 'array'],
            'providers.*.enabled' => ['nullable', 'boolean'],
            'providers.*.environment' => ['required', Rule::in(['sandbox', 'production'])],
            'providers.*.key_placeholder' => ['nullable', 'string', 'max:180'],
            'providers.*.webhook_url_placeholder' => ['nullable', 'string', 'max:250'],
        ];
    }
}
