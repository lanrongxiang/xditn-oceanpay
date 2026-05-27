<?php

namespace Xditn\Oceanpay;

use ArrayAccess;

abstract class AbstractAccount implements ArrayAccess
{
    /**
     * The unique identifier for the account.
     */
    public mixed $id;

    /**
     * The account's balance.
     */
    public int|float $balance;

    /**
     * The account's raw attributes.
     */
    public array $account;

    /**
     * The account's other attributes.
     */
    public array $attributes = [];

    /**
     * Get the unique identifier for the account.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the balance of the account.
     */
    public function getBalance(): float|int
    {
        return $this->balance;
    }

    /**
     * Get the raw account array.
     */
    public function getRaw(): array
    {
        return $this->account;
    }

    /**
     * Set the raw account array from the provider.
     *
     * @return $this
     */
    public function setRaw(array $account): static
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Map the given array onto the account's properties.
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
     * Determine if the given raw account attribute exists.
     *
     * @param  string  $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->account);
    }

    /**
     * Get the given key from the raw account.
     *
     * @param  string  $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset): mixed
    {
        return $this->account[$offset];
    }

    /**
     * Set the given attribute on the raw account array.
     *
     * @param  string  $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, mixed $value): void
    {
        $this->account[$offset] = $value;
    }

    /**
     * Unset the given value from the raw account array.
     *
     * @param  string  $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        unset($this->account[$offset]);
    }

    /**
     * Get an account attribute value dynamically.
     *
     * @return void
     */
    public function __get(string $key)
    {
        return $this->attributes[$key] ?? null;
    }
}
