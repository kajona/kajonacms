<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

/**
 * Checks if the selected user is the user which is currently logged in.
 *
 * @author stefan.meyer1@yahoo.de
 * @since 4.5
 * @package module_system
 */
class class_differentuser_validator extends class_user_validator {

    /**
     * Validates the passed chunk of data.
     * In most cases, this'll be a string-object.
     *
     * @param string $objValue
     * @return bool
     */
    public function validate($objValue) {
        if(!parent::validate($objValue)) {
            return false;
        }

        //check if user exists and if it is the logged in user
        $objUser = new class_module_user_user($objValue);
        if($objUser->getStrUsername() != "" && $objValue == class_carrier::getInstance()->getObjSession()->getUserID()) {
            return false;
        }

        return true;
    }

}
