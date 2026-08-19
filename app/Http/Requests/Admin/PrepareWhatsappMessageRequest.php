<?php

namespace App\Http\Requests\Admin;

use App\Services\WhatsAppIntentService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PrepareWhatsappMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_admin') === true;
    }

    public function rules(): array
    {
        return [
            'purpose' => ['required', Rule::in(array_keys(WhatsAppIntentService::MARKETING_PURPOSES))],
            'message' => ['required', 'string', 'min:5', 'max:1200'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'customer_cart_id' => ['nullable', 'integer', 'exists:customer_carts,id'],
        ];
    }
}
