<?php

namespace Xditn\Oceanpay;

use ArrayAccess;

abstract class AbstractDeposit implements ArrayAccess
{
    /**
     * The unique reference for the deposit.
     */
    public mixed $reference;

    /**
     * The provider's unique reference.
     */
    public mixed $providerReference;

    /**
     * The deposit's amount.
     */
    public mixed $amount;

    /**
     * The deposit's URL.
     */
    public ?string $url = null;

    /**
     * The deposit's QR-code.
     */
    public ?string $qrCode;

    /**
     * The deposit's status.
     */
    public mixed $status;

    /**
     * The successful code for the deposit.
     */
    public bool $successful;

    /**
     * The failed code for the deposit.
     */
    public bool $failed;

    /**
     * The deposit's message.
     */
    public mixed $message;

    /**
     * The deposit's raw attributes.
     */
    public array $deposit;

    /**
     * The deposit's other attributes.
     */
    public array $attributes = [];

    /**
     * Get the unique reference for the deposit.
     */
    public function getReference(): mixed
    {
        return $this->reference;
    }

    /**
     * Get the provider's unique reference for the deposit.
     */
    public function getProviderReference(): mixed
    {
        return $this->providerReference;
    }

    /**
     * Get the amount for the deposit.
     */
    public function getAmount(): string|int|float
    {
        return $this->amount;
    }

    /**
     * Get the URL for the deposit.
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Get the QR-code for the deposit.
     */
    public function getQrCode(): ?string
    {
        return $this->qrCode;
    }

    /**
     * Get the status for the deposit.
     */
    public function getStatus(): mixed
    {
        return $this->status;
    }

    /**
     * Get the message for the deposit.
     */
    public function getMessage(): mixed
    {
        return $this->message;
    }

    /**
     * Determine if the deposit was successful.
     */
    public function successful(): bool
    {
        return $this->successful;
    }

    /**
     * Determine if the deposit was failed.
     */
    public function failed(): bool
    {
        return $this->failed;
    }

    /**
     * Get the raw deposit array.
     */
    public function getRaw(): array
    {
        return $this->deposit;
    }

    /**
     * Set the raw deposit array from the provider.
     *
     * @return $this
     */
    public function setRaw(array $deposit): static
    {
        $this->deposit = $deposit;

        return $this;
    }

    /**
     * Map the given array onto the deposit's properties.
     *
     * @return $this
     */
    public function map(array $attributes): static
    {
        $this->attributes = $attributes;

        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        return $this;
    }

    /**
     * Determine if the given raw deposit attribute exists.
     *
     * @param  string  $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->deposit);
    }

    /**
     * Get the given key from the raw deposit.
     *
     * @param  string  $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset): mixed
    {
        return $this->deposit[$offset];
    }

    /**
     * Set the given attribute on the raw deposit array.
     *
     * @param  string  $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, mixed $value): void
    {
        $this->deposit[$offset] = $value;
    }

    /**
     * Unset the given value from the raw deposit array.
     *
     * @param  string  $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        unset($this->deposit[$offset]);
    }

    /**
     * Get a deposit attribute value dynamically.
     *
     * @return void
     */
    public function __get(string $key)
    {
        return $this->attributes[$key] ?? null;
    }
}
