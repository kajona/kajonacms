<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * A enum holding all possible values for modes regarding the handling
 * if logically deleted objects.
 *
 * @method static OrmDeletedhandlingEnum INCLUDED()
 * @method static OrmDeletedhandlingEnum EXCLUDED()
 * @method static OrmDeletedhandlingEnum EXCLUSIVE()
 *
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.8
 */
class OrmDeletedhandlingEnum extends EnumBase {

    const INCLUDED = 1;
    const EXCLUDED = 2;
    const EXCLUSIVE = 3;

    /**
     * Return the array of possible, so allowed values for the current enum
     *
     * @return string[]
     */
    protected function getArrValues() {
        return array("INCLUDED", "EXCLUDED", "EXCLUSIVE");
    }


}

