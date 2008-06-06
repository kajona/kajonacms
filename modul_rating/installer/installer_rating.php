<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	installer_rating.php																			    *
* 	Installer of the rating module																        *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id: installer_rating.php 1965 2008-03-09 12:43:03Z sidler $                                        *
********************************************************************************************************/

require_once(_systempath_."/class_installer_base.php");
require_once(_systempath_."/interface_installer.php");
require_once(_systempath_."/class_modul_pages_element.php");

/**
 * Class providing an installer for the rating module
 *
 * @package modul_rating
 */
class class_installer_rating extends class_installer_base implements interface_installer {

	public function __construct() {
		$arrModule["version"] 		  = "3.1.1";
		$arrModule["name"] 			  = "rating";
		$arrModule["name_lang"]       = "Module Ratings";
		$arrModule["moduleId"] 		  = _rating_modul_id_;

		$arrModule["table"]           = _dbprefix_."rating";
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}
	
    public function getMinSystemVersion() {
	    return "3.1.0";
	}

	public function hasPostInstalls() {
        return false;
	}

   public function install() {
		$strReturn = "";
		//Tabellen anlegen

		//rating ----------------------------------------------------------------------------------
		$strReturn .= "Installing table rating...\n";

		$arrFields = array();
		$arrFields["rating_id"] 		= array("char20", false);
		$arrFields["rating_systemid"] 	= array("char20", true);
		$arrFields["rating_checksum"] 	= array("char254", true);
		$arrFields["rating_rate"]       = array("double", true);
		$arrFields["rating_hits"]       = array("int", true);
		
		if(!$this->objDB->createTable("rating", $arrFields, array("rating_id")))
			$strReturn .= "An error occured! ...\n";
			
			
		$strReturn .= "Installing table rating_history...\n";

        $arrFields = array();
        $arrFields["rating_history_id"]     = array("char20", false);
        $arrFields["rating_history_rating"] = array("char20", true);
        $arrFields["rating_history_user"]   = array("char20", true);
        
        if(!$this->objDB->createTable("rating_history", $arrFields, array("rating_history_id")))
            $strReturn .= "An error occured! ...\n";	

        
		//register the module
		$strSystemID = $this->registerModule("rating", 
		                                     _rating_modul_id_, 
		                                     "", 
		                                     "", 
		                                     $this->arrModule["version"], 
		                                     false, 
		                                     "class_modul_rating_portal_xml.php");
		                                     
        $strReturn .= "Module registered. Module-ID: ".$strSystemID." \n";
		return $strReturn;

	}

	public function postInstall() {
		return "";
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