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
 * @targetTable page.page_id
 */
class class_module_pages_page extends class_model implements interface_model, interface_versionable, interface_admin_listable {

    public static $INT_TYPE_PAGE = 0;
    public static $INT_TYPE_ALIAS = 1;

    /**
     * @var string
     * @tableColumn page_name
     * @versionable
     *
     * @fieldMandatory
     * @fieldType text
     */
    private $strName = "";

    /**
     * @var int
     * @tableColumn page_type
     * @versionable
     */
    private $intType = 0;

    /**
     * @var string
     * @versionable
     */
    private $strKeywords = "";

    /**
     * @var string
     * @versionable
     *
     * @fieldType textarea
     */
    private $strDescription = "";

    /**
     * @var string
     * @versionable
     *
     * @fieldType dropdown
     */
    private $strTemplate = "";

    /**
     * @var string
     * @versionable
     *
     * @fieldMandatory
     * @fieldType text
     */
    private $strBrowsername = "";

    /**
     * @var string
     * @versionable
     */
    private $strSeostring = "";

    /**
     * @var string
     * @versionable
     */
    private $strLanguage = "";

    /**
     * @var string
     * @versionable
     *
     * @fieldType page
     */
    private $strAlias = "";
    
    /**
     * @var string
     * @versionable
     */
    private $strPath = "";

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
        if(defined("_admin_") && _admin_ === true) {
            $this->setStrLanguage($this->getStrAdminLanguageToWorkOn());
        }
        else {
            $this->setStrLanguage($this->getStrPortalLanguage());
        }

