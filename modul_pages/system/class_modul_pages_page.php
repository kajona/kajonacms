<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

include_once(_systempath_."/class_model.php");
include_once(_systempath_."/interface_model.php");
include_once(_systempath_."/class_modul_system_common.php");

/**
 * Model for a page
 *
 * @package modul_pages
 */
class class_modul_pages_page extends class_model implements interface_model  {
	private $strName = "";
	private $strKeywords = "";
	private $strDescription = "";
	private $strTemplate = "";
	private $strBrowsername = "";
	private $strSeostring = "";
	private $strLanguage = "";

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul["name"] 				= "modul_pages";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _pages_modul_id_;
		$arrModul["table"]       		= _dbprefix_."page";
		$arrModul["table2"]       		= _dbprefix_."page_properties";
		$arrModul["modul"]				= "pages";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init the object with the language currently selected - admin or portal
		if(defined("_admin_") && _admin_ === true)
		    $this->setStrLanguage($this->getStrAdminLanguageToWorkOn());
		else
		    $this->setStrLanguage($this->getStrPortalLanguage());


		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
		//language independant fields
		$strQuery = "SELECT *
					FROM "._dbprefix_."system,
					     ".$this->arrModule["table"]."
					WHERE system_id = page_id
					  AND system_id = '".$this->objDB->dbsafeString($this->getSystemid())."'";
		$arrRow = $this->objDB->getRow($strQuery);

