<?php

namespace Xditn\Oceanpay\Models;

use Illuminate\Database\Eloquent\Model;
use Xditn\Oceanpay\Enums\PaymentProviderChannelStatus;

class PaymentProviderChannel extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'name',
        'payment_provider_id',
        'currency_code',
        'code',
        'extra',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'extra' => 'array',
            'status' => PaymentProviderChannelStatus::class,
        ];
    }
}
