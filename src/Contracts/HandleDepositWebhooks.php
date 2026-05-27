<?php

namespace Xditn\Oceanpay\Contracts;

use Illuminate\Http\Request;

interface HandleDepositWebhooks
{
    public function handle(Request $request);
}
