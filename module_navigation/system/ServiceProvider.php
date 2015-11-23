<?php

namespace Kajona\Navigation\System;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        define("_navigation_modul_id_", 25);
    }
}
