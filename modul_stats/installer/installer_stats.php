<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	installer_stats.php																					*
* 	installer to handle the installation of the stats-module                                            *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                          *
********************************************************************************************************/

require_once(_systempath_."/class_installer_base.php");
require_once(_systempath_."/interface_installer.php");


/**
 * Installer handling the installation of the stats module
 *
 * @package modul_stats
 */
class class_installer_stats extends class_installer_base implements interface_installer {

    /**
     * Constructor
     *
     */
	public function __construct() {
		$arrModule["version"] 		= "3.0.95";
		$arrModule["name"] 			= "stats";
		$arrModule["class_admin"] 	= "class_modul_stats_admin";
		$arrModule["file_admin"] 	= "class_modul_stats_admin.php";
		$arrModule["class_portal"] 	= "class_modul_stats_portal";
		$arrModule["file_portal"] 	= "class_modul_stats_portal.php";
		$arrModule["name_lang"] 	= "Module Stats";
		$arrModule["moduleId"] 		= _stats_modul_id_;
		$arrModule["tabellen"][]    = _dbprefix_."stats_daten";
		parent::__construct($arrModule);

		//increase script-runtime
		set_time_limit(3600);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}
	
    public function getMinSystemVersion() {
	    return "3.0.9";
	}

	public function hasPostInstalls() {
        return false;
	}

	public function install() {
	    //Nur installieren, wenn noch nicht vorhanden
		if(count($this->objDB->getTables()) > 0) {
			$arrModul = $this->getModuleData($this->arrModule["name"]);
			if(count($arrModul) > 0)
				return "<strong>Module already installed!!!</strong><br /><br />";
		}

		$strReturn = "Installing ".$this->arrModule["name_lang"]."...\n";
		//Tabellen anlegen

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

		if(!$this->objDB->createTable("stats_data", $arrFields, array("stats_id"), array("stats_date"), false))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "Installing table ip2country...\n";
        
        $arrFields = array();
		$arrFields["ip_from"] 		= array("double", false);
		$arrFields["ip_to"] 		= array("double", false);
		$arrFields["country_code2"] = array("char10", false);
		$arrFields["country_code3"] = array("char10", false);
		$arrFields["country_name"] 	= array("char100", false);

		if(!$this->objDB->createTable("stats_ip2country", $arrFields, array("ip_from", "ip_to"), array(), false))
			$strReturn .= "An error occured! ...\n";

		$strReturn .= "Importing ip2country data...\n"	;
		$strReturn .= $this->importIp2CountryData("/installer/ip2country_full_1.csv");
		$strReturn .= $this->importIp2CountryData("/installer/ip2country_full_2.csv");
		$strReturn .= $this->importIp2CountryData("/installer/ip2country_full_3.csv");
		$strReturn .= $this->importIp2CountryData("/installer/ip2country_full_4.csv");
		$strReturn .= $this->importIp2CountryData("/installer/ip2country_full_5.csv");
		$strReturn .= $this->importIp2CountryData("/installer/ip2country_full_6.csv");
		$strReturn .= $this->importIp2CountryData("/installer/ip2country_full_7.csv");
		$strReturn .= $this->importIp2CountryData("/installer/ip2country_full_8.csv");


		//register module
		$strUserID = $this->registerModule("stats", _stats_modul_id_, "class_modul_stats_portal.php", "class_modul_stats_admin.php", $this->arrModule["version"], true);

		$strReturn .= "Registering system-constants...\n";
		//Number of rows in the login-log
		$this->registerConstant("_stats_anzahl_liste_", "25", class_modul_system_setting::$int_TYPE_INT, _stats_modul_id_);
		$this->registerConstant("_stats_zeitraum_online_", "300", class_modul_system_setting::$int_TYPE_INT, _stats_modul_id_);
		$this->registerConstant("_stats_ausschluss_", _webpath_, class_modul_system_setting::$int_TYPE_STRING, _stats_modul_id_);
		return $strReturn;
	}

	public function postInstall() {
    }

