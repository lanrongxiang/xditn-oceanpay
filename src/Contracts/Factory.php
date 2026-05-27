<?php

namespace Xditn\Oceanpay\Contracts;

interface Factory
{
    /**
     * Get a driver implementation.
     *
     * @param  string|null  $driver
     * @return Provider
     */
    public function driver($driver = null);
}
