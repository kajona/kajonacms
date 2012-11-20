<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                            *
********************************************************************************************************/

/**
 * Model for a element assigned to a page. NOT the raw-element!
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 *
 * @targetTable page_element.page_element_id
 */
class class_module_pages_pageelement extends class_model implements interface_model, interface_admin_listable  {

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

		parent::__construct( $strSystemid);
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrName() . " (".$this->getStrReadableName() . ")" ." - ". $this->getStrTitle(true);
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        return "icon_page.png";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     * @return string
     */
    public function getStrAdditionalInfo() {
        return "";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }


    /**
     * Initalises the current object, if a systemid was given
     *
     */
    protected function initObjectInternal() {
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
        $this->setIntSort(count($this->getSortedElementsAtPlaceholder())+1);
        $this->objDB->_pQuery($strQuery, array(count($this->getSortedElementsAtPlaceholder())+1 , $this->getSystemid() ));
        //And shift this element one pos up to get correct order on systemtables

        $this->objDB->flushQueryCache();



        return true;
    }


    /**
     * Makes a copy of the current element and saves it attached to the given page.
     * This copy includes the records in the elements' foreign tables
     *
     * @param string $strNewPage
     * @return class_module_pages_pageelement the new element or null in case of an error
     */
    public function copyObject($strNewPage = "") {

        class_logger::getInstance()->addLogRow("copy pageelement ".$this->getSystemid(), class_logger::$levelInfo);

        $this->objDB->transactionBegin();

        //fetch all values to insert after the general copy process - mainly the foreign table
        $strElementClass = str_replace(".php", "", $this->getStrClassAdmin());
        //and finally create the object
        /** @var $objElement class_element_admin */
        $objElement = new $strElementClass();
        $objElement->setSystemid($this->getSystemid());
        $arrElementData = $objElement->loadElementData();


        //duplicate the current elements - afterwards $this is the new element
        parent::copyObject($strNewPage);

        $objElement = new $strElementClass();
        $objElement->setSystemid($this->getSystemid());
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
                            "._dbprefix_."element,
                            "._dbprefix_."system
                  LEFT JOIN "._dbprefix_."system_date
                         ON (system_id = system_date_id)
                      WHERE system_prev_id= ?
                        AND page_element_ph_element = element_name
                        AND system_id = page_element_id
                        AND page_element_ph_language = ?
                       " . $strAnd."
                  ORDER BY page_element_ph_placeholder ASC,
                            system_sort ASC";

        return class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);
    }



    /**
     * Loads all Elements on the given ignoring both, status and language
     *
     * @param string $strPageId
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
     * If no matchin element was found, null is returned.
     *
     * @param string $strPageId
     * @param string $strPlaceholder
     * @param string $strLanguage
     * @param bool $bitJustActive
     * @return class_module_pages_pageelement or NULL of no element was found.
     */
    public static function getElementByPlaceholderAndPage($strPageId, $strPlaceholder, $strLanguage, $bitJustActive = true) {
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
                           " . $strAnd."
                         ORDER BY page_element_ph_placeholder ASC,
                                system_sort ASC";

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);

        if(count($arrIds) == 1) {
            return (new class_module_pages_pageelement($arrIds[0]["system_id"]));
        }
        else {
        	return null;
        }

    }

    /**
     * Helper, loads all elements registered at a single placeholder,
     * By default this method ignors the db-cache.
     *
     * @return array
     */
    private function getSortedElementsAtPlaceholder() {

        $strQuery = "SELECT *
						 FROM "._dbprefix_."page_element,
						      "._dbprefix_."element,
						      "._dbprefix_."system
						 WHERE system_prev_id= ?
						   AND page_element_ph_element = element_name
                           AND page_element_ph_language = ?
						   AND system_id = page_element_id
						 ORDER BY page_element_ph_placeholder ASC,
						 		system_sort ASC";

		$arrElementsOnPage = $this->objDB->getPArray($strQuery, array( $this->getPrevId(), $this->getStrLanguage() ), null, null, false);

		//Iterate over all elements to sort out
		$arrElementsOnPlaceholder = array();
		foreach($arrElementsOnPage as $arrOneElementOnPage) {
			if($this->getStrPlaceholder() == $arrOneElementOnPage["page_element_ph_placeholder"])
				$arrElementsOnPlaceholder[] = $arrOneElementOnPage;
		}

        return $arrElementsOnPlaceholder;
    }


    /**
     * Sets the position of an element using a given value.
     *
     * @param int $intNewPosition
     * @param bool $bitOnlySameModule
     *
     * @return void
     * @see class_root::setAbsolutePosition($strIdToSet, $intPosition)
     */
    public function setAbsolutePosition($intNewPosition, $bitOnlySameModule = false) {
        class_logger::getInstance()->addLogRow("move ".$this->getSystemid()." to new pos ".$intNewPosition, class_logger::$levelInfo);
        $this->objDB->flushQueryCache();

        //No caching here to allow multiple shiftings per request
        $arrElements = $this->getSortedElementsAtPlaceholder();

        //more than one record to set?
        if(count($arrElements) <= 1)
            return;

        //senseless new pos?
        if($intNewPosition <= 0 || $intNewPosition > count($arrElements))
            return;

        $intCurPos = $this->getIntSort();

        if($intNewPosition == $intCurPos)
            return;


        //searching the current element to get to know if element should be sorted up- or downwards
        $bitSortDown = false;
        $bitSortUp = false;
        if($intNewPosition < $intCurPos)
            $bitSortUp = true;
        else
            $bitSortDown = true;


        //sort up?
        if($bitSortUp) {
            //move the record to be shifted to the wanted pos
            $strQuery = "UPDATE "._dbprefix_."system
								SET system_sort=?
								WHERE system_id=?";
            $this->objDB->_pQuery($strQuery, array(((int)$intNewPosition), $this->getSystemid()));

            //start at the pos to be reached and move all one down
            for($intI = $intNewPosition; $intI < $intCurPos; $intI++) {

                $strQuery = "UPDATE "._dbprefix_."system
                            SET system_sort=?
                            WHERE system_id=?";
                $this->objDB->_pQuery($strQuery, array($intI+1, $arrElements[$intI-1]["system_id"]));
            }
        }

        if($bitSortDown) {
            //move the record to be shifted to the wanted pos
            $strQuery = "UPDATE "._dbprefix_."system
								SET system_sort=?
								WHERE system_id=?";
            $this->objDB->_pQuery($strQuery, array(((int)$intNewPosition), $this->getSystemid()));

            //start at the pos to be reached and move all one up
            for($intI = $intCurPos+1; $intI <= $intNewPosition; $intI++) {

                $strQuery = "UPDATE "._dbprefix_."system
                            SET system_sort= ?
                            WHERE system_id=?";
                $this->objDB->_pQuery($strQuery, array($intI-1, $arrElements[$intI-1]["system_id"]));
            }
        }

        //flush the cache
        $this->flushCompletePagesCache();
        $this->objDB->flushQueryCache();
        $this->setIntSort($intNewPosition);
    }

    /**
     * Shifts an element up or down
     * This is a special implementation, because we don't have the usual system_prev_id relations.
     * Creates an initial sorting.
     * Note: Could be optimized!
     *
     * @param string $strMode up || down
     *
     * @return void
     * @see class_root::setPosition($strDirection = "upwards")
     * @deprecated
     */
	public function setPosition($strMode = "up") {

        $arrElementsOnPlaceholder = $this->getSortedElementsAtPlaceholder();

        for($intI = 1; $intI <= count($arrElementsOnPlaceholder); $intI++) {
            if($arrElementsOnPlaceholder[$intI-1]["system_id"] == $this->getSystemid()) {
                if($strMode == "up")
                    $this->setAbsolutePosition($intI-1);
                else
                    $this->setAbsolutePosition($intI+1);

                break;
            }
        }
	}



	/**
	 * Deletes the element from the system-tables, also from the foreign-element-tables
	 *
	 * @return bool
	 */
    protected function deleteObjectInternal() {
	    //Load the Element-Data
		//Build the class-name
		$strElementClass = str_replace(".php", "", $this->getStrClassAdmin());

        if($strElementClass != "") {
            //and finally create the object
            $objElement = new $strElementClass();
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
	 * @return bool
	 */
	public static function assignNullElements($strTargetLanguage) {
        //Load all non-assigned props
        $strQuery = "SELECT page_element_id FROM "._dbprefix_."page_element
                     WHERE page_element_ph_language = '' OR page_element_ph_language IS NULL";
        $arrElementIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());

        foreach ($arrElementIds as $arrOneId) {
            $strId = $arrOneId["page_element_id"];
            $strUpdate = "UPDATE "._dbprefix_."page_element
                          SET page_element_ph_language = ?
                          WHERE page_element_id = ?";

            if(!class_carrier::getInstance()->getObjDB()->_pQuery($strUpdate, array( $strTargetLanguage, $strId ) ))
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

		        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array( $objOnePage->getSystemid() ) );
		        $arrPageElements = array();
		        foreach ($arrIds as $arrOneRow) {
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
        $strName = class_carrier::getInstance()->getObjLang()->getLang("element_".$this->getStrElement()."_name", "elemente");
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
     * @return string
     */
    public function getStrTitle($bitClever = false) {
        if($this->strTitle != "" || !$bitClever || $this->getStrClassAdmin() == "")
            return $this->strTitle;
        //Create an instance of the object and let it serve the comment...
        $strClassname = str_replace(".php", "", $this->getStrClassAdmin());
        $objElement = new $strClassname();
        $objElement->setSystemid($this->getSystemid());
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
