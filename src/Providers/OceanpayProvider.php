<?php

namespace Xditn\Oceanpay\Providers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Xditn\Oceanpay\Events\RequestFailed;
use Xditn\Oceanpay\Exceptions\RequestFailedException;
use Xditn\Oceanpay\HttpClientLogger;
use Xditn\Oceanpay\Providers\AbstractProvider;
use Xditn\Oceanpay\Providers\Account;
use Xditn\Oceanpay\Providers\Deposit;
use Xditn\Oceanpay\Providers\Withdrawal;

class OceanpayProvider extends AbstractProvider
{
    /**
     * The HTTP client request instance.
     */
    protected ClientRequest $clientRequest;

    /**
     * @throws ConnectionException|RequestFailedException
     */
    public function initiateDeposit(array $parameters = []): Deposit
    {
        $url = $this->getConfig(
            'deposit_initiate_url',
            'https://pay.dayangpay.com/api/v1/trades'
        );

        $data = [
            'client_key' => $this->getConfig('access_key'),
            'amount' => data_get($parameters, 'amount'),
            'channel_id' => $this->getConfig('channel_code'),
            'out_trade_no' => data_get($parameters, 'trade_no'),
            'notify_url' => data_get($parameters, 'notify_url'),

            'extra' => json_encode(data_get($parameters, 'extra')),
        ];

        $response = $this->post($url, $data);

        $responseData = $response->json('data');
        
        if (empty($responseData)) {
            $responseData = $response->json();
        }

        return (new Deposit)->setRaw($response->json())->map([
            'reference' => data_get($responseData, 'out_trade_no'),
            'providerReference' => data_get($responseData, 'trade_no'),
            'url' => data_get($responseData, 'payment_url'),
            'qrCode' => data_get($responseData, 'qr_code_text'),
        ]);
    }

    /**
     * @throws ConnectionException|RequestFailedException
     */
    public function fetchDeposit(array $parameters = []): Deposit
    {
        $url = $this->getConfig(
            'deposit_fetch_url',
            'https://pay.dayangpay.com/api/v1/trades'
        );
        $url .= sprintf('/%s', data_get($parameters, 'trade_no'));

        $query['client_key'] = $this->getConfig('access_key');

        $response = $this->get($url, $query);

        return (new Deposit)->setRaw($response->json())->map([
            'reference' => $response->json('out_trade_no'),
            'providerReference' => $response->json('trade_no'),
            'amount' => $response->json('amount'),
            'status' => $response->json('status'),
            'successful' => $response->json('status') == 1,
        ]);
    }

    /**
     * @throws ConnectionException|RequestFailedException
     */
    public function initiateWithdrawal(array $parameters = []): Withdrawal
    {
        $url = $this->getConfig(
            'withdrawal_initiate_url',
            'https://pay.dayangpay.com/api/v1/transfers'
        );

        $data = [
            'client_key' => $this->getConfig('access_key'),
            'amount' => data_get($parameters, 'amount'),
            'channel_id' => $this->getConfig('channel_code'),
            'out_transfer_no' => data_get($parameters, 'trade_no'),
            'notify_url' => data_get($parameters, 'notify_url'),

            'payee_account' => data_get($parameters, 'payee_account'),
            'payee_name' => data_get($parameters, 'payee_name'),

            'extra' => json_encode(data_get($parameters, 'extra')),
        ];

        $response = $this->post($url, $data);

        return (new Withdrawal)->setRaw($response->json())->map([
            'reference' => $response->json('out_transfer_no'),
            'providerReference' => $response->json('transfer_no'),
        ]);
    }

    /**
     * @throws ConnectionException|RequestFailedException
     */
    public function fetchWithdrawal(array $parameters = []): Withdrawal
    {
        $url = $this->getConfig(
            'withdrawal_fetch_url',
            'https://pay.dayangpay.com/api/v1/transfers'
        );
        $url .= sprintf('/%s', data_get($parameters, 'trade_no'));

        $query['client_key'] = $this->getConfig('access_key');

        $response = $this->get($url, $query);

        return (new Withdrawal)->setRaw($response->json())->map([
            'reference' => $response->json('out_transfer_no'),
            'providerReference' => $response->json('transfer_no'),
            'amount' => $response->json('amount'),
            'status' => $response->json('status'),
            'message' => $response->json('message'),
            'successful' => $response->json('status') == 1,
            'failed' => $response->json('status') == 3,
        ]);
    }

