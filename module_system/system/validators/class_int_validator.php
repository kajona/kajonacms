<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

/**
 * A simple validator to validate a positive integer.
 * By default, the string must contain a single char, the max length is unlimited.
 *
 * @author sidler@mulchprod.de
 * @since 4.3
 * @package module_system
 */
class class_int_validator implements interface_validator {

    /**
     * Validates the passed chunk of data.
     * In most cases, this'll be a string-object.
     *
     * @param string $objValue
     * @return bool
     */
    public function validate($objValue) {
        return is_numeric($objValue) && ctype_digit(strval($objValue));
    }


    /**
     * Returns a string-based name of the current validator.
     * Used to pass the type of validator to the js-engine rendering the
     * form in the browser.
     *
     * @return string
     */
    public function getStrName() {
        return "int";
    }
}
