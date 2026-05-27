<?php

namespace Xditn\Oceanpay\Enums;

enum PaymentProviderStatus: string
{
    case inactive = 'inactive';

    case active = 'active';
}
