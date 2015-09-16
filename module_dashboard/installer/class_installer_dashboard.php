<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                        *
********************************************************************************************************/


/**
 * Installer for the system-module
 *
 * @package module_dashboard
 *
 * @moduleId _dashboard_module_id_
 */
class class_installer_dashboard extends class_installer_base implements interface_installer {

	public function install() {
	    $strReturn = "";

        $objManager = new class_orm_schemamanager();
		$strReturn .= "Installing table dashboard...\n";
        $objManager->createTable("class_module_dashboard_widget");

        //the dashboard
        $this->registerModule("dashboard", _dashboard_module_id_, "", "class_module_dashboard_admin.php", $this->objMetadata->getStrVersion(), true, "", "class_module_dashboard_admin_xml.php");

        $strReturn .= "Setting dashboard to pos 1 in navigation.../n";
        $objModule = class_module_system_module::getModuleByName("dashboard");
        $objModule->setAbsolutePosition(1);


        return $strReturn;
	}


	public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);

        $strReturn .= "Version found:\n\t Module: ".$arrModule["module_name"].", Version: ".$arrModule["module_version"]."\n\n";

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.0") {
            $strReturn = "Updating 4.0 to 4.1...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("dashboard", "4.1");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.1") {
            $strReturn .= "Updating 4.1 to 4.2...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("dashboard", "4.2");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.2") {
            $strReturn .= "Updating 4.2 to 4.3...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("dashboard", "4.3");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.3") {
            $strReturn .= "Updating 4.3 to 4.4...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("dashboard", "4.4");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.4") {
            $strReturn .= "Updating 4.4 to 4.5...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("dashboard", "4.5");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.5") {
            $strReturn .= "Updating 4.5 to 4.6...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("dashboard", "4.6");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6") {
            $strReturn .= "Updating to 4.7...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("dashboard", "4.7");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7") {
            $strReturn .= $this->update_47_475();
        }

        return $strReturn."\n\n";
	}


    private function update_47_475() {
        $strReturn = "Updating database indexes\n";

        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."dashboard")." ADD INDEX ( ".$this->objDB->encloseColumnName("dashboard_user")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."dashboard")." ADD INDEX ( ".$this->objDB->encloseColumnName("dashboard_aspect")." ) ", array());

        $strReturn .= "Updating module-versions...\n";
        $this->objDB->flushQueryCache();
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.7.5");

        return $strReturn;
    }
}
