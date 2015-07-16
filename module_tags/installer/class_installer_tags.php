<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

/**
 * Class providing an install for the tags module
 *
 * @package module_tags
 * @author sidler@mulchprod.de
 * @moduleId _tags_modul_id_
 */
class class_installer_tags extends class_installer_base implements interface_installer_removable {

    public function install() {
		$strReturn = "";
        $objManager = new class_orm_schemamanager();

		//tags_tag --------------------------------------------------------------------------------------
		$strReturn .= "Installing table tags_tag...\n";
        $objManager->createTable("class_module_tags_tag");

		$strReturn .= "Installing table tags_member...\n";
        $arrFields = array();
		$arrFields["tags_memberid"]     = array("char20", false);
		$arrFields["tags_systemid"] 	= array("char20", false);
		$arrFields["tags_tagid"]        = array("char20", false);
		$arrFields["tags_attribute"]    = array("char254", true);
		$arrFields["tags_owner"]        = array("char20", true);

		if(!$this->objDB->createTable("tags_member", $arrFields, array("tags_memberid"), array("tags_systemid", "tags_tagid", "tags_attribute", "tags_owner")))
			$strReturn .= "An error occurred! ...\n";

        $strReturn .= "Installing table tags_favorite...\n";
        $objManager->createTable("class_module_tags_favorite");

		//register the module
		$this->registerModule(
            "tags",
            _tags_modul_id_,
            "",
            "class_module_tags_admin.php",
            $this->objMetadata->getStrVersion(),
            true,
            "",
            "class_module_tags_admin_xml.php"
        );

		$strReturn .= "Registering system-constants...\n";
        $this->registerConstant("_tags_defaultprivate_", "false", class_module_system_setting::$int_TYPE_BOOL, _tags_modul_id_);

        //Register the element
        $strReturn .= "Registering tags-element...\n";

        //check, if not already existing
        if(class_module_system_module::getModuleByName("pages") !== null && class_module_pages_element::getElement("tags") == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("tags");
            $objElement->setStrClassAdmin("class_element_tags_admin.php");
            $objElement->setStrClassPortal("class_element_tags_portal.php");
            $objElement->setIntCachetime(3600*24*30);
            $objElement->setIntRepeat(0);
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
            $objElement->updateObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else {
            $strReturn .= "Element already installed!...\n";
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

        $strReturn .= "Removing settings...\n";
        if(class_module_system_setting::getConfigByName("_tags_defaultprivate_") != null)
            class_module_system_setting::getConfigByName("_tags_defaultprivate_")->deleteObjectFromDatabase();

        //delete the page-element
        if(class_module_system_module::getModuleByName("pages") !== null && class_module_pages_element::getElement("tags") != null) {
            $objElement = class_module_pages_element::getElement("tags");
            if($objElement != null) {
                $strReturn .= "Deleting page-element 'tags'...\n";
                $objElement->deleteObjectFromDatabase();
            }
            else {
                $strReturn .= "Error finding page-element 'guestbook', tags.\n";
                return false;
            }
        }

        /** @var class_module_tags_favorite $objOneObject */
        foreach(class_module_tags_favorite::getObjectList() as $objOneObject) {
            $strReturn .= "Deleting object '".$objOneObject->getStrDisplayName()."' ...\n";
            if(!$objOneObject->deleteObjectFromDatabase()) {
                $strReturn .= "Error deleting object, aborting.\n";
                return false;
            }
        }

        /** @var class_module_tags_tag $objOneObject */
        foreach(class_module_tags_tag::getObjectList() as $objOneObject) {
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
        foreach(array("tags_tag", "tags_member", "tags_favorite") as $strOneTable) {
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
            $this->objDB->flushQueryCache();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.1") {
            $strReturn .= $this->update_41_42();
            $this->objDB->flushQueryCache();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.2") {
            $strReturn .= "Updating 4.2 to 4.3...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.3");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("tags", "4.3");
            $this->objDB->flushQueryCache();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.3") {
            $strReturn .= "Updating 4.3 to 4.4...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.4");
            $this->updateElementVersion($this->objMetadata->getStrTitle(), "4.4");
            $this->objDB->flushQueryCache();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.4") {
            $strReturn .= "Updating 4.4 to 4.5...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.5");
            $this->updateElementVersion($this->objMetadata->getStrTitle(), "4.5");
            $this->objDB->flushQueryCache();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.5") {
            $strReturn .= "Updating 4.5 to 4.6...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.6");
            $this->updateElementVersion($this->objMetadata->getStrTitle(), "4.6");
            $this->objDB->flushQueryCache();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6") {
            $strReturn .= "Updating to 4.7...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.7");
            $this->updateElementVersion($this->objMetadata->getStrTitle(), "4.7");
            $this->objDB->flushQueryCache();
        }

        return $strReturn."\n\n";
	}


    private function update_40_41() {
        $strReturn = "Updating 4.0 to 4.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("tags", "4.1");
        return $strReturn;
    }

    private function update_41_42() {
        $strReturn = "Updating 4.1 to 4.2...\n";

        $strReturn .= "Registering tags private mode setting\n";
        $this->registerConstant("_tags_defaultprivate_", "false", class_module_system_setting::$int_TYPE_BOOL, _tags_modul_id_);

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.2");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("tags", "4.2");
        return $strReturn;
    }

}
