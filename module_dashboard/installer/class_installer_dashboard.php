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
        if($arrModule["module_version"] == "3.4.2" || $arrModule["module_version"] == "3.4.2.2") {
            $strReturn .= $this->update_342_3491();
            $this->objDB->flushQueryCache();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "3.4.9.1") {
            $strReturn .= $this->update_3491_3492();
            $this->objDB->flushQueryCache();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "3.4.9.2") {
            $strReturn .= $this->update_3492_40();
            $this->objDB->flushQueryCache();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.0") {
            $strReturn .= $this->update_40_41();
            $this->objDB->flushQueryCache();
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

        return $strReturn."\n\n";
	}


    private function update_342_3491() {
        $strReturn = "Updating 3.4.9 to 3.4.9.1...\n";

        $strReturn .= "Updating model-classes...\n";

        $strReturn .= "Dashboard\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."dashboard, "._dbprefix_."system WHERE system_id = dashboard_id", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_dashboard_widget', $arrOneRow["system_id"] ) );
        }

        //drop widget id
        //add class, content
        $strReturn .= "Updating dashboard table...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."dashboard")."
                            ADD ".$this->objDB->encloseColumnName("dashboard_class")." ".$this->objDB->getDatatype("char254")." NULL,
                            ADD ".$this->objDB->encloseColumnName("dashboard_content")." ".$this->objDB->getDatatype("text")." NULL";
        if(!$this->objDB->_pQuery($strQuery, array()))
            $strReturn .= "An error occurred! ...\n";

        $strReturn .= "Migrating existing records...\n";
        $arrWidgetContent = $this->objDB->getPArray("SELECT * FROM "._dbprefix_."adminwidget", array());

        foreach($arrWidgetContent as $arrOneWidget) {

            $strQuery = "UPDATE "._dbprefix_."dashboard
                            SET dashboard_class = ?,
                                dashboard_content = ?
                          WHERE dashboard_widgetid = ?";

            $this->objDB->_pQuery($strQuery, array($arrOneWidget["adminwidget_class"], $arrOneWidget["adminwidget_content"], $arrOneWidget["adminwidget_id"]), array(true, false));

            //delete old records
            $this->objDB->_pQuery("DELETE FROM "._dbprefix_."adminwidget WHERE adminwidget_id = ?", array($arrOneWidget["adminwidget_id"]));
            $this->deleteSystemRecord($arrOneWidget["adminwidget_id"]);

            $strReturn .= "Migrated and deleted dashboard entry ".$arrOneWidget["adminwidget_id"]."\n";
        }


        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."dashboard")."
                            DROP ".$this->objDB->encloseColumnName("dashboard_widgetid")." ";
        if(!$this->objDB->_pQuery($strQuery, array()))
            $strReturn .= "An error occurred! ...\n";

        $strQuery = "DROP TABLE ".$this->objDB->encloseTableName(_dbprefix_."adminwidget")."";
        if(!$this->objDB->_pQuery($strQuery, array()))
            $strReturn .= "An error occurred! ...\n";

        $this->objDB->flushQueryCache();

        $strReturn .= "Moving all dashboard entries to the correct module-node\n";
        $arrWidgets = class_module_dashboard_widget::getAllWidgets();
        foreach($arrWidgets as $objOneWidget)
            $objOneWidget->updateObjectToDb(_dashboard_module_id_);


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("dashboard", "3.4.9.1");
        return $strReturn;
    }


    private function update_3491_3492() {
        $strReturn = "Updating 3.4.9.1 to 3.4.9.2...\n";

        $strReturn .= "Updating widget db-structure...\n";
        $arrUsers = class_module_user_user::getObjectList();
        $arrAspects = class_module_system_aspect::getObjectList();


        $strReturn .= "Enabling dashboard in navigation...\n";
        $objModule = class_module_system_module::getModuleByName("dashboard");
        $objModule->setIntNavigation(1);
        $objModule->updateObjectToDb();

        if(@ini_get("max_execution_time") < 3600 && @ini_get("max_execution_time") > 0)
            @ini_set("max_execution_time", "3600");


        foreach($arrUsers as $objOneUser) {
            $strReturn .= "  user: ".$objOneUser->getStrUsername()."\n";
            foreach($arrAspects as $objOneAspect) {
                $strReturn .= "    aspect: ".$objOneAspect->getStrName()."\n";

                $arrParams = array($objOneUser->getSystemid(), $objOneAspect->getSystemid());

                $strAspectWhere = " AND dashboard_aspect = ? ";
                if($objOneAspect->getBitDefault()) {
                    $strAspectWhere = " AND ( dashboard_aspect = ? OR dashboard_aspect IS NULL OR dashboard_aspect LIKE ? OR dashboard_aspect = '' ) ";
                    $arrParams[] = $objOneAspect->getSystemid();
                }

                $strQuery = "SELECT system_id
                              FROM "._dbprefix_."dashboard,
                                   "._dbprefix_."system
                             WHERE dashboard_id = system_id
                               AND dashboard_user = ?
                               ".$strAspectWhere;

                $arrRows = $this->objDB->getPArray($strQuery, $arrParams);
                foreach($arrRows as $arrOneRow) {
                    $objWidget = new class_module_dashboard_widget($arrOneRow["system_id"]);

                    if($objWidget->getStrClass() != "") {
                        $strReturn .= "     updating widget ".$objWidget->getSystemid()."\n";
                        $objWidget->updateObjectToDb(class_module_dashboard_widget::getWidgetsRootNodeForUser($objOneUser->getSystemid(), $objOneAspect->getSystemid()));
                    }
                }


            }

        }


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("dashboard", "3.4.9.2");
        return $strReturn;
    }

    private function update_3492_40() {
        $strReturn = "Updating 3.4.9.2 to 4.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("dashboard", "4.0");
        return $strReturn;
    }

    private function update_40_41() {
        $strReturn = "Updating 4.0 to 4.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("dashboard", "4.1");
        return $strReturn;
    }

}
