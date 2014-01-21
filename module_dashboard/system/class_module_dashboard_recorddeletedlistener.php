<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

/**
 * Listener to handle deleted users.
 * Removes all relevant widgets.
 *
 * @package module_dashboard
 * @author sidler@mulchprod.de
 *
 */
class class_module_dashboard_recorddeletedlistener implements interface_recorddeleted_listener {


    /**
     * Implementing callback to react on user-delete events
     *
     * Called whenever a record was deleted using the common methods.
     * Implement this method to be notified when a record is deleted, e.g. to to additional cleanups afterwards.
     * There's no need to register the listener, this is done automatically.
     *
     * Make sure to return a matching boolean-value, otherwise the transaction may be rolled back.
     *
     * @param string $strSystemid
     * @param string $strSourceClass
     *
     * @return bool
     */
    public function handleRecordDeletedEvent($strSystemid, $strSourceClass) {
        if($strSourceClass == "class_module_user_user" && validateSystemid($strSystemid)) {
            $strQuery = "SELECT dashboard_id FROM "._dbprefix_."dashboard WHERE dashboard_user = ?";
            $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strSystemid), null, null, false);
            foreach($arrRows as $arrOneRow) {
                $objWidget = new class_module_dashboard_widget($arrOneRow["dashboard_id"]);
                $objWidget->deleteObject();
            }
        }

        return true;
    }


}


