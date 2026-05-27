<?php

use Illuminate\Support\Facades\Route;
use Xditn\Oceanpay\Facades\PaymentGateway;
use Xditn\Oceanpay\Models\PaymentMethod;
use Xditn\Oceanpay\Models\PaymentProvider;
use Xditn\Oceanpay\Models\PaymentProviderChannel;
use Xditn\Oceanpay\Models\PaymentProviderCurrency;
use Xditn\Oceanpay\Providers\OceanpayProvider;

it('boots package services and routes', function () {
    expect(PaymentGateway::driver('oceanpay'))->toBeInstanceOf(OceanpayProvider::class)
        ->and(Route::has('payment-gateway-webhook.handle'))->toBeTrue();
});

it('runs package migrations and stores configuration records', function () {
    $provider = PaymentProvider::query()->create([
        'name' => '大洋支付',
        'code' => 'oceanpay',
        'status' => 'active',
        'deposit_config' => [
            'deposit_initiate_url' => 'https://pay.dayangpay.com/api/v1/trades',
            'deposit_fetch_url' => 'https://pay.dayangpay.com/api/v1/trades',
        ],
        'deposit_secret_config' => encrypt([
            'access_key' => 'test-access-key',
            'secret_key' => 'test-secret-key',
        ]),
        'withdrawal_config' => [
            'withdrawal_initiate_url' => 'https://pay.dayangpay.com/api/v1/transfers',
            'withdrawal_fetch_url' => 'https://pay.dayangpay.com/api/v1/transfers',
            'balance_fetch_url' => 'https://pay.dayangpay.com/api/v1/user/balances',
        ],
        'withdrawal_secret_config' => encrypt([
            'access_key' => 'test-access-key',
            'secret_key' => 'test-secret-key',
        ]),
    ]);

    $channel = PaymentProviderChannel::query()->create([
        'type' => 'deposit',
        'name' => 'PIX',
        'payment_provider_id' => $provider->id,
        'currency_code' => 'BRL',
        'code' => '201',
        'extra' => ['description' => 'PIX'],
        'status' => 'active',
    ]);

    PaymentProviderCurrency::query()->create([
        'payment_provider_id' => $provider->id,
        'currency_code' => 'BRL',
    ]);

    $method = PaymentMethod::query()->create([
        'type' => 'deposit',
        'name' => 'PIX 充值',
        'payment_provider_id' => $provider->id,
        'currency_code' => 'BRL',
        'config' => array_merge($provider->deposit_config, ['channel_code' => $channel->code]),
        'secret_config' => encrypt($provider->deposit_secret_config),
        'extra' => ['channel_code' => $channel->code],
        'status' => 'active',
    ]);

    expect($provider->deposit_secret_config)->toBe([
        'access_key' => 'test-access-key',
        'secret_key' => 'test-secret-key',
    ])->and($method->secret_config)->toBe([
        'access_key' => 'test-access-key',
        'secret_key' => 'test-secret-key',
    ]);
});
