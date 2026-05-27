<?php

namespace Xditn\Oceanpay\Facades;

use Illuminate\Support\Facades\Facade;
use Xditn\Oceanpay\Contracts\Factory;

/**
 * @method static \Xditn\Oceanpay\Contracts\Provider driver(string $driver = null)
 * @method static \Xditn\Oceanpay\Providers\AbstractProvider buildProvider($provider, $config)
 * @method static mixed extend(string $driver, \Closure $callback)
 *
 * @see \Xditn\Oceanpay\PaymentGatewayManager
 */
class PaymentGateway extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }
}
