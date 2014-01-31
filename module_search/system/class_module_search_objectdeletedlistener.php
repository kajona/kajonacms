<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Handles record-delete events. If catched, the index-entries assigned to the systemid
 * will be removed from the index.
 *
 * @package module_search
 * @author sidler@mulchprod.de
 */
class class_module_search_objectdeletedlistener implements interface_recorddeleted_listener {

    /**
     * Called whenever a records was deleted using the common methods.
     * Implement this method to be notified when a record is deleted, e.g. to to additional cleanups afterwards.
     * There's no need to register the listener, this is done automatically.
     * Make sure to return a matching boolean-value, otherwise the transaction may be rolled back.
     *
     * @param string $strSystemid
     * @param string $strSourceClass The class-name of the object deleted
     *
     * @return bool
     */
    public function handleRecordDeletedEvent($strSystemid, $strSourceClass) {
        $objIndex = new class_module_search_indexwriter();
        $objIndex->removeRecordFromIndex($strSystemid);
    }

}
