<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: installer_tags.php 4413 2012-01-03 19:38:11Z sidler $                                          *
********************************************************************************************************/

/**
 * Class providing an install for the templatemanager module
 *
 * @package module_templatemanager
 */
class class_installer_templatemanager extends class_installer_base implements interface_installer {

	public function __construct() {

        $this->setArrModuleEntry("version", "3.4.9");
        $this->setArrModuleEntry("moduleId", _templatemanager_module_id_);
        $this->setArrModuleEntry("name", "templatemanager");
        $this->setArrModuleEntry("name_lang", "Module Templatemanager");

		parent::__construct();
	}

	public function getNeededModules() {
	    return array("system");
	}

    public function getMinSystemVersion() {
	    return "3.4.9";
	}


    public function install() {
		$strReturn = "";


        $strReturn .= "Installing table templatepacks...\n";

        $arrFields = array();
        $arrFields["templatepack_id"] 		    = array("char20", false);
        $arrFields["templatepack_name"] 	    = array("char254", true);

        if(!$this->objDB->createTable("templatepacks", $arrFields, array("templatepack_id")))
            $strReturn .= "An error occured! ...\n";

		//register the module
		$this->registerModule(
            "templatemanager",
            _templatemanager_module_id_,
            "",
            "class_module_templatemanager_admin.php",
            $this->arrModule["version"],
            true
        );

		$strReturn .= "Registering system-constants...\n";
        $this->registerConstant("_templatemanager_defaultpack_", "", class_module_system_setting::$int_TYPE_STRING, _templatemanager_module_id_);

		return $strReturn;

	}


	public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";


        return $strReturn."\n\n";
	}

}
