<?php

namespace Xditn\Oceanpay\Http\Requests\Admin;

use Xditn\Oceanpay\Enums\PaymentMethodStatus;
use Xditn\Oceanpay\Enums\PaymentMethodType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentMethodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => [
                'required',
                Rule::enum(PaymentMethodType::class),
            ],
            'name' => 'required|string|max:255',
            'payment_provider_id' => [
                'required',
                Rule::exists('payment_providers', 'id'),
            ],
            'currency_code' => 'required|string',
            'config' => ['nullable', 'array'],
            'secret_config' => ['nullable', 'array'],
            'sort' => ['nullable', 'numeric'],
            'extra' => 'nullable|array',
            'status' => ['required', Rule::enum(PaymentMethodStatus::class)],
        ];
    }
}
