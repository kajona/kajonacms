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
 */
class class_module_pages_pageelement extends class_model implements interface_model, interface_admin_listable  {

    private $strPlaceholder = "";
    private $strName = "";
    private $strElement = "";
    private $strTitle = "";
    private $strLanguage = "";

    private $strClassAdmin = "";
    private $strClassPortal = "";
    private $intCachetime = -1;
    private $intRepeat = 0;


    private $intStartDate = 0;
    private $intEndDate = 0;

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
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."page_element" => "page_element_id");
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrName() . " (".$this->getStrReadableName() . ")" ." - ". $this->getStrTitle();
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        return "";
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
						      "._dbprefix_."system
						  LEFT JOIN "._dbprefix_."system_date
						    ON (system_id = system_date_id)
						 WHERE system_id= ?
						   AND page_element_ph_element = element_name
						   AND system_id = page_element_id
						 ORDER BY page_element_ph_placeholder ASC,
						 		system_sort ASC";
		$arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));
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

        //shift it to the first position by default
        //As a special feature, we set the element as the last
        $strQuery = "UPDATE "._dbprefix_."system SET system_sort = ? WHERE system_id = ?";
        $this->objDB->_pQuery($strQuery, array(count($this->getSortedElementsAtPlaceholder()) , $this->getSystemid() ));
        //And shift this element one pos up to get correct order on systemtables

        $this->objDB->flushQueryCache();



        return true;
    }


    /**
     * Updates the current object to the database
     * currently just updateing the internal title and the language
     *
     * @return bool
     */
    protected function updateStateToDb() {
        $strQuery = "UPDATE "._dbprefix_."page_element
							SET page_element_ph_title = ?,
							    page_element_ph_language = ?,
							    page_element_ph_placeholder = ?,
							    page_element_ph_name = ?,
							    page_element_ph_element = ?
							WHERE page_element_id = ? ";
        $bitReturn =  $this->objDB->_pQuery($strQuery, array( $this->strTitle, $this->getStrLanguage(), $this->getStrPlaceholder(), $this->getStrName(), $this->getStrElement(), $this->getSystemid()  ));
        return $bitReturn;
    }

    /**
     * Makes a copy of the current element and saves it attached to the given page.
     * This copy includes the records in the elements' foreign tables
     *
     * @param string $strNewPage
     * @return class_module_pages_pageelement the new element or null in case of an error
     */
    public function copyElementToPage($strNewPage) {
        class_logger::getInstance()->addLogRow("copy pageelement ".$this->getSystemid(), class_logger::$levelInfo);
        $this->objDB->transactionBegin();

        $strIdOfNewPageelement = generateSystemid();

        //working directly on the db is much easier right here!
        //start by making a copy of the sysrecords, attaching them to the new page
        $objCommon = new class_module_system_common($this->getSystemid());
        $objCommon->copyCurrentSystemrecord($strIdOfNewPageelement, $strNewPage);


        //fetch data of the current element
        $arrCurrentElement = $this->objDB->getPRow("SELECT * FROM "._dbprefix_."page_element WHERE page_element_id = ?", array( $this->getSystemid() ));

        //save data as foreign data of the new record
        $strQuery = "INSERT INTO "._dbprefix_."page_element
        			(page_element_id, page_element_ph_placeholder, page_element_ph_name, page_element_ph_element, page_element_ph_title, page_element_ph_language) VALUES
        			( ?, ?, ?, ?, ?, ?)";

        if(!$this->objDB->_pQuery($strQuery, array( $strIdOfNewPageelement,
                                                    $arrCurrentElement["page_element_ph_placeholder"],
                                                    $arrCurrentElement["page_element_ph_name"],
                                                    $arrCurrentElement["page_element_ph_element"],
                                                    $arrCurrentElement["page_element_ph_title"],
                                                    $arrCurrentElement["page_element_ph_language"]))) {
            $this->objDB->transactionRollback();
            return null;
        }

        //now the tricky part - the elements content-table...
        //get elements table-name
		$strElementClass = str_replace(".php", "", $this->getStrClassAdmin());
		$objElement = new $strElementClass();
		//Fetch the table
		$strElementTable = $objElement->getTable();

		//just copy, if a table was given
		if($strElementTable != "") {
			//load the old row
			$arrContentRow = $this->objDB->getPRow("SELECT * FROM ".$strElementTable." WHERE content_id = ? ", array($this->getSystemid()) );

			//load the Columns of the table
			$arrColumns = $this->objDB->getColumnsOfTable($strElementTable);

			//build the new insert
			$strQuery = "INSERT INTO ".$strElementTable." ( ";
            $arrValues = array();
            $arrEscapes = array();
			foreach ($arrColumns as $arrOneColumn)
	            $strQuery .= " ".$this->objDB->encloseColumnName($arrOneColumn["columnName"]).",";

	        //remove last comma
	        $strQuery = uniSubstr($strQuery, 0, -1);
	        $strQuery .= ") VALUES ( ";
	        foreach ($arrColumns as $arrOneColumn) {
	            if($arrOneColumn["columnName"] == "content_id") {
	                $strQuery .= " ?,";
                    $arrValues[] = $strIdOfNewPageelement;
                    $arrEscapes[] = true;
	            }
	            else if(strpos($arrOneColumn["columnType"], "int") !== false) {
	                $intValue = $arrContentRow[$arrOneColumn["columnName"]];
	                if($intValue == "")
	                    $intValue = "NULL";
	                $strQuery .= " ?,";
                    $arrValues[] = $intValue;
                    $arrEscapes[] = true;
	            }
	            else {
	            	//no dbsafestring here, otherwise contents may be double-encoded...
	                $strQuery .= " ?,";
                    $arrValues[] = $arrContentRow[$arrOneColumn["columnName"]];
                    $arrEscapes[] = false;
	            }
	        }
	        $strQuery = uniSubstr($strQuery, 0, -1);
	        $strQuery .= ")";

	        if(!$this->objDB->_pQuery($strQuery, $arrValues, $arrEscapes)) {
	            $this->objDB->transactionRollback();
	            return null;
	        }
		}

        //ok, all done. return.
        $this->objDB->transactionCommit();

        //create an instance of the new element and return it
        $objNewElement = new class_module_pages_pageelement($strIdOfNewPageelement);

        //adopt the new sort id, since page-elements have a special order at each placeholder
        //As a special feature, we set the element as the last
        $strQuery = "UPDATE "._dbprefix_."system SET system_sort = ? WHERE system_id = ?";
        $this->objDB->flushQueryCache();
        $this->objDB->_pQuery($strQuery, array(count($objNewElement->getSortedElementsAtPlaceholder())-1 , $strIdOfNewPageelement ));


        return $objNewElement;
    }


    /**
     * Loads all Elements on the given page known by the system, so db-sided, not template-sided
     *
     * @param string $strPageId
     * @param bool $bitJustActive
     * @param string $strLanguage
     * @return class_module_pages_pageelement[]
     * @static
     */
    public static function getElementsOnPage($strPageId, $bitJustActive = false, $strLanguage = "") {

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
						   " . $strAnd."
						 ORDER BY page_element_ph_placeholder ASC,
						 		system_sort ASC";

        //since theres the time as an parameter, theres no need for querying the cache...
		$arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, false);

		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_module_pages_pageelement($arrOneId["system_id"]);

		return $arrReturn;
    }



	/**
     * Loads all Elements on the given ignoring both, status and language
     *
     * @param string $strPageId
     * @return mixed
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
     * Helper, loads all elements registered at a single placeholder
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

		$arrElementsOnPage = $this->objDB->getPArray($strQuery, array( $this->getPrevId(), $this->getStrLanguage() ), false);

		//Iterate over all elements to sort out
		$arrElementsOnPlaceholder = array();
		foreach($arrElementsOnPage as $strKey => $arrOneElementOnPage) {
			if($this->getStrPlaceholder() == $arrOneElementOnPage["page_element_ph_placeholder"])
				$arrElementsOnPlaceholder[] = $arrOneElementOnPage;
		}

        return $arrElementsOnPlaceholder;
    }


    /**
     * Sets the position of an element using a given value.
     *
     * @param $strIdToSet
     * @param $intPosition
     * @see class_root::setAbsolutePosition($strIdToSet, $intPosition)
     */
    public function setAbsolutePosition($strIdToSet, $intPosition) {
        class_logger::getInstance()->addLogRow("move element ".$this->getSystemid()." to new pos ".$intPosition, class_logger::$levelInfo);

		//to have a better array-like handling, decrease pos by one.
		//remind to add at the end when saving to db
		$intPosition--;

		//No caching here to allow mutliple shiftings per request
		$arrElements = $this->getSortedElementsAtPlaceholder();

		//more than one record to set?
		if(count($arrElements) <= 1)
			return;

		//senseless new pos?
		if($intPosition < 0 || $intPosition >= count($arrElements))
		    return;


		//searching the current element to get to know if element should be
		//sorted up- or downwards
		$bitSortDown = false;
		$bitSortUp = false;
		$intHitKey = 0;
		for($intI = 0; $intI < count($arrElements); $intI++) {
			if($arrElements[$intI]["system_id"] == $this->getSystemid()) {
				if($intI < $intPosition)
					$bitSortDown = true;
				if($intI >= $intPosition+1)
					$bitSortUp = true;

				$intHitKey = $intI;
			}
		}

		//sort up?
		if($bitSortUp) {
			//move the record to be shifted to the wanted pos
			$strQuery = "UPDATE "._dbprefix_."system
								SET system_sort= ?
								WHERE system_id= ? ";
			$this->objDB->_pQuery($strQuery, array((int)$intPosition, $this->getSystemid() ) );

			//start at the pos to be reached and move all one down
			for($intI = 0; $intI < count($arrElements); $intI++) {
				//move all other one pos down, except the last in the interval:
				//already moved...
				if($intI >= $intPosition && $intI < $intHitKey) {
					$strQuery = "UPDATE "._dbprefix_."system
								SET system_sort=system_sort+1
								WHERE system_id= ?";
					$this->objDB->_pQuery($strQuery, array($arrElements[$intI]["system_id"]));
				}
			}
		}

		if($bitSortDown) {
			//move the record to be shifted to the wanted pos
			$strQuery = "UPDATE "._dbprefix_."system
								SET system_sort= ?
								WHERE system_id= ?";
			$this->objDB->_pQuery($strQuery, array((int)$intPosition, $this->getSystemid()));

			//start at the pos to be reached and move all one down
			for($intI = 0; $intI < count($arrElements); $intI++) {
				//move all other one pos down, except the last in the interval:
				//already moved...
				if($intI > $intHitKey && $intI <= $intPosition) {
					$strQuery = "UPDATE "._dbprefix_."system
								SET system_sort=system_sort-1
								WHERE system_id= ?";
					$this->objDB->_pQuery($strQuery, array($arrElements[$intI]["system_id"]));
				}
			}
		}


    }

    /**
	 * Shifts an element up or down
	 * This is a special implementation, because we don't have the usual system_prev_id relations.
     * Creates an initial sorting.
	 * Note: Could be optimized!
	 *
     * @param string  $strIdToShift  no effect
	 * @param string $strMode up || down
	 * @return string "" in case of success
     * @see class_root::setPosition($strIdToShift, $strDirection = "upwards")
	 */
	public function setPosition($strIdToShift, $strMode = "up") {
        class_logger::getInstance()->addLogRow("move element ".$this->getSystemid()." to new direction ".$strMode, class_logger::$levelInfo);
		$strReturn = "";
		//Iterate over all elements to sort out
		$arrElementsOnPlaceholder = $this->getSortedElementsAtPlaceholder();


		//Iterate again to move the element
		$arrElementsSorted = array();
		$bitSaveToDb = false;
		for($intI = 0; $intI < count($arrElementsOnPlaceholder); $intI++) {
			if($arrElementsOnPlaceholder[$intI]["system_id"] == $this->getSystemid()) {
				//Shift the elements around
				if($strMode == "up") {
					//Valid action requested?
					if($intI != 0) {
						//Shift it one position up
						$arrTemp = $arrElementsOnPlaceholder[$intI-1];
						$arrElementsOnPlaceholder[$intI-1] = $arrElementsOnPlaceholder[$intI];
						$arrElementsOnPlaceholder[$intI] = $arrTemp;
						$bitSaveToDb = true;
						break;
					}
				}
				elseif ($strMode == "down") {
					//Valid Action requested
					if($intI != (count($arrElementsOnPlaceholder)-1)) {
						//Shift it one position down
						$arrTemp = $arrElementsOnPlaceholder[$intI+1];
						$arrElementsOnPlaceholder[$intI+1] = $arrElementsOnPlaceholder[$intI];
						$arrElementsOnPlaceholder[$intI] = $arrTemp;
						$bitSaveToDb = true;
						break;
					}
				}
			}
		}
		//Do we have to save to the db?
		if($bitSaveToDb) {
			foreach($arrElementsOnPlaceholder as $intKey => $arrOneElementOnPlaceholder) {
				//$intKey+1 forces new elements to be at the top of lists
				$strQuery = "UPDATE "._dbprefix_."system
								SET system_sort= ?
								WHERE system_id= ?";
				$this->objDB->_pQuery($strQuery, array( (int)$intKey, $arrOneElementOnPlaceholder["system_id"] ));
			}
		}

		//Loading the data of the corresp site
        $this->objDB->flushQueryCache();
		$objPage = new class_module_pages_page($this->getPrevId());
        class_cache::flushCache("class_element_portal", $objPage->getStrName());

		return $strReturn;
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
		$strQuery = "DELETE FROM "._dbprefix_."page_element WHERE page_element_id= ?";
		if(!$this->objDB->_pQuery($strQuery, array($this->getSystemid())))
			return false;

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

    // --- GETTERS / SETTERS --------------------------------------------------------------------------------

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
    public function getStrTitle($bitClever = true) {
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

}
