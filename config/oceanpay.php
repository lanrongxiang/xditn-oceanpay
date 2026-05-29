<?php

return [
    'prefix' => 'api/oceanpay',

    'domain' => null,

    'middleware' => ['api'],

    'admin_middleware' => ['api'],

    'webhook_middleware' => ['api'],

    'routes' => [
        'admin' => true,
        'webhook' => true,
    ],

    'models' => [
        'payment_method' => Xditn\Oceanpay\Models\PaymentMethod::class,
        'payment_provider' => Xditn\Oceanpay\Models\PaymentProvider::class,
    ],

    'providers' => [
        'oceanpay' => [
            'access_key' => env('OCEANPAY_ACCESS_KEY'),
            'secret_key' => env('OCEANPAY_SECRET_KEY'),

            'deposit_initiate_url' => env('OCEANPAY_DEPOSIT_INITIATE_URL', 'https://pay.dayangpay.com/api/v1/trades'),
            'deposit_fetch_url' => env('OCEANPAY_DEPOSIT_FETCH_URL', 'https://pay.dayangpay.com/api/v1/trades'),

            'withdrawal_initiate_url' => env('OCEANPAY_WITHDRAWAL_INITIATE_URL', 'https://pay.dayangpay.com/api/v1/transfers'),
            'withdrawal_fetch_url' => env('OCEANPAY_WITHDRAWAL_FETCH_URL', 'https://pay.dayangpay.com/api/v1/transfers'),

            'balance_fetch_url' => env('OCEANPAY_BALANCE_FETCH_URL', 'https://pay.dayangpay.com/api/v1/user/balances'),

            'whitelist_enabled' => env('OCEANPAY_WEBHOOK_WHITELIST_ENABLED', false),
            'whitelist_ip_addresses' => [],
        ],
    ],
];
