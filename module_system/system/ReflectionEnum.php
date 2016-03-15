<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * Enum to differ Value or Params cache
 *
 * @package module_search
 * @author stefan.meyer1@yahoo.de.de
 * @since 4.7
 *
 * @method static ReflectionEnum VALUES()
 * @method static ReflectionEnum PARAMS()
 */
class ReflectionEnum extends EnumBase
{
    const VALUES = 1;
    const PARAMS = 2;

    /**
     * Return the array of possible, so allowed values for the current enum
     *
     * @return string[]
     */
    protected function getArrValues()
    {
        return array("VALUES", "PARAMS");
    }

}