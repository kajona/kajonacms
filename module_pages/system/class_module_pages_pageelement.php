<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                            *
********************************************************************************************************/

/**
 * Model for a element assigned to a page. NOT the raw-element!
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 * @targetTable page_element.page_element_id
 */
class class_module_pages_pageelement extends class_model implements interface_model, interface_admin_listable {


    private static $arrInitRowCache = array();


    /**
     * @var string
     * @tableColumn page_element_ph_placeholder
     */
    private $strPlaceholder = "";

    /**
     * @var string
     * @tableColumn page_element_ph_name
     */
    private $strName = "";

    /**
     * @var string
     * @tableColumn page_element_ph_element
     */
    private $strElement = "";

    /**
     * @var string
     * @tableColumn page_element_ph_title
     */
    private $strTitle = "";

    /**
     * @var string
     * @tableColumn page_element_ph_language
     */
    private $strLanguage = "";

    private $strClassAdmin = "";
    private $strClassPortal = "";
    private $intCachetime = -1;
    private $intRepeat = 0;


    private $intStartDate = 0;
    private $intEndDate = 0;

    private $strConfigVal1 = "";
    private $strConfigVal2 = "";
    private $strConfigVal3 = "";

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $this->setArrModuleEntry("modul", "pages_content");
        $this->setArrModuleEntry("moduleId", _pages_content_modul_id_);

        parent::__construct($strSystemid);

