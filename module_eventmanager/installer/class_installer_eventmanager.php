<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

/**
 * Class providing an installer for the eventmanager module
 *
 * @package module_eventmanager
 * @author sidler@mulchprod.de
 * @moduleId _eventmanager_module_id_
 */
class class_installer_eventmanager extends class_installer_base implements interface_installer_removable {

    public function install() {
		$strReturn = "";
        $objManager = new class_orm_schemamanager();

		$strReturn .= "Installing table em_event...\n";
        $objManager->createTable("class_module_eventmanager_event");

        $strReturn .= "Installing table em_participant...\n";
        $objManager->createTable("class_module_eventmanager_participant");

		//register the module
		$strSystemID = $this->registerModule(
            "eventmanager",
            _eventmanager_module_id_,
            "class_module_eventmanager_portal.php",
            "class_module_eventmanager_admin.php",
            $this->objMetadata->getStrVersion(),
            true
        );

        //modify default rights to allow guests to participate
		$strReturn .= "Modifying modules' rights node...\n";
        class_carrier::getInstance()->getObjRights()->addGroupToRight(class_module_system_setting::getConfigValue("_guests_group_id_"), $strSystemID, "right1");

        $strReturn .= "Registering eventmanager-element...\n";
        //check, if not already existing
        if(class_module_pages_element::getElement("eventmanager") == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("eventmanager");
            $objElement->setStrClassAdmin("class_element_eventmanager_admin.php");
            $objElement->setStrClassPortal("class_element_eventmanager_portal.php");
            $objElement->setIntCachetime(-1);
            $objElement->setIntRepeat(1);
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
            $objElement->updateObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else
            $strReturn .= "Element already installed!...\n";

        $strReturn .= "Setting aspect assignments...\n";
        if(class_module_system_aspect::getAspectByName("content") != null) {
            $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(class_module_system_aspect::getAspectByName("content")->getSystemid());
            $objModule->updateObjectToDb();
        }

		return $strReturn;

	}

    /**
     * Validates whether the current module/element is removable or not.
     * This is the place to trigger special validations and consistency checks going
     * beyond the common metadata-dependencies.
     *
     * @return bool
     */
    public function isRemovable() {
        return true;
    }

    /**
     * Removes the elements / modules handled by the current installer.
     * Use the reference param to add a human readable logging.
     *
     * @param string &$strReturn
     *
     * @return bool
     */
    public function remove(&$strReturn) {

        //delete the page-element
        $objElement = class_module_pages_element::getElement("eventmanager");
        if($objElement != null) {
            $strReturn .= "Deleting page-element 'eventmanager'...\n";
            $objElement->deleteObjectFromDatabase();
        }
        else {
            $strReturn .= "Error finding page-element 'eventmanager', aborting.\n";
            return false;
        }

        /** @var class_module_eventmanager_event $objOneObject */
        foreach(class_module_eventmanager_event::getObjectList() as $objOneObject) {
            $strReturn .= "Deleting object '".$objOneObject->getStrDisplayName()."' ...\n";
            if(!$objOneObject->deleteObjectFromDatabase()) {
                $strReturn .= "Error deleting object, aborting.\n";
                return false;
            }
        }

        //delete the module-node
        $strReturn .= "Deleting the module-registration...\n";
        $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle(), true);
        if(!$objModule->deleteObjectFromDatabase()) {
            $strReturn .= "Error deleting module, aborting.\n";
            return false;
        }

        //delete the tables
        foreach(array("em_event", "em_participant") as $strOneTable) {
            $strReturn .= "Dropping table ".$strOneTable."...\n";
            if(!$this->objDB->_pQuery("DROP TABLE ".$this->objDB->encloseTableName(_dbprefix_.$strOneTable)."", array())) {
                $strReturn .= "Error deleting table, aborting.\n";
                return false;
            }

        }

        return true;
    }




    public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        $strReturn .= "Version found:\n\t Module: ".$arrModule["module_name"].", Version: ".$arrModule["module_version"]."\n\n";

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.0") {
            $strReturn .= $this->update_40_41();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.1") {
            $strReturn .= "Updating 4.1 to 4.2...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("eventmanager", "4.2");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("eventmanager", "4.2");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.2") {
            $strReturn .= $this->update_42_421();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.2.1") {
            $strReturn .= $this->update_421_422();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.2.2") {
            $strReturn .= "Updating 4.2.2 to 4.3...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("eventmanager", "4.3");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("eventmanager", "4.3");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.3") {
            $strReturn .= "Updating 4.3 to 4.4...\n";
            $this->updateModuleVersion("eventmanager", "4.4");
            $this->updateElementVersion("eventmanager", "4.4");
        }


        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.4") {
            $strReturn .= "Updating 4.4 to 4.5...\n";
            $this->updateModuleVersion("eventmanager", "4.5");
            $this->updateElementVersion("eventmanager", "4.5");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.5") {
            $strReturn .= "Updating 4.5 to 4.6...\n";
            $this->updateModuleVersion("eventmanager", "4.6");
            $this->updateElementVersion("eventmanager", "4.6");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6") {
            $strReturn .= "Updating to 4.7...\n";
            $this->updateModuleVersion("eventmanager", "4.7");
            $this->updateElementVersion("eventmanager", "4.7");
        }

        return $strReturn."\n\n";
	}

    private function update_40_41() {
        $strReturn = "Updating 4.0 to 4.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("eventmanager", "4.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("eventmanager", "4.1");
        return $strReturn;
    }


    private function update_42_421() {
        $strReturn = "Updating 4.2 to 4.2.1...\n";

        $strReturn .= "Adding new status column...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."em_event")."
                            ADD ".$this->objDB->encloseColumnName("em_ev_eventstatus")." ".$this->objDB->getDatatype("int")." NULL";
        if(!$this->objDB->_pQuery($strQuery, array()))
            $strReturn .= "An error occurred! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("eventmanager", "4.2.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("eventmanager", "4.2.1");
        return $strReturn;
    }

    private function update_421_422() {
        $strReturn = "Updating 4.2.1 to 4.2.2...\n";

        $strReturn .= "Adding new user columns...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."em_participant")."
                            ADD ".$this->objDB->encloseColumnName("em_pt_userid")." ".$this->objDB->getDatatype("char20")." NULL";
        if(!$this->objDB->_pQuery($strQuery, array()))
            $strReturn .= "An error occurred! ...\n";

        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."em_participant")."
                            ADD ".$this->objDB->encloseColumnName("em_pt_status")." ".$this->objDB->getDatatype("int")." NULL";
        if(!$this->objDB->_pQuery($strQuery, array()))
            $strReturn .= "An error occurred! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("eventmanager", "4.2.2");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("eventmanager", "4.2.2");
        return $strReturn;
    }

}
