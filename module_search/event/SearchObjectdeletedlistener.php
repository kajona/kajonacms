<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

namespace Kajona\Search\Event;

use Kajona\Search\System\SearchIndexwriter;
use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\GenericeventListenerInterface;
use Kajona\System\System\SystemEventidentifier;

/**
 * Handles record-delete events. If catched, the index-entries assigned to the systemid
 * will be removed from the index.
 *
 * @package module_search
 * @author sidler@mulchprod.de
 */
class SearchObjectdeletedlistener implements GenericeventListenerInterface {

    public static $BIT_UPDATE_INDEX_ON_END_OF_REQUEST = true;


    /**
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments) {
        //unwrap arguments
        list($strSystemid, $strSourceClass) = $arrArguments;

        if(self::$BIT_UPDATE_INDEX_ON_END_OF_REQUEST) {
            SearchRequestEndprocessinglistener::addIdToDelete($strSystemid);
            return true;
        }
        else {
            $objIndex = new SearchIndexwriter();
            return $objIndex->removeRecordFromIndex($strSystemid);
        }

    }

    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     * @return void
     */
    public static function staticConstruct() {
        CoreEventdispatcher::getInstance()->removeAndAddListener(SystemEventidentifier::EVENT_SYSTEM_RECORDDELETED, new SearchObjectdeletedlistener());
        CoreEventdispatcher::getInstance()->removeAndAddListener(SystemEventidentifier::EVENT_SYSTEM_RECORDDELETED_LOGICALLY, new SearchObjectdeletedlistener());
    }

}

SearchObjectdeletedlistener::staticConstruct();