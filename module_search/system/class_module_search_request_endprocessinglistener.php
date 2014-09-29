<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Updates the search index on end of each request. This avoids double indexing or unnecessary indexes for records to be deleted in the same request.
 *
 * @package module_search
 * @author sidler@mulchprod.de
 * @since 4.6
 */
class class_module_search_request_endprocessinglistener implements interface_genericevent_listener {

    private static $arrToDelete = array();
    private static $arrToIndex = array();


    /**
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments) {

        $objIndex = new class_module_search_indexwriter();

        //start by processing the records to be deleted
        foreach(self::$arrToDelete as $strOneId => $strObject) {
            $objIndex->removeRecordFromIndex($strOneId);

            if(isset(self::$arrToIndex[$strOneId]))
                unset(self::$arrToIndex[$strOneId]);
        }

        //add new records
        foreach(self::$arrToIndex as $strOneId => $objInstance) {
            if(!is_object($objIndex) && validateSystemid($objInstance))
                $objInstance = class_objectfactory::getInstance()->getInstance($objInstance);

            $objIndex->indexObject($objInstance);
        }

        return true;
    }

    /**
     * Adds a systemid to be removed from the search-index
     * @param $strSystemid
     */
    public static function addIdToDelete($strSystemid) {
        self::$arrToDelete[$strSystemid] = $strSystemid;
    }

    /**
     * Adds a records systemid to be added to the search index
     * @param string $strSystemid
     */
    public static function addIdToIndex($strSystemid) {
        if(is_object($strSystemid) && $strSystemid instanceof class_model)
            self::$arrToIndex[$strSystemid->getSystemid()] = $strSystemid;
        else if(is_string($strSystemid) && !isset(self::$arrToIndex))
            self::$arrToIndex[$strSystemid] = $strSystemid;

    }


    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     * @return void
     */
    public static function staticConstruct() {
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_REQUEST_ENDPROCESSING, new class_module_search_request_endprocessinglistener());
    }

}

class_module_search_request_endprocessinglistener::staticConstruct();