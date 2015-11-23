<?php

namespace Kajona\PostAComment\System;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        define("_postacomment_modul_id_", 80);
    }
}
