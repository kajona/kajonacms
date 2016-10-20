<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

namespace Kajona\System\System;

use Kajona\Packagemanager\System\PackagemanagerMetadata;
use Kajona\Pages\System\PagesElement;

/**
 * Base class for all installers. Provides some needed function to avoid multiple
 * implementations
 *
 * @abstract
 * @package module_system
 */
abstract class InstallerBase extends Root implements InstallerInterface {

    /**
     * @var PackagemanagerMetadata
     */
    protected $objMetadata = null;

    /**
     * Constructor
     *
     */
    public function __construct() {
        //try to fetch the current dir

        $strClassname = get_class($this);
        $intStrps = StringUtil::lastIndexOf($strClassname, "\\");
        if($intStrps !== false) {
            $strClassname = StringUtil::substring($strClassname, $intStrps+1);
        }
        $strDir = Resourceloader::getInstance()->getPathForFile("/installer/".$strClassname.".php");
        $strDir = dirname(_realpath_.$strDir);
        $this->objMetadata = new PackagemanagerMetadata();
        $this->objMetadata->autoInit(StringUtil::replace(array("/installer", _realpath_), array("", ""), $strDir));
        parent::__construct();
    }

    /**
     * Generic implementation, triggers the update or the install method, depending on the parts already installed.
     * @return string
     */
    public function installOrUpdate() {

        $strReturn = "";

        $objModule = null;
        $objModule = SystemModule::getModuleByName($this->objMetadata->getStrTitle());

        if($objModule === null) {
            Logger::getInstance(Logger::PACKAGEMANAGEMENT)->addLogRow("triggering installation of ".$this->objMetadata->getStrTitle(), Logger::$levelInfo);
            $strReturn .= $this->install();
        }
        else {
            $strVersionInstalled = $objModule->getStrVersion();
            $strVersionAvailable = $this->objMetadata->getStrVersion();

            if(version_compare($strVersionAvailable, $strVersionInstalled, ">")) {
                Logger::getInstance(Logger::PACKAGEMANAGEMENT)->addLogRow("triggering update of ".$this->objMetadata->getStrTitle(), Logger::$levelInfo);
                $strReturn .= $this->update();
            }
        }

        Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_DBTABLES);
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
	 * @internal string $strXmlPortal
	 * @internal string $strXmlAdmin
	 * @return string the new SystemID of the record
	 */
	protected function registerModule($strName, $intModuleNr, $strFilePortal, $strFileAdmin, $strVersion, $bitNavi = true, $strXmlPortal = "", $strXmlAdmin = "") {

        $this->objDB->flushQueryCache();

		//The previous id is the the id of the Root-Record -> 0
		$strPrevId = "0";

        $objModule = new SystemModule();
        $objModule->setStrName($strName);
        $objModule->setIntNr($intModuleNr);
        $objModule->setStrNamePortal($strFilePortal);
        $objModule->setStrNameAdmin($strFileAdmin);
        $objModule->setStrVersion($strVersion);
        $objModule->setIntNavigation($bitNavi ? 1 : 0);
        $objModule->setIntDate(time());
        $objModule->setIntModuleNr($intModuleNr);
        $objModule->setArrModuleEntry("moduleId", $intModuleNr);
        $objModule->updateObjectToDb($strPrevId);

		Logger::getInstance()->addLogRow("New module registered: ".$objModule->getSystemid(). "(".$strName.")", Logger::$levelInfo);

		//flush db-cache afterwards
        Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_DBQUERIES | Carrier::INT_CACHE_TYPE_DBTABLES | Carrier::INT_CACHE_TYPE_MODULES | Carrier::INT_CACHE_TYPE_ORMCACHE | Carrier::INT_CACHE_TYPE_OBJECTFACTORY);

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
        $objModule = SystemModule::getModuleByName($strModuleName);
        $bitReturn = true;
        if($objModule !== null) {
            $objModule->setStrVersion($strVersion);
            $objModule->setIntDate(time());
            $bitReturn = $objModule->updateObjectToDb();
        }

	    Logger::getInstance()->addLogRow("module ".$strModuleName." updated to ".$strVersion, Logger::$levelInfo);
        Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_DBQUERIES | Carrier::INT_CACHE_TYPE_MODULES);
        return $bitReturn;
	}

    /**
     * Updates an element to the given version
     *
     * @param string $strElementName
     * @param string $strVersion
     */
    protected function updateElementVersion($strElementName, $strVersion) {
        if(SystemModule::getModuleByName("pages", true) !== null && Resourceloader::getInstance()->getCorePathForModule("module_pages") !== null) {
            $this->objDB->flushQueryCache();
            $objElement = PagesElement::getElement($strElementName);
            if($objElement != null) {
                $objElement->setStrVersion($strVersion);
                $objElement->updateObjectToDb();

                Logger::getInstance()->addLogRow("element ".$strElementName." updated to ".$strVersion, Logger::$levelInfo);
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
        $objModule = SystemModule::getModuleByName($this->objMetadata->getStrTitle(), true);
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
	 * @param int $intType @link SystemSetting::int_TYPE_XX
	 * @param int $intModule
     * @return bool
     */
	public function registerConstant($strName, $strValue, $intType, $intModule) {

		//register to current runtime env?
		if(!defined($strName))
			define($strName, $strValue);

	    if(!SystemSetting::checkConfigExisting($strName)) {
    	    $objConstant = new SystemSetting();
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

