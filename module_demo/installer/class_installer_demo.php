<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: installer_votings.php 4042 2011-07-25 17:37:44Z sidler $                                       *
********************************************************************************************************/

/**
 * Class providing an installer for the demo module
 *
 * @package module_demo
 * @author tim.kiefer@kojikui.de
 */
class class_installer_demo extends class_installer_base implements interface_installer {

    public function __construct() {
        $this->objMetadata = new class_module_packagemanager_metadata();
        $this->objMetadata->autoInit(uniStrReplace(array(DIRECTORY_SEPARATOR . "installer", _realpath_), array("", ""), __DIR__));
        $this->setArrModuleEntry("moduleId", _demo_module_id_);
        parent::__construct();
    }

    public function install() {
        $strReturn = "";

        //demo obj-------------------------------------------------------------------------------------
        $strReturn .= "Installing table demo_demo...\n";

        $arrFields = array();
        $arrFields["demo_id"] = array("char20", false);
        $arrFields["demo_title"] = array("char254", true);
        $arrFields["demo_float"] = array("double", true);
        $arrFields["demo_int"] = array("int", true);

        if(!$this->objDB->createTable("demo_demo", $arrFields, array("demo_id"))) {
            $strReturn .= "An error occured! ...\n";
        }

        //other demo obj-------------------------------------------------------------------------------------
        $strReturn .= "Installing table demo_other_object...\n";

        $arrFields = array();
        $arrFields["other_object_id"] = array("char20", false);
        $arrFields["other_object_title"] = array("char254", true);
        $arrFields["other_object_date"] = array("date", true);
        $arrFields["other_object_float"] = array("double", true);

        if(!$this->objDB->createTable("demo_other_object", $arrFields, array("other_object_id"))) {
            $strReturn .= "An error occured! ...\n";
        }

        //sub demo obj-------------------------------------------------------------------------------------
        $strReturn .= "Installing table demo_sub_object...\n";

        $arrFields = array();
        $arrFields["sub_object_id"] = array("char20", false);
        $arrFields["sub_object_title"] = array("char254", true);
        $arrFields["sub_object_int"] = array("int", true);

        if(!$this->objDB->createTable("demo_sub_object", $arrFields, array("sub_object_id"))) {
            $strReturn .= "An error occured! ...\n";
        }


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


    public function update() {
        $strReturn = "";
        //check installed version and to which version we can update
        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        $strReturn .= "Version found:\n\t Module: " . $arrModul["module_name"] . ", Version: " . $arrModul["module_version"] . "\n\n";

        return $strReturn . "\n\n";
    }

}
