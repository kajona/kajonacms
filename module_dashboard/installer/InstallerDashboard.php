<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                        *
********************************************************************************************************/

namespace Kajona\Dashboard\Installer;

use Kajona\Dashboard\System\DashboardWidget;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerInterface;
use Kajona\System\System\OrmSchemamanager;
use Kajona\System\System\SystemModule;

/**
 * Installer for the system-module
 *
 * @package module_dashboard
 *
 * @moduleId _dashboard_module_id_
 */
class InstallerDashboard extends InstallerBase implements InstallerInterface {

	public function install() {
	    $strReturn = "";

        $objManager = new OrmSchemamanager();
		$strReturn .= "Installing table dashboard...\n";
        $objManager->createTable(DashboardWidget::class);

        //the dashboard
        $this->registerModule("dashboard", _dashboard_module_id_, "", "DashboardAdmin.php", $this->objMetadata->getStrVersion());

        $strReturn .= "Setting dashboard to pos 1 in navigation.../n";
        $objModule = SystemModule::getModuleByName("dashboard");
        $objModule->setAbsolutePosition(1);


        return $strReturn;
	}


	public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);

        $strReturn .= "Version found:\n\t Module: ".$arrModule["module_name"].", Version: ".$arrModule["module_version"]."\n\n";


        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6") {
            $strReturn .= "Updating to 4.7...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("dashboard", "4.7");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7") {
            $strReturn .= $this->update_47_475();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7.5") {
            $strReturn .= "Updating to 5.0...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("dashboard", "5.0");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "5.0") {
            $strReturn .= "Updating to 5.1...\n";
            $this->updateModuleVersion("dashboard", "5.1");
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
