<?php

namespace Xditn\Oceanpay;

use Illuminate\Support\Manager;
use InvalidArgumentException;
use Xditn\Oceanpay\Providers\AbstractProvider;
use Xditn\Oceanpay\Providers\OceanpayProvider;

class PaymentGatewayManager extends Manager implements Contracts\Factory
{
    /**
     * Get a driver instance.
     *
     * @param string $driver
     * @return mixed
     */
    public function with(string $driver): mixed
    {
        return $this->driver($driver);
    }

    /**
     * Build a provider instance.
     */
    public function buildProvider(string $provider, ?array $config): AbstractProvider
    {
        return new $provider($config);
    }

    public function createOceanpayDriver(): OceanpayProvider
    {
        return $this->buildProvider(OceanpayProvider::class, config('oceanpay.providers.oceanpay'));
    }

    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        throw new InvalidArgumentException('No Payment driver was specified.');
    }
}
