<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                                                                                *
********************************************************************************************************/

/**
 * A simple validator to validate a string.
 * By default, the string must contain a single char, the max length is unlimited.
 *
 * @author stefan.meyer1@yahoo.de
 * @since 4.4
 * @package module_system
 */
class class_uploadfile_validator implements interface_validator {

    /**
     * Validates the passed chunk of data.
     * In most cases, this'll be a string-object.
     *
     * @param string $objValue
     * @return bool
     */
    public function validate($objValue) {

        if(!is_array($objValue)) {
            return false;
        }

        if(!is_uploaded_file($objValue["tmp_name"])) {
            return false;
        }

        if($objValue["error"] > 0) {
            return false;
        }

        return true;
    }

}
