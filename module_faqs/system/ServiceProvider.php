<?php

namespace Kajona\Faqs\System;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        define("_faqs_module_id_", 75);
    }
}
