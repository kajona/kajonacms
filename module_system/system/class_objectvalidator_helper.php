<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                         *
********************************************************************************************************/

/**
 * The scriptlet helper is the central place to trigger scriptlets or read meta-infos about the scriptlets currently installed.
 *
 * @package module_system
 * @author stefan.meyer1@yahoo.de
 * @since 4.5
 */
class class_objectvalidator_helper {

    public static function compareDates(class_formentry_date $objDateLeft, class_formentry_date $objDateRight) {
        if($objDateLeft != null && $objDateRight != null) {

            $strStartDateValue = $objDateLeft->getStrValue();
            $strEndDateValue = $objDateRight->getStrValue();

            if($strStartDateValue != null && $strEndDateValue != null) {
                $objDate1 = new class_date($strStartDateValue);
                $objDate12 = new class_date($strEndDateValue);

                if($objDate1->getLongTimestamp() < $objDate12->getLongTimestamp()) {
                     return -1;//less;
                }
                if($objDate1->getLongTimestamp() > $objDate12->getLongTimestamp()) {
                    return 1;//greater
                }
                else {
                    return 0;//equals
                }
            }
        }
    }
}

