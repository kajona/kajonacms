<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

/**
 * Releases locked records after the content-send event
 *
 * @package module_system
 * @author sidler@mulchprod.de
 *
 * @since 4.6
 *
 */
class class_module_system_lockmanager_aftercontentsendlistener implements interface_genericevent_listener {


    /**
     * Searches for languagesets containing the current systemid. either as a language or a referenced record.
     * Called whenever a records was deleted using the common methods.
     * Implement this method to be notified when a record is deleted, e.g. to to additional cleanups afterwards.
     * There's no need to register the listener, this is done automatically.
     * Make sure to return a matching boolean-value, otherwise the transaction may be rolled back.
     *
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments) {

        /** @var class_request_entrypoint_enum $objEntry */
        $objEntry = $arrArguments[0];

        if($objEntry->equals(class_request_entrypoint_enum::INDEX())) {
            $objLockmanager = new class_lockmanager();
            $objLockmanager->unlockOldRecords();
        }
    }


}
class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, new class_module_system_lockmanager_aftercontentsendlistener());
