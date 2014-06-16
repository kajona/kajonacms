<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                         *
********************************************************************************************************/

/**
 * Helper class for object validators.
 * Contains general validations methods and other.
 *
 * @package module_system
 * @author stefan.meyer1@yahoo.de
 * @since 4.5
 */
class class_objectvalidator_helper {


    /**
     * Compares two dates of type class_formentry_date
     *
     * @param class_formentry_date $objDateLeft
     * @param class_formentry_date $objDateRight
     *
     * @return int
     *         0, if the dates are equal
     *         1, if $objDateLeft is greater $objDateRight
     *         -1, if $objDateLeft is less than $objDateRight
     */
    public static function compareFormDates(class_formentry_date $objDateLeft, class_formentry_date $objDateRight) {
        if($objDateLeft != null && $objDateRight != null) {

            $strStartDateValue = $objDateLeft->getStrValue();
            $strEndDateValue = $objDateRight->getStrValue();

            if($strStartDateValue != null && $strEndDateValue != null) {
                $objDate1 = new class_date($strStartDateValue);
                $objDate12 = new class_date($strEndDateValue);

                return self::compareDates($objDate1, $objDate12);
            }
        }

        return null;
    }


    /**
     * @param class_date $objDateLeft
     * @param class_date $objDateRight
     *
     * @return int
     */
    public static function compareDates(class_date $objDateLeft = null, class_date $objDateRight = null) {
        if($objDateLeft != null && $objDateRight != null) {
            if($objDateLeft->getLongTimestamp() < $objDateRight->getLongTimestamp()) {
                return -1;//less;
            }
            if($objDateLeft->getLongTimestamp() > $objDateRight->getLongTimestamp()) {
                return 1;//greater
            }
            else {
                return 0;//equals
            }
        }

        return null;
    }
}

