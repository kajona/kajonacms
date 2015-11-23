<?php

namespace Kajona\PackageManager\System;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        define("_packagemanager_module_id_", 2);
    }
}
