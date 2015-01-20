<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * A special orm restriction to be mapped against the system status.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.7
 */
class class_orm_objectlist_systemstatus_restriction extends class_orm_objectlist_property_restriction {

    /**
     * @param class_orm_comparator_enum $objComparator
     * @param string $intStatus
     */
    function __construct(class_orm_comparator_enum $objComparator, $intStatus) {
        parent::__construct("intRecordStatus", $objComparator, $intStatus);
    }

}
