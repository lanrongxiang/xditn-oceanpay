<?php

namespace Xditn\Oceanpay;

use Xditn\Oceanpay\Contracts\ConfigurationResolvers;
use Xditn\Oceanpay\Contracts\HandleDepositWebhooks;
use Xditn\Oceanpay\Contracts\HandleWithdrawalWebhooks;

class PaymentGateway
{
    public static bool $registersRoutes = true;

    public static function handleDepositWebhooksUsing(string $callback): void
    {
        app()->singleton(HandleDepositWebhooks::class, $callback);
    }

    public static function handleWithdrawalWebhooksUsing(string $callback): void
    {
        app()->singleton(HandleWithdrawalWebhooks::class, $callback);
    }

    public static function configurationResolversUsing(string $callback): void
    {
        app()->singleton(ConfigurationResolvers::class, $callback);
    }

    public static function ignoreRoutes(): static
    {
        static::$registersRoutes = false;

        return new static;
    }
}
