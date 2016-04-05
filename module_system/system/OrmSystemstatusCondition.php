<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * A special orm condition to be mapped against the system status.
 *
 * @package Kajona\System\System
 * @author stefan.meyer1@yahoo.de
 * @since 5.0
 */
class OrmSystemstatusCondition extends OrmPropertyCondition
{

    /**
     * @param OrmComparatorEnum $objComparator
     * @param string $intStatus
     */
    function __construct(OrmComparatorEnum $objComparator, $intStatus)
    {
        parent::__construct("intRecordStatus", $objComparator, $intStatus);
    }

}
