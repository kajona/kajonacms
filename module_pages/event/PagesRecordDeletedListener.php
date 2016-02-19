<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\Event;

use Kajona\System\System\Carrier;
use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\GenericeventListenerInterface;
use Kajona\System\System\SystemEventidentifier;

/**
 * Removes category-assignments on record-deletions
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 *
 */
class PagesRecordDeletedListener implements GenericeventListenerInterface {


    /**
     * Searches for tags assigned to the systemid to be deleted.
     *
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments) {
        //unwrap arguments
        list($strSystemid, $strSourceClass) = $arrArguments;

        if($strSourceClass == "Kajona\\Pages\\System\\PagesPage") {
            return Carrier::getInstance()->getObjDB()->_pQuery("DELETE FROM " ._dbprefix_. "page_properties WHERE pageproperties_id = ?", array($strSystemid));
        }


        return true;
    }



    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     * @return void
     */
    public static function staticConstruct() {
        CoreEventdispatcher::getInstance()->removeAndAddListener(SystemEventidentifier::EVENT_SYSTEM_RECORDDELETED, new PagesRecordDeletedListener());
    }


}

PagesRecordDeletedListener::staticConstruct();