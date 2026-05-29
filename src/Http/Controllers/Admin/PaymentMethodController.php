<?php

namespace Xditn\Oceanpay\Http\Controllers\Admin;

use Xditn\Oceanpay\Enums\PaymentMethodStatus;
use Illuminate\Routing\Controller;
use Xditn\Oceanpay\Http\Requests\Admin\StorePaymentMethodRequest;
use Xditn\Oceanpay\Http\Requests\Admin\UpdatePaymentMethodRequest;
use Xditn\Oceanpay\Http\Resources\Admin\PaymentMethodResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        $paymentMethodClass = config('oceanpay.models.payment_method');

        $resource = $paymentMethodClass::query()
            ->when($request->status, function ($query, $value) {
                $query->where('status', $value);
            })
            ->when($request->type, function ($query, $value) {
                $query->where('type', $value);
            })
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page'));

        return PaymentMethodResource::collection($resource)->resolve();
    }

    public function store(StorePaymentMethodRequest $request)
    {
        $paymentMethodClass = config('oceanpay.models.payment_method');
        $resource = $paymentMethodClass::create($this->preparePayload($request->validated()));

        return new PaymentMethodResource($resource);
    }

    public function show(string $id)
    {
        $paymentMethodClass = config('oceanpay.models.payment_method');
        $resource = $paymentMethodClass::findOrFail($id);

        return PaymentMethodResource::make($resource);
    }

    public function update(UpdatePaymentMethodRequest $request, string $id)
    {
        $paymentMethodClass = config('oceanpay.models.payment_method');
        $resource = $paymentMethodClass::findOrFail($id);

        $resource->update($this->preparePayload($request->validated()));

        return PaymentMethodResource::make($resource);
    }

    public function destroy(string $id)
    {
        $paymentMethodClass = config('oceanpay.models.payment_method');
        $resource = $paymentMethodClass::findOrFail($id);

        $resource->delete();

        return response()->noContent();
    }

    public function status(Request $request, string $id)
    {
        $request->validate([
            'status' => [
                'required',
                Rule::enum(PaymentMethodStatus::class),
            ],
        ]);

        $paymentMethodClass = config('oceanpay.models.payment_method');
        $resource = $paymentMethodClass::findOrFail($id);

        $resource->update($request->only(['status']));

        return PaymentMethodResource::make($resource);
    }

    public function config(Request $request, string $id)
    {
        $validated = $request->validate([
            'config' => 'required|array',
            'secret_config' => 'nullable|array',
        ]);

        $paymentMethodClass = config('oceanpay.models.payment_method');
        $paymentMethod = $paymentMethodClass::findOrFail($id);

        $payload = ['config' => $validated['config']];

        if (array_key_exists('secret_config', $validated)) {
            $payload['secret_config'] = encrypt($validated['secret_config']);
        }

        $paymentMethod->update($payload);

        return new PaymentMethodResource($paymentMethod);
    }

    public function depositOptions(Request $request, string $id)
    {
        $validated = $request->validate([
            'fixed_options' => 'required|boolean',
            'options' => 'required|array',
        ]);

        $paymentMethodClass = config('oceanpay.models.payment_method');
        $paymentMethod = $paymentMethodClass::findOrFail($id);

        $paymentMethod->update([
            'deposit_options' => $validated,
        ]);

        return new PaymentMethodResource($paymentMethod);
    }

    protected function preparePayload(array $data): array
    {
        if (! isset($data['payment_provider_id'])) {
            return $data;
        }

        $paymentProviderClass = config('oceanpay.models.payment_provider');
        $provider = $paymentProviderClass::find($data['payment_provider_id']);

        if (! $provider) {
            return $data;
        }

        $type = data_get($data, 'type.value', data_get($data, 'type'));
        $baseConfig = [];
        $baseSecretConfig = [];

        if ($type === 'deposit') {
            $baseConfig = $provider->deposit_config ?? [];
            $baseSecretConfig = $provider->deposit_secret_config ?? [];
        } elseif ($type === 'withdrawal') {
            $baseConfig = $provider->withdrawal_config ?? [];
            $baseSecretConfig = $provider->withdrawal_secret_config ?? [];
        }

        $config = array_merge($baseConfig, Arr::except($data['config'] ?? [], ['secret_config']));
        $secretConfig = $data['secret_config'] ?? $baseSecretConfig;

        if ($channelCode = data_get($data, 'extra.channel_code')) {
            $config['channel_code'] = $channelCode;
        }

        $data['config'] = $config;

        if ($secretConfig) {
            $data['secret_config'] = encrypt($secretConfig);
        }

        return $data;
    }
}
