<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_search_indexwriter.php 6392 2014-01-31 08:56:43Z sidler $                                  *
********************************************************************************************************/

/**
 * Handles record-updated events. If catched, the index-entries assigned to the systemid
 * will be updated.
 *
 * @package module_search
 * @author sidler@mulchprod.de
 */
class class_module_search_recordupdatedlistener implements interface_genericevent_listener {

    public static $BIT_UPDATE_INDEX_ON_END_OF_REQUEST = true;

    /**
     * Triggered as soon as a record is updated
     *
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments) {

        $objRecord = $arrArguments[0];

        if(self::$BIT_UPDATE_INDEX_ON_END_OF_REQUEST) {
            class_module_search_request_endprocessinglistener::addIdToIndex($objRecord);
        }
        else {
            $objIndex = new class_module_search_indexwriter();
            $objIndex->indexObject($objRecord);

        }

        return true;
    }

    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     * @return void
     */
    public static function staticConstruct() {
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_RECORDUPDATED, new class_module_search_recordupdatedlistener());
    }


}

//register the listener
class_module_search_recordupdatedlistener::staticConstruct();