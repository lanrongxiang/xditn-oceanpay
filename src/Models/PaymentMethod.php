<?php

namespace Xditn\Oceanpay\Models;

use Xditn\Oceanpay\Enums\PaymentMethodStatus;
use Xditn\Oceanpay\Enums\PaymentMethodType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethod extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'name',
        'driver_name', // @deprecated
        'payment_provider_id',
        'currency_code',
        'config',
        'secret_config',
        'extra',
        'sort',
        'status',

        'deposit_options',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'secret_config',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PaymentMethodType::class,
            'config' => 'array',
            'extra' => 'array',
            'status' => PaymentMethodStatus::class,

            'deposit_options' => 'array',
        ];
    }

    protected function secretConfig(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->decryptConfigValue($value),
        );
    }

    protected function decryptConfigValue($value): ?array
    {
        if (! $value) {
            return null;
        }

        try {
            return decrypt($value);
        } catch (\Throwable) {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : null;
        }
    }

    public function paymentProvider(): BelongsTo
    {
        return $this->belongsTo(PaymentProvider::class);
    }
}
