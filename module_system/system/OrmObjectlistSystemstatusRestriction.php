<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * A special orm restriction to be mapped against the system status.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.7
 */
class OrmObjectlistSystemstatusRestriction extends OrmObjectlistPropertyRestriction
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