		//language dependant fields
		if(count($arrRow) > 0) {
    		$strQuery = "SELECT *
    					FROM ".$this->arrModule["table2"]."
    					WHERE pageproperties_id = '".$this->objDB->dbsafeString($this->getSystemid())."'
    					  AND pageproperties_language = '".dbsafeString($this->getStrLanguage())."'";
    		$arrPropRow = $this->objDB->getRow($strQuery);
    		if(count($arrPropRow) == 0) {
    		    $arrPropRow["pageproperties_browsername"] = "";
        		$arrPropRow["pageproperties_description"] = "";
        		$arrPropRow["pageproperties_keywords"] = "";
        		$arrPropRow["pageproperties_template"] = "";
        		$arrPropRow["pageproperties_seostring"] = "";
        		$arrPropRow["pageproperties_language"] = "";
    		}
    		//merge both
		    $arrRow = array_merge($arrRow, $arrPropRow);

    		$this->setStrBrowsername($arrRow["pageproperties_browsername"]);
    		$this->setStrDesc($arrRow["pageproperties_description"]);
    		$this->setStrKeywords($arrRow["pageproperties_keywords"]);
    		$this->setStrName($arrRow["page_name"]);
    		$this->setStrTemplate($arrRow["pageproperties_template"]);
    		$this->setStrSeostring($arrRow["pageproperties_seostring"]);
    		$this->setStrLanguage($arrRow["pageproperties_language"]);
		}
    }

    /**
     * saves the current object as a new object to the database
     *
     * @return bool
     */
    public function saveObjectToDb($strFolderid = "0") {
        //Befor saving to database, filter special chars
		$strDescription = htmlToString($this->getStrDesc(), false, false);
		$strName = $this->generateNonexistingPagename($this->getStrName());

		//Start the transaction
		$this->objDB->transactionBegin();
		$bitCommit = true;

		//Create the system-record
		if(_pages_newdisabled_ == "true")
		    $strPageSystemid = $this->createSystemRecord($strFolderid, "PAGE: ".$strName, true, "", "", 0);
		else
		    $strPageSystemid = $this->createSystemRecord($strFolderid, "PAGE: ".$strName);

		$this->setSystemid($strPageSystemid);
		class_logger::getInstance()->addLogRow("new page ".$strPageSystemid, class_logger::$levelInfo);

		//Saving the page to the pages-table
		$strQuery = "INSERT INTO ".$this->arrModule["table"]."
						(page_id, page_name ) VALUES
						('".$this->objDB->dbsafeString($strPageSystemid)."', '".$this->objDB->dbsafeString($strName)."')";

		if(!$this->objDB->_query($strQuery))
			$bitCommit = false;

		//and the page-properties
		$strQuery = "INSERT INTO ".$this->arrModule["table2"]."
						(pageproperties_id, pageproperties_keywords, pageproperties_description, pageproperties_template, pageproperties_browsername,
						 pageproperties_seostring, pageproperties_language) VALUES
						('".$this->objDB->dbsafeString($strPageSystemid)."', '".$this->objDB->dbsafeString($this->getStrKeywords())."',
						 '".$this->objDB->dbsafeString($strDescription)."', '".$this->objDB->dbsafeString($this->getStrTemplate())."',
						 '".$this->objDB->dbsafeString($this->getStrBrowsername())."', '".$this->objDB->dbsafeString($this->getStrSeostring())."',
						 '".$this->objDB->dbsafeString($this->getStrLanguage())."')";

		if(!$this->objDB->_query($strQuery))
			$bitCommit = false;

		//Commit or rollback?
		if($bitCommit) {
			$this->objDB->transactionCommit();
			return true;
		}
		else {
			$this->objDB->transactionRollback();
			return false;
		}
    }

    /**
     * Updates the current object to the database
     *
     * @return bool
     */
    public function updateObjectToDb($strFolderid = "0") {
        class_logger::getInstance()->addLogRow("updated page ".$this->getSystemid(), class_logger::$levelInfo);

        //Make texts db-save
		$strDescription = htmlToString($this->getStrDesc(), false, false);
		//Do we have a folderid?
		$strName = str_replace(" ", "_", $this->getStrName());
		//Pagename already existng?
		$strQuery = "SELECT COUNT(*)
					FROM "._dbprefix_."page
					WHERE page_name='".$this->objDB->dbsafeString($strName)."' AND page_id!='".$this->objDB->dbsafeString($this->getSystemid())."'";
		$arrResult = $this->objDB->getRow($strQuery);
		$intNumber = $arrResult["COUNT(*)"];
		if($intNumber != 0) {
			$intCount = 1;
			while($intNumber != 0) {
				$strTemp = $strName."_".$intCount;
				$strQuery = "SELECT COUNT(*)
							FROM "._dbprefix_."page
							WHERE page_name='".$this->objDB->dbsafeString($strTemp)."'";
				$arrResult = $this->objDB->getRow($strQuery);
				$intNumber = $arrResult["COUNT(*)"];
				$intCount++;
			}
			$strName = $strTemp;
		}

		//Update the baserecord
		$strQuery = "UPDATE  "._dbprefix_."page
					SET page_name='".$this->objDB->dbsafeString($strName)."'
						WHERE page_id='".$this->objDB->dbsafeString($this->getSystemid())."'";

		//and the properties record
		//properties fpr this language alerady existing?
		$strCountQuery = "SELECT COUNT(*) FROM ".$this->arrModule["table2"]."
		                 WHERE pageproperties_id='".$this->objDB->dbsafeString($this->getSystemid())."'
		                   AND pageproperties_language='".$this->objDB->dbsafeString($this->getStrLanguage())."'";
		$arrCountRow = $this->objDB->getRow($strCountQuery);


		if((int)$arrCountRow["COUNT(*)"] >= 1) {
		    //Alredy existing, updating properties
    		$strQuery2 = "UPDATE  "._dbprefix_."page_properties
    					SET pageproperties_description='".$this->objDB->dbsafeString($strDescription)."',
    						pageproperties_template='".$this->objDB->dbsafeString($this->getStrTemplate())."',
    						pageproperties_keywords='".$this->objDB->dbsafeString($this->getStrKeywords())."',
    						pageproperties_browsername='".$this->objDB->dbsafeString($this->getStrBrowsername())."',
    						pageproperties_seostring='".$this->objDB->dbsafeString($this->getStrSeostring())."'
    						WHERE pageproperties_id='".$this->objDB->dbsafeString($this->getSystemid())."'
    						  AND pageproperties_language='".$this->objDB->dbsafeString($this->getStrLanguage())."'";
		}
		else {
		    //Not existing, create one
		    $strQuery2 = "INSERT INTO ".$this->arrModule["table2"]."
						(pageproperties_id, pageproperties_keywords, pageproperties_description, pageproperties_template, pageproperties_browsername,
						 pageproperties_seostring, pageproperties_language) VALUES
						('".$this->objDB->dbsafeString($this->getSystemid())."', '".$this->objDB->dbsafeString($this->getStrKeywords())."',
						 '".$this->objDB->dbsafeString($strDescription)."', '".$this->objDB->dbsafeString($this->getStrTemplate())."',
						 '".$this->objDB->dbsafeString($this->getStrBrowsername())."', '".$this->objDB->dbsafeString($this->getStrSeostring())."',
						 '".$this->objDB->dbsafeString($this->getStrLanguage())."')";
		}

		//update the system record to set the new prev-id
		if($this->objDB->_query($strQuery) && $this->objDB->_query($strQuery2)) {
			$strQuery = "UPDATE "._dbprefix_."system
						SET SYSTEM_prev_id='".$this->objDB->dbsafeString($strFolderid)."',
							system_lm_time=".(int)time().",
							system_lm_user='".$this->objDB->dbsafeString($this->objSession->getUserID())."',
							system_comment='PAGE: ".$this->objDB->dbsafeString($strName)."'
							WHERE system_id = '".$this->objDB->dbsafeString($this->getSystemid())."'";

			if($this->objDB->_query($strQuery)) {
			    $this->setEditDate();
			    return true;
			}
		}
		else
		  return false;
    }
    
    

    /**
	 * Loads all pages known by the system
	 *
	 * @param int $intStart
	 * @param int $intEnd
	 * @param string $intFilter
	 * @return mixed class_modul_pages_page
	 * @static
	 */
	public static function getAllPages($intStart = 0, $intEnd = 0, $strFilter = "") {
		$strQuery = "SELECT system_id
					FROM "._dbprefix_."page,
					"._dbprefix_."system
					WHERE system_id = page_id
					".($strFilter != "" ? " AND page_name like '".dbsafeString($strFilter)."%'" : "" )."
					ORDER BY page_name ASC";

		if($intStart == 0 && $intEnd == 0)
		    $arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
		else
		    $arrIds = class_carrier::getInstance()->getObjDB()->getArraySection($strQuery, $intStart, $intEnd);
		        
		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_pages_page($arrOneId["system_id"]);

		return $arrReturn;
	}
	
	/**
	 * Fetches the total number of pages available
	 *
	 * @return unknown
	 */
	public static function getNumberOfPagesAvailable() {
	    $strQuery = "SELECT COUNT(*)
					FROM "._dbprefix_."page,
					"._dbprefix_."system
					WHERE system_id = page_id
					ORDER BY page_name ASC";
		$arrRow = class_carrier::getInstance()->getObjDB()->getRow($strQuery);
		return $arrRow["COUNT(*)"];
	}

	/**
	 * Returns a new page-instance, using the given name
	 *
	 * @param string $strName
	 * @return class_modul_pages_page
	 */
	public static function getPageByName($strName) {
        $strQuery = "SELECT system_id
						FROM "._dbprefix_."page,
							 "._dbprefix_."system
						WHERE page_name='".dbsafeString($strName)."'
							AND page_id = system_id";
		$arrId = class_carrier::getInstance()->getObjDB()->getRow($strQuery);
		return new class_modul_pages_page((isset($arrId["system_id"]) ? $arrId["system_id"] : ""));
	}

	/**
	 * Checks, how many elements are on this page
	 *
	 * @param bool $bitJustActive
	 * @return int
	 */
	public function getNumberOfElementsOnPage($bitJustActive = false) {
	    //Check, if there are any Elements on this page
		$strQuery = "SELECT COUNT(*)
						 FROM "._dbprefix_."page_element,
						      "._dbprefix_."element,
						      "._dbprefix_."system
						 WHERE system_prev_id='".dbsafeString($this->getSystemid())."'
						   AND page_element_placeholder_element = element_name
						   AND system_id = page_element_id
						   ".( $bitJustActive ? "AND system_status = 1 " : "")."
						   AND page_element_placeholder_language = '".dbsafeString($this->getStrLanguage())."'";
		$arrRow = $this->objDB->getRow($strQuery);
		return $arrRow["COUNT(*)"];
	}

	/**
	 * Checks, how many locked elements are on this page
	 *
	 * @param string $strSystemid
	 * @return int
	 */
	public function getNumberOfLockedElementsOnPage() {
	    //Check, if there are any Elements on this page
		$strQuery = "SELECT COUNT(*)
						 FROM "._dbprefix_."system as system
						  WHERE system_prev_id='".$this->objDB->dbsafeString($this->getSystemid())."'
							AND system_lock_id != 0
							AND system_lock_id != '".dbsafeString($this->objSession->getUserID())."'";
		$arrRow = $this->objDB->getRow($strQuery);
		return $arrRow["COUNT(*)"];
	}

    /**
     * Deletes the given page and all related elements from the system
     *
     * @param string $strSystemid
     * @return bool
     * @static
     */
	public static function deletePage($strSystemid) {
	    class_logger::getInstance()->addLogRow("deleted page ".$strSystemid, class_logger::$levelInfo);

	    $objDB = class_carrier::getInstance()->getObjDB();
	    $objRoot = new class_modul_system_common($strSystemid);
	    //Get all Elements belonging to this page
		$arrElements = class_modul_pages_pageelement::getAllElementsOnPage($strSystemid);

		//Start the transaction
		$objDB->transactionBegin();
		$bitCommit = true;
		$bitElements = true;
		//Loop over the elements
		foreach($arrElements as $objOneElement) {
			//Deletion passed to the pages_content class
			if(!class_modul_pages_pageelement::deletePageElement($objOneElement->getSystemid())) {
				$bitElements = false;
				$bitCommit = false;
				break;
			}
		}

		if($bitElements) {
			//Delete the page and the properties out of the tables
			$strQuery = "DELETE FROM "._dbprefix_."page WHERE page_id = '".dbsafeString($strSystemid)."'";
			$strQuery2 = "DELETE FROM "._dbprefix_."page_properties WHERE pageproperties_id = '".dbsafeString($strSystemid)."'";
			if($objDB->_query($strQuery) && $objDB->_query($strQuery2)) {
				$objRoot->deleteSystemRecord($strSystemid);
			}
			else {
				$bitCommit = false;
			}
		}
		else {
			$bitCommit = false;
		}

		//End TX
		if($bitCommit) {
			$objDB->transactionCommit();
			return true;
		}
		else {
			$objDB->transactionRollback();
			return false;
		}
	}

	/**
	 * Tries to assign all page-properties not yet assigned to a language.
	 * If properties are already existing, the record won't be modified
	 *
	 * @param string $strTargetLanguage
	 * @return bool
	 */
	public static function assignNullProperties($strTargetLanguage) {
        //Load all non-assigned props
        $strQuery = "SELECT pageproperties_id FROM "._dbprefix_."page_properties WHERE pageproperties_language = '' OR pageproperties_language IS NULL";
        $arrPropIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery); 
        
        foreach ($arrPropIds as $arrOneId) {
            $strId = $arrOneId["pageproperties_id"];
            $strCountQuery = "SELECT COUNT(*)
                                FROM "._dbprefix_."page_properties
                               WHERE pageproperties_language = '".dbsafeString($strTargetLanguage)."'
                                 AND pageproperties_id = '".dbsafeString($strId)."'";
            $arrCount = class_carrier::getInstance()->getObjDB()->getRow($strCountQuery);
            
            if((int)$arrCount["COUNT(*)"] == 0) {
                $strUpdate = "UPDATE "._dbprefix_."page_properties
                              SET pageproperties_language = '".dbsafeString($strTargetLanguage)."'
                              WHERE ( pageproperties_language = '' OR pageproperties_language IS NULL )
                                 AND pageproperties_id = '".dbsafeString($strId)."'";

                if(!class_carrier::getInstance()->getObjDB()->_query($strUpdate))
                    return false;
            }
        }
	    return true;
	}
	
	
	/**
	 * Does a deep copy of the current page.
	 * Inlcudes all page-elements created on the page
	 * and all languages.
	 *
	 * @return bool
	 */
	public function copyPage() {
	    class_logger::getInstance()->addLogRow("copy page ".$this->getSystemid(), class_logger::$levelInfo);
	    //working directly on the db is much more easier than handling this stuff by objects
	    $strSourcePage = $this->getSystemid();
	    
	    //load basic page properties
	    $arrBasicSourcePage = $this->objDB->getRow("SELECT * FROM ".$this->arrModule["table"]." WHERE page_id = '".dbsafeString($strSourcePage)."'");
	    
	    //and load an array of corresponding pageproperties
	    $arrBasicSourceProperties = $this->objDB->getArray("SELECT * FROM ".$this->arrModule["table2"]." WHERE pageproperties_id = '".dbsafeString($strSourcePage)."'");
	    
	    //create the new systemid
	    $strIdOfNewPage = generateSystemid();
	    
	    //start the copy-process
	    $this->objDB->transactionBegin();
	    
	    //copy the rights and systemrecord
	    $objCommon = new class_modul_system_common($this->getSystemid());
	    if(!$objCommon->copyCurrentSystemrecord($strIdOfNewPage)) {
	        $this->objDB->transactionRollback();
	        return false;
	    }
	    
	    //create the foregin record in our table
	    $strQuery = "INSERT INTO ".$this->arrModule["table"]."
	    			(page_id, page_name) VALUES 
	    			('".dbsafeString($strIdOfNewPage)."', '".dbsafeString($this->generateNonexistingPagename($arrBasicSourcePage["page_name"]))."')";
	    if(!$this->objDB->_query($strQuery)) {
	        $this->objDB->transactionRollback();
	        return false;
	    }
	    
	    //insert all pageprops in all languages
	    foreach ($arrBasicSourceProperties as $arrOneProperty) {
	        $strQuery = "INSERT INTO ".$this->arrModule["table2"]." 
	        (pageproperties_id, pageproperties_browsername, pageproperties_keywords, pageproperties_description, pageproperties_template, pageproperties_seostring, pageproperties_language) VALUES 
	        ('".dbsafeString($strIdOfNewPage)."', 
	        '".dbsafeString($arrOneProperty["pageproperties_browsername"])."', 
	        '".dbsafeString($arrOneProperty["pageproperties_keywords"])."',
	        '".dbsafeString($arrOneProperty["pageproperties_description"])."',
	        '".dbsafeString($arrOneProperty["pageproperties_template"])."',
	        '".dbsafeString($arrOneProperty["pageproperties_seostring"])."',
	        '".dbsafeString($arrOneProperty["pageproperties_language"])."')";
	        
	        if(!$this->objDB->_query($strQuery)) {
	            $this->objDB->transactionRollback();
	            return false;
	        }
	    }
	    
	    //ok. so now load all elements on the source page and copy them, too
	    $arrElementsOnSource = class_modul_pages_pageelement::getAllElementsOnPage($this->getSystemid());
	    if(count($arrElementsOnSource) > 0) {
    	    foreach ($arrElementsOnSource as $objOneSourceElement) {
    	        if(!$objOneSourceElement->copyElementToPage($strIdOfNewPage)) {
    	            $this->objDB->transactionRollback();
    	            return false;
    	        }
    	    }
	    }
	    
	    //if we reach up here, we've done it. commit and quit ;)
	    $this->objDB->transactionCommit();
	    return true;
	}
	
	public function generateNonexistingPagename($strName) {
	    //Filter blanks out of pagename
		$strName = str_replace(" ", "_", $this->getStrName());

		//Pagename already existing?
		$strQuery = "SELECT COUNT(*)
					FROM ".$this->arrModule["table"]."
					WHERE page_name='".$this->objDB->dbsafeString($strName)."'";
		$arrTemp = $this->objDB->getRow($strQuery);

		$intNumbers = $arrTemp["COUNT(*)"];
		if($intNumbers != 0) {
			$intCount = 1;
			while($intNumbers != 0) {
				$strTemp = $strName."_".$intCount;
				$strQuery = "SELECT COUNT(*)
							FROM ".$this->arrModule["table"] ."
							WHERE page_name='".$this->objDB->dbsafeString($strTemp)."'";
				$arrTemp = $this->objDB->getRow($strQuery);
				$intNumbers = $arrTemp["COUNT(*)"];
				$intCount++;
			}
			$strName = $strTemp;
		}
		return $strName;
	}


