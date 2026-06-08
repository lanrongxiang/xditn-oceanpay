<?php

namespace Xditn\Oceanpay\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        $this->normalizeWebhookPayload($request);

        if (data_get($config, 'whitelist_enabled')) {
            $whitelist = data_get($config, 'whitelist_ip_addresses');

            $this->validateIpAddress($request, $whitelist);
        }

        $driver = $this->getDriver($request);
        $gateway = PaymentGateway::driver($driver);
        $gateway->setConfig($config);

        $isValidAuthorization = $gateway->isValidAuthorization($request);

        $this->logWebhookValidation($request, $config, $driver, $isValidAuthorization);

        if (! $isValidAuthorization) {
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

    protected function normalizeWebhookPayload(Request $request): void
    {
        if ($request->input('signature')) {
            return;
        }

        $rawBody = trim($request->getContent());
        if ($rawBody === '') {
            return;
        }

        $payload = json_decode($rawBody, true);
        if (! is_array($payload) || json_last_error() !== JSON_ERROR_NONE) {
            return;
        }

        $request->merge($payload);
    }

    protected function logWebhookValidation(Request $request, ?array $config, string $driver, bool $isValid): void
    {
        if (! data_get($config, 'webhook_debug_enabled')) {
            return;
        }

        $rawBody = $request->getContent();
        $includeRawBody = (bool) data_get($config, 'webhook_debug_include_raw_body', false);
        $headers = collect($request->headers->all())
            ->only([
                'content-type',
                'accept',
                'user-agent',
                'x-forwarded-for',
                'x-real-ip',
                'cf-connecting-ip',
            ])
            ->map(fn (array $value) => implode(',', $value))
            ->all();

        Log::channel((string) data_get($config, 'webhook_debug_channel', 'oceanpay'))->info('Oceanpay webhook authorization checked.', array_filter([
            'driver' => $driver,
            'type' => $request->route('type'),
            'key' => $request->route('key'),
            'valid' => $isValid,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'client_ip' => $request->getClientIp(),
            'headers' => $headers,
            'input_keys' => array_keys($request->all()),
            'input' => $request->except(['signature']),
            'incoming_signature' => $request->input('signature'),
            'raw_body_sha256' => hash('sha256', $rawBody),
            'raw_body_length' => strlen($rawBody),
            'raw_body' => $includeRawBody ? $rawBody : null,
        ], fn ($value) => $value !== null));
    }
}
