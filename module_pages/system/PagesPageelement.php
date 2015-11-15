<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\System;

use class_cache;
use class_carrier;
use class_date;
use class_exception;
use class_logger;
use class_model;
use class_objectfactory;
use class_orm_base;
use class_orm_objectlist;
use class_orm_rowcache;
use class_reflection;
use interface_admin_listable;
use interface_model;
use Kajona\Pages\Admin\ElementAdmin;
use Kajona\Pages\Portal\ElementPortal;

/**
 * Model for a element assigned to a page. NOT the raw-element!
 *
 * @author sidler@mulchprod.de
 * @targetTable page_element.page_element_id
 * @sortManager Kajona\Pages\System\PageelementSortmanager
 *
 * @module pages_content
 * @moduleId _pages_content_modul_id_
 *
 * @blockFromAutosave
 */
class PagesPageelement extends class_model implements interface_model, interface_admin_listable
{


    /**
     * @var string
     * @tableColumn page_element.page_element_ph_placeholder
     * @tableColumnDatatype text
     */
    private $strPlaceholder = "";

    /**
     * @var string
     * @tableColumn page_element.page_element_ph_name
     * @tableColumnDatatype char254
     */
    private $strName = "";

    /**
     * @var string
     * @tableColumn page_element.page_element_ph_element
     * @tableColumnIndex
     * @tableColumnDatatype char254
     */
    private $strElement = "";

    /**
     * @var string
     * @tableColumn page_element.page_element_ph_title
     * @tableColumnDatatype char254
     */
    private $strTitle = "";

