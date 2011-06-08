<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Class providing an installer for the samplecontent.
 * Samplecontent is not installed as a module, it just creates a few default entries
 * for other modules and installes a few sample-templates
 *
 * @package modul_samplecontent
 */
class class_installer_samplecontent extends class_installer_base implements interface_installer {


    private $strContentLanguage;


	public function __construct() {
        $arrModule = array();
		$arrModule["version"] 		  = "3.4.0";
		$arrModule["name"] 			  = "samplecontent";
		$arrModule["name_lang"] 	  = "Module Samplecontent";
		$arrModule["moduleId"] 		  = _samplecontent_modul_id_;
		parent::__construct($arrModule);

		//set the correct language
        $this->strContentLanguage = $this->objSession->getAdminLanguage();
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}

    public function getMinSystemVersion() {
	    return "3.3.1.8";
	}

	public function hasPostInstalls() {
        return false;
	}


   public function install() {
        $strReturn = "";

		$strReturn = "Installing ".$this->arrModule["name_lang"]."...\n";

		//Register the module
        $strReturn .= "\nRegistering module\n";
        $this->registerModule($this->arrModule["name"], _samplecontent_modul_id_, "", "", $this->arrModule["version"] , false);

		//search for installers available
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
        //check installed version and to which version we can update
        $arrModul = $this->getModuleData($this->arrModule["name"], false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

	    $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.1.0") {
            $strReturn .= $this->update_310_311();
        }

	    $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.1.1") {
            $strReturn .= $this->update_311_319();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.1.9") {
            $strReturn .= $this->update_319_3195();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.1.95") {
            $strReturn .= $this->update_3195_320();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.0") {
            $strReturn .= $this->update_320_3209();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.0.9") {
            $strReturn .= $this->update_3209_321();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.1") {
            $strReturn .= $this->update_321_330();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.0") {
            $strReturn .= $this->update_330_3301();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.0.1") {
            $strReturn .= $this->update_3301_331();
        }
        
        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.1") {
            $strReturn .= $this->update_331_3318();
        }
        
        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.1.8") {
            $strReturn .= $this->update_3318_340();
        }

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

    private function update_319_3195() {
        $strReturn = "Updating 3.1.9 to 3.1.95...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("samplecontent", "3.1.95");
        return $strReturn;
    }

    private function update_3195_320() {
        $strReturn = "Updating 3.1.95 to 3.2.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("samplecontent", "3.2.0");
        return $strReturn;
    }

    private function update_320_3209() {
        $strReturn = "Updating 3.2.0 to 3.2.0.9...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("samplecontent", "3.2.0.9");
        return $strReturn;
    }

    private function update_3209_321() {
        $strReturn = "Updating 3.2.0.9 to 3.2.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("samplecontent", "3.2.1");
        return $strReturn;
    }

    private function update_321_330() {
        $strReturn = "Updating 3.2.1 to 3.3.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("samplecontent", "3.3.0");
        return $strReturn;
    }

    private function update_330_3301() {
        $strReturn = "Updating 3.3.0 to 3.3.0.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("samplecontent", "3.3.0.1");
        return $strReturn;
    }

    private function update_3301_331() {
        $strReturn = "Updating 3.3.0.1 to 3.3.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("samplecontent", "3.3.1");
        return $strReturn;
    }
    
    private function update_331_3318() {
        $strReturn = "Updating 3.3.1 to 3.3.1.8...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("samplecontent", "3.3.1.8");
        return $strReturn;
    }
    
    private function update_3318_340() {
        $strReturn = "Updating 3.3.1.8 to 3.4.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("samplecontent", "3.4.0");
        return $strReturn;
    }

}
?>