        $this->objSortManager = new class_pageelement_sortmanager($this);
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrName()." (".$this->getStrReadableName().")"." - ".$this->getStrTitle(true);
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        return "icon_page";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        return "";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }


    /**
     * Initalises the current object, if a systemid was given

     */
    protected function initObjectInternal() {

        //maybe
        if(isset(self::$arrInitRowCache[$this->getSystemid()])) {
            $arrRow = self::$arrInitRowCache[$this->getSystemid()];
        }
        else {
            $strQuery = "SELECT *
                             FROM "._dbprefix_."page_element,
                                  "._dbprefix_."element,
                                  "._dbprefix_."system_right,
                                  "._dbprefix_."system
                              LEFT JOIN "._dbprefix_."system_date
                                ON (system_id = system_date_id)
                             WHERE system_id= ?
                               AND page_element_ph_element = element_name
                               AND system_id = page_element_id
                               AND system_id = right_id
                             ORDER BY page_element_ph_placeholder ASC,
                                    system_sort ASC";
            $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));

        }
        $this->setArrInitRow($arrRow);
        if(count($arrRow) > 1) {
            $this->setStrPlaceholder($arrRow["page_element_ph_placeholder"]);
            $this->setStrName($arrRow["page_element_ph_name"]);
            $this->setStrElement($arrRow["page_element_ph_element"]);
            $this->setStrTitle($arrRow["page_element_ph_title"]);
            $this->setStrLanguage($arrRow["page_element_ph_language"]);
            $this->setStrClassAdmin($arrRow["element_class_admin"]);
            $this->setStrClassPortal($arrRow["element_class_portal"]);
            $this->setIntCachetime($arrRow["element_cachetime"]);
            $this->setIntRepeat($arrRow["element_repeat"]);
            $this->setStrConfigVal1($arrRow["element_config1"]);
            $this->setStrConfigVal2($arrRow["element_config2"]);
            $this->setStrConfigVal3($arrRow["element_config3"]);


            if($arrRow["system_date_start"] > 0)
                $this->intStartDate = $arrRow["system_date_start"];

            if($arrRow["system_date_end"] > 0)
                $this->intEndDate = $arrRow["system_date_end"];

        }
    }

    /**
     * saves the current object as a new object to the database
     *
     * @return bool
     */
    protected function onInsertToDb() {


        $objElementdefinitionToCreate = class_module_pages_element::getElement($this->getStrElement());
        if($objElementdefinitionToCreate == null)
            return false;

        //Build the class-name
        $strElementClass = str_replace(".php", "", $objElementdefinitionToCreate->getStrClassAdmin());
        //and finally create the object
        if($strElementClass != "") {
            $objElement = new $strElementClass();
            $strForeignTable = $objElement->getTable();


            //And create the row in the Element-Table, if given
            if($strForeignTable != "") {
                $strQuery = "INSERT INTO ".$strForeignTable." (content_id) VALUES (?)";
                $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
            }

        }

        //shift it to the last position by default
        //As a special feature, we set the element as the last
        $strQuery = "UPDATE "._dbprefix_."system SET system_sort = ? WHERE system_id = ?";
        $this->setIntSort(count($this->getSortedElementsAtPlaceholder()) + 1);
        $this->objDB->_pQuery($strQuery, array(count($this->getSortedElementsAtPlaceholder()) + 1, $this->getSystemid()));
        //And shift this element one pos up to get correct order on systemtables

        $this->objDB->flushQueryCache();


        return true;
    }

    /**
     * Creates an instance of the concrete admin-element instance, e.g. the concrete row-element
     * Please note, that due to performance issues the foreign content is not loaded in the step, use
     * $objElement->loadElementData() in order to fully initialize.
     *
     * @return class_element_admin
     */
    public function getConcreteAdminInstance() {
        if($this->getStrClassAdmin() == "")
            return null;

        $strElementClass = str_replace(".php", "", $this->getStrClassAdmin());
        //and finally create the object
        /** @var $objElement class_element_admin */
        $objElement = new $strElementClass();
        $objElement->setSystemid($this->getSystemid());
        return $objElement;
    }

    /**
     * Creates an instance of the concrete admin-element instance, e.g. the concrete row-element
     * Please note, that due to performance issues the foreign content is not loaded in the step!
     *
     * @return class_element_portal
     */
    public function getConcretePortalInstance() {

        if($this->getStrClassPortal() == "")
            return null;

        $strElementClass = str_replace(".php", "", $this->getStrClassPortal());
        //and finally create the object
        /** @var $objElement class_element_portal */
        $objElement = new $strElementClass($this);
        $objElement->setSystemid($this->getSystemid());
        return $objElement;
    }


    /**
     * Makes a copy of the current element and saves it attached to the given page.
     * This copy includes the records in the elements' foreign tables
     *
     * @param string $strNewPage
     *
     * @return class_module_pages_pageelement the new element or null in case of an error
     */
    public function copyObject($strNewPage = "") {

        class_logger::getInstance()->addLogRow("copy pageelement ".$this->getSystemid(), class_logger::$levelInfo);
        $this->objDB->transactionBegin();

        //fetch all values to insert after the general copy process - mainly the foreign table
        $objElement = $this->getConcreteAdminInstance();
        $arrElementData = $objElement->loadElementData();

        //duplicate the current elements - afterwards $this is the new element
        parent::copyObject($strNewPage);

        $objElement = $this->getConcreteAdminInstance();
        $arrNewElementData = $objElement->loadElementData();
        $arrElementData["content_id"] = $arrNewElementData["content_id"];
        $arrElementData["page_element_id"] = $arrNewElementData["page_element_id"];
        $arrElementData["system_id"] = $arrNewElementData["system_id"];
        $objElement->setArrParamData($arrElementData);

        $objElement->doBeforeSaveToDb();
        $objElement->updateForeignElement();
        $objElement->doAfterSaveToDb();

        $this->objDB->transactionCommit();

        return $this;

    }


    /**
     * Loads all Elements on the given page known by the system, so db-sided, not template-sided.
     * Returns the list of object
     *
     * @param string $strPageId
     * @param bool $bitJustActive
     * @param string $strLanguage
     *
     * @return class_module_pages_pageelement[]
     * @static
     */
    public static function getElementsOnPage($strPageId, $bitJustActive = false, $strLanguage = "") {

        //since theres the time as an parameter, theres no need for querying the cache...
        $arrIds = self::getPlainElementsOnPage($strPageId, $bitJustActive, $strLanguage);

        $arrReturn = array();
        foreach($arrIds as $arrOneId)
            $arrReturn[] = new class_module_pages_pageelement($arrOneId["system_id"]);

        return $arrReturn;
    }

    /**
     * Loads the list of elements on a single page.
     * Returns an array of plain data, not the corresponding objects.
     * In most cases getElementsOnPage is the right way to go.
     *
     * @see class_module_pages_pageeelemtn::getElementsOnPage()
     *
     * @param $strPageId
     * @param bool $bitJustActive
     * @param string $strLanguage
     *
     * @return array
     */
    public static function getPlainElementsOnPage($strPageId, $bitJustActive = false, $strLanguage = "") {
        //Calculate the current day as a time-stamp. This improves database-caches e.g. the kajona or mysql-query-cache.
        $objDate = new class_date();
        $objDate->setIntMin(0, true);
        $objDate->setIntSec(0, true);
        $objDate->setIntHour(0, true);

        $longToday = $objDate->getLongTimestamp();
        $arrParams = array($strPageId, $strLanguage);

        $strAnd = "";
        if($bitJustActive) {
            $strAnd = "AND system_status = 1
                       AND ( system_date_start IS null OR (system_date_start = 0 OR system_date_start <= ?))
                       AND ( system_date_end IS null OR (system_date_end = 0 OR system_date_end >= ?)) ";

            $arrParams[] = $longToday;
            $arrParams[] = $longToday;
        }

        $strQuery = "SELECT *
                       FROM "._dbprefix_."page_element,
                            "._dbprefix_."element as element,
                            "._dbprefix_."system_right,
                            "._dbprefix_."system as system
                  LEFT JOIN "._dbprefix_."system_date
                         ON (system.system_id = system_date_id)
                      WHERE system.system_prev_id= ?
                        AND page_element_ph_element = element.element_name
                        AND system.system_id = page_element_id
                        AND system.system_id = right_id
                        AND page_element_ph_language = ?
                       ".$strAnd."
                  ORDER BY page_element_ph_placeholder ASC,
                            system_sort ASC";

        $arrReturn = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);

        foreach($arrReturn as $arrOneRow) {
            if(!isset(self::$arrInitRowCache[$arrOneRow["system_id"]]))
                self::$arrInitRowCache[$arrOneRow["system_id"]] = $arrOneRow;
        }

        return $arrReturn;
    }


    /**
     * Loads all Elements on the given ignoring both, status and language
     *
     * @param string $strPageId
     *
     * @return class_module_pages_pageelement[]
     * @static
     */
    public static function getAllElementsOnPage($strPageId) {

        $strQuery = "SELECT system_id
						 FROM "._dbprefix_."page_element,
						      "._dbprefix_."element,
						      "._dbprefix_."system
						 WHERE system_prev_id=?
						   AND page_element_ph_element = element_name
						   AND system_id = page_element_id
						 ORDER BY page_element_ph_placeholder ASC,
						 		system_sort ASC";

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strPageId));

        $arrReturn = array();
        foreach($arrIds as $arrOneId)
            $arrReturn[] = new class_module_pages_pageelement($arrOneId["system_id"]);

        return $arrReturn;
    }

    /**
     * Tries to load an element identified by the pageId, the name of the placeholder and the language.
     * If no matching element was found, null is returned.
     *
     * @param string $strPageId
     * @param string $strPlaceholder
     * @param string $strLanguage
     * @param bool $bitJustActive
     *
     * @return class_module_pages_pageelement[]
     */
    public static function getElementsByPlaceholderAndPage($strPageId, $strPlaceholder, $strLanguage, $bitJustActive = true) {
        $strAnd = "";

        $arrParams = array($strPageId, $strLanguage, $strPlaceholder);

        if($bitJustActive) {
            $strAnd = "AND system_status = 1
                       AND ( system_date_start IS null OR (system_date_start = 0 OR system_date_start <= ?))
                       AND ( system_date_end IS null OR (system_date_end = 0 OR system_date_end >= ? )) ";

            $arrParams[] = time();
            $arrParams[] = time();
        }

        $strQuery = "SELECT system_id
                         FROM "._dbprefix_."page_element,
                              "._dbprefix_."element,
                              "._dbprefix_."system
                              LEFT JOIN "._dbprefix_."system_date
                                ON (system_id = system_date_id)
                         WHERE system_prev_id= ?
                           AND page_element_ph_element = element_name
                           AND system_id = page_element_id
                           AND page_element_ph_language = ?
                           AND page_element_ph_placeholder = ?
                           ".$strAnd."
                         ORDER BY system_sort ASC";

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);

        $arrReturn = array();
        foreach($arrIds as $arrOneRow) {
            $arrReturn[] = new class_module_pages_pageelement($arrOneRow["system_id"]);
        }

        return $arrReturn;

    }

    /**
     * Helper, loads all elements registered at a single placeholder,
     * By default this method ignors the db-cache.
     *
     * @return array
     */
    public function getSortedElementsAtPlaceholder() {

        $strQuery = "SELECT *
						 FROM "._dbprefix_."page_element,
						      "._dbprefix_."element,
						      "._dbprefix_."system
						 WHERE system_prev_id= ?
						   AND page_element_ph_element = element_name
                           AND page_element_ph_language = ?
                           AND page_element_ph_placeholder = ?
						   AND system_id = page_element_id
						 ORDER BY system_sort ASC";

        $arrElementsOnPage = $this->objDB->getPArray($strQuery, array($this->getPrevId(), $this->getStrLanguage(), $this->getStrPlaceholder()), null, null, false);
        return $arrElementsOnPage;
    }


    /**
     * Deletes the element from the system-tables, also from the foreign-element-tables
     *
     * @return bool
     */
    protected function deleteObjectInternal() {

        //fix the internal sorting
        $arrElements = $this->getSortedElementsAtPlaceholder();

        $bitHit = false;
        foreach($arrElements as $arrOneSibling) {

            if($bitHit) {
                $strQuery = "UPDATE "._dbprefix_."system SET system_sort = system_sort-1 where system_id = ?";
                $this->objDB->_pQuery($strQuery, array($arrOneSibling["system_id"]));
            }

            if($arrOneSibling["system_id"] == $this->getSystemid())
                $bitHit = true;
        }


        //Load the Element-Data
        $objElement = $this->getConcreteAdminInstance();
        if($objElement != null) {
            //Fetch the table
            $strElementTable = $objElement->getTable();
            //Delete the entry in the Element-Table
            if($strElementTable != "") {
                $strQuery = "DELETE FROM ".$strElementTable." WHERE content_id= ?";
                if(!$this->objDB->_pQuery($strQuery, array($this->getSystemid())))
                    return false;
            }
        }

        //Delete from page_element table
        parent::deleteObjectInternal();

        //Loading the data of the corresponding site
        $objPage = new class_module_pages_page($this->getPrevId());
        class_cache::flushCache("class_element_portal", $objPage->getStrName());

        return true;
    }

    /**
     * Tries to assign all page-elements not yet assigned to a language.
     *
     * @param string $strTargetLanguage
     *
     * @return bool
     */
    public static function assignNullElements($strTargetLanguage) {
        //Load all non-assigned props
        $strQuery = "SELECT page_element_id FROM "._dbprefix_."page_element
                     WHERE page_element_ph_language = '' OR page_element_ph_language IS NULL";
        $arrElementIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());

        foreach($arrElementIds as $arrOneId) {
            $strId = $arrOneId["page_element_id"];
            $strUpdate = "UPDATE "._dbprefix_."page_element
                          SET page_element_ph_language = ?
                          WHERE page_element_id = ?";

            if(!class_carrier::getInstance()->getObjDB()->_pQuery($strUpdate, array($strTargetLanguage, $strId)))
                return false;

        }

        return true;
    }

    /**
     * Updates placeholders in the db. Replaces all placeholders with a new one, if the template of elements' page
     * corresponds to the given one
     *
     * @param string $strTemplate
     * @param string $strOldPlaceholder
     * @param string $strNewPlaceholder
     *
     * @return bool
     */
    public static function updatePlaceholders($strTemplate, $strOldPlaceholder, $strNewPlaceholder) {
        $bitReturn = true;
        //Fetch all pages
        $arrObjPages = class_module_pages_page::getAllPages();
        foreach($arrObjPages as $objOnePage) {
            if($objOnePage->getStrTemplate() == $strTemplate || $strTemplate == "-1") {
                //Search for matching elements
                $strQuery = "SELECT system_id
						 FROM "._dbprefix_."page_element,
						      "._dbprefix_."element,
						      "._dbprefix_."system
						 WHERE system_prev_id= ?
						   AND page_element_ph_element = element_name
						   AND system_id = page_element_id
						 ORDER BY page_element_ph_placeholder ASC,
						 		system_sort ASC";

                $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($objOnePage->getSystemid()));
                $arrPageElements = array();
                foreach($arrIds as $arrOneRow) {
                    $arrPageElements[] = new class_module_pages_pageelement($arrOneRow["system_id"]);
                }

                foreach($arrPageElements as $objOnePageelement) {
                    if($objOnePageelement->getStrPlaceholder() == $strOldPlaceholder) {
                        $objOnePageelement->setStrPlaceholder($strNewPlaceholder);
                        if(!$objOnePageelement->updateObjectToDb())
                            $bitReturn = false;
                    }
                }
            }
        }
        return $bitReturn;
    }


    public function getStrPlaceholder() {
        return $this->strPlaceholder;
    }

    public function getStrName() {
        return $this->strName;
    }

    public function getStrElement() {
        return $this->strElement;
    }

    /**
     * Returns a readable representation of the current elements' name.
     * Searches the lang-file for an entry element_NAME_name.
     *
     * @return string
     */
    public function getStrReadableName() {
        $strName = class_carrier::getInstance()->getObjLang()->getLang("element_".$this->getStrElement()."_name", "elements");
        if($strName == "!element_".$this->getStrElement()."_name!")
            $strName = $this->getStrElement();
        return $strName;
    }

    /**
     * Returns a title.
     * If no title was specified, it creates an instance of the current element and
     * calls getContentTitle() to get an title
     *
     * @param bool $bitClever disables the loading by using an instance of the element
     *
     * @return string
     */
    public function getStrTitle($bitClever = false) {
        if($this->strTitle != "" || !$bitClever || $this->getStrClassAdmin() == "")
            return $this->strTitle;
        //Create an instance of the object and let it serve the comment...
        $objElement = $this->getConcreteAdminInstance();
        return $objElement->getContentTitle();
    }

    public function getStrClassPortal() {
        return $this->strClassPortal;
    }

    public function getStrClassAdmin() {
        return $this->strClassAdmin;
    }

    public function getIntCachetime() {
        return $this->intCachetime;
    }

    public function getIntRepeat() {
        return $this->intRepeat;
    }

    public function getStrLanguage() {
        return $this->strLanguage;
    }

    public function getStartDate() {
        return $this->intStartDate;
    }

    public function getEndDate() {
        return $this->intEndDate;
    }

    public function setStrPlaceholder($strPlaceholder) {
        $this->strPlaceholder = $strPlaceholder;
    }

    public function setStrName($strName) {
        $this->strName = $strName;
    }

    public function setStrElement($strElement) {
        $this->strElement = $strElement;
    }

    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }

    private function setStrClassPortal($strClassPortal) {
        $this->strClassPortal = $strClassPortal;
    }

    private function setStrClassAdmin($strClassAdmin) {
        $this->strClassAdmin = $strClassAdmin;
    }

    private function setIntCachetime($intCachtime) {
        $this->intCachetime = $intCachtime;
    }

    private function setIntRepeat($intRepeat) {
        $this->intRepeat = $intRepeat;
    }

    public function setStrLanguage($strLanguage) {
        $this->strLanguage = $strLanguage;
    }

    public function setStrConfigVal1($intConfigVal1) {
        $this->strConfigVal1 = $intConfigVal1;
    }

    public function getStrConfigVal1() {
        return $this->strConfigVal1;
    }

    public function setStrConfigVal2($intConfigVal2) {
        $this->strConfigVal2 = $intConfigVal2;
    }

    public function getStrConfigVal2() {
        return $this->strConfigVal2;
    }

    public function setStrConfigVal3($intConfigVal3) {
        $this->strConfigVal3 = $intConfigVal3;
    }

    public function getStrConfigVal3() {
        return $this->strConfigVal3;
    }

}
