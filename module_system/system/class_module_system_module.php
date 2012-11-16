<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

/**
 * Model for a single system-module
 *
 * @package module_system
 * @targetTable system_module.module_id
 */
class class_module_system_module extends class_model implements interface_model, interface_admin_listable {

    /**
     * @var string
     * @tableColumn module_name
     */
    private $strName = "";

    /**
     * @var string
     * @tableColumn module_filenameportal
     */
    private $strNamePortal = "";

    /**
     * @var string
     * @tableColumn module_xmlfilenameportal
     */
    private $strXmlNamePortal = "";

    /**
     * @var string
     * @tableColumn module_filenameadmin
     */
    private $strNameAdmin = "";

    /**
     * @var string
     * @tableColumn module_xmlfilenameadmin
     */
    private $strXmlNameAdmin = "";

    /**
     * @var string
     * @tableColumn module_version
     */
    private $strVersion = "";

    /**
     * @var int
     * @tableColumn module_date
     */
    private $intDate = "";

    /**
     * @var int
     * @tableColumn module_navigation
     */
    private $intNavigation = "";

    /**
     * @var int
     * @tableColumn module_nr
     */
    private $intNr = "";

    /**
     * @var string
     * @tableColumn module_aspect
     */
    private $strAspect = "";

    /**
     * @var class_module_system_module[]
     */
    private static $arrModules = array();


    //private static $arrModuleData = null;

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

