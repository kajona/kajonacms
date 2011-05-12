<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

/**
 * Base class for all installers. Provides some needed function to avoid multiple
 * implementations
 *
 * @abstract
 * @package modul_system
 */
abstract class class_installer_base extends class_root {

    /**
	 * Contructor
	 *
	 */
	public function __construct($arrModule) {
	    //Call base constructor
		parent::__construct($arrModule, "", "installer");
	}

    /**
     * Returns the version of the current module.
     * $arrModule["version"]
     *
     * @return string
     */
	public function getVersion() {
	    return $this->arrModule["version"];
	}

	/**
	 * Returns the value of $arrModule["name_lang"], the long name of the module
	 *
	 * @return string
	 */
	public function getModuleName() {
        return $this->arrModule["name_lang"];
	}

	/**
	 * Returns the value of $arrModule["name"], the name of the module
	 *
	 * @return string
	 */
	public function getModuleNameShort() {
        return $this->arrModule["name"];
	}

    /**
     * Creates a text-based info. Update-Links are placed within those infos.
     *
     * @since 3.2
     * @return string info or an empty string in case of now errors
     */
    public function getModuleInstallInfo() {

        //check needed modules
        $arrModulesNeeded = $this->getNeededModules();
        $strNeeded = "";
        foreach($arrModulesNeeded as $strOneModule) {
            try {
                $objModule = class_modul_system_module::getModuleByName($strOneModule, true);
            }
            catch (class_exception $objException) {
                $objModule = null;
            }
            if($objModule == null) {
                $strNeeded .= $strOneModule.", ";
            }
        }

        if($strNeeded != "") {
            return $this->getText("installer_modules_needed", "system", "admin").substr($strNeeded, 0, -2);
        }

        //check, if a min version of the system is needed
        if($this->getMinSystemVersion() != "") {
            //the systems version to compare to
            $objSystem = class_modul_system_module::getModuleByName("system");
            if($objSystem == null || version_compare($this->getMinSystemVersion(), $objSystem->getStrVersion(), ">")) {
                return $this->getText("installer_systemversion_needed", "system", "admin").$this->getMinSystemVersion()."<br />";
            }
        }

        //ok, all needed modules are installed. check if update or install-link should be generated
        //first check: current module installed?
        try {
            $objModule = class_modul_system_module::getModuleByName($this->arrModule["name"], true);
        }
        catch (class_exception $objException) {
                $objModule = null;
        }
        if($objModule == null) {
            return "";
        }
        else {
            //updates available?
            if(version_compare($objModule->getStrVersion(), $this->arrModule["version"], "<")) {
                if($this->arrModule["name"] == "samplecontent")
                    return "<a href=\""._webpath_."/installer/installer.php?step=samplecontent&update=installer_".$this->arrModule["name"]."\">".$this->getText("installer_update", "system", "admin").$this->arrModule["version"]." (".$objModule->getStrVersion().")</a>";
                else
                    return "<a href=\""._webpath_."/installer/installer.php?step=install&update=installer_".$this->arrModule["name"]."\">".$this->getText("installer_update", "system", "admin").$this->arrModule["version"]." (".$objModule->getStrVersion().")</a>";
            }
            elseif(version_compare($objModule->getStrVersion(), $this->arrModule["version"], "=="))
                return $this->getText("installer_versioninstalled", "system", "admin").$objModule->getStrVersion();

        }
        return "";
    }

    /**
     * checks if the module can be installed
     *
     * @since 3.2
     * @return boolean
     */
    public function isModuleInstallable() {
    	$bitReturn = false;

    	if($this->getModuleInstallInfo() == "") {
    		//check if module not yet installed
            try {
            	$objModule = class_modul_system_module::getModuleByName($this->arrModule["name"], true);
            }
            catch (class_exception $objException) {
				$objModule = null;
            }
            if($objModule == null) {
                //not yet installed
                $bitReturn = true;
            }
    	}

    	return $bitReturn;
    }


