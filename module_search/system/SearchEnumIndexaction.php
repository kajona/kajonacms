<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Search\System;

use Kajona\System\System\EnumBase;


/**
 * Enum to differ the deferred indexer actions
 *
 * @package module_search
 * @author sidler@mulchprod.de
 * @since 4.6
 *
 * @method static SearchEnumIndexaction INDEX()
 * @method static SearchEnumIndexaction DELETE()
 */
class SearchEnumIndexaction extends EnumBase {
    /**
     * Return the array of possible, so allowed values for the current enum
     *
     * @return string[]
     */
    protected function getArrValues() {
        return array("INDEX", "DELETE");
    }

}
