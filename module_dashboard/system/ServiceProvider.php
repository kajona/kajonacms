<?php

namespace Kajona\Dashboard\System;

use Kajona\Dashboard\Service\DashboardInitializerService;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * ServiceProvider for the dashboard module
 *
 * @package Kajona\System\System
 * @author sidler@mulchprod.de
 * @since 6.2
 */
class ServiceProvider implements ServiceProviderInterface
{
    const STR_DASHBOARD_INITIALIZER = "dashboard_initializer";

    public function register(Container $objContainer)
    {
        $objContainer[self::STR_DASHBOARD_INITIALIZER] = function ($c) {
            return new DashboardInitializerService();
        };

    }
}