	/**
	 * Creates the links to install a module or to run updates on a module
	 *
     * @deprecated use self::getModulInstallInfo() or self::getModuleInstallCheckbox() instead
	 * @return string
	 */
	public final function getModuleInstallLink() {
        $strReturn = "";
		$strReturn .= $this->arrModule["name_lang"]."<br />&nbsp;&nbsp;&nbsp;&nbsp;(V ".$this->arrModule["version"].")&nbsp;&nbsp;&nbsp;&nbsp;";

		//check needed modules
		$arrModulesNeeded = $this->getNeededModules();
		$strNeeded = "";
		foreach($arrModulesNeeded as $strOneModule) {
		    try {
		        $objModule = class_modul_system_module::getModuleByName($strOneModule, true);
		    }
		    catch (class_exception $objException) {
		        $objModule = null;
		    }
		    if($objModule == null) {
		        $strNeeded .= $strOneModule.", ";
		    }
		}

		if($strNeeded != "") {
		    $strReturn .= $this->getText("installer_modules_needed", "system", "admin").substr($strNeeded, 0, -2);
		    return $strReturn."<br />";
		}

		//check, if a min version of the system is needed
		if($this->getMinSystemVersion() != "") {
		    //the systems version to compare to
		    $objSystem = class_modul_system_module::getModuleByName("system");
		    if($objSystem == null || version_compare($this->getMinSystemVersion(), $objSystem->getStrVersion(), ">")) {
		        return $strReturn.$this->getText("installer_systemversion_needed", "system", "admin").$this->getMinSystemVersion()."<br />";
		    }
		}

		//ok, all needed modules are installed. check if update or install-link should be generated
		//or, no link ;)
		//first check: current module installed?
		try {
		    $objModule = class_modul_system_module::getModuleByName($this->arrModule["name"], true);
		}
		catch (class_exception $objException) {
		        $objModule = null;
		}
		if($objModule == null) {
		    //install link
		    if($this->arrModule["name"] == "samplecontent")
		        $strReturn .= "<a href=\""._webpath_."/installer/installer.php?step=samplecontent&install=installer_".$this->arrModule["name"]."\">".$this->getText("installer_install", "system", "admin")."</a>";
		    else
		        $strReturn .= "<a href=\""._webpath_."/installer/installer.php?step=install&install=installer_".$this->arrModule["name"]."\">".$this->getText("installer_install", "system", "admin")."</a>";
		    return $strReturn."<br />";
		}
		else {
		    //updates available?
		    if(version_compare($objModule->getStrVersion(), $this->arrModule["version"], "<")) {
                $strReturn .= "<a href=\""._webpath_."/installer/installer.php?step=install&update=installer_".$this->arrModule["name"]."\">".$this->getText("installer_update", "system", "admin").$this->arrModule["version"]." (".$objModule->getStrVersion().")</a>";
            }

			return $strReturn."<br />";
		}
	}

    /**
     * checks if module post-installs are available and can be installed
     *
     * @since 3.2
     * @return boolean
     */
    public final function isModulePostInstallable() {
    	$bitReturn = false;

        if($this->getModulePostInstallInfo() == "") {
            $objModule = null;
            try {
                $objModule = class_modul_system_module::getModuleByName($this->arrModule["name"], true);
            }
            catch (class_exception $objE) { }

            if(strpos($this->arrModule["name"], "element") !== false)
                $objModule = true;

            if($objModule != null && $this->hasPostInstalls()) {
            	$bitReturn = true;
            }

            //check if required modules are given
            $arrModulesNeeded = $this->getNeededModules();
            foreach($arrModulesNeeded as $strOneModule) {
                try {
                    $objModule = class_modul_system_module::getModuleByName($strOneModule, true);
                }
                catch (class_exception $objException) {
                    $objModule = null;
                }
                if($objModule == null) {
                    $bitReturn = false;
                }
            }

            //check, if a min version of the system is needed
            if($this->getMinSystemVersion() != "") {
                //the systems version to compare to
                $objSystem = class_modul_system_module::getModuleByName("system");
                if($objSystem == null || version_compare($this->getMinSystemVersion(), $objSystem->getStrVersion(), ">")) {
                    $bitReturn = false;
                }
            }

        }

        return $bitReturn;
    }

