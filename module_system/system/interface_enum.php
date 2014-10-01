<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Interface to enforce the getValues method of a single enum
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.6
 */
interface interface_enum {

    /**
     * Return the array of possible, so allowed values for the current enum
     * @return string[]
     */
    static function getArrValues();



}

