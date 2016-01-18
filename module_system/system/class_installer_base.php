<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/
use Kajona\Pages\System\PagesElement;

/**
 * Base class for all installers. Provides some needed function to avoid multiple
 * implementations
 *
 * @abstract
 * @package module_system
 */
abstract class class_installer_base extends class_root implements interface_installer {

    /**
     * @var class_module_packagemanager_metadata
     */
    protected $objMetadata = null;

    /**
     * Constructor
     *
     */
    public function __construct() {
        //try to fetch the current dir

        $strClassname = get_class($this);
        $intStrps = uniStrrpos($strClassname, "\\");
        if($intStrps !== false) {
            $strClassname = uniSubstr($strClassname, $intStrps+1);
        }
        $strDir = class_resourceloader::getInstance()->getPathForFile("/installer/".$strClassname.".php");
        $strDir = dirname(_realpath_.$strDir);
        $this->objMetadata = new class_module_packagemanager_metadata();
        $this->objMetadata->autoInit(uniStrReplace(array("/installer", _realpath_), array("", ""), $strDir));
        parent::__construct();
    }

    /**
     * Generic implementation, triggers the update or the install method, depending on the parts already installed.
     * @return string
     */
    public function installOrUpdate() {

        $strReturn = "";

        $objModule = null;
        if($this->objMetadata->getStrType() == class_module_packagemanager_manager::STR_TYPE_ELEMENT) {
            if(class_module_system_module::getModuleByName("pages") !== null && is_dir(class_resourceloader::getInstance()->getCorePathForModule("module_pages", true)))
                $objModule = class_module_pages_element::getElement(uniStrReplace("element_", "", $this->objMetadata->getStrTitle()));
        }
        else
            $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle());

        if($objModule === null) {
            class_logger::getInstance("triggering installation of ".$this->objMetadata->getStrTitle(), class_logger::$levelInfo);
            $strReturn .= $this->install();
        }
        else {
            $strVersionInstalled = $objModule->getStrVersion();
            $strVersionAvailable = $this->objMetadata->getStrVersion();

            if(version_compare($strVersionAvailable, $strVersionInstalled, ">")) {
                class_logger::getInstance("triggering update of ".$this->objMetadata->getStrTitle(), class_logger::$levelInfo);
                $strReturn .= $this->update();
            }
        }