    /**
     * Creates text-based infos regarding the current installation.
     * If a post-install is possible, an empty string is returned.
     *
     * @return string or ""
     */
    public final function getModulePostInstallInfo() {

        //ok, all needed modules are installed. check if update or install-link should be generated
        //or, no link ;)
        //first check: current module installed?
        $objModule = null;
        try {
            $objModule = class_modul_system_module::getModuleByName($this->arrModule["name"], true);
        }
        catch (class_exception $objE) { }

        if(strpos($this->arrModule["name"], "element") !== false)
            $objModule = true;

        //check, if a min version of the system is needed
        if($this->getMinSystemVersion() != "") {
            //the systems version to compare to
            $objSystem = class_modul_system_module::getModuleByName("system");
            if($objSystem == null || version_compare($this->getMinSystemVersion(), $objSystem->getStrVersion(), ">")) {
                return $this->getText("installer_systemversion_needed", "system", "admin").$this->getMinSystemVersion()."<br />";
            }
        }


        //check if required modules are given
        $arrModulesNeeded = $this->getNeededModules();
        $strNeeded = "";
        foreach($arrModulesNeeded as $strOneModule) {
            try {
                $objTestModule = class_modul_system_module::getModuleByName($strOneModule, true);
            }
            catch (class_exception $objException) {
                $objTestModule = null;
            }
            if($objTestModule == null) {
                $strNeeded .= $strOneModule.", ";
            }
        }

        if($strNeeded != "") {
            return $this->getText("installer_modules_needed", "system", "admin").substr($strNeeded, 0, -2);
        }

        if($objModule != null && $this->hasPostInstalls()) {
            //install link
            return "";
        }
        else if($objModule == null) {
            return $this->getText("installer_module_notinstalled", "system", "admin");
        }

        //Update-Link?
        if($this->hasPostUpdates()) {
            return "<a href=\""._webpath_."/installer/installer.php?step=postInstall&postUpdate=installer_".$this->arrModule["name"]."\">".$this->getText("installer_update", "system", "admin").$this->arrModule["version"]."</a>";
        }

        return "";
    }

	/**
	 * Invokes the installation of the module
	 *
	 */
	public final function doModuleInstall() {
	    $strReturn = "";
        //check, if module aint installed
        try {
            $objModule = class_modul_system_module::getModuleByName($this->arrModule["name"], true);
        }
        catch (class_exception $objException) {
		        $objModule = null;
		}

        if($objModule != null) {
            $strReturn .= "<b>Module already installed!</b>";
        }
        else {
            //check needed modules
    		$arrModulesNeeded = $this->getNeededModules();
    		$strNeeded = "";
    		foreach($arrModulesNeeded as $strOneModule) {
    		    $objModule = class_modul_system_module::getModuleByName($strOneModule, true);
    		    if($objModule == null) {
    		        $strNeeded .= $strOneModule.", ";
    		    }
    		}
    		if($strNeeded == "") {
                $strReturn .= "Installing ".$this->arrModule["name_lang"]."...\n";
                $strReturn .= $this->install();
    		}
    		else {
    		    $strReturn .= "Needed modules missing! \n";
    		}
            $this->objDB->flushQueryCache();
        }

        return "\n\n".$strReturn;
	}

	/**
	 * Invokes the installation of the module
	 *
	 */
	public final function doPostInstall() {
	    $strReturn = "";
        //check, if module has postinstalles
        $objModule = class_modul_system_module::getModuleByName($this->arrModule["name"], true);
        if($objModule == null && strpos($this->arrModule["name"], "element") === false) {
            $strReturn .= "<b>No post-install options available!</b>";
        }
        else {
            $strReturn .= "Post-Installing ".$this->arrModule["name_lang"]."...\n";
            $strReturn .= $this->postInstall();
            $this->objDB->flushQueryCache();
        }
        return "\n\n".$strReturn;
	}

