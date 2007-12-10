<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	installer_postacomment.php																			*
* 	Installer of the postacomment module																*																										*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

require_once(_systempath_."/class_installer_base.php");
require_once(_systempath_."/interface_installer.php");
require_once(_systempath_."/class_modul_pages_element.php");

/**
 * Class providing an install for the postacomment module
 *
 * @package modul_postacomment
 */
class class_installer_postacomment extends class_installer_base implements interface_installer {

	public function __construct() {
		$arrModule["version"] 		  = "3.0.9";
		$arrModule["name"] 			  = "postacomment";
		$arrModule["class_admin"]  	  = "class_modul_postacomment_admin";
		$arrModule["file_admin"] 	  = "class_modul_postacomment_admin.php";
		$arrModule["class_portal"] 	  = "class_modul_postacomment_portal";
		$arrModule["file_portal"] 	  = "class_modul_postacomment_portal.php";
		$arrModule["name_lang"] 	  = "Module Postacomment";
		$arrModule["moduleId"] 		  = _postacomment_modul_id_;

		$arrModule["table"]           = _dbprefix_."postacomment";
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}
	
    public function getMinSystemVersion() {
	    return "3.0.9";
	}

	public function hasPostInstalls() {
	    $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='postacomment'";
	    $arrRow = $this->objDB->getRow($strQuery);
        if($arrRow["COUNT(*)"] == 0)
            return true;

        return false;
	}

   public function install() {
		$strReturn = "";
		//Tabellen anlegen

		//postacomment ----------------------------------------------------------------------------------
		$strReturn .= "Installing table postacomment...\n";

		$arrFields = array();
		$arrFields["postacomment_id"] 		= array("char20", false);
		$arrFields["postacomment_date"] 	= array("int", true);
		$arrFields["postacomment_page"] 	= array("char254", true);
		$arrFields["postacomment_language"] = array("char20", true);
		$arrFields["postacomment_systemid"] = array("char20", true);
		$arrFields["postacomment_username"] = array("char254", true);
		$arrFields["postacomment_title"] 	= array("char254", true);
		$arrFields["postacomment_comment"] 	= array("text", true);
		
		if(!$this->objDB->createTable("postacomment", $arrFields, array("postacomment_id")))
			$strReturn .= "An error occured! ...\n";


		//register the module
		$strSystemID = $this->registerModule("postacomment", 
		                                     _postacomment_modul_id_, 
		                                     "class_modul_postacomment_portal.php", 
		                                     "class_modul_postacomment_admin.php", 
		                                     $this->arrModule["version"], 
		                                     true, 
		                                     "class_modul_postacomment_portal_xml.php");

		//modify default rights to allow guests to post
		$strReturn .= "Modifying modules' rights knot...\n";
		$this->objRights->addGroupToRight(_gaeste_gruppe_id_, $strSystemID, "right1");
		
		$strReturn .= "Registering system-constants...\n";

		return $strReturn;

	}

	public function postInstall() {
		$strReturn = "";

		//Uses universal table, so no extra table to create

		//Register the element
		$strReturn .= "Registering postacomment-element...\n";
		//check, if not already existing
		try {
		    $objElement = class_modul_pages_element::getElement("postacomment");
		}
		catch (class_exception $objEx)  {
		}
		if($objElement == null) {
		    $objElement = new class_modul_pages_element();
		    $objElement->setStrName("postacomment");
		    $objElement->setStrClassAdmin("class_element_postacomment.php");
		    $objElement->setStrClassPortal("class_element_postacomment.php");
		    $objElement->setIntCachetime(-1);
		    $objElement->setIntRepeat(0);
			$objElement->saveObjectToDb();
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

        return $strReturn."\n\n";
	}


}
?>