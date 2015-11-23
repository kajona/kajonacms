<?php

namespace Kajona\Dashboard\System;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        define("_dashboard_module_id_", 90);
    }
}
