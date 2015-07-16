<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Class providing an installer for the samplecontent.
 * Samplecontent is not installed as a module, it just creates a few default entries
 * for other modules and installes a few sample-templates
 *
 * @package module_samplecontent
 * @moduleId _samplecontent_modul_id_
 */
class class_installer_samplecontent extends class_installer_base implements interface_installer_removable {


    private $strContentLanguage;


	public function __construct() {
		parent::__construct();

		//set the correct language
        $this->strContentLanguage = $this->objSession->getAdminLanguage();
	}

    public function install() {
		$strReturn = "Installing ".$this->objMetadata->getStrTitle()."...\n";

		//Register the module
        $strReturn .= "\nRegistering module\n";
        $this->registerModule($this->objMetadata->getStrTitle(), _samplecontent_modul_id_, "", "", $this->objMetadata->getStrVersion() , false);

		//search for installers available
        $arrInstaller = class_resourceloader::getInstance()->getFolderContent("/installer", array(".php"), false, function($strFile) {
            return strpos($strFile, "installer_sc_") !== false;
        });

        asort($arrInstaller);

        $strReturn .= "Loading installers...\n";
        foreach ($arrInstaller as $strOneInstaller) {
            $strReturn .= "\n\nInstaller found: ".$strOneInstaller."\n";
            include_once(_realpath_.array_search($strOneInstaller, $arrInstaller));
            //Creating an object....
            $strClass = "class_".str_replace(".php", "", $strOneInstaller);
            /** @var $objInstaller interface_sc_installer|class_installer_base */
            $objInstaller = new $strClass();

            if($objInstaller instanceof interface_sc_installer ) {
                $strModule = $objInstaller->getCorrespondingModule();
                $strReturn .= "Module ".$strModule."...\n";
                $objModule = class_module_system_module::getModuleByName($strModule);
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

        if(!file_exists(_realpath_."/favicon.ico")) {
            if(!copy(class_resourceloader::getInstance()->getCorePathForModule("module_samplecontent", true)."/module_samplecontent/favicon.ico.root", _realpath_."/favicon.ico"))
                $strReturn .= "<b>Copying the favicon.ico.root to top level failed!!!</b>";
        }


		return $strReturn;
	}

    /**
     * Validates whether the current module/element is removable or not.
     * This is the place to trigger special validations and consistency checks going
     * beyond the common metadata-dependencies.
     *
     * @return bool
     */
    public function isRemovable() {
        return true;
    }

    /**
     * Removes the elements / modules handled by the current installer.
     * Use the reference param to add a human readable logging.
     *
     * @param string &$strReturn
     *
     * @return bool
     */
    public function remove(&$strReturn) {

        $strReturn .= "Removing the samplecontent module will NOT removed the contents created during installation! Only the module itself will be removed!\n";

        //delete the module-node
        $strReturn .= "Deleting the module-registration...\n";
        $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle(), true);
        if(!$objModule->deleteObjectFromDatabase()) {
            $strReturn .= "Error deleting module, aborting.\n";
            return false;
        }


        return true;
    }


    public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);

        $strReturn .= "Version found:\n\t Module: ".$arrModule["module_name"].", Version: ".$arrModule["module_version"]."\n\n";

          $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "3.4.9") {
            $strReturn .= "Updating 3.4.9 to 4.0...\n";
            $this->updateModuleVersion("samplecontent", "4.0");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.0") {
            $strReturn .= "Updating 4.0 to 4.1...\n";
            $this->updateModuleVersion("samplecontent", "4.1");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.1") {
            $strReturn .= "Updating 4.1 to 4.2...\n";
            $this->updateModuleVersion("samplecontent", "4.2");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.2") {
            $strReturn .= "Updating 4.2 to 4.3...\n";
            $this->updateModuleVersion("samplecontent", "4.3");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.3") {
            $strReturn .= "Updating 4.3 to 4.4...\n";
            $this->updateModuleVersion("samplecontent", "4.4");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.4") {
            $strReturn .= "Updating 4.4 to 4.5...\n";
            $this->updateModuleVersion("samplecontent", "4.5");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.5") {
            $strReturn .= "Updating 4.5 to 4.6...\n";
            $this->updateModuleVersion("samplecontent", "4.6");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6") {
            $strReturn .= "Updating to 4.7...\n";
            $this->updateModuleVersion("samplecontent", "4.7");
        }

        return $strReturn;
	}




}
