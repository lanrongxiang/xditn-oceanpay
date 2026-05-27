<?php

namespace Xditn\Oceanpay\Actions;

use Illuminate\Http\Request;
use Xditn\Oceanpay\Contracts\ConfigurationResolvers;

class DatabaseConfigurationResolver implements ConfigurationResolvers
{
    public function resolve(Request $request): ?array
    {
        $id = $request->route('key');

        if (! $id) {
            return null;
        }

        $paymentMethodClass = config('oceanpay.models.payment_method');
        $paymentMethod = $paymentMethodClass::query()->find($id);

        if (! $paymentMethod) {
            return null;
        }

        $config = $paymentMethod->config ?? [];
        $secretConfig = $paymentMethod->secret_config ?? [];

        return array_merge($config, $secretConfig);
    }
}
