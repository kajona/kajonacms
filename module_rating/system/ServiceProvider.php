<?php

namespace Kajona\Rating\System;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        define("_rating_modul_id_", 85);
    }
}
