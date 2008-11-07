<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                            *
********************************************************************************************************/

include_once(_systempath_."/class_model.php");
include_once(_systempath_."/interface_model.php");
include_once(_systempath_."/class_modul_system_common.php");
include_once(_systempath_."/class_modul_pages_page.php");

/**
 * Model for a element assigned to a page. NOT the raw-element!
 *
 * @package modul_pages
 */
class class_modul_pages_pageelement extends class_model implements interface_model  {

    private $strPlaceholder = "";
    private $strName = "";
    private $strElement = "";
    private $strTitle = "";
    private $strLanguage = "";

    private $strLockId = "";

    private $strClassAdmin = "";
    private $strClassPortal = "";
    private $intCachetime = -1;
    private $intRepeat = 0;


    private $intStartDate = 0;
    private $intEndDate = 0;

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul["name"] 				= "modul_pages_content";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _pages_inhalte_modul_id_;
		$arrModul["table"]       		= _dbprefix_."page_element";
		$arrModul["modul"]				= "pages_content";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
        $strQuery = "SELECT *
						 FROM "._dbprefix_."page_element,
						      "._dbprefix_."element,
						      "._dbprefix_."system
						  LEFT JOIN "._dbprefix_."system_date
						    ON (system_id = system_date_id)
						 WHERE system_id='".$this->objDB->dbsafeString($this->getSystemid())."'
						   AND page_element_placeholder_element = element_name
						   AND system_id = page_element_id
						 ORDER BY page_element_placeholder_placeholder ASC,
						 		system_sort ASC";
		$arrRow = $this->objDB->getRow($strQuery);
		if(count($arrRow) > 1) {
    		$this->setStrPlaceholder($arrRow["page_element_placeholder_placeholder"]);
    		$this->setStrName($arrRow["page_element_placeholder_name"]);
    		$this->setStrElement($arrRow["page_element_placeholder_element"]);
    		$this->setStrTitle($arrRow["page_element_placeholder_title"]);
    		$this->setStrLanguage($arrRow["page_element_placeholder_language"]);
    		$this->strLockId = $arrRow["system_lock_id"];
    		$this->setStrClassAdmin($arrRow["element_class_admin"]);
            $this->setStrClassPortal($arrRow["element_class_portal"]);
            $this->setIntCachetime($arrRow["element_cachetime"]);
            $this->setIntRepeat($arrRow["element_repeat"]);

            if((int)$arrRow["system_date_start"] > 0)
                $this->intStartDate = $arrRow["system_date_start"];

           if((int)$arrRow["system_date_end"] > 0)
                $this->intEndDate = $arrRow["system_date_end"];

		}
    }

    /**
     * saves the current object as a new object to the database
     *
     * @param string $strPrevId
     * @param string $strPlaceholder
     * @param string $strForeignTable
     * @param string $strPos
     * @return bool
     */
    public function saveObjectToDb($strPrevId, $strPlaceholder, $strForeignTable, $strPos) {

        //So, lets do the magic - create the records
		//For Security, we're using a tx --> create system & right records, then the element-record, and the page_element_record
		$this->objDB->transactionBegin();
		//As described, start with the system / right record.
		//Note: The current systemid is the pageid, so the system_prev_id for the element
		$strElementSystemId = $this->createSystemRecord($strPrevId, "ELEMENT: ".$strPlaceholder);
		$this->setSystemid($strElementSystemId);
		//And create the row in the Element-Table, if given
		if($strForeignTable != "") {
		    $strQuery = "INSERT INTO ".$strForeignTable." (content_id) VALUES ('".$this->objDB->dbsafeString($strElementSystemId)."')";
		}
		else {
		    $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."page_element";
		}
		//The record in the page_element_table
		$strQuery2 = "INSERT INTO "._dbprefix_."page_element
						(page_element_id, page_element_placeholder_placeholder, page_element_placeholder_name, page_element_placeholder_element, page_element_placeholder_language) VALUES
						('".$this->objDB->dbsafeString($strElementSystemId)."', '".$this->objDB->dbsafeString($this->getStrPlaceholder())."',
						 '".$this->objDB->dbsafeString($this->getStrName())."', '".$this->objDB->dbsafeString($this->getStrElement())."',
						 '".$this->objDB->dbsafeString($this->getStrLanguage())."')";

		class_logger::getInstance()->addLogRow("new page-element ".$strElementSystemId, class_logger::$levelInfo);

		if($this->objDB->_query($strQuery) && $this->objDB->_query($strQuery2))
			$this->objDB->transactionCommit();
		else {
			$this->objDB->transactionRollback();
			return false;
		}

        if($strPos == "first") {
			//As a special feature, we set the element as 2 in the array. so we can shift it one position up an have it on top of list
			$strQuery = "UPDATE "._dbprefix_."system SET system_sort = 1 WHERE system_id = '".$this->objDB->dbsafeString($strElementSystemId)."'";
			$this->objDB->_query($strQuery);
			//And shift this element one pos up to get correct order on systemtables
			class_modul_pages_pageelement::actionShiftElement("up", $strElementSystemId);

        }
        elseif ($strPos == "last") {
			//set the element as last, shift it up once an down again to get a correct order on systemtables
			$strQuery = "UPDATE "._dbprefix_."system SET system_sort = 999999 WHERE system_id = '".$this->objDB->dbsafeString($strElementSystemId)."'";
			$this->objDB->_query($strQuery);
			//And shift this element one pos up
			$this->actionShiftElement("up");
			$this->actionShiftElement("down");
        }

        return true;
    }

    /**
     * Updates the current object to the database
     * currently just updateing the internal title and the language
     *
     * @return bool
     */
    public function updateObjectToDb() {
        class_logger::getInstance()->addLogRow("updated page-element ".$this->getSystemid(), class_logger::$levelInfo);
        $strQuery = "UPDATE "._dbprefix_."page_element
							SET page_element_placeholder_title = '".$this->objDB->dbsafeString($this->getStrTitle(false))."',
							    page_element_placeholder_language = '".$this->objDB->dbsafeString($this->getStrLanguage())."',
							    page_element_placeholder_placeholder = '".$this->objDB->dbsafeString($this->getStrPlaceholder())."'
							WHERE page_element_id='".$this->objDB->dbsafeString($this->getSystemid())."'";
        $this->setEditDate();
        return $this->objDB->_query($strQuery);
    }

    /**
     * Makes a copy of the current element and saves it attached to the given page.
     * This copy includes the records in the elements' foreign tables
     *
     * @param string $strNewPage
     * @return bool
     */
    public function copyElementToPage($strNewPage) {
        class_logger::getInstance()->addLogRow("copy pageelement ".$this->getSystemid(), class_logger::$levelInfo);
        $this->objDB->transactionBegin();
        
        $strIdOfNewPageelement = generateSystemid();
        
        //working directly on the db is much easier right here!
        //start by making a copy of the sysrecords, attaching them to the new page
        $objCommon = new class_modul_system_common($this->getSystemid());
        $objCommon->copyCurrentSystemrecord($strIdOfNewPageelement, $strNewPage);
        
        //fetch data of the current element
        $arrCurrentElement = $this->objDB->getRow("SELECT * FROM ".$this->arrModule["table"]." WHERE page_element_id = '".dbsafeString($this->getSystemid())."'");
        
        //save data as foreign data of the new record
        $strQuery = "INSERT INTO ".$this->arrModule["table"]." 
        			(page_element_id, page_element_placeholder_placeholder, page_element_placeholder_name, page_element_placeholder_element, page_element_placeholder_title, page_element_placeholder_language) VALUES 
        			(
        			'".dbsafeString($strIdOfNewPageelement)."', 
        			'".dbsafeString($arrCurrentElement["page_element_placeholder_placeholder"])."', 
        			'".dbsafeString($arrCurrentElement["page_element_placeholder_name"])."',
        			'".dbsafeString($arrCurrentElement["page_element_placeholder_element"])."',
        			'".dbsafeString($arrCurrentElement["page_element_placeholder_title"])."',
        			'".dbsafeString($arrCurrentElement["page_element_placeholder_language"])."')";
        
        if(!$this->objDB->_query($strQuery)) {
            $this->objDB->transactionRollback();
            return false;
        }
        
        //now the tricky part - the elements content-table...
        //get elements table-name
        include_once(_adminpath_."/elemente/".$this->getStrClassAdmin());
		$strElementClass = str_replace(".php", "", $this->getStrClassAdmin());
		$objElement = new $strElementClass();
		//Fetch the table
		$strElementTable = $objElement->getTable();
		
		//just copy, if a table was given
		if($strElementTable != "") {
			//load the old row
			$arrContentRow = $this->objDB->getRow("SELECT * FROM ".$strElementTable." WHERE content_id = '".dbsafeString($this->getSystemid())."'");
	        
			//load the Columns of the table
			$arrColumns = $this->objDB->getColumnsOfTable($strElementTable);
			
			//build the new insert
			$strQuery = "INSERT INTO ".$strElementTable." ( ";
			foreach ($arrColumns as $arrOneColumn)
	            $strQuery .= " ".$this->objDB->encloseColumnName($arrOneColumn["columnName"]).",";
	
	        //remove last comma    
	        $strQuery = uniSubstr($strQuery, 0, -1);
	        $strQuery .= ") VALUES ( ";
	        foreach ($arrColumns as $arrOneColumn) {
	            if($arrOneColumn["columnName"] == "content_id") {
	                $strQuery .= " '".dbsafeString($strIdOfNewPageelement)."',";
	            }
	            else if(strpos($arrOneColumn["columnType"], "int") !== false) {
	                $intValue = $arrContentRow[$arrOneColumn["columnName"]];
	                if($intValue == "")
	                    $intValue = "NULL";
	                $strQuery .= "".dbsafeString($intValue).",";
	            }
	            else {
	            	//no dbsafestring here, otherwise contents may be double-encoded...
	                $strQuery .= "'".$arrContentRow[$arrOneColumn["columnName"]]."',";
	            }
	        }
	        $strQuery = uniSubstr($strQuery, 0, -1);
	        $strQuery .= ")";
			
	        if(!$this->objDB->_query($strQuery)) {
	            $this->objDB->transactionRollback();
	            return false;
	        }		
		}

        //ok, all done. return.
        $this->objDB->transactionCommit();
        return true;
    }
    
    
    /**
     * Loads all Elements on the given page known by the systems, so db-sided, not template-sided
     *
     * @param string $strPageId
     * @param bool $bitJustActive
     * @param string $strLanguage
     * @return mixed
     * @static
     */
    public static function getElementsOnPage($strPageId, $bitJustActive = false, $strLanguage = "") {

        $strAnd = "";
        if($bitJustActive) {
            $strAnd = "AND system_status = 1
                       AND ( system_date_start IS null OR (system_date_start = 0 OR system_date_start <= ".time()."))
                       AND ( system_date_end IS null OR (system_date_end = 0 OR system_date_end >= ".time().")) ";
        }

        $strQuery = "SELECT system_id
						 FROM "._dbprefix_."page_element,
						      "._dbprefix_."element,
						      "._dbprefix_."system
						      LEFT JOIN "._dbprefix_."system_date
						        ON (system_id = system_date_id)
						 WHERE system_prev_id='".dbsafeString($strPageId)."'
						   AND page_element_placeholder_element = element_name
						   AND system_id = page_element_id
						   AND page_element_placeholder_language = '".dbsafeString($strLanguage)."'
						   " . $strAnd."
						 ORDER BY page_element_placeholder_placeholder ASC,
						 		system_sort ASC";

		$arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);

		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_pages_pageelement($arrOneId["system_id"]);

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
						 WHERE system_prev_id='".dbsafeString($strPageId)."'
						   AND page_element_placeholder_element = element_name
						   AND system_id = page_element_id
						 ORDER BY page_element_placeholder_placeholder ASC,
						 		system_sort ASC";

		$arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);

		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_pages_pageelement($arrOneId["system_id"]);

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
     * @return class_modul_pages_pageelement or NULL of no element was found.
     */
    public static function getElementByPlaceholderAndPage($strPageId, $strPlaceholder, $strLanguage, $bitJustActive = true) {
    	$strAnd = "";
        if($bitJustActive) {
            $strAnd = "AND system_status = 1
                       AND ( system_date_start IS null OR (system_date_start = 0 OR system_date_start <= ".time()."))
                       AND ( system_date_end IS null OR (system_date_end = 0 OR system_date_end >= ".time().")) ";
        }

        $strQuery = "SELECT system_id
                         FROM "._dbprefix_."page_element,
                              "._dbprefix_."element,
                              "._dbprefix_."system
                              LEFT JOIN "._dbprefix_."system_date
                                ON (system_id = system_date_id)
                         WHERE system_prev_id='".dbsafeString($strPageId)."'
                           AND page_element_placeholder_element = element_name
                           AND system_id = page_element_id
                           AND page_element_placeholder_language = '".dbsafeString($strLanguage)."'
                           AND page_element_placeholder_placeholder = '".dbsafeString($strPlaceholder)."'
                           " . $strAnd."
                         ORDER BY page_element_placeholder_placeholder ASC,
                                system_sort ASC";

        $arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);

        if(count($arrIds) == 1) {
            return (new class_modul_pages_pageelement($arrIds[0]["system_id"]));	
        }
        else {
        	return null;
        }
    
    }

    /**
	 * Shifts an element up or down
	 * This is a special implementation, because we don't have the usual system_prev_id relations
	 * Note: Could be optimized!
	 *
	 * @param string $strMode up || down
	 * @return string "" in case of success
	 */
	public function actionShiftElement($strMode = "up") {
		$strReturn = "";
		//Load the current Element
		//Load all Elements on this page, sorted by the system_sort field
		$strQuery = "SELECT *
						 FROM "._dbprefix_."page_element,
						      "._dbprefix_."element,
						      "._dbprefix_."system
						 WHERE system_prev_id='".$this->objDB->dbsafeString($this->getPrevId())."'
						   AND page_element_placeholder_element = element_name
						   AND system_id = page_element_id
						 ORDER BY page_element_placeholder_placeholder ASC,
						 		system_sort ASC";

		$arrElementsOnPage = $this->objDB->getArray($strQuery, false);

		//Iterate over all elements to sort out
		$arrElementsOnPlaceholder = array();
		foreach($arrElementsOnPage as $strKey => $arrOneElementOnPage) {
			if($this->getStrPlaceholder() == $arrOneElementOnPage["page_element_placeholder_placeholder"])
				$arrElementsOnPlaceholder[] = $arrOneElementOnPage;
		}

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
								SET system_sort=".(((int)$intKey)+1)."
								WHERE system_id='".$this->objDB->dbsafeString($arrOneElementOnPlaceholder["system_id"])."'";
				$this->objDB->_query($strQuery);
			}
		}

		//Loading the data of the corresp site
		$objPage = new class_modul_pages_page($this->getPrevId());
		$this->flushPageFromPagesCache($objPage->getStrName());

		return $strReturn;
	}

	/**
	 * Deletes the element from the system-tables, also from the foreign-element-tables
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public static function deletePageElement($strSystemid) {
	    class_logger::getInstance()->addLogRow("deleted page-element ".$strSystemid, class_logger::$levelInfo);
	    $objDB = class_carrier::getInstance()->getObjDB();
	    $objRoot = new class_modul_system_common($strSystemid);
	    //Load the Element-Data
		$objElementData = new class_modul_pages_pageelement($strSystemid);
		include_once(_adminpath_."/elemente/".$objElementData->getStrClassAdmin());
		//Build the class-name
		$strElementClass = str_replace(".php", "", $objElementData->getStrClassAdmin());
		//and finally create the object
		$objElement = new $strElementClass();
		//Fetch the table
		$strElementTable = $objElement->getTable();
		//Delete the entry in the Element-Table
		if($strElementTable != "") {
    		$strQuery = "DELETE FROM ".$strElementTable." WHERE content_id='".dbsafeString($strSystemid)."'";
    		if(!$objDB->_query($strQuery))
    			return false;
		}

		//Delete from page_element table
		$strQuery = "DELETE FROM "._dbprefix_."page_element WHERE page_element_id='".dbsafeString($strSystemid)."'";
		if(!$objDB->_query($strQuery))
			return false;

		//And now the system / right table
		if(!$objRoot->deleteSystemRecord($strSystemid))
			return false;

		//Loading the data of the corresp site
		$objPage = new class_modul_pages_page($objRoot->getPrevId());
		$objRoot->flushPageFromPagesCache($objPage->getStrName());

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
                     WHERE page_element_placeholder_language = '' OR page_element_placeholder_language IS NULL";
        $arrElementIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);

        foreach ($arrElementIds as $arrOneId) {
            $strId = $arrOneId["page_element_id"];
            $strUpdate = "UPDATE "._dbprefix_."page_element
                          SET page_element_placeholder_language = '".dbsafeString($strTargetLanguage)."'
                          WHERE page_element_id = '".dbsafeString($strId)."'";

            if(!class_carrier::getInstance()->getObjDB()->_query($strUpdate))
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
        include_once(_systempath_."/class_modul_pages_page.php");
	    //Fetch all pages
        $arrObjPages = class_modul_pages_page::getAllPages();
        foreach($arrObjPages as $objOnePage) {
            if($objOnePage->getStrTemplate() == $strTemplate) {
                //Search for matching elements
                $strQuery = "SELECT system_id
						 FROM "._dbprefix_."page_element,
						      "._dbprefix_."element,
						      "._dbprefix_."system
						 WHERE system_prev_id='".dbsafeString($objOnePage->getSystemid())."'
						   AND page_element_placeholder_element = element_name
						   AND system_id = page_element_id
						 ORDER BY page_element_placeholder_placeholder ASC,
						 		system_sort ASC";

		        $arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
		        $arrPageElements = array();
		        foreach ($arrIds as $arrOneRow) {
		            $arrPageElements[] = new class_modul_pages_pageelement($arrOneRow["system_id"]);
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
     * Returns a title.
     * If no title was specified, it creates an instance of the current element and
     * class getContentTitle() to get an title
     *
     * @param bool $bitClever diables the loading by using an instance of the element
     * @return string
     */
    public function getStrTitle($bitClever = true) {
        if($this->strTitle != "" || !$bitClever)
            return $this->strTitle;
        //Create an instance of the objet and let it serve the comment...
        $strClassname = str_replace(".php", "", $this->getStrClassAdmin());
        include_once(_adminpath_."/elemente/".$this->getStrClassAdmin());
        $objElement = new $strClassname();
        $objElement->setSystemid($this->getSystemid());
        return $objElement->getContentTitle();
    }
    public function getStrLockId() {
    	if($this->strLockId == "")
    		return "0";
    	
        return $this->strLockId;
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
?>