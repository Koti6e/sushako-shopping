<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanySettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:120'],
            'legal_business_name' => ['nullable', 'string', 'max:160'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gstin' => ['nullable', 'string', 'max:20'],
            'pan' => ['nullable', 'string', 'max:20'],
            'cin' => ['nullable', 'string', 'max:30'],
            'address_line_1' => ['required', 'string', 'max:180'],
            'address_line_2' => ['nullable', 'string', 'max:180'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'max:120'],
            'pincode' => ['required', 'string', 'max:12'],
            'country' => ['required', 'string', 'max:80'],
            'support_email' => ['nullable', 'email', 'max:160'],
            'support_phone' => ['nullable', 'string', 'max:30'],
            'website' => ['nullable', 'url', 'max:180'],
            'currency' => ['required', 'string', 'size:3'],
            'timezone' => ['required', 'timezone'],
            'business_hours' => ['nullable', 'string', 'max:180'],
        ];
    }
}
