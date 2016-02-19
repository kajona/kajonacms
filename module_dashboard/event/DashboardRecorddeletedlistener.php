<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

namespace Kajona\Dashboard\Event;

use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\GenericeventListenerInterface;
use Kajona\System\System\OrmComparatorEnum;
use Kajona\System\System\OrmDeletedhandlingEnum;
use Kajona\System\System\OrmObjectlist;
use Kajona\System\System\OrmObjectlistPropertyRestriction;
use Kajona\System\System\SystemEventidentifier;

/**
 * Listener to handle deleted users.
 * Removes all relevant widgets.
 *
 * @package module_dashboard
 * @author sidler@mulchprod.de
 *
 */
class DashboardRecorddeletedlistener implements GenericeventListenerInterface {


    /**
     * Implementing callback to react on user-delete events
     *
     * Called whenever a record was deleted using the common methods.
     * Implement this method to be notified when a record is deleted, e.g. to to additional cleanups afterwards.
     * There's no need to register the listener, this is done automatically.
     *
     * Make sure to return a matching boolean-value, otherwise the transaction may be rolled back.
     *
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments) {
        //unwrap arguments
        list($strSystemid, $strSourceClass) = $arrArguments;

        if($strSourceClass == "class_module_user_user" && validateSystemid($strSystemid)) {

            $objORM = new OrmObjectlist();
            $objORM->addWhereRestriction(new OrmObjectlistPropertyRestriction("strUser", OrmComparatorEnum::Equal(), $strSystemid));
            $objORM->setObjHandleLogicalDeleted(OrmDeletedhandlingEnum::INCLUDED);
            $arrWidgets = $objORM->getObjectList("class_module_dashboard_widget");

            foreach($arrWidgets as $objWidget) {
                $objWidget->deleteObjectFromDatabase();
            }
        }

        return true;
    }

    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     * @return void
     */
    public static function staticConstruct() {
        CoreEventdispatcher::getInstance()->removeAndAddListener(SystemEventidentifier::EVENT_SYSTEM_RECORDDELETED, new DashboardRecorddeletedlistener());
    }

}

DashboardRecorddeletedlistener::staticConstruct();
