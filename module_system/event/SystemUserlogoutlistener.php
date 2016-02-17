<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

namespace Kajona\System\Event;

use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\GenericeventListenerInterface;
use Kajona\System\System\Lockmanager;
use Kajona\System\System\SystemEventidentifier;


/**
 * Unlocks all records currently locked by the user.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 *
 */
class SystemUserlogoutlistener implements GenericeventListenerInterface {


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

        foreach(Lockmanager::getLockedRecordsForUser($strUserid) as $objOneLock) {
            $objOneLock->getLockManager()->unlockRecord();
        }


        return true;
    }


    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     * @return void
     */
    public static function staticConstruct() {
        CoreEventdispatcher::getInstance()->removeAndAddListener(SystemEventidentifier::EVENT_SYSTEM_USERLOGOUT, new SystemUserlogoutlistener());
    }
}

SystemUserlogoutlistener::staticConstruct();