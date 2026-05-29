<?php

namespace Xditn\Oceanpay\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Xditn\Oceanpay\Enums\PaymentMethodType;
use Xditn\Oceanpay\Enums\PaymentProviderChannelStatus;
use Xditn\Oceanpay\Http\Resources\Admin\PaymentProviderChannelResource;

class PaymentProviderChannelController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'type' => ['nullable', Rule::enum(PaymentMethodType::class)],
            'currency_code' => ['nullable', 'string'],
            'payment_provider_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::enum(PaymentProviderChannelStatus::class)],
            'name' => ['nullable', 'string'],
        ]);

        $paymentProviderChannelClass = config('oceanpay.models.payment_provider_channel');

        $channels = $paymentProviderChannelClass::query()
            ->when($request->input('type'), fn (Builder $query, $value) => $query->where('type', $value))
            ->when($request->input('currency_code'), fn (Builder $query, $value) => $query->where('currency_code', $value))
            ->when($request->input('payment_provider_id'), fn (Builder $query, $value) => $query->where('payment_provider_id', $value))
            ->when($request->input('status'), fn (Builder $query, $value) => $query->where('status', $value))
            ->when($request->input('name'), fn (Builder $query, $value) => $query->where('name', 'like', '%'.$value.'%'))
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page'));

        return PaymentProviderChannelResource::collection($channels);
    }

    public function store(Request $request)
    {
        $paymentProviderChannelClass = config('oceanpay.models.payment_provider_channel');

        $channel = $paymentProviderChannelClass::create($this->validateData($request));

        return new PaymentProviderChannelResource($channel);
    }

    public function show(string $id)
    {
        $paymentProviderChannelClass = config('oceanpay.models.payment_provider_channel');

        return new PaymentProviderChannelResource($paymentProviderChannelClass::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $paymentProviderChannelClass = config('oceanpay.models.payment_provider_channel');
        $channel = $paymentProviderChannelClass::findOrFail($id);

        $channel->update($this->validateData($request, false));

        return new PaymentProviderChannelResource($channel);
    }

    public function destroy(string $id)
    {
        $paymentProviderChannelClass = config('oceanpay.models.payment_provider_channel');
        $channel = $paymentProviderChannelClass::findOrFail($id);

        $channel->delete();

        return response()->noContent();
    }

    protected function validateData(Request $request, bool $creating = true): array
    {
        $required = $creating ? 'required' : 'sometimes';

        return $request->validate([
            'type' => [$required, Rule::enum(PaymentMethodType::class)],
            'name' => [$required, 'string', 'max:255'],
            'payment_provider_id' => [$required, Rule::exists('payment_providers', 'id')],
            'currency_code' => [$required, 'string', 'max:32'],
            'code' => ['nullable', 'string', 'max:255'],
            'extra' => ['nullable', 'array'],
            'status' => ['nullable', Rule::enum(PaymentProviderChannelStatus::class)],
        ]);
    }
}
