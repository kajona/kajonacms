<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

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
    protected $objMetadata;


    /**
     * Generic implementation, triggers the update or the install method, depending on the parts already installed.
     * @return string
     */
    public function installOrUpdate() {

        $strReturn = "";

        if($this->objMetadata->getStrType() == class_module_packagemanager_manager::STR_TYPE_ELEMENT) {
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
		$this->objDB->flushQueryCache();

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
	    $strQuery = "UPDATE "._dbprefix_."system_module
	                 SET module_version= ?,
	                     module_date= ?
	               WHERE module_name= ?";

	    class_logger::getInstance()->addLogRow("module ".$strModuleName." updated to ".$strVersion, class_logger::$levelInfo);

	    $bitReturn = $this->objDB->_pQuery($strQuery, array($strVersion, time(), $strModuleName ));
        $this->objDB->flushQueryCache();
        return $bitReturn;
	}

    /**
     * Updates an element to the given version
     *
     * @param string $strElementName
     * @param string $strVersion
     */
    protected function updateElementVersion($strElementName, $strVersion) {
        $this->objDB->flushQueryCache();
        $objElement = class_module_pages_element::getElement($strElementName);
        if($objElement != null) {
            $objElement->setStrVersion($strVersion);
            $objElement->updateObjectToDb();

            class_logger::getInstance()->addLogRow("element ".$strElementName." updated to ".$strVersion, class_logger::$levelInfo);
        }
        $this->objDB->flushQueryCache();
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
    	    return $objConstant->updateObjectToDb();
	    }
	    else
	       return false;
	}

}

