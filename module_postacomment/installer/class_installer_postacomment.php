<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

/**
 * Class providing an install for the postacomment module
 *
 * @package module_postacomment
 * @moduleId _postacomment_modul_id_
 */
class class_installer_postacomment extends class_installer_base implements interface_installer_removable {

    public function install() {
		$strReturn = "";
        $objManager = new class_orm_schemamanager();
		$strReturn .= "Installing table postacomment...\n";
        $objManager->createTable("class_module_postacomment_post");

		//register the module
		$strSystemID = $this->registerModule(
            "postacomment",
		    _postacomment_modul_id_,
		    "class_module_postacomment_portal.php",
		    "class_module_postacomment_admin.php",
            $this->objMetadata->getStrVersion(),
		    true,
		    "class_module_postacomment_portal_xml.php");

		//modify default rights to allow guests to post
		$strReturn .= "Modifying modules' rights node...\n";
        class_carrier::getInstance()->getObjRights()->addGroupToRight(class_module_system_setting::getConfigValue("_guests_group_id_"), $strSystemID, "right1");
        class_carrier::getInstance()->getObjRights()->addGroupToRight(class_module_system_setting::getConfigValue("_guests_group_id_"), $strSystemID, "right2");


        $strReturn .= "Registering postacomment-element...\n";
        //check, if not already existing
        $objElement = class_module_pages_element::getElement("postacomment");
        if($objElement == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("postacomment");
            $objElement->setStrClassAdmin("class_element_postacomment_admin.php");
            $objElement->setStrClassPortal("class_element_postacomment_portal.php");
            $objElement->setIntCachetime(-1);
            $objElement->setIntRepeat(0);
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
            $objElement->updateObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else {
            $strReturn .= "Element already installed!...\n";
        }

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
        $objElement = class_module_pages_element::getElement("postacomment");
        if($objElement != null) {
            $strReturn .= "Deleting page-element 'postacomment'...\n";
            $objElement->deleteObject();
        }
        else {
            $strReturn .= "Error finding page-element 'postacomment', aborting.\n";
            return false;
        }

        /** @var class_module_postacomment_post $objOneObject */
        foreach(class_module_postacomment_post::getObjectList() as $objOneObject) {
            $strReturn .= "Deleting object '".$objOneObject->getStrDisplayName()."' ...\n";
            if(!$objOneObject->deleteObject()) {
                $strReturn .= "Error deleting object, aborting.\n";
                return false;
            }
        }

        //delete the module-node
        $strReturn .= "Deleting the module-registration...\n";
        $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle(), true);
        if(!$objModule->deleteObject()) {
            $strReturn .= "Error deleting module, aborting.\n";
            return false;
        }

        //delete the tables
        foreach(array("postacomment") as $strOneTable) {
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
        if($arrModule["module_version"] == "3.4.2") {
            $strReturn .= $this->update_342_349();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "3.4.9") {
            $strReturn .= $this->update_349_40();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.0") {
            $strReturn .= $this->update_40_41();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.1") {
            $strReturn = "Updating 4.1 to 4.2...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.2");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("postacomment", "4.2");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.2") {
            $strReturn = "Updating 4.2 to 4.3...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.3");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("postacomment", "4.3");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.3") {
            $strReturn = "Updating 4.3 to 4.4...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.4");
            $this->updateElementVersion($this->objMetadata->getStrTitle(), "4.4");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.4") {
            $strReturn = "Updating 4.4 to 4.5...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.5");
            $this->updateElementVersion($this->objMetadata->getStrTitle(), "4.5");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.5") {
            $strReturn = "Updating 4.5 to 4.5.1...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.5.1");
            $this->updateElementVersion($this->objMetadata->getStrTitle(), "4.5.1");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.5.1") {
            $strReturn = "Updating 4.5.1 to 4.6...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.6");
            $this->updateElementVersion($this->objMetadata->getStrTitle(), "4.6");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6") {
            $strReturn = "Updating to 4.7...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.7");
            $this->updateElementVersion($this->objMetadata->getStrTitle(), "4.7");
        }

        return $strReturn."\n\n";
	}




    private function update_342_349() {
        $strReturn = "Updating 3.4.1.1 to 3.4.9...\n";

        $strReturn .= "Adding classes for existing records...\n";


        $strReturn .= "Postacomment\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."postacomment, "._dbprefix_."system WHERE system_id = postacomment_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_postacomment_post', $arrOneRow["system_id"] ) );
        }

        $strReturn .= "Removing old notify-constant\n";
        $strQuery = "DELETE FROM "._dbprefix_."system_config WHERE system_config_name = ? ";
        $this->objDB->_pQuery($strQuery, array("_postacomment_notify_mail_"));

        $strReturn .= "Setting aspect assignments...\n";
        if(class_module_system_aspect::getAspectByName("content") != null) {
            $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(class_module_system_aspect::getAspectByName("content")->getSystemid());
            $objModule->updateObjectToDb();
        }

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "3.4.9");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("postacomment", "3.4.9");

        return $strReturn;
    }

    private function update_349_40() {
        $strReturn = "Updating 3.4.9 to 4.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.0");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("postacomment", "4.0");
        return $strReturn;
    }

    private function update_40_41() {
        $strReturn = "Updating 4.0 to 4.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("postacomment", "4.1");
        return $strReturn;
    }

}
