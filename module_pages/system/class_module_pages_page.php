<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

/**
 * Model for a page
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 */
class class_module_pages_page extends class_model implements interface_model, interface_versionable, interface_admin_listable  {

    public static $INT_TYPE_PAGE = 0;
    public static $INT_TYPE_ALIAS = 1;

    private $strActionEdit = "editPageProperties";
    private $strActionDelete = "deletePageProperties";


	private $strName = "";
	private $intType = 0;
	private $strKeywords = "";
	private $strDescription = "";
	private $strTemplate = "";
	private $strBrowsername = "";
	private $strSeostring = "";
	private $strLanguage = "";
	private $strAlias = "";

    private $strOldName;
    private $intOldType;
    private $strOldKeywords;
    private $strOldDescription;
    private $strOldTemplate;
    private $strOldBrowsername;
    private $strOldSeostring;
    private $strOldLanguage;
    private $strOldAlias;

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $this->setArrModuleEntry("modul", "pages");
        $this->setArrModuleEntry("moduleId", _pages_modul_id_);


		//init the object with the language currently selected - admin or portal
		if(defined("_admin_") && _admin_ === true)
		    $this->setStrLanguage($this->getStrAdminLanguageToWorkOn());
		else
		    $this->setStrLanguage($this->getStrPortalLanguage());

