<?php

namespace Xditn\Oceanpay\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;
use Xditn\Oceanpay\Contracts\HandleDepositWebhooks;
use Xditn\Oceanpay\Contracts\HandleWithdrawalWebhooks;
use Xditn\Oceanpay\Facades\PaymentGateway;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $type = $request->route('type');

        $handler = match ($type) {
            'deposit' => app(HandleDepositWebhooks::class),
            'withdrawal' => app(HandleWithdrawalWebhooks::class),
            default => null,
        };

        if (! $handler) {
            return new JsonResponse([
                'message' => 'Invalid type.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $processed = $handler->handle($request);

        $driver = $request->route('driver');
        $gateway = PaymentGateway::driver($driver);

        return $processed
            ? $gateway->successfulResponse()
            : $gateway->failedResponse();
    }
}
