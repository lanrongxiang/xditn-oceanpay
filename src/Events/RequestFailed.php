<?php

namespace Xditn\Oceanpay\Events;

use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Xditn\Oceanpay\Contracts\Provider;

class RequestFailed
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public Provider $provider,
        public Request $request,
        public Response $response
    ) {}
}
