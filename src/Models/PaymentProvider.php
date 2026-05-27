<?php

namespace Xditn\Oceanpay\Models;

use Xditn\Oceanpay\Enums\PaymentProviderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentProvider extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'status',

        'deposit_config',
        'deposit_secret_config',
        'withdrawal_config',
        'withdrawal_secret_config',
    ];

    protected $hidden = [
        'deposit_secret_config',
        'withdrawal_secret_config',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PaymentProviderStatus::class,

            'deposit_config' => 'array',
            'withdrawal_config' => 'array',
        ];
    }

    protected function depositSecretConfig(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->decryptConfigValue($value),
        );
    }

    protected function withdrawalSecretConfig(): Attribute
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

    public function currencies(): HasMany
    {
        return $this->hasMany(PaymentProviderCurrency::class);
    }
}