    private function importIp2CountryData($strFilename) {
        $strReturn = "";
        include_once(_systempath_."/class_csv.php");
        $objCsv = new class_csv();
        $objCsv->setStrFilename($strFilename);
        $objCsv->setTextEncloser("\"");
        $objCsv->setArrMapping(array(0 => "ip_from", 1 => "ip_to", 2 => "country_code2", 3 => "country_code3", 4 => "country_name"));
        $objCsv->createArrayFromFile();
        $arrData = $objCsv->getArrData();
        $strReturn .= "Importing ".count($arrData)." records...\n";
        //import.... but as a single transaction (yepp, even if the table itself is not supporting tx)
        $this->objDB->transactionBegin();
        foreach ($arrData as $arrOneRecord) {
        	$strQuery = "INSERT INTO "._dbprefix_."stats_ip2country
        	            (ip_from, ip_to, country_code2, country_code3, country_name) VALUES
        	            (".$arrOneRecord["ip_from"].", ".$arrOneRecord["ip_to"].", '".$arrOneRecord["country_code2"]."',
        	            '".$arrOneRecord["country_code3"]."', '".dbsafeString($arrOneRecord["country_name"])."')";
        	$this->objDB->_query($strQuery);
        	$this->objDB->flushQueryCache();
        }
		$this->objDB->transactionCommit();
        return $strReturn;
    }

	public function update() {
	    $strReturn = "";
        //check the version we have and to what version to update
        $arrModul = $this->getModuleData($this->arrModule["name"], false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "2.2.0.0") {
            $strReturn .= $this->update_2200_2201();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "2.2.0.1") {
            $strReturn .= $this->update_2201_300();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.0") {
            $strReturn .= $this->update_300_301();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.1") {
            $strReturn .= $this->update_301_302();
        }
        
		$arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.2") {
            $strReturn .= $this->update_302_309();
        }
        
		$arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.9") {
            $strReturn .= $this->update_309_3095();
        }

        return $strReturn."\n\n";
	}

	private function update_2200_2201() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 2.2.0.0 to 2.2.0.1...\n";

        $strReturn .= "Altering table stats...\n";
        $strQuery = "ALTER TABLE `"._dbprefix_."stats_data`
                       ADD `stats_language` VARCHAR( 100 ) NULL";

        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ... \n";


        //Update the module-records to 2.1.1.0
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("stats", "2.2.0.1");

        return $strReturn;
	}

	private function update_2201_300() {
	    $strReturn = "";
	    $strReturn .= "Altering tables...\n";
	    $strQuery = "
	    ALTER TABLE `"._dbprefix_."stats_data`
	        CHANGE `stats_ip` `stats_ip` VARCHAR( 20 ),
            CHANGE `stats_language` `stats_language` VARCHAR( 10 ) ";

	    if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ... \n";

        //Update the module-records to 3.0.0
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("stats", "3.0.0");

        return $strReturn;
	}

	private function update_300_301() {
	    $strReturn = "";
        $strReturn .= "Updating 3.0.0 to 3.0.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("stats", "3.0.1");

        return $strReturn;
	}


    private function update_301_302() {
	    $strReturn = "";
        $strReturn .= "Updating 3.0.1 to 3.0.2...\n";

        $strReturn .= "Installing table ip2country...\n";

		$arrFields = array();
		$arrFields["ip_from"] 		= array("double", false);
		$arrFields["ip_to"] 		= array("double", false);
		$arrFields["country_code2"] = array("char10", false);
		$arrFields["country_code3"] = array("char10", false);
		$arrFields["country_name"] 	= array("char100", false);

		if(!$this->objDB->createTable("stats_ip2country", $arrFields, array("ip_from", "ip_to"), array(), false))
			$strReturn .= "An error occured! ...\n";


		$strReturn .= "Importing ip2country data...\n"	;
		$strReturn .= $this->importIp2CountryData("/installer/ip2country_full_1.csv");
		$strReturn .= $this->importIp2CountryData("/installer/ip2country_full_2.csv");
		$strReturn .= $this->importIp2CountryData("/installer/ip2country_full_3.csv");
		$strReturn .= $this->importIp2CountryData("/installer/ip2country_full_4.csv");
		$strReturn .= $this->importIp2CountryData("/installer/ip2country_full_5.csv");
		$strReturn .= $this->importIp2CountryData("/installer/ip2country_full_6.csv");
		$strReturn .= $this->importIp2CountryData("/installer/ip2country_full_7.csv");
		$strReturn .= $this->importIp2CountryData("/installer/ip2country_full_8.csv");


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("stats", "3.0.2");

        return $strReturn;
	}
	
    private function update_302_309() {
        $strReturn = "";
        $strReturn .= "Updating 3.0.2 to 3.0.9...\n";
        
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("stats", "3.0.9");

        return $strReturn;
    }
    
	private function update_309_3095() {
        $strReturn = "";
        $strReturn .= "Updating 3.0.9 to 3.0.95...\n";
        
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("stats", "3.0.95");

        return $strReturn;
    }



}
?>