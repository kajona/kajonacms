<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

require_once(_systempath_."/class_installer_base.php");
require_once(_systempath_."/interface_installer.php");

/**
 * Class providing an installer for the samplecontent.
 * Samplecontent is not installed as a module, it just creates a few default entries
 * for other modules and installes a few sample-templates
 *
 * @package modul_samplecontent
 */
class class_installer_samplecontent extends class_installer_base implements interface_installer {


    private $strContentLanguage;

    private $strMasterID = "";
    private $strIndexID = "";

	public function __construct() {
        $arrModule = array();
		$arrModule["version"] 		  = "3.1.9";
		$arrModule["name"] 			  = "samplecontent";
		$arrModule["class_admin"]  	  = "";
		$arrModule["file_admin"] 	  = "";
		$arrModule["class_portal"] 	  = "";
		$arrModule["file_portal"] 	  = "";
		$arrModule["name_lang"] 	  = "Module Samplecontent";
		$arrModule["moduleId"] 		  = _samplecontent_modul_id_;

		$arrModule["tabellen"][]      = _dbprefix_."languages";
		parent::__construct($arrModule);

		//set the correct language
        $this->strContentLanguage = $this->objSession->getAdminLanguage();
        
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}
	
    public function getMinSystemVersion() {
	    return "3.0.9";
	}

	public function hasPostInstalls() {
        return false;
	}


   public function install() {
        $strReturn = "";
        include_once(_systempath_."/class_modul_system_module.php");
        $strPageId = "";

		$strReturn = "Installing ".$this->arrModule["name_lang"]."...\n";
		
		//Register the module
        $strReturn .= "\nRegistering module\n";
        $strSystemID = $this->registerModule($this->arrModule["name"], _samplecontent_modul_id_, "", "", $this->arrModule["version"] , false);
        
		
		//search for installers available
		include_once(_systempath_."/class_filesystem.php");
		$objFilesystem = new class_filesystem();
		$arrInstaller = $objFilesystem->getFilelist("/installer", array(".php"));

        foreach($arrInstaller as $intKey => $strFile)
            if(strpos($strFile, "installer_sc_") === false)
                unset($arrInstaller[$intKey]);

        asort($arrInstaller);
        
        $strReturn .= "Loading installers...\n";
        foreach ($arrInstaller as $strOneInstaller) {
            $strReturn .= "\n\nInstaller found: ".$strOneInstaller."\n";
            include_once(_realpath_."/installer/".$strOneInstaller);
            //Creating an object....
            $strClass = "class_".str_replace(".php", "", $strOneInstaller);
            $objInstaller = new $strClass();
            
            if($objInstaller instanceof interface_sc_installer ) {
                $strModule = $objInstaller->getCorrespondingModule();
                $strReturn .= "Module ".$strModule."...\n";
                $objModule = class_modul_system_module::getModuleByName($strModule);
                if($objModule == null) {
                    $strReturn .= "\t... not installed!\n";
                }
                else {
                    $strReturn .= "\t... installed.\n";
                    $objInstaller->setObjDb($this->objDB);
                    $objInstaller->setStrContentlanguage($this->strContentLanguage);
                    $strReturn .= $objInstaller->install();
                }
            }
            $this->objDB->flushQueryCache();
        }


		return $strReturn;
	}

	public function postInstall() {
	}


	public function update() {
	    $strReturn = "";
        //check the version we have and to what version to update
        $arrModul = $this->getModuleData($this->arrModule["name"], false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.0") {
            $strReturn .= $this->update_300_301();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.1") {
            $strReturn .= $this->update_301_302();
        }
        
	    $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.2") {
            $strReturn .= $this->update_302_309();
        }
        
		$arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.9") {
            $strReturn .= $this->update_309_3095();
        }
        
	    $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.95") {
            $strReturn .= $this->update_3095_310();
        }
        
	    $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.1.0") {
            $strReturn .= $this->update_310_311();
        }
        
	    $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.1.1") {
            $strReturn .= $this->update_311_319();
        }
	}

	private function update_300_301() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 3.0.0 to 3.0.1...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("samplecontent", "3.0.1");

        return $strReturn;
	}

	private function update_301_302() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 3.0.1 to 3.0.2...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("samplecontent", "3.0.2");

        return $strReturn;
	}
	
    private function update_302_309() {
        //Run the updates
        $strReturn = "";
        $strReturn .= "Updating 3.0.2 to 3.0.9...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("samplecontent", "3.0.9");

        return $strReturn;
    }
    
	private function update_309_3095() {
        //Run the updates
        $strReturn = "";
        $strReturn .= "Updating 3.0.9 to 3.0.95...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("samplecontent", "3.0.95");

        return $strReturn;
    }
    
    private function update_3095_310() {
        $strReturn = "Updating 3.0.95 to 3.1.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("samplecontent", "3.1.0");

        return $strReturn;
    }
    
    private function update_310_311() {
        $strReturn = "Updating 3.1.0 to 3.1.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("samplecontent", "3.1.1");

        return $strReturn;
    }
    
    private function update_311_319() {
        $strReturn = "Updating 3.1.1 to 3.1.9...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("samplecontent", "3.1.9");

        return $strReturn;
    }

}
?>