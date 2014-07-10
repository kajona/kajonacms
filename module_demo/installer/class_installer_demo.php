<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

/**
 * Class providing an installer for the demo module
 *
 * @package module_demo
 * @author tim.kiefer@kojikui.de
 *
 * @moduleId _demo_module_id_
 */
class class_installer_demo extends class_installer_base implements interface_installer_removable {

    public function install() {
        $strReturn = "";

        $objManager = new class_orm_schemamanager();
        $strReturn .= "Installing table demo_demo...\n";
        $objManager->createTable("class_module_demo_demo");

        $strReturn .= "Installing table demo_other_object...\n";
        $objManager->createTable("class_module_demo_other_object");

        $strReturn .= "Installing table demo_sub_object...\n";
        $objManager->createTable("class_module_demo_sub_object");


        //register the module
        $strSystemID = $this->registerModule(
            "demo",
            _demo_module_id_,
            "class_module_demo_portal.php",
            "class_module_demo_admin.php",
            $this->objMetadata->getStrVersion(),
            true
        );

        //modify default rights to allow guests to vote
        $strReturn .= "Modifying modules' rights node...\n";
        $this->objRights->addGroupToRight(_guests_group_id_, $strSystemID, "right1");

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
        /** @var class_module_demo_demo $objOneObject*/
        foreach(class_module_demo_demo::getObjectList() as $objOneObject) {
            $strReturn .= "Deleting object '".$objOneObject->getStrDisplayName()."' ...\n";
            if(!$objOneObject->deleteObject()) {
                $strReturn .= "Error deleting object, aborting.\n";
                return false;
            }
        }

        /** @var class_module_demo_other_object $objOneObject*/
        foreach(class_module_demo_other_object::getObjectList() as $objOneObject) {
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
        foreach(array("demo_demo", "demo_other_object", "demo_sub_object") as $strOneTable) {
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
        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        $strReturn .= "Version found:\n\t Module: " . $arrModul["module_name"] . ", Version: " . $arrModul["module_version"] . "\n\n";

        return $strReturn . "\n\n";
    }

}
