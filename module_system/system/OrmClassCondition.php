<?php
/*"******************************************************************************************************
*   (c) 2007-2017 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

declare(strict_types = 1);

namespace Kajona\System\System;

/**
 * A condition to add a restriction based on the class of queried objects.
 * This creates a system_class = ? restriction, extends and other OOP stuff is ignored.
 *
 * @package Kajona\System\System
 * @author sidler@mulchprod.de
 * @since 6.2
 */
class OrmClassCondition extends OrmCondition
{

    /**
     * @param string $strClass
     * @param string $strTablePrefix
     */
    public function __construct(string $strClass, string $strTablePrefix = "system.")
    {
        parent::__construct($strTablePrefix."system_class = ?", array($strClass));
    }
}
