<?php

namespace Xditn\Oceanpay\Contracts;

use Illuminate\Http\Request;

interface ConfigurationResolvers
{
    /**
     * Resolve a configuration value.
     */
    public function resolve(Request $request);
}
