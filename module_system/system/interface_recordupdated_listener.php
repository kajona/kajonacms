<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/


/**
 * Interface for objects listening on record-updated events.
 * The event is triggered after the source-object was updated to the database.
 *
 * @author sidler@mulchprod.de
 * @package module_system
 * @since 4.4
 *
 * @deprecated migrate to the generic approach
 * @see interface_genericevent_listener
 */
interface interface_recordupdated_listener {

    /**
     * The event is triggered after the source-object was updated to the database.
     *
     * @abstract
     *
     * @param class_model $objRecord
     *
     * @return bool
     */
    public function handleRecordUpdatedEvent($objRecord);

}
