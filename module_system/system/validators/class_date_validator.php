<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_versionable.php 4413 2012-01-03 19:38:11Z sidler $                                   *
********************************************************************************************************/

/**
 * A simple validator to validate a string.
 * By default, the string must contain a single char, the max length is unlimited.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_system
 */
class class_date_validator implements interface_validator {

    /**
     * Validates the passed chunk of data.
     * In most cases, this'll be a string-object.
     *
     * @param string $objValue
     * @return bool
     */
    public function validate($objValue) {

        if(is_numeric($objValue))
            $objValue = new class_date($objValue);

        if(is_object($objValue) && $objValue instanceof class_date)
            return $objValue->getLongTimestamp() != 0;

        return false;
    }


    /**
     * Returns a string-based name of the current validator.
     * Used to pass the type of validator to the js-engine rendering the
     * form in the browser.
     *
     * @return string
     */
    public function getStrName() {
        return "date";
    }
}
