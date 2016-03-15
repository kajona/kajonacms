<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Portal\Templatemapper;

use Kajona\System\Portal\TemplatemapperInterface;
use Kajona\System\System\UserUser;


/**
 * A templatemapper to render a property linking to a users' systemid
 *
 * @package module_system
 * @author sidler@mulchpropd.de
 * @since 4.6
 */
class UserTemplatemapper implements TemplatemapperInterface {

    /**
     * Converts the passed value to a formatted value.
     * In most scenarios, the value is written directly to the template.
     *
     * @param mixed $strValue
     *
     * @return string
     */
    public function format($strValue) {

        if(validateSystemid($strValue)) {
            $objUser = new UserUser($strValue);
            return $objUser->getStrDisplayName();
        }

        return $strValue;
    }

} 