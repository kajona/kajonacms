<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System\Validators;

use Kajona\System\System\Objectfactory;
use Kajona\System\System\UserGroup;
use Kajona\System\System\UserUser;


/**
 * Checks if the given object is a valid user or user group.
 *
 * @author stefan.meyer1@yahoo.de
 * @since 4.5
 */
class UserValidator extends SystemidValidator
{

    /**
     * Validates the passed chunk of data.
     * In most cases, this'll be a string-object.
     *
     * @param string $objValue
     *
     * @return bool
     */
    public function validate($objValue)
    {
        if (!parent::validate($objValue)) {
            return false;
        }

        //check if the user or usergroup exists
        $objObject = Objectfactory::getInstance()->getObject($objValue);
        if (!$objObject instanceof UserUser && !$objObject instanceof UserGroup) {
            return false;
        }

        if ($objObject->getIntRecordDeleted() == 1) {
            return false;
        }

        return true;
    }

}
