<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

/**
 * A simple validator to validate a string.
 * By default, the string must contain a single char, the max length is unlimited.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_system
 */
class class_email_validator implements interface_validator {

    /**
     * Validates the passed chunk of data.
     * In most cases, this'll be a string-object.
     *
     * @param string $objValue
     * @return bool
     */
    public function validate($objValue) {

        if(!is_string($objValue))
            return false;


        $intTest = uniEreg("([A-Za-z0-9])([A-Za-z0-9]|_|-|\.)*@([A-Za-z0-9]|_|-|\.)+\.([A-Za-z])([A-Za-z])+", $objValue);
        if($intTest === false || $intTest <= 0)
            return false;
        else
            return true;
    }

}