    /**
     * @throws ConnectionException|RequestFailedException
     */
    public function fetchBalance(): Account
    {
        $url = $this->getConfig(
            'balance_fetch_url',
            'https://pay.dayangpay.com/api/v1/user/balances'
        );

        $query['client_key'] = $this->getConfig('access_key');

        $response = $this->get($url, $query);

        return (new Account)->setRaw($response->json())->map([
            'balance' => $response->json('available_funds'),
        ]);
    }

    /**
     * Create string to sign.
     */
    protected function buildSignaturePayload(array $data, array $except = []): string
    {
        return collect($data)
            ->reject(fn ($value, $key) => in_array($key, $except) || $value == '')
            ->sortKeys()
            ->map(fn ($value, $key) => "{$key}={$value}")
            ->join('&');
    }

    /**
     * Create a signed string.
     */
    private function generateSignature(array $data, array $ignore = []): string
    {
        $stringToSign = $this->buildSignaturePayload($data, $ignore);

        $secretKey = $this->getConfig('secret_key');

        return hash_hmac('sha256', $stringToSign, $secretKey);
    }

    /**
     * Map the raw deposit array to a Payment Gateway's Deposit instance.
     */
    public function mapDepositToObject(array $data): Deposit
    {
        return (new Deposit)->setRaw($data)->map([
            'reference' => data_get($data, 'out_trade_no'),
            'providerReference' => data_get($data, 'trade_no'),
            'amount' => data_get($data, 'amount'),
            'status' => data_get($data, 'status'),
            'successful' => data_get($data, 'status') == 1,
        ]);
    }

    /**
     * Map the raw withdrawal array to a Payment Gateway's Withdrawal instance.
     */
    public function mapWithdrawalToObject(array $data): Withdrawal
    {
        return (new Withdrawal)->setRaw($data)->map([
            'reference' => data_get($data, 'out_transfer_no'),
            'providerReference' => data_get($data, 'transfer_no'),
            'amount' => data_get($data, 'amount'),
            'status' => data_get($data, 'status'),
            'successful' => data_get($data, 'status') == 1,
            'failed' => data_get($data, 'status') == 3,
            'message' => data_get($data, 'message'),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function isValidAuthorization(Request $request)
    {
        $incomingSignature = $request->input('signature');

        if (! $incomingSignature) {
            return false;
        }

        $signature = $this->generateSignature($request->all(), ['signature']);

        return hash_equals($signature, $incomingSignature);
    }

    /**
     * {@inheritDoc}
     */
    public function successfulResponse()
    {
        return Response::make([
            'code' => strtoupper('success'),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function failedResponse()
    {
        return Response::make([
            'code' => strtoupper('fail'),
        ]);
    }

    /**
     * Issue a POST request to the given URL.
     *
     * @throws ConnectionException
     * @throws RequestFailedException
     */
    protected function post(string $url, array $data = []): ClientResponse
    {
        $data['signature'] = $this->generateSignature($data, ['signature']);

        $response = Http::withMiddleware(new HttpClientLogger($this))
            ->acceptJson()
            ->beforeSending(function (ClientRequest $request) {
                $this->clientRequest = $request;
            })
            ->post($url, $data);

        if ($response->failed()) {
            Event::dispatch(new RequestFailed($this, $this->clientRequest, $response));

            throw new RequestFailedException($response->body());
        }

        return $response;
    }

    /**
     * Issue a GET request to the given URL.
     *
     * @throws ConnectionException
     * @throws RequestFailedException
     */
    protected function get(string $url, array|string|null $query = null): ClientResponse
    {
        $query['signature'] = $this->generateSignature($query, ['signature']);

        $response = Http::withMiddleware(new HttpClientLogger($this))
            ->acceptJson()
            ->beforeSending(function (ClientRequest $request) {
                $this->clientRequest = $request;
            })
            ->get($url, $query);

        if ($response->failed()) {
            Event::dispatch(new RequestFailed($this, $this->clientRequest, $response));

            throw new RequestFailedException($response->body());
        }

        return $response;
    }
}
