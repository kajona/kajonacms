<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

include_once(_systempath_."/class_model.php");
include_once(_systempath_."/interface_model.php");

/**
 * Model for a single system-module
 * Modules are not represented in the system-table directly. so a moduleid is being used instead
 *
 * @package modul_system
 */
class class_modul_system_module extends class_model implements interface_model  {

    private $strName = "";
    private $strNamePortal = "";
    private $strXmlNamePortal = "";
    private $strNameAdmin = "";
    private $strXmlNameAdmin = "";
    private $strVersion = "";
    private $intDate = "";
    private $intNavigation = "";
    private $intNr = "";


    /**
     * Constructor to create a valid object
     *
     * @param string $strModuleid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_system";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _system_modul_id_;
		$arrModul["table"]       		= _dbprefix_."system_module";
		$arrModul["modul"]				= "system";

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
        $strQuery = "SELECT * FROM ".$this->arrModule["table"].", "._dbprefix_."system WHERE system_id=module_id ORDER BY module_nr";
        $arrRow = array();
		$arrModules = $this->objDB->getArray($strQuery);

		foreach($arrModules as $arrOneModule) {
		    if($arrOneModule["module_id"] == $this->objDB->dbsafeString($this->getSystemid()))
		       $arrRow = $arrOneModule;
		}

        $this->setStrName($arrRow["module_name"]);
        $this->setStrNamePortal($arrRow["module_filenameportal"]);
        $this->setStrXmlNamePortal($arrRow["module_xmlfilenameportal"]);
        $this->setStrNameAdmin($arrRow["module_filenameadmin"]);
        $this->setStrXmlNameAdmin($arrRow["module_xmlfilenameadmin"]);
        $this->setStrVersion($arrRow["module_version"]);
        $this->setIntDate($arrRow["module_date"]);
        $this->setIntNavigation($arrRow["module_navigation"]);
        $this->setIntNr($arrRow["module_nr"]);
    }

    /**
     * Updates the current object to the database
     * @return bool
     */
    public function updateObjectToDb() {
        $this->objDB->transactionBegin();
        $strQuery = "UPDATE ".$this->arrModule["table"]." SET
					  module_name ='".dbsafeString($this->getStrName())."',
					  module_filenameportal ='".dbsafeString($this->getStrNamePortal())."',
					  module_xmlfilenameportal ='".dbsafeString($this->getStrXmlNamePortal())."',
					  module_filenameadmin ='".dbsafeString($this->getStrNameAdmin())."',
					  module_xmlfilenameadmin ='".dbsafeString($this->getStrXmlNameAdmin())."',
					  module_version ='".dbsafeString($this->getStrVersion())."',
					  module_date ='".dbsafeString($this->getIntDate())."',
					  module_navigation ='".dbsafeString($this->getIntNavigation())."'
					WHERE module_id = '".dbsafeString($this->getSystemid())."'				
					";
        if($this->objDB->_query($strQuery)) {
            $this->objDB->transactionCommit();
            return true;
        }
        else {
            $this->objDB->transactionRollback();
            return false;
        }
    }

    /**
     * Loads an array containing all installed modules from database
	 *
	 * @return mixed
	 * @static
	 */
	public static function getAllModules() {
		$strQuery = "SELECT module_id
		               FROM "._dbprefix_."system_module,
		                    "._dbprefix_."system
		              WHERE module_id = system_id
		           ORDER BY system_sort ASC, system_comment ASC";
		$arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_system_module($arrOneId["module_id"]);

		return $arrReturn;
	}

	/**
     * Tries to look up an module using the given name
	 *
	 * @param string $strName
	 * @param bool $bitIgnoreStatus
	 * @return class_modul_system_module
	 * @static
	 */
	public static function getModuleByName($strName, $bitIgnoreStatus = false) {
        if(count(class_carrier::getInstance()->getObjDB()->getTables()) == 0)
            return null;
            
		$strQuery = "SELECT * FROM "._dbprefix_."system_module, "._dbprefix_."system WHERE system_id=module_id ".($bitIgnoreStatus ? "" : " AND system_status=1 " )."ORDER BY module_nr";
		$arrModules = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
        $arrRow = array();
		foreach($arrModules as $arrOneModule) {
		    if($arrOneModule["module_name"] == $strName)
		       $arrRow = $arrOneModule;
		}

		if(count($arrRow) >= 1)
		    return new class_modul_system_module($arrRow["module_id"]);
		else
		    return null;
	}

