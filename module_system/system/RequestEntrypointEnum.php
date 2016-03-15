<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * A enum indicating the entry-point for the current request, e.g. INDEX or XML
 *
 * @method static RequestEntrypointEnum INDEX()
 * @method static RequestEntrypointEnum XML()
 * @method static RequestEntrypointEnum DOWNLOAD()
 * @method static RequestEntrypointEnum IMAGE()
 * @method static RequestEntrypointEnum INSTALLER()
 * @method static RequestEntrypointEnum DEBUG()
 *
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.6
 */
class RequestEntrypointEnum extends EnumBase {
    /**
     * Return the array of possible, so allowed values for the current enum
     *
     * @return string[]
     */
    protected function getArrValues() {
        return array("INDEX", "XML", "DOWNLOAD", "IMAGE", "INSTALLER", "DEBUG");
    }

}

