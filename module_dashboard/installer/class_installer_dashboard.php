<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                        *
********************************************************************************************************/


/**
 * Installer for the system-module
 *
 * @package module_dashboard
 */
class class_installer_dashboard extends class_installer_base implements interface_installer {

    private $strContentLanguage;

	public function __construct() {

        $this->objMetadata = new class_module_packagemanager_metadata();
        $this->objMetadata->autoInit(uniStrReplace(array(DIRECTORY_SEPARATOR."installer", _realpath_), array("", ""), __DIR__));

        $this->setArrModuleEntry("moduleId", _dashboard_module_id_);
		parent::__construct();

		//set the correct language
		$this->strContentLanguage = $this->objSession->getAdminLanguage();
	}


	public function install() {
	    $strReturn = "";


		//dashboard & widgets ---------------------------------------------------------------------------
		$strReturn .= "Installing table dashboard...\n";

		$arrFields = array();
		$arrFields["dashboard_id"] 			= array("char20", false);
		$arrFields["dashboard_column"]		= array("char254", true);
		$arrFields["dashboard_user"] 		= array("char20", true);
		$arrFields["dashboard_aspect"] 	    = array("char254", true);
		$arrFields["dashboard_class"] 	    = array("char254", true);
		$arrFields["dashboard_content"] 	= array("text", true);

		if(!$this->objDB->createTable("dashboard", $arrFields, array("dashboard_id")))
			$strReturn .= "An error occured! ...\n";
        //the dashboard
        $this->registerModule("dashboard", _dashboard_module_id_, "", "class_module_dashboard_admin.php", $this->objMetadata->getStrVersion(), false, "", "class_module_dashboard_admin_xml.php");

        //up till now, the default widgets are still missing - create them.
        $arrUsers = class_module_user_user::getAllUsers();
        foreach($arrUsers as $objOneUser) {
            $objDashboard = new class_module_dashboard_widget();
            $objDashboard->createInitialWidgetsForUser($objOneUser->getSystemid());
        }

        return $strReturn;
	}


	public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModul = $this->getModuleData($this->objMetadata->getStrTitle(), false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = $this->getModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.0") {
            $strReturn .= $this->update_340_3401();
            $this->objDB->flushQueryCache();
        }

        $arrModul = $this->getModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.0.1") {
            $strReturn .= $this->update_3401_3402();
            $this->objDB->flushQueryCache();
        }

        $arrModul = $this->getModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.0.2") {
            $strReturn .= $this->update_3402_341();
            $this->objDB->flushQueryCache();
        }

        $arrModul = $this->getModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.1") {
            $strReturn .= $this->update_341_349();
            $this->objDB->flushQueryCache();
        }

        $arrModul = $this->getModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.1.1") {
            $strReturn .= $this->update_341_349();
            $this->objDB->flushQueryCache();
        }

        $arrModul = $this->getModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9") {
            $strReturn .= $this->update_349_3491();
            $this->objDB->flushQueryCache();
        }

        return $strReturn."\n\n";
	}


     private function update_340_3401() {
        $strReturn = "Updating 3.4.0 to 3.4.0.1...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("dashboard", "3.4.0.1");
        return $strReturn;
    }


    private function update_3401_3402() {
        $strReturn = "Updating 3.4.0.1 to 3.4.0.2...\n";

        $this->updateModuleVersion("dashboard", "3.4.0.2");
        return $strReturn;
    }

    private function update_3402_341() {
        $strReturn = "Updating 3.4.0.2 to 3.4.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("dashboard", "3.4.1");
        return $strReturn;
    }


    private function update_341_349() {
        $strReturn = "Updating 3.4.1 to 3.4.9...\n";

        $strReturn .= "Updating model-classes...\n";

        $strReturn .= "Dashboard\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."dashboard, "._dbprefix_."system WHERE system_id = dashboard_id", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_dashboard_widget', $arrOneRow["system_id"] ) );
        }

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("dashboard", "3.4.9");
        return $strReturn;
    }

    private function update_349_3491() {
        $strReturn = "Updating 3.4.9 to 3.4.9.1...\n";

        //drop widget id
        //add class, content
        $strReturn .= "Updating dashboard table...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."dashboard")."
                            ADD ".$this->objDB->encloseColumnName("dashboard_class")." ".$this->objDB->getDatatype("char254")." NULL,
                            ADD ".$this->objDB->encloseColumnName("dashboard_content")." ".$this->objDB->getDatatype("text")." NULL";
        if(!$this->objDB->_pQuery($strQuery, array()))
            $strReturn .= "An error occured! ...\n";

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
            $strReturn .= "An error occured! ...\n";

        $strQuery = "DROP TABLE ".$this->objDB->encloseTableName(_dbprefix_."adminwidget")."";
        if(!$this->objDB->_pQuery($strQuery, array()))
            $strReturn .= "An error occured! ...\n";

        $this->objDB->flushQueryCache();

        $strReturn .= "Moving all dashboard entries to the correct module-node\n";
        $arrWidgets = class_module_dashboard_widget::getAllWidgets();
        foreach($arrWidgets as $objOneWidget)
            $objOneWidget->updateObjectToDb(_dashboard_module_id_);


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("dashboard", "3.4.9.1");
        return $strReturn;
    }


}
