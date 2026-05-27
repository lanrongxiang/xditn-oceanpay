<?php

namespace Xditn\Oceanpay\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Xditn\Oceanpay\Contracts\ConfigurationResolvers;
use Xditn\Oceanpay\Facades\PaymentGateway;

class ValidateWebhookCall
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $config = $this->getConfig($request);

        if (data_get($config, 'whitelist_enabled')) {
            $whitelist = data_get($config, 'whitelist_ip_addresses');

            $this->validateIpAddress($request, $whitelist);
        }

        $driver = $this->getDriver($request);
        $gateway = PaymentGateway::driver($driver);
        $gateway->setConfig($config);

        if (! $gateway->isValidAuthorization($request)) {
            return new JsonResponse([
                'message' => 'Invalid signature or token.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }

    protected function getDriver(Request $request)
    {
        $driver = $request->route('driver');

        if (! $driver) {
            throw new BadRequestHttpException('The given data was invalid.');
        }

        return $driver;
    }

    protected function getConfig(Request $request)
    {
        $config = app(ConfigurationResolvers::class)->resolve($request);

        if (! $config) {
            throw new BadRequestHttpException('The given data was invalid.');
        }

        return $config;
    }

    protected function validateIpAddress(Request $request, $whitelist): void
    {
        $collection = collect($whitelist);

        $ip = $request->hasHeader('cf-connecting-ip')
            ? $request->header('cf-connecting-ip')
            : $request->ip();

        if (! $collection->contains($ip)) {
            throw new AccessDeniedHttpException('Your IP address is not on the whitelist.');
        }
    }
}
