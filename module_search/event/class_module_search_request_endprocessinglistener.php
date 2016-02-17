<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
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


        if(count(self::$arrToDelete) == 0 && count(self::$arrToIndex) == 0)
            return true;

        //clean and reduce arrays to avoid logical duplicates
        foreach(self::$arrToDelete as $strOneId => $strObject) {
            if(isset(self::$arrToIndex[$strOneId]))
                unset(self::$arrToIndex[$strOneId]);
        }


        $strConfigValue = class_module_system_setting::getConfigValue("_search_deferred_indexer_");
        if($strConfigValue !== null && $strConfigValue == "true") {
            $this->processDeferred();
        }
        else {
            $this->processDirectly();
        }

        self::$arrToDelete = array();
        self::$arrToIndex = array();

        return true;
    }

    /**
     * Creates a new workflow-instance in order to index changed objects in a decoupled process
     */
    private function processDeferred() {

        $arrRows = array();
        foreach(array_keys(self::$arrToIndex) as $strOneId) {
            $arrRows[] = array(generateSystemid(), $strOneId, class_search_enum_indexaction::INDEX()."");
        }

        foreach(array_keys(self::$arrToDelete) as $strOneId) {
            $arrRows[] = array(generateSystemid(), $strOneId, class_search_enum_indexaction::DELETE()."");
        }

        $objQueue = new class_search_indexqueue();
        $objQueue->addRowsToQueue($arrRows);
    }


    /**
     * Handles the processing of objects directly
     */
    private function processDirectly() {
        $objIndex = new class_module_search_indexwriter();

        //start by processing the records to be deleted
        foreach(self::$arrToDelete as $strOneId => $strObject) {
            $objIndex->removeRecordFromIndex($strOneId);
        }

        //add new records
        foreach(self::$arrToIndex as $strOneId => $objInstance) {
            if(!is_object($objIndex) && validateSystemid($objInstance))
                $objInstance = class_objectfactory::getInstance()->getObject($objInstance);

            $objIndex->indexObject($objInstance);
        }

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
        if(is_object($strSystemid) && $strSystemid instanceof \Kajona\System\System\Model) {
            if($strSystemid instanceof class_module_workflows_workflow && $strSystemid->getStrClass() == "class_workflow_search_deferredindexer")
                return;

            self::$arrToIndex[$strSystemid->getSystemid()] = $strSystemid;
        }
        else if(is_string($strSystemid) && !isset(self::$arrToIndex)) {
            self::$arrToIndex[$strSystemid] = $strSystemid;
        }

    }


    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     * @return void
     */
    public static function staticConstruct() {
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, new class_module_search_request_endprocessinglistener());
    }

}

class_module_search_request_endprocessinglistener::staticConstruct();