<?php

namespace Xditn\Oceanpay\Contracts;

use Illuminate\Http\Request;

interface HandleWithdrawalWebhooks
{
    public function handle(Request $request);
}
