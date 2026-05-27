<?php

namespace Xditn\Oceanpay;

use ArrayAccess;

abstract class AbstractWithdrawal implements ArrayAccess
{
    /**
     * The unique reference for the withdrawal.
     */
    public mixed $reference;

    /**
     * The provider's unique reference for the withdrawal.
     */
    public mixed $providerReference;

    /**
     * The withdrawal's amount.
     */
    public mixed $amount;

    /**
     * The withdrawal's status.
     */
    public mixed $status;

    /**
     * The successful code for the withdrawal.
     */
    public bool $successful;

    /**
     * The failed code for the withdrawal.
     */
    public bool $failed;

    /**
     * The withdrawal's message.
     */
    public mixed $message;

    /**
     * The withdrawal's raw attributes.
     */
    public array $withdrawal;

    /**
     * The withdrawal's other attributes.
     */
    public array $attributes = [];

    /**
     * Get the unique reference for the withdrawal.
     */
    public function getReference(): mixed
    {
        return $this->reference;
    }

    /**
     * Get the provider's unique reference for the withdrawal.
     */
    public function getProviderReference(): mixed
    {
        return $this->providerReference;
    }

    /**
     * Get the amount for the withdrawal.
     */
    public function getAmount(): string|int|float
    {
        return $this->amount;
    }

    /**
     * Get the status for the withdrawal.
     */
    public function getStatus(): mixed
    {
        return $this->status;
    }

    /**
     * Get the message for the withdrawal.
     */
    public function getMessage(): mixed
    {
        return $this->message;
    }

    /**
     * Determine if the withdrawal was successful.
     */
    public function successful(): bool
    {
        return $this->successful;
    }

    /**
     * Determine if the withdrawal was failed.
     */
    public function failed(): bool
    {
        return $this->failed;
    }

    /**
     * Get the raw withdrawal array.
     */
    public function getRaw(): array
    {
        return $this->withdrawal;
    }

    /**
     * Set the raw withdrawal array from the provider.
     *
     * @return $this
     */
    public function setRaw(array $withdrawal): static
    {
        $this->withdrawal = $withdrawal;

        return $this;
    }

    /**
     * Map the given array onto the withdrawal's properties.
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
     * Determine if the given raw withdrawal attribute exists.
     *
     * @param  string  $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->withdrawal);
    }

    /**
     * Get the given key from the raw withdrawal.
     *
     * @param  string  $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset): mixed
    {
        return $this->withdrawal[$offset];
    }

    /**
     * Set the given attribute on the raw withdrawal array.
     *
     * @param  string  $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, mixed $value): void
    {
        $this->withdrawal[$offset] = $value;
    }

    /**
     * Unset the given value from the raw withdrawal array.
     *
     * @param  string  $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        unset($this->withdrawal[$offset]);
    }

    /**
     * Get a withdrawal attribute value dynamically.
     *
     * @return void
     */
    public function __get(string $key)
    {
        return $this->attributes[$key] ?? null;
    }
}
