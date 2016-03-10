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
    const STR_PHARGENERATOR = "packagemanager_phargenerator";

    public function register(Container $objContainer)
    {
        $objContainer[self::STR_PHARGENERATOR] = function ($c) {
            return new PackagemanagerPharModuleGenerator();
        };
    }
}
