<?php

namespace Xditn\Oceanpay\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Xditn\Oceanpay\Http\Requests\Admin\StorePaymentProviderRequest;
use Xditn\Oceanpay\Http\Requests\Admin\UpdatePaymentProviderRequest;
use Xditn\Oceanpay\Http\Resources\Admin\PaymentProviderResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PaymentProviderController extends Controller
{
    public function index(Request $request)
    {
        $paymentProviderClass = config('oceanpay.models.payment_provider');

        $resource = $paymentProviderClass::query()
            ->when($request->input('name'), function (Builder $query, $value) {
                $query->where('name', 'like', '%'.$value.'%');
            })
            ->when($request->input('code'), function (Builder $query, $value) {
                $query->where('code', 'like', '%'.$value.'%');
            })
            ->when($request->input('currency_code'), function (Builder $query, $value) {
                $query->whereHas('currencies', function (Builder $query) use ($value) {
                    $query->where('currency_code', $value);
                });
            })
            ->orderBy('id', 'desc')
            ->get();

        return PaymentProviderResource::collection($resource);
    }

    public function store(StorePaymentProviderRequest $request)
    {
        $paymentProviderClass = config('oceanpay.models.payment_provider');
        $resource = $paymentProviderClass::create($this->encryptSecretConfig($request->validated()));

        return new PaymentProviderResource($resource);
    }

    public function show(string $id)
    {
        $paymentProviderClass = config('oceanpay.models.payment_provider');
        $resource = $paymentProviderClass::findOrFail($id);

        return PaymentProviderResource::make($resource);
    }

    public function update(UpdatePaymentProviderRequest $request, string $id)
    {
        $paymentProviderClass = config('oceanpay.models.payment_provider');
        $resource = $paymentProviderClass::findOrFail($id);

        $resource->update($this->encryptSecretConfig($request->validated()));

        return PaymentProviderResource::make($resource);
    }

    public function destroy(string $id)
    {
        $paymentProviderClass = config('oceanpay.models.payment_provider');
        $resource = $paymentProviderClass::findOrFail($id);

        $resource->delete();

        return response()->noContent();
    }

    public function options(Request $request)
    {
        $paymentProviderClass = config('oceanpay.models.payment_provider');

        $providers = $paymentProviderClass::query()
            ->select(['id', 'name'])
            ->when($request->input('name'), function (Builder $query, $value) {
                $query->where('name', 'like', '%'.$value.'%');
            })
            ->orderBy('id', 'desc')
            ->get();

        return PaymentProviderResource::collection($providers);
    }

    protected function encryptSecretConfig(array $data): array
    {
        foreach (['deposit_secret_config', 'withdrawal_secret_config'] as $key) {
            if (array_key_exists($key, $data) && is_array($data[$key])) {
                $data[$key] = encrypt($data[$key]);
            }
        }

        return $data;
    }
}