	/**
	 * Invokes the installation of the module
	 *
	 */
	public final function doModuleUpdate() {
	    $strReturn = "";
        //check, if module is installed
        $objModule = class_modul_system_module::getModuleByName($this->arrModule["name"], true);
        if($objModule == null) {
            $strReturn .= "<b>Module not installed!</b>";
        }
        else {
            $strReturn .= $this->update();
        }
        $this->objDB->flushQueryCache();

        //flush global cache
        $objSystemtask = new class_systemtask_flushcache();
        $objSystemtask->executeTask();

        return "\n\n".$strReturn;
	}

    /**
	 * Invokes the post-updates of the module
	 *
	 */
	public final function doModulePostUpdate() {
	    $strReturn = "";

        $strReturn .= $this->postUpdate();
        $this->objDB->flushQueryCache();

        return "\n\n".$strReturn;
	}

	/**
	 * Overwrite this function
	 *
	 */
	protected function hasPostInstalls() {
	}


	/**
	 * Overwrite this method. it should return an array of module-names
	 * needed as dependencies for the current module
	 *
	 * @return  array
	 */
	protected function getNeededModules() {
	    return array();
	}

	/**
	 * Overwrite this function!!!
	 */
	protected function install() {
	}

	/**
	 * Overwrite this function!!!
	 */
	protected function update() {
	}

    /**
	 * Overwrite this function!!!
	 */
	protected function postInstall() {
	}


    /**
     * Used to update the elements installed by the postInstall method, e.g. page-elements
     * Overwrite if needed
     *
     * @return bool
     */
    public function hasPostUpdates() {
        return false;
    }

    /**
     * Does all the updating of the installations done after the module-install
     * Overwrite if needed
     */
    public function postUpdate() {
    }


	//--Helpers------------------------------------------------------------------------------------------
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
		//We need 3 Steps:
		// 	1: New SystemID
		//	2: New SystemRecord
		// 	3: Register the Module in the ModuleTable

		//The previous id is the the id of the Root-Record -> 0
		$strPrevId = "0";

		$strSystemid = $this->createSystemRecord($strPrevId, "Module ".$strName." System node", true, $intModuleNr);

		$strQuery = "INSERT INTO "._dbprefix_."system_module
						(module_id, module_name, module_nr, module_filenameportal, module_xmlfilenameportal, module_filenameadmin,
						module_xmlfilenameadmin, module_version ,module_date, module_navigation)
					VALUES (?,?,?,?,?,?,?,?,?,?)";

        $arrParams = array();
        $arrParams[] = $strSystemid;
		$arrParams[] = $strName;
		$arrParams[] = (int)$intModuleNr;
		$arrParams[] = $strFilePortal;
		$arrParams[] = $strXmlPortal;
		$arrParams[] = $strFileAdmin;
		$arrParams[] = $strXmlAdmin;
		$arrParams[] = $strVersion;
		$arrParams[] = (int)time();
		$arrParams[] = ($bitNavi ? 1 : 0);

		$this->objDB->_pQuery($strQuery, $arrParams);

		class_logger::getInstance()->addLogRow("New module registered: ".$strSystemid. "(".$strName.")", class_logger::$levelInfo);

		//flush db-cache afterwards
		$this->objDB->flushQueryCache();

		return $strSystemid;
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
        $objElement = class_modul_pages_element::getElement($strElementName);
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
	 * @param int $intType class_modul_system::int_TYPE_XX
	 * @param int $intModule
	 */
	public function registerConstant($strName, $strValue, $intType, $intModule) {

		//register to current runtime env?
		if(!defined($strName))
			define($strName, $strValue);

	    if(!class_modul_system_setting::checkConfigExisting($strName)) {
    	    $objConstant = new class_modul_system_setting("");
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

?>