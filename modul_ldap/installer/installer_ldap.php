<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

/**
 * Class providing an installer for the monita module
 *
 * @package modul_ldap
 * @author sidler@mulchprod.de
 */
class class_installer_ldap extends class_installer_base implements interface_installer {

	public function __construct() {
        $arrModule = array();
		$arrModule["version"] 		  = "3.4.1";
		$arrModule["name"] 			  = "ldap";
		$arrModule["name_lang"] 	  = "Modul LDAP";
		$arrModule["moduleId"] 		  = _ldap_module_id_;
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system");
	}

    public function getMinSystemVersion() {
	    return "3.4.0.2";
	}

	public function hasPostInstalls() {
        return false;
	}

   public function install() {
		$strReturn = "";

        $strReturn .= "Installing table group_ldap...\n";
		$arrFields = array();
        $arrFields["group_ldap_id"]                                     = array("char20", false);
		$arrFields["group_ldap_dn"]                                     = array("text", true);

		if(!$this->objDB->createTable("user_group_ldap", $arrFields, array("group_ldap_id")))
			$strReturn .= "An error occured! ...\n";
        
        $strReturn .= "Installing table user_ldap...\n";
		$arrFields = array();
        $arrFields["user_ldap_id"]                                     = array("char20", false);
		$arrFields["user_ldap_email"]                                  = array("char254", true);
		$arrFields["user_ldap_familyname"]                             = array("char254", true);
		$arrFields["user_ldap_givenname"]                              = array("char254", true);
		$arrFields["user_ldap_dn"]                                     = array("text", true);

		if(!$this->objDB->createTable("user_ldap", $arrFields, array("user_ldap_id")))
			$strReturn .= "An error occured! ...\n";


		//register the module
		$this->registerModule("ldap", _ldap_module_id_, "", "", $this->arrModule["version"], false);
		return $strReturn;

	}

	public function postInstall() {
		return "";
	}

	public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";


        return $strReturn."\n\n";
	}

}
?>