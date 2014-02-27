<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
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
    private $objConfig = null;
    private $objDB = null;

    private static $isIndexAvailable = null;

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
            $this->objDB->_pQuery("DELETE FROM "._dbprefix_."search_ix_content WHERE search_ix_content_id = ?", array($arrRow["search_ix_document_id"]));
            $this->objDB->_pQuery("DELETE FROM "._dbprefix_."search_ix_document WHERE search_ix_document_id = ?", array($arrRow["search_ix_document_id"]));
        }

        return true;
    }

    /**
     * @param class_model $objInstance
     * @return void
     */
    public function indexObject(class_model $objInstance) {

        if(!self::isIndexAvailable())
            return;

        if($objInstance instanceof class_module_pages_pageelement) {
            $objInstance = $objInstance->getConcreteAdminInstance();
            if($objInstance != null)
                $objInstance->loadElementData();
        }

        if($objInstance == null)
            return;

        $objSearchDocument = new class_module_search_document();
        $objSearchDocument->setDocumentId(generateSystemid());
        $objSearchDocument->setStrSystemId($objInstance->getSystemid());

        $objReflection = new class_reflection($objInstance);
        $arrProperties = $objReflection->getPropertiesWithAnnotation("@addSearchIndex");
        foreach($arrProperties as $strPropertyName => $strAnnotationValue) {
            $getter = $objReflection->getGetter($strPropertyName);
            $strContent = $objInstance->$getter();
            //TODO sir: changed first param from db-field to property name since there may be indexable fields not stored directly to the database
            $objSearchDocument->addContent($strPropertyName, $strContent);
        }

        //trigger event-listeners
        class_core_eventdispatcher::getInstance()->notifyGenericListeners(class_search_eventidentifier::EVENT_SEARCH_OBJECTINDEXED, array($objInstance, $objSearchDocument));

        $this->updateSearchDocumentToDb($objSearchDocument);
    }

    /**
     * Triggers a full rebuild of the index. The index is not flused before!
     * @see clearIndex()
     *
     * @return void
     */
    public function indexRebuild() {

        if(!self::isIndexAvailable())
            return;

        $this->clearIndex();
        $arrObj = $this->getIndexableEntries();

        foreach($arrObj as $objObj) {
            $objInstance = class_objectfactory::getInstance()->getObject($objObj["system_id"]);
            if($objInstance != null)
                $this->indexObject($objInstance);
        }
    }

    /**
     * @return array
     */
    private function getIndexableEntries(){
        //Load possible existing document if exists
        $strQuery = "SELECT * FROM " . _dbprefix_ . "system ";
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
     * @param class_module_search_document $objSearchDocument
     * @return void
     */
    public function updateSearchDocumentToDb($objSearchDocument) {

        if(!self::isIndexAvailable())
            return;

        //Load possible existing document if exists
        $strQuery = "SELECT * FROM " . _dbprefix_ . "search_ix_document " .
            "WHERE search_ix_system_id = ?";

        $arrSearchDocument = $this->objDB->getPRow($strQuery, array($objSearchDocument->getStrSystemId()));

        // Delete existing entries
        if(count($arrSearchDocument) > 0) {
            $strDocumentId = $arrSearchDocument["search_ix_document_id"];


            $strQuery = "DELETE FROM " . _dbprefix_ . "search_ix_document
            WHERE search_ix_system_id = ?";
            $this->objDB->_pQuery($strQuery, array($objSearchDocument->getStrSystemId()));

            $strQuery = "DELETE FROM " . _dbprefix_ . "search_ix_content
            WHERE search_ix_content_document_id = ?";
            $this->objDB->_pQuery($strQuery, array($strDocumentId));
        }

        if(count($objSearchDocument->getContent()) == 0)
            return;

        //insert search document
        $strQuery = "INSERT INTO " . _dbprefix_ . "search_ix_document
                        (search_ix_document_id, search_ix_system_id) VALUES
                        (?, ?)";
        $this->objDB->_pQuery($strQuery, array($objSearchDocument->getDocumentId(), $objSearchDocument->getStrSystemId()));

        foreach($objSearchDocument->getContent() as $objSearchContent)
            $this->updateSearchContentToDb($objSearchContent);
    }

    /**
     * @param class_module_search_content $objSearchContent
     * @return void
     */
    private function updateSearchContentToDb($objSearchContent) {
        //insert search document
        $strQuery = "INSERT INTO " . _dbprefix_ . "search_ix_content
                        (search_ix_content_id, search_ix_content_field_name, search_ix_content_content, search_ix_content_score, search_ix_content_document_id) VALUES
                        (?, ?, ?, ?, ?)";
        $this->objDB->_pQuery($strQuery, array($objSearchContent->getStrId(), $objSearchContent->getFieldName(), $objSearchContent->getContent(), $objSearchContent->getScore(), $objSearchContent->getDocumentId()));
    }

    /**
     * Resets the internal check whether the search module is available with index support or not.
     * @return void
     */
    public static function resetIndexAvailableCheck() {
        self::$isIndexAvailable = null;
    }
}
