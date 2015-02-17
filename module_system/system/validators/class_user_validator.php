<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

/**
 * Checks if the given object is a valid user or user group.
 *
 * @author stefan.meyer1@yahoo.de
 * @since 4.5
 * @package module_system
 */
class class_user_validator extends class_systemid_validator {

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

        //check if the user or usergroup exists
        $objUser = new class_module_user_user($objValue);
        $objUserGroup = new class_module_user_group($objValue);
        if($objUser->getStrUsername() == "" && $objUserGroup->getStrName() == "") {
            return false;
        }

        if($objUser->getIntDeleted() == 1)
            return false;

        return true;
    }

}
