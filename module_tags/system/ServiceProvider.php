<?php

namespace Kajona\Tags\System;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * ServiceProvider
 *
 * @package Kajona\System\System
 * @author christoph.kappestein@gmail.com
 * @since 4.6
 */
class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $objContainer)
    {
        define("_tags_modul_id_", 95);
    }
}
