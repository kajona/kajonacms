<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Enum to differ Value or Params cache
 *
 * @package module_search
 * @author stefan.meyer1@yahoo.de.de
 * @since 4.7
 *
 * @method static class_reflection_enum VALUES()
 * @method static class_reflection_enum PARAMS()
 */
class class_reflection_enum extends class_enum
{
    const VALUES = 1;
    const PARAMS = 2;

    /**
     * Return the array of possible, so allowed values for the current enum
     *
     * @return string[]
     */
    protected function getArrValues() {
        return array("VALUES", "PARAMS");
    }

}