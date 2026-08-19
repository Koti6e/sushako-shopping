<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_admin') === true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:160'],
            'name' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'string', 'max:120'],
            'customer_id' => ['nullable', 'integer', 'min:1'],
            'order_number' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'suspended', 'blocked', 'pending'])],
            'type' => ['nullable', Rule::in(['registered', 'guest', 'converted', 'repeat'])],
            'payment' => ['nullable', Rule::in(['cod', 'online', 'mixed'])],
            'activity' => ['nullable', Rule::in(['new', 'inactive_30', 'inactive_60', 'cancelled', 'abandoned'])],
            'registered_from' => ['nullable', 'date'],
            'registered_to' => ['nullable', 'date', 'after_or_equal:registered_from'],
            'last_order_from' => ['nullable', 'date'],
            'last_order_to' => ['nullable', 'date', 'after_or_equal:last_order_from'],
            'sort' => ['nullable', Rule::in(['latest_activity', 'name', 'orders', 'spent', 'oldest', 'last_order'])],
            'view' => ['nullable', Rule::in(['cards', 'table'])],
            'export' => ['nullable', Rule::in(['customer_list', 'order_summary', 'cod_report', 'online_paid_report', 'abandoned_cart_report', 'marketing_consent_report', 'selected_customer'])],
        ];
    }

    public function filters(): array
    {
        return collect($this->validated())
            ->except(['export', 'view'])
            ->filter(fn ($value): bool => filled($value))
            ->all();
    }
}
