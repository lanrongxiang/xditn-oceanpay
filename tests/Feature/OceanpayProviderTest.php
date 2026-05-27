<?php

use Illuminate\Support\Facades\Http;
use Xditn\Oceanpay\Facades\PaymentGateway;

it('creates an oceanpay deposit with mocked http response', function () {
    Http::fake([
        'pay.dayangpay.com/api/v1/trades' => Http::response([
            'out_trade_no' => 'D001',
            'trade_no' => 'OP001',
            'payment_url' => 'https://pay.example.test/D001',
            'qr_code_text' => 'qr-content',
        ], 200),
    ]);

    $gateway = PaymentGateway::driver('oceanpay');
    $gateway->setConfig([
        'access_key' => 'test-access-key',
        'secret_key' => 'test-secret-key',
        'channel_code' => '201',
    ]);

    $order = $gateway->initiateDeposit([
        'amount' => 100,
        'trade_no' => 'D001',
        'notify_url' => 'https://example.test/webhook',
    ]);

    expect($order->getReference())->toBe('D001')
        ->and($order->getProviderReference())->toBe('OP001')
        ->and($order->getUrl())->toBe('https://pay.example.test/D001')
        ->and($order->getQrCode())->toBe('qr-content');

    Http::assertSent(fn ($request) => $request->url() === 'https://pay.dayangpay.com/api/v1/trades'
        && $request['client_key'] === 'test-access-key'
        && $request['channel_id'] === '201'
        && $request['out_trade_no'] === 'D001'
        && filled($request['signature']));
});
