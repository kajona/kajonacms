<?php

namespace Kajona\PackageServer\System;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        define("_packageserver_module_id_",	140);
    }
}
