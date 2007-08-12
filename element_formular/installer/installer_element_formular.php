<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	installer_element_formular.php																		*
* 	Installer of the form element																		*																										*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

require_once(_realpath_."/installer/class_installer_base.php");
require_once(_realpath_."/installer/interface_installer.php");


/**
 * Installer to install a form-element (provides a basic contact form)
 *
 * @package modul_pages
 */
class class_installer_element_formular extends class_installer_base implements interface_installer {

    /**
     * Constructor
     *
     */
	public function __construct() {
		$arrModule["version"] 		= "3.0.2";
		$arrModule["name"] 			= "element_formular";
		$arrModule["name_lang"] 	= "Element Form";
		$arrModule["nummer2"] 		= _pages_inhalte_modul_id_;
		$arrModule["tabellen"][]    = _dbprefix_."element_formular";
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}

	public function hasPostInstalls() {
	    //needed: pages
	    try {
		    $objModule = class_modul_system_module::getModuleByName("pages");
		}
		catch (class_exception $objE) {
		    return false;
		}

	    //check, if not already existing
		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='form'";
		$arrRow = $this->objDB->getRow($strQuery);
        if(isset($arrRow["COUNT(*)"]) && $arrRow["COUNT(*)"] == 0)
            return true;

        return false;
	}

    public function install() {
    }

    public function postInstall() {
		$strReturn = "";

		//Table for page-element
		$strReturn .= "Installing formular-element table...\n";
		$strQuery = "CREATE TABLE IF NOT EXISTS `"._dbprefix_."element_formular` (
                        `content_id` VARCHAR( 20 ) NOT NULL ,
                        `formular_class` VARCHAR( 255 ) ,
                        `formular_email` VARCHAR( 255 ) ,
                        `formular_success` TEXT,
                        `formular_error` TEXT,
                        PRIMARY KEY ( `content_id` )
                        ) ";

		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering formular-element...\n";
		//check, if not already existing
		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='form'";
		$arrRow = $this->objDB->getRow($strQuery);
		if($arrRow["COUNT(*)"] == 0) {
			$strQuery = "INSERT INTO "._dbprefix_."element
							(element_id, element_name, element_class_portal, element_class_admin, element_repeat) VALUES
							('".$this->generateSystemid()."', 'form', 'class_element_formular.php', 'class_element_formular.php', 1)";
			$this->objDB->_query($strQuery);
			$strReturn .= "Element registered...\n";
		}
		else {
			$strReturn .= "Element already installed!...\n";
		}
		return $strReturn;
	}


	public function update() {
	}
}
?>