	/**
     * Looks up the id of a module using the passed module-number
	 *
	 * @return string $strNr
	 * @static
	 */
	public static function getModuleIdByNr($strNr) {
		$strQuery = "SELECT * FROM "._dbprefix_."system_module, "._dbprefix_."system WHERE system_id=module_id ORDER BY module_nr";
		$arrModules = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
        $arrRow = array();
		foreach($arrModules as $arrOneModule) {
		    if($arrOneModule["module_nr"] == $strNr)
		       $arrRow = $arrOneModule;
		}

		if(count($arrRow) >= 1)
		    return $arrRow["module_id"];
		else
		    return "";
	}

	/**
	 * Looks up all modules being active and allowed to appear in the admin-navigation
	 * Creates a simple array, NO OBJECTS
	 *
	 * @return array
	 * @static
	 */
	public static function getModulesInNaviAsArray() {
	    //Loading all Modules
		$strQuery = "SELECT module_id, module_name
		               FROM "._dbprefix_."system_module,
		                    "._dbprefix_."system
		              WHERE module_navigation = 1
		                AND system_status = 1
		                AND module_id = system_id
		              ORDER BY system_sort ASC, system_comment ASC";
		return class_carrier::getInstance()->getObjDB()->getArray($strQuery);
	}

    /**
     * Factory method, creates an instance of the admin-module referenced by the current
     * module-object.
     * The object returned is not being initialized with a systemid.
     *
     * @return object
     */
    public function getAdminInstanceOfConcreteModule() {
        if($this->getStrNameAdmin() != "" && uniStrpos($this->getStrNameAdmin(), ".php") !== false) {
            include_once(_adminpath_."/".$this->getStrNameAdmin());
            //creating an instance of the wanted module
            $strClassname = uniStrReplace(".php", "", $this->getStrNameAdmin());
            $objModule = new $strClassname();
            return $objModule;
        }
        else
            return null;
    }

// --- GETTERS / SETTERS --------------------------------------------------------------------------------
    public function getStrName() {
        return $this->strName;
    }
    public function getStrNamePortal() {
        return $this->strNamePortal;
    }
    public function getStrXmlNamePortal() {
        return $this->strXmlNamePortal;
    }
    public function getStrNameAdmin() {
        return $this->strNameAdmin;
    }
    public function getStrXmlNameAdmin() {
        return $this->strXmlNameAdmin;
    }
    public function getStrVersion() {
        return $this->strVersion;
    }
    public function getIntDate() {
        return $this->intDate;
    }
    public function getIntNavigation() {
        return $this->intNavigation;
    }
    public function getIntNr() {
        return $this->intNr;
    }

    public function setStrName($strName) {
        $this->strName = $strName;
    }
    public function setStrNamePortal($strPortal) {
        $this->strNamePortal = $strPortal;
    }
    public function setStrXmlNamePortal($strXmlPortal) {
        $this->strXmlNamePortal = $strXmlPortal;
    }
    public function setStrNameAdmin($strAdmin) {
        $this->strNameAdmin = $strAdmin;
    }
    public function setStrXmlNameAdmin($strXmlAdmin) {
        $this->strXmlNameAdmin = $strXmlAdmin;
    }
    public function setStrVersion($strVersion) {
        $this->strVersion = $strVersion;
    }
    public function setIntDate($intDate) {
        $this->intDate = $intDate;
    }
    public function setIntNavigation($intNavigation) {
        $this->intNavigation = $intNavigation;
    }
    public function setIntNr($intNr) {
        $this->intNr = $intNr;
    }
}
?>