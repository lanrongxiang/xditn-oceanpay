<?php

use Illuminate\Support\Facades\Route;
use Xditn\Oceanpay\Http\Controllers\WebhookController;
use Xditn\Oceanpay\Http\Middleware\ValidateWebhookCall;

Route::group([
    'middleware' => config('oceanpay.webhook_middleware', ['api']),
], function () {
    Route::match(['post', 'get'], '/webhook/payment/{driver}/{key}/{type}', [WebhookController::class, 'handle'])
        ->name('payment-gateway-webhook.handle')
        ->middleware(ValidateWebhookCall::class);
});
