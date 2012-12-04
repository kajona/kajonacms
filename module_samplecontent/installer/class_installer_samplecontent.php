<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
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
 */
class class_installer_samplecontent extends class_installer_base implements interface_installer {


    private $strContentLanguage;


	public function __construct() {
        $this->objMetadata = new class_module_packagemanager_metadata();
        $this->objMetadata->autoInit(uniStrReplace(array(DIRECTORY_SEPARATOR."installer", _realpath_), array("", ""), __DIR__));

        $this->setArrModuleEntry("moduleId", _samplecontent_modul_id_);

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
        $arrInstaller = class_resourceloader::getInstance()->getFolderContent("/installer", array(".php"));

        foreach($arrInstaller as $strPath => $strFile)
            if(strpos($strFile, "installer_sc_") === false)
                unset($arrInstaller[$strPath]);

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
            if(!copy(_realpath_."/core/module_samplecontent/favicon.ico.root", _realpath_."/favicon.ico"))
                $strReturn .= "<b>Copying the favicon.ico.root to top level failed!!!</b>";
        }


		return $strReturn;
	}

	public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);

        $strReturn .= "Version found:\n\t Module: ".$arrModule["module_name"].", Version: ".$arrModule["module_version"]."\n\n";

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "3.4.2") {
            $strReturn .= $this->update_342_349();
        }

        return $strReturn;
	}


    private function update_342_349() {
        $strReturn = "Updating 3.4.2 to 3.4.9...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("samplecontent", "3.4.9");
        return $strReturn;
    }

}
