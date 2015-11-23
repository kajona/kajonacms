<?php

namespace Kajona\Guestbook\System;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        define("_guestbook_module_id_",	35);
    }
}
