<?php

namespace Xditn\Oceanpay\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Xditn\Oceanpay\Enums\PaymentProviderStatus;

class StorePaymentProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'unique:payment_providers,code'],
            'status' => ['nullable', Rule::enum(PaymentProviderStatus::class)],
            'deposit_config' => ['nullable', 'array'],
            'deposit_secret_config' => ['nullable', 'array'],
            'withdrawal_config' => ['nullable', 'array'],
            'withdrawal_secret_config' => ['nullable', 'array'],
        ];
    }
}
