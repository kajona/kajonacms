<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

/**
 * Class providing an install for the postacomment module
 *
 * @package modul_postacomment
 */
class class_installer_postacomment extends class_installer_base implements interface_installer {

	public function __construct() {
        $arrModule = array();
		$arrModule["version"] 		  = "3.3.0.1";
		$arrModule["name"] 			  = "postacomment";
		$arrModule["name_lang"] 	  = "Module Postacomment";
		$arrModule["moduleId"] 		  = _postacomment_modul_id_;

		$arrModule["table"]           = _dbprefix_."postacomment";
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}
	
    public function getMinSystemVersion() {
	    return "3.2.1";
	}

	public function hasPostInstalls() {
	    $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='postacomment'";
	    $arrRow = $this->objDB->getRow($strQuery);
        if($arrRow["COUNT(*)"] == 0)
            return true;

        return false;
	}

   public function install() {
		$strReturn = "";
		//Tabellen anlegen

		//postacomment ----------------------------------------------------------------------------------
		$strReturn .= "Installing table postacomment...\n";

		$arrFields = array();
		$arrFields["postacomment_id"] 		= array("char20", false);
		$arrFields["postacomment_date"] 	= array("int", true);
		$arrFields["postacomment_page"] 	= array("char254", true);
		$arrFields["postacomment_language"] = array("char20", true);
		$arrFields["postacomment_systemid"] = array("char20", true);
		$arrFields["postacomment_username"] = array("char254", true);
		$arrFields["postacomment_title"] 	= array("char254", true);
		$arrFields["postacomment_comment"] 	= array("text", true);
		
		if(!$this->objDB->createTable("postacomment", $arrFields, array("postacomment_id")))
			$strReturn .= "An error occured! ...\n";


		//register the module
		$strSystemID = $this->registerModule("postacomment", 
		                                     _postacomment_modul_id_, 
		                                     "class_modul_postacomment_portal.php", 
		                                     "class_modul_postacomment_admin.php", 
		                                     $this->arrModule["version"], 
		                                     true, 
		                                     "class_modul_postacomment_portal_xml.php");

		//modify default rights to allow guests to post
		$strReturn .= "Modifying modules' rights node...\n";
		$this->objRights->addGroupToRight(_guests_group_id_, $strSystemID, "right1");
		$this->objRights->addGroupToRight(_guests_group_id_, $strSystemID, "right2");
		
		$strReturn .= "Registering system-constants...\n";

		return $strReturn;

	}

	public function postInstall() {
		$strReturn = "";

		//Uses universal table, so no extra table to create

		//Register the element
		$strReturn .= "Registering postacomment-element...\n";
		//check, if not already existing
        $objElement = null;
		try {
		    $objElement = class_modul_pages_element::getElement("postacomment");
		}
		catch (class_exception $objEx)  {
		}
		if($objElement == null) {
		    $objElement = new class_modul_pages_element();
		    $objElement->setStrName("postacomment");
		    $objElement->setStrClassAdmin("class_element_postacomment.php");
		    $objElement->setStrClassPortal("class_element_postacomment.php");
		    $objElement->setIntCachetime(3600);
		    $objElement->setIntRepeat(0);
            $objElement->setStrVersion($this->getVersion());
			$objElement->updateObjectToDb();
			$strReturn .= "Element registered...\n";
		}
		else {
			$strReturn .= "Element already installed!...\n";
		}
		return $strReturn;
	}


	public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModul = $this->getModuleData($this->arrModule["name"], false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";
        
	    $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.1.0") {
            $strReturn .= $this->update_310_311();
        }
        
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

        return $strReturn."\n\n";
	}
	
    private function update_310_311() {
        $strReturn = "Updating 3.1.0 to 3.1.1..\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("postacomment", "3.1.1");
        return $strReturn;
    }
    
    private function update_311_319() {
        $strReturn = "Updating 3.1.1 to 3.1.9..\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("postacomment", "3.1.9");
        return $strReturn;
    }

    private function update_319_3195() {
        $strReturn = "Updating 3.1.9 to 3.1.95..\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("postacomment", "3.1.95");
        return $strReturn;
    }

    private function update_3195_320() {
        $strReturn = "Updating 3.1.95 to 3.2.0..\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("postacomment", "3.2.0");
        return $strReturn;
    }

    private function update_320_3209() {
        $strReturn = "Updating 3.2.0 to 3.2.0.9..\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("postacomment", "3.2.0.9");
        return $strReturn;
    }

    private function update_3209_321() {
        $strReturn = "Updating 3.2.0.9 to 3.2.1..\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("postacomment", "3.2.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("postacomment", "3.2.1");
        return $strReturn;
    }

    private function update_321_3291() {
        $strReturn = "Updating 3.2.1 to 3.2.91..\n";
        $strReturn .= "Updating module-versions...\n";

        $strReturn .= "Reorganizing comments..\n";

        $strQuery = "SELECT module_id
                       FROM "._dbprefix_."system_module
                      WHERE module_nr = "._postacomment_modul_id_."";
        $arrEntries = $this->objDB->getRow($strQuery);
        $strModuleId = $arrEntries["module_id"];

        $strQuery = "SELECT postacomment_id
                       FROM "._dbprefix_."postacomment";
        $arrEntries = $this->objDB->getArray($strQuery);

        foreach($arrEntries as $arrSingleRow) {
            $strReturn .= " ...updating comment ".$arrSingleRow["postacomment_id"]."";
            $strQuery = "UPDATE "._dbprefix_."system
                            SET system_prev_id = '".dbsafeString($strModuleId)."'
                          WHERE system_id = '".dbsafeString($arrSingleRow["postacomment_id"])."'";
            if($this->objDB->_query($strQuery))
                $strReturn .= " ...ok\n";
            else
                $strReturn .= " ...failed!!!\n";
        }

        $this->updateModuleVersion("postacomment", "3.2.91");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("postacomment", "3.2.91");
        return $strReturn;
    }

    private function update_3291_330() {
        $strReturn = "Updating 3.2.91 to 3.3.0..\n";
        $strReturn .= "Updating module-versions...\n";

        $this->updateModuleVersion("postacomment", "3.3.0");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("postacomment", "3.3.0");
        return $strReturn;
    }

    private function update_330_3301() {
        $strReturn = "Updating 3.3.0 to 3.3.0.1..\n";
        $strReturn .= "Updating module-versions...\n";

        $strReturn .= "Setting cache-timeouts for postacomment-element...\n";
        $strQuery = "UPDATE "._dbprefix_."element
                        SET element_cachetime=3600
                      WHERE element_class_admin = 'class_element_postacomment.php'";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ...\n";

        $this->updateModuleVersion("postacomment", "3.3.0.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("postacomment", "3.3.0.1");
        return $strReturn;
    }

}
?>