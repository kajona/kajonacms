<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: installer_ldap.php 4735 2012-06-26 11:52:02Z sidler $                                            *
********************************************************************************************************/

/**
 * Class providing an installer for the monita module
 *
 * @package module_ldap
 * @author sidler@mulchprod.de
 */
class class_installer_ldap extends class_installer_base implements interface_installer {

	public function __construct() {
        $this->objMetadata = new class_module_packagemanager_metadata();
        $this->objMetadata->autoInit(uniStrReplace(array(DIRECTORY_SEPARATOR."installer", _realpath_), array("", ""), __DIR__));
        $this->setArrModuleEntry("moduleId", _ldap_module_id_);
        parent::__construct();
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
		$this->registerModule("ldap", _ldap_module_id_, "", "", $this->objMetadata->getStrVersion(), false);
		return $strReturn;

	}

	public function postInstall() {
		return "";
	}

	public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.2") {
            $strReturn .= $this->update_342_349();
        }

        return $strReturn."\n\n";
	}

    private function update_342_349() {
        $strReturn = "Updating 3.4.2 to 3.4.9...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("ldap", "3.4.9");
        return $strReturn;
    }

}
