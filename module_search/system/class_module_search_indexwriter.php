<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * General object to build / rebuild / update the search-index.
 * Registers for record-updated events in order to update the index of an object.
 *
 * @package module_search
 * @author tim.kiefer@kojikui.de
 */
class class_module_search_indexwriter {

    const STR_ANNOTATION_ADDSEARCHINDEX = "@addSearchIndex";

    private $objConfig = null;
    private $objDB = null;

    private static $isIndexAvailable = null;

    /**
     * Internal flag to avoid explicit delete statements on a full index rebuild. since
     * the index is flushed before, the delete statements are useless and only time-consuming.
     * @var bool
     */
    private $bitSkipDeletes = false;

    /**
     * Plain constructor
     */
    public function __construct() {
        //Generating all the needed objects. For this we use our cool cool carrier-object
        //take care of loading just the necessary objects
        $this->objConfig = class_carrier::getInstance()->getObjConfig();
        $this->objDB = class_carrier::getInstance()->getObjDB();
    }

    /**
     * Validates if the search module is installed with a supported index
     * @return bool
     */
    private static function isIndexAvailable() {
        if(self::$isIndexAvailable === null) {
            $objSearch = class_module_system_module::getModuleByName("search");
            if($objSearch != null && version_compare($objSearch->getStrVersion(), "4.4", ">="))
                self::$isIndexAvailable = true;
            else
                self::$isIndexAvailable = false;
        }

        return self::$isIndexAvailable;
    }