    /**
     * @var string
     * @tableColumn page_element.page_element_ph_language
     * @tableColumnIndex
     * @tableColumnDatatype char20
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
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName()
    {
        return $this->getStrReadableName()." (".$this->getStrName().")"." - ".$this->getStrTitle(true);
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon()
    {
        return "icon_page";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo()
    {
        return "";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription()
    {
        return "";
    }


    /**
     * Initialises the current object, if a systemid was given
     *
     * @return void
     */
    protected function initObjectInternal()
    {

        //maybe
        $arrRow = class_orm_rowcache::getCachedInitRow($this->getSystemid());
        if ($arrRow === null) {
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
        if (count($arrRow) > 1) {
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

            if ($arrRow["system_date_start"] > 0) {
                $this->intStartDate = $arrRow["system_date_start"];
            }

            if ($arrRow["system_date_end"] > 0) {
                $this->intEndDate = $arrRow["system_date_end"];
            }

        }
    }

    /**
     * saves the current object as a new object to the database
     *
     * @return bool
     */
    protected function onInsertToDb()
    {

        $objElementdefinitionToCreate = PagesElement::getElement($this->getStrElement());
        if ($objElementdefinitionToCreate != null) {

            $strFilename = \class_resourceloader::getInstance()->getPathForFile("/admin/elements/".$objElementdefinitionToCreate->getStrClassAdmin());
            $objInstance = \class_classloader::getInstance()->getInstanceFromFilename($strFilename, "Kajona\\Pages\\Admin\\ElementAdmin");

            //and finally create the object
            if ($objInstance != null) {

                $strForeignTable = $objInstance->getTable();

                //And create the row in the Element-Table, if given
                if ($strForeignTable != "") {
                    $strQuery = "INSERT INTO ".$this->objDB->encloseTableName($strForeignTable)." (".$this->objDB->encloseColumnName("content_id").") VALUES (?)";
                    $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
                }

            }
        }


        $this->objDB->flushQueryCache();


        return true;
    }

    /**
     * Creates an instance of the concrete admin-element instance, e.g. the concrete row-element
     * Please note, that due to performance issues the foreign content is not loaded in the step, use
     * $objElement->loadElementData() in order to fully initialize.
     *
     * @return ElementAdmin
     */
    public function getConcreteAdminInstance()
    {
        if ($this->getStrClassAdmin() == "") {
            //Build the class-name based on the linked element
            $objElementdefinitionToCreate = PagesElement::getElement($this->getStrElement());
            if ($objElementdefinitionToCreate == null) {
                return null;
            }
            $this->setStrClassAdmin($objElementdefinitionToCreate->getStrClassAdmin());
        }

        $strFilename = \class_resourceloader::getInstance()->getPathForFile("/admin/elements/".$this->getStrClassAdmin());
        $objInstance = \class_classloader::getInstance()->getInstanceFromFilename($strFilename, "Kajona\\Pages\\Admin\\ElementAdmin");

        //and finally create the object
        /** @var $objInstance ElementAdmin */
        $objInstance->setSystemid($this->getSystemid());
        return $objInstance;
    }

    /**
     * Creates an instance of the concrete admin-element instance, e.g. the concrete row-element
     * Please note, that due to performance issues the foreign content is not loaded in the step!
     *
     * @return ElementPortal
     */
    public function getConcretePortalInstance()
    {

        if ($this->getStrClassPortal() == "") {
            //Build the class-name based on the linked element
            $objElementdefinitionToCreate = PagesElement::getElement($this->getStrElement());
            if ($objElementdefinitionToCreate == null) {
                return null;
            }
            $this->setStrClassPortal($objElementdefinitionToCreate->getStrClassPortal());
        }


        $strFilename = \class_resourceloader::getInstance()->getPathForFile("/portal/elements/".$this->getStrClassPortal());
        $objInstance = \class_classloader::getInstance()->getInstanceFromFilename($strFilename, "Kajona\\Pages\\Portal\\ElementPortal", null, array($this));

        //and finally create the object
        /** @var $objInstance ElementPortal */
        $objInstance->setSystemid($this->getSystemid());
        return $objInstance;
    }


    /**
     * Makes a copy of the current element and saves it attached to the given page.
     * This copy includes the records in the elements' foreign tables
     *
     * @param string $strNewPage
     * @param bool $bitChangeTitle
     *
     * @throws class_exception
     * @return PagesPageelement the new element or null in case of an error
     */
    public function copyObject($strNewPage = "", $bitChangeTitle = true, $bitCopyChilds = true)
    {

        class_logger::getInstance()->addLogRow("copy pageelement ".$this->getSystemid(), class_logger::$levelInfo);
        $this->objDB->transactionBegin();

        //fetch all values to insert after the general copy process - mainly the foreign table
        $objElement = $this->getConcreteAdminInstance();
        $arrElementData = $objElement->loadElementData();

        //duplicate the current elements - afterwards $this is the new element
        parent::copyObject($strNewPage, $bitChangeTitle, $bitCopyChilds);

        //copy the old contents into the new elements
        $objElement = $this->getConcreteAdminInstance();
        $arrNewElementData = $objElement->loadElementData();
        $arrElementData["content_id"] = $arrNewElementData["content_id"];
        $arrElementData["page_element_id"] = $arrNewElementData["page_element_id"];
        $arrElementData["system_id"] = $arrNewElementData["system_id"];
        $objElement->setArrParamData($arrElementData);

        //try to find setters to inject the values
        $objAnnotation = new class_reflection($objElement);
        $arrMappedProperties = $objAnnotation->getPropertiesWithAnnotation(class_orm_base::STR_ANNOTATION_TABLECOLUMN);

        foreach ($arrElementData as $strColumn => $strValue) {
            foreach ($arrMappedProperties as $strPropertyname => $strAnnotation) {
                $strMappedColumn = uniSubstr($strAnnotation, uniStrpos($strAnnotation, ".") + 1);
                if ($strColumn == $strMappedColumn) {
                    $objSetter = $objAnnotation->getSetter($strPropertyname);
                    if ($objSetter != null) {
                        call_user_func_array(array($objElement, $objSetter), array($strValue));
                    }
                }
            }
        }

        $objElement->doBeforeSaveToDb();
        $objElement->updateForeignElement();
        $objElement->doAfterSaveToDb();

        $this->objDB->transactionCommit();

        return $this;
    }


    /**
     * Loads all Elements on the given page known by the system, so db-sided, not template-sided.
     * Returns the list of objects
     *
     * @param string $strPageId
     * @param bool $bitJustActive
     * @param string $strLanguage
     *
     * @return PagesPageelement[]
     * @static
     */
    public static function getElementsOnPage($strPageId, $bitJustActive = false, $strLanguage = "")
    {

        //since there's the time as an parameter, there's no need for querying the cache...
        $arrIds = self::getPlainElementsOnPage($strPageId, $bitJustActive, $strLanguage);

        $arrReturn = array();
        foreach ($arrIds as $arrOneId) {
            $arrReturn[] = new PagesPageelement($arrOneId["system_id"]);
        }

        return $arrReturn;
    }

    /**
     * Loads the list of elements on a single page.
     * Returns an array of plain data, not the corresponding objects.
     * In most cases getElementsOnPage is the right way to go.
     *
     *
     * @param string $strPageId
     * @param bool $bitJustActive
     * @param string $strLanguage
     *
     * @see PagesPageelement::getElementsOnPage()
     * @return array
     */
    public static function getPlainElementsOnPage($strPageId, $bitJustActive = false, $strLanguage = "")
    {
        //Calculate the current day as a time-stamp. This improves database-caches e.g. the kajona or mysql-query-cache.
        $objDate = new class_date();
        $objDate->setIntMin(0, true);
        $objDate->setIntSec(0, true);
        $objDate->setIntHour(0, true);

        $longToday = $objDate->getLongTimestamp();
        $arrParams = array($strPageId, $strLanguage);
        $objORM = new class_orm_objectlist();

        $strAnd = "";
        if ($bitJustActive) {
            $strAnd = "AND system_status = 1
                       AND ( system_date_start IS null OR (system_date_start = 0 OR system_date_start <= ?))
                       AND ( system_date_end IS null OR (system_date_end = 0 OR system_date_end >= ?)) ";

            $arrParams[] = $longToday;
            $arrParams[] = $longToday;
        }

        $strQuery = "SELECT *
                       FROM "._dbprefix_."page_element,
                            "._dbprefix_."element,
                            "._dbprefix_."system_right,
                            "._dbprefix_."system as system
                  LEFT JOIN "._dbprefix_."system_date
                         ON (system_id = system_date_id)
                      WHERE system_prev_id= ?
                        AND page_element_ph_element = element_name
                        AND system_id = page_element_id
                        AND system_id = right_id
                        AND page_element_ph_language = ?
                       ".$strAnd."
                       ".$objORM->getDeletedWhereRestriction()."
                  ORDER BY page_element_ph_placeholder ASC,
                           page_element_ph_language ASC,
                           system_sort ASC";

        $arrReturn = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);

        foreach ($arrReturn as $arrOneRow) {
            class_orm_rowcache::addSingleInitRow($arrOneRow);
        }

        return $arrReturn;
    }


