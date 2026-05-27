<?php

namespace App\Actions\PaymentGateway;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Xditn\Oceanpay\Contracts\HandleWithdrawalWebhooks;

class HandleWithdrawalWebhook implements HandleWithdrawalWebhooks
{
    public function handle(Request $request): bool
    {
        Log::info('Oceanpay withdrawal webhook received.', [
            'driver' => $request->route('driver'),
            'key' => $request->route('key'),
            'payload' => $request->all(),
        ]);

        return true;
    }
}