        class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBTABLES);
        return $strReturn;
    }



	/**
	 * Writes the data of a module to the database
	 *
	 * @param string $strName
	 * @param int $intModuleNr
	 * @param string $strFilePortal
	 * @param string $strFileAdmin
	 * @param string $strVersion
	 * @param bool $bitNavi
	 * @param string $strXmlPortal
	 * @param string $strXmlAdmin
	 * @return string the new SystemID of the record
	 */
	protected function registerModule($strName, $intModuleNr, $strFilePortal, $strFileAdmin, $strVersion, $bitNavi, $strXmlPortal = "", $strXmlAdmin = "") {

        $this->objDB->flushQueryCache();

		//The previous id is the the id of the Root-Record -> 0
		$strPrevId = "0";

        $objModule = new class_module_system_module();
        $objModule->setStrName($strName);
        $objModule->setIntNr($intModuleNr);
        $objModule->setStrNamePortal($strFilePortal);
        $objModule->setStrNameAdmin($strFileAdmin);
        $objModule->setStrVersion($strVersion);
        $objModule->setIntNavigation($bitNavi ? 1 : 0);
        $objModule->setStrXmlNamePortal($strXmlPortal);
        $objModule->setStrXmlNameAdmin($strXmlAdmin);
        $objModule->setIntDate(time());
        $objModule->setIntModuleNr($intModuleNr);
        $objModule->setArrModuleEntry("moduleId", $intModuleNr);
        $objModule->updateObjectToDb($strPrevId);

		class_logger::getInstance()->addLogRow("New module registered: ".$objModule->getSystemid(). "(".$strName.")", class_logger::$levelInfo);

		//flush db-cache afterwards
        class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBQUERIES | class_carrier::INT_CACHE_TYPE_DBTABLES | class_carrier::INT_CACHE_TYPE_MODULES | class_carrier::INT_CACHE_TYPE_ORMCACHE | class_carrier::INT_CACHE_TYPE_OBJECTFACTORY);

		return $objModule->getSystemid();
	}

	/**
	 * Updates the version of the given module to the given version
	 *
	 * @param string $strModuleName
	 * @param string $strVersion
	 * @return bool
	 */
	protected function updateModuleVersion($strModuleName, $strVersion) {
        $this->objDB->flushQueryCache();
        $objModule = class_module_system_module::getModuleByName($strModuleName);
        $bitReturn = true;
        if($objModule !== null) {
            $objModule->setStrVersion($strVersion);
            $objModule->setIntDate(time());
            $bitReturn = $objModule->updateObjectToDb();
        }

	    class_logger::getInstance()->addLogRow("module ".$strModuleName." updated to ".$strVersion, class_logger::$levelInfo);
        class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBQUERIES | class_carrier::INT_CACHE_TYPE_MODULES);
        return $bitReturn;
	}

    /**
     * Updates an element to the given version
     *
     * @param string $strElementName
     * @param string $strVersion
     */
    protected function updateElementVersion($strElementName, $strVersion) {
        if(class_module_system_module::getModuleByName("pages", true) !== null && class_resourceloader::getInstance()->getCorePathForModule("module_pages") !== null) {
            $this->objDB->flushQueryCache();
            $objElement = \Kajona\Pages\System\PagesElement::getElement($strElementName);
            if($objElement != null) {
                $objElement->setStrVersion($strVersion);
                $objElement->updateObjectToDb();

                class_logger::getInstance()->addLogRow("element ".$strElementName." updated to ".$strVersion, class_logger::$levelInfo);
            }
            $this->objDB->flushQueryCache();
        }
    }

    /**
     * Updates both, module and element to a new version -  if named the same way.
     * Makes use of $this->objMetadata->getStrTitle() to fetch the current name
     *
     * @param $strNewVersion
     *
     * @return bool
     */
    protected function updateElementAndModule($strNewVersion)
    {
        $bitReturn = $this->updateModuleVersion($this->objMetadata->getStrTitle(), $strNewVersion);
        $bitReturn = $bitReturn && $this->updateElementVersion($this->objMetadata->getStrTitle(), $strNewVersion);
        return $bitReturn;
    }

    /**
     * Removes the elements / modules handled by the current installer.
     * Use the reference param to add a human readable logging.
     *
     * @param string &$strReturn
     *
     * @return bool
     */
    protected function removeModuleAndElement(&$strReturn) {
        //delete the page-element
        $objElement = PagesElement::getElement($this->objMetadata->getStrTitle());
        if($objElement != null) {
            $strReturn .= "Deleting page-element '".$this->objMetadata->getStrTitle()."'...\n";
            $objElement->deleteObjectFromDatabase();
        }
        else {
            $strReturn .= "Error finding page-element '".$this->objMetadata->getStrTitle()."', aborting.\n";
            return false;
        }

        //delete the module-node
        $strReturn .= "Deleting the module-registration...\n";
        $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle(), true);
        if(!$objModule->deleteObjectFromDatabase()) {
            $strReturn .= "Error deleting module, aborting.\n";
            return false;
        }

        return true;
    }

	/**
	 * Registers a constant to load at system-startup
	 *
	 * @param string $strName
	 * @param string $strValue
	 * @param int $intType @link class_module_system_setting::int_TYPE_XX
	 * @param int $intModule
     * @return bool
     */
	public function registerConstant($strName, $strValue, $intType, $intModule) {

		//register to current runtime env?
		if(!defined($strName))
			define($strName, $strValue);

	    if(!class_module_system_setting::checkConfigExisting($strName)) {
    	    $objConstant = new class_module_system_setting();
    	    $objConstant->setStrName($strName);
    	    $objConstant->setStrValue($strValue);
    	    $objConstant->setIntType($intType);
    	    $objConstant->setIntModule($intModule);
    	    $bitReturn = $objConstant->updateObjectToDb();
            $this->objDB->flushQueryCache();
            return $bitReturn;
	    }
	    else
	       return false;

	}

}

