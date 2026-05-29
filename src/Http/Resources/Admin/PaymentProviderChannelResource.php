<?php

namespace Xditn\Oceanpay\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentProviderChannelResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
