<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_search_indexwriter.php 6392 2014-01-31 08:56:43Z sidler $                                  *
********************************************************************************************************/

/**
 * Handles record-delete events. If catched, the index-entries assigned to the systemid
 * will be updated.
 *
 * @package module_search
 * @author sidler@mulchprod.de
 */
class class_module_search_recordupdatedlistener implements interface_recordupdated_listener {

    /**
     * The event is triggered after the source-object was updated to the database.
     *
     * @param class_model $objRecord
     *
     * @return bool
     */
    public function handleRecordUpdatedEvent($objRecord) {
        $objIndex = new class_module_search_indexwriter();
        $objIndex->indexObject($objRecord);
    }
}
