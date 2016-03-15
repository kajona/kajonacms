<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

namespace Kajona\Search\Event;
use Kajona\Search\System\SearchEnumIndexaction;
use Kajona\Search\System\SearchIndexqueue;
use Kajona\Search\System\SearchIndexwriter;
use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\GenericeventListenerInterface;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\SystemEventidentifier;
use Kajona\System\System\SystemSetting;
use Kajona\Workflows\System\WorkflowsWorkflow;


/**
 * Updates the search index on end of each request. This avoids double indexing or unnecessary indexes for records to be deleted in the same request.
 *
 * @package module_search
 * @author sidler@mulchprod.de
 * @since 4.6
 */
class SearchRequestEndprocessinglistener implements GenericeventListenerInterface {

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


        $strConfigValue = SystemSetting::getConfigValue("_search_deferred_indexer_");
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
            $arrRows[] = array(generateSystemid(), $strOneId, SearchEnumIndexaction::INDEX()."");
        }

        foreach(array_keys(self::$arrToDelete) as $strOneId) {
            $arrRows[] = array(generateSystemid(), $strOneId, SearchEnumIndexaction::DELETE()."");
        }

        $objQueue = new SearchIndexqueue();
        $objQueue->addRowsToQueue($arrRows);
    }


    /**
     * Handles the processing of objects directly
     */
    private function processDirectly() {
        $objIndex = new SearchIndexwriter();

        //start by processing the records to be deleted
        foreach(self::$arrToDelete as $strOneId => $strObject) {
            $objIndex->removeRecordFromIndex($strOneId);
        }

        //add new records
        foreach(self::$arrToIndex as $strOneId => $objInstance) {
            if(!is_object($objIndex) && validateSystemid($objInstance))
                $objInstance = Objectfactory::getInstance()->getObject($objInstance);

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
            if($strSystemid instanceof WorkflowsWorkflow && $strSystemid->getStrClass() == "Kajona\\Search\\System\\Workflows\\WorkflowSearchDeferredindexer")
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
        CoreEventdispatcher::getInstance()->removeAndAddListener(SystemEventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, new SearchRequestEndprocessinglistener());
    }

}

SearchRequestEndprocessinglistener::staticConstruct();