		//base class
		parent::__construct($strSystemid);
    }


    /**
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."page" => "page_id");
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrBrowsername();
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        if($this->getIntType() == self::$INT_TYPE_ALIAS)
            return "icon_page_alias.gif";
        else
            return "icon_page.gif";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     * @return string
     */
    public function getStrAdditionalInfo() {
        if($this->getIntType() == self::$INT_TYPE_ALIAS)
            return "-> ".uniStrTrim($this->getStrAlias(), 20);
        else
            return $this->getStrName();
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     * @return string
     */
    public function getStrLongDescription() {
        // TODO: Implement getStrLongDescription() method.
    }


    /**
     * Initialises the current object, if a systemid was given
     *
     */
    protected function initObjectInternal() {
		//language independent fields
		$strQuery = "SELECT *
					FROM "._dbprefix_."system,
					     "._dbprefix_."page
					WHERE system_id = page_id
					  AND system_id = ?";
		$arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()) );

        $this->setArrInitRow($arrRow);

		//language dependant fields
		if(count($arrRow) > 0) {
    		$strQuery = "SELECT *
    					FROM "._dbprefix_."page_properties
    					WHERE pageproperties_id = ?
    					  AND pageproperties_language = ? ";
    		$arrPropRow = $this->objDB->getPRow($strQuery, array( $this->getSystemid(), $this->getStrLanguage() ));
    		if(count($arrPropRow) == 0) {
    		    $arrPropRow["pageproperties_browsername"] = "";
        		$arrPropRow["pageproperties_description"] = "";
        		$arrPropRow["pageproperties_keywords"] = "";
        		$arrPropRow["pageproperties_template"] = "";
        		$arrPropRow["pageproperties_seostring"] = "";
        		$arrPropRow["pageproperties_language"] = "";
        		$arrPropRow["pageproperties_alias"] = "";
    		}
    		//merge both
		    $arrRow = array_merge($arrRow, $arrPropRow);

    		$this->setStrBrowsername($arrRow["pageproperties_browsername"]);
    		$this->setStrDesc($arrRow["pageproperties_description"]);
    		$this->setStrKeywords($arrRow["pageproperties_keywords"]);
    		$this->setStrName($arrRow["page_name"]);
    		$this->setIntType($arrRow["page_type"]);
    		$this->setStrTemplate($arrRow["pageproperties_template"]);
    		$this->setStrSeostring($arrRow["pageproperties_seostring"]);
    		$this->setStrLanguage($arrRow["pageproperties_language"]);
    		$this->setStrAlias($arrRow["pageproperties_alias"]);


            $this->strOldBrowsername = $arrRow["pageproperties_browsername"];
    		$this->strOldDescription = $arrRow["pageproperties_description"];
    		$this->strOldKeywords = $arrRow["pageproperties_keywords"];
    		$this->strOldName = $arrRow["page_name"];
    		$this->intOldType = $arrRow["page_type"];
    		$this->strOldTemplate = $arrRow["pageproperties_template"];
    		$this->strOldSeostring = $arrRow["pageproperties_seostring"];
    		$this->strOldLanguage = $arrRow["pageproperties_language"];
    		$this->strOldAlias = $arrRow["pageproperties_alias"];
		}
    }

    /**
     * saves the current object as a new object to the database
     *
     * @return bool
     */
    public function onInsertToDb() {

		//Create the system-record
		if(_pages_newdisabled_ == "true")
            $this->setStatus();

        return true;
    }



    /**
     * Updates the current object to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {

        //Make texts db-safe
        $strName = $this->generateNonexistingPagename($this->getStrName());
        $this->setStrName($strName);

        //create change-logs
        $objChanges = new class_module_system_changelog();
        $objChanges->createLogEntry($this, $this->strActionEdit);

		//Update the baserecord
		$strQuery = "UPDATE  "._dbprefix_."page
					SET page_name= ?,
                        page_type= ?
				       WHERE page_id= ?";

		//and the properties record
		//properties for this language already existing?
		$strCountQuery = "SELECT COUNT(*) FROM "._dbprefix_."page_properties
		                 WHERE pageproperties_id= ?
		                   AND pageproperties_language= ?";
		$arrCountRow = $this->objDB->getPRow($strCountQuery, array($this->getSystemid(), $this->getStrLanguage()) );

		if((int)$arrCountRow["COUNT(*)"] >= 1) {
		    //Already existing, updating properties
    		$strQuery2 = "UPDATE  "._dbprefix_."page_properties
    					SET pageproperties_description=?,
    						pageproperties_template=?,
    						pageproperties_keywords=?,
    						pageproperties_browsername=?,
    						pageproperties_seostring=?,
    						pageproperties_alias=?
    						WHERE pageproperties_id=?
    						  AND pageproperties_language=?";

            $arrParams = array(
                $this->getStrDesc(),
    			$this->getStrTemplate(),
    			$this->getStrKeywords(),
    			$this->getStrBrowsername(),
    			$this->getStrSeostring(),
    			$this->getStrAlias(),
    			$this->getSystemid(),
    			$this->getStrLanguage()
            );
		}
		else {
		    //Not existing, create one
		    $strQuery2 = "INSERT INTO "._dbprefix_."page_properties
						(pageproperties_id, pageproperties_keywords, pageproperties_description, pageproperties_template, pageproperties_browsername,
						 pageproperties_seostring, pageproperties_alias, pageproperties_language) VALUES
						(?, ?, ?, ?, ?, ?, ?, ?)";

            $arrParams = array(
                $this->getSystemid(),
                $this->getStrKeywords(),
                $this->getStrDesc(),
                $this->getStrTemplate(),
                $this->getStrBrowsername(),
                $this->getStrSeostring(),
                $this->getStrAlias(),
                $this->getStrLanguage()
            );
		}

        return ($this->objDB->_pQuery($strQuery, array( $strName, $this->getIntType(), $this->getSystemid() ) ) && $this->objDB->_pQuery($strQuery2, $arrParams)) ;

    }


    /**
     * Loads all pages known by the system
     *
     * @param int $intStart
     * @param int $intEnd
     * @param string $strFilter
     * @param bool $bitIncludeAlias
     * @return mixed class_module_pages_page
     * @static
     */
	public static function getAllPages($intStart = 0, $intEnd = 0, $strFilter = "", $bitIncludeAlias = true) {
        $arrParams = array();

        if($strFilter != "")
            $arrParams[] = $strFilter."%";

		$strQuery = "SELECT system_id
					FROM "._dbprefix_."page,
					"._dbprefix_."system
					WHERE system_id = page_id
					".($strFilter != "" ? " AND page_name like ? " : "" )."
                    ".($bitIncludeAlias ? "" : " AND page_type = 0 ")."
					ORDER BY page_name ASC";

		if($intStart == 0 && $intEnd == 0)
		    $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);
		else
		    $arrIds = class_carrier::getInstance()->getObjDB()->getPArraySection($strQuery, $arrParams, $intStart, $intEnd);

		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_module_pages_page($arrOneId["system_id"]);

		return $arrReturn;
	}

	/**
	 * Fetches the total number of pages available
	 *
     * @param bool $bitIncludeAlias
	 * @return int
	 */
	public static function getNumberOfPagesAvailable( $bitIncludeAlias = true) {
	    $strQuery = "SELECT COUNT(*)
					FROM "._dbprefix_."page,
					"._dbprefix_."system
					WHERE system_id = page_id
                    ".($bitIncludeAlias ? "" : " AND page_type = 0 ")." ";
		$arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array());
		return $arrRow["COUNT(*)"];
	}

	/**
	 * Returns a new page-instance, using the given name
	 *
	 * @param string $strName
	 * @return class_module_pages_page
	 */
	public static function getPageByName($strName) {
        $strQuery = "SELECT page_id
						FROM "._dbprefix_."page
						WHERE page_name= ?";
		$arrId = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strName));
		return new class_module_pages_page((isset($arrId["page_id"]) ? $arrId["page_id"] : ""));
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
						 WHERE system_prev_id=?
						   AND page_element_ph_element = element_name
						   AND system_id = page_element_id
						   ".( $bitJustActive ? "AND system_status = 1 " : "")."
						   AND page_element_ph_language = ?";
		$arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid(), $this->getStrLanguage() ));
		return $arrRow["COUNT(*)"];
	}

	/**
	 * Checks, how many locked elements are on this page
	 *
	 * @return int
	 */
	public function getNumberOfLockedElementsOnPage() {
	    //Check, if there are any Elements on this page
		$strQuery = "SELECT COUNT(*)
						 FROM "._dbprefix_."system as system
						  WHERE system_prev_id=?
							AND system_lock_id != 0
							AND system_lock_id != ? ";
		$arrRow = $this->objDB->getPRow($strQuery, array( $this->getSystemid(), $this->objSession->getUserID()  ));
		return $arrRow["COUNT(*)"];
	}

    /**
     * Deletes the given page and all related elements from the system.
     * Subelements, so pages and/or folders below are delete, too
     *
     * @return bool
     */
	protected function deleteObjectInternal() {

        $bitReturn = false;

        $arrSubElements = class_module_pages_folder::getPagesAndFolderList($this->getSystemid());
        foreach($arrSubElements as $objOneElement) {
            $objOneElement->deleteObject();
        }

        $objChanges = new class_module_system_changelog();
        $objChanges->createLogEntry($this, $this->strActionDelete);

	    //Get all Elements belonging to this page
		$arrElements = class_module_pages_pageelement::getAllElementsOnPage($this->getSystemid());

		//Loop over the elements
		foreach($arrElements as $objOneElement) {
			//Deletion passed to the pages_content class
			if(!$objOneElement->deleteObject()) {
				$bitReturn = false;
				break;
			}
		}

        //Delete the page and the properties out of the tables
        $strQuery = "DELETE FROM "._dbprefix_."page WHERE page_id = ? ";
        $strQuery2 = "DELETE FROM "._dbprefix_."page_properties WHERE pageproperties_id = ?";
        if($this->objDB->_pQuery($strQuery, array($this->getSystemid()) ) && $this->objDB->_pQuery($strQuery2, array($this->getSystemid()) )) {
            $bitReturn =  true;
        }

		return $bitReturn;
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
                               WHERE pageproperties_language = ?
                                 AND pageproperties_id = ? ";
            $arrCount = class_carrier::getInstance()->getObjDB()->getPRow($strCountQuery, array( $strTargetLanguage, $strId ) );

            if((int)$arrCount["COUNT(*)"] == 0) {
                $strUpdate = "UPDATE "._dbprefix_."page_properties
                              SET pageproperties_language = ?
                              WHERE ( pageproperties_language = '' OR pageproperties_language IS NULL )
                                 AND pageproperties_id = ? ";

                if(!class_carrier::getInstance()->getObjDB()->_pQuery($strUpdate, array( $strTargetLanguage, $strId  ) ))
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
	    $arrBasicSourcePage = $this->objDB->getPRow("SELECT * FROM "._dbprefix_."page WHERE page_id = ?", array( $strSourcePage ));

	    //and load an array of corresponding pageproperties
	    $arrBasicSourceProperties = $this->objDB->getPArray("SELECT * FROM "._dbprefix_."page_properties WHERE pageproperties_id = ?", array( $strSourcePage ));

	    //create the new systemid
	    $strIdOfNewPage = generateSystemid();

	    //start the copy-process
	    $this->objDB->transactionBegin();

	    //copy the rights and systemrecord
	    $objCommon = new class_module_system_common($this->getSystemid());
	    if(!$objCommon->copyCurrentSystemrecord($strIdOfNewPage)) {
	        $this->objDB->transactionRollback();
            class_logger::getInstance()->addLogRow("error while duplicating systemrecord ".$this->getSystemid()." to ".$strIdOfNewPage, class_logger::$levelError);
	        return false;
	    }


        $strNewPagename = $this->generateNonexistingPagename($arrBasicSourcePage["page_name"], false);
	    //create the foregin record in our table
	    $strQuery = "INSERT INTO "._dbprefix_."page
	    			(page_id, page_name, page_type) VALUES
	    			(?, ?, ?)";
	    if(!$this->objDB->_pQuery($strQuery, array( $strIdOfNewPage, $strNewPagename, $arrBasicSourcePage["page_type"] ) )) {
	        $this->objDB->transactionRollback();
            class_logger::getInstance()->addLogRow("error while creating record in table "._dbprefix_."page", class_logger::$levelError);
	        return false;
	    }

        //update the comment in system-table
        $strQuery = "UPDATE "._dbprefix_."system
                        SET system_comment= ?
                      WHERE system_id= ?";

        $this->objDB->_pQuery($strQuery, array($strNewPagename, $strIdOfNewPage ));

	    //insert all pageprops in all languages
	    foreach ($arrBasicSourceProperties as $arrOneProperty) {
	        $strQuery = "INSERT INTO "._dbprefix_."page_properties
	        (pageproperties_id, pageproperties_browsername, pageproperties_keywords, pageproperties_description, pageproperties_template, pageproperties_seostring, pageproperties_language, pageproperties_alias) VALUES
	        (?, ?, ?, ?, ?, ?, ?, ?)";

            $arrValues = array(
                $strIdOfNewPage,
                $arrOneProperty["pageproperties_browsername"],
                $arrOneProperty["pageproperties_keywords"],
                $arrOneProperty["pageproperties_description"],
                $arrOneProperty["pageproperties_template"],
                $arrOneProperty["pageproperties_seostring"],
                $arrOneProperty["pageproperties_language"],
                $arrOneProperty["pageproperties_alias"]
            );

	        if(!$this->objDB->_pQuery($strQuery, $arrValues, array(false, false, false, false, false, false, false, false))) {
	            $this->objDB->transactionRollback();
                class_logger::getInstance()->addLogRow("error while copying page properties", class_logger::$levelError);
	            return false;
	        }
	    }

	    //ok. so now load all elements on the source page and copy them, too
	    $arrElementsOnSource = class_module_pages_pageelement::getAllElementsOnPage($this->getSystemid());
	    if(count($arrElementsOnSource) > 0) {
    	    foreach ($arrElementsOnSource as $objOneSourceElement) {
    	        if($objOneSourceElement->copyElementToPage($strIdOfNewPage) == null) {
    	            $this->objDB->transactionRollback();
                    class_logger::getInstance()->addLogRow("error while copying page element ".$objOneSourceElement->getStrName(), class_logger::$levelError);
    	            return false;
    	        }
    	    }
	    }

	    //if we reach up here, we've done it. commit and quit ;)
	    $this->objDB->transactionCommit();
	    return true;
	}

    /**
     * Generates a pagename not yet existing.
     * Tries to detect if the new name is the name of the current page. If given, the same name
     * is being returned. Can be suppressed.
     *
     * @param string $strName
     * @param bool $bitAvoidSelfchek
     * @return string
     */
	public function generateNonexistingPagename($strName, $bitAvoidSelfchek = true) {
	    //Filter blanks out of pagename
		$strName = str_replace(" ", "_", $strName);

		//Pagename already existing?
		$strQuery = "SELECT page_id
					FROM "._dbprefix_."page
					WHERE page_name=? ";
		$arrTemp = $this->objDB->getPRow($strQuery, array($strName));

		$intNumbers = count($arrTemp);
		if($intNumbers != 0 && !($bitAvoidSelfchek && $arrTemp["page_id"] == $this->getSystemid()) ) {
			$intCount = 1;
            $strTemp = "";
			while($intNumbers != 0 && !($bitAvoidSelfchek && $arrTemp["page_id"] == $this->getSystemid()) ) {
				$strTemp = $strName."_".$intCount;
				$strQuery = "SELECT page_id
							FROM "._dbprefix_."page
							WHERE page_name=? ";
				$arrTemp = $this->objDB->getPRow($strQuery, array($strTemp));
				$intNumbers = count($arrTemp);
				$intCount++;
			}
			$strName = $strTemp;
		}
		return $strName;
	}


    public function getActionName($strAction) {
        if($strAction == $this->strActionEdit)
            return $this->getLang("seite_bearbeiten", "pages");
        else if($strAction == $this->strActionDelete)
            return $this->getLang("seite_loeschen", "pages");

        return $strAction;
    }

    public function getChangedFields($strAction) {
        if($strAction == $this->strActionEdit) {
            return array(
                array("property" => "browsername", "oldvalue" => $this->strOldBrowsername, "newvalue" => $this->getStrBrowsername()),
                array("property" => "description", "oldvalue" => $this->strOldDescription, "newvalue" => $this->getStrDesc()),
                array("property" => "keywords",    "oldvalue" => $this->strOldKeywords,    "newvalue" => $this->getStrKeywords()),
                array("property" => "name",        "oldvalue" => $this->strOldName,        "newvalue" => $this->getStrName()),
                array("property" => "template",    "oldvalue" => $this->strOldTemplate,    "newvalue" => $this->getStrTemplate()),
                array("property" => "seostring",   "oldvalue" => $this->strOldSeostring,   "newvalue" => $this->getStrSeostring()),
                array("property" => "alias",       "oldvalue" => $this->strOldAlias,       "newvalue" => $this->getStrAlias()),
                array("property" => "type",        "oldvalue" => $this->intOldType,        "newvalue" => $this->getIntType()),
                array("property" => "language",    "oldvalue" => $this->strOldLanguage,    "newvalue" => $this->getStrLanguage())
            );
        }
        else if($strAction == $this->strActionDelete) {
            return array(
                array("property" => "browsername", "oldvalue" => $this->strOldBrowsername),
                array("property" => "description", "oldvalue" => $this->strOldDescription),
                array("property" => "keywords",    "oldvalue" => $this->strOldKeywords),
                array("property" => "name",        "oldvalue" => $this->strOldName),
                array("property" => "template",    "oldvalue" => $this->strOldTemplate),
                array("property" => "seostring",   "oldvalue" => $this->strOldSeostring),
                array("property" => "alias",       "oldvalue" => $this->strOldAlias),
                array("property" => "type",        "oldvalue" => $this->intOldType),
                array("property" => "language",    "oldvalue" => $this->strOldLanguage)
            );
        }
    }

    public function renderValue($strProperty, $strValue) {
        return $strValue;
    }

    public function getClassname() {
        return __CLASS__;
    }

    public function getModuleName() {
        return $this->arrModule["modul"];
    }

    public function getPropertyName($strProperty) {
        return $strProperty;
    }

    public function getRecordName() {
        return class_carrier::getInstance()->getObjLang()->getLang("change_object_page", "pages");
    }



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
        $strName = uniStrtolower(urlSafeString($strName));

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
    	$this->strSeostring = urlSafeString($strSeostring);
    }
    public function setStrLanguage($strLanguage) {
        $this->strLanguage = $strLanguage;
    }

    public function getIntType() {
        return $this->intType;
    }

    public function setIntType($intType) {
        $this->intType = $intType;
    }

    public function getStrAlias() {
        return $this->strAlias;
    }

    public function setStrAlias($strAlias) {
        $this->strAlias = $strAlias;
    }



}
