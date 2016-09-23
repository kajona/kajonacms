<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Statustransition\System;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * @author christoph.kappestein@artemeon.de
 * @module statustransition
 */
class ServiceProvider implements ServiceProviderInterface
{
    const STR_MANAGER = "statustransition_manager";

    public function register(Container $c)
    {
        $c[self::STR_MANAGER] = function(){
            return new StatustransitionManager();
        };
    }
}
