<?php

namespace Kajona\MediaManager\System;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        define("_mediamanager_module_id_", 130);

        // ID der Ordneransicht
        define("_mediamanager_folderview_modul_id_", 13);
    }
}
