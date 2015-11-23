<?php

namespace Kajona\Stats\System;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        define("_stats_modul_id_", 60);
    }
}
