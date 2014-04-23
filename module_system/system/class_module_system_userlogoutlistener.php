<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

/**
 * Unlocks all records currently locked by the user.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 *
 */
class class_module_system_userlogoutlistener implements interface_genericevent_listener {


    /**
     * Handles the userlogout event and unlocks all records currently locked by the current user.
     *
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments) {
        //unwrap arguments
        list($strUserid) = $arrArguments;

        foreach(class_lockmanager::getLockedRecordsForUser($strUserid) as $objOneLock) {
            $objOneLock->getLockManager()->unlockRecord();
        }


        return true;
    }


    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     * @return void
     */
    public static function staticConstruct() {
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_USERLOGOUT, new class_module_system_userlogoutlistener());
    }
}

class_module_system_userlogoutlistener::staticConstruct();