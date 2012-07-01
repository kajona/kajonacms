<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: installer_stats.php 4156 2011-10-29 12:02:37Z sidler $                                         *
********************************************************************************************************/


/**
 * Installer handling the installation of the stats module
 *
 * @package module_stats
 */
class class_installer_stats extends class_installer_base implements interface_installer {

    /**
     * Constructor
     *
     */
	public function __construct() {
        $this->objMetadata = new class_module_packagemanager_metadata();
        $this->objMetadata->autoInit(uniStrReplace(array(DIRECTORY_SEPARATOR."installer", _realpath_), array("", ""), __DIR__));
        $this->setArrModuleEntry("moduleId", _stats_modul_id_);

        parent::__construct();

	}


	public function install() {
        $strReturn = "";

		//Stats table -----------------------------------------------------------------------------------
		$strReturn .= "Installing table stats...\n";

		$arrFields = array();
		$arrFields["stats_id"] 		= array("char20", false);
		$arrFields["stats_ip"] 		= array("char20", true);
		$arrFields["stats_hostname"]= array("char254", true);
		$arrFields["stats_date"] 	= array("int", true);
		$arrFields["stats_page"] 	= array("char254", true);
		$arrFields["stats_language"]= array("char10", true);
		$arrFields["stats_referer"] = array("char254", true);
		$arrFields["stats_browser"] = array("char254", true);
		$arrFields["stats_session"] = array("char100", true);

        if(!$this->objDB->createTable(
            "stats_data",
            $arrFields,
            array("stats_id"),
            array("stats_date", "stats_hostname", "stats_page", "stats_referer", "stats_browser"),
            false
        )
        ) {
            $strReturn .= "An error occured! ...\n";
        }

        $strReturn .= "Installing table ip2country...\n";

        $arrFields = array();
		$arrFields["ip2c_ip"] 		= array("char20", false);
		$arrFields["ip2c_name"] 	= array("char100", false);

		if(!$this->objDB->createTable("stats_ip2country", $arrFields, array("ip2c_ip"), array(), false))
			$strReturn .= "An error occured! ...\n";


		//register module
		$this->registerModule(
            "stats",
            _stats_modul_id_,
            "class_module_stats_portal.php",
            "class_module_stats_admin.php",
            $this->objMetadata->getStrVersion(),
            true,
            "",
            "class_module_stats_admin_xml.php"
        );

		$strReturn .= "Registering system-constants...\n";
		//Number of rows in the login-log
		$this->registerConstant("_stats_nrofrecords_", "25", class_module_system_setting::$int_TYPE_INT, _stats_modul_id_);
		$this->registerConstant("_stats_duration_online_", "300", class_module_system_setting::$int_TYPE_INT, _stats_modul_id_);
		$this->registerConstant("_stats_exclusionlist_", _webpath_, class_module_system_setting::$int_TYPE_STRING, _stats_modul_id_);
		return $strReturn;
	}


	public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModul = $this->getModuleData($this->objMetadata->getStrTitle(), false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.4.2") {
            $strReturn .= $this->update_342_349();
        }

        return $strReturn."\n\n";
	}




    private function update_342_349() {
        $strReturn = "Updating 3.4.2 to 3.4.9...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("stats", "3.4.9");
        return $strReturn;
    }


}
