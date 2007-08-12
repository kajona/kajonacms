<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	installer_languages.php																				*
* 	Installer of the language module																	*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                      *
********************************************************************************************************/

require_once(_realpath_."/installer/class_installer_base.php");
require_once(_realpath_."/installer/interface_installer.php");

/**
 * Class providing an installer for the language module
 *
 * @package modul_languages
 */
class class_installer_languages extends class_installer_base implements interface_installer {

	public function __construct() {
		$arrModule["version"] 		  = "3.0.2";
		$arrModule["name"] 			  = "languages";
		$arrModule["class_admin"]  	  = "class_modul_languages_admin";
		$arrModule["file_admin"] 	  = "class_modul_languages_admin.php";
		$arrModule["class_portal"] 	  = "class_modul_languages_portal";
		$arrModule["file_portal"] 	  = "class_modul_languages_portal.php";
		$arrModule["name_lang"] 	  = "Module Languages";
		$arrModule["moduleId"] 		  = _languages_modul_id_;

		$arrModule["tabellen"][]      = _dbprefix_."languages";
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}

	public function hasPostInstalls() {
	    $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='languageswitch'";
	    $arrRow = $this->objDB->getRow($strQuery);
        if($arrRow["COUNT(*)"] == 0)
            return true;

        return false;
	}


   public function install() {

		$strReturn = "";
		//Tabellen anlegen

		//news cat-------------------------------------------------------------------------------------
		$strReturn .= "Installing table languages...\n";

		$strQuery = "CREATE TABLE IF NOT EXISTS `"._dbprefix_."languages` (
                        `language_id` VARCHAR( 20 ) NOT NULL ,
                        `language_name` VARCHAR( 255 ) ,
                        `language_default` INT( 2 ) ,
                         PRIMARY KEY ( `language_id` )
                    ) ";

		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";



		//register the module
		$strSystemID = $this->registerModule("languages", _languages_modul_id_, "class_modul_languages_portal", "class_modul_languages_portal.php",
		                                      "class_modul_languages_admin", "class_modul_languages_admin.php", $this->arrModule["version"] , true);

		return $strReturn;

	}

	public function postInstall() {
		$strReturn = "";

		//Register the element
		$strReturn .= "Registering languageswitch-element...\n";
		//check, if not already existing
		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='languageswitch'";
		$arrRow = $this->objDB->getRow($strQuery);
		if($arrRow["COUNT(*)"] == 0) {
			$strQuery = "INSERT INTO "._dbprefix_."element
							(element_id, element_name, element_class_portal, element_class_admin, element_repeat) VALUES
							('".$this->generateSystemid()."', 'languageswitch', 'class_element_languageswitch.php', 'class_element_languageswitch.php', 0)";
			$this->objDB->_query($strQuery);
			$strReturn .= "Element registered...\n";
		}
		else {
				$strReturn .= "Element already installed!...\n";
		}
		return $strReturn;
	}


	public function update() {
	    $strReturn = "";
        //check the version we have and to what version to update
        $arrModul = $this->getModuleData($this->arrModule["name"], false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "2.2.0.0") {
            $strReturn .= $this->update_2200_300();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.0") {
            $strReturn .= $this->update_300_301();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.1") {
            $strReturn .= $this->update_301_302();
        }

        return $strReturn."\n\n";
	}

	private function update_2200_300() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 2.2.0.0 to 3.0.0...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("languages", "3.0.0");

        return $strReturn;
	}

	private function update_300_301() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 3.0.0 to 3.0.1...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("languages", "3.0.1");

        return $strReturn;
	}

	private function update_301_302() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 3.0.1 to 3.0.2...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("languages", "3.0.2");

        return $strReturn;
	}

}
?>