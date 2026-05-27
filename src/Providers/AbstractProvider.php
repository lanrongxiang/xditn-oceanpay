<?php

namespace Xditn\Oceanpay\Providers;

use Xditn\Oceanpay\Contracts\Provider as ProviderContract;

abstract class AbstractProvider implements ProviderContract
{
    /**
     * The configuration options for the provider.
     */
    protected ?array $config;

    /**
     * Create a new provider instance.
     */
    public function __construct(?array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Set the configuration options for the provider.
     *
     * @param array|null $config
     * @return $this
     */
    public function setConfig(?array $config): static
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get the configuration options of the provider.
     *
     * @param mixed $key
     * @param mixed|null $default
     * @return mixed
     */
    protected function getConfig(mixed $key = null, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return $this->config;
        }

        return data_get($this->config, $key, $default);
    }
}