    /**
     * Returns the number of documents currently in the index
     * @return int
     */
    public function getNumberOfDocuments() {
        if(!self::isIndexAvailable())
            return 0;

        $arrRow = $this->objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."search_ix_document", array());
        return $arrRow["COUNT(*)"];
    }

    /**
     * Returns the number of entries currently in the index
     * @return int
     */
    public function getNumberOfContentEntries() {
        if(!self::isIndexAvailable())
            return 0;

        $arrRow = $this->objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."search_ix_content", array());
        return $arrRow["COUNT(*)"];
    }

    /**
     * Removes an entry from the index, based on the systemid. Removes the indexed content and the document.
     * @param string $strSystemid
     *
     * @return bool
     */
    public function removeRecordFromIndex($strSystemid) {

        if(!self::isIndexAvailable())
            return true;

        $arrRow = $this->objDB->getPRow("SELECT * FROM "._dbprefix_."search_ix_document WHERE search_ix_system_id = ?", array($strSystemid));

        if(isset($arrRow["search_ix_document_id"])) {
            $this->objDB->_pQuery("DELETE FROM "._dbprefix_."search_ix_content WHERE search_ix_content_document_id = ?", array($arrRow["search_ix_document_id"]));
            $this->objDB->_pQuery("DELETE FROM "._dbprefix_."search_ix_document WHERE search_ix_document_id = ?", array($arrRow["search_ix_document_id"]));
        }

        return true;
    }

    /**
     * Triggers the indexing of a single object.
     *
     * @param class_model $objInstance
     *
     * @return void
     */
    public function indexObject(class_model $objInstance = null) {

        if(!self::isIndexAvailable())
            return;

        if($objInstance != null && $objInstance instanceof class_module_pages_pageelement) {
            $objInstance = $objInstance->getConcreteAdminInstance();
            if($objInstance != null)
                $objInstance->loadElementData();
        }

        if($objInstance == null)
            return;

        $objSearchDocument = new class_module_search_document();
        $objSearchDocument->setDocumentId(generateSystemid());
        $objSearchDocument->setStrSystemId($objInstance->getSystemid());
        if($objInstance instanceof interface_search_portalobject) {
            $objSearchDocument->setBitPortalObject(true);
            $objSearchDocument->setStrContentLanguage($objInstance->getContentLang());
        }

        $objReflection = new class_reflection($objInstance);
        $arrProperties = $objReflection->getPropertiesWithAnnotation(self::STR_ANNOTATION_ADDSEARCHINDEX);
        foreach($arrProperties as $strPropertyName => $strAnnotationValue) {
            $getter = $objReflection->getGetter($strPropertyName);
            $strContent = $objInstance->$getter();
            $objSearchDocument->addContent($strPropertyName, $strContent);
        }

        //trigger event-listeners
        class_core_eventdispatcher::getInstance()->notifyGenericListeners(class_search_eventidentifier::EVENT_SEARCH_OBJECTINDEXED, array($objInstance, $objSearchDocument));

        $this->updateSearchDocumentToDb($objSearchDocument);
    }

    /**
     * Triggers a full rebuild of the index.
     *
     * @return void
     */
    public function indexRebuild() {

        if(!self::isIndexAvailable())
            return;

        $this->clearIndex();
        $arrObj = $this->getIndexableEntries();

        $this->bitSkipDeletes = true;

        $intI = 0;
        foreach($arrObj as $objObj) {
            $objInstance = class_objectfactory::getInstance()->getObject($objObj["system_id"]);
            if($objInstance != null)
                $this->indexObject($objInstance);

            //flush the caches each 4.000 objects in order to keep memory usage low
            if(++$intI > 4000) {
                class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBQUERIES | class_carrier::INT_CACHE_TYPE_ORMCACHE | class_carrier::INT_CACHE_TYPE_OBJECTFACTORY);
                $intI = 0;
            }
        }

        $this->bitSkipDeletes = false;
    }

    /**
     * @return array
     */
    private function getIndexableEntries(){
        //Load possible existing document if exists
        $strQuery = "SELECT * FROM " . _dbprefix_ . "system WHERE system_deleted = 0";
        return $this->objDB->getPArray($strQuery, array());
    }

    /**
     * Clears the complete cache
     * @return void
     */
    public function clearIndex() {

        if(!self::isIndexAvailable())
            return;

        // Delete existing entries
        $strQuery = "DELETE FROM " . _dbprefix_ . "search_ix_document";
        $this->objDB->_pQuery($strQuery, array());

        $strQuery = "DELETE FROM " . _dbprefix_ . "search_ix_content";
        $this->objDB->_pQuery($strQuery, array());
    }

    /**
     * @param class_module_search_document $objSearchDoc
     * @return void
     */
    public function updateSearchDocumentToDb(class_module_search_document $objSearchDoc) {

        if(!self::isIndexAvailable())
            return;

        // Delete existing entries
        if(!$this->bitSkipDeletes)
            $this->removeRecordFromIndex($objSearchDoc->getStrSystemId());

        if(count($objSearchDoc->getContent()) == 0)
            return;

        //insert search document
        $strQuery = "INSERT INTO " . _dbprefix_ . "search_ix_document
                        (search_ix_document_id, search_ix_system_id, search_ix_content_lang, search_ix_portal_object) VALUES
                        (?, ?, ?, ?)";
        $this->objDB->_pQuery($strQuery, array($objSearchDoc->getDocumentId(), $objSearchDoc->getStrSystemId(), $objSearchDoc->getStrContentLanguage(), $objSearchDoc->getBitPortalObject() ? 1 : 0));

        $this->updateSearchContentsToDb($objSearchDoc->getContent());
    }

    /**
     * @param class_module_search_content[] $arrSearchContent
     *
     * @return void
     */
    private function updateSearchContentsToDb(array $arrSearchContent) {
        $arrValues = array();

        foreach($arrSearchContent as $objOneContent) {
            $arrValues[] = array(
                $objOneContent->getStrId(),
                $objOneContent->getFieldName(),
                $objOneContent->getContent(),
                $objOneContent->getScore(),
                $objOneContent->getDocumentId()
            );
        }

        //insert search document in a single query - much faster than single updates
        $this->objDB->multiInsert(
            "search_ix_content",
            array("search_ix_content_id", "search_ix_content_field_name", "search_ix_content_content", "search_ix_content_score", "search_ix_content_document_id"),
            $arrValues
        );
    }

    /**
     * Resets the internal check whether the search module is available with index support or not.
     * @return void
     */
    public static function resetIndexAvailableCheck() {
        self::$isIndexAvailable = null;
    }
}
