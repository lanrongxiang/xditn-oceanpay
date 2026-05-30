<?php

use Illuminate\Support\Facades\Route;
use Xditn\Oceanpay\Http\Controllers\Admin\PaymentMethodController;
use Xditn\Oceanpay\Http\Controllers\Admin\PaymentProviderController;

Route::group([
    'middleware' => config('oceanpay.admin_middleware', ['api']),
    'as' => 'oceanpay.admin.',
    'prefix' => 'admin',
], function () {
    Route::get('payment-providers/options', [PaymentProviderController::class, 'options'])
        ->name('payment-providers.options');
    Route::apiResource('payment-providers', PaymentProviderController::class);

    Route::put('payment-methods/{payment_method}/status', [PaymentMethodController::class, 'status'])
        ->name('payment-methods.status');
    Route::put('payment-methods/{payment_method}/config', [PaymentMethodController::class, 'config'])
        ->name('payment-methods.config');
    Route::put('payment-methods/{payment_method}/deposit-options', [PaymentMethodController::class, 'depositOptions'])
        ->name('payment-methods.deposit-options');
    Route::apiResource('payment-methods', PaymentMethodController::class);
});
