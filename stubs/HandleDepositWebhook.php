<?php

namespace App\Actions\PaymentGateway;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Xditn\Oceanpay\Contracts\HandleDepositWebhooks;

class HandleDepositWebhook implements HandleDepositWebhooks
{
    public function handle(Request $request): bool
    {
        Log::info('Oceanpay deposit webhook received.', [
            'driver' => $request->route('driver'),
            'key' => $request->route('key'),
            'payload' => $request->all(),
        ]);

        return true;
    }
}
