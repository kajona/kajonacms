<?php

namespace Kajona\Packagemanager\System;

use Pimple\Container;
use Pimple\ServiceProviderInterface;


/**
 * ServiceProvider
 *
 * @author sidler@mulchprod.de
 * @since 4.6
 */
class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $objContainer)
    {
        $objContainer['packagemanager_phargenerator'] = function ($c) {
            return new PackagemanagerPharModuleGenerator();
        };
    }
}