        //base class
        parent::__construct($strSystemid);
    }


    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        $strName = $this->getStrBrowsername();
        
        if ($strName == "")
            $strName = $this->getStrName ();
        
        return $strName;
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        if($this->getIntType() == self::$INT_TYPE_ALIAS) {
            return "icon_page_alias.png";
        }
        else {
            return "icon_page.png";
        }
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        if($this->getIntType() == self::$INT_TYPE_ALIAS) {
            return "-> " . uniStrTrim($this->getStrAlias(), 20);
        }
        else {
            return $this->getStrName();
        }
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
     * Initialises the current object, if a systemid was given

     */
    protected function initObjectInternal() {


        parent::initObjectInternal();
        $arrRow = $this->getArrInitRow();

        //language dependant fields
        if(count($arrRow) > 0) {
            $strQuery = "SELECT *
    					FROM " . _dbprefix_ . "page_properties
    					WHERE pageproperties_id = ?
    					  AND pageproperties_language = ? ";
            $arrPropRow = $this->objDB->getPRow($strQuery, array($this->getSystemid(), $this->getStrLanguage()));
            if(count($arrPropRow) == 0) {
                $arrPropRow["pageproperties_browsername"] = "";
                $arrPropRow["pageproperties_description"] = "";
                $arrPropRow["pageproperties_keywords"] = "";
                $arrPropRow["pageproperties_template"] = "";
                $arrPropRow["pageproperties_seostring"] = "";
                $arrPropRow["pageproperties_language"] = $this->getStrLanguage();
                $arrPropRow["pageproperties_alias"] = "";
                $arrPropRow["pageproperties_path"] = "";
            }
            //merge both
            $arrRow = array_merge($arrRow, $arrPropRow);

            $this->setStrBrowsername($arrRow["pageproperties_browsername"]);
            $this->setStrDesc($arrRow["pageproperties_description"]);
            $this->setStrKeywords($arrRow["pageproperties_keywords"]);
            $this->setStrTemplate($arrRow["pageproperties_template"]);
            $this->setStrSeostring($arrRow["pageproperties_seostring"]);
            $this->setStrAlias($arrRow["pageproperties_alias"]);
            $this->setStrPath($arrRow["pageproperties_path"]);


            $this->strOldBrowsername = $arrRow["pageproperties_browsername"];
            $this->strOldDescription = $arrRow["pageproperties_description"];
            $this->strOldKeywords = $arrRow["pageproperties_keywords"];
            $this->strOldName = $this->strName;
            $this->intOldType = $this->intType;
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
        if(_pages_newdisabled_ == "true") {
            $this->setIntRecordStatus(0);
        }
        
        $this->updatePath();
        
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
        $objChanges->createLogEntry($this, class_module_system_changelog::$STR_ACTION_EDIT);

        $this->updatePath();

        //Update the baserecord
        $bitBaseUpdate = parent::updateStateToDb();

        //and the properties record
        //properties for this language already existing?
        $strCountQuery = "SELECT COUNT(*) FROM " . _dbprefix_ . "page_properties
		                 WHERE pageproperties_id= ?
		                   AND pageproperties_language= ?";
        $arrCountRow = $this->objDB->getPRow($strCountQuery, array($this->getSystemid(), $this->getStrLanguage()));

        if((int)$arrCountRow["COUNT(*)"] >= 1) {
            //Already existing, updating properties
            $strQuery2 = "UPDATE  " . _dbprefix_ . "page_properties
    					SET pageproperties_description=?,
    						pageproperties_template=?,
    						pageproperties_keywords=?,
    						pageproperties_browsername=?,
    						pageproperties_seostring=?,
    						pageproperties_alias=?,
                                                pageproperties_path=?
    						WHERE pageproperties_id=?
    						  AND pageproperties_language=?";

            $arrParams = array(
                $this->getStrDesc(),
                $this->getStrTemplate(),
                $this->getStrKeywords(),
                $this->getStrBrowsername(),
                $this->getStrSeostring(),
                $this->getStrAlias(),
                $this->getStrPath(),
                $this->getSystemid(),
                $this->getStrLanguage()
            );
        }
        else {
            //Not existing, create one
            $strQuery2 = "INSERT INTO " . _dbprefix_ . "page_properties
						(pageproperties_id, pageproperties_keywords, pageproperties_description, pageproperties_template, pageproperties_browsername,
						 pageproperties_seostring, pageproperties_alias, pageproperties_language, pageproperties_path) VALUES
						(?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $arrParams = array(
                $this->getSystemid(),
                $this->getStrKeywords(),
                $this->getStrDesc(),
                $this->getStrTemplate(),
                $this->getStrBrowsername(),
                $this->getStrSeostring(),
                $this->getStrAlias(),
                $this->getStrLanguage(),
                $this->getStrPath()
            );
        }
        
        $bitBaseUpdate &= $this->objDB->_pQuery($strQuery2, $arrParams);
        
        $arrChildIds = $this->getChildNodesAsIdArray();
        
        foreach ($arrChildIds as $strChildId) {
            $objInstance = class_objectfactory::getInstance()->getObject($strChildId);
            $objInstance->updateObjectToDb();
        }

        return $bitBaseUpdate;
    }
    
    /**
     * Updates the navigation path of this page based on the parent's name.
     */
    public function updatePath() {
        $arrPathIds = $this->getPathArray("", _pages_modul_id_);
        $arrPathIds = array_slice($arrPathIds, 0, count($arrPathIds) - 1);
        $arrPathNames = array();
        
        foreach ($arrPathIds as $strParentId) {
            $objInstance = class_objectfactory::getInstance()->getObject($strParentId);
            
            if($objInstance instanceof class_module_pages_page) {
                $arrPathNames[] = urlSafeString($objInstance->getStrBrowsername());
            }
            //elseif($objInstance instanceof class_module_pages_folder) {
            //    $arrPathNames[] = urlSafeString($objInstance->getStrName());
            //}
        }
        
        $arrPathNames[] = urlSafeString($this->getStrBrowsername());
        
        $this->strPath = implode("/", $arrPathNames);
    }


    /**
     * Loads all pages known by the system
     *
     * @param int $intStart
     * @param int $intEnd
     * @param string $strFilter
     *
     * @return class_module_pages_page[]
     * @static
     */
    public static function getAllPages($intStart = null, $intEnd = null, $strFilter = "") {
        $arrParams = array();

        if($strFilter != "") {
            $arrParams[] = $strFilter . "%";
        }

        $strQuery = "SELECT system_id
					FROM " . _dbprefix_ . "page,
					" . _dbprefix_ . "system
					WHERE system_id = page_id
					" . ($strFilter != "" ? " AND page_name like ? " : "") . "
					ORDER BY page_name ASC";

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrIds as $arrOneId) {
            $arrReturn[] = new class_module_pages_page($arrOneId["system_id"]);
        }

        return $arrReturn;
    }

    /**
     * Returns a new page-instance, using the given name
     *
     * @param string $strName
     *
     * @return class_module_pages_page
     */
    public static function getPageByName($strName) {
        $strQuery = "SELECT page_id
						FROM " . _dbprefix_ . "page
						WHERE page_name= ?";
        $arrId = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strName));
        return new class_module_pages_page((isset($arrId["page_id"]) ? $arrId["page_id"] : ""));
    }

    /**
     * Checks, how many elements are on this page
     *
     * @param bool $bitJustActive
     *
     * @return int
     */
    public function getNumberOfElementsOnPage($bitJustActive = false) {
        //Check, if there are any Elements on this page
        $strQuery = "SELECT COUNT(*)
						 FROM " . _dbprefix_ . "page_element,
						      " . _dbprefix_ . "element,
						      " . _dbprefix_ . "system
						 WHERE system_prev_id=?
						   AND page_element_ph_element = element_name
						   AND system_id = page_element_id
						   " . ($bitJustActive ? "AND system_status = 1 " : "") . "
						   AND page_element_ph_language = ?";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid(), $this->getStrLanguage()));
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
						 FROM " . _dbprefix_ . "system as system
						  WHERE system_prev_id=?
							AND system_lock_id != 0
							AND system_lock_id != ? ";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid(), $this->objSession->getUserID()));
        return $arrRow["COUNT(*)"];
    }

    /**
     * Deletes the given page and all related elements from the system.
     * Subelements, so pages and/or folders below are delete, too
     *
     * @return bool
     */
    protected function deleteObjectInternal() {

        //Delete the page and the properties out of the tables
        $strQuery2 = "DELETE FROM " . _dbprefix_ . "page_properties WHERE pageproperties_id = ?";
        $this->objDB->_pQuery($strQuery2, array($this->getSystemid()));

        return parent::deleteObjectInternal();
    }

    /**
     * Tries to assign all page-properties not yet assigned to a language.
     * If properties are already existing, the record won't be modified
     *
     * @param string $strTargetLanguage
     *
     * @return bool
     */
    public static function assignNullProperties($strTargetLanguage) {
        //Load all non-assigned props
        $strQuery = "SELECT pageproperties_id FROM " . _dbprefix_ . "page_properties WHERE pageproperties_language = '' OR pageproperties_language IS NULL";
        $arrPropIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());

        foreach($arrPropIds as $arrOneId) {
            $strId = $arrOneId["pageproperties_id"];
            $strCountQuery = "SELECT COUNT(*)
                                FROM " . _dbprefix_ . "page_properties
                               WHERE pageproperties_language = ?
                                 AND pageproperties_id = ? ";
            $arrCount = class_carrier::getInstance()->getObjDB()->getPRow($strCountQuery, array($strTargetLanguage, $strId));

            if((int)$arrCount["COUNT(*)"] == 0) {
                $strUpdate = "UPDATE " . _dbprefix_ . "page_properties
                              SET pageproperties_language = ?
                              WHERE ( pageproperties_language = '' OR pageproperties_language IS NULL )
                                 AND pageproperties_id = ? ";

                if(!class_carrier::getInstance()->getObjDB()->_pQuery($strUpdate, array($strTargetLanguage, $strId))) {
                    return false;
                }
            }
        }
        return true;
    }


    /**
     * Does a deep copy of the current page.
     * Inlcudes all page-elements created on the page
     * and all languages.
     *
     * @param string $strNewPrevid
     *
     * @return bool
     */
    public function copyObject($strNewPrevid = "") {


        $this->objDB->transactionBegin();

        //fetch data to be updated after the general copy process
        //page-properties, language dependant
        $arrBasicSourceProperties = $this->objDB->getPArray("SELECT * FROM " . _dbprefix_ . "page_properties WHERE pageproperties_id = ?", array($this->getSystemid()));

        //create a new page-name
        $this->setStrName($this->generateNonexistingPagename($this->getStrName(), false));

        //copy the page-instance and all elements on the page
        parent::copyObject($strNewPrevid);

        //update the pages' properties in the table - manually
        foreach($arrBasicSourceProperties as $arrOneProperty) {

            //insert or update - the properties for the current language should aready be in place
            $this->objDB->flushQueryCache();
            $arrCount = $this->objDB->getPRow("SELECT COUNT(*) FROM " . _dbprefix_ . "page_properties WHERE pageproperties_id = ? AND pageproperties_language = ? ", array($this->getSystemid(), $arrOneProperty["pageproperties_language"]));

            if($arrCount["COUNT(*)"] == 0) {
                $strQuery = "INSERT INTO " . _dbprefix_ . "page_properties
                (pageproperties_browsername, pageproperties_keywords, pageproperties_description, pageproperties_template, pageproperties_seostring, pageproperties_alias, pageproperties_language, pageproperties_id) VALUES
                (?, ?, ?, ?, ?, ?, ?, ?)";
            }
            else {
                $strQuery = "UPDATE " . _dbprefix_ . "page_properties SET
                 pageproperties_id = ?, pageproperties_browsername = ?, pageproperties_keywords = ?, pageproperties_description = ?, pageproperties_template = ?, pageproperties_seostring = ? WHERE pageproperties_language = ? AND pageproperties_alias = ?";
            }

            $arrValues = array(
                $arrOneProperty["pageproperties_browsername"],
                $arrOneProperty["pageproperties_keywords"],
                $arrOneProperty["pageproperties_description"],
                $arrOneProperty["pageproperties_template"],
                $arrOneProperty["pageproperties_seostring"],
                $arrOneProperty["pageproperties_alias"],
                $arrOneProperty["pageproperties_language"],
                $this->getSystemid()
            );

            if(!$this->objDB->_pQuery($strQuery, $arrValues, array(false, false, false, false, false, false, false, false))) {
                $this->objDB->transactionRollback();
                class_logger::getInstance()->addLogRow("error while copying page properties", class_logger::$levelError);
                return false;
            }
        }


        $this->objDB->transactionCommit();

        return $this;
    }

    /**
     * Generates a pagename not yet existing.
     * Tries to detect if the new name is the name of the current page. If given, the same name
     * is being returned. Can be suppressed.
     *
     * @param string $strName
     * @param bool $bitAvoidSelfchek
     *
     * @return string
     */
    public function generateNonexistingPagename($strName, $bitAvoidSelfchek = true) {
        //Filter blanks out of pagename
        $strName = str_replace(" ", "_", $strName);

        //Pagename already existing?
        $strQuery = "SELECT page_id
					FROM " . _dbprefix_ . "page
					WHERE page_name=? ";
        $arrTemp = $this->objDB->getPRow($strQuery, array($strName));

        $intNumbers = count($arrTemp);
        if($intNumbers != 0 && !($bitAvoidSelfchek && $arrTemp["page_id"] == $this->getSystemid())) {
            $intCount = 1;
            $strTemp = "";
            while($intNumbers != 0 && !($bitAvoidSelfchek && $arrTemp["page_id"] == $this->getSystemid())) {
                $strTemp = $strName . "_" . $intCount;
                $strQuery = "SELECT page_id
							FROM " . _dbprefix_ . "page
							WHERE page_name=? ";
                $arrTemp = $this->objDB->getPRow($strQuery, array($strTemp));
                $intNumbers = count($arrTemp);
                $intCount++;
            }
            $strName = $strTemp;
        }
        return $strName;
    }


    public function getVersionActionName($strAction) {
        if($strAction == class_module_system_changelog::$STR_ACTION_EDIT) {
            return $this->getLang("seite_bearbeiten", "pages");
        }
        else if($strAction == class_module_system_changelog::$STR_ACTION_DELETE) {
            return $this->getLang("seite_loeschen", "pages");
        }

        return $strAction;
    }

    public function renderVersionValue($strProperty, $strValue) {
        return $strValue;
    }

    public function getVersionPropertyName($strProperty) {
        return $strProperty;
    }

    public function getVersionRecordName() {
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
    
    public function setStrPath($strPath) {
        $this->strPath = $strPath;
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

    /**
     * @param string $strDescription
     */
    public function setStrDescription($strDescription) {
        $this->strDescription = $strDescription;
    }

    /**
     * @return string
     */
    public function getStrDescription() {
        return $this->strDescription;
    }

    /**
     * @return string
     */
    public function getStrPath() {
        return $this->strPath;
    }

}
