<?php

namespace Xditn\Oceanpay\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class PaymentProviderCurrencyController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'payment_provider_id' => ['nullable', 'integer'],
            'currency_code' => ['nullable', 'string'],
        ]);

        $paymentProviderCurrencyClass = config('oceanpay.models.payment_provider_currency');

        $currencies = $paymentProviderCurrencyClass::query()
            ->when($request->input('payment_provider_id'), fn ($query, $value) => $query->where('payment_provider_id', $value))
            ->when($request->input('currency_code'), fn ($query, $value) => $query->where('currency_code', $value))
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page'));

        return response()->json($currencies);
    }

    public function store(Request $request)
    {
        $paymentProviderCurrencyClass = config('oceanpay.models.payment_provider_currency');

        $currency = $paymentProviderCurrencyClass::create($this->validateData($request));

        return response()->json(['data' => $currency], 201);
    }

    public function show(string $id)
    {
        $paymentProviderCurrencyClass = config('oceanpay.models.payment_provider_currency');

        return response()->json([
            'data' => $paymentProviderCurrencyClass::findOrFail($id),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $paymentProviderCurrencyClass = config('oceanpay.models.payment_provider_currency');
        $currency = $paymentProviderCurrencyClass::findOrFail($id);

        $currency->update($this->validateData($request, false));

        return response()->json(['data' => $currency]);
    }

    public function destroy(string $id)
    {
        $paymentProviderCurrencyClass = config('oceanpay.models.payment_provider_currency');
        $currency = $paymentProviderCurrencyClass::findOrFail($id);

        $currency->delete();

        return response()->noContent();
    }

    protected function validateData(Request $request, bool $creating = true): array
    {
        $required = $creating ? 'required' : 'sometimes';

        return $request->validate([
            'payment_provider_id' => [$required, Rule::exists('payment_providers', 'id')],
            'currency_code' => [$required, 'string', 'max:32'],
        ]);
    }
}
