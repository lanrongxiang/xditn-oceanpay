<?php

namespace Xditn\Oceanpay\Enums;

enum PaymentMethodType: string
{
    case deposit = 'deposit';

    case withdrawal = 'withdrawal';
}
