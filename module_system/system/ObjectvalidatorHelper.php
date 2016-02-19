<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                         *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * Helper class for object validators.
 * Contains general validations methods and other.
 *
 * @package module_system
 * @author stefan.meyer1@yahoo.de
 * @since 4.5
 */
class ObjectvalidatorHelper
{


    /**
     * Compares two dates of type \Kajona\System\System\Date.
     *
     * @param Date $objDateLeft
     * @param Date $objDateRight
     *
     * @return int
     *         0, if the dates are equal
     *         1, if $objDateLeft is greater than $objDateRight
     *         -1, if $objDateLeft is less than $objDateRight
     *         null, if $objDateLeft or $objDateRight are null (then no comparison is possible)
     */
    public static function compareDates(Date $objDateLeft = null, Date $objDateRight = null)
    {
        if ($objDateLeft != null && $objDateRight != null) {
            if ($objDateLeft->getLongTimestamp() < $objDateRight->getLongTimestamp()) {
                return -1;//less;
            }
            if ($objDateLeft->getLongTimestamp() > $objDateRight->getLongTimestamp()) {
                return 1;//greater
            }
            else {
                return 0;//equals
            }
        }

        return null;
    }
}

