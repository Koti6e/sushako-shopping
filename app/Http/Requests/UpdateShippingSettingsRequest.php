<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShippingSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'free_shipping_enabled' => ['nullable', 'boolean'],
            'free_shipping_threshold' => ['required', 'integer', 'min:0'],
            'shipping_policy_text' => ['required', 'string', 'max:2000'],
            'weight_based_shipping_enabled' => ['nullable', 'boolean'],
            'courier_integration_enabled' => ['nullable', 'boolean'],
            'zone_based_shipping_enabled' => ['nullable', 'boolean'],
        ];
    }
}
