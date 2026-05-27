<?php

namespace Xditn\Oceanpay\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Xditn\Oceanpay\Enums\PaymentProviderStatus;

class UpdatePaymentProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('payment_provider') ?? $this->route('id');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:255', Rule::unique('payment_providers', 'code')->ignore($id)],
            'status' => ['sometimes', Rule::enum(PaymentProviderStatus::class)],
            'deposit_config' => ['nullable', 'array'],
            'deposit_secret_config' => ['nullable', 'array'],
            'withdrawal_config' => ['nullable', 'array'],
            'withdrawal_secret_config' => ['nullable', 'array'],
        ];
    }
}
