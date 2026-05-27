<?php

namespace Xditn\Oceanpay;

use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Xditn\Oceanpay\Contracts\Provider;

class HttpClientLogger
{
    public function __construct(public Provider $provider)
    {
        //
    }

    public function __invoke(callable $handler): Closure
    {
        return function (RequestInterface $request, array $options) use ($handler): PromiseInterface {
            return $handler($request, $options)->then(
                function (ResponseInterface $response) use ($request): ResponseInterface {
                    try {
                        Log::build([
                            'driver' => 'daily',
                            'path' => App::storagePath(sprintf('logs/%s.log', class_basename($this->provider))),
                        ])->info(__METHOD__, [
                            'request' => [
                                'url' => (string) $request->getUri(),
                                'method' => $request->getMethod(),
                                'headers' => $request->getHeaders(),
                                'body' => $this->readStream($request->getBody()),
                            ],
                            'response' => [
                                'body' => $this->readStream($response->getBody()),
                                'status' => $response->getStatusCode(),
                            ],
                        ]);
                    } catch (\Throwable $exception) {
                        // Failed to log outgoing HTTP request.
                        Log::error('HttpClientLogger failed: '.$exception->getMessage());
                    }

                    return $response;
                }
            );
        };
    }

    protected function readStream(StreamInterface $stream): string
    {
        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        $content = $stream->getContents();

        if (Str::isJson($content)) {
            return json_encode(json_decode($content, true));
        }

        return $content;
    }
}
