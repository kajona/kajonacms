<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                        *
********************************************************************************************************/

/**
 * Class providing an installer for the rating module
 *
 * @package modul_rating
 */
class class_installer_rating extends class_installer_base implements interface_installer {

	public function __construct() {
        $arrModule = array();
		$arrModule["version"] 		  = "3.3.1";
		$arrModule["name"] 			  = "rating";
		$arrModule["name_lang"]       = "Module Ratings";
		$arrModule["moduleId"] 		  = _rating_modul_id_;

		$arrModule["table"]           = _dbprefix_."rating";
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}
	
    public function getMinSystemVersion() {
	    return "3.2.1";
	}

	public function hasPostInstalls() {
        return false;
	}

   public function install() {
		$strReturn = "";
		//Tabellen anlegen

		//rating ----------------------------------------------------------------------------------
		$strReturn .= "Installing table rating...\n";

		$arrFields = array();
		$arrFields["rating_id"] 		= array("char20", false);
		$arrFields["rating_systemid"] 	= array("char20", true);
		$arrFields["rating_checksum"] 	= array("char254", true);
		$arrFields["rating_rate"]       = array("double", true);
		$arrFields["rating_hits"]       = array("int", true);
		
		if(!$this->objDB->createTable("rating", $arrFields, array("rating_id")))
			$strReturn .= "An error occured! ...\n";
			
			
		$strReturn .= "Installing table rating_history...\n";

        $arrFields = array();
        $arrFields["rating_history_id"]     = array("char20", false);
        $arrFields["rating_history_rating"] = array("char20", true);
        $arrFields["rating_history_user"]   = array("char20", true);
        $arrFields["rating_history_timestamp"]= array("int", true);
        $arrFields["rating_history_value"]  = array("double", true);
        
        if(!$this->objDB->createTable("rating_history", $arrFields, array("rating_history_id")))
            $strReturn .= "An error occured! ...\n";	

        
		//register the module
		$strSystemID = $this->registerModule("rating", 
		                                     _rating_modul_id_, 
		                                     "", 
		                                     "", 
		                                     $this->arrModule["version"], 
		                                     false, 
		                                     "class_modul_rating_portal_xml.php");
		                                     
        $strReturn .= "Module registered. Module-ID: ".$strSystemID." \n";
		return $strReturn;

	}

	public function postInstall() {
		return "";
	}


	public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModul = $this->getModuleData($this->arrModule["name"], false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";
        
	    $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.1.1") {
            $strReturn .= $this->update_311_319();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.1.9") {
            $strReturn .= $this->update_319_3195();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.1.95") {
            $strReturn .= $this->update_3195_320();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.0") {
            $strReturn .= $this->update_320_3209();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.0.9") {
            $strReturn .= $this->update_3209_321();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.1") {
            $strReturn .= $this->update_321_3291();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.91") {
            $strReturn .= $this->update_3291_330();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.0") {
            $strReturn .= $this->update_330_3301();
        }
        
        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.0.1") {
            $strReturn .= $this->update_3301_331();
        }

        return $strReturn."\n\n";
	}
	
    private function update_311_319() {
        $strReturn = "Updating 3.1.1 to 3.1.9..\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("rating", "3.1.9");
        return $strReturn;
    }

    private function update_319_3195() {
        $strReturn = "Updating 3.1.9 to 3.1.95..\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("rating", "3.1.95");
        return $strReturn;
    }

    private function update_3195_320() {
        $strReturn = "Updating 3.1.95 to 3.2.0..\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("rating", "3.2.0");
        return $strReturn;
    }

    private function update_320_3209() {
        $strReturn = "Updating 3.2.0 to 3.2.0.9..\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("rating", "3.2.0.9");
        return $strReturn;
    }

    private function update_3209_321() {
        $strReturn = "Updating 3.2.0.9 to 3.2.1..\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("rating", "3.2.1");
        return $strReturn;
    }

    private function update_321_3291() {
        $strReturn = "Updating 3.2.1 to 3.2.91..\n";

        $strReturn .= "Reorganizing ratings..\n";

        $strQuery = "SELECT module_id
                       FROM "._dbprefix_."system_module
                      WHERE module_nr = "._rating_modul_id_."";
        $arrEntries = $this->objDB->getRow($strQuery);
        $strModuleId = $arrEntries["module_id"];

        $strQuery = "SELECT rating_id
                       FROM "._dbprefix_."rating";
        $arrEntries = $this->objDB->getArray($strQuery);

        foreach($arrEntries as $arrSingleRow) {
            $strReturn .= " ...updating rating ".$arrSingleRow["rating_id"]."";
            $strQuery = "UPDATE "._dbprefix_."system
                            SET system_prev_id = '".dbsafeString($strModuleId)."'
                          WHERE system_id = '".dbsafeString($arrSingleRow["rating_id"])."'";
            if($this->objDB->_query($strQuery))
                $strReturn .= " ...ok\n";
            else
                $strReturn .= " ...failed!!!\n";
        }

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("rating", "3.2.91");
        return $strReturn;
    }

    private function update_3291_330() {
        $strReturn = "Updating 3.2.91 to 3.3.0..\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("rating", "3.3.0");
        return $strReturn;
    }

    private function update_330_3301() {
        $strReturn = "Updating 3.3.0 to 3.3.0.1..\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("rating", "3.3.0.1");
        return $strReturn;
    }

    private function update_3301_331() {
        $strReturn = "Updating 3.3.0.1 to 3.3.1..\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("rating", "3.3.1");
        return $strReturn;
    }
	
}
?>