    /**
     * Loads all Elements on the given ignoring both, status and language
     *
     * @param string $strPageId
     *
     * @return PagesPageelement[]
     * @static
     */
    public static function getAllElementsOnPage($strPageId)
    {
        $objORM = new class_orm_objectlist();
        $strQuery = "SELECT *
						 FROM "._dbprefix_."page_element,
						      "._dbprefix_."element,
						      "._dbprefix_."system_right,
						      "._dbprefix_."system
					 LEFT JOIN "._dbprefix_."system_date
                            ON system_id = system_date_id
						 WHERE system_prev_id=?
						   AND system_id = right_id
						   AND page_element_ph_element = element_name
						   AND system_id = page_element_id
						   ".$objORM->getDeletedWhereRestriction()."
				      ORDER BY page_element_ph_placeholder ASC,
				               page_element_ph_language ASC,
						 	   system_sort ASC";

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strPageId));
        class_orm_rowcache::addArrayOfInitRows($arrIds);
        $arrReturn = array();
        foreach ($arrIds as $arrOneId) {
            $arrReturn[] = class_objectfactory::getInstance()->getObject($arrOneId["system_id"]);
        }

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
     * @return PagesPageelement[]
     */
    public static function getElementsByPlaceholderAndPage($strPageId, $strPlaceholder, $strLanguage, $bitJustActive = true)
    {
        $strAnd = "";

        $arrParams = array($strPageId, $strLanguage, $strPlaceholder);

        if ($bitJustActive) {
            $strAnd = "AND system_status = 1
                       AND ( system_date_start IS null OR (system_date_start = 0 OR system_date_start <= ?))
                       AND ( system_date_end IS null OR (system_date_end = 0 OR system_date_end >= ? )) ";

            $arrParams[] = time();
            $arrParams[] = time();
        }

        $objORM = new class_orm_objectlist();
        $strQuery = "SELECT *
                         FROM "._dbprefix_."page_element,
                              "._dbprefix_."element,
                              "._dbprefix_."system_right,
                              "._dbprefix_."system
                     LEFT JOIN "._dbprefix_."system_date
                            ON (system_id = system_date_id)
                         WHERE system_prev_id= ?
                           AND page_element_ph_element = element_name
                           AND system_id = right_id
                           AND system_id = page_element_id
                           AND page_element_ph_language = ?
                           AND page_element_ph_placeholder = ?
                           ".$strAnd."
                           ".$objORM->getDeletedWhereRestriction()."
                         ORDER BY system_sort ASC";

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);
        class_orm_rowcache::addArrayOfInitRows($arrIds);
        $arrReturn = array();
        foreach ($arrIds as $arrOneRow) {
            $arrReturn[] = class_objectfactory::getInstance()->getObject($arrOneRow["system_id"]);
        }

        return $arrReturn;

    }

    /**
     * Helper, loads all elements registered at a single placeholder for the same language,
     * By default this method ignores the db-cache.
     *
     * @return array
     */
    public function getSortedElementsAtPlaceholder()
    {

        $objORM = new class_orm_objectlist();
        $strQuery = "SELECT *
						 FROM "._dbprefix_."page_element,
						      "._dbprefix_."element,
						      "._dbprefix_."system
						 WHERE system_prev_id= ?
						   AND page_element_ph_element = element_name
                           AND page_element_ph_language = ?
                           AND page_element_ph_placeholder = ?
						   AND system_id = page_element_id
						   ".$objORM->getDeletedWhereRestriction()."
						 ORDER BY system_sort ASC";

        $arrElementsOnPage = $this->objDB->getPArray($strQuery, array($this->getPrevId(), $this->getStrLanguage(), $this->getStrPlaceholder()), null, null, false);
        return $arrElementsOnPage;
    }


    /**
     * Deletes the element from the system-tables, also from the foreign-element-tables.
     * This takes care of reordering the internal sort-ids.
     *
     * @return bool
     */
    public function deleteObjectFromDatabase()
    {

        //fix the internal sorting
        $arrElements = $this->getSortedElementsAtPlaceholder();

        $arrIds = array();
        $bitHit = false;
        foreach ($arrElements as $arrOneSibling) {

            if ($bitHit) {
                $arrIds[] = $arrOneSibling["system_id"];
            }

            if ($arrOneSibling["system_id"] == $this->getSystemid()) {
                $bitHit = true;
            }
        }

        if (count($arrIds) > 0) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_sort = system_sort-1 where system_id IN (".implode(",", array_map(function ($strVal) {
                    return "?";
                }, $arrIds)).")";
            $this->objDB->_pQuery($strQuery, $arrIds);
        }


        //Load the Element-Data
        $objElement = $this->getConcreteAdminInstance();
        if ($objElement != null) {
            //Fetch the table
            $strElementTable = $objElement->getTable();
            //Delete the entry in the Element-Table
            if ($strElementTable != "") {
                $strQuery = "DELETE FROM ".$strElementTable." WHERE content_id= ?";
                if (!$this->objDB->_pQuery($strQuery, array($this->getSystemid()))) {
                    return false;
                }
            }
        }

        //Delete from page_element table
        parent::deleteObjectFromDatabase();

        //Loading the data of the corresponding site
        $objPage = new PagesPage($this->getPrevId());
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
    public static function assignNullElements($strTargetLanguage)
    {
        //Load all non-assigned props
        $strQuery = "SELECT page_element_id FROM "._dbprefix_."page_element
                     WHERE page_element_ph_language = '' OR page_element_ph_language IS NULL";
        $arrElementIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());

        foreach ($arrElementIds as $arrOneId) {
            $strId = $arrOneId["page_element_id"];
            $strUpdate = "UPDATE "._dbprefix_."page_element
                          SET page_element_ph_language = ?
                          WHERE page_element_id = ?";

            if (!class_carrier::getInstance()->getObjDB()->_pQuery($strUpdate, array($strTargetLanguage, $strId))) {
                return false;
            }

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
    public static function updatePlaceholders($strTemplate, $strOldPlaceholder, $strNewPlaceholder)
    {
        $bitReturn = true;
        //Fetch all pages
        $objORM = new class_orm_objectlist();
        $arrObjPages = PagesPage::getAllPages();
        foreach ($arrObjPages as $objOnePage) {
            if ($objOnePage->getStrTemplate() == $strTemplate || $strTemplate == "-1") {
                //Search for matching elements
                $strQuery = "SELECT system_id
						 FROM "._dbprefix_."page_element,
						      "._dbprefix_."element,
						      "._dbprefix_."system
						 WHERE system_prev_id= ?
						   AND page_element_ph_element = element_name
						   AND system_id = page_element_id
						   ".$objORM->getDeletedWhereRestriction()."
						 ORDER BY page_element_ph_placeholder ASC,
						 		system_sort ASC";

                $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($objOnePage->getSystemid()));
                /** @var PagesPageelement[] $arrPageElements */
                $arrPageElements = array();
                foreach ($arrIds as $arrOneRow) {
                    $arrPageElements[] = new PagesPageelement($arrOneRow["system_id"]);
                }

                foreach ($arrPageElements as $objOnePageelement) {
                    if ($objOnePageelement->getStrPlaceholder() == $strOldPlaceholder) {
                        $objOnePageelement->setStrPlaceholder($strNewPlaceholder);
                        if (!$objOnePageelement->updateObjectToDb()) {
                            $bitReturn = false;
                        }
                    }
                }
            }
        }
        return $bitReturn;
    }


    /**
     * @return string
     */
    public function getStrPlaceholder()
    {
        return $this->strPlaceholder;
    }

    /**
     * @return string
     */
    public function getStrName()
    {
        return $this->strName;
    }

    /**
     * @return string
     */
    public function getStrElement()
    {
        return $this->strElement;
    }

    /**
     * Returns a readable representation of the current elements' name.
     * Searches the lang-file for an entry element_NAME_name.
     *
     * @return string
     */
    public function getStrReadableName()
    {
        $strName = class_carrier::getInstance()->getObjLang()->getLang("element_".$this->getStrElement()."_name", "elements");
        if ($strName == "!element_".$this->getStrElement()."_name!") {
            $strName = $this->getStrElement();
        }
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
    public function getStrTitle($bitClever = false)
    {
        if ($this->strTitle != "" || !$bitClever || $this->getStrClassAdmin() == "") {
            return $this->strTitle;
        }
        //Create an instance of the object and let it serve the comment...
        $objElement = $this->getConcreteAdminInstance();
        return $objElement->getContentTitle();
    }

    /**
     * @return string
     */
    public function getStrClassPortal()
    {
        return $this->strClassPortal;
    }

    /**
     * @return string
     */
    public function getStrClassAdmin()
    {
        return $this->strClassAdmin;
    }

    /**
     * @return int
     */
    public function getIntCachetime()
    {
        return $this->intCachetime;
    }

    /**
     * @return int
     */
    public function getIntRepeat()
    {
        return $this->intRepeat;
    }

    /**
     * @return string
     */
    public function getStrLanguage()
    {
        return $this->strLanguage;
    }

    /**
     * @return int
     */
    public function getStartDate()
    {
        return $this->intStartDate;
    }

    /**
     * @return int
     */
    public function getEndDate()
    {
        return $this->intEndDate;
    }

    /**
     * @param string $strPlaceholder
     *
     * @return void
     */
    public function setStrPlaceholder($strPlaceholder)
    {
        $this->strPlaceholder = $strPlaceholder;
    }

    /**
     * @param string $strName
     *
     * @return void
     */
    public function setStrName($strName)
    {
        $this->strName = $strName;
    }

    /**
     * @param string $strElement
     *
     * @return void
     */
    public function setStrElement($strElement)
    {
        $this->strElement = $strElement;
    }

    /**
     * @param string $strTitle
     *
     * @return void
     */
    public function setStrTitle($strTitle)
    {
        $this->strTitle = $strTitle;
    }

    /**
     * @param string $strClassPortal
     *
     * @return void
     */
    private function setStrClassPortal($strClassPortal)
    {
        $this->strClassPortal = $strClassPortal;
    }

    /**
     * @param string $strClassAdmin
     *
     * @return void
     */
    private function setStrClassAdmin($strClassAdmin)
    {
        $this->strClassAdmin = $strClassAdmin;
    }

    /**
     * @param int $intCachtime
     *
     * @return void
     */
    private function setIntCachetime($intCachtime)
    {
        $this->intCachetime = $intCachtime;
    }

    /**
     * @param int $intRepeat
     *
     * @return void
     */
    private function setIntRepeat($intRepeat)
    {
        $this->intRepeat = $intRepeat;
    }

    /**
     * @param string $strLanguage
     *
     * @return void
     */
    public function setStrLanguage($strLanguage)
    {
        $this->strLanguage = $strLanguage;
    }

    /**
     * @param int $intConfigVal1
     *
     * @return void
     */
    public function setStrConfigVal1($intConfigVal1)
    {
        $this->strConfigVal1 = $intConfigVal1;
    }

    /**
     * @return string
     */
    public function getStrConfigVal1()
    {
        return $this->strConfigVal1;
    }

    /**
     * @param string $intConfigVal2
     *
     * @return void
     */
    public function setStrConfigVal2($intConfigVal2)
    {
        $this->strConfigVal2 = $intConfigVal2;
    }

    /**
     * @return string
     */
    public function getStrConfigVal2()
    {
        return $this->strConfigVal2;
    }

    /**
     * @param string $intConfigVal3
     *
     * @return void
     */
    public function setStrConfigVal3($intConfigVal3)
    {
        $this->strConfigVal3 = $intConfigVal3;
    }

    /**
     * @return string
     */
    public function getStrConfigVal3()
    {
        return $this->strConfigVal3;
    }

}
