<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * A enum holding all possible values for modes regarding the handling
 * if logically deleted objects.
 *
 * @method static class_orm_deletedhandling_enum INCLUDED()
 * @method static class_orm_deletedhandling_enum EXCLUDED()
 * @method static class_orm_deletedhandling_enum EXCLUSIVE()
 *
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.8
 */
class class_orm_deletedhandling_enum extends class_enum {

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

