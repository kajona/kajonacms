<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: installer_eventmanager.php 3245 2010-04-10 13:58:22Z sidler $                                       *
********************************************************************************************************/

/**
 * Class providing an installer for the eventmanager module
 *
 * @package modul_eventmanager
 */
class class_installer_eventmanager extends class_installer_base implements interface_installer {

	public function __construct() {
        $arrModule = array();
		$arrModule["version"] 		  = "3.3.1.8";
		$arrModule["name"] 			  = "eventmanager";
		$arrModule["name_lang"] 	  = "Module Eventmanager";
		$arrModule["moduleId"] 		  = _eventmanager_modul_id_;
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}

    public function getMinSystemVersion() {
	    return "3.3.1.8";
	}

	public function hasPostInstalls() {
	    $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='eventmanager'";
	    $arrRow = $this->objDB->getRow($strQuery);
        if($arrRow["COUNT(*)"] == 0)
            return true;

        return false;
	}

   public function install() {
		$strReturn = "";
		//Tabellen anlegen

		//eventmanager cat-------------------------------------------------------------------------------------
		$strReturn .= "Installing table eventmanager_event...\n";

		$arrFields = array();
		$arrFields["eventmanager_event_id"]                        = array("char20", false);
		$arrFields["eventmanager_event_title"]                     = array("char254", true);
		$arrFields["eventmanager_event_description"]               = array("text", true);
		$arrFields["eventmanager_event_location"]                  = array("char254", true);
		$arrFields["eventmanager_event_participant_registration"]  = array("int", true);
		$arrFields["eventmanager_event_participant_limit"]         = array("int", true);
		$arrFields["eventmanager_event_participant_max"]           = array("int", true);

		if(!$this->objDB->createTable("eventmanager_event", $arrFields, array("eventmanager_event_id")))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "Installing table eventmanager_participant...\n";

		$arrFields = array();
		$arrFields["eventmanager_participant_id"]           = array("char20", false);
		$arrFields["eventmanager_participant_forename"]     = array("char254", true);
		$arrFields["eventmanager_participant_lastname"]     = array("char254", true);
		$arrFields["eventmanager_participant_email"]        = array("char254", true);
		$arrFields["eventmanager_participant_phone"]        = array("char254", true);
		$arrFields["eventmanager_participant_comment"]      = array("text", true);

		if(!$this->objDB->createTable("eventmanager_participant", $arrFields, array("eventmanager_participant_id")))
			$strReturn .= "An error occured! ...\n";

		

		//register the module
		$strSystemID = $this->registerModule("eventmanager", _eventmanager_modul_id_, "class_modul_eventmanager_portal.php", "class_modul_eventmanager_admin.php", $this->arrModule["version"], true);

        //modify default rights to allow guests to participate
		$strReturn .= "Modifying modules' rights node...\n";
		$this->objRights->addGroupToRight(_guests_group_id_, $strSystemID, "right1");

		return $strReturn;

	}

	public function postInstall() {
		$strReturn = "";

		//Register the element
		$strReturn .= "Registering eventmanager-element...\n";
		//check, if not already existing
        $objElement = null;
		try {
		    $objElement = class_modul_pages_element::getElement("eventmanager");
		}
		catch (class_exception $objEx)  {
		}
		if($objElement == null) {
		    $objElement = new class_modul_pages_element();
		    $objElement->setStrName("eventmanager");
		    $objElement->setStrClassAdmin("class_element_eventmanager.php");
		    $objElement->setStrClassPortal("class_element_eventmanager.php");
		    $objElement->setIntCachetime(-1);
		    $objElement->setIntRepeat(1);
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
        if($arrModul["module_version"] == "3.3.1.1") {
            $strReturn .= $this->update_3311_3318();
        }
        
        return $strReturn."\n\n";
	}
    
    private function update_3311_3318() {
        $strReturn = "Updating 3.3.1.1 to 3.3.1.8...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("eventmanager", "3.3.1.8");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("eventmanager", "3.3.1.8");
        return $strReturn;
    }


}
?>