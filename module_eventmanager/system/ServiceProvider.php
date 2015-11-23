<?php

namespace Kajona\EventManager\System;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        define("_eventmanager_module_id_", 120);
    }
}