// --- GETTERS / SETTERS --------------------------------------------------------------------------------
    public function getStrName() {
    	return $this->strName;
    }
    public function getStrKeywords() {
    	return $this->strKeywords;
    }
    public function getStrDesc() {
    	return $this->strDescription;
    }
    public function getStrTemplate() {
    	return $this->strTemplate;
    }
    public function getStrBrowsername() {
    	return $this->strBrowsername;
    }
    public function getStrSeostring() {
        return $this->strSeostring;
    }
    public function getStrLanguage() {
        return $this->strLanguage;
    }

    public function setStrName($strName) {
        //make a valid pagename
        $strName = uniStrtolower($strName);
        $arrSearch = array("ä", "ö", "ü", ".", " ", "ß");
        $arrReplace = array("ae", "oe", "ue", "-", "_", "ss");
        $strName = uniStrReplace($arrSearch, $arrReplace, $strName);
        
    	$this->strName = $strName;
    }
    public function setStrKeywords($strKeywords) {
    	$this->strKeywords = $strKeywords;
    }
    public function setStrDesc($strDesc) {
    	$this->strDescription = $strDesc;
    }
    public function setStrTemplate($strTemplate) {
    	$this->strTemplate = $strTemplate;
    }
    public function setStrBrowsername($strBrowsername) {
    	$this->strBrowsername = $strBrowsername;
    }
    public function setStrSeostring($strSeostring) {
        //Remove permitted characters
        $arrSearch =  array( ".", " ", "," );
        $arrReplace = array( "_", "_", "-" );
        $strSeostring = str_replace($arrSearch, $arrReplace, $strSeostring);
    	$this->strSeostring = $strSeostring;
    }
    public function setStrLanguage($strLanguage) {
        $this->strLanguage = $strLanguage;
    }

}
?>