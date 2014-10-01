<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Enum to differ the deferred indexer actions
 *
 * @package module_search
 * @author sidler@mulchprod.de
 * @since 4.6
 *
 * @method static class_search_enum_indexaction INDEX()
 * @method static class_search_enum_indexaction DELETE()
 */
class class_search_enum_indexaction extends class_enum {
    /**
     * Return the array of possible, so allowed values for the current enum
     *
     * @return string[]
     */
    static function getArrValues() {
        return array("INDEX", "DELETE");
    }

}
