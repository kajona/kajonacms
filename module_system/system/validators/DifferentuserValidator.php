<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\System\System\Validators;

use Kajona\System\System\Carrier;
use Kajona\System\System\UserUser;


/**
 * Checks if the selected user is the user which is currently logged in.
 *
 * @author stefan.meyer1@yahoo.de
 * @since 4.5
 * @package module_system
 */
class DifferentuserValidator extends UserValidator {

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
        $objUser = new UserUser($objValue);
        if($objUser->getStrUsername() != "" && $objValue == Carrier::getInstance()->getObjSession()->getUserID()) {
            return false;
        }

        return true;
    }

}
