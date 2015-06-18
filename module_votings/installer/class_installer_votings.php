<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

/**
 * Class providing an installer for the votings module
 *
 * @package module_votings
 * @author sidler@mulchprod.de
 * @moduleId _votings_module_id_
 */
class class_installer_votings extends class_installer_base implements interface_installer_removable {

    public function install() {
		$strReturn = "";
        $objManager = new class_orm_schemamanager();
		$strReturn .= "Installing table votings_voting...\n";
        $objManager->createTable("class_module_votings_voting");

        $strReturn .= "Installing table votings_answer...\n";
        $objManager->createTable("class_module_votings_answer");

		//register the module
		$strSystemID = $this->registerModule(
            $this->objMetadata->getStrTitle(),
            _votings_module_id_,
            "class_module_votings_portal.php",
            "class_module_votings_admin.php",
            $this->objMetadata->getStrVersion(),
            true
        );

        //modify default rights to allow guests to vote
		$strReturn .= "Modifying modules' rights node...\n";
        class_carrier::getInstance()->getObjRights()->addGroupToRight(class_module_system_setting::getConfigValue("_guests_group_id_"), $strSystemID, "right1");

        $strReturn .= "Registering votings-element...\n";
        if(class_module_pages_element::getElement("votings") == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("votings");
            $objElement->setStrClassAdmin("class_element_votings_admin.php");
            $objElement->setStrClassPortal("class_element_votings_portal.php");
            $objElement->setIntCachetime(-1);
            $objElement->setIntRepeat(1);
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
        $objElement = class_module_pages_element::getElement("votings");
        if($objElement != null) {
            $strReturn .= "Deleting page-element 'votings'...\n";
            $objElement->deleteObjectFromDatabase();
        }
        else {
            $strReturn .= "Error finding page-element 'votings', aborting.\n";
            return false;
        }

        /** @var class_module_votings_voting $objOneObject */
        foreach(class_module_votings_voting::getObjectList() as $objOneObject) {
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
        foreach(array("votings_voting", "votings_answer") as $strOneTable) {
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
        if($arrModule["module_version"] == "1.0") {
            $strReturn .= $this->update_10_11();
            $this->objDB->flushQueryCache();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "1.1") {
            $strReturn .= $this->update_11_12();
            $this->objDB->flushQueryCache();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "1.2") {
            $strReturn .= "Updating 1.2 to 1.3...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("votings", "1.3");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("votings", "1.3");
            $this->objDB->flushQueryCache();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "1.3") {
            $strReturn .= "Updating 1.3 to 1.4...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("votings", "1.4");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("votings", "1.4");
            $this->objDB->flushQueryCache();
        }


        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "1.4") {
            $strReturn .= "Updating 1.4 to 1.5...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("votings", "1.5");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("votings", "1.5");
            $this->objDB->flushQueryCache();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "1.5") {
            $strReturn .= "Updating 1.5 to 1.6...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("votings", "1.6");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("votings", "1.6");
            $this->objDB->flushQueryCache();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "1.6") {
            $strReturn .= "Updating to 1.7...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("votings", "1.7");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("votings", "1.7");
            $this->objDB->flushQueryCache();
        }

        return $strReturn."\n\n";
	}
    
    private function update_10_11() {
        $strReturn = "Updating 1.0 to 1.1...\n";


        $strReturn .= "Adding classes for existing records...\n";

        $strReturn .= "Votings\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."votings_voting, "._dbprefix_."system WHERE system_id = votings_voting_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_votings_voting', $arrOneRow["system_id"] ) );
        }

        $strReturn .= "Answerrs\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."votings_answer, "._dbprefix_."system WHERE system_id = votings_answer_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_votings_answer', $arrOneRow["system_id"] ) );
        }

        $strReturn .= "Setting aspect assignments...\n";
        if(class_module_system_aspect::getAspectByName("content") != null) {
            $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(class_module_system_aspect::getAspectByName("content")->getSystemid());
            $objModule->updateObjectToDb();
        }

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("votings", "1.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("votings", "1.1");
        return $strReturn;
    }

    private function update_11_12() {
        $strReturn = "Updating 1.1 to 1.2...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("votings", "1.2");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("votings", "1.2");
        return $strReturn;
    }

}
