<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
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
class class_module_search_objectdeletedlistener implements interface_genericevent_listener {

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
            class_module_search_request_endprocessinglistener::addIdToDelete($strSystemid);
            return true;
        }
        else {
            $objIndex = new class_module_search_indexwriter();
            return $objIndex->removeRecordFromIndex($strSystemid);
        }

    }

    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     * @return void
     */
    public static function staticConstruct() {
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_RECORDDELETED, new class_module_search_objectdeletedlistener());
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_RECORDDELETED_LOGICALLY, new class_module_search_objectdeletedlistener());
    }

}

class_module_search_objectdeletedlistener::staticConstruct();