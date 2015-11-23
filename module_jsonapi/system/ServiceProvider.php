<?php

namespace Kajona\JsonApi\System;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        define("_jsonapi_module_id_", 1420146491);
    }
}
