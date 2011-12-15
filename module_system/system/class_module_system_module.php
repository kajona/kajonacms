<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

/**
 * Model for a single system-module
 * Modules are not represented in the system-table directly. so a moduleid is being used instead
 *
 * @package module_system
 */
class class_module_system_module extends class_model implements interface_model, interface_admin_listable  {

    private $strName = "";
    private $strNamePortal = "";
    private $strXmlNamePortal = "";
    private $strNameAdmin = "";
    private $strXmlNameAdmin = "";
    private $strVersion = "";
    private $intDate = "";
    private $intNavigation = "";
    private $intNr = "";
    private $strAspect = "";


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid
     */
    public function __construct($strSystemid = "") {
        $this->setArrModuleEntry("modul", "system");
        $this->setArrModuleEntry("moduleId", _system_modul_id_);

		//base class
		parent::__construct($strSystemid);

    }

    /**
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."system_module" => "module_id");
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrName();
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        return "icon_module.gif";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     * @return string
     */
    public function getStrAdditionalInfo() {
        return "V ".$this->getStrVersion()." &nbsp;(".timeToString($this->getIntDate(), true).")";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     * @return string
     */
    public function getStrLongDescription() {
        $objAdminInstance = $this->getAdminInstanceOfConcreteModule();
       if($objAdminInstance != null)
           $strDescription = $objAdminInstance->getModuleDescription();
       else
           $strDescription = "";

        return $strDescription;
    }


    /**
     * Initialises the current object, if a systemid was given
     *
     */
    protected function initObjectInternal() {
        $strQuery = "SELECT * FROM "._dbprefix_."system_module, "._dbprefix_."system WHERE system_id=module_id ORDER BY module_nr";
        $arrRow = array();
		$arrModules = $this->objDB->getPArray($strQuery, array());

		foreach($arrModules as $arrOneModule) {
		    if($arrOneModule["module_id"] == $this->getSystemid())
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
        if(isset($arrRow["module_aspect"]))
            $this->setStrAspect($arrRow["module_aspect"]);
    }

    /**
     * Updates the current object to the database
     * @return bool
     */
    protected function updateStateToDb() {
        $strQuery = "UPDATE "._dbprefix_."system_module SET
					  module_name =?,
					  module_filenameportal =?,
					  module_xmlfilenameportal =?,
					  module_filenameadmin =?,
					  module_xmlfilenameadmin =?,
					  module_version =?,
					  module_date =?,
					  module_navigation =?,
					  module_nr =?,
					  module_aspect=?
					WHERE module_id = ?
					";
        return$this->objDB->_pQuery($strQuery, array($this->getStrName(), $this->getStrNamePortal(), $this->getStrXmlNamePortal(), $this->getStrNameAdmin(),
                                            $this->getStrXmlNameAdmin(), $this->getStrVersion(), $this->getIntDate(), $this->getIntNavigation(), $this->getIntNr(), $this->getStrAspect(), $this->getSystemid()));
    }

    /**
     * Deletes the current object from the system
     * @return bool
     */
    public function deleteObject() {
        return true;
    }

    /**
     * Deletes the current object from the system.
     * Overwrite this method in order to remove the current object from the system.
     * The system-record itself is being delete automatically.
     *
     * @return bool
     */
    protected function deleteObjectInternal() {
        return true;
    }


    /**
     * Loads an array containing all installed modules from database
     *
     * @param bool $intStart
     * @param bool $intEnd
     * @return mixed
     * @static
     */
	public static function getAllModules($intStart = false, $intEnd = false) {
		$strQuery = "SELECT module_id
		               FROM "._dbprefix_."system_module,
		                    "._dbprefix_."system
		              WHERE module_id = system_id
		           ORDER BY system_sort ASC, system_comment ASC";
        if($intStart !== false && $intEnd !== false)
		    $arrIds = class_carrier::getInstance()->getObjDB()->getPArraySection($strQuery, array(), $intStart, $intEnd);
        else
		    $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());

		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_module_system_module($arrOneId["module_id"]);

		return $arrReturn;
	}

    /**
     * Counts the number of modules available
     * @static
     * @return int
     */
    public static function getAllModulesCount() {
        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."system_module,
                            "._dbprefix_."system
                      WHERE module_id = system_id
                   ORDER BY system_sort ASC, system_comment ASC";
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array());
        return $arrRow["COUNT(*)"];
    }

