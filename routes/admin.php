<?php

use Illuminate\Support\Facades\Route;
use Xditn\Oceanpay\Http\Controllers\Admin\PaymentMethodController;
use Xditn\Oceanpay\Http\Controllers\Admin\PaymentProviderChannelController;
use Xditn\Oceanpay\Http\Controllers\Admin\PaymentProviderController;
use Xditn\Oceanpay\Http\Controllers\Admin\PaymentProviderCurrencyController;

Route::group([
    'middleware' => config('oceanpay.admin_middleware', ['api']),
    'as' => 'oceanpay.admin.',
    'prefix' => 'admin',
], function () {
    Route::get('payment-providers/options', [PaymentProviderController::class, 'options'])
        ->name('payment-providers.options');
    Route::apiResource('payment-providers', PaymentProviderController::class);

    Route::apiResource('payment-provider-channels', PaymentProviderChannelController::class);
    Route::apiResource('payment-provider-currencies', PaymentProviderCurrencyController::class);

    Route::patch('payment-methods/{payment_method}/status', [PaymentMethodController::class, 'status'])
        ->name('payment-methods.status');
    Route::put('payment-methods/{payment_method}/config', [PaymentMethodController::class, 'config'])
        ->name('payment-methods.config');
    Route::put('payment-methods/{payment_method}/deposit-options', [PaymentMethodController::class, 'depositOptions'])
        ->name('payment-methods.deposit-options');
    Route::apiResource('payment-methods', PaymentMethodController::class);
});