        if(validateSystemid($strSystemid)) {
            self::$arrModules[$strSystemid] = $this;
        }
    }

    /**
     * Initialises the internal modules-cache.
     * Loads all module-data into a single array.
     * Avoids multiple queries against the module-table.
     *
     * @static
     *
     * @param bool $bitCache
     *
     * @return array
     */
    private static function loadModuleData($bitCache = true) {
        $strQuery = "SELECT *
                       FROM " . _dbprefix_ . "system_right,
                            " . _dbprefix_ . "system_module,
                            " . _dbprefix_ . "system
                  LEFT JOIN " . _dbprefix_ . "system_date
                         ON system_id = system_date_id
                      WHERE system_id = right_id
                        AND system_id=module_id
                   ORDER BY system_sort ASC, system_comment ASC   ";

        return class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array(), null, null, $bitCache);
    }


    /**
     * Overwrites the base-method in order to provide a better caching-mechanism.
     */
    protected function initObjectInternal() {

        $arrRows = self::loadModuleData();

        foreach($arrRows as $arrOneRow) {
            if($arrOneRow["system_id"] == $this->getSystemid()) {
                $this->setStrName($arrOneRow["module_name"]);
                $this->setStrNamePortal($arrOneRow["module_filenameportal"]);
                $this->setStrXmlNamePortal($arrOneRow["module_xmlfilenameportal"]);
                $this->setStrNameAdmin($arrOneRow["module_filenameadmin"]);
                $this->setStrXmlNameAdmin($arrOneRow["module_xmlfilenameadmin"]);
                $this->setStrVersion($arrOneRow["module_version"]);
                $this->setIntDate($arrOneRow["module_date"]);
                $this->setIntNavigation($arrOneRow["module_navigation"]);
                $this->setIntNr($arrOneRow["module_nr"]);
                $this->setStrAspect($arrOneRow["module_aspect"]);

                $this->setArrInitRow($arrOneRow);
                break;
            }
        }
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
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
        return "icon_module.png";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        return "V " . $this->getStrVersion() . " &nbsp;(" . timeToString($this->getIntDate(), true) . ")";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        $objAdminInstance = $this->getAdminInstanceOfConcreteModule();
        if($objAdminInstance != null) {
            $strDescription = $objAdminInstance->getModuleDescription();
        }
        else {
            $strDescription = "";
        }

        return $strDescription;
    }

    /**
     * Loads an array containing all installed modules from database
     *
     * @param bool $intStart
     * @param bool $intEnd
     *
     * @return class_module_system_module[]
     * @static
     */
    public static function getAllModules($intStart = null, $intEnd = null) {
        $arrRows = self::loadModuleData();

        $arrReturn = array();
        $intI = 0;
        foreach($arrRows as $arrOneRow) {
            if($intStart != null && $intEnd != null) {
                if($intI >= $intStart && $intI <= $intEnd) {
                    $arrReturn[] = class_module_system_module::getModuleBySystemid($arrOneRow["module_id"]);
                }
            }
            else {
                $arrReturn[] = class_module_system_module::getModuleBySystemid($arrOneRow["module_id"]);
            }

            $intI++;
        }

        return $arrReturn;
    }

    /**
     * Counts the number of modules available
     *
     * @static
     *
     * @param string $strPrevid
     *
     * @return int
     */
    public static function getObjectCount($strPrevid = "") {
        return count(self::loadModuleData());
    }

    /**
     * Tries to look up a module using the given name
     *
     * @param string $strName
     * @param bool $bitIgnoreStatus
     *
     * @return class_module_system_module
     * @static
     */
    public static function getModuleByName($strName, $bitIgnoreStatus = false) {
        if(count(class_carrier::getInstance()->getObjDB()->getTables()) == 0) {
            return null;
        }


        //check if the module is already cached
        foreach(self::$arrModules as $objOneModule) {
            if(!$bitIgnoreStatus && $objOneModule->getStrName() == $strName) {
                return $objOneModule;
            }
        }


        $arrModules = self::loadModuleData();
        $arrRow = array();
        foreach($arrModules as $arrOneModule) {
            if($arrOneModule["module_name"] == $strName) {
                $arrRow = $arrOneModule;
            }
        }

        if(count($arrRow) >= 1) {
            //check the status right here - better performance due to cached queries
            if(!$bitIgnoreStatus) {
                if($arrRow["system_status"] == "1") {
                    return self::getModuleBySystemid($arrRow["module_id"]);
                }
                else {
                    return null;
                }

            }
            else {
                return self::getModuleBySystemid($arrRow["module_id"]);
            }
        }
        else {
            return null;
        }
    }


    /**
     * Creates a new instance of a module or returns an already instantiated one.
     * For modules, this is the preferred way of generating instances.
     *
     * @param $strSystemid
     *
     * @return class_module_system_module
     * @static
     */
    public static function getModuleBySystemid($strSystemid) {
        if(isset(self::$arrModules[$strSystemid])) {
            return self::$arrModules[$strSystemid];
        }

        return new class_module_system_module($strSystemid);

    }

    /**
     * Looks up the id of a module using the passed module-number
     *
     * @param $strNr
     *
     * @return string $strNr
     * @static
     */
    public static function getModuleIdByNr($strNr) {
        $arrModules = self::loadModuleData();
        $arrRow = array();
        foreach($arrModules as $arrOneModule) {
            if($arrOneModule["module_nr"] == $strNr) {
                $arrRow = $arrOneModule;
            }
        }

        if(count($arrRow) >= 1) {
            return $arrRow["module_id"];
        }
        else {
            return "";
        }
    }

    /**
     * Looks up all modules being active and allowed to appear in the admin-navigation
     * Creates a simple array, NO OBJECTS
     *
     * @param string $strAspectFilter
     *
     * @return class_module_system_module[]
     * @static
     */
    public static function getModulesInNaviAsArray($strAspectFilter = "") {

        $arrParams = array();
        if($strAspectFilter != "") {
            $arrParams[] = "%" . $strAspectFilter . "%";
            $strAspectFilter = " AND (module_aspect = '' OR module_aspect IS NULL OR module_aspect LIKE ? )";
        }

        //Loading all Modules
        $strQuery = "SELECT module_id, module_name
		               FROM " . _dbprefix_ . "system_module,
		                    " . _dbprefix_ . "system
		              WHERE module_navigation = 1
		                AND system_status = 1
		                AND module_id = system_id
                            " . $strAspectFilter . "
		              ORDER BY system_sort ASC, system_comment ASC";
        return class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);
    }

    /**
     * Factory method, creates an instance of the admin-module referenced by the current
     * module-object.
     * The object returned is being initialized with a systemid optionally.
     *
     * @param string $strSystemid
     *
     * @return interface_admin|class_admin
     */
    public function getAdminInstanceOfConcreteModule($strSystemid = "") {
        if($this->getStrNameAdmin() != "" && uniStrpos($this->getStrNameAdmin(), ".php") !== false) {
            //creating an instance of the wanted module
            $strClassname = uniStrReplace(".php", "", $this->getStrNameAdmin());
            if(validateSystemid($strSystemid)) {
                $objModule = new $strClassname($strSystemid);
            }
            else {
                $objModule = new $strClassname();
            }
            return $objModule;
        }
        else {
            return null;
        }
    }

    /**
     * Factory method, creates an instance of the portal-module referenced by the current
     * module-object.
     * The object returned is being initialized with the config-array optionally.
     *
     * @param string $arrElementData
     *
     * @return interface_portal
     */
    public function getPortalInstanceOfConcreteModule($arrElementData = null) {
        if($this->getStrNamePortal() != "" && uniStrpos($this->getStrNamePortal(), ".php") !== false) {
            //creating an instance of the wanted module
            $strClassname = uniStrReplace(".php", "", $this->getStrNamePortal());
            if(is_array($arrElementData)) {
                $objModule = new $strClassname($arrElementData);
            }
            else {
                $objModule = new $strClassname(array());
            }
            return $objModule;
        }
        else {
            return null;
        }
    }

    /**
     * Returns the data for a registered module as given in the database
     *
     * @param string $strName
     * @param bool $bitCache
     *
     * @return mixed
     */
    public static function getPlainModuleData($strName, $bitCache = true) {
        $arrModules = self::loadModuleData($bitCache);

        foreach($arrModules as $arrOneModule) {
            if($arrOneModule["module_name"] == $strName) {
                return $arrOneModule;
            }
        }

        return array();
    }

    public static function flushCache() {
        self::$arrModules = array();
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