	/**
     * Tries to look up a module using the given name
	 *
	 * @param string $strName
	 * @param bool $bitIgnoreStatus
	 * @return class_module_system_module
	 * @static
	 */
	public static function getModuleByName($strName, $bitIgnoreStatus = false) {
        if(count(class_carrier::getInstance()->getObjDB()->getTables()) == 0)
            return null;

		$strQuery = "SELECT * FROM "._dbprefix_."system_module, "._dbprefix_."system WHERE system_id=module_id ORDER BY module_nr";
		$arrModules = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());
        $arrRow = array();
		foreach($arrModules as $arrOneModule) {
		    if($arrOneModule["module_name"] == $strName)
		       $arrRow = $arrOneModule;
		}

		if(count($arrRow) >= 1) {
            //check the status right here - better performance due to cached queries
            if(!$bitIgnoreStatus) {
                if($arrRow["system_status"] == "1")
                    return new class_module_system_module($arrRow["module_id"]);
                else
                    return null;

            }
            else
                return new class_module_system_module($arrRow["module_id"]);
        }
		else
		    return null;
	}

    /**
     * Looks up the id of a module using the passed module-number
     *
     * @param $strNr
     * @return string $strNr
     * @static
     */
	public static function getModuleIdByNr($strNr) {
		$strQuery = "SELECT * FROM "._dbprefix_."system_module, "._dbprefix_."system WHERE system_id=module_id ORDER BY module_nr";
		$arrModules = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());
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
     * @param string $strAspectFilter
	 * @return array
	 * @static
	 */
	public static function getModulesInNaviAsArray($strAspectFilter = "") {

        $arrParams = array();
        if($strAspectFilter != "") {
            $arrParams[] = "%".$strAspectFilter."%";
            $strAspectFilter = " AND (module_aspect = '' OR module_aspect IS NULL OR module_aspect LIKE ? )";
        }

	    //Loading all Modules
		$strQuery = "SELECT module_id, module_name
		               FROM "._dbprefix_."system_module,
		                    "._dbprefix_."system
		              WHERE module_navigation = 1
		                AND system_status = 1
		                AND module_id = system_id
                            ".$strAspectFilter."
		              ORDER BY system_sort ASC, system_comment ASC";
		return class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);
	}

    /**
     * Factory method, creates an instance of the admin-module referenced by the current
     * module-object.
     * The object returned is being initialized with a systemid optionally.
     *
     * @param string $strSystemid
     * @return interface_admin
     */
    public function getAdminInstanceOfConcreteModule($strSystemid = "") {
        if($this->getStrNameAdmin() != "" && uniStrpos($this->getStrNameAdmin(), ".php") !== false) {
            //creating an instance of the wanted module
            $strClassname = uniStrReplace(".php", "", $this->getStrNameAdmin());
            if(validateSystemid($strSystemid))
                $objModule = new $strClassname($strSystemid);
            else
                $objModule = new $strClassname();
            return $objModule;
        }
        else
            return null;
    }

    /**
     * Factory method, creates an instance of the portal-module referenced by the current
     * module-object.
     * The object returned is being initialized with the config-array optionally.
     *
     * @param string $arrElementData
     * @return interface_portal
     */
    public function getPortalInstanceOfConcreteModule($arrElementData = null) {
        if($this->getStrNamePortal() != "" && uniStrpos($this->getStrNamePortal(), ".php") !== false) {
            //creating an instance of the wanted module
            $strClassname = uniStrReplace(".php", "", $this->getStrNamePortal());
            if(is_array($arrElementData))
                $objModule = new $strClassname($arrElementData);
            else
                $objModule = new $strClassname(array());
            return $objModule;
        }
        else
            return null;
    }



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
    public function getStrAspect() {
        return $this->strAspect;
    }

    public function setStrAspect($strAspect) {
        $this->strAspect = $strAspect;
    }


}
