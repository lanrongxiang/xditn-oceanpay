<?php

namespace Xditn\Oceanpay\Enums;

enum PaymentProviderChannelStatus: string
{
    case active = 'active';

    case inactive = 'inactive';
}
