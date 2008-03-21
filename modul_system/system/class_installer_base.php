<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_installer_base.php						        									        *
* 	Interface for all model-classes          															*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

//Extend the root-class
require_once(_realpath_."/system/class_root.php");
require_once(_realpath_."/system/class_modul_system_module.php");

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
	 * Returns the value of $arrModule["name_lang"], the name of the module
	 *
	 * @return string
	 */
	public function getModuleName() {
        $this->arrModule["name_lang"];
	}


	/**
	 * Creates the links to install a module or to run updates on a module
	 *
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
		    if(version_compare($objModule->getStrVersion(), $this->arrModule["version"], "<"))
				$strReturn .= "<a href=\""._webpath_."/installer/installer.php?step=install&update=installer_".$this->arrModule["name"]."\">".$this->getText("installer_update", "system", "admin").$this->arrModule["version"]." (".$objModule->getStrVersion().")</a>";

			return $strReturn."<br />";
		}
	}

	/**
	 * Creates the links to install a module or to run updates on a module
	 *
	 * @return string
	 */
	public final function getModulePostInstallLink() {
        $strReturn = "";
		$strReturn .= $this->arrModule["name_lang"]."<br />&nbsp;&nbsp;&nbsp;&nbsp;(V ".$this->arrModule["version"].")&nbsp;&nbsp;&nbsp;&nbsp;";

		//ok, all needed modules are installed. check if update or install-link should be generated
		//or, no link ;)
		//first check: current module installed?
		$objModule = null;
		try {
		    $objModule = class_modul_system_module::getModuleByName($this->arrModule["name"], true);
		}
		catch (class_exception $objE) {

		}

		if(strpos($this->arrModule["name"], "element") !== false)
		    $objModule = true;
		    
	    //check, if a min version of the system is needed
		if($this->getMinSystemVersion() != "") {
		    //the systems version to compare to
		    $objSystem = class_modul_system_module::getModuleByName("system");
		    if($objSystem == null || version_compare($this->getMinSystemVersion(), $objSystem->getStrVersion(), ">")) {
		        return $strReturn.$this->getText("installer_systemversion_needed", "system", "admin").$this->getMinSystemVersion()."<br />";
		    }
		}    

		if($objModule != null && $this->hasPostInstalls()) {
		    //install link
		    $strReturn .= "<a href=\""._webpath_."/installer/installer.php?step=postInstall&postInstall=installer_".$this->arrModule["name"]."\">".$this->getText("installer_installpe", "system", "admin")."</a>";
		}
		else if($objModule == null) {
			$strReturn .= $this->getText("installer_module_notinstalled", "system", "admin");
		}

		return $strReturn ."<br />";
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
					VALUES (
						'".$this->objDB->dbsafeString($strSystemid)."',
						'".$this->objDB->dbsafeString($strName)."',
						".(int)$intModuleNr.",
						'".$this->objDB->dbsafeString($strFilePortal)."',
						'".$this->objDB->dbsafeString($strXmlPortal)."',
						'".$this->objDB->dbsafeString($strFileAdmin)."',
						'".$this->objDB->dbsafeString($strXmlAdmin)."',
						'".$this->objDB->dbsafeString($strVersion)."',
						".(int)time().",
						".( $bitNavi ? 1 : 0)."
					 )";
		$this->objDB->_query($strQuery);

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
	    $strQuery = "UPDATE "._dbprefix_."system_module
	                 SET module_version= '".$this->objDB->dbsafeString($strVersion)."',
	                     module_date=".(int)time()."
	                 WHERE module_name='".$this->objDB->dbsafeString($strModuleName)."'";

	    class_logger::getInstance()->addLogRow("module ".$strModuleName." updated to ".$strVersion, class_logger::$levelInfo);

	    return $this->objDB->_query($strQuery);
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
		
	    include_once(_systempath_."/class_modul_system_setting.php");
	    if(!class_modul_system_setting::checkConfigExisting($strName)) {
    	    $objConstant = new class_modul_system_setting("");
    	    $objConstant->setStrName($strName);
    	    $objConstant->setStrValue($strValue);
    	    $objConstant->setIntType($intType);
    	    $objConstant->setIntModule($intModule);
    	    return $objConstant->saveObjectToDb();
	    }
	    else
	       return false;
	}

}

?>